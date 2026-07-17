<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check()) {
            return $this->redirectByRole(auth()->user());
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $user = Auth::user();
        $user->update(['last_activity' => now()]);

        // Agent-specific status checks
        if ($user->isAgent()) {
            if ($user->approval_status === 'pending') {
                return redirect()->route('agent.pending');
            }
            if ($user->approval_status === 'suspended') {
                return redirect()->route('agent.suspended');
            }
        }

        return $this->redirectByRole($user);
    }

    private function redirectByRole($user)
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard.index'));
        }
        if ($user->isAgent()) {
            return redirect()->intended(route('agent.dashboard'));
        }
        return redirect()->intended(route('user.dashboard'));
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Logged out successfully!');
    }
}