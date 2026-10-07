<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Per-role Google Authenticator gate, switched on independently for admin,
 * agent and user accounts from Settings > Security. Each account gets its
 * own TOTP secret (generated on first login once required for its role),
 * shown once as a QR code; two_factor_confirmed_at then skips straight to
 * the 6-digit prompt on every later login.
 */
class TwoFactor
{
    public static function requiredForUser(User $user): bool
    {
        return Setting::getValue(self::settingKeyFor($user), 'security') === '1';
    }

    public static function settingKeyFor(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin_2fa_enabled';
        }

        if ($user->isAgent()) {
            return 'agent_2fa_enabled';
        }

        return 'user_2fa_enabled';
    }

    public static function completeLogin(User $user, bool $remember): RedirectResponse
    {
        Auth::login($user, $remember);
        request()->session()->regenerate();
        $user->update(['last_activity' => now()]);

        return self::redirectForRole($user);
    }

    public static function redirectForRole(User $user): RedirectResponse
    {
        if ($user->isAgent()) {
            if ($user->approval_status === 'pending') {
                return redirect()->route('agent.pending');
            }
            if ($user->approval_status === 'suspended') {
                return redirect()->route('agent.suspended');
            }
        }

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard.index'));
        }
        if ($user->isAgent()) {
            return redirect()->intended(route('agent.dashboard'));
        }

        return redirect()->intended(route('user.dashboard'));
    }

    public static function otpAuthUri(User $user, string $secret): string
    {
        $issuer = getSetting('business_name', 'main', 'TravelBookingPanel');
        $label  = rawurlencode($issuer . ':' . $user->email);

        return 'otpauth://totp/' . $label
            . '?secret=' . $secret
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }
}
