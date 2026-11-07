<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * The agent's own fee: a fixed amount added on top of every flight price the
 * agent sees. Saved from the dashboard form and live from the flight list.
 * The amount is in the site's active currency at the moment it is saved.
 */
class MarkupController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'agent_markup' => 'required|numeric|min:0|max:1000000',
        ]);

        $currency = activeCurrency()->currency_name;
        $agent = auth()->user();
        $agent->update([
            'agent_markup' => round((float) $validated['agent_markup'], 2),
            'agent_markup_currency' => $currency,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'agent_markup' => (float) $agent->agent_markup,
                'currency' => $currency,
            ]);
        }

        return back()->with('success', 'Your fee is saved: ' . $currency . ' ' . number_format((float) $agent->agent_markup, 2) . ' on every flight.');
    }
}
