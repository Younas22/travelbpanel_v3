<?php
namespace App\Http\Controllers;

use App\Models\FlightBooking;
use App\Models\HotelBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\TravelPartner;
use App\Models\Hotel;
use App\Models\Location;
use App\Models\PaymentGateways;
use App\Models\AgentWallet;
use Illuminate\Support\Facades\Session;

class HotelController extends Controller
{


    /**
     * Show the home page (hotel search section).
     */
    public function index()
    {
        $featuredHotels = Hotel::with(['images', 'location', 'amenities'])
            ->where('status', 1)
            ->where('featured', '1')
            ->limit(9)
            ->get();

        return view('hotel.hotel', compact('featuredHotels'));
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
    public function search($destination, $checkin, $checkout, $adult, $child, $room, $nationality)
    {
        try {

            $suppliers = TravelPartner::where('status', 'active')->where('supplier_type', 'hotel')->get();

            $currency = activeCurrency();

            $payload = [
                'city' => $destination,
                'checkin' => date('Y-m-d', strtotime($checkin)),
                'checkout' => date('Y-m-d', strtotime($checkout)),
                'adults' => $adult,
                'childs' => $child,
                'child_age' => '',
                'rooms' => $room,
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
                            'roomTypes' => fn($q) => $q->where('status', 1)->orderBy('price_per_night'),
                            'amenities',
                        ])
                        ->get();

                    foreach ($manualHotels as $hotel) {
                        $minRate      = $hotel->roomTypes->min('price_per_night') ?? 0;
                        $firstImage   = $hotel->images->first();
                        $imageUrl     = $firstImage ? asset('public/assets/images/' . $firstImage->image_path) : '';
                        $firstRoom    = $hotel->roomTypes->first();

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
                            'categoryCode'  => 'MANUAL',
                            'categoryName'  => ucfirst($hotel->type),
                            'amenities'     => $hotel->amenities->map(fn($a) => [
                                'name' => $a->name,
                                'icon' => $a->icon ?? '',
                            ])->toArray(),
                        ];
                    }
                }
            }

            if (!empty($all_hotels)) {
                $sorted_hotels = collect($all_hotels)
                    ->sortBy('actual_price')
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
                ]);
            }
        } catch (\Exception $exception) {
            return view('hotel.list', [
                'error' => 'An error occurred while searching for hotels. Please try again.',
                'debug_error' => $exception->getMessage(),
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

                $roomsData = $hotel->roomTypes->map(function ($roomType) use ($adults, $childs, $nights, $currency) {
                    $roomImages = $roomType->images
                        ->map(fn($img) => asset('public/assets/images/' . $img->image_path))
                        ->toArray();
                    $amenities  = $roomType->amenities->pluck('name')->toArray();
                    $price      = round($roomType->price_per_night * $nights, 2);

                    return [
                        'id'        => (string) $roomType->id,
                        'name'      => $roomType->name,
                        'images'    => $roomImages,
                        'amenities' => $amenities,
                        'currency'  => $currency->currency_name,
                        "price"       => $price,
                        "actual_price"  => $price,
                        "per_day"  => round($roomType->price_per_night, 2),
                        "actual_per_day"  => round($roomType->price_per_night, 2),
                        "original_currency" =>  $currency->currency_name,
                        "refundable"  =>  0,
                        "refund_date" => null,
                        'options'   => [
                            [
                                'id'      => 'RT-' . $roomType->id . '-' . $adults . '-' . $childs,
                                'adults'  => (int) $adults,
                                'child'   => (int) $childs,
                                'price'   => $price,
                                'per_day' => round($roomType->price_per_night, 2),
                            ],
                        ],
                        "room_data" =>[]
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
                    'refundable' => false,
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
            $payload = [
                'endpoint' => $endpoint,
                'hotel_id' => $hotel_id,
                'checkin' => date('Y-m-d', strtotime($checkin)),
                'checkout' => date('Y-m-d', strtotime($checkout)),
                'adults' => $adults,
                'childs' => $childs,
                'child_age' => '[{"ages":"5"}]',
                'rooms' => $rooms,
                'currency' => $currency->currency_name,
                'api_credential_1' => $supplier->api_credential_1,
                'api_credential_2' => $supplier->api_credential_2,
                'api_credential_3' => $supplier->api_credential_3,
                'api_credential_4' => $supplier->api_credential_4,
                'api_credential_5' => $supplier->api_credential_5,
                'api_credential_6' => $supplier->api_credential_6,
                'commission'=> $supplier->commission_rate,
                'env' => "dev",
                'supplier_name' => $supplier_name,
            ];

            $response = Http::post($endpoint, $payload);
            $data = $response->json();

            if (isset($data['status']) && $data['status'] === false) {
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
     * Display hotel booking page with encrypted data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function hotel_booking(Request $request)
    {
        try {
            $countries = Location::select('country', 'country_code')->distinct()->orderBy('country', 'asc')->get();
            $payment_gateways = PaymentGateways::all()->where('status', '1');
            $encrypted_room_data = $request->input('room_data');
            $encrypted_room = $request->input('room');
            $encrypted_option = $request->input('option');
            $encrypted_booking = $request->input('booking_data');

            $room = json_decode(decrypt($encrypted_room));
            $encrypted_room_data = json_decode(decrypt($encrypted_room_data));
            $booking_option = json_decode(decrypt($encrypted_option), true);
            $booking_data = json_decode(decrypt($encrypted_booking), true);

            return view('hotel.booking', [
                'room' => $room,
                'room_data' => $encrypted_room_data,
                'booking_option' => $booking_option,
                'booking_data' => $booking_data,
                'payment' => $payment_gateways,
                'countries' => $countries,
                'hotel_search' => session('hotel_search'),
                'agentWallet' => auth()->check() && auth()->user()->isAgent() ? auth()->user()->wallet : null,
            ]);
        } catch (\Exception $exception) {
            return abort(400, 'Invalid booking data provided.');
        }
    }

    /**
     * Save hotel booking to database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function booking(Request $request)
    {
            $room = json_decode(decrypt($request->input('room')), true);
            $room_data = json_decode(decrypt($request->input('room_data')), true);
            $option = json_decode(decrypt($request->input('option')), true);
            $booking_data = json_decode(decrypt($request->input('booking_data')), true);
            $user = $request->input('user');
            $payment = $request->input('accept_payment');
            $bookingdata = [
                'room_data' =>$room_data,
                'room' => $room,
                'option' => $option,
                'booking_data' => $booking_data,
            ];

            $guestData = $this->buildGuestData($request);

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

                    $invoice_url = route('hotel.invoice', ['booking_ref' => $booking->booking_code_ref]);

                return redirect($invoice_url);
            }

            return back()->withError('Failed to create booking. Please try again.');
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

        return view('hotel.invoice', compact('booking'));
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
                // Determine environment mode
                $env = 'dev';

                $params = [
                    'api_credential_1' => $api_cred->api_credential_1,
                    'api_credential_2' => $api_cred->api_credential_2,
                    'api_credential_3' => $api_cred->api_credential_3,
                    'api_credential_4' => '',
                    'api_credential_5' => '',
                    'api_credential_6' => '',
                    'env' => $env,
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

                    $rep = $response->json();

                    $params = [
                        "booking_status_flag" => "confirmed",
                        "booking_payment_state" => "paid",
                        "booking_payment_gateway" => request()->get('gateway'),
                        "booking_pnr" => $rep['booking_pnr'],
                        "booking_response_json" => $rep['response'],
                        "booking_response_error" => $rep['response'],
                    ];
                    DB::table('hotels_booking')->where('booking_code_ref', request()->get('token'))->update($params);
                    $invoice_url = route('hotel.invoice', ['booking_ref' => request()->get('token')]);
                    return redirect($invoice_url);

                } catch (\Exception $e) {
                    dd($e->getMessage());
                }

            }
        }else{
            return abort(404, 'Booking not found');
        }

    }

}
