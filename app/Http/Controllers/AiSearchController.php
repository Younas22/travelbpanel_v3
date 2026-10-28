<?php

namespace App\Http\Controllers;

use App\Services\AiFlightSearch;
use Illuminate\Http\Request;

class AiSearchController extends Controller
{
    /**
     * Homepage "Search with AI": free-text request -> flight results URL.
     */
    public function flights(Request $request, AiFlightSearch $search)
    {
        $data = $request->validate([
            'query' => 'required|string|min:3|max:500',
        ]);

        return response()->json($search->search($data['query']));
    }
}
