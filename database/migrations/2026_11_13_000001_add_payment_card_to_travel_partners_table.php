<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // WebBeds live bookings are paid by card (Rezpayments): stored encrypted, see TravelPartner::paymentCard()
        Schema::table('travel_partners', function (Blueprint $table) {
            $table->text('payment_card')->nullable()->after('api_credential_6');
        });
    }

    public function down(): void
    {
        Schema::table('travel_partners', function (Blueprint $table) {
            $table->dropColumn('payment_card');
        });
    }
};
