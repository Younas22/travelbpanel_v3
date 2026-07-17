<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->unique();
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->string('currency', 10)->default('PKR');
            $table->decimal('total_credited', 12, 2)->default(0.00);
            $table->decimal('total_debited', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_wallets');
    }
};
