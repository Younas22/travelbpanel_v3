<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'room_number',
        'floor',
        'status',
    ];

    protected $casts = [
        'hotel_id' => 'integer',
        'room_type_id' => 'integer',
    ];

    public $timestamps = true;

    // Relationships
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeByFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Accessors
    public function getIsAvailableAttribute()
    {
        return $this->status === 'available';
    }

    public function getIsOccupiedAttribute()
    {
        return $this->status === 'occupied';
    }
}
