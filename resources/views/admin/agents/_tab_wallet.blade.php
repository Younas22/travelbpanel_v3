@php $wallet = $agent->wallet; @endphp

    <!-- ===== BALANCE STATS ===== -->
<div class="wal-stats">
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-blue"><i class="bi bi-wallet2"></i></div>
        <div>
            <div class="wal-stat-value">PKR {{ number_format($wallet?->balance ?? 0, 2) }}</div>
            <div class="wal-stat-label">Current Balance</div>
        </div>
    </div>
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-accent"><i class="bi bi-arrow-down-circle"></i></div>
        <div>
            <div class="wal-stat-value">PKR {{ number_format($wallet?->total_credited ?? 0, 2) }}</div>
            <div class="wal-stat-label">Total Credited</div>
        </div>
    </div>
    <div class="wal-stat">
        <div class="wal-stat-icon wal-icon-red"><i class="bi bi-arrow-up-circle"></i></div>
        <div>
            <div class="wal-stat-value">PKR {{ number_format($wallet?->total_debited ?? 0, 2) }}</div>
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
                <label>Amount (PKR)</label>
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

    {{-- Deduct Balance --}}
    <div class="as-card">
        <h5 class="wal-section-title wal-title-red"><i class="bi bi-dash-circle"></i> Deduct Balance (Debit)</h5>
        <form method="POST" action="{{ route('admin.agents.wallet.debit', $agent) }}">
            @csrf
            <div class="wal-field">
                <label>Amount (PKR)</label>
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
<div class="wal-history-row">
    <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="wal-history-btn">
        <i class="bi bi-clock-history"></i> View Full Transaction History
    </a>
</div>

@push('styles')
    <style>
        /* ===== BALANCE STATS ===== */
        .wal-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .wal-stat {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .wal-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            flex-shrink: 0;
        }
        .wal-icon-blue,
        .wal-icon-accent { background: #E3F0FF; color: #0C6DFD; }
        .wal-icon-red    { background: #FCEBEB; color: #A32D2D; }
        [data-bs-theme="dark"] .wal-icon-blue,
        [data-bs-theme="dark"] .wal-icon-accent { background: #0a2a4d; color: #6ba8ff; }
        [data-bs-theme="dark"] .wal-icon-red    { background: #2e0a0a; color: #f08080; }

        .wal-stat-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--bs-body-color);
            line-height: 1;
        }
        .wal-stat-label {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin-top: 3px;
        }

        /* ===== FORM GRID ===== */
        .wal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        /* ===== SECTION TITLES ===== */
        .wal-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 1.1rem;
            padding-bottom: .85rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .wal-title-accent { color: #0C6DFD; }
        .wal-title-accent i { color: #0C6DFD; }
        .wal-title-red { color: #A32D2D; }
        .wal-title-red i { color: #E24B4A; }
        [data-bs-theme="dark"] .wal-title-accent { color: #6ba8ff; }
        [data-bs-theme="dark"] .wal-title-red { color: #f08080; }

        /* ===== FIELDS ===== */
        .wal-field { margin-bottom: 1rem; }
        .wal-field:last-of-type { margin-bottom: 1.25rem; }
        .wal-field label {
            font-size: 12px;
            font-weight: 500;
            color: var(--bs-secondary-color);
            margin-bottom: 6px;
            display: block;
        }
        .wal-required { color: #A32D2D; }

        .as-card .form-control {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
        }
        .as-card .form-control:focus {
            border-color: #0C6DFD;
            box-shadow: 0 0 0 3px rgba(12, 109, 253, .12);
        }

        /* ===== ACTION BUTTONS ===== */
        .wal-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            color: #fff;
            transition: opacity .15s;
        }
        .wal-btn:hover { opacity: .9; color: #fff; }
        .wal-btn-accent { background: #0C6DFD; }
        .wal-btn-red    { background: #E24B4A; }

        /* ===== TRANSACTION HISTORY ===== */
        .wal-history-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.25rem;
        }
        .wal-history-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #0C6DFD;
            color: #0C6DFD;
            background: transparent;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .wal-history-btn:hover {
            background: #0C6DFD;
            color: #fff;
            text-decoration: none;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .wal-stats { grid-template-columns: 1fr; }
            .wal-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .wal-history-row { justify-content: stretch; }
            .wal-history-btn { justify-content: center; width: 100%; }
        }
    </style>
@endpush
