@extends('admin-nova.layouts.app')

@section('title', 'Dashboard')

@php
    $cur = $dash['base_currency'];
    $money = fn ($v) => $cur . ' ' . number_format((float) $v);

    // Smooth SVG path through points (Catmull-Rom → cubic Bézier).
    $smooth = function (array $pts) {
        if (!$pts) return '';
        if (count($pts) === 1) return 'M' . $pts[0][0] . ',' . $pts[0][1];
        $d = 'M' . round($pts[0][0], 1) . ',' . round($pts[0][1], 1);
        for ($i = 0; $i < count($pts) - 1; $i++) {
            $p0 = $pts[max(0, $i - 1)]; $p1 = $pts[$i]; $p2 = $pts[$i + 1]; $p3 = $pts[min(count($pts) - 1, $i + 2)];
            $c1 = [$p1[0] + ($p2[0] - $p0[0]) / 6, $p1[1] + ($p2[1] - $p0[1]) / 6];
            $c2 = [$p2[0] - ($p3[0] - $p1[0]) / 6, $p2[1] - ($p3[1] - $p1[1]) / 6];
            $d .= sprintf(' C%.1f,%.1f %.1f,%.1f %.1f,%.1f', $c1[0], $c1[1], $c2[0], $c2[1], $p2[0], $p2[1]);
        }
        return $d;
    };
    $scalePts = function (array $values, float $w, float $h, float $pad = 4) {
        $n = count($values); $max = max($values ?: [0]) ?: 1;
        return array_map(fn ($v, $i) => [$n > 1 ? $i * $w / ($n - 1) : $w / 2, $h - $pad - ($v / $max) * ($h - 2 * $pad)], $values, array_keys($values));
    };
    $niceMax = function ($v) {
        if ($v <= 0) return 1;
        $p = pow(10, floor(log10($v))); $n = $v / $p;
        return ($n <= 1 ? 1 : ($n <= 2 ? 2 : ($n <= 5 ? 5 : 10))) * $p;
    };
    $short = fn ($v) => $v >= 1000000 ? round($v / 1000000, 1) . 'M' : ($v >= 1000 ? round($v / 1000, 1) . 'K' : (string) round($v));

    // Donut segments (stroke-dasharray on r=15.9155 → circumference 100).
    $donut = function (array $items) {
        $total = array_sum(array_column($items, 'count')); $offset = 25; $out = [];
        foreach ($items as $it) {
            if (!$total || !$it['count']) continue;
            $pct = $it['count'] / $total * 100;
            $out[] = ['color' => $it['color'], 'dash' => max(0, $pct - 0.6) . ' ' . (100 - max(0, $pct - 0.6)), 'offset' => $offset];
            $offset -= $pct;
        }
        return $out;
    };

    $series = $dash['series'];
    $revMax = $niceMax(max(array_column($series, 'revenue') ?: [0]));
    $bkMax = $niceMax(max(array_column($series, 'bookings') ?: [0]));
    $W = 700; $H = 220;
    $revPts = array_map(fn ($s, $i) => [count($series) > 1 ? $i * $W / (count($series) - 1) : $W / 2, $H - ($s['revenue'] / $revMax) * $H], $series, array_keys($series));
    $bkPts = array_map(fn ($s, $i) => [count($series) > 1 ? $i * $W / (count($series) - 1) : $W / 2, $H - ($s['bookings'] / $bkMax) * $H], $series, array_keys($series));
    $labelStep = max(1, (int) ceil(count($series) / 7));

    $serviceColors = ['flight' => '#2563EB', 'hotel' => '#3B82F6', 'tour' => '#60A5FA', 'umrah' => '#93C5FD', 'visa' => '#1E40AF'];
    $serviceIcons = ['flight' => 'bi-airplane', 'hotel' => 'bi-building', 'tour' => 'bi-compass', 'umrah' => 'bi-moon', 'visa' => 'bi-file-earmark-text'];
    $serviceItems = [];
    foreach ($dash['by_service'] as $k => $s) $serviceItems[$k] = $s + ['color' => $serviceColors[$k] ?? '#BFDBFE'];

    $statusColors = ['confirmed' => '#16A34A', 'pending' => '#F59E0B', 'cancelled' => '#EF4444', 'failed' => '#B91C1C', 'on_hold' => '#94A3B8'];
    $statusItems = [];
    foreach ($dash['by_status'] as $k => $s) $statusItems[$k] = $s + ['color' => $statusColors[$k] ?? '#64748B'];

    $revenueRows = array_filter($dash['by_service'], fn ($s) => $s['revenue'] !== null);
    $revenueTop = max(array_column($revenueRows, 'revenue') ?: [0]) ?: 1;
    $destTop = max(array_column($dash['destinations'], 'count') ?: [0]) ?: 1;

    $rangeQuery = fn ($r) => request()->fullUrlWithQuery(['range' => $r, 'from' => null, 'to' => null]);
    $openMessages = \App\Models\ContactMessage::whereNotIn('status', ['read', 'replied'])->count();

    $typeConfig = [
        'flight' => ['label' => 'Flight', 'icon' => 'bi-airplane', 'tab' => 'Flights'],
        'hotel'  => ['label' => 'Stay',   'icon' => 'bi-building', 'tab' => 'Hotels'],
        'tour'   => ['label' => 'Tour',   'icon' => 'bi-compass',  'tab' => 'Tours'],
        'umrah'  => ['label' => 'Umrah',  'icon' => 'bi-moon',     'tab' => 'Umrah'],
    ];
    $avatarPalette = [['#DBEAFE', '#1D4ED8'], ['#DCFCE7', '#15803D'], ['#FEF3C7', '#B45309'], ['#FCE7F3', '#BE185D'], ['#EDE9FE', '#6D28D9']];
