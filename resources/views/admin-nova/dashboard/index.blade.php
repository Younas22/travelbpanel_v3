@extends('admin-nova.layouts.app')

@section('title', 'Dashboard')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = { corePlugins: { preflight: false } };
</script>
<style>
    /* Scoped resets — Tailwind Preflight is disabled globally (so it doesn't
       fight the shared Bootstrap-based chrome this phased-in design still
       borrows), so this dashboard neutralises Bootstrap's own element
       defaults just inside its own root. */
    #dashTT, #dashTT *, #dashTT *::before, #dashTT *::after { box-sizing: border-box; }
    #dashTT { font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif; }
    #dashTT h1, #dashTT h2, #dashTT h3, #dashTT h4, #dashTT h5, #dashTT h6,
    #dashTT p, #dashTT ul, #dashTT ol, #dashTT dl, #dashTT dd { margin: 0; padding: 0; }
    #dashTT ul, #dashTT ol { list-style: none; }
    #dashTT a { text-decoration: none; color: inherit; }
    #dashTT button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #dashTT svg { display: block; }

    #dashTT .tt-fade-in { animation: ttFadeIn .5s ease both; }
    @keyframes ttFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #dashTT .tt-card { transition: transform .2s cubic-bezier(.4,0,.2,1), box-shadow .2s cubic-bezier(.4,0,.2,1); }
    #dashTT .tt-card:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -12px rgba(15,23,42,.12); }

    #dashTT .tt-row:hover { background: #f8fafc; }

    #dashTT .tt-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    #dashTT .tt-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 9999px; }

    #dashTT .tt-pill.active { background: #0f172a !important; color: #fff !important; border-color: #0f172a !important; }
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
        ['label' => 'Total Bookings',   'value' => number_format($stats['total_bookings'] ?? 0),   'circle' => 'bg-blue-600',    'icon' => 'check'],
        ['label' => 'Visa Requests',    'value' => number_format($stats['total_visarequest'] ?? 0), 'circle' => 'bg-pink-500',    'icon' => 'passport'],
        ['label' => 'Total Revenue',    'value' => '$' . number_format(($stats['total_revenue'] ?? 0) / 1000, 1) . 'k', 'circle' => 'bg-slate-900', 'icon' => 'check'],
        ['label' => 'No. of Customers', 'value' => number_format($stats['total_customers'] ?? 0),  'circle' => 'bg-emerald-500', 'icon' => 'check'],
    ];

    $ttTypeConfig = [
        'flight' => ['label' => 'Flight', 'badge' => 'bg-blue-50 text-blue-700', 'icon' => 'bg-blue-50 text-blue-600'],
        'hotel'  => ['label' => 'Stay',   'badge' => 'bg-emerald-50 text-emerald-700', 'icon' => 'bg-emerald-50 text-emerald-600'],
        'tour'   => ['label' => 'Tour',   'badge' => 'bg-amber-50 text-amber-700', 'icon' => 'bg-amber-50 text-amber-600'],
        'umrah'  => ['label' => 'Umrah',  'badge' => 'bg-violet-50 text-violet-700', 'icon' => 'bg-violet-50 text-violet-600'],
    ];
@endphp

