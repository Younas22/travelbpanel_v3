<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = collect(); // placeholder until SupportTicket model is created
        return view('user.support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject'     => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'required|in:low,normal,high,urgent',
            'attachment'  => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,zip',
        ]);

        // TODO: save to support_tickets table
        return back()->with('success', 'Ticket submitted successfully. We will get back to you soon.');
    }
}
