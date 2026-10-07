<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesTwoFactorLogin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use HandlesTwoFactorLogin;

    public function showLogin()
    {
        if (auth()->check()) {
            return TwoFactor::redirectForRole(auth()->user());
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

        RateLimiter::clear($key);

        $candidate = User::where('email', $credentials['email'])->first();

        return $this->proceedAfterPassword($request, $candidate, $request->boolean('remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully!');
    }
}
