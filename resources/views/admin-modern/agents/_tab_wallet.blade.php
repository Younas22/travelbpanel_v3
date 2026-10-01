@php
    $wallet = $agent->wallet;
    $activeCurrency = activeCurrency();
    $walletCurrency = $wallet?->currency ?? 'PKR';
    $displayCurrency = $activeCurrency->currency_name ?? $walletCurrency;
    $toDisplay = fn($amt) => $activeCurrency ? convertCurrency($amt ?? 0, $walletCurrency, $displayCurrency) : ($amt ?? 0);
@endphp

<div class="wal-stats">
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-blue"><i class="bi bi-wallet2"></i></div>
        <div>
            <div class="wal-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->balance), 2) }}</div>
            <div class="wal-stat-label">Current Balance</div>
        </div>
    </div>
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-accent"><i class="bi bi-arrow-down-circle"></i></div>
        <div>
            <div class="wal-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->total_credited), 2) }}</div>
            <div class="wal-stat-label">Total Credited</div>
        </div>
    </div>
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-red"><i class="bi bi-arrow-up-circle"></i></div>
        <div>
            <div class="wal-stat-value">{{ $displayCurrency }} {{ number_format($toDisplay($wallet?->total_debited), 2) }}</div>
            <div class="wal-stat-label">Total Debited</div>
        </div>
    </div>
</div>

<div class="wal-grid">

    <div class="as-card">
        <h5 class="wal-section-title wal-title-accent"><i class="bi bi-plus-circle"></i> Add Balance (Credit)</h5>
        <form method="POST" action="{{ route('admin.agents.wallet.credit', $agent) }}">
            @csrf
            <div class="wal-field">
                <label>Amount ({{ $walletCurrency }})</label>
                <input type="number" name="amount" class="form-control" min="1" step="1" required>
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

    <div class="as-card">
        <h5 class="wal-section-title wal-title-red"><i class="bi bi-dash-circle"></i> Deduct Balance (Debit)</h5>
        <form method="POST" action="{{ route('admin.agents.wallet.debit', $agent) }}">
            @csrf
            <div class="wal-field">
                <label>Amount ({{ $walletCurrency }})</label>
                <input type="number" name="amount" class="form-control" min="1" step="1" required>
            </div>
            <div class="wal-field">
                <label>Reason <span class="wal-required">*</span></label>
                <textarea name="note" class="form-control" rows="2" placeholder="Reason for deduction..." required></textarea>
            </div>
            <button type="submit" class="wal-btn wal-btn-red" onclick="return confirm('Deduct from wallet?')">
                <i class="bi bi-dash-circle"></i> Deduct Balance
            </button>
        </form>
    </div>
</div>

<div class="wal-history-row">
    <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="wal-history-btn">
        <i class="bi bi-clock-history"></i> View Full Transaction History
    </a>
</div>
