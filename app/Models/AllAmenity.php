<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllAmenity extends Model
{
    use HasFactory;

    protected $table = 'all_amenities';

    protected $fillable = [
        'agent_id',
        'name',
        'icon',
    ];

    public $timestamps = false;

    const UPDATED_AT = null;

    // Relationships
    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'hotel_amenities', 'amenity_id', 'hotel_id');
    }

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'room_type_amenities', 'amenity_id', 'room_type_id');
    }
}
