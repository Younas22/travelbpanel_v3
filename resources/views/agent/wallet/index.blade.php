@extends('agent.layouts.app')
@section('title', 'My Wallet')

@section('content')

@php
    $activeCurrency = activeCurrency();
    $walletCurrency = $wallet?->currency ?? 'PKR';
    $displayCurrency = $activeCurrency->currency_name ?? $walletCurrency;
    $toDisplay = fn($amt) => $activeCurrency ? convertCurrency($amt ?? 0, $walletCurrency, $displayCurrency) : ($amt ?? 0);
@endphp

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center ap-tint-bg">
            <i class="fas fa-wallet ap-accent"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">My Wallet</h4>
            <p class="text-xs text-gray-400">Balance overview & recent transactions</p>
        </div>
    </div>
    @if(auth()->user()->hasPermission('wallet.request'))
    <a href="{{ route('agent.wallet.topup') }}"
       class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white flex items-center gap-2 ap-solid-accent-btn">
        <i class="fas fa-plus-circle"></i> Request Top-Up
    </a>
    @endif
</div>

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" class="ap-accent-link">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">My Wallet</span>
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ap-tint-bg">
            <i class="fas fa-wallet ap-accent"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Current Balance</p>
            <p class="text-xl font-bold text-gray-800">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->balance), 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ap-tint-bg">
            <i class="fas fa-arrow-circle-down ap-accent"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Credited</p>
            <p class="text-xl font-bold text-gray-800">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->total_credited), 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ap-danger-icon-bg">
            <i class="fas fa-arrow-circle-up ap-danger-icon-color"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Spent</p>
            <p class="text-xl font-bold text-gray-800">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->total_debited), 2) }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <span class="font-semibold text-sm text-gray-700">Recent Transactions</span>
        <a href="{{ route('agent.wallet.transactions') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold ap-chip-link">
            View All
        </a>
    </div>
    @if($recentTransactions->isEmpty())
    <div class="text-center py-12">
        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 ap-tint-bg">
            <i class="fas fa-receipt text-xl ap-accent"></i>
        </div>
        <p class="text-sm font-semibold text-gray-600 mb-1">No transactions yet</p>
        <p class="text-xs text-gray-400">Your wallet transactions will appear here.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Details</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Balance After</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recentTransactions as $txn)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3">
                        @if($txn->type === 'credit')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Credit</span>
                        @else
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Debit</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $txn->note }}</td>
                    <td class="px-4 py-3 text-sm font-semibold {{ $txn->type === 'credit' ? 'text-green-600' : 'text-red-500' }}">
                        {{ $txn->type === 'credit' ? '+' : '-' }} {{ $displayCurrency }} {{ number_format($toDisplay($txn->amount), 2) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $displayCurrency }} {{ number_format($toDisplay($txn->balance_after), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
