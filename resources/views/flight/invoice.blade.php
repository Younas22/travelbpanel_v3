@php
    // The booked flight as saved at checkout (stored JSON-encoded, sometimes twice)
    $segmentData = $booking->booking_flight_segment;
    while (is_string($segmentData)) {
        $segmentData = json_decode($segmentData, true);
    }
    $legs = array_values(array_filter((array) ($segmentData['segments'] ?? []), 'is_array'));
    $first = $legs[0][0] ?? [];
    $outLast = $legs ? end($legs[0]) : [];
    $isRound = count($legs) > 1;

    $guestData = $booking->booking_guest;
    while (is_string($guestData)) {
        $guestData = json_decode($guestData, true);
    }
    $guestData = is_array($guestData) ? array_values($guestData) : [];
    $responseError = $booking->booking_response_error;
    if (is_string($responseError)) {
        $responseError = json_decode($responseError, true) ?? $responseError;
    }

    $status = $booking->booking_status_flag ?: 'pending';
    $payState = $booking->booking_payment_state ?: 'unpaid';
    $gateway = strtolower((string) $booking->booking_payment_gateway);
    $isPaid = $payState === 'paid';
    $isCancelled = $status === 'cancelled';
    $isConfirmed = $status === 'confirmed';

    // When an agent made this booking, the invoice is branded with the
    // agent's own agency (company name, logo, phone, address) instead of the
    // platform's — this invoice represents the agent's business to their client.
    $bookingAgent = $booking->booked_via === 'agent' ? $booking->agent : null;
    $logo = ($bookingAgent && $bookingAgent->company_logo)
        ? url('public/assets/images/settings/branding/' . $bookingAgent->company_logo)
        : getSettingImage('business_logo', 'branding');
    $businessName = ($bookingAgent && $bookingAgent->company_name) ? $bookingAgent->company_name : getSetting('business_name', 'main', 'Travel Booking Panel');
    $contactPhone = ($bookingAgent && $bookingAgent->company_phone) ? $bookingAgent->company_phone : getSetting('contact_phone', 'contact', '');
    $contactEmail = $bookingAgent ? $bookingAgent->email : getSetting('contact_email', 'contact', '');
    $agencyAddress = $bookingAgent ? $bookingAgent->company_address : null;

    // Price shown in the site's active currency (admin/currencies rates)
    $__activeCurrency = activeCurrency();
    $fareCurrency = $first['currency'] ?? ($booking->booking_currency_origin ?: 'USD');
    $farePrice = (float) str_replace(',', '', $first['price'] ?? $booking->booking_fare_base ?? 0);
    $currency = $__activeCurrency->currency_name ?? $fareCurrency;
    $fare = $__activeCurrency ? convertCurrency($farePrice, $fareCurrency, $currency) : $farePrice;
    $money = fn($v) => $currency . ' ' . number_format((float) $v, 2);

    $logoUrl = fn($code) => 'https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/' . $code . '.svg';
    $bagText = fn($raw) => ($raw === null || $raw === '') ? '—' : (is_numeric($raw) ? (int) $raw . ' PC' : (string) $raw);
    $pnr = $booking->booking_air_pnr;
    $adultCount = (int) $booking->booking_adult_count;
    $childCount = (int) $booking->booking_child_count;
    $infantCount = (int) $booking->booking_infant_count;
    $travellerCount = count($guestData) ?: $adultCount + $childCount + $infantCount;

    // Header message follows the real state, never "confirmed" before it is
    [$headState, $headIcon, $headMsg] = match (true) {
        $isCancelled => ['is-cancelled', 'fa-circle-xmark', t('hotelinvoice.msgCancelled')],
        $isConfirmed => ['is-confirmed', 'fa-check', t('flightinvoice.msgConfirmed')],
        $isPaid => ['is-pending', 'fa-check', t('flightinvoice.msgPaidPending')],
        $gateway === 'after_pay' => ['is-pending', 'fa-check', t('flightinvoice.msgPayLater')],
        default => ['is-pending', 'fa-check', t('flightinvoice.msgCreated')],
    };

    $steps = [
        ['icon' => 'fa-plane', 'label' => t('flightinvoice.stepFlight'), 'state' => 'is-done'],
        ['icon' => 'fa-users', 'label' => t('flightinvoice.stepTravellers'), 'state' => count($guestData) ? 'is-done' : 'is-current'],
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

    // traveller rows: type, title, dates as "02 Oct 2026"
    $guestType = fn($type) => match ($type) {
        'children', 'child' => t('flightinvoice.child'),
        'infant' => t('flightinvoice.infant'),
        default => t('flightinvoice.adult'),
    };
    $title = fn($g) => match ($g) { 'm' => t('flightinvoice.mr'), 'f' => t('flightinvoice.miss'), default => t('flightinvoice.mx') };
    $date = function ($d, $m, $y) {
        if (!$d || !$m || !$y || !checkdate((int) $m, (int) $d, (int) $y)) {
            return '—';
        }
        return \Carbon\Carbon::create((int) $y, (int) $m, (int) $d)->format('d M Y');
    };

    $isRtl = app()->getLocale() === 'ar';
    $siteCss = $isRtl ? 'rtlstyle.css' : 'style.css';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('flightinvoice.title') }} #INV-{{ $booking->booking_code_ref }}</title>
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
            <button type="button" class="inv-btn" id="invPrint"><i class="fas fa-print"></i> <span>{{ t('flightinvoice.print') }}</span></button>
            <button type="button" class="inv-btn" id="invPdf"><i class="fas fa-download"></i> <span>{{ t('flightinvoice.downloadPdf') }}</span></button>
            <button type="button" class="inv-btn is-primary" id="invEmail" data-url="{{ route('flight.send-invoice-email', $booking->booking_code_ref) }}"><i class="fas fa-envelope"></i> <span>{{ t('flightinvoice.sendEmail') }}</span></button>
        </div>
    </div>
    <div class="inv-toast" id="invToast" role="status" aria-live="polite" hidden></div>
