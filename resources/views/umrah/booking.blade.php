@extends('common.layout')
@section('page_class', 'view-umrah-checkout')
@section('content')
@php
    // Hotel checkout design (common/package-checkout). Prices are the ones
    // storeBooking() charges ($umrah_data, already in the active currency).
    $cur = $umrah_data['currency'];
    $adults = (int) $searchParams['adult'];
    $children = (int) $searchParams['child'];
    $infants = (int) $searchParams['infant'];
    $adultPrice = $umrah_data['price'];
    $childLinePrice = $umrah_data['child_price'];
    $infantLinePrice = $umrah_data['infant_price'];
    $totalPrice = $adultPrice * $adults + $childLinePrice * $children + $infantLinePrice * $infants;
    $money = fn($v) => $cur . ' ' . number_format($v, 2);

    $route = trim(($originAirport->city ?? $searchParams['origin']) . ' → ' . ($destinationAirport->city ?? $searchParams['destination']));
    $rows = [];
    if ($package->duration) {
        $rows[] = ['fas fa-clock', t('umrahbooking.duration'), $package->duration];
    }
    $rows[] = ['fas fa-kaaba', t('umrahbooking.makkah'), $package->night_in_mekkah . ' ' . t('umrahbooking.nights')];
    $rows[] = ['fas fa-mosque', t('umrahbooking.madina'), $package->night_in_madina . ' ' . t('umrahbooking.nights')];
    if ($package->class) {
        $rows[] = ['fas fa-plane', t('umrahbooking.flight_class'), $package->class];
    }
    $rows[] = ['far fa-calendar', t('umrahbooking.departure_date'), $searchParams['departure_date']];
    $rows[] = ['far fa-calendar', t('umrahbooking.return_date'), $searchParams['return_date']];
    $rows[] = ['fas fa-user-group', t('umrahbooking.travellers'), $adults + $children + $infants];

    $prices = [[t('umrahbooking.adults') . ' (' . $adults . ' × ' . $money($adultPrice) . ')', $money($adultPrice * $adults)]];
    if ($children) {
        $prices[] = [t('umrahbooking.children') . ' (' . $children . ' × ' . $money($childLinePrice) . ')', $money($childLinePrice * $children)];
    }
    if ($infants) {
        $prices[] = [t('umrahbooking.infants') . ' (' . $infants . ' × ' . $money($infantLinePrice) . ')', $money($infantLinePrice * $infants)];
    }
@endphp

@include('common.package-checkout', [
    'action' => route('umrah.booking.store'),
    'hidden' => [
        'umrah_data' => encrypt(json_encode($umrah_data)),
        'search_params' => encrypt(json_encode($searchParams)),
    ],
    'groups' => [
        ['count' => $adults, 'p' => 'adult_', 'type_field' => 'traveller_type_', 'type_value' => 'adults', 'label' => t('umrahbooking.adult')],
        ['count' => $children, 'p' => 'child_', 'type_field' => 'traveller_child_', 'type_value' => 'child', 'label' => t('umrahbooking.child')],
        ['count' => $infants, 'p' => 'infant_', 'type_field' => 'traveller_infant_', 'type_value' => 'infant', 'label' => t('umrahbooking.infant')],
    ],
    'summary' => [
        'image' => $package->images && $package->images->first() ? asset('public/assets/images/' . $package->images->first()->image) : null,
        'icon' => 'fa-kaaba',
        'badge' => $package->packageType ? ucfirst($package->packageType->packege_type) : null,
        'name' => $package->name,
        'place' => $route,
        'stars' => (int) $package->stars,
        'rows' => $rows,
        'prices' => $prices,
        'total' => $money($totalPrice),
    ],
])
@endsection
