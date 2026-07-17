<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelImage extends Model
{
    use HasFactory;

    protected $table = 'hotel_images';

    protected $fillable = [
        'hotel_id',
        'image_path',
        'image_type',
        'sort_order',
    ];

    protected $casts = [
        'hotel_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public $timestamps = false;

    const UPDATED_AT = null;

    // Relationships
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('image_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
