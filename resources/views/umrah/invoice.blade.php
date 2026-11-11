@php
    // Umrah booking confirmation in the hotel invoice design (common/package-invoice)
    $guests = json_decode((string) $booking->booking_guest, true) ?: [];
    $search = json_decode((string) $booking->search_params, true) ?: [];
    $cur = $booking->booking_currency_origin;
    $money = fn($v) => $cur . ' ' . number_format((float) $v, 2);
    $date = function ($d) {
        try {
            return $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '—';
        } catch (\Throwable $e) {
            return $d;
        }
    };
    $adults = (int) $booking->booking_adult_count;
    $children = (int) $booking->booking_child_count;
    $infants = (int) $booking->infant_count;
    $gender = fn($g) => $g ? (t('hotelbooking.' . $g) !== 'hotelbooking.' . $g ? t('hotelbooking.' . $g) : ucfirst($g)) : null;
    $route = !empty($search['origin']) ? strtoupper($search['origin']) . ' → ' . strtoupper($search['destination'] ?? '') : null;
    $makkah = $package->night_in_mekkah ?? ($search['makkah_nights'] ?? null);
    $madina = $package->night_in_madina ?? ($search['madina_nights'] ?? null);
    $people = $adults . ' ' . t('umrahinvoice.adults') . ($children ? ', ' . $children . ' ' . t('umrahinvoice.children') : '') . ($infants ? ', ' . $infants . ' ' . t('umrahinvoice.infants') : '');

    $trip = [
        ['fas fa-plane-departure', t('umrahinvoice.departureDate'), $date($search['departure_date'] ?? null), $route],
        ['fas fa-plane-arrival', t('umrahinvoice.returnDate'), $date($search['return_date'] ?? null), null],
        ['fas fa-kaaba', t('umrahinvoice.makkah'), ($makkah ?? 0) . ' ' . t('umrahinvoice.nights'), null],
        ['fas fa-mosque', t('umrahinvoice.madina'), ($madina ?? 0) . ' ' . t('umrahinvoice.nights'), null],
        ['fas fa-user-group', t('umrahinvoice.passengers'), $adults + $children + $infants, $people],
    ];

    $rows = [
        ['fas fa-plane-departure', t('umrahinvoice.departure'), $date($search['departure_date'] ?? null)],
        ['fas fa-plane-arrival', t('umrahinvoice.return'), $date($search['return_date'] ?? null)],
    ];
    if ($package && $package->class) {
        $rows[] = ['fas fa-plane', t('umrahinvoice.flightClass'), $package->class];
    }
    $rows[] = ['fas fa-user-group', t('umrahinvoice.passengers'), $adults + $children + $infants];

    // stored line totals (adult_price = adults × price, …)
    $prices = [[t('umrahinvoice.adults') . ' (' . $adults . ' × ' . $money($booking->adult_price / max($adults, 1)) . ')', $money($booking->adult_price)]];
    if ($children) {
        $prices[] = [t('umrahinvoice.children') . ' (' . $children . ' × ' . $money($booking->child_price / $children) . ')', $money($booking->child_price)];
    }
    if ($infants) {
        $prices[] = [t('umrahinvoice.infants') . ' (' . $infants . ' × ' . $money($booking->infant_price / $infants) . ')', $money($booking->infant_price)];
    }

    $typeLabel = fn($type) => match ($type) { 'child' => t('umrahinvoice.child'), 'infant' => t('umrahinvoice.infant'), default => t('umrahinvoice.adult') };
    $gateway = strtolower((string) $booking->booking_payment_gateway);
@endphp
@include('common.package-invoice', [
    'keys' => 'umrahinvoice',
    'emailUrl' => route('umrah.invoice.email', $booking->booking_code_ref),
    'payUrl' => $gateway ? route('payment.umrah', ['gateway_name' => $gateway, 'booking_ref' => $booking->booking_code_ref]) : '#',
    'trip' => $trip,
    'travellers' => array_map(fn($g) => [
        $typeLabel($g['type'] ?? 'adult'),
        trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? '')),
        $gender($g['gender'] ?? null),
        ($g['type'] ?? 'adult') === 'adult',
    ], $guests),
    'summary' => [
        'image' => $package && $package->images->first() ? asset('public/assets/images/' . $package->images->first()->image) : null,
        'icon' => 'fa-kaaba',
        'badge' => $package && $package->packageType ? ucfirst($package->packageType->packege_type) : null,
        'name' => $booking->umrah_name,
        'place' => $route,
        'rows' => $rows,
        'prices' => $prices,
        'total' => $money($booking->total_price),
    ],
    'policies' => [
        ['far fa-calendar', t('umrahinvoice.departure') . ' / ' . t('umrahinvoice.return'), [$date($search['departure_date'] ?? null) . ' – ' . $date($search['return_date'] ?? null)]],
        ['far fa-calendar-xmark', t('umrahinvoice.cancellation'), [t('umrahinvoice.cancellationPolicy')]],
        ['far fa-id-card', t('umrahinvoice.documents'), [t('umrahinvoice.documentsRequired')]],
    ],
])
