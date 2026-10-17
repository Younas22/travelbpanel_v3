<?php
// app/Http/Controllers/Admin/CurrenciesController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currencies;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CurrenciesController extends Controller
{
    /**
     * Display All the Currencies.
     */
    public function index(Request $request)
    {
        $currencies = Currencies::orderBy('id', 'desc')->paginate(10)->withQueryString();
        $stats = [
            'total'     => Currencies::count(),
            'active'    => Currencies::where('currency_status', "1")->count(),
            'inactive'  => Currencies::where('currency_status', "0")->count(),
            'default'   => Currencies::where('currency_default', "1")->count(),
        ];
        $currencylayerApiKey = Setting::getValue('currencylayer_api_key', 'currency');
        $currencylayerLastUpdated = Setting::getValue('currencylayer_last_updated', 'currency');
        return view('admin.currencies.index', compact('currencies', 'stats', 'currencylayerApiKey', 'currencylayerLastUpdated'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::orderBy('name')->get();
        return view('admin.currencies.create', compact('countries'));
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'currency_name'     => 'required|string|max:255',
            'currency_country'  => 'required|string|max:255',
            // Any numeric value is allowed (including negative/zero) — the
            // only thing actually invalid is a bare "-", which Laravel's
            // "numeric" rule already rejects on its own.
            'currency_rate'     => ['required', 'numeric', 'not_in:-'],
            'currency_status'   => 'required|boolean',
            'currency_default'  => 'nullable|boolean',
        ]);

        if (!empty($validated['currency_default']) && $validated['currency_default'] == true) {
            Currencies::where('currency_default', true)->update(['currency_default' => false]);
        }

        $currency = Currencies::create([
            'currency_name'     => $validated['currency_name'],
            'currency_country'  => $validated['currency_country'],
            'currency_rate'     => $validated['currency_rate'],
            'currency_status'   => $validated['currency_status'],
            'currency_default'  => $validated['currency_default'] ?? false,
        ]);

        return redirect()->route('admin.currencies.index')->with('success', 'Currency added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currencies $currency)
    {
        $countries = Country::orderBy('name')->get();
        return view('admin.currencies.edit', compact('currency', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Currencies $currency)
    {
        $validated = $request->validate([
            'currency_name'   => 'required|string|max:255',
            'currency_country'   => 'required|string|max:255',
            'currency_status'   => 'required|boolean',
            'currency_default'  => 'nullable|boolean',
            // Any numeric value is allowed (including negative/zero) — the
            // only thing actually invalid is a bare "-", which Laravel's
            // "numeric" rule already rejects on its own.
            'currency_rate'     => ['required', 'numeric', 'not_in:-'],
        ]);

        $currency->update($validated);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currencies $currency)
    {
        $currency->delete();

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency deleted successfully!');
    }
    /**
     * Toggle the default of a Currency (active/inactive).
     */
    public function toggle_default(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:currencies,id',
        ]);

        $currency = Currencies::findOrFail($request->id);

        //Reset all other currencies to '0'
        Currencies::where('id', '!=', $currency->id)
            ->update(['currency_default' => '0']);

        //Set the selected currency to '1'
        $currency->currency_default = '1';
        $currency->save();

        return response()->json([
            'success' => true,
            'message' => "Currency '{$currency->currency_name}' is now set as default."
        ]);
    }

    /**
     * Toggle the status of a Currency (active/inactive).
     */
    public function toggle_status(Request $request)
    {
        // Validate request
        $request->validate([
            'id' => 'required|exists:currencies,id',
            'currency_active' => 'required|in:0,1', // ensure 0 or 1
        ]);

        $currency = Currencies::findOrFail($request->id);
        $currency->currency_status = (string)$request->currency_active;
        $currency->save();
        $activeCount = Currencies::where('currency_status', '1')->count();
        if ($activeCount == 0) {
            Currencies::query()->update(['currency_default' => '0']);
            Currencies::where('currency_name', 'USD')
                ->update(['currency_status' => '1','currency_default' => '1']);
        }

        return response()->json([
            'success' => true,
            'message' => "Currency '{$currency->currency_name}' status updated successfully."
        ]);
    }

    /**
     * Save the CurrencyLayer access key and refresh exchange rates for every
     * currency (other than the USD base) from the CurrencyLayer "live" endpoint.
     */
    public function updateExchangeRates(Request $request)
    {
        $validated = $request->validate([
            'currencylayer_api_key' => 'required|string|min:10',
        ]);

        $apiKey = trim($validated['currencylayer_api_key']);

        Setting::setValue('currencylayer_api_key', $apiKey, 'currency');

        $codes = Currencies::where('currency_name', '!=', 'USD')->pluck('currency_name');

        if ($codes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No non-USD currencies to update. Add currencies first.',
            ], 422);
        }

        try {
            $response = Http::timeout(15)->get('http://api.currencylayer.com/live', [
                'access_key' => $apiKey,
                'currencies' => $codes->implode(','),
                'source'     => 'USD',
                'format'     => 1,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not reach CurrencyLayer: ' . $e->getMessage(),
            ], 500);
        }

        $data = $response->json();

        if (!$response->successful() || empty($data['success'])) {
            $errorMessage = $data['error']['info'] ?? 'CurrencyLayer request failed. Please check the access key.';
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 422);
        }

        $quotes = $data['quotes'] ?? [];
        $updated = 0;

        foreach ($codes as $code) {
            $quoteKey = 'USD' . $code;
            if (!array_key_exists($quoteKey, $quotes)) {
                continue;
            }

            Currencies::where('currency_name', $code)
                ->update(['currency_rate' => round((float) $quotes[$quoteKey], 6)]);
            $updated++;
        }

        $lastUpdated = now()->toDateTimeString();
        Setting::setValue('currencylayer_last_updated', $lastUpdated, 'currency');

        if ($updated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'CurrencyLayer responded but returned no matching rates for your currencies.',
            ], 422);
        }

        return response()->json([
            'success'      => true,
            'message'      => "Exchange rates updated for {$updated} currenc" . ($updated === 1 ? 'y' : 'ies') . ".",
            'updated'      => $updated,
            'last_updated' => $lastUpdated,
        ]);
    }

}