@endphp

@push('header-actions')
    <div class="tbp-dashboard-header-actions">
        <div class="dropdown">
            <button type="button" class="tbp-dashboard-range-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="bi bi-calendar3"></i>
                <span>{{ $dash['from']->format('M j, Y') }} – {{ $dash['to']->format('M j, Y') }}</span>
                <i class="bi bi-chevron-down"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end tbp-dashboard-range-menu">
                @foreach($dash['presets'] as $key => $label)
                    <a class="tbp-dashboard-range-opt {{ $dash['range'] === (string) $key ? 'is-active' : '' }}" href="{{ $rangeQuery($key) }}">{{ $label }}</a>
                @endforeach
                <form method="GET" action="{{ url()->current() }}" class="tbp-dashboard-range-custom">
                    <div class="tbp-dashboard-range-custom-title">Custom range</div>
                    <input type="date" name="from" value="{{ $dash['from']->format('Y-m-d') }}" required>
                    <input type="date" name="to" value="{{ $dash['to']->format('Y-m-d') }}" required>
                    <button type="submit">Apply</button>
                </form>
            </div>
        </div>
        <a href="{{ route('admin.contact-messages.index') }}" class="tbp-dashboard-bell" title="Contact messages">
            <i class="bi bi-bell"></i>
            @if($openMessages)<span class="tbp-dashboard-bell-dot">{{ $openMessages }}</span>@endif
        </a>
    </div>
@endpush

