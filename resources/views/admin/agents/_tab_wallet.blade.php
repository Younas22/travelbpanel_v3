@php
    $wallet = $agent->wallet;
    $walletCurrency = ($wallet?->currency && $wallet->currency !== 'PKR') ? $wallet->currency : 'USD';
    $currencies = $currencies ?? allCurrencies();
@endphp

    <!-- ===== BALANCE STATS ===== -->
<div class="wal-stats">
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-blue"><i class="bi bi-wallet2"></i></div>
        <div>
            <div class="wal-stat-value">{{ $walletCurrency }} {{ number_format($wallet?->balance ?? 0, 2) }}</div>
            <div class="wal-stat-label">Current Balance</div>
        </div>
    </div>
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-accent"><i class="bi bi-arrow-down-circle"></i></div>
        <div>
            <div class="wal-stat-value">{{ $walletCurrency }} {{ number_format($wallet?->total_credited ?? 0, 2) }}</div>
            <div class="wal-stat-label">Total Credited</div>
        </div>
    </div>
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-red"><i class="bi bi-arrow-up-circle"></i></div>
        <div>
            <div class="wal-stat-value">{{ $walletCurrency }} {{ number_format($wallet?->total_debited ?? 0, 2) }}</div>
            <div class="wal-stat-label">Total Debited</div>
        </div>
    </div>
</div>

<!-- ===== CREDIT / DEBIT FORMS ===== -->
<div class="wal-grid">

    {{-- Add Balance --}}
    <div class="as-card">
        <h5 class="wal-section-title wal-title-accent"><i class="bi bi-plus-circle"></i> Add Balance (Credit)</h5>
        <form method="POST" action="{{ route('admin.agents.wallet.credit', $agent) }}">
            @csrf
            <div class="wal-field">
                <label>Amount</label>
                <input type="number" name="amount" class="form-control" min="1" step="1" required>
            </div>
            <div class="wal-field">
                <label>Currency</label>
                <select name="currency" class="form-control" required>
                    @foreach($currencies as $c)
                        <option value="{{ $c->currency_name }}" {{ $c->currency_name === $walletCurrency ? 'selected' : '' }}>{{ $c->currency_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="wal-field">
                <label>Payment Method</label>
                <input type="text" name="payment_method" class="form-control" placeholder="e.g. Bank Transfer, Cash">
            </div>
            <div class="wal-field">
                <label>Note</label>
                <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
            </div>
            <button type="submit" class="wal-btn wal-btn-accent">
                <i class="bi bi-plus-circle"></i> Add Balance
            </button>
        </form>
    </div>

    {{-- Deduct Balance --}}
    <div class="as-card">
        <h5 class="wal-section-title wal-title-red"><i class="bi bi-dash-circle"></i> Deduct Balance (Debit)</h5>
        <form method="POST" action="{{ route('admin.agents.wallet.debit', $agent) }}">
            @csrf
            <div class="wal-field">
                <label>Amount</label>
                <input type="number" name="amount" class="form-control" min="1" step="1" required>
            </div>
            <div class="wal-field">
                <label>Reason <span class="wal-required">*</span></label>
                <textarea name="note" class="form-control" rows="2" placeholder="Reason for deduction..." required></textarea>
            </div>
            <button type="submit" class="wal-btn wal-btn-red"
                    onclick="return confirm('Deduct from wallet?')">
                <i class="bi bi-dash-circle"></i> Deduct Balance
            </button>
        </form>
    </div>
</div>

<!-- ===== TRANSACTION HISTORY ===== -->
<div class="wal-history-row" style="gap:10px;">
    @if(($wallet?->balance ?? 0) > 0)
    <button type="button" class="wal-history-btn" style="border-color:#E24B4A; color:#E24B4A;"
            data-bs-toggle="modal" data-bs-target="#zeroBalanceModal{{ $agent->id }}">
        <i class="bi bi-arrow-counterclockwise"></i> Zero Out Balance
    </button>
    @endif
    <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="wal-history-btn">
        <i class="bi bi-clock-history"></i> View Full Transaction History
    </a>
</div>

@if(($wallet?->balance ?? 0) > 0)
<!-- ===== ZERO BALANCE MODAL ===== -->
<div class="modal fade" id="zeroBalanceModal{{ $agent->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.agents.wallet.zero', $agent) }}">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">Zero Out Wallet Balance</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.85rem;">
                        This will reset the current balance
                        (<strong>{{ $walletCurrency }} {{ number_format($wallet?->balance ?? 0, 2) }}</strong>)
                        down to zero. This action is recorded in the transaction history below.
                    </p>
                    <label class="form-label">Reason <span class="wal-required">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Why is this balance being zeroed out?" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="wal-btn wal-btn-accent" style="width:auto; background:#e5e7eb; color:#374151;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="wal-btn wal-btn-red" style="width:auto;"
                            onclick="return confirm('Reset this wallet balance to zero?')">Zero Out Balance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
