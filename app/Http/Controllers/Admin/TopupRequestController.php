<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentTopupRequest;
use App\Models\AgentWallet;
use Illuminate\Http\Request;

class TopupRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentTopupRequest::with('agent')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->pending(); // default: show pending
        }

        $requests     = $query->paginate(20);
        $pendingCount = AgentTopupRequest::pending()->count();

        return view('admin.topup-requests.index', compact('requests', 'pendingCount'));
    }

    public function show(AgentTopupRequest $topupRequest)
    {
        $topupRequest->load('agent', 'reviewedBy');
        return view('admin.topup-requests.show', compact('topupRequest'));
    }

    public function approve(Request $request, AgentTopupRequest $topupRequest)
    {
        if ($topupRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $wallet = AgentWallet::firstOrCreate(
            ['agent_id' => $topupRequest->agent_id],
        );

        // Add balance
        $wallet->credit(
            amount:        $topupRequest->amount,
            note:          'Top-up request #' . $topupRequest->id . ' approved',
            performedBy:   auth()->id(),
            paymentMethod: $topupRequest->payment_method,
            reference:     'TOPUP-' . $topupRequest->id,
        );

        // Update request status
        $topupRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Top-up request approved. PKR ' . number_format($topupRequest->amount, 2) . ' added to agent wallet.');
    }

    public function reject(Request $request, AgentTopupRequest $topupRequest)
    {
        $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        if ($topupRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $topupRequest->update([
            'status'         => 'rejected',
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'rejection_note' => $request->rejection_note,
        ]);

        return back()->with('success', 'Top-up request rejected.');
    }
}
