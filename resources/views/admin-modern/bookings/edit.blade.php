@extends('admin-modern.layouts.app')

@section('title', 'Edit Booking')

@section('content')

    <div class="eb-header">
        <div>
            <h2 class="eb-title">Edit Booking</h2>
            <p class="eb-subtitle">Update booking and payment status</p>
        </div>
        <a href="{{ route('admin.bookings.all') }}" class="eb-back-btn"><i class="bi bi-arrow-left"></i> Back to list</a>
    </div>

    <div class="eb-content">
        <form method="POST" action="{{ route('admin.bookings.update', ['type' => $bookingType, 'id' => $booking->id]) }}">
            @csrf
            @method('PUT')

            <div class="eb-card">
                <h5 class="eb-card-title">Booking Summary</h5>
                <div class="eb-summary-grid">
                    <div class="eb-summary-item"><div class="eb-summary-label">Booking ID</div><div class="eb-summary-value">#{{ $booking->booking_code_ref }}</div></div>
                    <div class="eb-summary-item"><div class="eb-summary-label">Booking Type</div><div class="eb-summary-value">{{ ucfirst($bookingType) }}</div></div>
                    <div class="eb-summary-item"><div class="eb-summary-label">Amount</div><div class="eb-summary-value">{{ $booking->formatted_amount }}</div></div>
                    <div class="eb-summary-item"><div class="eb-summary-label">Customer Name</div><div class="eb-summary-value">{{ $booking->customer_name }}</div></div>
                    <div class="eb-summary-item"><div class="eb-summary-label">Customer Email</div><div class="eb-summary-value">{{ $booking->customer_email }}</div></div>
                    <div class="eb-summary-item"><div class="eb-summary-label">Created Date</div><div class="eb-summary-value">{{ $booking->created_at->format('M j, Y g:i A') }}</div></div>
                </div>
            </div>

            <div class="eb-card">
                <h5 class="eb-card-title">Update Status</h5>

                <div class="eb-form-grid">
                    <div class="eb-field">
                        <label for="booking_status">Booking Status <span class="eb-required">*</span></label>
                        <select name="booking_status" id="booking_status" class="form-select @error('booking_status') is-invalid @enderror" required>
                            <option value="">Select status</option>
                            <option value="pending" {{ $booking->booking_status_flag === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $booking->booking_status_flag === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ $booking->booking_status_flag === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('booking_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="eb-field">
                        <label for="payment_status">Payment Status <span class="eb-required">*</span></label>
                        <select name="payment_status" id="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                            <option value="">Select payment status</option>
                            <option value="unpaid" {{ $booking->booking_payment_state === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="paid" {{ $booking->booking_payment_state === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="refunded" {{ $booking->booking_payment_state === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                        @error('payment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="eb-status-row">
                    <div class="eb-status-block">
                        <span class="eb-status-label">Current Booking Status</span>
                        <span class="badge-status {{ $booking->status_badge_class }}">{{ ucfirst($booking->booking_status_flag) }}</span>
                    </div>
                    <div class="eb-status-block">
                        <span class="eb-status-label">Current Payment Status</span>
                        <span class="badge-status {{ $booking->payment_status_badge_class }}">{{ ucfirst($booking->booking_payment_state) }}</span>
                    </div>
                </div>
            </div>

            <div class="eb-actions">
                <a href="{{ route('admin.bookings.all') }}" class="eb-btn eb-btn-outline">Cancel</a>
                <button type="submit" class="eb-btn eb-btn-primary"><i class="bi bi-check-circle"></i> Update Status</button>
            </div>
        </form>
    </div>

@endsection
