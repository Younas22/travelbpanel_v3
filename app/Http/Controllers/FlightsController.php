<?php
namespace App\Http\Controllers;

use App\Models\FlightBooking;
use App\Models\Location;
use App\Models\PaymentGateways;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Session;
use App\Models\TravelPartner;
use App\Models\AgentWallet;
use Resend\Laravel\Facades\Resend;

class FlightsController extends Controller
{


    /**
     * Remove specific session data related to the booking or search flow.
     *
     * This method clears session values such as search criteria, booking data,
     * or any temporary user inputs to ensure a clean session state.
     * Typically used when restarting the booking process.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */

    public function remove_session(){
        session()->forget('key');
        session()->flush();

        return redirect('/');
    }


    /**
     * Handle flight search based on provided parameters.
     *
     * This method accepts various search criteria for flights, such as origin, destination,
     * trip type, flight type, travel dates, and passenger counts. It supports both one-way
     * and round-trip searches.
     *
     * @param string $origin          The IATA code or city name of the departure location.
     * @param string $destination     The IATA code or city name of the arrival location.
     * @param string $trip            Type of trip: 'oneway' or 'round'.
     * @param string $flight_type     Type of flight: e.g., 'economy', 'business'.
     * @param string $departure_date  Departure date in Y-m-d format.
     * @param string|null $return_date  Return date (only required for round trips).
     * @param int|null $adult         Number of adult passengers.
     * @param int|null $child         Number of child passengers.
     * @param int|null $infant        Number of infant passengers.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */

