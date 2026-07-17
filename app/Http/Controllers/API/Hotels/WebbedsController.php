<?php

namespace App\Http\Controllers\API\Hotels;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Exception;
use Illuminate\Support\Facades\DB;

class WebbedsController extends BaseController
{
    /** DOTW XML gateway endpoint */
    private const API_ENDPOINT = 'https://xmldev.dotwconnect.com/gatewayV4.dotw';

    private const CURL_TIMEOUT_LIST  = 120;
    private const CURL_TIMEOUT_PRICE = 60;
    private const HOTEL_BATCH_SIZE   = 50;

    /**
     * Cache TTL in minutes.
     * Search results are cached per hotel_id so hotel_details can read them.
     * 60 minutes is enough for a typical session — adjust as needed.
     */
    private const CACHE_TTL_MINUTES = 60;

    /**
     * Cache key prefix — prevents collision with other suppliers.
     */
    private const CACHE_PREFIX = 'webbeds_hotel_';

    // -------------------------------------------------------------------------
    // PUBLIC ENDPOINTS
    // -------------------------------------------------------------------------

    /**
     * Search for hotels based on user criteria.
     *
     * After building the final result array every hotel's listing data
     * (name, address, stars, images, currency, room_name) is stored in
     * Laravel Cache keyed by hotel_id so hotel_details() can read it later.
     *
     * Cache key format:  webbeds_hotel_{hotel_id}
     * TTL              :  60 minutes (CACHE_TTL_MINUTES)
     */
    public function hotel_search(Request $request): JsonResponse
    {
        try {
            $validated   = $this->validate_input($request);
            $destination = $this->get_destination($validated['city']);

            if (!$destination) {
                return $this->error_response('Destination not found.', 404);
            }


            $searchXml      = $this->build_search_params($validated, $destination);
            $searchResponse = $this->make_curl_request($searchXml, self::CURL_TIMEOUT_LIST);

            if (!$searchResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $searchResponse['message'],
                ], 500);
            }

            $searchData = $this->parseXmlToArray($searchResponse['body']);
            $hotels     = $searchData['hotels']['hotel'] ?? [];

            if (empty($hotels)) {
                return $this->error_response('No hotels found for the given criteria.', 404);
            }


            if (isset($hotels['@attributes'])) {
                $hotels = [$hotels];
            }

            $hotels   = array_slice($hotels, 0, self::HOTEL_BATCH_SIZE);
            $hotelIds = array_map(fn($h) => $h['@attributes']['hotelid'], $hotels);


            $priceXml      = $this->build_price_batch_params($validated, $hotelIds);
            $priceResponse = $this->make_curl_request($priceXml, self::CURL_TIMEOUT_PRICE);

