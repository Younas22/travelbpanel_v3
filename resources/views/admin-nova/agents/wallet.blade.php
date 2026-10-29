@extends('admin-nova.layouts.app')

@section('title', 'Agent Wallet — ' . $agent->full_name)

@php
    // Same display rule as the rest of the agent pages: stored "PKR" wallets show as USD.
    $walletCurrency = ($wallet?->currency && $wallet->currency !== 'PKR') ? $wallet->currency : 'USD';
    $money = fn ($v) => $walletCurrency . ' ' . number_format((float) $v, 2);
    $initials = collect(explode(' ', $agent->full_name))->filter()->map(fn ($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') ?: '?';
    $statusLabel = ucfirst($agent->approval_status ?? 'unknown');
    $tabs = [
        ['label' => 'Overview', 'icon' => 'bi-person', 'url' => route('admin.agents.show', $agent)],
        ['label' => 'Permissions', 'icon' => 'bi-shield', 'url' => route('admin.agents.permissions', $agent)],
        ['label' => 'Wallet', 'icon' => 'bi-wallet2', 'url' => route('admin.agents.wallet', $agent), 'active' => true],
        ['label' => 'Bookings', 'icon' => 'bi-calendar3', 'url' => route('admin.agents.show', $agent) . '#bookings'],
    ];
    $pendingTopups = $topupRequests->where('status', 'pending')->count();
    $filterUrl = fn (array $params) => request()->fullUrlWithQuery($params);
@endphp

@section('content')
<div class="tbp-agent-wallet-page" id="tbpAgentWallet">

    {{-- ============ PROFILE HEADER + TABS ============ --}}
    <div class="tbp-agent-wallet-header">
        <div class="tbp-agent-wallet-header-row">
            <div class="tbp-agent-wallet-profile">
                <span class="tbp-agent-wallet-avatar">{{ $initials }}</span>
                <div>
                    <div class="tbp-agent-wallet-name">
                        <h1>{{ $agent->full_name }}</h1>
                        <span class="tbp-agent-wallet-status is-{{ $agent->approval_status }}">{{ $statusLabel }}</span>
                    </div>
                    <p class="tbp-agent-wallet-meta">{{ $agent->agent_code }} <span>•</span> {{ $agent->email }}</p>
                </div>
            </div>
            <div class="tbp-agent-wallet-header-actions">
                <a href="{{ route('admin.agents.index') }}" class="tbp-agent-wallet-btn"><i class="bi bi-arrow-left"></i> Back</a>
                <a href="{{ route('admin.agents.edit', $agent) }}" class="tbp-agent-wallet-btn is-primary"><i class="bi bi-pencil"></i> Edit Agent</a>
            </div>
        </div>
        <nav class="tbp-agent-wallet-tabs">
            @foreach($tabs as $tab)
                <a href="{{ $tab['url'] }}" class="tbp-agent-wallet-tab {{ !empty($tab['active']) ? 'is-active' : '' }}" @if(!empty($tab['active'])) aria-current="page" @endif>
                    <i class="bi {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- ============ SUMMARY ============ --}}
    <div class="tbp-agent-wallet-summary-grid">
        <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="tbp-agent-wallet-summary-card is-blue">
            <span class="tbp-agent-wallet-summary-icon"><i class="bi bi-wallet2"></i></span>
            <span class="tbp-agent-wallet-summary-text"><span>Current Balance</span><strong>{{ $money($wallet?->balance) }}</strong></span>
            <span class="tbp-agent-wallet-summary-end"><i class="bi bi-chevron-right"></i></span>
        </a>
        <div class="tbp-agent-wallet-summary-card is-green">
            <span class="tbp-agent-wallet-summary-icon"><i class="bi bi-arrow-down"></i></span>
            <span class="tbp-agent-wallet-summary-text"><span>Total Credited</span><strong>{{ $money($wallet?->total_credited) }}</strong></span>
            <span class="tbp-agent-wallet-summary-end"><i class="bi bi-bar-chart-fill"></i></span>
        </div>
        <div class="tbp-agent-wallet-summary-card is-red">
            <span class="tbp-agent-wallet-summary-icon"><i class="bi bi-arrow-up"></i></span>
            <span class="tbp-agent-wallet-summary-text"><span>Total Debited</span><strong>{{ $money($wallet?->total_debited) }}</strong></span>
            <span class="tbp-agent-wallet-summary-end"><i class="bi bi-bar-chart-fill"></i></span>
        </div>
    </div>

    {{-- ============ CREDIT / DEBIT / SIDEBAR ============ --}}
    <div class="tbp-agent-wallet-main-grid">

        <section class="tbp-agent-wallet-credit-card" id="tbpCredit">
            <div class="tbp-agent-wallet-card-head">
                <span class="tbp-agent-wallet-round-icon is-green"><i class="bi bi-plus-lg"></i></span>
                <div>
                    <h2>Add Balance (Credit)</h2>
                    <p>Add funds to the agent's wallet. The amount will be credited to their wallet.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.agents.wallet.credit', $agent) }}" class="tbp-agent-wallet-form" data-guard>
                @csrf
                <div class="tbp-agent-wallet-row">
                    <label class="tbp-agent-wallet-field">
                        <span>Amount <em>*</em></span>
                        <span class="tbp-agent-wallet-amount">
                            <b data-currency-prefix>{{ $walletCurrency }}</b>
                            <input type="number" name="amount" min="1" step="0.01" placeholder="0.00" value="{{ old('amount') }}" required>
                        </span>
                    </label>
                    <label class="tbp-agent-wallet-field">
                        <span>Currency</span>
                        <select name="currency" required data-currency-select>
                            @foreach($currencies as $c)
                                <option value="{{ $c->currency_name }}" {{ old('currency', $walletCurrency) === $c->currency_name ? 'selected' : '' }}>{{ $c->currency_name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <label class="tbp-agent-wallet-field">
                    <span>Payment Method <em>*</em></span>
                    <span class="tbp-agent-wallet-select-icon">
                        <i class="bi bi-credit-card"></i>
                        <select name="payment_method" required>
                            <option value="">Select payment method</option>
                            @foreach($paymentMethods as $m)
                                <option value="{{ $m }}" {{ old('payment_method') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </span>
                </label>
                <label class="tbp-agent-wallet-field">
                    <span>Note (Optional)</span>
                    <textarea name="note" rows="3" maxlength="500" placeholder="Add a note for this transaction..." data-counter>{{ old('note') }}</textarea>
                    <small class="tbp-agent-wallet-counter"><span>0</span>/500</small>
                </label>
                <button type="submit" class="tbp-agent-wallet-submit is-blue"><i class="bi bi-plus-lg"></i> <span>Add Balance</span></button>
            </form>
        </section>

        <section class="tbp-agent-wallet-debit-card" id="tbpDebit">
            <div class="tbp-agent-wallet-card-head">
                <span class="tbp-agent-wallet-round-icon is-red"><i class="bi bi-dash-lg"></i></span>
                <div>
                    <h2 class="is-red">Deduct Balance (Debit)</h2>
                    <p>Deduct funds from the agent's wallet. The amount will be debited from their wallet.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.agents.wallet.debit', $agent) }}" class="tbp-agent-wallet-form" data-guard
                  data-confirm="Deduct this amount from {{ $agent->full_name }}'s wallet?">
                @csrf
                <label class="tbp-agent-wallet-field">
                    <span>Amount <em>*</em></span>
                    <span class="tbp-agent-wallet-amount">
                        <b>{{ $walletCurrency }}</b>
                        <input type="number" name="amount" min="1" step="0.01" max="{{ max(1, (float) ($wallet?->balance ?? 0)) }}" placeholder="0.00" required>
                    </span>
                    <small class="tbp-agent-wallet-hint">Available: {{ $money($wallet?->balance) }}</small>
                </label>
                <label class="tbp-agent-wallet-field">
                    <span>Reason <em>*</em></span>
                    <span class="tbp-agent-wallet-select-icon">
                        <i class="bi bi-file-earmark-text"></i>
                        <select name="reason" required>
                            <option value="">Select reason</option>
                            @foreach($debitReasons as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach
                        </select>
                    </span>
                </label>
                <label class="tbp-agent-wallet-field">
                    <span>Note (Optional)</span>
                    <textarea name="note" rows="3" maxlength="500" placeholder="Add a note for this transaction..." data-counter></textarea>
                    <small class="tbp-agent-wallet-counter"><span>0</span>/500</small>
                </label>
                <button type="submit" class="tbp-agent-wallet-submit is-red" @if(($wallet?->balance ?? 0) <= 0) disabled title="Wallet balance is zero" @endif>
                    <i class="bi bi-dash-lg"></i> <span>Deduct Balance</span>
                </button>
            </form>
        </section>

        <aside class="tbp-agent-wallet-sidebar">
            <div class="tbp-agent-wallet-hero">
                <div class="tbp-agent-wallet-hero-top">
                    <div><strong>Agent Wallet</strong><span>Available Balance</span></div>
                    <span class="tbp-agent-wallet-hero-icon"><i class="bi bi-wallet2"></i></span>
                </div>
                <div class="tbp-agent-wallet-hero-balance">{{ $money($wallet?->balance) }}</div>
                <div class="tbp-agent-wallet-hero-actions">
                    <button type="button" data-focus="#tbpCredit input[name=amount]"><i class="bi bi-plus-lg"></i> Top Up</button>
                    <button type="button" data-focus="#tbpDebit input[name=amount]" class="is-red"><i class="bi bi-dash-lg"></i> Deduct</button>
                    <a href="{{ route('admin.agents.wallet.transactions', $agent) }}"><i class="bi bi-clock-history"></i> View History</a>
                </div>
            </div>
            <ul class="tbp-agent-wallet-info">
                <li><span><i class="bi bi-person"></i> Agent Code</span><strong>{{ $agent->agent_code ?: '—' }}</strong></li>
                <li><span><i class="bi bi-building"></i> Company</span><strong>{{ $agent->company_name ?: '—' }}</strong></li>
                <li><span><i class="bi bi-check-circle"></i> Status</span><strong><span class="tbp-agent-wallet-status is-{{ $agent->approval_status }}">{{ $statusLabel }}</span></strong></li>
                <li><span><i class="bi bi-calendar3"></i> Join Date</span><strong>{{ $agent->created_at?->format('d M Y') }}</strong></li>
                <li><span><i class="bi bi-percent"></i> Commission</span><strong>{{ $agent->commission_rate !== null ? rtrim(rtrim(number_format($agent->commission_rate, 2), '0'), '.') : 0 }}%</strong></li>
            </ul>
            @if(($wallet?->balance ?? 0) > 0)
                <button type="button" class="tbp-agent-wallet-zero" data-bs-toggle="modal" data-bs-target="#zeroBalanceModal"><i class="bi bi-arrow-counterclockwise"></i> Zero out balance</button>
            @endif
        </aside>
    </div>

    {{-- ============ TOP-UPS + TRANSACTIONS ============ --}}
    <div class="tbp-agent-wallet-tables">
        <section class="tbp-agent-wallet-topup-section">
            <div class="tbp-agent-wallet-table-head">
                <div class="tbp-agent-wallet-table-title">
                    <i class="bi bi-file-earmark-text"></i>
                    <div>
                        <h3>Top-Up Requests @if($pendingTopups)<span class="tbp-agent-wallet-pill">{{ $pendingTopups }} pending</span>@endif</h3>
                        <p>Manage agent top-up requests and approvals.</p>
                    </div>
                </div>
                <select class="tbp-agent-wallet-filter" data-nav aria-label="Filter top-up requests by status">
                    @foreach(['' => 'All Statuses', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $label)
                        <option value="{{ $filterUrl(['topup_status' => $val ?: null]) }}" {{ request('topup_status', '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tbp-agent-wallet-table-scroll">
                <table class="tbp-agent-wallet-table">
                    <thead><tr><th>#</th><th>Date</th><th>Amount</th><th>Payment Method</th><th>Proof</th><th>Note</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($topupRequests as $req)
                            <tr>
                                <td>{{ $req->id }}</td>
                                <td class="tbp-agent-wallet-nowrap">{{ displayDateTime($req->created_at) }}</td>
                                <td class="tbp-agent-wallet-strong">{{ $money($req->amount) }}</td>
                                <td>{{ $req->payment_method ?? '—' }}</td>
                                <td>@if($req->proof_url)<a href="{{ $req->proof_url }}" target="_blank" class="tbp-agent-wallet-link">View</a>@else — @endif</td>
                                <td class="tbp-agent-wallet-muted">{{ Str::limit($req->note ?? "", 40) ?: '—' }}</td>
                                <td><span class="tbp-agent-wallet-badge is-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                                <td>
                                    @if($req->status === 'pending')
                                        <div class="tbp-agent-wallet-row-actions">
                                            <form method="POST" action="{{ route('admin.topup.approve', $req) }}" onsubmit="return confirm('Approve {{ number_format($req->amount, 2) }} top-up?')">
                                                @csrf
                                                <button type="submit" class="is-green">Approve</button>
                                            </form>
                                            <button type="button" class="is-red" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">Reject</button>
                                        </div>
                                    @else
                                        <span class="tbp-agent-wallet-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8">
                                <div class="tbp-agent-wallet-empty-state">
                                    <i class="bi bi-file-earmark-x"></i>
                                    <strong>{{ request('topup_status') ? 'No ' . request('topup_status') . ' top-up requests.' : 'No top-up requests yet.' }}</strong>
                                    <span>When the agent requests a top-up, it will appear here.</span>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="tbp-agent-wallet-transactions-section">
            <div class="tbp-agent-wallet-table-head">
                <div class="tbp-agent-wallet-table-title">
                    <i class="bi bi-clock-history"></i>
                    <div>
                        <h3>Recent Transactions</h3>
                        <p>Latest wallet transactions for this agent.</p>
                    </div>
                </div>
                <form method="GET" class="tbp-agent-wallet-txn-filters">
                    @foreach(request()->except(['txn_type', 'from', 'to']) as $k => $v)
                        @if(is_string($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                    @endforeach
                    <select name="txn_type" class="tbp-agent-wallet-filter" onchange="this.form.submit()" aria-label="Transaction type">
                        <option value="">All Types</option>
                        <option value="credit" {{ request('txn_type') === 'credit' ? 'selected' : '' }}>Credit</option>
                        <option value="debit" {{ request('txn_type') === 'debit' ? 'selected' : '' }}>Debit</option>
                    </select>
                    <div class="dropdown">
                        <button type="button" class="tbp-agent-wallet-filter" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="bi bi-calendar3"></i>
                            {{ request('from') || request('to') ? (request('from') ?: '…') . ' – ' . (request('to') ?: '…') : 'Select date range' }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-end tbp-agent-wallet-range">
                            <label>From<input type="date" name="from" value="{{ request('from') }}"></label>
                            <label>To<input type="date" name="to" value="{{ request('to') }}"></label>
                            <button type="submit">Apply</button>
                            @if(request('from') || request('to'))<a href="{{ $filterUrl(['from' => null, 'to' => null]) }}">Clear</a>@endif
                        </div>
                    </div>
                    <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="tbp-agent-wallet-link">View All</a>
                </form>
            </div>
            <div class="tbp-agent-wallet-table-scroll">
                <table class="tbp-agent-wallet-table">
                    <thead><tr><th>#</th><th>Type</th><th>Amount</th><th>Note</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($recentTransactions as $txn)
                            <tr>
                                <td>{{ $txn->id }}</td>
                                <td><span class="tbp-agent-wallet-badge is-{{ $txn->type }}">{{ ucfirst($txn->type) }}</span></td>
                                <td class="tbp-agent-wallet-strong is-{{ $txn->type }}">{{ $txn->type === 'credit' ? '+' : '−' }} {{ $txn->currency ?? $walletCurrency }} {{ number_format($txn->amount, 2) }}</td>
                                <td class="tbp-agent-wallet-muted" title="{{ $txn->note }}">{{ Str::limit($txn->note ?? "", 50) ?: '—' }}</td>
                                <td class="tbp-agent-wallet-nowrap">{{ displayDateTime($txn->created_at) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">
                                <div class="tbp-agent-wallet-empty-state">
                                    <i class="bi bi-receipt"></i>
                                    <strong>{{ request()->hasAny(['txn_type', 'from', 'to']) ? 'No transactions match these filters.' : 'No transactions yet.' }}</strong>
                                    <span>All wallet transactions will be shown here.</span>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- ============ MODALS (same routes as before) ============ --}}
    @foreach($topupRequests->where('status', 'pending') as $req)
        <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content tbp-agents-modal">
                    <form method="POST" action="{{ route('admin.topup.reject', $req) }}">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">Reject Top-Up Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_note" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="tbp-agents-modal-btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="tbp-agents-modal-btn is-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    @if(($wallet?->balance ?? 0) > 0)
        <div class="modal fade" id="zeroBalanceModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content tbp-agents-modal">
                    <form method="POST" action="{{ route('admin.agents.wallet.zero', $agent) }}" onsubmit="return confirm('Reset this wallet balance to zero?')">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">Zero Out Wallet Balance</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <p>This resets the current balance (<strong>{{ $money($wallet->balance) }}</strong>) to zero. It is recorded in the transaction history.</p>
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Why is this balance being zeroed out?" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="tbp-agents-modal-btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="tbp-agents-modal-btn is-danger">Zero Out Balance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('tbpAgentWallet');
    if (!root) return;

    // Character counters
    root.querySelectorAll('[data-counter]').forEach(t => {
        const out = t.parentElement.querySelector('.tbp-agent-wallet-counter span');
        const sync = () => { out.textContent = t.value.length; };
        t.addEventListener('input', sync); sync();
    });

    // Credit amount prefix follows the selected currency
    const curSel = root.querySelector('[data-currency-select]');
    const prefix = root.querySelector('[data-currency-prefix]');
    curSel?.addEventListener('change', () => { prefix.textContent = curSel.value; });

    // Confirm (debit) + block double submits
    root.querySelectorAll('form[data-guard]').forEach(form => form.addEventListener('submit', e => {
        if (form.dataset.confirm && !confirm(form.dataset.confirm)) { e.preventDefault(); return; }
        const btn = form.querySelector('[type=submit]');
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Processing…';
        btn.querySelector('i').className = 'bi bi-arrow-repeat tbp-agent-wallet-spin';
    }));

    // Top Up / Deduct shortcuts
    root.querySelectorAll('[data-focus]').forEach(b => b.addEventListener('click', () => {
        const input = root.querySelector(b.dataset.focus);
        input?.closest('section').scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => input?.focus(), 350);
    }));

    root.querySelectorAll('select[data-nav]').forEach(s => s.addEventListener('change', () => { window.location = s.value; }));
})();
</script>
@endpush
