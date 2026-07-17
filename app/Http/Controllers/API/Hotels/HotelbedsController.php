<?php

namespace App\Http\Controllers\API\Hotels;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HotelbedsController extends BaseController
{
    private const RESULTS_PER_PAGE = 100;
    private const API_ENDPOINTS = [
        'dev' => 'https://api.test.hotelbeds.com/hotel-api/1.0/hotels',
        'pro' => 'https://api.hotelbeds.com/hotel-api/1.0/hotels',
    ];
    private const HOTEL_IMAGE_BASE_URL = 'http://photos.hotelbeds.com/giata/';
    private const CHECKRATES_ENDPOINTS = [
        'dev' => 'https://api.test.hotelbeds.com/hotel-api/1.0/checkrates',
        'pro' => 'https://api.hotelbeds.com/hotel-api/1.0/checkrates',
    ];
    private const BOOKING_ENDPOINTS = [
        'dev' => 'https://api.test.hotelbeds.com/hotel-api/1.0/bookings',
        'pro' => 'https://api.hotelbeds.com/hotel-api/1.0/bookings',
    ];

    /**
     * Search for Hotels using Hotelbeds API
     */

    public function hotel_search(Request $request): JsonResponse
    {
        try {
            $validated = $this->validate_input($request);

            $destination = $this->get_destination($validated['city']);
            if (!$destination) {
                return $this->error_response('Destination not found.');
            }

            $hotel_codes = $this->get_hotel_codes($destination->code);
            if (empty($hotel_codes)) {
                return $this->error_response('No hotels available for selected destination.');
            }

            $api_response = $this->call_hotelbeds_api($validated, $hotel_codes);
            if (!$this->isvalid_api_response($api_response)) {
                return $this->error_response('No hotels found for selected search criteria.');
            }

            $formatted_hotels = $this->format_hotels($api_response['hotels']['hotels'],$validated);

            return $this->success_response([
                'total' => $api_response['hotels']['total'],
                'data' => $formatted_hotels,
            ], 'Hotels fetched successfully');

        } catch (Exception $e) {
            return $this->error_response('Server Error', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Validate incoming request
     */
    private function validate_input(Request $request): array
    {
        $rules = [
            'city' => 'required|string|max:100',
            'checkin' => 'required|date|after_or_equal:today',
            'checkout' => 'required|date|after:checkin',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'rooms' => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'env' => 'required|in:dev,pro',
            'api_credential_1' => 'required|string',
            'api_credential_2' => 'required|string',
            'commission' => 'required|string',
            'country_code' => 'nullable|string|size:3',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new Exception('Validation Error: ' . json_encode($validator->errors()));
        }

        return $validator->validated();
    }

    /**
     * Retrieve destination by name
     */
    private function get_destination(string $city)
    {
        return DB::connection('hotelbeds')->table('destinations')->where('name', 'LIKE', '%' . $city . '%')->first();
    }

    /**
     * Get hotel codes for a destination
     */
    private function get_hotel_codes(string $destinationCode): array
    {
        return DB::connection('hotelbeds')->table('hotels')->where('destinationCode', $destinationCode)->offset(1)->limit(self::RESULTS_PER_PAGE)->pluck('hotel_code')->toArray();
    }

    /**
     * Call HotelBeds API
     */
    private function call_hotelbeds_api(array $validated, array $hotelCodes): array
    {
        $timestamp = time();
        $signature = $this->generate_signature(
            $validated['api_credential_1'],
            $validated['api_credential_2'],
            $timestamp
        );

        $payload = $this->build_api_payload($validated, $hotelCodes);
        $url = self::API_ENDPOINTS[$validated['env']];

        $response = Http::withHeaders([
            'Api-key' => $validated['api_credential_1'],
            'X-Signature' => $signature,
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip',
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if (!$response->successful()) {
            throw new Exception("API request failed: {$response->status()}");
        }

        return $response->json();
    }

    /**
     * Generate SHA256 signature
     */
    private function generate_signature(string $apiKey, string $secret, int $timestamp): string
    {
        return hash('sha256', $apiKey . $secret . $timestamp);
    }

    /**
     * Build API request payload
     */
    private function build_api_payload(array $validated, array $hotelCodes): array
    {
        $paxes = $this->build_paxes($validated);

        return [
            'sourceMarket' => $validated['country_code'] ?? 'PK',
            'lastUpdateTime' => now()->format('Y-m-d'),
            'stay' => [
                'checkIn' => date('Y-m-d', strtotime($validated['checkin'])),
                'checkOut' => date('Y-m-d', strtotime($validated['checkout'])),
                'allowOnlyShift' => true,
            ],
            'occupancies' => [
                [
                    'rooms' => intval($validated['rooms']),
                    'adults' => intval($validated['adults']),
                    'children' => intval($validated['children'] ?? 0),
                    'paxes' => $paxes,
                ]
            ],
            'hotels' => [
                'hotel' => $hotelCodes,
            ],
        ];
    }

    /**
     * Build paxes array for API
     */
    private function build_paxes(array $validated): array
    {
        $paxes = [];

        // Add adults
        for ($i = 0; $i < $validated['adults']; $i++) {
            $paxes[] = ['type' => 'AD'];
        }

        // Add children with ages
        if (!empty($validated['children']) && isset($validated['child_ages'])) {
            $childAges = is_string($validated['child_ages'])
                ? json_decode($validated['child_ages'], true)
                : $validated['child_ages'];

            if (is_array($childAges)) {
                foreach ($childAges as $ageData) {
                    $age = $ageData['ages'] ?? $ageData['age'] ?? null;
                    if ($age !== null) {
                        $paxes[] = ['type' => 'CH', 'age' => intval($age)];
                    }
                }
            }
        }

        return $paxes;
    }

    /**
     * Validate API response
     */
    private function isvalid_api_response(array $response): bool
    {
        return !empty($response['hotels']) &&
            isset($response['hotels']['total']) &&
            $response['hotels']['total'] > 0 &&
            isset($response['hotels']['hotels']);
    }

    /**
     * Format hotel data for response
     */
    private function format_hotels(array $hotelsData, array $validated): array
    {
        return array_map(function (array $hotel) use ($validated) {
            $price = convertCurrency($hotel['minRate'], $hotel['currency'], $validated['currency']);
            $hotelDetails = $this->get_hotel_details($hotel['code']);
            return [
                'hotel_id' => $hotel['code'],
                'name' => $this->sanitize_name($hotel['name'] ?? ''),
                'location' => $hotel['destinationName'] ?? '',
                'description' => $hotelDetails->description ?? '',
                'address' => $this->format_address($hotelDetails),
                'categoryCode' => $hotel['categoryCode'] ?? null,
                'categoryName' => $hotel['categoryName'] ?? '',
                'stars' => intval(preg_replace('/\D/', '', $hotel['categoryName'] ?? '0')),
                'latitude' => floatval($hotel['latitude'] ?? 0),
                'longitude' => floatval($hotel['longitude'] ?? 0),
                'minRate' => $price,
                'real_price' => $hotel['minRate'],
                'actual_price' => $this->commission($hotel['minRate'],$validated['commission']),
                'currency' => $validated['currency'],
                'original_currency' => $hotel['currency'],
                'room_name' => $hotel['rooms'][0]['name'] ?? '',
                'images' => $this->format_image($hotelDetails->images ?? ''),
                'supplier_name' => "hotelbeds",
                'redirect' => "",
            ];
        }, $hotelsData);
    }

    private function commission($price,$commission) {
        $commission = $price * ($commission/ 100);
        return ($price + $commission);
    }
    /**
     * Get hotel details from database
     */
    private function get_hotel_details($hotelCode)
    {
        return DB::connection('hotelbeds')->table('hotels')
            ->where('hotel_code', $hotelCode)
            ->first() ?? (object)[];
    }

    /**
     * Sanitize hotel name
     */
    private function sanitize_name(string $name): string
    {
        return str_replace('&', '-', trim($name));
    }

    /**
     * Format address
     */
    private function format_address($hotelDetails): string
    {
        $addressParts = [
            $hotelDetails->address ?? '',
            $hotelDetails->city ?? '',
            $hotelDetails->postalCode ?? '',
        ];

        return implode(', ', array_filter($addressParts));
    }

    /**
     * Format image URL
     */
    private function format_image(?string $images): string
    {
        if (!$images) {
            return '';
        }

        $imageArray = explode(',', $images);
        $firstImage = trim($imageArray[0]);

        return !empty($firstImage) ? self::HOTEL_IMAGE_BASE_URL . $firstImage : '';
    }

    /**
     * Success response
     */
    private function success_response(array $data = [], string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            ...$data,
        ]);
    }

    /**
     * Error response
     */
    private function error_response(string $message, array $details = []): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            ...$details,
        ], 400);
    }

