<?php

namespace App\Http\Middleware;

use App\Services\WebbedsLogger;
use Closure;
use Illuminate\Http\Request;

/**
 * Logs every WebBeds API endpoint call (input + final result) to the WebBeds
 * logs (admin → Suppliers → WebBeds → Logs). The raw XML calls are logged by
 * WebbedsController itself.
 */
class WebbedsApiLog
{
    private const STEPS = [
        'hotel_search'         => 'search',
        'hotel_details'        => 'details',
        'hotel_getrooms_block' => 'block',
        'hotel_booking'        => 'booking',
        'cancel_booking'       => 'cancel',
        'test_credentials'     => 'test',
    ];

    public function handle(Request $request, Closure $next)
    {
        $step  = self::STEPS[basename($request->path())] ?? basename($request->path());
        $input = $request->except(['api_credential_1', 'api_credential_2', 'api_credential_3', 'api_credential_4', 'api_credential_5', 'api_credential_6']);
        WebbedsLogger::write($step, 'Request', ['Input' => $input]);

        $started  = microtime(true);
        $response = $next($request);

        $body  = json_decode((string) $response->getContent(), true);
        $ok    = $response->getStatusCode() < 400 && (($body['success'] ?? true) !== false);
        $error = $ok ? null : (string) ($body['message'] ?? ('HTTP ' . $response->getStatusCode()));
        if (!$ok && !empty($body['exception'])) $error .= ' — ' . $body['exception'];
        if (!$ok && !empty($body['errors'])) $error .= ' — ' . json_encode($body['errors']);

        // Keep the result short for search (thousands of hotels); full for the other steps
        $result = is_array($body) && $step === 'search' && isset($body['data'])
            ? ['success' => $body['success'] ?? null, 'hotels' => is_countable($body['data']) ? count($body['data']) : null, 'message' => $body['message'] ?? null]
            : ($body ?? (string) $response->getContent());

        WebbedsLogger::write($step, 'Result', [
            'HTTP'        => (string) $response->getStatusCode() . ' in ' . round(microtime(true) - $started, 2) . 's',
            'Response'    => $result,
            'booking_pnr' => $body['booking_pnr'] ?? null,
        ], $error);

        return $response;
    }
}
