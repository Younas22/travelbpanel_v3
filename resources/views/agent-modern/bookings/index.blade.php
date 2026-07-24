@extends('agent-modern.layouts.app')
@section('title', 'My Bookings')

@section('content')

    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-calendar-check"></i></div>
            <div>
                <h4 class="ap-page-title">My Bookings</h4>
                <p class="ap-page-sub">All your bookings in one place</p>
            </div>
        </div>
    </div>

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <span>My Bookings</span>
    </div>

    <div class="am-card mb-0">
        <div class="bk-tabs">
            <a href="{{ route('agent.bookings.index') }}" class="bk-tab {{ $type === 'all' ? 'bk-tab-active' : '' }}">
                <i class="bi bi-grid"></i> All
            </a>
            <a href="{{ route('agent.bookings.index', ['type' => 'hotel']) }}" class="bk-tab {{ $type === 'hotel' ? 'bk-tab-active' : '' }}">
                <i class="bi bi-building"></i> Hotels
            </a>
            <a href="{{ route('agent.bookings.index', ['type' => 'flight']) }}" class="bk-tab {{ $type === 'flight' ? 'bk-tab-active' : '' }}">
                <i class="bi bi-airplane"></i> Flights
            </a>
            <a href="{{ route('agent.bookings.index', ['type' => 'tour']) }}" class="bk-tab {{ $type === 'tour' ? 'bk-tab-active' : '' }}">
                <i class="bi bi-geo-alt"></i> Tours
            </a>
            <a href="{{ route('agent.bookings.index', ['type' => 'umrah']) }}" class="bk-tab {{ $type === 'umrah' ? 'bk-tab-active' : '' }}">
                <i class="bi bi-moon-stars"></i> Umrah
            </a>
        </div>

        @if($bookings->isEmpty())
        <div class="am-empty">
            <i class="bi bi-calendar-x"></i>
            <h6>No bookings found</h6>
            <p class="mb-0">Your bookings will appear here once made.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    @php
                        $typeLabel = ucfirst($booking['booking_type']);
                        $typeBadge = ['hotel' => 'bg-primary', 'flight' => 'bg-success', 'tour' => 'bg-warning', 'umrah' => 'bg-info', 'visa' => 'bg-secondary'];
                        $badgeClass = $typeBadge[$booking['booking_type']] ?? 'bg-secondary';
                        $s = $booking['status'] ?? '';
                        $agentInvoiceRoute = match($booking['booking_type'] ?? 'flight') {
                            'flight' => url('flight/invoice', $booking['booking_code']),
                            'hotel'  => url('hotel/invoice', $booking['booking_code']),
                            'tour'   => route('tour.invoice', ['booking_ref' => $booking['booking_code']]),
                            'umrah'  => route('umrah.invoice', ['booking_ref' => $booking['booking_code']]),
                            default  => url('flight/invoice', $booking['booking_code']),
                        };
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ $agentInvoiceRoute }}" target="_blank" style="font-weight: 650;">
                                #{{ $booking['booking_code'] ?? 'N/A' }}
                                <i class="bi bi-box-arrow-up-right" style="font-size: 10px;"></i>
                            </a>
                        </td>
                        <td><span class="badge {{ $badgeClass }}">{{ $typeLabel }}</span></td>
                        <td>
                            @if($booking['booking_type'] === 'hotel')
                                <div style="font-weight: 600;">{{ $booking['hotel_name'] ?? '—' }}</div>
                                <div style="font-size: 11px; color: color-mix(in srgb, var(--text-color) 50%, transparent);">{{ $booking['check_in'] ?? '' }} &rarr; {{ $booking['check_out'] ?? '' }}</div>
                            @elseif($booking['booking_type'] === 'flight')
                                <div style="font-weight: 600;">{{ $booking['origin'] ?? '' }} &rarr; {{ $booking['destination'] ?? '' }}</div>
                                <div style="font-size: 11px; color: color-mix(in srgb, var(--text-color) 50%, transparent);">{{ $booking['departure_date'] ?? '' }}</div>
                            @else
                                {{ Str::limit($booking['title'] ?? ($booking['tour_name'] ?? ($booking['package_name'] ?? '—')), 30) }}
                            @endif
                        </td>
                        <td style="font-weight: 650;">PKR {{ number_format($booking['total_fare'] ?? 0, 0) }}</td>
                        <td>
                            @if($s === 'confirmed')
                                <span class="badge bg-success">Confirmed</span>
                            @elseif($s === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($s === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($s) ?: 'N/A' }}</span>
                            @endif
                        </td>
                        <td style="font-size: 12px; color: color-mix(in srgb, var(--text-color) 55%, transparent); white-space: nowrap;">{{ \Carbon\Carbon::parse($booking['created_at'])->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('agent.bookings.show', [$booking['booking_type'], $booking['id']]) }}" class="ap-btn-outline" style="padding: 6px 12px; font-size: 11.5px;">
                                    View
                                </a>
                                <a href="{{ $agentInvoiceRoute }}" target="_blank" class="ap-btn-outline" style="padding: 6px 12px; font-size: 11.5px;">
                                    <i class="bi bi-file-earmark-text"></i> Invoice
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

@endsection
