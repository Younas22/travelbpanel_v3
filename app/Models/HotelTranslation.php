<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelTranslation extends Model
{
    public const FIELDS = ['name', 'address', 'description', 'cancellation_policy', 'privacy_policy'];

    protected $fillable = ['hotel_id', 'locale', 'name', 'address', 'description', 'cancellation_policy', 'privacy_policy'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
