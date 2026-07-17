<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourInclusion;
use App\Models\TourExclusion;
use App\Models\TourPackageType;
use App\Models\Location;
use App\Models\PaymentGateways;
use App\Models\AgentWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TourController extends Controller
{
    /**
     * Show the tour home page.
     */
    public function index()
    {
        // Fetch featured and popular tours (status=1, featured first, limit to 8)
        $tours = Tour::with(['images', 'packageType'])
            ->where('status', '1')
            ->orderBy('featured', 'desc')
            ->limit(8)
            ->get();

        // Get all inclusions and exclusions for mapping
        $allInclusions = TourInclusion::pluck('name', 'id')->toArray();
        $allExclusions = TourExclusion::pluck('name', 'id')->toArray();

        // Transform tours to include inclusion/exclusion names
        $tours->transform(function ($tour) use ($allInclusions, $allExclusions) {
            // Map inclusion IDs to names
            if ($tour->inclusions) {
                $tour->highlights = collect($tour->inclusions)->map(function ($id) use ($allInclusions) {
                    return $allInclusions[$id] ?? null;
                })->filter()->values()->toArray();
            } else {
                $tour->highlights = [];
            }

            // Map exclusion IDs to names
            if ($tour->exclusions) {
                $tour->exclusion_names = collect($tour->exclusions)->map(function ($id) use ($allExclusions) {
                    return $allExclusions[$id] ?? null;
                })->filter()->values()->toArray();
            } else {
                $tour->exclusion_names = [];
            }

            // Get location name
            if ($tour->loaction) {
                $locationData = DB::table('locations')->where('id', $tour->loaction)->first();
                $tour->location_name = $locationData ? $locationData->city : '';
            } else {
                $tour->location_name = '';
            }

            return $tour;
        });

        return view('tour.tour', compact('tours'));
    }

    /**
     * Handle tour search with parameters.
     */
    public function list(Request $request, $location, $type, $startDate, $endDate, $adult, $child)
    {
        // Format dates from dd-mm-yyyy to Y-m-d for database queries
        $formattedStartDate = date('Y-m-d', strtotime($startDate));
        $formattedEndDate = date('Y-m-d', strtotime($endDate));

        // Get location ID from database
        $locationData = DB::table('locations')->where('city', 'like', '%' . $location . '%')->first();
        $locationId = $locationData ? $locationData->id : null;

        // Build query for tour packages (status=1 only, featured first)
        $query = Tour::with(['images', 'packageType'])
            ->where('status', '1')
            ->orderByRaw('CASE WHEN featured = 1 THEN 0 ELSE 1 END');

        // Filter by location if found
        if ($locationId) {
            $query->where('loaction', $locationId);
        }

        // Filter by package type if provided
        if ($type && $type !== 'all') {
            // Check if type is numeric (ID) or string (package name)
            if (is_numeric($type)) {
                // Get package type name from ID
                $packageType = DB::table('tour_package_type')->where('id', $type)->first();
                if ($packageType) {
                    $query->where('packege_type', $packageType->packege_type);
                }
            } else {
                $query->where('packege_type', $type);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'popular');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'duration':
                $query->orderBy('days', 'asc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            default:
                // popular - already ordered by featured
                break;
        }



        // Paginate results (20 per page)
        $tours = $query->paginate(20);

        // Get all inclusions and exclusions for mapping
        $allInclusions = TourInclusion::pluck('name', 'id')->toArray();
        $allExclusions = TourExclusion::pluck('name', 'id')->toArray();

        // Transform tours to include inclusion/exclusion names
        $tours->getCollection()->transform(function ($tour) use ($allInclusions, $allExclusions) {
            // Map inclusion IDs to names
            if ($tour->inclusions) {
                $tour->highlights = collect($tour->inclusions)->map(function ($id) use ($allInclusions) {
                    return $allInclusions[$id] ?? null;
                })->filter()->values()->toArray();
            } else {
                $tour->highlights = [];
            }

            // Map exclusion IDs to names
            if ($tour->exclusions) {
                $tour->exclusion_names = collect($tour->exclusions)->map(function ($id) use ($allExclusions) {
                    return $allExclusions[$id] ?? null;
                })->filter()->values()->toArray();
            } else {
                $tour->exclusion_names = [];
            }

            // Get location name
            $tour->location = $tour->location_name ?? $tour->loaction;

            return $tour;
        });

        // Get tour type name for session
        $typeName = 'All tour';
        if ($type && $type !== 'all') {
            if (is_numeric($type)) {
                $typeData = DB::table('tour_package_type')->where('id', $type)->first();
                $typeName = $typeData ? $typeData->packege_type : $type;
            } else {
                $typeName = $type;
            }
        }

        // Prepare search parameters
        $searchParams = [
            'location' => $location,
            'location_name' => $locationData ? $locationData->city : ucfirst($location),
            'location_country' => $locationData ? ($locationData->country ?? '') : '',
            'type' => $type,
            'type_name' => $typeName,
            'start_date' => $formattedStartDate,
            'end_date' => $formattedEndDate,
            'adult' => (int) $adult,
            'child' => (int) $child,
            'original_start_date' => $startDate,
            'original_end_date' => $endDate,
        ];

        // Store in session for later use
        session(['tour_search' => $searchParams]);

        return view('tour.list', compact(
            'tours',
            'searchParams',
            'sortBy',
            'locationData'
        ));
    }

    /**
     * Show the tour details page.
     */
    public function details($slug, $location, $type, $startDate, $endDate, $adult, $child)
    {
        // Find tour by slug (name with spaces replaced by dashes)
        $tour = Tour::with(['images', 'packageType'])
            ->where('status', '1')
            ->get()
            ->first(function ($t) use ($slug) {
                return \Str::slug($t->name) === $slug;
            });

        if (!$tour) {
            abort(404);
        }

        // Get all inclusions and exclusions for mapping
        $allInclusions = TourInclusion::pluck('name', 'id')->toArray();
        $allExclusions = TourExclusion::pluck('name', 'id')->toArray();

        // Map inclusion IDs to names
        if ($tour->inclusions) {
            $tour->inclusion_names = collect($tour->inclusions)->map(function ($id) use ($allInclusions) {
                return $allInclusions[$id] ?? null;
            })->filter()->values()->toArray();
        } else {
            $tour->inclusion_names = [];
        }

        // Map exclusion IDs to names
        if ($tour->exclusions) {
            $tour->exclusion_names = collect($tour->exclusions)->map(function ($id) use ($allExclusions) {
                return $allExclusions[$id] ?? null;
            })->filter()->values()->toArray();
        } else {
            $tour->exclusion_names = [];
        }

        // Get location name
        $locationData = DB::table('locations')->where('city', 'like', '%' . $location . '%')->first();

        // Prepare search parameters
        $searchParams = [
            'location' => $location,
            'type' => $type,
            'start_date' => date('Y-m-d', strtotime($startDate)),
            'end_date' => date('Y-m-d', strtotime($endDate)),
            'adult' => (int) $adult,
            'child' => (int) $child,
            'original_start_date' => $startDate,
            'original_end_date' => $endDate,
        ];

        return view('tour.details', [
            'tour' => $tour,
            'searchParams' => $searchParams,
            'locationData' => $locationData,
        ]);
    }

    /**
     * Show the tour booking page.
     */
    public function booking($slug, $location, $type, $startDate, $endDate, $adult, $child)
    {
        $countries = Location::select('country', 'country_code')->distinct()->orderBy('country', 'asc')->get();
        // Find tour by slug (name with spaces replaced by dashes)
        $tour = Tour::with(['images', 'packageType'])
            ->where('status', '1')
            ->get()
            ->first(function ($t) use ($slug) {
                return \Str::slug($t->name) === $slug;
            });

        if (!$tour) {
            abort(404);
        }

        // Get all inclusions and exclusions for mapping
        $allInclusions = TourInclusion::pluck('name', 'id')->toArray();
        $allExclusions = TourExclusion::pluck('name', 'id')->toArray();

        // Map inclusion IDs to names
        if ($tour->inclusions) {
            $tour->inclusion_names = collect($tour->inclusions)->map(function ($id) use ($allInclusions) {
                return $allInclusions[$id] ?? null;
            })->filter()->values()->toArray();
        } else {
            $tour->inclusion_names = [];
        }

        // Map exclusion IDs to names
        if ($tour->exclusions) {
            $tour->exclusion_names = collect($tour->exclusions)->map(function ($id) use ($allExclusions) {
                return $allExclusions[$id] ?? null;
            })->filter()->values()->toArray();
        } else {
            $tour->exclusion_names = [];
        }

        // Get location name
        if ($tour->loaction) {
            $locationData = DB::table('locations')->where('id', $tour->loaction)->first();
            $tour->location_name = $locationData ? $locationData->city : '';
        }

        // Prepare search parameters
        $searchParams = [
            'location' => $location,
            'type' => $type,
            'start_date' => date('Y-m-d', strtotime($startDate)),
            'end_date' => date('Y-m-d', strtotime($endDate)),
            'adult' => (int) $adult,
            'child' => (int) $child,
            'original_start_date' => $startDate,
            'original_end_date' => $endDate,
        ];

        $active_currency = activeCurrency();
        $tour_price = convertCurrency($tour->price, $tour->currency ?? 'USD', $active_currency->currency_name);
        // Prepare tour data for booking
        $tour_data = [
            'tour_id' => $tour->id,
            'tour_name' => $tour->name,
            'price' => $tour_price,
            'child_price' => convertCurrency($tour->child_price, $tour->currency ?? 'USD', $active_currency->currency_name) ?? ($tour_price * 0.7),
            'currency' => $active_currency->currency_name ?? 'USD',
            'duration' => $tour->duration,
            'days' => $tour->days,
        ];

        $payment_gateways = PaymentGateways::all()->where('status', '1');

        return view('tour.booking', [
            'countries' => $countries,
            'tour' => $tour,
            'searchParams' => $searchParams,
            'tour_data' => $tour_data,
            'payment' => $payment_gateways,
            'agentWallet' => auth()->check() && auth()->user()->isAgent() ? auth()->user()->wallet : null,
        ]);
    }

    /**
     * Store the tour booking.
     */
    public function storeBooking(Request $request)
    {
        // Decrypt tour data and search params
        $tourData = json_decode(decrypt($request->tour_data), true);
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

        // Get tour details
        $tour = Tour::find($tourData['tour_id']);
        $locationData = null;
        if ($tour && $tour->loaction) {
            $locationData = DB::table('locations')->where('id', $tour->loaction)->first();
        }

        // Calculate prices
        $adultPrice = $tourData['price'];
        $childPrice = $tourData['child_price'] ?? ($adultPrice * 0.7);
        $totalPrice = ($adultPrice * $adultCount) + ($childPrice * $childCount);

        // Generate booking reference
        $bookingRef = 'TUR' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        // Prepare booking data for storage
        $bookingDataJson = json_encode([
            'tour_data' => $tourData,
            'search_params' => $searchParams,
        ]);

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

        // Insert into tours_booking table
        DB::table('tours_booking')->insert([
            'booking_code_ref' => $bookingRef,
            'booking_status_flag' => 'pending',
            'tour_id' => $tourData['tour_id'],
            'tour_name' => $tourData['tour_name'],
            'tour_location' => $locationData ? $locationData->city : ($searchParams['location'] ?? null),
            'tour_type' => $tour && $tour->packageType ? $tour->packageType->packege_type : null,
            'departure_date' => $searchParams['start_date'],
            'return_date' => $searchParams['end_date'],
            'tour_duration' => $tourData['duration'] ?? null,
            'tour_days' => $tourData['days'] ?? null,
            'booking_fare_base' => $totalPrice,
            'booking_adult_count' => $adultCount,
            'booking_child_count' => $childCount,
            'booking_adult_price' => $adultPrice,
            'booking_child_price' => $childPrice,
            'booking_total_price' => $totalPrice,
            'booking_currency_origin' => $tourData['currency'],
            'booking_data' => $bookingDataJson,
            'booking_payment_state' => 'unpaid',
            'booking_payment_gateway' => $request->input('accept_payment'),
            'booking_user_data' => json_encode($userData),
            'booking_guest' => json_encode($travellers),
            'booking_supplier_name' => "tour",
            'booking_nationality_code' => $userData['country'] ?? null,
            'agent_id' => $agentId,
            'user_id' => $userId,
            'booked_via' => $bookedVia,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Debit agent wallet if selected
        if ($request->input('accept_payment') === 'agent_wallet' && $agentId) {
            $tourBooking = DB::table('tours_booking')->where('booking_code_ref', $bookingRef)->first();
            $agentWallet = $agentWallet ?? AgentWallet::where('agent_id', $agentId)->first();
            $agentWallet->debit(
                amount:      $totalPrice,
                note:        'Tour booking #' . $bookingRef,
                performedBy: $agentId,
                bookingType: 'tour',
                bookingId:   $tourBooking?->id,
                reference:   $bookingRef,
            );
            DB::table('tours_booking')->where('booking_code_ref', $bookingRef)
                ->update(['booking_payment_state' => 'paid']);
        }

        // Redirect to invoice page
        return redirect()->route('tour.invoice', ['booking_ref' => $bookingRef]);
    }

    /**
     * Show the tour invoice page.
     */
    public function invoice($bookingRef)
    {
        // Get booking from database
        $booking = DB::table('tours_booking')
            ->where('booking_code_ref', $bookingRef)
            ->first();

        if (!$booking) {
            abort(404, 'Booking not found');
        }

        // Convert to object with Carbon date
        $booking = (object) array_merge((array) $booking, [
            'created_at' => \Carbon\Carbon::parse($booking->created_at),
            'updated_at' => \Carbon\Carbon::parse($booking->updated_at),
        ]);

        return view('tour.invoice', compact('booking'));
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
        $booking = DB::table('tours_booking')->where('booking_code_ref', $booking_ref)->first();
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
            $booking =  DB::table('tours_booking')->where('booking_code_ref', request()->get('token'))->first();
            $params = [
                "booking_status_flag" => "confirmed",
                "booking_payment_state" => "paid",
                "booking_payment_gateway" => request()->get('gateway'),
                "booking_pnr" => strtoupper(uniqid()),
            ];
            DB::table('tours_booking')->where('booking_code_ref', request()->get('token'))->update($params);
            $invoice_url = route('tour.invoice', ['booking_ref' =>  request()->get('token')]);
            return redirect($invoice_url);
        }else{
            return abort(404, 'Booking not found');
        }

    }
}
