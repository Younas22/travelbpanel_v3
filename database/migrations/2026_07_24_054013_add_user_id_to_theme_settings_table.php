<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A null user_id is the shared/global theme the Admin panel manages.
     * A non-null user_id is one agent's personal override — at most one
     * row per agent, found directly by user_id (is_active is meaningless
     * for these rows and only applies to the single global row).
     */
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