            if (!$priceResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $priceResponse['message'],
                ], 500);
            }

            $priceData = $this->parseXmlToArray($priceResponse['body']);
            $priceMap  = $this->build_price_map($priceData);


            $result = [];

            foreach ($hotels as $hotel) {
                $hotelId = $hotel['@attributes']['hotelid'];
                $price   = $priceMap[$hotelId] ?? null;

                if ($price === null) {
                    continue;
                }

                $roundedPrice = round($price['minRate'], 2);

                $images = [];
                foreach (array_slice($hotel['images']['hotelImages']['image'], 0, 20) as $value) {
                    if (!empty($value['url'])) {
                        $images[] = $value['url'];
                    }
                }


                $hotelRow = [
                    'hotel_id'          => $hotelId,
                    'name'              => $this->sanitize_name($hotel['hotelName'] ?? ''),
                    'address'           => $hotel['address'] ?? '',
                    'stars'             => $this->convert_rating($hotel['rating'] ?? 0),
                    'minRate'           => $roundedPrice,
                    'real_price'        => $roundedPrice,
                    'actual_price'      => $roundedPrice,
                    'currency'          => $price['currencyId'],
                    'original_currency' => $price['currencyId'],
                    'room_name'         => $price['roomName'],
                    'images'            => $hotel['images']['hotelImages']['thumb'] ?? '',
                    'all_images'        => $images ?? '',
                    'amenitie'        => $hotel['amenitie']['language']['amenitieItem'] ?? [],
                    'supplier_name'     => 'Webbeds',
                    'location'          => $destination->name,
                    'description'       => $hotel['description1']['language'] ??'',
                    'categoryCode'      => '',
                    'categoryName'      => '',
                    'latitude'          => '',
                    'longitude'         => '',
                    'redirect'          => '',
                ];


                $this->cache_hotel_listing($hotelId, $hotelRow);

                $result[] = $hotelRow;
            }

            usort($result, fn($a, $b) => $a['minRate'] <=> $b['minRate']);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // PRIVATE — CACHE HELPERS
    // -------------------------------------------------------------------------

    /**
     * Store a hotel's listing snapshot in Laravel Cache.
     *
     * Only the fields that hotel_details() needs to enrich its response
     * are stored — not the full hotel row — to keep memory usage low.
     *
     * @param  string $hotelId
     * @param  array  $hotelRow  Full row from hotel_search result
     */
    private function cache_hotel_listing(string $hotelId, array $hotelRow): void
    {
        $cacheKey = self::CACHE_PREFIX . $hotelId;


        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, [
                'h_name'    => $hotelRow['name'],
                'address'   => $hotelRow['address'],
                'stars'     => $hotelRow['stars'],
                'images'    => $hotelRow['all_images'],
                'description'  => $hotelRow['description'],
                'location' => $hotelRow['location'],
                'amenities' => $hotelRow['amenitie'],
            ], now()->addMinutes(self::CACHE_TTL_MINUTES));
        }
    }

    /**
     * Retrieve a hotel's cached listing data by hotel_id.
     * Returns null if not cached (cache expired or search was never run).
     *
     * @param  string $hotelId
     * @return array|null
     */
    private function get_cached_hotel_listing(string $hotelId): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $hotelId);
    }

    // -------------------------------------------------------------------------
    // PUBLIC ENDPOINTS — HOTEL DETAILS
    // -------------------------------------------------------------------------

    /**
     * Get full details for a single hotel by ID.
     *
     * Enrichment from cache:
     *   After fetching live price/room data from DOTW, the method reads the
     *   hotel's listing snapshot (name, address, stars, images, lat/lng) from
     *   Laravel Cache and merges it into the response.  If the cache key is
     *   missing (e.g. the user bookmarked a direct link), the fields default
     *   to empty strings — no error is thrown.
     *
     * Response shape (matches Hotelbeds unified format):
     *   h_id, h_name, agent_id, city, country, stars, rating,
     *   lat, lng, address, desc, imgs, amenities,
     *   checkin, checkout,
     *   rooms: [ id, name, price, actual_price, per_day, actual_per_day,
     *             currency, original_currency, refundable, refund_date,
     *             images, amenities, options: [...] ]
     */
    public function hotel_details(Request $request): JsonResponse
    {
        try {
            // ── Validate ──────────────────────────────────────────────────────
            $validator = Validator::make($request->all(), [
                'hotel_id'             => 'required|string|max:100',
                'checkin'              => 'required|date|after_or_equal:today',
                'checkout'             => 'required|date|after:checkin',
                'adults'               => 'required|integer|min:1',
                'childs'               => 'nullable|integer|min:0',
                'child_age'            => 'nullable|string',
                'rooms'                => 'required|integer|min:1',
                'currency'             => 'required|string|size:3',
                'env'                  => 'required|in:dev,pro',
                'api_credential_1'     => 'required|string',
                'api_credential_2'     => 'required|string',
                'api_credential_3'     => 'required|string',
                'commission'           => 'required|string',
                'supplier_name'        => 'required|string',
                'nationality'          => 'nullable|string',
                'country_of_residence' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation Error', 422, [
                    'errors' => $validator->errors()->toArray(),
                ]);
            }

            $v          = $validator->validated();
            $commission = (float) $v['commission'];
            $hotelId    = $v['hotel_id'];
            $currency   = $v['currency'];

            $nights = max(1, (int) (
                (strtotime($v['checkout']) - strtotime($v['checkin'])) / 86400
            ));

            $childAges = [];
            if (!empty($v['child_age'])) {
                $childAges = array_map('intval', explode(',', $v['child_age']));
            }


            $cachedListing = $this->get_cached_hotel_listing($hotelId);


            $detailXml      = $this->build_hotel_detail_params($v, $hotelId, $childAges);
            $detailResponse = $this->make_curl_request($detailXml, self::CURL_TIMEOUT_PRICE);

            if (!$detailResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $detailResponse['message'],
                ], 500);
            }

            $detailData = $this->parseXmlToArray($detailResponse['body']);

            if (strtoupper($detailData['successful'] ?? '') !== 'TRUE') {
                return $this->error_response('Hotel not found or no availability.', 404);
            }


            $hotelNode = $detailData['hotel']
                ?? $detailData['hotels']['hotel']
                ?? null;

            if (empty($hotelNode)) {
                return $this->error_response('No hotel data returned.', 404);
            }


            if (!isset($hotelNode['@attributes'])) {
                $hotelNode = $hotelNode[0];
            }


            $rawRooms = $hotelNode['rooms']['room'] ?? [];

            if (empty($rawRooms)) {
                return $this->error_response('No rooms available for the selected dates.', 404);
            }


            if (isset($rawRooms['@attributes'])) {
                $rawRooms = [$rawRooms];
            }


            if (!isset($rawRooms[0]) && !isset($rawRooms['@attributes'])) {
                $rawRooms = array_values($rawRooms);
            }

            $roomsList = [];

            foreach ($rawRooms as $room) {
                $roomAdults   = (int) ($room['@attributes']['adults']   ?? 0);
                $roomChildren = (int) ($room['@attributes']['children'] ?? 0);

                $roomTypes = $room['roomType'] ?? [];

                if (empty($roomTypes)) {
                    continue;
                }


                if (isset($roomTypes['@attributes'])) {
                    $roomTypes = [$roomTypes];
                } elseif (!isset($roomTypes[0])) {
                    $roomTypes = array_values($roomTypes);
                }

                foreach ($roomTypes as $roomType) {
                    $roomTypeCode = $roomType['@attributes']['roomtypecode'] ?? '';
                    $roomTypeName = $roomType['name'] ?? '';
                    $rateBases    = $roomType['rateBases']['rateBasis'] ?? [];

                    if (empty($rateBases)) {
                        continue;
                    }

                    if (isset($rateBases['@attributes'])) {
                        $rateBases = [$rateBases];
                    }

                    $options = [];

                    foreach ($rateBases as $rateBasis) {

                        $rateBasisRunno  = (string) ($rateBasis['@attributes']['runno']      ?? '');
                        $rateBasisId     = (string) ($rateBasis['@attributes']['id']         ?? '');
                        $rateBasisDesc   = (string) ($rateBasis['@attributes']['description'] ?? '');
                        $rateBasisStatus = (string) ($rateBasis['status'] ?? 'unchecked');
                        $total           = (float)  ($rateBasis['total'] ?? 0);
                        $currencyId      = (string) ($rateBasis['rateType']['@attributes']['currencyid'] ?? '520');
                        $allocationDetails = (string) ($rateBasis['allocationDetails'] ?? '');
                        $isBookable      = (string) ($rateBasis['isBookable'] ?? 'yes');

                        if ($total <= 0 || $isBookable !== 'yes' || empty($allocationDetails)) {
                            continue;
                        }

                        $sellTotal  = $commission > 0
                            ? round($total / (1 - $commission / 100), 2)
                            : round($total, 2);

                        $netTotal   = round($total, 2);
                        $sellPerDay = round($sellTotal / $nights, 2);
                        $netPerDay  = round($netTotal  / $nights, 2);

                        $cancelRules = $this->parse_cancellation_rules(
                            $rateBasis['cancellationRules']['rule'] ?? []
                        );

                        $firstPenalty = collect($cancelRules)->firstWhere('type', 'penalty');

                        $passengersRequired = (int) ($rateBasis['passengerNamesRequiredForBooking'] ?? 1);

                        $options[] = [
                            'id'                 => $rateBasisRunno,
                            'rate_basis_id'      => $rateBasisId,
                            'description'        => $rateBasisDesc,
                            'status'             => $rateBasisStatus,
                            'passengers_required' => $passengersRequired,
                            'price'              => $sellTotal,
                            'actual_price'       => $netTotal,
                            'per_day'            => $sellPerDay,
                            'actual_per_day'     => $netPerDay,
                            'adults'             => $roomAdults,
                            'child'              => $roomChildren,
                            'children_ages'      => $childAges,
                            'currency_id'        => $currencyId,
                            'refundable'         => $firstPenalty['cancel_charge'] ?? 0,
                            'refund_date'        => $firstPenalty['from_date']     ?? null,
                            'cancellation_rules' => $cancelRules,
                            'allocation_details' => $allocationDetails,
                            'tariff_notes'       => trim($rateBasis['tariffNotes'] ?? ''),
                            'meal_included'      => $rateBasisId !== '0',
                            'left_to_sell'       => (int) ($rateBasis['leftToSell'] ?? 0),
                            'on_request'         => (int) ($rateBasis['onRequest']  ?? 0),
                        ];
                    }

                    if (empty($options)) {
                        continue;
                    }

                    usort($options, fn($a, $b) => $a['price'] <=> $b['price']);
                    $cheapest = $options[0];

                    $roomsList[] = [
                        'id'                => $roomTypeCode,
                        'name'              => $roomTypeName,
                        'price'             => $cheapest['price'],
                        'actual_price'      => $cheapest['actual_price'],
                        'per_day'           => $cheapest['per_day'],
                        'actual_per_day'    => $cheapest['actual_per_day'],
                        'currency'          => $currency,
                        'original_currency' => $cheapest['currency_id'],
                        'refundable'        => $cheapest['refundable'],
                        'refund_date'       => $cheapest['refund_date'],
                        'meal_included'     => $cheapest['meal_included'],
                        'description'       => $cheapest['description'],
                        'images'            => [],
                        'amenities'         => [],
                        'adults'            => $roomAdults,
                        'children'          => $roomChildren,
                        'supplier_name'     => $v['supplier_name'],
                        'options'           => $options,
                        'room_data'         => [
                            'product_id'           => $hotelId,
                            'checkin'              => $v['checkin'],
                            'checkout'             => $v['checkout'],
                            'adults'               => $roomAdults,
                            'children'             => $childAges,
                            'rooms'                => (int) $v['rooms'],
                            'currency'             => $currency,
                            'nationality'          => $v['nationality']  ?? '',
                            'country_of_residence' => $v['country_of_residence'] ?? '',
                            'supplier_name'        => $v['supplier_name'],
                            'room_type_code'       => $roomTypeCode,
                            'room_name'            => $roomTypeName,
                            'rate_basis_runno'                  => $cheapest['id'],
                            'selected_rate_basis'               => $cheapest['rate_basis_id'],
                            'rate_description'                  => $cheapest['description'],
                            'allocation_details'                => $cheapest['allocation_details'] ?? '',
                            'passengers_required'               => $cheapest['passengers_required'],
                            'price'                => $cheapest['price'],
                            'actual_price'         => $cheapest['actual_price'],
                            'per_day'              => $cheapest['per_day'],
                            'currency_id'          => $cheapest['currency_id'],
                            'refundable'           => $cheapest['refundable'],
                            'refund_date'          => $cheapest['refund_date'],
                            'meal_included'        => $cheapest['meal_included'],
                        ],
                    ];
                }
            }

            if (empty($roomsList)) {
                return $this->error_response('No priced rooms available for the selected dates.', 404);
            }

            usort($roomsList, fn($a, $b) => $a['price'] <=> $b['price']);

            return response()->json([
                'success'  => true,
                'response' => [
                    [
                        'h_id'          => $hotelId,
                        'h_name'        => $cachedListing['h_name']    ?? '',
                        'address'       => $cachedListing['address']   ?? '',
                        'stars'         => $cachedListing['stars']     ?? 0,
                        'imgs'          => $cachedListing['images']   ?? [],
                        'lat'           => '',
                        'lng'           => '',
                        'agent_id'      => '',
                        'city'          => '',
                        'country'       => $cachedListing['location']     ?? 0,
                        'rating'        => $cachedListing['stars']     ?? 0,
                        'desc'          => $cachedListing['description'] ?? '',
                        'amenities'     => $cachedListing['amenities'] ?? [],
                        'checkin'       => $v['checkin'],
                        'checkout'      => $v['checkout'],
                        'supplier_name' => $v['supplier_name'],
                        'rooms'         => $roomsList,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // PUBLIC ENDPOINTS — SAVE BOOKING
    // -------------------------------------------------------------------------

    /**
     * Temporarily save a booking on the DOTW server (savebooking command).
     *
     * A saved booking does NOT hold allotments and the price is not guaranteed.
     * It must be finalised later via bookitinerary().
     *
     * Expected request fields:
     *   api_credential_1      – DOTW username
     *   api_credential_2      – DOTW password (plain; will be MD5-hashed)
     *   api_credential_3      – DOTW customer ID
     *   checkin               – Y-m-d  (converted to Unix timestamp internally)
     *   checkout              – Y-m-d  (converted to Unix timestamp internally)
     *   currency              – 3-char ISO code (mapped to DOTW currency ID)
     *   product_id            – DOTW hotel/product ID
     *   customer_reference    – your own booking reference string
     *   rooms                 – array of room objects (see below)
     *
     * Each room object:
     *   room_type_code            – roomTypeCode from hotel_details response
     *   selected_rate_basis       – rateBasis id chosen by the user
     *   allocation_details        – allocationDetails token from hotel_details
     *   adults                    – number of adults
     *   actual_adults             – actual adult count (usually same as adults)
     *   children                  – array of child ages  (empty = no children)
     *   actual_children           – array of actual child ages
     *   extra_bed                 – 0 or 1
     *   nationality               – DOTW nationality code (default 167)
     *   country_of_residence      – DOTW residence code  (default 167)
     *   passengers                – array of passenger objects:
     *       salutation            – DOTW salutation code (e.g. 3801 = Mr)
     *       first_name
     *       last_name
     *       leading               – bool, true for the lead passenger
     *
     * Response on success:
     *   success        true
     *   booking_ref    DOTW booking reference
     *   customer_ref   echo of customerReference sent
     *   status         booking status string from DOTW
     *   raw            full parsed DOTW response (for debugging / storage)
     */
    public function hotel_booking(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'api_credential_1' => 'required|string|min:5',
                'api_credential_2' => 'required|string|min:5',
                'api_credential_3' => 'required|string|min:5',
                'guest'            => 'required',
                'user_data'        => 'required',
                'booking_data'     => 'required',
                'device_payload'   => 'nullable|string',
                'env'              => 'nullable|in:dev,pro',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation Error', 422, [
                    'errors' => $validator->errors()->toArray(),
                ]);
            }

            $v = $validator->validated();


            $salutationMap = [
                'male'   => '3801',
                'female' => '3802',
                'mr'     => '3801',
                'mrs'    => '3802',
                'miss'   => '3803',
                'ms'     => '3802',
                'dr'     => '3804',
            ];

            $roomData  = json_decode($v['booking_data']);


            $checkin     = $roomData->room_data->checkin;
            $checkout    = $roomData->room_data->checkout;
            $productId   = $roomData->room_data->product_id;
            $adults      = (int) $roomData->room_data->adults;
            $children    = $roomData->room_data->children ?? [];
            $roomTypeCode      = $roomData->room_data->room_type_code;
            $selectedRateBasis = $roomData->room_data->selected_rate_basis;

            $nationality = '167';
            $residence   = '167';


            $allocationDetails = $roomData->room_data->allocation_details ?? '';

            $guest              = json_decode($v['guest']);
            $passengersRequired = (int) ($roomData->room_data->passengers_required ?? 1);

            $passengers = [];

            foreach ($guest as $i => $traveller) {
                $titleKey   = strtolower(trim($traveller->title ?? ''));
                $salutation = $salutationMap[$titleKey] ?? '3801';

                $passengers[] = [
                    'salutation' => $salutation,
                    'first_name' => $traveller->first_name,
                    'last_name'  => $traveller->last_name,
                    'leading'    => ($i === 0),
                ];
            }


            $leadPassenger = $passengers[0] ?? [
                'salutation' => '3801',
                'first_name' => 'Guest',
                'last_name'  => 'Guest',
            ];

            while (count($passengers) < $passengersRequired) {
                $passengers[] = [
                    'salutation' => $leadPassenger['salutation'],
                    'first_name' => $leadPassenger['first_name'],
                    'last_name'  => $leadPassenger['last_name'],
                    'leading'    => false,
                ];
            }


            $customerReference = strtoupper('WB-' . $productId . '-' . date('Ymd', strtotime($checkin)) . '-' . substr(bin2hex(random_bytes(3)), 0, 6));


            $normalised = [
                'api_credential_1'   => $v['api_credential_1'],
                'api_credential_2'   => $v['api_credential_2'],
                'api_credential_3'   => $v['api_credential_3'],
                'customer_reference' => $customerReference,
                'hotel_id'           => $productId,
                'checkin'            => $checkin,
                'checkout'           => $checkout,
                'rooms' => [
                    [
                        'room_type_code'       => $roomTypeCode,
                        'rate_basis_id'        => $selectedRateBasis,
                        'allocation_details'   => $allocationDetails,
                        'adults'               => $adults,
                        'actual_adults'        => $adults,
                        'children'             => $children,
                        'extra_bed'            => 0,
                        'nationality'          => $nationality,
                        'country_of_residence' => $residence,
                        'passengers'           => $passengers,
                    ],
                ],
            ];


            $xml      = $this->build_save_booking_xml($normalised);
            $response = $this->make_curl_request($xml, self::CURL_TIMEOUT_PRICE);



            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'],
                ], 500);
            }

            $data = $this->parseXmlToArray($response['body']);


            if (strtoupper($data['successful'] ?? '') !== 'TRUE') {
                $errorMsg = $data['errorMessage']
                    ?? ($data['errors']['error'] ?? 'Save booking failed.');

                return $this->error_response(
                    is_array($errorMsg) ? implode(', ', $errorMsg) : $errorMsg,
                    422
                );
            }


            $bookingCode  = (string) ($data['returnedCode'] ?? '');
            $rawSvcCodes  = $data['returnedServiceCodes']['returnedServiceCode'] ?? [];

            if (empty($bookingCode)) {
                return $this->error_response(
                    'Booking saved but no bookingCode returned by DOTW.',
                    502
                );
            }


            if (!is_array($rawSvcCodes)) {
                $serviceCodes = [(string) $rawSvcCodes];
            } else {

                $serviceCodes = [];
                foreach ($rawSvcCodes as $sc) {
                    $serviceCodes[] = is_array($sc) ? (string) ($sc['_value'] ?? reset($sc)) : (string) $sc;
                }
            }


            $itinParams1 = [
                'api_credential_1' => $v['api_credential_1'],
                'api_credential_2' => $v['api_credential_2'],
                'api_credential_3' => $v['api_credential_3'],
                'booking_code'     => $bookingCode,
                'confirm'          => 'no',
                'service_codes'    => $serviceCodes,
                'total_price'      => $roomData->room_data->price ?? 0,
            ];

            $itinXml1      = $this->build_book_itinerary_xml($itinParams1, null);
            $itinResponse1 = $this->make_curl_request($itinXml1, self::CURL_TIMEOUT_PRICE);



            if (!$itinResponse1['success']) {
                return response()->json([
                    'success'      => false,
                    'message'      => 'bookitinerary(confirm=no) failed: ' . $itinResponse1['message'],
                    'booking_code' => $bookingCode,
                ], 500);
            }

            $itinData1 = $this->parseXmlToArray($itinResponse1['body']);

            if (strtoupper($itinData1['successful'] ?? '') !== 'TRUE') {
                $errMsg = $itinData1['error']['details']
                    ?? ($itinData1['errorMessage'] ?? 'bookitinerary check failed.');
                return $this->error_response((string) $errMsg, 422, [
                    'booking_code' => $bookingCode,
                ]);
            }


            $products = $itinData1['product'] ?? [];
            if (empty($products)) {
                return $this->error_response('No product in bookitinerary response.', 502, [
                    'booking_code' => $bookingCode,
                    'raw'          => $itinData1,
                ]);
            }
            if (isset($products['@attributes'])) {
                $products = [$products];
            }

            $testServices   = [];
            $totalTestPrice = 0;

            foreach ($products as $product) {
                $svcCode         = (string) ($product['@attributes']['code'] ?? '');
                $freshAlloc      = (string) ($product['allocationDetails']   ?? '');
                // confirm=no response: price is in <price> element (not servicePrice)
                $svcPrice        = (float)  ($product['price']               ?? 0);
                $totalTestPrice += $svcPrice;

                if (!empty($svcCode) && !empty($freshAlloc)) {
                    $testServices[] = [
                        'code'               => $svcCode,
                        'price'              => $svcPrice,
                        'allocation_details' => $freshAlloc,
                    ];
                }
            }

            if (empty($testServices)) {
                return $this->error_response(
                    'No service data returned from bookitinerary check.',
                    502,
                    ['booking_code' => $bookingCode]
                );
            }


            $tokenResult = $this->rezpayments_tokenize([
                'cardName'     => 'Usama Malik',
                'cardNumber'   => '4444333322221111',
                'expiryYear'   => '2026',
                'expiryMonth'  => '06',
                'securityCode' => '123',
                'env'          => $v['env'] ?? 'dev',
            ]);




            if (!$tokenResult['success']) {
                return $this->error_response(
                    'Payment tokenization failed: ' . $tokenResult['message'],
                    422,
                    ['booking_code' => $bookingCode]
                );
            }

            $ccToken = $tokenResult['token'];


            $generatedDevicePayload = $this->generate_device_payload(
                $v['end_user_ip'] ?? '127.0.0.1',
                $request->header('User-Agent', 'Mozilla/5.0'),
                $request->header('Accept-Language', 'en-US,en;q=0.9')
            );


            $itinParams2 = [
                'api_credential_1' => $v['api_credential_1'],
                'api_credential_2' => $v['api_credential_2'],
                'api_credential_3' => $v['api_credential_3'],
                'booking_code'     => $bookingCode,
                'confirm'          => 'preauth',
                'test_services'    => $testServices,
                'total_price'      => $totalTestPrice,
                'payment_method'   => 'CC_PAYMENT_NET',
                'cc_token'         => $ccToken,
                'cc_charge'        => $totalTestPrice,
                'avs_first_name'   => 'Carol',
                'avs_last_name'    => 'Pala',
                'avs_address'      => '518 East Cape Shores Drive, Lewes, DE, USA',
                'avs_zip'          => '19958',
                'avs_country'      => 'US',
                'avs_city'         => 'Lewes',
                'avs_email'        => 'desenvolvimento@lemontech.com.br',
                'avs_phone'        => '+13023121794',
                'end_user_ip'      => '127.0.0.1',
                'device_payload'   => $v['device_payload'] ?? $generatedDevicePayload,
            ];

            $itinXml2      = $this->build_book_itinerary_xml($itinParams2, $testServices);
            $itinResponse2 = $this->make_curl_request($itinXml2, self::CURL_TIMEOUT_PRICE);



            if (!$itinResponse2['success']) {
                return response()->json([
                    'success'      => false,
                    'message'      => 'bookitinerary(confirm=preauth) failed: ' . $itinResponse2['message'],
                    'booking_code' => $bookingCode,
                ], 500);
            }

            $itinData2 = $this->parseXmlToArray($itinResponse2['body']);

            if (strtoupper($itinData2['successful'] ?? '') !== 'TRUE') {
                $errMsg = $itinData2['error']['details']
                    ?? ($itinData2['errorMessage'] ?? 'Booking preauth failed.');
                return $this->error_response((string) $errMsg, 422, [
                    'booking_code' => $bookingCode,
                    'raw'          => $itinData2,
                ]);
            }


            $preauthProducts = $itinData2['product'] ?? [];
            if (isset($preauthProducts['@attributes'])) {
                $preauthProducts = [$preauthProducts];
            }

            $yesServices     = [];
            $orderCode       = '';
            $authorisationId = '';

            foreach ($preauthProducts as $pp) {
                $ppCode  = (string) ($pp['@attributes']['code'] ?? '');
                $ppAlloc = (string) ($pp['allocationDetails']   ?? '');
                $ppPrice = (float)  ($pp['price']               ?? 0);

                if (!empty($ppCode)) {
                    $yesServices[] = [
                        'code'               => $ppCode,
                        'price'              => $ppPrice,
                        'allocation_details' => $ppAlloc,
                    ];
                }
            }


            $orderCode       = (string) ($itinData2['threeDSData']['orderCode']      ?? '');
            $authorisationId = (string) ($itinData2['threeDSData']['authorisationId'] ?? '');

            if (empty($orderCode) || empty($authorisationId)) {
                return $this->error_response(
                    'Preauth succeeded but orderCode/authorisationId missing in response.',
                    502,
                    ['booking_code' => $bookingCode, 'raw' => $itinData2]
                );
            }


            $itinParams3 = [
                'api_credential_1'  => $v['api_credential_1'],
                'api_credential_2'  => $v['api_credential_2'],
                'api_credential_3'  => $v['api_credential_3'],
                'booking_code'      => $bookingCode,
                'confirm'           => 'yes',
                'test_services'     => $yesServices,
                'total_price'       => $totalTestPrice,
                'cc_charge'         => $totalTestPrice,
                'order_code'        => $orderCode,
                'authorisation_id'  => $authorisationId,
                'end_user_ip'       => '127.0.0.1',
                'device_payload'    => $v['device_payload'] ?? $generatedDevicePayload,
            ];

            $itinXml3      = $this->build_book_itinerary_xml($itinParams3, $yesServices);
            $itinResponse3 = $this->make_curl_request($itinXml3, self::CURL_TIMEOUT_PRICE);




            if (!$itinResponse3['success']) {
                return response()->json([
                    'success'      => false,
                    'message'      => 'bookitinerary(confirm=yes) failed: ' . $itinResponse3['message'],
                    'booking_code' => $bookingCode,
                ], 500);
            }

            $itinData3 = $this->parseXmlToArray($itinResponse3['body']);

            if (strtoupper($itinData3['successful'] ?? '') !== 'TRUE') {
                $errMsg = $itinData3['error']['details']
                    ?? ($itinData3['errorMessage'] ?? 'Final booking confirmation failed.');
                return $this->error_response((string) $errMsg, 422, [
                    'booking_code' => $bookingCode,
                    'raw'          => $itinData3,
                ]);
            }


            $bookingRef = $itinData3['bookingReferenceNumber']
                ?? ($itinData3['product']['bookingReferenceNumber'] ?? $bookingCode);

            return response()->json([
                'success'            => true,
                'booking_pnr'       => $bookingCode,
                'booking_reference'  => (string) $bookingRef,
                'status'             => 'confirmed',
                'message'            => 'Booking confirmed successfully.',
                'response'           => $itinData3,
            ]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }


    // -------------------------------------------------------------------------
    // PRIVATE — HTTP
    // -------------------------------------------------------------------------

    private function make_curl_request(string $xml, int $timeout = 60): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL             => self::API_ENDPOINT,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $xml,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => ['Content-Type: text/xml; charset=UTF-8'],
            CURLOPT_TIMEOUT         => $timeout,
            CURLOPT_CONNECTTIMEOUT  => 15,
            CURLOPT_BUFFERSIZE      => 131072,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_ENCODING        => '',
        ]);

        $body      = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'message' => "cURL error: {$curlError}"];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'message' => "HTTP error: {$httpCode}"];
        }

        return ['success' => true, 'body' => $body];
    }

    // -------------------------------------------------------------------------
    // PRIVATE — XML BUILDERS
    // -------------------------------------------------------------------------

    private function build_search_params(array $validated, object $destination): string
    {
        $password    = md5($validated['api_credential_2']);
        $childrenXml = $this->build_children_xml($validated['children'] ?? []);
        $nationality = $validated['nationality'] ?? '167';
        $residence   = $validated['country_of_residence'] ?? '167';

        $conditionXml = '';
        if (!empty($validated['rating'])) {
            $conditionXml = <<<XML
            <c:condition>
                <a:condition>
                    <fieldName>rating</fieldName>
                    <fieldTest>equals</fieldTest>
                    <fieldValues>
                        <fieldValue>{$validated['rating']}</fieldValue>
                    </fieldValues>
                </a:condition>
            </c:condition>
            XML;
        }

        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <customer>
            <username>{$validated['api_credential_1']}</username>
            <password>{$password}</password>
            <id>{$validated['api_credential_3']}</id>
            <source>1</source>
            <product>hotel</product>
            <language>en</language>
            <request command="searchhotels">
                <bookingDetails>
                    <fromDate>{$validated['checkin']}</fromDate>
                    <toDate>{$validated['checkout']}</toDate>
                    <currency>520</currency>
                    <rooms no="1">
                        <room runno="0">
                            <adultsCode>{$validated['adults']}</adultsCode>
                            {$childrenXml}
                            <rateBasis>0</rateBasis>
                            <passengerNationality>{$nationality}</passengerNationality>
                            <passengerCountryOfResidence>{$residence}</passengerCountryOfResidence>
                        </room>
                    </rooms>
                </bookingDetails>
                <return>
                    <filters xmlns:a="http://us.dotwconnect.com/xsd/atomicCondition"
                             xmlns:c="http://us.dotwconnect.com/xsd/complexCondition">
                        <city>{$destination->code}</city>
                        <noPrice>true</noPrice>
                        {$conditionXml}
                    </filters>
                    <fields>
                        <field>hotelName</field>
                        <field>address</field>
                        <field>rating</field>
                        <field>images</field>
                        <field>amenitie</field>
                        <field>description1</field>
                    </fields>
                </return>
            </request>
        </customer>
        XML;
    }

    private function build_price_batch_params(array $validated, array $hotelIds): string
    {
        $password    = md5($validated['api_credential_2']);
        $childrenXml = $this->build_children_xml($validated['children'] ?? []);
        $nationality = $validated['nationality'] ?? '167';
        $residence   = $validated['country_of_residence'] ?? '167';

        $fieldValues = implode('', array_map(
            fn($id) => "<fieldValue>{$id}</fieldValue>",
            $hotelIds
        ));

        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <customer>
            <username>{$validated['api_credential_1']}</username>
            <password>{$password}</password>
            <id>{$validated['api_credential_3']}</id>
            <source>1</source>
            <product>hotel</product>
            <language>en</language>
            <request command="searchhotels">
                <bookingDetails>
                    <fromDate>{$validated['checkin']}</fromDate>
                    <toDate>{$validated['checkout']}</toDate>
                    <currency>520</currency>
                    <rooms no="1">
                        <room runno="0">
                            <adultsCode>{$validated['adults']}</adultsCode>
                            {$childrenXml}
                            <rateBasis>1</rateBasis>
                            <passengerNationality>{$nationality}</passengerNationality>
                            <passengerCountryOfResidence>{$residence}</passengerCountryOfResidence>
                        </room>
                    </rooms>
                </bookingDetails>
                <return>
                    <filters xmlns:a="http://us.dotwconnect.com/xsd/atomicCondition"
                             xmlns:c="http://us.dotwconnect.com/xsd/complexCondition">
                        <c:condition>
                            <a:condition>
                                <fieldName>hotelId</fieldName>
                                <fieldTest>in</fieldTest>
                                <fieldValues>
                                    {$fieldValues}
                                </fieldValues>
                            </a:condition>
                        </c:condition>
                    </filters>
                </return>
            </request>
        </customer>
        XML;
    }

    private function build_children_xml(array $children): string
    {
        $count = count($children);

        if ($count === 0) {
            return '<children no="0"/>';
        }

        $xml = "<children no=\"{$count}\">";
        foreach ($children as $index => $age) {
            $xml .= "<child runno=\"{$index}\">{$age}</child>";
        }
        return $xml . '</children>';
    }

    private function build_hotel_detail_params(array $v, string $hotelId, array $childAges): string
    {
        $password    = md5($v['api_credential_2']);
        $childrenXml = $this->build_children_xml($childAges);
        $nationality = $v['nationality'] ?? '167';
        $residence   = $v['country_of_residence'] ?? '167';


        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$v['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$v['api_credential_3']}</id>
    <source>1</source>
    <product>hotel</product>
    <request command="getrooms">
        <bookingDetails>
            <fromDate>{$v['checkin']}</fromDate>
            <toDate>{$v['checkout']}</toDate>
            <currency>520</currency>
            <rooms no="1">
                <room runno="0">
                    <adultsCode>{$v['adults']}</adultsCode>
                    {$childrenXml}
                    <rateBasis>-1</rateBasis>
                    <passengerNationality>{$nationality}</passengerNationality>
                    <passengerCountryOfResidence>{$residence}</passengerCountryOfResidence>
                </room>
            </rooms>
            <productId>{$hotelId}</productId>
        </bookingDetails>
    </request>
</customer>
XML;
    }

    /**
     * Build the savebooking XML payload.
     *
     * Timestamps: DOTW expects Unix timestamps for fromDate/toDate.
     * Children:   Two sibling elements are required — <children> (booked) and
     *             <actualChildren> — each with a `no` attribute.
     * Passengers: The lead passenger gets leading="yes"; others get no attribute.
     */
    private function build_save_booking_xml(array $v): string
    {
        $password  = md5($v['api_credential_2']);
        $roomCount = count($v['rooms']);
        $roomsXml  = '';

        foreach ($v['rooms'] as $index => $room) {
            $children      = $room['children'] ?? [];
            $childCount    = count($children);
            $extraBed      = $room['extra_bed'] ?? 0;
            $nationality   = $room['nationality']          ?: '167';
            $residence     = $room['country_of_residence'] ?: '167';
            $actualAdults  = $room['actual_adults'];


            if ($childCount === 0) {
                $childrenXml       = "<children no=\"0\"/>";
                $actualChildrenXml = "<actualChildren no=\"0\"/>";
            } else {
                $childrenXml       = "<children no=\"{$childCount}\">";
                $actualChildrenXml = "<actualChildren no=\"{$childCount}\">";
                foreach ($children as $ci => $age) {
                    $childrenXml       .= "<child runno=\"{$ci}\">{$age}</child>";
                    $actualChildrenXml .= "<actualChild runno=\"{$ci}\">{$age}</actualChild>";
                }
                $childrenXml       .= '</children>';
                $actualChildrenXml .= '</actualChildren>';
            }


            $passengersXml = '<passengersDetails>';

            foreach ($room['passengers'] as $passenger) {
                $leadingAttr    = $passenger['leading'] ? ' leading="yes"' : '';
                $passengersXml .= <<<XML

                        <passenger{$leadingAttr}>
                            <salutation>{$passenger['salutation']}</salutation>
                            <firstName>{$this->sanitize_name($passenger['first_name'])}</firstName>
                            <lastName>{$this->sanitize_name($passenger['last_name'])}</lastName>
                        </passenger>
XML;
            }

            $passengersXml .= '
                    </passengersDetails>';


            $roomsXml .= <<<XML

                <room>
                    <roomTypeCode>{$room['room_type_code']}</roomTypeCode>
                    <selectedRateBasis>{$room['rate_basis_id']}</selectedRateBasis>
                    <allocationDetails>{$room['allocation_details']}</allocationDetails>
                    <adultsCode>{$room['adults']}</adultsCode>
                    <actualAdults>{$actualAdults}</actualAdults>
                    {$childrenXml}
                    {$actualChildrenXml}
                    <passengerNationality>{$nationality}</passengerNationality>
                    <passengerCountryOfResidence>{$residence}</passengerCountryOfResidence>
                    {$passengersXml}
                </room>
XML;
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$v['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$v['api_credential_3']}</id>
    <source>1</source>
    <product>hotel</product>
    <request command="savebooking">
        <bookingDetails>
            <fromDate>{$v['checkin']}</fromDate>
            <toDate>{$v['checkout']}</toDate>
            <currency>520</currency>
            <productId>{$v['hotel_id']}</productId>
            <rooms>
                {$roomsXml}
            </rooms>
        </bookingDetails>
    </request>
</customer>
XML;
    }

    // -------------------------------------------------------------------------
    // PRIVATE — RESPONSE PARSERS
    // -------------------------------------------------------------------------

    private function parseXmlToArray(string $xmlString): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            $errors = array_map(fn($e) => $e->message, libxml_get_errors());
            libxml_clear_errors();
            return ['error' => 'Failed to parse XML response', 'details' => $errors];
        }

        return json_decode(json_encode($xml), true);
    }

    private function build_price_map(array $priceData): array
    {
        $map    = [];
        $hotels = $priceData['hotels']['hotel'] ?? [];

        if (empty($hotels)) {
            return $map;
        }

        if (isset($hotels['@attributes'])) {
            $hotels = [$hotels];
        }

        foreach ($hotels as $hotel) {
            $hotelId = $hotel['@attributes']['hotelid'] ?? null;

            if (!$hotelId) {
                continue;
            }

            $room = $hotel['rooms']['room'] ?? null;

            if (empty($room)) {
                continue;
            }

            if (!isset($room['@attributes'])) {
                $room = $room[0];
            }

            $roomTypes = $room['roomType'] ?? null;

            if (empty($roomTypes)) {
                continue;
            }

            $firstRoomType = isset($roomTypes['@attributes']) ? $roomTypes : $roomTypes[0];

            $roomName  = $firstRoomType['name'] ?? '';
            $rateBases = $firstRoomType['rateBases']['rateBasis'] ?? null;

            if (empty($rateBases)) {
                continue;
            }

            if (isset($rateBases['@attributes'])) {
                $rateBases = [$rateBases];
            }

            $targetBasis = null;

            foreach ($rateBases as $basis) {
                if (($basis['@attributes']['id'] ?? null) === '0') {
                    $targetBasis = $basis;
                    break;
                }
            }

            if ($targetBasis === null) {
                $targetBasis = $rateBases[0];
            }

            $total      = (float) ($targetBasis['total'] ?? 0);
            $currencyId = (string) ($targetBasis['rateType']['@attributes']['currencyid'] ?? '520');

            if ($total <= 0) {
                continue;
            }

            $map[$hotelId] = [
                'minRate'    => $total,
                'currencyId' => $currencyId,
                'roomName'   => $roomName,
            ];
        }

        return $map;
    }

    private function parse_cancellation_rules(array $rules): array
    {
        if (empty($rules)) {
            return [];
        }

        if (isset($rules['toDate']) || isset($rules['noShowPolicy'])) {
            $rules = [$rules];
        }

        $parsed = [];

        foreach ($rules as $rule) {
            if (!empty($rule['noShowPolicy'])) {
                $parsed[] = [
                    'type'          => 'no_show',
                    'from_date'     => null,
                    'to_date'       => null,
                    'cancel_charge' => round((float) ($rule['charge'] ?? 0), 2),
                    'amend_charge'  => null,
                    'description'   => 'No-show charge',
                ];
                continue;
            }

            $cancelCharge = round((float) ($rule['cancelCharge'] ?? 0), 2);
            $amendCharge  = round((float) ($rule['amendCharge']  ?? 0), 2);

            $parsed[] = [
                'type'          => $cancelCharge > 0 ? 'penalty' : 'free',
                'from_date'     => $rule['fromDate'] ?? null,
                'to_date'       => $rule['toDate']   ?? null,
                'cancel_charge' => $cancelCharge,
                'amend_charge'  => $amendCharge,
                'description'   => $cancelCharge > 0
                    ? "Cancellation charge: {$cancelCharge}"
                    : 'Free cancellation',
            ];
        }

        return $parsed;
    }

    // -------------------------------------------------------------------------
    // PRIVATE — DATA HELPERS
    // -------------------------------------------------------------------------

    private function convert_rating($ratingCode): int
    {
        $ratingMap = [
            '559'   => 1,
            '560'   => 2,
            '561'   => 3,
            '562'   => 4,
            '563'   => 5,
            '564'   => 3,
            '565'   => 4,
            '48055' => 0,
        ];

        return $ratingMap[(string) $ratingCode] ?? 0;
    }

    private function sanitize_name(string $name): string
    {
        return str_replace('&', '-', trim($name));
    }

    private function get_destination(string $city): ?object
    {
        try {
            return DB::connection('sqlite_webbeds')
                ->table('cities')
                ->where('name', 'LIKE', '%' . $city . '%')
                ->first();

        } catch (Exception $e) {
            throw new Exception('Database error while fetching destination: ' . $e->getMessage());
        }
    }

    private function validate_input(Request $request): array
    {
        $rules = [
            'city'                 => 'required|string|max:100',
            'checkin'              => 'required|date_format:Y-m-d|after_or_equal:today',
            'checkout'             => 'required|date_format:Y-m-d|after:checkin',
            'adults'               => 'required|integer|min:1|max:20',
            'children'             => 'nullable|array',
            'children.*'           => 'integer|min:0|max:17',
            'rooms'                => 'required|integer|min:1|max:10',
            'currency'             => 'required|string|size:3|regex:/^[A-Z]{3}$/',
            'env'                  => 'required|in:dev,pro',
            'api_credential_1'     => 'required|string|min:5',
            'api_credential_2'     => 'required|string|min:5',
            'api_credential_3'     => 'required|string|min:5',
            'country_code'         => 'nullable|string|size:2|regex:/^[A-Z]{2}$/',
            'nationality'          => 'nullable|string',
            'country_of_residence' => 'nullable|string',
            'rating'               => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new Exception(
                'Validation Error: ' . json_encode($validator->errors()->toArray())
            );
        }

        return $validator->validated();
    }

    // -------------------------------------------------------------------------
    // PRIVATE — RESPONSE HELPERS
    // -------------------------------------------------------------------------

    private function error_response(
        string $message,
        int $statusCode = 400,
        array $details = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            ...$details,
        ], $statusCode);
    }

    private function success_response(
        array $data = [],
        string $message = 'Success'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            ...$data,
        ]);
    }

    // -------------------------------------------------------------------------
    // PRIVATE — BOOK ITINERARY XML BUILDER
    // -------------------------------------------------------------------------

    /**
     * Build bookitinerary XML.
     *
     * Three calls are made:
     *   1. confirm=no      — verify price + get fresh allocationDetails per service
     *   2. confirm=preauth — preauthorize card via creditCardPaymentDetails (token-based)
     *   3. confirm=yes     — final confirmation using creditCardPaymentDetails
     *                        (orderCode + authorisationId from preauth response)
     *
     * @param array      $v             credentials + booking_code + confirm + total_price
     *                                  for preauth: cc_token, avs_*, cc_charge
     *                                  for yes:     order_code, authorisation_id, cc_charge
     * @param array|null $testServices  null for confirm=no; array of service data otherwise
     */
    private function build_book_itinerary_xml(array $v, ?array $testServices): string
    {
        $password    = md5($v['api_credential_2']);
        $bookingCode = $v['booking_code'];
        $confirm     = $v['confirm'];  // 'no' or 'preauth'

        // ── testPricesAndAllocation + creditCardPaymentDetails blocks ──────────
        $testBlock    = '';
        $paymentBlock = '';

        if (in_array($confirm, ['preauth', 'yes']) && !empty($testServices)) {


            $testBlock = '<testPricesAndAllocation>';
            foreach ($testServices as $svc) {
                $testBlock .= '
                <service referencenumber="' . $svc['code'] . '">
                    <testPrice>' . $svc['price'] . '</testPrice>
                    <allocationDetails>' . $svc['allocation_details'] . '</allocationDetails>
                </service>';
            }
            $testBlock .= '
            </testPricesAndAllocation>';


            if ($confirm === 'preauth' && !empty($v['cc_token'])) {
                $ccCharge     = $v['cc_charge']   ?? $v['total_price'];
                $ccToken      = $v['cc_token'];
                $avsFirstName = htmlspecialchars($v['avs_first_name'] ?? '');
                $avsLastName  = htmlspecialchars($v['avs_last_name']  ?? '');
                $avsAddress   = htmlspecialchars($v['avs_address']    ?? '');
                $avsZip       = htmlspecialchars($v['avs_zip']        ?? '');
                $avsCountry   = htmlspecialchars($v['avs_country']    ?? '');
                $avsCity      = htmlspecialchars($v['avs_city']       ?? '');
                $avsEmail     = htmlspecialchars($v['avs_email']      ?? '');
                $avsPhone      = htmlspecialchars($v['avs_phone']      ?? '');
                $endUserIp     = $v['end_user_ip']    ?? '127.0.0.1';

                $devicePayload = $v['device_payload'] ?? '';

                $paymentBlock = <<<PAYMENT
            <creditCardPaymentDetails>
                <paymentMethod>CC_PAYMENT_NET</paymentMethod>
                <usedCredit>0</usedCredit>
                <creditCardCharge>{$ccCharge}</creditCardCharge>
                <creditCardDetails>
                    <token>{$ccToken}</token>
                    <avsDetails>
                        <avsFirstName>{$avsFirstName}</avsFirstName>
                        <avsLastName>{$avsLastName}</avsLastName>
                        <avsAddress>{$avsAddress}</avsAddress>
                        <avsZip>{$avsZip}</avsZip>
                        <avsCountry>{$avsCountry}</avsCountry>
                        <avsCity>{$avsCity}</avsCity>
                        <avsEmail>{$avsEmail}</avsEmail>
                        <avsPhone>{$avsPhone}</avsPhone>
                    </avsDetails>
                </creditCardDetails>
                <devicePayload>{$devicePayload}</devicePayload>
                <endUserIPv4Address>{$endUserIp}</endUserIPv4Address>
            </creditCardPaymentDetails>
PAYMENT;
            }


            if ($confirm === 'yes' && !empty($v['order_code']) && !empty($v['authorisation_id'])) {
                $ccCharge        = $v['cc_charge'] ?? $v['total_price'];
                $orderCode       = $v['order_code'];
                $authorisationId = $v['authorisation_id'];
                $endUserIp       = $v['end_user_ip']    ?? '127.0.0.1';
                $devicePayload   = $v['device_payload'] ?? '';

                $paymentBlock = <<<PAYMENT
            <creditCardPaymentDetails>
                <paymentMethod>CC_PAYMENT_NET</paymentMethod>
                <usedCredit>0</usedCredit>
                <creditCardCharge>{$ccCharge}</creditCardCharge>
                <creditCardDetails>
                    <orderCode>{$orderCode}</orderCode>
                    <authorisationId>{$authorisationId}</authorisationId>
                </creditCardDetails>
                <devicePayload>{$devicePayload}</devicePayload>
                <endUserIPv4Address>{$endUserIp}</endUserIPv4Address>
            </creditCardPaymentDetails>
PAYMENT;
            }
        }


        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$v['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$v['api_credential_3']}</id>
    <source>1</source>
    <request command="bookitinerary">
        <bookingDetails>
            <bookingType>2</bookingType>
            <bookingCode>{$bookingCode}</bookingCode>
            <confirm>{$confirm}</confirm>
            {$testBlock}
            {$paymentBlock}
        </bookingDetails>
    </request>
</customer>
XML;
    }


    // -------------------------------------------------------------------------
    // PRIVATE — REZPAYMENTS TOKENIZER
    // -------------------------------------------------------------------------

    /**
     * Tokenize a credit card via Rezpayments Tokenizer Service.
     *
     * Docs: BookingFlow.Bookitinerary.CreditLine V1 Dec2024
     *
     * TEST endpoint: https://securepayapi.dev.rezpayments.com/
     * PROD endpoint: https://securepayapi.rezpayments.com/
     *
     * Request:  POST application/json
     *           Header: X-Secure-Pay-Authorization: Basic 99Bwebbeds
     *           Body:   { cardName, cardNumber, expiryYear, expiryMonth, securityCode }
     *
     * Response: { "id": "token_string", "prefix": "44443333" }
     *
     * @param  array $params  card details + env (dev|pro)
     * @return array          ['success' => bool, 'token' => string, 'message' => string]
     */
    private function rezpayments_tokenize(array $params): array
    {
        $env      = $params['env'] ?? 'dev';
        $endpoint = $env === 'pro'
            ? 'https://securepayapi.rezpayments.com/'
            : 'https://securepayapi.dev.rezpayments.com/';

        $payload = json_encode([
            'cardName'     => $params['cardName'],
            'cardNumber'   => $params['cardNumber'],
            'expiryYear'   => $params['expiryYear'],
            'expiryMonth'  => $params['expiryMonth'],
            'securityCode' => $params['securityCode'],
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL             => $endpoint,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $payload,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => [
                'Content-Type: application/json',
                'X-Secure-Pay-Authorization: Basic 99Bwebbeds',
            ],
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_CONNECTTIMEOUT  => 10,
            CURLOPT_SSL_VERIFYPEER  => false,
        ]);

        $body      = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'token' => '', 'message' => 'Rezpayments cURL error: ' . $curlError];
        }

        if ($httpCode !== 200) {
            $errBody = json_decode($body, true);
            $errMsg  = $errBody['message'] ?? $errBody['error'] ?? "HTTP {$httpCode}";
            return ['success' => false, 'token' => '', 'message' => 'Rezpayments error: ' . $errMsg];
        }

        $result = json_decode($body, true);
        $token  = $result['id'] ?? '';

        if (empty($token)) {
            return ['success' => false, 'token' => '', 'message' => 'Rezpayments: no token in response'];
        }

        return ['success' => true, 'token' => $token, 'message' => 'OK'];
    }


    // -------------------------------------------------------------------------
    // PRIVATE — DEVICE PAYLOAD GENERATOR
    // -------------------------------------------------------------------------

    /**
     * Generate devicePayload for bookitinerary preauth.
     *
     * devicePayload is a base64-encoded JSON object containing request context
     * used by Rezpayments for fraud detection.
     *
     * Structure (decoded from DOTW example):
     * {
     *   "payload":          base64( {"tid": transactionId, "browser": ..., "os": ...} ),
     *   "httpHeaders":      { "User-Agent": "...", "Accept-Language": "..." },
     *   "remoteAddress":    "end user IP",
     *   "transactionId":    "unique hex string",
     *   "requestMethod":    "POST",
     *   "requestTimestamp": unix timestamp in ms,
     *   "customerData":     null
     * }
     *
     * @param  string $remoteAddress   End user IPv4 address
     * @param  string $userAgent       User-Agent header from request
     * @param  string $acceptLanguage  Accept-Language header from request
     * @return string                  base64 encoded payload string
     */
    private function generate_device_payload(
        string $remoteAddress,
        string $userAgent,
        string $acceptLanguage
    ): string {
        $transactionId = bin2hex(random_bytes(20)); // 40 char hex
        $timestampMs   = (int) (microtime(true) * 1000);


        $innerPayload = base64_encode(json_encode([
            'tid'     => substr($transactionId, 0, 12),
            'browser' => $this->extract_browser($userAgent),
            'os'      => $this->extract_os($userAgent),
        ]));

        $outerPayload = [
            'payload'          => $innerPayload,
            'httpHeaders'      => [
                'User-Agent'      => $userAgent,
                'Accept-Language' => $acceptLanguage,
            ],
            'remoteAddress'    => $remoteAddress,
            'transactionId'    => $transactionId,
            'requestMethod'    => 'POST',
            'requestTimestamp' => $timestampMs,
            'customerData'     => null,
        ];

        return base64_encode(json_encode($outerPayload));
    }

    /**
     * Extract browser name from User-Agent string.
     */
    private function extract_browser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome'))  return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari'))  return 'Safari';
        if (str_contains($userAgent, 'Edge'))    return 'Edge';
        return 'Unknown';
    }

    /**
     * Extract OS name from User-Agent string.
     */
    private function extract_os(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac'))     return 'MacOS';
        if (str_contains($userAgent, 'Linux'))   return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iPhone'))  return 'iOS';
        return 'Unknown';
    }

}
