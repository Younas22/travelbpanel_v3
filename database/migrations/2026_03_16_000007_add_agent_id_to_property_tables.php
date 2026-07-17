<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hotels
        Schema::table('hotels', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('id');
            $table->string('added_by', 20)->default('admin')->after('agent_id'); // admin | agent
            $table->foreign('agent_id')->references('id')->on('users')->nullOnDelete();
            $table->index('agent_id');
        });

        // Tours
        Schema::table('tours', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('id');
            $table->string('added_by', 20)->default('admin')->after('agent_id');
            $table->foreign('agent_id')->references('id')->on('users')->nullOnDelete();
            $table->index('agent_id');
        });

        // Umrah
        Schema::table('umrah', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('id');
            $table->string('added_by', 20)->default('admin')->after('agent_id');
            $table->foreign('agent_id')->references('id')->on('users')->nullOnDelete();
            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropIndex(['agent_id']);
            $table->dropColumn(['agent_id', 'added_by']);
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropIndex(['agent_id']);
            $table->dropColumn(['agent_id', 'added_by']);
        });

        Schema::table('umrah', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropIndex(['agent_id']);
            $table->dropColumn(['agent_id', 'added_by']);
        });
    }
};
