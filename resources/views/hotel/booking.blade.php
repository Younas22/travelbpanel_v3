@extends('common.layout')
@section('content')
@php
    // Same layout and styles as the flight booking page (.fb-* / .td-* in style.css)
    $days = (new DateTime($booking_data['checkin']))->diff(new DateTime($booking_data['checkout']))->days;
    $hbCurrency = $room->currency ?? '';
    $hbStars = max(0, min(5, (int) $booking_data['stars']));

    // Prefer the fresh, GetRooms-With-Blocking-derived taxes/fees
    // (re-validated for THIS booking attempt); fall back to the
    // figures captured at search time if that re-check didn't run.
    $taxesIncluded   = $fresh_taxes_and_fees['included_in_price']   ?? ($booking_option['taxes_included']    ?? []);
    $taxesAtProperty = $fresh_taxes_and_fees['payable_at_property'] ?? ($booking_option['taxes_at_property'] ?? []);
    // <totalTaxes> is mandatory to disclose per WebBeds — it's already
    // included within the room price, so this is a disclosure line only.
    $totalTaxes      = $fresh_taxes_and_fees['total_taxes'] ?? null;
    $totalTaxesCurrency = $fresh_taxes_and_fees['currency']
        ?? ($taxesIncluded[0]['currency'] ?? null)
        ?? ($taxesAtProperty[0]['currency'] ?? '');
@endphp