    /**
     * Details for Hotels using Hotelbeds API
     */

    public function hotel_details(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'hotel_id'        => 'required|string|max:100',
                'checkin'         => 'required|date|after_or_equal:today',
                'checkout'        => 'required|date|after:checkin',
                'adults'          => 'required|integer|min:1',
                'childs'          => 'nullable|integer|min:0',
                'child_age'       => 'nullable|string',
                'rooms'           => 'required|integer|min:1',
                'currency'        => 'required|string|size:3',
                'env'             => 'required|in:dev,pro',
                'api_credential_1'=> 'required|string',
                'api_credential_2'=> 'required|string',
                'commission'      => 'required|string',
                'supplier_name'=> 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->error_response("Validation Error", $validator->errors());
            }

            $paxes = [];

            // Adults
            for ($i = 0; $i < $request->adults; $i++) {
                $paxes[] = ["type" => "AD", "age" => null];
            }

            // Children (if available)
            if ($request->filled('childs') && $request->filled('child_age')) {
                $child_ages = json_decode($request->child_age, true);

                for ($i = 0; $i < $request->childs; $i++) {
                    $paxes[] = [
                        "type" => "CH",
                        "age"  => $child_ages[$i]['ages'] ?? 5
                    ];
                }
            }

            $url = $request->env === "dev"
                ? "https://api.test.hotelbeds.com/hotel-api/1.0/hotels"
                : "https://api.hotelbeds.com/hotel-api/1.0/hotels";

