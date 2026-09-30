<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HotelBooking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hotels_booking';
 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'booking_code_ref',
        'booking_status_flag',
        'booking_hotel_pnr',
        'booking_fare_base',
        'booking_adult_count',
        'booking_child_count',
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
        'booking_user_data' => 'array',
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
     * Get hotel information
     */
    public function getHotelInfoAttribute()
    {
        $bookingData = is_array($this->booking_data) ? $this->booking_data : json_decode($this->booking_data, true);

        // Check if data is nested (booking_data -> booking_data)
        if (is_array($bookingData) && isset($bookingData['booking_data'])) {
            $hotelData = $bookingData['booking_data'];

            if (!empty($hotelData['hotel_name'])) {
                // Extract location from address
                $address = $hotelData['address'] ?? '';
                $location = 'N/A';

                if ($address) {
                    // Try to extract city from address (usually after last comma)
                    $parts = explode(',', $address);
                    if (count($parts) >= 2) {
                        $location = trim($parts[count($parts) - 2]);
                    }
                }

                return [
                    'name' => $hotelData['hotel_name'],
                    'location' => $location
                ];
            }
        }

        // Fallback for direct structure
        if (is_array($bookingData) && !empty($bookingData['hotel_name'])) {
            $city = $bookingData['city'] ?? '';
            $country = $bookingData['country'] ?? '';
            $location = trim($city . ($city && $country ? ', ' : '') . $country);

            return [
                'name' => $bookingData['hotel_name'],
                'location' => $location ?: 'N/A'
            ];
        }

        return [
            'name' => 'N/A',
            'location' => 'N/A'
        ];
    }

    /**
     * Get check-in and check-out dates
     */
    public function getStayDatesAttribute()
    {
        $bookingData = is_array($this->booking_data) ? $this->booking_data : json_decode($this->booking_data, true);

        // Check if data is nested (booking_data -> booking_data)
        if (is_array($bookingData) && isset($bookingData['booking_data'])) {
            $hotelData = $bookingData['booking_data'];

            if (!empty($hotelData['checkin'])) {
                try {
                    $checkIn = Carbon::parse($hotelData['checkin']);
                    $checkOut = !empty($hotelData['checkout']) ? Carbon::parse($hotelData['checkout']) : null;

                    $nights = $checkOut ? $checkIn->diffInDays($checkOut) : 0;

                    return [
                        'check_in' => $checkIn->format('M j, Y'),
                        'check_out' => $checkOut ? $checkOut->format('M j, Y') : 'N/A',
                        'nights' => $nights . ' Night' . ($nights > 1 ? 's' : '')
                    ];
                } catch (\Exception $e) {
                    // If date parsing fails, continue to fallback
                }
            }
        }

        // Fallback for direct structure
        if (is_array($bookingData) && !empty($bookingData['check_in'])) {
            try {
                $checkIn = Carbon::parse($bookingData['check_in']);
                $checkOut = !empty($bookingData['check_out']) ? Carbon::parse($bookingData['check_out']) : null;

                $nights = $checkOut ? $checkIn->diffInDays($checkOut) : 0;

                return [
                    'check_in' => $checkIn->format('M j, Y'),
                    'check_out' => $checkOut ? $checkOut->format('M j, Y') : 'N/A',
                    'nights' => $nights . ' Night' . ($nights > 1 ? 's' : '')
                ];
            } catch (\Exception $e) {
                // If date parsing fails, return N/A
            }
        }

        return [
            'check_in' => 'N/A',
            'check_out' => 'N/A',
            'nights' => 'N/A'
        ];
    }

    /**
     * Get guest count information
     */
    public function getGuestCountAttribute()
    {
        $adults = $this->booking_adult_count ?? 0;
        $children = $this->booking_child_count ?? 0;

        $parts = [];
        if ($adults > 0) $parts[] = $adults . ' Adult' . ($adults > 1 ? 's' : '');
        if ($children > 0) $parts[] = $children . ' Child' . ($children > 1 ? 'ren' : '');

        return !empty($parts) ? implode(', ', $parts) : '0 Guests';
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
                  ->orWhere('booking_hotel_pnr', 'like', "%{$search}%")
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
