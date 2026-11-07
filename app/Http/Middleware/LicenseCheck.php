<?php

namespace App\Http\Middleware;

use App\Services\LicenseGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks the whole site unless it holds a genuine license certificate for this
 * domain (see App\Services\LicenseGuard: certificates are signed by the license
 * server and checked here with its public key, so they can't be made by hand).
 *
 * Only a site opened on the machine itself (localhost) skips the check — APP_ENV
 * or other .env values don't switch it off.
 */
class LicenseCheck
{
    public function __construct(private LicenseGuard $guard)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('license.invalid', 'license.setup', 'license.setup.test', 'license.setup.save')) {
            return $next($request);
        }

        if (LicenseGuard::isLocalHost($request->getHost())) {
            return $next($request);
        }

        if (! $this->guard->isConfigured()) {
            return redirect()->route('license.setup');
        }

        $result = $this->guard->check($request->getHost());
        if ($result['ok']) {
            return $next($request);
        }

        Log::warning('[License] Blocked', ['code' => $result['code'], 'domain' => $request->getHost()]);

        return redirect()->route('license.invalid', ['reason' => $result['code']]);
    }
}
