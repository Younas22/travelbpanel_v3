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
    private const API_ENDPOINT      = 'https://xmldev.dotwconnect.com/gatewayV4.dotw';
    private const CURL_TIMEOUT_LIST  = 120;
    private const CURL_TIMEOUT_PRICE = 60;
    private const HOTEL_BATCH_SIZE   = 50;
    private const CACHE_TTL_MINUTES  = 60;
    private const CACHE_PREFIX       = 'webbeds_hotel_';

    // -------------------------------------------------------------------------
    // PUBLIC — HOTEL SEARCH
    // -------------------------------------------------------------------------

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
                return response()->json(['success' => false, 'message' => $searchResponse['message']], 500);
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
                return response()->json(['success' => false, 'message' => $priceResponse['message']], 500);
            }

            $priceData = $this->parseXmlToArray($priceResponse['body']);
            $priceMap  = $this->build_price_map($priceData);

            $result = [];

            foreach ($hotels as $hotel) {
                $hotelId = $hotel['@attributes']['hotelid'];
                $price   = $priceMap[$hotelId] ?? null;

                if ($price === null) continue;

                $roundedPrice = round($price['minRate'], 2);

                $images = [];
                foreach (array_slice($hotel['images']['hotelImages']['image'] ?? [], 0, 20) as $value) {
                    if (!empty($value['url'])) $images[] = $value['url'];
                }

                $hotelNameRaw    = $hotel['hotelName'] ?? '';
                $hotelAddressRaw = $hotel['address']   ?? '';

                $hotelRow = [
                    'hotel_id'          => $hotelId,
                    'name'              => $this->sanitize_name(is_array($hotelNameRaw) ? '' : $hotelNameRaw),
                    'address'           => is_array($hotelAddressRaw) ? '' : $hotelAddressRaw,
                    'stars'             => $this->convert_rating($hotel['rating'] ?? 0),
                    'minRate'           => $roundedPrice,
                    'real_price'        => $roundedPrice,
                    'actual_price'      => $roundedPrice,
                    'currency'          => $price['currencyId'],
                    'original_currency' => $price['currencyId'],
                    'room_name'         => $price['roomName'],
                    'images'            => $hotel['images']['hotelImages']['thumb'] ?? '',
                    'all_images'        => $images,
                    'supplier_name'     => 'Webbeds',
                    'location'          => $destination->name,
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

            return response()->json(['success' => true, 'data' => $result]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, ['exception' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // PUBLIC — HOTEL DETAILS
    // -------------------------------------------------------------------------

    public function hotel_details(Request $request): JsonResponse
    {
        try {
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
                return $this->error_response('Validation Error', 422, ['errors' => $validator->errors()->toArray()]);
            }

            $v          = $validator->validated();
            $commission = (float) $v['commission'];
            $hotelId    = $v['hotel_id'];
            $currency   = $v['currency'];
            $nights     = max(1, (int) ((strtotime($v['checkout']) - strtotime($v['checkin'])) / 86400));

            $childAges = [];
            if (!empty($v['child_age'])) {
                $childAges = array_map('intval', explode(',', $v['child_age']));
            }

            $cachedListing  = $this->get_cached_hotel_listing($hotelId);
            $detailXml      = $this->build_hotel_detail_params($v, $hotelId, $childAges);
            $detailResponse = $this->make_curl_request($detailXml, self::CURL_TIMEOUT_PRICE);

            if (!$detailResponse['success']) {
                return response()->json(['success' => false, 'message' => $detailResponse['message']], 500);
            }

            $detailData = $this->parseXmlToArray($detailResponse['body']);

            if (strtoupper($detailData['successful'] ?? '') !== 'TRUE') {
                return $this->error_response('Hotel not found or no availability.', 404);
            }

            $hotelNode = $detailData['hotel'] ?? $detailData['hotels']['hotel'] ?? null;
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
                $roomTypes    = $room['roomType'] ?? [];

                if (empty($roomTypes)) continue;

                if (isset($roomTypes['@attributes'])) {
                    $roomTypes = [$roomTypes];
                } elseif (!isset($roomTypes[0])) {
                    $roomTypes = array_values($roomTypes);
                }

                foreach ($roomTypes as $roomType) {
                    $roomTypeCode = $roomType['@attributes']['roomtypecode'] ?? '';
                    $roomTypeName = $roomType['name'] ?? '';
                    $rateBases    = $roomType['rateBases']['rateBasis'] ?? [];

                    if (empty($rateBases)) continue;
                    if (isset($rateBases['@attributes'])) $rateBases = [$rateBases];

                    $options = [];

                    foreach ($rateBases as $rateBasis) {
                        $rateBasisRunno    = (string) ($rateBasis['@attributes']['runno']       ?? '');
                        $rateBasisId       = (string) ($rateBasis['@attributes']['id']          ?? '');
                        $rateBasisDesc     = (string) ($rateBasis['@attributes']['description'] ?? '');
                        $rateBasisStatus   = (string) ($rateBasis['status']    ?? 'unchecked');
                        $total             = (float)  ($rateBasis['total']     ?? 0);
                        $currencyId        = (string) ($rateBasis['rateType']['@attributes']['currencyid'] ?? '520');
                        $allocationDetails = (string) ($rateBasis['allocationDetails'] ?? '');
                        $isBookable        = (string) ($rateBasis['isBookable'] ?? 'yes');

                        // Cert #19: filter out changedOccupancy rates
                        $changedOccupancy  = (string) ($rateBasis['changedOccupancy'] ?? '');
                        if (!empty($changedOccupancy) && strtolower($changedOccupancy) !== 'false') continue;

                        if ($total <= 0 || $isBookable !== 'yes' || empty($allocationDetails)) continue;

                        $sellTotal  = $commission > 0 ? round($total / (1 - $commission / 100), 2) : round($total, 2);
                        $netTotal   = round($total, 2);
                        $sellPerDay = round($sellTotal / $nights, 2);
                        $netPerDay  = round($netTotal  / $nights, 2);

                        $cancelRules  = $this->parse_cancellation_rules($rateBasis['cancellationRules']['rule'] ?? []);
                        $firstPenalty = collect($cancelRules)->firstWhere('type', 'penalty');
                        $passengersRequired = (int) ($rateBasis['passengerNamesRequiredForBooking'] ?? 1);

                        // Cert #21: Taxes & Fees
                        $taxesFees = [];
                        $rawTaxes  = $rateBasis['taxesFees']['tax'] ?? [];
                        if (!empty($rawTaxes)) {
                            if (isset($rawTaxes['@attributes'])) $rawTaxes = [$rawTaxes];
                            foreach ($rawTaxes as $tax) {
                                $taxesFees[] = [
                                    'type'        => (string) ($tax['@attributes']['type']     ?? ''),
                                    'description' => (string) ($tax['description']             ?? ''),
                                    'amount'      => (float)  ($tax['amount']                  ?? 0),
                                    'currency'    => (string) ($tax['@attributes']['currency'] ?? ''),
                                    'included'    => strtolower((string) ($tax['@attributes']['included'] ?? 'false')) === 'true',
                                ];
                            }
                        }

                        // is_refundable: true if any free cancellation rule exists
                        $isRefundable = !empty($cancelRules) && collect($cancelRules)->contains(
                                fn($r) => $r['type'] === 'free' && $r['cancel_charge'] == 0
                            );

                        // Cert #20: specials per rateBasis
                        $specialsRaw = $roomType['specials']['special'] ?? [];
                        if (!empty($specialsRaw) && isset($specialsRaw['@attributes'])) $specialsRaw = [$specialsRaw];
                        $specials = array_map(fn($s) => [
                            'type'     => $s['type']        ?? '',
                            'name'     => $s['specialName'] ?? '',
                            'discount' => $s['discount']    ?? '',
                        ], is_array($specialsRaw) ? $specialsRaw : []);

                        $options[] = [
                            'id'                  => $rateBasisRunno,
                            'rate_basis_id'       => $rateBasisId,
                            'description'         => $rateBasisDesc,
                            'status'              => $rateBasisStatus,
                            'passengers_required' => $passengersRequired,
                            'price'               => $sellTotal,
                            'actual_price'        => $netTotal,
                            'per_day'             => $sellPerDay,
                            'actual_per_day'      => $netPerDay,
                            'adults'              => $roomAdults,
                            'child'               => $roomChildren,
                            'children_ages'       => $childAges,
                            'currency_id'         => $currencyId,
                            'is_refundable'       => $isRefundable,
                            'refundable'          => $firstPenalty['cancel_charge'] ?? 0,
                            'refund_date'         => $firstPenalty['from_date']     ?? null,
                            'cancellation_rules'  => $cancelRules,
                            'allocation_details'  => $allocationDetails,
                            'tariff_notes'        => trim($this->xml_str($rateBasis['tariffNotes'] ?? '')),
                            'meal_included'       => $rateBasisId !== '0',
                            'left_to_sell'        => (int) ($rateBasis['leftToSell'] ?? 0),
                            'on_request'          => (int) ($rateBasis['onRequest']  ?? 0),
                            // Cert #1: minStay
                            'min_stay'            => trim($this->xml_str($rateBasis['minStay']          ?? '')),
                            'date_apply_min_stay' => trim($this->xml_str($rateBasis['dateApplyMinStay'] ?? '')),
                            // Cert #21: taxes & fees
                            'taxes_fees'          => $taxesFees,
                            // Cert #22: restricted flags
                            'non_refundable'      => strtolower($this->xml_str($rateBasis['nonRefundable']    ?? 'no'))   === 'yes',
                            'cancel_restricted'   => strtolower($this->xml_str($rateBasis['cancelRestricted'] ?? ''))     === 'true',
                            'amend_restricted'    => strtolower($this->xml_str($rateBasis['amendRestricted']  ?? ''))     === 'true',
                            // Cert #20: special promotions
                            'specials'            => $specials,
                        ];
                    }

                    if (empty($options)) continue;

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
                        'is_refundable'     => $cheapest['is_refundable'],
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
                            'nationality'          => $v['nationality']          ?? '',
                            'country_of_residence' => $v['country_of_residence'] ?? '',
                            'supplier_name'        => $v['supplier_name'],
                            'room_type_code'       => $roomTypeCode,
                            'room_name'            => $roomTypeName,
                            'rate_basis_runno'     => $cheapest['id'],
                            'selected_rate_basis'  => $cheapest['rate_basis_id'],
                            'rate_description'     => $cheapest['description'],
                            'allocation_details'   => $cheapest['allocation_details'],
                            'passengers_required'  => $cheapest['passengers_required'],
                            'price'                => $cheapest['price'],
                            'actual_price'         => $cheapest['actual_price'],
                            'per_day'              => $cheapest['per_day'],
                            'currency_id'          => $cheapest['currency_id'],
                            'is_refundable'        => $cheapest['is_refundable'],
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
                'response' => [[
                    'h_id'          => $hotelId,
                    'h_name'        => $cachedListing['h_name']  ?? '',
                    'address'       => $cachedListing['address'] ?? '',
                    'stars'         => $cachedListing['stars']   ?? 0,
                    'imgs'          => $cachedListing['images']  ?? [],
                    'lat'           => '',
                    'lng'           => '',
                    'agent_id'      => '',
                    'city'          => '',
                    'country'       => $cachedListing['location'] ?? '',
                    'rating'        => $cachedListing['stars']   ?? 0,
                    'desc'          => '',
                    'amenities'     => [],
                    'checkin'       => $v['checkin'],
                    'checkout'      => $v['checkout'],
                    'supplier_name' => $v['supplier_name'],
                    'rooms'         => $roomsList,
                ]],
            ]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, ['exception' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // PUBLIC — HOTEL BOOKING
    // -------------------------------------------------------------------------

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
                return $this->error_response('Validation Error', 422, ['errors' => $validator->errors()->toArray()]);
            }

            $v = $validator->validated();

            $salutationMap = [
                'male' => '3801', 'female' => '3802', 'mr'   => '3801',
                'mrs'  => '3802', 'miss'   => '3803', 'ms'   => '3802', 'dr' => '3804',
            ];

            $roomData  = json_decode($v['booking_data']);

            // ── Base fields from room_data (hotel + dates context) ────────────
            $checkin      = $roomData->room_data->checkin;
            $checkout     = $roomData->room_data->checkout;
            $productId    = $roomData->room_data->product_id;
            $adults       = (int) $roomData->room_data->adults;
            $children     = $roomData->room_data->children ?? [];
            $roomTypeCode = $roomData->room_data->room_type_code;
            $nationality  = '167';
            $residence    = '167';

            // ── Use option object for rate-specific booking fields ─────────────
            $optionData        = $roomData->option ?? null;
            $selectedRateBasis = $optionData->rate_basis_id      ?? $roomData->room_data->selected_rate_basis;
            $allocationDetails = $optionData->allocation_details ?? $roomData->room_data->allocation_details ?? '';
            $passengersFromOpt = (int) ($optionData->passengers_required ?? $roomData->room_data->passengers_required ?? 1);

            $customerReference = strtoupper('WB-' . $productId . '-' . date('Ymd', strtotime($checkin)) . '-' . substr(bin2hex(random_bytes(3)), 0, 6));

            $guest              = json_decode($v['guest']);
            $passengersRequired = max($passengersFromOpt, $adults);

            // Build adult passengers from guest input
            $passengers = [];
            foreach ($guest as $i => $traveller) {
                $titleKey   = strtolower(trim($traveller->title ?? ''));
                $salutation = $salutationMap[$titleKey] ?? '3801';
                $passengers[] = [
                    'salutation' => $salutation,
                    'first_name' => $this->sanitize_name($traveller->first_name ?? 'Guest'),
                    'last_name'  => $this->sanitize_name($traveller->last_name  ?? 'Guest'),
                    'leading'    => ($i === 0),
                ];
            }

            $leadPassenger = $passengers[0] ?? ['salutation' => '3801', 'first_name' => 'Guest', 'last_name' => 'Guest'];
            while (count($passengers) < $passengersRequired) {
                $passengers[] = [
                    'salutation' => $leadPassenger['salutation'],
                    'first_name' => $leadPassenger['first_name'],
                    'last_name'  => $leadPassenger['last_name'],
                    'leading'    => false,
                ];
            }
            $passengers = array_slice($passengers, 0, $passengersRequired);

            $normalised = [
                'api_credential_1'   => $v['api_credential_1'],
                'api_credential_2'   => $v['api_credential_2'],
                'api_credential_3'   => $v['api_credential_3'],
                'customer_reference' => $customerReference,
                'hotel_id'           => $productId,
                'checkin'            => $checkin,
                'checkout'           => $checkout,
                'rooms' => [[
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
                ]],
            ];

            // ── Step 1: savebooking ───────────────────────────────────────────
            $xml      = $this->build_save_booking_xml($normalised);
            $response = $this->make_curl_request($xml, self::CURL_TIMEOUT_PRICE);

            if (!$response['success']) {
                return response()->json(['success' => false, 'booking_pnr' => null, 'step' => 'savebooking', 'message' => $response['message'], 'response' => $response['body'] ?? null], 500);
            }

            $data = $this->parseXmlToArray($response['body']);

            if (strtoupper($data['successful'] ?? '') !== 'TRUE') {
                $errorMsg = $data['errorMessage'] ?? ($data['errors']['error'] ?? 'Save booking failed.');
                return response()->json(['success' => false, 'booking_pnr' => null, 'step' => 'savebooking', 'message' => is_array($errorMsg) ? implode(', ', $errorMsg) : $errorMsg, 'response' => $data], 422);
            }

            $bookingCode = (string) ($data['returnedCode'] ?? '');
            $rawSvcCodes = $data['returnedServiceCodes']['returnedServiceCode'] ?? [];

            if (empty($bookingCode)) {
                return response()->json(['success' => false, 'booking_pnr' => null, 'step' => 'savebooking', 'message' => 'Booking saved but no bookingCode returned by DOTW.', 'response' => $data], 502);
            }

            $serviceCodes = [];
            if (!is_array($rawSvcCodes)) {
                $serviceCodes = [(string) $rawSvcCodes];
            } else {
                foreach ($rawSvcCodes as $sc) {
                    $serviceCodes[] = is_array($sc) ? (string) ($sc['_value'] ?? reset($sc)) : (string) $sc;
                }
            }

            // ── Step 2: bookitinerary confirm=no ─────────────────────────────
            $itinParams1 = [
                'api_credential_1' => $v['api_credential_1'],
                'api_credential_2' => $v['api_credential_2'],
                'api_credential_3' => $v['api_credential_3'],
                'booking_code'     => $bookingCode,
                'confirm'          => 'no',
                'total_price'      => $optionData->price ?? $roomData->room_data->price ?? 0,
            ];

            $itinXml1      = $this->build_book_itinerary_xml($itinParams1, null);
            $itinResponse1 = $this->make_curl_request($itinXml1, self::CURL_TIMEOUT_PRICE);

            if (!$itinResponse1['success']) {
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'bookitinerary_no', 'message' => 'bookitinerary(no) failed: ' . $itinResponse1['message'], 'response' => $itinResponse1['body'] ?? null], 500);
            }

            $itinData1 = $this->parseXmlToArray($itinResponse1['body']);

            if (strtoupper($itinData1['successful'] ?? '') !== 'TRUE') {
                $errMsg = $itinData1['error']['details'] ?? ($itinData1['errorMessage'] ?? 'bookitinerary check failed.');
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'bookitinerary_no', 'message' => (string) $errMsg, 'response' => $itinData1], 422);
            }

            $products = $itinData1['product'] ?? [];
            if (empty($products)) {
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'bookitinerary_no', 'message' => 'No product in bookitinerary response.', 'response' => $itinData1], 502);
            }
            if (isset($products['@attributes'])) $products = [$products];

            $testServices   = [];
            $totalTestPrice = 0;

            foreach ($products as $product) {
                $svcCode         = (string) ($product['@attributes']['code'] ?? '');
                $freshAlloc      = (string) ($product['allocationDetails']   ?? '');
                $svcPrice        = (float)  ($product['price']               ?? 0);
                $totalTestPrice += $svcPrice;
                if (!empty($svcCode) && !empty($freshAlloc)) {
                    $testServices[] = ['code' => $svcCode, 'price' => $svcPrice, 'allocation_details' => $freshAlloc];
                }
            }

            if (empty($testServices)) {
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'bookitinerary_no', 'message' => 'No service data from bookitinerary check.', 'response' => $itinData1], 502);
            }

            // ── Step 3: Rezpayments tokenize ─────────────────────────────────
            $tokenResult = $this->rezpayments_tokenize([
                'cardName'     => 'Usama Malik',
                'cardNumber'   => '4444333322221111',
                'expiryYear'   => '2026',
                'expiryMonth'  => '12',
                'securityCode' => '123',
                'env'          => $v['env'] ?? 'dev',
            ]);

            if (!$tokenResult['success']) {
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'rezpayments_tokenize', 'message' => 'Payment tokenization failed: ' . $tokenResult['message'], 'response' => $tokenResult], 422);
            }

            $ccToken = $tokenResult['token'];

            $generatedDevicePayload = $this->generate_device_payload(
                '127.0.0.1',
                $request->header('User-Agent', 'Mozilla/5.0'),
                $request->header('Accept-Language', 'en-US,en;q=0.9')
            );

            // ── Step 4: bookitinerary confirm=preauth ─────────────────────────
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
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'bookitinerary_preauth', 'message' => 'bookitinerary(preauth) failed: ' . $itinResponse2['message'], 'response' => $itinResponse2['body'] ?? null], 500);
            }

            $itinData2 = $this->parseXmlToArray($itinResponse2['body']);

            if (strtoupper($itinData2['successful'] ?? '') !== 'TRUE') {
                $errMsg = $itinData2['error']['details'] ?? ($itinData2['errorMessage'] ?? 'Booking preauth failed.');
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'bookitinerary_preauth', 'message' => (string) $errMsg, 'response' => $itinData2], 422);
            }

            $preauthProducts = $itinData2['product'] ?? [];
            if (isset($preauthProducts['@attributes'])) $preauthProducts = [$preauthProducts];

            $yesServices = [];
            foreach ($preauthProducts as $pp) {
                $ppCode  = (string) ($pp['@attributes']['code'] ?? '');
                $ppAlloc = (string) ($pp['allocationDetails']   ?? '');
                $ppPrice = (float)  ($pp['price']               ?? 0);
                if (!empty($ppCode)) {
                    $yesServices[] = ['code' => $ppCode, 'price' => $ppPrice, 'allocation_details' => $ppAlloc];
                }
            }

            // Extract orderCode + authorisationId recursively
            $findKey = function (array $arr, string $key) use (&$findKey) {
                foreach ($arr as $k => $val) {
                    if (strcasecmp((string) $k, $key) === 0 && !is_array($val)) return (string) $val;
                    if (is_array($val)) {
                        $found = $findKey($val, $key);
                        if ($found !== null) return $found;
                    }
                }
                return null;
            };

            $orderCode       = $findKey($itinData2, 'orderCode')       ?? '';
            $authorisationId = $findKey($itinData2, 'authorisationId') ?? '';

            if (empty($orderCode) || empty($authorisationId)) {
                return response()->json([
                    'success'                => false,
                    'booking_pnr'            => $bookingCode,
                    'step'                   => 'bookitinerary_preauth',
                    'message'                => 'Preauth succeeded but orderCode/authorisationId missing.',
                    'order_code_found'       => $orderCode,
                    'authorisation_id_found' => $authorisationId,
                    'response'               => $itinData2,
                ], 502);
            }

            // ── Step 5: bookitinerary confirm=yes ─────────────────────────────
            $itinParams3 = [
                'api_credential_1' => $v['api_credential_1'],
                'api_credential_2' => $v['api_credential_2'],
                'api_credential_3' => $v['api_credential_3'],
                'booking_code'     => $bookingCode,
                'confirm'          => 'yes',
                'test_services'    => $yesServices,
                'total_price'      => $totalTestPrice,
                'cc_charge'        => $totalTestPrice,
                'order_code'       => $orderCode,
                'authorisation_id' => $authorisationId,
                'end_user_ip'      => '127.0.0.1',
                'device_payload'   => $v['device_payload'] ?? $generatedDevicePayload,
            ];

            $itinXml3      = $this->build_book_itinerary_xml($itinParams3, $yesServices);
            $itinResponse3 = $this->make_curl_request($itinXml3, self::CURL_TIMEOUT_PRICE);

            if (!$itinResponse3['success']) {
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'bookitinerary_yes', 'message' => 'bookitinerary(yes) failed: ' . $itinResponse3['message'], 'response' => $itinResponse3['body'] ?? null], 500);
            }

            $itinData3 = $this->parseXmlToArray($itinResponse3['body']);

            if (strtoupper($itinData3['successful'] ?? '') !== 'TRUE') {
                $errMsg = $itinData3['error']['details'] ?? ($itinData3['errorMessage'] ?? 'Final booking confirmation failed.');
                return response()->json(['success' => false, 'booking_pnr' => $bookingCode, 'step' => 'bookitinerary_yes', 'message' => (string) $errMsg, 'response' => $itinData3], 422);
            }

            // ── Extract booking reference + cancel info ────────────────────────
            $bookingNode  = $itinData3['bookings']['booking'] ?? [];
            $productNode  = $itinData3['product']             ?? [];
            $productAttr  = $productNode['@attributes']       ?? [];

            $bookingRef = (string) (
                $bookingNode['bookingReferenceNumber']
                ?? $itinData3['bookingReferenceNumber']
                ?? $bookingCode
            );

            // Cancel details — service_code = product.@attributes.code
            $cancelServiceCode    = (string) ($productAttr['code'] ?? $bookingCode);
            $withinCancelDeadline = (string) ($productNode['withinCancellationDeadline'] ?? '');
            $rawCancelRules       = $productNode['cancellationRules']['rule'] ?? [];
            if (!empty($rawCancelRules) && !isset($rawCancelRules[0])) {
                $rawCancelRules = [$rawCancelRules];
            }

            $paymentBalance = round((float) $totalTestPrice, 4);

            return response()->json([
                'success'           => true,
                'booking_pnr'       => $bookingNode['bookingCode'],
                'booking_reference' => (string) $bookingRef,
                'service_codes'     => $serviceCodes,
                'customer_reference'=> $customerReference,
                'total_price'       => $totalTestPrice,
                'status'            => 'confirmed',
                'message'           => 'Booking confirmed successfully.',
                'cancel_info'       => [
                    'booking_code'                 => $cancelServiceCode,
                    'service_code'                 => $cancelServiceCode,
                    'payment_balance'              => $paymentBalance,
                    'within_cancellation_deadline' => $withinCancelDeadline,
                    'cancellation_rules'           => $this->parse_cancellation_rules($rawCancelRules),
                ],
                'response'          => $itinData3,
            ]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, ['exception' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // PUBLIC — GETROOMS BLOCK
    // -------------------------------------------------------------------------

    public function hotel_getrooms_block(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'hotel_id'             => 'required|string',
                'checkin'              => 'required|date|after_or_equal:today',
                'checkout'             => 'required|date|after:checkin',
                'adults'               => 'required|integer|min:1',
                'child_age'            => 'nullable|string',
                'rooms'                => 'required|integer|min:1',
                'room_type_code'       => 'required|string',
                'rate_basis_id'        => 'required|string',
                'allocation_details'   => 'required|string',
                'currency'             => 'required|string|size:3',
                'nationality'          => 'nullable|string',
                'country_of_residence' => 'nullable|string',
                'api_credential_1'     => 'required|string',
                'api_credential_2'     => 'required|string',
                'api_credential_3'     => 'required|string',
                'env'                  => 'nullable|in:dev,pro',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation Error', 422, ['errors' => $validator->errors()->toArray()]);
            }

            $v         = $validator->validated();
            $childAges = [];
            if (!empty($v['child_age'])) {
                $childAges = array_map('intval', explode(',', $v['child_age']));
            }

            $blockXml      = $this->build_getrooms_block_xml($v, $childAges);
            $blockResponse = $this->make_curl_request($blockXml, self::CURL_TIMEOUT_PRICE);

            if (!$blockResponse['success']) {
                return response()->json(['success' => false, 'message' => $blockResponse['message']], 500);
            }

            $blockData = $this->parseXmlToArray($blockResponse['body']);

            if (strtoupper($blockData['successful'] ?? '') !== 'TRUE') {
                return $this->error_response('Getrooms block failed — hotel not available.', 404);
            }

            $hotelNode = $blockData['hotel'] ?? $blockData['hotels']['hotel'] ?? null;
            if (!isset($hotelNode['@attributes'])) {
                $hotelNode = $hotelNode[0] ?? $hotelNode;
            }

            $rawRooms = $hotelNode['rooms']['room'] ?? [];
            if (isset($rawRooms['@attributes'])) $rawRooms = [$rawRooms];

            $blockedAllocation = null;
            $checkedStatus     = false;

            foreach ($rawRooms as $room) {
                $roomTypes = $room['roomType'] ?? [];
                if (isset($roomTypes['@attributes'])) $roomTypes = [$roomTypes];

                foreach ($roomTypes as $rt) {
                    if (($rt['@attributes']['roomtypecode'] ?? '') !== $v['room_type_code']) continue;
                    $rateBases = $rt['rateBases']['rateBasis'] ?? [];
                    if (isset($rateBases['@attributes'])) $rateBases = [$rateBases];

                    foreach ($rateBases as $rb) {
                        if ((string) ($rb['@attributes']['id'] ?? '') !== (string) $v['rate_basis_id']) continue;
                        if (strtolower($rb['status'] ?? '') === 'checked') {
                            $checkedStatus     = true;
                            $blockedAllocation = (string) ($rb['allocationDetails'] ?? '');
                        }
                        break 3;
                    }
                }
            }

            if (!$checkedStatus || empty($blockedAllocation)) {
                return $this->error_response('Room blocking failed — status not checked. Please search again.', 409);
            }

            return response()->json([
                'success'            => true,
                'allocation_details' => $blockedAllocation,
                'status'             => 'checked',
                'message'            => 'Room successfully blocked. Proceed to booking.',
            ]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, ['exception' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // PUBLIC — CANCEL BOOKING
    // -------------------------------------------------------------------------

    public function hotel_cancel_booking(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'booking_code'     => 'required|string',
                'confirm'          => 'required|in:no,yes',
                'penalty_charge'   => 'nullable|numeric',
                'payment_balance'  => 'required|numeric',
                'service_code'     => 'nullable|string',
                'api_credential_1' => 'required|string',
                'api_credential_2' => 'required|string',
                'api_credential_3' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation Error', 422, ['errors' => $validator->errors()->toArray()]);
            }

            $v           = $validator->validated();
            $password    = md5($v['api_credential_2']);
            $bookingCode = $v['booking_code'];
            $confirm     = $v['confirm'];

            // confirm=no  → no testPricesAndAllocation
            // confirm=yes → testPricesAndAllocation with penaltyApplied + paymentBalance
            $testBlock = '';
            if ($confirm === 'yes') {
                $serviceCode    = $v['service_code']   ?? $bookingCode;
                $penaltyValue   = $v['penalty_charge'];
                $paymentBalance = $v['payment_balance'];

                $testBlock = <<<TESTBLOCK
            <testPricesAndAllocation>
                <service referencenumber="{$serviceCode}">
                    <penaltyApplied>{$penaltyValue}</penaltyApplied>
                    <paymentBalance>{$paymentBalance}</paymentBalance>
                </service>
            </testPricesAndAllocation>
TESTBLOCK;
            }

            $xml = <<<XMLREQ
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$v['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$v['api_credential_3']}</id>
    <source>1</source>
    <request command="cancelbooking">
        <bookingDetails>
            <bookingType>1</bookingType>
            <bookingCode>{$bookingCode}</bookingCode>
            <confirm>{$confirm}</confirm>
            {$testBlock}
        </bookingDetails>
    </request>
</customer>
XMLREQ;

            $response = $this->make_curl_request($xml, self::CURL_TIMEOUT_PRICE);

            if (!$response['success']) {
                return response()->json(['success' => false, 'message' => $response['message']], 500);
            }

            $data = $this->parseXmlToArray($response['body']);

            if (strtoupper($data['successful'] ?? '') !== 'TRUE') {
                $errMsg = $data['error']['details'] ?? ($data['errorMessage'] ?? 'Cancel booking failed.');
                return $this->error_response((string) $errMsg, 422);
            }

            if ($confirm === 'no') {
                // Step 1: extract charge
                $serviceNode  = $data['services']['service'] ?? [];
                if (isset($serviceNode['@attributes'])) $serviceNode = [$serviceNode];
                $firstService = $serviceNode[0] ?? [];
                $serviceCode  = (string) ($firstService['@attributes']['code'] ?? $bookingCode);
                $chargeNode   = $firstService['cancellationPenalty']['charge']  ?? 0;
                $charge       = (float) (is_array($chargeNode) ? ($chargeNode[0] ?? 0) : $chargeNode);
                $paymentBal   = (float) $v['payment_balance'];

                // Auto-call confirm=yes with extracted values
                $penaltyFmt     = $charge;
                $paymentFmt     = $paymentBal;
                $paymentBal = $paymentFmt - $penaltyFmt;
                $yesServiceCode = $bookingCode;

                $yesTestBlock = <<<TESTBLOCK
            <testPricesAndAllocation>
                <service referencenumber="{$yesServiceCode}">
                    <penaltyApplied>{$penaltyFmt}</penaltyApplied>
                    <paymentBalance>{$paymentBal}</paymentBalance>
                </service>
            </testPricesAndAllocation>
TESTBLOCK;

                $yesXml = <<<YESXML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$v['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$v['api_credential_3']}</id>
    <source>1</source>
    <request command="cancelbooking">
        <bookingDetails>
            <bookingType>1</bookingType>
            <bookingCode>{$bookingCode}</bookingCode>
            <confirm>yes</confirm>
            {$yesTestBlock}
        </bookingDetails>
    </request>
</customer>
YESXML;

                $yesResponse = $this->make_curl_request($yesXml, self::CURL_TIMEOUT_PRICE);

                if (!$yesResponse['success']) {
                    return response()->json(['success' => false, 'message' => 'Step2(yes) failed: ' . $yesResponse['message']], 500);
                }

                $yesData      = $this->parseXmlToArray($yesResponse['body']);
                $yesSuccess   = strtoupper($yesData['successful'] ?? '') === 'TRUE';
                $productsLeft = (int) ($yesData['productsLeftOnItinerary'] ?? 0);

                return response()->json([
                    'success'                    => $yesSuccess,
                    'booking_code'               => $bookingCode,
                    'service_code'               => $serviceCode,
                    'penalty_charge'             => $charge,
                    'payment_balance'            => $paymentBal,
                    'products_left_on_itinerary' => $productsLeft,
                    'partial_cancellation'       => $productsLeft > 0,
                    'status'                     => $yesSuccess ? 'cancelled' : 'failed',
                    'message'                    => $yesSuccess
                        ? ($productsLeft > 0
                            ? "Partially cancelled. {$productsLeft} service(s) still active."
                            : 'Booking cancelled successfully.')
                        : 'Cancellation confirmation failed.',
                    'step1_raw' => $data,
                    'step2_raw' => $yesData,
                ]);
            }

            // confirm=yes called directly
            $productsLeft = (int) ($data['productsLeftOnItinerary'] ?? 0);
            return response()->json([
                'success'                    => true,
                'booking_code'               => $bookingCode,
                'products_left_on_itinerary' => $productsLeft,
                'partial_cancellation'       => $productsLeft > 0,
                'status'                     => 'cancelled',
                'message'                    => $productsLeft > 0
                    ? "Partially cancelled. {$productsLeft} service(s) still active."
                    : 'Booking cancelled successfully.',
                'raw' => $data,
            ]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, ['exception' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // PRIVATE — HTTP
    // -------------------------------------------------------------------------

    private function make_curl_request(string $xml, int $timeout = 60): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::API_ENDPOINT,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $xml,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=UTF-8',
                'Accept-Encoding: gzip, deflate',
            ],
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_BUFFERSIZE     => 131072,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => 'gzip, deflate',
        ]);

        $body      = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) return ['success' => false, 'message' => "cURL error: {$curlError}"];
        if ($httpCode !== 200) return ['success' => false, 'message' => "HTTP error: {$httpCode}"];
        return ['success' => true, 'body' => $body];
    }

    // -------------------------------------------------------------------------
    // PRIVATE — XML BUILDERS
    // -------------------------------------------------------------------------

    private function build_search_params(array $validated, object $destination): string
    {
        $password     = md5($validated['api_credential_2']);
        $childrenXml  = $this->build_children_xml($validated['children'] ?? []);
        $nationality  = $validated['nationality'] ?? '167';
        $residence    = $validated['country_of_residence'] ?? '167';
        $roomsCount   = (int) ($validated['rooms'] ?? 1);
        $conditionXml = '';

        if (!empty($validated['rating'])) {
            $conditionXml = '<c:condition><a:condition><fieldName>rating</fieldName><fieldTest>equals</fieldTest><fieldValues><fieldValue>' . $validated['rating'] . '</fieldValue></fieldValues></a:condition></c:condition>';
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
            <rooms no="{$roomsCount}">
                <room runno="0">
                    <adultsCode>{$validated['adults']}</adultsCode>
                    {$childrenXml}
                    <rateBasis>-1</rateBasis>
                    <passengerNationality>{$nationality}</passengerNationality>
                    <passengerCountryOfResidence>{$residence}</passengerCountryOfResidence>
                </room>
            </rooms>
        </bookingDetails>
        <return>
            <filters xmlns:a="http://us.dotwconnect.com/xsd/atomicCondition" xmlns:c="http://us.dotwconnect.com/xsd/complexCondition">
                <city>{$destination->code}</city>
                <noPrice>true</noPrice>
                {$conditionXml}
            </filters>
            <fields>
                <field>hotelName</field>
                <field>address</field>
                <field>rating</field>
                <field>images</field>
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
        $roomsCount  = (int) ($validated['rooms'] ?? 1);
        $fieldValues = implode('', array_map(fn($id) => "<fieldValue>{$id}</fieldValue>", $hotelIds));

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
            <rooms no="{$roomsCount}">
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
            <filters xmlns:a="http://us.dotwconnect.com/xsd/atomicCondition" xmlns:c="http://us.dotwconnect.com/xsd/complexCondition">
                <c:condition>
                    <a:condition>
                        <fieldName>hotelId</fieldName>
                        <fieldTest>in</fieldTest>
                        <fieldValues>{$fieldValues}</fieldValues>
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
        if ($count === 0) return '<children no="0"/>';
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
        $roomsCount  = (int) ($v['rooms'] ?? 1);

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
            <rooms no="{$roomsCount}">
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

    private function build_save_booking_xml(array $v): string
    {
        $password  = md5($v['api_credential_2']);
        $roomCount = count($v['rooms']);
        $customerRef = $v['customer_reference'] ?? '';
        $roomsXml  = '';

        foreach ($v['rooms'] as $index => $room) {
            $children     = (array) ($room['children'] ?? []);
            $childCount   = count($children);
            $adultsCode   = (int) $room['adults'];
            $actualAdults = (int) ($room['actual_adults'] ?? $adultsCode);
            $extraBed     = (int) ($room['extra_bed'] ?? 0);
            $nationality  = $room['nationality']          ?: '167';
            $residence    = $room['country_of_residence'] ?: '167';

            // Required passengers = adults + children
            $requiredPassengers = $adultsCode + $childCount;

            if ($childCount === 0) {
                $childrenXml       = '<children no="0"/>';
                $actualChildrenXml = '<actualChildren no="0"/>';
            } else {
                $childrenXml       = '<children no="' . $childCount . '">';
                $actualChildrenXml = '<actualChildren no="' . $childCount . '">';
                foreach ($children as $ci => $age) {
                    $childrenXml       .= '<child runno="' . $ci . '">' . $age . '</child>';
                    $actualChildrenXml .= '<actualChild runno="' . $ci . '">' . $age . '</actualChild>';
                }
                $childrenXml       .= '</children>';
                $actualChildrenXml .= '</actualChildren>';
            }

            // Adult passengers only (filter salutation 3803)
            $adultGuests = array_values(array_filter(
                $room['passengers'] ?? [],
                fn($p) => ($p['salutation'] ?? '') !== '3803'
            ));

            $lead = $adultGuests[0] ?? ['salutation' => '3801', 'first_name' => 'Guest', 'last_name' => 'Guest'];

            // Build final passenger list: adults first, then child slots
            $finalPassengers = [];
            for ($i = 0; $i < $adultsCode; $i++) {
                $finalPassengers[] = $adultGuests[$i] ?? $lead;
            }
            for ($i = 0; $i < $childCount; $i++) {
                $finalPassengers[] = ['salutation' => '3803', 'first_name' => $lead['first_name'], 'last_name' => $lead['last_name']];
            }
            $finalPassengers = array_slice($finalPassengers, 0, $requiredPassengers);
            while (count($finalPassengers) < $requiredPassengers) {
                $finalPassengers[] = $lead;
            }

            $passengersXml = '<passengersDetails>';
            foreach ($finalPassengers as $i => $p) {
                $leadAttr       = ($i === 0) ? ' leading="yes"' : '';
                $passengersXml .= '<passenger' . $leadAttr . '>'
                    . '<salutation>' . ($p['salutation'] ?? '3801') . '</salutation>'
                    . '<firstName>'  . $this->sanitize_name($p['first_name'] ?? 'Guest') . '</firstName>'
                    . '<lastName>'   . $this->sanitize_name($p['last_name']  ?? 'Guest') . '</lastName>'
                    . '</passenger>';
            }
            $passengersXml .= '</passengersDetails>';

            $roomsXml .= '<room runno="' . $index . '">'
                . '<roomTypeCode>'                . $room['room_type_code']     . '</roomTypeCode>'
                . '<selectedRateBasis>'           . $room['rate_basis_id']      . '</selectedRateBasis>'
                . '<allocationDetails>'           . $room['allocation_details'] . '</allocationDetails>'
                . '<adultsCode>'                  . $adultsCode                 . '</adultsCode>'
                . '<actualAdults>'                . $actualAdults               . '</actualAdults>'
                . $childrenXml
                . $actualChildrenXml
                . '<extraBed>'                    . $extraBed    . '</extraBed>'
                . '<passengerNationality>'        . $nationality . '</passengerNationality>'
                . '<passengerCountryOfResidence>' . $residence   . '</passengerCountryOfResidence>'
                . $passengersXml
                . '</room>';
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
            <customerReference>{$customerRef}</customerReference>
            <rooms no="{$roomCount}">{$roomsXml}</rooms>
        </bookingDetails>
    </request>
</customer>
XML;
    }

    private function build_book_itinerary_xml(array $v, ?array $testServices): string
    {
        $password    = md5($v['api_credential_2']);
        $bookingCode = $v['booking_code'];
        $confirm     = $v['confirm'];
        $testBlock   = '';
        $paymentBlock = '';

        if (in_array($confirm, ['preauth', 'yes']) && !empty($testServices)) {
            $testBlock = '<testPricesAndAllocation>';
            foreach ($testServices as $svc) {
                $testBlock .= '<service referencenumber="' . $svc['code'] . '">'
                    . '<testPrice>' . $svc['price'] . '</testPrice>'
                    . '<allocationDetails>' . $svc['allocation_details'] . '</allocationDetails>'
                    . '</service>';
            }
            $testBlock .= '</testPricesAndAllocation>';

            if ($confirm === 'preauth' && !empty($v['cc_token'])) {
                $ccCharge      = $v['cc_charge']   ?? $v['total_price'];
                $ccToken       = $v['cc_token'];
                $avsFirstName  = htmlspecialchars($v['avs_first_name'] ?? '');
                $avsLastName   = htmlspecialchars($v['avs_last_name']  ?? '');
                $avsAddress    = htmlspecialchars($v['avs_address']    ?? '');
                $avsZip        = htmlspecialchars($v['avs_zip']        ?? '');
                $avsCountry    = htmlspecialchars($v['avs_country']    ?? '');
                $avsCity       = htmlspecialchars($v['avs_city']       ?? '');
                $avsEmail      = htmlspecialchars($v['avs_email']      ?? '');
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
                $ccCharge        = $v['cc_charge']       ?? $v['total_price'];
                $orderCode       = $v['order_code'];
                $authorisationId = $v['authorisation_id'];
                $endUserIp       = $v['end_user_ip']     ?? '127.0.0.1';
                $devicePayload   = $v['device_payload']  ?? '';

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

    private function build_getrooms_block_xml(array $v, array $childAges): string
    {
        $password     = md5($v['api_credential_2']);
        $childrenXml  = $this->build_children_xml($childAges);
        $nationality  = $v['nationality']          ?? '167';
        $residence    = $v['country_of_residence'] ?? '167';
        $hotelId      = $v['hotel_id'];
        $allocDetails = $v['allocation_details'];
        $roomTypeCode = $v['room_type_code'];
        $rateBasisId  = $v['rate_basis_id'];

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
            <rooms no="{$v['rooms']}">
                <room runno="0">
                    <adultsCode>{$v['adults']}</adultsCode>
                    {$childrenXml}
                    <rateBasis>-1</rateBasis>
                    <passengerNationality>{$nationality}</passengerNationality>
                    <passengerCountryOfResidence>{$residence}</passengerCountryOfResidence>
                    <roomTypeSelected>
                        <code>{$roomTypeCode}</code>
                        <selectedRateBasis>{$rateBasisId}</selectedRateBasis>
                        <allocationDetails>{$allocDetails}</allocationDetails>
                    </roomTypeSelected>
                </room>
            </rooms>
            <productId>{$hotelId}</productId>
        </bookingDetails>
    </request>
</customer>
XML;
    }

    // -------------------------------------------------------------------------
    // PRIVATE — PARSERS & HELPERS
    // -------------------------------------------------------------------------

    private function parseXmlToArray(string $xmlString): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            $errors = array_map(fn($e) => $e->message, libxml_get_errors());
            libxml_clear_errors();
            return ['error' => 'Failed to parse XML', 'details' => $errors];
        }
        return json_decode(json_encode($xml), true);
    }

    private function build_price_map(array $priceData): array
    {
        $map    = [];
        $hotels = $priceData['hotels']['hotel'] ?? [];
        if (empty($hotels)) return $map;
        if (isset($hotels['@attributes'])) $hotels = [$hotels];

        foreach ($hotels as $hotel) {
            $hotelId = $hotel['@attributes']['hotelid'] ?? null;
            if (!$hotelId) continue;

            $room = $hotel['rooms']['room'] ?? null;
            if (empty($room)) continue;
            if (!isset($room['@attributes'])) $room = $room[0];

            $roomTypes = $room['roomType'] ?? null;
            if (empty($roomTypes)) continue;

            $firstRoomType = isset($roomTypes['@attributes']) ? $roomTypes : $roomTypes[0];
            $roomName      = $firstRoomType['name'] ?? '';
            $rateBases     = $firstRoomType['rateBases']['rateBasis'] ?? null;
            if (empty($rateBases)) continue;
            if (isset($rateBases['@attributes'])) $rateBases = [$rateBases];

            $targetBasis = null;
            foreach ($rateBases as $basis) {
                if (($basis['@attributes']['id'] ?? null) === '0') { $targetBasis = $basis; break; }
            }
            if ($targetBasis === null) $targetBasis = $rateBases[0];

            $total      = (float)  ($targetBasis['total'] ?? 0);
            $currencyId = (string) ($targetBasis['rateType']['@attributes']['currencyid'] ?? '520');
            if ($total <= 0) continue;

            $map[$hotelId] = ['minRate' => $total, 'currencyId' => $currencyId, 'roomName' => $roomName];
        }
        return $map;
    }

    private function xml_str($value): string
    {
        if (is_array($value)) {
            if (isset($value['0']) && is_string($value['0'])) return $value['0'];
            if (isset($value['_']) && is_string($value['_'])) return $value['_'];
            return '';
        }
        return (string) ($value ?? '');
    }

    private function parse_cancellation_rules(array $rules): array
    {
        if (empty($rules)) return [];
        if (isset($rules['toDate']) || isset($rules['noShowPolicy'])) $rules = [$rules];

        $parsed = [];
        foreach ($rules as $rule) {
            if (!empty($rule['noShowPolicy'])) {
                $parsed[] = ['type' => 'no_show', 'from_date' => null, 'to_date' => null, 'cancel_charge' => round((float) ($rule['charge'] ?? 0), 2), 'amend_charge' => null, 'description' => 'No-show charge'];
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
                'description'   => $cancelCharge > 0 ? "Cancellation charge: {$cancelCharge}" : 'Free cancellation',
            ];
        }
        return $parsed;
    }

    private function convert_rating($ratingCode): int
    {
        $ratingMap = ['559' => 1, '560' => 2, '561' => 3, '562' => 4, '563' => 5, '564' => 3, '565' => 4, '48055' => 0];
        return $ratingMap[(string) $ratingCode] ?? 0;
    }

    private function sanitize_name($name): string
    {
        if (is_array($name)) $name = '';
        $name = trim((string) $name);
        $name = preg_replace('/[^a-zA-Z]/', '', $name);
        $name = substr($name, 0, 25);
        if (strlen($name) < 2) $name = str_pad($name, 2, 'X');
        return $name;
    }

    private function cache_hotel_listing(string $hotelId, array $hotelRow): void
    {
        $cacheKey = self::CACHE_PREFIX . $hotelId;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, [
                'h_name'  => $hotelRow['name'],
                'address' => $hotelRow['address'],
                'stars'   => $hotelRow['stars'],
                'images'  => $hotelRow['all_images'],
                'location'=> $hotelRow['location'],
            ], now()->addMinutes(self::CACHE_TTL_MINUTES));
        }
    }

    private function get_cached_hotel_listing(string $hotelId): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $hotelId);
    }

    private function get_destination(string $city): ?object
    {
        try {
            return DB::connection('sqlite_webbeds')->table('cities')->where('name', 'LIKE', '%' . $city . '%')->first();
        } catch (Exception $e) {
            throw new Exception('Database error: ' . $e->getMessage());
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
            throw new Exception('Validation Error: ' . json_encode($validator->errors()->toArray()));
        }
        return $validator->validated();
    }

    private function error_response(string $message, int $statusCode = 400, array $details = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, ...$details], $statusCode);
    }

    private function success_response(array $data = [], string $message = 'Success'): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, ...$data]);
    }

    // -------------------------------------------------------------------------
    // PRIVATE — REZPAYMENTS
    // -------------------------------------------------------------------------

    private function rezpayments_tokenize(array $params): array
    {
        $env      = $params['env'] ?? 'dev';
        $endpoint = $env === 'pro' ? 'https://securepayapi.rezpayments.com/' : 'https://securepayapi.dev.rezpayments.com/';
        $payload  = json_encode([
            'cardName'     => $params['cardName'],
            'cardNumber'   => $params['cardNumber'],
            'expiryYear'   => $params['expiryYear'],
            'expiryMonth'  => $params['expiryMonth'],
            'securityCode' => $params['securityCode'],
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'X-Secure-Pay-Authorization: Basic 99Bwebbeds'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $body      = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) return ['success' => false, 'token' => '', 'message' => 'Rezpayments cURL error: ' . $curlError];
        if ($httpCode !== 200) {
            $errBody = json_decode($body, true);
            return ['success' => false, 'token' => '', 'message' => 'Rezpayments error: ' . ($errBody['message'] ?? $errBody['error'] ?? "HTTP {$httpCode}")];
        }

        $result = json_decode($body, true);
        $token  = $result['id'] ?? '';
        if (empty($token)) return ['success' => false, 'token' => '', 'message' => 'Rezpayments: no token in response'];
        return ['success' => true, 'token' => $token, 'message' => 'OK'];
    }

    // -------------------------------------------------------------------------
    // PRIVATE — DEVICE PAYLOAD
    // -------------------------------------------------------------------------

    private function generate_device_payload(string $remoteAddress, string $userAgent, string $acceptLanguage): string
    {
        $transactionId = bin2hex(random_bytes(20));
        $timestampMs   = (int) (microtime(true) * 1000);
        $innerPayload  = base64_encode(json_encode(['tid' => substr($transactionId, 0, 12), 'browser' => $this->extract_browser($userAgent), 'os' => $this->extract_os($userAgent)]));
        return base64_encode(json_encode([
            'payload'          => $innerPayload,
            'httpHeaders'      => ['User-Agent' => $userAgent, 'Accept-Language' => $acceptLanguage],
            'remoteAddress'    => $remoteAddress,
            'transactionId'    => $transactionId,
            'requestMethod'    => 'POST',
            'requestTimestamp' => $timestampMs,
            'customerData'     => null,
        ]));
    }

    private function extract_browser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome'))  return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari'))  return 'Safari';
        if (str_contains($userAgent, 'Edge'))    return 'Edge';
        return 'Unknown';
    }

    private function extract_os(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac'))     return 'MacOS';
        if (str_contains($userAgent, 'Linux'))   return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iPhone'))  return 'iOS';
        return 'Unknown';
    }



    // -------------------------------------------------------------------------
    // PUBLIC — TEST CREDENTIALS
    // -------------------------------------------------------------------------

    /**
     * Test API credentials by sending a lightweight searchhotels request.
     * Returns success/error with response time and connection status.
     */
    public function test_credentials(Request $request): JsonResponse
    {
        $start = microtime(true);

        try {
            $validator = Validator::make($request->all(), [
                'api_credential_1' => 'required|string',
                'api_credential_2' => 'required|string',
                'api_credential_3' => 'required|string',
                'endpoint'         => 'nullable|string',
                'developer_mode'   => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false, 'verified' => false,
                    'message' => 'Validation Error',
                    'errors'  => $validator->errors()->toArray(),
                ], 422);
            }

            $v        = $validator->validated();
            $username = trim($v['api_credential_1']);
            $password = md5(trim($v['api_credential_2']));
            $id       = trim($v['api_credential_3']);
            $endpoint = !empty($v['endpoint']) ? $v['endpoint'] : self::API_ENDPOINT;
            $devMode  = !empty($v['developer_mode']);

            // Simple XML — DOTW checks credentials on every request
            // If username/password/id wrong → returns error XML
            $fromDate = date('Y-m-d', strtotime('+30 days'));
            $toDate   = date('Y-m-d', strtotime('+31 days'));

            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$username}</username>
    <password>{$password}</password>
    <id>{$id}</id>
    <source>1</source>
    <product>hotel</product>
    <request command="getrooms">
        <bookingDetails>
            <fromDate>{$fromDate}</fromDate>
            <toDate>{$toDate}</toDate>
            <currency>520</currency>
            <rooms no="1">
                <room runno="0">
                    <adultsCode>2</adultsCode>
                    <children no="0"/>
                    <rateBasis>-1</rateBasis>
                    <passengerNationality>167</passengerNationality>
                    <passengerCountryOfResidence>167</passengerCountryOfResidence>
                </room>
            </rooms>
            <productId>126824</productId>
        </bookingDetails>
    </request>
