<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TravelPartner;
use App\Services\WebbedsCityImporter;
use Illuminate\Http\Request;

class WebbedsCityImportController extends Controller
{
    public function __construct(private WebbedsCityImporter $importer) {}

    private function guard(TravelPartner $partner): void
    {
        abort_unless(strtolower(trim($partner->company_name)) === 'webbeds', 404);
    }

    public function stats(TravelPartner $partner)
    {
        $this->guard($partner);
        return response()->json(['success' => true, 'stats' => $this->importer->stats()]);
    }

    public function cities(Request $request, TravelPartner $partner)
    {
        $this->guard($partner);
        $data = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:all,imported,pending,failed,outdated',
            'page' => 'nullable|integer|min:1',
        ]);
        return response()->json(['success' => true] + $this->importer->cities(
            trim($data['search'] ?? ''), $data['status'] ?? 'all', (int) ($data['page'] ?? 1)
        ));
    }

    public function importCity(TravelPartner $partner, string $code)
    {
        $this->guard($partner);
        abort_unless(ctype_digit($code), 422, 'Invalid city code.');
        @set_time_limit(300);
        return response()->json($this->importer->importCity($code));
    }

    public function runBatch(TravelPartner $partner)
    {
        $this->guard($partner);
        @set_time_limit(300);
        return response()->json($this->importer->runBatch(25, 'admin'));
    }

    public function regenerateToken(TravelPartner $partner)
    {
        $this->guard($partner);
        $token = $this->importer->cronToken(true);
        return response()->json(['success' => true, 'cron_url' => route('cron.webbeds.city-import', $token)]);
    }

    /** Public cron endpoint — protected by the secret token in the URL. */
    public function cron(string $token)
    {
        abort_unless(hash_equals($this->importer->cronToken(), $token), 403);
        @set_time_limit(400);
        ignore_user_abort(true);
        return response()->json($this->importer->runBatch(50, 'cron'));
    }
}
