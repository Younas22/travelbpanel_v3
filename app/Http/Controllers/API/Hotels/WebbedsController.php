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

    // Salutation (title) IDs — from DOTW's getsalutationsids method.
    // 3801 = Mr, 3802 = Mrs/Ms (confirmed valid; "3803" is NOT valid in DOTW).
    // TODO: confirm the correct CHILD title id via get_salutations_ids() and set it here.
    private const SALUTATION_MR      = '3801';
    private const SALUTATION_MRS     = '3802';
    private const CHILD_SALUTATION_ID = '3801'; // placeholder — replace with the real child title id

    // -------------------------------------------------------------------------
    // PUBLIC — HOTEL SEARCH
    // -------------------------------------------------------------------------

    public function hotel_search(Request $request): JsonResponse
    {
        try {
            $validated   = $this->validate_input($request);

            // Drop any spurious 0/invalid child age so occupancy is accurate
            $validated['child_age'] = $this->parse_child_ages($validated['child_age'] ?? []);

            $destination = $this->get_destination($validated['city']);

            if (!$destination) {
                return $this->error_response('Destination not found.', 404);
            }

            // Per WebBeds: only use searchhotels with rateBasis=1 (price search)
            // Static data (noPrice=true) must NOT be part of booking flow
            $priceXml      = $this->build_search_params($validated, $destination);

            $priceResponse = $this->make_curl_request($priceXml, self::CURL_TIMEOUT_PRICE);

            if (!$priceResponse['success']) {
                return response()->json(['success' => false, 'message' => $priceResponse['message']], 500);
            }

            $priceData = $this->parseXmlToArray($priceResponse['body']);
            $hotels    = $priceData['hotels']['hotel'] ?? [];

            if (empty($hotels)) {
                return $this->error_response('No hotels found for the given criteria.', 404);
            }

            if (isset($hotels['@attributes'])) {
                $hotels = [$hotels];
            }

            $hotels   = array_slice($hotels, 0, self::HOTEL_BATCH_SIZE);
            $priceMap = $this->build_price_map($priceData);

            $result = [];

            foreach ($hotels as $hotel) {
                $hotelId = $hotel['@attributes']['hotelid'];
                $price   = $priceMap[$hotelId] ?? null;

                if ($price === null) continue;

                $roundedPrice = round($price['minRate'], 2);

                // get_hotelDetails() is a lookup against our LOCAL static-data cache
                // (for mapping/display only — name, images, rating) and can be null
                // for a hotel WebBeds returns live rates for but we haven't cached
                // yet. That must never break the live search/booking response.
                $staticHotelsDetails = $this->get_hotelDetails($hotelId);

                $html = (string) ($staticHotelsDetails->hotelImages ?? '');
                preg_match('/<thumb>(.*?)<\/thumb>/s', $html, $thumb);
                preg_match_all('/<url>(.*?)<\/url>/s', $html, $matches);
                $thumb = trim($thumb[1] ?? '');
                $images = array_slice( array_map('trim', $matches[1] ?? []), 0, 20 );


                $hotelNameRaw    = $staticHotelsDetails->hotelName ?? '';
                $hotelAddressRaw = $staticHotelsDetails->address  ?? '';

                $hotelRow = [
                    'hotel_id'          => $hotelId,
                    'name'              => $this->sanitize_name(is_array($hotelNameRaw) ? '' : $hotelNameRaw),
                    'address'           => is_array($hotelAddressRaw) ? '' : $hotelAddressRaw,
                    'stars'             => $this->convert_rating( $staticHotelsDetails->rating ?? 0),
                    'minRate'           => $roundedPrice,
                    'real_price'        => $roundedPrice,
                    'actual_price'      => $roundedPrice,
                    'currency'          => $price['currencyId'],
                    'original_currency' => $price['currencyId'],
                    'room_name'         => $price['roomName'],
                    'images'            => $thumb ?? '',
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

            return response()->json([
                'success'        => true,
                'data'           => $result,
            ]);

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
                'child_age'            => 'nullable|array',
                'child_age.*'          => 'integer|min:0|max:17',
                // CC integration: multiroom bookings are not supported — single room only
                'rooms'                => 'required|integer|min:1|max:1',
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

            $childAges = $this->parse_child_ages($v['child_age'] ?? '');

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

                        // Cert #19: <changedOccupancy> is NOT a true/false flag — when
                        // present it's a CSV of the occupancy WebBeds actually priced this
                        // rate for (adults,children,childAge...,extraBeds), which differs
                        // from what was requested (e.g. it substituted 3 adults/1 child for
                        // our requested 2 adults/2 children because this room type can't
                        // fit both children). Comparing it to the literal string "true"
                        // never matched, so this always reported false. The element's mere
                        // presence/non-emptiness is what signals the occupancy was changed;
                        // <validForOccupancy> alongside it holds the actual split used, so
                        // the frontend can show "priced for 3 adults, 1 child" etc.
                        $changedOccupancy = trim((string) ($rateBasis['changedOccupancy'] ?? '')) !== '';

                        $validForOccupancy = null;
                        if ($changedOccupancy && !empty($rateBasis['validForOccupancy'])) {
                            $vfo = $rateBasis['validForOccupancy'];
                            $vfoChildAges = trim((string) ($vfo['childrenAges'] ?? ''));
                            $validForOccupancy = [
                                'adults'        => (int) ($vfo['adults'] ?? 0),
                                'children'      => (int) ($vfo['children'] ?? 0),
                                'children_ages' => $vfoChildAges !== '' ? array_map('intval', explode(',', $vfoChildAges)) : [],
                                'extra_bed'     => (int) ($vfo['extraBed'] ?? 0),
                            ];
                        }

                        if ($total <= 0 || $isBookable !== 'yes' || empty($allocationDetails)) continue;

                        $sellTotal  = $commission > 0 ? round($total / (1 - $commission / 100), 2) : round($total, 2);
                        $netTotal   = round($total, 2);
                        $sellPerDay = round($sellTotal / $nights, 2);
                        $netPerDay  = round($netTotal  / $nights, 2);

                        $cancelRules      = $this->parse_cancellation_rules($rateBasis['cancellationRules']['rule'] ?? []);
                        $firstPenalty     = collect($cancelRules)->firstWhere('type', 'penalty');
                        $cancelRestricted = collect($cancelRules)->contains('cancel_restricted', true);
                        $amendRestricted  = collect($cancelRules)->contains('amend_restricted', true);
                        $passengersRequired = (int) ($rateBasis['passengerNamesRequiredForBooking'] ?? 1);

                        // Cert #21: Taxes & Fees — per WebBeds format
                        // Display separately: included in price vs payable at property
                        $taxesFees          = [];
                        $taxesIncluded      = [];
                        $taxesAtProperty    = [];
                        $rawTaxes           = $rateBasis['taxesFees']['tax'] ?? [];
                        if (!empty($rawTaxes)) {
                            if (isset($rawTaxes['@attributes'])) $rawTaxes = [$rawTaxes];
                            foreach ($rawTaxes as $tax) {
                                $isIncluded = strtolower((string) ($tax['@attributes']['included'] ?? 'false')) === 'true';
                                $taxEntry   = [
                                    'type'        => (string) ($tax['@attributes']['type']     ?? ''),
                                    'description' => (string) ($tax['description']             ?? ''),
                                    'amount'      => (float)  ($tax['amount']                  ?? 0),
                                    'currency'    => (string) ($tax['@attributes']['currency'] ?? ''),
                                    'included'    => $isIncluded,
                                ];
                                $taxesFees[] = $taxEntry;
                                if ($isIncluded) {
                                    $taxesIncluded[]   = $taxEntry;
                                } else {
                                    $taxesAtProperty[] = $taxEntry;
                                }
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
                            'changed_occupancy'   => $changedOccupancy,
                            'valid_for_occupancy' => $validForOccupancy,
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
                            // Cert #21: taxes & fees — split per WebBeds display requirement
                            'taxes_fees'          => $taxesFees,
                            'taxes_included'      => $taxesIncluded,
                            'taxes_at_property'   => $taxesAtProperty,
                            // Cert #22: restricted flags — <amendRestricted>/
                            // <cancelRestricted> live on individual rules inside
                            // <cancellationRules>, not on the rateBasis itself;
                            // true if ANY of this rate's rules is restricted.
                            'non_refundable'      => strtolower($this->xml_str($rateBasis['nonRefundable']    ?? 'no'))   === 'yes',
                            'cancel_restricted'       => $cancelRestricted,
                            'amend_restricted'        => $amendRestricted,
                            'cancel_restricted_note'  => $cancelRestricted
                                ? 'Cancellation not allowed'
                                : null,
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


            // See the same null-guard note in hotel_search() above — a hotel WebBeds
            // returns live rates for may not yet exist in our static-data cache.
            $staticHotelsDetails = $this->get_hotelDetails($hotelId);

            $html = (string) ($staticHotelsDetails->hotelImages ?? '');
            preg_match('/<thumb>(.*?)<\/thumb>/s', $html, $thumb);
            preg_match_all('/<url>(.*?)<\/url>/s', $html, $matches);
            $thumb = trim($thumb[1] ?? '');
            $images = array_slice( array_map('trim', $matches[1] ?? []), 0, 20 );


            $hotelNameRaw    = $staticHotelsDetails->hotelName ?? '';
            $hotelAddressRaw = $staticHotelsDetails->address  ?? '';


            return response()->json([
                'success'        => true,
                'response'       => [[
                    'h_id'          => $hotelId,
                    'h_name'        => $hotelNameRaw  ?? '',
                    'address'       => $hotelAddressRaw ?? '',
                    'stars'         => $this->convert_rating( $staticHotelsDetails->rating ?? 0),
                    'imgs'          => $images  ?? [],
                    'lat'           => '',
                    'lng'           => '',
                    'agent_id'      => '',
                    'city'          => '',
                    'country'       => $cachedListing['location'] ?? '',
                    'rating'        => $this->convert_rating( $staticHotelsDetails->rating ?? 0),
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
                'male' => self::SALUTATION_MR,  'female' => self::SALUTATION_MRS,
                'mr'   => self::SALUTATION_MR,  'mrs'    => self::SALUTATION_MRS,
                'ms'   => self::SALUTATION_MRS, 'miss'   => self::SALUTATION_MRS,
            ];

            $roomData  = json_decode($v['booking_data']);

            // ── Base fields from room_data (hotel + dates context) ────────────
            $checkin      = $roomData->room_data->checkin;
            $checkout     = $roomData->room_data->checkout;
            $productId    = $roomData->room_data->product_id;
            $adults       = (int) $roomData->room_data->adults;
            $children     = $this->parse_child_ages($roomData->room_data->children ?? []);
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
            $childCount         = count($children);
            // DOTW needs a name per occupant (adults + children)
            $passengersRequired = max($passengersFromOpt, $adults + $childCount);

            // Build passengers from guest input. Each guest may flag itself as a
            // child (type/is_child); otherwise it's treated as an adult.
            $passengers = [];
            foreach ($guest as $traveller) {
                $titleKey = strtolower(trim($traveller->title ?? ''));
                $type     = strtolower(trim($traveller->type ?? ''));
                $isChild  = !empty($traveller->is_child) || $type === 'child';
                $passengers[] = [
                    'salutation' => $isChild ? self::CHILD_SALUTATION_ID : ($salutationMap[$titleKey] ?? self::SALUTATION_MR),
                    'first_name' => $this->sanitize_name($traveller->first_name ?? 'Guest'),
                    'last_name'  => $this->sanitize_name($traveller->last_name  ?? 'Guest'),
                    'is_child'   => $isChild,
                ];
            }

            // Pad up to the required count with DISTINCT names (never duplicate an
            // existing passenger). Padded slots are adults unless children remain.
            $childrenSoFar = count(array_filter($passengers, fn($p) => !empty($p['is_child'])));
            while (count($passengers) < $passengersRequired) {
                $needChild    = $childrenSoFar < $childCount;
                $passengers[] = [
                    'salutation' => $needChild ? self::CHILD_SALUTATION_ID : self::SALUTATION_MR,
                    'first_name' => $needChild ? 'Child' : 'Guest',
                    'last_name'  => 'Traveller',
                    'is_child'   => $needChild,
                ];
                if ($needChild) $childrenSoFar++;
            }
            $passengers = array_slice($passengers, 0, $passengersRequired);
            $passengers = $this->ensure_unique_passenger_names($passengers);

            // ── Pre-book: getrooms with blocking (logged as step3_getroomsblock) ──
            // WebBeds requires the allocation to be re-validated/blocked immediately
            // before savebooking. Run it here so it is ALWAYS executed and logged as
            // part of the booking, and use its freshly blocked allocationDetails for
            // savebooking. This gates the booking: if the block fails, we stop.
            // $children is already sanitized to real ages (1..17)
            $childAgesForBlock = $children;

            $blockParams = [
                'api_credential_1'     => $v['api_credential_1'],
                'api_credential_2'     => $v['api_credential_2'],
                'api_credential_3'     => $v['api_credential_3'],
                'checkin'              => $checkin,
                'checkout'             => $checkout,
                'rooms'                => 1,
                'adults'               => $adults,
                'nationality'          => $nationality,
                'country_of_residence' => $residence,
                'hotel_id'             => $productId,
                'room_type_code'       => $roomTypeCode,
                'rate_basis_id'        => $selectedRateBasis,
                'allocation_details'   => $allocationDetails,
            ];

            $blockXml      = $this->build_getrooms_block_xml($blockParams, $childAgesForBlock);
            $blockResponse = $this->make_curl_request($blockXml, self::CURL_TIMEOUT_PRICE);

            if (!$blockResponse['success']) {
                return response()->json(['success' => false, 'booking_pnr' => null, 'step' => 'getroomsblock', 'message' => 'getrooms with blocking failed: ' . $blockResponse['message'], 'response' => $blockResponse['body'] ?? null], 500);
            }

            $blockData = $this->parseXmlToArray($blockResponse['body']);

            if (strtoupper($blockData['successful'] ?? '') !== 'TRUE') {
                $blockErr = $blockData['error']['details'] ?? ($blockData['errorMessage'] ?? 'Room no longer available for booking.');
                return response()->json(['success' => false, 'booking_pnr' => null, 'step' => 'getroomsblock', 'message' => is_array($blockErr) ? implode(', ', $blockErr) : (string) $blockErr, 'response' => $blockData], 409);
            }

            // Extract the fresh (blocked) allocation for the selected room type + rate basis
            $blockHotelNode = $blockData['hotel'] ?? $blockData['hotels']['hotel'] ?? null;
            if (!isset($blockHotelNode['@attributes'])) {
                $blockHotelNode = $blockHotelNode[0] ?? $blockHotelNode;
            }
            $blockRooms = $blockHotelNode['rooms']['room'] ?? [];
            if (isset($blockRooms['@attributes'])) $blockRooms = [$blockRooms];

            $freshAllocation = '';
            $blockChecked    = false;
            foreach ((array) $blockRooms as $bRoom) {
                $bRoomTypes = $bRoom['roomType'] ?? [];
                if (isset($bRoomTypes['@attributes'])) $bRoomTypes = [$bRoomTypes];
                foreach ($bRoomTypes as $bRt) {
                    if (($bRt['@attributes']['roomtypecode'] ?? '') !== $roomTypeCode) continue;
                    $bRateBases = $bRt['rateBases']['rateBasis'] ?? [];
                    if (isset($bRateBases['@attributes'])) $bRateBases = [$bRateBases];
                    foreach ($bRateBases as $bRb) {
                        if ((string) ($bRb['@attributes']['id'] ?? '') !== (string) $selectedRateBasis) continue;
                        if (strtolower($bRb['status'] ?? '') === 'checked') {
                            $blockChecked    = true;
                            $freshAllocation = (string) ($bRb['allocationDetails'] ?? '');
                        }
                        break 3;
                    }
                }
            }

            if (!$blockChecked || empty($freshAllocation)) {
                return response()->json(['success' => false, 'booking_pnr' => null, 'step' => 'getroomsblock', 'message' => 'Room could not be blocked (status not checked). Please re-search and try again.', 'response' => $blockData], 409);
            }

            // Use the freshly blocked allocation for savebooking
            $allocationDetails = $freshAllocation;

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
                'child_age'            => 'nullable|array',
                'child_age.*'          => 'integer|min:0|max:17',
                // CC integration: multiroom bookings are not supported — single room only
                'rooms'                => 'required|integer|min:1|max:1',
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
            $childAges = $this->parse_child_ages($v['child_age'] ?? '');

            $blockXml      = $this->build_getrooms_block_xml($v, $childAges);
            $blockResponse = $this->make_curl_request($blockXml, self::CURL_TIMEOUT_PRICE);

            if (!$blockResponse['success']) {
                return response()->json(['success' => false, 'message' => $blockResponse['message']], 500);
            }

            $blockData      = $this->parseXmlToArray($blockResponse['body']);
            $blockXmlNative = @simplexml_load_string($blockResponse['body'], 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($blockXmlNative === false) $blockXmlNative = null;

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
            // Taxes & property fees can change during the getrooms-with-blocking
            // validation, so capture the fresh values from the checked rate basis
            $taxesAndFees      = [
                'total_taxes'         => 0.0,
                'currency'            => $v['currency'],
                'property_fees'       => [],
                'included_in_price'   => [],
                'payable_at_property' => [],
            ];

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
                            $nativeRateBasis   = $this->find_native_rate_basis($blockXmlNative, $v['room_type_code'], (string) $v['rate_basis_id']);
                            $taxesAndFees      = $this->extract_taxes_and_property_fees($rb, $nativeRateBasis);
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
                // Checkout breakdown — display before confirming the booking.
                // Amounts reflect the latest getrooms-with-blocking values and are
                // split into what is already included in price vs payable at the property.
                'taxes_and_fees'     => $taxesAndFees,
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
                'payment_balance'  => 'required|numeric',
                'service_code'     => 'nullable|string',
                // Required only when finalizing — sourced from the confirm=no
                // preview response the caller already received (see below).
                'penalty_charge'   => 'required_if:confirm,yes|nullable|numeric',
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

            if ($confirm === 'no') {
                // Preview only — ask WebBeds for the authoritative, full-precision
                // cancellation charge (its <charge> element carries 4 decimal
                // places, e.g. 75.6721) so the caller can show the customer the
                // exact amount before they confirm. Nothing is cancelled yet.
                $noXml      = $this->build_cancelbooking_xml($v, $bookingCode, $password, 'no');
                $noResponse = $this->make_curl_request($noXml, self::CURL_TIMEOUT_PRICE);

                if (!$noResponse['success']) {
                    return response()->json(['success' => false, 'message' => $noResponse['message']], 500);
                }

                $noData = $this->parseXmlToArray($noResponse['body']);

                if (strtoupper($noData['successful'] ?? '') !== 'TRUE') {
                    $errMsg = $noData['error']['details'] ?? ($noData['errorMessage'] ?? 'Cancel booking failed.');
                    return $this->error_response((string) $errMsg, 422);
                }

                $serviceNode  = $noData['services']['service'] ?? [];
                if (isset($serviceNode['@attributes'])) $serviceNode = [$serviceNode];
                $firstService = $serviceNode[0] ?? [];
                $serviceCode  = (string) ($firstService['@attributes']['code'] ?? $v['service_code'] ?? $bookingCode);
                $chargeNode   = $firstService['cancellationPenalty']['charge'] ?? 0;
                $charge       = (float) (is_array($chargeNode) ? ($chargeNode[0] ?? 0) : $chargeNode);
                $paymentBal   = round((float) $v['payment_balance'] - $charge, 4);

                return response()->json([
                    'success'         => true,
                    'booking_code'    => $bookingCode,
                    'service_code'    => $serviceCode,
                    'penalty_charge'  => $charge,
                    'payment_balance' => $paymentBal,
                    'status'          => 'preview',
                    'message'         => 'Cancellation charge retrieved. Call again with confirm=yes to finalize.',
                    'raw'             => $noData,
                ]);
            }

            // confirm=yes — finalize using the charge the caller already has
            // from their own earlier confirm=no preview call (above). WebBeds
            // requires confirm=yes to carry a <testPricesAndAllocation> block
            // asserting that exact charge (omitting it is rejected with error
            // 320); re-probing with confirm=no again here just to rebuild that
            // block would repeat the same request the caller already made —
            // the redundant extra call WebBeds certification flagged — so we
            // reuse the preview's own figures instead of re-fetching them.
            $serviceCode = $v['service_code'] ?? $bookingCode;
            $charge      = (float) $v['penalty_charge'];
            $paymentBal  = round((float) $v['payment_balance'] - $charge, 4);

            $yesXml      = $this->build_cancelbooking_xml($v, $bookingCode, $password, 'yes', $serviceCode, $charge, $paymentBal);
            $yesResponse = $this->make_curl_request($yesXml, self::CURL_TIMEOUT_PRICE);

            if (!$yesResponse['success']) {
                return response()->json(['success' => false, 'message' => 'Cancellation request failed: ' . $yesResponse['message']], 500);
            }

            $yesData = $this->parseXmlToArray($yesResponse['body']);

            if (strtoupper($yesData['successful'] ?? '') !== 'TRUE') {
                $errMsg = $yesData['error']['details'] ?? ($yesData['errorMessage'] ?? 'Cancel booking failed.');
                return $this->error_response((string) $errMsg, 422);
            }

            $productsLeft = (int) ($yesData['productsLeftOnItinerary'] ?? 0);

            return response()->json([
                'success'                    => true,
                'booking_code'               => $bookingCode,
                'service_code'               => $serviceCode,
                'penalty_charge'             => $charge,
                'payment_balance'            => $paymentBal,
                'products_left_on_itinerary' => $productsLeft,
                'partial_cancellation'       => $productsLeft > 0,
                'status'                     => 'cancelled',
                'message'                    => $productsLeft > 0
                    ? "Partially cancelled. {$productsLeft} service(s) still active."
                    : 'Booking cancelled successfully.',
                'raw' => $yesData,
            ]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Build a WebBeds cancelbooking XML request. confirm=no (preview) sends no
     * testPricesAndAllocation block; confirm=yes (finalize) requires one — see
     * hotel_cancel_booking(), which sources $serviceCode/$penalty/$paymentBalance
     * from the caller's own earlier confirm=no preview rather than re-probing.
     */
    private function build_cancelbooking_xml(
        array $v,
        string $bookingCode,
        string $password,
        string $confirm,
        ?string $serviceCode = null,
        ?float $penalty = null,
        ?float $paymentBalance = null
    ): string {
        $testBlock = '';
        if ($confirm === 'yes' && $serviceCode !== null && $penalty !== null && $paymentBalance !== null) {
            $testBlock = <<<TESTBLOCK
            <testPricesAndAllocation>
                <service referencenumber="{$serviceCode}">
                    <penaltyApplied>{$penalty}</penaltyApplied>
                    <paymentBalance>{$paymentBalance}</paymentBalance>
                </service>
            </testPricesAndAllocation>
TESTBLOCK;
        }

        return <<<XMLREQ
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

        if ($curlError) {
            $this->maybe_log_xml($xml, null);
            return ['success' => false, 'message' => "cURL error: {$curlError}"];
        }
        if ($httpCode !== 200) {
            $this->maybe_log_xml($xml, $body);
            return ['success' => false, 'message' => "HTTP error: {$httpCode}"];
        }
        $this->maybe_log_xml($xml, $body);
        return ['success' => true, 'body' => $body];
    }

    /**
     * Opt-in request/response XML capture for certification evidence — inert
     * unless WEBBEDS_XML_LOG_DIR is set in the environment, so it never runs
     * in normal production traffic.
     */
    private static int $xmlLogSeq = 0;

    private function maybe_log_xml(string $requestXml, ?string $responseXml): void
    {
        $dir = env('WEBBEDS_XML_LOG_DIR');
        if (!$dir) return;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        self::$xmlLogSeq++;
        $label = 'step';
        if (preg_match('/command="([a-zA-Z]+)"/', $requestXml, $m)) {
            $label = $m[1];
        }
        $base = sprintf('%s_%02d_%s', date('His'), self::$xmlLogSeq, $label);
        @file_put_contents("{$dir}/{$base}_request.xml", $requestXml);
        @file_put_contents("{$dir}/{$base}_response.xml", $responseXml ?? '');
    }

    // -------------------------------------------------------------------------
    // PRIVATE — XML BUILDERS
    // -------------------------------------------------------------------------

    private function build_search_params(array $validated, object $destination): string
    {
        $password    = md5($validated['api_credential_2']);
        $childrenXml = $this->build_children_xml($validated['child_age'] ?? []);
        $nationality = '167' ;
        $residence   = '167';
        $roomsCount  = (int) ($validated['rooms'] ?? 1);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$validated['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$validated['api_credential_3']}</id>
    <source>1</source>
    <product>hotel</product>
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
            <filters>
                <city>{$destination->code}</city>
            </filters>
        </return>
    </request>
</customer>
XML;
    }

    private function build_price_batch_params(array $validated, array $hotelIds): string
    {
        $password    = md5($validated['api_credential_2']);
        $childrenXml = $this->build_children_xml($validated['child_age'] ?? []);
        $nationality = '167';
        $residence   =  '167';
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
        // Keep only real child ages (1..17); a stray 0/invalid entry must NOT
        // become a phantom child in the occupancy.
        $children = $this->parse_child_ages($children);
        $count = count($children);
        if ($count === 0) return '<children no="0"/>';
        $xml = "<children no=\"{$count}\">";
        foreach (array_values($children) as $index => $age) {
            $xml .= "<child runno=\"{$index}\">{$age}</child>";
        }
        return $xml . '</children>';
    }

    /**
     * Normalise a child-age input (comma string "5,8" or an array) into a clean
     * list of real child ages. Non-numeric values and ages outside 1..17 (which
     * includes the spurious "0" the frontend sometimes sends for no-children
     * bookings) are dropped, so occupancy never contains a phantom child.
     */
    private function parse_child_ages(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = trim($raw) === '' ? [] : explode(',', $raw);
        }
        $ages = [];
        foreach ((array) $raw as $age) {
            if (!is_numeric($age)) continue;
            $age = (int) $age;
            if ($age >= 1 && $age <= 17) $ages[] = $age;
        }
        return array_values($ages);
    }

    private function build_hotel_detail_params(array $v, string $hotelId, array $childAges): string
    {
        $password    = md5($v['api_credential_2']);
        $childrenXml = $this->build_children_xml($childAges);
        $nationality = '167';
        $residence   =  '167';
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
            $children     = $this->parse_child_ages($room['children'] ?? []);
            $childCount   = count($children);
            $adultsCode   = (int) $room['adults'];
            $actualAdults = (int) ($room['actual_adults'] ?? $adultsCode);
            $extraBed     = (int) ($room['extra_bed'] ?? 0);
            $nationality  = $room['nationality']          ?: '167';
            $residence    = $room['country_of_residence'] ?: '167';

            // Required passengers = adults + children
            $requiredPassengers = $adultsCode + $childCount;

            if ($childCount === 0) {
                $childrenXml       = '<children no="0"></children>';
                $actualChildrenXml = '<actualChildren no="0"></actualChildren>';
            } else {
                $childrenXml       = '<children no="' . $childCount . '">';
                $actualChildrenXml = '<actualChildren no="' . $childCount . '">';
                foreach (array_values($children) as $ci => $age) {
                    $childrenXml       .= '<child runno="' . $ci . '">' . $age . '</child>';
                    $actualChildrenXml .= '<actualChild runno="' . $ci . '">' . $age . '</actualChild>';
                }
                $childrenXml       .= '</children>';
                $actualChildrenXml .= '</actualChildren>';
            }

            // Split incoming guests into adults vs children by the explicit
            // is_child flag (never by salutation — DOTW has no "3803" title).
            $allGuests    = $room['passengers'] ?? [];
            $adultGuests  = array_values(array_filter($allGuests, fn($p) => empty($p['is_child'])));
            $childGuests  = array_values(array_filter($allGuests, fn($p) => !empty($p['is_child'])));

            // Assemble the final passenger list with UNIQUE names (DOTW rejects
            // duplicate names in the same room). Adults first, then children.
            $finalPassengers = [];
            for ($i = 0; $i < $adultsCode; $i++) {
                $finalPassengers[] = $adultGuests[$i] ?? [
                    'salutation' => '3801',
                    'first_name' => 'Guest',
                    'last_name'  => 'Guest',
                    'is_child'   => false,
                ];
            }
            for ($i = 0; $i < $childCount; $i++) {
                $finalPassengers[] = $childGuests[$i] ?? [
                    'salutation' => self::CHILD_SALUTATION_ID,
                    'first_name' => 'Child',
                    'last_name'  => 'Guest',
                    'is_child'   => true,
                ];
            }
            $finalPassengers = array_slice($finalPassengers, 0, $requiredPassengers);

            // Guarantee no two passengers share the same first+last name.
            $finalPassengers = $this->ensure_unique_passenger_names($finalPassengers);

            $passengersXml = '<passengersDetails>';
            foreach ($finalPassengers as $i => $p) {
                $leadAttr       = ($i === 0) ? ' leading="yes"' : '';
                $isChild        = !empty($p['is_child']);
                $salutation     = $isChild ? self::CHILD_SALUTATION_ID : ($p['salutation'] ?? '3801');
                $passengersXml .= '<passenger' . $leadAttr . '>'
                    . '<salutation>' . $salutation . '</salutation>'
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

    /**
     * Locate the SimpleXMLElement for a specific rateBasis (by roomtypecode + rate
     * basis id) directly in the parsed XML tree — used so property fees can be read
     * natively instead of through the lossy array round-trip (see extract_taxes_and_property_fees).
     */
    private function find_native_rate_basis(?\SimpleXMLElement $xml, string $roomTypeCode, string $rateBasisId): ?\SimpleXMLElement
    {
        if ($xml === null) return null;

        foreach ($xml->xpath('//roomType') as $rtNode) {
            $rtAttrs = $rtNode->attributes();
            if ((string) ($rtAttrs['roomtypecode'] ?? '') !== $roomTypeCode) continue;

            if (!isset($rtNode->rateBases->rateBasis)) continue;
            foreach ($rtNode->rateBases->rateBasis as $rbNode) {
                $rbAttrs = $rbNode->attributes();
                if ((string) ($rbAttrs['id'] ?? '') !== $rateBasisId) continue;
                return $rbNode;
            }
        }

        return null;
    }

    /**
     * Extract taxes & property fees from a rate basis node (getrooms-with-blocking).
     *
     * propertyFee elements carry BOTH attributes (name, includedinprice, ...) and
     * mixed text+<formatted> content. SimpleXML's json round-trip used by
     * parseXmlToArray() drops the attributes AND the <formatted> child on nodes
     * shaped like this, keeping only the raw leading text — so every fee silently
     * failed the is_array() check below and was skipped. We read them natively
     * off $nativeRateBasis instead, which preserves everything. totalTaxes has no
     * attributes so it survives the array round-trip fine and is read from $rateBasis.
     * Each property fee carries includedinprice="Yes|No" — Yes = already in price,
     * No = payable at the property. Returns the breakdown for the checkout page.
     */
    private function extract_taxes_and_property_fees(array $rateBasis, ?\SimpleXMLElement $nativeRateBasis = null): array
    {
        $totalTaxesNode = $rateBasis['totalTaxes'] ?? null;
        if (is_array($totalTaxesNode)) {
            $totalTaxes = (float) ($totalTaxesNode['formatted'] ?? 0);
        } else {
            $totalTaxes = (float) ($totalTaxesNode ?? 0);
        }

        $all        = [];
        $included   = [];
        $atProperty = [];
        $currency   = '';

        if ($nativeRateBasis !== null && isset($nativeRateBasis->propertyFees->propertyFee)) {
            foreach ($nativeRateBasis->propertyFees->propertyFee as $fee) {
                $attr       = $fee->attributes();
                $amount     = isset($fee->formatted) ? (float) $fee->formatted : (float) trim((string) $fee);
                $isIncluded = strtolower((string) ($attr['includedinprice'] ?? 'no')) === 'yes';
                $currency   = $currency ?: (string) ($attr['currencyshort'] ?? '');

                $entry = [
                    'name'              => (string) ($attr['name']          ?? ''),
                    'description'       => (string) ($attr['description']   ?? ''),
                    'amount'            => $amount,
                    'currency'          => (string) ($attr['currencyshort'] ?? ''),
                    'currency_id'       => (string) ($attr['currencyid']    ?? ''),
                    'included_in_price' => $isIncluded,
                    'payable'           => $isIncluded ? 'included_in_price' : 'payable_at_property',
                ];

                $all[] = $entry;
                if ($isIncluded) {
                    $included[]   = $entry;
                } else {
                    $atProperty[] = $entry;
                }
            }
        }

        return [
            'total_taxes'         => $totalTaxes,
            'currency'            => $currency,
            'property_fees'       => $all,
            'included_in_price'   => $included,
            'payable_at_property' => $atProperty,
        ];
    }

    private function parse_cancellation_rules(array $rules): array
    {
        if (empty($rules)) return [];
        // A single <rule> parses to one flat associative array (its own
        // '@attributes' key holds runno); a list of rules is a numeric array
        // of such associative arrays. Checking for specific field names like
        // toDate/noShowPolicy here used to miss rules that have neither —
        // e.g. a bare <rule><amendRestricted>true</amendRestricted>
        // <cancelRestricted>true</cancelRestricted></rule> with no charge or
        // dates at all — which then fell into the foreach below as if it
        // were a list, iterating over its own field VALUES as "rules" and
        // silently producing garbage (so a restricted rule was reported as
        // ordinary "Free cancellation").
        if (isset($rules['@attributes'])) $rules = [$rules];

        $parsed = [];
        foreach ($rules as $rule) {
            if (!empty($rule['noShowPolicy'])) {
                $parsed[] = ['type' => 'no_show', 'from_date' => null, 'to_date' => null, 'cancel_charge' => round((float) ($rule['charge'] ?? 0), 2), 'amend_charge' => null, 'description' => 'No-show charge', 'cancel_restricted' => false, 'amend_restricted' => false];
                continue;
            }

            $cancelRestricted = strtolower((string) ($rule['cancelRestricted'] ?? '')) === 'true';
            $amendRestricted  = strtolower((string) ($rule['amendRestricted']  ?? '')) === 'true';
            $cancelCharge     = round((float) ($rule['cancelCharge'] ?? 0), 2);
            $amendCharge      = round((float) ($rule['amendCharge']  ?? 0), 2);

            $type = 'free';
            $description = 'Free cancellation';
            if ($cancelRestricted) {
                $type = 'restricted';
                $description = 'Cancellation not allowed';
            } elseif ($cancelCharge > 0) {
                $type = 'penalty';
                $description = "Cancellation charge: {$cancelCharge}";
            }

            $parsed[] = [
                'type'              => $type,
                'from_date'         => $rule['fromDate'] ?? null,
                'to_date'           => $rule['toDate']   ?? null,
                'cancel_charge'     => $cancelCharge,
                'amend_charge'      => $amendCharge,
                'description'       => $description,
                'cancel_restricted' => $cancelRestricted,
                'amend_restricted'  => $amendRestricted,
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

    /**
     * Ensure no two passengers in the same room share an identical first+last
     * name (DOTW rejects duplicates). When a clash is found, a distinct
     * alphabetic suffix (A, B, C, …) is appended to the last name. Letters only,
     * so the value still passes sanitize_name and stays within length.
     */
    private function ensure_unique_passenger_names(array $passengers): array
    {
        $used = [];
        foreach ($passengers as &$p) {
            $first = $this->sanitize_name($p['first_name'] ?? 'Guest');
            $last  = $this->sanitize_name($p['last_name']  ?? 'Guest');
            $base  = $last;
            $i     = 0;
            while (isset($used[strtolower($first . '|' . $last)])) {
                $suffix = chr(65 + ($i % 26)); // A, B, C, ...
                $last   = substr($base, 0, 24) . $suffix;
                $i++;
            }
            $used[strtolower($first . '|' . $last)] = true;
            $p['first_name'] = $first;
            $p['last_name']  = $last;
        }
        unset($p);
        return $passengers;
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

    private function get_hotelDetails(string $hotel_id): ?object
    {
        try {
            return DB::connection('sqlite_webbeds')
                ->table('hotels')
                ->where('hotel_id', $hotel_id)
                ->first();
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
            'childs'             => 'nullable|integer|min:0|max:20',
            'children.*'           => 'integer|min:0|max:17',
            'child_age'            => 'nullable|array',
            'child_age.*'          => 'integer|min:0|max:17',
            // CC integration: multiroom bookings are not supported — single room only
            'rooms'                => 'required|integer|min:1|max:1',
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
                'developer_mode'   => 'nullable|boolean',
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
            $devMode     = !empty($v['developer_mode']);

            // Static data endpoint — always use production URL
            $staticEndpoint = 'https://us.dotwconnect.com/gateway.dotw';

            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$v['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$v['api_credential_3']}</id>
    <source>1</source>
    <request command="getallcities">
        <return>
            <fields>
                <field>countryName</field>
                <field>countryCode</field>
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
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: text/xml; charset=UTF-8',
                    'Accept-Encoding: gzip, deflate',
                ],
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
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

            if (!in_array($httpCode, [200, 201])) {
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
            // Debug — show raw parsed keys to identify correct node
            if ($devMode ?? false) {
                return response()->json([
                    'success'     => true,
                    'debug_keys'  => array_keys($parsed),
                    'debug_first' => array_slice($parsed, 0, 2),
                    'raw_body'    => substr($body, 0, 500),
                ]);
            }

            // DOTW wraps cities inside 'request' node
            $requestNode = $parsed['request'] ?? [];
            $rawCities   = $requestNode['city']
                ?? $requestNode['cities']['city']
                ?? $parsed['city']
                ?? $parsed['cities']['city']
                ?? [];

            if (empty($rawCities)) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'No cities found for the given filter.',
                    'total'       => 0,
                    'parsed_keys' => array_keys($parsed),
                    'data'        => [],
                ]);
            }

            // Normalize single city (assoc) vs multiple (indexed)
            if (isset($rawCities['@attributes'])) {
                $rawCities = [$rawCities];
            }

            $cities = [];
            foreach ($rawCities as $city) {
                $cityCountryCode = (string) ($city['countryCode'] ?? $city['country_code'] ?? '');
                $cityCountryName = (string) ($city['countryName'] ?? $city['country_name'] ?? '');
                $cityId          = (string) ($city['@attributes']['id'] ?? $city['id'] ?? '');
                $cityName        = (string) ($city['cityName'] ?? $city['city_name'] ?? $city['name'] ?? '');

                // PHP-level filter as fallback if DOTW filter did not apply
                if (!empty($countryCode) && strtoupper($cityCountryCode) !== strtoupper($countryCode)) {
                    continue;
                }
                if (!empty($countryName) && stripos($cityCountryName, $countryName) === false) {
                    continue;
                }

                $cities[] = [
                    'city_id'      => $cityId,
                    'city_name'    => $cityName,
                    'country_code' => $cityCountryCode,
                    'country_name' => $cityCountryName,
                ];
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Cities retrieved successfully.',
                'total'    => count($cities),
                'endpoint' => $staticEndpoint,
                'data'     => $cities,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    // -------------------------------------------------------------------------
    // PUBLIC — GET SALUTATION IDS (title codes)
    // -------------------------------------------------------------------------

    /**
     * Fetch the valid salutation (title) IDs from DOTW via the getsalutationsids
     * method. Use the returned numeric ids to confirm the adult titles and the
     * correct CHILD title id (the code currently uses SALUTATION_MR/MRS for
     * adults; "3803" is NOT valid).
     */
    public function get_salutations_ids(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'api_credential_1' => 'required|string',
                'api_credential_2' => 'required|string',
                'api_credential_3' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation Error', 422, ['errors' => $validator->errors()->toArray()]);
            }

            $v        = $validator->validated();
            $password = md5($v['api_credential_2']);

            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$v['api_credential_1']}</username>
    <password>{$password}</password>
    <id>{$v['api_credential_3']}</id>
    <source>1</source>
    <request command="getsalutationsids"></request>
</customer>
XML;

            $response = $this->make_curl_request($xml, self::CURL_TIMEOUT_PRICE);

            if (!$response['success']) {
                return response()->json(['success' => false, 'message' => $response['message']], 500);
            }

            $parsed = $this->parseXmlToArray($response['body']);

            return response()->json([
                'success' => true,
                'message' => 'Salutation IDs retrieved. Use these to confirm adult and child title codes.',
                'data'    => $parsed,
                'raw'     => $response['body'],
            ]);

        } catch (Exception $e) {
            return $this->error_response('Server Error', 500, ['exception' => $e->getMessage()]);
        }
    }


}
