<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Disable strict mode temporarily to handle legacy tables with zero-date defaults
        DB::statement("SET SESSION sql_mode = ''");

        $tables = ['hotels_booking', 'flights_booking', 'tours_booking', 'umrah_bookings', 'visa_requests'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('agent_id')->nullable()->after('id');
                $table->enum('booked_via', ['direct', 'agent'])->default('direct')->after('agent_id');
                $table->foreign('agent_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['hotels_booking', 'flights_booking', 'tours_booking', 'umrah_bookings', 'visa_requests'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign([$tableName . '_agent_id_foreign']);
                $table->dropColumn(['agent_id', 'booked_via']);
            });
        }
    }
};
