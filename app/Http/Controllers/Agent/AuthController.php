<?php

namespace App\Http\Controllers\Agent;

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
    public function showLogin()
    {
        if (auth()->check() && auth()->user()->isAgent()) {
            return redirect()->route('agent.dashboard');
        }
        return view('agent.auth.login');
    }

    public function showRegister()
    {
        if (auth()->check() && auth()->user()->isAgent()) {
            return redirect()->route('agent.dashboard');
        }
        return view('agent.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:200',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'company_name'    => $request->company_name,
            'password'        => Hash::make($request->password),
            'user_type'       => 'agent',
            'agent_code'      => User::generateAgentCode(),
            'approval_status' => 'pending',
        ]);

        // Create wallet
        $user->wallet()->create(['agent_id' => $user->id, 'balance' => 0]);

        $this->sendWelcomeEmail($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('agent.pending');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $key = 'agent-login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.']);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password, 'user_type' => 'agent'], $request->remember)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'Invalid credentials or not an agent account.'])->withInput();
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->route('agent.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function sendWelcomeEmail(User $agent): void
    {
        try {
            $senderEmail  = getSetting('sender_email', 'email', 'contact@travelbookingpanel.com');
            $senderName   = getSetting('sender_name',  'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
            $loginUrl     = url('/agent/login');

            $html = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
                <div style='background:#0077BE;padding:24px 32px;text-align:center;'>
                    <h1 style='color:#fff;margin:0;font-size:22px;'>Welcome to {$businessName}</h1>
                </div>
                <div style='padding:32px;'>
                    <p style='font-size:15px;color:#333;'>Dear <strong>{$agent->full_name}</strong>,</p>
                    <p style='font-size:15px;color:#333;'>Thank you for registering as an agent with <strong>{$businessName}</strong>.</p>
                    <p style='font-size:15px;color:#333;'>Your agent code is: <strong style='font-size:18px;color:#0077BE;'>{$agent->agent_code}</strong></p>
                    <p style='font-size:15px;color:#333;'>Your account is currently <strong style='color:#e67e22;'>pending approval</strong>. We'll notify you by email as soon as it has been reviewed.</p>
                    <div style='text-align:center;margin:32px 0;'>
                        <a href='{$loginUrl}' style='background:#0077BE;color:#fff;padding:12px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;'>Go to Agent Portal</a>
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

            \Log::info('Agent welcome email sending', [
                'from' => $senderEmail . ' (' . $senderName . ')',
                'to'   => $agent->email . ' (' . $agent->full_name . ')',
            ]);

            Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$agent->email],
                'subject' => "Welcome to {$businessName}!",
                'html'    => $html,
            ]);

            \Log::info('Agent welcome email sent successfully', [
                'from' => $senderEmail,
                'to'   => $agent->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Agent welcome email failed: ' . $e->getMessage(), [
                'from' => $senderEmail ?? 'unknown',
                'to'   => $agent->email,
            ]);
        }
    }
}
