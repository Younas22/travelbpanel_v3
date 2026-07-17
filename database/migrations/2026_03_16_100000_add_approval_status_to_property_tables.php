<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved')->after('added_by');
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved')->after('added_by');
        });

        Schema::table('umrah', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved')->after('added_by');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });

        Schema::table('umrah', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
