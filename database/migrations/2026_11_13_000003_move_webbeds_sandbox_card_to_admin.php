<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The WebBeds sandbox card is no longer hard-coded in WebbedsController: the card that was there
     * becomes WebBeds' saved sandbox card (editable in admin → Suppliers → WebBeds → Payment Details).
     */
    public function up(): void
    {
        DB::table('travel_partners')
            ->whereRaw('LOWER(TRIM(company_name)) = ?', ['webbeds'])
            ->whereNull('sandbox_card')
            ->update(['sandbox_card' => Crypt::encryptString(json_encode([
                'cardName'     => 'Usama Malik',
                'cardNumber'   => '4444333322221111',
                'expiryMonth'  => '12',
                'expiryYear'   => '2026',
                'securityCode' => '123',
            ]))]);
    }

    public function down(): void
    {
        // Leaves the saved card in place: it is admin data now.
    }
};
