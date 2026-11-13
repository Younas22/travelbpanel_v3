<?php
namespace App\Http\Controllers;

use App\Models\FlightBooking;
use App\Models\HotelBooking;
use App\Models\HotelBookingDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\TravelPartner;
use App\Models\Hotel;
use App\Models\Location;
use App\Models\PaymentGateways;
use App\Models\AgentWallet;
use App\Models\Setting;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HotelController extends Controller
{


    /**
     * Show the home page (hotel search section).
     */
    public function index()
    {
        // featured hotels only while the hotel Manual supplier is active
        $showFeatured = TravelPartner::featuredEnabled('hotel');
        $featuredHotels = $showFeatured
            ? Hotel::with(['images', 'location', 'amenities'])
                ->where('status', 1)
                ->where('featured', '1')
                ->limit(9)
                ->get()
            : collect();

        $hotel_search = session('hotel_search');

        return view('hotel.hotel', compact('featuredHotels', 'hotel_search', 'showFeatured'));
    }

    /**
     * Search and display list of hotels based on criteria.
     *
     * @param string $destination
     * @param string $checkin
     * @param string $checkout
     * @param int $adult
     * @param int $child
     * @param int $room
     * @param string $nationality
     * @return \Illuminate\View\View
     */
    public function search(Request $request, $destination, $checkin, $checkout, $adult, $child, $room, $nationality)
    {
        try {

            $suppliers = TravelPartner::where('status', 'active')->where('supplier_type', 'hotel')->get();

            $currency = activeCurrency();

            // The stay form POSTs the freshly-picked ages to /hotels/store-child-ages
            // right before navigating here, so they normally arrive via this one-time
            // session flag. But if that request never lands (e.g. the browser fires a
            // duplicate/prefetch navigation to this same search URL, or the fetch is
            // interrupted by the page unload) and the child count hasn't changed, fall
            // back to whatever ages are already on file instead of wiping them out.
            $childAgesInput = session()->pull('pending_child_ages', []);
            if (empty($childAgesInput) && (int) $child > 0) {
                $childAgesInput = session('hotel_search.child_age', []);
            }

            $payload = [
                'city' => $destination,
                'checkin' => date('Y-m-d', strtotime($checkin)),
                'checkout' => date('Y-m-d', strtotime($checkout)),
                'adults' => $adult,
                'childs' => $child,
                'child_age' => $this->sanitize_child_ages($childAgesInput, (int) $child),
                'rooms' => $room,
                'nationality' => $nationality,
                'currency' => $currency->currency_name,
                'env' => 'dev',
            ];

            // Prepare session data
            $session_data = $this->prepare_session_data($payload);
            session(['hotel_search' => $session_data]);

            // Make parallel requests to all API suppliers
            $all_hotels = [];

            foreach ($suppliers as $supplier) {
                $endpoint = url('/') . '/api/'.$supplier->company_name.'/hotel_search';

                $supplier_payload = array_merge($payload, [
                    'endpoint' => $endpoint,
                    'env' => $supplier->apiEnv(),
                    'api_credential_1' => $supplier->api_credential_1,
                    'api_credential_2' => $supplier->api_credential_2,
                    'api_credential_3' => $supplier->api_credential_3,
                    'api_credential_4' => $supplier->api_credential_4,
                    'api_credential_5' => $supplier->api_credential_5,
                    'api_credential_6' => $supplier->api_credential_6,
                    'commission'=> $supplier->commission_rate,
                ]);

                $response = Http::post($endpoint, $supplier_payload);

                $data = $response->json();

                if (!empty($data) && isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $hotel) {
                        $all_hotels[] = [
                            'hotel_id' => $hotel['hotel_id'],
                            'images' => $hotel['images'],
                            'name' => $hotel['name'],
                            'location' => $hotel['location'],
                            'address' => $hotel['address'],
                            'stars' => $hotel['stars'],
                            'latitude' => $hotel['latitude'],
                            'longitude' => $hotel['longitude'],
                            'currency' =>  $currency->currency_name,
                            'supplier_name' => $hotel['supplier_name'],
                            'redirect' => $hotel['redirect'],
                            'room_name' => $hotel['room_name'],
                            'minRate' => $hotel['minRate'],
                            'categoryCode' => $hotel['categoryCode'],
                            'categoryName' => $hotel['categoryName'],
                        ];
                    }
                }
            }

            // Fetch manual hotels if a manual partner is active for the hotel module
            $manualPartner = TravelPartner::where('status', 'active')
                ->where('supplier_type', 'manual')
                ->whereHas('module', fn($q) => $q->where('slug', 'hotel'))
                ->first();

            if ($manualPartner) {
                $location = Location::where('status', '1')
                    ->where(function ($q) use ($destination) {
                        $q->where('city', 'LIKE', '%' . $destination . '%')
                          ->orWhere('country', 'LIKE', '%' . $destination . '%');
                    })
                    ->first();

                if ($location) {
                    $manualHotels = Hotel::where('location_id', $location->id)
                        ->where('status', 1)
                        ->with([
                            'images'    => fn($q) => $q->orderBy('sort_order')->limit(1),
                            'roomTypes' => fn($q) => $q->where('status', 1)->orderBy('price_per_night')->with(['rates' => fn($r) => $r->whereBetween('date', [$payload['checkin'], $payload['checkout']])]),
                            'amenities',
                        ])
                        ->withCount('images')
                        ->get();

                    foreach ($manualHotels as $hotel) {
                        if ($hotel->roomTypes->isEmpty()) {
                            continue;
                        }

                        // Cheapest stay total (all nights × rooms) across room types and both rate plans,
                        // like supplier rows — the list view divides minRate by the nights.
                        $cheapest = $hotel->roomTypes
                            ->map(fn($rt) => ['rt' => $rt, 'total' => min(array_filter(array_slice($this->manualStayPrices($rt, $payload['checkin'], $payload['checkout'], (int) $room), 0, 2), fn($v) => $v !== null))])
                            ->sortBy('total')
                            ->first();
                        $minRate      = $cheapest['total'];
                        $firstImage   = $hotel->images->first();
                        $imageUrl     = $firstImage ? asset('public/assets/images/' . $firstImage->image_path) : '';
                        $firstRoom    = $cheapest['rt'];

                        $all_hotels[] = [
                            'hotel_id'      => $hotel->id,
                            'images'        => $imageUrl,
                            'name'          => $hotel->name,
                            'location'      => $location->city,
                            'address'       => $hotel->address,
                            'stars'         => $hotel->stars ?? 0,
                            'latitude'      => $location->latitude ?? '',
                            'longitude'     => $location->longitude ?? '',
                            'currency'      => $currency->currency_name,
                            'supplier_name' => 'Manual',
                            'redirect'      => null,
                            'room_name'     => $firstRoom ? $firstRoom->name : '',
                            'minRate'       => $minRate,
                            'all_images'    => array_fill(0, (int) $hotel->images_count, $imageUrl),
                            'categoryCode'  => 'MANUAL',
                            'categoryName'  => ucfirst((string) $hotel->type),
                            'amenities'     => $hotel->amenities->map(fn($a) => [
                                'name' => $a->name,
                                'icon' => $a->icon ?? '',
                            ])->toArray(),
                        ];
                    }
                }
            }

            if (!empty($all_hotels)) {
                // Each aggregated row only carries 'minRate' (see the normalisation
                // above) — 'actual_price' doesn't exist on any of them, so sorting
                // by it was a no-op (every row ties on null) and results stayed in
                // whatever order suppliers happened to respond in.
                $sorted_hotels = collect($all_hotels)
                    ->sortBy('minRate')
                    ->values()
                    ->toArray();

                // echo"<pre>"; print_r($sorted_hotels); exit;

                return view('hotel.list', [
                    'status' => true,
                    'message' => 'Hotels found',
                    'hotels' => $sorted_hotels,
                    'hotel_search' => session('hotel_search'),
                ]);
            } else {
                return view('hotel.list', [
                    'status' => false,
                    'message' => 'No hotels found',
                    'hotels' => [],
                    'hotel_search' => session('hotel_search'),
                ]);
            }
        } catch (\Exception $exception) {
            return view('hotel.list', [
                'error' => 'An error occurred while searching for hotels. Please try again.',
                'debug_error' => $exception->getMessage(),
                'hotel_search' => session('hotel_search'),
            ]);
        }
    }

    /**
     * Display details of a single hotel.
     *
     * @param int $hotel_id
     * @param string $hotel_name
     * @param string $checkin
     * @param string $checkout
     * @param int $adults
     * @param int $childs
     * @param int $rooms
     * @param string $supplier_name
     * @return \Illuminate\View\View
     */
    public function details($hotel_id, $hotel_name, $checkin, $checkout, $adults, $childs, $rooms, $supplier_name)
    {
        try {
            // Handle manual hotel supplier from local database
            if (strtolower($supplier_name) === 'manual') {
                $hotel = Hotel::with([
                    'images'    => fn($q) => $q->orderBy('sort_order'),
                    'roomTypes' => fn($q) => $q->where('status', 1)->with(['images', 'amenities']),
                    'amenities',
                    'location',
                ])->find($hotel_id);
                $hotel?->roomTypes->load(['rates' => fn($r) => $r->whereBetween('date', [date('Y-m-d', strtotime($checkin)), date('Y-m-d', strtotime($checkout))])]);

                if (!$hotel) {
                    return view('hotel.details', ['error' => 'Hotel not found.']);
                }

                $currency     = activeCurrency();
                $checkinDate  = date('Y-m-d', strtotime($checkin));
                $checkoutDate = date('Y-m-d', strtotime($checkout));
                $nights       = max(1, (int) ((strtotime($checkoutDate) - strtotime($checkinDate)) / 86400));

                $hotelImages = $hotel->images
                    ->map(fn($img) => asset('public/assets/images/' . $img->image_path))
                    ->toArray();

                $roomsData = $hotel->roomTypes->map(function ($roomType) use ($hotel, $adults, $childs, $rooms, $nights, $currency, $checkinDate, $checkoutDate) {
                    $roomImages = $roomType->images
                        ->map(fn($img) => asset('public/assets/images/' . $img->image_path))
                        ->toArray();
                    $amenities  = $roomType->amenities->pluck('name')->toArray();

                    // Stay totals from the rate calendar (refundable / non-refundable plans)
                    [$refPrice, $nonRefPrice, $nonRefFromCalendar] = $this->manualStayPrices($roomType, $checkinDate, $checkoutDate, (int) $rooms);
                    $perNight = fn($total) => round($total / $nights / max(1, (int) $rooms), 2);

                    // Room type policy → refundable plan; the non-refundable price → its own plan
                    $plans = [];
                    if ($roomType->isRefundable()) {
                        $plans[] = ['refundable', $refPrice];
                        if ($nonRefPrice !== null) {
                            $plans[] = ['non_refundable', $nonRefPrice];
                        }
                    } else {
                        $plans[] = ['non_refundable', $nonRefPrice];
                    }

                    $options = array_map(function ($plan) use ($roomType, $adults, $childs, $currency, $checkinDate, $perNight, $nonRefFromCalendar) {
                        [$kind, $price] = $plan;
                        $fee          = $roomType->cancellationFeeFor($price);
                        $freeUntil    = $kind === 'refundable' ? $roomType->freeCancellationUntil($checkinDate) : null;
                        $isRefundable = $freeUntil !== null && $freeUntil->isFuture();
                        $refundDate   = $isRefundable ? $freeUntil->format('Y-m-d H:i:s') : null;
                        // Non-refundable charge: the calendar's non-refundable price when set for these dates,
                        // otherwise the room type's cancellation fee (percent / fixed)
                        if ($kind === 'non_refundable' && $nonRefFromCalendar > 0) {
                            $fee = round(min($nonRefFromCalendar, $price), 2);
                        }
                        $cancelRules  = $isRefundable
                            ? [['type' => 'free', 'from_date' => null, 'to_date' => $refundDate, 'cancel_charge' => 0, 'amend_charge' => null, 'description' => 'Free cancellation', 'cancel_restricted' => false, 'amend_restricted' => false],
                               ['type' => $fee > 0 ? 'penalty' : 'free', 'from_date' => $refundDate, 'to_date' => null, 'cancel_charge' => $fee, 'amend_charge' => null, 'description' => 'Cancellation charge', 'cancel_restricted' => false, 'amend_restricted' => false]]
                            : [['type' => $fee > 0 ? 'penalty' : 'free', 'from_date' => null, 'to_date' => null, 'cancel_charge' => $fee, 'amend_charge' => null, 'description' => $fee > 0 ? 'Non-refundable — cancellation charge: ' . number_format($fee, 2) . ' ' . $currency->currency_name : 'Non-refundable', 'cancel_restricted' => false, 'amend_restricted' => false]];

                        return [
                            'id'                  => 'RT-' . $roomType->id . '-' . $kind . '-' . $adults . '-' . $childs,
                            'rate_basis_id'       => (string) $roomType->id,
                            'rate_plan'           => $kind,
                            'description'         => $kind === 'refundable' ? 'Refundable rate' : 'Non-refundable rate',
                            'allocation_details'  => '',
                            'adults'              => (int) $adults,
                            'child'               => (int) $childs,
                            'children_ages'       => session('hotel_search.child_age', []),
                            'price'               => $price,
                            'actual_price'        => $price,
                            'per_day'             => $perNight($price),
                            'actual_per_day'      => $perNight($price),
                            'currency_id'         => $currency->currency_name,
                            'passengers_required' => (int) $adults + (int) $childs,
                            // Cancellation policy from the room type, in the same shape as WebBeds
                            'is_refundable'       => $isRefundable,
                            'non_refundable'      => !$isRefundable,
                            'refundable'          => $fee,
                            'refund_date'         => $refundDate,
                            'cancellation_rules'  => $cancelRules,
                            'meal_included'       => false,
                            'left_to_sell'        => 0,
                            'on_request'          => 0,
                            'min_stay'            => '',
                            'date_apply_min_stay' => '',
                            'tariff_notes'        => '',
                            'taxes_fees'          => [],
                            'taxes_included'      => [],
                            'taxes_at_property'   => [],
                            'changed_occupancy'   => false,
                            'cancel_restricted'      => false,
                            'amend_restricted'       => false,
                            'cancel_restricted_note' => null,
                            'specials'            => [],
                        ];
                    }, $plans);

                    $first = $options[0];

                    return [
                        'id'        => (string) $roomType->id,
                        'name'      => $roomType->name,
                        'images'    => $roomImages,
                        'amenities' => $amenities,
                        'currency'  => $currency->currency_name,
                        "price"       => $first['price'],
                        "actual_price"  => $first['price'],
                        "per_day"  => $first['per_day'],
                        "actual_per_day"  => $first['per_day'],
                        "original_currency" =>  $currency->currency_name,
                        "refundable"  =>  $first['refundable'],
                        "refund_date" => $first['refund_date'],
                        'options'   => $options,
                        // Same role as a supplier's room_data: what the booking needs to identify the stay
                        "room_data" => [
                            'supplier_name'  => 'manual',
                            'product_id'     => (string) $hotel->id,
                            'hotel_id'       => (string) $hotel->id,
                            'room_type_id'   => (string) $roomType->id,
                            'room_type_code' => (string) $roomType->id,
                            'checkin'        => $checkinDate,
                            'checkout'       => $checkoutDate,
                            'nights'         => $nights,
                            'adults'         => (int) $adults,
                            'childs'         => (int) $childs,
                            'children'       => session('hotel_search.child_age', []),
                            'rooms'          => (int) $rooms,
                            'currency'       => $currency->currency_name,
                        ],
                    ];
                })->values()->toArray();

                $details = [[
                    'h_name'     => $hotel->name,
                    'agent_id'     => $hotel->agent_id,
                    'address'    => $hotel->address ?? '',
                    'stars'      => ($hotel->stars ?? 0),
                    'rating'     => $hotel->total_rating ?? 0,
                    'desc'       => $hotel->description ?? '',
                    'imgs'       => $hotelImages ?: ['https://placehold.co/800x400'],
                    'refundable' => collect($roomsData)->flatMap(fn($r) => $r['options'])->contains('is_refundable', true),
                    'rooms'      => $roomsData,
                    'amenities'  => $hotel->amenities->pluck('name')->toArray(),
                    'lat'        => $hotel->location->latitude ?? '',
                    'lng'        => $hotel->location->longitude ?? '',
                    'checkin'    => $checkinDate,
                    'checkout'   => $checkoutDate,
                    'city'       => $hotel->location->city ?? '',
                    'country'    => $hotel->location->country ?? '',
                    'supplier_name' => strtolower($supplier_name),
                ]];

                return view('hotel.details', [
                    'details'      => $details,
                    'hotel_search' => session('hotel_search'),
                ]);
            }

            $supplier = TravelPartner::where('status', 'active')->where('company_name', $supplier_name)->first();
            $endpoint = url('/') . '/api/'.strtolower($supplier_name).'/hotel_details';
            $currency = activeCurrency();
            // Use the real ages captured on the search form (stored in the
            // hotel_search session during search()) instead of a hardcoded value.
            $childAge = session('hotel_search.child_age', []);
            $payload = [
                'endpoint' => $endpoint,
                'hotel_id' => $hotel_id,
                'checkin' => date('Y-m-d', strtotime($checkin)),
                'checkout' => date('Y-m-d', strtotime($checkout)),
                'adults' => $adults,
                'childs' => $childs,
                'child_age' => $childAge,
                'rooms' => $rooms,
                'currency' => $currency->currency_name,
                'api_credential_1' => $supplier->api_credential_1,
                'api_credential_2' => $supplier->api_credential_2,
                'api_credential_3' => $supplier->api_credential_3,
                'api_credential_4' => $supplier->api_credential_4,
                'api_credential_5' => $supplier->api_credential_5,
                'api_credential_6' => $supplier->api_credential_6,
                'commission'=> $supplier->commission_rate,
                'env' => $supplier->apiEnv(),
                'supplier_name' => $supplier_name,
            ];

            $response = Http::post($endpoint, $payload);
            $data = $response->json();

            if (isset($data['success']) && $data['success'] === false) {
                $message = $data['message'] ?? 'No hotel found for selected criteria.';

                return view('hotel.details', [
                    'error' => $message,
                ]);
            }

            return view('hotel.details', [
                'details' => $data['response'] ?? [],
                'hotel_search' => session('hotel_search'),
            ]);
        } catch (\Exception $exception) {
            return view('hotel.details', [
                'error' => 'An error occurred while retrieving hotel details. Please try again.',
            ]);
        }
    }

    /**
     * Start (or resume) the hotel checkout.
     *
     * POST (from "Book Now" on the hotel details page): decrypt the selected
     * room, save it as a checkout draft and redirect to that draft's page, so a
     * refresh never re-posts the form and the booking can be resumed later.
     * Picking the same room again reuses the open draft instead of creating a
     * duplicate. GET without a token: reopen this visitor's latest open draft.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function hotel_booking(Request $request)
    {
        if ($request->isMethod('get')) {
            $draft = HotelBookingDraft::active()->ownedBy($request)->latest('updated_at')->first();

            return $draft
                ? redirect()->route('hotel.checkout', $draft->token)
                : redirect(url('/'));
        }

        try {
            $room = json_decode(decrypt($request->input('room')), true);
            $room_data = json_decode(decrypt($request->input('room_data')), true);
            $booking_option = json_decode(decrypt($request->input('option')), true);
            $booking_data = json_decode(decrypt($request->input('booking_data')), true);
        } catch (\Exception $exception) {
            return abort(400, 'Invalid booking data provided.');
        }

        $hotel_search = session('hotel_search') ?? [];
        $fingerprint = hash('sha256', json_encode([$room_data, $booking_option['id'] ?? null, $booking_option['price'] ?? null, $booking_data]));

        $guestKey = (string) $request->cookie(HotelBookingDraft::GUEST_COOKIE);
        if (!auth()->check() && $guestKey === '') {
            $guestKey = Str::random(48);
        }
        if ($guestKey !== '') {
            Cookie::queue(HotelBookingDraft::GUEST_COOKIE, $guestKey, 60 * 24 * 30);
            $request->cookies->set(HotelBookingDraft::GUEST_COOKIE, $guestKey);
        }

        $draft = HotelBookingDraft::active()->ownedBy($request)->where('fingerprint', $fingerprint)->first();
        if (!$draft) {
            $draft = HotelBookingDraft::create([
                'token'           => Str::random(64),
                'user_id'         => auth()->id(),
                'guest_key'       => auth()->check() ? null : $guestKey,
                'fingerprint'     => $fingerprint,
                'supplier_name'   => $booking_data['supplier_name'] ?? ($room_data['supplier_name'] ?? null),
                'hotel_id'        => $room_data['hotel_id'] ?? ($room_data['product_id'] ?? null),
                'room_id'         => $room['id'] ?? null,
                'checkin'         => $booking_data['checkin'] ?: null,
                'checkout'        => $booking_data['checkout'] ?: null,
                'adults'          => (int) ($hotel_search['adults'] ?? $booking_data['adults'] ?? 0),
                'children'        => (int) ($hotel_search['childs'] ?? $booking_data['child'] ?? 0),
                'rooms'           => (int) ($hotel_search['rooms'] ?? $room_data['rooms'] ?? 1),
                'payload'         => [
                    'room'                 => $room,
                    'room_data'            => $room_data,
                    'option'               => $booking_option,
                    'booking_data'         => $booking_data,
                    'hotel_search'         => $hotel_search,
                    'fresh_taxes_and_fees' => $this->freshTaxesAndFees($room_data),
                ],
                'guest_data'      => $this->profileContact(),
                'travellers'      => [],
                'completed_steps' => [],
                'current_step'    => 1,
                'status'          => 'draft',
                'expires_at'      => now()->addHours(HotelBookingDraft::LIFETIME_HOURS),
            ]);
        }

        return redirect()->route('hotel.checkout', $draft->token);
    }

    /**
     * The three-step checkout page for a saved draft. Renders every step with
     * the saved values and opens the step the customer stopped on.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request, string $token)
    {
        $draft = $this->ownedDraft($request, $token);
        if ($draft instanceof \Illuminate\Http\RedirectResponse) {
            return $draft;
        }

        $payload = $draft->payload;
        $countries = Location::select('country', 'country_code')->distinct()->orderBy('country', 'asc')->get();

        return view('hotel.booking', [
            'draft' => $draft,
            'room' => json_decode(json_encode($payload['room'])),
            'room_data' => json_decode(json_encode($payload['room_data'])),
            'booking_option' => $payload['option'],
            'booking_data' => $payload['booking_data'],
            'payment' => PaymentGateways::all()->where('status', '1'),
            'countries' => $countries,
            'hotel_search' => $payload['hotel_search'],
            'agentWallet' => auth()->check() && auth()->user()->isAgent() ? auth()->user()->wallet : null,
            'fresh_taxes_and_fees' => $payload['fresh_taxes_and_fees'] ?? null,
            'profile' => auth()->check() ? $this->profileContact() : null,
            'profileAvatar' => $this->profileAvatar(),
        ]);
    }

    /**
     * Save checkout progress (AJAX). `intent`:
     *  - autosave: store what was typed so far, no validation
     *  - continue: validate the step, mark it completed and open the next one
     *  - goto:     store what was typed and open an earlier/allowed step
     *
     * @param \Illuminate\Http\Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveCheckoutStep(Request $request, string $token)
    {
        $draft = HotelBookingDraft::where('token', $token)->first();
        if (!$draft || !$draft->isOwnedBy($request)) {
            return response()->json(['message' => t('hotelbooking.draft_expired')], 404);
        }
        if ($draft->status === 'completed') {
            return response()->json(['redirect' => $this->completedDraftUrl($draft)], 409);
        }
        if ($draft->isExpired()) {
            return response()->json(['message' => t('hotelbooking.draft_expired')], 410);
        }

        $step = (int) $request->input('step');
        $intent = (string) $request->input('intent');
        if (!in_array($step, [1, 2, 3], true) || !in_array($intent, ['autosave', 'continue', 'goto'], true)) {
            return response()->json(['message' => 'Invalid request.'], 422);
        }

        $data = (array) $request->input('data', []);
        $this->storeStepData($draft, $step, $data);

        $completed = $draft->completed_steps ?? [];

        if ($intent === 'continue') {
            $errors = $this->checkoutStepErrors($draft, $step);
            if ($errors) {
                $draft->save();
                return response()->json(['errors' => $errors], 422);
            }
            $completed = array_values(array_unique(array_merge($completed, [$step])));
            sort($completed);
            $draft->completed_steps = $completed;
            $draft->current_step = min(3, $step + 1);
        } elseif ($intent === 'goto') {
            $target = (int) $request->input('target');
            if ($target >= 1 && $target <= $draft->maxAllowedStep()) {
                $draft->current_step = $target;
            }
        }

        $draft->save();

        return response()->json([
            'current_step'    => $draft->current_step,
            'completed_steps' => $draft->completed_steps ?? [],
            'max_step'        => $draft->maxAllowedStep(),
            'saved_at'        => $draft->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Save hotel booking to database.
     *
     * With a checkout draft (the normal path) the room, price and traveller
     * details come from the server-side draft — never from the browser — and
     * every step is validated again. The draft row is locked while the booking
     * is created, so a double click or a resent form cannot book twice.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function booking(Request $request)
    {
        if ($request->filled('draft')) {
            return $this->bookingFromDraft($request);
        }

            $room = json_decode(decrypt($request->input('room')), true);
            $room_data = json_decode(decrypt($request->input('room_data')), true);
            $option = json_decode(decrypt($request->input('option')), true);
            $booking_data = json_decode(decrypt($request->input('booking_data')), true);
            $user = $request->input('user');
            $payment = $request->input('accept_payment');

            $guestData = $this->buildGuestData($request);

            $booking = $this->createHotelBooking($room, $room_data, $option, $booking_data, $user, $guestData, $payment);
            if ($booking instanceof \Illuminate\Http\RedirectResponse) {
                return $booking;
            }

            if ($booking) {
                    $invoice_url = route('hotel.invoice', ['booking_ref' => $booking->booking_code_ref]);

                return redirect($invoice_url);
            }

            return back()->withError('Failed to create booking. Please try again.');
    }

    /**
     * Final step of the checkout draft: validate everything again, create the
     * booking once and close the draft.
     */
    private function bookingFromDraft(Request $request)
    {
        $token = (string) $request->input('draft');

        return DB::transaction(function () use ($request, $token) {
            $draft = HotelBookingDraft::where('token', $token)->lockForUpdate()->first();
            if (!$draft || !$draft->isOwnedBy($request)) {
                return redirect(url('/'))->with('error', t('hotelbooking.draft_expired'));
            }
            // Already booked (double click / resent form): show that booking
            if ($draft->status === 'completed') {
                return redirect($this->completedDraftUrl($draft));
            }
            if ($draft->isExpired()) {
                return redirect(url('/'))->with('error', t('hotelbooking.draft_expired'));
            }

            $this->storeStepData($draft, 3, ['payment' => $request->input('accept_payment')]);

            foreach ([1, 2, 3] as $step) {
                if ($this->checkoutStepErrors($draft, $step)) {
                    $draft->completed_steps = array_values(array_filter($draft->completed_steps ?? [], fn($s) => $s < $step));
                    $draft->current_step = $step;
                    $draft->save();
                    return redirect()->route('hotel.checkout', $draft->token)->with('error', t('hotelbooking.check_details'));
                }
            }

            $payload = $draft->payload;
            $option = $payload['option'];

            // Taxes/fees can change after the search: refresh them for WebBeds
            // (non-fatal, like on the checkout page) and keep the latest figures.
            if (($fresh = $this->freshTaxesAndFees($payload['room_data'])) !== null) {
                $payload['fresh_taxes_and_fees'] = $fresh;
                $draft->payload = $payload;
            }

            $guestData = $this->draftGuestData($draft);
            $booking = $this->createHotelBooking($payload['room'], $payload['room_data'], $option, $payload['booking_data'], $draft->guest_data, $guestData, $draft->payment_method);

            if ($booking instanceof \Illuminate\Http\RedirectResponse) {
                $draft->save();
                return redirect()->route('hotel.checkout', $draft->token)->with('error', session('error'));
            }
            if (!$booking) {
                $draft->save();
                return redirect()->route('hotel.checkout', $draft->token)->with('error', 'Failed to create booking. Please try again.');
            }

            $draft->completed_steps = [1, 2, 3];
            $draft->status = 'completed';
            $draft->hotel_booking_id = $booking->id;
            $draft->save();

            return redirect(route('hotel.invoice', ['booking_ref' => $booking->booking_code_ref]));
        });
    }

    /**
     * Create the hotels_booking row (and debit the agent wallet when that is the
     * payment method). Returns a redirect back with an error when the wallet
     * cannot pay.
     */
    private function createHotelBooking(array $room, array $room_data, array $option, array $booking_data, $user, array $guestData, $payment)
    {
            $bookingdata = [
                'room_data' =>$room_data,
                'room' => $room,
                'option' => $option,
                'booking_data' => $booking_data,
            ];

            $userId    = null;
            $agentId   = null;
            $bookedVia = 'guest';
            if (auth()->check()) {
                $authUser = auth()->user();
                if ($authUser->isAgent()) {
                    $agentId   = $authUser->id;
                    $bookedVia = 'agent';
                } elseif ($authUser->isCustomer()) {
                    $userId    = $authUser->id;
                    $bookedVia = 'user';
                }
            }

            // Wallet payment validation
            $agentWallet = null;
            if ($payment === 'agent_wallet') {
                if (!$agentId) {
                    return back()->withInput()->with('error', 'Wallet payment is only available for agents.');
                }
                $bookingAmount = (float) ($option['price'] ?? 0);
                $agentWallet   = AgentWallet::where('agent_id', $agentId)->first();
                if (!$agentWallet || !$agentWallet->hasSufficientBalance($bookingAmount)) {
                    $available = $agentWallet ? number_format($agentWallet->balance, 2) : '0.00';
                    return back()->withInput()->with('error', "Insufficient wallet balance. Available: PKR {$available}. Please top up your wallet first.");
                }
            }

            $booking_payload = [
                'booking_code_ref' => date('YmdHis'),
                'agent_id' => $agentId,
                'user_id' => $userId,
                'booked_via' => $bookedVia,
                'booking_status_flag' => 'pending',
                'booking_fare_base' => $option['price'] ?? 0,
                'booking_adult_count' => $booking_data['adults'] ?? 0,
                'booking_child_count' => $booking_data['child'] ?? 0,
                'booking_currency_origin' => 'USD',
                'booking_payment_state' => 'unpaid',
                'booking_data' => ($bookingdata),
                'booking_supplier_name' => $booking_data['supplier_name'],
                'booking_user_data' => ($user),
                'booking_guest' => ($guestData),
                'booking_nationality_code' => $user['country'] ?? null,
                'booking_payment_gateway' => $payment,
            ];

            $booking = HotelBooking::create($booking_payload);

            if ($booking) {
                // Debit agent wallet if selected
                if ($payment === 'agent_wallet' && $agentId) {
                    $agentWallet = $agentWallet ?? AgentWallet::where('agent_id', $agentId)->first();
                    $agentWallet->debit(
                        amount:      (float) ($option['price'] ?? 0),
                        note:        'Hotel booking #' . $booking->booking_code_ref,
                        performedBy: $agentId,
                        bookingType: 'hotel',
                        bookingId:   $booking->id,
                        reference:   $booking->booking_code_ref,
                    );
                    $booking->update(['booking_payment_state' => 'paid']);
                }
            }

            return $booking;
    }

    /**
     * Load a draft for the checkout page, or a redirect when it can't be shown:
     * unknown/foreign token, expired, or already booked (→ its invoice).
     */
    private function ownedDraft(Request $request, string $token)
    {
        $draft = HotelBookingDraft::where('token', $token)->first();

        // A guest draft opened after signing in on the same browser becomes the user's
        if ($draft && !$draft->user_id && auth()->check()) {
            $guestKey = (string) $request->cookie(HotelBookingDraft::GUEST_COOKIE);
            if ($guestKey !== '' && $draft->guest_key && hash_equals($draft->guest_key, $guestKey)) {
                $draft->user_id = auth()->id();
                $draft->save();
            }
        }

        if (!$draft || !$draft->isOwnedBy($request)) {
            return redirect(url('/'))->with('error', t('hotelbooking.draft_expired'));
        }
        if ($draft->status === 'completed') {
            return redirect($this->completedDraftUrl($draft));
        }
        if ($draft->isExpired()) {
            return redirect(url('/'))->with('error', t('hotelbooking.draft_expired'));
        }

        return $draft;
    }

    private function completedDraftUrl(HotelBookingDraft $draft): string
    {
        $ref = HotelBooking::whereKey($draft->hotel_booking_id)->value('booking_code_ref');

        return $ref ? route('hotel.invoice', ['booking_ref' => $ref]) : url('/');
    }

    /**
     * Copy the submitted values of one step onto the draft (trimmed, length
     * limited, only the known fields). Validation happens separately.
     */
    private function storeStepData(HotelBookingDraft $draft, int $step, array $data): void
    {
        $clean = fn($v, $max = 100) => mb_substr(trim((string) (is_scalar($v) ? $v : '')), 0, $max);

        if ($step === 1) {
            $guest = $draft->guest_data ?? [];
            foreach (['first_name', 'last_name', 'email', 'phone', 'country'] as $field) {
                if (array_key_exists($field, $data)) {
                    $guest[$field] = $clean($data[$field]);
                }
            }
            // a signed-in customer always books with their account email
            if (auth()->check()) {
                $guest['email'] = (string) auth()->user()->email;
            }
            $draft->guest_data = $guest;
        } elseif ($step === 2) {
            $travellers = [];
            $submitted = array_values((array) ($data['travellers'] ?? []));
            for ($i = 0; $i < $draft->adults + $draft->children; $i++) {
                $row = (array) ($submitted[$i] ?? []);
                $travellers[] = [
                    'gender'     => $clean($row['gender'] ?? '', 10),
                    'first_name' => $clean($row['first_name'] ?? '', 50),
                    'last_name'  => $clean($row['last_name'] ?? '', 50),
                ];
            }
            $draft->travellers = $travellers;
        } elseif ($step === 3 && array_key_exists('payment', $data)) {
            $draft->payment_method = $clean($data['payment'], 50) ?: null;
        }
    }

    /**
     * Validation errors for one checkout step, keyed by the form field name the
     * page uses (so they show under the right input). Empty when valid.
     */
    private function checkoutStepErrors(HotelBookingDraft $draft, int $step): array
    {
        $required = t('hotelbooking.field_required');
        $messages = [
            'required' => $required,
            'email'    => t('hotelbooking.invalid_email'),
            'regex'    => t('hotelbooking.invalid_phone'),
            'in'       => t('hotelbooking.invalid_option'),
            'max'      => t('hotelbooking.invalid_option'),
        ];

        if ($step === 1) {
            $countries = Location::select('country_code')->distinct()->pluck('country_code')->map(fn($c) => strtolower($c))->all();
            $validator = Validator::make($draft->guest_data ?? [], [
                'first_name' => 'required|string|max:50',
                'last_name'  => 'required|string|max:50',
                'email'      => 'required|email|max:100',
                'phone'      => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s\-()]{6,30}$/'],
                'country'    => ['required', Rule::in($countries)],
            ], $messages);

            return collect($validator->errors()->messages())
                ->mapWithKeys(fn($m, $field) => ["user[$field]" => $m[0]])->all();
        }

        if ($step === 2) {
            $errors = [];
            $travellers = $draft->travellers ?? [];
            for ($i = 0; $i < $draft->adults + $draft->children; $i++) {
                $row = $travellers[$i] ?? [];
                $validator = Validator::make($row, [
                    'gender'     => 'required|in:male,female,other',
                    'first_name' => 'required|string|max:50',
                    'last_name'  => 'required|string|max:50',
                ], $messages);
                foreach ($validator->errors()->messages() as $field => $m) {
                    $errors["travellers[$i][$field]"] = $m[0];
                }
            }

            return $errors;
        }

        $allowed = PaymentGateways::where('status', '1')->pluck('name')->all();
        if (auth()->check() && auth()->user()->isAgent()) {
            $allowed[] = 'agent_wallet';
        }

        return in_array($draft->payment_method, $allowed, true) ? [] : ['accept_payment' => t('hotelbooking.select_payment')];
    }

    /**
     * Travellers of a draft in the same shape buildGuestData() produces from the
     * old single-page form (adults first, then children), so suppliers receive
     * exactly what they did before.
     */
    private function draftGuestData(HotelBookingDraft $draft): array
    {
        $guestData = [];
        foreach (array_values($draft->travellers ?? []) as $i => $traveller) {
            $guestData[] = $i < $draft->adults
                ? (object) ['traveller_type' => 'adults', 'title' => $traveller['gender'], 'first_name' => $traveller['first_name'], 'last_name' => $traveller['last_name']]
                : (object) ['traveller_type' => 'child', 'first_name' => $traveller['first_name'], 'last_name' => $traveller['last_name']];
        }

        return $guestData;
    }

    /**
     * Booking contact taken from the signed-in user's profile (empty for
     * guests). Country is matched to the country codes the form offers.
     */
    private function profileContact(): array
    {
        if (!auth()->check()) {
            return [];
        }

        $user = auth()->user();
        $country = strtolower(trim((string) $user->country));
        if ($country !== '' && strlen($country) > 2) {
            $country = strtolower((string) Location::whereRaw('LOWER(country) = ?', [$country])->value('country_code'));
        }

        return [
            'first_name' => (string) $user->first_name,
            'last_name'  => (string) $user->last_name,
            'email'      => (string) $user->email,
            'phone'      => (string) $user->phone,
            'country'    => $country,
        ];
    }

    /**
     * Profile picture of the signed-in user, resolved the same way as the
     * navbar account button (admin / agent / customer paths, agent logo as a
     * fallback). Null when there is none, so the page shows initials.
     */
    private function profileAvatar(): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();
        if ($user->profile_image) {
            return $user->isAdmin()
                ? url('public/' . $user->profile_image)
                : ($user->isAgent() ? $user->profile_image_url : url('public/assets/images/avatars/' . $user->profile_image));
        }

        return $user->isAgent() && $user->company_logo
            ? url('public/assets/images/settings/branding/' . $user->company_logo)
            : null;
    }

    /**
     * Taxes & property fees can change between the initial search and the
     * final booking, so re-validate via GetRooms-With-Blocking (WebBeds only)
     * and use the FRESH figures. Credentials stay server-side. Returns null when
     * the check doesn't apply or fails, so callers fall back to the option's
     * own tax fields.
     */
    private function freshTaxesAndFees(array $room_data): ?array
    {
        if (strtolower($room_data['supplier_name'] ?? '') !== 'webbeds') {
            return null;
        }

        $supplier = TravelPartner::where('status', 'active')
            ->where('company_name', 'webbeds')
            ->first();
        if (!$supplier) {
            return null;
        }

        try {
            $blockResponse = Http::post(url('/') . '/api/webbeds/hotel_getrooms_block', [
                'hotel_id'             => $room_data['product_id']          ?? '',
                'checkin'              => $room_data['checkin']             ?? '',
                'checkout'             => $room_data['checkout']            ?? '',
                'adults'               => $room_data['adults']              ?? 1,
                'child_age'            => $room_data['children']            ?? [],
                'rooms'                => $room_data['rooms']               ?? 1,
                'room_type_code'       => $room_data['room_type_code']      ?? '',
                'rate_basis_id'        => $room_data['selected_rate_basis'] ?? '',
                'allocation_details'   => $room_data['allocation_details']   ?? '',
                'currency'             => $room_data['currency'] ?? activeCurrency()->currency_name,
                'nationality'          => $room_data['nationality']          ?? '',
                'country_of_residence' => $room_data['country_of_residence'] ?? '',
                'api_credential_1'     => $supplier->api_credential_1,
                'api_credential_2'     => $supplier->api_credential_2,
                'api_credential_3'     => $supplier->api_credential_3,
                'env'                  => $supplier->apiEnv(),
            ]);

            $blockData = $blockResponse->json();

            return ($blockData['success'] ?? false) === true ? ($blockData['taxes_and_fees'] ?? null) : null;
        } catch (\Exception $blockException) {
            return null;
        }
    }

    /**
     * Display invoice for a specific booking.
     *
     * @param string $bookingRef
     * @return \Illuminate\View\View
     */
    public function invoice($bookingRef)
    {
        $booking = HotelBooking::where('booking_code_ref', $bookingRef)->first();
        if (!$booking) {
            abort(404, 'Booking not found.');
        }

        // Manual (own catalogue) hotels have real check-in/out times, a type and
        // written policies in the database; supplier hotels only have what the
        // booked rate carries (cancellation rules, meals, taxes).
        $roomData = $booking->booking_data['room_data'] ?? [];
        $hotelRecord = null;
        $hotelPolicies = collect();
        if (strtolower($booking->booking_supplier_name ?? '') === 'manual' && !empty($roomData['hotel_id'])) {
            $hotelRecord = Hotel::with(['policies', 'translations'])->find($roomData['hotel_id']);
            $hotelPolicies = $hotelRecord?->policies->keyBy('policy_type') ?? collect();
        }

        // Pay Now goes to the existing gateway page, which only exists for these
        $gateway = strtolower((string) $booking->booking_payment_gateway);
        $canPay = $booking->booking_status_flag !== 'confirmed'
            && $booking->booking_status_flag !== 'cancelled'
            && $booking->booking_payment_state !== 'paid'
            && $booking->booking_payment_state !== 'refunded'
            && $gateway !== '' && view()->exists("gateways.$gateway");

        return view('hotel.invoice', compact('booking', 'hotelRecord', 'hotelPolicies', 'canPay'));
    }

    /**
     * Email the invoice link to the booking contact (the address entered at
     * checkout, or the signed-in customer's account email), through the same
     * Resend setup the flight invoice uses.
     *
     * @param string $bookingRef
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInvoiceEmail($bookingRef)
    {
        $booking = HotelBooking::where('booking_code_ref', $bookingRef)->first();
        if (!$booking) {
            return response()->json(['success' => false, 'message' => t('hotelinvoice.emailFailed')], 404);
        }

        $contact = (array) $booking->booking_user_data;
        $email = trim((string) ($contact['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => t('hotelinvoice.emailMissing')], 422);
        }

        try {
            config(['services.resend.key' => Setting::getValue('resend_api_key', 'email')]);
            $senderEmail = getSetting('sender_email', 'email', 'noreply@travelbookingpanel.com');
            $senderName = getSetting('sender_name', 'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');

            $html = view('emails.hotel_invoice', [
                'booking'      => $booking,
                'contact'      => $contact,
                'businessName' => $businessName,
                'invoiceUrl'   => route('hotel.invoice', ['booking_ref' => $booking->booking_code_ref]),
            ])->render();

            $result = Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$email],
                'subject' => t('hotelinvoice.emailSubject') . ' - ' . $booking->booking_code_ref,
                'html'    => $html,
            ]);

            \Log::info('Hotel invoice email sent', ['booking' => $booking->booking_code_ref, 'id' => $result['id'] ?? null]);

            return response()->json(['success' => true, 'message' => str_replace(':email', $email, t('hotelinvoice.emailSent'))]);
        } catch (\Throwable $e) {
            \Log::error('Hotel invoice email failed', ['booking' => $booking->booking_code_ref, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => t('hotelinvoice.emailFailed')], 500);
        }
    }

    /**
     * Build guest data array from request inputs.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function buildGuestData(Request $request): array
    {
        $hotel_pessanger = session('hotel_search');
        $guestData = [];

        // Add adult guests
        for ($i = 1; $i <= $hotel_pessanger['adults']; $i++) {
            $guestData[] = (object) [
                'traveller_type' => $request->input("traveller_type_$i"),
                'title' => $request->input("adult_gender_$i"),
                'first_name' => $request->input("adult_first_name_$i"),
                'last_name' => $request->input("adult_last_name_$i"),
            ];
        }

        // Add child guests
        for ($i = 1; $i <= $hotel_pessanger['childs']; $i++) {
            $guestData[] = (object) [
                'traveller_type' => $request->input("traveller_child_$i"),
                'first_name' => $request->input("child_first_name_$i"),
                'last_name' => $request->input("child_last_name_$i"),
            ];
        }

        return $guestData;
    }


        public function hotelbooking($bookingRef)
    {
        return view('hotel.hotelbookingmsg', compact('bookingRef'));
    }


    /**
     * Prepare session data
     *
     * @param array $params
     * @return array
     */
    /**
     * Manual hotel stay totals for a room type from the rate calendar: every night
     * from check-in up to (not including) check-out, times the number of rooms.
     * Expects the room type's rates for the stay loaded on `rates`.
     *
     * Non-refundable room type: its price is the normal price, and the calendar's
     * non-refundable prices are the cancellation charge for those nights.
     * Refundable room type: the optional non-refundable plan is priced per night
     * from the calendar non-refundable price, else the base non-refundable price
     * (not offered when a night has neither).
     *
     * @return array{0: float, 1: float|null, 2: float} [normal total, non-refundable plan total or null,
     *         sum of the calendar non-refundable prices for the stay]
     */
    private function manualStayPrices($roomType, string $checkin, string $checkout, int $rooms = 1): array
    {
        $rates  = $roomType->rates->keyBy(fn($r) => $r->date->toDateString());
        $nights = max(1, (int) ((strtotime($checkout) - strtotime($checkin)) / 86400));
        $ref = $nonRef = $fromCalendar = 0.0;
        $hasNonRef = true;

        for ($i = 0; $i < $nights; $i++) {
            $rate  = $rates->get(date('Y-m-d', strtotime($checkin . " +$i day")));
            $night = (float) ($rate?->price ?? $roomType->price_per_night);
            $ref  += $night;

            if ($rate?->non_refundable_price !== null) {
                $fromCalendar += (float) $rate->non_refundable_price;
            }

            if (!$roomType->isRefundable()) {
                $nonRef += $night;
            } elseif (($n = $rate?->non_refundable_price ?? $roomType->non_refundable_price) !== null) {
                $nonRef += (float) $n;
            } else {
                $hasNonRef = false;
            }
        }

        $rooms = max(1, $rooms);

        return [
            round($ref * $rooms, 2),
            $hasNonRef ? round($nonRef * $rooms, 2) : null,
            round($fromCalendar * $rooms, 2),
        ];
    }

    /**
     * Sanitize the child ages picked on the search form into a clean array
     * of valid ages (2-11), one per child in order — so `child_age[0]` is
     * always child 1's age, `child_age[1]` is child 2's age, and so on,
     * paired positionally with the `childs` count. Accepts either an array
     * (the normal case, read from session) or a legacy comma-separated
     * string, for backwards compatibility. WebbedsController::parse_child_ages()
     * already accepts a plain array, so no further encoding is needed.
     */
    private function sanitize_child_ages($raw, int $childCount): array
    {
        if ($childCount <= 0 || empty($raw)) {
            return [];
        }

        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        return collect($raw)
            ->map(fn($age) => (int) trim((string) $age))
            ->filter(fn($age) => $age >= 2 && $age <= 11)
            ->take($childCount)
            ->values()
            ->all();
    }

    /**
     * Store the child ages picked on the search form into the session
     * instead of passing them through the URL. Called via fetch immediately
     * before the search form navigates to the results page; search() then
     * reads them back out of session (one-time, via session()->pull()).
     */
    public function storeChildAges(Request $request)
    {
        $ages = collect($request->input('child_ages', []))
            ->map(fn($age) => (int) $age)
            ->filter(fn($age) => $age >= 2 && $age <= 11)
            ->values()
            ->all();

        session(['pending_child_ages' => $ages]);

        return response()->json(['success' => true]);
    }

    private function prepare_session_data(array $params): array
    {
        $totalPassengers = $params['adults'] + $params['childs'];

        return [
            'city' => $params['city'],
            'checkin' => $params['checkin'],
            'checkout' => $params['checkout'],
            'adults' => $params['adults'],
            'childs' => $params['childs'],
            'child_age' => $params['child_age'],
            'rooms' => $params['rooms'],
            'nationality' => $params['nationality'] ?? '',
            'currency' => $params['currency'],
            'search_timestamp' => now()->toDateTimeString(),
            'passenger_count' => $totalPassengers,
        ];
    }


    /**
     * Show the payment page for a given booking reference and gateway.
     *
     * @param  string  $getway_name
     * @param  string  $booking_ref
     * @return \Illuminate\View\View
     */
    public function payment($getway_name,$booking_ref)
    {
        $rand =date('Ymdhis').rand();
        session(['bookingkey' => $rand]);
        $booking = DB::table('hotels_booking')->where('booking_code_ref', $booking_ref)->first();
        $payment_gatway = PaymentGateways::where('name', $booking->booking_payment_gateway)->first();
        return view("gateways.$getway_name", get_defined_vars());
    }



    /**
     * Handle the successful payment response.
     *
     * This method is called after the payment is completed successfully.
     * You can use it to update the booking/payment status, clear session values,
     * and redirect the user to a confirmation or invoice page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function payment_success()
    {
        $bookingKey = Session::get('bookingkey');
       if(!empty($bookingKey)){
            $booking =  DB::table('hotels_booking')->where('booking_code_ref', request()->get('token'))->first();
            if($booking->booking_supplier_name == "manual"){
                $params = [
                    "booking_status_flag" => "confirmed",
                    "booking_payment_state" => "paid",
                    "booking_payment_gateway" => request()->get('gateway'),
                    "booking_pnr" => strtoupper(uniqid()),
                ];
                DB::table('hotels_booking')->where('booking_code_ref', request()->get('token'))->update($params);
                $invoice_url = route('hotel.invoice', ['booking_ref' =>  request()->get('token')]);
                return redirect($invoice_url);
            }else {
                $api_cred = TravelPartner::where('status', 'active')->where("company_name",strtolower($booking->booking_supplier_name))->first();
                // Development mode on the supplier → test API + sandbox card, off → live API + saved card
                $env = $api_cred->apiEnv();

                $params = [
                    'api_credential_1' => $api_cred->api_credential_1,
                    'api_credential_2' => $api_cred->api_credential_2,
                    'api_credential_3' => $api_cred->api_credential_3,
                    'api_credential_4' => '',
                    'api_credential_5' => '',
                    'api_credential_6' => '',
                    'env' => $env,
                    'payment_card' => $env === 'pro' ? $api_cred->paymentCard() : $api_cred->sandboxCard(),
                    'booking_data' => $booking->booking_data,
                    'guest' => $booking->booking_guest,
                    'user_data' => $booking->booking_user_data,
                ];

                // Define actual API endpoint
                $endpoint = url('/') . "/api/" . strtolower($booking->booking_supplier_name) . "/hotel_booking";

                try {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                    ])->post($endpoint, $params);

                    $rep = $response->json() ?? [];
                    $confirmed = ($rep['success'] ?? false) === true && !empty($rep['booking_pnr']);

                    // Through the model so the array response is stored as JSON (casts).
                    // booking_pnr / booking_hotel_pnr = the booking code the supplier API returns (WebBeds bookingCode).
                    HotelBooking::where('booking_code_ref', request()->get('token'))->first()?->update([
                        'booking_status_flag'     => $confirmed ? 'confirmed' : 'pending',
                        'booking_payment_state'   => 'paid',
                        'booking_payment_gateway' => request()->get('gateway'),
                        'booking_pnr'             => $rep['booking_pnr'] ?? null,
                        'booking_hotel_pnr'       => $confirmed ? (string) $rep['booking_pnr'] : null,
                        'booking_response_json'   => $rep['response'] ?? $rep,
                        'booking_response_error'  => $confirmed ? null : (string) ($rep['message'] ?? 'The supplier could not confirm this booking.'),
                    ]);
                } catch (\Exception $e) {
                    // Payment went through but the supplier call failed: keep the booking pending with the reason
                    HotelBooking::where('booking_code_ref', request()->get('token'))->first()?->update([
                        'booking_payment_state'   => 'paid',
                        'booking_payment_gateway' => request()->get('gateway'),
                        'booking_response_error'  => 'Supplier booking failed: ' . $e->getMessage(),
                    ]);
                }

                return redirect(route('hotel.invoice', ['booking_ref' => request()->get('token')]));

            }
        }else{
            return abort(404, 'Booking not found');
        }

    }

}
