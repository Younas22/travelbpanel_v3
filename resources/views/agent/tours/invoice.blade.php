@extends('agent.layouts.app')
@section('title', 'Tour Booking Confirmed')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-receipt"></i> Tour Booking Confirmed</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('agent.bookings.index') }}" class="btn btn-outline-secondary btn-sm">All Bookings</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

<div class="alert alert-success">
    <i class="bi bi-check-circle-fill"></i> Tour booking confirmed! Wallet has been debited.
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between">
        <h6 class="mb-0">Reference: <strong>{{ $booking->booking_code_ref }}</strong></h6>
        <span class="badge bg-success">Confirmed</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="text-muted small mb-2">Tour Details</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Tour Name</td><td><strong>{{ $booking->tour_name }}</strong></td></tr>
                    <tr><td class="text-muted">Location</td><td>{{ $booking->tour_location }}</td></tr>
                    <tr><td class="text-muted">Departure</td><td>{{ $booking->departure_date ? \Carbon\Carbon::parse($booking->departure_date)->format('d M Y') : '-' }}</td></tr>
                    <tr><td class="text-muted">Return</td><td>{{ $booking->return_date ? \Carbon\Carbon::parse($booking->return_date)->format('d M Y') : '-' }}</td></tr>
                    <tr><td class="text-muted">Duration</td><td>{{ $booking->tour_days }} Days</td></tr>
                    <tr><td class="text-muted">Passengers</td><td>{{ $booking->booking_adult_count }} Adults, {{ $booking->booking_child_count }} Children</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted small mb-2">Payment Details</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Amount Paid</td><td><strong class="text-success">{{ $booking->booking_currency_origin }} {{ number_format($booking->booking_total_price ?? $booking->booking_fare_base, 2) }}</strong></td></tr>
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
