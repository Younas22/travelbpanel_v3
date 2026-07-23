@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="content-area">

        {{-- Stats Cards --}}
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
                        <div class="stat-icon icon-blue">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-bar-wrap">
                        <div class="stat-bar">
                            <div class="stat-bar-fill bar-blue" style="width:{{ ($stats['total_bookings'] ?? 0) > 0 ? number_format(($stats['confirmed_bookings'] / $stats['total_bookings']) * 100, 1) : 0 }}%"></div>
                        </div>
                        <div class="stat-bar-labels">
                            <span>Confirmed</span>
                            <span>{{ ($stats['total_bookings'] ?? 0) > 0 ? number_format(($stats['confirmed_bookings'] / $stats['total_bookings']) * 100, 1) : 0 }}%</span>
                        </div>
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
                                <i class="bi bi-arrow-up-short"></i>
                                {{ number_format($stats['total_visarequest']) }} this month
                            </div>
                        </div>
                        <div class="stat-icon icon-green">
                            <i class="bi bi-passport"></i>
                        </div>
                    </div>
                    <div class="stat-sparkline">
                        <svg viewBox="0 0 80 28" preserveAspectRatio="none">
                            <polyline points="0,24 13,18 26,20 39,10 52,14 65,6 80,8"
                                      fill="none" stroke="#1a7a44" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round" opacity=".5"/>
                            <polyline points="0,24 13,18 26,20 39,10 52,14 65,6 80,8 80,28 0,28"
                                      fill="#1a7a44" stroke="none" opacity=".08"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-1">
                            <div class="stat-label">Total Blogs</div>
                            <div class="stat-value">{{ number_format($stats['blogs_count']) }}</div>
                            <div class="stat-sub text-warning">
                                <i class="bi bi-arrow-right-short"></i>
                                {{ $stats['new_blogs_this_month'] }} new this month
                            </div>
                        </div>
                        <div class="stat-icon icon-amber">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                    <div class="stat-bar-wrap">
                        <div class="stat-bar">
                            @php $blogPct = $stats['blogs_count'] > 0 ? min(100, ($stats['new_blogs_this_month'] / max($stats['blogs_count'],1)) * 100) : 0; @endphp
                            <div class="stat-bar-fill bar-amber" style="width:{{ $blogPct }}%"></div>
                        </div>
                        <div class="stat-bar-labels">
                            <span>New vs Total</span>
                            <span>{{ number_format($blogPct, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-1">
                            <div class="stat-label">Total Subscribers</div>
                            <div class="stat-value">{{ number_format($stats['NewsletterSubscriber']) }}</div>
                            <div class="stat-sub text-success">
                                <i class="bi bi-arrow-up-short"></i>
                                {{ $stats['new_subscriber_this_month'] }} new
                            </div>
                        </div>
                        <div class="stat-icon icon-purple">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="stat-sparkline">
                        <svg viewBox="0 0 80 28" preserveAspectRatio="none">
                            <polyline points="0,22 13,20 26,14 39,16 52,8 65,10 80,4"
                                      fill="none" stroke="#5340c0" stroke-width="1.8"
                                      stroke-linecap="round" stroke-linejoin="round" opacity=".5"/>
                            <polyline points="0,22 13,20 26,14 39,16 52,8 65,10 80,4 80,28 0,28"
                                      fill="#5340c0" stroke="none" opacity=".08"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Row --}}
        <div class="row g-3">

            {{-- Recent Bookings --}}
            <div class="col-xl-8">
                <div class="dash-panel">
                    <div class="dash-panel-header">
                        <div>
                            <div class="dash-panel-title">Recent Bookings</div>
                            <div class="dash-panel-sub">Latest bookings</div>
                        </div>
                        <a href="{{ route('admin.bookings.all') }}" class="dash-view-btn">
                            View all <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    {{-- Mini summary strip --}}
                    <div class="booking-summary-strip">
                        <div class="bss-item">
                            <span class="bss-dot bss-dot-blue"></span>
                            <span class="bss-label">Flight</span>
                            <span class="bss-val">{{ number_format($stats['flight_bookings'] ?? 0) }}</span>
                        </div>
                        <div class="bss-sep"></div>
                        <div class="bss-item">
                            <span class="bss-dot bss-dot-green"></span>
                            <span class="bss-label">Stay</span>
                            <span class="bss-val">{{ number_format($stats['hotel_bookings'] ?? 0) }}</span>
                        </div>
                        <div class="bss-sep"></div>
                        <div class="bss-item">
                            <span class="bss-dot bss-dot-amber"></span>
                            <span class="bss-label">Tour</span>
                            <span class="bss-val">{{ number_format($stats['tour_bookings'] ?? 0) }}</span>
                        </div>
                        <div class="bss-sep"></div>
                        <div class="bss-item">
                            <span class="bss-dot bss-dot-purple"></span>
                            <span class="bss-label">Umrah</span>
                            <span class="bss-val">{{ number_format($stats['umrah_bookings'] ?? 0) }}</span>
                        </div>
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
                                    $userData = is_array($booking->booking_user_data) ? $booking->booking_user_data : (array) json_decode($booking->booking_user_data, true);
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
                                    $serviceSub = match($booking->booking_type) {
                                        'hotel' => $booking->hotel_info['location'] ?? '',
                                        'tour'  => $booking->tour_info['location'] ?? '',
                                        'umrah' => '',
                                        default => ($booking->flight_route['stops'] ?? '') . ($booking->travel_date['date'] ?? ''),
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
                                        <a href="{{ $invoiceRoute }}" class="booking-id-link">
                                            #{{ $booking->booking_code_ref }}
                                        </a>
                                        <div class="row-meta">{{ $booking->created_at->format('M j') }}</div>
                                    </td>
                                    <td>
                                        <div class="cust-cell">
                                            <div class="cust-avatar" style="background:{{ $av['bg'] }};color:{{ $av['color'] }}">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <div class="cust-name">{{ Str::limit($booking->customer_name ?? '', 20) }}</div>
                                                <div class="row-meta">{{ Str::limit($booking->customer_email ?? '', 25) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="type-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">{{ $tc['label'] }}</span>
                                    </td>
                                    <td>
                                        <div class="route-name">{{ Str::limit($serviceName, 24) }}</div>
                                        @if($serviceSub)
                                            <div class="row-meta">{{ $serviceSub }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="amount-val">{{ $booking->formatted_amount }}</div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $booking->booking_status_flag }}">
                                            {{ ucfirst($booking->booking_status_flag) }}
                                        </span>
                                    </td>
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
            </div>

            {{-- Quick Actions + Donut --}}
            <div class="col-xl-4">
                <div class="dash-panel h-100 d-flex flex-column">
                    <div class="dash-panel-header">
                        <div>
                            <div class="dash-panel-title">Quick Actions</div>
                            <div class="dash-panel-sub">Administrative tasks</div>
                        </div>
                    </div>

                    {{-- Donut overview --}}
                    <div class="donut-wrap">
                        @php
                            $total    = max($stats['total_bookings'] ?? 1, 1);
                            $confirmed = $stats['confirmed_bookings'] ?? 0;
                            $cancelled = $stats['cancelled_bookings'] ?? 0;
                            $pending   = $total - $confirmed - $cancelled;
                            $pending   = max($pending, 0);
                            $r = 36; $circ = 2 * M_PI * $r;
                            $confPct = $confirmed / $total;
                            $penPct  = $pending  / $total;
                            $canPct  = $cancelled / $total;
                            $confLen = $confPct * $circ;
                            $penLen  = $penPct  * $circ;
                            $canLen  = $canPct  * $circ;
                            $confOff = 0;
                            $penOff  = -($confLen);
                            $canOff  = -($confLen + $penLen);
                        @endphp
                        <div class="donut-chart-wrap">
                            <svg width="96" height="96" viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#f0f0f0" stroke-width="12"/>
                                @if($confirmed > 0)
                                    <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#1a6b3a" stroke-width="12"
                                            stroke-dasharray="{{ $confLen }} {{ $circ - $confLen }}"
                                            stroke-dashoffset="{{ $confOff }}" transform="rotate(-90 48 48)"/>
                                @endif
                                @if($pending > 0)
                                    <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#f0b054" stroke-width="12"
                                            stroke-dasharray="{{ $penLen }} {{ $circ - $penLen }}"
                                            stroke-dashoffset="{{ $penOff }}" transform="rotate(-90 48 48)"/>
                                @endif
                                @if($cancelled > 0)
                                    <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#e05252" stroke-width="12"
                                            stroke-dasharray="{{ $canLen }} {{ $circ - $canLen }}"
                                            stroke-dashoffset="{{ $canOff }}" transform="rotate(-90 48 48)"/>
                                @endif
                                <text x="48" y="44" text-anchor="middle" font-size="13" font-weight="600" fill="currentColor">{{ $total }}</text>
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
                            <div class="qa-body">
                                <div class="qa-title">Travel Partners</div>
                                <div class="qa-desc">Manage partnerships</div>
                            </div>
                            <i class="bi bi-chevron-right qa-arrow"></i>
                        </a>
                        <a href="{{ route('admin.visa-requests.visaindex') }}" class="qa-item">
                            <div class="qa-icon icon-green"><i class="bi bi-passport"></i></div>
                            <div class="qa-body">
                                <div class="qa-title">Visa Requests</div>
                                <div class="qa-desc">Review applications</div>
                            </div>
                            <i class="bi bi-chevron-right qa-arrow"></i>
                        </a>
                        <a href="{{ route('admin.content.blog.create') }}" class="qa-item">
                            <div class="qa-icon icon-amber"><i class="bi bi-file-earmark-text"></i></div>
                            <div class="qa-body">
                                <div class="qa-title">Create Blog Post</div>
                                <div class="qa-desc">Write new article</div>
                            </div>
                            <i class="bi bi-chevron-right qa-arrow"></i>
                        </a>
                        <a href="{{ route('admin.settings.website') }}" class="qa-item">
                            <div class="qa-icon icon-purple"><i class="bi bi-gear"></i></div>
                            <div class="qa-body">
                                <div class="qa-title">System Settings</div>
                                <div class="qa-desc">Configure platform</div>
                            </div>
                            <i class="bi bi-chevron-right qa-arrow"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
