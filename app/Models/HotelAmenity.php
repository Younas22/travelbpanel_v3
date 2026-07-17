<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelAmenity extends Model
{
    use HasFactory;

    protected $table = 'hotel_amenities';

    protected $fillable = [
        'hotel_id',
        'amenity_id',
    ];

    protected $casts = [
        'hotel_id' => 'integer',
        'amenity_id' => 'integer',
    ];

    public $timestamps = false;

    // Relationships
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function amenity()
    {
        return $this->belongsTo(AllAmenity::class, 'amenity_id');
    }
}
