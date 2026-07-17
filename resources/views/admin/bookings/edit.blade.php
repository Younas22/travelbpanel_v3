@extends('admin.layouts.app')

@section('title', 'Edit Booking')

@section('content')
    <div class="content-area">

        <!-- ===== PAGE HEADER ===== -->
        <div class="eb-header">
            <div>
                <h2 class="eb-title">Edit Booking</h2>
                <p class="eb-subtitle">Update booking and payment status</p>
            </div>
            <a href="{{ route('admin.bookings.all') }}" class="eb-back-btn">
                <i class="bi bi-arrow-left"></i> Back to list
            </a>
        </div>

        <div class="eb-content">
            <form method="POST" action="{{ route('admin.bookings.update', ['type' => $bookingType, 'id' => $booking->id]) }}">
                @csrf
                @method('PUT')

                <!-- ===== BOOKING SUMMARY ===== -->
                <div class="eb-card">
                    <h5 class="eb-card-title">Booking Summary</h5>
                    <div class="eb-summary-grid">
                        <div class="eb-summary-item">
                            <div class="eb-summary-label">Booking ID</div>
                            <div class="eb-summary-value">#{{ $booking->booking_code_ref }}</div>
                        </div>
                        <div class="eb-summary-item">
                            <div class="eb-summary-label">Booking Type</div>
                            <div class="eb-summary-value">{{ ucfirst($bookingType) }}</div>
                        </div>
                        <div class="eb-summary-item">
                            <div class="eb-summary-label">Amount</div>
                            <div class="eb-summary-value">{{ $booking->formatted_amount }}</div>
                        </div>
                        <div class="eb-summary-item">
                            <div class="eb-summary-label">Customer Name</div>
                            <div class="eb-summary-value">{{ $booking->customer_name }}</div>
                        </div>
                        <div class="eb-summary-item">
                            <div class="eb-summary-label">Customer Email</div>
                            <div class="eb-summary-value">{{ $booking->customer_email }}</div>
                        </div>
                        <div class="eb-summary-item">
                            <div class="eb-summary-label">Created Date</div>
                            <div class="eb-summary-value">{{ $booking->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                </div>

                <!-- ===== UPDATE STATUS ===== -->
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
                            @error('booking_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="eb-field">
                            <label for="payment_status">Payment Status <span class="eb-required">*</span></label>
                            <select name="payment_status" id="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                <option value="">Select payment status</option>
                                <option value="unpaid" {{ $booking->booking_payment_state === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="paid" {{ $booking->booking_payment_state === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ $booking->booking_payment_state === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                            @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Current Status -->
                    <div class="eb-status-row">
                        <div class="eb-status-block">
                            <span class="eb-status-label">Current Booking Status</span>
                            <span class="badge-status {{ $booking->status_badge_class }}">
                                {{ ucfirst($booking->booking_status_flag) }}
                            </span>
                        </div>
                        <div class="eb-status-block">
                            <span class="eb-status-label">Current Payment Status</span>
                            <span class="badge-status {{ $booking->payment_status_badge_class }}">
                                {{ ucfirst($booking->booking_payment_state) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ===== ACTIONS ===== -->
                <div class="eb-actions">
                    <a href="{{ route('admin.bookings.all') }}" class="eb-btn eb-btn-outline">Cancel</a>
                    <button type="submit" class="eb-btn eb-btn-primary">
                        <i class="bi bi-check-circle"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            /* ===== PAGE HEADER ===== */
            .eb-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
                margin-bottom: 1.25rem;
            }
            .eb-title {
                font-size: 20px;
                font-weight: 600;
                color: var(--bs-body-color);
                margin: 0 0 4px;
            }
            .eb-subtitle {
                font-size: 13px;
                color: var(--bs-secondary-color);
                margin: 0;
            }
            .eb-back-btn {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 8px 16px;
                border: 1px solid var(--bs-border-color);
                border-radius: 8px;
                background: var(--bs-secondary-bg);
                color: var(--bs-secondary-color);
                font-size: 13px;
                font-weight: 500;
                text-decoration: none;
                transition: background .15s, color .15s;
            }
            .eb-back-btn:hover {
                background: var(--bs-tertiary-bg);
                color: var(--bs-body-color);
                text-decoration: none;
            }

            /* ===== CONTENT WRAPPER ===== */
            .eb-content {
                max-width: 760px;
                margin: 0 auto;
            }

            /* ===== CARD ===== */
            .eb-card {
                background: var(--bs-body-bg);
                border: 1px solid var(--bs-border-color);
                border-radius: 14px;
                padding: 1.25rem 1.5rem;
                margin-bottom: 1.25rem;
            }
            .eb-card-title {
                font-size: 14px;
                font-weight: 600;
                color: var(--bs-body-color);
                margin: 0 0 1rem;
            }

            /* ===== SUMMARY GRID ===== */
            .eb-summary-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1.25rem;
            }
            .eb-summary-label {
                font-size: 11px;
                font-weight: 500;
                color: var(--bs-secondary-color);
                text-transform: uppercase;
                letter-spacing: .05em;
                margin-bottom: 4px;
            }
            .eb-summary-value {
                font-size: 13px;
                font-weight: 500;
                color: var(--bs-body-color);
                word-break: break-word;
            }

            /* ===== FORM GRID ===== */
            .eb-form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.25rem;
                margin-bottom: 1.25rem;
            }
            .eb-field label {
                font-size: 12px;
                font-weight: 500;
                color: var(--bs-secondary-color);
                margin-bottom: 6px;
                display: block;
            }
            .eb-required { color: #A32D2D; }
            .eb-card .form-select {
                border-radius: 8px;
                border-color: var(--bs-border-color);
                font-size: 13px;
            }

            /* ===== CURRENT STATUS ROW ===== */
            .eb-status-row {
                display: flex;
                gap: 2rem;
                padding-top: 1.1rem;
                border-top: 1px solid var(--bs-border-color);
                flex-wrap: wrap;
            }
            .eb-status-block {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .eb-status-label {
                font-size: 11px;
                font-weight: 500;
                color: var(--bs-secondary-color);
                text-transform: uppercase;
                letter-spacing: .05em;
            }

            /* ===== STATUS BADGES ===== */
            .badge-status {
                display: inline-block;
                align-self: flex-start;
                padding: 3px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                letter-spacing: .02em;
                text-transform: capitalize;
            }
            .badge-status.bg-success { background: #EAF3DE !important; color: #27500A !important; }
            .badge-status.bg-warning { background: #FAEEDA !important; color: #633806 !important; }
            .badge-status.bg-danger  { background: #FCEBEB !important; color: #A32D2D !important; }
            .badge-status.bg-info,
            .badge-status.bg-primary { background: #E6F1FB !important; color: #0C447C !important; }
            .badge-status.bg-secondary,
            .badge-status.bg-light   { background: var(--bs-tertiary-bg) !important; color: var(--bs-secondary-color) !important; }

            [data-bs-theme="dark"] .badge-status.bg-success { background: #0a2e1a !important; color: #6dd499 !important; }
            [data-bs-theme="dark"] .badge-status.bg-warning { background: #2e1e05 !important; color: #f0b054 !important; }
            [data-bs-theme="dark"] .badge-status.bg-danger  { background: #2e0a0a !important; color: #f08080 !important; }
            [data-bs-theme="dark"] .badge-status.bg-info,
            [data-bs-theme="dark"] .badge-status.bg-primary { background: #0c2f4d !important; color: #7db8f0 !important; }

            /* ===== ACTIONS ===== */
            .eb-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }
            .eb-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 9px 20px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                border: 1px solid var(--bs-border-color);
                transition: background .15s, color .15s, opacity .15s;
            }
            .eb-btn-outline {
                background: var(--bs-secondary-bg);
                color: var(--bs-secondary-color);
            }
            .eb-btn-outline:hover {
                background: var(--bs-tertiary-bg);
                color: var(--bs-body-color);
                text-decoration: none;
            }
            .eb-btn-primary {
                background: var(--bs-primary);
                border-color: var(--bs-primary);
                color: #fff;
            }
            .eb-btn-primary:hover {
                opacity: .9;
                color: #fff;
            }

            /* ===== RESPONSIVE ===== */
            @media (max-width: 768px) {
                .eb-summary-grid { grid-template-columns: 1fr 1fr; }
                .eb-form-grid { grid-template-columns: 1fr; }
                .eb-status-row { gap: 1.5rem; }
            }
            @media (max-width: 480px) {
                .eb-summary-grid { grid-template-columns: 1fr; }
                .eb-header { align-items: flex-start; }
            }
        </style>
    @endpush

@endsection
