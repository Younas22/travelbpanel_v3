<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'room_types';

    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'price_per_night',
        'beds',
        'max_adults',
        'max_children',
        'ac',
        'status',
    ];

    protected $casts = [
        'hotel_id' => 'integer',
        'price_per_night' => 'decimal:2',
        'beds' => 'integer',
        'max_adults' => 'integer',
        'max_children' => 'integer',
        'ac' => 'boolean',
        'status' => 'boolean',
    ];

    public $timestamps = true;

    // Relationships
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function images()
    {
        return $this->hasMany(RoomTypeImage::class)->orderBy('sort_order');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(AllAmenity::class, 'room_type_amenities', 'room_type_id', 'amenity_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeWithAC($query)
    {
        return $query->where('ac', 1);
    }

    public function scopeByCapacity($query, $adults, $children = 0)
    {
        return $query->where('max_adults', '>=', $adults)
                     ->where('max_children', '>=', $children);
    }

    // Accessors
    public function getAvailableRoomsAttribute()
    {
        return $this->rooms()->where('status', 'available')->get();
    }

    public function getAvailableRoomsCountAttribute()
    {
        return $this->rooms()->where('status', 'available')->count();
    }
}
