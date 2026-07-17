<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelPolicy extends Model
{
    use HasFactory;

    protected $table = 'hotel_policies';

    protected $fillable = [
        'hotel_id',
        'policy_type',
        'description',
    ];

    protected $casts = [
        'hotel_id' => 'integer',
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
        return $query->where('policy_type', $type);
    }

    public function scopeCancellation($query)
    {
        return $query->where('policy_type', 'cancellation');
    }

    public function scopeSmoking($query)
    {
        return $query->where('policy_type', 'smoking');
    }

    public function scopeFamily($query)
    {
        return $query->where('policy_type', 'family');
    }
}
