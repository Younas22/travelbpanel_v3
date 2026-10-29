@extends('admin-nova.layouts.app')

@section('title', 'Cancelled & Refunds')

@section('content')
<div class="tbp-bookings-page" id="bkPage">
    @include('admin-nova.bookings._header', [
        'title' => 'Cancelled & Refunded Bookings',
        'subtitle' => 'Track cancelled bookings and refunds across every module',
        'formRoute' => 'admin.bookings.cancelled-refunds',
    ])
    <section class="tbp-bookings-card">
        @include('admin-nova.bookings._list', ['listTitle' => 'Cancelled & refunded bookings'])
    </section>
</div>
@endsection

@push('scripts')
    @include('admin-nova.bookings._scripts')
@endpush
