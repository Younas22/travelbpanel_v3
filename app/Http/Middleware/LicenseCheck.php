<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces SaaS license validation on every request.
 *
 * On each request:
 *  1. Read the encrypted local token from storage/app/.license
 *  2. If token is valid and revalidation interval not reached → allow
 *  3.   If >5 min since last ping → ping server for instant revocation detection
 *  4. Otherwise → call POST /api/validate-license on the admin panel
 *  5. If valid → store new token, allow
 *  6. If invalid → redirect to license error page (blocks entire system)
 *
 * Required .env keys:
 *   LICENSE_KEY, LICENSE_API_KEY, LICENSE_API_SECRET, LICENSE_SERVER_URL
 */
class LicenseCheck
{
    private string $tokenPath;
    private int    $revalidateAfter; // seconds
    private int    $pingInterval;    // seconds between lightweight pings

    public function __construct()
    {
        $this->tokenPath      = storage_path('app/.license');
        $this->revalidateAfter = (int) env('LICENSE_REVALIDATE_HOURS', 12) * 3600;
        $this->pingInterval   = (int) env('LICENSE_PING_INTERVAL', 300); // default 5 min
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Always allow license pages (prevents infinite redirect)
        if ($request->routeIs('license.invalid') ||
            $request->routeIs('license.setup') ||
            $request->routeIs('license.setup.test') ||
            $request->routeIs('license.setup.save')) {
            return $next($request);
        }

        // Skip license check when running from source code locally
        if (app()->environment('local')) {
            return $next($request);
        }

        // Detect placeholder or missing config → show setup wizard
        if ($this->isNotConfigured()) {
            return redirect()->route('license.setup');
        }

        $domain = $request->getHost();
        $ip     = $this->getServerIp();

        if ($this->hasValidLocalToken($domain, $ip)) {
            // Ping server every N minutes to catch instant revocations
            if (! $this->pingServer()) {
                @unlink($this->tokenPath);
                Log::warning('[License] Ping returned revoked — blocking access', ['domain' => $domain]);
                return redirect()->route('license.invalid', ['reason' => 'token_revoked']);
            }
            return $next($request);
        }

        // Local token missing / expired → revalidate with server
        $result = $this->callLicenseServer($domain, $ip);

        if (($result['status'] ?? '') === 'valid') {
            $this->storeToken($result, $domain, $ip);
            return $next($request);
        }

        $code = $result['code'] ?? 'unknown';

        Log::warning('[License] Blocked', ['code' => $code, 'domain' => $domain, 'ip' => $ip]);

        if ($code === 'missing_config') {
            return redirect()->route('license.setup');
        }

        return redirect()->route('license.invalid', ['reason' => $code]);
    }

    // ── Local Token ───────────────────────────────────────────────────────────

    private function hasValidLocalToken(string $domain, string $ip): bool
    {
        if (! file_exists($this->tokenPath)) {
            return false;
        }

        try {
            $raw  = file_get_contents($this->tokenPath);
            $data = json_decode(decrypt($raw), true);

            if (! is_array($data)) {
                return false;
            }

            if (($data['domain'] ?? '') !== $domain || ($data['ip'] ?? '') !== $ip) {
                @unlink($this->tokenPath);
                return false;
            }

            if (($data['expires_at'] ?? 0) < time()) {
                return false;
            }

            if ((time() - ($data['stored_at'] ?? 0)) >= $this->revalidateAfter) {
                return false;
            }

            return true;
        } catch (\Throwable) {
            @unlink($this->tokenPath);
            return false;
        }
    }

    private function storeToken(array $result, string $domain, string $ip): void
    {
        $expiresAt = $result['expires_at'] ?? null;

        $payload = json_encode([
            'token'        => $result['token'],
            'domain'       => $domain,
            'ip'           => $ip,
            'expires_at'   => $expiresAt ? strtotime($expiresAt) : (time() + 86400),
            'stored_at'    => time(),
            'last_ping_at' => time(),
            'license'      => $result['license'] ?? [],
        ]);

        file_put_contents($this->tokenPath, encrypt($payload));
        @chmod($this->tokenPath, 0600);
    }

