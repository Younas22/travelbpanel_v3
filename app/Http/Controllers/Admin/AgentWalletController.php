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
    public function index(User $agent)
    {
        $wallet = $agent->wallet ?? AgentWallet::create(['agent_id' => $agent->id]);
        $recentTransactions = AgentWalletTransaction::byAgent($agent->id)->latest()->take(10)->get();
        $topupRequests = AgentTopupRequest::byAgent($agent->id)->latest()->take(10)->get();

        return view('admin.agents.wallet', compact('agent', 'wallet', 'recentTransactions', 'topupRequests'));
    }

    public function credit(Request $request, User $agent)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
        ]);

        $wallet = $agent->wallet ?? AgentWallet::create(['agent_id' => $agent->id]);

        $wallet->credit(
            amount:        $request->amount,
            note:          $request->note ?? 'Manual credit by admin',
            performedBy:   auth()->id(),
            paymentMethod: $request->payment_method,
        );

        return back()->with('success', $wallet->currency . ' ' . number_format($request->amount, 2) . ' added to wallet.');
    }

    public function debit(Request $request, User $agent)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'required|string|max:500',
        ]);

        $wallet = $agent->wallet;

        if (!$wallet || !$wallet->hasSufficientBalance($request->amount)) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        $wallet->debit(
            amount:      $request->amount,
            note:        $request->note,
            performedBy: auth()->id(),
        );

        return back()->with('success', $wallet->currency . ' ' . number_format($request->amount, 2) . ' deducted from wallet.');
    }

    public function transactions(Request $request, User $agent)
    {
        $query = AgentWalletTransaction::byAgent($agent->id)->with('performedBy');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->byDateRange($request->from, $request->to . ' 23:59:59');
        }

        $transactions = $query->latest()->paginate(20);
        $wallet       = $agent->wallet;

        return view('admin.agents.transactions', compact('agent', 'wallet', 'transactions'));
    }
}
