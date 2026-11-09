<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * License check against the TravelBookingPanel license server (v2 API).
 *
 * Every answer from the server is signed with the server's private key; this
 * file only holds the matching PUBLIC key, which can check a signature but
 * can't make one. So a license certificate can't be written by hand, edited
 * (domain, expiry …), or answered by a fake server — any of those fails the
 * signature check and the site stays locked.
 *
 * The client needs only LICENSE_KEY in .env (set by the /license-setup page).
 */
class LicenseGuard
{
    /** Public key of the license server (php artisan license:keys there). */
    private const PUBLIC_KEY = <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoee83sgO+IFHTqzkvBvq
ueY5OiCOKs8CVyjOykRyXfrlZtiAhL/sqd48JIkwptwbR6QCcLYywP382jmEEimv
mKUh4hzD2s2npXWV4HvDV2ThdW+qAAIA51SiYBEv80wQUkMT+sHQWf7l+0z5JOG/
2BYzoCvytyzwOYSvAxDUyTX0ikC5AAc+ne9U8cQnijYR/REcyiWtqicX2S/cXaJa
wuFWIa+CutomJA/+CMfCfcT1TNsFrNF/EiquxFM4wfjGY4fiPoYKoQCyYw07VZCf
UqkurWBCcXVXbLZ2KG+laCFNiUoR47UNf4VgbShZ7NvbTDp947FW/uz9bpMF6Ahr
YQIDAQAB
-----END PUBLIC KEY-----
PEM;

    private const DEFAULT_SERVER = 'https://travelbookingpanel.com';

    /** Get a fresh certificate this often (it stays usable offline until it expires). */
    private const REFRESH_AFTER = 12 * 3600;

    /** Ask the server "still allowed?" this often, so a revoked license stops quickly. */
    private const PING_EVERY = 30 * 60;

    /** Why the last server answer could not be used (shown on the setup page). */
    private ?string $lastError = null;

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function licenseKey(): string
    {
        return strtoupper(trim((string) config('license.key', '')));
    }

    public function isConfigured(): bool
    {
        return (bool) preg_match('/^[A-Z0-9]{4}(-[A-Z0-9]{4}){3}$/', $this->licenseKey());
    }

