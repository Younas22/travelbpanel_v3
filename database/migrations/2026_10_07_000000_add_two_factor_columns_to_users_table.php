<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user TOTP secret for the Google Authenticator login step. The secret
 * is generated the first time 2FA applies to that account and shown once as
 * a QR code; two_factor_confirmed_at records that the user actually scanned
 * it and entered a valid code, so later logins go straight to the code
 * prompt instead of re-showing the QR.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret', 64)->nullable()->after('remember_token');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_confirmed_at']);
        });
    }
};