    // ── Ping (instant revocation detection) ──────────────────────────────────

    /**
     * Sends a lightweight heartbeat to the license server.
     * Returns true = still active (or skipped / server unreachable — fail open).
     * Returns false = server explicitly says revoked.
     */
    private function pingServer(): bool
    {
        try {
            $raw  = file_get_contents($this->tokenPath);
            $data = json_decode(decrypt($raw), true);

            if (! is_array($data)) {
                return true; // corrupted — full revalidation will handle it
            }

            // Skip if pinged recently
            if ((time() - ($data['last_ping_at'] ?? 0)) < $this->pingInterval) {
                return true;
            }

            $serverUrl = rtrim(env('LICENSE_SERVER_URL', ''), '/');
            if (! $serverUrl) {
                return true;
            }

            $tokenHash = hash('sha256', $data['token'] ?? '');

            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$serverUrl}/api/license-ping", ['token_hash' => $tokenHash]);

            $body = $response->json();

            if (($body['status'] ?? '') === 'revoked') {
                return false;
            }

            // Update last_ping_at in the stored token
            $data['last_ping_at'] = time();
            file_put_contents($this->tokenPath, encrypt(json_encode($data)));
            @chmod($this->tokenPath, 0600);

            return true;
        } catch (\Throwable $e) {
            // Network error / server down — fail open (don't block the client)
            Log::warning('[License] Ping failed (fail-open)', ['error' => $e->getMessage()]);
            return true;
        }
    }

    // ── License Server Call ───────────────────────────────────────────────────

    private function callLicenseServer(string $domain, string $ip): array
    {
        $licenseKey = env('LICENSE_KEY',        '');
        $apiKey     = env('LICENSE_API_KEY',    '');
        $secret     = env('LICENSE_API_SECRET', '');
        $serverUrl  = rtrim(env('LICENSE_SERVER_URL', ''), '/');

        if (! $licenseKey || ! $apiKey || ! $secret || ! $serverUrl) {
            return [
                'status'  => 'invalid',
                'code'    => 'missing_config',
                'message' => 'LICENSE_KEY, LICENSE_API_KEY, LICENSE_API_SECRET, LICENSE_SERVER_URL must be set in .env',
            ];
        }

        $timestamp = time();
        $nonce     = bin2hex(random_bytes(16));
        $signature = hash_hmac(
            'sha256',
            "{$licenseKey}|{$domain}|{$ip}|{$timestamp}|{$nonce}",
            $secret
        );

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$serverUrl}/api/validate-license", [
                    'api_key'     => $apiKey,
                    'license_key' => $licenseKey,
                    'domain'      => $domain,
                    'ip'          => $ip,
                    'timestamp'   => $timestamp,
                    'nonce'       => $nonce,
                    'signature'   => $signature,
                ]);

            $body = $response->json();

            if (! is_array($body)) {
                return ['status' => 'error', 'code' => 'invalid_response'];
            }

            return $body;
        } catch (\Throwable $e) {
            Log::error('[License] Server unreachable', ['error' => $e->getMessage()]);
            return [
                'status'  => 'error',
                'code'    => 'server_unreachable',
                'message' => $e->getMessage(),
            ];
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getServerIp(): string
    {
        return gethostbyname(gethostname()) ?: '127.0.0.1';
    }

    private function isNotConfigured(): bool
    {
        $key    = env('LICENSE_KEY', '');
        $apiKey = env('LICENSE_API_KEY', '');
        $secret = env('LICENSE_API_SECRET', '');
        $url    = env('LICENSE_SERVER_URL', '');

        return empty($key)
            || str_contains($key,    'XXXX')
            || empty($apiKey)
            || str_contains($apiKey, 'xxxx')
            || ! str_starts_with($apiKey, 'lak_')
            || empty($secret)
            || str_contains($secret, 'xxxx')
            || empty($url);
    }
}
