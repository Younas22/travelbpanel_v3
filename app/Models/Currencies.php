<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currencies extends Model
{
    protected $table = 'currencies';


    protected $fillable = [
        'currency_name',
        'currency_country',
        'currency_rate',
        'currency_status',
        'currency_default'
    ];

    public $timestamps = false; // if no created_at / updated_at

    protected $casts = [
        'currency_default' => 'boolean',
    ];
}
