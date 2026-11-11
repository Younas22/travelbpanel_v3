{{--
    Tour / Umrah checkout, in the hotel checkout design (.hb-*): guest → travellers
    → payment, with the package summary on the side. Field names are the ones
    TourController / UmrahController::storeBooking() read.

    Params:
      $action      form URL
      $hidden      [name => value] hidden inputs (already encrypted)
      $groups      traveller groups: [count, p (field prefix), type_field, type_value, label]
      $summary     [image, icon, badge, name, place, stars, rows => [[icon, label, value]],
                    prices => [[label, value]], total]
      $payment, $agentWallet, $countries
--}}
@php
    // signed in: the account is the booking contact (fields stay editable)
    $me = auth()->user();
    $meCountry = null;
    if ($me && $me->country) {
        $meCountry = $countries->first(fn($c) => strcasecmp($c->country, $me->country) === 0 || strcasecmp($c->country_code, $me->country) === 0);
    }
    $contact = [
        'first_name' => old('user.first_name', $me->first_name ?? ''),
        'last_name' => old('user.last_name', $me->last_name ?? ''),
        'email' => old('user.email', $me->email ?? ''),
        'phone' => old('user.phone', $me->phone ?? ''),
        'country' => old('user.country', $meCountry ? strtolower($meCountry->country_code) : ''),
    ];
    $travellerTotal = collect($groups)->sum('count');
    $selected = fn($name, $value) => (string) old($name) === (string) $value ? 'selected' : '';
    $firstPay = old('accept_payment', optional($payment->first())->name ?? ($agentWallet ? 'agent_wallet' : null));
    $payLabel = fn($name) => $name === 'agent_wallet' ? 'Agent Wallet' : ucfirst(str_replace('_', ' ', (string) $name));
    $steps = [
        1 => t('hotelbooking.guest_information'),
        2 => t('hotelbooking.travellers_information'),
        3 => t('hotelbooking.payment_methods'),
    ];
    $start = session('error') && old('accept_payment') ? 3 : 1;
    $travellerNo = 0;
@endphp