    public function index()
    {
        return view('flight.flights');
    }
    public function search($origin, $destination, $trip, $flight_type, $departure_date, $return_date = null, $adult = null, $child = null, $infant = null)
    {
        try {
            // For oneway trips, adjust parameters
            if ($trip == "oneway") {
                $adults   = $return_date;
                $ret_date = "";
                $infants  = $child;
                $children = $adult;
            } else {
                $adults   = $adult;
                $ret_date = $return_date;
                $infants  = $infant;
                $children = $child;
            }

            $view = 'flight.f2';
            $search_params = $this->normalize_parameters($origin, $destination, $trip, $flight_type, $departure_date, $ret_date, $adults, $children, $infants);

            // Validate parameters
            $validator = $this->validate_parameters($search_params);
            if ($validator->fails()) {
                return $this->show_error('validation', [
                    'errors' => $validator->errors(),
                    'search_params' => $search_params
                ]);
            }

            // Prepare session data
            $session_data = $this->prepare_session_data($search_params);
            session(['flight_search' => $session_data]);

            // Determine environment mode
            $env = 'dev';


            $api_creds = TravelPartner::where('status', 'active')->where('supplier_type', 'flight')->get();

            if ($api_creds->isEmpty()) {
                return $this->show_error('not_found', [
                    'error_details' => 'No active travel partners configured.',
                ]);
            }

            $currency = activeCurrency();

            $aggregated_flights = collect();


            foreach ($api_creds as $api_cred) {

                $payload = [
                    'api_credential_1' => $api_cred->api_credential_1,
                    'api_credential_2' => $api_cred->api_credential_2,
                    'api_credential_3' => $api_cred->api_credential_3,
                    'api_credential_4' => $api_cred->api_credential_4,
                    'api_credential_5' => '',
                    'api_credential_6' => '',
                    'commission' => $api_cred->commission_rate,
                    'discount'   => $api_cred->discount_rate,
                    'env' => $env,
                    'origin' => $origin ? strtoupper($origin) : "",
                    'destination' => $destination ? strtoupper($destination) : "",
                    'type' => $trip,
                    'departure_date' => $departure_date,
                    'return_date' => $ret_date,
                    'adults' => $adults,
                    'children' => $children,
                    'infants' => $infants,
                    'class' => $flight_type,
                    'currency' => $currency->currency_name,
                    'user_ip' => request()->ip(),
                ];


                $endpoint = url('/')."/api/".strtolower($api_cred->company_name)."/flight_search";

                $response_data = $this->call_api_curl($endpoint, $payload);

                // Skip this supplier if call failed or no usable data
                if (empty($response_data) || !isset($response_data['data'])) {
                    continue;
                }

                // Skip this supplier if it returned no results
                if (isset($response_data['data']['msg']) && $response_data['data']['msg'] === 'no_result') {
                    continue;
                }

                // Merge response data exactly as it came (no extra keys/wrapping)
                $aggregated_flights = $aggregated_flights->merge($response_data['data']);
            }

            if ($aggregated_flights->isEmpty()) {
                return $this->show_error('not_found', [
                    'error_details' => 'No flights found for the given search criteria.',
                ]);
            }

            // Current page from request
            $currentPage = RequestFacade::get('page', 1);
            $perPage = 100;

            // Slice data for current page
            $currentPageItems = $aggregated_flights->slice(($currentPage - 1) * $perPage, $perPage)->values();

            // Create paginator instance
            $paginatedFlights = new LengthAwarePaginator(
                $currentPageItems,
                $aggregated_flights->count(),
                $perPage,
                $currentPage,
                ['path' => url()->current(), 'query' => request()->query()]
            );

            return view($view, [
                'flights_data'  => $paginatedFlights,
                'flight_search' => session('flight_search'),
            ]);

        } catch (\InvalidArgumentException $e) {
            return view($view, [
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            return view($view, [
                'error' => 'An unexpected error occurred. Please try again.'
            ]);
        }
    }

    /**
     * Direct cURL call to a supplier's flight search endpoint.
     * Returns decoded JSON array, or null on failure.
     */
    /**
     * Direct cURL call to a supplier's flight search endpoint.
     * Returns decoded JSON array, or null on failure.
     */
    private function call_api_curl($url, $payload)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload, // array, NOT json_encode
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error     = curl_error($curl);

        curl_close($curl);

        if ($error || $http_code < 200 || $http_code >= 300 || !$response) {
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Normalize and clean parameters from URL
     *
     * @param mixed ...$params
     * @return array
     */

    private function normalize_parameters($origin, $destination, $trip, $flight_type, $departure_date, $returndate, $adults, $child, $infant): array
{
    // Clean parameters
    $origin = urldecode(trim($origin));
    $destination = urldecode(trim($destination));
    $trip = strtolower(trim($trip));
    $flight_type = strtolower(trim($flight_type));

    //  Convert departure date to Y-m-d
    $departure_date = $this->toYmdFormat($departure_date);

    //  Convert return date only if round trip
    if ($trip === 'oneway' || empty($returndate)) {
        $return_date = null;
    } else {
        $return_date = $this->toYmdFormat($returndate);
    }

    // Passenger counts
    $adult = (int) ($adults ?: 1);
    $child = (int) ($child ?: 0);
    $infant = (int) ($infant ?: 0);

    return [
        'origin' => $origin,
        'destination' => $destination,
        'trip_type' => $trip,
        'flight_type' => $flight_type,
        'departure_date' => $departure_date,
        'return_date' => $return_date,
        'adults' => $adult,
        'children' => $child,
        'infants' => $infant,
    ];
}


private function toYmdFormat($date)
{
    if (empty($date)) {
        return null;
    }

    try {
        $parsed = new \DateTime($date);
        return $parsed->format('Y-m-d'); // Force Y-m-d format
    } catch (\Exception $e) {
        return null; // Invalid date → null
    }
}


    /**
     * Validate search parameters
     *
     * @param array $params
     * @return \Illuminate\Validation\Validator
     */
    private function validate_parameters(array $params)
    {
        $rules = [
            'origin' => 'required|string|min:3|max:100',
            'destination' => 'required|string|min:3|max:100|different:origin',
            'trip_type' => 'required|in:oneway,round',
            'flight_type' => 'required|in:economy,premium_economy,business,first',
            'departure_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'return_date' => 'nullable|date_format:Y-m-d|after:departure_date',
            'adults' => 'required|integer|min:1|max:9',
            'children' => 'integer|min:0|max:9',
            'infants' => 'integer|min:0|max:9',
        ];

        $messages = [
            'origin.required' => 'Origin is required',
            'destination.required' => 'Destination is required',
            'destination.different' => 'Destination must be different from origin',
            'trip_type.in' => 'Trip type must be oneway or round',
            'flight_type.in' => 'Flight type must be economy, business, or first',
            'departure_date.required' => 'Departure date is required',
            'departure_date.date_format' => 'Departure date must be in Y-m-d format',
            'departure_date.after_or_equal' => 'Departure date cannot be in the past',
            'return_date.date_format' => 'Return date must be in Y-m-d format',
            'return_date.after' => 'Return date must be after departure date',
            'adults.min' => 'At least 1 adult passenger is required',
            'adults.max' => 'Maximum 9 adult passengers allowed',
            'children.max' => 'Maximum 9 child passengers allowed',
            'infants.max' => 'Maximum 9 infant passengers allowed',
        ];

        $validator = Validator::make($params, $rules, $messages);

        // Add custom validation
        $validator->after(function ($validator) use ($params) {
            $this->show_message($validator, $params);
        });

        return $validator;
    }

    /**
     * Add custom validation rules
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param array $params
     */
    private function show_message($validator, array $params): void
    {
        // Infants cannot exceed adults
        if ($params['infants'] > $params['adults']) {
            $validator->errors()->add('infants', 'Number of infants cannot exceed number of adults');
        }

        // Total passengers limit
        $total_passengers = $params['adults'] + $params['children'] + $params['infants'];
        if ($total_passengers > 9) {
            $validator->errors()->add('passengers', 'Total passengers cannot exceed 9');
        }

        // Return date validation for return trips
        if ($params['trip_type'] === 'round' && empty($params['return_date'])) {
            $validator->errors()->add('return_date', 'Return date is required for return trips');
        }

        // Validate airport codes (if needed)
        if (!$this->valid_airport_code($params['origin'])) {
            $validator->errors()->add('origin', 'Invalid origin airport code');
        }

        if (!$this->valid_airport_code($params['destination'])) {
            $validator->errors()->add('destination', 'Invalid destination airport code');
        }
    }

    /**
     * Validate airport code format (basic validation)
     *
     * @param string $code
     * @return bool
     */
    private function valid_airport_code(string $code): bool
    {
        // Basic validation - 3 letter IATA code or city name
        return strlen($code) >= 3 && strlen($code) <= 50 && preg_match('/^[a-zA-Z\s]+$/', $code);
    }

    /**
     * Prepare session data
     *
     * @param array $params
     * @return array
     */
    private function prepare_session_data(array $params): array
    {
        $totalPassengers = $params['adults'] + $params['children'] + $params['infants'];

        return [
            'origin' => $params['origin'],
            'destination' => $params['destination'],
            'origin_name' => $this->get_airport_name($params['origin']),
            'destination_name' => $this->get_airport_name($params['destination']),
            'trip_type' => $params['trip_type'],
            'flight_type' => $params['flight_type'],
            'departure_date' => $params['departure_date'],
            'return_date' => $params['return_date'],
            'adults' => $params['adults'],
            'children' => $params['children'],
            'infants' => $params['infants'],
            'passenger_count' => $totalPassengers,
            'search_timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get airport name from code (placeholder - implement as needed)
     *
     * @param string $code
     * @return string
     */
    private function get_airport_name(string $code): string
    {
        $flights_airports = DB::table("flights_airports")->where("code", $code)->get();
        return $flights_airports[0]->city;
    }

    /**
     * Show flight search error page
     *
     * @param string $error_type
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    public function show_error($error_type = 'general', $data = [])
    {
        $errorData = $this->get_error_data($error_type, $data);
        return view('error', $errorData);
    }

    /**
     * Get error data based on error type
     *
     * @param string $error_type
     * @param array $data
     * @return array
     */
    private function get_error_data($error_type, $data = []): array
    {
        $errorConfigs = [
            'validation' => [
                'error_code' => 'VALIDATION_ERROR',
                'error_title' => 'Invalid Search Parameters',
                'error_message' => 'Please check your search parameters and try again.',
                'error_details' => 'One or more search parameters are invalid or missing.'
            ],
            'general' => [
                'error_code' => 'FLIGHT_SEARCH_ERROR',
                'error_title' => 'Flight Search Error',
                'error_message' => 'We encountered an issue while processing your flight search.',
                'error_details' => 'Please try again or contact support if the problem persists.'
            ],
            'not_found' => [
                'error_code' => 'NO_FLIGHTS_FOUND',
                'error_title' => 'No Flights Found',
                'error_message' => 'We couldnt find any flights matching your search criteria.',
                'error_details' => 'Try adjusting your search parameters like dates, destinations, or passenger count.'
            ],
            'api_error' => [
                'error_code' => 'API_CONNECTION_ERROR',
                'error_title' => 'Service Unavailable',
                'error_message' => 'Our flight search service is temporarily unavailable.',
                'error_details' => 'Please try again in a few minutes or contact support.'
            ],
            'timeout' => [
                'error_code' => 'SEARCH_TIMEOUT',
                'error_title' => 'Search Timeout',
                'error_message' => 'Your search request timed out.',
                'error_details' => 'Please try again with a more specific search or contact support.'
            ]
        ];

        $config = $errorConfigs[$error_type] ?? $errorConfigs['general'];

        // Merge with provided data
        return array_merge($config, $data);
    }


    /**
     * Display the booking page with decrypted route and booking data.
     *
     * This method securely decrypts and decodes the 'routes' and 'booking_data'
     * parameters passed via the request. It also includes error handling to
     * gracefully manage decryption failures or invalid data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */

    public function booking(Request $request)
    {
        try {
            $encrypted_routes = $request->input('routes');
            $encrypted_booking_data = $request->input('booking_data');

            $payment_gateways = PaymentGateways::all()->where('status', '1');
            $route = json_decode(decrypt($encrypted_routes));
            $booking_data = json_decode(decrypt($encrypted_booking_data), true);

            $countries = Location::select('country', 'country_code')->distinct()->orderBy('country', 'asc')->get();

            return view('flight.booking', [
                'routes' => $route,
                'booking_data' => $booking_data,
                'payment' => $payment_gateways,
                'countries' => $countries,
                'session_data' => session('flight_search'),
                'agentWallet' => auth()->check() && auth()->user()->isAgent() ? auth()->user()->wallet : null,
            ]);
        } catch (\Exception $e) {
            return $this->show_error('decryption_or_other_error', [
                'error_details' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Handle the flight booking process with decrypted data and save it to the database.
     *
     * This method decrypts the input payloads, structures user and guest data,
     * and creates a new booking record. On success, redirects to the invoice page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function flight_booking(Request $request)
    {

        // ===============================
        // Decrypt & Decode Booking Inputs
        // ===============================
        $bookingPayload = json_decode(decrypt($request->input('booking_data')), true);
        $flightSegment  = json_decode(decrypt($request->input('flight_segment')), true);
        $currency       = decrypt($request->input('currency'));
        $price          = decrypt($request->input('price'));
        $partnerName    = decrypt($request->input('partner_name'));
        $payment        = $request->input('accept_payment');

        $get_traveller = session('flight_search');

        // ====================
        // Capture User Details
        // ====================
        $user = (object) [
            'first_name'   => $request->user['first_name'],
            'last_name'    => $request->user['last_name'],
            'user_email'   => $request->user['email'],
            'user_phone'   => preg_replace('/\D/', '', $request->user['phone']),
            'user_address' => $request->user['address'],
        ];

        // =============================
        // Capture Guest Traveler Detail
        // =============================
        $guestData = [];

        for ($i = 1; $i <= $get_traveller['adults']; $i++) {
            $guestData[] = (object) [
                'traveller_type'          => $request->input("traveller_type_$i"),
                'title'                   => $request->input("title_$i"),
                'first_name'              => $request->input("first_name_$i"),
                'last_name'               => $request->input("last_name_$i"),
                'nationality'             => $request->input("nationality_$i"),
                'dob_day'                 => $request->input("dob_day_$i"),
                'dob_month'               => $request->input("dob_month_$i"),
                'dob_year'                => $request->input("dob_year_$i"),
                'passport'                => $request->input("passport_$i"),
                'passport_day_expiry'     => $request->input("passport_day_expiry_$i"),
                'passport_month_expiry'   => $request->input("passport_month_expiry_$i"),
                'passport_year_expiry'    => $request->input("passport_year_expiry_$i"),
                'passport_issuance_day'   => $request->input("passport_issuance_day_$i"),
                'passport_issuance_month' => $request->input("passport_issuance_month_$i"),
                'passport_issuance_year'  => $request->input("passport_issuance_year_$i"),
                'gender'                  => $request->input("gender_$i"),
                'email'                   => $request->input("email_$i"),
                'phone'                   => preg_replace('/\D/', '', $request->input("phone_$i")),
            ];
        }

        for ($i = 1; $i <= $get_traveller['children']; $i++) {
            $guestData[] = (object) [
                'traveller_type'          => $request->input("children_traveller_type_$i"),
                'title'                   => $request->input("children_title_$i"),
                'first_name'              => $request->input("children_first_name_$i"),
                'last_name'               => $request->input("children_last_name_$i"),
                'dob_day'                 => $request->input("children_dob_day_$i"),
                'dob_month'               => $request->input("children_dob_month_$i"),
                'dob_year'                => $request->input("children_dob_year_$i"),
                'gender'                  => $request->input("children_gender_$i"),
                'email'                   => $request->input("children_email_$i"),
                'phone'                   => $request->input("children_phone_$i"),
                'passport'                => $request->input("children_passport_$i"),
                'passport_day_expiry'     => $request->input("children_passport_day_expiry_$i"),
                'passport_month_expiry'   => $request->input("children_passport_month_expiry_$i"),
                'passport_year_expiry'    => $request->input("children_passport_year_expiry_$i"),
                'passport_issuance_day'   => $request->input("children_passport_issuance_day_$i"),
                'passport_issuance_month' => $request->input("children_passport_issuance_month_$i"),
                'passport_issuance_year'  => $request->input("children_passport_issuance_year_$i"),
            ];
        }

        for ($i = 1; $i <= $get_traveller['infants']; $i++) {
            $guestData[] = (object) [
                'traveller_type'          => $request->input("infant_traveller_type_$i"),
                'title'                   => $request->input("infant_title_$i"),
                'first_name'              => $request->input("infant_first_name_$i"),
                'last_name'               => $request->input("infant_last_name_$i"),
                'dob_day'                 => $request->input("infant_dob_day_$i"),
                'dob_month'               => $request->input("infant_dob_month_$i"),
                'dob_year'                => $request->input("infant_dob_year_$i"),
                'gender'                  => $request->input("infant_gender_$i"),
                'email'                   => $request->input("infant_email_$i"),
                'phone'                   => $request->input("infant_phone_$i"),
            ];
        }

        $guest = json_encode($guestData);

        // ============================
        // Save Booking to the Database
        // ============================
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
            $bookingAmount = (float) str_replace(',', '', $price);
            $agentWallet   = AgentWallet::where('agent_id', $agentId)->first();
            if (!$agentWallet || !$agentWallet->hasSufficientBalance($bookingAmount)) {
                $available = $agentWallet ? number_format($agentWallet->balance, 2) : '0.00';
                return back()->withInput()->with('error', "Insufficient wallet balance. Available: PKR {$available}. Please top up your wallet first.");
            }
        }

        $booking_payload = [
            'booking_code_ref'         => date('YmdHis'),
            'booking_status_flag'      => 'pending',
            'booking_fare_base'        => str_replace(",", "", $price),
            'booking_adult_count'      => $get_traveller['adults'],
            'booking_child_count'      => $get_traveller['children'],
            'booking_infant_count'     => $get_traveller['infants'],
            'booking_currency_origin'  => $currency,
            'booking_payment_state'    => 'unpaid',
            'booking_data'             => json_encode($bookingPayload),
            'booking_supplier_name'    => $partnerName,
            'booking_user_data'        => json_encode($user),
            'booking_guest'            => $guest,
            'booking_nationality_code' => $request->input('p_nationality') ?? "US",
            'booking_flight_segment'   => json_encode($flightSegment),
            'booking_payment_gateway'  => $payment,
            'agent_id'                 => $agentId,
            'user_id'                  => $userId,
            'booked_via'               => $bookedVia,
        ];

        $booking = FlightBooking::create($booking_payload);

        // =====================
        // Redirect or Show Error
        // =====================
        if ($booking) {
            // Debit agent wallet if selected
            if ($payment === 'agent_wallet' && $agentId) {
                $agentWallet = $agentWallet ?? AgentWallet::where('agent_id', $agentId)->first();
                $agentWallet->debit(
                    amount:      (float) str_replace(',', '', $price),
                    note:        'Flight booking #' . $booking->booking_code_ref,
                    performedBy: $agentId,
                    bookingType: 'flight',
                    bookingId:   $booking->id,
                    reference:   $booking->booking_code_ref,
                );
                $booking->update(['booking_payment_state' => 'paid']);
            }

            $invoice_url = route('flight.invoice', ['booking_ref' => $booking->booking_code_ref]);

            try {

                $customerEmail = $user->user_email;

                if (!empty($customerEmail)) {

                    // Resend API key
                    $apiKey = Setting::getValue('resend_api_key', 'email');
                    config(['services.resend.key' => $apiKey]);

                    $senderEmail  = getSetting('sender_email', 'email', 'noreply@travelbookingpanel.com');
                    $senderName   = getSetting('sender_name', 'email', 'Travel Booking Panel');
                    $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');

                    $invoiceUrl = route('flight.invoice', [
                        'booking_ref' => $booking->booking_code_ref
                    ]);

                    Resend::emails()->send([
                        'from'    => "{$senderName} <{$senderEmail}>",
                        'to'      => [$customerEmail],
                        'subject' => "Your Flight Booking Invoice - {$booking->booking_code_ref}",
                        'html'    => $this->getInvoiceEmailHtml(
                            $booking,
                            $businessName,
                            $invoiceUrl
                        ),
                    ]);

                    \Log::info('Invoice email sent on booking creation', [
                        'email' => $customerEmail,
                        'booking' => $booking->booking_code_ref
                    ]);
                }

            } catch (\Exception $e) {
                \Log::error('Invoice email failed on booking creation', [
                    'error' => $e->getMessage()
                ]);
            }

            return redirect($invoice_url);
        }

        return $this->show_error('booking_failed', [
            'error_details' => 'Unable to save booking to the database.',
        ]);
    }


    /**
     * Display the invoice page for a given booking reference.
     *
     * This method fetches the flight booking using the booking reference.
     * If not found, it returns a custom error response.
     *
     * @param  string  $booking_ref
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */


    public function invoice($booking_ref)
{
    $booking = FlightBooking::where('booking_code_ref', $booking_ref)->first();

    if (!$booking) {
        return $this->show_error('invoice', [
            'error_details' => 'Please provide a valid invoice URL.',
        ]);
    }

    return view('flight.invoice', compact('booking'));
}


    /**
     * Send invoice email to customer
     *
     * @param  string  $booking_ref
     * @return \Illuminate\Http\JsonResponse
     */

    public function sendInvoiceEmail($booking_ref)
    {
        try {
            $booking = FlightBooking::where('booking_code_ref', $booking_ref)->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.'
                ], 404);
            }

            $customerEmail = $booking->customer_email;

            if (empty($customerEmail) || $customerEmail === 'N/A') {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer email not found.'
                ], 400);
            }


            // 🔑 Same API key logic as test email
            $apiKey = Setting::getValue('resend_api_key', 'email');
            config(['services.resend.key' => $apiKey]);

            $senderEmail = getSetting('sender_email', 'email', 'noreply@travelbookingpanel.com');
            $senderName  = getSetting('sender_name', 'email', 'Travel Booking Panel');
            $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');

            $invoiceUrl = route('flight.invoice', [
                'booking_ref' => $booking->booking_code_ref
            ]);

            $result = Resend::emails()->send([
                'from'    => "{$senderName} <{$senderEmail}>",
                'to'      => [$customerEmail],
                'subject' => "Your Flight Booking Invoice - {$booking->booking_code_ref}",
                'html'    => $this->getInvoiceEmailHtml($booking, $businessName, $invoiceUrl),
            ]);

            \Log::info('Invoice email sent via Resend', [
                'email' => $customerEmail,
                'id' => $result['id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice email sent successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Invoice email failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the HTML content for invoice email
     */

    private function getInvoiceEmailHtml($booking, $businessName, $invoiceUrl)
{

    $businessLogo    = getSettingImage('business_logo_white', 'branding');
    $businessAddress = getSetting('business_address', 'contact', '');
    $contactPhone    = getSetting('contact_phone', 'contact', '+92 21 1234 5678');
    $contactEmail    = getSetting('contact_email', 'contact', 'support@travelbookingpanel.com');

    $customerName = $booking->customer_name;
    $firstName = explode(' ', $customerName)[0] ?? 'Customer';

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . t('invoice_email_template.title') . '</title>
    </head>
    <body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;background:#f8f9fa;line-height:1.6;">

    <div style="max-width:600px;margin:0 auto;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.1);">

        <div style="background:linear-gradient(135deg,#0077BE,#005a8d);color:#fff;padding:40px 30px;text-align:center;">
            ' . ($businessLogo ? '<img src="'.$businessLogo.'" style="max-height:60px;margin-bottom:20px;">' : '') . '
            <h1 style="margin:0;font-size:32px;">' . t('invoice_email_template.title') . '</h1>
            <p style="margin-top:10px;">
                ' . t('invoice_email_template.booking_reference') . ':
                ' . htmlspecialchars($booking->booking_code_ref) . '
            </p>
        </div>

        <div style="padding:40px 30px;">

            <p>' . t('invoice_email_template.dear'). htmlspecialchars($firstName) . '</p>

            <p>' . t('invoice_email_template.intro'). htmlspecialchars($businessName) . '</p>

            <p>' . t('invoice_email_template.invoice_instruction') . '</p>

            <div style="text-align:center;margin:30px 0;">
                <a href="'.$invoiceUrl.'" style="background:#0077BE;color:#fff;padding:16px 40px;border-radius:30px;text-decoration:none;font-weight:700;">
                    📄 ' . t('invoice_email_template.view_invoice') . '
                </a>
            </div>

            <div style="background:#f8f9fa;padding:20px;text-align:center;">
                <p><strong>' . t('invoice_email_template.copy_link') . '</strong></p>
                <a href="'.$invoiceUrl.'" style="color:#0077BE;">'.$invoiceUrl.'</a>
            </div>

            <div style="background:#fff3cd;padding:20px;margin:30px 0;">
                <h4>⚠️ ' . t('invoice_email_template.important_title') . '</h4>
                <ul>
                    <li>' . t('invoice_email_template.important_1') . '</li>
                    <li>' . t('invoice_email_template.important_2') . '</li>
                    <li>' . t('invoice_email_template.important_3') . '</li>
                </ul>
            </div>

            <div style="background:#f8f9fa;padding:20px;">
                <h4>📞 ' . t('invoice_email_template.need_help') . '</h4>
                <p>
                    ' . t('invoice_email_template.help_text') . '<br><br>
                    <strong>Email:</strong> '.$contactEmail.'<br>
                    <strong>Phone:</strong> '.$contactPhone.'<br>
                    <strong>' . t('invoice_email_template.support') . '</strong>
                </p>
            </div>

            <p>' . t('invoice_email_template.thank_you'). htmlspecialchars($businessName) . '</p>

            <p>
                ' . t('invoice_email_template.regards') . '<br>
                <strong>' . t('invoice_email_template.team') . htmlspecialchars($businessName) . '</strong>
            </p>
        </div>

        <div style="background:#343a40;color:#fff;padding:25px;text-align:center;">
            <p>' . t('invoice_email_template.trusted_partner') . '</p>
            <p>© '.date('Y').' '.$businessName.'. ' . t('invoice_email_template.rights') . '</p>
        </div>

    </div>
    </body>
    </html>';
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
        $booking = FlightBooking::where('booking_code_ref', $booking_ref)->first();
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
            $booking = FlightBooking::where('booking_code_ref', request()->get('token'))->first();

            $api_cred = TravelPartner::where('status', 'active')->where("company_name",$booking->booking_supplier_name)->first();


            // Determine environment mode
            $env = 'dev';

            $params = [
                'api_credential_1'=> $api_cred->api_credential_1,
                'api_credential_2'=> $api_cred->api_credential_2,
                'api_credential_3'=> $api_cred->api_credential_3,
                'api_credential_4'=> '',
                'api_credential_5'=> '',
                'api_credential_6'=> '',
                'env' => $env,
                'booking_data' => $booking->booking_data,
                'guest' => $booking->booking_guest,
                'user_data' => $booking->booking_user_data,
            ];

        // Define actual API endpoint
        $endpoint = url('/') . "/api/".$booking->booking_supplier_name."/booking";

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($endpoint, $params);

            $rep = $response->json();

            $params = [
                "booking_status_flag" => "confirmed",
                "booking_payment_state" => "paid",
                "booking_payment_gateway" => request()->get('gateway'),
                "booking_air_pnr" => $rep['data'][0]['Prn'],
                "booking_response_json" => $rep['data'][0]['response'],
                "booking_response_error" => $rep['data'][0]['response_error'],
            ];
            FlightBooking::where('booking_code_ref', request()->get('token'))->update($params);
            $invoice_url = route('flight.invoice', ['booking_ref' =>  request()->get('token')]);
            return redirect($invoice_url);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        }else{
            return abort(404, 'Booking not found');
        }

    }


}
