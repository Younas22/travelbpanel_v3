@extends('admin-modern.layouts.app')
@section('title', 'Agent Wallet — ' . $agent->full_name)

@section('content')

@php
    $activeCurrency = activeCurrency();
    $walletCurrency = $wallet->currency ?? 'PKR';
    $displayCurrency = $activeCurrency->currency_name ?? $walletCurrency;
    $toDisplay = fn($amt) => $activeCurrency ? convertCurrency($amt ?? 0, $walletCurrency, $displayCurrency) : ($amt ?? 0);
@endphp

    <div class="agw-header">
        <div>
            <h2 class="agw-title">Agent Wallet</h2>
            <p class="agw-subtitle">{{ $agent->full_name }} ({{ $agent->agent_code }})</p>
        </div>
        <a href="{{ route('admin.agents.show', $agent) }}" class="agw-back-btn"><i class="bi bi-arrow-left"></i> Back to Agent</a>
    </div>

    <div class="agw-stats">
        <div class="agw-stat">
            <div class="agw-stat-icon agw-icon-accent"><i class="bi bi-wallet2"></i></div>
            <div><div class="agw-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->balance), 2) }}</div><div class="agw-stat-label">Current Balance</div></div>
        </div>
        <div class="agw-stat">
            <div class="agw-stat-icon agw-icon-accent"><i class="bi bi-arrow-down-circle"></i></div>
            <div><div class="agw-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->total_credited), 2) }}</div><div class="agw-stat-label">Total Credited</div></div>
        </div>
        <div class="agw-stat">
            <div class="agw-stat-icon agw-icon-red"><i class="bi bi-arrow-up-circle"></i></div>
            <div><div class="agw-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet->total_debited), 2) }}</div><div class="agw-stat-label">Total Debited</div></div>
        </div>
    </div>

    <div class="agw-grid">
        <div class="agw-form-card">
            <h5 class="agw-section-title agw-title-accent"><i class="bi bi-plus-circle"></i> Add Balance (Credit)</h5>
            <form method="POST" action="{{ route('admin.agents.wallet.credit', $agent) }}">
                @csrf
                <div class="agw-field"><label>Amount ({{ $walletCurrency }})</label><input type="number" name="amount" class="form-control" min="1" step="1" required></div>
                <div class="agw-field"><label>Payment Method</label><input type="text" name="payment_method" class="form-control" placeholder="e.g. Bank Transfer, Cash"></div>
                <div class="agw-field"><label>Note</label><textarea name="note" class="form-control" rows="2"></textarea></div>
                <button type="submit" class="agw-btn agw-btn-accent"><i class="bi bi-plus-circle"></i> Add Balance</button>
            </form>
        </div>

        <div class="agw-form-card">
            <h5 class="agw-section-title agw-title-red"><i class="bi bi-dash-circle"></i> Deduct Balance (Debit)</h5>
            <form method="POST" action="{{ route('admin.agents.wallet.debit', $agent) }}">
                @csrf
                <div class="agw-field"><label>Amount ({{ $walletCurrency }})</label><input type="number" name="amount" class="form-control" min="1" step="1" required></div>
                <div class="agw-field"><label>Reason <span class="agw-required">*</span></label><textarea name="note" class="form-control" rows="2" required></textarea></div>
                <button type="submit" class="agw-btn agw-btn-red" onclick="return confirm('Deduct from wallet?')"><i class="bi bi-dash-circle"></i> Deduct Balance</button>
            </form>
        </div>
    </div>

    <div class="agw-card">
        <div class="agw-card-header">
            <h5 class="agw-card-title"><i class="bi bi-receipt"></i> Top-Up Requests</h5>
            @if($topupRequests->where('status', 'pending')->count())
                <span class="agw-pending-badge">{{ $topupRequests->where('status', 'pending')->count() }} Pending</span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="agw-table">
                <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Proof</th><th>Note</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($topupRequests as $req)
                    <tr>
                        <td><span class="agw-meta">{{ $req->created_at->format('d M Y, h:i A') }}</span></td>
                        <td><span class="agw-amount">{{ $displayCurrency }} {{ number_format($toDisplay($req->amount), 2) }}</span></td>
                        <td>{{ $req->payment_method ?? '—' }}</td>
                        <td>
                            @if($req->proof_url)
                                <a href="{{ $req->proof_url }}" target="_blank" class="agw-action-btn">View</a>
                            @else
                                <span class="agw-meta">—</span>
                            @endif
                        </td>
                        <td><span class="agw-meta">{{ $req->note ?? '—' }}</span></td>
                        <td><span class="agw-status agw-status-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                        <td>
                            @if($req->status === 'pending')
                                <div class="agw-actions">
                                    <form method="POST" action="{{ route('admin.topup.approve', $req) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="agw-action-btn agw-action-approve" onclick="return confirm('Approve  {{ number_format($req->amount, 2) }} top-up?')">Approve</button>
                                    </form>
                                    <button type="button" class="agw-action-btn agw-action-reject" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">Reject</button>
                                </div>

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
                    <tr><td colspan="7" class="agw-empty-cell">No top-up requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="agw-card">
        <div class="agw-card-header">
            <h5 class="agw-card-title"><i class="bi bi-clock-history"></i> Recent Transactions</h5>
            <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="agw-view-all">View All</a>
        </div>
        <div class="table-responsive">
            <table class="agw-table">
                <thead><tr><th>Type</th><th>Amount</th><th>Note</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($recentTransactions as $txn)
                    <tr>
                        <td><span class="agw-type agw-type-{{ $txn->type }}">{{ ucfirst($txn->type) }}</span></td>
                        <td><span class="agw-amount-flow agw-amount-{{ $txn->type }}">{{ $txn->type === 'credit' ? '+' : '-' }} {{ $displayCurrency }} {{ number_format($toDisplay($txn->amount), 2) }}</span></td>
                        <td><span class="agw-meta">{{ $txn->note ?? '—' }}</span></td>
                        <td><span class="agw-meta">{{ $txn->created_at->format('d M Y, h:i A') }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="agw-empty-cell">No transactions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
