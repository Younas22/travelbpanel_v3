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
    $statusColors = ['confirmed' => 'success', 'pending' => 'warning', 'cancelled' => 'danger'];
    $statusColor = $statusColors[$booking->status ?? ''] ?? 'secondary';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">{{ ucfirst($type) }}</span>
                    <span class="text-sm font-semibold text-gray-700 font-mono">{{ $booking->booking_code ?? 'N/A' }}</span>
                </div>
                @if(($booking->status ?? '') === 'confirmed')
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Confirmed</span>
                @elseif(($booking->status ?? '') === 'pending')
                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Pending</span>
                @elseif(($booking->status ?? '') === 'cancelled')
                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Cancelled</span>
                @else
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">{{ ucfirst($booking->status ?? 'N/A') }}</span>
                @endif
            </div>
            <div class="p-5">
                <dl class="divide-y divide-gray-50">
                    @if($type === 'hotel')
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Hotel</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->hotel_name }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Check-in</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Check-out</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Rooms / Guests</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->rooms }} room(s) &bull; {{ $booking->adults }} adult(s), {{ $booking->children ?? 0 }} child(ren)</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Guest Name</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->guest_name }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Guest Email</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->guest_email }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Guest Phone</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->guest_phone }}</dd>
                        </div>

                    @elseif($type === 'flight')
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Route</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->origin }} &rarr; {{ $booking->destination }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Departure</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ \Carbon\Carbon::parse($booking->departure_date)->format('d M Y') }}</dd>
                        </div>
                        @if($booking->return_date)
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Return</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ \Carbon\Carbon::parse($booking->return_date)->format('d M Y') }}</dd>
                        </div>
                        @endif
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Passengers</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->adults }} adult(s), {{ $booking->children ?? 0 }} child(ren)</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Airline</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->airline ?? '—' }}</dd>
                        </div>

                    @elseif(in_array($type, ['tour', 'umrah']))
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Package</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->tour_name ?? $booking->package_name ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Travel Date</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->travel_date ? \Carbon\Carbon::parse($booking->travel_date)->format('d M Y') : '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Persons</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->persons ?? $booking->adults ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Lead Traveller</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->lead_name ?? $booking->guest_name ?? '—' }}</dd>
                        </div>

                    @elseif($type === 'visa')
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Visa Type</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->visa_type ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Country</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->country ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 py-2.5">
                            <dt class="text-xs font-semibold text-gray-500">Applicant</dt>
                            <dd class="col-span-2 text-sm text-gray-800">{{ $booking->applicant_name ?? '—' }}</dd>
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
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
                Payment Summary
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Amount</span>
                    <span class="text-sm font-semibold text-gray-800">PKR {{ number_format($booking->total_fare ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Payment</span>
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Wallet</span>
                </div>
                <div class="border-t border-gray-100 pt-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Total Paid</span>
                    <span class="text-sm font-bold text-green-600">PKR {{ number_format($booking->total_fare ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        @if($type === 'hotel' && isset($booking->booking_code))
        <a href="{{ route('agent.hotels.invoice', $booking->booking_code) }}"
           class="w-full px-5 py-2.5 rounded-lg text-sm font-semibold text-white flex items-center justify-center gap-2 ap-solid-accent-btn"
           target="_blank">
            <i class="fas fa-print"></i> Print Invoice
        </a>
        @endif
    </div>

</div>

@endsection
