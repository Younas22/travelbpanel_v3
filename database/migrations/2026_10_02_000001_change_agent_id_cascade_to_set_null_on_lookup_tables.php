<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 2026_03_16_200000_add_agent_id_to_lookup_tables gave these 7 shared lookup
 * tables an agent_id with ON DELETE CASCADE. They're a shared/global catalog
 * agents contribute entries to (e.g. "WiFi" in all_amenities), not private
 * per-agent data — other agents' hotels already reference the same rows via
 * hotel_amenities/room_type_amenities (no cascade on that FK). So deleting
 * an agent cascaded into trying to delete an all_amenities row still in use
 * elsewhere, and MySQL rejected the whole user delete with a 1451 FK error.
 * SET NULL keeps the shared rows intact and just clears their "added by"
 * attribution when that agent is removed.
 */
return new class extends Migration
{
    private array $tables = [
        'all_amenities',
        'umrah_package_type',
        'umrah_inclusions',
        'umrah_exclusions',
        'tour_package_type',
        'tour_inclusions',
        'tour_exclusions',
    ];

    public function up(): void
    {
        // Several of these tables carry a legacy invalid default on
        // updated_at that strict mode rejects on any ALTER TABLE, even one
        // untouching that column — the original migration that added
        // agent_id hit the same thing and worked around it the same way.
        DB::statement('SET SESSION sql_mode = ""');

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign("{$table}_agent_id_foreign");
                $blueprint->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign("{$table}_agent_id_foreign");
                $blueprint->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};
