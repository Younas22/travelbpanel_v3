<?php

namespace App\Http\Controllers;

use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\SupportTicket;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use Illuminate\Http\Request;

/**
 * Public, no-login lookup — "I have a reference number, let me see it" for
 * anyone on the front site, not just logged-in users/agents. One box takes
 * either a support ticket number (TKT-...) or a booking/invoice reference
 * (the same code shown on a flight/hotel/tour/umrah invoice) — most visitors
 * think of both as "my ticket", so this tries a support ticket first, then
 * falls through to each booking type, and sends a booking match straight to
 * its own existing invoice page rather than duplicating it here.
 *
 * Just the reference number is enough — the same model the invoice pages
 * already use (anyone with that exact, effectively-unguessable reference can
 * open it directly by URL already; this lookup box doesn't add a weaker
 * path than that). The support-ticket route binds by ticket_number (not the
 * numeric id) for the same reason: an auto-increment id is guessable, the
 * ticket number isn't.
 */
class TicketLookupController extends Controller
{
    public function index()
    {
        return view('tickets.lookup');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string',
        ]);

        $reference = trim($request->ticket_number);

        $ticket = SupportTicket::where('ticket_number', $reference)->first();

        if ($ticket) {
            return redirect()->route('ticket.show', $ticket);
        }

        // Not a support ticket — try it as a booking/invoice reference instead.
        $bookingRoutes = [
            FlightBooking::class => 'flight.invoice',
            HotelBooking::class  => 'hotel.invoice',
            TourBooking::class   => 'tour.invoice',
            UmrahBooking::class  => 'umrah.invoice',
        ];

        foreach ($bookingRoutes as $model => $routeName) {
            if ($model::where('booking_code_ref', $reference)->exists()) {
                return redirect()->route($routeName, ['booking_ref' => $reference]);
            }
        }

        return back()->withInput()->with('error', 'No ticket or booking found with that reference number.');
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'agent', 'replies.sender']);

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->replies()->create([
            'sender_id' => $ticket->user_id ?? $ticket->agent_id,
            'message'   => $request->message,
        ]);

        $ticket->update([
            'status'           => in_array($ticket->status, ['resolved', 'closed']) ? 'open' : $ticket->status,
            'last_activity_at' => now(),
        ]);

        return back()->with('success', 'Reply sent.');
    }
}
