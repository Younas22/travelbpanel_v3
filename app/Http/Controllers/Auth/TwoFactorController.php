<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Totp;
use App\Support\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Role-agnostic — the same two screens serve admin, agent and user logins,
 * since all three share one `users` table and the pending-login state
 * (set by HandlesTwoFactorLogin) only ever carries a user id + remember flag.
 */
class TwoFactorController extends Controller
{
    public function showEnroll(Request $request)
    {
        $pending = $request->session()->get('2fa_pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($pending['user_id']);

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('2fa.show');
        }

        if (!$user->two_factor_secret) {
            $user->update(['two_factor_secret' => Totp::generateSecret()]);
        }

        return view('auth.two-factor-enroll', [
            'secret' => $user->two_factor_secret,
            'qrUri'  => TwoFactor::otpAuthUri($user, $user->two_factor_secret),
        ]);
    }

    public function confirmEnroll(Request $request)
    {
        $pending = $request->session()->get('2fa_pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        $request->validate(['code' => 'required|digits:6']);

        $key = 'login.2fa.enroll.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = User::findOrFail($pending['user_id']);

        if (!$user->two_factor_secret || !Totp::verify($user->two_factor_secret, $request->code)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'code' => 'Incorrect code. Please check the app and try again.',
            ]);
        }

        RateLimiter::clear($key);
        $user->update(['two_factor_confirmed_at' => now()]);
        $request->session()->forget('2fa_pending');

        return TwoFactor::completeLogin($user, $pending['remember']);
    }

    public function showVerify(Request $request)
    {
        if (!$request->session()->has('2fa_pending')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-verify');
    }

    public function verify(Request $request)
    {
        $pending = $request->session()->get('2fa_pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        $request->validate(['code' => 'required|digits:6']);

        $key = 'login.2fa.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = User::findOrFail($pending['user_id']);

        if (!$user->two_factor_secret || !Totp::verify($user->two_factor_secret, $request->code)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'code' => 'The code is incorrect or has expired. Please try again.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->forget('2fa_pending');

        return TwoFactor::completeLogin($user, $pending['remember']);
    }
}
