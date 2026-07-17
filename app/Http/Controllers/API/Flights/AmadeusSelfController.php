<?php
namespace App\Http\Controllers\API\Flights;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AmadeusSelfController extends BaseController
{
    /**
     * Search for flights using Amadeus API
     */
    public function flight_search(Request $request): JsonResponse
    {
        try {
            $input = $request->all();

            // Validate input
            $validator = Validator::make($input, [
                'origin' => 'required|string',
                'destination' => 'required|string',
                'type' => 'required|in:oneway,round',
                'departure_date' => 'required|date',
                'adults' => 'required|integer|min:1',
                'children' => 'nullable|integer|min:0',
                'infants' => 'nullable|integer|min:0',
                'class' => 'required|in:economy,premium_economy,business,first',
                'currency' => 'required|string|size:3',
                'api_credential_1' => 'required|string', // grant_type
                'api_credential_2' => 'required|string', // client_id
                'api_credential_3' => 'required|string', // client_secret
                'commission' => 'required|string',
                'discount' => 'required|string',
                'env' => 'required|in:dev,pro',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Set API endpoints based on environment
            $endpoints = $this->get_endpoints($input['env']);

            // Build route data
            $routes = $this->route_data($input);

            // Build travelers data
            $travelers = $this->travelers_data($input);

            // Build search criteria
            $searchData = $this->search_data($input, $routes, $travelers);

            // Get OAuth token
            $token = $this->get_token($endpoints['v1'], $input);

            if (!$token) {
                return $this->sendError('Authentication failed', ['msg' => 'wrong_credentials']);
            }

            // Search flights
            $flightResults = $this->search_flights($endpoints['v2'], $searchData, $token);

            if (!$flightResults) {
                return $this->sendError('Flight search failed', ['msg' => 'no_result']);
            }

            // Process and format results
            $formattedResults = $this->process_flight_results($flightResults, $input);

            if (!empty($formattedResults)) {
                $flightData = array_slice($formattedResults, 0, 200);
                return $this->sendResponse($flightData, 'Successfully retrieved flights.');
            } else {
                return $this->sendResponse(['msg' => 'no_result'], 'No flights found.');
            }

        } catch (Exception $e) {
            return $this->sendError('Server Error', ['msg' => $e->getMessage()]);
        }
    }

    /**
     * Build route data for the search
     */
    private function route_data(array $input): array
    {
        $routeData = [];

        // Outbound route
        $routeData[] = [
            "id" => "1",
            "originLocationCode" => strtoupper($input['origin']),
            "destinationLocationCode" => strtoupper($input['destination']),
            "departureDateTimeRange" => [
                'date' => Carbon::parse($input['departure_date'])->format('Y-m-d'),
                'time' => Carbon::parse($input['departure_date'])->format('H:i:s')
            ],
        ];

        // Return route for round trip
        if ($input['type'] === 'round') {
            $routeData[] = [
                "id" => "2",
                "originLocationCode" => strtoupper($input['destination']),
                "destinationLocationCode" => strtoupper($input['origin']),
                "departureDateTimeRange" => [
                    'date' => Carbon::parse($input['return_date'])->format('Y-m-d'),
                    'time' => Carbon::parse($input['return_date'])->format('H:i:s')
                ],
            ];
        }

        return $routeData;
    }

    /**
     * Build travelers data
     */
    private function travelers_data(array $input): array
    {
        $travelers = [];
        $id = 1;

        // Adults
        for ($i = 1; $i < $input['adults']+1; $i++) {
            $travelers[] = [
                "id" => $i,
                "travelerType" => 'ADULT',
                "fareOptions" => ['STANDARD'],
            ];
        }

        // Children
        if (!empty($input['children'])) {
            for ($i=1; $i < $input['children']+1; $i++) {
                $travelers[] = [
                    "id" => $i + $input['adults'],
                    "travelerType" => 'CHILD',
                    "fareOptions" => ['STANDARD'],
                ];
            }
        }

        // Infants
        if (!empty($input['infants'])) {
            for ($i=1; $i < $input['infants']+1; $i++) {
                $travelers[] = (object)array(
                    "id" => $i + $input['adults'] + $input['children'],
                    "travelerType" => 'HELD_INFANT',
                    "associatedAdultId"=> $i + $input['adults'] - 1,
                    "fareOptions" => array('STANDARD'),

                );
            }
        }

        return $travelers;
    }

    /**
     * Build search data payload
     */
    private function search_data(array $input, array $routeData, array $travelers): array
    {
        return [
            'currencyCode' => strtoupper($input['currency']),
            'originDestinations' => $routeData,
            'travelers' => $travelers,
            'sources' => ['GDS'],
            'searchCriteria' => [
                'maxFlightOffers' => 50,
                'flightFilters' => [
                    'cabinRestrictions' => [
                        [
                            'cabin' => strtoupper($input['class']),
                            'coverage' => 'MOST_SEGMENTS',
                            'originDestinationIds' => ['1']
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * Search flights
     */
    private function search_flights(string $endpoint, array $searchData, string $token): ?array
    {


        try {
            $response = Http::withToken($token)
                ->post($endpoint . 'shopping/flight-offers', $searchData);

            if ($response->successful()) {
                $result = $response->json();

                // If there are errors, try with fallback search criteria
                if (!empty($result['errors'])) {
                    $fallbackSearchData = $this->buildFallbackSearchData($searchData);
                    $response = Http::withToken($token)
                        ->post($endpoint . 'shopping/flight-offers', $fallbackSearchData);

                    if ($response->successful()) {
                        $result = $response->json();
                    }
                }

                return $result;
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get OAuth token
     */
    private function get_token(string $endpoint, array $input)
    {
        try {
            $response = Http::asForm()->post($endpoint . 'security/oauth2/token', [
                'grant_type' => $input['api_credential_1'],
                'client_id' => $input['api_credential_2'],
                'client_secret' => $input['api_credential_3'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get API endpoints based on environment
     */
    private function get_endpoints(string $env): array
    {
        $baseUrl = $env === 'dev'
            ? 'https://test.api.amadeus.com/'
            : 'https://api.amadeus.com';

        return [
            'v1' => $baseUrl . '/v1/',
            'v2' => $baseUrl . '/v2/',
        ];
    }


    /**
     * Process flight results into formatted array
     */
    private function process_flight_results(array $results, array $input): array
    {
        $mainArray = [];

        if (empty($results['data'])) {
            return $mainArray;
        }

        foreach ($results['data'] as $flightOffer) {
            $objectArray = [];
            $currencyCode = $flightOffer['price']['currency'] ?? '';

            foreach ($flightOffer['itineraries'] as $itinerary) {
                $segmentArray = [];

                foreach ($itinerary['segments'] as $segment) {
                    $pricing = $this->pricing($flightOffer['travelerPricings'] ?? []);
                    $airlineInfo = $this->airline_info($segment['carrierCode']);
                    $departureAirportInfo = $this->airport_details($segment['departure']['iataCode']);
                    $arrivalAirportInfo = $this->airport_details($segment['arrival']['iataCode']);
                    $duration = $this->duration($segment['duration']);
                    $total_duration = $this->total_duration($itinerary['segments']);

                    if(!empty($pricing['weight_baggage'])){
                        $weight_baggage = $pricing['weight_baggage'];
                    }else{
                        $weight_baggage = "PC";
                    }

                    $segmentArray[] = [
                        'id' => $segment['id'] ?? uniqid(),
                        'flight_number' => $segment['carrierCode'] . " " . $segment['number'],
                        'airline_name' => $airlineInfo['name'],
                        'departure' => [
                            'airport' => $segment['departure']['iataCode'],
                            'city' => $departureAirportInfo['city'],
                            'city_name' => $departureAirportInfo['city'],
                            'airport_name' => $departureAirportInfo['airport'],
                            'country' => $departureAirportInfo['country'],
                            'time' => Carbon::parse($segment['departure']['at'])->format('h:i A'),
                            'date' => Carbon::parse($segment['departure']['at'])->format('Y-m-d'),
                            'date_convert' => Carbon::parse($segment['departure']['at'])->format('D d M Y'),
                            'terminal' => $segment['departure']['terminal'] ?? null,
                        ],
                        'arrival' => [
                            'airport' => $segment['arrival']['iataCode'],
                            'city' => $arrivalAirportInfo['city'],
                            'city_name' => $arrivalAirportInfo['city'],
                            'airport_name' => $arrivalAirportInfo['airport'],
                            'country' => $arrivalAirportInfo['country'],
                            'time' => Carbon::parse($segment['arrival']['at'])->format('h:i A'),
                            'date' => Carbon::parse($segment['arrival']['at'])->format('Y-m-d'),
                            'date_convert' => Carbon::parse($segment['arrival']['at'])->format('D d M Y'),
                            'terminal' => $segment['arrival']['terminal'] ?? null,
                            'date_adjustment' => 0,
                        ],
                        'carrier' => [
                            'marketing' => $segment['carrierCode'],
                            'operating' => $segment['operating']['carrierCode'] ?? $segment['carrierCode'],
                            'alliances' => $airlineInfo['alliances'] ?? null,
                        ],
                        'equipment' => $segment['aircraft']['code'] ?? null,
                        'duration' => $duration,
                        'total_duration' => $total_duration,
                        'distance' => "",
                        'eTicketable' => true,
                        'frequency' => 1,
                        'stop_count' => $segment['numberOfStops'] ?? 0,
                        'class' => $pricing['classType'],
                        'baggage' => $pricing['baggage'].$weight_baggage,
                        'cabin_baggage' => $pricing['cabin_bags'] ."PC",
                        'currency' => $currencyCode,
                        'actual_price' => $flightOffer['price']['total'],
                        'actual_adult_price' => $pricing['adultPrice'],
                        'actual_child_price' => $pricing['childPrice'],
                        'actual_infant_price' => $pricing['infantPrice'],
                        'price' => number_format($this->commission($flightOffer['price']['total'],$input['commission'],$input['discount'])),
                        'adult_price' => number_format($this->commission($pricing['adultPrice'] ,$input['commission'],$input['discount']) * $input['adults']),
                        'child_price' => number_format($this->commission($pricing['childPrice'] ,$input['commission'],$input['discount']) * ($input['children'] ?? 0)),
                        'infant_price' => number_format($this->commission($pricing['infantPrice'] ,$input['commission'],$input['discount']) * ($input['infants'] ?? 0)),
                        'booking_data' => $flightOffer,
                        'supplier' => "amadeus_self",
                        'type' => $input['type'],
                    ];
                }
                $objectArray[] = $segmentArray;
            }
            $mainArray[]['segments'] = $objectArray;
        }

        return $mainArray;
    }

    private function commission($price,$commission,$discount) {
        $commission = $price * ($commission/ 100);
        $priceWithCommission = $price + $commission;
        // Apply discount
        $discountAmount = $priceWithCommission * ($discount / 100);
        $finalPrice = $priceWithCommission - $discountAmount;

        return ($finalPrice);
    }

    /**
     * Extract pricing information from traveler pricings
     */
    private function pricing(array $traveler_pricings): array
    {
        $adultPrice = 0;
        $childPrice = 0;
        $infantPrice = 0;
        $baggage = 0;
        $weight_baggage = "";
        $classType = '';
        $cabin_bags = '';

        foreach ($traveler_pricings as $pricing) {
            switch ($pricing['travelerType']) {
                case 'ADULT':
                    $adultPrice = $pricing['price']['total'];
                    $classType = $pricing['fareDetailsBySegment'][0]['cabin'] ?? '';
                    $baggage = $pricing['fareDetailsBySegment'][0]['includedCheckedBags']['weight'] ?? 0;
                    $weight_baggage = $pricing['fareDetailsBySegment'][0]['includedCheckedBags']['weightUnit'] ?? "";
                    $cabin_bags = $pricing['fareDetailsBySegment'][0]['includedCabinBags']['quantity'] ?? 0;
                    break;
                case 'CHILD':
                    $childPrice = $pricing['price']['total'];
                    break;
                case 'SEATED_INFANT':
                    $infantPrice = $pricing['price']['total'];
                    break;
            }
        }

        return [
            'adultPrice' => $adultPrice,
            'childPrice' => $childPrice,
            'infantPrice' => $infantPrice,
            'baggage' => $baggage,
            'weight_baggage' => $weight_baggage,
            'cabin_bags' => $cabin_bags,
            'classType' => $classType
        ];
    }

    /**
     * Get airline information
     */
    private function airline_info(string $carrierCode): array
    {
        $airline = DB::table('flights_airlines')->where('code', $carrierCode)->first();
        return [
            'name' => $airline->name ?? '',
            'code' => $carrierCode
        ];
    }

    /**
     * Get detailed airport information
     */
    private function airport_details(string $airportCode): array
    {
        $airport = DB::table('flights_airports')->where('code', $airportCode)->first();
        return [
            'airport' => $airport->airport ?? $airportCode,
            'city' => $airport->city ?? '',
            'country' => $airport->country ?? ''
        ];
    }

    /**
     * Get airport information
     */
    private function airport_info(string $departureCode, string $arrivalCode): array
    {
        $departureAirport = DB::table('flights_airports')->where('code', $departureCode)->first();
        $arrivalAirport = DB::table('flights_airports')->where('code', $arrivalCode)->first();

        return [
            'departure' => $departureAirport->airport ?? $departureCode,
            'arrival' => $arrivalAirport->airport ?? $arrivalCode
        ];
    }

    /**
     * Format duration from ISO 8601 format
     */
    private function duration(string $duration): string
    {
        try {
            $start = new \DateTime('@0');
            $start->add(new \DateInterval($duration));
            return $start->format('H\h:i\m');
        } catch (Exception $e) {
            return '00:00';
        }
    }

    /**
     * Calculate total duration for all segments
     */
    private function total_duration(array $segments): string
    {
        try {
            $totalDuration = new \DateTime('@0');
            $segmentCount = count($segments);
            $maxSegments = min($segmentCount, 5);

            for ($i = 1; $i <= $maxSegments; $i++) {
                $segment = $segments[$segmentCount - $i];
                $totalDuration->add(new \DateInterval($segment['duration']));
            }

            return $totalDuration->format('H\h:i\m');
        } catch (Exception $e) {
            return '00:00';
        }
    }


}

