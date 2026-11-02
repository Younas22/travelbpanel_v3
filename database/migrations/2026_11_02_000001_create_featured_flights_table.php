<?php

use App\Support\FeaturedFlightDeals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Manually managed deals shown in "Featured Flight Deals" (home + /flights)
        Schema::create('featured_flights', function (Blueprint $table) {
            $table->id();
            $table->string('from_code', 3);
            $table->string('from_city', 100);
            $table->string('to_code', 3);
            $table->string('to_city', 100);
            $table->string('airline', 100);
            $table->string('airline_code', 3);
            $table->decimal('price', 10, 2)->comment('Starting price per adult, USD');
            $table->string('cabin', 20)->default('economy');
            $table->unsignedSmallInteger('days_ahead')->default(7)->comment('Departure date = today + N days');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Start from the deals that were hard-coded until now
        $now = now();
        DB::table('featured_flights')->insert(array_map(fn($d, $i) => [
            'from_code' => $d[0], 'from_city' => $d[1], 'to_code' => $d[2], 'to_city' => $d[3],
            'airline' => $d[4], 'price' => $d[5], 'airline_code' => $d[7],
            'cabin' => 'economy', 'days_ahead' => 7, 'sort_order' => $i + 1, 'status' => true,
            'created_at' => $now, 'updated_at' => $now,
        ], FeaturedFlightDeals::all(), array_keys(FeaturedFlightDeals::all())));

        // Manual supplier under Flight → Suppliers; switching it off hides the section on the site
        $flightModuleId = DB::table('modules')->where('slug', 'flight')->value('id');
        if ($flightModuleId && !DB::table('travel_partners')->where('company_name', 'Featured Flights')->exists()) {
            DB::table('travel_partners')->insert([
                'company_name'  => 'Featured Flights',
                'module_id'     => $flightModuleId,
                'supplier_type' => 'manual',
                'api_type'      => 'manual',
                'status'        => 'active',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('travel_partners')->where('company_name', 'Featured Flights')->where('supplier_type', 'manual')->delete();
        Schema::dropIfExists('featured_flights');
    }
};
