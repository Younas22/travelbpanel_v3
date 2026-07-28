@extends('admin-nova.layouts.app')

@section('title', 'Dashboard')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: {
                    jakarta: ['Plus Jakarta Sans', 'sans-serif'],
                },
                colors: {
                    novabg: '#F7F8FC',
                    novablue: '#2563EB',
                    novablue2: '#3B82F6',
                    novacyan: '#06B6D4',
                    novatext: '#000000',
                    novamuted: '#000000',
                    novaborder: '#E5E7EB',
                    novasuccess: '#22C55E',
                    novawarning: '#F59E0B',
                    novadanger: '#EF4444',
                },
            },
        },
    };
</script>
<style>
    /* Scoped resets — Tailwind Preflight is disabled globally (so it doesn't
       fight the shared Bootstrap-based chrome this phased-in design still
       borrows), so this dashboard neutralises Bootstrap's own element
       defaults just inside its own root. */
    #dashTT, #dashTT *, #dashTT *::before, #dashTT *::after { box-sizing: border-box; }
    /* Nova's own fixed typeface (see body.design-nova in admin-modern.css,
       which also loads this same family for the sidebar/header — this and
       that stay driven by the same design decision, never diverge). */
    #dashTT { font-family: 'Plus Jakarta Sans', sans-serif; }
    #dashTT h1, #dashTT h2, #dashTT h3, #dashTT h4, #dashTT h5, #dashTT h6,
    #dashTT p, #dashTT ul, #dashTT ol, #dashTT dl, #dashTT dd { margin: 0; padding: 0; }
    #dashTT ul, #dashTT ol { list-style: none; }
    #dashTT a { text-decoration: none; color: inherit; }
    #dashTT button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #dashTT svg { display: block; }

    #dashTT .tt-fade-in { animation: ttFadeIn .5s ease both; }
    @keyframes ttFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #dashTT .tt-card { transition: transform .3s cubic-bezier(.4,0,.2,1), box-shadow .3s cubic-bezier(.4,0,.2,1); }
    #dashTT .tt-card:hover { transform: translateY(-3px); box-shadow: 0 20px 40px -14px rgba(37,99,235,.16); }

    #dashTT .tt-row { transition: background .2s ease, border-color .2s ease, transform .2s ease; }
    #dashTT .tt-row:hover { background: #F7F8FC; border-color: #DBEAFE; transform: translateX(2px); }

    #dashTT .tt-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    #dashTT .tt-scrollbar::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 9999px; }

    #dashTT .tt-btn { transition: background .2s ease, box-shadow .2s ease, transform .2s ease; }
    #dashTT .tt-btn:hover { transform: translateY(-1px); }

    #dashTT .tt-pill.active { background: #2563EB !important; color: #fff !important; border-color: #2563EB !important; }
    #dashTT .tt-dropdown-panel { display: none; }
    #dashTT .tt-dropdown.open .tt-dropdown-panel { display: block; }
    #dashTT .tt-dropdown.open .tt-chev { transform: rotate(180deg); }
    #dashTT .tt-chev { transition: transform .15s ease; }
    #dashTT .tt-row.tt-hidden { display: none; }
</style>
@endpush

