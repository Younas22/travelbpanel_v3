<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Shared by every login controller (Admin, Agent) right after credentials
 * check out, so each one's own post-login steps (approval checks,
 * redirectByRole, etc.) stay in TwoFactor::completeLogin() rather than
 * duplicated per controller.
 */
trait HandlesTwoFactorLogin
{
    protected function proceedAfterPassword(Request $request, User $user, bool $remember): RedirectResponse
    {
        if (!TwoFactor::requiredForUser($user)) {
            return TwoFactor::completeLogin($user, $remember);
        }

        $request->session()->put('2fa_pending', [
            'user_id'  => $user->id,
            'remember' => $remember,
        ]);

        return redirect()->route($user->two_factor_confirmed_at ? '2fa.show' : '2fa.enroll');
    }
}
