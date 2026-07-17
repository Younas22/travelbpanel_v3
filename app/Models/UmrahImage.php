<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmrahImage extends Model
{
    use HasFactory;

    protected $table = 'umrah_images';

    protected $fillable = [
        'umrah_id',
        'image',
    ];

    public function umrah()
    {
        return $this->belongsTo(Umrah::class);
    }
}
