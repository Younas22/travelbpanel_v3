@extends('user.layouts.app')
@section('title', 'Dashboard')

@section('content')

@php
    // same building blocks as the agent dashboard (.agd-*, .ap-card, .ap-table in admin.css)
    $dhUser = auth()->user();
    $dhStats = [
        ['fa-calendar-check', 'Total Bookings', $totalBookings],
        ['fa-circle-check', 'Confirmed', $recentBookings->where('status', 'confirmed')->count()],
        ['fa-clock', 'Pending', $recentBookings->where('status', 'pending')->count()],
        ['fa-circle-xmark', 'Cancelled', $recentBookings->where('status', 'cancelled')->count()],
    ];
    $dhActions = [
        [route('flights.index'), 'fa-plane', 'Book Flight', 'Search and book flights'],
        [route('tours.index'), 'fa-map-location-dot', 'Book Tour', 'Browse tour packages'],
        [route('umrah.index'), 'fa-kaaba', 'Book Umrah', 'Umrah packages'],
    ];
    $statusBadge = ['confirmed' => 'ap-badge-success', 'pending' => 'ap-badge-warning', 'cancelled' => 'ap-badge-danger'];
    $payBadge = ['paid' => 'ap-badge-success', 'unpaid' => 'ap-badge-warning', 'refunded' => 'ap-badge-blue'];
    $typeIcons = ['hotel' => 'fa-hotel', 'flight' => 'fa-plane', 'tour' => 'fa-map-location-dot', 'umrah' => 'fa-kaaba', 'visa' => 'fa-file-alt'];
@endphp

{{-- Page Header --}}
<div class="agd-head">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
            <i class="fas fa-gauge-high ap-accent"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">Welcome back, {{ $dhUser->first_name }}!</h4>
            <p class="text-xs text-gray-400">Your Travel Overview</p>
        </div>
    </div>
    <a href="{{ route('user.profile.index') }}" class="ap-btn-outline ap-no-underline">
        <i class="fas fa-user-pen ap-accent"></i> Edit Profile
    </a>
</div>

{{-- Stats --}}
<div class="agd-stats">
    @foreach($dhStats as [$icon, $label, $value])
        <div class="agd-stat">
            <span class="agd-stat-icon"><i class="fas {{ $icon }}"></i></span>
            <div class="agd-stat-text">
                <small>{{ $label }}</small>
                <b>{{ $value }}</b>
            </div>
        </div>
    @endforeach
</div>

{{-- Quick Actions --}}
<div class="agd-actions">
    @foreach($dhActions as [$url, $icon, $label, $hint])
        <a href="{{ $url }}" class="agd-action ap-no-underline">
            <span class="agd-action-icon"><i class="fas {{ $icon }}"></i></span>
            <span class="agd-action-text"><b>{{ $label }}</b><small>{{ $hint }}</small></span>
            <i class="fas fa-arrow-right agd-action-arrow"></i>
        </a>
    @endforeach
</div>

{{-- Recent Bookings --}}
<div class="ap-card">
    <div class="ap-card-header">
        <span><i class="fas fa-calendar-check ap-accent"></i> Recent Bookings <small class="agd-count">{{ $totalBookings }} records</small></span>
        <a href="{{ route('user.bookings.index') }}" class="ap-btn-outline-sm ap-no-underline">View All</a>
    </div>

    @if($recentBookings->isEmpty())
        <div class="agd-empty">
            <i class="fas fa-calendar-xmark"></i>
            No bookings yet. Your recent bookings will appear here.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="ap-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Module</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Price</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $i => $b)
                        <tr>
                            <td class="agd-date">{{ $i + 1 }}</td>
                            <td class="agd-ref">{{ $b['booking_code'] }}</td>
                            <td>
                                <span class="ap-badge ap-badge-blue">
                                    <i class="fas {{ $typeIcons[$b['booking_type']] ?? 'fa-circle' }}"></i>
                                    {{ ucfirst($b['booking_type']) }}
                                </span>
                            </td>
                            <td><span class="ap-badge {{ $statusBadge[$b['status']] ?? 'ap-muted-badge' }}">{{ ucfirst($b['status']) }}</span></td>
                            <td><span class="ap-badge {{ $payBadge[$b['payment']] ?? 'ap-muted-badge' }}">{{ ucfirst($b['payment']) }}</span></td>
                            <td class="agd-amount">{{ $b['price'] }}</td>
                            <td class="agd-date">{{ \Carbon\Carbon::parse($b['created_at'])->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('user.bookings.index') }}" class="ap-btn-outline-sm ap-no-underline" aria-label="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
