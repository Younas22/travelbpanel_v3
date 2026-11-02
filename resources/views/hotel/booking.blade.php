@extends('common.layout')
@section('page_class', 'view-hotel-checkout')
@section('content')
@php
    // Three-step checkout over a saved draft (hotel_booking_drafts): every step
    // is rendered here, the script shows one at a time and saves each step via
    // AJAX, so a refresh or a later visit reopens the same step with its values.
    $days = (new DateTime($booking_data['checkin']))->diff(new DateTime($booking_data['checkout']))->days;
    $hbCurrency = $room->currency ?? '';
    $hbStars = max(0, min(5, (int) filter_var($booking_data['stars'] ?? 0, FILTER_SANITIZE_NUMBER_INT)));
    // review score only when the supplier sends one on a 0–100 scale (as on the details page)
    $hbRating = (float) ($booking_data['rating'] ?? 0);
    $hbScore = ($hbRating >= 10 && $hbRating <= 100) ? round($hbRating / 10, 1) : null;
    $hbImage = $booking_data['image'] ?? null ?: ($room->images[0] ?? null);
    $hbPrice = number_format((float) $booking_option['price'], 2) . ' ' . $hbCurrency;

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

    $guest = $draft->guest_data ?? [];
    $travellers = $draft->travellers ?? [];
    $adults = (int) $draft->adults;
    $children = (int) $draft->children;
    $completed = $draft->completed_steps ?? [];
    $current = min((int) $draft->current_step, $draft->maxAllowedStep());
    $profileComplete = $profile && collect(['first_name', 'last_name', 'email', 'phone', 'country'])->every(fn($f) => trim((string) ($guest[$f] ?? '')) !== '');
    $selectedPayment = $draft->payment_method ?: optional($payment->first())->name;
    $steps = [
        1 => t('hotelbooking.guest_information'),
        2 => t('hotelbooking.travellers_information'),
        3 => t('hotelbooking.payment_methods'),
    ];
@endphp

