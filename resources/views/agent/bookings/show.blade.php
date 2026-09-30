@extends('agent.layouts.app')
@section('title', 'Booking Details')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
            <i class="fas fa-receipt ap-accent"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">Booking Details</h4>
            <p class="text-xs text-gray-400">Full booking information & payment summary</p>
        </div>
    </div>
    <a href="{{ route('agent.bookings.index') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 ap-chip-link">
        <i class="fas fa-arrow-left"></i> Back to Bookings
    </a>
</div>

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="ap-accent-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <a href="{{ route('agent.bookings.index') }}" class="ap-accent-link">My Bookings</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Booking Details</span>
</div>

@php
    // $booking is the raw Eloquent model for the given $type (agent\BookingController::show()
    // does not normalize it — unlike the bookings index page, which goes through
    // formatBooking()). Fields below use each model's own real columns/accessors.
    $bookingCode = $booking->booking_code_ref ?? ('#' . $booking->id);
    $statusColors = ['confirmed' => 'success', 'pending' => 'warning', 'cancelled' => 'danger'];
    $statusColor = $statusColors[$booking->booking_status_flag ?? ''] ?? 'secondary';

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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">{{ ucfirst($type) }}</span>
                    <span class="text-sm font-semibold text-gray-700 font-mono">{{ $bookingCode }}</span>
                </div>
                @if(($booking->booking_status_flag ?? '') === 'confirmed')
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Confirmed</span>
                @elseif(($booking->booking_status_flag ?? '') === 'pending')
                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Pending</span>
                @elseif(($booking->booking_status_flag ?? '') === 'cancelled')
                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Cancelled</span>
                @elseif($booking->booking_status_flag ?? null)
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">{{ ucfirst($booking->booking_status_flag) }}</span>
                @endif
            </div>
            <div class="p-5">
                <dl class="divide-y divide-gray-50">
                    @if($type === 'hotel')
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Hotel</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->hotel_info['name'] }} &bull; {{ $booking->hotel_info['location'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Check-in</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->stay_dates['check_in'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Check-out</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->stay_dates['check_out'] }} ({{ $booking->stay_dates['nights'] }})</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Guests</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->guest_count }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Guest Name</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->customer_name }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Guest Email</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->customer_email }}</dd>
                        </div>
                        @if($booking->booking_hotel_pnr)
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">PNR</dt>
                            <dd class="col-span-2 text-sm text-gray-800 font-mono">{{ $booking->booking_hotel_pnr }}</dd>
                        </div>
                        @endif

                    @elseif($type === 'flight')
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Route</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->flight_route['route'] }} <span class="text-xs text-gray-400">({{ $booking->flight_route['stops'] }})</span></dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Airline / Flight</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->flight_details['airline'] }} &bull; {{ $booking->flight_details['flight_number'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Departure</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->travel_date['date'] }} &bull; {{ $booking->travel_date['time'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Passengers</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->passenger_count }}</dd>
                        </div>
                        @if($booking->booking_air_pnr)
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">PNR</dt>
                            <dd class="col-span-2 text-sm text-gray-800 font-mono">{{ $booking->booking_air_pnr }}</dd>
                        </div>
                        @endif
                        @if(!empty($guests))
                        <div class="py-2.5">
                            <dt class="text-xs font-semibold text-gray-500 mb-2">Travellers</dt>
                            <dd class="overflow-x-auto">
                                <table class="w-full text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-500 uppercase text-[10px]">
                                            <th class="border border-gray-200 px-2 py-1.5 text-left">Name</th>
                                            <th class="border border-gray-200 px-2 py-1.5 text-left">Passport No.</th>
                                            <th class="border border-gray-200 px-2 py-1.5 text-left">Date of Birth</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($guests as $guest)
                                        <tr>
                                            <td class="border border-gray-200 px-2 py-1.5">{{ trim(($guest['first_name'] ?? '') . ' ' . ($guest['last_name'] ?? '')) ?: '—' }}</td>
                                            <td class="border border-gray-200 px-2 py-1.5">{{ $guest['passport'] ?? '—' }}</td>
                                            <td class="border border-gray-200 px-2 py-1.5">{{ $guest['dob_day'] ?? '—' }}-{{ $guest['dob_month'] ?? '—' }}-{{ $guest['dob_year'] ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </dd>
                        </div>
                        @endif

                    @elseif(in_array($type, ['tour', 'umrah']))
                        @php $packageInfo = $type === 'tour' ? $booking->tour_info : $booking->umrah_info; @endphp
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Package</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $packageInfo['name'] }} &bull; {{ $packageInfo['location'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Travel Date</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->travel_date['date'] }} &bull; {{ $booking->travel_date['time'] }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Passengers</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->passenger_count }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Lead Traveller</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->customer_name }} &bull; {{ $booking->customer_email }}</dd>
                        </div>

                    @elseif($type === 'visa')
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Visa Type</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->visa_type ?? '—' }} @if($booking->visa_plan)&bull; {{ $booking->visa_plan }}@endif</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Applicant</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ trim(($booking->first_name ?? '') . ' ' . ($booking->middle_name ?? '') . ' ' . ($booking->surname ?? '')) ?: '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Nationality</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->nationality ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Passport No.</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->passport_no ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Passport Validity</dt>
                            <dd class="col-span-2 text-sm text-gray-800">
                                {{ $booking->passport_issue_date?->format('d M Y') ?? '—' }} &rarr; {{ $booking->passport_expiry_date?->format('d M Y') ?? '—' }}
                            </dd>
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-2 py-2.5">
                        <dt class="text-xs font-semibold text-gray-500">Booked On</dt>
                        <dd class="col-span-2 text-sm text-gray-800">{{ $booking->created_at->format('d M Y, h:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @if($hasPaymentInfo)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
                Payment Summary
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Amount</span>
                    <span class="text-sm font-semibold text-gray-800">{{ $invoiceCurrencyCode }} {{ number_format($invoiceAmount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Payment</span>
                    @if(($booking->booking_payment_state ?? '') === 'paid')
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Paid</span>
                    @else
                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">{{ ucfirst($booking->booking_payment_state ?? 'Unpaid') }}</span>
                    @endif
                </div>
                <div class="border-t border-gray-100 pt-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Total</span>
                    <span class="text-sm font-bold text-green-600">{{ $invoiceCurrencyCode }} {{ number_format($invoiceAmount, 2) }}</span>
                </div>
            </div>
        </div>
        @endif

        @if(isset($invoiceRouteNames[$type]) && $booking->booking_code_ref)
        <a href="{{ route($invoiceRouteNames[$type], $booking->booking_code_ref) }}"
           class="w-full px-5 py-2.5 rounded-lg text-sm font-semibold text-white flex items-center justify-center gap-2 ap-solid-accent-btn"
           target="_blank">
            <i class="fas fa-print"></i> Print Invoice
        </a>
        @endif
    </div>

</div>

@endsection
