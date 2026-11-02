<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedFlight;
use App\Models\TravelPartner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Manual entries for "Featured Flight Deals" (Suppliers → Flight → Featured Flights). */
class FeaturedFlightController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $flights = FeaturedFlight::query()
            ->when($search !== '', fn($q) => $q->where(fn($w) => $w
                ->where('from_code', 'like', "%{$search}%")->orWhere('from_city', 'like', "%{$search}%")
                ->orWhere('to_code', 'like', "%{$search}%")->orWhere('to_city', 'like', "%{$search}%")
                ->orWhere('airline', 'like', "%{$search}%")->orWhere('airline_code', 'like', "%{$search}%")))
            ->when(in_array($status, ['0', '1'], true), fn($q) => $q->where('status', $status))
            ->orderBy('sort_order')->orderBy('id')
            ->paginate(20)->withQueryString();

        $stats = [
            'total'    => FeaturedFlight::count(),
            'active'   => FeaturedFlight::where('status', true)->count(),
            'inactive' => FeaturedFlight::where('status', false)->count(),
        ];
        $partner = TravelPartner::where('company_name', FeaturedFlight::PARTNER_NAME)->where('supplier_type', 'manual')->first();
        $enabled = FeaturedFlight::enabled();

        return view('admin.featured-flights.index', compact('flights', 'stats', 'search', 'status', 'partner', 'enabled'));
    }

    public function create()
    {
        $flight = new FeaturedFlight(['cabin' => 'economy', 'days_ahead' => 7, 'status' => true, 'sort_order' => (int) FeaturedFlight::max('sort_order') + 1]);

        return view('admin.featured-flights.form', compact('flight'));
    }

    public function store(Request $request)
    {
        FeaturedFlight::create($this->validated($request));

        return redirect()->route('admin.featured-flights.index')->with('success', 'Featured flight added successfully!');
    }

    public function edit(FeaturedFlight $featuredFlight)
    {
        return view('admin.featured-flights.form', ['flight' => $featuredFlight]);
    }

    public function update(Request $request, FeaturedFlight $featuredFlight)
    {
        $featuredFlight->update($this->validated($request));

        return redirect()->route('admin.featured-flights.index')->with('success', 'Featured flight updated successfully!');
    }

    public function destroy(FeaturedFlight $featuredFlight)
    {
        $featuredFlight->delete();

        return redirect()->route('admin.featured-flights.index')->with('success', 'Featured flight deleted successfully!');
    }

    public function toggleStatus(FeaturedFlight $featuredFlight)
    {
        $featuredFlight->update(['status' => !$featuredFlight->status]);

        return response()->json([
            'success' => true,
            'status'  => $featuredFlight->status,
            'message' => 'Featured flight ' . ($featuredFlight->status ? 'activated' : 'deactivated') . '.',
        ]);
    }

    private function validated(Request $request): array
    {
        foreach (['from_code', 'to_code', 'airline_code'] as $code) {
            $request->merge([$code => strtoupper(trim((string) $request->input($code)))]);
        }

        $data = $request->validate([
            'from_code'    => 'required|alpha|size:3',
            'from_city'    => 'required|string|max:100',
            'to_code'      => 'required|alpha|size:3|different:from_code',
            'to_city'      => 'required|string|max:100',
            'airline'      => 'required|string|max:100',
            'airline_code' => 'required|alpha_num|between:2,3',
            'price'        => 'required|numeric|min:0|max:99999999',
            'cabin'        => ['required', Rule::in(array_keys(FeaturedFlight::CABINS))],
            'days_ahead'   => 'required|integer|min:0|max:365',
            'sort_order'   => 'nullable|integer|min:0',
            'status'       => 'nullable|boolean',
        ], [], [
            'from_code' => 'from airport code', 'to_code' => 'to airport code', 'airline_code' => 'airline code',
            'days_ahead' => 'departure (days from today)', 'price' => 'starting price',
        ]);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['status'] = $request->boolean('status');

        return $data;
    }
}