@section('content')
<div class="tbp-dashboard-page" id="tbpDashboard">

    {{-- ============ KPI CARDS ============ --}}
    <div class="tbp-dashboard-kpi-grid">
        @foreach($dash['kpis'] as $kpi)
            @php $sp = $scalePts($kpi['spark'], 96, 36); $chg = $kpi['change']; @endphp
            <div class="tbp-dashboard-kpi-card">
                <div class="tbp-dashboard-kpi-icon"><i class="bi {{ $kpi['icon'] }}"></i></div>
                <div class="tbp-dashboard-kpi-body">
                    <div class="tbp-dashboard-kpi-label">{{ $kpi['label'] }}</div>
                    <div class="tbp-dashboard-kpi-value">{{ $kpi['value'] }}</div>
                    <div class="tbp-dashboard-kpi-change {{ $chg === null || $chg > 0 ? 'is-up' : ($chg < 0 ? 'is-down' : 'is-flat') }}"
                         title="Compared with the previous {{ $dash['from']->diffInDays($dash['to']) + 1 }} days">
                        @if($chg === null)
                            <i class="bi bi-arrow-up"></i> New
                        @elseif($chg == 0)
                            <i class="bi bi-dash"></i> 0%
                        @else
                            <i class="bi {{ $chg > 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i> {{ abs($chg) }}%
                        @endif
                        @isset($kpi['note'])<span class="tbp-dashboard-kpi-note">· {{ $kpi['note'] }}</span>@endisset
                    </div>
                </div>
                <svg class="tbp-dashboard-sparkline" viewBox="0 0 96 36" preserveAspectRatio="none" aria-hidden="true">
                    <path d="{{ $smooth($sp) }} L96,36 L0,36 Z" class="tbp-dashboard-sparkline-area"/>
                    <path d="{{ $smooth($sp) }}" class="tbp-dashboard-sparkline-line"/>
                </svg>
            </div>
        @endforeach
    </div>

    {{-- ============ SALES + BOOKINGS BY SERVICE ============ --}}
    <div class="tbp-dashboard-analytics-grid tbp-dashboard-analytics-top">
        <div class="tbp-dashboard-chart-card tbp-dashboard-sales">
            <div class="tbp-dashboard-card-head">
                <div>
                    <h3 class="tbp-dashboard-card-title">Total Sales Performance</h3>
                    <p class="tbp-dashboard-card-sub">Booking revenue ({{ $cur }}, confirmed) and bookings over time</p>
                </div>
                @include('admin-nova.dashboard._range-select')
            </div>
            <div class="tbp-dashboard-legend">
                <span><i style="background:#2563EB"></i> Bookings</span>
                <span><i style="background:#38BDF8"></i> Revenue</span>
            </div>
            <div class="tbp-dashboard-line-chart">
                <div class="tbp-dashboard-axis tbp-dashboard-axis-left">
                    @for($i = 4; $i >= 0; $i--)<span>{{ $short($revMax * $i / 4) }}</span>@endfor
                </div>
                <div class="tbp-dashboard-plot" id="tbpSalesPlot">
                    <svg viewBox="0 0 {{ $W }} {{ $H }}" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="tbpBkFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563EB" stop-opacity="0.14"/>
                                <stop offset="100%" stop-color="#2563EB" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        @for($i = 0; $i <= 4; $i++)<line x1="0" x2="{{ $W }}" y1="{{ $H * $i / 4 }}" y2="{{ $H * $i / 4 }}" class="tbp-dashboard-grid-line"/>@endfor
                        <path d="{{ $smooth($bkPts) }} L{{ $W }},{{ $H }} L0,{{ $H }} Z" fill="url(#tbpBkFill)"/>
                        <path d="{{ $smooth($revPts) }}" class="tbp-dashboard-line tbp-dashboard-line-revenue"/>
                        <path d="{{ $smooth($bkPts) }}" class="tbp-dashboard-line tbp-dashboard-line-bookings"/>
                        <line id="tbpSalesCursor" x1="0" x2="0" y1="0" y2="{{ $H }}" class="tbp-dashboard-cursor" visibility="hidden"/>
                    </svg>
                    @foreach($series as $i => $s)
                        <span class="tbp-dashboard-dot tbp-dashboard-dot-revenue" style="left:{{ $revPts[$i][0] / $W * 100 }}%;top:{{ $revPts[$i][1] / $H * 100 }}%" data-i="{{ $i }}"></span>
                        <span class="tbp-dashboard-dot tbp-dashboard-dot-bookings" style="left:{{ $bkPts[$i][0] / $W * 100 }}%;top:{{ $bkPts[$i][1] / $H * 100 }}%" data-i="{{ $i }}"></span>
                    @endforeach
                    <div class="tbp-dashboard-tooltip" id="tbpSalesTip" hidden></div>
                </div>
                <div class="tbp-dashboard-axis tbp-dashboard-axis-right">
                    @for($i = 4; $i >= 0; $i--)<span>{{ $short($bkMax * $i / 4) }}</span>@endfor
                </div>
            </div>
            <div class="tbp-dashboard-xlabels">
                @foreach($series as $i => $s)
                    @if($i % $labelStep === 0 || $i === count($series) - 1)
                        <span style="left:{{ count($series) > 1 ? $i / (count($series) - 1) * 100 : 50 }}%">{{ $s['label'] }}</span>
                    @endif
                @endforeach
            </div>
            <div class="tbp-dashboard-axis-caption"><span>Revenue ({{ $cur }})</span><span>Bookings</span></div>
        </div>

        <div class="tbp-dashboard-chart-card tbp-dashboard-service-chart">
            <div class="tbp-dashboard-card-head">
                <h3 class="tbp-dashboard-card-title">Bookings by Service</h3>
                @include('admin-nova.dashboard._range-select')
            </div>
            <div class="tbp-dashboard-donut-wrap">
                <div class="tbp-dashboard-donut">
                    <svg viewBox="0 0 42 42">
                        <circle cx="21" cy="21" r="15.9155" class="tbp-dashboard-donut-track"/>
                        @foreach($donut($serviceItems) as $seg)
                            <circle cx="21" cy="21" r="15.9155" fill="none" stroke="{{ $seg['color'] }}" stroke-width="6" stroke-dasharray="{{ $seg['dash'] }}" stroke-dashoffset="{{ $seg['offset'] }}"/>
                        @endforeach
                    </svg>
                    <div class="tbp-dashboard-donut-center"><strong>{{ number_format($dash['service_total']) }}</strong><span>Total Bookings</span></div>
                </div>
                <ul class="tbp-dashboard-donut-legend">
                    @foreach($serviceItems as $k => $s)
                        <li>
                            <i style="background:{{ $s['color'] }}"></i>
                            <span class="tbp-dashboard-legend-icon"><i class="bi {{ $serviceIcons[$k] }}"></i></span>
                            <span class="tbp-dashboard-legend-name">{{ $s['label'] }}</span>
                            <strong>{{ $s['count'] }}</strong>
                            <span class="tbp-dashboard-legend-pct">({{ $dash['service_total'] ? round($s['count'] / $dash['service_total'] * 100, 1) : 0 }}%)</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- ============ REVENUE / DESTINATIONS / STATUS ============ --}}
    <div class="tbp-dashboard-analytics-grid tbp-dashboard-analytics-bottom">
        <div class="tbp-dashboard-chart-card">
            <div class="tbp-dashboard-card-head">
                <h3 class="tbp-dashboard-card-title">Revenue by Service</h3>
                @include('admin-nova.dashboard._range-select')
            </div>
            <ul class="tbp-dashboard-bars">
                @foreach($revenueRows as $k => $s)
                    <li>
                        <span class="tbp-dashboard-legend-icon"><i class="bi {{ $serviceIcons[$k] }}"></i></span>
                        <span class="tbp-dashboard-bars-name">{{ $s['label'] }}</span>
                        <span class="tbp-dashboard-bar"><span style="width:{{ $s['revenue'] / $revenueTop * 100 }}%"></span></span>
                        <strong>{{ $money($s['revenue']) }}</strong>
                    </li>
                @endforeach
            </ul>
            <p class="tbp-dashboard-card-foot">Confirmed bookings, converted to {{ $cur }}. Visa fees aren't included.</p>
        </div>

        <div class="tbp-dashboard-chart-card tbp-dashboard-destination-card">
            <div class="tbp-dashboard-card-head">
                <h3 class="tbp-dashboard-card-title">Top Destinations</h3>
                @include('admin-nova.dashboard._range-select')
            </div>
            @forelse($dash['destinations'] as $i => $d)
                <div class="tbp-dashboard-dest">
                    <span class="tbp-dashboard-dest-rank">{{ $i + 1 }}</span>
                    @if($d['iso2'])<span class="fi fi-{{ $d['iso2'] }} tbp-dashboard-dest-flag"></span>@else<span class="tbp-dashboard-dest-flag"></span>@endif
                    <span class="tbp-dashboard-dest-name">{{ $d['city'] }} ({{ $d['code'] }})</span>
                    <span class="tbp-dashboard-bar"><span style="width:{{ $d['count'] / $destTop * 100 }}%"></span></span>
                    <span class="tbp-dashboard-dest-count">{{ $d['count'] }} {{ Str::plural('Booking', $d['count']) }}</span>
                </div>
            @empty
                <div class="tbp-dashboard-empty">No flight or Umrah destinations in this period.</div>
            @endforelse
        </div>

        <div class="tbp-dashboard-chart-card tbp-dashboard-booking-status">
            <div class="tbp-dashboard-card-head">
                <h3 class="tbp-dashboard-card-title">Booking Status</h3>
                @include('admin-nova.dashboard._range-select')
            </div>
            <div class="tbp-dashboard-donut-wrap">
                <div class="tbp-dashboard-donut">
                    <svg viewBox="0 0 42 42">
                        <circle cx="21" cy="21" r="15.9155" class="tbp-dashboard-donut-track"/>
                        @foreach($donut($statusItems) as $seg)
                            <circle cx="21" cy="21" r="15.9155" fill="none" stroke="{{ $seg['color'] }}" stroke-width="6" stroke-dasharray="{{ $seg['dash'] }}" stroke-dashoffset="{{ $seg['offset'] }}"/>
                        @endforeach
                    </svg>
                    <div class="tbp-dashboard-donut-center"><strong>{{ number_format($dash['status_total']) }}</strong><span>Total Bookings</span></div>
                </div>
                <ul class="tbp-dashboard-donut-legend">
                    @foreach($statusItems as $s)
                        <li>
                            <i style="background:{{ $s['color'] }}"></i>
                            <span class="tbp-dashboard-legend-name">{{ $s['label'] }}</span>
                            <strong>{{ $s['count'] }}</strong>
                            <span class="tbp-dashboard-legend-pct">({{ $dash['status_total'] ? round($s['count'] / $dash['status_total'] * 100, 1) : 0 }}%)</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- ============ RECENT BOOKINGS ============ --}}
    <div class="tbp-dashboard-chart-card tbp-dashboard-recent-bookings">
        <div class="tbp-dashboard-card-head">
            <h3 class="tbp-dashboard-card-title">Recent Bookings</h3>
            <a href="{{ route('admin.bookings.all') }}" class="tbp-dashboard-primary-btn">View All Bookings <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="tbp-dashboard-booking-tabs" role="tablist">
            <button type="button" class="is-active" data-type="">All Bookings <span class="tbp-dashboard-tab-count" id="tbpShownCount">{{ $recent_bookings->count() }} shown</span></button>
            @foreach($typeConfig as $type => $tc)
                <button type="button" data-type="{{ $type }}"><i class="bi {{ $tc['icon'] }}"></i> {{ $tc['tab'] }}</button>
            @endforeach
        </div>

        <div class="tbp-dashboard-filters">
            <label class="tbp-dashboard-filter"><i class="bi bi-funnel"></i>
                <select id="tbpFilterType">
                    <option value="">Booking Type</option>
                    @foreach($typeConfig as $type => $tc)<option value="{{ $type }}">{{ $tc['label'] }}</option>@endforeach
                </select>
            </label>
            <label class="tbp-dashboard-filter"><i class="bi bi-check2-circle"></i>
                <select id="tbpFilterStatus">
                    <option value="">Status</option>
                    @foreach($recent_bookings->pluck('booking_status_flag')->filter()->unique()->sort() as $st)
                        <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="dropdown">
                <button type="button" class="tbp-dashboard-filter" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="bi bi-calendar3"></i> <span id="tbpDateLabel">Date Range</span> <i class="bi bi-chevron-down"></i></button>
                <div class="dropdown-menu tbp-dashboard-range-menu">
                    <div class="tbp-dashboard-range-custom">
                        <input type="date" id="tbpDateFrom">
                        <input type="date" id="tbpDateTo">
                        <button type="button" id="tbpDateApply">Apply</button>
                        <button type="button" id="tbpDateClear" class="is-ghost">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="tbp-dashboard-table-wrap">
            <table class="tbp-dashboard-booking-table">
                <thead>
                    <tr><th>Booking</th><th>Customer</th><th>Details</th><th>Date / Stay</th><th>Pax</th><th>Phone</th><th>Partner</th><th>Amount / Status</th><th>Actions</th></tr>
                </thead>
                <tbody id="tbpBookingRows">
                    @forelse($recent_bookings as $booking)
                        @php
                            $tc = $typeConfig[$booking->booking_type] ?? ['label' => ucfirst($booking->booking_type), 'icon' => 'bi-bookmark'];
                            $invoiceRoute = match($booking->booking_type) {
                                'hotel' => route('hotel.invoice', $booking->booking_code_ref),
                                'tour'  => route('tour.invoice', $booking->booking_code_ref),
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
                                'umrah' => $booking->umrah_info['location'] ?? 'N/A',
                                default => $booking->flight_route['stops'] ?? 'N/A',
                            };
                            $status = $booking->booking_status_flag;
                            $statusCls = $status === 'confirmed' ? 'is-success' : ($status === 'cancelled' || $status === 'failed' ? 'is-danger' : 'is-warning');
                            $payCls = $booking->booking_payment_state === 'paid' ? 'is-success' : ($booking->booking_payment_state === 'refunded' ? 'is-info' : 'is-warning');
                            $paxCount = $booking->booking_type === 'hotel' ? ($booking->guest_count ?? 'N/A') : ($booking->passenger_count ?? 'N/A');
                            $userData = is_string($booking->booking_user_data) ? json_decode($booking->booking_user_data, true) : $booking->booking_user_data;
                            $phone = (is_array($userData) ? ($userData['user_phone'] ?? null) : ($userData->user_phone ?? null)) ?: 'No number';
                            $initials = collect(explode(' ', $booking->customer_name ?? ''))->filter()->map(fn ($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
                            [$avBg, $avFg] = $avatarPalette[crc32($booking->customer_name ?? 'guest') % count($avatarPalette)];
                        @endphp
                        <tr data-type="{{ $booking->booking_type }}" data-status="{{ $status }}" data-date="{{ $booking->created_at->format('Y-m-d') }}">
                            <td>
                                <a href="{{ $invoiceRoute }}" target="_blank" class="tbp-dashboard-booking-cell">
                                    <span class="tbp-dashboard-type-icon"><i class="bi {{ $tc['icon'] }}"></i></span>
                                    <span class="tbp-dashboard-cell-text">
                                        <strong title="{{ $serviceName }}">{{ Str::limit($serviceName, 22) }}</strong>
                                        <small>#{{ $booking->booking_code_ref }}</small>
                                        <span class="tbp-dashboard-badge">{{ $tc['label'] }}</span>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <div class="tbp-dashboard-customer">
                                    <span class="tbp-dashboard-avatar" style="background:{{ $avBg }};color:{{ $avFg }}">{{ $initials }}</span>
                                    <span class="tbp-dashboard-cell-text"><strong>{{ $booking->customer_name ?? 'N/A' }}</strong><small>{{ $booking->customer_email ?? 'N/A' }}</small></span>
                                </div>
                            </td>
                            <td><span class="tbp-dashboard-cell-text"><strong title="{{ $serviceName }}">{{ Str::limit($serviceName, 30) }}</strong><small>{{ Str::limit($serviceLocation, 32) }}</small></span></td>
                            <td class="tbp-dashboard-nowrap">{{ $booking->created_at->format('M j, Y') }}</td>
                            <td class="tbp-dashboard-nowrap">{{ $paxCount }}</td>
                            <td class="tbp-dashboard-nowrap tbp-dashboard-muted">{{ $phone }}</td>
                            <td class="tbp-dashboard-nowrap">{{ ucfirst($booking->booking_supplier_name ?? 'Manual') }}</td>
                            <td class="tbp-dashboard-nowrap">
                                <strong class="tbp-dashboard-amount">{{ $booking->formatted_amount }}</strong>
                                <span class="tbp-dashboard-status {{ $statusCls }}">{{ ucfirst($status) }}</span>
                                <span class="tbp-dashboard-status {{ $payCls }}">{{ ucfirst($booking->booking_payment_state) }}</span>
                            </td>
                            <td>
                                <div class="tbp-dashboard-actions">
                                    <a href="{{ $invoiceRoute }}" target="_blank" title="View invoice"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.bookings.edit', ['type' => $booking->booking_type, 'id' => $booking->id]) }}" title="Edit booking"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="is-danger tbp-dashboard-delete" data-id="{{ $booking->id }}" data-type="{{ $booking->booking_type }}" data-ref="{{ $booking->booking_code_ref }}" title="Delete booking"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="tbp-dashboard-empty">No recent bookings found.</td></tr>
                    @endforelse
                    <tr id="tbpNoMatches" hidden><td colspan="9" class="tbp-dashboard-empty">No bookings match these filters.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('public/assets/libs/flag-icons/css/flag-icons.min.css') }}">
