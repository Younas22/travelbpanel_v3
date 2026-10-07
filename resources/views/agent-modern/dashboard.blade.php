@extends('agent-modern.layouts.app')
@section('title', 'Dashboard')

@section('content')

    <div class="dash-welcome">
        <div>
            <h4>Welcome back, {{ auth()->user()->first_name }}!</h4>
            <p>{{ auth()->user()->company_name }} &bull; {{ auth()->user()->agent_code }}</p>
        </div>
        @php
            $dhActiveCurrency = activeCurrency();
            $dhWalletCurrency = $wallet?->currency ?? 'PKR';
            $dhDisplayCurrency = $dhActiveCurrency->currency_name ?? $dhWalletCurrency;
            $dhToDisplay = fn($amt) => $dhActiveCurrency ? convertCurrency($amt ?? 0, $dhWalletCurrency, $dhDisplayCurrency) : ($amt ?? 0);
            $dhWalletBalance = $dhToDisplay($wallet?->balance);
        @endphp
        @if(auth()->user()->hasPermission('wallet.view'))
            <a href="{{ route('agent.wallet.index') }}" class="am-topbar-wallet">
                <i class="bi bi-wallet2"></i> Wallet: <strong>{{ $dhDisplayCurrency }} {{ number_format($dhWalletBalance, 0) }}</strong>
            </a>
        @endif
    </div>

    <div class="dash-stats-grid">

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-blue"><i class="bi bi-building"></i></div>
            <div>
                <div class="dash-stat-label">Hotels</div>
                <div class="dash-stat-value">{{ $stats['total_hotels'] }}</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-blue"><i class="bi bi-airplane"></i></div>
            <div>
                <div class="dash-stat-label">Flight Bookings</div>
                <div class="dash-stat-value">{{ $stats['flights'] }}</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-green"><i class="bi bi-map"></i></div>
            <div>
                <div class="dash-stat-label">Tours</div>
                <div class="dash-stat-value">{{ $stats['total_tours'] }}</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-amber"><i class="bi bi-moon-stars"></i></div>
            <div>
                <div class="dash-stat-label">Umrah</div>
                <div class="dash-stat-value">{{ $stats['total_umrah'] }}</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-blue"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="dash-stat-label">Bookings</div>
                <div class="dash-stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-amber"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="dash-stat-label">Wallet</div>
                <div class="dash-stat-value">{{ $dhDisplayCurrency }} {{ number_format($dhWalletBalance, 0) }}</div>
            </div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-cyan"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="dash-stat-label">Spent</div>
                <div class="dash-stat-value">{{ $dhDisplayCurrency }} {{ number_format($dhToDisplay($wallet?->total_debited), 0) }}</div>
            </div>
        </div>

    </div>

    <div class="dash-quick-grid">

        @if(auth()->user()->hasPermission('module.flights'))
        <a href="{{ route('agent.flights.index') }}" class="dash-quick-card">
            <i class="bi bi-airplane" style="color: var(--success-color);"></i>
            <div class="dash-quick-label">Book Flight</div>
        </a>
        @endif

        @if(auth()->user()->hasPermission('module.tours'))
        <a href="{{ route('agent.tours.index') }}" class="dash-quick-card">
            <i class="bi bi-map" style="color: var(--warning-color);"></i>
            <div class="dash-quick-label">Book Tour</div>
        </a>
        @endif

        @if(auth()->user()->hasPermission('module.umrah'))
        <a href="{{ route('agent.umrah.index') }}" class="dash-quick-card">
            <i class="bi bi-moon-stars" style="color: var(--info-color);"></i>
            <div class="dash-quick-label">Book Umrah</div>
        </a>
        @endif

    </div>

    <div class="dash-panel">
        <div class="dash-panel-header">
            <h6>Recent Bookings</h6>
            <a href="{{ route('agent.bookings.index') }}" class="dash-panel-link">View All</a>
        </div>
        @if($recentBookings->isEmpty())
            <div class="am-empty">
                <i class="bi bi-calendar-x"></i>
                No bookings yet.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                        <td><span style="font-family: monospace; color: var(--primary-color); font-weight: 700;">{{ $booking['booking_code_ref'] ?? 'N/A' }}</span></td>
                        <td><span class="badge bg-secondary">{{ ucfirst($booking['booking_type']) }}</span></td>
                        <td style="font-weight: 650;">{{ $booking['booking_currency_origin'] ?? 'PKR' }} {{ number_format($booking['booking_fare_base'] ?? 0, 0) }}</td>
                        <td style="color: color-mix(in srgb, var(--text-color) 55%, transparent); font-size: 12px;">{{ \Carbon\Carbon::parse($booking['created_at'])->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    @if(auth()->user()->hasPermission('wallet.view'))
    <div class="dash-panel">
        <div class="dash-panel-header">
            <h6>Wallet Activity</h6>
            <a href="{{ route('agent.wallet.transactions') }}" class="dash-panel-link">View All</a>
        </div>
        @if($recentTransactions->isEmpty())
            <div class="am-empty">
                <i class="bi bi-wallet2"></i>
                No transactions yet.
            </div>
        @else
        <ul class="wallet-activity-list">
            @foreach($recentTransactions as $txn)
            <li class="wallet-activity-item">
                <div class="min-w-0">
                    <div class="wallet-activity-note">{{ Str::limit($txn->note, 30) }}</div>
                    <div class="wallet-activity-date">{{ $txn->created_at->format('d M Y') }}</div>
                </div>
                <span class="wallet-activity-amt {{ $txn->type === 'credit' ? 'credit' : 'debit' }}">
                    {{ $txn->type === 'credit' ? '+' : '-' }} {{ $dhDisplayCurrency }} {{ number_format($dhToDisplay($txn->amount), 0) }}
                </span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif

@endsection
