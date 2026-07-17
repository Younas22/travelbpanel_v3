<?php

namespace App\Http\Controllers\API\Flights;


use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DuffelController extends BaseController
{

    /**
     * Search flights via Duffel and return a normalized response.
     *
     * Expects `sendResponse()` / `sendError()` helpers to be available
     * (e.g. from AppBaseController / ApiResponser trait).
     */
    public function flight_search(Request $request)
    {
        try {
            $input = $request->all();


            $validator = Validator::make($input, [
                'origin' => 'required|string',
                'destination' => 'required|string',
                'type' => 'required|in:oneway,round',
                'departure_date' => 'required|date',
                'return_date' => '',
                'adults' => 'required|integer|min:1',
                'children' => 'nullable|integer|min:0',
                'infants' => 'nullable|integer|min:0',
                'class' => 'required|in:economy,premium_economy,business,first',
                'currency' => 'required|string|size:3',
                'api_credential_1' => 'required|string', // keys
                'env' => 'required|in:dev,pro',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $formattedResults = $this->route_data($input);

            if (!empty($formattedResults)) {
                $flightData = array_slice($formattedResults, 0, 200);
                return $this->sendResponse($flightData, 'Successfully retrieved flights.');
            } else {
                return $this->sendResponse(['msg' => 'no_result'], 'No flights found.');
            }

        } catch (Exception $e) {
            return $this->sendError('Server Error', ['msg' => $e->getMessage()]);
        }
    }

    /**
     * Create a Duffel order (booking) for a previously created offer.
     */
    public function booking(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'booking_data'     => 'required',
                'user_data'        => 'required',
                'guest'            => 'required',
                'env'              => 'required',
                'api_credential_1' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $input = $request->all();

            $bookingData = json_decode($input['booking_data']);
            $userData    = json_decode($input['user_data']);
            $guestInfo   = json_decode($input['guest']) ?: [];
            $apiCredential = $input['api_credential_1'];


            $pit = !empty($input['pit_id'])
                ? $input['pit_id']
                : 'pit_00009h' . ($input['booking_ref_no'] ?? '');

            $payload = [
                'data' => [
                    'type' => 'instant',
                    'selected_offers' => [
                        $bookingData->booking_token,
                    ],
                    'payments' => [[
                        'type'     => 'balance',
                        'currency' => $bookingData->currency,
                        'amount'   => $bookingData->amount,
                    ]],
                    'passengers' => $this->buildBookingPassengers($guestInfo, $userData, $bookingData),
                    'metadata'   => [
                        'payment_intent_id' => $pit,
                    ],
                ],
            ];


            $response = Http::withHeaders([
                'Accept'         => 'application/json',
                'Content-Type'   => 'application/json',
                'Duffel-Version' => 'v2',
                'Authorization'  => 'Bearer ' . $apiCredential,
            ])->post('https://api.duffel.com/air/orders', $payload);

            $bookingRes = $response->body();

            if (!empty($bookingRes)) {
                $decodeBookingRes = json_decode($bookingRes);

                if (isset($decodeBookingRes->errors)) {
                    return $this->sendResponse([[
                        'status'         => true,
                        'response'       => '',
                        'Prn'            => '',
                        'response_error' => $decodeBookingRes->errors,
                    ]], 'false.');
                }

                $pnr = $decodeBookingRes->data?->booking_reference
                    ?? $decodeBookingRes->data?->associatedRecords[0]?->reference
                    ?? null;

                return $this->sendResponse([[
                    'status'         => true,
                    'response'       => $decodeBookingRes,
                    'Prn'            => $pnr,
                    'response_error' => '',
                ]], 'successfully.');
            }

            return $this->sendResponse([[
                'status'   => false,
                'response' => 0,
                'msg'      => 'something is wrong please check your request',
            ]], 'false.');

        } catch (Exception $e) {
            return $this->sendError('Server Error', ['msg' => $e->getMessage()]);
        }
    }

    /**
     * Build the Duffel "passengers" array for the order payload from the
     * decoded guest list, user contact details, and the offer's booking data.
     */
    protected function buildBookingPassengers(array $guests, $userData, $bookingData): array
    {
        $phoneNumber ='+447874472356'; // Default phone number if not provided

        $typeMap = [
            'adults'   => 'adult',
            'children' => 'child',
            'infants'  => 'infant_without_seat',
        ];

        $passengers = [];

        foreach ($guests as $guest) {
            $gender = 'm';
            $type = $typeMap[$guest->traveller_type] ?? $guest->traveller_type;

            $passengers[] = [
                'type'         => $type,
                'title'        => 'mr',
                'phone_number' => $phoneNumber,
                'id'           => $bookingData->passenger_id,
                'given_name'   => $guest->first_name,
                'gender'       => $gender,
                'family_name'  => $guest->last_name,
                'email'        => $userData->user_email,
                'born_on'      => Carbon::create(
                    (int) $guest->dob_year,
                    (int) $guest->dob_month,
                    (int) $guest->dob_day
                )->format('Y-m-d'),
            ];
        }

        return $passengers;
    }


    /**
     * Call Duffel and return the normalized list of flight offers.
     * Returns an empty array if nothing is found or the API call fails.
     */
    protected function route_data(array $input): array
    {
        $payload = $this->buildPayload($input);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Duffel-Version' => 'v2',
            'Authorization' => 'Bearer ' . $input['api_credential_1'],
        ])->post('https://api.duffel.com/air/offer_requests', $payload);

        if ($response->failed()) {
            return [];
        }

        $decoded = $response->json();

        if (empty($decoded['data']['offers'])) {
            return [];
        }

        $offers = $decoded['data']['offers'];
        $result = [];

        foreach ($offers as $offer) {
            $result[] = [
                'segments' => $this->mapOffer($offer, $input),
            ];
        }

        return $result;
    }

    /**
     * Build the Duffel offer_requests payload from validated input.
     */
    protected function buildPayload(array $input): array
    {
        $departureDate = Carbon::parse($input['departure_date'])->format('Y-m-d');

        $slices = [[
            'departure_date' => $departureDate,
            'destination'    => $input['destination'],
            'origin'         => $input['origin'],
        ]];

        if ($input['type'] === 'round' && !empty($input['return_date'])) {
            $returnDate = Carbon::parse($input['return_date'])->format('Y-m-d');

            $slices[] = [
                'departure_date' => $returnDate,
                'destination'    => $input['origin'],
                'origin'         => $input['destination'],
            ];
        }

        $passengers = [];

        for ($i = 0; $i < $input['adults']; $i++) {
            $passengers[] = ['type' => 'adult'];
        }

        for ($i = 0; $i < ($input['children'] ?? 0); $i++) {
            $passengers[] = ['type' => 'child'];
        }

        for ($i = 0; $i < ($input['infants'] ?? 0); $i++) {
            $passengers[] = ['type' => 'infant_without_seat'];
        }

        return [
            'data' => [
                'cabin_class' => $input['class'],
                'slices'      => $slices,
                'passengers'  => $passengers,
            ],
        ];
    }

    /**
     * Map a single Duffel offer into an array of slices, each containing
     * an array of normalized segments.
     */
    protected function mapOffer(array $offer, array $input): array
    {
        $segments = [];

        foreach ($offer['slices'] as $slice) {
            $sliceSegments = [];
            $totalDuration = $this->calculateSliceDuration($slice['segments']);

            foreach ($slice['segments'] as $seg) {
                $sliceSegments[] = $this->mapSegment($seg, $offer, $slice, $input, $totalDuration);
            }

            $segments[] = $sliceSegments;
        }

        return $segments;
    }

    /**
     * Map a single Duffel segment into the normalized segment structure.
     */
    protected function mapSegment(array $seg, array $offer, array $slice, array $input, string $totalDuration): array
    {
        [$baggage, $cabinBaggage] = $this->extractBaggage($seg);

        $totalAmount = number_format((float) ($offer['total_amount'] ?? 0), 2, '.', '');

        return [
            'id'              => $seg['id'] ?? $offer['id'] ?? uniqid(),
            'flight_number'   => $seg['operating_carrier_flight_number']
                ?? $seg['marketing_carrier_flight_number']
                    ?? null,
            'airline_name'    => $seg['operating_carrier']['name'] ?? null,
            'departure'       => [
                'airport'      => $seg['origin']['iata_code'] ?? null,
                'city'         => $seg['origin']['iata_city_code'] ?? null,
                'city_name'    => $seg['origin']['city_name'] ?? null,
                'airport_name' => $seg['origin']['name'] ?? null,
                'country'      => $seg['origin']['iata_country_code'] ?? null,
                'time'         => Carbon::parse($seg['departing_at'])->format('h:i A'),
                'booking_time' => $seg['departing_at'],
                'date_convert' => Carbon::parse($seg['departing_at'])->format('D d M Y'),
                'terminal'     => $seg['origin_terminal'] ?? null,
            ],
            'arrival'         => [
                'airport'      => $seg['destination']['iata_code'] ?? null,
                'city'         => $seg['destination']['iata_city_code'] ?? null,
                'city_name'    => $seg['destination']['city_name'] ?? null,
                'country'      => $seg['destination']['iata_country_code'] ?? null,
                'airport_name' => $seg['destination']['name'] ?? null,
                'time'         => Carbon::parse($seg['arriving_at'])->format('h:i A'),
                'booking_time' => $seg['arriving_at'],
                'date'         => Carbon::parse($seg['arriving_at'])->format('Y-m-d'),
                'date_convert' => Carbon::parse($seg['arriving_at'])->format('D d M Y'),
                'terminal'     => $seg['destination_terminal'] ?? null,
            ],
            'carrier'         => [
                'marketing' => $seg['marketing_carrier']['iata_code'] ?? null,
                'operating' => $seg['operating_carrier']['iata_code'] ?? null,
                'alliances' => $seg['operating_carrier']['alliances'] ?? null,
            ],
            'equipment'       => $seg['aircraft']['name'] ?? null,
            'total_duration'  => $totalDuration,
            'duration'        => $this->formatDuration($seg['duration'] ?? null),
            'distance'        => null,
            'eTicketable'     => true,
            'frequency'       => null,
            'stop_count'      => max(count($slice['segments']) - 1, 0),
            'class'           => $seg['passengers'][0]['cabin_class'] ?? ($input['class'] ?? ''),
            'baggage'         => $baggage,
            'cabin_baggage'   => $cabinBaggage,
            'currency'        => $offer['base_currency'] ?? $input['currency'] ?? null,
            'price'           => $totalAmount,
            'adult_price'     => $totalAmount,
            'child_price'     => $totalAmount,
            'infant_price'    => $totalAmount,
            'options'         => '',
            'booking_data'    => [
                'svc_id'        => $offer['owner']['id'] ?? null,
                'passenger_id'  => $seg['passengers'][0]['passenger_id'] ?? null,
                'booking_token' => $offer['id'] ?? null,
                'currency'      => $offer['base_currency'] ?? null,
                'amount'        => $offer['total_amount'] ?? null,
            ],
            'redirect_url'    => '',
            'refundable'      => '',
            'supplier'        => 'duffel',
            'type'            => $input['type'],
        ];
    }

    /**
     * Sum the flight time of every segment in a slice (excluding layover
     * time), e.g. "5:30".
     */
    protected function calculateSliceDuration(array $segments): string
    {
        $totalMinutes = 0;

        foreach ($segments as $segment) {
            $totalMinutes += Carbon::parse($segment['departing_at'])
                ->diffInMinutes(Carbon::parse($segment['arriving_at']));
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return $hours . ':' . $minutes;
    }

    /**
     * Convert an ISO 8601 duration (e.g. "PT5H30M") to "5h 30m".
     */
    protected function formatDuration(?string $isoDuration): ?string
    {
        if (!$isoDuration) {
            return null;
        }

        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $isoDuration, $matches);

        $hours = $matches[1] ?? 0;
        $minutes = $matches[2] ?? 0;

        return $hours . 'h ' . $minutes . 'm';
    }

    /**
     * Extract checked / cabin baggage allowance counts from a segment's
     * first passenger.
     */
    protected function extractBaggage(array $seg): array
    {
        $checked = 0;
        $cabin = 0;

        foreach ($seg['passengers'][0]['baggages'] ?? [] as $bag) {
            if (($bag['type'] ?? '') === 'checked') {
                $checked += $bag['quantity'] ?? 0;
            } elseif (($bag['type'] ?? '') === 'carry_on') {
                $cabin += $bag['quantity'] ?? 0;
            }
        }

        return [$checked, $cabin];
    }
}
