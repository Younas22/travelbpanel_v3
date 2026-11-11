<?php

namespace App\Http\Controllers;

use App\Services\LicenseGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * First-run license activation: the owner pastes the license key, the license
 * server answers with a signed certificate (App\Services\LicenseGuard), and the
 * key is saved to .env.
 */
class LicenseSetupController extends Controller
{
    private const MESSAGES = [
        'license_valid'            => 'License is valid for this domain.',
        'license_not_found'        => 'This license key does not exist.',
        'license_revoked'          => 'This license has been revoked.',
        'license_suspended'        => 'This license is suspended.',
        'license_inactive'         => 'This license is not active yet.',
        'license_expired'          => 'This license has expired.',
        'domain_not_allowed'       => 'This license is not for this domain.',
        'no_domain_set'            => 'No domain is set on this license yet — ask your provider to add this domain.',
        'ip_not_allowed'           => 'This license is not for this server.',
        'activation_limit_reached' => 'This license is already used on its maximum number of domains.',
        'activation_revoked'       => 'This domain has been removed from the license.',
        'server_unreachable'       => 'The license server could not be reached. Check the internet connection and try again.',
    ];

    public function __construct(private LicenseGuard $guard)
    {
    }

    public function index(Request $request)
    {
        if ($this->guard->isConfigured() && $this->guard->savedCertificate($request->getHost())) {
            return redirect('/');
        }

        // This page is public: never send the saved key to the browser — only its last 4 characters as a hint.
        $saved = $this->guard->isConfigured() ? substr($this->guard->licenseKey(), -4) : null;

        return view('license.setup', ['savedKeyHint' => $saved]);
    }

    /** AJAX: check a key before saving. */
    public function test(Request $request)
    {
        $key = $this->validatedKey($request);
        $answer = $this->guard->activate($request->getHost(), $key, false);

        return response()->json([
            'status'  => $answer['state'] === 'valid' ? 'valid' : 'invalid',
            'code'    => $answer['code'],
            'message' => self::MESSAGES[$answer['code']] ?? 'License validation failed.',
            'detail'  => $answer['detail'] ?? null,
            'license' => isset($answer['license']) ? [
                'client'     => $answer['license']['client'] ?? null,
                'product'    => $answer['license']['product'] ?? null,
                'expires_at' => $answer['license']['lic_exp'] ?? null,
            ] : null,
        ]);
    }

    public function save(Request $request)
    {
        $key = $this->validatedKey($request);
        $answer = $this->guard->activate($request->getHost(), $key, false);

        if ($answer['state'] !== 'valid') {
            return back()->withInput()
                ->with('error', self::MESSAGES[$answer['code']] ?? 'License validation failed.')
                ->with('error_code', $answer['code'])
                ->with('error_detail', $answer['detail'] ?? null);
        }

        $this->writeToEnv(['LICENSE_KEY' => $key]);
        $this->guard->save($answer['certificate']);
        Artisan::call('config:clear');

        return redirect('/')->with('license_activated', true);
    }

    private function validatedKey(Request $request): string
    {
        $data = $request->validate([
            'license_key' => ['required', 'string', 'regex:/^[A-Za-z0-9]{4}(-[A-Za-z0-9]{4}){3}$/'],
        ], ['license_key.regex' => 'The license key looks like XXXX-XXXX-XXXX-XXXX.']);

        return strtoupper(trim($data['license_key']));
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
}
