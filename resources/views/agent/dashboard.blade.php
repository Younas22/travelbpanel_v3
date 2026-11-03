@extends('agent.layouts.app')
@section('title', 'Dashboard')

@section('content')

@if($zeroOutNotice)
<div class="mb-5 p-4 rounded-xl border border-amber-200 bg-amber-50 flex items-start gap-3">
    <i class="fas fa-circle-exclamation text-amber-500 mt-0.5"></i>
    <div class="flex-1">
        <p class="text-sm font-semibold text-amber-800">Your wallet balance was reset to zero by admin</p>
        <p class="text-xs text-amber-700 mt-0.5">{{ \Illuminate\Support\Str::after($zeroOutNotice->note, 'Reason: ') }}</p>
        <p class="text-[11px] text-amber-600 mt-1">{{ displayDateTime($zeroOutNotice->created_at) }}</p>
    </div>
    <form method="POST" action="{{ route('agent.wallet.notice.dismiss', $zeroOutNotice) }}">
        @csrf
        <button type="submit" class="text-xs font-semibold text-amber-700 hover:text-amber-900 px-3 py-1.5 border border-amber-300 rounded-lg hover:bg-amber-100 transition">
            Got it
        </button>
    </form>
</div>
@endif

@php
    $dhActiveCurrency = activeCurrency();
    $dhWalletCurrency = $wallet?->currency ?? 'PKR';
    $dhDisplayCurrency = $dhActiveCurrency->currency_name ?? $dhWalletCurrency;
    $dhToDisplay = fn($amt) => $dhActiveCurrency ? convertCurrency($amt ?? 0, $dhWalletCurrency, $dhDisplayCurrency) : ($amt ?? 0);
    $dhWalletBalance = $dhToDisplay($wallet?->balance);
    $agentUser = auth()->user();

    // stat tiles: [permission (null = always), icon, label, value]
    $dhStats = array_filter([
        $agentUser->hasPermission('module.hotels') ? ['fa-building', 'Hotels', $stats['total_hotels']] : null,
        $agentUser->hasPermission('module.flights') ? ['fa-plane', 'Flights', $stats['flights']] : null,
        $agentUser->hasPermission('module.tours') ? ['fa-map-location-dot', 'Tours', $stats['total_tours']] : null,
        $agentUser->hasPermission('module.umrah') ? ['fa-kaaba', 'Umrah', $stats['total_umrah']] : null,
        ['fa-calendar-check', 'Bookings', $stats['total']],
        ['fa-wallet', 'Wallet', $dhDisplayCurrency . ' ' . number_format($dhWalletBalance, 0)],
        ['fa-chart-line', 'Spent', $dhDisplayCurrency . ' ' . number_format($dhToDisplay($wallet?->total_debited), 0)],
    ]);
    $dhActions = array_filter([
        $agentUser->hasPermission('module.flights') ? [route('flights.index'), 'fa-plane', 'Book Flight', 'Search and book flights'] : null,
        $agentUser->hasPermission('module.tours') ? [route('tours.index'), 'fa-map-location-dot', 'Book Tour', 'Browse tour packages'] : null,
        $agentUser->hasPermission('module.umrah') ? [route('umrah.index'), 'fa-kaaba', 'Book Umrah', 'Umrah packages'] : null,
    ]);
@endphp

{{-- Welcome Header --}}
<div class="agd-head">
    <div>
        <h1 class="agd-title">Welcome back, {{ $agentUser->first_name }}!</h1>
        <p class="agd-subtitle">{{ $agentUser->company_name }}@if($agentUser->agent_code) &bull; {{ $agentUser->agent_code }}@endif</p>
    </div>
    @if($agentUser->hasPermission('wallet.view'))
        <a href="{{ route('agent.wallet.index') }}" class="agd-wallet ap-no-underline">
            <i class="fas fa-wallet"></i>
            <span><small>Wallet balance</small><b>{{ $dhDisplayCurrency }} {{ number_format($dhWalletBalance, 0) }}</b></span>
        </a>
    @endif
</div>

{{-- Stats --}}
<div class="agd-stats">
    @foreach($dhStats as [$icon, $label, $value])
        <div class="agd-stat">
            <span class="agd-stat-icon"><i class="fas {{ $icon }}"></i></span>
            <div class="agd-stat-text">
                <small>{{ $label }}</small>
                <b title="{{ $value }}">{{ $value }}</b>
            </div>
        </div>
    @endforeach
</div>

{{-- Quick Actions --}}
@if($dhActions)
    <div class="agd-actions">
        @foreach($dhActions as [$url, $icon, $label, $hint])
            <a href="{{ $url }}" class="agd-action ap-no-underline">
                <span class="agd-action-icon"><i class="fas {{ $icon }}"></i></span>
                <span class="agd-action-text"><b>{{ $label }}</b><small>{{ $hint }}</small></span>
                <i class="fas fa-arrow-right agd-action-arrow"></i>
            </a>
        @endforeach
    </div>
@endif

<div class="agd-panels">
    {{-- Recent Bookings --}}
    <div class="ap-card">
        <div class="ap-card-header">
            <span><i class="fas fa-calendar-check ap-accent"></i> Recent Bookings</span>
            <a href="{{ route('agent.bookings.index') }}" class="ap-btn-outline-sm ap-no-underline">View All</a>
        </div>
        @if($recentBookings->isEmpty())
            <div class="agd-empty">
                <i class="fas fa-calendar-xmark"></i>
                No bookings yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBookings as $booking)
                            <tr>
                                <td class="agd-ref">{{ $booking['booking_code_ref'] ?? 'N/A' }}</td>
                                <td><span class="ap-badge ap-badge-blue">{{ ucfirst($booking['booking_type']) }}</span></td>
                                <td class="agd-amount">{{ $booking['booking_currency_origin'] ?? 'PKR' }} {{ number_format($booking['booking_fare_base'] ?? 0, 0) }}</td>
                                <td class="agd-date">{{ \Carbon\Carbon::parse($booking['created_at'])->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Wallet Activity --}}
    @if($agentUser->hasPermission('wallet.view'))
        <div class="ap-card">
            <div class="ap-card-header">
                <span><i class="fas fa-wallet ap-accent"></i> Wallet Activity</span>
                <a href="{{ route('agent.wallet.transactions') }}" class="ap-btn-outline-sm ap-no-underline">View All</a>
            </div>
            @if($recentTransactions->isEmpty())
                <div class="agd-empty">
                    <i class="fas fa-wallet"></i>
                    No transactions yet.
                </div>
            @else
                <ul class="agd-txns">
                    @foreach($recentTransactions as $txn)
                        <li>
                            <span class="agd-txn-icon {{ $txn->type === 'credit' ? 'is-credit' : 'is-debit' }}"><i class="fas {{ $txn->type === 'credit' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i></span>
                            <div class="agd-txn-text">
                                <b>{{ Str::limit($txn->note, 30) }}</b>
                                <small>{{ $txn->created_at->format('d M Y') }}</small>
                            </div>
                            <span class="agd-txn-amount {{ $txn->type === 'credit' ? 'is-credit' : 'is-debit' }}">
                                {{ $txn->type === 'credit' ? '+' : '-' }} {{ $dhDisplayCurrency }} {{ number_format($dhToDisplay($txn->amount), 0) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>

@endsection
