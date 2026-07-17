<?php
// app/Http/Controllers/Admin/CurrenciesController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currencies;
use Illuminate\Http\Request;
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
        return view('admin.currencies.index', compact('currencies', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.currencies.create');
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'currency_name'     => 'required|string|max:255',
            'currency_country'  => 'required|string|max:255',
            'currency_rate'     => 'required|numeric|min:0',
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
        return view('admin.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Currencies $currency)
    {
        $validated = $request->validate([
            'currency_name'   => 'required|string|max:255',
            'currency_country'   => 'required|string|max:10',
            'currency_status'   => 'required|boolean',
            'currency_default'  => 'nullable|boolean',
            'currency_rate'     => 'required|numeric|min:0',
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


}
