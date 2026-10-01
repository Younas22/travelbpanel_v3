<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('agent_id', auth()->id())->latest()->get();
        return view('agent.support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject'     => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'required|in:low,normal,high,urgent',
            'attachment'  => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,zip',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('assets/uploads/support');
            if (!is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, $filename);
            $attachmentPath = $filename;
        }

        $ticket = SupportTicket::create([
            'ticket_number'     => 'TKT-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
            'agent_id'          => auth()->id(),
            'subject'           => $request->subject,
            'description'       => $request->description,
            'priority'          => $request->priority,
            'status'            => 'open',
            'attachment'        => $attachmentPath,
            'last_activity_at'  => now(),
        ]);

        return back()->with('success', "Ticket #{$ticket->ticket_number} submitted successfully. We will get back to you soon.");
    }

    public function show(SupportTicket $ticket)
    {
        abort_unless($ticket->agent_id === auth()->id(), 403);

        $ticket->load('replies.sender');

        return view('agent.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->agent_id === auth()->id(), 403);

        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->replies()->create([
            'sender_id' => auth()->id(),
            'message'   => $request->message,
        ]);

        $ticket->update([
            'status'            => in_array($ticket->status, ['resolved', 'closed']) ? 'open' : $ticket->status,
            'last_activity_at'  => now(),
        ]);

        return back()->with('success', 'Reply sent.');
    }
}
