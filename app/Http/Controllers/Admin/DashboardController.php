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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        $dash = $this->analytics(request());

        return view('admin.dashboard.index', compact('stats', 'recent_bookings', 'recent_customers', 'dash'));
    }

    /**
     * Range-scoped analytics for the dashboard charts. Revenue = confirmed
     * bookings, converted to the default currency via the Currencies rates.
     */
    private function analytics(Request $request): array
    {
        $presets = ['7' => 'Last 7 Days', '30' => 'Last 30 Days', '90' => 'Last 90 Days', '365' => 'Last 12 Months'];
        $range = (string) $request->query('range', '30');
        if ($request->filled(['from', 'to'])) {
            try {
                $from = Carbon::parse($request->query('from'))->startOfDay();
                $to = Carbon::parse($request->query('to'))->endOfDay();
                if ($from->gt($to)) [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                $range = 'custom';
            } catch (\Throwable $e) {
                $range = '30';
            }
        }
        if ($range !== 'custom') {
            $range = array_key_exists($range, $presets) ? $range : '30';
            $to = now()->endOfDay();
            $from = now()->subDays((int) $range - 1)->startOfDay();
        }
        $days = $from->diffInDays($to) + 1;
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $from->copy()->subDays($days);

        $currencies = DB::table('currencies')->get();
        $default = $currencies->firstWhere('currency_default', '1');
        $baseCurrency = $default->currency_name ?? 'USD';
        $rates = $currencies->pluck('currency_rate', 'currency_name')->map(fn ($r) => (float) $r ?: 1.0);
        $toBase = fn ($amount, $cur) => (float) str_replace([',', ' '], '', (string) $amount) / ($rates[$cur] ?? 1.0) * ($rates[$baseCurrency] ?? 1.0);

        $types = ['flight' => FlightBooking::class, 'hotel' => HotelBooking::class, 'tour' => TourBooking::class, 'umrah' => UmrahBooking::class];
        $all = collect();
        foreach ($types as $type => $model) {
            $model::whereBetween('created_at', [$prevFrom, $to])->get()->each(function ($b) use ($type, $toBase, &$all) {
                $all->push([
                    'type' => $type,
                    'at' => $b->created_at,
                    'status' => strtolower((string) $b->booking_status_flag) ?: 'pending',
                    'revenue' => $b->booking_status_flag === 'confirmed' ? $toBase($b->booking_fare_base, $b->booking_currency_origin ?: 'USD') : 0.0,
                    'dest' => match ($type) {
                        'flight' => trim(explode('→', $b->flight_route['route'] ?? '')[1] ?? ''),
                        'umrah' => trim(explode('→', $b->umrah_info['location'] ?? '')[1] ?? ''),
                        default => '',
                    },
                ]);
            });
        }
        $cur = $all->filter(fn ($b) => $b['at']->between($from, $to));
        $prev = $all->filter(fn ($b) => $b['at']->between($prevFrom, $prevTo));

        $visaCur = VisaRequest::whereBetween('created_at', [$from, $to])->count();
        $visaPrev = VisaRequest::whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $custNewCur = User::customers()->whereBetween('created_at', [$from, $to])->count();
        $custNewPrev = User::customers()->whereBetween('created_at', [$prevFrom, $prevTo])->count();

        // Daily buckets up to 90 days, monthly beyond that.
        $monthly = $days > 90;
        $buckets = [];
        for ($d = $from->copy(); $d->lte($to); $monthly ? $d->addMonthNoOverflow()->startOfMonth() : $d->addDay()) {
            $key = $monthly ? $d->format('Y-m') : $d->format('Y-m-d');
            $buckets[$key] = ['label' => $monthly ? $d->format('M Y') : $d->format('M j'), 'full' => $monthly ? $d->format('F Y') : $d->format('M j, Y'), 'bookings' => 0, 'revenue' => 0.0, 'visa' => 0, 'customers' => 0];
        }
        $bucketKey = fn ($at) => $monthly ? $at->format('Y-m') : $at->format('Y-m-d');
        foreach ($cur as $b) {
            $k = $bucketKey($b['at']);
            if (isset($buckets[$k])) { $buckets[$k]['bookings']++; $buckets[$k]['revenue'] += $b['revenue']; }
        }
        foreach (VisaRequest::whereBetween('created_at', [$from, $to])->pluck('created_at') as $at) {
            if (isset($buckets[$k = $bucketKey($at)])) $buckets[$k]['visa']++;
        }
        foreach (User::customers()->whereBetween('created_at', [$from, $to])->pluck('created_at') as $at) {
            if (isset($buckets[$k = $bucketKey($at)])) $buckets[$k]['customers']++;
        }
        $series = array_values($buckets);

        $change = function ($now, $before) {
            if ($before == 0) return $now > 0 ? null : 0.0;
            return round(($now - $before) / $before * 100, 1);
        };

        $serviceLabels = ['flight' => 'Flights', 'hotel' => 'Hotels', 'tour' => 'Tours', 'umrah' => 'Umrah'];
        $byService = [];
        foreach ($serviceLabels as $type => $label) {
            $byService[$type] = ['label' => $label, 'count' => $cur->where('type', $type)->count(), 'revenue' => round($cur->where('type', $type)->sum('revenue'), 2)];
        }
        $byService['visa'] = ['label' => 'Visa', 'count' => $visaCur, 'revenue' => null];

        $statusOrder = ['confirmed' => 'Confirmed', 'pending' => 'Pending', 'cancelled' => 'Cancelled', 'failed' => 'Failed', 'on_hold' => 'On Hold'];
        $statusCounts = $cur->countBy(fn ($b) => str_replace([' ', '-'], '_', $b['status']));
        $byStatus = [];
        foreach ($statusOrder as $key => $label) $byStatus[$key] = ['label' => $label, 'count' => (int) ($statusCounts[$key] ?? 0)];
        foreach ($statusCounts as $key => $n) if (!isset($byStatus[$key])) $byStatus[$key] = ['label' => ucwords(str_replace('_', ' ', $key)), 'count' => $n];

        $destCounts = $cur->pluck('dest')->filter(fn ($c) => preg_match('/^[A-Z]{3}$/', $c))->countBy()->sortDesc()->take(5);
        $airports = DB::table('flights_airports')->whereIn('code', $destCounts->keys())->get()->keyBy('code');
        $isoByCountry = DB::table('countries')->whereIn('name', $airports->pluck('country'))->pluck('iso2', 'name');
        $destinations = $destCounts->map(fn ($n, $code) => [
            'code' => $code,
            'city' => $airports[$code]->city ?? $code,
            'iso2' => strtolower($isoByCountry[$airports[$code]->country ?? ''] ?? ''),
            'count' => $n,
        ])->values()->all();

        $curRevenue = $cur->sum('revenue');

        return [
            'range' => $range,
            'presets' => $presets,
            'from' => $from,
            'to' => $to,
            'range_label' => $range === 'custom' ? $from->format('M j, Y') . ' – ' . $to->format('M j, Y') : $presets[$range],
            'base_currency' => $baseCurrency,
            'series' => $series,
            'kpis' => [
                ['key' => 'bookings', 'label' => 'Total Bookings', 'icon' => 'bi-calendar-check', 'value' => number_format($cur->count()), 'change' => $change($cur->count(), $prev->count()), 'spark' => array_column($series, 'bookings')],
                ['key' => 'visa', 'label' => 'Visa Requests', 'icon' => 'bi-file-earmark-text', 'value' => number_format($visaCur), 'change' => $change($visaCur, $visaPrev), 'spark' => array_column($series, 'visa')],
                ['key' => 'revenue', 'label' => 'Total Revenue', 'icon' => 'bi-currency-dollar', 'value' => $baseCurrency . ' ' . number_format($curRevenue), 'change' => $change($curRevenue, $prev->sum('revenue')), 'spark' => array_column($series, 'revenue')],
                ['key' => 'customers', 'label' => 'No. of Customers', 'icon' => 'bi-people', 'value' => number_format(User::customers()->count()), 'change' => $change($custNewCur, $custNewPrev), 'spark' => array_column($series, 'customers'), 'note' => number_format($custNewCur) . ' new'],
            ],
            'by_service' => $byService,
            'service_total' => array_sum(array_column($byService, 'count')),
            'by_status' => $byStatus,
            'status_total' => $cur->count(),
            'destinations' => $destinations,
        ];
    }
}
