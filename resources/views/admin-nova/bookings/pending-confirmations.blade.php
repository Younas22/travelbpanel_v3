@extends('admin-nova.layouts.app')

@section('title', 'Pending Confirmations')

@section('content')
<div class="tbp-bookings-page" id="bkPage">
    @include('admin-nova.bookings._header', [
        'title' => 'Pending Confirmations',
        'subtitle' => 'Bookings awaiting confirmation across every module',
        'formRoute' => 'admin.bookings.pending-confirmations',
    ])
    <section class="tbp-bookings-card">
        @include('admin-nova.bookings._list', ['listTitle' => 'Pending bookings'])
    </section>
</div>
@endsection

@push('scripts')
    @include('admin-nova.bookings._scripts')
@endpush
