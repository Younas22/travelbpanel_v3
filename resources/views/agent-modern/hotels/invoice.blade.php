{{-- Disclosure: mirrors agent/hotels/invoice.blade.php, which is dead/unreachable
     code — no route ever calls a controller method that returns this view.
     Kept for file-tree parity with the exhaustive file list this task specifies. --}}
@extends('agent-modern.layouts.app')
@section('title', 'Booking Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-receipt"></i> Hotel Booking Confirmed</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('agent.bookings.index') }}" class="btn btn-outline-secondary btn-sm">All Bookings</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

<div class="alert alert-success">
    <i class="bi bi-check-circle-fill"></i> Booking confirmed! Wallet has been debited.
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between">
        <h6 class="mb-0">Booking Reference: <strong>{{ $booking->booking_code_ref }}</strong></h6>
        <span class="badge bg-success">{{ ucfirst($booking->booking_status_flag) }}</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="text-muted small mb-2">Hotel Details</h6>
                @php $info = $booking->hotel_info; $dates = $booking->stay_dates; @endphp
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Hotel</td><td><strong>{{ $info['name'] }}</strong></td></tr>
                    <tr><td class="text-muted">Location</td><td>{{ $info['location'] }}</td></tr>
                    <tr><td class="text-muted">Check-in</td><td>{{ $dates['check_in'] }}</td></tr>
                    <tr><td class="text-muted">Check-out</td><td>{{ $dates['check_out'] }}</td></tr>
                    <tr><td class="text-muted">Duration</td><td>{{ $dates['nights'] }}</td></tr>
                    <tr><td class="text-muted">Guests</td><td>{{ $booking->guest_count }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted small mb-2">Payment Details</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Amount Paid</td><td><strong class="text-success">{{ $booking->formatted_amount }}</strong></td></tr>
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
