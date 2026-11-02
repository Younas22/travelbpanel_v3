<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Saved progress of the hotel checkout (see the hotel_booking_drafts migration).
 */
class HotelBookingDraft extends Model
{
    /** Name of the encrypted cookie that ties guest drafts to this browser. */
    public const GUEST_COOKIE = 'hb_guest';

    /** How long a draft can be resumed (supplier rates go stale after that). */
    public const LIFETIME_HOURS = 48;

    protected $fillable = [
        'token',
        'user_id',
        'guest_key',
        'fingerprint',
        'supplier_name',
        'hotel_id',
        'room_id',
        'checkin',
        'checkout',
        'adults',
        'children',
        'rooms',
        'payload',
        'guest_data',
        'travellers',
        'payment_method',
        'completed_steps',
        'current_step',
        'status',
        'hotel_booking_id',
        'expires_at',
    ];

    protected $casts = [
        'payload'         => 'encrypted:array',
        'guest_data'      => 'encrypted:array',
        'travellers'      => 'encrypted:array',
        'completed_steps' => 'array',
        'checkin'         => 'date',
        'checkout'        => 'date',
        'expires_at'      => 'datetime',
    ];

    /**
     * Drafts that can still be continued.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'draft')->where('expires_at', '>', now());
    }

    /**
     * Drafts owned by whoever is making this request: the signed-in user, or
     * the guest key from this browser's cookie.
     */
    public function scopeOwnedBy($query, Request $request)
    {
        $guestKey = (string) $request->cookie(self::GUEST_COOKIE);

        return $query->where(function ($q) use ($guestKey) {
            if (auth()->check()) {
                $q->where('user_id', auth()->id());
            }
            if ($guestKey !== '') {
                $q->orWhere(fn($g) => $g->whereNull('user_id')->where('guest_key', $guestKey));
            }
            if (!auth()->check() && $guestKey === '') {
                $q->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Whether this request may see and change the draft.
     */
    public function isOwnedBy(Request $request): bool
    {
        if ($this->user_id) {
            return auth()->check() && (int) auth()->id() === (int) $this->user_id;
        }

        $guestKey = (string) $request->cookie(self::GUEST_COOKIE);

        return $guestKey !== '' && $this->guest_key && hash_equals($this->guest_key, $guestKey);
    }

    public function isExpired(): bool
    {
        return !$this->expires_at || $this->expires_at->isPast();
    }

    /**
     * The step a customer may open: completed steps, plus the first step not
     * completed yet. Later steps stay locked until the earlier ones validate.
     */
    public function maxAllowedStep(): int
    {
        $done = $this->completed_steps ?? [];
        $step = 1;
        while ($step < 3 && in_array($step, $done)) {
            $step++;
        }

        return $step;
    }
}
