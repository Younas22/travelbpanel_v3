<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ── Agent-specific fields ──────────────────────────────────────
            $table->string('company_name')->nullable()->after('last_name');
            $table->string('agent_code', 20)->unique()->nullable()->after('company_name');
            $table->enum('approval_status', ['pending', 'active', 'suspended'])->default('pending')->after('agent_code');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->text('company_address')->nullable()->after('rejection_reason');
            $table->string('company_phone', 30)->nullable()->after('company_address');
            $table->string('company_logo')->nullable()->after('company_phone');
            $table->string('cnic_or_reg_number', 100)->nullable()->after('company_logo');

            // ── Agent ke customers ka linkage ─────────────────────────────
            $table->unsignedBigInteger('parent_agent_id')->nullable()->after('cnic_or_reg_number');

            // ── Foreign Keys ───────────────────────────────────────────────
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('parent_agent_id')->references('id')->on('users')->nullOnDelete();

            // ── Indexes ────────────────────────────────────────────────────
            $table->index('parent_agent_id');
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['parent_agent_id']);
            $table->dropIndex(['parent_agent_id']);
            $table->dropIndex(['approval_status']);
            $table->dropColumn([
                'company_name', 'agent_code', 'approval_status',
                'approved_by', 'approved_at', 'rejection_reason',
                'company_address', 'company_phone', 'company_logo',
                'cnic_or_reg_number', 'parent_agent_id',
            ]);
        });
    }
};