</customer>
XML;

            // Send request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $endpoint,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $xml,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: text/xml; charset=UTF-8',
                    'Accept-Encoding: gzip, deflate',
                ],
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING       => 'gzip, deflate',
            ]);

            $body      = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlInfo  = curl_getinfo($ch);
            curl_close($ch);

            $responseTime = round((microtime(true) - $start) * 1000, 2);

            // cURL error
            if ($curlError) {
                return response()->json([
                    'success'       => false,
                    'verified'      => false,
                    'message'       => 'Connection Failed',
                    'error_type'    => str_contains($curlError, 'timed out') ? 'Connection Timeout' : 'Endpoint Not Found',
                    'error_detail'  => $curlError,
                    'response_time' => $responseTime,
                    'debug'         => $devMode ? ['xml' => $xml, 'endpoint' => $endpoint] : null,
                ]);
            }

            // HTTP error
            if ($httpCode !== 200) {
                return response()->json([
                    'success'       => false,
                    'verified'      => false,
                    'message'       => 'API Credential Verification Failed',
                    'error_type'    => "HTTP {$httpCode}",
                    'error_detail'  => "Server returned HTTP {$httpCode}",
                    'http_code'     => $httpCode,
                    'response_time' => $responseTime,
                    'debug'         => $devMode ? ['xml' => $xml, 'response' => $body, 'endpoint' => $endpoint] : null,
                ]);
            }

            // Parse response
            libxml_use_internal_errors(true);
            $xmlObj = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
            $parsed = $xmlObj ? json_decode(json_encode($xmlObj), true) : [];

            // ── Extract error from DOTW response ─────────────────────────────
            // DOTW returns errors in <details>, <errorMessage>, or <message> tags
            $errorDetail = '';

            // Try all possible DOTW error locations
            preg_match('/<details>(.*?)<\/details>/is',         $body, $m1);
            preg_match('/<errorMessage>(.*?)<\/errorMessage>/is',$body, $m2);
            preg_match('/<message>(.*?)<\/message>/is',          $body, $m3);

            $rawError = trim(html_entity_decode(
                $m1[1] ?? $m2[1] ?? $m3[1] ?? ''
            ));

            // Also check parsed array
            if (empty($rawError)) {
                $rawError = $parsed['error']['details']
                    ?? $parsed['errorMessage']
                    ?? $parsed['error']['message']
                    ?? '';
                $rawError = trim(html_entity_decode((string) $rawError));
            }

            $hasError = !empty($rawError)
                || str_contains(strtolower($body), '<error>')
                || str_contains(strtolower($body), 'errormessage');

            if ($hasError && !empty($rawError)) {
                $errorDetail = $rawError; // exact DOTW message e.g. "Wrong customer username or password or no access rights"
                $errLower    = strtolower($errorDetail);

                if (str_contains($errLower, 'password') || str_contains($errLower, 'username') || str_contains($errLower, 'wrong customer') || str_contains($errLower, 'access rights')) {
                    $errorType = 'Invalid Username/Password';
                } elseif (str_contains($errLower, 'unauthorized') || str_contains($errLower, 'unauthorised')) {
                    $errorType = 'Unauthorized';
                } elseif (str_contains($errLower, 'customer') || str_contains($errLower, 'id')) {
                    $errorType = 'Invalid Customer ID';
                } elseif (str_contains($errLower, 'key')) {
                    $errorType = 'Invalid API Key';
                } else {
                    $errorType = 'Invalid Credentials';
                }

                return response()->json([
                    'success'        => false,
                    'verified'       => false,
                    'message'        => $errorDetail,
                    'dotw_message'   => $errorDetail,
                    'error_type'     => $errorType,
                    'error_detail'   => $errorDetail,
                    'environment'    => $devMode ? 'developer' : 'production',
                    'http_code'      => $httpCode,
                    'response_time'  => $responseTime,
                    'debug'          => $devMode ? [
                        'xml'      => $xml,
                        'response' => $body,
                        'parsed'   => $parsed,
                        'endpoint' => $endpoint,
                    ] : null,
                ]);
            }

            // ── Simple check: getrooms returns rooms on valid creds, empty on invalid ──
            // Count rooms in response
            $roomNode  = $parsed['hotel']['rooms']['room'] ?? $parsed['rooms']['room'] ?? null;
            $roomCount = 0;
            if ($roomNode) {
                $roomCount = isset($roomNode[0]) ? count($roomNode) : 1;
            }

            // Also check hotel node exists
            $hasHotel = isset($parsed['hotel']) || isset($parsed['hotels']['hotel']);

            // Extract any error message from response
            preg_match('/<details>(.*?)<\/details>/is',          $body, $dm);
            preg_match('/<errorMessage>(.*?)<\/errorMessage>/is', $body, $em);
            $rawErr = trim(html_entity_decode($dm[1] ?? $em[1] ?? ''));

            // Wrong credentials OR zero rooms = invalid credentials
            if (!$hasHotel && $roomCount === 0) {
                $errMsg = !empty($rawErr)
                    ? $rawErr
                    : 'Wrong customer username or password or no access rights';

                return response()->json([
                    'success'       => false,
                    'verified'      => false,
                    'message'       => $errMsg,
                    'dotw_message'  => $errMsg,
                    'error_type'    => 'Invalid Username/Password',
                    'error_detail'  => $errMsg,
                    'environment'   => $devMode ? 'developer' : 'production',
                    'http_code'     => $httpCode,
                    'response_time' => $responseTime,
                    'debug'         => $devMode ? [
                        'xml'      => $xml,
                        'response' => $body,
                        'parsed'   => $parsed,
                        'endpoint' => $endpoint,
                    ] : null,
                ]);
            }

            // Hotel/rooms returned = credentials valid
            return response()->json([
                'success'       => true,
                'verified'      => true,
                'message'       => 'API Credentials Verified Successfully',
                'connection'    => 'Connected',
                'environment'   => $devMode ? 'developer' : 'production',
                'http_code'     => $httpCode,
                'response_time' => $responseTime,
                'endpoint'      => $endpoint,
                'api_info'      => [
                    'username'    => $username,
                    'customer_id' => $id,
                    'api_version' => (string) ($xmlObj['version'] ?? '4.0'),
                    'rooms_found' => $roomCount,
                ],
                'debug' => $devMode ? [
                    'xml'      => $xml,
                    'response' => $body,
                    'parsed'   => $parsed,
                    'curl_info'=> $curlInfo,
                    'endpoint' => $endpoint,
                ] : null,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success'       => false,
                'verified'      => false,
                'message'       => 'Server Error',
                'error_type'    => 'Server Error',
                'error_detail'  => $e->getMessage(),
                'response_time' => round((microtime(true) - $start) * 1000, 2),
            ], 500);
        }
    }


    // -------------------------------------------------------------------------
    // PUBLIC — GET ALL CITIES (Static Data)
    // -------------------------------------------------------------------------

    public function get_all_cities(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'api_credential_1' => 'required|string',
                'api_credential_2' => 'required|string',
                'api_credential_3' => 'required|string',
                'country_code'     => 'nullable|string',
                'country_name'     => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors'  => $validator->errors()->toArray(),
                ], 422);
            }

            $v           = $validator->validated();
            $password    = md5($v['api_credential_2']);
            $countryCode = $v['country_code'] ?? '';
            $countryName = $v['country_name'] ?? '';

            // Static data endpoint — always use production URL
            $staticEndpoint = self::API_ENDPOINT;

            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$v['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$v['api_credential_3']}</id>
    <source>1</source>
    <request command="getallcities">
        <return>
            <filters>
                <countryCode>{$countryCode}</countryCode>
                <countryName>{$countryName}</countryName>
            </filters>
            <fields>
                <field>{$countryCode}</field>
                <field>{$countryName}</field>
            </fields>
        </return>
    </request>
</customer>
XML;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $staticEndpoint,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $xml,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: text/xml; charset=UTF-8',
                    'Accept-Encoding: gzip, deflate',
                ],
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING       => 'gzip, deflate',
            ]);

            $body      = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection failed: ' . $curlError,
                ], 500);
            }

            if ($httpCode !== 200) {
                return response()->json([
                    'success'   => false,
                    'message'   => "HTTP error: {$httpCode}",
                    'http_code' => $httpCode,
                ], 500);
            }

            $parsed = $this->parseXmlToArray($body);

            // Check for auth errors
            $errorMsg = $parsed['error']['details']
                ?? $parsed['errorMessage']
                ?? $parsed['error']['message']
                ?? null;

            if ($errorMsg) {
                return response()->json([
                    'success' => false,
                    'message' => (string) $errorMsg,
                ], 422);
            }

            // Parse cities
            $rawCities = $parsed['city'] ?? [];
            if (empty($rawCities)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cities found.',
                    'data'    => [],
                ]);
            }

            // Normalize single city vs multiple
            if (isset($rawCities['@attributes'])) {
                $rawCities = [$rawCities];
            }

            $cities = array_map(fn($city) => [
                'city_id'      => (string) ($city['@attributes']['id']   ?? ''),
                'city_name'    => (string) ($city['cityName']             ?? ''),
                'country_code' => (string) ($city['countryCode']          ?? ''),
                'country_name' => (string) ($city['countryName']          ?? ''),
            ], $rawCities);

            return response()->json([
                'success'      => true,
                'message'      => 'Cities retrieved successfully.',
                'total'        => count($cities),
                'endpoint'     => $staticEndpoint,
                'data'         => $cities,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

}
