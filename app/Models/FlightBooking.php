<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FlightBooking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flights_booking';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'booking_code_ref',
        'booking_status_flag',
        'booking_air_pnr',
        'booking_fare_base',
        'booking_adult_count',
        'booking_child_count',
        'booking_infant_count',
        'booking_currency_origin',
        'booking_data',
        'booking_response_json',
        'booking_response_error',
        'booking_payment_state',
        'booking_supplier_name',
        'booking_txn_id',
        'booking_user_data',
        'booking_guest',
        'booking_nationality_code',
        'booking_flight_segment',
        'booking_payment_gateway',
        'agent_id',
        'user_id',
        'booked_via',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'booking_data' => 'array',
        'booking_response_json' => 'array',
        'booking_guest' => 'array',
        'booking_flight_segment' => 'array',
        'booking_user_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer name from user data or guest data
     */
    public function getCustomerNameAttribute()
    {
        $userData = is_array($this->booking_user_data) ? $this->booking_user_data : json_decode($this->booking_user_data, true);
        
        if (!empty($userData['first_name']) || !empty($userData['last_name'])) {
            return trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''));
        }

        $guestData = is_array($this->booking_guest) ? $this->booking_guest : json_decode($this->booking_guest, true);
        if (!empty($guestData) && is_array($guestData)) {
            $firstGuest = $guestData[0] ?? [];
            return trim(($firstGuest['first_name'] ?? '') . ' ' . ($firstGuest['last_name'] ?? ''));
        }

        return 'N/A';
    }

    /**
     * Get the customer email
     */
    public function getCustomerEmailAttribute()
    {
        $userData = is_array($this->booking_user_data) ? $this->booking_user_data : json_decode($this->booking_user_data, true);
        
        if (!empty($userData['user_email'])) {
            return $userData['user_email'];
        }

        $guestData = is_array($this->booking_guest) ? $this->booking_guest : json_decode($this->booking_guest, true);
        if (!empty($guestData) && is_array($guestData)) {
            $firstGuest = $guestData[0] ?? [];
            return $firstGuest['email'] ?? 'N/A';
        }

        return 'N/A';
    }

    /**
     * Get flight route information
     */
    public function getFlightRouteAttribute()
    {
        $segmentData = is_array($this->booking_flight_segment) ? $this->booking_flight_segment : json_decode($this->booking_flight_segment, true);
        
        if (!empty($segmentData['segments']) && is_array($segmentData['segments'])) {
            $segments = $segmentData['segments'];
            
            if (!empty($segments[0]) && is_array($segments[0])) {
                $firstSegment = $segments[0][0] ?? $segments[0];
                $lastSegment = end($segments[0]);
                
                $departure = $firstSegment['departure']['airport'] ?? 'N/A';
                $arrival = $lastSegment['arrival']['airport'] ?? 'N/A';
                
                $stopCount = count($segments[0]) - 1;
                $stopText = $stopCount === 0 ? 'Non-stop' : $stopCount . ' Stop' . ($stopCount > 1 ? 's' : '');
                
                return [
                    'route' => $departure . ' → ' . $arrival,
                    'stops' => $stopText
                ];
            }
        }

        return [
            'route' => 'N/A → N/A',
            'stops' => 'N/A'
        ];
    }

    /**
     * Get flight details (airline and flight number)
     */
    public function getFlightDetailsAttribute()
    {
        $segmentData = is_array($this->booking_flight_segment) ? $this->booking_flight_segment : json_decode($this->booking_flight_segment, true);
        
        if (!empty($segmentData['segments']) && is_array($segmentData['segments'])) {
            $segments = $segmentData['segments'];
            
            if (!empty($segments[0]) && is_array($segments[0])) {
                $firstSegment = $segments[0][0] ?? $segments[0];
                
                return [
                    'flight_number' => $firstSegment['flight_number'] ?? 'N/A',
                    'airline' => $firstSegment['airline_name'] ?? 'N/A'
                ];
            }
        }

        return [
            'flight_number' => 'N/A',
            'airline' => 'N/A'
        ];
    }

    /**
     * Get travel date information
     */
    public function getTravelDateAttribute()
    {
        $segmentData = is_array($this->booking_flight_segment) ? $this->booking_flight_segment : json_decode($this->booking_flight_segment, true);
        
        if (!empty($segmentData['segments']) && is_array($segmentData['segments'])) {
            $segments = $segmentData['segments'];
            
            if (!empty($segments[0]) && is_array($segments[0])) {
                $firstSegment = $segments[0][0] ?? $segments[0];
                
                if (!empty($firstSegment['departure']['date'])) {
                    $date = Carbon::parse($firstSegment['departure']['date']);
                    $time = $firstSegment['departure']['time'] ?? '';
                    
                    return [
                        'date' => $date->format('M j, Y'),
                        'time' => $time
                    ];
                }
            }
        }

        return [
            'date' => 'N/A',
            'time' => 'N/A'
        ];
    }

    /**
     * Get passenger count information
     */
    public function getPassengerCountAttribute()
    {
        $adults = $this->booking_adult_count ?? 0;
        $children = $this->booking_child_count ?? 0;
        $infants = $this->booking_infant_count ?? 0;

        $parts = [];
        if ($adults > 0) $parts[] = $adults . ' Adult' . ($adults > 1 ? 's' : '');
        if ($children > 0) $parts[] = $children . ' Child' . ($children > 1 ? 'ren' : '');
        if ($infants > 0) $parts[] = $infants . ' Infant' . ($infants > 1 ? 's' : '');

        return !empty($parts) ? implode(', ', $parts) : '0 Passengers';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        $currency = $this->booking_currency_origin ?? 'USD';
        $amount = $this->booking_fare_base ?? '0';
        
        // Remove commas and spaces from amount
        $cleanAmount = str_replace([',', ' '], '', $amount);
        
        return $currency . ' ' . number_format((float)$cleanAmount, 0);
    }

    /**
     * Get status badge class
     */
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

    /**
     * Get payment status badge class
     */
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

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('booking_status_flag', $status);
    }

    /**
     * Scope for filtering by payment state
     */
    public function scopeByPaymentState($query, $paymentState)
    {
        return $query->where('booking_payment_state', $paymentState);
    }

    /**
     * Scope for searching bookings
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('booking_code_ref', 'like', "%{$search}%")
                  ->orWhere('booking_air_pnr', 'like', "%{$search}%")
                  ->orWhere('booking_user_data', 'like', "%{$search}%")
                  ->orWhere('booking_guest', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for date range filtering
     */
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