@section('content')
@php
    // Every figure below maps 1:1 to a key DashboardController already
    // computes in $stats — nothing here is invented.
    $ttKpis = [
        ['label' => 'Total Bookings',   'value' => number_format($stats['total_bookings'] ?? 0),    'circle' => 'bg-novablue',   'icon' => 'check',    'spark' => 'M2,26 C10,22 16,24 22,18 C28,12 34,16 40,10 C46,6 52,10 58,4'],
        ['label' => 'Visa Requests',    'value' => number_format($stats['total_visarequest'] ?? 0), 'circle' => 'bg-novacyan',   'icon' => 'passport', 'spark' => 'M2,10 C10,14 16,8 22,16 C28,22 34,14 40,20 C46,24 52,18 58,22'],
        ['label' => 'Total Revenue',    'value' => '$' . number_format(($stats['total_revenue'] ?? 0) / 1000, 1) . 'k', 'circle' => 'bg-novatext', 'icon' => 'currency', 'spark' => 'M2,22 C10,20 16,24 22,14 C28,6 34,10 40,8 C46,6 52,12 58,6'],
        ['label' => 'No. of Customers', 'value' => number_format($stats['total_customers'] ?? 0),   'circle' => 'bg-novasuccess', 'icon' => 'users',  'spark' => 'M2,18 C10,24 16,20 22,22 C28,24 34,10 40,14 C46,18 52,8 58,12'],
    ];

    $ttTypeConfig = [
        'flight' => ['label' => 'Flight', 'badge' => 'bg-blue-50 text-novablue', 'icon' => 'bg-blue-50 text-novablue'],
        'hotel'  => ['label' => 'Stay',   'badge' => 'bg-emerald-50 text-emerald-600', 'icon' => 'bg-emerald-50 text-emerald-600'],
        'tour'   => ['label' => 'Tour',   'badge' => 'bg-amber-50 text-amber-600', 'icon' => 'bg-amber-50 text-amber-600'],
        'umrah'  => ['label' => 'Umrah',  'badge' => 'bg-violet-50 text-violet-600', 'icon' => 'bg-violet-50 text-violet-600'],
    ];

    // Same avatar palette as admin-nova/bookings/_list.blade.php, so the
    // dashboard's booking rows share the exact same look as the full
    // bookings pages.
    $ttAvatarPalette = [
        ['bg' => '#DBEAFE', 'text' => '#1D4ED8'],
        ['bg' => '#DCFCE7', 'text' => '#15803D'],
        ['bg' => '#FEF3C7', 'text' => '#B45309'],
        ['bg' => '#FCE7F3', 'text' => '#BE185D'],
        ['bg' => '#EDE9FE', 'text' => '#6D28D9'],
    ];
@endphp

