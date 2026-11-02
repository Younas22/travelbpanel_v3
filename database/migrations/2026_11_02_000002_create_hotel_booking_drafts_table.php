<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saved progress of the three-step hotel checkout (guest, travellers, payment),
 * so a customer who refreshes, closes the tab or comes back later resumes the
 * same booking on the step they left. A draft belongs to the signed-in user, or
 * to a guest through a random key kept in an encrypted cookie. The selected
 * hotel/room/price snapshot and the personal details are stored encrypted.
 * Card details are never stored here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_booking_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('guest_key', 64)->nullable()->index();
            $table->string('fingerprint', 64)->index();
            $table->string('supplier_name', 50)->nullable();
            $table->string('hotel_id', 100)->nullable();
            $table->string('room_id', 100)->nullable();
            $table->date('checkin')->nullable();
            $table->date('checkout')->nullable();
            $table->unsignedSmallInteger('adults')->default(0);
            $table->unsignedSmallInteger('children')->default(0);
            $table->unsignedSmallInteger('rooms')->default(1);
            $table->longText('payload');
            $table->text('guest_data')->nullable();
            $table->text('travellers')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->json('completed_steps')->nullable();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->unsignedBigInteger('hotel_booking_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_booking_drafts');
    }
};
