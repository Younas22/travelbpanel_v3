@extends('common.layout')
@section('content')
@php
    // Booker details: an agent books for a traveller, so this section is
    // skipped for them — FlightsController::flight_booking() fills it from the
    // agent's own profile. A logged-in customer sees their own details,
    // pre-filled and read-only; only a guest types them in.
    $loggedInCustomer = (auth()->check() && auth()->user()->isCustomer()) ? auth()->user() : null;
    $isAgent = auth()->check() && auth()->user()->isAgent();
    $matchedCountry = null;
    if ($loggedInCustomer && $loggedInCustomer->country) {
        $matchedCountry = $countries->first(fn($c) => strcasecmp($c->country, $loggedInCustomer->country) === 0
            || strcasecmp($c->country_code, $loggedInCustomer->country) === 0);
    }

    $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    $isRound = ($session_data['trip_type'] ?? '') === 'round';
    $legs = $isRound ? array_slice($routes->segments, 0, 2) : $routes->segments;
    $first = $routes->segments[0][0];
    $logoUrl = fn($code) => 'https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/' . $code . '.svg';

    // Traveller groups. Field names are exactly what flight_booking() reads —
    // including the children nationality name, which the backend expects as is.
    $travellerGroups = [
        [
            'count' => (int) ($session_data['adults'] ?? 0), 'label' => t('flightbooking.adult'), 'icon' => 'fa-user',
            'p' => '', 'type_field' => 'traveller_type_', 'type_value' => 'adults', 'nat' => 'nationality_',
            'dob_years' => range(2023, 1920), 'passport' => true,
        ],
        [
            'count' => (int) ($session_data['children'] ?? 0), 'label' => t('flightbooking.children'), 'icon' => 'fa-child',
            'p' => 'children_', 'type_field' => 'children_traveller_type_', 'type_value' => 'children', 'nat' => 'nchildren_ationality_',
            'dob_years' => range((int) date('Y'), (int) date('Y') - 17), 'passport' => true,
        ],
        [
            'count' => (int) ($session_data['infants'] ?? 0), 'label' => t('flightbooking.infant'), 'icon' => 'fa-baby',
            'p' => 'infant_', 'type_field' => 'infant_traveller_type_', 'type_value' => 'infant', 'nat' => null,
            'dob_years' => range((int) date('Y'), (int) date('Y') - 2), 'passport' => false,
        ],
    ];

    // Shown converted into the site's active currency, using the admin's
    // own exchange rates (not just relabeled).
    $__activeCurrency = activeCurrency();
    $__activeCurrencyCode = $__activeCurrency->currency_name ?? $first->currency;
    $__displayedFare = $__activeCurrency
        ? convertCurrency($first->price, $first->currency, $__activeCurrencyCode)
        : $first->price;
    $travellerNo = 0;
@endphp

