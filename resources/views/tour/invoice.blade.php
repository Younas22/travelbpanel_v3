@php
    // Tour booking confirmation in the hotel invoice design (common/package-invoice)
    $guests = json_decode((string) $booking->booking_guest, true) ?: [];
    $cur = $booking->booking_currency_origin;
    $money = fn($v) => $cur . ' ' . number_format((float) $v, 2);
    $date = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '—';
    $adults = (int) $booking->booking_adult_count;
    $children = (int) $booking->booking_child_count;
    $gender = fn($g) => $g ? (t('hotelbooking.' . $g) !== 'hotelbooking.' . $g ? t('hotelbooking.' . $g) : ucfirst($g)) : null;
    $duration = $booking->tour_duration ?: ($booking->tour_days ? $booking->tour_days . ' ' . t('tourinvoice.days') : null);

    $trip = [
        ['fas fa-plane-departure', t('tourinvoice.departureDate'), $date($booking->departure_date), null],
        ['fas fa-plane-arrival', t('tourinvoice.returnDate'), $date($booking->return_date), null],
    ];
    if ($duration) {
        $trip[] = ['fas fa-clock', t('tourinvoice.duration'), $duration, null];
    }
    $trip[] = ['fas fa-user-group', t('hotelinvoice.guests'), $adults + $children,
        $adults . ' ' . t('tourinvoice.adults') . ($children ? ', ' . $children . ' ' . t('tourinvoice.children') : '')];

    $rows = [
        ['fas fa-plane-departure', t('tourinvoice.departure'), $date($booking->departure_date)],
        ['fas fa-plane-arrival', t('tourinvoice.return'), $date($booking->return_date)],
    ];
    if ($duration) {
        $rows[] = ['fas fa-clock', t('tourinvoice.duration'), $duration];
    }
    $rows[] = ['fas fa-user-group', t('hotelinvoice.guests'), $adults + $children];

    $prices = [[t('tourinvoice.adults') . ' (' . $adults . ' × ' . $money($booking->booking_adult_price) . ')', $money($adults * $booking->booking_adult_price)]];
    if ($children) {
        $prices[] = [t('tourinvoice.children') . ' (' . $children . ' × ' . $money($booking->booking_child_price) . ')', $money($children * $booking->booking_child_price)];
    }

    $gateway = strtolower((string) $booking->booking_payment_gateway);
@endphp
@include('common.package-invoice', [
    'keys' => 'tourinvoice',
    'emailUrl' => route('tour.invoice.email', $booking->booking_code_ref),
    'payUrl' => $gateway ? route('payment.tour', ['gateway_name' => $gateway, 'booking_ref' => $booking->booking_code_ref]) : '#',
    'trip' => $trip,
    'travellers' => array_map(fn($g) => [
        ($g['type'] ?? '') === 'child' ? t('tourinvoice.child') : t('tourinvoice.adult'),
        trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? '')),
        $gender($g['gender'] ?? null),
        ($g['type'] ?? 'adult') !== 'child',
    ], $guests),
    'summary' => [
        'image' => $tour && $tour->images->first() ? asset('public/assets/images/' . $tour->images->first()->image) : null,
        'icon' => 'fa-mountain-sun',
        'badge' => $booking->tour_type,
        'name' => $booking->tour_name,
        'place' => $booking->tour_location,
        'rows' => $rows,
        'prices' => $prices,
        'total' => $money($booking->booking_total_price),
    ],
    'policies' => [
        ['far fa-calendar', t('tourinvoice.departure') . ' / ' . t('tourinvoice.return'), [$date($booking->departure_date) . ' – ' . $date($booking->return_date)]],
        ['far fa-calendar-xmark', t('tourinvoice.cancellation'), [t('tourinvoice.cancellationPolicy')]],
        ['far fa-credit-card', t('tourinvoice.payment'), [t('tourinvoice.paymentPolicy')]],
    ],
])
