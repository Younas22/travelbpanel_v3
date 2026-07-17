@extends('admin.layouts.app')
@section('title', 'Agent Wallet — ' . $agent->full_name)

@section('content')

    <!-- ===== PAGE HEADER ===== -->
    <div class="agw-header">
        <div>
            <h2 class="agw-title">Agent Wallet</h2>
            <p class="agw-subtitle">{{ $agent->full_name }} ({{ $agent->agent_code }})</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="agw-back-btn">
            <i class="bi bi-arrow-left"></i> Back to Agent
        </a>
    </div>

    <!-- ===== BALANCE STATS ===== -->
    <div class="agw-stats">
        <div class="agw-stat">
            <div class="agw-stat-icon agw-icon-accent"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="agw-stat-value">{{ number_format($wallet->balance, 2) }}</div>
                <div class="agw-stat-label">Current Balance</div>
            </div>
        </div>
        <div class="agw-stat">
            <div class="agw-stat-icon agw-icon-accent"><i class="bi bi-arrow-down-circle"></i></div>
            <div>
                <div class="agw-stat-value">{{ number_format($wallet->total_credited, 2) }}</div>
                <div class="agw-stat-label">Total Credited</div>
            </div>
        </div>
        <div class="agw-stat">
            <div class="agw-stat-icon agw-icon-red"><i class="bi bi-arrow-up-circle"></i></div>
            <div>
                <div class="agw-stat-value">{{ number_format($wallet->total_debited, 2) }}</div>
                <div class="agw-stat-label">Total Debited</div>
            </div>
        </div>
    </div>

    <!-- ===== CREDIT / DEBIT FORMS ===== -->
    <div class="agw-grid">

        {{-- Add Balance --}}
        <div class="agw-form-card">
            <h5 class="agw-section-title agw-title-accent"><i class="bi bi-plus-circle"></i> Add Balance (Credit)</h5>
            <form method="POST" action="{{ route('admin.agents.wallet.credit', $agent) }}">
                @csrf
                <div class="agw-field">
                    <label>Amount</label>
                    <input type="number" name="amount" class="form-control" min="1" step="1" required>
                </div>
                <div class="agw-field">
                    <label>Payment Method</label>
                    <input type="text" name="payment_method" class="form-control" placeholder="e.g. Bank Transfer, Cash">
                </div>
                <div class="agw-field">
                    <label>Note</label>
                    <textarea name="note" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="agw-btn agw-btn-accent">
                    <i class="bi bi-plus-circle"></i> Add Balance
                </button>
            </form>
        </div>

        {{-- Deduct Balance --}}
        <div class="agw-form-card">
            <h5 class="agw-section-title agw-title-red"><i class="bi bi-dash-circle"></i> Deduct Balance (Debit)</h5>
            <form method="POST" action="{{ route('admin.agents.wallet.debit', $agent) }}">
                @csrf
                <div class="agw-field">
                    <label>Amount</label>
                    <input type="number" name="amount" class="form-control" min="1" step="1" required>
                </div>
                <div class="agw-field">
                    <label>Reason <span class="agw-required">*</span></label>
                    <textarea name="note" class="form-control" rows="2" required></textarea>
                </div>
                <button type="submit" class="agw-btn agw-btn-red"
                        onclick="return confirm('Deduct from wallet?')">
                    <i class="bi bi-dash-circle"></i> Deduct Balance
                </button>
            </form>
        </div>
    </div>

    <!-- ===== TOP-UP REQUESTS ===== -->
    <div class="agw-card">
        <div class="agw-card-header">
            <h5 class="agw-card-title"><i class="bi bi-receipt"></i> Top-Up Requests</h5>
            @if($topupRequests->where('status', 'pending')->count())
                <span class="agw-pending-badge">{{ $topupRequests->where('status', 'pending')->count() }} Pending</span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="agw-table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Proof</th>
                    <th>Note</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($topupRequests as $req)
                    <tr>
                        <td><span class="agw-meta">{{ $req->created_at->format('d M Y, h:i A') }}</span></td>
                        <td><span class="agw-amount">{{ number_format($req->amount, 2) }}</span></td>
                        <td>{{ $req->payment_method ?? '—' }}</td>
                        <td>
                            @if($req->proof_url)
                                <a href="{{ $req->proof_url }}" target="_blank" class="agw-action-btn">View</a>
                            @else
                                <span class="agw-meta">—</span>
                            @endif
                        </td>
                        <td><span class="agw-meta">{{ $req->note ?? '—' }}</span></td>
                        <td>
                            <span class="agw-status agw-status-{{ $req->status }}">{{ ucfirst($req->status) }}</span>
                        </td>
                        <td>
                            @if($req->status === 'pending')
                                <div class="agw-actions">
                                    <form method="POST" action="{{ route('admin.topup.approve', $req) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="agw-action-btn agw-action-approve"
                                                onclick="return confirm('Approve  {{ number_format($req->amount, 2) }} top-up?')">
                                            Approve
                                        </button>
                                    </form>
                                    <button type="button" class="agw-action-btn agw-action-reject"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                        Reject
                                    </button>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content agw-modal">
                                            <div class="modal-header">
                                                <h6 class="modal-title">Reject Top-Up Request</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.topup.reject', $req) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <label class="form-label">Reason <span class="agw-required">*</span></label>
                                                    <textarea name="rejection_note" class="form-control" rows="3" required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="agw-btn agw-btn-outline" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="agw-btn agw-btn-red">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="agw-meta">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="agw-empty-cell">No top-up requests yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== RECENT TRANSACTIONS ===== -->
    <div class="agw-card">
        <div class="agw-card-header">
            <h5 class="agw-card-title"><i class="bi bi-clock-history"></i> Recent Transactions</h5>
            <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="agw-view-all">View All</a>
        </div>
        <div class="table-responsive">
            <table class="agw-table">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Note</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentTransactions as $txn)
                    <tr>
                        <td>
                            <span class="agw-type agw-type-{{ $txn->type }}">{{ ucfirst($txn->type) }}</span>
                        </td>
                        <td>
                            <span class="agw-amount-flow agw-amount-{{ $txn->type }}">{{ $txn->formatted_amount }}</span>
                        </td>
                        <td><span class="agw-meta">{{ $txn->note ?? '—' }}</span></td>
                        <td><span class="agw-meta">{{ $txn->created_at->format('d M Y, h:i A') }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="agw-empty-cell">No transactions yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .agw-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .agw-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 4px;
        }
        .agw-subtitle {
            font-size: 13px;
            color: var(--bs-secondary-color);
            margin: 0;
        }
        .agw-back-btn {
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
        .agw-back-btn:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }

        /* ===== BALANCE STATS ===== */
        .agw-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .agw-stat {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .agw-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            flex-shrink: 0;
        }
        .agw-icon-accent { background: #E3F0FF; color: #0C6DFD; }
        .agw-icon-red    { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .agw-icon-accent { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .agw-icon-red    { background: #2e0a0a; color: #f08080; }

        .agw-stat-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--bs-body-color);
            line-height: 1;
        }
        .agw-stat-label {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin-top: 3px;
        }

        /* ===== CREDIT / DEBIT FORM GRID ===== */
        .agw-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }
        .agw-form-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
        }
        .agw-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 1.1rem;
            padding-bottom: .85rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .agw-title-accent { color: #0C6DFD; }
        .agw-title-accent i { color: #0C6DFD; }
        .agw-title-red { color: #A32D2D; }
        .agw-title-red i { color: #E24B4A; }
        [data-bs-theme="dark"] .agw-title-accent { color: #6ba8ff; }
        [data-bs-theme="dark"] .agw-title-red { color: #f08080; }

        .agw-field { margin-bottom: 1rem; }
        .agw-field:last-of-type { margin-bottom: 1.25rem; }
        .agw-field label {
            font-size: 12px;
            font-weight: 500;
            color: var(--bs-secondary-color);
            margin-bottom: 6px;
            display: block;
        }
        .agw-required { color: #A32D2D; }

        .agw-form-card .form-control {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
        }
        .agw-form-card .form-control:focus {
            border-color: #0C6DFD;
            box-shadow: 0 0 0 3px rgba(12, 109, 253, .12);
        }

        /* ===== BUTTONS ===== */
        .agw-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid var(--bs-border-color);
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s, background .15s, color .15s;
        }
        .agw-btn-accent {
            width: 100%;
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .agw-btn-accent:hover { opacity: .9; color: #fff; }
        .agw-btn-red {
            background: #E24B4A;
            border-color: #E24B4A;
            color: #fff;
        }
        .agw-btn-red:hover { opacity: .9; color: #fff; }
        .agw-grid .agw-btn-red { width: 100%; }
        .agw-btn-outline {
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
        }
        .agw-btn-outline:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }

        /* ===== CARD ===== */
        .agw-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }
        .agw-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .agw-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0;
        }
        .agw-card-title i {
            font-size: 16px;
            color: #0C6DFD;
        }
        .agw-pending-badge {
            background: #FAEEDA;
            color: #633806;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }
        [data-bs-theme="dark"] .agw-pending-badge { background: #2e1e05; color: #f0b054; }
        .agw-view-all {
            font-size: 12px;
            font-weight: 500;
            color: #0C6DFD;
            text-decoration: none;
            white-space: nowrap;
        }
        .agw-view-all:hover { text-decoration: underline; }

        /* ===== TABLE ===== */
        .agw-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .agw-table thead th {
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
        .agw-table tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--bs-border-color);
            vertical-align: middle;
            color: var(--bs-body-color);
        }
        .agw-table tbody tr:last-child td { border-bottom: none; }
        .agw-table tbody tr:hover td { background: var(--bs-tertiary-bg); }

        .agw-meta { font-size: 12px; color: var(--bs-secondary-color); }
        .agw-amount { font-size: 13px; font-weight: 600; color: var(--bs-body-color); }

        /* ===== TOP-UP STATUS ===== */
        .agw-status {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .agw-status-pending  { background: #FAEEDA; color: #633806; }
        .agw-status-approved { background: #E3F0FF; color: #0C6DFD; }
        .agw-status-rejected { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .agw-status-pending  { background: #2e1e05; color: #f0b054; }
        [data-bs-theme="dark"] .agw-status-approved { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .agw-status-rejected { background: #2e0a0a; color: #f08080; }

        /* ===== TRANSACTION TYPE / AMOUNT ===== */
        .agw-type {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .agw-type-credit { background: #E3F0FF; color: #0C6DFD; }
        .agw-type-debit  { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .agw-type-credit { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .agw-type-debit  { background: #2e0a0a; color: #f08080; }

        .agw-amount-flow { font-size: 13px; font-weight: 600; }
        .agw-amount-credit { color: #0C6DFD; }
        .agw-amount-debit  { color: #A32D2D; }
        [data-bs-theme="dark"] .agw-amount-credit { color: #6ba8ff; }
        [data-bs-theme="dark"] .agw-amount-debit  { color: #f08080; }

        /* ===== ACTION BUTTONS ===== */
        .agw-actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .agw-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid var(--bs-border-color);
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s, background .15s, color .15s;
        }
        .agw-action-btn:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            text-decoration: none;
        }
        .agw-action-approve {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .agw-action-approve:hover { opacity: .85; color: #fff; }
        .agw-action-reject {
            background: #E24B4A;
            border-color: #E24B4A;
            color: #fff;
        }
        .agw-action-reject:hover { opacity: .85; color: #fff; }

        /* ===== EMPTY CELL ===== */
        .agw-empty-cell {
            text-align: center;
            padding: 2rem 1rem;
            font-size: 13px;
            color: var(--bs-secondary-color);
        }

        /* ===== MODAL ===== */
        .agw-modal {
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
        }
        .agw-modal .modal-header,
        .agw-modal .modal-footer {
            border-color: var(--bs-border-color);
        }
        .agw-modal .form-control {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .agw-stats { grid-template-columns: 1fr; }
            .agw-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .agw-header { align-items: flex-start; }
            .agw-card-header { flex-wrap: wrap; }
        }
    </style>
@endpush