<div class="fb-page hb-checkout" data-checkout data-start="{{ $start }}">
    @if(session('error'))
        <div class="fb-alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <div class="hb-head">
        <h1 class="td-title">{{ t('hotelbooking.complete_booking') }}</h1>
        <p class="hb-subtitle">{{ t('hotelbooking.complete_booking_sub') }}</p>
    </div>

    <form action="{{ $action }}" method="POST" class="hb-layout" novalidate>
        @csrf
        @foreach($hidden as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        <div class="hb-main">
            <ol class="hb-steps" aria-label="{{ t('hotelbooking.booking_progress') }}">
                @foreach($steps as $n => $label)
                    <li class="hb-step {{ $n === 1 ? 'is-active' : '' }}" data-step="{{ $n }}">
                        <button type="button" class="hb-step-btn" data-goto="{{ $n }}" @if($n > 1) disabled @else aria-current="step" @endif>
                            <span class="hb-step-num"><span>{{ $n }}</span><i class="fas fa-check"></i></span>
                            <span class="hb-step-text"><small>{{ str_replace(':n', $n, t('hotelbooking.step_label')) }}</small><b>{{ $label }}</b></span>
                        </button>
                    </li>
                @endforeach
            </ol>

            <!-- Step 1: booking contact -->
            <section class="td-card hb-panel" data-step="1">
                <header class="hb-panel-head">
                    <span class="hb-panel-icon"><i class="fas fa-user"></i></span>
                    <div>
                        <h2 class="hb-panel-title">{{ t('hotelbooking.guest_information') }}</h2>
                        <p class="hb-panel-desc">{{ t('hotelbooking.guest_info_desc') }}</p>
                    </div>
                </header>

                <div class="fb-grid hb-fields">
                    @foreach([
                        ['first_name', 'text', t('hotelbooking.first_name'), t('hotelbooking.enter_first_name'), 'far fa-user', 'given-name'],
                        ['last_name', 'text', t('hotelbooking.last_name'), t('hotelbooking.enter_last_name'), 'far fa-user', 'family-name'],
                        ['email', 'email', t('hotelbooking.email_address'), 'example@email.com', 'far fa-envelope', 'email'],
                        ['phone', 'tel', t('hotelbooking.phone_number'), '+92 300 1234567', 'fas fa-phone', 'tel'],
                    ] as [$f, $type, $label, $placeholder, $icon, $auto])
                        <label class="hb-field">
                            <span>{{ $label }} <em>*</em></span>
                            <span class="hb-input-wrap"><i class="{{ $icon }}"></i>
                                <input type="{{ $type }}" class="fb-input" name="user[{{ $f }}]" value="{{ $contact[$f] }}" placeholder="{{ $placeholder }}" autocomplete="{{ $auto }}" maxlength="100" required>
                            </span>
                            <small class="hb-error"></small>
                        </label>
                    @endforeach
                    <label class="hb-field is-wide">
                        <span>{{ t('hotelbooking.country') }} <em>*</em></span>
                        <select class="fb-input" name="user[country]" autocomplete="country" required>
                            <option value="">{{ t('flightbooking.select_country') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ strtolower($country->country_code) }}" @selected($contact['country'] === strtolower($country->country_code))>{{ $country->country }}</option>
                            @endforeach
                        </select>
                        <small class="hb-error"></small>
                    </label>
                </div>

                <div class="hb-actions">
                    <a href="{{ url()->previous() }}" class="fb-back"><i class="fas fa-arrow-left"></i> {{ t('hotelbooking.back') }}</a>
                    <button type="button" class="td-book-btn hb-next" data-continue="1">
                        {{ t('hotelbooking.continue_travellers') }} <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- Step 2: one block per traveller -->
            <section class="td-card hb-panel" data-step="2" hidden>
                <header class="hb-panel-head">
                    <span class="hb-panel-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <h2 class="hb-panel-title">{{ t('hotelbooking.travellers_information') }}</h2>
                        <p class="hb-panel-desc">{{ t('hotelbooking.travellers_desc') }}</p>
                    </div>
                    <span class="hb-count"><b><i class="fas fa-user-group"></i> {{ str_replace(':count', $travellerTotal, t('hotelbooking.traveller_count')) }}</b>
                        <small>{{ collect($groups)->filter(fn($g) => $g['count'] > 0)->map(fn($g) => $g['count'] . ' ' . $g['label'])->implode(' · ') }}</small>
                    </span>
                </header>

                @foreach($groups as $g)
                    @for($i = 1; $i <= $g['count']; $i++)
                        @php $travellerNo++; $p = $g['p']; @endphp
                        <div class="hb-traveller">
                            <div class="hb-traveller-head">
                                <span class="hb-traveller-num">{{ $travellerNo }}</span>
                                <b>{{ t('hotelbooking.traveller') }} {{ $travellerNo }}</b>
                                <span class="fb-tag">{{ $g['label'] }}</span>
                                @if($travellerNo === 1)
                                    <button type="button" class="hb-link-btn" data-use-contact="{{ $p }}first_name_{{ $i }}|{{ $p }}last_name_{{ $i }}"><i class="far fa-copy"></i> {{ t('hotelbooking.use_contact') }}</button>
                                @endif
                            </div>
                            <input type="hidden" name="{{ $g['type_field'] }}{{ $i }}" value="{{ $g['type_value'] }}"/>
                            <div class="fb-grid is-3">
                                <label class="hb-field">
                                    <span>{{ t('hotelbooking.gender') }} <em>*</em></span>
                                    <span class="hb-input-wrap"><i class="fas fa-venus-mars"></i>
                                        <select class="fb-input hb-select" name="{{ $p }}gender_{{ $i }}" required>
                                            <option value="">{{ t('hotelbooking.select_gender') }}</option>
                                            @foreach(['male', 'female', 'other'] as $gv)
                                                <option value="{{ $gv }}" {{ $selected($p . 'gender_' . $i, $gv) }}>{{ t('hotelbooking.' . $gv) }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                    <small class="hb-error"></small>
                                </label>
                                <label class="hb-field">
                                    <span>{{ t('hotelbooking.first_name') }} <em>*</em></span>
                                    <span class="hb-input-wrap"><i class="far fa-user"></i>
                                        <input type="text" class="fb-input" name="{{ $p }}first_name_{{ $i }}" value="{{ old($p . 'first_name_' . $i) }}" placeholder="{{ t('hotelbooking.enter_first_name') }}" maxlength="50" required>
                                    </span>
                                    <small class="hb-error"></small>
                                </label>
                                <label class="hb-field">
                                    <span>{{ t('hotelbooking.last_name') }} <em>*</em></span>
                                    <span class="hb-input-wrap"><i class="far fa-user"></i>
                                        <input type="text" class="fb-input" name="{{ $p }}last_name_{{ $i }}" value="{{ old($p . 'last_name_' . $i) }}" placeholder="{{ t('hotelbooking.enter_last_name') }}" maxlength="50" required>
                                    </span>
                                    <small class="hb-error"></small>
                                </label>
                            </div>
                        </div>
                    @endfor
                @endforeach

                <div class="hb-actions">
                    <button type="button" class="fb-back" data-back="1"><i class="fas fa-arrow-left"></i> {{ t('hotelbooking.back_guest') }}</button>
                    <button type="button" class="td-book-btn hb-next" data-continue="2">
                        {{ t('hotelbooking.continue_payment') }} <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- Step 3: payment -->
            <section class="td-card hb-panel" data-step="3" hidden>
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
                            <input type="radio" name="accept_payment" value="{{ $value->name }}" data-label="{{ $payLabel($value->name) }}" @checked($firstPay === $value->name)>
                            <span class="hb-pay-radio"></span>
                            <img src="{{ url('public/assets/images/settings/payment/' . $value->name . '.png') }}" alt="" class="hb-pay-logo">
                            <span class="hb-pay-text"><b>{{ $payLabel($value->name) }}</b></span>
                        </label>
                    @endforeach
                    @if($agentWallet)
                        <label class="hb-pay">
                            <input type="radio" name="accept_payment" value="agent_wallet" data-label="Agent Wallet" @checked($firstPay === 'agent_wallet')>
                            <span class="hb-pay-radio"></span>
                            <span class="hb-pay-logo is-icon"><i class="fas fa-wallet"></i></span>
                            <span class="hb-pay-text"><b>Agent Wallet</b><small>Available Balance: PKR {{ number_format($agentWallet->balance, 2) }}</small></span>
                        </label>
                    @endif
                </div>
                <small class="hb-error" data-error-for="accept_payment"></small>

                <div class="hb-pay-summary">
                    <div><small>{{ t('hotelbooking.selected_method') }}</small><b data-selected-method>{{ $firstPay ? $payLabel($firstPay) : '—' }}</b></div>
                    <div class="is-amount"><small>{{ t('hotelbooking.amount_to_pay') }}</small><b>{{ $summary['total'] }}</b></div>
                </div>
                <p class="hb-secure-note"><i class="fas fa-shield-halved"></i> {{ t('hotelbooking.pay_secure_note') }}</p>

                <div class="hb-actions">
                    <button type="button" class="fb-back" data-back="2"><i class="fas fa-arrow-left"></i> {{ t('hotelbooking.back_travellers') }}</button>
                    <button type="submit" class="td-book-btn hb-next" data-confirm>
                        <i class="fas fa-lock"></i> <span>{{ t('hotelbooking.confirm_booking') }}</span>
                    </button>
                </div>
            </section>
        </div>

        <!-- Package summary (visible on every step; collapsible on phones) -->
        <aside class="hb-summary">
            <button type="button" class="hb-sum-toggle" aria-expanded="false">
                <span><i class="fas fa-receipt"></i> {{ t('hotelbooking.booking_summary') }}</span>
                <b>{{ $summary['total'] }}</b>
                <i class="fas fa-chevron-down hb-sum-caret"></i>
            </button>
            <div class="td-card hb-sum-card">
                <h2 class="hb-sum-title">{{ t('hotelbooking.booking_summary') }}</h2>

                <div class="hb-hotel">
                    <div class="hb-hotel-img">
                        @if($summary['image'])
                            <img src="{{ $summary['image'] }}" alt="" onerror="this.onerror=null;this.remove()">
                        @endif
                        <i class="fas {{ $summary['icon'] }}"></i>
                    </div>
                    <div class="hb-hotel-info">
                        @if($summary['badge'])<span class="hb-badge">{{ $summary['badge'] }}</span>@endif
                        <h3 dir="auto">{{ $summary['name'] }}</h3>
                        @if($summary['stars'])
                            <span class="td-stars">@for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= $summary['stars'] ? 'is-on' : '' }}"></i>@endfor</span>
                        @endif
                        @if($summary['place'])
                            <p class="hb-hotel-address" dir="auto"><i class="fas fa-location-dot"></i> {{ $summary['place'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="hb-sum-rows">
                    @foreach($summary['rows'] as [$icon, $label, $value])
                        <div class="hb-row"><span><i class="{{ $icon }}"></i> {{ $label }}</span><b dir="auto">{{ $value }}</b></div>
                    @endforeach
                </div>

                <h3 class="hb-sum-sub">{{ t('hotelbooking.price_summary') }}</h3>
                @foreach($summary['prices'] as [$label, $value])
                    <div class="hb-row"><span>{{ $label }}</span><b>{{ $value }}</b></div>
                @endforeach
                <div class="hb-total">
                    <span>{{ t('hotelbooking.total_amount') }}</span>
                    <b>{{ $summary['total'] }}</b>
                </div>
                <p class="td-secure hb-secure"><i class="fas fa-lock"></i> {{ t('hotelbooking.secure_payment') }}</p>
            </div>
        </aside>
    </form>
</div>

@include('common.checkout-script')
<script>
// "Use contact details": the contact's name into traveller 1
document.querySelectorAll('[data-use-contact]').forEach((btn) => btn.addEventListener('click', () => {
    const form = btn.closest('form');
    const [first, last] = btn.dataset.useContact.split('|');
    [[first, 'first_name'], [last, 'last_name']].forEach(([target, from]) => {
        const el = form.querySelector(`[name="${target}"]`);
        el.value = form.querySelector(`[name="user[${from}]"]`).value;
        el.dispatchEvent(new Event('input', { bubbles: true }));
    });
}));
</script>
