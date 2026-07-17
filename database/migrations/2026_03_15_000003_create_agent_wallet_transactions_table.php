<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference', 100)->nullable();
            $table->string('booking_type', 50)->nullable(); // hotel/flight/tour/umrah/visa
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->text('note')->nullable();
            $table->string('payment_method', 100)->nullable(); // for credits only
            $table->unsignedBigInteger('performed_by'); // admin or agent user id
            $table->enum('status', ['completed', 'pending', 'reversed'])->default('completed');
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users');
            $table->index(['agent_id', 'type']);
            $table->index(['agent_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_wallet_transactions');
    }
};