</header>

<main class="inv-page" id="invDoc">
    <!-- Document logo (print / PDF only) -->
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
        @if($pnr)
            <div class="inv-status-item">
                <span class="inv-status-icon"><i class="fas fa-plane"></i></span>
                <div><small>{{ t('flightinvoice.bookingpnr') }}</small><b dir="ltr">{{ $pnr }}</b></div>
            </div>
        @endif
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="far fa-calendar"></i></span>
            <div><small>{{ t('hotelinvoice.invoiceDate') }}</small><b dir="ltr">{{ $booking->created_at->format('d M Y') }}</b></div>
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

    @if(!empty($responseError) && !$isConfirmed)
        <div class="inv-alert"><i class="fas fa-triangle-exclamation"></i>
            <span>{{ is_array($responseError) ? trim(($responseError[0]['title'] ?? '') . ' ' . ($responseError[0]['detail'] ?? '')) ?: json_encode($responseError) : $responseError }}</span>
        </div>
    @endif

    <div class="inv-layout">
        <div class="inv-main">
            <!-- Trip at a glance -->
            <section class="inv-card">
                <h2 class="inv-card-title"><i class="fas fa-plane-departure"></i> {{ t('flightinvoice.tripDetails') }}</h2>
                <div class="inv-stay is-flight">
                    <div class="inv-stay-item"><i class="fas fa-plane-departure"></i><small>{{ t('flightinvoice.departure') }}</small><b dir="ltr">{{ $first['departure']['date_convert'] ?? '—' }}</b><em dir="ltr">{{ $first['departure']['time'] ?? '' }} {{ $first['departure']['airport'] ?? '' }}</em></div>
                    @if($isRound && isset($legs[1][0]))
                        <div class="inv-stay-item"><i class="fas fa-plane-arrival"></i><small>{{ t('flightinvoice.return') }}</small><b dir="ltr">{{ $legs[1][0]['departure']['date_convert'] ?? '—' }}</b><em dir="ltr">{{ $legs[1][0]['departure']['time'] ?? '' }} {{ $legs[1][0]['departure']['airport'] ?? '' }}</em></div>
                    @endif
                    <div class="inv-stay-item"><i class="fas fa-user-group"></i><small>{{ t('flightinvoice.passengers') }}</small><b>{{ $travellerCount }}</b>
                        <em>{{ $adultCount }} {{ t('hotelinvoice.adultsLabel') }}@if($childCount), {{ $childCount }} {{ t('hotelinvoice.childrenLabel') }}@endif @if($infantCount), {{ $infantCount }} {{ t('flightinvoice.infant') }}@endif</em></div>
                    @if(!empty($first['class']))
                        <div class="inv-stay-item"><i class="fas fa-couch"></i><small>{{ t('flightbooking.cabin_class') }}</small><b>{{ ucwords(str_replace('_', ' ', $first['class'])) }}</b></div>
                    @endif
                    <div class="inv-stay-item"><i class="fas fa-suitcase-rolling"></i><small>{{ t('flightbooking.baggage') }}</small><b>{{ $bagText($first['baggage'] ?? null) }}</b><em>{{ t('flightlist.cabin_bag') }} {{ $bagText($first['cabin_baggage'] ?? null) }}</em></div>
                </div>
            </section>

            <!-- The booked flight, segment by segment -->
            @if($legs)
                <section class="inv-card">
                    <h2 class="inv-card-title"><i class="fas fa-route"></i> {{ t('flightinvoice.flightDetails') }}</h2>
                    @include('flight.itinerary', ['legs' => $legs])
                </section>
            @endif

            <!-- Travellers -->
            <section class="inv-card">
                <h2 class="inv-card-title"><i class="fas fa-users"></i> {{ t('flightinvoice.travellersInfo') }}</h2>
                @if(count($guestData))
                    <table class="inv-table">
                        <thead>
                            <tr>
                                <th>{{ t('flightinvoice.no') }}</th>
                                <th>{{ t('flightinvoice.name') }}</th>
                                <th>{{ t('flightinvoice.dateOfBirth') }}</th>
                                <th>{{ t('flightinvoice.passportNo') }}</th>
                                <th>{{ t('flightinvoice.passportExpiry') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($guestData as $index => $guest)
                                @php $type = $guest['traveller_type'] ?? 'adults'; @endphp
                                <tr>
                                    <td data-label="{{ t('flightinvoice.no') }}">{{ $index + 1 }}</td>
                                    <td data-label="{{ t('flightinvoice.name') }}" dir="auto">
                                        <span><b>{{ $title($guest['gender'] ?? '') }} {{ trim(($guest['first_name'] ?? '') . ' ' . ($guest['last_name'] ?? '')) ?: '—' }}</b>
                                        <span class="inv-chip {{ $type === 'adults' ? '' : 'is-muted' }}">{{ $guestType($type) }}</span></span>
                                    </td>
                                    <td data-label="{{ t('flightinvoice.dateOfBirth') }}" dir="ltr">{{ $date($guest['dob_day'] ?? null, $guest['dob_month'] ?? null, $guest['dob_year'] ?? null) }}</td>
                                    <td data-label="{{ t('flightinvoice.passportNo') }}" dir="ltr">
                                        <span>{{ $guest['passport'] ?? '' ?: '—' }}
                                        @if(!empty($guest['nationality']))<small>{{ t('flightinvoice.nationality') }}: {{ strtoupper($guest['nationality']) }}</small>@endif</span>
                                    </td>
                                    <td data-label="{{ t('flightinvoice.passportExpiry') }}" dir="ltr">{{ $date($guest['passport_day_expiry'] ?? null, $guest['passport_month_expiry'] ?? null, $guest['passport_year_expiry'] ?? null) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="inv-empty">—</p>
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
                            @if($booking->booking_txn_id)<small>{{ t('hotelinvoice.transactionId') }}: <span dir="ltr">{{ $booking->booking_txn_id }}</span></small>@endif
                        </div>
                        <span class="inv-badge {{ $badge($payState) }}">{{ $isPaid ? t('hotelinvoice.paymentReceived') : ($payState === 'refunded' ? t('hotelinvoice.refunded') : t('hotelinvoice.awaitingPayment')) }}</span>
                    </div>
                </section>
            @endif

            <!-- Important information -->
            <section class="inv-card inv-notes">
                <h2 class="inv-card-title"><i class="fas fa-circle-info"></i> {{ t('flightinvoice.importantInfo') }}</h2>
                <ul>
                    @foreach(['info1', 'info2', 'info3', 'info4'] as $info)
                        <li>{{ t('flightinvoice.' . $info) }}</li>
                    @endforeach
                </ul>
            </section>
        </div>

        <!-- Booking summary -->
        <aside class="inv-side">
            <div class="inv-summary">
                <div class="inv-summary-img is-logo">
                    <i class="fas fa-plane"></i>
                    @if(!empty($first['carrier']))<img src="{{ $logoUrl($first['carrier']['operating'] ?? $first['carrier']['marketing'] ?? '') }}" alt="" onerror="this.remove()">@endif
                </div>
                <div class="inv-summary-body">
                    <h2 class="inv-card-title"><i class="fas fa-bookmark"></i> {{ t('hotelinvoice.bookingSummary') }}</h2>
                    <div class="inv-summary-hotel">
                        <h3><bdi>{{ $first['departure']['city_name'] ?? ($first['departure']['airport'] ?? '') }}</bdi> <i class="fas {{ $isRound ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right' }} fb-sum-arrow"></i> <bdi>{{ $outLast['arrival']['city_name'] ?? ($outLast['arrival']['airport'] ?? '') }}</bdi></h3>
                        <div class="inv-summary-meta">
                            <span class="inv-chip">{{ $isRound ? t('flightbooking.round_trip') : t('flightbooking.one_way') }}</span>
                            @if(!empty($first['class']))<span class="inv-chip is-muted">{{ ucwords(str_replace('_', ' ', $first['class'])) }}</span>@endif
                        </div>
                        @if(!empty($first['airline_name']))<p dir="auto"><i class="fas fa-plane"></i> {{ $first['airline_name'] }}</p>@endif
                    </div>

                    <div class="inv-rows">
                        @if($pnr)
                            <div class="inv-row"><span><i class="fas fa-plane"></i> {{ t('flightinvoice.bookingpnr') }}</span><b dir="ltr">{{ $pnr }}</b></div>
                        @endif
                        <div class="inv-row"><span><i class="fas fa-hashtag"></i> {{ t('flightinvoice.bookingRef') }}</span><b dir="ltr">{{ $booking->booking_code_ref }}</b></div>
                        <div class="inv-row"><span><i class="fas fa-plane-departure"></i> {{ t('flightinvoice.departure') }}</span><b dir="ltr">{{ $first['departure']['date_convert'] ?? '—' }}</b></div>
                        @if($isRound && isset($legs[1][0]))
                            <div class="inv-row"><span><i class="fas fa-plane-arrival"></i> {{ t('flightinvoice.return') }}</span><b dir="ltr">{{ $legs[1][0]['departure']['date_convert'] ?? '—' }}</b></div>
                        @endif
                        <div class="inv-row"><span><i class="fas fa-user-group"></i> {{ t('flightinvoice.passengers') }}</span><b>{{ $travellerCount }}</b></div>
                    </div>

                    <div class="inv-rows is-price">
                        <div class="inv-row"><span>{{ t('flightinvoice.flightCharges') }}</span><b>{{ $money($fare) }}</b></div>
                        <div class="inv-row is-sub"><span>{{ t('flightinvoice.taxesFees') }} <em>({{ t('hoteldetails.included_in_price') }})</em></span><b>{{ $money(0) }}</b></div>
                    </div>

                    <div class="inv-total">
                        <span>{{ t('hotelinvoice.totalAmount') }}</span>
                        <b>{{ $money($fare) }}</b>
                    </div>

                    <!-- Pay Now only while the booking can still be paid through its gateway -->
                    <div class="inv-pay" data-html2canvas-ignore>
                        @if($canPay)
                            <form method="get" action="{{ route('payment', ['gateway_name' => $gateway, 'booking_ref' => $booking->booking_code_ref]) }}" id="invPayForm">
                                <button type="submit" class="inv-pay-btn"><i class="fas fa-lock"></i> <span>{{ t('flightinvoice.payNow') }}</span></button>
                            </form>
                            <p class="inv-secure"><i class="fas fa-shield-halved"></i> {{ t('flightinvoice.securePayment') }} · {{ $gatewayName }}</p>
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

    <!-- Document footer strip -->
    <footer class="inv-foot">
        <div><b>{{ $businessName }}</b>@if($agencyAddress) · {{ $agencyAddress }}@endif · {{ t('hotelinvoice.generated') }}</div>
        <div>
            @if($contactEmail)<span><i class="far fa-envelope"></i> {{ $contactEmail }}</span>@endif
            @if($contactPhone)<span><i class="fas fa-phone"></i> <span dir="ltr">{{ $contactPhone }}</span></span>@endif
        </div>
    </footer>
</main>

<script>
(function () {
    const toast = document.getElementById('invToast');
    let toastTimer;
    function notify(message, isError) {
        toast.textContent = message;
        toast.classList.toggle('is-error', !!isError);
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.hidden = true, 5000);
    }
    function busy(btn, on, label) {
        const text = btn.querySelector('span');
        if (on) { btn.dataset.label = text.textContent; text.textContent = label; }
        else if (btn.dataset.label) { text.textContent = btn.dataset.label; }
        btn.disabled = on;
        btn.classList.toggle('is-loading', on);
    }

    // A4 sheet = 794 x 1123 px. Slightly taller content is scaled to fit one
    // page (not below 80%); longer content flows onto more A4 pages.
    const A4_H = 1123, MIN_FIT = 0.8;
    function sheetFit(height) {
        const fit = (A4_H - 8) / height;
        return fit >= 1 || fit < MIN_FIT ? 1 : fit;
    }

    // Print: measure the sheet as printed (logo shown, Pay Now hidden)
    window.addEventListener('beforeprint', () => {
        document.body.classList.add('is-pdf', 'is-measure');
        const height = document.getElementById('invDoc').offsetHeight;
        document.body.classList.remove('is-pdf', 'is-measure');
        document.documentElement.style.setProperty('--inv-fit', sheetFit(height));
    });
    window.addEventListener('afterprint', () => document.documentElement.style.removeProperty('--inv-fit'));
    document.getElementById('invPrint').addEventListener('click', () => window.print());

    // PDF: capture the sheet (action bar and Pay Now are skipped) and place it
    // on A4 pages. The libraries are only downloaded when someone asks for a PDF.
    const pdfBtn = document.getElementById('invPdf');
    function loadScript(src, ready) {
        if (ready()) return Promise.resolve();
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }
    async function buildPdf() {
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', () => window.html2canvas);
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', () => window.jspdf);
        const canvas = await html2canvas(document.getElementById('invDoc'), { scale: 2, useCORS: true, backgroundColor: '#FFFFFF', windowWidth: 1100 });
        const pdf = new window.jspdf.jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });
        const pageW = 210, pageH = 297;
        const imgH = canvas.height * pageW / canvas.width;
        const fit = sheetFit(imgH * A4_H / pageH);
        if (imgH * fit <= pageH + 0.5) {
            const w = pageW * fit;
            pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', (pageW - w) / 2, 0, w, Math.min(imgH * fit, pageH));
        } else {
            const slicePx = Math.floor(canvas.width * pageH / pageW);
            for (let y = 0, page = 0; y < canvas.height; y += slicePx, page++) {
                const part = document.createElement('canvas');
                part.width = canvas.width;
                part.height = Math.min(slicePx, canvas.height - y);
                part.getContext('2d').drawImage(canvas, 0, -y);
                if (page) pdf.addPage();
                pdf.addImage(part.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, pageW, part.height * pageW / canvas.width);
            }
        }
        return pdf;
    }
    pdfBtn.addEventListener('click', async () => {
        if (pdfBtn.disabled) return;
        busy(pdfBtn, true, @json(t('hotelinvoice.preparingPdf')));
        document.body.classList.add('is-pdf');
        try {
            (await buildPdf()).save('invoice-{{ $booking->booking_code_ref }}.pdf');
        } catch (e) {
            notify(@json(t('hotelinvoice.pdfFailed')), true);
        } finally {
            document.body.classList.remove('is-pdf');
            busy(pdfBtn, false);
        }
    });
    window.invBuildPdf = buildPdf;

    // Email the invoice link to the booking contact (once at a time)
    const emailBtn = document.getElementById('invEmail');
    emailBtn.addEventListener('click', async () => {
        if (emailBtn.disabled) return;
        busy(emailBtn, true, @json(t('hotelinvoice.sending')));
        try {
            const res = await fetch(emailBtn.dataset.url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                credentials: 'same-origin',
            });
            const json = await res.json().catch(() => ({}));
            notify(json.message || @json(t('hotelinvoice.emailFailed')), !res.ok || !json.success);
        } catch (e) {
            notify(@json(t('hotelinvoice.emailFailed')), true);
        } finally {
            busy(emailBtn, false);
        }
    });

    // Pay Now: one submission only
    const payForm = document.getElementById('invPayForm');
    if (payForm) payForm.addEventListener('submit', (e) => {
        const btn = payForm.querySelector('button');
        if (btn.disabled) { e.preventDefault(); return; }
        busy(btn, true, @json(t('hotelinvoice.processing')));
    });
    window.addEventListener('pageshow', (e) => {
        if (e.persisted && payForm) busy(payForm.querySelector('button'), false);
    });
})();
</script>
</body>
</html>
