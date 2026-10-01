<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentTopupRequest;
use App\Models\AgentWalletTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $agent  = auth()->user();
        $wallet = $agent->wallet;
        $recentTransactions = AgentWalletTransaction::byAgent($agent->id)->latest()->take(10)->get();

        return view('agent.wallet.index', compact('agent', 'wallet', 'recentTransactions'));
    }

    public function transactions(Request $request)
    {
        $agent = auth()->user();
        $query = AgentWalletTransaction::byAgent($agent->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->byDateRange($request->from, $request->to . ' 23:59:59');
        }

        $transactions = $query->latest()->paginate(20);
        $wallet = $agent->wallet;

        return view('agent.wallet.transactions', compact('agent', 'wallet', 'transactions'));
    }

    public function topupForm()
    {
        $agent = auth()->user();
        $wallet = $agent->wallet;
        $pendingRequest = AgentTopupRequest::byAgent($agent->id)->pending()->latest()->first();

        return view('agent.wallet.topup', compact('agent', 'wallet', 'pendingRequest'));
    }

    public function topupSubmit(Request $request)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:100',
            'payment_method' => 'required|string|max:100',
            'note'           => 'nullable|string|max:500',
            'payment_proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $agent = auth()->user();

        if (AgentTopupRequest::byAgent($agent->id)->pending()->exists()) {
            return back()->with('error', 'You already have a pending top-up request. Please wait for it to be reviewed.');
        }

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('topup-proofs', 'public');
        }

        // The form collects the amount in whatever currency the site is
        // currently showing (activeCurrency()), but the wallet itself — and
        // admin's approve flow, which credits $topupRequest->amount as-is —
        // always deals in the wallet's own currency. Convert once here so
        // storage stays consistent no matter what currency was on screen.
        $walletCurrency  = $agent->wallet?->currency ?? 'PKR';
        $activeCurrency  = activeCurrency();
        $enteredCurrency = $activeCurrency->currency_name ?? $walletCurrency;
        $amount = $enteredCurrency !== $walletCurrency
            ? convertCurrency($request->amount, $enteredCurrency, $walletCurrency)
            : (float) $request->amount;

        AgentTopupRequest::create([
            'agent_id'       => $agent->id,
            'amount'         => $amount,
            'payment_method' => $request->payment_method,
            'payment_proof'  => $proofPath,
            'note'           => $request->note,
            'status'         => 'pending',
        ]);

        return redirect()->route('agent.wallet.index')
                         ->with('success', 'Top-up request submitted. Admin will review shortly.');
    }
}
