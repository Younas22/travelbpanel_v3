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

class SabreController extends BaseController
{

    private function getSabreAccessToken($data)
    {
        $pcc = $data['api_credential_1'];
        $epr = $data['api_credential_2'];
        $domain = $data['api_credential_3'];
        $password = $data['api_credential_4'];

        $v1 = "V1:{$epr}:{$pcc}:{$domain}";
        $b_v1 = base64_encode($v1);
        $b_pwd = base64_encode($password);
        $joined = $b_v1 . ':' . $b_pwd;
        $final  = base64_encode($joined);


        $url = $this->get_endpoints($data['env']) . '/v2/auth/token';
        $postFields = http_build_query([
            'grant_type' => 'client_credentials'
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Basic $final",
            "Content-Type: application/x-www-form-urlencoded"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return response()->json(['error' => curl_error($ch)], 500);
        }

        curl_close($ch);

        $result = json_decode($response, true);

        $accessToken = $result['access_token'];

        return $accessToken;
    }

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
                'api_credential_1' => 'required|string', // pcc
                'api_credential_2' => 'required|string', // epr
                'api_credential_3' => 'required|string', // domain
                'api_credential_4' => 'required|string', // password
                'env' => 'required|in:dev,pro',
            ]);


            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Build route data
            $formattedResults = $this->route_data($input);

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
        $class = strtolower($input['class']);

        switch ($class) {
            case "economy":
                $class_type = "Y";
                break;
            case "premium_economy":
                $class_type = "P";
                break;
            case "business":
                $class_type = "J";
                break;
            case "first":
                $class_type = "F";
                break;
            default:
                $class_type = "Y";
        }

        $passenger_array = [];
        for ($i = 0; $i < $input['adults']; $i++) {
            $passenger_array[] = [
                "Code" => "ADT",
                "Quantity" => 1,
                "TPA_Extensions" => [
                    "VoluntaryChanges" => [
                        "Match" => "Info"
                    ]
                ]
            ];
        }

        for ($i = 0; $i < $input['children']; $i++) {
            $passenger_array[] = [
                "Code" => "CNN",
                "Quantity" => 1,
                "TPA_Extensions" => [
                    "VoluntaryChanges" => [
                        "Match" => "Info"
                    ]
                ]
            ];
        }

        for ($i = 0; $i < $input['infants']; $i++) {
            $passenger_array[] = [
                "Code" => "INF",
                "Quantity" => 1,
                "TPA_Extensions" => [
                    "VoluntaryChanges" => [
                        "Match" => "Info"
                    ]
                ]
            ];
        }


        if($input['type'] == "oneway"){
            $segment = [
                [
                    "RPH" => "1",
                    "DepartureDateTime" => Carbon::parse($input['departure_date'])->format('Y-m-d')."T00:00:00",
                    "OriginLocation" => [
                        "LocationCode" => $input['origin']
                    ],
                    "DestinationLocation" => [
                        "LocationCode" => $input['destination']
                    ]
                ]
            ];
        } else if($input['type'] == "round") {
            $segment = [
                [
                    "RPH" => "1",
                    "DepartureDateTime" => Carbon::parse($input['departure_date'])->format('Y-m-d')."T00:00:00",
                    "OriginLocation" => [
                        "LocationCode" => $input['origin']
                    ],
                    "DestinationLocation" => [
                        "LocationCode" => $input['destination']
                    ]
                ],[
                    "RPH" => "2",
                    "DepartureDateTime" => Carbon::parse($input['return_date'])->format('Y-m-d')."T00:00:00",
                    "OriginLocation" => [
                        "LocationCode" => $input['destination']
                    ],
                    "DestinationLocation" => [
                        "LocationCode" => $input['origin']
                    ]
                ]
            ];
        }


        $payload = [
            "OTA_AirLowFareSearchRQ" => [
                "DirectFlightsOnly" => false,
                "Version" => "1",
                "POS" => [
                    "Source" => [
                        [
                            "PseudoCityCode" => $input['api_credential_1'],
                            "RequestorID" => [
                                "Type" => "1",
                                "ID" => "1",
                                "CompanyName" => [
                                    "Code" => "TN"
                                ]
                            ]
                        ]
                    ]
                ],
                "OriginDestinationInformation" => $segment,
                "TravelPreferences" => [
                    "TPA_Extensions" => [
                        "DataSources" => [
                            "NDC" => "Disable",
                            "ATPCO" => "Enable",
                            "LCC" => "Disable"
                        ],
                        "PreferNDCSourceOnTie" => [
                            "Value" => false
                        ],
                        "ExcludeCallDirectCarriers" => [
                            "Enabled" => true
                        ],
                        "KeepSameCabin" => [
                            "Enabled" => false
                        ]
                    ],
                    "Baggage" => [
                        "Description" => true,
                        "RequestType" => "A"
                    ],
                    "CabinPref" => [
                        [
                            "Cabin" =>ucfirst($class),
                            "PreferLevel" => "Preferred"
                        ]
                    ]
                ],
                "TravelerInfoSummary" => [
                    "AirTravelerAvail" => [
                        [
                            "PassengerTypeQuantity" => $passenger_array
                        ]
                    ],
                    "PriceRequestInformation" => [
                        "CurrencyCode" => $input['currency']
                    ]
                ],
                "TPA_Extensions" => [
                    "IntelliSellTransaction" => [
                        "RequestType" => [
                            "Name" => "200ITINS"
                        ],
                        "CompressResponse" => [
                            "Value" => false
                        ]
                    ]
                ]
            ]
        ];

        $accessToken = $this->getSabreAccessToken($input);
        $url = $this->get_endpoints($input['env']) . '/v5/offers/shop';

        $data = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

        $response = $data->json();

        if (!isset($response['groupedItineraryResponse']['scheduleDescs'])) {
            return response()->json(['error' => 'Invalid flight data'], 400);
        }

        $flights = $response['groupedItineraryResponse']['scheduleDescs'];
        $legs = $response['groupedItineraryResponse']['legDescs'] ?? [];
        $itineraryGroups = $response['groupedItineraryResponse']['itineraryGroups'] ?? [];

        $baggageAllowanceMap = $this->createBaggageAllowanceMap($response);

        // Create pricing map
        $pricingByRefId = $this->createPricingMap($itineraryGroups,$baggageAllowanceMap);

        $flightMap = [];
        foreach ($flights as $flight) {
            $flightMap[$flight['id']] = $flight;
        }

        $allFlights = [];
        $desiredOrigin = strtoupper($input['origin']);
        $desiredDestination = strtoupper($input['destination']);
        foreach ($legs as $leg) {
            if (!isset($leg['schedules']) || count($leg['schedules']) < 1) continue;

            $isValidLeg = true;
            $pricingInfo = $pricingByRefId[$leg['id']] ?? null;

            $segmentsGroup = []; // Holds all groups of connected segments
            $currentGroup = [];  // Collects segments in a single group

            foreach ($leg['schedules'] as $scheduleRef) {
                $refId = $scheduleRef['ref'];
                if (!isset($flightMap[$refId])) {
                    $isValidLeg = false;
                    break;
                }

                $flight = $flightMap[$refId];
                $directionStatus = [];
                if (!in_array('return', $directionStatus) && $flight['departure']['airport'] === strtoupper($input['destination'])) {
                    $directionStatus[] = 'return';
                }
                if (!in_array('return', $directionStatus) && $flight['arrival']['airport'] === strtoupper($input['origin'])) {
                    $directionStatus[] = 'return';
                }
                $baseDate = in_array('return', $directionStatus) ?  Carbon::parse($input['return_date'])->format('Y-m-d') :  Carbon::parse($input['departure_date'])->format('Y-m-d');


                $dateAdjustment = $flight['arrival']['dateAdjustment'] ?? 0;
                $adjustedArrivalDate = Carbon::parse($baseDate)->addDays($dateAdjustment);

                $dep_airports = DB::table("flights_airports")->where("code", $flight['departure']['city'])->first();
                $arrival_airports = DB::table("flights_airports")->where("code", $flight['arrival']['city'])->first();
                $airline_name = DB::table("flights_airlines")->where("code", $flight['carrier']['marketing'])->first();

                $pricing = $this->pricing($pricingInfo ?? []);

                $adultPrice = $pricing['adultPrice'] *  $input['adults'];
                $childPrice = $pricing['childPrice'] * $input['children'];
                $infantPrice = $pricing['infantPrice'] * $input['infants'];

                $totalPrice = $adultPrice + $childPrice + $infantPrice;

                $segment = [
                    'id' => $flight['id'] ?? uniqid(),
                    'flight_number' => $flight['carrier']['marketingFlightNumber'],
                    'airline_name' => $airline_name->name  ?? null,
                    'departure' => [
                        'airport' => $flight['departure']['airport'],
                        'city' => $flight['departure']['city'],
                        'city_name' => $dep_airports->city  ?? null,
                        'airport_name' => $dep_airports->airport  ?? null,
                        'country' => $flight['departure']['country'],
                        'time' => Carbon::parse($flight['departure']['time'])->format('h:i A'),
                        'booking_time' => $flight['departure']['time'],
                        'date_convert' => Carbon::parse($baseDate)->format('D d M Y'),
                        'terminal' => $flight['departure']['terminal'] ?? null,
                    ],
                    'arrival' => [
                        'airport' => $flight['arrival']['airport'],
                        'city' => $flight['arrival']['city'],
                        'city_name' => $arrival_airports->city  ?? null,
                        'country' => $flight['arrival']['country'],
                        'airport_name' => $arrival_airports->airport  ?? null,
                        'time' => Carbon::parse($flight['arrival']['time'])->format('h:i A'),
                        'booking_time' => $flight['arrival']['time'],
                        'date' => $adjustedArrivalDate->format('Y-m-d'),
                        'date_convert' => $adjustedArrivalDate->format('D d M Y'),
                        'terminal' => $flight['arrival']['terminal'] ?? null,
                    ],
                    'carrier' => [
                        'marketing' => $flight['carrier']['marketing'],
                        'operating' => $flight['carrier']['operating'],
                        'alliances' => $flight['carrier']['alliances'] ?? null,
                    ],
                    'equipment' => $flight['carrier']['equipment']['code'] ?? null,
                    'total_duration' => $this->formatDuration($leg['elapsedTime']),
                    'duration' => $this->formatDuration($flight['elapsedTime']),
                    'distance' => $this->formatDistance($flight['totalMilesFlown']),
                    'eTicketable' => $flight['eTicketable'],
                    'frequency' => $flight['frequency'],
                    'stop_count' => $flight['stopCount'],
                    'class' => $input['class'] ?? '',
                    'baggage' => $pricing['baggage'],
                    'cabin_baggage' => $pricing['cabin_bags'],
                    'currency' => $pricing['currency'],
                    'price' => number_format($totalPrice),
                    'adult_price' => number_format($pricing['adultPrice'] * $input['adults']),
                    'child_price' => number_format($pricing['childPrice'] * ($input['children'] ?? 0)),
                    'infant_price' => number_format($pricing['infantPrice'] * ($input['infants'] ?? 0)),
                    'booking_data' => "",
                    'supplier' => "sabre",
                    'type' => $input['type'] ?? 'oneway',
                ];

                // Add flight to current group
                $currentGroup[] = $segment;

                // If this flight has no stops, or next flight is a new group, finalize current group
                if ($scheduleRef === end($leg['schedules'])) {
                    $segmentsGroup[] = $currentGroup; // Push group
                    $currentGroup = [];               // Reset for next group
                }
            }

            if ($isValidLeg && count($segmentsGroup)) {
                $allFlights[] = [
                    'segments' => $segmentsGroup
                ];
            }
        }

        // ==============================
        // Step 2: Handle Return Flights
        // ==============================


        if ($input['type'] === "round") {
            $groupedFlights = [];
            $usedLegIds    = [];
            $onwardFlights = [];
            $returnFlights = [];

            foreach ($allFlights as $index => $flight) {
                $from = $flight['segments'][0][0]['departure']['airport'];
                $to = end($flight['segments'][0])['arrival']['airport'];

                if (
                    (!empty($desiredOrigin) && $from === $desiredOrigin) &&
                    (!empty($desiredDestination) && $to === $desiredDestination)
                ) {
                    $onwardFlights[$index] = $flight; // FIXED: index as key
                } elseif (
                    (!empty($desiredOrigin) && $from === $desiredDestination) &&
                    (!empty($desiredDestination) && $to === $desiredOrigin)
                ) {
                    $returnFlights[$index] = $flight; // FIXED: index as key
                }
            }

            foreach ($onwardFlights as $oIdx => $onward) {
                foreach ($returnFlights as $rIdx => $return) {
                    if (!in_array($oIdx, $usedLegIds) && !in_array($rIdx, $usedLegIds)) {
                        $groupedFlights[] = [
                            'segments' => array_merge($onward['segments'], $return['segments']),
                        ];

                        $usedLegIds[] = $oIdx; // FIXED: index track karo
                        $usedLegIds[] = $rIdx; // FIXED: index track karo
                        break;
                    }
                }
            }
        }

        // =============================
        // Step 3: Final Output Response
        // =============================
        if ($input['type'] === "oneway") {
            $groupedFlight = $allFlights;
        } elseif ($input['type'] === "round") {
            $groupedFlight = $groupedFlights;
        }



        return $groupedFlight;

    }


    /**
     * Extract pricing information from traveler pricings
     */
    /**
     * Extract pricing info from traveler pricings array
     *
     * @param array $traveler_pricings
     * @return array
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
        $currency = '';

        foreach ($traveler_pricings['passenger_prices'] as $pricing) {

            switch ($pricing['type']) {
                case 'ADT':
                    $adultPrice = $pricing['FareAmount'] ?? 0;
                    $baggage = $pricing['baggage']['value'] ?? 0;
                    $weight_baggage = '';
                    $cabin_bags = 0;
                    $currency = $pricing['currency'];
                    break;

                case 'CNN': // sometimes child comes as CNN
                    $childPrice = $pricing['FareAmount']?? 0;
                    $currency = $pricing['currency'];
                    break;

                case 'INF':
                    $infantPrice = $pricing['FareAmount'] ?? 0;
                    $currency = $pricing['currency'];
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
            'currency' => $currency,
        ];
    }

    function formatDistance($miles): string
    {
        return number_format($miles) . ' miles';
    }

    function formatDuration($minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return "{$hours}h {$mins}m";
    }
    protected function createPricingMap(array $itineraryGroups,$baggageAllowanceMap): array
    {
        $pricingMap = [];

        foreach ($itineraryGroups as $group) {

            foreach ($group['itineraries'] as $itinerary) {

                if (isset($itinerary['pricingInformation'][0]['fare'])) {
                    $fare = $itinerary['pricingInformation'][0]['fare'];

                    // Collect all passenger prices
                    $passengerPrices = [];
                    if (isset($fare['passengerInfoList'])) {
                        foreach ($fare['passengerInfoList'] as $pInfo) {
                            if (!empty($pInfo['passengerInfo']['baggageInformation'])) {
                                $baggageEntries = $pInfo['passengerInfo']['baggageInformation'] ?? [];
                                foreach ($baggageEntries as $bInfo) {
                                    $refId = $bInfo['allowance']['ref'] ?? null;

                                    if ($refId && isset($baggageAllowanceMap[$refId])) {
                                        $allowance = $baggageAllowanceMap[$refId];

                                        $baggage = [
                                            'ref' => $refId,
                                            'value' => null,
                                            'description1' => null,
                                            'description2' => null,
                                        ];

                                        if ($allowance) {
                                            // Piece-based
                                            if (!empty($allowance['pieceCount'])) {
                                                $baggage['value'] = $allowance['pieceCount'] . 'PC';
                                            }
                                            // Weight-based
                                            elseif (!empty($allowance['weight'])) {
                                                $baggage['value'] = $allowance['weight'] . ($allowance['unit'] ?? '');
                                            }

                                            // Descriptions (optional)
                                            $baggage['description1'] = $allowance['description1'] ?? null;
                                            $baggage['description2'] = $allowance['description2'] ?? null;
                                        }

                                    }
                                }
                            }




                            $passengerPrices[] = [
                                'type' => $pInfo['passengerInfo']['passengerType'] ?? null,
                                'fareComponents' => $pInfo['passengerInfo']['fareComponents'],
                                'total' => $pInfo['passengerInfo']['passengerTotalFare']['baseFareAmount'] ?? null,
                                'totalTax' => $pInfo['passengerInfo']['passengerTotalFare']['totalTaxAmount'] ?? null,
                                'currency' => $pInfo['passengerInfo']['passengerTotalFare']['currency'] ?? null,
                                'FareAmount' => $pInfo['passengerInfo']['passengerTotalFare']['totalFare'] ?? null,
                                'baggage' => $baggage,
                            ];
                        }
                    }

                    // Build pricing info
                    $pricingInfo = [
                        "passenger_prices" => $passengerPrices,
                        'total_fare' => $fare['totalFare']['totalPrice'] ?? null,
                        'currency' => $fare['totalFare']['currency'] ?? null,
                        'base_fare' => $fare['totalFare']['baseFareAmount'] ?? null,
                        'base_fare_currency' => $fare['totalFare']['baseFareCurrency'] ?? null,
                        'taxes' => $fare['totalFare']['totalTaxAmount'] ?? null,
                        'validating_carrier' => $fare['validatingCarrierCode'] ?? null,
                    ];

                    // Map to leg ref IDs
                    if (isset($itinerary['legs'])) {
                        foreach ($itinerary['legs'] as $leg) {
                            if (isset($leg['ref'])) {
                                $pricingMap[$leg['ref']] = $pricingInfo;
                            }
                        }
                    }
                }
            }
        }

        return $pricingMap;
    }
    protected function createBaggageAllowanceMap(array $response): array
    {
        $baggageAllowanceDescs = $response['groupedItineraryResponse']['baggageAllowanceDescs'] ?? [];

        $baggageAllowanceMap = [];
        foreach ($baggageAllowanceDescs as $desc) {
            if (isset($desc['id'])) {
                $baggageAllowanceMap[$desc['id']] = $desc;
            }
        }

        return $baggageAllowanceMap;
    }

    /**
     * Get API endpoints based on environment
     */
    private function get_endpoints(string $env): string
    {
        return $env === 'dev'
            ? 'https://api.cert.platform.sabre.com'
            : 'https://api.platform.sabre.com';
    }
}
