@extends('agent.layouts.app')
@section('title', 'Hotel Booking')

@section('content')
<div class="mb-3">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('agent.hotels.booking.confirm') }}">
            @csrf
            <input type="hidden" name="room" value="{{ request('room') }}">
            <input type="hidden" name="option" value="{{ request('option') }}">
            <input type="hidden" name="booking_data" value="{{ request('booking_data') }}">

            {{-- Guest Details --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-person"></i> Guest Details</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        @php $adults = $hotelSearch['adults'] ?? 1; @endphp
                        @for($i = 1; $i <= $adults; $i++)
                        <div class="col-12">
                            <h6 class="text-muted small mb-2">Adult {{ $i }}</h6>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Title</label>
                            <select name="adult_gender_{{ $i }}" class="form-select form-select-sm">
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Ms">Ms</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">First Name *</label>
                            <input type="text" name="adult_first_name_{{ $i }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Last Name *</label>
                            <input type="text" name="adult_last_name_{{ $i }}" class="form-control form-control-sm" required>
                        </div>
                        @endfor
                    </div>
                    <hr>
                    <h6 class="text-muted small mb-2">Contact Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Email *</label>
                            <input type="email" name="user[user_email]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Phone *</label>
                            <input type="text" name="user[phone]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">First Name</label>
                            <input type="text" name="user[first_name]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Last Name</label>
                            <input type="text" name="user[last_name]" class="form-control form-control-sm">
                        </div>
                        @if(isset($countries))
                        <div class="col-md-6">
                            <label class="form-label small">Nationality</label>
                            <select name="user[country]" class="form-select form-select-sm">
                                @foreach($countries as $c)
                                <option value="{{ $c->country_code }}">{{ $c->country }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 py-3">
                <i class="bi bi-wallet2"></i> Confirm & Pay from Wallet
            </button>
        </form>
    </div>

    <div class="col-lg-4">
        {{-- Wallet Balance --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center">
                <i class="bi bi-wallet2 text-success fs-2 mb-2"></i>
                <div class="text-muted small">Wallet Balance</div>
                <div class="fs-4 fw-bold text-success">
                    {{ activeCurrency()->currency_name }} {{ number_format($wallet?->balance ?? 0, 2) }}
                </div>
                @if(isset($bookingOption) && ($wallet?->balance ?? 0) < ($bookingOption['price'] ?? 0))
                <div class="alert alert-warning mt-2 small">
                    <i class="bi bi-exclamation-triangle"></i> Insufficient balance.
                    <a href="{{ route('agent.wallet.topup') }}">Top up</a>
                </div>
                @endif
            </div>
        </div>

        {{-- Booking Summary --}}
        @if(isset($room) && isset($bookingOption))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Booking Summary</h6></div>
            <div class="card-body small">
                @if(isset($hotelSearch))
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Check-in</span>
                    <strong>{{ $hotelSearch['checkin'] ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Check-out</span>
                    <strong>{{ $hotelSearch['checkout'] ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Guests</span>
                    <strong>{{ $hotelSearch['adults'] ?? 1 }} Adults, {{ $hotelSearch['childs'] ?? 0 }} Children</strong>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total Amount</span>
                    <span class="text-primary fs-5">{{ $bookingOption['currency'] ?? '' }} {{ number_format($bookingOption['price'] ?? 0, 2) }}</span>
                </div>
                <input type="hidden" name="amount" value="{{ $bookingOption['price'] ?? 0 }}">
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
