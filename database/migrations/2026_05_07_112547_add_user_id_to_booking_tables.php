<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['hotels_booking', 'flights_booking', 'visa_requests', 'tours_booking', 'umrah_bookings'];

        foreach ($tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('agent_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        $tables = ['hotels_booking', 'flights_booking', 'visa_requests', 'tours_booking', 'umrah_bookings'];

        foreach ($tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropForeign(["{$tbl}_user_id_foreign"]);
                $table->dropColumn('user_id');
            });
        }
    }
};