            $payload = [
                "stay" => [
                    "checkIn" => date("Y-m-d", strtotime($request->checkin)),
                    "checkOut" => date("Y-m-d", strtotime($request->checkout))
                ],
                "occupancies" => [
                    [
                        "rooms" => (int)$request->rooms,
                        "adults" => (int)$request->adults,
                        "children" => (int)$request->childs,
                        "paxes" => $paxes
                    ]
                ],
                "currency" => $request->currency,
                "hotels" => [
                    "hotel" => [$request->hotel_id]
                ],
            ];

            $response = Http::withHeaders([
                "Api-key" => $request->api_credential_1,
                "X-Signature" => hash("sha256", $request->api_credential_1 . $request->api_credential_2 . time()),
                "Accept" => "application/json",
            ])->post($url, $payload);

            $data = $response->json();

            if (isset($data["error"])) {
                return response()->json([
                    "success" => false,
                    "message" => $data["error"]["message"] ?? "Something went wrong",
                    "code"    => $data["error"]["code"] ?? null,
                ]);
            }

            if (!empty($data['hotels']['hotels'])) {
                $currency = $request->currency;
                $commission = $request->commission;
                $response = collect($data['hotels']['hotels'])->map(function ($rec) use ($currency,$commission) {

                    $hdata = DB::connection('hotelbeds')->table('hotels')->where('hotel_code', $rec['code'])->first();

                    $dest = DB::connection('hotelbeds')->table('destinations')->where('code', $rec['destinationCode'])->first();

                    $facilities = DB::connection('hotelbeds')->table('facilities')->whereIn('facilityGroupCode', [60, 70, 71, 73, 74, 80, 85, 90, 91])->select('facilityGroupCode', 'description', 'code as facilityCode')->get()->toArray();



                    $hotel_imgs = collect(explode(',', $hdata->images ?? ''))
                        ->map(fn($img) => 'http://photos.hotelbeds.com/giata/' . trim($img))
                        ->filter()
                        ->values()
                        ->toArray();

                    $facilities_values = collect($hdata->facilities ?? [])
                        ->filter(fn($f) => in_array($f['facilityGroupCode'], [60, 70, 71, 73, 74, 80, 85, 90, 91]))->map(fn($f) => collect($facilities)->firstWhere('facilityCode', $f['facilityCode']))
                        ->filter()
                        ->pluck('description')
                        ->toArray();

                    $room_facilities = collect($hdata->facilities ?? [])
                        ->filter(fn($f) => $f['facilityGroupCode'] == 60)
                        ->map(fn($f) => collect($facilities)
                            ->firstWhere('facilityCode', $f['facilityCode'])
                        )
                        ->filter()
                        ->pluck('description')
                        ->toArray();

                    $rooms = collect($rec['rooms'] ?? [])->map(function ($room) use ($room_facilities, $hotel_imgs,$rec,$currency,$commission) {
                        $price = convertCurrency($room['rates'][0]['net'], $rec['currency'],$currency);
                        return [
                            "id"          => $room['code'],
                            "name"        => $room['name'],
                            "price"       => $this->commission($price,$commission),
                            "actual_price"  => $room['rates'][0]['net'],
                            "per_day"  => $price,
                            "actual_per_day"  => $room['rates'][0]['net'],
                            "currency"    => $currency,
                            "original_currency" => $rec['currency'],
                            "refundable"  => $room['rates'][0]['cancellationPolicies'][0]['amount'] ?? 0,
                            "refund_date" => $room['rates'][0]['cancellationPolicies'][0]['from'] ?? null,
                            "images"      => $hotel_imgs,
                            "amenities"   => $room_facilities,
                            "options"     => collect($room['rates'])->map(function ($opt) use ($rec,$currency,$commission) {
                                $price = convertCurrency( $opt['net'], $rec['currency'],$currency);
                                return [
                                    "id"       => $opt['rateKey'],
                                    "price"    => $this->commission($price,$commission),
                                    "actual_price"    => $price,
                                    "per_day"  => $price,
                                    "actual_per_day"  => $opt['net'],
                                    "adults"   => $opt['adults'],
                                    "child"    => $opt['children'],
                                    "children_ages" => collect($opt['paxes'] ?? [])->pluck('age')->toArray(),
                                ];
                            }),
                            "room_data" =>[]
                        ];
                    });

                    return [
                        "h_id"      => $rec['code'],
                        "h_name"    => $rec['name'],
                        "agent_id"    => "",
                        "city"      => $dest->name ?? '',
                        "country"   => $hdata->countryCode ?? '',
                        "stars"     => intval(preg_replace('/\D/', '', $rec['categoryName'] ?? '0')),
                        "rating"    => $hdata->ranking ?? 0,
                        "lat"       => $rec['latitude'],
                        "lng"       => $rec['longitude'],
                        "address"   => "{$hdata->address}, {$hdata->city}, {$hdata->postalCode}",
                        "desc"      => $hdata->description->content ?? '',
                        "imgs"      => $hotel_imgs,
                        "amenities" => $facilities_values,
                        "checkin"   => request()->checkin,
                        "checkout"  => request()->checkout,
                        "rooms"     => $rooms,
                        "supplier_name"     => "hotelbeds",
                    ];
                });
            }

