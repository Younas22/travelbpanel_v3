<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\Umrah;
use App\Models\HotelBooking;
use App\Models\FlightBooking;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use App\Models\AgentWalletTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $agent  = auth()->user();
        $wallet = $agent->wallet;
        $agentId = $agent->id;

        // Booking counts
        $stats = [
            'hotels'        => HotelBooking::where('agent_id', $agentId)->count(),
            'flights'       => FlightBooking::where('agent_id', $agentId)->count(),
            'tours'         => TourBooking::where('agent_id', $agentId)->count(),
            'umrah'         => UmrahBooking::where('agent_id', $agentId)->count(),
            'total_hotels'  => Hotel::where('agent_id', $agentId)->count(),
            'total_tours'   => Tour::where('agent_id', $agentId)->count(),
            'total_umrah'   => Umrah::where('agent_id', $agentId)->count(),
        ];

        $stats['total'] = $stats['hotels'] + $stats['flights'] + $stats['tours'] + $stats['umrah'];

        // Recent bookings (merge all types)
        $recentHotels  = HotelBooking::where('agent_id', $agentId)->latest()->take(3)->get()->map(fn($b) => array_merge($b->toArray(), ['booking_type' => 'hotel']));
        $recentFlights = FlightBooking::where('agent_id', $agentId)->latest()->take(3)->get()->map(fn($b) => array_merge($b->toArray(), ['booking_type' => 'flight']));
        $recentTours   = TourBooking::where('agent_id', $agentId)->latest()->take(3)->get()->map(fn($b) => array_merge($b->toArray(), ['booking_type' => 'tour']));

        $recentBookings = collect(array_merge($recentHotels->all(), $recentFlights->all(), $recentTours->all()))
            ->sortByDesc('created_at')
            ->take(5);

        // Recent wallet transactions
        $recentTransactions = AgentWalletTransaction::byAgent($agentId)->latest()->take(5)->get();

        return view('agent.dashboard', compact('agent', 'wallet', 'stats', 'recentBookings', 'recentTransactions'));
    }
}
