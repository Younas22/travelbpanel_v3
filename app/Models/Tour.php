<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

    protected $table = 'tours';

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
        'days',
        'nights',
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
        return $this->hasMany(TourImage::class);
    }

    public function packageType()
    {
        return $this->belongsTo(TourPackageType::class, 'packege_type', 'packege_type');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'loaction', 'id');
    }

    public function getLocationNameAttribute()
    {
        if ($this->loaction) {
            $location = \DB::table('locations')->find($this->loaction);
            return $location ? $location->city . ', ' . $location->country : 'Unknown Location';
        }
        return 'Unknown Location';
    }

    public function getHighlightsAttribute()
    {
        if ($this->inclusions && is_array($this->inclusions)) {
            $allInclusions = \DB::table('tour_inclusions')->pluck('name', 'id')->toArray();
            return collect($this->inclusions)->map(function ($id) use ($allInclusions) {
                return $allInclusions[$id] ?? null;
            })->filter()->values()->toArray();
        }
        return [];
    }
}