<div class="fb-page">
    @if(session('error'))
        <div class="fb-alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <!-- Header -->
    <div class="td-header">
        <h1 class="td-title">
            {{ $first->departure->airport }}
            <i class="fas {{ $isRound ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right' }} tl-title-arrow"></i>
            {{ end($routes->segments[0])->arrival->airport }}
        </h1>
        <div class="td-meta">
            <span class="td-meta-item"><i class="fas fa-calendar"></i> {{ $first->departure->date_convert }}@if($isRound && isset($routes->segments[1][0])) – {{ $routes->segments[1][0]->departure->date_convert }}@endif</span>
            <span class="td-meta-item"><i class="fas fa-users"></i> {{ ($session_data['adults'] ?? 0) + ($session_data['children'] ?? 0) + ($session_data['infants'] ?? 0) }} {{ t('flightbooking.traveller') }}</span>
        </div>
    </div>

    <form method="post" id="MSF-multiStepForm" action="{{ route('flight_booking') }}" class="td-main">
        @csrf
        <input type="hidden" name="flight_segment" value="{{ encrypt(json_encode($routes))}}">
        <input type="hidden" name="booking_data" value="{{ encrypt(json_encode($booking_data)) }}">
        <input type="hidden" name="currency" value="{{ encrypt($first->currency)}}">
        <input type="hidden" name="price" value="{{ encrypt($first->price)}}">
        <input type="hidden" name="partner_name" value="{{ encrypt($first->supplier)}}">

        <div class="td-main-col">
            @unless($isAgent)
            <!-- Personal information -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('flightbooking.personal_information') }}</h2>
                @if($loggedInCustomer)
                    <p class="fb-note">Using your account details. To change these, update your <a href="{{ route('user.profile.index') }}">profile</a>.</p>
                @endif
                <div class="fb-grid">
                    <label class="fb-field">
                        <span>{{ t('flightbooking.first_name') }} *</span>
                        <input type="text" class="fb-input" name="user[first_name]" placeholder="{{ t('flightbooking.enter_first_name') }}" value="{{ old('user.first_name', $loggedInCustomer->first_name ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('flightbooking.last_name') }} *</span>
                        <input type="text" class="fb-input" name="user[last_name]" placeholder="{{ t('flightbooking.enter_last_name') }}" value="{{ old('user.last_name', $loggedInCustomer->last_name ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('flightbooking.email') }} *</span>
                        <input type="email" class="fb-input" name="user[email]" placeholder="example@gmail.com" value="{{ old('user.email', $loggedInCustomer->email ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('flightbooking.phone_number') }} *</span>
                        <input type="tel" class="fb-input" name="user[phone]" value="{{ old('user.phone', $loggedInCustomer->phone ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </label>
                    <label class="fb-field is-wide">
                        <span>{{ t('flightbooking.address') }} *</span>
                        <input type="text" class="fb-input" name="user[address]" placeholder="{{ t('flightbooking.enter_your_address') }}" value="{{ old('user.address', $loggedInCustomer->address ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('flightbooking.city') }} *</span>
                        <input type="text" class="fb-input" name="user[city]" placeholder="{{ t('flightbooking.enter_city') }}" value="{{ old('user.city', $loggedInCustomer->city ?? '') }}" @if($loggedInCustomer) readonly @else required @endif>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('flightbooking.country') }} *</span>
                        @if($loggedInCustomer)
                            <input type="text" class="fb-input" value="{{ $matchedCountry->country ?? $loggedInCustomer->country ?? '—' }}" readonly>
                            <input type="hidden" name="user[country]" value="{{ old('user.country', $matchedCountry ? strtolower($matchedCountry->country_code) : '') }}">
                        @else
                            <select class="fb-input" name="user[country]" required>
                                <option value="">{{ t('flightbooking.select_country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>{{ $country->country }}</option>
                                @endforeach
                            </select>
                        @endif
                    </label>
                </div>
            </div>
            @endunless

            <!-- Travellers -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('flightbooking.travellers_information') }}</h2>

                @foreach($travellerGroups as $g)
                    @for($i = 1; $i <= $g['count']; $i++)
                        @php $travellerNo++; $p = $g['p']; @endphp
                        <div class="fb-traveller">
                            <div class="fb-traveller-head">
                                <span class="td-fact-icon"><i class="fas {{ $g['icon'] }}"></i></span>
                                <b>{{ t('flightbooking.traveller') }} {{ $travellerNo }}</b>
                                <span class="fb-tag">{{ $g['label'] }}</span>
                            </div>
                            <input type="hidden" name="{{ $g['type_field'] }}{{ $i }}" value="{{ $g['type_value'] }}"/>

                            <div class="fb-grid">
                                <label class="fb-field">
                                    <span>{{ t('flightbooking.first_name') }} *</span>
                                    <input type="text" class="fb-input" name="{{ $p }}first_name_{{ $i }}" placeholder="{{ t('flightbooking.as_per_passport') }}" required>
                                </label>
                                <label class="fb-field">
                                    <span>{{ t('flightbooking.last_name') }} *</span>
                                    <input type="text" class="fb-input" name="{{ $p }}last_name_{{ $i }}" placeholder="{{ t('flightbooking.as_per_passport') }}" required>
                                </label>
                                <label class="fb-field">
                                    <span>{{ t('flightbooking.gender') }} *</span>
                                    <select class="fb-input" name="{{ $p }}gender_{{ $i }}" required>
                                        <option value="">{{ t('flightbooking.select_gender') }}</option>
                                        <option value="m">{{ t('flightbooking.male') }}</option>
                                        <option value="f">{{ t('flightbooking.female') }}</option>
                                        <option value="o">{{ t('flightbooking.other') }}</option>
                                    </select>
                                </label>
                                <div class="fb-field">
                                    <span>{{ t('flightbooking.date_of_birth') }} *</span>
                                    <div class="fb-date">
                                        <select class="fb-input" name="{{ $p }}dob_day_{{ $i }}" required aria-label="{{ t('flightbooking.day') }}">
                                            <option value="">{{ t('flightbooking.day') }}</option>
                                            @for($d = 1; $d <= 31; $d++)<option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>@endfor
                                        </select>
                                        <select class="fb-input" name="{{ $p }}dob_month_{{ $i }}" required aria-label="{{ t('flightbooking.month') }}">
                                            <option value="">{{ t('flightbooking.month') }}</option>
                                            @foreach($months as $m => $key)<option value="{{ $m + 1 }}">{{ t('flightbooking.' . $key) }}</option>@endforeach
                                        </select>
                                        <select class="fb-input" name="{{ $p }}dob_year_{{ $i }}" required aria-label="{{ t('flightbooking.year') }}">
                                            <option value="">{{ t('flightbooking.year') }}</option>
                                            @foreach($g['dob_years'] as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @if($g['passport'])
                                <div class="fb-passport">
                                    <div class="fb-passport-title"><i class="fas fa-passport"></i> {{ t('flightbooking.passport_details') }}</div>
                                    <div class="fb-grid">
                                        <label class="fb-field">
                                            <span>{{ t('flightbooking.passport_number') }} *</span>
                                            <input type="text" class="fb-input" name="{{ $p }}passport_{{ $i }}" placeholder="AB123456" required>
                                        </label>
                                        <label class="fb-field">
                                            <span>{{ t('flightbooking.nationality') }} *</span>
                                            <select class="fb-input" name="{{ $g['nat'] }}{{ $i }}" required>
                                                <option value="">{{ t('flightbooking.select_country') }}</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>{{ $country->country }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <div class="fb-field">
                                            <span>{{ t('flightbooking.passport_issuance_date') }} *</span>
                                            <div class="fb-date">
                                                <select class="fb-input" name="{{ $p }}passport_issuance_day_{{ $i }}" required aria-label="{{ t('flightbooking.day') }}">
                                                    <option value="">{{ t('flightbooking.day') }}</option>
                                                    @for($d = 1; $d <= 31; $d++)<option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>@endfor
                                                </select>
                                                <select class="fb-input" name="{{ $p }}passport_issuance_month_{{ $i }}" required aria-label="{{ t('flightbooking.month') }}">
                                                    <option value="">{{ t('flightbooking.month') }}</option>
                                                    @foreach($months as $m => $key)<option value="{{ $m + 1 }}">{{ t('flightbooking.' . $key) }}</option>@endforeach
                                                </select>
                                                <select class="fb-input" name="{{ $p }}passport_issuance_year_{{ $i }}" required aria-label="{{ t('flightbooking.year') }}">
                                                    <option value="">{{ t('flightbooking.year') }}</option>
                                                    @for($y = 2026; $y >= 1920; $y--)<option value="{{ $y }}">{{ $y }}</option>@endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="fb-field">
                                            <span>{{ t('flightbooking.passport_expiry_date') }} *</span>
                                            <div class="fb-date">
                                                <select class="fb-input" name="{{ $p }}passport_day_expiry_{{ $i }}" required aria-label="{{ t('flightbooking.day') }}">
                                                    <option value="">{{ t('flightbooking.day') }}</option>
                                                    @for($d = 1; $d <= 31; $d++)<option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>@endfor
                                                </select>
                                                <select class="fb-input" name="{{ $p }}passport_month_expiry_{{ $i }}" required aria-label="{{ t('flightbooking.month') }}">
                                                    <option value="">{{ t('flightbooking.month') }}</option>
                                                    @foreach($months as $m => $key)<option value="{{ $m + 1 }}">{{ t('flightbooking.' . $key) }}</option>@endforeach
                                                </select>
                                                <select class="fb-input" name="{{ $p }}passport_year_expiry_{{ $i }}" required aria-label="{{ t('flightbooking.year') }}">
                                                    <option value="">{{ t('flightbooking.year') }}</option>
                                                    @for($y = 2043; $y >= 2023; $y--)<option value="{{ $y }}">{{ $y }}</option>@endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endfor
                @endforeach
            </div>

            <!-- Payment -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('flightbooking.payment_methods') }}</h2>
                <div class="fb-payments">
                    @if($isAgent)
                        {{-- Agents always pay from their wallet — no other gateway is offered. --}}
                        @if($agentWallet)
                            <label class="fb-pay">
                                <input type="radio" name="accept_payment" value="agent_wallet" checked>
                                <span class="fb-pay-icon"><i class="fas fa-wallet"></i></span>
                                <span class="fb-pay-text"><b>Agent Wallet</b><small>Available Balance: {{ $walletDisplayCurrency }} {{ number_format($walletDisplayBalance, 2) }}</small></span>
                            </label>
                        @else
                            <div class="fb-alert">No wallet found for your account. Please contact admin.</div>
                        @endif
                    @else
                        @foreach($payment as $key => $value)
                            <label class="fb-pay">
                                <input type="radio" name="accept_payment" value="{{ $value->name }}" checked>
                                <img src="{{ url('public/assets/images/settings/payment/' . $value->name . '.png') }}" alt="{{ ucfirst($value->name) }}" class="fb-pay-logo">
                                <span class="fb-pay-text"><b>{{ ucfirst($value->name) }}</b></span>
                            </label>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="fb-actions">
                <button type="button" class="fb-back" onclick="history.back()"><i class="fas fa-arrow-left"></i> {{ t('flightbooking.back') }}</button>
                <button type="submit" class="td-book-btn fb-pay-btn">{{ t('flightbooking.pay_now') }} <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="td-sidebar">
            <div class="fb-timer">
                <i class="fas fa-hourglass-half"></i>
                <span>{{ t('flightbooking.complete_booking_in') }}</span>
                <b id="timer">30:00</b>
            </div>

            @foreach($legs as $legIndex => $leg)
                @php $legLast = end($leg); $legStops = count($leg) - 1; @endphp
                <div class="td-card">
                    <h2 class="td-side-title">{{ $legIndex === 0 ? t('flightbooking.outbound_flight') : t('flightbooking.return_flight') }}</h2>
                    <div class="fb-airline">
                        <img src="{{ $logoUrl($leg[0]->carrier->operating ?? $leg[0]->carrier->marketing) }}" alt="" onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';">
                        <div><b>{{ $leg[0]->airline_name }}</b><small>Flight {{ $leg[0]->flight_number }}</small></div>
                    </div>
                    <div class="fb-route">
                        <div><b>{{ $leg[0]->departure->time }}</b><span>{{ $leg[0]->departure->airport }}</span></div>
                        <div class="fl-line">
                            <small>{{ $leg[0]->total_duration }}</small>
                            <span class="fl-track"><i class="fas fa-plane"></i></span>
                            <small class="{{ $legStops == 0 ? 'is-direct' : '' }}">{{ $legStops == 0 ? t('flightlist.direct') : $legStops . ' ' . t('flightbooking.stop') }}</small>
                        </div>
                        <div class="is-end"><b>{{ $legLast->arrival->time }}</b><span>{{ $legLast->arrival->airport }}</span></div>
                    </div>
                    <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('flightbooking.date') }}</span><b>{{ $leg[0]->departure->date_convert }}</b></div>
                    <div class="td-row"><span><i class="fas fa-suitcase-rolling"></i> {{ t('flightbooking.baggage') }}</span><b>{{ $leg[0]->baggage }} + {{ $leg[0]->cabin_baggage }}</b></div>
                </div>
            @endforeach

            <div class="td-card">
                <h2 class="td-side-title">{{ t('flightbooking.price_summary') }}</h2>
                <div class="td-row"><span>{{ t('flightbooking.base_fare') }}</span><b>{{ $__activeCurrencyCode }} {{ number_format($__displayedFare, 2) }}</b></div>
                <div class="td-row"><span>{{ t('flightbooking.taxes_fees') }}</span><b>{{ $__activeCurrencyCode }} 0.00</b></div>
                <div class="td-total">
                    <span>{{ t('flightbooking.total') }}</span>
                    <b>{{ $__activeCurrencyCode }} {{ number_format($__displayedFare, 2) }}</b>
                </div>
            </div>
        </aside>
    </form>
</div>

<script>
    // 30 minute booking timer
    document.addEventListener('DOMContentLoaded', function () {
        let timeLeft = 30 * 60;
        const timerDisplay = document.getElementById('timer');
        const interval = setInterval(() => {
            timerDisplay.textContent = String(Math.floor(timeLeft / 60)).padStart(2, '0') + ':' + String(timeLeft % 60).padStart(2, '0');
            if (timeLeft < 300) timerDisplay.closest('.fb-timer').classList.add('is-ending');
            if (timeLeft === 0) {
                clearInterval(interval);
                timerDisplay.textContent = '00:00';
                alert('Booking time expired! Please start over.');
            }
            timeLeft--;
        }, 1000);
    });
</script>

@endsection
