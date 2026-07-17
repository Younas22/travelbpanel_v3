<?php

namespace App\Http\Controllers\Agent;

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
        $agent   = auth()->user();
        $agentId = $agent->id;
        $type    = $request->input('type', 'all');

        $hotels  = $type === 'all' || $type === 'hotel'  ? HotelBooking::where('agent_id', $agentId)->latest()->get()->map(fn($b) => $this->formatBooking($b, 'hotel'))   : collect();
        $flights = $type === 'all' || $type === 'flight' ? FlightBooking::where('agent_id', $agentId)->latest()->get()->map(fn($b) => $this->formatBooking($b, 'flight')) : collect();
        $tours   = $type === 'all' || $type === 'tour'   ? TourBooking::where('agent_id', $agentId)->latest()->get()->map(fn($b) => $this->formatBooking($b, 'tour'))     : collect();
        $umrah   = $type === 'all' || $type === 'umrah'  ? UmrahBooking::where('agent_id', $agentId)->latest()->get()->map(fn($b) => $this->formatBooking($b, 'umrah'))   : collect();

        $bookings = $hotels->merge($flights)->merge($tours)->merge($umrah)
                           ->sortByDesc('created_at')
                           ->values();

        return view('agent.bookings.index', compact('bookings', 'type'));
    }

    public function show(string $type, int $id)
    {
        $agentId = auth()->id();

        $booking = match($type) {
            'hotel'  => HotelBooking::where('agent_id', $agentId)->findOrFail($id),
            'flight' => FlightBooking::where('agent_id', $agentId)->findOrFail($id),
            'tour'   => TourBooking::where('agent_id', $agentId)->findOrFail($id),
            'umrah'  => UmrahBooking::where('agent_id', $agentId)->findOrFail($id),
            'visa'   => VisaRequest::where('agent_id', $agentId)->findOrFail($id),
            default  => abort(404),
        };

        return view('agent.bookings.show', compact('booking', 'type'));
    }

    private function formatBooking($booking, string $type): array
    {
        $data = $booking->toArray();

        $normalized = [
            'booking_type' => $type,
            'booking_code' => $data['booking_code_ref'] ?? null,
            'status'       => $data['booking_status_flag'] ?? null,
            'created_at'   => $data['created_at'] ?? null,
        ];

        switch ($type) {
            case 'flight':
                $segment = is_array($data['booking_flight_segment'] ?? null)
                    ? $data['booking_flight_segment']
                    : json_decode($data['booking_flight_segment'] ?? '{}', true);
                $segments  = $segment['segments'] ?? [];
                $firstSeg  = $segments[0][0] ?? ($segments[0] ?? []);
                $lastGroup = !empty($segments) ? end($segments) : [];
                $lastSeg   = is_array($lastGroup) ? (end($lastGroup) ?: []) : [];
                $normalized['total_fare']     = $data['booking_fare_base'] ?? 0;
                $normalized['origin']         = $firstSeg['departure']['airport'] ?? null;
                $normalized['destination']    = $lastSeg['arrival']['airport'] ?? $firstSeg['arrival']['airport'] ?? null;
                $normalized['departure_date'] = $firstSeg['departure']['date'] ?? null;
                break;

            case 'hotel':
                $bookingData = is_array($data['booking_data'] ?? null)
                    ? $data['booking_data']
                    : json_decode($data['booking_data'] ?? '{}', true);
                $innerData = $bookingData['booking_data'] ?? [];
                $normalized['total_fare']  = $data['booking_fare_base'] ?? 0;
                $normalized['hotel_name']  = $innerData['hotel_name'] ?? ($bookingData['hotel_name'] ?? null);
                $normalized['check_in']    = $innerData['checkin'] ?? ($innerData['check_in'] ?? null);
                $normalized['check_out']   = $innerData['checkout'] ?? ($innerData['check_out'] ?? null);
                break;

            case 'tour':
                $normalized['total_fare'] = $data['booking_total_price'] ?? $data['booking_fare_base'] ?? 0;
                $normalized['tour_name']  = $data['tour_name'] ?? null;
                break;

            case 'umrah':
                $normalized['total_fare']    = $data['total_price'] ?? $data['booking_fare_base'] ?? 0;
                $normalized['package_name']  = $data['umrah_name'] ?? null;
                break;
        }

        return array_merge($data, $normalized);
    }
}
