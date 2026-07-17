<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
 
class TourBooking extends Model
{
    use HasFactory;

    protected $table = 'tours_booking';

    protected $fillable = [
        'booking_code_ref',
        'booking_status_flag',
        'tour_id',
        'tour_name',
        'tour_location',
        'tour_type',
        'departure_date',
        'return_date',
        'tour_duration',
        'tour_days',
        'booking_fare_base',
        'booking_adult_count',
        'booking_child_count',
        'booking_adult_price',
        'booking_child_price',
        'booking_total_price',
        'booking_currency_origin',
        'booking_data',
        'booking_response_json',
        'booking_response_error',
        'booking_payment_state',
        'booking_supplier_name',
        'booking_txn_id',
        'booking_pnr',
        'booking_user_data',
        'booking_guest',
        'booking_nationality_code',
        'booking_payment_gateway',
        'agent_id',
        'user_id',
        'booked_via',
    ];

    protected $casts = [
        'booking_data' => 'array',
        'booking_response_json' => 'array',
        'booking_user_data' => 'array',
        'booking_guest' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getCustomerNameAttribute()
    {
        $userData = is_array($this->booking_user_data) ? $this->booking_user_data : json_decode($this->booking_user_data, true);

        if (!empty($userData['first_name']) || !empty($userData['last_name'])) {
            return trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''));
        }

        return 'N/A';
    }

    public function getCustomerEmailAttribute()
    {
        $userData = is_array($this->booking_user_data) ? $this->booking_user_data : json_decode($this->booking_user_data, true);

        return $userData['email'] ?? 'N/A';
    }

    public function getTourInfoAttribute()
    {
        return [
            'name' => $this->tour_name ?? 'N/A',
            'location' => $this->tour_location ?? 'N/A'
        ];
    }

    public function getTravelDateAttribute()
    {
        if ($this->departure_date) {
            $date = Carbon::parse($this->departure_date);
            return [
                'date' => $date->format('M j, Y'),
                'time' => $this->tour_duration ?? 'N/A'
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

        $parts = [];
        if ($adults > 0) $parts[] = $adults . ' Adult' . ($adults > 1 ? 's' : '');
        if ($children > 0) $parts[] = $children . ' Child' . ($children > 1 ? 'ren' : '');

        return !empty($parts) ? implode(', ', $parts) : '0 Travellers';
    }

    public function getFormattedAmountAttribute()
    {
        $currency = $this->booking_currency_origin ?? 'USD';
        $amount = $this->booking_total_price ?? '0';

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
                  ->orWhere('tour_name', 'like', "%{$search}%")
                  ->orWhere('booking_user_data', 'like', "%{$search}%");
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
