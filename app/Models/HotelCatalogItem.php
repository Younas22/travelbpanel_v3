<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogTranslations;
use Illuminate\Database\Eloquent\Model;

/** Stay types, room type names, room amenities and meal plans (see `kind`). */
class HotelCatalogItem extends Model
{
    use HasCatalogTranslations;

    public const KINDS = ['stay_type', 'room_type', 'room_amenity', 'meal_plan'];

    protected $fillable = ['kind', 'name', 'icon', 'status', 'sort_order'];

    protected $casts = ['status' => 'boolean', 'sort_order' => 'integer'];

    public function scopeKind($query, string $kind)
    {
        return $query->where('kind', $kind)->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