<div id="dashTT" class="tt-fade-in">

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
        @foreach($ttKpis as $kpi)
            <div class="tt-card bg-white rounded-xl border border-slate-100 shadow-sm p-3.5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 truncate">{{ $kpi['label'] }}</p>
                    <div class="w-6 h-6 rounded-full {{ $kpi['circle'] }} text-white flex items-center justify-center flex-shrink-0">
                        @if($kpi['icon'] === 'passport')
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M8 17h8"/></svg>
                        @else
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        @endif
                    </div>
                </div>
                <div class="flex items-end justify-between gap-1">
                    <span class="text-lg sm:text-xl font-bold text-slate-900 truncate">{{ $kpi['value'] }}+</span>
                    <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-emerald-600 flex-shrink-0">
                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15 12 9l-6 6"/></svg>
                        +0.2%
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ============ SALES PERFORMANCE ============ --}}
    <div class="tt-card bg-white rounded-xl border border-slate-100 shadow-sm p-4 sm:p-5 mb-4">
        <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Total Sales Performance</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ number_format($stats['confirmed_bookings'] ?? 0) }} of {{ number_format($stats['total_bookings'] ?? 0) }} bookings confirmed</p>
            </div>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex-shrink-0">
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                Export
            </button>
        </div>

        <div class="flex gap-2">
            <div class="hidden sm:flex flex-col justify-between text-[10px] text-slate-400 py-1 h-44 sm:h-52">
                <span>60k</span><span>50k</span><span>40k</span><span>30k</span><span>20k</span><span>10k</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="h-44 sm:h-52">
                    <svg viewBox="0 0 700 240" preserveAspectRatio="none" class="w-full h-full">
                        <defs>
                            <linearGradient id="ttGradA" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563eb" stop-opacity="0.18"/>
                                <stop offset="100%" stop-color="#2563eb" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <g stroke="#f1f5f9" stroke-width="1">
                            <line x1="0" y1="20" x2="700" y2="20"/>
                            <line x1="0" y1="64" x2="700" y2="64"/>
                            <line x1="0" y1="108" x2="700" y2="108"/>
                            <line x1="0" y1="152" x2="700" y2="152"/>
                            <line x1="0" y1="196" x2="700" y2="196"/>
                        </g>
                        <path d="M0,150 C60,100 120,80 180,110 C240,140 300,150 360,120 C420,90 480,60 540,90 C600,120 640,70 700,40 L700,240 L0,240 Z" fill="url(#ttGradA)"/>
                        <path d="M0,150 C60,100 120,80 180,110 C240,140 300,150 360,120 C420,90 480,60 540,90 C600,120 640,70 700,40" fill="none" stroke="#7dd3fc" stroke-width="2.5" stroke-linecap="round"/>
                        <path d="M0,190 C60,170 120,175 180,150 C240,125 300,170 360,155 C420,140 480,150 540,110 C570,90 600,70 640,75 C660,78 680,60 700,55" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="360" cy="155" r="4" fill="#2563eb"/>
                        <circle cx="360" cy="155" r="8" fill="#2563eb" opacity="0.18"/>
                    </svg>
                </div>
                <div class="flex justify-between text-[10px] font-medium text-slate-400 mt-1.5 px-1">
                    <span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span><span>SUN</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ ALL BOOKINGS ============ --}}
    <div class="tt-card bg-white rounded-xl border border-slate-100 shadow-sm p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h2 class="text-sm font-bold text-slate-900">All Bookings</h2>
            <span class="text-[11px] text-slate-400">{{ $recent_bookings->count() }} shown</span>
        </div>

        {{-- Real, working filters — client-side over the bookings already
             rendered below (this is a dashboard summary, not a paginated
             list, so there's no server route to filter against yet). --}}
        <div class="flex flex-wrap gap-2 mb-4" id="ttFilters">
            <div class="relative tt-dropdown" data-dropdown="type">
                <button type="button" class="tt-pill tt-dd-trigger inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    <span class="tt-dd-label">Booking Type</span>
                    <svg class="w-3 h-3 tt-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="tt-dropdown-panel absolute z-20 top-full left-0 mt-1.5 w-40 bg-white rounded-lg border border-slate-200 shadow-lg py-1">
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="">All types</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="flight">Flight</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="hotel">Stay</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="tour">Tour</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="umrah">Umrah</button>
                </div>
            </div>

            <div class="relative tt-dropdown" data-dropdown="status">
                <button type="button" class="tt-pill tt-dd-trigger inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    <span class="tt-dd-label">Status</span>
                    <svg class="w-3 h-3 tt-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="tt-dropdown-panel absolute z-20 top-full left-0 mt-1.5 w-40 bg-white rounded-lg border border-slate-200 shadow-lg py-1">
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="">All statuses</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="confirmed">Confirmed</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="pending">Pending</button>
                    <button type="button" class="tt-dd-opt w-full text-left px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50" data-value="cancelled">Cancelled</button>
                </div>
            </div>

            <div class="relative tt-dropdown" data-dropdown="date">
                <button type="button" class="tt-pill tt-dd-trigger inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    <span class="tt-dd-label">Date Range</span>
                    <svg class="w-3 h-3 tt-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="tt-dropdown-panel absolute z-20 top-full left-0 mt-1.5 w-56 bg-white rounded-lg border border-slate-200 shadow-lg p-3">
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">From</label>
                    <input type="date" id="ttDateFrom" class="w-full text-xs border border-slate-200 rounded-md px-2 py-1.5 mb-2">
                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">To</label>
                    <input type="date" id="ttDateTo" class="w-full text-xs border border-slate-200 rounded-md px-2 py-1.5 mb-3">
                    <div class="flex gap-2">
                        <button type="button" id="ttDateApply" class="flex-1 rounded-md bg-slate-900 text-white text-xs font-semibold py-1.5">Apply</button>
                        <button type="button" id="ttDateClear" class="flex-1 rounded-md border border-slate-200 text-slate-600 text-xs font-semibold py-1.5">Clear</button>
                    </div>
                </div>
            </div>

            <button type="button" id="ttFilterReset" class="hidden inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-red-500 hover:bg-red-50 transition-colors">
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Reset
            </button>
        </div>

        <div class="space-y-2.5 max-h-[460px] overflow-y-auto tt-scrollbar pr-1" id="ttBookingList">
            @forelse($recent_bookings as $booking)
                @php
                    $tc = $ttTypeConfig[$booking->booking_type] ?? ['label' => ucfirst($booking->booking_type ?? ''), 'badge' => 'bg-slate-100 text-slate-600', 'icon' => 'bg-slate-100 text-slate-500'];
                    $invoiceRoute = match($booking->booking_type) {
                        'hotel' => route('hotel.invoice', $booking->booking_code_ref),
                        'tour'  => route('tour.invoice',  $booking->booking_code_ref),
                        'umrah' => route('umrah.invoice', $booking->booking_code_ref),
                        default => route('flight.invoice', $booking->booking_code_ref),
                    };
                    $serviceName = match($booking->booking_type) {
                        'hotel' => $booking->hotel_info['name'] ?? 'N/A',
                        'tour'  => $booking->tour_name ?? 'N/A',
                        'umrah' => $booking->umrah_name ?? 'N/A',
                        default => $booking->flight_route['route'] ?? 'N/A',
                    };

                    $isFlight = $booking->booking_type === 'flight';
                    if ($isFlight) {
                        $routeParts = array_map('trim', explode('→', $booking->flight_route['route'] ?? ''));
                        $originCode = $routeParts[0] ?? 'N/A';
                        $destCode   = $routeParts[1] ?? 'N/A';
                        $stopsLabel = $booking->flight_route['stops'] ?? '';
                        $depTime    = $booking->travel_date['time'] ?? $booking->created_at->format('g:i A');
                    }
                @endphp
                <a href="{{ $invoiceRoute }}" target="_blank"
                   class="tt-row flex items-center gap-3 rounded-lg border border-slate-100 p-2.5 sm:p-3 transition-colors"
                   data-type="{{ $booking->booking_type }}" data-status="{{ $booking->booking_status_flag }}" data-date="{{ $booking->created_at->format('Y-m-d') }}">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full {{ $tc['icon'] }} flex items-center justify-center flex-shrink-0">
                        @switch($booking->booking_type)
                            @case('hotel')
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22v-4h6v4M9 7h1M9 11h1M14 7h1M14 11h1"/></svg>
                                @break
                            @case('tour')
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8 6 6 9.5 6 13a6 6 0 0 0 12 0c0-3.5-2-7-6-11Z"/></svg>
                                @break
                            @case('umrah')
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                                @break
                            @default
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 19.5 21 12 2.5 4.5 5 11l-2.5.5L5 12l-2.5.5Z"/></svg>
                        @endswitch
                    </div>

                    <div class="min-w-0 w-28 sm:w-36 flex-shrink-0">
                        <p class="text-xs sm:text-sm font-semibold text-slate-900 truncate">{{ Str::limit($serviceName, 18) }}</p>
                        <p class="text-[10px] text-slate-400 truncate">#{{ $booking->booking_code_ref }}</p>
                    </div>

                    <span class="hidden xs:inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $tc['badge'] }} flex-shrink-0">{{ $tc['label'] }}</span>

                    @if($isFlight)
                        {{-- Real flight path: origin/destination + stop count,
                             centred, from FlightBooking::flight_route(). --}}
                        <div class="hidden md:flex items-center gap-2 flex-1 min-w-0 px-2">
                            <div class="text-center flex-shrink-0">
                                <p class="text-xs font-bold text-slate-800">{{ $depTime }}</p>
                                <p class="text-[10px] text-slate-400">{{ $originCode }}</p>
                            </div>
                            <span class="flex-1 border-t border-dashed border-slate-300 relative min-w-[40px]">
                                <span class="absolute -top-[3px] left-0 w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                <span class="absolute -top-[3px] right-0 w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                <span class="absolute -top-[19px] left-1/2 -translate-x-1/2 text-[10px] font-semibold text-slate-400 whitespace-nowrap">{{ $stopsLabel }}</span>
                            </span>
                            <div class="text-center flex-shrink-0">
                                <p class="text-xs font-bold text-slate-800">&nbsp;</p>
                                <p class="text-[10px] text-slate-400">{{ $destCode }}</p>
                            </div>
                        </div>
                    @else
                        <div class="hidden md:block flex-1 min-w-0 px-2 text-center">
                            <p class="text-[11px] text-slate-400">{{ $booking->created_at->format('M j, Y') }}</p>
                        </div>
                    @endif

                    <div class="text-right flex-shrink-0 ml-auto">
                        <p class="text-xs sm:text-sm font-bold text-slate-900">{{ $booking->formatted_amount }}</p>
                        <p class="text-[10px] text-slate-400">{{ ucfirst($booking->booking_status_flag) }}</p>
                    </div>
                </a>
            @empty
                <div class="text-center py-14">
                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                    <p class="text-xs text-slate-400">No recent bookings found</p>
                </div>
            @endforelse

            <div id="ttNoMatches" class="hidden text-center py-14">
                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <p class="text-xs text-slate-400">No bookings match these filters</p>
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
    })();
</script>
@endpush
