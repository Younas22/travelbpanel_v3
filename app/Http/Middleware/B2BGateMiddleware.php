<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class B2BGateMiddleware
{
    // Paths that are always accessible regardless of business model
    private const ALLOWED_PREFIXES = [
        'admin',
        'agent',
        'license',
        'up',        // health check
    ];

    private const ALLOWED_EXACT = [
        'login',
        'signin',           // login form POST handler
        'admin/login',
        'set-currency',
        'user/register',
        'user/logout',
        'newsletter/subscribe',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $businessModel = Setting::getValue('business_model', 'system') ?? 'both';

        if ($businessModel !== 'b2b') {
            return $next($request);
        }

        $path = $request->path();

        // Always allow admin/agent/auth/license paths through
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        if (in_array($path, self::ALLOWED_EXACT)) {
            return $next($request);
        }

        // B2B mode: require agent or admin login for all other pages
        if (!auth()->check()) {
            return redirect()->route('agent.register');
        }

        $user = auth()->user();

        // Regular users (non-agent, non-admin) are not allowed in B2B mode
        if (!$user->isAgent() && !$user->isAdmin()) {
            return redirect()->route('agent.register');
        }

        return $next($request);
    }
}
