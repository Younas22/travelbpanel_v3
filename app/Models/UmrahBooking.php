<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UmrahBooking extends Model
{
    use HasFactory;

    protected $table = 'umrah_bookings';

    protected $fillable = [
        'booking_code_ref',
        'booking_status_flag',
        'booking_fare_base',
        'umrah_id',
        'umrah_name',
        'user_first_name',
        'user_last_name',
        'user_email',
        'user_phone',
        'user_country',
        'booking_guest',
        'booking_nationality_code',
        'search_params',
        'booking_adult_count',
        'booking_child_count',
        'infant_count',
        'adult_price',
        'child_price',
        'infant_price',
        'total_price',
        'booking_currency_origin',
        'booking_data',
        'booking_response_json',
        'booking_response_error',
        'booking_payment_state',
        'booking_supplier_name',
        'booking_txn_id',
        'booking_pnr',
        'booking_user_data',
        'booking_payment_gateway',
        'agent_id',
        'user_id',
        'booked_via',
    ];

    protected $casts = [
        'booking_guest' => 'array',
        'booking_data' => 'array',
        'booking_response_json' => 'array',
        'booking_user_data' => 'array',
        'search_params' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The agent who made this booking, when booked_via is "agent".
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function getCustomerNameAttribute()
    {
        return trim(($this->user_first_name ?? '') . ' ' . ($this->user_last_name ?? '')) ?: 'N/A';
    }

    public function getCustomerEmailAttribute()
    {
        return $this->user_email ?? 'N/A';
    }

    public function getUmrahInfoAttribute()
    {
        $searchParams = is_array($this->search_params) ? $this->search_params : json_decode($this->search_params, true);

        return [
            'name' => $this->umrah_name ?? 'N/A',
            'location' => ($searchParams['origin'] ?? 'N/A') . ' → ' . ($searchParams['destination'] ?? 'N/A')
        ];
    }

    public function getTravelDateAttribute()
    {
        $searchParams = is_array($this->search_params) ? $this->search_params : json_decode($this->search_params, true);

        if (!empty($searchParams['departure_date'])) {
            $date = Carbon::parse($searchParams['departure_date']);
            $nights = ($searchParams['makkah_nights'] ?? 0) + ($searchParams['madina_nights'] ?? 0);

            return [
                'date' => $date->format('M j, Y'),
                'time' => $nights . ' Night' . ($nights > 1 ? 's' : '')
            ];
        }

        return [
            'date' => 'N/A',
            'time' => 'N/A'
        ];
    }

    public function getPassengerCountAttribute()
    {
        $adults = $this->booking_adult_count ?? 0;
        $children = $this->booking_child_count ?? 0;
        $infants = $this->infant_count ?? 0;

        $parts = [];
        if ($adults > 0) $parts[] = $adults . ' Adult' . ($adults > 1 ? 's' : '');
        if ($children > 0) $parts[] = $children . ' Child' . ($children > 1 ? 'ren' : '');
        if ($infants > 0) $parts[] = $infants . ' Infant' . ($infants > 1 ? 's' : '');

        return !empty($parts) ? implode(', ', $parts) : '0 Travellers';
    }

    public function getFormattedAmountAttribute()
    {
        $currency = $this->booking_currency_origin ?? 'USD';
        $amount = $this->total_price ?? '0';

        $cleanAmount = str_replace([',', ' '], '', $amount);

        return $currency . ' ' . number_format((float)$cleanAmount, 2);
    }

    public function getStatusBadgeClassAttribute()
    {
        switch ($this->booking_status_flag) {
            case 'confirmed':
                return 'status-confirmed';
            case 'pending':
                return 'status-pending';
            case 'cancelled':
                return 'status-cancelled';
            default:
                return 'status-pending';
        }
    }

    public function getPaymentStatusBadgeClassAttribute()
    {
        switch ($this->booking_payment_state) {
            case 'paid':
                return 'status-confirmed';
            case 'unpaid':
                return 'status-pending';
            case 'refunded':
                return 'status-refunded';
            default:
                return 'status-pending';
        }
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('booking_status_flag', $status);
    }

    public function scopeByPaymentState($query, $paymentState)
    {
        return $query->where('booking_payment_state', $paymentState);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('booking_code_ref', 'like', "%{$search}%")
                  ->orWhere('umrah_name', 'like', "%{$search}%")
                  ->orWhere('user_first_name', 'like', "%{$search}%")
                  ->orWhere('user_last_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%");
        });
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            return $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            return $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

}
