<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeAmenity extends Model
{
    use HasFactory;

    protected $table = 'room_type_amenities';

    protected $fillable = [
        'room_type_id',
        'amenity_id',
    ];

    protected $casts = [
        'room_type_id' => 'integer',
        'amenity_id' => 'integer',
    ];

    public $timestamps = false;

    // Relationships
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function amenity()
    {
        return $this->belongsTo(AllAmenity::class, 'amenity_id');
    }
}
