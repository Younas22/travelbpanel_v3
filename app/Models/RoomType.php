<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'room_types';

    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'price_per_night',
        'non_refundable_price',
        'beds',
        'max_adults',
        'max_children',
        'ac',
        'status',
        'cancellation_type',
        'free_cancel_days',
        'free_cancel_time',
        'cancellation_fee',
        'cancellation_fee_type',
    ];

    protected $casts = [
        'hotel_id' => 'integer',
        'price_per_night' => 'decimal:2',
        'non_refundable_price' => 'decimal:2',
        'beds' => 'integer',
        'max_adults' => 'integer',
        'max_children' => 'integer',
        'ac' => 'boolean',
        'status' => 'boolean',
        'free_cancel_days' => 'integer',
        'cancellation_fee' => 'decimal:2',
    ];

    public $timestamps = true;

    // Relationships
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function images()
    {
        return $this->hasMany(RoomTypeImage::class)->orderBy('sort_order');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(AllAmenity::class, 'room_type_amenities', 'room_type_id', 'amenity_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeWithAC($query)
    {
        return $query->where('ac', 1);
    }

    public function scopeByCapacity($query, $adults, $children = 0)
    {
        return $query->where('max_adults', '>=', $adults)
                     ->where('max_children', '>=', $children);
    }

    // Accessors
    public function getAvailableRoomsAttribute()
    {
        return $this->rooms()->where('status', 'available')->get();
    }

    public function getAvailableRoomsCountAttribute()
    {
        return $this->rooms()->where('status', 'available')->count();
    }

    /** Room Amenities (Hotels → Room Amenities list). */
    public function roomAmenities()
    {
        return $this->belongsToMany(HotelCatalogItem::class, 'room_type_room_amenity', 'room_type_id', 'room_amenity_id');
    }

    /** Date-specific nightly prices (Rate calendar). */
    public function rates()
    {
        return $this->hasMany(RoomTypeRate::class);
    }

    /** Nightly price on a date: the rate-calendar price if set, otherwise price_per_night. */
    public function priceOn($date): float
    {
        $rate = $this->rates()->whereDate('date', \Illuminate\Support\Carbon::parse($date)->toDateString())->value('price');

        return (float) ($rate ?? $this->price_per_night);
    }

    /** Non-refundable nightly price on a date: calendar price, else the base non-refundable price (null = not offered). */
    public function nonRefundablePriceOn($date): ?float
    {
        $rate = $this->rates()->whereDate('date', \Illuminate\Support\Carbon::parse($date)->toDateString())->value('non_refundable_price');
        $price = $rate ?? $this->non_refundable_price;

        return $price === null ? null : (float) $price;
    }

    public function isRefundable(): bool
    {
        return $this->cancellation_type !== 'non_refundable';
    }

    /** Last moment a booking checking in on $checkin can be cancelled for free (null when non-refundable). */
    public function freeCancellationUntil($checkin): ?\Illuminate\Support\Carbon
    {
        if (!$this->isRefundable()) {
            return null;
        }
        [$h, $m] = array_map('intval', explode(':', substr($this->free_cancel_time ?: '12:00', 0, 5)));

        return \Illuminate\Support\Carbon::parse($checkin)->startOfDay()->subDays((int) $this->free_cancel_days)->setTime($h, $m);
    }

    /** Cancellation fee for a booking of $total: a percentage of it, or a fixed USD amount (never more than the total). */
    public function cancellationFeeFor(float $total): float
    {
        $fee = $this->cancellation_fee_type === 'fixed'
            ? (float) $this->cancellation_fee
            : $total * (float) $this->cancellation_fee / 100;

        return round(min($fee, $total), 2);
    }
}