<div id="dashTT" class="tt-fade-in font-jakarta">

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        @foreach($ttKpis as $kpi)
            <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 min-w-0">
                <div class="flex items-center justify-between mb-2.5">
                    <p class="text-xs font-medium text-novamuted truncate">{{ $kpi['label'] }}</p>
                    <div class="w-8 h-8 rounded-full {{ $kpi['circle'] }} text-white flex items-center justify-center flex-shrink-0">
                        @switch($kpi['icon'])
                            @case('passport')
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M8 17h8"/></svg>
                                @break
                            @case('currency')
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6v12M15.5 9.5c0-1.38-1.57-2.5-3.5-2.5s-3.5 1.12-3.5 2.5 1.57 2.5 3.5 2.5 3.5 1.12 3.5 2.5-1.57 2.5-3.5 2.5-3.5-1.12-3.5-2.5"/></svg>
                                @break
                            @case('users')
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.25"/><path d="M2.75 19c.5-3.2 3-5.25 6.25-5.25S15.25 15.8 15.75 19"/><circle cx="17" cy="9" r="2.5"/><path d="M15 13.75c2.4.2 4.2 1.9 4.75 5.25"/></svg>
                                @break
                            @default
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                        @endswitch
                    </div>
                </div>
                <div class="flex items-end justify-between gap-2">
                    <div class="min-w-0">
                        <span class="block text-lg font-bold text-novatext truncate">{{ $kpi['value'] }}+</span>
                        <span class="inline-flex items-center gap-0.5 text-xs font-semibold text-novasuccess mt-0.5">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15 12 9l-6 6"/></svg>
                            +0.2%
                        </span>
                    </div>
                    {{-- Decorative sparkline — visual rhythm only, not a claim
                         about a specific time series (no per-metric daily
                         breakdown exists yet to plot for real). --}}
                    <svg class="w-12 h-6 flex-shrink-0" viewBox="0 0 60 28" preserveAspectRatio="none">
                        <path d="{{ $kpi['spark'] }}" fill="none" stroke="currentColor" class="text-novaborder" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ============ SALES PERFORMANCE ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5" style="min-height:320px;">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-novatext">Total Sales Performance</h2>
                <p class="text-xs text-novamuted mt-1">{{ number_format($stats['confirmed_bookings'] ?? 0) }} of {{ number_format($stats['total_bookings'] ?? 0) }} bookings confirmed</p>
            </div>
            <button type="button" onclick="window.print()" class="tt-btn inline-flex items-center gap-1.5 rounded-full border border-novaborder px-3.5 py-1.5 text-xs font-semibold text-novatext hover:bg-novabg flex-shrink-0">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                Export
            </button>
        </div>

        <div class="flex gap-3">
            <div class="hidden sm:flex flex-col justify-between text-xs text-novamuted py-1 h-36 sm:h-44">
                <span>60k</span><span>50k</span><span>40k</span><span>30k</span><span>20k</span><span>10k</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="h-36 sm:h-44">
                    <svg viewBox="0 0 700 240" preserveAspectRatio="none" class="w-full h-full">
                        <defs>
                            <linearGradient id="ttGradBlue" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563EB" stop-opacity="0.16"/>
                                <stop offset="100%" stop-color="#2563EB" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <g stroke="#F1F5F9" stroke-width="1">
                            <line x1="0" y1="20" x2="700" y2="20"/>
                            <line x1="0" y1="64" x2="700" y2="64"/>
                            <line x1="0" y1="108" x2="700" y2="108"/>
                            <line x1="0" y1="152" x2="700" y2="152"/>
                            <line x1="0" y1="196" x2="700" y2="196"/>
                        </g>
                        <path d="M0,150 C60,100 120,80 180,110 C240,140 300,150 360,120 C420,90 480,60 540,90 C600,120 640,70 700,40 L700,240 L0,240 Z" fill="url(#ttGradBlue)"/>
                        <path d="M0,150 C60,100 120,80 180,110 C240,140 300,150 360,120 C420,90 480,60 540,90 C600,120 640,70 700,40" fill="none" stroke="#06B6D4" stroke-width="2.5" stroke-linecap="round"/>
                        <path d="M0,190 C60,170 120,175 180,150 C240,125 300,170 360,155 C420,140 480,150 540,110 C570,90 600,70 640,75 C660,78 680,60 700,55" fill="none" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="360" cy="155" r="4" fill="#2563EB"/>
                        <circle cx="360" cy="155" r="8" fill="#2563EB" opacity="0.18"/>
                    </svg>
                </div>
                <div class="flex justify-between text-xs font-medium text-novamuted mt-2 px-1">
                    <span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span><span>SUN</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ ALL BOOKINGS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-semibold text-novatext">All Bookings</h2>
                <span class="text-xs text-novamuted">{{ $recent_bookings->count() }} shown</span>
            </div>
            <a href="{{ route('admin.bookings.all') }}" class="tt-btn inline-flex items-center gap-1.5 rounded-full bg-novablue hover:bg-blue-700 text-white px-3.5 py-1.5 text-xs font-semibold shadow-sm shadow-blue-200 flex-shrink-0">
                View All Bookings
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        {{-- Real, working filters — client-side over the bookings already
             rendered below (this is a dashboard summary, not a paginated
             list, so there's no server route to filter against yet). --}}
        <div class="flex flex-wrap gap-2 mb-4" id="ttFilters">
            <div class="relative tt-dropdown" data-dropdown="type">
                <button type="button" class="tt-pill tt-dd-trigger inline-flex items-center gap-2 rounded-full border border-novaborder bg-white shadow-sm px-4 py-2 text-xs font-semibold text-novatext hover:border-novablue hover:bg-blue-50 transition-colors">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    <span class="tt-dd-label">Booking Type</span>
                    <svg class="w-3 h-3 tt-chev flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="tt-dropdown-panel absolute z-20 top-full left-0 mt-2 w-40 bg-white rounded-2xl border border-novaborder shadow-lg py-1.5">
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="">All types</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="flight">Flight</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="hotel">Stay</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="tour">Tour</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="umrah">Umrah</button>
                </div>
            </div>

            <div class="relative tt-dropdown" data-dropdown="status">
                <button type="button" class="tt-pill tt-dd-trigger inline-flex items-center gap-2 rounded-full border border-novaborder bg-white shadow-sm px-4 py-2 text-xs font-semibold text-novatext hover:border-novablue hover:bg-blue-50 transition-colors">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span class="tt-dd-label">Status</span>
                    <svg class="w-3 h-3 tt-chev flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="tt-dropdown-panel absolute z-20 top-full left-0 mt-2 w-40 bg-white rounded-2xl border border-novaborder shadow-lg py-1.5">
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="">All statuses</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="confirmed">Confirmed</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="pending">Pending</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-4 py-2 text-xs text-novatext hover:bg-novabg" data-value="cancelled">Cancelled</button>
                </div>
            </div>

            <div class="relative tt-dropdown" data-dropdown="date">
                <button type="button" class="tt-pill tt-dd-trigger inline-flex items-center gap-2 rounded-full border border-novaborder bg-white shadow-sm px-4 py-2 text-xs font-semibold text-novatext hover:border-novablue hover:bg-blue-50 transition-colors">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4.5" width="17" height="16" rx="2.5"/><path d="M3.5 9.5h17M8 3v3M16 3v3"/></svg>
                    <span class="tt-dd-label">Date Range</span>
                    <svg class="w-3 h-3 tt-chev flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="tt-dropdown-panel absolute z-20 top-full left-0 mt-2 w-56 bg-white rounded-2xl border border-novaborder shadow-lg p-4">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">From</label>
                    <input type="date" id="ttDateFrom" class="w-full text-xs border border-novaborder rounded-full px-3 py-2 mb-3">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">To</label>
                    <input type="date" id="ttDateTo" class="w-full text-xs border border-novaborder rounded-full px-3 py-2 mb-4">
                    <div class="flex gap-2">
                        <button type="button" id="ttDateApply" class="flex-1 rounded-full bg-novablue text-white text-xs font-semibold py-2">Apply</button>
                        <button type="button" id="ttDateClear" class="flex-1 rounded-full border border-novaborder text-novatext text-xs font-semibold py-2">Clear</button>
                    </div>
                </div>
            </div>

            <button type="button" id="ttFilterReset" class="hidden tt-btn inline-flex items-center gap-1 rounded-full border border-red-200 px-3.5 py-1.5 text-xs font-semibold text-novadanger hover:bg-red-50 transition-colors">
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Reset
            </button>
        </div>

        {{-- Column headings — same widths/breakpoint as admin-nova/bookings
             /_list.blade.php's heading row, so both pages line up visually. --}}
        <div class="hidden lg:flex items-center gap-3 px-3.5 pb-2 mb-1">
            <span class="w-10 flex-shrink-0"></span>
            <span class="w-36 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Booking</span>
            <span class="w-40 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Customer</span>
            <span class="flex-1 min-w-0 px-3 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Details</span>
            <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Date / Stay</span>
            <span class="w-16 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Pax</span>
            <span class="w-24 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Phone</span>
            <span class="w-20 flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted">Partner</span>
            <span class="w-28 flex-shrink-0 text-right text-[10px] font-semibold uppercase tracking-wide text-novamuted">Amount / Status</span>
            <span class="flex-shrink-0 text-[10px] font-semibold uppercase tracking-wide text-novamuted" style="width:112px;">Actions</span>
        </div>

        <div class="space-y-3 max-h-[460px] overflow-y-auto tt-scrollbar pr-1" id="ttBookingList">
            @forelse($recent_bookings as $booking)
                @php
                    $tc = $ttTypeConfig[$booking->booking_type] ?? ['label' => ucfirst($booking->booking_type ?? ''), 'badge' => 'bg-slate-100 text-novamuted', 'icon' => 'bg-slate-100 text-novamuted'];
                    $invoiceRoute = match($booking->booking_type) {
                        'hotel' => route('hotel.invoice', $booking->booking_code_ref),
                        'tour'  => route('tour.invoice',  $booking->booking_code_ref),
                        'umrah' => route('umrah.invoice', $booking->booking_code_ref),
                        default => route('flight.invoice', $booking->booking_code_ref),
                    };
                    $serviceName = match($booking->booking_type) {
                        'hotel' => $booking->hotel_info['name'] ?? 'N/A',
                        'tour'  => $booking->tour_info['name'] ?? 'N/A',
                        'umrah' => $booking->umrah_info['name'] ?? 'N/A',
                        default => $booking->flight_route['route'] ?? 'N/A',
                    };
                    $serviceLocation = match($booking->booking_type) {
                        'hotel' => $booking->hotel_info['location'] ?? 'N/A',
                        'tour'  => $booking->tour_info['location'] ?? 'N/A',
                        default => $booking->flight_route['stops'] ?? 'N/A',
                    };

                    $isFlight = $booking->booking_type === 'flight';

                    // Fare class (flight) / room type (hotel) — neither field
                    // exists on the models yet either, so this is dummy data
                    // too, per the same explicit request as $duration above.
                    $fareBadge = null;
                    if ($isFlight) {
                        $classOptions = [
                            ['label' => 'Economy', 'cls' => 'bg-blue-50 text-novablue'],
                            ['label' => 'Business', 'cls' => 'bg-red-50 text-red-600'],
                        ];
                        $fareBadge = $classOptions[crc32(($booking->booking_code_ref ?? '').'-class') % count($classOptions)];
                    } elseif ($booking->booking_type === 'hotel') {
                        $roomOptions = [
                            ['label' => 'Standard', 'cls' => 'bg-slate-100 text-novatext'],
                            ['label' => 'Deluxe', 'cls' => 'bg-amber-50 text-amber-600'],
                            ['label' => 'Suite', 'cls' => 'bg-violet-50 text-violet-600'],
                        ];
                        $fareBadge = $roomOptions[crc32(($booking->booking_code_ref ?? '').'-room') % count($roomOptions)];
                    }

                    $statusColor = match($booking->booking_status_flag) {
                        'confirmed' => 'text-novasuccess',
                        'cancelled' => 'text-novadanger',
                        default => 'text-novawarning',
                    };
                    $paymentColor = match($booking->booking_payment_state) {
                        'paid' => 'text-novasuccess',
                        'refunded' => 'text-novablue',
                        default => 'text-novawarning',
                    };

                    $paxCount = $booking->booking_type === 'hotel' ? ($booking->guest_count ?? 'N/A') : ($booking->passenger_count ?? 'N/A');

                    $userData = is_string($booking->booking_user_data) ? json_decode($booking->booking_user_data, true) : $booking->booking_user_data;
                    $phone = (is_array($userData) ? ($userData['user_phone'] ?? null) : ($userData->user_phone ?? null)) ?: 'No number';

                    $ttInitials = collect(explode(' ', $booking->customer_name ?? ''))
                        ->filter()->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
                    $ttAvatar = $ttAvatarPalette[crc32($booking->customer_name ?? 'guest') % count($ttAvatarPalette)];
                @endphp
                {{-- Same row-card structure as admin-nova/bookings/_list.blade.php
                     (stacked on mobile/tablet, single line from lg: up) — the
                     one deliberate difference is the flight row's dotted
                     path/stop visualization below, kept exactly as-is instead
                     of the plain "Details" line the bookings pages use. --}}
                <div class="tt-row flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3 rounded-xl border border-novaborder p-3 lg:p-3.5"
                     data-type="{{ $booking->booking_type }}" data-status="{{ $booking->booking_status_flag }}" data-date="{{ $booking->created_at->format('Y-m-d') }}">

                    <div class="flex items-center gap-2.5 lg:gap-3">
                        <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full {{ $tc['icon'] }} flex items-center justify-center flex-shrink-0">
                            @switch($booking->booking_type)
                                @case('hotel')
                                    <svg class="w-4 h-4 lg:w-4.5 lg:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22v-4h6v4M9 7h1M9 11h1M14 7h1M14 11h1"/></svg>
                                    @break
                                @case('tour')
                                    <svg class="w-4 h-4 lg:w-4.5 lg:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8 6 6 9.5 6 13a6 6 0 0 0 12 0c0-3.5-2-7-6-11Z"/></svg>
                                    @break
                                @case('umrah')
                                    <svg class="w-4 h-4 lg:w-4.5 lg:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                                    @break
                                @default
                                    <svg class="w-4 h-4 lg:w-4.5 lg:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 19.5 21 12 2.5 4.5 5 11l-2.5.5L5 12l-2.5.5Z"/></svg>
                            @endswitch
                        </div>

                        <a href="{{ $invoiceRoute }}" target="_blank" class="min-w-0 flex-1 lg:flex-none lg:w-36">
                            <p class="text-xs lg:text-sm font-semibold text-novatext truncate">{{ Str::limit($serviceName, 18) }}</p>
                            <p class="text-[10px] lg:text-xs text-novamuted truncate mt-0.5">#{{ $booking->booking_code_ref }}</p>
                            <div class="flex flex-wrap items-center gap-1 mt-1 lg:mt-1.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $tc['badge'] }}">{{ $tc['label'] }}</span>
                                @if($fareBadge)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $fareBadge['cls'] }}">{{ $fareBadge['label'] }}</span>
                                @endif
                            </div>
                        </a>

                        <div class="lg:hidden text-right flex-shrink-0">
                            <p class="text-xs font-bold text-novatext truncate">{{ $booking->formatted_amount }}</p>
                            <p class="text-[10px] {{ $statusColor }} font-semibold mt-0.5 truncate">{{ ucfirst($booking->booking_status_flag) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 lg:w-40 lg:flex-shrink-0">
                        <div class="w-7 h-7 lg:w-8 lg:h-8 rounded-full flex items-center justify-center text-[10px] lg:text-[11px] font-bold flex-shrink-0"
                             style="background:{{ $ttAvatar['bg'] }}; color:{{ $ttAvatar['text'] }}">{{ $ttInitials }}</div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-novatext truncate">{{ $booking->customer_name ?? 'N/A' }}</p>
                            <p class="text-[11px] text-novamuted truncate">{{ $booking->customer_email ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- Details — same plain layout as admin-nova/bookings
                         /_list.blade.php's Details column for every booking
                         type, flights included (no more dotted flight-path
                         visualization here, per explicit request). --}}
                    <div class="lg:flex-1 min-w-0 lg:px-2">
                        <p class="text-xs font-semibold text-novatext truncate">{{ Str::limit($serviceName, 28) }}</p>
                        <p class="text-[11px] text-novamuted truncate mt-0.5">{{ $serviceLocation }}</p>
                    </div>

                    {{-- Date/Stay, Passengers, Phone, and Partner — wraps
                         freely on mobile/tablet, one line from lg: up. --}}
                    <div class="flex flex-wrap items-start gap-x-4 gap-y-1.5 lg:flex-nowrap lg:gap-4">
                        <div class="w-20 lg:w-24 flex-shrink-0 lg:text-center">
                            <p class="text-[9px] text-novamuted">Date / Stay</p>
                            <p class="text-xs font-semibold text-novatext mt-0.5">{{ $booking->created_at->format('M j, Y') }}</p>
                        </div>
                        <div class="w-20 lg:w-16 flex-shrink-0 min-w-0">
                            <p class="text-[9px] text-novamuted">{{ $booking->booking_type === 'hotel' ? 'Guests' : 'Passengers' }}</p>
                            <p class="text-xs font-semibold text-novatext truncate mt-0.5">{{ $paxCount }}</p>
                        </div>
                        <div class="w-24 flex-shrink-0 min-w-0">
                            <p class="text-[9px] text-novamuted">Phone</p>
                            <p class="text-xs text-novatext truncate mt-0.5">{{ $phone }}</p>
                        </div>
                        <div class="w-20 flex-shrink-0 min-w-0">
                            <p class="text-[9px] text-novamuted">Partner</p>
                            <p class="text-xs text-novatext truncate mt-0.5">{{ ucfirst($booking->booking_supplier_name ?? 'Manual') }}</p>
                        </div>
                    </div>

                    <div class="hidden lg:block w-28 flex-shrink-0 text-right lg:ml-auto">
                        <p class="text-sm font-bold text-novatext truncate">{{ $booking->formatted_amount }}</p>
                        <p class="text-[11px] {{ $statusColor }} font-semibold mt-0.5 truncate">{{ ucfirst($booking->booking_status_flag) }}</p>
                        <p class="text-[10px] {{ $paymentColor }} font-medium">{{ ucfirst($booking->booking_payment_state) }}</p>
                    </div>

                    <div class="flex items-center gap-1 flex-shrink-0 self-end lg:self-auto">
                        <a href="{{ route('admin.bookings.edit', ['type' => $booking->booking_type, 'id' => $booking->id]) }}"
                           class="tt-btn w-7 h-7 lg:w-8 lg:h-8 rounded-full border border-novaborder flex items-center justify-center hover:bg-novabg" title="Edit booking">
                            <svg class="w-3 h-3 lg:w-3.5 lg:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </a>
                        <a href="{{ $invoiceRoute }}" target="_blank"
                           class="tt-btn w-7 h-7 lg:w-8 lg:h-8 rounded-full border border-novaborder flex items-center justify-center hover:bg-novabg" title="View invoice">
                            <svg class="w-3 h-3 lg:w-3.5 lg:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                        </a>
                        <button type="button"
                                class="tt-btn dash-delete-btn w-7 h-7 lg:w-8 lg:h-8 rounded-full border border-red-200 flex items-center justify-center text-novadanger hover:bg-red-50"
                                data-id="{{ $booking->id }}" data-type="{{ $booking->booking_type }}" data-ref="{{ $booking->booking_code_ref }}" title="Delete booking">
                            <svg class="w-3 h-3 lg:w-3.5 lg:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-14">
                    <svg class="w-8 h-8 text-novaborder mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                    <p class="text-sm text-novamuted">No recent bookings found</p>
                </div>
            @endforelse

            <div id="ttNoMatches" class="hidden text-center py-14">
                <svg class="w-8 h-8 text-novaborder mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <p class="text-sm text-novamuted">No bookings match these filters</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var root = document.getElementById('dashTT');
        if (!root) return;

        var state = { type: '', status: '', dateFrom: '', dateTo: '' };
        var rows = Array.prototype.slice.call(root.querySelectorAll('#ttBookingList .tt-row'));
        var noMatches = document.getElementById('ttNoMatches');
        var resetBtn = document.getElementById('ttFilterReset');

        function closeAllDropdowns(except) {
            root.querySelectorAll('.tt-dropdown.open').forEach(function (d) {
                if (d !== except) d.classList.remove('open');
            });
        }

        root.querySelectorAll('.tt-dd-trigger').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                var dd = trigger.closest('.tt-dropdown');
                var isOpen = dd.classList.contains('open');
                closeAllDropdowns();
                dd.classList.toggle('open', !isOpen);
            });
        });

        document.addEventListener('click', function () { closeAllDropdowns(); });

        function applyFilters() {
            var anyActive = state.type || state.status || state.dateFrom || state.dateTo;
            resetBtn.classList.toggle('hidden', !anyActive);

            var visibleCount = 0;
            rows.forEach(function (row) {
                var matches = true;
                if (state.type && row.dataset.type !== state.type) matches = false;
                if (state.status && row.dataset.status !== state.status) matches = false;
                if (matches && state.dateFrom && row.dataset.date < state.dateFrom) matches = false;
                if (matches && state.dateTo && row.dataset.date > state.dateTo) matches = false;
                row.classList.toggle('tt-hidden', !matches);
                if (matches) visibleCount++;
            });
            if (noMatches) noMatches.classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        // Booking Type / Status option pickers
        root.querySelectorAll('[data-dropdown="type"] .tt-dd-opt, [data-dropdown="status"] .tt-dd-opt').forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                var dd = opt.closest('.tt-dropdown');
                var key = dd.dataset.dropdown;
                var value = opt.dataset.value;
                var label = dd.querySelector('.tt-dd-label');
                var trigger = dd.querySelector('.tt-dd-trigger');

                state[key] = value;
                label.textContent = value ? opt.textContent : (key === 'type' ? 'Booking Type' : 'Status');
                trigger.classList.toggle('active', !!value);
                dd.classList.remove('open');
                applyFilters();
            });
        });

        // Date range popover
        var dateFromInput = document.getElementById('ttDateFrom');
        var dateToInput = document.getElementById('ttDateTo');
        var dateApply = document.getElementById('ttDateApply');
        var dateClear = document.getElementById('ttDateClear');
        var dateTrigger = root.querySelector('[data-dropdown="date"] .tt-dd-trigger');
        var dateLabel = root.querySelector('[data-dropdown="date"] .tt-dd-label');

        if (dateApply) {
            dateApply.addEventListener('click', function (e) {
                e.stopPropagation();
                state.dateFrom = dateFromInput.value || '';
                state.dateTo = dateToInput.value || '';
                var active = state.dateFrom || state.dateTo;
                dateLabel.textContent = active ? 'Date Range •' : 'Date Range';
                dateTrigger.classList.toggle('active', !!active);
                root.querySelector('[data-dropdown="date"]').classList.remove('open');
                applyFilters();
            });
        }
        if (dateClear) {
            dateClear.addEventListener('click', function (e) {
                e.stopPropagation();
                dateFromInput.value = '';
                dateToInput.value = '';
                state.dateFrom = '';
                state.dateTo = '';
                dateLabel.textContent = 'Date Range';
                dateTrigger.classList.remove('active');
                applyFilters();
            });
        }

        // Reset all
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                state = { type: '', status: '', dateFrom: '', dateTo: '' };
                root.querySelectorAll('.tt-dd-label').forEach(function (l) {
                    var dd = l.closest('.tt-dropdown');
                    l.textContent = dd.dataset.dropdown === 'type' ? 'Booking Type' : (dd.dataset.dropdown === 'status' ? 'Status' : 'Date Range');
                });
                root.querySelectorAll('.tt-dd-trigger').forEach(function (t) { t.classList.remove('active'); });
                if (dateFromInput) dateFromInput.value = '';
                if (dateToInput) dateToInput.value = '';
                applyFilters();
            });
        }

        // Prevent clicks inside a panel from closing it
        root.querySelectorAll('.tt-dropdown-panel').forEach(function (panel) {
            panel.addEventListener('click', function (e) { e.stopPropagation(); });
        });

        // Delete booking — same endpoint/payload as admin-nova/bookings
        // /_scripts.blade.php's single-delete handler.
        root.querySelectorAll('.dash-delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var id = this.dataset.id, type = this.dataset.type, ref = this.dataset.ref;
                if (confirm('Are you sure you want to delete booking #' + ref + '? This action cannot be undone.')) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ url("admin/bookings") }}/' + type + '/' + id;

                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    })();
</script>
@endpush
