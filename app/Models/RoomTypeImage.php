<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeImage extends Model
{
    use HasFactory;

    protected $table = 'room_type_images';

    protected $fillable = [
        'room_type_id',
        'image_path',
        'sort_order',
    ];

    protected $casts = [
        'room_type_id' => 'integer',
    ];

    public $timestamps = false;

    const UPDATED_AT = null;

    // Relationships
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