    /** Sites opened on this machine itself (development) are not checked. */
    public static function isLocalHost(string $host): bool
    {
        return in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '[::1]'], true);
    }

    /**
     * The decision for one request: ['ok' => bool, 'code' => string].
     * Uses the saved certificate when it is genuine; refreshes / pings the
     * server on schedule; when the server can't be reached, keeps running only
     * until the saved certificate expires (offline grace).
     */
    public function check(string $domain): array
    {
        $domain = strtolower($domain);
        $cert = $this->savedCertificate($domain);

        if ($cert) {
            if (time() - $cert['iat'] >= self::REFRESH_AFTER) {
                $answer = $this->activate($domain);
                if ($answer['state'] === 'invalid') {
                    $this->forget();
                    return ['ok' => false, 'code' => $answer['code']];
                }
                // valid → saved inside activate(); unreachable → keep the current certificate
                return ['ok' => true, 'code' => 'license_valid'];
            }

            if ($this->pingDue()) {
                $ping = $this->ping($domain);
                if ($ping['state'] === 'revoked') {
                    $this->forget();
                    return ['ok' => false, 'code' => $ping['code']];
                }
            }

            return ['ok' => true, 'code' => 'license_valid'];
        }

        $answer = $this->activate($domain);

        return $answer['state'] === 'valid'
            ? ['ok' => true, 'code' => 'license_valid']
            : ['ok' => false, 'code' => $answer['code']];
    }

    /**
     * Ask the server for a certificate. ['state' => valid|invalid|unreachable, 'code', 'license'?]
     * A valid one is saved. Only a correctly signed answer counts.
     */
    public function activate(string $domain, ?string $licenseKey = null, bool $save = true): array
    {
        $licenseKey = $licenseKey ?? $this->licenseKey();
        $nonce = bin2hex(random_bytes(16));
        $answer = $this->post('/api/v2/license/activate', [
            'license_key' => $licenseKey,
            'domain'      => strtolower($domain),
            'ip'          => $this->serverIp(),
            'nonce'       => $nonce,
        ]);

        $payload = $answer ? $this->verify($answer, $nonce, $licenseKey, $domain, 'certificate') : null;
        if (! $payload) {
            return ['state' => 'unreachable', 'code' => 'server_unreachable', 'detail' => $this->lastError];
        }
        if ($payload['status'] !== 'valid' || ($payload['exp'] ?? 0) < time()) {
            return ['state' => 'invalid', 'code' => $payload['code'] ?? 'license_invalid'];
        }

        if ($save) {
            $this->save($answer);
            $this->markPinged();
            $this->rememberBranding($answer, $payload);
        }

        return ['state' => 'valid', 'code' => 'license_valid', 'license' => $payload, 'certificate' => $answer];
    }

    /** ['state' => active|revoked|unreachable, 'code'] */
    public function ping(string $domain): array
    {
        $nonce = bin2hex(random_bytes(16));
        $answer = $this->post('/api/v2/license/ping', [
            'license_key' => $this->licenseKey(),
            'domain'      => strtolower($domain),
            'nonce'       => $nonce,
        ]);

        $payload = $answer ? $this->verify($answer, $nonce, $this->licenseKey(), $domain, 'ping') : null;
        if (! $payload) {
            return ['state' => 'unreachable', 'code' => 'server_unreachable'];
        }

        $this->markPinged();
        $this->rememberBranding($answer, $payload);

        return $payload['status'] === 'active'
            ? ['state' => 'active', 'code' => 'ok']
            : ['state' => 'revoked', 'code' => $payload['code'] ?? 'license_revoked'];
    }

    /** The saved certificate's details when it is genuine and still valid for this domain, else null. */
    public function savedCertificate(string $domain): ?array
    {
        $file = $this->certificatePath();
        if (! is_file($file)) {
            return null;
        }

        $cert = json_decode((string) file_get_contents($file), true);
        $payload = is_array($cert) ? $this->verify($cert, null, $this->licenseKey(), $domain, 'certificate') : null;

        if (! $payload || $payload['status'] !== 'valid' || ($payload['exp'] ?? 0) < time()) {
            return null;
        }

        return $payload;
    }

    /** Store a certificate from the setup page (already checked by activate()). */
    public function save(array $certificate): void
    {
        file_put_contents($this->certificatePath(), json_encode([
            'payload'   => $certificate['payload'],
            'signature' => $certificate['signature'],
        ]));
        @chmod($this->certificatePath(), 0600);
    }

    public function forget(): void
    {
        @unlink($this->certificatePath());
    }

    /**
     * Show "Made by TravelBookingPanel" in the footer?
     *
     * The license server decides (Admin > Client Packages) and sends the choice
     * inside its SIGNED ping/certificate answers. The last such answer is kept
     * with its signature and checked again here, so editing the saved file by
     * hand doesn't hide the credit. No genuine answer yet → shown (default).
     */
    public function showBranding(string $domain): bool
    {
        static $cache = [];
        $domain = strtolower($domain);
        if (array_key_exists($domain, $cache)) {
            return $cache[$domain];
        }

        $show = true;
        $saved = json_decode((string) @file_get_contents($this->brandingPath()), true);
        if (is_array($saved) && isset($saved['payload']) && is_string($saved['payload'])) {
            $type = json_decode($this->b64d($saved['payload']), true)['type'] ?? '';
            $payload = in_array($type, ['ping', 'certificate'], true)
                ? $this->verify($saved, null, $this->licenseKey(), $domain, $type)
                : null;
            if ($payload && ($payload['brand'] ?? null) === 'hide') {
                $show = false;
            }
        }

        return $cache[$domain] = $show;
    }

    // ── internals ─────────────────────────────────────────────────────────────

    /** Keep a signed answer that carries the branding choice (newest wins). */
    private function rememberBranding(array $answer, array $payload): void
    {
        if (! isset($payload['brand'])) {
            return; // older license server: nothing to store, keep the last choice
        }
        @file_put_contents($this->brandingPath(), json_encode([
            'payload'   => $answer['payload'],
            'signature' => $answer['signature'],
        ]));
    }

    private function brandingPath(): string
    {
        return storage_path('app/license.brand');
    }

    /**
     * Payload of a signed answer, or null when the signature is wrong or it is
     * not an answer to this request (nonce), this license, this domain, this kind.
     */
    private function verify(array $answer, ?string $nonce, string $licenseKey, string $domain, string $type): ?array
    {
        if (! isset($answer['payload'], $answer['signature']) || ! is_string($answer['payload']) || ! is_string($answer['signature'])) {
            $this->lastError = $this->serverUrl() . ' did not send a signed license answer'
                . (isset($answer['code']) ? ' (it said: ' . $answer['code'] . ')' : '')
                . '. Is LICENSE_SERVER_URL right and is the license server updated?';
            return null;
        }

        $json = $this->b64d($answer['payload']);
        $signature = $this->b64d($answer['signature']);
        $key = openssl_pkey_get_public(self::PUBLIC_KEY);

        if (! $key || openssl_verify($json, $signature, $key, OPENSSL_ALGO_SHA256) !== 1) {
            $this->lastError = 'The answer from ' . $this->serverUrl() . ' is signed with a different key than this site expects (wrong license server, or its signing key was changed).';
            return null;
        }

        $payload = json_decode($json, true);
        if (! is_array($payload)
            || ($payload['v'] ?? null) !== 2
            || ($payload['type'] ?? '') !== $type
            || ($payload['lic'] ?? '') !== $licenseKey
            || ($payload['dom'] ?? '') !== strtolower($domain)
            || ($nonce !== null && ! hash_equals($nonce, (string) ($payload['nonce'] ?? '')))) {
            $this->lastError = 'The license server answered for another license / domain / request.';
            return null;
        }

        return $payload;
    }

    private function post(string $path, array $body): ?array
    {
        $this->lastError = null;
        try {
            $response = Http::timeout(10)->acceptJson()->post($this->serverUrl() . $path, $body);
            $json = $response->json();
            if (! is_array($json)) {
                $this->lastError = $this->serverUrl() . $path . ' answered HTTP ' . $response->status() . ' without a license answer. Is LICENSE_SERVER_URL right?';
                Log::warning('[License] ' . $this->lastError);
                return null;
            }
            return $json;
        } catch (\Throwable $e) {
            // e.g. DNS / firewall / SSL certificate problems on this server
            $this->lastError = 'Could not connect to ' . $this->serverUrl() . ': ' . $e->getMessage();
            Log::warning('[License] Server unreachable', ['error' => $e->getMessage(), 'server' => $this->serverUrl()]);
            return null;
        }
    }

    private function serverUrl(): string
    {
        $url = rtrim((string) config('license.server_url', ''), '/');
        // a localhost address left in .env (copied from a developer machine) is
        // ignored on a real domain — it can never be the license server there
        $urlHost = strtolower((string) parse_url($url, PHP_URL_HOST));
        $siteHost = app()->runningInConsole() ? '' : request()->getHost();
        if ($url === '' || (self::isLocalHost($urlHost) && ! self::isLocalHost($siteHost))) {
            return self::DEFAULT_SERVER;
        }

        return $url;
    }

    private function serverIp(): ?string
    {
        $ip = gethostbyname(gethostname());
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }

    private function certificatePath(): string
    {
        return storage_path('app/license.cert');
    }

    private function pingDue(): bool
    {
        $last = (int) @file_get_contents(storage_path('app/license.ping'));
        return time() - $last >= self::PING_EVERY;
    }

    private function markPinged(): void
    {
        @file_put_contents(storage_path('app/license.ping'), (string) time());
    }

    private function b64d(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $pad = strlen($data) % 4;
        return (string) base64_decode($pad ? $data . str_repeat('=', 4 - $pad) : $data);
    }
}
