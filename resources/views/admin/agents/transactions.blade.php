@extends('admin.layouts.app')
@section('title', 'Wallet Transactions — ' . $agent->full_name)

@section('content')

    @php $wallet = $agent->wallet; @endphp

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
                <div class="wt-stat-value"> {{ number_format($wallet?->balance ?? 0, 2) }}</div>
                <div class="wt-stat-label">Current Balance</div>
            </div>
        </div>
        <div class="wt-stat">
            <div class="wt-stat-icon wt-icon-accent"><i class="bi bi-arrow-down-circle"></i></div>
            <div>
                <div class="wt-stat-value"> {{ number_format($wallet?->total_credited ?? 0, 2) }}</div>
                <div class="wt-stat-label">Total Credited</div>
            </div>
        </div>
        <div class="wt-stat">
            <div class="wt-stat-icon wt-icon-red"><i class="bi bi-arrow-up-circle"></i></div>
            <div>
                <div class="wt-stat-value"> {{ number_format($wallet?->total_debited ?? 0, 2) }}</div>
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
                                    {{ $txn->formatted_amount }}
                                </span>
                        </td>
                        <td><span class="wt-meta"> {{ number_format($txn->balance_before, 2) }}</span></td>
                        <td><span class="wt-meta"> {{ number_format($txn->balance_after, 2) }}</span></td>
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

@push('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .wt-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .wt-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 4px;
        }
        .wt-subtitle {
            font-size: 13px;
            color: var(--bs-secondary-color);
            margin: 0;
        }
        .wt-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .wt-back-btn:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }

        /* ===== BALANCE STATS ===== */
        .wt-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .wt-stat {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .wt-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            flex-shrink: 0;
        }
        .wt-icon-accent { background: #E3F0FF; color: #0C6DFD; }
        .wt-icon-red    { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .wt-icon-accent { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .wt-icon-red    { background: #2e0a0a; color: #f08080; }

        .wt-stat-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--bs-body-color);
            line-height: 1;
        }
        .wt-stat-label {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin-top: 3px;
        }

        /* ===== FILTER CARD ===== */
        .wt-filter-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 0.85rem 1.1rem;
            margin-bottom: 1.25rem;
        }
        .wt-filter-grid {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .wt-filter-grid .form-select,
        .wt-filter-grid .form-control {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
            width: auto;
        }
        .wt-filter-grid .form-select { min-width: 140px; }
        .wt-filter-grid .form-control[type="date"] { min-width: 150px; }

        /* ===== BUTTONS ===== */
        .wt-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid var(--bs-border-color);
            white-space: nowrap;
            transition: background .15s, color .15s, opacity .15s;
        }
        .wt-btn-primary {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .wt-btn-primary:hover { opacity: .9; color: #fff; }
        .wt-btn-outline {
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
        }
        .wt-btn-outline:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }

        /* ===== TABLE CARD ===== */
        .wt-table-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            overflow: hidden;
        }
        .wt-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .wt-table thead th {
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--bs-secondary-color);
            border-bottom: 1px solid var(--bs-border-color);
            white-space: nowrap;
            text-align: left;
            background: var(--bs-secondary-bg);
        }
        .wt-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--bs-border-color);
            vertical-align: middle;
            color: var(--bs-body-color);
        }
        .wt-table tbody tr:last-child td { border-bottom: none; }
        .wt-table tbody tr:hover td { background: var(--bs-tertiary-bg); }

        .wt-meta { font-size: 12px; color: var(--bs-secondary-color); }

        /* ===== TYPE BADGE ===== */
        .wt-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .wt-type-credit { background: #E3F0FF; color: #0C6DFD; }
        .wt-type-debit  { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .wt-type-credit { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .wt-type-debit  { background: #2e0a0a; color: #f08080; }

        /* ===== AMOUNT ===== */
        .wt-amount { font-size: 13px; font-weight: 600; }
        .wt-amount-credit { color: #0C6DFD; }
        .wt-amount-debit  { color: #A32D2D; }
        [data-bs-theme="dark"] .wt-amount-credit { color: #6ba8ff; }
        [data-bs-theme="dark"] .wt-amount-debit  { color: #f08080; }

        /* ===== EMPTY STATE ===== */
        .wt-empty {
            text-align: center;
            padding: 3rem 1rem;
        }
        .wt-empty i {
            font-size: 40px;
            color: var(--bs-border-color);
            margin-bottom: 12px;
            display: block;
        }
        .wt-empty h5 {
            font-size: 14px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 4px;
        }
        .wt-empty p {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin: 0;
        }

        /* ===== PAGINATION ===== */
        .wt-pagination {
            padding: 14px 20px;
            border-top: 1px solid var(--bs-border-color);
        }
        .wt-pagination .pagination { margin: 0; }
        .wt-pagination .page-link {
            border-radius: 7px;
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
            font-size: 13px;
            margin: 0 2px;
            background: var(--bs-body-bg);
        }
        .wt-pagination .page-item.active .page-link {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .wt-pagination .page-item.disabled .page-link {
            color: var(--bs-secondary-color);
            background: var(--bs-secondary-bg);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .wt-stats { grid-template-columns: 1fr; }
            .wt-filter-grid { flex-direction: column; align-items: stretch; }
            .wt-filter-grid .form-select,
            .wt-filter-grid .form-control,
            .wt-btn { width: 100%; min-width: 0; justify-content: center; }
        }
        @media (max-width: 480px) {
            .wt-header { align-items: flex-start; }
        }
    </style>
@endpush
