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
        'track-ticket', // public support-ticket lookup — no account needed
    ];

    private const ALLOWED_EXACT = [
        'login',
        'login-flight',     // flight / agent login design
        'signin',           // login form POST handler
        'signin/enroll',    // first-time 2FA QR setup
        'signin/verify',    // 2FA code step
        'forgot-password',  // password reset request form
        'reset-password',   // password reset form (token in query string)
        'admin/login',
        'set-currency',
        'user/register',
        'user/logout',
        'newsletter/subscribe',
        // license pages: the license check sends visitors here; without them a
        // blocked site bounced to the signup/landing page instead of the error
        'license-setup',
        'license-setup/test',
        'license-setup/save',
        'license-invalid',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Signup the package doesn't include (license server → Client Packages) is
        // closed even when its URL is typed directly — hiding the link isn't enough.
        $allowed = allowedBusinessModel();
        if ($allowed === 'b2c' && $path === 'agent/register') {
            return redirect('/');
        }
        if ($allowed === 'b2b' && $path === 'user/register') {
            return redirect()->route('agent.register');
        }

        $businessModel = businessModel();

        if ($businessModel !== 'b2b') {
            return $next($request);
        }

        // Always allow admin/agent/auth/license paths through
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        if (in_array($path, self::ALLOWED_EXACT)) {
            return $next($request);
        }

        // The Agent Signup destination (System Settings > Agent Signup URL) may
        // be a custom path outside the built-in /agent/* prefix — always let
        // that specific page through too, or visitors would be redirected
        // straight back to the page they were just sent to.
        $signupUrl = agentSignupUrl();
        $signupPath = agentSignupRelativePath();
        if ($signupPath !== null && $path === $signupPath) {
            return $next($request);
        }

        // B2B mode: require agent or admin login for all other pages
        if (!auth()->check()) {
            return redirect()->to($signupUrl);
        }

        $user = auth()->user();

        // Regular users (non-agent, non-admin) are not allowed in B2B mode
        if (!$user->isAgent() && !$user->isAdmin()) {
            return redirect()->to($signupUrl);
        }

        return $next($request);
    }
}
