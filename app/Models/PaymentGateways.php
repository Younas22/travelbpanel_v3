<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateways extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'credential_1',
        'credential_2',
        'credential_3',
        'credential_4',
        'credential_5',
        'status',
        'mode',
    ];

    protected $casts = [
        'status' => 'string',
        'mode' => 'string',
    ];

    // Accessors to convert enum to boolean for easy use
    public function getStatusAttribute($value)
    {
        return $value === '1';
    }

    public function getModeAttribute($value)
    {
        return $value === '1';
    }

    // Mutators to convert boolean to enum for storage
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value ? '1' : '0';
    }

    public function setModeAttribute($value)
    {
        $this->attributes['mode'] = $value ? '1' : '0';
    }
}
