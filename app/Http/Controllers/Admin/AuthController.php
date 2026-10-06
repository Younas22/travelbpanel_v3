<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Support\Totp;
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

        $credentials = $request->only('email', 'password');

        if (!Auth::validate($credentials)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $candidate = User::where('email', $credentials['email'])->first();

        if ($candidate->isAdmin() && $this->twoFactorRequired()) {
            RateLimiter::clear($key);
            $request->session()->put('admin_2fa_pending', [
                'user_id' => $candidate->id,
                'remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('admin.2fa.show');
        }

        Auth::login($candidate, $request->boolean('remember'));

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

    public function showTwoFactor(Request $request)
    {
        if (!$request->session()->has('admin_2fa_pending')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $pending = $request->session()->get('admin_2fa_pending');
        if (!$pending) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $key = 'login.2fa.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (!Totp::verify((string) Setting::getValue('admin_2fa_secret', 'security'), $request->code)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'code' => 'The code is incorrect or has expired. Please try again.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->forget('admin_2fa_pending');

        $user = User::findOrFail($pending['user_id']);
        Auth::login($user, $pending['remember']);
        $request->session()->regenerate();

        $user->update(['last_activity' => now()]);

        return $this->redirectByRole($user);
    }

    private function twoFactorRequired(): bool
    {
        return Setting::getValue('admin_2fa_enabled', 'security') === '1'
            && filled(Setting::getValue('admin_2fa_secret', 'security'));
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