@extends('agent-modern.layouts.app')
@section('title', 'My Wallet')

@section('content')

    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-wallet2"></i></div>
            <div>
                <h4 class="ap-page-title">My Wallet</h4>
                <p class="ap-page-sub">Balance overview &amp; recent transactions</p>
            </div>
        </div>
        @if(auth()->user()->hasPermission('wallet.request'))
        <a href="{{ route('agent.wallet.topup') }}" class="ap-btn-primary">
            <i class="bi bi-plus-circle"></i> Request Top-Up
        </a>
        @endif
    </div>

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <span>My Wallet</span>
    </div>

    <div class="wal-stats-grid">
        <div class="wal-stat-card">
            <div class="wal-stat-icon wal-accent"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="wal-stat-label">Current Balance</div>
                <div class="wal-stat-value">PKR {{ number_format($wallet?->balance ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="wal-stat-card">
            <div class="wal-stat-icon wal-accent"><i class="bi bi-arrow-down-circle"></i></div>
            <div>
                <div class="wal-stat-label">Total Credited</div>
                <div class="wal-stat-value">PKR {{ number_format($wallet?->total_credited ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="wal-stat-card">
            <div class="wal-stat-icon wal-danger"><i class="bi bi-arrow-up-circle"></i></div>
            <div>
                <div class="wal-stat-label">Total Spent</div>
                <div class="wal-stat-value">PKR {{ number_format($wallet?->total_debited ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="dash-panel">
        <div class="dash-panel-header">
            <span>Recent Transactions</span>
            <a href="{{ route('agent.wallet.transactions') }}" class="dash-panel-link">View All</a>
        </div>
        @if($recentTransactions->isEmpty())
            <div class="am-empty">
                <i class="bi bi-receipt"></i>
                <h6>No transactions yet</h6>
                <p class="mb-0">Your wallet transactions will appear here.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $txn)
                    <tr>
                        <td style="font-size: 12px; color: color-mix(in srgb, var(--text-color) 55%, transparent); white-space: nowrap;">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($txn->type === 'credit')
                                <span class="badge bg-success">Credit</span>
                            @else
                                <span class="badge bg-danger">Debit</span>
                            @endif
                        </td>
                        <td>{{ $txn->note }}</td>
                        <td style="font-weight: 650; color: {{ $txn->type === 'credit' ? 'var(--success-color)' : 'var(--danger-color)' }};">
                            {{ $txn->formatted_amount }}
                        </td>
                        <td>PKR {{ number_format($txn->balance_after, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

@endsection
