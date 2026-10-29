<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentWallet;
use App\Models\AgentWalletTransaction;
use App\Models\AgentTopupRequest;
use Illuminate\Http\Request;

class AgentWalletController extends Controller
{
    public const DEBIT_REASONS = ['Booking adjustment', 'Refund reversal', 'Service fee', 'Penalty / charge', 'Balance correction', 'Other'];
    public const PAYMENT_METHODS = ['Bank Transfer', 'Cash', 'Card', 'Cheque', 'Online Payment', 'Other'];

    public function index(Request $request, User $agent)
    {
        $wallet = $agent->wallet ?? AgentWallet::create(['agent_id' => $agent->id]);

        $txnQuery = AgentWalletTransaction::byAgent($agent->id);
        if (in_array($request->query('txn_type'), ['credit', 'debit'], true)) {
            $txnQuery->where('type', $request->query('txn_type'));
        }
        if ($request->filled('from') && strtotime($request->query('from'))) {
            $txnQuery->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->filled('to') && strtotime($request->query('to'))) {
            $txnQuery->whereDate('created_at', '<=', $request->query('to'));
        }
        $recentTransactions = $txnQuery->latest()->take(10)->get();

        $topupQuery = AgentTopupRequest::byAgent($agent->id);
        if (in_array($request->query('topup_status'), ['pending', 'approved', 'rejected'], true)) {
            $topupQuery->where('status', $request->query('topup_status'));
        }
        $topupRequests = $topupQuery->latest()->take(10)->get();

        $currencies = allCurrencies();
        $debitReasons = self::DEBIT_REASONS;
        $paymentMethods = self::PAYMENT_METHODS;

        return view('admin.agents.wallet', compact('agent', 'wallet', 'recentTransactions', 'topupRequests', 'currencies', 'debitReasons', 'paymentMethods'));
    }

    public function credit(Request $request, User $agent)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'currency'       => 'required|string|exists:currencies,currency_name',
            'payment_method' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
        ]);

        $wallet = $agent->wallet ?? AgentWallet::create(['agent_id' => $agent->id]);

        $wallet->credit(
            amount:        $request->amount,
            note:          $request->note ?? 'Manual credit by admin',
            performedBy:   auth()->id(),
            paymentMethod: $request->payment_method,
            currency:      $request->currency,
        );

        return back()->with('success', $wallet->currency . ' ' . number_format($request->amount, 2) . ' added to wallet.');
    }

    public function debit(Request $request, User $agent)
    {
        // The redesigned wallet page sends a reason (+ optional note); the
        // agent show page's Wallet tab still sends just a required note.
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string|in:' . implode(',', self::DEBIT_REASONS),
            'note'   => 'required_without:reason|nullable|string|max:500',
        ]);

        $wallet = $agent->wallet;

        if (!$wallet || !$wallet->hasSufficientBalance($request->amount)) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        $note = $request->filled('reason')
            ? $request->reason . ($request->filled('note') ? ' — ' . $request->note : '')
            : $request->note;

        $wallet->debit(
            amount:      $request->amount,
            note:        $note,
            performedBy: auth()->id(),
        );

        return back()->with('success', $wallet->currency . ' ' . number_format($request->amount, 2) . ' deducted from wallet.');
    }

    public function zero(Request $request, User $agent)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $wallet = $agent->wallet;

        if (!$wallet || (float) $wallet->balance <= 0) {
            return back()->with('error', 'This wallet is already at zero.');
        }

        $wallet->debit(
            amount:      $wallet->balance,
            note:        'Balance zeroed out by admin — Reason: ' . $request->reason,
            performedBy: auth()->id(),
        );

        return back()->with('success', 'Wallet balance has been reset to zero.');
    }

    public function transactions(Request $request, User $agent)
    {
        $query = AgentWalletTransaction::byAgent($agent->id)->with('performedBy');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->byDateRange($request->from, $request->to . ' 23:59:59');
        } elseif ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        } elseif ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('note', 'like', $term)
                  ->orWhere('reference', 'like', $term)
                  ->orWhereHas('performedBy', fn ($u) => $u->where('first_name', 'like', $term)
                      ->orWhere('last_name', 'like', $term)
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$term]));
            });
        }

        $transactions = $query->latest()->paginate(20);
        $wallet       = $agent->wallet;

        return view('admin.agents.transactions', compact('agent', 'wallet', 'transactions'));
    }
}
