<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Resend\Laravel\Facades\Resend;

/**
 * Self-service password reset for agent and user accounts. Admin accounts
 * are deliberately excluded — a matching email that belongs to an admin is
 * treated the same as no match at all, so this page can't be used to reset
 * (or even detect the existence of) an admin login. Admin password resets
 * stay a manual, out-of-band action.
 */
class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $key = 'password-reset.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }
        RateLimiter::hit($key, 300);

        $user = User::where('email', $request->email)
            ->whereIn('user_type', ['agent', 'user'])
            ->first();

        if ($user) {
            $this->sendResetLink($user);
        }

        // Same message whether or not the email matched, so this page can't
        // be used to probe which addresses have an account.
        return back()->with('success', 'If that email has an account, a password reset link has been sent.');
    }

    public function showReset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || now()->diffInMinutes($record->created_at) > 60 || !Hash::check($request->token, $record->token)) {
            throw ValidationException::withMessages([
                'email' => 'This password reset link is invalid or has expired. Please request a new one.',
            ]);
        }

        $user = User::where('email', $request->email)
            ->whereIn('user_type', ['agent', 'user'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'This password reset link is invalid or has expired. Please request a new one.',
            ]);
        }

        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset. Please log in.');
    }

    private function sendResetLink(User $user): void
    {
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($plainToken), 'created_at' => now()]
        );

        $resetUrl = url('/reset-password?' . http_build_query([
            'token' => $plainToken,
            'email' => $user->email,
        ]));

        try {
            $senderEmail  = getSetting('sender_email', 'email', 'contact@travelbookingpanel.com');
            $senderName   = getSetting('sender_name',  'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
            $businessLogo = getSettingImage('business_logo_white', 'branding');

            $logoHtml = $businessLogo
                ? "<img src='{$businessLogo}' alt='{$businessName}' style='max-height:40px;margin-bottom:10px;'><br>"
                : '';

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                <div style='background:#0077BE;padding:24px 32px;text-align:center;'>
                    {$logoHtml}
                    <h1 style='color:#fff;margin:0;font-size:22px;'>Reset Your Password</h1>
                </div>
                <div style='padding:32px;'>
                    <p style='font-size:15px;color:#333;'>Dear <strong>{$user->full_name}</strong>,</p>
                    <p style='font-size:15px;color:#333;'>We received a request to reset the password for your {$businessName} account.</p>
                    <div style='text-align:center;margin:32px 0;'>
                        <a href='{$resetUrl}' style='background:#0077BE;color:#fff;padding:12px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;'>Reset Password</a>
                    </div>
                    <p style='font-size:13px;color:#777;'>This link will expire in 60 minutes. If you did not request this, you can safely ignore this email — your password will not be changed.</p>
                </div>
                <div style='background:#f5f5f5;padding:16px 32px;text-align:center;'>
                    <p style='font-size:12px;color:#999;margin:0;'>© " . date('Y') . " {$businessName}. All rights reserved.</p>
                </div>
            </div>";

            $apiKey = Setting::getValue('resend_api_key', 'email');
            if ($apiKey) {
                config(['services.resend.key' => $apiKey]);
            }

            \Log::info('Password reset email sending', ['to' => $user->email]);

            Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$user->email],
                'subject' => "Reset Your Password – {$businessName}",
                'html'    => $html,
            ]);

            \Log::info('Password reset email sent successfully', ['to' => $user->email]);
        } catch (\Exception $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage(), ['to' => $user->email]);
        }
    }
}
