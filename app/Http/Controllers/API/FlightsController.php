<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FlightsController extends BaseController
{
    public function flights_airports(Request $request): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'code' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $flights_airports = DB::table("flights_airports")->where("code", $input['code'])->get();

        if ($flights_airports->isEmpty()) {
            $city_airports = DB::table("flights_airports")->where("city", 'like', '%' . $input['code'] . '%')->get();
            return $this->sendResponse($city_airports, 'successfully.');
        } else {
            return $this->sendResponse($flights_airports, 'successfully.');
        }
    }


    // Hotel Destinations Search API
    public function hotelDestinations(Request $request)
    {
        $search = $request->input('search');

        if (empty($search) || strlen($search) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter at least 3 characters'
            ]);
        }

        $destinations = DB::table('locations')
            ->where(function ($query) use ($search) {
                $query->where('city', 'LIKE', "%{$search}%")
                      ->orWhere('country', 'LIKE', "%{$search}%");
            })
            ->where('status', 1)
            ->select('id', 'city', 'country', 'country_code')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $destinations
        ]);
    }

    // Umrah Locations API
    public function umrahLocations(): JsonResponse
    {
        $locations = DB::table('locations')
            ->select('id', 'city as name', 'country')
            ->where('status', 1)
            ->orderBy('city', 'ASC')
            ->get();

        return response()->json($locations);
    }

    // Get All Countries List
    public function countries()
    {
        $countries = DB::table('locations')
            ->select('country', 'country_code')
            ->where('status', 1)
            ->groupBy('country', 'country_code')
            ->orderBy('country', 'ASC')
            ->get();

        // Flags Mapping
        $countryFlags = [
            'PK' => '🇵🇰',
            'AE' => '🇦🇪',
            'US' => '🇺🇸',
            'GB' => '🇬🇧',
            'SA' => '🇸🇦',
            'IN' => '🇮🇳',
            'SG' => '🇸🇬',
            'CH' => '🇨🇭',
            'CA' => '🇨🇦',
            'AU' => '🇦🇺',
            'DE' => '🇩🇪',
            'FR' => '🇫🇷',
            'IT' => '🇮🇹',
            'ES' => '🇪🇸',
            'NL' => '🇳🇱',
            'BE' => '🇧🇪',
            'SE' => '🇸🇪',
            'NO' => '🇳🇴',
            'DK' => '🇩🇰',
            'FI' => '🇫🇮',
            'AT' => '🇦🇹',
            'GR' => '🇬🇷',
            'PT' => '🇵🇹',
            'IE' => '🇮🇪',
            'NZ' => '🇳🇿',
            'JP' => '🇯🇵',
            'CN' => '🇨🇳',
            'KR' => '🇰🇷',
            'TH' => '🇹🇭',
            'MY' => '🇲🇾',
            'ID' => '🇮🇩',
            'PH' => '🇵🇭',
            'VN' => '🇻🇳',
            'BD' => '🇧🇩',
            'EG' => '🇪🇬',
            'ZA' => '🇿🇦',
            'BR' => '🇧🇷',
            'MX' => '🇲🇽',
            'AR' => '🇦🇷',
            'TR' => '🇹🇷',
            'RU' => '🇷🇺',
            'PL' => '🇵🇱',
            'UA' => '🇺🇦',
            'CZ' => '🇨🇿',
            'RO' => '🇷🇴',
            'HU' => '🇭🇺',
        ];

        foreach ($countries as $country) {
            $country->flag = $countryFlags[$country->country_code] ?? '🏳️';
        }

        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    // Get All Tour Package Types
    public function tourPackageTypes()
    {
        $tourTypes = DB::table('tour_package_type')
            ->select('id', 'packege_type as name')
            ->where('status', '1')
            ->orderBy('packege_type', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tourTypes
        ]);
    }


}
