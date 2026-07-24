@extends('agent-modern.layouts.app')
@section('title', 'Booking Details')

@section('content')

    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-receipt"></i></div>
            <div>
                <h4 class="ap-page-title">Booking Details</h4>
                <p class="ap-page-sub">Full booking information &amp; payment summary</p>
            </div>
        </div>
        <a href="{{ route('agent.bookings.index') }}" class="ap-btn-outline">
            <i class="bi bi-arrow-left"></i> Back to Bookings
        </a>
    </div>

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('agent.bookings.index') }}">My Bookings</a>
        <i class="bi bi-chevron-right"></i>
        <span>Booking Details</span>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="am-card">
                <div class="am-card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">{{ ucfirst($type) }}</span>
                        <span style="font-family: monospace; font-weight: 650;">{{ $booking->booking_code ?? 'N/A' }}</span>
                    </div>
                    @if(($booking->status ?? '') === 'confirmed')
                        <span class="badge bg-success">Confirmed</span>
                    @elseif(($booking->status ?? '') === 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @elseif(($booking->status ?? '') === 'cancelled')
                        <span class="badge bg-danger">Cancelled</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($booking->status ?? 'N/A') }}</span>
                    @endif
                </div>
                <div class="am-card-body">

                    @if($type === 'hotel')
                        <div class="bk-detail-row"><div class="bk-detail-label">Hotel</div><div class="bk-detail-value">{{ $booking->hotel_name }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Check-in</div><div class="bk-detail-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Check-out</div><div class="bk-detail-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Rooms / Guests</div><div class="bk-detail-value">{{ $booking->rooms }} room(s) &bull; {{ $booking->adults }} adult(s), {{ $booking->children ?? 0 }} child(ren)</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Guest Name</div><div class="bk-detail-value">{{ $booking->guest_name }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Guest Email</div><div class="bk-detail-value">{{ $booking->guest_email }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Guest Phone</div><div class="bk-detail-value">{{ $booking->guest_phone }}</div></div>

                    @elseif($type === 'flight')
                        <div class="bk-detail-row"><div class="bk-detail-label">Route</div><div class="bk-detail-value">{{ $booking->origin }} &rarr; {{ $booking->destination }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Departure</div><div class="bk-detail-value">{{ \Carbon\Carbon::parse($booking->departure_date)->format('d M Y') }}</div></div>
                        @if($booking->return_date)
                        <div class="bk-detail-row"><div class="bk-detail-label">Return</div><div class="bk-detail-value">{{ \Carbon\Carbon::parse($booking->return_date)->format('d M Y') }}</div></div>
                        @endif
                        <div class="bk-detail-row"><div class="bk-detail-label">Passengers</div><div class="bk-detail-value">{{ $booking->adults }} adult(s), {{ $booking->children ?? 0 }} child(ren)</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Airline</div><div class="bk-detail-value">{{ $booking->airline ?? '—' }}</div></div>

                    @elseif(in_array($type, ['tour', 'umrah']))
                        <div class="bk-detail-row"><div class="bk-detail-label">Package</div><div class="bk-detail-value">{{ $booking->tour_name ?? $booking->package_name ?? '—' }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Travel Date</div><div class="bk-detail-value">{{ $booking->travel_date ? \Carbon\Carbon::parse($booking->travel_date)->format('d M Y') : '—' }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Persons</div><div class="bk-detail-value">{{ $booking->persons ?? $booking->adults ?? '—' }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Lead Traveller</div><div class="bk-detail-value">{{ $booking->lead_name ?? $booking->guest_name ?? '—' }}</div></div>

                    @elseif($type === 'visa')
                        <div class="bk-detail-row"><div class="bk-detail-label">Visa Type</div><div class="bk-detail-value">{{ $booking->visa_type ?? '—' }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Country</div><div class="bk-detail-value">{{ $booking->country ?? '—' }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Applicant</div><div class="bk-detail-value">{{ $booking->applicant_name ?? '—' }}</div></div>
                    @endif

                    <div class="bk-detail-row"><div class="bk-detail-label">Booked On</div><div class="bk-detail-value">{{ $booking->created_at->format('d M Y, h:i A') }}</div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="am-card mb-3">
                <div class="am-card-header">Payment Summary</div>
                <div class="am-card-body">
                    <div class="bk-summary-row">
                        <span class="label">Amount</span>
                        <span style="font-weight: 650;">PKR {{ number_format($booking->total_fare ?? 0, 2) }}</span>
                    </div>
                    <div class="bk-summary-row">
                        <span class="label">Payment</span>
                        <span class="badge bg-success">Wallet</span>
                    </div>
                    <div class="bk-summary-total">
                        <span>Total Paid</span>
                        <span class="amt">PKR {{ number_format($booking->total_fare ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($type === 'hotel' && isset($booking->booking_code))
            <a href="{{ route('agent.hotels.invoice', $booking->booking_code) }}" class="ap-btn-primary w-100 justify-content-center" target="_blank">
                <i class="bi bi-printer"></i> Print Invoice
            </a>
            @endif
        </div>

    </div>

@endsection
