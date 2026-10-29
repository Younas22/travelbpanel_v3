@extends('common.layout')
@section('content')
@php
    // Same layout and styles as the flight booking page (.fb-* / .td-* in style.css)
    $active_currency = activeCurrency();
    $cur = $active_currency->currency_name;
    $adultPrice = convertCurrency($tour->price, $tour->currency ?? 'USD', $cur);
    // Total kept exactly as before; the per-child line now shows the same
    // child price the total uses (it used to show 0 when no child price was set)
    $childPrice = $tour->child_price ?? ($adultPrice * 0.7);
    $childLinePrice = $childPrice;
    $totalPrice = ($adultPrice * $searchParams['adult']) + ($childPrice * $searchParams['child']);
    $tbImage = $tour->images && $tour->images->first() ? asset('public/assets/images/' . $tour->images->first()->image) : null;
    $travellerNo = 0;
@endphp

<div class="fb-page">
    @if(session('error'))
        <div class="fb-alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <!-- Header -->
    <div class="td-header">
        <h1 class="td-title" dir="auto">{{ $tour->name }}</h1>
        <div class="td-meta">
            <span class="td-meta-item"><i class="fas fa-location-dot"></i> {{ $tour->location_name ?? $tour->loaction }}</span>
            <span class="td-meta-item"><i class="fas fa-calendar"></i> {{ $searchParams['original_start_date'] }} – {{ $searchParams['original_end_date'] }}</span>
            <span class="td-meta-item"><i class="fas fa-users"></i> {{ $searchParams['adult'] }} {{ t('tourbooking.adults') }}@if($searchParams['child'] > 0), {{ $searchParams['child'] }} {{ t('tourbooking.children') }}@endif</span>
        </div>
    </div>

    <form action="{{ route('tour.booking.store') }}" method="POST" class="td-main">
        @csrf
        <input type="hidden" name="tour_data" value="{{ encrypt(json_encode($tour_data)) }}">
        <input type="hidden" name="search_params" value="{{ encrypt(json_encode($searchParams)) }}">

        <div class="td-main-col">
            <!-- Guest information -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('tourbooking.guest_information') }}</h2>
                <div class="fb-grid">
                    <label class="fb-field">
                        <span>{{ t('tourbooking.first_name') }} *</span>
                        <input type="text" class="fb-input" name="user[first_name]" placeholder="{{ t('tourbooking.enter_first_name') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('tourbooking.last_name') }} *</span>
                        <input type="text" class="fb-input" name="user[last_name]" placeholder="{{ t('tourbooking.enter_last_name') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('tourbooking.email_address') }} *</span>
                        <input type="email" class="fb-input" name="user[email]" placeholder="example@email.com" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('tourbooking.phone_number') }} *</span>
                        <input type="tel" class="fb-input" name="user[phone]" placeholder="+92 300 1234567" required>
                    </label>
                    <label class="fb-field is-wide">
                        <span>{{ t('tourbooking.country') }} *</span>
                        <select class="fb-input" name="user[country]" required>
                            <option value="">{{ t('flightbooking.select_country') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>{{ $country->country }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            <!-- Travellers -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('tourbooking.travellers_information') }}</h2>

                @foreach([
                    ['count' => (int) $searchParams['adult'], 'p' => 'adult_', 'hidden' => 'traveller_type_', 'hiddenValue' => 'adults', 'label' => t('tourbooking.adult'), 'title' => t('tourbooking.traveller'), 'icon' => 'fa-user'],
                    ['count' => (int) $searchParams['child'], 'p' => 'child_', 'hidden' => 'traveller_child_', 'hiddenValue' => 'child', 'label' => t('tourbooking.children'), 'title' => t('tourbooking.child'), 'icon' => 'fa-child'],
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
                                    <span>{{ t('tourbooking.gender') }} *</span>
                                    <select class="fb-input" name="{{ $g['p'] }}gender_{{ $i }}" required>
                                        <option value="">{{ t('tourbooking.select_gender') }}</option>
                                        <option value="male">{{ t('tourbooking.male') }}</option>
                                        <option value="female">{{ t('tourbooking.female') }}</option>
                                        <option value="other">{{ t('tourbooking.other') }}</option>
                                    </select>
                                </label>
                                <label class="fb-field">
                                    <span>{{ t('tourbooking.first_name') }} *</span>
                                    <input type="text" class="fb-input" name="{{ $g['p'] }}first_name_{{ $i }}" placeholder="{{ t('tourbooking.enter_first_name') }}" required>
                                </label>
                                <label class="fb-field">
                                    <span>{{ t('tourbooking.last_name') }} *</span>
                                    <input type="text" class="fb-input" name="{{ $g['p'] }}last_name_{{ $i }}" placeholder="{{ t('tourbooking.enter_last_name') }}" required>
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
                <a href="{{ url()->previous() }}" class="fb-back"><i class="fas fa-arrow-left"></i> {{ t('tourbooking.back') }}</a>
                <button type="submit" class="td-book-btn fb-pay-btn"><i class="fas fa-check"></i> {{ t('tourbooking.confirm_booking') }}</button>
            </div>
        </div>

        <!-- Summary -->
        <aside class="td-sidebar">
            <div class="td-card">
                <h2 class="td-side-title">{{ t('tourbooking.booking_summary') }}</h2>
                <div class="fb-item">
                    @if($tbImage)<img src="{{ $tbImage }}" alt="">@endif
                    <div>
                        <b dir="auto">{{ $tour->name }}</b>
                        @if($tour->stars)
                            <span class="td-stars">@for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= $tour->stars ? 'is-on' : '' }}"></i>@endfor</span>
                        @endif
                    </div>
                </div>
                @if($tour->packageType)
                    <div class="td-row"><span><i class="fas fa-tag"></i> {{ t('tourbooking.tour_type') }}</span><b>{{ $tour->packageType->packege_type }}</b></div>
                @endif
                <div class="td-row"><span><i class="fas fa-clock"></i> {{ t('tourbooking.duration') }}</span><b>{{ $tour->duration }}</b></div>
                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('tourbooking.departure_date') }}</span><b>{{ $searchParams['original_start_date'] }}</b></div>
                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('tourbooking.return_date') }}</span><b>{{ $searchParams['original_end_date'] }}</b></div>
                <div class="td-row"><span><i class="fas fa-user"></i> {{ t('tourbooking.price_per_person') }}</span><b>{{ $cur }} {{ number_format($adultPrice) }}</b></div>
                <div class="td-row"><span>{{ t('tourbooking.adults') }} ({{ $searchParams['adult'] }} × {{ $cur }} {{ number_format($adultPrice) }})</span><b>{{ $cur }} {{ number_format($adultPrice * $searchParams['adult']) }}</b></div>
                @if($searchParams['child'] > 0)
                    <div class="td-row"><span>{{ t('tourbooking.children') }} ({{ $searchParams['child'] }} × {{ $cur }} {{ number_format($childLinePrice) }})</span><b>{{ $cur }} {{ number_format($childLinePrice * $searchParams['child']) }}</b></div>
                @endif
                <div class="td-total">
                    <span>{{ t('tourbooking.total_amount') }}</span>
                    <b>{{ $cur }} {{ number_format($totalPrice) }}</b>
                </div>
                <p class="td-secure"><i class="fas fa-lock"></i> {{ t('tourbooking.secure_payment') }}</p>
            </div>
        </aside>
    </form>
</div>
@endsection
