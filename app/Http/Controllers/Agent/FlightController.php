<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\FlightBooking;
use App\Models\Location;
use App\Models\TravelPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FlightController extends Controller
{
    public function index()
    {
        return view('agent.flights.index');
    }

    public function search(Request $request)
    {
        $origin        = $request->input('origin');
        $destination   = $request->input('destination');
        $trip          = $request->input('trip', 'oneway');
        $flightType    = $request->input('flight_type', 'economy');
        $departureDate = $request->input('departure_date');
        $returnDate    = $request->input('return_date');
        $adult         = $request->input('adult', 1);
        $child         = $request->input('child', 0);
        $infant        = $request->input('infant', 0);

        $searchParams = compact('origin', 'destination', 'trip', 'flightType', 'departureDate', 'returnDate', 'adult', 'child', 'infant');
        session(['flight_search' => $searchParams]);

        return redirect()->route('agent.flights.results')->with('flight_search', $searchParams);
    }

    public function results(Request $request)
    {
        $searchParams = session('flight_search') ?? $request->all();

        if (empty($searchParams['origin'])) {
            return redirect()->route('agent.flights.index')->with('error', 'Please perform a search first.');
        }

        try {
            $suppliers = TravelPartner::where('status', 'active')->where('supplier_type', 'flight')->get();
            $currency  = activeCurrency();

            $payload = [
                'origin'         => $searchParams['origin'],
                'destination'    => $searchParams['destination'],
                'trip'           => $searchParams['trip'] ?? 'oneway',
                'flight_type'    => $searchParams['flightType'] ?? 'economy',
                'departure_date' => $searchParams['departureDate'],
                'return_date'    => $searchParams['returnDate'] ?? null,
                'adult'          => $searchParams['adult'] ?? 1,
                'child'          => $searchParams['child'] ?? 0,
                'infant'         => $searchParams['infant'] ?? 0,
                'currency'       => $currency->currency_name,
                'env'            => 'dev',
            ];

            $all_flights = [];

            foreach ($suppliers as $supplier) {
                $endpoint = url('/') . '/api/' . $supplier->company_name . '/flight_search';
                try {
                    $response = Http::post($endpoint, array_merge($payload, [
                        'endpoint'         => $endpoint,
                        'api_credential_1' => $supplier->api_credential_1,
                        'api_credential_2' => $supplier->api_credential_2,
                        'api_credential_3' => $supplier->api_credential_3,
                        'api_credential_4' => $supplier->api_credential_4,
                        'api_credential_5' => $supplier->api_credential_5,
                        'api_credential_6' => $supplier->api_credential_6,
                        'commission'       => $supplier->commission_rate,
                    ]));

                    $data = $response->json();
                    if (!empty($data['data']) && is_array($data['data'])) {
                        foreach ($data['data'] as $flight) {
                            $all_flights[] = $flight;
                        }
                    }
                } catch (\Exception $e) {}
            }

            $flights = collect($all_flights)->sortBy('price')->values()->toArray();

            return view('agent.flights.results', compact('flights', 'searchParams'));
        } catch (\Exception $e) {
            return view('agent.flights.results', ['flights' => [], 'error' => $e->getMessage(), 'searchParams' => $searchParams]);
        }
    }

    public function booking(Request $request)
    {
        try {
            $agent       = auth()->user();
            $wallet      = $agent->wallet;
            $countries   = Location::select('country', 'country_code')->distinct()->orderBy('country')->get();
            $searchParams = session('flight_search') ?? [];

            $flightData  = json_decode(decrypt($request->input('flight_data')), true);

            return view('agent.flights.booking', compact('agent', 'wallet', 'countries', 'flightData', 'searchParams'));
        } catch (\Exception $e) {
            return abort(400, 'Invalid flight data.');
        }
    }

    public function confirmBooking(Request $request)
    {
        $agent  = auth()->user();
        $wallet = $agent->wallet;

        try {
            $flightData   = json_decode(decrypt($request->input('flight_data')), true);
            $searchParams = session('flight_search') ?? [];
            $user         = $request->input('user');

            $amount = $flightData['price'] ?? 0;

            if (!$wallet || !$wallet->hasSufficientBalance($amount)) {
                return back()->with('error', 'Insufficient wallet balance. Available: ' .
                    ($wallet ? $wallet->balance : 0) . ', Required: ' . $amount);
            }

            $bookingRef = 'FLT' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

            $travellers = [];
            $adultCount = $searchParams['adult'] ?? 1;
            $childCount = $searchParams['child'] ?? 0;

            for ($i = 1; $i <= $adultCount; $i++) {
                $travellers[] = [
                    'type'       => 'adult',
                    'title'      => $request->input("adult_gender_$i"),
                    'first_name' => $request->input("adult_first_name_$i"),
                    'last_name'  => $request->input("adult_last_name_$i"),
                    'dob'        => $request->input("adult_dob_$i"),
                    'passport'   => $request->input("adult_passport_$i"),
                    'expiry'     => $request->input("adult_expiry_$i"),
                    'nationality'=> $request->input("adult_nationality_$i"),
                ];
            }

            for ($i = 1; $i <= $childCount; $i++) {
                $travellers[] = [
                    'type'       => 'child',
                    'first_name' => $request->input("child_first_name_$i"),
                    'last_name'  => $request->input("child_last_name_$i"),
                    'dob'        => $request->input("child_dob_$i"),
                ];
            }

            $currency = activeCurrency();

            $booking = FlightBooking::create([
                'booking_code_ref'        => $bookingRef,
                'booking_status_flag'     => 'confirmed',
                'booking_fare_base'       => $amount,
                'booking_adult_count'     => $adultCount,
                'booking_child_count'     => $childCount,
                'booking_currency_origin' => $flightData['currency'] ?? $currency->currency_name,
                'booking_payment_state'   => 'paid',
                'booking_data'            => $flightData,
                'booking_supplier_name'   => $flightData['supplier_name'] ?? 'api',
                'booking_user_data'       => $user,
                'booking_guest'           => $travellers,
                'booking_nationality_code'=> $user['country'] ?? null,
                'booking_payment_gateway' => 'agent_wallet',
            ]);

            DB::table('flights_booking')
                ->where('id', $booking->id)
                ->update(['agent_id' => $agent->id, 'booked_via' => 'agent']);

            $wallet->debit(
                $amount,
                'Flight booking: ' . $bookingRef,
                $agent->id,
                'flight',
                $booking->id,
                $bookingRef
            );

            return redirect()->route('agent.flights.invoice', $bookingRef)
                ->with('success', 'Flight booked! Wallet debited: ' . $amount);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function invoice(string $ref)
    {
        $booking = FlightBooking::where('booking_code_ref', $ref)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        return view('agent.flights.invoice', compact('booking'));
    }
}
