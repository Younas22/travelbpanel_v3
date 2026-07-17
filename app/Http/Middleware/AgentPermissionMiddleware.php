<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permissionKey): Response
    {
        $user = auth()->user();

        if (!$user || !$user->hasPermission($permissionKey)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Permission denied.'], 403);
            }
            return response()->view('agent.403', ['permission' => $permissionKey], 403);
        }

        return $next($request);
    }
}
