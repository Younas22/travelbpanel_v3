<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmrahInclusion extends Model
{
    use HasFactory;

    protected $table = 'umrah_inclusions';

    protected $fillable = [
        'agent_id',
        'name',
    ];
}
