<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HotelBooking;
use App\Models\FlightBooking;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use App\Models\VisaRequest;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $type   = $request->input('type', 'all');

        $all = collect()
            ->merge($type === 'all' || $type === 'hotel'  ? HotelBooking::where('user_id', $userId)->latest()->get()->map(fn($b) => $this->fmt($b, 'hotel'))   : collect())
            ->merge($type === 'all' || $type === 'flight' ? FlightBooking::where('user_id', $userId)->latest()->get()->map(fn($b) => $this->fmt($b, 'flight')) : collect())
            ->merge($type === 'all' || $type === 'tour'   ? TourBooking::where('user_id', $userId)->latest()->get()->map(fn($b) => $this->fmt($b, 'tour'))     : collect())
            ->merge($type === 'all' || $type === 'umrah'  ? UmrahBooking::where('user_id', $userId)->latest()->get()->map(fn($b) => $this->fmt($b, 'umrah'))   : collect())
            ->merge($type === 'all' || $type === 'visa'   ? VisaRequest::where('user_id', $userId)->latest()->get()->map(fn($b) => $this->fmt($b, 'visa'))     : collect())
            ->sortByDesc('created_at')->values();

        $stats = [
            'total'     => $all->count(),
            'confirmed' => $all->where('status', 'confirmed')->count(),
            'pending'   => $all->where('status', 'pending')->count(),
            'cancelled' => $all->where('status', 'cancelled')->count(),
            'paid'      => $all->where('payment', 'paid')->count(),
            'unpaid'    => $all->where('payment', 'unpaid')->count(),
            'refunded'  => $all->where('payment', 'refunded')->count(),
        ];

        return view('user.bookings.index', compact('all', 'type', 'stats'));
    }

    private function fmt($b, string $type): array
    {
        return [
            'id'           => $b->id,
            'booking_type' => $type,
            'booking_code' => $b->booking_code_ref ?? strtoupper(substr(md5($b->id . $type), 0, 8)),
            'status'       => $b->booking_status_flag ?? 'pending',
            'payment'      => $b->booking_payment_state ?? 'unpaid',
            'price'        => ($b->booking_currency_origin ?? 'USD') . ' ' . number_format((float)str_replace(',', '', $b->booking_fare_base ?? $b->total_price ?? 0), 2),
            'pnr'          => $b->booking_air_pnr ?? $b->booking_hotel_pnr ?? $b->booking_pnr ?? null,
            'created_at'   => $b->created_at,
        ];
    }
}
