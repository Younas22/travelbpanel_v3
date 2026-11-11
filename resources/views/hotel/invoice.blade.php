@php
    // booking_data holds the snapshot taken at checkout: room_data, room, option, booking_data
    if (is_string($booking->booking_data)) {
        $data = json_decode($booking->booking_data);
    } else {
        $data = json_decode(json_encode($booking->booking_data));
    }
    $bookingData = is_object($data->booking_data) ? $data->booking_data : (object) $data->booking_data;
    $room = $data->room ?? (object) [];
    $option = $data->option ?? (object) [];
    $roomData = $data->room_data ?? (object) [];
    $days = (new DateTime($bookingData->checkin))->diff(new DateTime($bookingData->checkout))->days;

    $guestData = [];
    if (!empty($booking->booking_guest)) {
        $guestData = is_string($booking->booking_guest) ? json_decode($booking->booking_guest, true) : $booking->booking_guest;
    }
    $guestData = is_array($guestData) ? array_values($guestData) : [];
    $adultCount = (int) ($bookingData->adults ?? 0);
    $childCount = (int) ($bookingData->child ?? 0);
    $roomCount = max(1, (int) ($roomData->rooms ?? 1));

    $status = $booking->booking_status_flag ?: 'pending';
    $payState = $booking->booking_payment_state ?: 'unpaid';
    $gateway = strtolower((string) $booking->booking_payment_gateway);
    $isPaid = $payState === 'paid';
    $isCancelled = $status === 'cancelled';
    $isConfirmed = $status === 'confirmed';

    $currency = $room->currency ?? $booking->booking_currency_origin;
    $price = (float) ($option->price ?? $room->price ?? 0);
    $money = fn($v, $c = null) => number_format((float) $v, 2) . ' ' . ($c ?? $currency);
    $taxesIncluded = json_decode(json_encode($option->taxes_included ?? []), true);
    $taxesAtProperty = json_decode(json_encode($option->taxes_at_property ?? []), true);

    $stars = max(0, min(5, (int) filter_var($bookingData->stars ?? 0, FILTER_SANITIZE_NUMBER_INT)));
    $image = ($bookingData->image ?? null) ?: ($room->images[0] ?? null);
    $category = $hotelRecord?->type ? ucwords($hotelRecord->type) : ($stars ? $stars . ' ' . t('hotelinvoice.starHotel') : t('hotelinvoice.hotel'));
    $timeOf = fn($t) => $t ? \Carbon\Carbon::parse($t)->format('g:i A') : null;
    $checkinTime = $timeOf($hotelRecord?->check_in_time);
    $checkoutTime = $timeOf($hotelRecord?->check_out_time);

    // Header message follows the real state, never "confirmed" before it is
    [$headState, $headIcon, $headMsg] = match (true) {
        $isCancelled => ['is-cancelled', 'fa-circle-xmark', t('hotelinvoice.msgCancelled')],
        $isConfirmed => ['is-confirmed', 'fa-check', t('hotelinvoice.msgConfirmed')],
        $isPaid => ['is-pending', 'fa-check', t('hotelinvoice.msgPaidPending')],
        $gateway === 'after_pay' => ['is-pending', 'fa-check', t('hotelinvoice.msgPayLater')],
        default => ['is-pending', 'fa-check', t('hotelinvoice.msgCreated')],
    };

    // Progress: booking + guests exist; payment only when the gateway marked it paid
    $steps = [
        ['icon' => 'fa-hotel', 'label' => t('hotelinvoice.stepHotel'), 'state' => 'is-done'],
        ['icon' => 'fa-users', 'label' => t('hotelinvoice.stepGuests'), 'state' => count($guestData) ? 'is-done' : 'is-current'],
        ['icon' => 'fa-credit-card', 'label' => t('hotelinvoice.stepPayment'), 'state' => $isPaid ? 'is-done' : ($isCancelled ? '' : 'is-current')],
        ['icon' => 'fa-circle-check', 'label' => t('hotelinvoice.stepConfirmation'), 'state' => $isConfirmed ? 'is-done' : ($isCancelled ? 'is-cancelled' : ($isPaid ? 'is-current' : ''))],
    ];

    $badge = fn($value) => match ($value) {
        'confirmed', 'paid' => 'is-success',
        'cancelled' => 'is-danger',
        'refunded' => 'is-info',
        default => 'is-warning',
    };
    $gatewayName = $gateway === 'agent_wallet' ? t('hotelinvoice.agentWallet') : ucwords(str_replace('_', ' ', $gateway));
    $gatewayLogo = $gateway && file_exists(public_path('assets/images/settings/payment/' . $gateway . '.png'))
        ? url('public/assets/images/settings/payment/' . $gateway . '.png') : null;
    $gatewayDesc = t('hotelinvoice.gw_' . $gateway);
    $gatewayDesc = $gatewayDesc === 'hotelinvoice.gw_' . $gateway ? null : $gatewayDesc;

    // Cancellation terms of the booked rate (from the supplier / rate calendar)
    $cancelLines = [];
    if (!empty($option->cancel_restricted)) {
        $cancelLines[] = $option->cancel_restricted_note ?? t('hotelinvoice.nonRefundable');
    } elseif (!empty($option->non_refundable)) {
        $cancelLines[] = t('hotelinvoice.nonRefundable');
    } elseif (!empty($option->refund_date)) {
        $cancelLines[] = str_replace(':date', \Carbon\Carbon::parse($option->refund_date)->format('d M Y, g:i A'), t('hotelinvoice.freeCancellationUntil'));
    }
    foreach ($option->cancellation_rules ?? [] as $rule) {
        if ((float) ($rule->cancel_charge ?? 0) > 0) {
            $cancelLines[] = str_replace(':amount', $money($rule->cancel_charge), t('hotelinvoice.cancellationCharge'));
            break;
        }
    }
    $hotelCancelPolicy = $hotelPolicies->get('cancellation')?->description;
    $hotelPaymentPolicy = $hotelPolicies->get('payment')?->description;
    $services = array_values(array_filter(array_merge(
        !empty($option->meal_included) ? [t('hotelinvoice.mealsIncluded')] : [],
        (array) ($room->amenities ?? [])
    )));

    $logo = getSettingImage('business_logo', 'branding');
    $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
    $contactEmail = getSetting('contact_email', 'contact', '');
    $contactPhone = getSetting('contact_phone', 'contact', '');
    $isRtl = app()->getLocale() === 'ar';
    $siteCss = $isRtl ? 'rtlstyle.css' : 'style.css';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('hotelinvoice.title') }} #INV-{{ $booking->booking_code_ref }}</title>
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
            <button type="button" class="inv-btn" id="invPrint"><i class="fas fa-print"></i> <span>{{ t('hotelinvoice.print') }}</span></button>
            <button type="button" class="inv-btn" id="invPdf"><i class="fas fa-download"></i> <span>{{ t('hotelinvoice.downloadPdf') }}</span></button>
            <button type="button" class="inv-btn is-primary" id="invEmail" data-url="{{ route('hotel.invoice.email', $booking->booking_code_ref) }}"><i class="fas fa-envelope"></i> <span>{{ t('hotelinvoice.sendEmail') }}</span></button>
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

    <!-- Invoice no. / date / statuses -->
    <section class="inv-status">
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="fas fa-file-invoice"></i></span>
            <div><small>{{ t('hotelinvoice.invoiceNumber') }}</small><b dir="ltr">#INV-{{ $booking->booking_code_ref }}</b></div>
        </div>
        @if($booking->booking_hotel_pnr)
            <div class="inv-status-item">
                <span class="inv-status-icon"><i class="fas fa-hotel"></i></span>
                <div><small>{{ t('hotelinvoice.hotelPnr') }}</small><b dir="ltr">{{ $booking->booking_hotel_pnr }}</b></div>
            </div>
        @endif
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="far fa-calendar"></i></span>
            <div><small>{{ t('hotelinvoice.invoiceDate') }}</small><b dir="ltr">{{ $booking->created_at->format('d M Y') }}</b></div>
        </div>
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="fas fa-clipboard-check"></i></span>
            <div><small>{{ t('hotelinvoice.bookingStatus') }}</small><b class="inv-badge {{ $badge($status) }}">{{ t('hotelinvoice.' . $status) }}</b></div>
        </div>
        <div class="inv-status-item">
            <span class="inv-status-icon"><i class="fas fa-wallet"></i></span>
            <div><small>{{ t('hotelinvoice.paymentStatus') }}</small><b class="inv-badge {{ $badge($payState) }}">{{ t('hotelinvoice.' . $payState) }}</b></div>
        </div>
    </section>

    <!-- Progress -->
    <ol class="inv-steps">
        @foreach($steps as $i => $step)
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

    @if($booking->booking_status_flag !== 'confirmed' && $isPaid && !empty($booking->booking_response_error))
        <div class="inv-alert"><i class="fas fa-triangle-exclamation"></i> <span>{{ is_string($booking->booking_response_error) ? $booking->booking_response_error : json_encode($booking->booking_response_error) }}</span></div>
    @endif

    <div class="inv-layout">
        <div class="inv-main">
            <!-- Stay details (hotel name/photo/address are in the booking summary) -->
            <section class="inv-card">
                <h2 class="inv-card-title"><i class="far fa-calendar-check"></i> {{ t('hotelinvoice.stayDetails') }}</h2>
                <div class="inv-stay">
                    <div class="inv-stay-item"><i class="far fa-calendar"></i><small>{{ t('hotelinvoice.checkIn') }}</small><b dir="ltr">{{ \Carbon\Carbon::parse($bookingData->checkin)->format('d M Y') }}</b>@if($checkinTime)<em>{{ str_replace(':time', $checkinTime, t('hotelinvoice.checkInFrom')) }}</em>@endif</div>
                    <div class="inv-stay-item"><i class="far fa-calendar"></i><small>{{ t('hotelinvoice.checkOut') }}</small><b dir="ltr">{{ \Carbon\Carbon::parse($bookingData->checkout)->format('d M Y') }}</b>@if($checkoutTime)<em>{{ str_replace(':time', $checkoutTime, t('hotelinvoice.checkOutUntil')) }}</em>@endif</div>
                    <div class="inv-stay-item"><i class="far fa-moon"></i><small>{{ t('hotelinvoice.nights') }}</small><b>{{ $days }}</b></div>
                    <div class="inv-stay-item"><i class="fas fa-user-group"></i><small>{{ t('hotelinvoice.guests') }}</small><b>{{ $adultCount }} {{ t('hotelinvoice.adultsLabel') }}@if($childCount), {{ $childCount }} {{ t('hotelinvoice.childrenLabel') }}@endif</b><em>{{ $roomCount }} {{ t('hotelinvoice.rooms') }}</em></div>
                    <div class="inv-stay-item"><i class="fas fa-bed"></i><small>{{ t('hotelinvoice.roomType') }}</small><b dir="auto">{{ $room->name ?? '' }}</b></div>
                </div>
            </section>

            <!-- Guests -->
            <section class="inv-card">
                <h2 class="inv-card-title"><i class="fas fa-users"></i> {{ t('hotelinvoice.guestInformation') }}</h2>
                @if(count($guestData))
                    <table class="inv-table">
                        <thead>
                            <tr><th>{{ t('hotelinvoice.no') }}</th><th>{{ t('hotelinvoice.guestType') }}</th><th>{{ t('hotelinvoice.name') }}</th><th>{{ t('hotelinvoice.gender') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($guestData as $index => $guest)
                                @php $isChildGuest = ($guest['traveller_type'] ?? '') === 'child'; @endphp
                                <tr>
                                    <td data-label="{{ t('hotelinvoice.no') }}">{{ $index + 1 }}</td>
                                    <td data-label="{{ t('hotelinvoice.guestType') }}"><span class="inv-chip {{ $isChildGuest ? 'is-muted' : '' }}">{{ $isChildGuest ? t('hotelinvoice.child') : t('hotelinvoice.adult') }}</span></td>
                                    <td data-label="{{ t('hotelinvoice.name') }}" dir="auto"><b>{{ trim(($guest['first_name'] ?? '') . ' ' . ($guest['last_name'] ?? '')) ?: '—' }}</b></td>
                                    <td data-label="{{ t('hotelinvoice.gender') }}">{{ !empty($guest['title']) ? (t('hotelbooking.' . strtolower($guest['title'])) !== 'hotelbooking.' . strtolower($guest['title']) ? t('hotelbooking.' . strtolower($guest['title'])) : ucfirst($guest['title'])) : '—' }}</td>
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

            <!-- Important information (existing notes) -->
            <section class="inv-card inv-notes">
                <h2 class="inv-card-title"><i class="fas fa-circle-info"></i> {{ t('hotelinvoice.importantInfo') }}</h2>
                <ul>
                    @foreach(['info1', 'info2', 'info3', 'info4', 'info5'] as $info)
                        <li>{{ t('hotelinvoice.' . $info) }}</li>
                    @endforeach
                </ul>
            </section>
        </div>

        <!-- Booking summary -->
        <aside class="inv-side">
            <div class="inv-summary">
                <div class="inv-summary-img">
                    <i class="fas fa-hotel"></i>
                    @if($image)<img src="{{ $image }}" alt="" onerror="this.remove()">@endif
                </div>
                <div class="inv-summary-body">
                    <h2 class="inv-card-title"><i class="fas fa-bookmark"></i> {{ t('hotelinvoice.bookingSummary') }}</h2>
                    <div class="inv-summary-hotel">
                        <h3 dir="auto">{{ $bookingData->hotel_name ?? '' }}</h3>
                        <div class="inv-summary-meta">
                            @if($stars)<span class="inv-stars">@for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= $stars ? 'is-on' : '' }}"></i>@endfor</span>@endif
                            <span class="inv-chip">{{ $category }}</span>
                        </div>
                        @if(!empty($bookingData->address))<p dir="auto"><i class="fas fa-location-dot"></i> {{ $bookingData->address }}</p>@endif
                    </div>

                    <div class="inv-rows">
                        @if($booking->booking_hotel_pnr)
                            <div class="inv-row"><span><i class="fas fa-hotel"></i> {{ t('hotelinvoice.hotelPnr') }}</span><b dir="ltr">{{ $booking->booking_hotel_pnr }}</b></div>
                        @endif
                        @if($booking->booking_pnr && $booking->booking_pnr !== $booking->booking_hotel_pnr)
                            <div class="inv-row"><span><i class="fas fa-hashtag"></i> {{ t('hotelinvoice.bookingPnr') }}</span><b dir="ltr">{{ $booking->booking_pnr }}</b></div>
                        @endif
                        <div class="inv-row"><span><i class="fas fa-bed"></i> {{ t('hotelinvoice.roomType') }}</span><b dir="auto">{{ $room->name ?? '' }}</b></div>
                        <div class="inv-row"><span><i class="far fa-calendar"></i> {{ t('hotelinvoice.checkIn') }}</span><b dir="ltr">{{ \Carbon\Carbon::parse($bookingData->checkin)->format('d M Y') }}</b></div>
                        <div class="inv-row"><span><i class="far fa-calendar"></i> {{ t('hotelinvoice.checkOut') }}</span><b dir="ltr">{{ \Carbon\Carbon::parse($bookingData->checkout)->format('d M Y') }}</b></div>
                        <div class="inv-row"><span><i class="far fa-moon"></i> {{ t('hotelinvoice.nights') }}</span><b>{{ $days }}</b></div>
                        <div class="inv-row"><span><i class="fas fa-user-group"></i> {{ t('hotelinvoice.guests') }}</span><b>{{ $adultCount + $childCount }}</b></div>
                    </div>

                    <div class="inv-rows is-price">
                        <div class="inv-row"><span>{{ t('hotelinvoice.roomCharges') }} ({{ $days }} {{ t('hotelinvoice.nights') }})</span><b>{{ $money($price) }}</b></div>
                        @foreach($taxesIncluded as $tax)
                            <div class="inv-row is-sub"><span>{{ ($tax['description'] ?? '') ?: ($tax['name'] ?? $tax['type'] ?? t('hotelinvoice.taxesFees')) }} <em>({{ t('hoteldetails.included_in_price') }})</em></span><b>{{ $money($tax['amount'] ?? 0, $tax['currency'] ?? null) }}</b></div>
                        @endforeach
                        @foreach($taxesAtProperty as $tax)
                            <div class="inv-row is-sub"><span>{{ ($tax['description'] ?? '') ?: ($tax['name'] ?? $tax['type'] ?? t('hotelinvoice.taxesFees')) }} <em>({{ t('hoteldetails.payable_at_property') }})</em></span><b>{{ $money($tax['amount'] ?? 0, $tax['currency'] ?? null) }}</b></div>
                        @endforeach
                    </div>

                    <div class="inv-total">
                        <span>{{ t('hotelinvoice.totalAmount') }}</span>
                        <b>{{ $money($price) }}</b>
                    </div>

                    <!-- Pay Now only while the booking can still be paid through its gateway -->
                    <div class="inv-pay" data-html2canvas-ignore>
                        @if($canPay)
                            <form method="get" action="{{ route('payment.hotel', ['gateway_name' => $gateway, 'booking_ref' => $booking->booking_code_ref]) }}" id="invPayForm">
                                <button type="submit" class="inv-pay-btn"><i class="fas fa-lock"></i> <span>{{ t('hotelinvoice.payNow') }}</span></button>
                            </form>
                            <p class="inv-secure"><i class="fas fa-shield-halved"></i> {{ t('hotelinvoice.securePayment') }} · {{ $gatewayName }}</p>
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

    <div class="inv-wide">
        <!-- Policies: only what the hotel record or the booked rate actually says -->
        <section class="inv-card">
            <h2 class="inv-card-title"><i class="fas fa-shield-halved"></i> {{ t('hotelinvoice.hotelPolicies') }}</h2>
            <div class="inv-policies">
                @if($checkinTime || $checkoutTime)
                    <div class="inv-policy">
                        <i class="far fa-clock"></i>
                        <div>
                            <b>{{ t('hotelinvoice.checkIn') }} / {{ t('hotelinvoice.checkOut') }}</b>
                            @if($checkinTime)<p>{{ str_replace(':time', $checkinTime, t('hotelinvoice.checkInFrom')) }}</p>@endif
                            @if($checkoutTime)<p>{{ str_replace(':time', $checkoutTime, t('hotelinvoice.checkOutUntil')) }}</p>@endif
                        </div>
                    </div>
                @endif
                <div class="inv-policy">
                    <i class="far fa-credit-card"></i>
                    <div>
                        <b>{{ t('hotelinvoice.payment') }}</b>
                        <p>{{ $isPaid ? str_replace(':method', $gatewayName, t('hotelinvoice.paidVia')) : ($gateway === 'after_pay' ? t('hotelinvoice.payLaterPolicy') : str_replace(':method', $gatewayName, t('hotelinvoice.paymentDueVia'))) }}</p>
                        @if($hotelPaymentPolicy)<p dir="auto">{{ $hotelPaymentPolicy }}</p>@endif
                    </div>
                </div>
                @if($cancelLines || $hotelCancelPolicy)
                    <div class="inv-policy">
                        <i class="far fa-calendar-xmark"></i>
                        <div>
                            <b>{{ t('hotelinvoice.cancellation') }}</b>
                            @foreach($cancelLines as $line)<p>{{ $line }}</p>@endforeach
                            @if($hotelCancelPolicy)<p dir="auto">{{ $hotelCancelPolicy }}</p>@endif
                        </div>
                    </div>
                @endif
                @if($services)
                    <div class="inv-policy">
                        <i class="fas fa-concierge-bell"></i>
                        <div>
                            <b>{{ t('hotelinvoice.services') }}</b>
                            <p>{{ implode(', ', $services) }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <!-- Document footer strip (this page only) -->
    <footer class="inv-foot">
        <div><b>{{ $businessName }}</b> · {{ t('hotelinvoice.generated') }}</div>
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
    const A4_W = 794, A4_H = 1123, MIN_FIT = 0.8;
    function sheetFit(height) {
        // a few px of headroom so rounding never pushes the sheet onto page 2
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
    // html2canvas misplaces icon-font glyphs, stretches object-fit images and
    // (with Tailwind's `img { display: block }`) draws text too low. So, for
    // the PDF only: each icon is drawn here as the browser lays it out and
    // given to the copy as a picture, and images become backgrounds.
    const BLANK = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    function pdfParts(sheet) {
        const icons = [];
        sheet.querySelectorAll('i').forEach((icon) => {
            const box = icon.getBoundingClientRect();
            const before = getComputedStyle(icon, '::before');
            const glyph = (before.content || '').replace(/^["']|["']$/g, '');
            if (!glyph || glyph === 'none' || !box.width || !box.height) return;
            // drawn 4x, with a 1px empty edge so scaling never bleeds the glyph across
            const k = 4, pad = 4;
            const canvas = document.createElement('canvas');
            canvas.width = Math.ceil(box.width * k) + pad * 2;
            canvas.height = Math.ceil(box.height * k) + pad * 2;
            const ctx = canvas.getContext('2d');
            ctx.font = `${before.fontStyle} ${before.fontWeight} ${parseFloat(before.fontSize) * k}px ${before.fontFamily}`;
            ctx.fillStyle = before.color;
            // same side of a wide icon box as the page (text-align / direction)
            const cs = getComputedStyle(icon);
            const align = { start: cs.direction === 'rtl' ? 'right' : 'left', end: cs.direction === 'rtl' ? 'left' : 'right' }[cs.textAlign] || cs.textAlign;
            const x = align === 'left' ? pad + parseFloat(cs.paddingLeft) * k
                : align === 'right' ? canvas.width - pad - parseFloat(cs.paddingRight) * k
                : canvas.width / 2;
            ctx.textAlign = ['left', 'right'].includes(align) ? align : 'center';
            const m = ctx.measureText(glyph);
            ctx.fillText(glyph, x, (canvas.height - m.fontBoundingBoxAscent - m.fontBoundingBoxDescent) / 2 + m.fontBoundingBoxAscent);
            icon.dataset.pdfIcon = icons.push([canvas.toDataURL(), canvas.width / k, canvas.height / k, pad / k]) - 1;
        });
        const images = [];
        sheet.querySelectorAll('img').forEach((img) => {
            const cs = getComputedStyle(img);
            if (!img.currentSrc || !['cover', 'contain'].includes(cs.objectFit)) return;
            // contain: the picture's own box inside the padding (no stretching)
            let place = null;
            if (cs.objectFit === 'contain' && img.naturalWidth && img.naturalHeight) {
                const box = img.getBoundingClientRect();
                const pl = parseFloat(cs.paddingLeft), pt = parseFloat(cs.paddingTop);
                const w = box.width - pl - parseFloat(cs.paddingRight), h = box.height - pt - parseFloat(cs.paddingBottom);
                const scale = Math.min(w / img.naturalWidth, h / img.naturalHeight);
                const fw = img.naturalWidth * scale, fh = img.naturalHeight * scale;
                place = { left: img.offsetLeft + pl + (w - fw) / 2, top: img.offsetTop + pt + (h - fh) / 2, width: fw, height: fh };
            }
            img.dataset.pdfImage = images.push([img.currentSrc, cs.objectFit, place]) - 1;
        });
        return function (doc) {
            const style = doc.createElement('style');
            style.textContent = 'i[data-pdf-icon] > * { visibility: hidden !important; }';
            doc.head.appendChild(style);
            doc.querySelectorAll('[data-pdf-icon]').forEach((icon) => {
                const [src, w, h, edge] = icons[icon.dataset.pdfIcon];
                Object.assign(icon.style, { backgroundImage: `url(${src})`, backgroundSize: `${w}px ${h}px`, backgroundPosition: `-${edge}px -${edge}px`, backgroundRepeat: 'no-repeat' });
            });
            doc.querySelectorAll('[data-pdf-image]').forEach((img) => {
                const [src, fit, place] = images[img.dataset.pdfImage];
                if (place) {
                    Object.assign(img.style, { inset: 'auto', left: place.left + 'px', top: place.top + 'px', width: place.width + 'px', height: place.height + 'px', padding: '0', objectFit: 'fill' });
                    return;
                }
                Object.assign(img.style, { backgroundImage: `url("${src}")`, backgroundSize: fit, backgroundPosition: 'center', backgroundRepeat: 'no-repeat', backgroundOrigin: 'content-box', backgroundClip: 'content-box' });
                img.src = BLANK;
            });
        };
    }
    async function buildPdf() {
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', () => window.html2canvas);
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', () => window.jspdf);
        const sheet = document.getElementById('invDoc');
        // html2canvas measures the text baseline on this page: let its test image sit inline
        const baseline = document.createElement('style');
        baseline.textContent = 'body > div[style*="hidden"] > img { display: inline !important; }';
        document.head.appendChild(baseline);
        let canvas;
        try {
            canvas = await html2canvas(sheet, { scale: 2, useCORS: true, backgroundColor: '#FFFFFF', windowWidth: 1100, onclone: pdfParts(sheet) });
        } finally {
            baseline.remove();
            sheet.querySelectorAll('[data-pdf-icon], [data-pdf-image]').forEach((el) => { delete el.dataset.pdfIcon; delete el.dataset.pdfImage; });
        }
        const pdf = new window.jspdf.jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });
        const pageW = 210, pageH = 297;
        const imgH = canvas.height * pageW / canvas.width;
        const fit = sheetFit(imgH * A4_H / pageH);
        if (imgH * fit <= pageH + 0.5) {
            // one page, scaled down a little when needed and centred
            const w = pageW * fit;
            pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', (pageW - w) / 2, 0, w, Math.min(imgH * fit, pageH));
        } else {
            // longer documents: one A4 slice per page
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
