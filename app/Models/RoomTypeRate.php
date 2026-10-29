<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One night's prices for a room type on a given date (Rate calendar); either price may be null = use the room type's base. */
class RoomTypeRate extends Model
{
    protected $fillable = ['room_type_id', 'date', 'price', 'non_refundable_price'];

    protected $casts = ['date' => 'date:Y-m-d', 'price' => 'decimal:2', 'non_refundable_price' => 'decimal:2'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
