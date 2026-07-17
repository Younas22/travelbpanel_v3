<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmrahExclusion extends Model
{
    use HasFactory;

    protected $table = 'umrah_exclusions';

    protected $fillable = [
        'agent_id',
        'name',
    ];
}
