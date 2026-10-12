<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records which currency each wallet transaction was actually entered in.
 * Admin's Add Balance form now lets them pick any active currency instead
 * of assuming the wallet's fixed PKR — the wallet's own `currency` column
 * is relabeled to whatever was just picked, but old transaction rows must
 * keep remembering their own original currency so the history list doesn't
 * get silently relabeled too.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_wallet_transactions', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('agent_wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
