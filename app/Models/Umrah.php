<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Umrah extends Model
{
    use HasFactory;

    protected $table = 'umrah';

    protected $fillable = [
        'agent_id',
        'added_by',
        'name',
        'packege_type',
        'currceny',
        'price',
        'duration',
        'loaction',
        'leaving_from',
        'going_to',
        'checkin_date',
        'checkout_date',
        'night_in_mekkah',
        'night_in_madina',
        'class',
        'desc',
        'inclusions',
        'exclusions',
        'policy',
        'featured',
        'status',
        'stars',
        'rating',
        'adults',
        'childs',
        'infants',
        'approval_status',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'inclusions' => 'array',
        'exclusions' => 'array',
    ];

    public function images()
    {
        return $this->hasMany(UmrahImage::class);
    }

    public function packageType()
    {
        return $this->belongsTo(UmrahPackageType::class, 'packege_type', 'packege_type');
    }
}
