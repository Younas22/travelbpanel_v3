<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, set all existing string values to NULL or a default value
        DB::table('tours')->update(['loaction' => null]);

        Schema::table('tours', function (Blueprint $table) {
            // Change loaction column from string to unsigned big integer
            $table->unsignedBigInteger('loaction')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            // Revert loaction column back to string
            $table->string('loaction')->nullable()->change();
        });
    }
};
