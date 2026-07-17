<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPackageType extends Model
{
    use HasFactory;

    protected $table = 'tour_package_type';

    protected $fillable = [
        'agent_id',
        'packege_type',
        'status',
    ];

    public function packages()
    {
        return $this->hasMany(Tour::class, 'packege_type', 'packege_type');
    }
}
