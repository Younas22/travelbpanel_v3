<?php

namespace App\Support;

use App\Models\Hotel;
use App\Models\HotelCatalogItem;

/**
 * Stay Types (Hotels → Stay Types) as used by the hotel Type dropdown and validation.
 * Hotels keep the lower-cased type name in their own `type` column, so renaming a
 * Stay Type also renames it on the hotels that use it.
 */
class HotelTypes
{
    public const DEFAULTS = ['hotel', 'guest house', 'resort'];

    /** @return array<string,string> value => label, e.g. ['guest house' => 'Guest House'] */
    public static function options(): array
    {
        return collect(self::all())->mapWithKeys(fn ($t) => [$t => ucwords($t)])->all();
    }

    /** @return string[] active Stay Types (Hotels → Stay Types), lower-cased as stored on hotels.type */
    public static function all(): array
    {
        $names = HotelCatalogItem::kind('stay_type')->active()->pluck('name')->map(fn ($n) => self::normalize($n))->unique()->values()->all();

        return $names ?: self::DEFAULTS;
    }

    public static function normalize(?string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $name)));
    }

    public static function usage(string $type): int
    {
        return Hotel::where('type', $type)->count();
    }
}
