{{-- Disclosure: mirrors agent/flights/invoice.blade.php — dead/unreachable
     code, see agent-modern/flights/index.blade.php for full explanation. --}}
@extends('agent-modern.layouts.app')
@section('title', 'Flight Booking Confirmed')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-receipt"></i> Flight Booking Confirmed</h4>
    <div class="flex gap-2">
        <a href="{{ route('agent.bookings.index') }}" class="ap-btn-outline">All Bookings</a>
        <button class="ap-btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

<div class="alert alert-success">
    <i class="bi bi-check-circle-fill"></i> Flight booking confirmed! Wallet has been debited.
</div>

<div class="ap-card">
    <div class="ap-card-header flex justify-between">
        <h6 class="mb-0">Booking Reference: <strong>{{ $booking->booking_code_ref }}</strong></h6>
        <span class="badge bg-success">{{ ucfirst($booking->booking_status_flag) }}</span>
    </div>
    <div class="ap-card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <h6 class="text-muted small mb-2">Flight Details</h6>
                @php
                    $data = is_array($booking->booking_data) ? $booking->booking_data : json_decode($booking->booking_data, true);
                @endphp
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Route</td><td><strong>{{ $data['origin'] ?? '-' }} → {{ $data['destination'] ?? '-' }}</strong></td></tr>
                    <tr><td class="text-muted">Airline</td><td>{{ $data['airline'] ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Flight No</td><td>{{ $data['flight_number'] ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Departure</td><td>{{ $data['departure_date'] ?? '-' }} {{ $data['departure_time'] ?? '' }}</td></tr>
                    <tr><td class="text-muted">Class</td><td>{{ ucfirst($data['class'] ?? '-') }}</td></tr>
                    <tr><td class="text-muted">Passengers</td><td>{{ $booking->booking_adult_count }} Adults, {{ $booking->booking_child_count }} Children</td></tr>
                </table>
            </div>
            <div>
                <h6 class="text-muted small mb-2">Payment Details</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Amount Paid</td><td><strong class="text-success">{{ $booking->booking_currency_origin }} {{ number_format($booking->booking_fare_base, 2) }}</strong></td></tr>
                    <tr><td class="text-muted">Payment Method</td><td>Agent Wallet</td></tr>
                    <tr><td class="text-muted">Payment Status</td><td><span class="badge bg-success">Paid</span></td></tr>
                    <tr><td class="text-muted">Booking Date</td><td>{{ $booking->created_at->format('d M Y, h:i A') }}</td></tr>
                    <tr><td class="text-muted">Agent</td><td>{{ auth()->user()->company_name }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
