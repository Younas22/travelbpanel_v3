{{-- Expects: $agent, $wallet, $recentTransactions, $topupRequests — same
     variables AgentWalletController::index() passes to the Classic partial.
     Shared by show.blade.php's Wallet tab and the standalone wallet.blade.php page. --}}

{{-- ===== BALANCE STATS ===== --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 14.5h1.5"/></svg>
        </div>
        <div>
            <p class="text-lg font-bold text-novatext">{{ number_format($wallet->balance, 2) }}</p>
            <p class="text-[11px] text-novamuted">Current Balance</p>
        </div>
    </div>
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M18 13l-6 6-6-6"/></svg>
        </div>
        <div>
            <p class="text-lg font-bold text-novatext">{{ number_format($wallet->total_credited, 2) }}</p>
            <p class="text-[11px] text-novamuted">Total Credited</p>
        </div>
    </div>
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-novadanger text-white flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
        </div>
        <div>
            <p class="text-lg font-bold text-novatext">{{ number_format($wallet->total_debited, 2) }}</p>
            <p class="text-[11px] text-novamuted">Total Debited</p>
        </div>
    </div>
</div>

{{-- ===== CREDIT / DEBIT FORMS ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

    {{-- Add Balance --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <h3 class="text-sm font-semibold text-novasuccess flex items-center gap-1.5 mb-4">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Add Balance (Credit)
        </h3>
        <form method="POST" action="{{ route('admin.agents.wallet.credit', $agent) }}">
            @csrf
            <div class="mb-3">
                <label class="ag-label">Amount</label>
                <input type="number" name="amount" class="ag-input" min="1" step="1" required>
            </div>
            <div class="mb-3">
                <label class="ag-label">Payment Method</label>
                <input type="text" name="payment_method" class="ag-input" placeholder="e.g. Bank Transfer, Cash">
            </div>
            <div class="mb-4">
                <label class="ag-label">Note</label>
                <textarea name="note" class="ag-input" rows="2"></textarea>
            </div>
            <button type="submit" class="ag-btn-nova w-full py-2.5 text-xs font-semibold" style="background:#22C55E; color:#fff; border-color:#22C55E;">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add Balance
            </button>
        </form>
    </div>

    {{-- Deduct Balance --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <h3 class="text-sm font-semibold text-novadanger flex items-center gap-1.5 mb-4">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
            Deduct Balance (Debit)
        </h3>
        <form method="POST" action="{{ route('admin.agents.wallet.debit', $agent) }}" onsubmit="return confirm('Deduct from wallet?')">
            @csrf
            <div class="mb-3">
                <label class="ag-label">Amount</label>
                <input type="number" name="amount" class="ag-input" min="1" step="1" required>
            </div>
            <div class="mb-4">
                <label class="ag-label">Reason <span class="ag-required">*</span></label>
                <textarea name="note" class="ag-input" rows="2" required></textarea>
            </div>
            <button type="submit" class="ag-btn-nova ag-btn-danger w-full py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                Deduct Balance
            </button>
        </form>
    </div>
</div>

{{-- ===== TOP-UP REQUESTS ===== --}}
<div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <h3 class="text-sm font-semibold text-novatext flex items-center gap-1.5">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l3 3v15H6z"/><path d="M15 3v3h3M9 12h6M9 16h6"/></svg>
            Top-Up Requests
        </h3>
        @if($topupRequests->where('status', 'pending')->count())
            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-600">{{ $topupRequests->where('status', 'pending')->count() }} Pending</span>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="ag-table">
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
                    <td class="text-novamuted whitespace-nowrap">{{ $req->created_at->format('d M Y, h:i A') }}</td>
                    <td class="font-semibold text-novatext">{{ number_format($req->amount, 2) }}</td>
                    <td>{{ $req->payment_method ?? '—' }}</td>
                    <td>
                        @if($req->proof_url)
                            <a href="{{ $req->proof_url }}" target="_blank" class="text-novablue font-semibold">View</a>
                        @else
                            <span class="text-novamuted">—</span>
                        @endif
                    </td>
                    <td class="text-novamuted">{{ $req->note ?? '—' }}</td>
                    <td>
                        @php
                            $topupStatusStyle = [
                                'pending'  => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
                                'approved' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                                'rejected' => ['bg' => 'bg-red-50', 'text' => 'text-novadanger'],
                            ][$req->status] ?? ['bg' => 'bg-slate-100', 'text' => 'text-novamuted'];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $topupStatusStyle['bg'] }} {{ $topupStatusStyle['text'] }}">{{ ucfirst($req->status) }}</span>
                    </td>
                    <td>
                        @if($req->status === 'pending')
                            <div class="flex items-center gap-1.5">
                                <form method="POST" action="{{ route('admin.topup.approve', $req) }}" onsubmit="return confirm('Approve {{ number_format($req->amount, 2) }} top-up?')">
                                    @csrf
                                    <button type="submit" class="ag-btn-nova px-3 py-1.5 text-[11px] font-semibold" style="color:#22C55E; border-color:#22C55E;">Approve</button>
                                </form>
                                <button type="button" class="ag-btn-nova px-3 py-1.5 text-[11px] font-semibold" style="color:#EF4444; border-color:#EF4444;"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">Reject</button>
                            </div>

                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="border-radius:1.5rem; font-family:'Plus Jakarta Sans',sans-serif;">
                                        <form method="POST" action="{{ route('admin.topup.reject', $req) }}">
                                            @csrf
                                            <div class="modal-header" style="border-bottom:1px solid #E5E7EB;">
                                                <h6 class="modal-title" style="font-weight:700;">Reject Top-Up Request</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label" style="font-size:.8rem; font-weight:600;">Reason <span style="color:#EF4444;">*</span></label>
                                                <textarea name="rejection_note" class="form-control" style="border-radius:1rem;" rows="3" required></textarea>
                                            </div>
                                            <div class="modal-footer" style="border-top:1px solid #E5E7EB;">
                                                <button type="button" data-bs-dismiss="modal"
                                                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#F7F8FC; color:#000; border:1px solid #E5E7EB; cursor:pointer;">Cancel</button>
                                                <button type="submit"
                                                        style="display:inline-flex; align-items:center; border-radius:9999px; padding:10px 20px; font-size:13px; font-weight:600; font-family:'Plus Jakarta Sans',sans-serif; background:#EF4444; color:#fff; border:1px solid #EF4444; cursor:pointer;">Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-novamuted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-novamuted py-8">No top-up requests yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== RECENT TRANSACTIONS ===== --}}
<div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <h3 class="text-sm font-semibold text-novatext flex items-center gap-1.5">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            Recent Transactions
        </h3>
        <a href="{{ route('admin.agents.wallet.transactions', $agent) }}" class="text-xs font-semibold text-novablue">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="ag-table">
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
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $txn->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-novadanger' }}">{{ ucfirst($txn->type) }}</span>
                    </td>
                    <td class="font-semibold {{ $txn->type === 'credit' ? 'text-novasuccess' : 'text-novadanger' }}">{{ $txn->formatted_amount }}</td>
                    <td class="text-novamuted">{{ $txn->note ?? '—' }}</td>
                    <td class="text-novamuted whitespace-nowrap">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-novamuted py-8">No transactions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
