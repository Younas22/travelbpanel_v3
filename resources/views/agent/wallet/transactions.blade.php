@extends('agent.layouts.app')
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
<div class="mb-5">
    <h4 class="text-lg font-bold text-gray-800">{{ $agent->company_name ?? $agent->full_name }} — Wallet Transactions</h4>
    <p class="text-xs text-gray-500">Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>
<script>window.addEventListener('load', () => window.print());</script>
@endif

@unless($isPrint)
<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
            <i class="fas fa-history ap-accent"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">Wallet Transactions</h4>
            <p class="text-xs text-gray-400">Full transaction history</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('agent.wallet.transactions', array_merge(request()->query(), ['print' => 1])) }}"
           target="_blank"
           class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 ap-chip-link">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('agent.wallet.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 ap-chip-link">
            <i class="fas fa-arrow-left"></i> Back to Wallet
        </a>
    </div>
</div>

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="ap-accent-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <a href="{{ route('agent.wallet.index') }}" class="ap-accent-link">My Wallet</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Transactions</span>
</div>
@endunless

@if($wallet && !$isPrint)
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ap-tint-bg">
            <i class="fas fa-wallet ap-accent"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Current Balance</p>
            <p class="text-xl font-bold text-gray-800">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->balance), 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ap-tint-bg">
            <i class="fas fa-arrow-circle-down ap-accent"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Credited</p>
            <p class="text-xl font-bold text-gray-800">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->total_credited), 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ap-danger-icon-bg">
            <i class="fas fa-arrow-circle-up ap-danger-icon-color"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Spent</p>
            <p class="text-xl font-bold text-gray-800">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->total_debited), 2) }}</p>
        </div>
    </div>
</div>
@endif

@unless($isPrint)
<style>
    .wal-filter-card { background: #fff; border-radius: 10px; border: 1px solid #eef0f3; box-shadow: 0 1px 2px rgba(16,24,40,.04); margin-bottom: 20px; overflow: hidden; }
    .wal-filter-head { padding: 9px 16px; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #f1f3f5; }
    .wal-filter-body { padding: 12px 16px 14px; }
    .wal-filter-row { display: flex; flex-wrap: nowrap; align-items: flex-end; gap: 8px; overflow-x: auto; }
    .wal-field { flex: 1; min-width: 96px; }
    .wal-field.wal-field-date { min-width: 118px; }
    .wal-field label { display: block; font-size: 9.5px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #9aa4b2; margin-bottom: 4px; }
    .wal-field select, .wal-field input[type="date"] {
        width: 100%; border: 1px solid #e2e6ea; border-radius: 7px; background: #fff;
        padding: 6px 8px; font-size: 12px; color: #374151; height: 32px;
        transition: border-color .15s, box-shadow .15s;
    }
    .wal-field select:focus, .wal-field input[type="date"]:focus {
        outline: none; border-color: var(--primary-color, #0C6DFD);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-color, #0C6DFD) 14%, transparent);
    }
    .wal-filter-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .wal-btn-filter, .wal-btn-reset {
        display: inline-flex; align-items: center; gap: 5px; height: 32px; white-space: nowrap;
        padding: 0 13px; font-size: 11.5px; font-weight: 650; border-radius: 7px; cursor: pointer;
        transition: box-shadow .15s, transform .08s, background .15s, border-color .15s; text-decoration: none;
    }
    .wal-btn-filter { border: none; color: #fff; background: var(--primary-color, #0C6DFD); box-shadow: 0 1px 2px rgba(16,24,40,.12); }
    .wal-btn-filter:hover { box-shadow: 0 3px 8px color-mix(in srgb, var(--primary-color, #0C6DFD) 38%, transparent); }
    .wal-btn-filter:active { transform: scale(.96); }
    .wal-btn-reset { border: 1px solid #e2e6ea; background: #fff; color: #64748b; }
    .wal-btn-reset:hover { background: #f8fafc; border-color: #cbd5e1; color: #374151; }
</style>
<div class="wal-filter-card">
    <div class="wal-filter-head">Filter Transactions</div>
    <div class="wal-filter-body">
        <form method="GET" class="wal-filter-row">
            <div class="wal-field">
                <label>Type</label>
                <select name="type">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit"  {{ request('type') === 'debit'  ? 'selected' : '' }}>Debit</option>
                </select>
            </div>
            <div class="wal-field">
                <label>Category</label>
                <select name="category">
                    <option value="">All</option>
                    <option value="booking" {{ request('category') === 'booking' ? 'selected' : '' }}>Booking</option>
                    <option value="others"  {{ request('category') === 'others'  ? 'selected' : '' }}>Others</option>
                </select>
            </div>
            <div class="wal-field">
                <label>Month</label>
                <select name="month">
                    <option value="">All Months</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (string) request('month') === (string) $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="wal-field">
                <label>Year</label>
                <select name="year">
                    <option value="">All Years</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ (string) request('year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="wal-field wal-field-date">
                <label>From Date</label>
                <input type="date" name="from" value="{{ request('from') }}">
            </div>
            <div class="wal-field wal-field-date">
                <label>To Date</label>
                <input type="date" name="to" value="{{ request('to') }}">
            </div>
            <div class="wal-filter-actions">
                <button type="submit" class="wal-btn-filter">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('agent.wallet.transactions') }}" class="wal-btn-reset">Reset</a>
            </div>
        </form>
    </div>
</div>
@endunless

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    @if($transactions->isEmpty())
    <div class="text-center py-12">
        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 ap-tint-bg">
            <i class="fas fa-receipt text-xl ap-accent"></i>
        </div>
        <p class="text-sm font-semibold text-gray-600 mb-1">No transactions found</p>
        <p class="text-xs text-gray-400">Try adjusting your filters or check back later.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Details</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reference</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Balance After</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($transactions as $txn)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $isPrint ? $loop->iteration : $transactions->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3">
                        @if($txn->type === 'credit')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Credit</span>
                        @else
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Debit</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($txn->booking_type)
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Booking</span>
                        @else
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">Others</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $txn->note }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $txn->reference ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-right {{ $txn->type === 'credit' ? 'text-green-600' : 'text-red-500' }}">
                        {{ $txn->type === 'credit' ? '+' : '-' }} {{ $displayCurrency }} {{ number_format($toDisplay($txn->amount), 2) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ $displayCurrency }} {{ number_format($toDisplay($txn->balance_after), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @unless($isPrint)
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $transactions->withQueryString()->links() }}
    </div>
    @endunless
    @endif
</div>

@endsection
