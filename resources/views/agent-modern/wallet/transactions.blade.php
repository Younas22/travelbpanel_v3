@extends('agent-modern.layouts.app')
@section('title', 'Wallet Transactions')

@section('content')

@php
    $activeCurrency = activeCurrency();
    $walletCurrency = $wallet?->currency ?? 'PKR';
    $displayCurrency = $activeCurrency->currency_name ?? $walletCurrency;
    $toDisplay = fn($amt) => $activeCurrency ? convertCurrency($amt ?? 0, $walletCurrency, $displayCurrency) : ($amt ?? 0);
    $isPrint = $isPrint ?? false;
@endphp

@if($isPrint)
    <div class="mb-4">
        <h4 class="ap-page-title">{{ $agent->company_name ?? $agent->full_name }} — Wallet Transactions</h4>
        <p class="ap-page-sub">Generated {{ now()->format('d M Y, h:i A') }}</p>
    </div>
    <script>window.addEventListener('load', () => window.print());</script>
@endif

@unless($isPrint)
    <div class="ap-page-header">
        <div class="ap-page-header-left">
            <div class="ap-icon-badge"><i class="bi bi-clock-history"></i></div>
            <div>
                <h4 class="ap-page-title">Wallet Transactions</h4>
                <p class="ap-page-sub">Full transaction history</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('agent.wallet.transactions', array_merge(request()->query(), ['print' => 1])) }}" target="_blank" class="ap-btn-outline">
                <i class="bi bi-file-earmark-pdf"></i> Download PDF
            </a>
            <a href="{{ route('agent.wallet.index') }}" class="ap-btn-outline">
                <i class="bi bi-arrow-left"></i> Back to Wallet
            </a>
        </div>
    </div>

    <div class="ap-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('agent.wallet.index') }}">My Wallet</a>
        <i class="bi bi-chevron-right"></i>
        <span>Transactions</span>
    </div>
@endunless

    @if($wallet)
    <div class="wal-stats-grid">
        <div class="wal-stat-card">
            <div class="wal-stat-icon wal-accent"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="wal-stat-label">Current Balance</div>
                <div class="wal-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->balance), 2) }}</div>
            </div>
        </div>
        <div class="wal-stat-card">
            <div class="wal-stat-icon wal-accent"><i class="bi bi-arrow-down-circle"></i></div>
            <div>
                <div class="wal-stat-label">Total Credited</div>
                <div class="wal-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->total_credited), 2) }}</div>
            </div>
        </div>
        <div class="wal-stat-card">
            <div class="wal-stat-icon wal-danger"><i class="bi bi-arrow-up-circle"></i></div>
            <div>
                <div class="wal-stat-label">Total Spent</div>
                <div class="wal-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->total_debited), 2) }}</div>
            </div>
        </div>
    </div>
    @endif

    @unless($isPrint)
    <div class="am-card mb-4">
        <div class="am-card-header">Filter Transactions</div>
        <div class="am-card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                        <option value="debit"  {{ request('type') === 'debit'  ? 'selected' : '' }}>Debit</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All</option>
                        <option value="booking" {{ request('category') === 'booking' ? 'selected' : '' }}>Booking</option>
                        <option value="others"  {{ request('category') === 'others'  ? 'selected' : '' }}>Others</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ (string) request('month') === (string) $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ (string) request('year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-lg-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-lg-6 d-flex gap-2">
                    <button type="submit" class="ap-btn-primary" style="flex: 1; justify-content: center;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('agent.wallet.transactions') }}" class="ap-btn-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>
    @endunless

    <div class="am-card">
        @if($transactions->isEmpty())
            <div class="am-empty">
                <i class="bi bi-receipt"></i>
                <h6>No transactions found</h6>
                <p class="mb-0">Try adjusting your filters or check back later.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Details</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $txn)
                    <tr>
                        <td style="color: color-mix(in srgb, var(--text-color) 50%, transparent); font-size: 12px;">{{ $isPrint ? $loop->iteration : $transactions->firstItem() + $loop->index }}</td>
                        <td style="font-size: 12px; color: color-mix(in srgb, var(--text-color) 55%, transparent); white-space: nowrap;">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($txn->type === 'credit')
                                <span class="badge bg-success">Credit</span>
                            @else
                                <span class="badge bg-danger">Debit</span>
                            @endif
                        </td>
                        <td>
                            @if($txn->booking_type)
                                <span class="badge bg-primary">Booking</span>
                            @else
                                <span class="badge bg-secondary">Others</span>
                            @endif
                        </td>
                        <td>{{ $txn->note }}</td>
                        <td style="font-family: monospace; font-size: 12px; color: color-mix(in srgb, var(--text-color) 50%, transparent);">{{ $txn->reference ?? '—' }}</td>
                        <td class="text-end" style="font-weight: 650; color: {{ $txn->type === 'credit' ? 'var(--success-color)' : 'var(--danger-color)' }};">
                            {{ $txn->type === 'credit' ? '+' : '-' }} {{ $displayCurrency }} {{ number_format($toDisplay($txn->amount), 2) }}
                        </td>
                        <td class="text-end">{{ $displayCurrency }} {{ number_format($toDisplay($txn->balance_after), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @unless($isPrint)
        <div style="padding: var(--am-space-4) var(--am-space-5); border-top: 1px solid var(--border-color);">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @endunless
        @endif
    </div>

@endsection
