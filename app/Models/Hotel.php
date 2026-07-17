<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $table = 'hotels';

    protected $fillable = [
        'agent_id',
        'added_by',
        'location_id',
        'name',
        'type',
        'description',
        'address',
        'phone',
        'whatsapp',
        'email',
        'check_in_time',
        'check_out_time',
        'total_rooms',
        'stars',
        'total_rating',
        'status',
        'featured',
        'approval_status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'total_rooms' => 'integer',
        'location_id' => 'integer',
        'stars' => 'integer',
        'total_rating' => 'integer',
    ];

    public $timestamps = true;

    // Relationships
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function images()
    {
        return $this->hasMany(HotelImage::class)->orderBy('sort_order');
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(AllAmenity::class, 'hotel_amenities', 'hotel_id', 'amenity_id');
    }

    public function policies()
    {
        return $this->hasMany(HotelPolicy::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    // Accessors
    public function getAvailableRoomsCountAttribute()
    {
        return $this->rooms()->where('status', 'available')->count();
    }
}
