{{--
    Tour / Umrah booking confirmation + invoice, in the hotel invoice design
    (A4 sheet, .inv-*). Print / PDF / Email / Pay Now: common/invoice-script.

    Params:
      $booking   booking row (booking_code_ref, statuses, gateway, txn, pnr, created_at, agent_id, booked_via)
      $keys      translation group: 'tourinvoice' | 'umrahinvoice'
      $emailUrl  POST url that emails the invoice link
      $payUrl    Pay Now url (used while $canPay)
      $canPay
      $trip      [[icon, label, value, note]] — details strip
      $travellers [[type, name, gender]]
      $summary   [image, icon, badge, name, place, rows => [[icon, label, value]], prices => [[label, value]], total]
      $policies  [[icon, title, [lines]]]
      $inclusions, $exclusions  lists of names
--}}
@php
    $status = $booking->booking_status_flag ?: 'pending';
    $payState = $booking->booking_payment_state ?: 'unpaid';
    $gateway = strtolower((string) $booking->booking_payment_gateway);
    $isPaid = $payState === 'paid';
    $isCancelled = $status === 'cancelled';
    $isConfirmed = $status === 'confirmed';
    $createdAt = \Carbon\Carbon::parse($booking->created_at);

    // An agent's booking carries the agent's own agency branding (as the flight invoice)
    $bookingAgent = ($booking->booked_via ?? null) === 'agent' && !empty($booking->agent_id) ? \App\Models\User::find($booking->agent_id) : null;
    $logo = ($bookingAgent && $bookingAgent->company_logo)
        ? url('public/assets/images/settings/branding/' . $bookingAgent->company_logo)
        : getSettingImage('business_logo', 'branding');
    $businessName = ($bookingAgent && $bookingAgent->company_name) ? $bookingAgent->company_name : getSetting('business_name', 'main', 'Travel Booking Panel');
    $contactPhone = ($bookingAgent && $bookingAgent->company_phone) ? $bookingAgent->company_phone : getSetting('contact_phone', 'contact', '');
    $contactEmail = $bookingAgent ? $bookingAgent->email : getSetting('contact_email', 'contact', '');
    $agencyAddress = $bookingAgent ? $bookingAgent->company_address : null;

    [$headState, $headIcon, $headMsg] = match (true) {
        $isCancelled => ['is-cancelled', 'fa-circle-xmark', t('hotelinvoice.msgCancelled')],
        $isConfirmed => ['is-confirmed', 'fa-check', t($keys . '.msgConfirmed')],
        $isPaid => ['is-pending', 'fa-check', t($keys . '.msgPaidPending')],
        $gateway === 'after_pay' => ['is-pending', 'fa-check', t($keys . '.msgPayLater')],
        default => ['is-pending', 'fa-check', t($keys . '.msgCreated')],
    };
    $steps = [
        ['icon' => $summary['icon'], 'label' => t($keys . '.stepPackage'), 'state' => 'is-done'],
        ['icon' => 'fa-users', 'label' => t('flightinvoice.stepTravellers'), 'state' => count($travellers) ? 'is-done' : 'is-current'],
        ['icon' => 'fa-credit-card', 'label' => t('hotelinvoice.stepPayment'), 'state' => $isPaid ? 'is-done' : ($isCancelled ? '' : 'is-current')],
        ['icon' => 'fa-circle-check', 'label' => t('hotelinvoice.stepConfirmation'), 'state' => $isConfirmed ? 'is-done' : ($isCancelled ? 'is-cancelled' : ($isPaid ? 'is-current' : ''))],
    ];
    $badge = fn($value) => match ($value) {
        'confirmed', 'paid' => 'is-success',
        'cancelled' => 'is-danger',
        'refunded' => 'is-info',
        default => 'is-warning',
    };
    $stateText = function ($value) {
        $text = t('hotelinvoice.' . $value);
        return $text === 'hotelinvoice.' . $value ? ucfirst($value) : $text;
    };
    $gatewayName = $gateway === 'agent_wallet' ? t('hotelinvoice.agentWallet') : ucwords(str_replace('_', ' ', $gateway));
    $gatewayLogo = $gateway && file_exists(public_path('assets/images/settings/payment/' . $gateway . '.png'))
        ? url('public/assets/images/settings/payment/' . $gateway . '.png') : null;
    $gatewayDesc = t('hotelinvoice.gw_' . $gateway);
    $gatewayDesc = $gatewayDesc === 'hotelinvoice.gw_' . $gateway ? null : $gatewayDesc;

    $isRtl = app()->getLocale() === 'ar';
    $siteCss = $isRtl ? 'rtlstyle.css' : 'style.css';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t($keys . '.title') }} #INV-{{ $booking->booking_code_ref }}</title>
    <link rel="icon" href="{{ getSettingImage('favicon', 'branding') }}" type="image/png">
    <script src="{{ url('public/assets/libs/tailwind/tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="{{ url('public/assets/libs/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('public/assets/css/' . $siteCss) }}?v={{ filemtime(public_path('assets/css/' . $siteCss)) }}">
</head>
<body class="view-standalone view-invoice">

<!-- Top action bar (not printed / not in the PDF) -->
<header class="inv-bar" data-html2canvas-ignore>
    <div class="inv-bar-inner">
        <a href="{{ url('/') }}" class="inv-bar-logo"><img src="{{ $logo }}" alt="{{ $businessName }}"></a>
        <div class="inv-bar-actions">
            <button type="button" class="inv-btn" id="invPrint"><i class="fas fa-print"></i> <span>{{ t($keys . '.print') }}</span></button>
            <button type="button" class="inv-btn" id="invPdf"><i class="fas fa-download"></i> <span>{{ t($keys . '.downloadPdf') }}</span></button>
            <button type="button" class="inv-btn is-primary" id="invEmail" data-url="{{ $emailUrl }}"><i class="fas fa-envelope"></i> <span>{{ t($keys . '.sendEmail') }}</span></button>
        </div>
    </div>
    <div class="inv-toast" id="invToast" role="status" aria-live="polite" hidden></div>
</header>

<main class="inv-page" id="invDoc">
    <div class="inv-doc-logo"><img src="{{ $logo }}" alt="{{ $businessName }}"></div>

    <!-- Heading: reflects the real booking state -->
    <section class="inv-head {{ $headState }}">
        <span class="inv-head-icon"><i class="fas {{ $headIcon }}"></i></span>
        <div class="inv-head-text">
            <h1>{{ t('hotelinvoice.confirmationTitle') }}</h1>
            <p>{{ $headMsg }}</p>
        </div>
        <div class="inv-head-route" aria-hidden="true"><span></span><i class="fas fa-plane"></i></div>
    </section>

    <!-- Invoice no. / PNR / date / statuses -->
    <section class="inv-status">
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="fas fa-file-invoice"></i></span>
            <div><small>{{ t('hotelinvoice.invoiceNumber') }}</small><b dir="ltr">#INV-{{ $booking->booking_code_ref }}</b></div>
        </div>
        @if(!empty($booking->booking_pnr))
            <div class="inv-status-item">
                <span class="inv-status-icon"><i class="fas fa-hashtag"></i></span>
                <div><small>{{ t($keys . '.bookingPnr') }}</small><b dir="ltr">{{ $booking->booking_pnr }}</b></div>
            </div>
        @endif
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="far fa-calendar"></i></span>
            <div><small>{{ t('hotelinvoice.invoiceDate') }}</small><b dir="ltr">{{ $createdAt->format('d M Y') }}</b></div>
        </div>
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="fas fa-clipboard-check"></i></span>
            <div><small>{{ t('hotelinvoice.bookingStatus') }}</small><b class="inv-badge {{ $badge($status) }}">{{ $stateText($status) }}</b></div>
        </div>
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="fas fa-wallet"></i></span>
            <div><small>{{ t('hotelinvoice.paymentStatus') }}</small><b class="inv-badge {{ $badge($payState) }}">{{ $stateText($payState) }}</b></div>
        </div>
    </section>

    <!-- Progress -->
    <ol class="inv-steps">
        @foreach($steps as $step)
            <li class="inv-step {{ $step['state'] }}">
                <span class="inv-step-icon">
                    @if($step['state'] === 'is-done')<i class="fas fa-check"></i>
                    @elseif($step['state'] === 'is-cancelled')<i class="fas fa-xmark"></i>
                    @else<i class="fas {{ $step['icon'] }}"></i>@endif
                </span>
                <span class="inv-step-label">{{ $step['state'] === 'is-cancelled' ? t('hotelinvoice.cancelled') : $step['label'] }}</span>
            </li>
        @endforeach
    </ol>

    <div class="inv-layout">
        <div class="inv-main">
            <!-- Trip at a glance -->
            <section class="inv-card">
                <h2 class="inv-card-title"><i class="fas {{ $summary['icon'] }}"></i> {{ t($keys . '.packageDetails') }}</h2>
                <div class="inv-stay is-flight">
                    @foreach($trip as [$icon, $label, $value, $note])
                        <div class="inv-stay-item"><i class="{{ $icon }}"></i><small>{{ $label }}</small><b dir="auto">{{ $value }}</b>@if($note)<em>{{ $note }}</em>@endif</div>
                    @endforeach
                </div>
            </section>

            <!-- Travellers -->
            <section class="inv-card">
                <h2 class="inv-card-title"><i class="fas fa-users"></i> {{ t($keys . '.travellersInfo') }}</h2>
                @if(count($travellers))
                    <table class="inv-table">
                        <thead>
                            <tr><th>{{ t($keys . '.no') }}</th><th>{{ t($keys . '.travellerType') }}</th><th>{{ t($keys . '.name') }}</th><th>{{ t($keys . '.gender') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($travellers as $index => [$type, $name, $gender, $isAdult])
                                <tr>
                                    <td data-label="{{ t($keys . '.no') }}">{{ $index + 1 }}</td>
                                    <td data-label="{{ t($keys . '.travellerType') }}"><span class="inv-chip {{ $isAdult ? '' : 'is-muted' }}">{{ $type }}</span></td>
                                    <td data-label="{{ t($keys . '.name') }}" dir="auto"><b>{{ $name ?: '—' }}</b></td>
                                    <td data-label="{{ t($keys . '.gender') }}">{{ $gender ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="inv-empty">{{ t($keys . '.noTravellerInfo') }}</p>
                @endif
            </section>

            <!-- Payment method -->
            @if($gateway)
                <section class="inv-card">
                    <h2 class="inv-card-title"><i class="far fa-credit-card"></i> {{ t('hotelinvoice.paymentMethod') }}</h2>
                    <div class="inv-pay-method">
                        <span class="inv-pay-logo">
                            @if($gatewayLogo)<img src="{{ $gatewayLogo }}" alt="">@else<i class="fas fa-wallet"></i>@endif
                        </span>
                        <div class="inv-pay-text">
                            <b>{{ $gatewayName }}</b>
                            @if($gatewayDesc)<small>{{ $gatewayDesc }}</small>@endif
                            @if(!empty($booking->booking_txn_id))<small>{{ t('hotelinvoice.transactionId') }}: <span dir="ltr">{{ $booking->booking_txn_id }}</span></small>@endif
                        </div>
                        <span class="inv-badge {{ $badge($payState) }}">{{ $isPaid ? t('hotelinvoice.paymentReceived') : ($payState === 'refunded' ? t('hotelinvoice.refunded') : t('hotelinvoice.awaitingPayment')) }}</span>
                    </div>
                </section>
            @endif

            <!-- Important information -->
            <section class="inv-card inv-notes">
                <h2 class="inv-card-title"><i class="fas fa-circle-info"></i> {{ t($keys . '.importantInfo') }}</h2>
                <ul>
                    @foreach(['info1', 'info2', 'info3', 'info4', 'info5'] as $info)
                        <li>{{ t($keys . '.' . $info) }}</li>
                    @endforeach
                </ul>
            </section>
        </div>

        <!-- Booking summary -->
        <aside class="inv-side">
            <div class="inv-summary">
                <div class="inv-summary-img">
                    <i class="fas {{ $summary['icon'] }}"></i>
                    @if($summary['image'])<img src="{{ $summary['image'] }}" alt="" onerror="this.remove()">@endif
                </div>
                <div class="inv-summary-body">
                    <h2 class="inv-card-title"><i class="fas fa-bookmark"></i> {{ t('hotelinvoice.bookingSummary') }}</h2>
                    <div class="inv-summary-hotel">
                        <h3 dir="auto">{{ $summary['name'] }}</h3>
                        @if($summary['badge'])<div class="inv-summary-meta"><span class="inv-chip">{{ $summary['badge'] }}</span></div>@endif
                        @if($summary['place'])<p dir="auto"><i class="fas fa-location-dot"></i> {{ $summary['place'] }}</p>@endif
                    </div>

                    <div class="inv-rows">
                        <div class="inv-row"><span><i class="fas fa-hashtag"></i> {{ t($keys . '.bookingRef') }}</span><b dir="ltr">{{ $booking->booking_code_ref }}</b></div>
                        @foreach($summary['rows'] as [$icon, $label, $value])
                            <div class="inv-row"><span><i class="{{ $icon }}"></i> {{ $label }}</span><b dir="auto">{{ $value }}</b></div>
                        @endforeach
                    </div>

                    <div class="inv-rows is-price">
                        @foreach($summary['prices'] as [$label, $value])
                            <div class="inv-row"><span>{{ $label }}</span><b>{{ $value }}</b></div>
                        @endforeach
                    </div>

                    <div class="inv-total">
                        <span>{{ t('hotelinvoice.totalAmount') }}</span>
                        <b>{{ $summary['total'] }}</b>
                    </div>

                    <!-- Pay Now only while the booking can still be paid through its gateway -->
                    <div class="inv-pay" data-html2canvas-ignore>
                        @if($canPay)
                            <form method="get" action="{{ $payUrl }}" id="invPayForm">
                                <button type="submit" class="inv-pay-btn"><i class="fas fa-lock"></i> <span>{{ t($keys . '.payNow') }}</span></button>
                            </form>
                            <p class="inv-secure"><i class="fas fa-shield-halved"></i> {{ t($keys . '.securePayment') }} · {{ $gatewayName }}</p>
                        @elseif($isPaid)
                            <div class="inv-paid"><i class="fas fa-circle-check"></i> {{ t('hotelinvoice.paymentCompleted') }}</div>
                        @elseif($isCancelled)
                            <div class="inv-paid is-muted"><i class="fas fa-ban"></i> {{ t('hotelinvoice.payUnavailable') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Policies + what the package includes -->
    <div class="inv-wide">
        <section class="inv-card">
            <h2 class="inv-card-title"><i class="fas fa-shield-halved"></i> {{ t($keys . ($keys === 'umrahinvoice' ? '.umrahPolicies' : '.tourPolicies')) }}</h2>
            <div class="inv-policies">
                @foreach($policies as [$icon, $title, $lines])
                    <div class="inv-policy">
                        <i class="{{ $icon }}"></i>
                        <div>
                            <b>{{ $title }}</b>
                            @foreach($lines as $line)<p dir="auto">{{ $line }}</p>@endforeach
                        </div>
                    </div>
                @endforeach
                @if($inclusions)
                    <div class="inv-policy">
                        <i class="fas fa-circle-check"></i>
                        <div><b>{{ t($keys . '.inclusions') }}</b><p dir="auto">{{ implode(', ', $inclusions) }}</p></div>
                    </div>
                @endif
                @if($exclusions)
                    <div class="inv-policy">
                        <i class="fas fa-circle-minus"></i>
                        <div><b>{{ t($keys . '.exclusions') }}</b><p dir="auto">{{ implode(', ', $exclusions) }}</p></div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <footer class="inv-foot">
        <div><b>{{ $businessName }}</b>@if($agencyAddress) · {{ $agencyAddress }}@endif · {{ t('hotelinvoice.generated') }}</div>
        <div>
            @if($contactEmail)<span><i class="far fa-envelope"></i> {{ $contactEmail }}</span>@endif
            @if($contactPhone)<span><i class="fas fa-phone"></i> <span dir="ltr">{{ $contactPhone }}</span></span>@endif
        </div>
    </footer>
</main>

@include('common.invoice-script', ['invoiceRef' => $booking->booking_code_ref])
</body>
</html>
