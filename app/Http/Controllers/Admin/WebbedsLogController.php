<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TravelPartner;
use App\Services\WebbedsLogger;
use Illuminate\Http\Request;

/** Admin → Suppliers → WebBeds → Logs: list, download and delete WebBeds API logs. */
class WebbedsLogController extends Controller
{
    private function guard(TravelPartner $partner): void
    {
        abort_unless(strtolower(trim($partner->company_name)) === 'webbeds', 404);
    }

    /** Separate admin page (sidebar: WebBeds → Logs). */
    public function page(TravelPartner $partner)
    {
        $this->guard($partner);
        return view('admin.travel-partners.webbeds-page', ['partner' => $partner, 'section' => 'logs']);
    }

    public function index(Request $request, TravelPartner $partner)
    {
        $this->guard($partner);
        $rows   = WebbedsLogger::all();
        $search = strtolower(trim((string) $request->query('search', '')));
        $filter = (string) $request->query('filter', 'all');

        $rows = array_values(array_filter($rows, function ($r) use ($search, $filter) {
            if ($filter === 'errors' && empty($r['errors'])) return false;
            if ($filter === 'bookings' && !in_array('booking', $r['steps'] ?? [], true)) return false;
            if ($search === '') return true;
            $hay = strtolower(implode(' ', [$r['trace'], $r['booking_ref'] ?? '', $r['booking_pnr'] ?? '', $r['hotel_id'] ?? '', $r['last_error'] ?? '']));
            return str_contains($hay, $search);
        }));

        return response()->json(['success' => true, 'logs' => array_slice($rows, 0, 300), 'total' => count($rows)]);
    }

    public function download(TravelPartner $partner, string $trace)
    {
        $this->guard($partner);
        $file = WebbedsLogger::path($trace);
        abort_unless($file, 404);

        return response()->download($file, "webbeds-{$trace}.log", ['Content-Type' => 'text/plain']);
    }

    public function destroy(TravelPartner $partner, string $trace)
    {
        $this->guard($partner);
        $ok = WebbedsLogger::delete($trace);

        return response()->json(['success' => $ok, 'message' => $ok ? 'Log deleted.' : 'Log not found.'], $ok ? 200 : 404);
    }

    public function destroyAll(TravelPartner $partner)
    {
        $this->guard($partner);
        $n = WebbedsLogger::deleteAll();

        return response()->json(['success' => true, 'message' => "{$n} log(s) deleted."]);
    }
}
