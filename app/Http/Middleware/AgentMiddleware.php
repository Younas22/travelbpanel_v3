<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Not logged in
        if (!auth()->check()) {
            $businessModel = Setting::getValue('business_model', 'system') ?? 'both';
            if ($businessModel === 'b2b') {
                return redirect()->route('agent.register');
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Not an agent
        if (!$user->isAgent()) {
            abort(403, 'Access denied. Agent account required.');
        }

        // Pending approval
        if ($user->approval_status === 'pending') {
            return redirect()->route('agent.pending');
        }

        // Suspended
        if ($user->approval_status === 'suspended') {
            return redirect()->route('agent.suspended');
        }

        return $next($request);
    }
}
