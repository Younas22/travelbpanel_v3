<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each room type gets a second nightly rate: the non-refundable price.
 * room_types.non_refundable_price is its base; room_type_rates can override it per date
 * (price and non_refundable_price are each optional on a date row).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('non_refundable_price', 10, 2)->nullable()->after('price_per_night');
        });
        Schema::table('room_type_rates', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->decimal('non_refundable_price', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('room_type_rates', function (Blueprint $table) {
            $table->dropColumn('non_refundable_price');
        });
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('non_refundable_price');
        });
    }
};
