{{-- Disclosure: mirrors agent/flights/booking.blade.php — dead/unreachable
     code, see agent-modern/flights/index.blade.php for full explanation. --}}
@extends('agent-modern.layouts.app')
@section('title', 'Flight Booking')

@section('content')
<div class="mb-3">
    <a href="{{ route('agent.flights.results') }}" class="ap-btn-outline">
        <i class="bi bi-arrow-left"></i> Back to Results
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('agent.flights.booking.confirm') }}">
            @csrf
            <input type="hidden" name="flight_data" value="{{ request('flight_data') ?? old('flight_data') }}">

            {{-- Traveller Details --}}
            @php $adultCount = $searchParams['adult'] ?? 1; $childCount = $searchParams['child'] ?? 0; @endphp

            <div class="ap-card mb-3">
                <div class="ap-card-header"><h6 class="mb-0">Traveller Details</h6></div>
                <div class="ap-card-body">
                    @for($i = 1; $i <= $adultCount; $i++)
                    <h6 class="text-muted small mb-2">Adult {{ $i }}</h6>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
                        <div>
                            <select name="adult_gender_{{ $i }}" class="form-select form-select-sm">
                                <option value="Mr">Mr</option><option value="Mrs">Mrs</option><option value="Ms">Ms</option>
                            </select>
                        </div>
                        <div>
                            <input type="text" name="adult_first_name_{{ $i }}" class="form-control form-control-sm" placeholder="First Name" required>
                        </div>
                        <div>
                            <input type="text" name="adult_last_name_{{ $i }}" class="form-control form-control-sm" placeholder="Last Name" required>
                        </div>
                        <div>
                            <input type="date" name="adult_dob_{{ $i }}" class="form-control form-control-sm" placeholder="DOB">
                        </div>
                        <div>
                            <input type="text" name="adult_passport_{{ $i }}" class="form-control form-control-sm" placeholder="Passport No">
                        </div>
                        <div>
                            <input type="date" name="adult_expiry_{{ $i }}" class="form-control form-control-sm" placeholder="Passport Expiry">
                        </div>
                        <div>
                            @if(isset($countries))
                            <select name="adult_nationality_{{ $i }}" class="form-select form-select-sm">
                                @foreach($countries as $c)
                                <option value="{{ $c->country_code }}">{{ $c->country }}</option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                    </div>
                    @endfor

                    @for($i = 1; $i <= $childCount; $i++)
                    <h6 class="text-muted small mb-2">Child {{ $i }}</h6>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-3">
                        <div>
                            <input type="text" name="child_first_name_{{ $i }}" class="form-control form-control-sm" placeholder="First Name" required>
                        </div>
                        <div>
                            <input type="text" name="child_last_name_{{ $i }}" class="form-control form-control-sm" placeholder="Last Name" required>
                        </div>
                        <div>
                            <input type="date" name="child_dob_{{ $i }}" class="form-control form-control-sm" placeholder="DOB">
                        </div>
                    </div>
                    @endfor

                    <hr>
                    <h6 class="text-muted small mb-2">Contact</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <input type="email" name="user[email]" class="form-control form-control-sm" placeholder="Email *" required>
                        </div>
                        <div>
                            <input type="text" name="user[phone]" class="form-control form-control-sm" placeholder="Phone" required>
                        </div>
                        <div>
                            <input type="text" name="user[first_name]" class="form-control form-control-sm" placeholder="First Name">
                        </div>
                        <div>
                            <input type="text" name="user[last_name]" class="form-control form-control-sm" placeholder="Last Name">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="ap-btn-success w-full py-3">
                <i class="bi bi-wallet2"></i> Confirm & Pay from Wallet
            </button>
        </form>
    </div>

    <div class="lg:col-span-1">
        <div class="ap-card mb-3">
            <div class="ap-card-body text-center">
                <i class="bi bi-wallet2 text-success fs-2 mb-2"></i>
                <div class="text-muted small">Wallet Balance</div>
                <div class="fs-4 fw-bold text-success">
                    {{ activeCurrency()->currency_name }} {{ number_format($wallet?->balance ?? 0, 2) }}
                </div>
            </div>
        </div>

        @if(isset($flightData))
        <div class="ap-card">
            <div class="ap-card-header"><h6 class="mb-0">Flight Summary</h6></div>
            <div class="ap-card-body small">
                <div class="flex justify-between mb-1">
                    <span class="text-muted">Route</span>
                    <strong>{{ $flightData['origin'] ?? '' }} → {{ $flightData['destination'] ?? '' }}</strong>
                </div>
                <div class="flex justify-between mb-1">
                    <span class="text-muted">Airline</span>
                    <span>{{ $flightData['airline'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span class="text-muted">Date</span>
                    <span>{{ $flightData['departure_date'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span class="text-muted">Passengers</span>
                    <span>{{ ($searchParams['adult'] ?? 1) }} Adults, {{ ($searchParams['child'] ?? 0) }} Children</span>
                </div>
                <hr>
                <div class="flex justify-between fw-bold">
                    <span>Total</span>
                    <span class="text-primary">{{ $flightData['currency'] ?? activeCurrency()->currency_name }} {{ number_format($flightData['price'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
