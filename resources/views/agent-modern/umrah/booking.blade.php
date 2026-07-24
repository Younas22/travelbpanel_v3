{{-- Disclosure: mirrors agent/umrah/booking.blade.php, which is dead/unreachable
     code — no route ever calls a controller method that returns this view.
     Kept for file-tree parity with the exhaustive file list this task specifies. --}}
@extends('agent-modern.layouts.app')
@section('title', 'Book Umrah Package')

@section('content')
<div class="mb-3">
    <a href="{{ route('agent.umrah.details', \Str::slug($package->name)) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Package
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('agent.umrah.booking.confirm') }}">
            @csrf
            <input type="hidden" name="package_data" value="{{ encrypt(json_encode($packageData)) }}">
            <input type="hidden" name="search_params" value="{{ encrypt(json_encode(['adult' => $adult, 'child' => $child])) }}">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Traveller Details</h6></div>
                <div class="card-body">
                    @for($i = 1; $i <= $adult; $i++)
                    <h6 class="text-muted small mb-2">Adult {{ $i }}</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-2">
                            <select name="adult_gender_{{ $i }}" class="form-select form-select-sm">
                                <option value="Mr">Mr</option><option value="Mrs">Mrs</option><option value="Ms">Ms</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="adult_first_name_{{ $i }}" class="form-control form-control-sm" placeholder="First Name" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="adult_last_name_{{ $i }}" class="form-control form-control-sm" placeholder="Last Name" required>
                        </div>
                    </div>
                    @endfor

                    @for($i = 1; $i <= $child; $i++)
                    <h6 class="text-muted small mb-2">Child {{ $i }}</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="text" name="child_first_name_{{ $i }}" class="form-control form-control-sm" placeholder="First Name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="child_last_name_{{ $i }}" class="form-control form-control-sm" placeholder="Last Name" required>
                        </div>
                    </div>
                    @endfor

                    <hr>
                    <h6 class="text-muted small mb-2">Contact</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="email" name="user[email]" class="form-control form-control-sm" placeholder="Email *" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="user[phone]" class="form-control form-control-sm" placeholder="Phone" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="user[first_name]" class="form-control form-control-sm" placeholder="First Name">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="user[last_name]" class="form-control form-control-sm" placeholder="Last Name">
                        </div>
                        @if(isset($countries))
                        <div class="col-md-6">
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
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center">
                <i class="bi bi-wallet2 text-success fs-2 mb-2"></i>
                <div class="text-muted small">Wallet Balance</div>
                <div class="fs-4 fw-bold {{ ($wallet?->balance ?? 0) >= $totalPrice ? 'text-success' : 'text-danger' }}">
                    {{ $currency->currency_name }} {{ number_format($wallet?->balance ?? 0, 2) }}
                </div>
                @if(($wallet?->balance ?? 0) < $totalPrice)
                <div class="alert alert-warning mt-2 small">
                    Insufficient balance. <a href="{{ route('agent.wallet.topup') }}">Top up</a>
                </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Booking Summary</h6></div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Package</span>
                    <strong>{{ $package->name }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Adults ({{ $adult }} × {{ $currency->currency_name }} {{ number_format($packageData['price'], 0) }})</span>
                    <span>{{ number_format($packageData['price'] * $adult, 2) }}</span>
                </div>
                @if($child > 0)
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Children ({{ $child }} × {{ $currency->currency_name }} {{ number_format($packageData['child_price'], 0) }})</span>
                    <span>{{ number_format($packageData['child_price'] * $child, 2) }}</span>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span class="text-primary fs-5">{{ $currency->currency_name }} {{ number_format($totalPrice, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
