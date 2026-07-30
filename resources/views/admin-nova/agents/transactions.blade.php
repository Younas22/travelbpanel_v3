@extends('admin-nova.layouts.app')

@section('title', 'Wallet Transactions — ' . $agent->full_name)

@push('styles')
@include('admin-nova.agents._styles')
@endpush

@section('content')
@php $wallet = $agent->wallet; @endphp
<div id="agPage" class="tt-fade-in font-jakarta">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Wallet Transactions</h1>
            <p class="text-xs text-novamuted mt-1">{{ $agent->full_name }} ({{ $agent->agent_code }})</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="ag-btn-nova px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Agent
        </a>
    </div>

    {{-- ===== BALANCE STATS ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 14.5h1.5"/></svg>
            </div>
            <div>
                <p class="text-lg font-bold text-novatext">{{ number_format($wallet?->balance ?? 0, 2) }}</p>
                <p class="text-[11px] text-novamuted">Current Balance</p>
            </div>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M18 13l-6 6-6-6"/></svg>
            </div>
            <div>
                <p class="text-lg font-bold text-novatext">{{ number_format($wallet?->total_credited ?? 0, 2) }}</p>
                <p class="text-[11px] text-novamuted">Total Credited</p>
            </div>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-novadanger text-white flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
            </div>
            <div>
                <p class="text-lg font-bold text-novatext">{{ number_format($wallet?->total_debited ?? 0, 2) }}</p>
                <p class="text-[11px] text-novamuted">Total Debited</p>
            </div>
        </div>
    </div>

    {{-- ===== FILTERS ===== --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="w-36">
                <label class="ag-label">Type</label>
                <select name="type" class="ag-input">
                    <option value="">All Types</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit"  {{ request('type') === 'debit'  ? 'selected' : '' }}>Debit</option>
                </select>
            </div>
            <div class="w-40">
                <label class="ag-label">From</label>
                <input type="date" name="from" class="ag-input" value="{{ request('from') }}">
            </div>
            <div class="w-40">
                <label class="ag-label">To</label>
                <input type="date" name="to" class="ag-input" value="{{ request('to') }}">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="ag-btn-nova ag-btn-primary w-10 h-10" data-tooltip="Apply filters">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                </button>
                <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="ag-btn-nova w-10 h-10" data-tooltip="Reset filters">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                </a>
            </div>
        </form>
    </div>

    {{-- ===== TRANSACTIONS TABLE ===== --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="overflow-x-auto">
            <table class="ag-table">
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
                        <td class="text-novamuted">{{ $txn->id }}</td>
                        <td>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold inline-flex items-center gap-1 {{ $txn->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-novadanger' }}">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    @if($txn->type === 'credit')
                                        <path d="M12 5v14M18 13l-6 6-6-6"/>
                                    @else
                                        <path d="M12 19V5M6 11l6-6 6 6"/>
                                    @endif
                                </svg>
                                {{ ucfirst($txn->type) }}
                            </span>
                        </td>
                        <td class="font-semibold {{ $txn->type === 'credit' ? 'text-novasuccess' : 'text-novadanger' }}">{{ $txn->formatted_amount }}</td>
                        <td class="text-novamuted">{{ number_format($txn->balance_before, 2) }}</td>
                        <td class="text-novamuted">{{ number_format($txn->balance_after, 2) }}</td>
                        <td class="text-novamuted">{{ $txn->note ?? '—' }}</td>
                        <td class="text-novamuted">{{ $txn->performedBy?->full_name ?? '—' }}</td>
                        <td class="text-novamuted whitespace-nowrap">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-14">
                            <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l3 3v15H6z"/><path d="M15 3v3h3M9 12h6M9 16h6"/></svg>
                            <p class="text-sm font-semibold text-novatext">No transactions found</p>
                            <p class="text-xs text-novamuted mt-1">Try adjusting your filters.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-5 border-t border-novaborder">
                {{ $transactions->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