<div class="fb-page hb-checkout" id="hbCheckout"
     data-save-url="{{ route('hotel.checkout.save', $draft->token) }}"
     data-token="{{ $draft->token }}"
     data-current="{{ $current }}"
     data-completed="{{ json_encode($completed) }}"
     data-max="{{ $draft->maxAllowedStep() }}">
    @if(session('error'))
        <div class="fb-alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <div class="hb-head">
        <h1 class="td-title">{{ t('hotelbooking.complete_booking') }}</h1>
        <p class="hb-subtitle">{{ t('hotelbooking.complete_booking_sub') }}</p>
    </div>

    <form action="{{ route('booking') }}" method="POST" class="hb-layout" id="hbForm" novalidate>
        @csrf
        <input type="hidden" name="draft" value="{{ $draft->token }}">

        <div class="hb-main">
            <!-- Progress: reflects the saved draft (completed / current / locked) -->
            <ol class="hb-steps" aria-label="{{ t('hotelbooking.booking_progress') }}">
                @foreach($steps as $n => $label)
                    @php $state = $n === $current ? 'is-active' : (in_array($n, $completed) ? 'is-done' : ''); @endphp
                    <li class="hb-step {{ $state }}" data-step="{{ $n }}">
                        <button type="button" class="hb-step-btn" data-goto="{{ $n }}" @if($n > $draft->maxAllowedStep()) disabled @endif @if($n === $current) aria-current="step" @endif>
                            <span class="hb-step-num"><span>{{ $n }}</span><i class="fas fa-check"></i></span>
                            <span class="hb-step-text"><small>{{ str_replace(':n', $n, t('hotelbooking.step_label')) }}</small><b>{{ $label }}</b></span>
                        </button>
                    </li>
                @endforeach
            </ol>

            <!-- Step 1: guest / booking contact -->
            <section class="td-card hb-panel" data-step="1" @if($current !== 1) hidden @endif>
                <header class="hb-panel-head">
                    <span class="hb-panel-icon"><i class="fas fa-user"></i></span>
                    <div>
                        <h2 class="hb-panel-title">{{ t('hotelbooking.guest_information') }}</h2>
                        <p class="hb-panel-desc">{{ t('hotelbooking.guest_info_desc') }}</p>
                    </div>
                </header>

                @if($profile)
                    <!-- Signed in: the account is the booking contact -->
                    <div class="hb-profile">
                        @if($profileAvatar)
                            <img src="{{ $profileAvatar }}" alt="" class="hb-profile-avatar">
                        @else
                            <span class="hb-profile-avatar">{{ strtoupper(mb_substr($guest['first_name'] ?? '', 0, 1) . mb_substr($guest['last_name'] ?? '', 0, 1)) ?: '?' }}</span>
                        @endif
                        <div class="hb-profile-info">
                            <small>{{ t('hotelbooking.logged_in_as') }}</small>
                            <b dir="auto" data-profile="name">{{ trim(($guest['first_name'] ?? '') . ' ' . ($guest['last_name'] ?? '')) }}</b>
                            <span><i class="far fa-envelope"></i> <span data-profile="email" dir="ltr">{{ $guest['email'] ?? '' }}</span></span>
                            @if(!empty($guest['phone']))
                                <span><i class="fas fa-phone"></i> <span data-profile="phone" dir="ltr">{{ $guest['phone'] }}</span></span>
                            @endif
                        </div>
                        <button type="button" class="hb-link-btn" id="hbEditContact" aria-controls="hbGuestFields" aria-expanded="{{ $profileComplete ? 'false' : 'true' }}">
                            <i class="fas fa-pen"></i> {{ t('hotelbooking.edit_details') }}
                        </button>
                    </div>
                    <p class="hb-hint">{{ $profileComplete ? t('hotelbooking.profile_contact_note') : t('hotelbooking.complete_profile_note') }}</p>
                @endif

                <div class="fb-grid hb-fields" id="hbGuestFields" @if($profileComplete) hidden @endif>
                    <label class="hb-field">
                        <span>{{ t('hotelbooking.first_name') }} <em>*</em></span>
                        <span class="hb-input-wrap"><i class="far fa-user"></i>
                            <input type="text" class="fb-input" name="user[first_name]" value="{{ $guest['first_name'] ?? '' }}" placeholder="{{ t('hotelbooking.enter_first_name') }}" autocomplete="given-name" maxlength="50" required>
                        </span>
                        <small class="hb-error" data-error-for="user[first_name]"></small>
                    </label>
                    <label class="hb-field">
                        <span>{{ t('hotelbooking.last_name') }} <em>*</em></span>
                        <span class="hb-input-wrap"><i class="far fa-user"></i>
                            <input type="text" class="fb-input" name="user[last_name]" value="{{ $guest['last_name'] ?? '' }}" placeholder="{{ t('hotelbooking.enter_last_name') }}" autocomplete="family-name" maxlength="50" required>
                        </span>
                        <small class="hb-error" data-error-for="user[last_name]"></small>
                    </label>
                    <label class="hb-field">
                        <span>{{ t('hotelbooking.email_address') }} <em>*</em></span>
                        <span class="hb-input-wrap"><i class="far fa-envelope"></i>
                            <input type="email" class="fb-input" name="user[email]" value="{{ $guest['email'] ?? '' }}" placeholder="example@email.com" autocomplete="email" maxlength="100" required @if($profile) readonly aria-readonly="true" @endif>
                            @if($profile)<i class="fas fa-lock hb-input-lock" title="{{ t('hotelbooking.email_locked') }}"></i>@endif
                        </span>
                        @if($profile)<small class="hb-field-note">{{ t('hotelbooking.email_locked') }}</small>@endif
                        <small class="hb-error" data-error-for="user[email]"></small>
                    </label>
                    <label class="hb-field">
                        <span>{{ t('hotelbooking.phone_number') }} <em>*</em></span>
                        <span class="hb-input-wrap"><i class="fas fa-phone"></i>
                            <input type="tel" class="fb-input" name="user[phone]" value="{{ $guest['phone'] ?? '' }}" placeholder="+92 300 1234567" autocomplete="tel" maxlength="30" required>
                        </span>
                        <small class="hb-error" data-error-for="user[phone]"></small>
                    </label>
                    <label class="hb-field is-wide">
                        <span>{{ t('hotelbooking.country') }} <em>*</em></span>
                        <span class="hb-input-wrap is-select2">
                            <select class="fb-input hb-country" name="user[country]" autocomplete="country" data-placeholder="{{ t('flightbooking.select_country') }}" required>
                                <option value="">{{ t('flightbooking.select_country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ strtolower($country->country_code) }}" data-flag="fi fi-{{ strtolower($country->country_code) }}" @selected(($guest['country'] ?? '') === strtolower($country->country_code))>{{ $country->country }}</option>
                                @endforeach
                            </select>
                        </span>
                        <small class="hb-error" data-error-for="user[country]"></small>
                    </label>
                </div>

                <div class="hb-actions">
                    <span class="hb-save-status" aria-live="polite"></span>
                    <button type="button" class="td-book-btn hb-next" data-continue="1">
                        {{ t('hotelbooking.continue_travellers') }} <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- Step 2: one form per traveller, from the searched adults + children -->
            <section class="td-card hb-panel" data-step="2" @if($current !== 2) hidden @endif>
                <header class="hb-panel-head">
                    <span class="hb-panel-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <h2 class="hb-panel-title">{{ t('hotelbooking.travellers_information') }}</h2>
                        <p class="hb-panel-desc">{{ t('hotelbooking.travellers_desc') }}</p>
                    </div>
                    <span class="hb-count"><b><i class="fas fa-user-group"></i> {{ str_replace(':count', $adults + $children, t('hotelbooking.traveller_count')) }}</b>
                        <small>{{ $adults }} {{ t('hotelbooking.adults') }}@if($children) · {{ $children }} {{ t('hotelbooking.children') }}@endif</small>
                    </span>
                </header>

                @for($i = 0; $i < $adults + $children; $i++)
                    @php
                        $isChild = $i >= $adults;
                        $t = $travellers[$i] ?? [];
                    @endphp
                    <div class="hb-traveller">
                        <div class="hb-traveller-head">
                            <span class="hb-traveller-num">{{ $i + 1 }}</span>
                            <b>{{ t('hotelbooking.traveller') }} {{ $i + 1 }}</b>
                            <span class="fb-tag">{{ $isChild ? t('hotelbooking.child') : t('hotelbooking.adult') }}</span>
                            @if($i === 0)
                                <button type="button" class="hb-link-btn" id="hbUseContact"><i class="far fa-copy"></i> {{ t('hotelbooking.use_contact') }}</button>
                            @endif
                        </div>
                        <div class="fb-grid is-3">
                            <label class="hb-field">
                                <span>{{ t('hotelbooking.gender') }} <em>*</em></span>
                                <select class="fb-input" name="travellers[{{ $i }}][gender]" required>
                                    <option value="">{{ t('hotelbooking.select_gender') }}</option>
                                    @foreach(['male', 'female', 'other'] as $g)
                                        <option value="{{ $g }}" @selected(($t['gender'] ?? '') === $g)>{{ t('hotelbooking.' . $g) }}</option>
                                    @endforeach
                                </select>
                                <small class="hb-error" data-error-for="travellers[{{ $i }}][gender]"></small>
                            </label>
                            <label class="hb-field">
                                <span>{{ t('hotelbooking.first_name') }} <em>*</em></span>
                                <input type="text" class="fb-input" name="travellers[{{ $i }}][first_name]" value="{{ $t['first_name'] ?? '' }}" placeholder="{{ t('hotelbooking.enter_first_name') }}" maxlength="50" required>
                                <small class="hb-error" data-error-for="travellers[{{ $i }}][first_name]"></small>
                            </label>
                            <label class="hb-field">
                                <span>{{ t('hotelbooking.last_name') }} <em>*</em></span>
                                <input type="text" class="fb-input" name="travellers[{{ $i }}][last_name]" value="{{ $t['last_name'] ?? '' }}" placeholder="{{ t('hotelbooking.enter_last_name') }}" maxlength="50" required>
                                <small class="hb-error" data-error-for="travellers[{{ $i }}][last_name]"></small>
                            </label>
                        </div>
                    </div>
                @endfor

                <div class="hb-actions">
                    <button type="button" class="fb-back" data-back="1"><i class="fas fa-arrow-left"></i> {{ t('hotelbooking.back_guest') }}</button>
                    <span class="hb-save-status" aria-live="polite"></span>
                    <button type="button" class="td-book-btn hb-next" data-continue="2">
                        {{ t('hotelbooking.continue_payment') }} <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- Step 3: configured payment gateways only -->
            <section class="td-card hb-panel" data-step="3" @if($current !== 3) hidden @endif>
                <header class="hb-panel-head">
                    <span class="hb-panel-icon"><i class="far fa-credit-card"></i></span>
                    <div>
                        <h2 class="hb-panel-title">{{ t('hotelbooking.payment_methods') }}</h2>
                        <p class="hb-panel-desc">{{ t('hotelbooking.payment_desc') }}</p>
                    </div>
                </header>

                <div class="hb-pays" role="radiogroup">
                    @foreach($payment as $value)
                        <label class="hb-pay">
                            <input type="radio" name="accept_payment" value="{{ $value->name }}" data-label="{{ ucfirst(str_replace('_', ' ', $value->name)) }}" @checked($selectedPayment === $value->name)>
                            <span class="hb-pay-radio"></span>
                            <img src="{{ url('public/assets/images/settings/payment/' . $value->name . '.png') }}" alt="" class="hb-pay-logo">
                            <span class="hb-pay-text"><b>{{ ucfirst(str_replace('_', ' ', $value->name)) }}</b></span>
                        </label>
                    @endforeach
                    @if($agentWallet)
                        <label class="hb-pay">
                            <input type="radio" name="accept_payment" value="agent_wallet" data-label="Agent Wallet" @checked($selectedPayment === 'agent_wallet')>
                            <span class="hb-pay-radio"></span>
                            <span class="hb-pay-logo is-icon"><i class="fas fa-wallet"></i></span>
                            <span class="hb-pay-text"><b>Agent Wallet</b><small>Available Balance: PKR {{ number_format($agentWallet->balance, 2) }}</small></span>
                        </label>
                    @endif
                </div>
                <small class="hb-error" data-error-for="accept_payment"></small>

                <div class="hb-pay-summary">
                    <div><small>{{ t('hotelbooking.selected_method') }}</small><b id="hbSelectedMethod">{{ $selectedPayment ? ucfirst(str_replace('_', ' ', $selectedPayment)) : '—' }}</b></div>
                    <div class="is-amount"><small>{{ t('hotelbooking.amount_to_pay') }}</small><b>{{ $hbPrice }}</b></div>
                </div>
                <p class="hb-secure-note"><i class="fas fa-shield-halved"></i> {{ t('hotelbooking.pay_secure_note') }}</p>

                <div class="hb-actions">
                    <button type="button" class="fb-back" data-back="2"><i class="fas fa-arrow-left"></i> {{ t('hotelbooking.back_travellers') }}</button>
                    <span class="hb-save-status" aria-live="polite"></span>
                    <button type="submit" class="td-book-btn hb-next" id="hbConfirm">
                        <i class="fas fa-lock"></i> <span>{{ t('hotelbooking.confirm_booking') }}</span>
                    </button>
                </div>
            </section>
        </div>

        <!-- Booking summary (visible on every step; collapsible on phones) -->
        <aside class="hb-summary" id="hbSummary">
            <button type="button" class="hb-sum-toggle" aria-expanded="false" aria-controls="hbSummaryBody">
                <span><i class="fas fa-receipt"></i> {{ t('hotelbooking.booking_summary') }}</span>
                <b>{{ $hbPrice }}</b>
                <i class="fas fa-chevron-down hb-sum-caret"></i>
            </button>
            <div class="td-card hb-sum-card" id="hbSummaryBody">
                <h2 class="hb-sum-title">{{ t('hotelbooking.booking_summary') }}</h2>

                <div class="hb-hotel">
                    <div class="hb-hotel-img">
                        @if($hbImage)
                            <img src="{{ $hbImage }}" alt="{{ $booking_data['hotel_name'] }}" onerror="this.onerror=null;this.remove()">
                        @endif
                        <i class="fas fa-hotel"></i>
                    </div>
                    <div class="hb-hotel-info">
                        <span class="hb-badge">{{ $hbStars > 0 ? $hbStars . ' ' . t('hotelbooking.star_hotel') : t('hotelbooking.hotel') }}</span>
                        <h3 dir="auto">{{ $booking_data['hotel_name'] }}</h3>
                        <div class="hb-hotel-rating">
                            @if($hbStars > 0)
                                <span class="td-stars">@for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= $hbStars ? 'is-on' : '' }}"></i>@endfor</span>
                            @endif
                            @if($hbScore)
                                <span class="hb-score">{{ number_format($hbScore, 1) }}</span>
                            @endif
                        </div>
                        @if(!empty($booking_data['address']))
                            <p class="hb-hotel-address" dir="auto"><i class="fas fa-location-dot"></i> {{ $booking_data['address'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="hb-sum-rows">
                    <div class="hb-row"><span><i class="fas fa-bed"></i> {{ t('hotelbooking.room_type') }}</span><b dir="auto">{{ $room->name }}</b></div>
                    <div class="hb-row"><span><i class="far fa-calendar"></i> {{ t('hotelbooking.check_in') }}</span><b>{{ \Carbon\Carbon::parse($booking_data['checkin'])->format('D, M d, Y') }}</b></div>
                    <div class="hb-row"><span><i class="far fa-calendar"></i> {{ t('hotelbooking.check_out') }}</span><b>{{ \Carbon\Carbon::parse($booking_data['checkout'])->format('D, M d, Y') }}</b></div>
                    <div class="hb-row"><span><i class="far fa-moon"></i> {{ t('hotelbooking.nights') }}</span><b>{{ $days }}</b></div>
                    <div class="hb-row"><span><i class="fas fa-user"></i> {{ t('hotelbooking.adults') }}</span><b>{{ $adults }}</b></div>
                    <div class="hb-row"><span><i class="fas fa-child"></i> {{ t('hotelbooking.children') }}</span><b>{{ $children }}</b></div>
                    <div class="hb-row"><span><i class="fas fa-door-closed"></i> {{ t('hotelbooking.rooms') }}</span><b>{{ (int) $draft->rooms }}</b></div>
                </div>

                @if(!empty($booking_option['cancel_restricted']))
                    <div class="option-notice restricted fb-notice"><i class="fas fa-ban"></i><span>{{ $booking_option['cancel_restricted_note'] ?? 'Cancellation not allowed' }}</span></div>
                @elseif(!empty($booking_option['non_refundable']))
                    <div class="option-notice restricted fb-notice"><i class="fas fa-times-circle"></i><span>{{ t('hoteldetails.non_refundable') }}</span></div>
                @endif
                @if(!empty($booking_option['min_stay']) && (int) $booking_option['min_stay'] > 0)
                    <div class="option-notice min-stay fb-notice"><i class="fas fa-calendar-day"></i><span>{{ str_replace(':nights', $booking_option['min_stay'], t('hoteldetails.min_stay_note')) }}</span></div>
                @endif

                <h3 class="hb-sum-sub">{{ t('hotelbooking.price_summary') }}</h3>
                <div class="hb-row"><span>{{ str_replace(':nights', $days, t('hotelbooking.room_charges')) }}</span><b>{{ $hbPrice }}</b></div>

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

                <div class="hb-total">
                    <span>{{ t('hotelbooking.total_amount') }}</span>
                    <b>{{ $hbPrice }}</b>
                </div>
                <p class="td-secure hb-secure"><i class="fas fa-lock"></i> {{ t('hotelbooking.secure_payment') }}</p>
            </div>
        </aside>
    </form>
</div>

<script>
(function () {
    const root = document.getElementById('hbCheckout');
    if (!root) return;

    const form = document.getElementById('hbForm');
    const saveUrl = root.dataset.saveUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const pendingKey = 'hb_pending_' + root.dataset.token;
    const panels = [...root.querySelectorAll('.hb-panel')];
    const text = {
        required: @json(t('hotelbooking.field_required')),
        email: @json(t('hotelbooking.invalid_email')),
        payment: @json(t('hotelbooking.select_payment')),
        saving: @json(t('hotelbooking.saving')),
        saved: @json(t('hotelbooking.saved')),
        offline: @json(t('hotelbooking.save_failed')),
        network: @json(t('hotelbooking.network_error')),
        processing: @json(t('hotelbooking.processing')),
    };
    const state = {
        current: +root.dataset.current,
        completed: JSON.parse(root.dataset.completed || '[]'),
        max: +root.dataset.max,
        busy: false,
    };

    const panel = (step) => panels.find(p => +p.dataset.step === step);

    // Values of one step, in the shape the save endpoint expects
    function stepData(step) {
        const fd = new FormData(form);
        if (step === 1) {
            const data = {};
            ['first_name', 'last_name', 'email', 'phone', 'country'].forEach(f => data[f] = fd.get(`user[${f}]`) || '');
            return data;
        }
        if (step === 2) {
            const travellers = [];
            panel(2).querySelectorAll('[name^="travellers["]').forEach(el => {
                const [, i, field] = el.name.match(/^travellers\[(\d+)\]\[(\w+)\]$/);
                (travellers[i] = travellers[i] || {})[field] = el.value;
            });
            return { travellers };
        }
        return { payment: fd.get('accept_payment') || '' };
    }

    function setStatus(message, isError) {
        root.querySelectorAll('.hb-save-status').forEach(el => {
            el.textContent = message;
            el.classList.toggle('is-error', !!isError);
        });
    }

    async function post(body) {
        const res = await fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body),
            credentials: 'same-origin',
        });
        const json = await res.json().catch(() => ({}));
        if (res.status === 409 && json.redirect) { window.location.href = json.redirect; }
        return { ok: res.ok, status: res.status, json };
    }

    // ---- inline errors ----
    function clearErrors(scope) {
        scope.querySelectorAll('.hb-error').forEach(e => e.textContent = '');
        scope.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
    }

    function showErrors(scope, errors) {
        clearErrors(scope);
        let first = null;
        Object.entries(errors).forEach(([name, message]) => {
            const slot = scope.querySelector(`[data-error-for="${CSS.escape(name)}"]`);
            if (slot) slot.textContent = message;
            const input = scope.querySelector(`[name="${CSS.escape(name)}"]`);
            if (input) {
                input.classList.add('is-invalid');
                first = first || input;
            }
        });
        // guest fields may be folded away for signed-in users
        const fields = document.getElementById('hbGuestFields');
        if (fields && fields.hidden && scope.contains(fields) && Object.keys(errors).length) toggleContact(true);
        if (first) first.focus({ preventScroll: false });
    }

    // quick check before asking the server (the server validates again)
    function localErrors(step) {
        const errors = {};
        const scope = panel(step);
        scope.querySelectorAll('[required]').forEach(el => {
            if (!el.value.trim()) errors[el.name] = text.required;
            else if (el.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value.trim())) errors[el.name] = text.email;
        });
        if (step === 3 && !form.querySelector('[name="accept_payment"]:checked')) errors.accept_payment = text.payment;
        return errors;
    }

    // ---- stepper ----
    function render() {
        panels.forEach(p => p.hidden = +p.dataset.step !== state.current);
        root.querySelectorAll('.hb-step').forEach(li => {
            const n = +li.dataset.step;
            const btn = li.querySelector('.hb-step-btn');
            li.classList.toggle('is-active', n === state.current);
            li.classList.toggle('is-done', n !== state.current && state.completed.includes(n));
            btn.disabled = n > state.max;
            n === state.current ? btn.setAttribute('aria-current', 'step') : btn.removeAttribute('aria-current');
        });
        setStatus('');
    }

    function applyState(json) {
        state.current = json.current_step;
        state.completed = json.completed_steps;
        state.max = json.max_step;
        render();
        root.querySelector('.hb-steps').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    async function continueFrom(step, button) {
        if (state.busy) return;
        const scope = panel(step);
        const errors = localErrors(step);
        if (Object.keys(errors).length) { showErrors(scope, errors); return; }
        clearErrors(scope);
        state.busy = true;
        button.classList.add('is-loading');
        button.disabled = true;
        try {
            clearTimeout(autosaveTimer);
            const { ok, status, json } = await post({ step, intent: 'continue', data: stepData(step) });
            if (ok) { forgetPending(); applyState(json); }
            else if (status === 422 && json.errors) showErrors(scope, json.errors);
            else setStatus(json.message || text.network, true);
        } catch (e) {
            setStatus(text.network, true);
        } finally {
            state.busy = false;
            button.classList.remove('is-loading');
            button.disabled = false;
        }
    }

    async function goTo(target) {
        if (state.busy || target === state.current || target > state.max) return;
        state.busy = true;
        const from = state.current;
        try {
            clearTimeout(autosaveTimer);
            const { ok, json } = await post({ step: from, intent: 'goto', target, data: stepData(from) });
            if (ok) { forgetPending(); applyState(json); }
            else setStatus(json.message || text.network, true);
        } catch (e) {
            // offline: still let the customer look back, keep the values to resend
            remember(from);
            state.current = target;
            render();
            setStatus(text.offline, true);
        } finally {
            state.busy = false;
        }
    }

    root.querySelectorAll('[data-continue]').forEach(btn => btn.addEventListener('click', () => continueFrom(+btn.dataset.continue, btn)));
    root.querySelectorAll('[data-back]').forEach(btn => btn.addEventListener('click', () => goTo(+btn.dataset.back)));
    root.querySelectorAll('[data-goto]').forEach(btn => btn.addEventListener('click', () => goTo(+btn.dataset.goto)));

    // ---- autosave (debounced), with a local copy only while offline ----
    let autosaveTimer;
    function remember(step) {
        try { localStorage.setItem(pendingKey, JSON.stringify({ step, data: stepData(step) })); } catch (e) {}
    }
    function forgetPending() {
        try { localStorage.removeItem(pendingKey); } catch (e) {}
    }
    async function autosave(step, data) {
        setStatus(text.saving);
        try {
            const { ok } = await post({ step, intent: 'autosave', data });
            if (!ok) throw new Error();
            forgetPending();
            setStatus(text.saved);
        } catch (e) {
            remember(step);
            setStatus(text.offline, true);
        }
    }
    form.addEventListener('input', scheduleAutosave);
    form.addEventListener('change', scheduleAutosave);
    function scheduleAutosave(e) {
        const p = e.target.closest('.hb-panel');
        if (!p || !e.target.name) return;
        if (e.target.classList.contains('is-invalid') && e.target.value.trim()) {
            e.target.classList.remove('is-invalid');
            const slot = p.querySelector(`[data-error-for="${CSS.escape(e.target.name)}"]`);
            if (slot) slot.textContent = '';
        }
        const step = +p.dataset.step;
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => autosave(step, stepData(step)), 800);
    }

    // values typed while the connection was down: put them back and resend
    function restorePending() {
        let pending = null;
        try { pending = JSON.parse(localStorage.getItem(pendingKey) || 'null'); } catch (e) {}
        if (!pending || !pending.data) return;
        const set = (name, value) => { const el = form.querySelector(`[name="${CSS.escape(name)}"]`); if (el && value != null) el.value = value; };
        if (pending.step === 1) Object.entries(pending.data).forEach(([f, v]) => set(`user[${f}]`, v));
        if (pending.step === 2) (pending.data.travellers || []).forEach((row, i) => row && Object.entries(row).forEach(([f, v]) => set(`travellers[${i}][${f}]`, v)));
        autosave(pending.step, pending.data);
    }
    restorePending();
    window.addEventListener('online', restorePending);

    // ---- step 1: signed-in contact card ----
    const editBtn = document.getElementById('hbEditContact');
    function toggleContact(open) {
        const fields = document.getElementById('hbGuestFields');
        fields.hidden = !open;
        if (editBtn) editBtn.setAttribute('aria-expanded', String(open));
    }
    if (editBtn) editBtn.addEventListener('click', () => toggleContact(document.getElementById('hbGuestFields').hidden));

    // ---- step 2: copy the contact's name into traveller 1 ----
    const useContact = document.getElementById('hbUseContact');
    if (useContact) useContact.addEventListener('click', () => {
        ['first_name', 'last_name'].forEach(f => {
            const el = form.querySelector(`[name="travellers[0][${f}]"]`);
            el.value = form.querySelector(`[name="user[${f}]"]`).value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    // ---- country: Select2 with flags (Select2/jQuery load at the end of the page) ----
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.jQuery || !jQuery.fn.select2) return;
        const flagOption = (state) => {
            const flag = state.element && state.element.dataset.flag;
            if (!state.id || !flag) return state.text;
            const wrap = document.createElement('span');
            wrap.className = 'hb-flag-opt';
            wrap.innerHTML = `<span class="${flag}"></span>`;
            wrap.append(state.text);
            return jQuery(wrap);
        };
        const country = form.querySelector('.hb-country');
        jQuery(country).select2({
            width: '100%',
            placeholder: country.dataset.placeholder,
            templateResult: flagOption,
            templateSelection: flagOption,
            dropdownParent: jQuery(panel(1)),
        }).on('select2:select', () => country.dispatchEvent(new Event('input', { bubbles: true })))
          .on('select2:open', () => {
              const search = panel(1).querySelector('.select2-search__field');
              if (search) search.placeholder = @json(t('hotelbooking.search_country'));
          });

        // Open the list below the field: when there isn't room underneath,
        // scroll the page up a little first, then open.
        const listHeight = 320;
        let scrolled = false;
        let keepY = null;
        jQuery(country).on('select2:opening', (e) => {
            if (scrolled) { scrolled = false; keepY = window.scrollY; return; }
            const box = country.nextElementSibling.getBoundingClientRect();
            const need = box.bottom + listHeight - window.innerHeight;
            if (need <= 0) return;
            e.preventDefault();
            window.scrollBy({ top: need, behavior: 'smooth' });
            // open once the smooth scroll has settled (or after 1s at most)
            let last = -1, ticks = 0;
            const wait = setInterval(() => {
                ticks++;
                if (window.scrollY === last || ticks > 20) {
                    clearInterval(wait);
                    scrolled = true;
                    jQuery(country).select2('open');
                }
                last = window.scrollY;
            }, 50);
        }).on('select2:open', () => {
            // some mobile browsers jump the page as the list is attached; put
            // it back and let Select2 place the list below again
            requestAnimationFrame(() => {
                if (keepY === null) return;
                const y = keepY;
                keepY = null;
                if (Math.abs(window.scrollY - y) < 5) return;
                window.scrollTo({ top: y, behavior: 'instant' });
                document.querySelectorAll('.select2-container--open').forEach(el => el.classList.remove('select2-container--above'));
                panel(1).querySelectorAll('.select2-dropdown--above').forEach(el => el.classList.remove('select2-dropdown--above'));
                window.dispatchEvent(new Event('resize'));
            });
        });
    });

    // ---- step 3: selected method label ----
    form.querySelectorAll('[name="accept_payment"]').forEach(r => r.addEventListener('change', () => {
        document.getElementById('hbSelectedMethod').textContent = r.dataset.label;
    }));

    // ---- mobile summary ----
    const sumToggle = root.querySelector('.hb-sum-toggle');
    sumToggle.addEventListener('click', () => {
        const open = document.getElementById('hbSummary').classList.toggle('is-open');
        sumToggle.setAttribute('aria-expanded', String(open));
    });

    // ---- final submit: only from step 3, once ----
    form.addEventListener('submit', (e) => {
        if (state.current !== 3) {
            e.preventDefault();
            const btn = panel(state.current).querySelector('[data-continue]');
            if (btn) continueFrom(state.current, btn);
            return;
        }
        const errors = localErrors(3);
        if (Object.keys(errors).length || state.busy) {
            e.preventDefault();
            showErrors(panel(3), errors);
            return;
        }
        state.busy = true;
        clearTimeout(autosaveTimer);
        const btn = document.getElementById('hbConfirm');
        btn.disabled = true;
        btn.classList.add('is-loading');
        btn.querySelector('span').textContent = text.processing;
    });

    // coming back via the browser's back/forward cache: re-enable the button
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        const btn = document.getElementById('hbConfirm');
        btn.disabled = false;
        btn.classList.remove('is-loading');
        state.busy = false;
    });
})();
</script>
@endsection
