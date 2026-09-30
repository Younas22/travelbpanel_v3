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

    @php
        // $booking is the raw Eloquent model for the given $type (agent\BookingController::show()
        // does not normalize it — unlike the bookings index page, which goes through
        // formatBooking()). Fields below use each model's own real columns/accessors.
        $bookingCode = $booking->booking_code_ref ?? ('#' . $booking->id);
        $hasPaymentInfo = $type !== 'visa';
        if ($hasPaymentInfo) {
            $activeCurrency = activeCurrency();
            $invoiceCurrencyCode = $activeCurrency->currency_name ?? ($booking->booking_currency_origin ?? 'USD');
            $invoiceAmount = $activeCurrency
                ? convertCurrency($booking->booking_fare_base ?? 0, $booking->booking_currency_origin ?? 'USD', $invoiceCurrencyCode)
                : ($booking->booking_fare_base ?? 0);
        }
        $invoiceRouteNames = ['hotel' => 'hotel.invoice', 'flight' => 'flight.invoice', 'tour' => 'tour.invoice', 'umrah' => 'umrah.invoice'];
        if ($type === 'flight') {
            $guests = is_array($booking->booking_guest) ? $booking->booking_guest : (json_decode($booking->booking_guest ?? '[]', true) ?: []);
        }
    @endphp

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="am-card">
                <div class="am-card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">{{ ucfirst($type) }}</span>
                        <span style="font-family: monospace; font-weight: 650;">{{ $bookingCode }}</span>
                    </div>
                    @if(($booking->booking_status_flag ?? '') === 'confirmed')
                        <span class="badge bg-success">Confirmed</span>
                    @elseif(($booking->booking_status_flag ?? '') === 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @elseif(($booking->booking_status_flag ?? '') === 'cancelled')
                        <span class="badge bg-danger">Cancelled</span>
                    @elseif($booking->booking_status_flag ?? null)
                        <span class="badge bg-secondary">{{ ucfirst($booking->booking_status_flag) }}</span>
                    @endif
                </div>
                <div class="am-card-body">

                    @if($type === 'hotel')
                        <div class="bk-detail-row"><div class="bk-detail-label">Hotel</div><div class="bk-detail-value">{{ $booking->hotel_info['name'] }} &bull; {{ $booking->hotel_info['location'] }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Check-in</div><div class="bk-detail-value">{{ $booking->stay_dates['check_in'] }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Check-out</div><div class="bk-detail-value">{{ $booking->stay_dates['check_out'] }} ({{ $booking->stay_dates['nights'] }})</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Guests</div><div class="bk-detail-value">{{ $booking->guest_count }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Guest Name</div><div class="bk-detail-value">{{ $booking->customer_name }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Guest Email</div><div class="bk-detail-value">{{ $booking->customer_email }}</div></div>
                        @if($booking->booking_hotel_pnr)
                        <div class="bk-detail-row"><div class="bk-detail-label">PNR</div><div class="bk-detail-value" style="font-family: monospace;">{{ $booking->booking_hotel_pnr }}</div></div>
                        @endif

                    @elseif($type === 'flight')
                        <div class="bk-detail-row"><div class="bk-detail-label">Route</div><div class="bk-detail-value">{{ $booking->flight_route['route'] }} <span class="text-muted small">({{ $booking->flight_route['stops'] }})</span></div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Airline / Flight</div><div class="bk-detail-value">{{ $booking->flight_details['airline'] }} &bull; {{ $booking->flight_details['flight_number'] }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Departure</div><div class="bk-detail-value">{{ $booking->travel_date['date'] }} &bull; {{ $booking->travel_date['time'] }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Passengers</div><div class="bk-detail-value">{{ $booking->passenger_count }}</div></div>
                        @if($booking->booking_air_pnr)
                        <div class="bk-detail-row"><div class="bk-detail-label">PNR</div><div class="bk-detail-value" style="font-family: monospace;">{{ $booking->booking_air_pnr }}</div></div>
                        @endif
                        @if(!empty($guests))
                        <div class="bk-detail-row" style="display: block;">
                            <div class="bk-detail-label mb-2">Travellers</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                                    <thead>
                                        <tr class="text-muted text-uppercase" style="font-size: 10px;">
                                            <th>Name</th>
                                            <th>Passport No.</th>
                                            <th>Date of Birth</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($guests as $guest)
                                        <tr>
                                            <td>{{ trim(($guest['first_name'] ?? '') . ' ' . ($guest['last_name'] ?? '')) ?: '—' }}</td>
                                            <td>{{ $guest['passport'] ?? '—' }}</td>
                                            <td>{{ $guest['dob_day'] ?? '—' }}-{{ $guest['dob_month'] ?? '—' }}-{{ $guest['dob_year'] ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                    @elseif(in_array($type, ['tour', 'umrah']))
                        @php $packageInfo = $type === 'tour' ? $booking->tour_info : $booking->umrah_info; @endphp
                        <div class="bk-detail-row"><div class="bk-detail-label">Package</div><div class="bk-detail-value">{{ $packageInfo['name'] }} &bull; {{ $packageInfo['location'] }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Travel Date</div><div class="bk-detail-value">{{ $booking->travel_date['date'] }} &bull; {{ $booking->travel_date['time'] }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Passengers</div><div class="bk-detail-value">{{ $booking->passenger_count }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Lead Traveller</div><div class="bk-detail-value">{{ $booking->customer_name }} &bull; {{ $booking->customer_email }}</div></div>

                    @elseif($type === 'visa')
                        <div class="bk-detail-row"><div class="bk-detail-label">Visa Type</div><div class="bk-detail-value">{{ $booking->visa_type ?? '—' }} @if($booking->visa_plan)&bull; {{ $booking->visa_plan }}@endif</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Applicant</div><div class="bk-detail-value">{{ trim(($booking->first_name ?? '') . ' ' . ($booking->middle_name ?? '') . ' ' . ($booking->surname ?? '')) ?: '—' }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Nationality</div><div class="bk-detail-value">{{ $booking->nationality ?? '—' }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Passport No.</div><div class="bk-detail-value">{{ $booking->passport_no ?? '—' }}</div></div>
                        <div class="bk-detail-row"><div class="bk-detail-label">Passport Validity</div><div class="bk-detail-value">{{ $booking->passport_issue_date?->format('d M Y') ?? '—' }} &rarr; {{ $booking->passport_expiry_date?->format('d M Y') ?? '—' }}</div></div>
                    @endif

                    <div class="bk-detail-row"><div class="bk-detail-label">Booked On</div><div class="bk-detail-value">{{ $booking->created_at->format('d M Y, h:i A') }}</div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($hasPaymentInfo)
            <div class="am-card mb-3">
                <div class="am-card-header">Payment Summary</div>
                <div class="am-card-body">
                    <div class="bk-summary-row">
                        <span class="label">Amount</span>
                        <span style="font-weight: 650;">{{ $invoiceCurrencyCode }} {{ number_format($invoiceAmount, 2) }}</span>
                    </div>
                    <div class="bk-summary-row">
                        <span class="label">Payment</span>
                        @if(($booking->booking_payment_state ?? '') === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @else
                            <span class="badge bg-warning">{{ ucfirst($booking->booking_payment_state ?? 'Unpaid') }}</span>
                        @endif
                    </div>
                    <div class="bk-summary-total">
                        <span>Total</span>
                        <span class="amt">{{ $invoiceCurrencyCode }} {{ number_format($invoiceAmount, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($invoiceRouteNames[$type]) && $booking->booking_code_ref)
            <a href="{{ route($invoiceRouteNames[$type], $booking->booking_code_ref) }}" class="ap-btn-primary w-100 justify-content-center" target="_blank">
                <i class="bi bi-printer"></i> Print Invoice
            </a>
            @endif
        </div>

    </div>

@endsection
