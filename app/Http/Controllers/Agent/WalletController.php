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
        $pendingRequest = AgentTopupRequest::byAgent($agent->id)->pending()->latest()->first();

        return view('agent.wallet.topup', compact('agent', 'pendingRequest'));
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

        AgentTopupRequest::create([
            'agent_id'       => $agent->id,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_proof'  => $proofPath,
            'note'           => $request->note,
            'status'         => 'pending',
        ]);

        return redirect()->route('agent.wallet.index')
                         ->with('success', 'Top-up request submitted. Admin will review shortly.');
    }
}