@endpush

@push('scripts')
<script>
(function () {
    const root = document.getElementById('tbpDashboard');
    if (!root) return;

    // ---- Sales chart tooltip ----
    const series = @json($series);
    const cur = @json($cur);
    const plot = document.getElementById('tbpSalesPlot');
    const tip = document.getElementById('tbpSalesTip');
    const cursor = document.getElementById('tbpSalesCursor');
    const W = {{ $W }};
    if (plot && series.length) {
        const show = i => {
            const s = series[i];
            const x = series.length > 1 ? i / (series.length - 1) : 0.5;
            cursor.setAttribute('x1', x * W); cursor.setAttribute('x2', x * W); cursor.setAttribute('visibility', 'visible');
            plot.querySelectorAll('.tbp-dashboard-dot').forEach(d => d.classList.toggle('is-active', +d.dataset.i === i));
            tip.innerHTML = `<div class="tbp-dashboard-tooltip-date">${s.full}</div>
                <div><i style="background:#2563EB"></i> Bookings <strong>${s.bookings}</strong></div>
                <div><i style="background:#38BDF8"></i> Revenue <strong>${cur} ${Math.round(s.revenue).toLocaleString()}</strong></div>`;
            tip.hidden = false;
            const left = x * plot.clientWidth;
            tip.style.left = Math.min(Math.max(left, 80), plot.clientWidth - 80) + 'px';
        };
        plot.addEventListener('mousemove', e => {
            const r = plot.getBoundingClientRect();
            const i = Math.round(((e.clientX - r.left) / r.width) * (series.length - 1));
            show(Math.max(0, Math.min(series.length - 1, i)));
        });
        plot.addEventListener('mouseleave', () => {
            tip.hidden = true; cursor.setAttribute('visibility', 'hidden');
            plot.querySelectorAll('.tbp-dashboard-dot.is-active').forEach(d => d.classList.remove('is-active'));
        });
    }

    // ---- Range selects on the cards ----
    root.querySelectorAll('.tbp-dashboard-range-select').forEach(sel => sel.addEventListener('change', () => { window.location = sel.value; }));

    // ---- Recent bookings: tabs + filters (client-side over the rows shown) ----
    const state = { type: '', status: '', from: '', to: '' };
    const rows = Array.from(root.querySelectorAll('#tbpBookingRows tr[data-type]'));
    const tabs = Array.from(root.querySelectorAll('.tbp-dashboard-booking-tabs button'));
    const typeSel = document.getElementById('tbpFilterType');
    function apply() {
        let visible = 0;
        rows.forEach(r => {
            const ok = (!state.type || r.dataset.type === state.type)
                && (!state.status || r.dataset.status === state.status)
                && (!state.from || r.dataset.date >= state.from)
                && (!state.to || r.dataset.date <= state.to);
            r.hidden = !ok;
            if (ok) visible++;
        });
        document.getElementById('tbpNoMatches').hidden = visible > 0 || !rows.length;
        document.getElementById('tbpShownCount').textContent = visible + ' shown';
        tabs.forEach(t => t.classList.toggle('is-active', t.dataset.type === state.type));
        typeSel.value = state.type;
    }
    tabs.forEach(t => t.addEventListener('click', () => { state.type = t.dataset.type; apply(); }));
    typeSel.addEventListener('change', () => { state.type = typeSel.value; apply(); });
    document.getElementById('tbpFilterStatus').addEventListener('change', e => { state.status = e.target.value; apply(); });
    document.getElementById('tbpDateApply').addEventListener('click', () => {
        state.from = document.getElementById('tbpDateFrom').value;
        state.to = document.getElementById('tbpDateTo').value;
        document.getElementById('tbpDateLabel').textContent = state.from || state.to ? `${state.from || '…'} – ${state.to || '…'}` : 'Date Range';
        apply();
    });
    document.getElementById('tbpDateClear').addEventListener('click', () => {
        document.getElementById('tbpDateFrom').value = ''; document.getElementById('tbpDateTo').value = '';
        state.from = state.to = '';
        document.getElementById('tbpDateLabel').textContent = 'Date Range';
        apply();
    });

    // ---- Delete booking (same endpoint as the bookings pages) ----
    root.querySelectorAll('.tbp-dashboard-delete').forEach(btn => btn.addEventListener('click', () => {
        if (!confirm(`Are you sure you want to delete booking #${btn.dataset.ref}? This action cannot be undone.`)) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/bookings') }}/${btn.dataset.type}/${btn.dataset.id}`;
        form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}"><input type="hidden" name="_method" value="DELETE">`;
        document.body.appendChild(form);
        form.submit();
    }));
})();
</script>
@endpush
