@extends('admin-nova.layouts.app')

@section('title', 'Bookings')

@section('content')
@php
    $typeLabel = ($bookingType ?? 'all') === 'all' ? null : ucfirst($bookingType);
    $listTitle = match (true) {
        request('payment_state') === 'refunded' => 'Refunded bookings',
        request('status') === 'confirmed' => 'Confirmed bookings',
        request('status') === 'pending' => 'Pending bookings',
        request('status') === 'cancelled' => 'Cancelled bookings',
        default => 'All bookings',
    };
@endphp
<div class="tbp-bookings-page" id="bkPage">
    @include('admin-nova.bookings._header', [
        'title' => $typeLabel ? $typeLabel . ' Bookings' : 'Bookings Management',
        'subtitle' => 'Track and manage ' . ($typeLabel ? strtolower($typeLabel) . ' bookings' : 'all bookings across your platform'),
        'formRoute' => 'admin.bookings.all',
        'fullFilters' => true,
    ])
    <section class="tbp-bookings-card">
        @include('admin-nova.bookings._list', ['listTitle' => $listTitle])
    </section>
</div>
@endsection

@push('scripts')
    @include('admin-nova.bookings._scripts')
@endpush
