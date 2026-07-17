<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class LicenseSetupController extends Controller
{
    // ── Show Setup Wizard ─────────────────────────────────────────────────────

    public function index()
    {
        // If license is already valid, skip setup
        if ($this->isLicenseActive()) {
            return redirect('/');
        }

        $current = [
            'license_key'        => env('LICENSE_KEY', ''),
            'license_api_key'    => env('LICENSE_API_KEY', ''),
            'license_server_url' => env('LICENSE_SERVER_URL', 'https://travelbookingpanel.com'),
        ];

        return view('license.setup', compact('current'));
    }

    // ── AJAX: Test connection before saving ───────────────────────────────────

    public function test(Request $request)
    {
        $data = $request->validate([
            'license_key'        => 'required|string',
            'license_api_key'    => 'required|string',
            'license_api_secret' => 'required|string',
            'license_server_url' => 'required|url',
        ]);

        $result = $this->callValidateApi(
            licenseKey: strtoupper(trim($data['license_key'])),
            apiKey:     trim($data['license_api_key']),
            secret:     trim($data['license_api_secret']),
            serverUrl:  rtrim($data['license_server_url'], '/'),
            domain:     $request->getHost(),
            ip:         $this->getServerIp(),
        );

        return response()->json($result);
    }

    // ── Save to .env and activate ─────────────────────────────────────────────

    public function save(Request $request)
    {
        $data = $request->validate([
            'license_key'        => 'required|string',
            'license_api_key'    => 'required|string|starts_with:lak_',
            'license_api_secret' => 'required|string|min:32',
            'license_server_url' => 'required|url',
        ]);

        $licenseKey = strtoupper(trim($data['license_key']));
        $apiKey     = trim($data['license_api_key']);
        $secret     = trim($data['license_api_secret']);
        $serverUrl  = rtrim($data['license_server_url'], '/');
        $domain     = $request->getHost();
        $ip         = $this->getServerIp();

        // Verify with server before saving
        $result = $this->callValidateApi($licenseKey, $apiKey, $secret, $serverUrl, $domain, $ip);

        if (($result['status'] ?? '') !== 'valid') {
            return back()
                ->withInput()
                ->with('error', $result['message'] ?? 'License validation failed.')
                ->with('error_code', $result['code'] ?? 'unknown');
        }

        // Write to .env
        $this->writeToEnv([
            'LICENSE_KEY'              => $licenseKey,
            'LICENSE_API_KEY'          => $apiKey,
            'LICENSE_API_SECRET'       => $secret,
            'LICENSE_SERVER_URL'       => $serverUrl,
            'LICENSE_REVALIDATE_HOURS' => '12',
        ]);

        // Store the valid token immediately so user isn't blocked
        $this->storeToken($result, $domain, $ip);

        // Clear config cache so new .env values take effect
        Artisan::call('config:clear');

        return redirect('/')->with('license_activated', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function callValidateApi(
        string $licenseKey,
        string $apiKey,
        string $secret,
        string $serverUrl,
        string $domain,
        string $ip
    ): array {
        $timestamp = time();
        $nonce     = bin2hex(random_bytes(16));
        $signature = hash_hmac('sha256',
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

            return $response->json() ?? ['status' => 'error', 'code' => 'empty_response', 'message' => 'Empty response from server'];
        } catch (\Throwable $e) {
            return [
                'status'  => 'error',
                'code'    => 'server_unreachable',
                'message' => 'Cannot connect to license server: ' . $e->getMessage(),
            ];
        }
    }

    private function writeToEnv(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $line = "{$key}={$value}";
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", $line, $content);
            } else {
                $content .= "\n{$line}";
            }
        }

        file_put_contents($envPath, $content);
    }

    private function storeToken(array $result, string $domain, string $ip): void
    {
        $tokenPath = storage_path('app/.license');
        $expiresAt = $result['expires_at'] ?? null;

        $payload = json_encode([
            'token'      => $result['token'],
            'domain'     => $domain,
            'ip'         => $ip,
            'expires_at' => $expiresAt ? strtotime($expiresAt) : (time() + 86400),
            'stored_at'  => time(),
            'license'    => $result['license'] ?? [],
        ]);

        file_put_contents($tokenPath, encrypt($payload));
        @chmod($tokenPath, 0600);
    }

    private function isLicenseActive(): bool
    {
        $tokenPath = storage_path('app/.license');
        if (! file_exists($tokenPath)) {
            return false;
        }
        try {
            $data = json_decode(decrypt(file_get_contents($tokenPath)), true);
            return is_array($data)
                && ($data['expires_at'] ?? 0) > time()
                && ($data['domain'] ?? '') === request()->getHost();
        } catch (\Throwable) {
            return false;
        }
    }

    private function getServerIp(): string
    {
        return gethostbyname(gethostname()) ?: '127.0.0.1';
    }
}
