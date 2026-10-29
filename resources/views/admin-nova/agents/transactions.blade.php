@extends('admin-nova.layouts.app')

@section('title', 'Wallet Transactions — ' . $agent->full_name)

@section('content')
@php
    $walletCurrency = ($wallet?->currency && $wallet->currency !== 'PKR') ? $wallet->currency : 'USD';
    $money = fn ($v, $cur = null) => ($cur && $cur !== 'PKR' ? $cur : $walletCurrency) . ' ' . number_format((float) $v, 2);
@endphp
<div class="tbp-wallet-transactions-page">

    <div class="tbp-wallet-transactions-header">
        <div>
            <h1>Wallet Transactions</h1>
            <p>View all wallet transactions for {{ $agent->full_name }} ({{ $agent->agent_code }})</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="tbp-agent-form-btn"><i class="bi bi-arrow-left"></i> Back to Agent</a>
    </div>

    <div class="tbp-wallet-summary-grid">
        <div class="tbp-wallet-summary-card is-balance">
            <span class="tbp-wallet-summary-icon"><i class="bi bi-wallet2"></i></span>
            <div><small>Current Balance</small><strong>{{ $money($wallet?->balance ?? 0) }}</strong></div>
        </div>
        <div class="tbp-wallet-summary-card is-credit">
            <span class="tbp-wallet-summary-icon"><i class="bi bi-arrow-down"></i></span>
            <div><small>Total Credited</small><strong>{{ $money($wallet?->total_credited ?? 0) }}</strong></div>
        </div>
        <div class="tbp-wallet-summary-card is-debit">
            <span class="tbp-wallet-summary-icon"><i class="bi bi-arrow-up"></i></span>
            <div><small>Total Debited</small><strong>{{ $money($wallet?->total_debited ?? 0) }}</strong></div>
        </div>
    </div>

    <form method="GET" class="tbp-wallet-transactions-filters">
        <label class="tbp-agent-form-field">
            <span>Transaction Type</span>
            <span class="tbp-wallet-filter-control"><i class="bi bi-arrow-left-right"></i>
                <select name="type" class="tbp-agent-form-input">
                    <option value="">All Types</option>
                    <option value="credit" @selected(request('type') === 'credit')>Credit</option>
                    <option value="debit" @selected(request('type') === 'debit')>Debit</option>
                </select>
            </span>
        </label>
        <label class="tbp-agent-form-field">
            <span>From Date</span>
            <input type="date" name="from" class="tbp-agent-form-input" value="{{ request('from') }}">
        </label>
        <label class="tbp-agent-form-field">
            <span>To Date</span>
            <input type="date" name="to" class="tbp-agent-form-input" value="{{ request('to') }}">
        </label>
        <div class="tbp-wallet-filter-buttons">
            <button type="submit" class="tbp-agent-form-btn is-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="tbp-agent-form-btn is-icon" title="Reset filters"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
        <label class="tbp-wallet-filter-search">
            <i class="bi bi-search"></i>
            <input type="search" name="search" class="tbp-agent-form-input" value="{{ request('search') }}" placeholder="Search by note, reference or performed by...">
        </label>
    </form>

    <div class="tbp-wallet-transactions-table">
        <div class="tbp-wallet-table-scroll">
            <table>
                <thead>
                    <tr><th>#</th><th>Type</th><th>Amount</th><th>Balance Before</th><th>Balance After</th><th>Note</th><th>Performed By</th><th>Date</th></tr>
                </thead>
                <tbody>
                @forelse($transactions as $txn)
                    @php $isCredit = $txn->type === 'credit'; @endphp
                    <tr>
                        <td class="is-id">{{ $txn->id }}</td>
                        <td><span class="tbp-wallet-transaction-badge {{ $isCredit ? 'is-credit' : 'is-debit' }}"><i class="bi bi-arrow-{{ $isCredit ? 'down' : 'up' }}"></i> {{ ucfirst($txn->type) }}</span></td>
                        <td class="is-amount {{ $isCredit ? 'is-credit' : 'is-debit' }}">{{ $isCredit ? '+' : '-' }} {{ $money($txn->amount, $txn->currency) }}</td>
                        <td>{{ $money($txn->balance_before, $txn->currency) }}</td>
                        <td>{{ $money($txn->balance_after, $txn->currency) }}</td>
                        <td class="is-note">{{ $txn->note ?: '—' }}</td>
                        <td>{{ $txn->performedBy?->full_name ?? '—' }}</td>
                        <td class="is-date"><i class="bi bi-calendar3"></i> {{ displayDateTime($txn->created_at) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="tbp-wallet-empty">
                        <i class="bi bi-receipt"></i>
                        <strong>No transactions found</strong>
                        <span>Try adjusting your filters or search.</span>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tbp-wallet-pagination">
        <span>
            @if($transactions->total())
                Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
            @else
                No transactions
            @endif
        </span>
        <nav>
            @php $p = $transactions->withQueryString(); @endphp
            <a class="{{ $p->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $p->previousPageUrl() ?? '#' }}" aria-label="Previous"><i class="bi bi-arrow-left"></i></a>
            @foreach($p->getUrlRange(max(1, $p->currentPage() - 2), min($p->lastPage(), $p->currentPage() + 2)) as $num => $url)
                <a class="{{ $num === $p->currentPage() ? 'is-active' : '' }}" href="{{ $url }}">{{ $num }}</a>
            @endforeach
            <a class="{{ $p->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $p->nextPageUrl() ?? '#' }}" aria-label="Next"><i class="bi bi-arrow-right"></i></a>
        </nav>
    </div>
</div>
@endsection
