<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET SESSION sql_mode = ""');

        $tables = [
            'umrah_package_type',
            'umrah_inclusions',
            'umrah_exclusions',
            'tour_package_type',
            'tour_inclusions',
            'tour_exclusions',
            'all_amenities',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('agent_id')->nullable()->after('id');
                $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'umrah_package_type',
            'umrah_inclusions',
            'umrah_exclusions',
            'tour_package_type',
            'tour_inclusions',
            'tour_exclusions',
            'all_amenities',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['agent_id']);
                $table->dropColumn('agent_id');
            });
        }
    }
};