            return response()->json([
                "success"      => true,
                "response" => $response,
            ], 200);

        } catch (Exception $e) {
            return response()->json(["success" => false, "message" => "Server Error", "exception" => $e->getMessage()], 500);
        }
    }

    /**
     * Confirm and create a hotel booking using the Hotelbeds API.
     *
     * Expects the same shape of payload produced by the front-end booking flow:
     * env, guest (JSON array of travellers), user_data (JSON holder info),
     * booking_data (JSON containing the selected `option`/`room` rateKey and
     * booking meta such as checkin/checkout, adults, child), plus credentials.
     */
    public function hotel_booking(Request $request): JsonResponse
    {
        try {
            $validated = $this->validate_booking_input($request);

            $guests = $this->decode_json_field($validated['guest'], 'guest');
            $holder = $this->decode_json_field($validated['user_data'], 'user_data');
            $selection = $this->decode_json_field($validated['booking_data'], 'booking_data');

            $rateKey = $selection->option->id ?? $selection->room->id ?? null;
            if (!$rateKey) {
                return $this->error_response('Selected room rate could not be found.');
            }

            // Hotelbeds rate keys are time-sensitive, so re-validate via checkrates
            // and use the (possibly refreshed) rateKey it returns for the booking call.
            $checkrate_response = $this->call_checkrates($validated, $rateKey);
            $confirmedRateKey = $checkrate_response['hotel']['rooms'][0]['rates'][0]['rateKey'] ?? $rateKey;

            $paxes = $this->build_booking_paxes($guests, $selection);
            if (empty($paxes)) {
                return $this->error_response('No valid guest details were provided.');
            }

            $payload = $this->build_booking_payload($holder, $confirmedRateKey, $paxes, (float) $validated['tolerance']);
            $booking_response = $this->call_booking_api($validated, $payload);

            if (empty($booking_response['booking']['reference'])) {
                return $this->error_response('Booking could not be confirmed.', [
                    'booking_pnr' => '',
                    'response' => $booking_response,
                ]);
            }

            return $this->success_response([
                'booking_pnr' => $booking_response['booking']['reference'],
                'response' => $booking_response,
            ], 'Booking confirmed successfully');

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), ': 410')) {
                return $this->error_response('This room rate is no longer available. Please search again and select a fresh rate.', [
                    'booking_pnr' => '',
                    'response' => "This room rate is no longer available. Please search again and select a fresh rate",
                ]);
            }

            if (str_contains($e->getMessage(), 'PRODUCT_ERROR') && str_contains($e->getMessage(), 'Price has changed')) {
                return $this->error_response('The room price has changed since it was selected and is outside the allowed tolerance. Please refresh the room rate and try booking again.', [
                    'booking_pnr' => '',
                    'response' => "The room price has changed since it was selected and is outside the allowed tolerance. Please refresh the room rate and try booking again.",
                ]);
            }

            return $this->error_response('Server Error', [ 'booking_pnr' => '', 'response' => $e->getMessage()]);
        }
    }

    /**
     * Validate incoming booking request
     */
    private function validate_booking_input(Request $request): array
    {
        $rules = [
            'env' => 'required|in:dev,pro',
            'guest' => 'required|string',
            'user_data' => 'required|string',
            'booking_data' => 'required|string',
            'api_credential_1' => 'required|string',
            'api_credential_2' => 'required|string',
            'tolerance' => 'nullable|numeric|min:0|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new Exception('Validation Error: ' . json_encode($validator->errors()));
        }

        $validated = $validator->validated();
        $validated['tolerance'] = $validated['tolerance'] ?? 5;

        return $validated;
    }

    /**
     * Decode a JSON-encoded request field, throwing on malformed input
     */
    private function decode_json_field(string $json, string $field)
    {
        $decoded = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON provided for '{$field}'.");
        }

        return $decoded;
    }

    /**
     * Re-validate the selected rate with Hotelbeds before booking
     */
    private function call_checkrates(array $validated, string $rateKey): array
    {
        $timestamp = time();
        $signature = $this->generate_signature(
            $validated['api_credential_1'],
            $validated['api_credential_2'],
            $timestamp
        );

        $url = self::CHECKRATES_ENDPOINTS[$validated['env']];

        $response = Http::withHeaders([
            'Api-key' => $validated['api_credential_1'],
            'X-Signature' => $signature,
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip',
            'Content-Type' => 'application/json',
        ])->post($url, [
            'rooms' => [
                ['rateKey' => $rateKey],
            ],
        ]);

        if (!$response->successful()) {
            $body = $response->json() ?? $response->body();
            throw new Exception("Checkrates request failed: {$response->status()} - " . json_encode($body));
        }

        return $response->json();
    }

    /**
     * Build the paxes array from submitted guest details, grouped by room.
     *
     * `booking_data.booking_data.rooms` (if present) drives how many rooms the
     * guest list is split across; defaults to a single room when not provided.
     */
    private function build_booking_paxes(array $guests, $selection): array
    {
        $roomsCount = (int) ($selection->booking_data->rooms ?? 1);
        $roomsCount = max($roomsCount, 1);

        $totalGuests = count($guests);
        $guestsPerRoom = (int) ceil($totalGuests / $roomsCount);

        $paxes = [];
        $guestIndex = 0;

        for ($roomId = 1; $roomId <= $roomsCount; $roomId++) {
            for ($i = 0; $i < $guestsPerRoom && $guestIndex < $totalGuests; $i++, $guestIndex++) {
                $guest = $guests[$guestIndex];

                $type = strtolower($guest->traveller_type ?? '') === 'child' ? 'CH' : 'AD';

                $pax = [
                    'roomId' => $roomId,
                    'type' => $type,
                    'name' => $guest->first_name ?? '',
                    'surname' => $guest->last_name ?? '',
                ];

                if ($type === 'CH' && isset($guest->age)) {
                    $pax['age'] = (int) $guest->age;
                }

                $paxes[] = $pax;
            }
        }

        return $paxes;
    }

    /**
     * Build the Hotelbeds booking API payload
     */
    private function build_booking_payload($holder, string $rateKey, array $paxes, float $tolerance = 5): array
    {
        return [
            'holder' => [
                'name' => $holder->first_name ?? '',
                'surname' => $holder->last_name ?? '',
            ],
            'rooms' => [
                [
                    'rateKey' => $rateKey,
                    'paxes' => $paxes,
                ],
            ],
            'clientReference' => 'IntegrationAgency',
            'remark' => 'Booking remarks are to be written here.',
            'tolerance' => $tolerance,
        ];
    }

    /**
     * Submit the booking request to Hotelbeds
     */
    private function call_booking_api(array $validated, array $payload): array
    {
        $timestamp = time();
        $signature = $this->generate_signature(
            $validated['api_credential_1'],
            $validated['api_credential_2'],
            $timestamp
        );

        $url = self::BOOKING_ENDPOINTS[$validated['env']];

        $response = Http::withHeaders([
            'Api-key' => $validated['api_credential_1'],
            'X-Signature' => $signature,
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip',
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if (!$response->successful()) {
            $body = $response->json() ?? $response->body();
            throw new Exception("Booking request failed: {$response->status()} - " . json_encode($body));
        }

        return $response->json();
    }

}
