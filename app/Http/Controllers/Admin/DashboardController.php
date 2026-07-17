<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NewsletterSubscriber;
use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use App\Models\VisaRequest;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $flightTotal    = FlightBooking::count();
        $hotelTotal     = HotelBooking::count();
        $tourTotal      = TourBooking::count();
        $umrahTotal     = UmrahBooking::count();
        $totalBookings  = $flightTotal + $hotelTotal + $tourTotal + $umrahTotal;

        $confirmedBookings =
            FlightBooking::where('booking_status_flag', 'confirmed')->count() +
            HotelBooking::where('booking_status_flag', 'confirmed')->count() +
            TourBooking::where('booking_status_flag', 'confirmed')->count() +
            UmrahBooking::where('booking_status_flag', 'confirmed')->count();

        $cancelledBookings =
            FlightBooking::where('booking_status_flag', 'cancelled')->count() +
            HotelBooking::where('booking_status_flag', 'cancelled')->count() +
            TourBooking::where('booking_status_flag', 'cancelled')->count() +
            UmrahBooking::where('booking_status_flag', 'cancelled')->count();

        $totalRevenue =
            FlightBooking::where('booking_status_flag', 'confirmed')->sum('booking_fare_base') +
            HotelBooking::where('booking_status_flag', 'confirmed')->sum('booking_fare_base') +
            TourBooking::where('booking_status_flag', 'confirmed')->sum('booking_fare_base') +
            UmrahBooking::where('booking_status_flag', 'confirmed')->sum('booking_fare_base');

        $stats = [
            'total_visarequest'        => VisaRequest::count(),
            'blogs_count'              => BlogPost::count(),
            'NewsletterSubscriber'     => NewsletterSubscriber::count(),
            'total_customers'          => User::customers()->count(),
            'active_customers'         => User::customers()->active()->count(),
            'vip_customers'            => User::customers()->vip()->count(),
            'avg_customer_value'       => User::customers()->avg('total_spent') ?? 0,
            'new_customers_this_month' => User::customers()->whereMonth('created_at', now()->month)->count(),
            'new_blogs_this_month'     => BlogPost::whereMonth('created_at', now()->month)->count(),
            'new_subscriber_this_month'=> NewsletterSubscriber::whereMonth('created_at', now()->month)->count(),
            'total_bookings'           => $totalBookings,
            'confirmed_bookings'       => $confirmedBookings,
            'cancelled_bookings'       => $cancelledBookings,
            'pending_bookings'         => $totalBookings - $confirmedBookings - $cancelledBookings,
            'total_revenue'            => $totalRevenue,
            // Per-type counts for summary strip
            'flight_bookings'          => $flightTotal,
            'hotel_bookings'           => $hotelTotal,
            'tour_bookings'            => $tourTotal,
            'umrah_bookings'           => $umrahTotal,
        ];

        // Unified recent bookings — merge all types, sort by created_at, take 10
        $tagged = function ($collection, string $type) {
            return $collection->each(function ($b) use ($type) {
                $b->setAttribute('booking_type', $type);
            });
        };

        $recent_bookings = collect()
            ->concat($tagged(FlightBooking::latest()->take(10)->get(), 'flight'))
            ->concat($tagged(HotelBooking::latest()->take(10)->get(),  'hotel'))
            ->concat($tagged(TourBooking::latest()->take(10)->get(),   'tour'))
            ->concat($tagged(UmrahBooking::latest()->take(10)->get(),  'umrah'))
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        $recent_customers = User::customers()->latest()->take(5)->get();

        return view('admin.dashboard.index', compact('stats', 'recent_bookings', 'recent_customers'));
    }
}
