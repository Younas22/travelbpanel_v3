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
        Schema::table('travel_partners', function (Blueprint $table) {
            $table->string('commission_type')->default('percentage')->after('commission_rate');
            $table->string('discount_type')->default('percentage')->after('discount_rate');
            $table->string('b2b_markup_type')->default('percentage')->after('b2b_markup');
            $table->string('b2c_markup_type')->default('percentage')->after('b2c_markup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_partners', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'discount_type', 'b2b_markup_type', 'b2c_markup_type']);
        });
    }
};
