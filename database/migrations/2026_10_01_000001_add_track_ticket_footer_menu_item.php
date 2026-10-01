<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Track Booking" link to the public footer's support column
 * (alongside Privacy Policy / Terms & Conditions), pointing at the public,
 * no-login lookup page — it accepts either a booking/invoice reference or a
 * support ticket number, but "booking" is what most visitors are there for.
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('menu_items')
            ->where('category', 'footer_support')
            ->where('url', '/track-ticket')
            ->exists();

        if (!$exists) {
            $maxSort = DB::table('menu_items')->where('category', 'footer_support')->max('sort_order') ?? 0;

            DB::table('menu_items')->insert([
                'name'        => 'Track Booking',
                'url'         => '/track-ticket',
                'category'    => 'footer_support',
                'sort_order'  => $maxSort + 1,
                'is_active'   => true,
                'icon'        => 'bi bi-life-preserver',
                'target'      => '_self',
                'description' => 'Track Booking',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menu_items')
            ->where('category', 'footer_support')
            ->where('url', '/track-ticket')
            ->delete();
    }
};
