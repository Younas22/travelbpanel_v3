<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * User::rejectAgent() has always set approval_status = 'rejected', and
 * getApprovalStatusBadgeAttribute() / the agents index view both already
 * handle a 'rejected' badge — but the enum itself never included it, so
 * every reject() call failed with a DB truncation error before the
 * rejection email could even be attempted.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY approval_status ENUM('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY approval_status ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending'");
    }
};
