<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("SET SESSION sql_mode = ''");

        $tables = ['hotels_booking', 'flights_booking', 'tours_booking', 'umrah_bookings', 'visa_requests'];

        foreach ($tables as $table) {
            DB::statement("UPDATE `$table` SET `booked_via` = 'guest' WHERE `booked_via` = 'direct'");
            DB::statement("ALTER TABLE `$table` MODIFY COLUMN `booked_via` ENUM('guest', 'user', 'agent') NOT NULL DEFAULT 'guest'");
        }
    }

    public function down(): void
    {
        DB::statement("SET SESSION sql_mode = ''");

        $tables = ['hotels_booking', 'flights_booking', 'tours_booking', 'umrah_bookings', 'visa_requests'];

        foreach ($tables as $table) {
            DB::statement("UPDATE `$table` SET `booked_via` = 'direct' WHERE `booked_via` IN ('guest', 'user')");
            DB::statement("ALTER TABLE `$table` MODIFY COLUMN `booked_via` ENUM('direct', 'agent') NOT NULL DEFAULT 'direct'");
        }
    }
};
