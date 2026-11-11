@extends('common.layout')
@section('page_class', 'view-tour-checkout')
@section('content')
@php
    // Hotel checkout design (common/package-checkout). Prices are the ones
    // storeBooking() charges ($tour_data, already in the active currency).
    $cur = $tour_data['currency'];
    $adultPrice = $tour_data['price'];
    $childPrice = $tour_data['child_price'];
    $adults = (int) $searchParams['adult'];
    $children = (int) $searchParams['child'];
    $totalPrice = ($adultPrice * $adults) + ($childPrice * $children);
    $money = fn($v) => $cur . ' ' . number_format($v, 2);

    $rows = [];
    if ($tour->duration) {
        $rows[] = ['fas fa-clock', t('tourbooking.duration'), $tour->duration];
    }
    $rows[] = ['far fa-calendar', t('tourbooking.departure_date'), $searchParams['original_start_date']];
    $rows[] = ['far fa-calendar', t('tourbooking.return_date'), $searchParams['original_end_date']];
    $rows[] = ['fas fa-user', t('tourbooking.adults'), $adults];
    if ($children) {
        $rows[] = ['fas fa-child', t('tourbooking.children'), $children];
    }

    $prices = [[t('tourbooking.adults') . ' (' . $adults . ' × ' . $money($adultPrice) . ')', $money($adultPrice * $adults)]];
    if ($children) {
        $prices[] = [t('tourbooking.children') . ' (' . $children . ' × ' . $money($childPrice) . ')', $money($childPrice * $children)];
    }
@endphp

@include('common.package-checkout', [
    'action' => route('tour.booking.store'),
    'hidden' => [
        'tour_data' => encrypt(json_encode($tour_data)),
        'search_params' => encrypt(json_encode($searchParams)),
    ],
    'groups' => [
        ['count' => $adults, 'p' => 'adult_', 'type_field' => 'traveller_type_', 'type_value' => 'adults', 'label' => t('tourbooking.adult')],
        ['count' => $children, 'p' => 'child_', 'type_field' => 'traveller_child_', 'type_value' => 'child', 'label' => t('tourbooking.child')],
    ],
    'summary' => [
        'image' => $tour->images && $tour->images->first() ? asset('public/assets/images/' . $tour->images->first()->image) : null,
        'icon' => 'fa-mountain-sun',
        'badge' => $tour->packageType->packege_type ?? null,
        'name' => $tour->name,
        'place' => $tour->location_name ?? null,
        'stars' => (int) $tour->stars,
        'rows' => $rows,
        'prices' => $prices,
        'total' => $money($totalPrice),
    ],
])
@endsection
