<?php

namespace App\Http\Controllers;

use App\Models\Umrah;
use App\Models\UmrahInclusion;
use App\Models\UmrahExclusion;
use App\Models\UmrahPackageType;
use App\Models\Location;
use App\Models\PaymentGateways;
use App\Models\AgentWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UmrahController extends Controller
{

    public function index()
    {
        // Fetch featured and popular umrah packages (status=1, featured first, limit to 8)
        $umrahPackages = Umrah::with(['images', 'packageType'])
            ->where('status', '1')
            ->orderBy('featured', 'desc')
            ->limit(8)
            ->get();

        // Get all inclusions and exclusions for mapping
        $allInclusions = UmrahInclusion::pluck('name', 'id')->toArray();
        $allExclusions = UmrahExclusion::pluck('name', 'id')->toArray();

        // Transform packages to include inclusion/exclusion names
        $umrahPackages->transform(function ($package) use ($allInclusions, $allExclusions) {
            // Map inclusion IDs to names
            if ($package->inclusions) {
                $package->highlights = collect($package->inclusions)->map(function ($id) use ($allInclusions) {
                    return $allInclusions[$id] ?? null;
                })->filter()->values()->toArray();
            } else {
                $package->highlights = [];
            }

            // Map exclusion IDs to names
            if ($package->exclusions) {
                $package->exclusion_names = collect($package->exclusions)->map(function ($id) use ($allExclusions) {
                    return $allExclusions[$id] ?? null;
                })->filter()->values()->toArray();
            } else {
                $package->exclusion_names = [];
            }

            // Get location names from flights_airports
            if ($package->leaving_from) {
                $fromAirport = DB::table('flights_airports')->where('id', $package->leaving_from)->first();
                $package->from_location = $fromAirport ? $fromAirport->city : '';
            } else {
                $package->from_location = '';
            }

            if ($package->going_to) {
                $toAirport = DB::table('flights_airports')->where('id', $package->going_to)->first();
                $package->to_location = $toAirport ? $toAirport->city : '';
            } else {
                $package->to_location = '';
            }

            return $package;
        });

        return view('umrah.umrah', compact('umrahPackages'));
    }

    // Umrah Search API
    public function umrahSearch($origin, $destination, $departure_date, $return_date, $adult, $child, $infant, $makkah_nights, $madina_nights)
    {
        // Get airport IDs from flights_airports table using codes
        $originAirport = DB::table('flights_airports')->where('code', strtoupper($origin))->first();
        $destinationAirport = DB::table('flights_airports')->where('code', strtoupper($destination))->first();

        $originId = $originAirport ? $originAirport->id : null;
        $destinationId = $destinationAirport ? $destinationAirport->id : null;

        // Build query for umrah packages (status=1 only, featured first)
        $query = Umrah::with(['images', 'packageType'])
            ->where('status', '1')
            ->orderBy('featured', 'desc');

        // Filter by leaving_from and going_to if airport IDs found
        if ($originId) {
            $query->where('leaving_from', $originId);
        }
        if ($destinationId) {
            $query->where('going_to', $destinationId);
        }

        // Paginate results (20 per page)
        $packages = $query->paginate(20);

        // Get all inclusions and exclusions for mapping
        $allInclusions = UmrahInclusion::pluck('name', 'id')->toArray();
        $allExclusions = UmrahExclusion::pluck('name', 'id')->toArray();

        // Transform packages to include inclusion/exclusion names
        $packages->getCollection()->transform(function ($package) use ($allInclusions, $allExclusions) {
            // Map inclusion IDs to names
            if ($package->inclusions) {
                $package->inclusion_names = collect($package->inclusions)->map(function ($id) use ($allInclusions) {
                    return $allInclusions[$id] ?? null;
                })->filter()->values()->toArray();
            } else {
                $package->inclusion_names = [];
            }

            // Map exclusion IDs to names
            if ($package->exclusions) {
                $package->exclusion_names = collect($package->exclusions)->map(function ($id) use ($allExclusions) {
                    return $allExclusions[$id] ?? null;
                })->filter()->values()->toArray();
            } else {
                $package->exclusion_names = [];
            }

            // Get location names from flights_airports
            if ($package->leaving_from) {
                $fromAirport = DB::table('flights_airports')->where('id', $package->leaving_from)->first();
                $package->from_location = $fromAirport ? $fromAirport->city : '';
            } else {
                $package->from_location = '';
            }

            if ($package->going_to) {
                $toAirport = DB::table('flights_airports')->where('id', $package->going_to)->first();
                $package->to_location = $toAirport ? $toAirport->city : '';
            } else {
                $package->to_location = '';
            }

            return $package;
        });

        // Search parameters for reference
        $searchParams = [
            'origin' => strtoupper($origin),
            'destination' => strtoupper($destination),
            'departure_date' => $departure_date,
            'return_date' => $return_date,
            'adult' => (int) $adult,
            'child' => (int) $child,
            'infant' => (int) $infant,
            'makkah_nights' => (int) $makkah_nights,
            'madina_nights' => (int) $madina_nights,
        ];

        // Store in session for form persistence
        // Convert DD-MM-YYYY to YYYY-MM-DD for flatpickr
        $departureParts = explode('-', $departure_date);
        $returnParts = explode('-', $return_date);
        $sessionDeparture = count($departureParts) === 3 ? $departureParts[2] . '-' . $departureParts[1] . '-' . $departureParts[0] : $departure_date;
        $sessionReturn = count($returnParts) === 3 ? $returnParts[2] . '-' . $returnParts[1] . '-' . $returnParts[0] : $return_date;

        $totalPassengers = (int) $adult + (int) $child + (int) $infant;
        session(['umrah_search' => [
            'origin' => strtoupper($origin),
            'origin_name' => $originAirport ? $originAirport->city . ' (' . $originAirport->code . ')' : strtoupper($origin),
            'destination' => strtoupper($destination),
            'destination_name' => $destinationAirport ? $destinationAirport->city . ' (' . $destinationAirport->code . ')' : strtoupper($destination),
            'departure_date' => $sessionDeparture,
            'return_date' => $sessionReturn,
            'adult' => (int) $adult,
            'children' => (int) $child,
            'infants' => (int) $infant,
            'passenger_count' => $totalPassengers,
            'makkah_nights' => (int) $makkah_nights,
            'madina_nights' => (int) $madina_nights,
        ]]);

        return view('umrah.list', [
            'packages' => $packages,
            'searchParams' => $searchParams,
            'originAirport' => $originAirport,
            'destinationAirport' => $destinationAirport,
        ]);
    }

    // Umrah Details Page
    public function details($slug, $origin, $destination, $departure_date, $return_date, $adult, $child, $infant, $makkah_nights, $madina_nights)
    {
        // Find package by slug (name with spaces replaced by dashes)
        $package = Umrah::with(['images', 'packageType'])
            ->where('status', '1')
            ->get()
            ->first(function ($pkg) use ($slug) {
                return \Str::slug($pkg->name) === $slug;
            });

        if (!$package) {
            abort(404);
        }

        // Get airport details
        $originAirport = DB::table('flights_airports')->where('code', strtoupper($origin))->first();
        $destinationAirport = DB::table('flights_airports')->where('code', strtoupper($destination))->first();

        // Get all inclusions and exclusions for mapping
        $allInclusions = UmrahInclusion::pluck('name', 'id')->toArray();
        $allExclusions = UmrahExclusion::pluck('name', 'id')->toArray();

        // Map inclusion IDs to names
        if ($package->inclusions) {
            $package->inclusion_names = collect($package->inclusions)->map(function ($id) use ($allInclusions) {
                return $allInclusions[$id] ?? null;
            })->filter()->values()->toArray();
        } else {
            $package->inclusion_names = [];
        }

        // Map exclusion IDs to names
        if ($package->exclusions) {
            $package->exclusion_names = collect($package->exclusions)->map(function ($id) use ($allExclusions) {
                return $allExclusions[$id] ?? null;
            })->filter()->values()->toArray();
        } else {
            $package->exclusion_names = [];
        }

        // Search parameters for reference
        $searchParams = [
            'origin' => strtoupper($origin),
            'destination' => strtoupper($destination),
            'departure_date' => $departure_date,
            'return_date' => $return_date,
            'adult' => (int) $adult,
            'child' => (int) $child,
            'infant' => (int) $infant,
            'makkah_nights' => (int) $makkah_nights,
            'madina_nights' => (int) $madina_nights,
        ];

        return view('umrah.details', [
            'package' => $package,
            'searchParams' => $searchParams,
            'originAirport' => $originAirport,
            'destinationAirport' => $destinationAirport,
        ]);
    }

    // Umrah Booking Page
    public function booking($slug, $origin, $destination, $departure_date, $return_date, $adult, $child, $infant, $makkah_nights, $madina_nights)
    {
        $countries = Location::select('country', 'country_code')->distinct()->orderBy('country', 'asc')->get();
        // Find package by slug (name with spaces replaced by dashes)
        $package = Umrah::with(['images', 'packageType'])
            ->where('status', '1')
            ->get()
            ->first(function ($pkg) use ($slug) {
                return \Str::slug($pkg->name) === $slug;
            });

        if (!$package) {
            abort(404);
        }

        // Get airport details
        $originAirport = DB::table('flights_airports')->where('code', strtoupper($origin))->first();
        $destinationAirport = DB::table('flights_airports')->where('code', strtoupper($destination))->first();

        // Get all inclusions and exclusions for mapping
        $allInclusions = UmrahInclusion::pluck('name', 'id')->toArray();
        $allExclusions = UmrahExclusion::pluck('name', 'id')->toArray();

        // Map inclusion IDs to names
        if ($package->inclusions) {
            $package->inclusion_names = collect($package->inclusions)->map(function ($id) use ($allInclusions) {
                return $allInclusions[$id] ?? null;
            })->filter()->values()->toArray();
        } else {
            $package->inclusion_names = [];
        }

        // Map exclusion IDs to names
        if ($package->exclusions) {
            $package->exclusion_names = collect($package->exclusions)->map(function ($id) use ($allExclusions) {
                return $allExclusions[$id] ?? null;
            })->filter()->values()->toArray();
        } else {
            $package->exclusion_names = [];
        }

        // Search parameters for reference
        $searchParams = [
            'origin' => strtoupper($origin),
            'destination' => strtoupper($destination),
            'departure_date' => $departure_date,
            'return_date' => $return_date,
            'adult' => (int) $adult,
            'child' => (int) $child,
            'infant' => (int) $infant,
            'makkah_nights' => (int) $makkah_nights,
            'madina_nights' => (int) $madina_nights,
        ];

        $active_currency = activeCurrency();
        $package_price = convertCurrency($package->price, $package->currency ?? 'USD', $active_currency->currency_name);
        // Prepare umrah data for booking
        $umrah_data = [
            'umrah_id' => $package->id,
            'umrah_name' => $package->name,
            'price' => $package_price,
            'child_price' => convertCurrency($package->child_price, $package->currency ?? 'USD', $active_currency->currency_name) ?? ($package_price * 0.7),
            'infant_price' => convertCurrency($package->infant_price, $package->currency ?? 'USD', $active_currency->currency_name) ?? ($package_price * 0.1),
            'currency' => $active_currency->currency_name ?? 'USD',
            'duration' => $package->duration,
            'night_in_mekkah' => $package->night_in_mekkah,
            'night_in_madina' => $package->night_in_madina,
        ];

        $payment_gateways = PaymentGateways::all()->where('status', '1');

        return view('umrah.booking', [
            'countries' => $countries,
            'package' => $package,
            'searchParams' => $searchParams,
            'umrah_data' => $umrah_data,
            'originAirport' => $originAirport,
            'destinationAirport' => $destinationAirport,
            'payment' => $payment_gateways,
            'agentWallet' => auth()->check() && auth()->user()->isAgent() ? auth()->user()->wallet : null,
        ]);
    }

    // Store Umrah Booking
    public function storeBooking(Request $request)
    {
        // Decrypt umrah data and search params
        $umrahData = json_decode(decrypt($request->umrah_data), true);
        $searchParams = json_decode(decrypt($request->search_params), true);

        // Get user data
        $userData = $request->input('user');

        // Prepare travellers array
        $travellers = [];

        // Process adult travellers
        $adultCount = $searchParams['adult'] ?? 0;
        for ($i = 1; $i <= $adultCount; $i++) {
            $travellers[] = [
                'type' => 'adult',
                'gender' => $request->input("adult_gender_{$i}"),
                'first_name' => $request->input("adult_first_name_{$i}"),
                'last_name' => $request->input("adult_last_name_{$i}"),
            ];
        }

        // Process child travellers
        $childCount = $searchParams['child'] ?? 0;
        for ($i = 1; $i <= $childCount; $i++) {
            $travellers[] = [
                'type' => 'child',
                'gender' => $request->input("child_gender_{$i}"),
                'first_name' => $request->input("child_first_name_{$i}"),
                'last_name' => $request->input("child_last_name_{$i}"),
            ];
        }

        // Process infant travellers
        $infantCount = $searchParams['infant'] ?? 0;
        for ($i = 1; $i <= $infantCount; $i++) {
            $travellers[] = [
                'type' => 'infant',
                'gender' => $request->input("infant_gender_{$i}"),
                'first_name' => $request->input("infant_first_name_{$i}"),
                'last_name' => $request->input("infant_last_name_{$i}"),
            ];
        }

        // Calculate total price
        $adultPrice = $umrahData['price'] * $adultCount;
        $childPrice = ($umrahData['child_price'] ?? ($umrahData['price'] * 0.7)) * $childCount;
        $infantPrice = ($umrahData['infant_price'] ?? ($umrahData['price'] * 0.1)) * $infantCount;
        $totalPrice = $adultPrice + $childPrice + $infantPrice;

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
        if ($request->input('accept_payment') === 'agent_wallet') {
            if (!$agentId) {
                return back()->withInput()->with('error', 'Wallet payment is only available for agents.');
            }
            $agentWallet = AgentWallet::where('agent_id', $agentId)->first();
            if (!$agentWallet || !$agentWallet->hasSufficientBalance($totalPrice)) {
                $available = $agentWallet ? number_format($agentWallet->balance, 2) : '0.00';
                return back()->withInput()->with('error', "Insufficient wallet balance. Available: PKR {$available}. Please top up your wallet first.");
            }
        }

        // Generate booking reference
        $bookingRef = 'UMR-' . strtoupper(uniqid());

        // Store booking in database
        $booking = DB::table('umrah_bookings')->insertGetId([
            'booking_code_ref' => $bookingRef,
            'booking_status_flag' => 'pending',
            'umrah_id' => $umrahData['umrah_id'],
            'umrah_name' => $umrahData['umrah_name'],
            'user_first_name' => $userData['first_name'],
            'user_last_name' => $userData['last_name'],
            'user_email' => $userData['email'],
            'user_phone' => $userData['phone'],
            'user_country' => $userData['country'],
            'booking_guest' => json_encode($travellers),
            'booking_nationality_code' => $userData['country'] ?? null,
            'search_params' => json_encode($searchParams),
            'booking_adult_count' => $adultCount,
            'booking_child_count' => $childCount,
            'infant_count' => $infantCount,
            'adult_price' => $adultPrice,
            'child_price' => $childPrice,
            'infant_price' => $infantPrice,
            'total_price' => $totalPrice,
            'booking_fare_base' => $totalPrice,
            'booking_currency_origin' => $umrahData['currency'],
            'booking_payment_state' => 'unpaid',
            'booking_payment_gateway' => $request->input('accept_payment'),
            'booking_user_data' => json_encode($userData),
            'booking_supplier_name' => "umrah",
            'agent_id' => $agentId,
            'user_id' => $userId,
            'booked_via' => $bookedVia,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Debit agent wallet if selected
        if ($request->input('accept_payment') === 'agent_wallet' && $agentId) {
            $agentWallet = $agentWallet ?? AgentWallet::where('agent_id', $agentId)->first();
            $agentWallet->debit(
                amount:      $totalPrice,
                note:        'Umrah booking #' . $bookingRef,
                performedBy: $agentId,
                bookingType: 'umrah',
                bookingId:   $booking,
                reference:   $bookingRef,
            );
            DB::table('umrah_bookings')->where('booking_code_ref', $bookingRef)
                ->update(['booking_payment_state' => 'paid']);
        }

        return redirect()->route('umrah.invoice', ['booking_ref' => $bookingRef]);
    }

    // Umrah Invoice
    public function invoice($bookingRef)
    {
        $booking = DB::table('umrah_bookings')->where('booking_code_ref', $bookingRef)->first();

        if (!$booking) {
            abort(404);
        }

        $package = Umrah::with(['images', 'packageType'])->find($booking->umrah_id);

        return view('umrah.invoice', [
            'booking' => $booking,
            'package' => $package,
        ]);
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
        $booking = DB::table('umrah_bookings')->where('booking_code_ref', $booking_ref)->first();
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
            $booking =  DB::table('umrah_bookings')->where('booking_code_ref', request()->get('token'))->first();
                $params = [
                    "booking_status_flag" => "confirmed",
                    "booking_payment_state" => "paid",
                    "booking_payment_gateway" => request()->get('gateway'),
                    "booking_pnr" => strtoupper(uniqid()),
                ];
            DB::table('umrah_bookings')->where('booking_code_ref', request()->get('token'))->update($params);
                $invoice_url = route('umrah.invoice', ['booking_ref' =>  request()->get('token')]);
                return redirect($invoice_url);
        }else{
            return abort(404, 'Booking not found');
        }

    }

}
