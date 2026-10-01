@extends('admin.layouts.app')
@section('title', 'Wallet Transactions — ' . $agent->full_name)

@section('content')

    @php
        $wallet = $agent->wallet;
        $activeCurrency = activeCurrency();
        $walletCurrency = $wallet?->currency ?? 'PKR';
        $displayCurrency = $activeCurrency->currency_name ?? $walletCurrency;
        $toDisplay = fn($amt) => $activeCurrency ? convertCurrency($amt ?? 0, $walletCurrency, $displayCurrency) : ($amt ?? 0);
    @endphp

        <!-- ===== PAGE HEADER ===== -->
    <div class="wt-header">
        <div>
            <h2 class="wt-title">Wallet Transactions</h2>
            <p class="wt-subtitle">{{ $agent->full_name }} ({{ $agent->agent_code }})</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="wt-back-btn">
            <i class="bi bi-arrow-left"></i> Back to Agent
        </a>
    </div>

    <!-- ===== BALANCE STATS ===== -->
    <div class="wt-stats">
        <div class="wt-stat">
            <div class="wt-stat-icon wt-icon-accent"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="wt-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->balance), 2) }}</div>
                <div class="wt-stat-label">Current Balance</div>
            </div>
        </div>
        <div class="wt-stat">
            <div class="wt-stat-icon wt-icon-accent"><i class="bi bi-arrow-down-circle"></i></div>
            <div>
                <div class="wt-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->total_credited), 2) }}</div>
                <div class="wt-stat-label">Total Credited</div>
            </div>
        </div>
        <div class="wt-stat">
            <div class="wt-stat-icon wt-icon-red"><i class="bi bi-arrow-up-circle"></i></div>
            <div>
                <div class="wt-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->total_debited), 2) }}</div>
                <div class="wt-stat-label">Total Debited</div>
            </div>
        </div>
    </div>

    <!-- ===== FILTERS ===== -->
    <div class="wt-filter-card">
        <form method="GET" class="wt-filter-grid">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                <option value="debit"  {{ request('type') === 'debit'  ? 'selected' : '' }}>Debit</option>
            </select>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            <button type="submit" class="wt-btn wt-btn-primary">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="wt-btn wt-btn-outline">Reset</a>
        </form>
    </div>

    <!-- ===== TRANSACTIONS TABLE ===== -->
    <div class="wt-table-card">
        <div class="table-responsive">
            <table class="wt-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance Before</th>
                    <th>Balance After</th>
                    <th>Note</th>
                    <th>Performed By</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transactions as $txn)
                    <tr>
                        <td><span class="wt-meta">{{ $txn->id }}</span></td>
                        <td>
                                <span class="wt-type wt-type-{{ $txn->type }}">
                                    <i class="bi {{ $txn->type === 'credit' ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle' }}"></i>
                                    {{ ucfirst($txn->type) }}
                                </span>
                        </td>
                        <td>
                                <span class="wt-amount wt-amount-{{ $txn->type }}">
                                    {{ $txn->type === 'credit' ? '+' : '-' }} {{ $displayCurrency }} {{ number_format($toDisplay($txn->amount), 2) }}
                                </span>
                        </td>
                        <td><span class="wt-meta">{{ $displayCurrency }} {{ number_format($toDisplay($txn->balance_before), 2) }}</span></td>
                        <td><span class="wt-meta">{{ $displayCurrency }} {{ number_format($toDisplay($txn->balance_after), 2) }}</span></td>
                        <td>{{ $txn->note ?? '—' }}</td>
                        <td>{{ $txn->performedBy?->full_name ?? '—' }}</td>
                        <td><span class="wt-meta">{{ $txn->created_at->format('d M Y, h:i A') }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="wt-empty">
                                <i class="bi bi-receipt"></i>
                                <h5>No transactions found</h5>
                                <p>Try adjusting your filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="wt-pagination">
                {{ $transactions->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
