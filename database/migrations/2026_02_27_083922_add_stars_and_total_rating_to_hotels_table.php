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
        Schema::table('hotels', function (Blueprint $table) {
            $table->tinyInteger('stars')->unsigned()->nullable()->default(null)->after('total_rooms');
            $table->decimal('total_rating', 3, 1)->nullable()->default(null)->after('stars');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['stars', 'total_rating']);
        });
    }
};
