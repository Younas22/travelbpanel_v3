<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Currencies;


class CurrencyController extends Controller
{
    /**
     * Set selected currency in session
     */
    public function setCurrency(Request $request)
    {
        // Validate input
        $request->validate([
            'code' => 'required|string|exists:currencies,currency_name'
        ]);

        // Get currency from DB
        $currency = Currencies::where('currency_name', $request->code)->first();

        if ($currency) {
            // Save in session
            session(['currency' => $request->code]);

            return response()->json([
                'success' => true,
                'message' => "Currency changed to {$currency->code}"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Currency not found'
        ], 400);
    }
}
