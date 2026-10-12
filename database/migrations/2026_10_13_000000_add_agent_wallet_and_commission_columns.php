<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two unrelated additions bundled into one migration since both just add
 * nullable columns with no data backfill needed:
 *
 * - agent_wallet_transactions.seen_by_agent: lets the agent dashboard show
 *   a one-time banner when admin zeroes out a wallet (with the reason),
 *   then mark it acknowledged so it doesn't keep reappearing.
 * - users.commission_type: the agent create/edit form now lets admin pick
 *   whether commission_rate is a percentage of the fare or a flat amount
 *   per booking — defaults to 'percentage' so existing agent rows keep
 *   their current meaning unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_wallet_transactions', function (Blueprint $table) {
            $table->timestamp('seen_by_agent')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('commission_type', ['fixed', 'percentage'])->default('percentage')->after('commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('agent_wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('seen_by_agent');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('commission_type');
        });
    }
};
