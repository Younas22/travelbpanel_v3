<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A manually managed "Featured Flight Deals" card (home + /flights). */
class FeaturedFlight extends Model
{
    public const CABINS = ['economy' => 'Economy', 'premium_economy' => 'Premium Economy', 'business' => 'Business', 'first' => 'First'];

    /** Supplier row (Flight → Suppliers) whose switch turns the section on/off. */
    public const PARTNER_NAME = 'Featured Flights';

    protected $fillable = ['from_code', 'from_city', 'to_code', 'to_city', 'airline', 'airline_code', 'price', 'cabin', 'days_ahead', 'sort_order', 'status'];

    protected $casts = ['price' => 'decimal:2', 'days_ahead' => 'integer', 'sort_order' => 'integer', 'status' => 'boolean'];

    /** True when the Flight module and the Featured Flights supplier are both switched on. */
    public static function enabled(): bool
    {
        return (bool) getActiveModule('flight')
            && TravelPartner::where('company_name', self::PARTNER_NAME)->where('supplier_type', 'manual')->where('status', 'active')->exists();
    }

    /** Active deals for the site, or none when the section is switched off. */
    public static function forSite(?int $limit = null)
    {
        if (!self::enabled()) {
            return collect();
        }

        return self::where('status', true)->orderBy('sort_order')->orderBy('id')->when($limit, fn($q) => $q->limit($limit))->get();
    }
}
