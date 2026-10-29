@extends('common.layout')
@section('content')
@php
    // Same layout and styles as the flight booking page (.fb-* / .td-* in style.css)
    $active_currency = activeCurrency();
    $cur = $active_currency->currency_name;
    $pkgCurrency = $package->currency ?? 'USD';
    $adultPrice = convertCurrency($package->price, $pkgCurrency, $cur);
    // Total kept exactly as before; the per-person lines now show the same
    // child/infant prices the total uses, converted once into the active currency
    $childPrice = $package->child_price ?? ($package->price * 0.7);
    $infantPrice = $package->infant_price ?? ($package->price * 0.1);
    $childLinePrice = convertCurrency($childPrice, $pkgCurrency, $cur);
    $infantLinePrice = convertCurrency($infantPrice, $pkgCurrency, $cur);
    $totalPrice = ($package->price * $searchParams['adult']) + ($childPrice * $searchParams['child']) + ($infantPrice * $searchParams['infant']);
    $ubImage = $package->images && $package->images->first() ? asset('public/assets/images/' . $package->images->first()->image) : null;
@endphp

<div class="fb-page">
    @if(session('error'))
        <div class="fb-alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <!-- Header -->
    <div class="td-header">
        <h1 class="td-title" dir="auto">{{ $package->name }}</h1>
        <div class="td-meta">
            <span class="td-meta-item"><i class="fas fa-plane-departure"></i> {{ strtoupper($searchParams['origin']) }} <i class="fas fa-arrow-right"></i> {{ strtoupper($searchParams['destination']) }}</span>
            <span class="td-meta-item"><i class="fas fa-calendar"></i> {{ $searchParams['departure_date'] }} – {{ $searchParams['return_date'] }}</span>
            <span class="td-meta-item"><i class="fas fa-users"></i> {{ $searchParams['adult'] + $searchParams['child'] + $searchParams['infant'] }} {{ t('umrahbooking.travellers') }}</span>
        </div>
    </div>

    <form action="{{ route('umrah.booking.store') }}" method="POST" class="td-main">
        @csrf
        <input type="hidden" name="umrah_data" value="{{ encrypt(json_encode($umrah_data)) }}">
        <input type="hidden" name="search_params" value="{{ encrypt(json_encode($searchParams)) }}">

        <div class="td-main-col">
            <!-- Guest information -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('umrahbooking.guest_information') }}</h2>
                <div class="fb-grid">
                    <label class="fb-field">
                        <span>{{ t('umrahbooking.first_name') }} *</span>
                        <input type="text" class="fb-input" name="user[first_name]" placeholder="{{ t('umrahbooking.enter_first_name') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('umrahbooking.last_name') }} *</span>
                        <input type="text" class="fb-input" name="user[last_name]" placeholder="{{ t('umrahbooking.enter_last_name') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('umrahbooking.email_address') }} *</span>
                        <input type="email" class="fb-input" name="user[email]" placeholder="{{ t('umrahbooking.enter_email') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('umrahbooking.phone_number') }} *</span>
                        <input type="tel" class="fb-input" name="user[phone]" placeholder="{{ t('umrahbooking.enter_phone') }}" required>
                    </label>
                    <label class="fb-field is-wide">
                        <span>{{ t('umrahbooking.country') }} *</span>
                        <select class="fb-input" name="user[country]" required>
                            <option value="">{{ t('umrahbooking.select_country') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>{{ $country->country }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            <!-- Travellers -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('umrahbooking.travellers_information') }}</h2>

                @foreach([
                    ['count' => (int) $searchParams['adult'], 'p' => 'adult_', 'hidden' => 'traveller_type_', 'hiddenValue' => 'adults', 'label' => t('umrahbooking.adult'), 'title' => t('umrahbooking.traveller'), 'icon' => 'fa-user'],
                    ['count' => (int) $searchParams['child'], 'p' => 'child_', 'hidden' => 'traveller_child_', 'hiddenValue' => 'child', 'label' => t('umrahbooking.children'), 'title' => t('umrahbooking.child'), 'icon' => 'fa-child'],
                    ['count' => (int) $searchParams['infant'], 'p' => 'infant_', 'hidden' => 'traveller_infant_', 'hiddenValue' => 'infant', 'label' => t('umrahbooking.infant'), 'title' => t('umrahbooking.infant'), 'icon' => 'fa-baby'],
                ] as $g)
                    @for($i = 1; $i <= $g['count']; $i++)
                        <div class="fb-traveller">
                            <div class="fb-traveller-head">
                                <span class="td-fact-icon"><i class="fas {{ $g['icon'] }}"></i></span>
                                <b>{{ $g['title'] }} {{ $i }}</b>
                                <span class="fb-tag">{{ $g['label'] }}</span>
                            </div>
                            <input type="hidden" name="{{ $g['hidden'] }}{{ $i }}" value="{{ $g['hiddenValue'] }}"/>
                            <div class="fb-grid is-3">
                                <label class="fb-field">
                                    <span>{{ t('umrahbooking.gender') }} *</span>
                                    <select class="fb-input" name="{{ $g['p'] }}gender_{{ $i }}" required>
                                        <option value="">{{ t('umrahbooking.select_gender') }}</option>
                                        <option value="male">{{ t('umrahbooking.male') }}</option>
                                        <option value="female">{{ t('umrahbooking.female') }}</option>
                                        <option value="other">{{ t('umrahbooking.other') }}</option>
                                    </select>
                                </label>
                                <label class="fb-field">
                                    <span>{{ t('umrahbooking.first_name') }} *</span>
                                    <input type="text" class="fb-input" name="{{ $g['p'] }}first_name_{{ $i }}" placeholder="{{ t('umrahbooking.enter_first_name') }}" required>
                                </label>
                                <label class="fb-field">
                                    <span>{{ t('umrahbooking.last_name') }} *</span>
                                    <input type="text" class="fb-input" name="{{ $g['p'] }}last_name_{{ $i }}" placeholder="{{ t('umrahbooking.enter_last_name') }}" required>
                                </label>
                            </div>
                        </div>
                    @endfor
                @endforeach
            </div>

            <!-- Payment -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('flightbooking.payment_methods') }}</h2>
                <div class="fb-payments">
                    @foreach($payment as $key => $value)
                        <label class="fb-pay">
                            <input type="radio" name="accept_payment" value="{{ $value->name }}" {{ $loop->first ? 'checked' : '' }}>
                            <img src="{{ url('public/assets/images/settings/payment/' . $value->name . '.png') }}" alt="{{ ucfirst($value->name) }}" class="fb-pay-logo">
                            <span class="fb-pay-text"><b>{{ ucfirst($value->name) }}</b></span>
                        </label>
                    @endforeach
                    @if($agentWallet)
                        <label class="fb-pay">
                            <input type="radio" name="accept_payment" value="agent_wallet">
                            <span class="fb-pay-icon"><i class="fas fa-wallet"></i></span>
                            <span class="fb-pay-text"><b>Agent Wallet</b><small>Available Balance: PKR {{ number_format($agentWallet->balance, 2) }}</small></span>
                        </label>
                    @endif
                </div>
            </div>

            <div class="fb-actions">
                <a href="{{ url()->previous() }}" class="fb-back"><i class="fas fa-arrow-left"></i> {{ t('umrahbooking.back') }}</a>
                <button type="submit" class="td-book-btn fb-pay-btn"><i class="fas fa-check"></i> {{ t('umrahbooking.confirm_booking') }}</button>
            </div>
        </div>

        <!-- Summary -->
        <aside class="td-sidebar">
            <div class="td-card">
                <h2 class="td-side-title">{{ t('umrahbooking.booking_summary') }}</h2>
                <div class="fb-item">
                    @if($ubImage)<img src="{{ $ubImage }}" alt="">@endif
                    <div>
                        <b dir="auto">{{ $package->name }}</b>
                        @if($package->stars)
                            <span class="td-stars">@for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= $package->stars ? 'is-on' : '' }}"></i>@endfor</span>
                        @endif
                    </div>
                </div>
                @if($package->packageType)
                    <div class="td-row"><span><i class="fas fa-tag"></i> {{ t('umrahbooking.package_type') }}</span><b>{{ ucfirst($package->packageType->packege_type) }}</b></div>
                @endif
                <div class="td-row"><span><i class="fas fa-clock"></i> {{ t('umrahbooking.duration') }}</span><b>{{ $package->duration }}</b></div>
                <div class="td-row"><span><i class="fas fa-kaaba"></i> {{ t('umrahbooking.makkah') }}</span><b>{{ $package->night_in_mekkah }} {{ t('umrahbooking.nights') }}</b></div>
                <div class="td-row"><span><i class="fas fa-mosque"></i> {{ t('umrahbooking.madina') }}</span><b>{{ $package->night_in_madina }} {{ t('umrahbooking.nights') }}</b></div>
                @if($package->class)
                    <div class="td-row"><span><i class="fas fa-plane"></i> {{ t('umrahbooking.flight_class') }}</span><b>{{ $package->class }}</b></div>
                @endif
                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('umrahbooking.departure_date') }}</span><b>{{ $searchParams['departure_date'] }}</b></div>
                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('umrahbooking.return_date') }}</span><b>{{ $searchParams['return_date'] }}</b></div>
                <div class="td-row"><span><i class="fas fa-user"></i> {{ t('umrahbooking.price_per_person') }}</span><b>{{ $cur }} {{ number_format($adultPrice) }}</b></div>
                <div class="td-row"><span>{{ t('umrahbooking.adults') }} ({{ $searchParams['adult'] }} × {{ $cur }} {{ number_format($adultPrice) }})</span><b>{{ $cur }} {{ number_format($adultPrice * $searchParams['adult']) }}</b></div>
                @if($searchParams['child'] > 0)
                    <div class="td-row"><span>{{ t('umrahbooking.children') }} ({{ $searchParams['child'] }} × {{ $cur }} {{ number_format($childLinePrice) }})</span><b>{{ $cur }} {{ number_format($childLinePrice * $searchParams['child']) }}</b></div>
                @endif
                @if($searchParams['infant'] > 0)
                    <div class="td-row"><span>{{ t('umrahbooking.infants') }} ({{ $searchParams['infant'] }} × {{ $cur }} {{ number_format($infantLinePrice) }})</span><b>{{ $cur }} {{ number_format($infantLinePrice * $searchParams['infant']) }}</b></div>
                @endif
                <div class="td-total">
                    <span>{{ t('umrahbooking.total_amount') }}</span>
                    <b>{{ $cur }} {{ number_format(convertCurrency($totalPrice, $pkgCurrency, $cur)) }}</b>
                </div>
                <p class="td-secure"><i class="fas fa-lock"></i> {{ t('umrahbooking.secure_payment') }}</p>
            </div>
        </aside>
    </form>
</div>
@endsection
