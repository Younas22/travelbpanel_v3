<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // WebBeds sandbox (Development mode) card entered in admin (Payment Details)
        Schema::table('travel_partners', function (Blueprint $table) {
            $table->text('sandbox_card')->nullable()->after('payment_card');
        });
    }

    public function down(): void
    {
        Schema::table('travel_partners', function (Blueprint $table) {
            $table->dropColumn('sandbox_card');
        });
    }
};
