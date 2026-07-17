<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Resend\Laravel\Facades\Resend;

class AuthController extends Controller
{
    public function showRegister()
    {
        if (auth()->check() && auth()->user()->isCustomer()) {
            return redirect()->route('home');
        }
        return view('user.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'user_type'  => 'user',
        ]);

        $this->sendWelcomeEmail($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function sendWelcomeEmail(User $user): void
    {
        try {
            $senderEmail  = getSetting('sender_email', 'email', 'contact@travelbookingpanel.com');
            $senderName   = getSetting('sender_name',  'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
            $loginUrl     = url('/user/login');

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                <div style='background:#0077BE;padding:24px 32px;text-align:center;'>
                    <h1 style='color:#fff;margin:0;font-size:22px;'>Welcome to {$businessName}</h1>
                </div>
                <div style='padding:32px;'>
                    <p style='font-size:15px;color:#333;'>Dear <strong>{$user->full_name}</strong>,</p>
                    <p style='font-size:15px;color:#333;'>Thank you for creating an account with <strong>{$businessName}</strong>. We're excited to have you on board!</p>
                    <p style='font-size:15px;color:#333;'>You can now log in to your account and start exploring our services.</p>
                    <div style='text-align:center;margin:32px 0;'>
                        <a href='{$loginUrl}' style='background:#0077BE;color:#fff;padding:12px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;'>Login to Your Account</a>
                    </div>
                    <p style='font-size:13px;color:#777;'>If you have any questions, please contact our support team.</p>
                </div>
                <div style='background:#f5f5f5;padding:16px 32px;text-align:center;'>
                    <p style='font-size:12px;color:#999;margin:0;'>© " . date('Y') . " {$businessName}. All rights reserved.</p>
                </div>
            </div>";

            $apiKey = Setting::getValue('resend_api_key', 'email');
            if ($apiKey) {
                config(['services.resend.key' => $apiKey]);
            }

            \Log::info('User welcome email sending', [
                'from' => $senderEmail . ' (' . $senderName . ')',
                'to'   => $user->email . ' (' . $user->full_name . ')',
            ]);

            Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$user->email],
                'subject' => "Welcome to {$businessName}!",
                'html'    => $html,
            ]);

            \Log::info('User welcome email sent successfully', [
                'from' => $senderEmail,
                'to'   => $user->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('User welcome email failed: ' . $e->getMessage(), [
                'from' => $senderEmail ?? 'unknown',
                'to'   => $user->email,
            ]);
        }
    }
}
