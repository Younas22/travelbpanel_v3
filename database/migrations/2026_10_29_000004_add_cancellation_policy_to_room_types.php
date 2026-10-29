<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refund / cancellation policy moves from the hotel to each room type.
 * Refundable: free cancellation until `free_cancel_days` before check-in, at `free_cancel_time`;
 * after that the cancellation fee applies. Non-refundable: the cancellation fee always applies.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->string('cancellation_type', 20)->default('refundable')->after('ac');
            $table->unsignedSmallInteger('free_cancel_days')->nullable()->after('cancellation_type');
            $table->time('free_cancel_time')->nullable()->after('free_cancel_days');
            $table->decimal('cancellation_fee', 10, 2)->default(0)->after('free_cancel_time');
            $table->string('cancellation_fee_type', 10)->default('percent')->after('cancellation_fee');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['cancellation_type', 'free_cancel_days', 'free_cancel_time', 'cancellation_fee', 'cancellation_fee_type']);
        });
    }
};
