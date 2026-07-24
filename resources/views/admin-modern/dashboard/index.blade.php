@extends('admin-modern.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h2 class="mb-1">Dashboard</h2>
        <p class="text-muted mb-0">Here's what's happening across your platform today.</p>
    </div>

    {{-- ===== STATS ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="flex-1">
                        <div class="stat-label">Total Bookings</div>
                        <div class="stat-value">{{ number_format($stats['total_bookings']) }}</div>
                        <div class="stat-sub text-success">
                            <i class="bi bi-arrow-up-short"></i>
                            {{ ($stats['total_bookings'] ?? 0) > 0 ? number_format(($stats['confirmed_bookings'] / $stats['total_bookings']) * 100, 1) : 0 }}% confirmed
                        </div>
                    </div>
                    <div class="stat-icon icon-blue"><i class="bi bi-calendar-check"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="flex-1">
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">{{ number_format($stats['total_revenue'], 0) }}</div>
                        <div class="stat-sub text-success">
                            <i class="bi bi-arrow-up-short"></i> from confirmed bookings
                        </div>
                    </div>
                    <div class="stat-icon icon-green"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="flex-1">
                        <div class="stat-label">Visa Requests</div>
                        <div class="stat-value">{{ number_format($stats['total_visarequest']) }}</div>
                        <div class="stat-sub text-success">
                            <i class="bi bi-arrow-up-short"></i> {{ number_format($stats['total_visarequest']) }} total
                        </div>
                    </div>
                    <div class="stat-icon icon-amber"><i class="bi bi-passport"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="flex-1">
                        <div class="stat-label">Newsletter Subscribers</div>
                        <div class="stat-value">{{ number_format($stats['NewsletterSubscriber']) }}</div>
                        <div class="stat-sub text-success">
                            <i class="bi bi-arrow-up-short"></i> {{ $stats['new_subscriber_this_month'] }} this month
                        </div>
                    </div>
                    <div class="stat-icon icon-purple"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ===== RECENT BOOKINGS ===== --}}
        <div class="col-xl-8">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div>
                        <div class="dash-panel-title">Recent Bookings</div>
                        <div class="dash-panel-sub">Latest activity across every module</div>
                    </div>
                    <a href="{{ route('admin.bookings.all') }}" class="dash-view-btn">
                        View all <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="booking-summary-strip">
                    <div class="bss-item"><span class="bss-dot bss-dot-blue"></span><span class="bss-label">Flight</span><span class="bss-val">{{ number_format($stats['flight_bookings'] ?? 0) }}</span></div>
                    <div class="bss-sep"></div>
                    <div class="bss-item"><span class="bss-dot bss-dot-green"></span><span class="bss-label">Stay</span><span class="bss-val">{{ number_format($stats['hotel_bookings'] ?? 0) }}</span></div>
                    <div class="bss-sep"></div>
                    <div class="bss-item"><span class="bss-dot bss-dot-amber"></span><span class="bss-label">Tour</span><span class="bss-val">{{ number_format($stats['tour_bookings'] ?? 0) }}</span></div>
                    <div class="bss-sep"></div>
                    <div class="bss-item"><span class="bss-dot bss-dot-purple"></span><span class="bss-label">Umrah</span><span class="bss-val">{{ number_format($stats['umrah_bookings'] ?? 0) }}</span></div>
                </div>

                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recent_bookings as $booking)
                            @php
                                $typeConfig = [
                                    'flight' => ['label' => 'Flight', 'bg' => '#EBF3FD', 'color' => '#1a68b3'],
                                    'hotel'  => ['label' => 'Stay',   'bg' => '#EAF6EE', 'color' => '#1a7a44'],
                                    'tour'   => ['label' => 'Tour',   'bg' => '#FDF3E3', 'color' => '#a05c0a'],
                                    'umrah'  => ['label' => 'Umrah',  'bg' => '#F0EFFE', 'color' => '#5340c0'],
                                ];
                                $tc = $typeConfig[$booking->booking_type] ?? ['label' => ucfirst($booking->booking_type ?? ''), 'bg' => '#eee', 'color' => '#666'];
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
                                $initials = collect(explode(' ', $booking->customer_name ?? ''))
                                    ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                                    ->take(2)->implode('');
                                $avatarPalette = [
                                    ['bg' => '#B5D4F4', 'color' => '#0C447C'],
                                    ['bg' => '#C0DD97', 'color' => '#27500A'],
                                    ['bg' => '#FAC775', 'color' => '#633806'],
                                    ['bg' => '#F4C0D1', 'color' => '#72243E'],
                                    ['bg' => '#CECBF6', 'color' => '#3C3489'],
                                ];
                                $av = $avatarPalette[crc32($booking->customer_name ?? '') % count($avatarPalette)];
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ $invoiceRoute }}" class="booking-id-link">#{{ $booking->booking_code_ref }}</a>
                                    <div class="row-meta">{{ $booking->created_at->format('M j') }}</div>
                                </td>
                                <td>
                                    <div class="cust-cell">
                                        <div class="cust-avatar" style="background:{{ $av['bg'] }};color:{{ $av['color'] }}">{{ $initials }}</div>
                                        <div>
                                            <div class="cust-name">{{ Str::limit($booking->customer_name ?? '', 20) }}</div>
                                            <div class="row-meta">{{ Str::limit($booking->customer_email ?? '', 25) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="type-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">{{ $tc['label'] }}</span></td>
                                <td><div class="route-name">{{ Str::limit($serviceName, 24) }}</div></td>
                                <td><div class="amount-val">{{ $booking->formatted_amount }}</div></td>
                                <td><span class="status-badge status-{{ $booking->booking_status_flag }}">{{ ucfirst($booking->booking_status_flag) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-calendar-x fs-3 text-muted d-block mb-2 opacity-50"></i>
                                    <small class="text-muted">No recent bookings found</small>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== NEW CUSTOMERS ===== --}}
            <div class="dash-panel mt-3">
                <div class="dash-panel-header">
                    <div>
                        <div class="dash-panel-title">New Customers</div>
                        <div class="dash-panel-sub">Most recently registered</div>
                    </div>
                    <a href="{{ route('admin.customers.index') }}" class="dash-view-btn">
                        View all <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Bookings</th>
                            <th>Total Spent</th>
                            <th>Joined</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recent_customers as $customer)
                            @php
                                $initials = collect(explode(' ', $customer->full_name ?? ''))
                                    ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                                    ->take(2)->implode('');
                            @endphp
                            <tr>
                                <td>
                                    <div class="cust-cell">
                                        <div class="cust-avatar" style="background:var(--primary-tint-10);color:var(--primary-color)">{{ $initials }}</div>
                                        <div>
                                            <div class="cust-name">{{ $customer->full_name }}</div>
                                            <div class="row-meta">{{ $customer->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="amount-val">{{ $customer->total_bookings ?? 0 }}</div></td>
                                <td><div class="amount-val">${{ number_format($customer->total_spent ?? 0, 2) }}</div></td>
                                <td><div class="row-meta">{{ $customer->created_at->diffForHumans() }}</div></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <small class="text-muted">No customers yet</small>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===== QUICK ACTIONS + DONUT ===== --}}
        <div class="col-xl-4">
            <div class="dash-panel h-100 d-flex flex-column">
                <div class="dash-panel-header">
                    <div>
                        <div class="dash-panel-title">Quick Actions</div>
                        <div class="dash-panel-sub">Administrative tasks</div>
                    </div>
                </div>

                <div class="donut-wrap">
                    @php
                        $total    = max($stats['total_bookings'] ?? 1, 1);
                        $confirmed = $stats['confirmed_bookings'] ?? 0;
                        $cancelled = $stats['cancelled_bookings'] ?? 0;
                        $pending   = max($total - $confirmed - $cancelled, 0);
                        $r = 36; $circ = 2 * M_PI * $r;
                        $confLen = ($confirmed / $total) * $circ;
                        $penLen  = ($pending  / $total) * $circ;
                        $canLen  = ($cancelled / $total) * $circ;
                        $confOff = 0;
                        $penOff  = -($confLen);
                        $canOff  = -($confLen + $penLen);
                    @endphp
                    <div class="donut-chart-wrap">
                        <svg width="96" height="96" viewBox="0 0 96 96">
                            <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#f0f0f0" stroke-width="12"/>
                            @if($confirmed > 0)
                                <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#1a6b3a" stroke-width="12" stroke-dasharray="{{ $confLen }} {{ $circ - $confLen }}" stroke-dashoffset="{{ $confOff }}" transform="rotate(-90 48 48)"/>
                            @endif
                            @if($pending > 0)
                                <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#f0b054" stroke-width="12" stroke-dasharray="{{ $penLen }} {{ $circ - $penLen }}" stroke-dashoffset="{{ $penOff }}" transform="rotate(-90 48 48)"/>
                            @endif
                            @if($cancelled > 0)
                                <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#e05252" stroke-width="12" stroke-dasharray="{{ $canLen }} {{ $circ - $canLen }}" stroke-dashoffset="{{ $canOff }}" transform="rotate(-90 48 48)"/>
                            @endif
                            <text x="48" y="44" text-anchor="middle" font-size="13" font-weight="700" fill="currentColor">{{ $total }}</text>
                            <text x="48" y="57" text-anchor="middle" font-size="8" fill="#999">bookings</text>
                        </svg>
                        <div class="donut-legend">
                            <div class="donut-leg-item"><span class="donut-dot donut-dot-confirmed"></span><span>Confirmed <strong>{{ $confirmed }}</strong></span></div>
                            <div class="donut-leg-item"><span class="donut-dot donut-dot-pending"></span><span>Pending <strong>{{ $pending }}</strong></span></div>
                            <div class="donut-leg-item"><span class="donut-dot donut-dot-cancelled"></span><span>Cancelled <strong>{{ $cancelled }}</strong></span></div>
                        </div>
                    </div>
                </div>

                <div class="quick-actions-list">
                    <a href="{{ route('admin.travel-partners.index') }}" class="qa-item">
                        <div class="qa-icon icon-blue"><i class="bi bi-building"></i></div>
                        <div class="qa-body"><div class="qa-title">Travel Partners</div><div class="qa-desc">Manage partnerships</div></div>
                        <i class="bi bi-chevron-right qa-arrow"></i>
                    </a>
                    <a href="{{ route('admin.visa-requests.visaindex') }}" class="qa-item">
                        <div class="qa-icon icon-green"><i class="bi bi-passport"></i></div>
                        <div class="qa-body"><div class="qa-title">Visa Requests</div><div class="qa-desc">Review applications</div></div>
                        <i class="bi bi-chevron-right qa-arrow"></i>
                    </a>
                    <a href="{{ route('admin.content.blog.create') }}" class="qa-item">
                        <div class="qa-icon icon-amber"><i class="bi bi-file-earmark-text"></i></div>
                        <div class="qa-body"><div class="qa-title">Create Blog Post</div><div class="qa-desc">Write new article</div></div>
                        <i class="bi bi-chevron-right qa-arrow"></i>
                    </a>
                    <a href="{{ route('admin.settings.website') }}" class="qa-item">
                        <div class="qa-icon icon-purple"><i class="bi bi-gear"></i></div>
                        <div class="qa-body"><div class="qa-title">System Settings</div><div class="qa-desc">Configure platform</div></div>
                        <i class="bi bi-chevron-right qa-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
