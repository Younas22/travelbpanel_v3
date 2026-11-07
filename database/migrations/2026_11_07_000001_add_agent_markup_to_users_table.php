<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An agent's own fee: a fixed amount the agent adds on top of every flight
     * price (set by the agent on the dashboard or the flight list), stored in
     * the currency it was entered in and converted for display.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'agent_markup')) {
                $table->decimal('agent_markup', 12, 2)->default(0)->after('commission_type');
            }
            if (!Schema::hasColumn('users', 'agent_markup_currency')) {
                $table->string('agent_markup_currency', 10)->nullable()->after('agent_markup');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['agent_markup', 'agent_markup_currency']);
        });
    }
};
