@extends('common.layout')
@section('page_class', 'view-flight-checkout')
@section('content')
@php
    // Same three-step checkout as the hotel (contact → travellers → payment),
    // stepped in the browser and sent in one POST to flight_booking(). The
    // contact step is skipped for agents: flight_booking() takes it from the
    // agent's profile. A logged-in customer books with their account details.
    $loggedInCustomer = (auth()->check() && auth()->user()->isCustomer()) ? auth()->user() : null;
    $isAgent = auth()->check() && auth()->user()->isAgent();
    $matchedCountry = null;
    if ($loggedInCustomer && $loggedInCustomer->country) {
        $matchedCountry = $countries->first(fn($c) => strcasecmp($c->country, $loggedInCustomer->country) === 0
            || strcasecmp($c->country_code, $loggedInCustomer->country) === 0);
    }

    $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    $isRound = ($session_data['trip_type'] ?? '') === 'round';
    $first = $routes->segments[0][0];
    // the booked flight as plain arrays (same shape the invoice reads back)
    $legs = json_decode(json_encode($routes), true)['segments'] ?? [];
    $legs = $isRound ? array_slice($legs, 0, 2) : $legs;
    $outLast = end($legs[0]);
    $logoUrl = fn($code) => 'https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/' . $code . '.svg';
    $bagText = fn($raw) => ($raw === null || $raw === '') ? '—' : (is_numeric($raw) ? (int) $raw . ' PC' : (string) $raw);

    $adults = (int) ($session_data['adults'] ?? 0);
    $childrenCount = (int) ($session_data['children'] ?? 0);
    $infants = (int) ($session_data['infants'] ?? 0);

    // Traveller groups. Field names are exactly what flight_booking() reads —
    // including the children nationality name, which the backend expects as is.
    $travellerGroups = [
        [
            'count' => $adults, 'label' => t('flightbooking.adult'),
            'p' => '', 'type_field' => 'traveller_type_', 'type_value' => 'adults', 'nat' => 'nationality_',
            'dob_years' => range(2023, 1920), 'passport' => true,
        ],
        [
            'count' => $childrenCount, 'label' => t('flightbooking.children'),
            'p' => 'children_', 'type_field' => 'children_traveller_type_', 'type_value' => 'children', 'nat' => 'nchildren_ationality_',
            'dob_years' => range((int) date('Y'), (int) date('Y') - 17), 'passport' => true,
        ],
        [
            'count' => $infants, 'label' => t('flightbooking.infant'),
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
    // A logged-in agent's own fee (set on the dashboard / flight list) is shown on top
    // of the fare, only to that agent. The fare itself is what is booked and paid.
    $__agentFee = $isAgent ? auth()->user()->agentMarkupIn($__activeCurrencyCode) : 0;
    $fareText = $__activeCurrencyCode . ' ' . number_format($__displayedFare, 2);
    $totalText = $__activeCurrencyCode . ' ' . number_format($__displayedFare + $__agentFee, 2);

    $steps = [];
    if (!$isAgent) {
        $steps['contact'] = ['label' => t('flightbooking.personal_information'), 'icon' => 'fa-user'];
    }
    $steps['travellers'] = ['label' => t('flightbooking.travellers_information'), 'icon' => 'fa-users'];
    $steps['payment'] = ['label' => t('flightbooking.payment_methods'), 'icon' => 'fa-credit-card'];
    $stepNo = array_flip(array_keys($steps));
    $stepNo = array_map(fn($i) => $i + 1, $stepNo);

    $selected = fn($name, $value) => (string) old($name) === (string) $value ? 'selected' : '';
    $travellerNo = 0;
@endphp

<div class="fb-page hb-checkout" id="fbCheckout">
    @if(session('error'))
        <div class="fb-alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <div class="hb-head">
        <h1 class="td-title">{{ t('hotelbooking.complete_booking') }}</h1>
        <p class="hb-subtitle">{{ t('hotelbooking.complete_booking_sub') }}</p>
    </div>

    <form method="post" id="MSF-multiStepForm" action="{{ route('flight_booking') }}" class="hb-layout" novalidate>
        @csrf
        <input type="hidden" name="flight_segment" value="{{ encrypt(json_encode($routes))}}">
        <input type="hidden" name="booking_data" value="{{ encrypt(json_encode($booking_data)) }}">
        <input type="hidden" name="currency" value="{{ encrypt($first->currency)}}">
        <input type="hidden" name="price" value="{{ encrypt($first->price)}}">
        <input type="hidden" name="partner_name" value="{{ encrypt($first->supplier)}}">

        <div class="hb-main">
            <ol class="hb-steps" aria-label="{{ t('hotelbooking.booking_progress') }}">
                @foreach($steps as $key => $step)
                    <li class="hb-step {{ $stepNo[$key] === 1 ? 'is-active' : '' }}" data-step="{{ $stepNo[$key] }}">
                        <button type="button" class="hb-step-btn" data-goto="{{ $stepNo[$key] }}" @if($stepNo[$key] > 1) disabled @else aria-current="step" @endif>
                            <span class="hb-step-num"><span>{{ $stepNo[$key] }}</span><i class="fas fa-check"></i></span>
                            <span class="hb-step-text"><small>{{ str_replace(':n', $stepNo[$key], t('hotelbooking.step_label')) }}</small><b>{{ $step['label'] }}</b></span>
                        </button>
                    </li>
                @endforeach
            </ol>

            @unless($isAgent)
            <!-- Step: booking contact -->
            <section class="td-card hb-panel" data-step="{{ $stepNo['contact'] }}">
                <header class="hb-panel-head">
                    <span class="hb-panel-icon"><i class="fas fa-user"></i></span>
                    <div>
                        <h2 class="hb-panel-title">{{ t('flightbooking.personal_information') }}</h2>
                        <p class="hb-panel-desc">{{ t('flightbooking.contact_desc') }}</p>
                    </div>
                </header>

                @if($loggedInCustomer)
                    <!-- Signed in: the account is the booking contact -->
                    <div class="hb-profile">
                        <span class="hb-profile-avatar">{{ strtoupper(mb_substr($loggedInCustomer->first_name ?? '', 0, 1) . mb_substr($loggedInCustomer->last_name ?? '', 0, 1)) ?: '?' }}</span>
                        <div class="hb-profile-info">
                            <small>{{ t('hotelbooking.logged_in_as') }}</small>
                            <b dir="auto">{{ trim($loggedInCustomer->first_name . ' ' . $loggedInCustomer->last_name) }}</b>
                            <span><i class="far fa-envelope"></i> <span dir="ltr">{{ $loggedInCustomer->email }}</span></span>
                            @if($loggedInCustomer->phone)
                                <span><i class="fas fa-phone"></i> <span dir="ltr">{{ $loggedInCustomer->phone }}</span></span>
                            @endif
                        </div>
                        <a href="{{ route('user.profile.index') }}" class="hb-link-btn"><i class="fas fa-pen"></i> {{ t('flightbooking.update_profile') }}</a>
                    </div>
                    <p class="hb-hint">{{ t('hotelbooking.profile_contact_note') }}</p>
                @endif

                <div class="fb-grid hb-fields" @if($loggedInCustomer) hidden @endif>
                    @foreach([
                        ['first_name', 'text', t('flightbooking.first_name'), t('flightbooking.enter_first_name'), 'far fa-user', 'given-name'],
                        ['last_name', 'text', t('flightbooking.last_name'), t('flightbooking.enter_last_name'), 'far fa-user', 'family-name'],
                        ['email', 'email', t('flightbooking.email'), 'example@email.com', 'far fa-envelope', 'email'],
                        ['phone', 'tel', t('flightbooking.phone_number'), '+92 300 1234567', 'fas fa-phone', 'tel'],
                        ['address', 'text', t('flightbooking.address'), t('flightbooking.enter_your_address'), 'fas fa-location-dot', 'street-address'],
                        ['city', 'text', t('flightbooking.city'), t('flightbooking.enter_city'), 'fas fa-city', 'address-level2'],
                    ] as [$f, $type, $label, $placeholder, $icon, $auto])
                        <label class="hb-field">
                            <span>{{ $label }} <em>*</em></span>
                            <span class="hb-input-wrap"><i class="{{ $icon }}"></i>
                                <input type="{{ $type }}" class="fb-input" name="user[{{ $f }}]" value="{{ old('user.' . $f, $loggedInCustomer->{$f} ?? '') }}" placeholder="{{ $placeholder }}" autocomplete="{{ $auto }}" @if($loggedInCustomer) readonly @else required @endif>
                            </span>
                            <small class="hb-error"></small>
                        </label>
                    @endforeach
                    <label class="hb-field is-wide">
                        <span>{{ t('flightbooking.country') }} <em>*</em></span>
                        @if($loggedInCustomer)
                            <input type="text" class="fb-input" value="{{ $matchedCountry->country ?? $loggedInCustomer->country ?? '—' }}" readonly>
                            <input type="hidden" name="user[country]" value="{{ old('user.country', $matchedCountry ? strtolower($matchedCountry->country_code) : '') }}">
                        @else
                            <select class="fb-input" name="user[country]" autocomplete="country" required>
                                <option value="">{{ t('flightbooking.select_country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ strtolower($country->country_code) }}" {{ $selected('user.country', strtolower($country->country_code)) }}>{{ $country->country }}</option>
                                @endforeach
                            </select>
                        @endif
                        <small class="hb-error"></small>
                    </label>
                </div>

                <div class="hb-actions">
                    <button type="button" class="fb-back" onclick="history.back()"><i class="fas fa-arrow-left"></i> {{ t('flightbooking.back') }}</button>
                    <button type="button" class="td-book-btn hb-next" data-continue="{{ $stepNo['contact'] }}">
                        {{ t('flightbooking.continue_travellers') }} <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>
            @endunless

            <!-- Step: one block per traveller (as on the passport) -->
            <section class="td-card hb-panel" data-step="{{ $stepNo['travellers'] }}" @if($stepNo['travellers'] !== 1) hidden @endif>
                <header class="hb-panel-head">
                    <span class="hb-panel-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <h2 class="hb-panel-title">{{ t('flightbooking.travellers_information') }}</h2>
                        <p class="hb-panel-desc">{{ t('flightbooking.travellers_desc') }}</p>
                    </div>
                    <span class="hb-count"><b><i class="fas fa-user-group"></i> {{ str_replace(':count', $adults + $childrenCount + $infants, t('hotelbooking.traveller_count')) }}</b>
                        <small>{{ $adults }} {{ t('flightbooking.adult') }}@if($childrenCount) · {{ $childrenCount }} {{ t('flightbooking.children') }}@endif @if($infants) · {{ $infants }} {{ t('flightbooking.infant') }}@endif</small>
                    </span>
                </header>

                @foreach($travellerGroups as $g)
                    @for($i = 1; $i <= $g['count']; $i++)
                        @php $travellerNo++; $p = $g['p']; @endphp
                        <div class="hb-traveller">
                            <div class="hb-traveller-head">
                                <span class="hb-traveller-num">{{ $travellerNo }}</span>
                                <b>{{ t('flightbooking.traveller') }} {{ $travellerNo }}</b>
                                <span class="fb-tag">{{ $g['label'] }}</span>
                            </div>
                            <input type="hidden" name="{{ $g['type_field'] }}{{ $i }}" value="{{ $g['type_value'] }}"/>

                            <div class="fb-grid">
                                <label class="hb-field">
                                    <span>{{ t('flightbooking.first_name') }} <em>*</em></span>
                                    <span class="hb-input-wrap"><i class="far fa-user"></i>
                                        <input type="text" class="fb-input" name="{{ $p }}first_name_{{ $i }}" value="{{ old($p . 'first_name_' . $i) }}" placeholder="{{ t('flightbooking.as_per_passport') }}" maxlength="50" required>
                                    </span>
                                    <small class="hb-error"></small>
                                </label>
                                <label class="hb-field">
                                    <span>{{ t('flightbooking.last_name') }} <em>*</em></span>
                                    <span class="hb-input-wrap"><i class="far fa-user"></i>
                                        <input type="text" class="fb-input" name="{{ $p }}last_name_{{ $i }}" value="{{ old($p . 'last_name_' . $i) }}" placeholder="{{ t('flightbooking.as_per_passport') }}" maxlength="50" required>
                                    </span>
                                    <small class="hb-error"></small>
                                </label>
                                <label class="hb-field">
                                    <span>{{ t('flightbooking.gender') }} <em>*</em></span>
                                    <span class="hb-input-wrap"><i class="fas fa-venus-mars"></i>
                                        <select class="fb-input hb-select" name="{{ $p }}gender_{{ $i }}" required>
                                            <option value="">{{ t('flightbooking.select_gender') }}</option>
                                            @foreach(['m' => 'male', 'f' => 'female', 'o' => 'other'] as $gv => $gk)
                                                <option value="{{ $gv }}" {{ $selected($p . 'gender_' . $i, $gv) }}>{{ t('flightbooking.' . $gk) }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                    <small class="hb-error"></small>
                                </label>
                                <div class="hb-field">
                                    <span>{{ t('flightbooking.date_of_birth') }} <em>*</em></span>
                                    <div class="fb-date">
                                        <select class="fb-input" name="{{ $p }}dob_day_{{ $i }}" required aria-label="{{ t('flightbooking.day') }}">
                                            <option value="">{{ t('flightbooking.day') }}</option>
                                            @for($d = 1; $d <= 31; $d++)@php $dv = str_pad($d, 2, '0', STR_PAD_LEFT); @endphp<option value="{{ $dv }}" {{ $selected($p . 'dob_day_' . $i, $dv) }}>{{ $dv }}</option>@endfor
                                        </select>
                                        <select class="fb-input" name="{{ $p }}dob_month_{{ $i }}" required aria-label="{{ t('flightbooking.month') }}">
                                            <option value="">{{ t('flightbooking.month') }}</option>
                                            @foreach($months as $m => $key)<option value="{{ $m + 1 }}" {{ $selected($p . 'dob_month_' . $i, $m + 1) }}>{{ t('flightbooking.' . $key) }}</option>@endforeach
                                        </select>
                                        <select class="fb-input" name="{{ $p }}dob_year_{{ $i }}" required aria-label="{{ t('flightbooking.year') }}">
                                            <option value="">{{ t('flightbooking.year') }}</option>
                                            @foreach($g['dob_years'] as $y)<option value="{{ $y }}" {{ $selected($p . 'dob_year_' . $i, $y) }}>{{ $y }}</option>@endforeach
                                        </select>
                                    </div>
                                    <small class="hb-error"></small>
                                </div>
                            </div>

                            @if($g['passport'])
                                <div class="fb-passport">
                                    <div class="fb-passport-title"><i class="fas fa-passport"></i> {{ t('flightbooking.passport_details') }}</div>
                                    <div class="fb-grid">
                                        <label class="hb-field">
                                            <span>{{ t('flightbooking.passport_number') }} <em>*</em></span>
                                            <span class="hb-input-wrap"><i class="fas fa-passport"></i>
                                                <input type="text" class="fb-input" name="{{ $p }}passport_{{ $i }}" value="{{ old($p . 'passport_' . $i) }}" placeholder="AB123456" maxlength="20" required>
                                            </span>
                                            <small class="hb-error"></small>
                                        </label>
                                        <label class="hb-field">
                                            <span>{{ t('flightbooking.nationality') }} <em>*</em></span>
                                            <select class="fb-input" name="{{ $g['nat'] }}{{ $i }}" required>
                                                <option value="">{{ t('flightbooking.select_country') }}</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ strtolower($country->country_code) }}" {{ $selected($g['nat'] . $i, strtolower($country->country_code)) }}>{{ $country->country }}</option>
                                                @endforeach
                                            </select>
                                            <small class="hb-error"></small>
                                        </label>
                                        @foreach([
                                            [t('flightbooking.passport_issuance_date'), 'passport_issuance_day_', 'passport_issuance_month_', 'passport_issuance_year_', range((int) date('Y'), 1920)],
                                            [t('flightbooking.passport_expiry_date'), 'passport_day_expiry_', 'passport_month_expiry_', 'passport_year_expiry_', range((int) date('Y') + 20, (int) date('Y'))],
                                        ] as [$dateLabel, $dayF, $monthF, $yearF, $years])
                                            <div class="hb-field">
                                                <span>{{ $dateLabel }} <em>*</em></span>
                                                <div class="fb-date">
                                                    <select class="fb-input" name="{{ $p }}{{ $dayF }}{{ $i }}" required aria-label="{{ t('flightbooking.day') }}">
                                                        <option value="">{{ t('flightbooking.day') }}</option>
                                                        @for($d = 1; $d <= 31; $d++)@php $dv = str_pad($d, 2, '0', STR_PAD_LEFT); @endphp<option value="{{ $dv }}" {{ $selected($p . $dayF . $i, $dv) }}>{{ $dv }}</option>@endfor
                                                    </select>
                                                    <select class="fb-input" name="{{ $p }}{{ $monthF }}{{ $i }}" required aria-label="{{ t('flightbooking.month') }}">
                                                        <option value="">{{ t('flightbooking.month') }}</option>
                                                        @foreach($months as $m => $key)<option value="{{ $m + 1 }}" {{ $selected($p . $monthF . $i, $m + 1) }}>{{ t('flightbooking.' . $key) }}</option>@endforeach
                                                    </select>
                                                    <select class="fb-input" name="{{ $p }}{{ $yearF }}{{ $i }}" required aria-label="{{ t('flightbooking.year') }}">
                                                        <option value="">{{ t('flightbooking.year') }}</option>
                                                        @foreach($years as $y)<option value="{{ $y }}" {{ $selected($p . $yearF . $i, $y) }}>{{ $y }}</option>@endforeach
                                                    </select>
                                                </div>
                                                <small class="hb-error"></small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endfor
                @endforeach

                <div class="hb-actions">
                    @if($isAgent)
                        <button type="button" class="fb-back" onclick="history.back()"><i class="fas fa-arrow-left"></i> {{ t('flightbooking.back') }}</button>
                    @else
                        <button type="button" class="fb-back" data-back="{{ $stepNo['contact'] }}"><i class="fas fa-arrow-left"></i> {{ t('flightbooking.back_contact') }}</button>
                    @endif
                    <button type="button" class="td-book-btn hb-next" data-continue="{{ $stepNo['travellers'] }}">
                        {{ t('hotelbooking.continue_payment') }} <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- Step: payment -->
            <section class="td-card hb-panel" data-step="{{ $stepNo['payment'] }}" hidden>
                <header class="hb-panel-head">
                    <span class="hb-panel-icon"><i class="far fa-credit-card"></i></span>
                    <div>
                        <h2 class="hb-panel-title">{{ t('flightbooking.payment_methods') }}</h2>
                        <p class="hb-panel-desc">{{ t('hotelbooking.payment_desc') }}</p>
                    </div>
                </header>

                <div class="hb-pays" role="radiogroup">
                    @if($isAgent)
                        {{-- Agents always pay from their wallet — no other gateway is offered. --}}
                        @if($agentWallet)
                            <label class="hb-pay">
                                <input type="radio" name="accept_payment" value="agent_wallet" data-label="Agent Wallet" checked>
                                <span class="hb-pay-radio"></span>
                                <span class="hb-pay-logo is-icon"><i class="fas fa-wallet"></i></span>
                                <span class="hb-pay-text"><b>Agent Wallet</b><small>Available Balance: {{ $walletDisplayCurrency }} {{ number_format($walletDisplayBalance, 2) }}</small></span>
                            </label>
                        @else
                            <div class="fb-alert">No wallet found for your account. Please contact admin.</div>
                        @endif
                    @else
                        @foreach($payment as $value)
                            <label class="hb-pay">
                                <input type="radio" name="accept_payment" value="{{ $value->name }}" data-label="{{ ucfirst(str_replace('_', ' ', $value->name)) }}" @checked($loop->first)>
                                <span class="hb-pay-radio"></span>
                                <img src="{{ url('public/assets/images/settings/payment/' . $value->name . '.png') }}" alt="" class="hb-pay-logo">
                                <span class="hb-pay-text"><b>{{ ucfirst(str_replace('_', ' ', $value->name)) }}</b></span>
                            </label>
                        @endforeach
                    @endif
                </div>
                <small class="hb-error" data-error-for="accept_payment"></small>

                <div class="hb-pay-summary">
                    <div><small>{{ t('hotelbooking.selected_method') }}</small><b id="fbSelectedMethod">{{ $isAgent ? 'Agent Wallet' : ucfirst(str_replace('_', ' ', optional($payment->first())->name ?? '—')) }}</b></div>
                    <div class="is-amount"><small>{{ t('hotelbooking.amount_to_pay') }}</small><b>{{ $fareText }}</b></div>
                </div>
                <p class="hb-secure-note"><i class="fas fa-shield-halved"></i> {{ t('hotelbooking.pay_secure_note') }}</p>

                <div class="hb-actions">
                    <button type="button" class="fb-back" data-back="{{ $stepNo['travellers'] }}"><i class="fas fa-arrow-left"></i> {{ t('hotelbooking.back_travellers') }}</button>
                    <button type="submit" class="td-book-btn hb-next" id="fbConfirm">
                        <i class="fas fa-lock"></i> <span>{{ t('flightbooking.pay_now') }}</span>
                    </button>
                </div>
            </section>
        </div>

        <!-- Booking summary: the selected flight (collapsible on phones) -->
        <aside class="hb-summary" id="fbSummary">
            <div class="fb-timer">
                <i class="fas fa-hourglass-half"></i>
                <span>{{ t('flightbooking.complete_booking_in') }}</span>
                <b id="timer">30:00</b>
            </div>
            <button type="button" class="hb-sum-toggle" aria-expanded="false" aria-controls="fbSummaryBody">
                <span><i class="fas fa-receipt"></i> {{ t('hotelbooking.booking_summary') }}</span>
                <b>{{ $totalText }}</b>
                <i class="fas fa-chevron-down hb-sum-caret"></i>
            </button>
            <div class="td-card hb-sum-card" id="fbSummaryBody">
                <h2 class="hb-sum-title">{{ t('hotelbooking.booking_summary') }}</h2>

                <div class="hb-hotel">
                    <div class="hb-hotel-img is-logo">
                        <i class="fas fa-plane"></i>
                        <img src="{{ $logoUrl($first->carrier->operating ?? $first->carrier->marketing) }}" alt="" onerror="this.onerror=null;this.remove()">
                    </div>
                    <div class="hb-hotel-info">
                        <span class="hb-badge">{{ $isRound ? t('flightbooking.round_trip') : t('flightbooking.one_way') }}</span>
                        <h3 dir="auto">{{ $first->departure->city_name ?? $first->departure->airport }} <i class="fas {{ $isRound ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right' }} fb-sum-arrow"></i> {{ $outLast['arrival']['city_name'] ?? $outLast['arrival']['airport'] }}</h3>
                        <p class="hb-hotel-address">{{ $first->airline_name }}</p>
                    </div>
                </div>

                <div class="hb-sum-rows">
                    <div class="hb-row"><span><i class="fas fa-plane-departure"></i> {{ t('flightbooking.departure_date') }}</span><b>{{ $first->departure->date_convert }}</b></div>
                    @if($isRound && isset($legs[1][0]))
                        <div class="hb-row"><span><i class="fas fa-plane-arrival"></i> {{ t('flightbooking.return_date') }}</span><b>{{ $legs[1][0]['departure']['date_convert'] ?? '' }}</b></div>
                    @endif
                    <div class="hb-row"><span><i class="fas fa-user-group"></i> {{ t('flightbooking.traveller') }}</span><b>{{ $adults + $childrenCount + $infants }}</b></div>
                    @if(!empty($first->class))
                        <div class="hb-row"><span><i class="fas fa-couch"></i> {{ t('flightbooking.cabin_class') }}</span><b>{{ ucwords(str_replace('_', ' ', $first->class)) }}</b></div>
                    @endif
                    <div class="hb-row"><span><i class="fas fa-suitcase-rolling"></i> {{ t('flightbooking.baggage') }}</span><b>{{ $bagText($first->baggage ?? null) }} + {{ $bagText($first->cabin_baggage ?? null) }}</b></div>
                </div>

                <h3 class="hb-sum-sub">{{ t('flightbooking.itinerary') }}</h3>
                @include('flight.itinerary', ['legs' => $legs])

                <h3 class="hb-sum-sub">{{ t('flightbooking.price_summary') }}</h3>
                <div class="hb-row"><span>{{ t('flightbooking.base_fare') }}</span><b>{{ $fareText }}</b></div>
                <div class="hb-row"><span>{{ t('flightbooking.taxes_fees') }}</span><b>{{ $__activeCurrencyCode }} 0.00</b></div>
                @if($__agentFee > 0)
                    <div class="hb-row td-agent-fee"><span><i class="fas fa-hand-holding-dollar"></i> {{ t('flightlist.your_fee') }}</span><b>+ {{ $__activeCurrencyCode }} {{ number_format($__agentFee, 2) }}</b></div>
                @endif
                <div class="hb-total">
                    <span>{{ t('flightbooking.total') }}</span>
                    <b>{{ $totalText }}</b>
                </div>
                @if($__agentFee > 0)
                    <p class="td-agent-fee-note">{{ t('flightbooking.agent_fee_note') }}</p>
                @endif
                <p class="td-secure hb-secure"><i class="fas fa-lock"></i> {{ t('hotelbooking.secure_payment') }}</p>
            </div>
        </aside>
    </form>
</div>

<script>
(function () {
    const root = document.getElementById('fbCheckout');
    if (!root) return;
    const form = document.getElementById('MSF-multiStepForm');
    const panels = [...root.querySelectorAll('.hb-panel')];
    const last = panels.length;
    const text = {
        required: @json(t('hotelbooking.field_required')),
        email: @json(t('hotelbooking.invalid_email')),
        payment: @json(t('hotelbooking.select_payment')),
        processing: @json(t('hotelbooking.processing')),
        expired: @json(t('flightbooking.time_expired')),
    };
    const state = { current: 1, max: 1 };
    const panel = (step) => panels.find(p => +p.dataset.step === step);

    // ---- inline errors (slot = the field's own .hb-error) ----
    function clearErrors(scope) {
        scope.querySelectorAll('.hb-error').forEach(e => e.textContent = '');
        scope.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
    }
    function check(step) {
        const scope = panel(step);
        clearErrors(scope);
        let first = null;
        scope.querySelectorAll('[required]').forEach(el => {
            const value = el.value.trim();
            let message = '';
            if (!value) message = text.required;
            else if (el.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) message = text.email;
            if (!message) return;
            el.classList.add('is-invalid');
            const slot = el.closest('.hb-field')?.querySelector('.hb-error');
            if (slot && !slot.textContent) slot.textContent = message;
            first = first || el;
        });
        if (step === last && !form.querySelector('[name="accept_payment"]:checked')) {
            scope.querySelector('[data-error-for="accept_payment"]').textContent = text.payment;
            first = first || scope.querySelector('.hb-pays');
        }
        if (first) {
            const fields = first.closest('.hb-fields');
            if (fields && fields.hidden) fields.hidden = false;
            first.focus?.({ preventScroll: false });
        }
        return !first;
    }

    // ---- stepper ----
    function show(step) {
        state.current = step;
        state.max = Math.max(state.max, step);
        panels.forEach(p => p.hidden = +p.dataset.step !== step);
        root.querySelectorAll('.hb-step').forEach(li => {
            const n = +li.dataset.step;
            const btn = li.querySelector('.hb-step-btn');
            li.classList.toggle('is-active', n === step);
            li.classList.toggle('is-done', n < state.max && n !== step);
            btn.disabled = n > state.max;
            n === step ? btn.setAttribute('aria-current', 'step') : btn.removeAttribute('aria-current');
        });
        root.querySelector('.hb-steps').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    root.querySelectorAll('[data-continue]').forEach(btn => btn.addEventListener('click', () => {
        const step = +btn.dataset.continue;
        if (check(step)) show(step + 1);
    }));
    root.querySelectorAll('[data-back]').forEach(btn => btn.addEventListener('click', () => show(+btn.dataset.back)));
    root.querySelectorAll('[data-goto]').forEach(btn => btn.addEventListener('click', () => {
        const target = +btn.dataset.goto;
        // forward only through checked steps
        for (let s = state.current; s < target; s++) if (!check(s)) { show(s); return; }
        show(target);
    }));

    // a fixed field loses its error as soon as it has a value
    form.addEventListener('input', clearOne);
    form.addEventListener('change', clearOne);
    function clearOne(e) {
        const el = e.target;
        if (el.classList.contains('is-invalid') && el.value.trim()) {
            el.classList.remove('is-invalid');
            const field = el.closest('.hb-field');
            if (field && !field.querySelector('.is-invalid')) field.querySelector('.hb-error').textContent = '';
        }
        if (el.name === 'accept_payment') {
            document.getElementById('fbSelectedMethod').textContent = el.dataset.label;
            panel(last).querySelector('[data-error-for="accept_payment"]').textContent = '';
        }
    }

    // ---- mobile summary ----
    const sumToggle = root.querySelector('.hb-sum-toggle');
    sumToggle.addEventListener('click', () => {
        const open = document.getElementById('fbSummary').classList.toggle('is-open');
        sumToggle.setAttribute('aria-expanded', String(open));
    });

    // ---- submit: only from the payment step, once ----
    const confirmBtn = document.getElementById('fbConfirm');
    form.addEventListener('submit', (e) => {
        if (state.current !== last) {
            e.preventDefault();
            panel(state.current).querySelector('[data-continue]')?.click();
            return;
        }
        // every step again (a field may have been emptied after moving on)
        for (let s = 1; s <= last; s++) {
            if (!check(s)) { e.preventDefault(); show(s); check(s); return; }
        }
        if (confirmBtn.disabled) { e.preventDefault(); return; }
        confirmBtn.disabled = true;
        confirmBtn.classList.add('is-loading');
        confirmBtn.querySelector('span').textContent = text.processing;
    });
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('is-loading');
    });

    // a failed booking comes back with the values: open the payment step
    @if(session('error') && old('accept_payment'))
        show(last);
    @endif

    // ---- 30 minute booking timer ----
    const timer = document.getElementById('timer');
    let timeLeft = 30 * 60;
    const tick = setInterval(() => {
        timeLeft--;
        timer.textContent = String(Math.floor(timeLeft / 60)).padStart(2, '0') + ':' + String(timeLeft % 60).padStart(2, '0');
        if (timeLeft < 300) timer.closest('.fb-timer').classList.add('is-ending');
        if (timeLeft <= 0) {
            clearInterval(tick);
            timer.textContent = '00:00';
            alert(text.expired);
        }
    }, 1000);
})();
</script>

@endsection
