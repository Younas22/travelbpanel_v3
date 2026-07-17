@extends('agent.layouts.app')
@section('title', 'Wallet Transactions')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#e8f4fd;">
            <i class="fas fa-history" style="color:#0077BE;"></i>
        </div>
        <div>
            <h4 class="text-lg font-bold text-gray-800">Wallet Transactions</h4>
            <p class="text-xs text-gray-400">Full transaction history</p>
        </div>
    </div>
    <a href="{{ route('agent.wallet.index') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2"
       style="background:#e8f4fd; color:#0077BE; text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Back to Wallet
    </a>
</div>

<div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
    <a href="{{ route('agent.dashboard') }}" style="color:#0077BE; text-decoration:none;">Dashboard</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <a href="{{ route('agent.wallet.index') }}" style="color:#0077BE; text-decoration:none;">My Wallet</a>
    <i class="fas fa-chevron-right text-gray-300"></i>
    <span class="text-gray-600">Transactions</span>
</div>

@if($wallet)
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#e8f4fd;">
            <i class="fas fa-wallet" style="color:#0077BE;"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Current Balance</p>
            <p class="text-xl font-bold text-gray-800">PKR {{ number_format($wallet->balance, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#e8f4fd;">
            <i class="fas fa-arrow-circle-down" style="color:#0077BE;"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Credited</p>
            <p class="text-xl font-bold text-gray-800">PKR {{ number_format($wallet->total_credited, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background:#fee2e2;">
            <i class="fas fa-arrow-circle-up" style="color:#dc2626;"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Spent</p>
            <p class="text-xl font-bold text-gray-800">PKR {{ number_format($wallet->total_debited, 2) }}</p>
        </div>
    </div>
</div>
@endif

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-sm text-gray-700">
        Filter Transactions
    </div>
    <div class="p-5">
        <form method="GET" class="grid grid-cols-1 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type</label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit"  {{ request('type') === 'debit'  ? 'selected' : '' }}>Debit</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">From Date</label>
                <input type="date" name="from"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"
                       value="{{ request('from') }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">To Date</label>
                <input type="date" name="to"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-400 bg-gray-50"
                       value="{{ request('to') }}">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 px-5 py-2.5 rounded-lg text-sm font-semibold text-white"
                        style="background:#0077BE; border:none; cursor:pointer;">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('agent.wallet.transactions') }}"
                   class="px-4 py-2.5 rounded-lg text-sm font-semibold"
                   style="background:#e8f4fd; color:#0077BE; text-decoration:none;">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    @if($transactions->isEmpty())
    <div class="text-center py-12">
        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3" style="background:#e8f4fd;">
            <i class="fas fa-receipt text-xl" style="color:#0077BE;"></i>
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Details</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reference</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Balance After</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($transactions as $txn)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $transactions->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3">
                        @if($txn->type === 'credit')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Credit</span>
                        @else
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Debit</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $txn->note }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $txn->reference ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-right {{ $txn->type === 'credit' ? 'text-green-600' : 'text-red-500' }}">
                        {{ $txn->type === 'credit' ? '+' : '-' }} PKR {{ number_format($txn->amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 text-right">PKR {{ number_format($txn->balance_after, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $transactions->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