<div class="fb-page">
    @if(session('error'))
        <div class="fb-alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <!-- Header -->
    <div class="td-header">
        @if(trim((string) $booking_data['hotel_name']) !== '')
            <h1 class="td-title" dir="auto">{{ $booking_data['hotel_name'] }}</h1>
        @endif
        <div class="td-meta">
            @if(!empty($booking_data['address']))
                <span class="td-meta-item"><i class="fas fa-location-dot"></i> {{ $booking_data['address'] }}</span>
            @endif
            @if($hbStars > 0)
                <span class="td-stars">@for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= $hbStars ? 'is-on' : '' }}"></i>@endfor</span>
            @endif
            <span class="td-meta-item"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($booking_data['checkin'])->format('M d, Y') }} – {{ \Carbon\Carbon::parse($booking_data['checkout'])->format('M d, Y') }}</span>
        </div>
    </div>

    <form action="{{ route('booking') }}" method="POST" class="td-main">
        @csrf
        <input type="hidden" name="room_data" value="{{ encrypt(json_encode($room_data)) }}">
        <input type="hidden" name="room" value="{{ encrypt(json_encode($room)) }}">
        <input type="hidden" name="option" value="{{ encrypt(json_encode($booking_option)) }}">
        <input type="hidden" name="booking_data" value="{{ encrypt(json_encode($booking_data)) }}">

        <div class="td-main-col">
            <!-- Guest information -->
            <div class="td-card">
                <h2 class="td-section-title">{{ t('hotelbooking.guest_information') }}</h2>
                <div class="fb-grid">
                    <label class="fb-field">
                        <span>{{ t('hotelbooking.first_name') }} *</span>
                        <input type="text" class="fb-input" name="user[first_name]" placeholder="{{ t('hotelbooking.enter_first_name') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('hotelbooking.last_name') }} *</span>
                        <input type="text" class="fb-input" name="user[last_name]" placeholder="{{ t('hotelbooking.enter_last_name') }}" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('hotelbooking.email_address') }} *</span>
                        <input type="email" class="fb-input" name="user[email]" placeholder="example@email.com" required>
                    </label>
                    <label class="fb-field">
                        <span>{{ t('hotelbooking.phone_number') }} *</span>
                        <input type="tel" class="fb-input" name="user[phone]" placeholder="+92 300 1234567" required>
                    </label>
                    <label class="fb-field is-wide">
                        <span>{{ t('hotelbooking.country') }} *</span>
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
                <h2 class="td-section-title">{{ t('hotelbooking.travellers_information') }}</h2>

                @foreach([
                    ['count' => (int) $hotel_search['adults'], 'p' => 'adult_', 'hidden' => 'traveller_type_', 'hiddenValue' => 'adults', 'label' => t('hotelbooking.adult'), 'title' => t('hotelbooking.traveller'), 'icon' => 'fa-user'],
                    ['count' => (int) $hotel_search['childs'], 'p' => 'child_', 'hidden' => 'traveller_child_', 'hiddenValue' => 'child', 'label' => t('hotelbooking.children'), 'title' => t('hotelbooking.child'), 'icon' => 'fa-child'],
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
                                    <span>{{ t('hotelbooking.gender') }} *</span>
                                    <select class="fb-input" name="{{ $g['p'] }}gender_{{ $i }}" required>
                                        <option value="">{{ t('hotelbooking.select_gender') }}</option>
                                        <option value="male">{{ t('hotelbooking.male') }}</option>
                                        <option value="female">{{ t('hotelbooking.female') }}</option>
                                        <option value="other">{{ t('hotelbooking.other') }}</option>
                                    </select>
                                </label>
                                <label class="fb-field">
                                    <span>{{ t('hotelbooking.first_name') }} *</span>
                                    <input type="text" class="fb-input" name="{{ $g['p'] }}first_name_{{ $i }}" placeholder="{{ t('hotelbooking.enter_first_name') }}" required>
                                </label>
                                <label class="fb-field">
                                    <span>{{ t('hotelbooking.last_name') }} *</span>
                                    <input type="text" class="fb-input" name="{{ $g['p'] }}last_name_{{ $i }}" placeholder="{{ t('hotelbooking.enter_last_name') }}" required>
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
                            <input type="radio" name="accept_payment" value="{{ $value->name }}" checked>
                            <img src="{{ url('public/assets/images/settings/payment/' . $value->name . '.png') }}" alt="{{ $value->name }}" class="fb-pay-logo">
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
                <button type="button" class="fb-back" onclick="history.back()"><i class="fas fa-arrow-left"></i> {{ t('hotelbooking.back') }}</button>
                <button type="submit" class="td-book-btn fb-pay-btn"><i class="fas fa-check"></i> {{ t('hotelbooking.confirm_booking') }}</button>
            </div>
        </div>

        <!-- Summary -->
        <aside class="td-sidebar">
            <div class="td-card">
                <h2 class="td-side-title">{{ t('hotelbooking.booking_summary') }}</h2>
                <div class="td-row"><span><i class="fas fa-bed"></i> {{ t('hotelbooking.room_type') }}</span><b>{{ $room->name }}</b></div>
                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('hotelbooking.check_in') }}</span><b>{{ \Carbon\Carbon::parse($booking_data['checkin'])->format('M d, Y') }}</b></div>
                <div class="td-row"><span><i class="fas fa-calendar"></i> {{ t('hotelbooking.check_out') }}</span><b>{{ \Carbon\Carbon::parse($booking_data['checkout'])->format('M d, Y') }}</b></div>
                <div class="td-row"><span><i class="fas fa-moon"></i> {{ t('hotelbooking.nights') }}</span><b>{{ $days }}</b></div>
                <div class="td-row"><span><i class="fas fa-users"></i> {{ t('hotelbooking.guests') }}</span><b>{{ $booking_data['adults'] }} {{ t('hotelbooking.adults') }}</b></div>
                <div class="td-row"><span><i class="fas fa-receipt"></i> {{ str_replace(':nights', $days, t('hotelbooking.room_charges')) }}</span><b>{{ $booking_option['price'] }} {{ $hbCurrency }}</b></div>

                @if(!empty($booking_option['cancel_restricted']))
                    <div class="option-notice restricted fb-notice"><i class="fas fa-ban"></i><span>{{ $booking_option['cancel_restricted_note'] ?? 'Cancellation not allowed' }}</span></div>
                @elseif(!empty($booking_option['non_refundable']))
                    <div class="option-notice restricted fb-notice"><i class="fas fa-times-circle"></i><span>{{ t('hoteldetails.non_refundable') }}</span></div>
                @endif
                @if(!empty($booking_option['min_stay']) && (int) $booking_option['min_stay'] > 0)
                    <div class="option-notice min-stay fb-notice"><i class="fas fa-calendar-day"></i><span>{{ str_replace(':nights', $booking_option['min_stay'], t('hoteldetails.min_stay_note')) }}</span></div>
                @endif

                @if(!empty($taxesIncluded) || !empty($taxesAtProperty) || $totalTaxes !== null)
                    <div class="fb-taxes">
                        <div class="fb-taxes-title">{{ t('hoteldetails.taxes_and_fees') }}</div>
                        @if($totalTaxes !== null)
                            <div class="fb-tax-row"><span>{{ t('hoteldetails.total_taxes') }}</span><b>{{ number_format($totalTaxes, 2) }} {{ $totalTaxesCurrency }}</b></div>
                        @endif
                        @foreach($taxesIncluded as $tax)
                            <div class="fb-tax-row"><span>{{ $tax['description'] ?: ($tax['name'] ?? $tax['type'] ?? '') }} <em class="is-included">({{ t('hoteldetails.included_in_price') }})</em></span><b>{{ number_format($tax['amount'], 2) }} {{ $tax['currency'] ?? '' }}</b></div>
                        @endforeach
                        @foreach($taxesAtProperty as $tax)
                            <div class="fb-tax-row"><span>{{ $tax['description'] ?: ($tax['name'] ?? $tax['type'] ?? '') }} <em class="is-property">({{ t('hoteldetails.payable_at_property') }})</em></span><b>{{ number_format($tax['amount'], 2) }} {{ $tax['currency'] ?? '' }}</b></div>
                        @endforeach
                    </div>
                @endif

                <div class="td-total">
                    <span>{{ t('hotelbooking.total_amount') }}</span>
                    <b>{{ $booking_option['price'] }} {{ $hbCurrency }}</b>
                </div>
                <p class="td-secure"><i class="fas fa-lock"></i> {{ t('hotelbooking.secure_payment') }}</p>
            </div>
        </aside>
    </form>
</div>
@endsection
