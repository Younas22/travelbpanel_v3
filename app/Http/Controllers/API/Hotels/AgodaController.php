<?php

namespace App\Http\Controllers\API\Hotels;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
class AgodaController extends BaseController
{

    private const API_ENDPOINT = 'http://affiliateapi7643.agoda.com/affiliateservice/lt_v1';
    private const DEFAULT_MAX_RESULTS = 30;
    private const DEFAULT_MIN_PRICE = 20;
    private const DEFAULT_MAX_PRICE = 10000;
    private const API_TIMEOUT = 30; // seconds

    /**
     * Search for hotels based on user criteria
     */
    public function hotel_search(Request $request): JsonResponse
    {
        try {
            $validated = $this->validate_input($request);
            $destination = $this->get_destination($validated['city']);

            if (!$destination) {
                return $this->error_response('Destination not found.', 404);
            }

            $params = $this->build_search_params($validated, $destination);
            $response = $this->call_agoda_api($validated, $params);

            if (!$response->successful()) {
                return $this->handle_api_error($response);
            }

            $hotels = $this->parse_hotel_response($response->json(), $validated);

            return $this->success_response([
                'total' => count($hotels),
                'data' => $hotels,
            ], 'Hotels fetched successfully');

        } catch (Exception $e) {
            return $this->error_response(
                'Server Error',
                500,
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Build search parameters for API
     */
    private function build_search_params(array $validated, object $destination): array
    {
        return [
            'criteria' => [
                'additional' => [
                    'currency' => $validated['currency'],
                    'dailyRate' => [
                        'minimum' => self::DEFAULT_MIN_PRICE,
                        'maximum' => self::DEFAULT_MAX_PRICE,
                    ],
                    'discountOnly' => false,
                    'language' => 'en-us',
                    'maxResult' => self::DEFAULT_MAX_RESULTS,
                    'minimumReviewScore' => 0,
                    'minimumStarRating' => 0,
                    'occupancy' => [
                        'numberOfAdult' => (int)$validated['adults'],
                        'numberOfChildren' => (int)($validated['childs'] ?? 0),
                    ],
                    'sortBy' => 'PriceAsc',
                ],
                'checkInDate' => $validated['checkin'],
                'checkOutDate' => $validated['checkout'],
                'cityId' => (int)$destination->continent_name,
            ],
        ];
    }

    /**
     * Call Agoda API with error handling
     */
    private function call_agoda_api(array $validated, array $params)
    {
        try {
            return Http::withHeaders([
                'Authorization' => $validated['api_credential_1'] . ':' . $validated['api_credential_2'],
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
            ])->timeout(self::API_TIMEOUT)
                ->retry(2, 100)
                ->post(self::API_ENDPOINT, $params);

        } catch (Exception $e) {
            throw new Exception('API request failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle API errors
     */
    private function handle_api_error($response): JsonResponse
    {
        $statusCode = $response->status();

        if ($statusCode === 401) {
            return $this->error_response(
                'Authentication failed. Invalid API credentials.',
                401
            );
        }

        if ($statusCode === 429) {
            return $this->error_response(
                'Rate limit exceeded. Please try again later.',
                429
            );
        }

        if ($statusCode >= 500) {
            return $this->error_response(
                'External service temporarily unavailable.',
                503
            );
        }

        return $this->error_response(
            'Failed to fetch hotels from external service.',
            $statusCode,
            ['response' => $response->body()]
        );
    }

    /**
     * Parse and format hotel response data
     */
    private function parse_hotel_response(array $responseData, array $validated): array
    {
        $hotels = [];
        $results = $responseData['results'] ?? [];

        if (empty($results)) {
            return $hotels;
        }

        foreach ($results as $hotel) {
            try {
                $hotels[] = $this->format_hotel_data($hotel, $validated);
            } catch (Exception $e) {
                // Log individual hotel parsing error but continue processing. The
                // logging call itself must never be allowed to fail the whole
                // search (e.g. a storage-permission issue) — that would turn one
                // malformed hotel entry into a complete outage for every user.
                try {
                    \Log::warning('Error parsing hotel data: ' . $e->getMessage(), ['hotel' => $hotel]);
                } catch (\Throwable $logException) {
                    // Swallow — logging is best-effort here.
                }
                continue;
            }
        }

        return $hotels;
    }

    /**
     * Format individual hotel data
     */
    private function format_hotel_data(array $hotel, array $validated): object
    {

        $price = isset($hotel['dailyRate']) ? round($hotel['dailyRate']) : 0;

        // Field set mirrors WebbedsController::hotel_search()'s hotel row shape so
        // HotelController::search()'s aggregation across suppliers can treat every
        // result uniformly regardless of which API it came from.
        return (object)[
            'hotel_id' => $hotel['hotelId'] ?? null,
            'name' => $hotel['hotelName'] ?? '',
            'images' => $hotel['imageURL'] ?? '',
            'all_images' => !empty($hotel['imageURL']) ? [$hotel['imageURL']] : [],
            'stars' => (int)($hotel['starRating'] ?? 0),
            'latitude' => (float)($hotel['latitude'] ?? 0),
            'longitude' => (float)($hotel['longitude'] ?? 0),
            'minRate' => $price,
            'real_price' => $price,
            'actual_price' => $price,
            'currency' => $hotel['currency'] ?? $validated['currency'],
            'original_currency' => $hotel['currency'] ?? $validated['currency'],
            'redirect' => $hotel['landingURL'] ?? null,
            'supplier_name' => 'agoda',
            'address' => ucfirst($validated['city'] ?? ''),
            'location' => ucfirst($validated['city'] ?? ''),
            'room_name' => "",
            'categoryCode' => "",
            'categoryName' => "",
        ];
    }

    /**
     * Retrieve destination by city name
     */
    private function get_destination(string $city)
    {
        try {
            return DB::connection('sqlite_agoda')
                ->table('hotels')
                ->where('city', ucfirst($city))
                ->first();

        } catch (Exception $e) {
            throw new Exception('Database error while fetching destination: ' . $e->getMessage());
        }
    }

    /**
     * Validate incoming request
     */
    private function validate_input(Request $request): array
    {
        $rules = [
            'city' => 'required|string|max:100',
            'checkin' => 'required|date_format:Y-m-d|after_or_equal:today',
            'checkout' => 'required|date_format:Y-m-d|after:checkin',
            'adults' => 'required|integer|min:1|max:20',
            'childs' => 'nullable|integer|min:0|max:20',
            'rooms' => 'required|integer|min:1|max:10',
            'currency' => 'required|string|size:3|regex:/^[A-Z]{3}$/',
            'env' => 'required|in:dev,pro',
            'api_credential_1' => 'required|string|min:5',
            'api_credential_2' => 'required|string|min:5',
            'country_code' => 'nullable|string|size:2|regex:/^[A-Z]{2}$/',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new Exception('Validation Error: ' . json_encode($validator->errors()->toArray()));
        }

        return $validator->validated();
    }

    /**
     * Error response helper
     */
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

    /**
     * Success response helper
     */
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


}
