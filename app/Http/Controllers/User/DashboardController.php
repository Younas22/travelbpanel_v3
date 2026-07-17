<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HotelBooking;
use App\Models\FlightBooking;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use App\Models\VisaRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $recentBookings = collect()
            ->merge(HotelBooking::where('user_id', $userId)->latest()->take(5)->get()->map(fn($b) => $this->fmt($b, 'hotel')))
            ->merge(FlightBooking::where('user_id', $userId)->latest()->take(5)->get()->map(fn($b) => $this->fmt($b, 'flight')))
            ->merge(TourBooking::where('user_id', $userId)->latest()->take(5)->get()->map(fn($b) => $this->fmt($b, 'tour')))
            ->merge(UmrahBooking::where('user_id', $userId)->latest()->take(5)->get()->map(fn($b) => $this->fmt($b, 'umrah')))
            ->merge(VisaRequest::where('user_id', $userId)->latest()->take(5)->get()->map(fn($b) => $this->fmt($b, 'visa')))
            ->sortByDesc('created_at')->take(5)->values();

        $totalBookings = $recentBookings->count();

        return view('user.dashboard', compact('recentBookings', 'totalBookings'));
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
