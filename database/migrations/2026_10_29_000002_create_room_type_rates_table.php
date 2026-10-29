<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rate calendar (Hotels → Rate calendar): a nightly price per room type per date.
 * Dates without a row use the room type's own price_per_night.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_type_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_type_id');
            $table->date('date');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['room_type_id', 'date']);
            $table->foreign('room_type_id')->references('id')->on('room_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_type_rates');
    }
};
