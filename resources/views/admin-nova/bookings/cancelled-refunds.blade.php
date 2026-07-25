@extends('admin-nova.layouts.app')

@section('title', 'Cancelled & Refunds')

@push('styles')
    @include('admin-nova.bookings._styles')
@endpush

@section('content')
    <div id="bkPage" class="tt-fade-in font-jakarta">

        {{-- ============ HEADER + STATS ============ --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-lg font-bold text-novatext">Cancelled &amp; Refunded Bookings</h1>
                <p class="text-xs text-novamuted mt-1">Track cancelled bookings and refunds across every module</p>
            </div>
            <div class="flex items-center gap-5">
                <div class="text-center">
                    <p class="text-xl font-bold text-novatext">{{ number_format($stats['total'] ?? 0) }}</p>
                    <p class="text-[11px] text-novamuted mt-0.5">Total</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-novadanger">{{ number_format($stats['cancelled'] ?? 0) }}</p>
                    <p class="text-[11px] text-novamuted mt-0.5">Cancelled</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-novablue">{{ number_format($stats['refunded'] ?? 0) }}</p>
                    <p class="text-[11px] text-novamuted mt-0.5">Refunded</p>
                </div>
            </div>
        </div>

        {{-- ============ FILTERS (real GET filters, server-side) ============ --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
            <form method="GET" action="{{ route('admin.bookings.cancelled-refunds') }}">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-novamuted mb-1.5">Search bookings</label>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Booking ID, customer name, email..."
                                   class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                        </div>
                    </div>

                    <div class="w-36">
                        <label class="block text-xs font-semibold text-novamuted mb-1.5">Type</label>
                        <select name="type" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                            <option value="all" {{ ($bookingType ?? 'all') === 'all' ? 'selected' : '' }}>All types</option>
                            <option value="flight" {{ ($bookingType ?? '') === 'flight' ? 'selected' : '' }}>Flight</option>
                            <option value="hotel" {{ ($bookingType ?? '') === 'hotel' ? 'selected' : '' }}>Hotel</option>
                            <option value="tour" {{ ($bookingType ?? '') === 'tour' ? 'selected' : '' }}>Tour</option>
                            <option value="umrah" {{ ($bookingType ?? '') === 'umrah' ? 'selected' : '' }}>Umrah</option>
                        </select>
                    </div>

                    <div class="w-36">
                        <label class="block text-xs font-semibold text-novamuted mb-1.5">Date from</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                    </div>

                    <div class="w-36">
                        <label class="block text-xs font-semibold text-novamuted mb-1.5">Date to</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="tt-btn bk-filter-submit" title="Apply filters">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                        </button>
                        <a href="{{ route('admin.bookings.cancelled-refunds') }}" class="tt-btn bk-filter-reset" title="Reset filters">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- ============ LIST ============ --}}
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
            @include('admin-nova.bookings._list', ['listTitle' => 'Cancelled bookings'])
        </div>
    </div>
@endsection

@push('scripts')
    @include('admin-nova.bookings._scripts')
@endpush
