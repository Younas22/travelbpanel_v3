<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmrahPackageType extends Model
{
    use HasFactory;

    protected $table = 'umrah_package_type';

    protected $fillable = [
        'agent_id',
        'packege_type',
        'status',
    ];

    public function packages()
    {
        return $this->hasMany(Umrah::class, 'packege_type', 'packege_type');
    }
}
