<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\RateLimitException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Turns a free-text request ("Lahore to Dubai next Friday, 2 adults, business")
 * into the parameters of the existing /flights/... search URL.
 *
 * Claude does the extraction when ANTHROPIC_API_KEY is configured; otherwise
 * (or if the API call fails) a built-in rule-based parser handles English
 * phrasing. Either way every value is validated against the airports table
 * and the date/passenger limits before a URL is built.
 */
class AiFlightSearch
{
    private const CABINS = ['economy', 'premium_economy', 'business', 'first'];

    /** Cities served by several airports -> the one travellers usually mean. */
    private const MAIN_AIRPORTS = [
        'london' => 'LHR', 'istanbul' => 'IST', 'new york' => 'JFK', 'paris' => 'CDG', 'dubai' => 'DXB',
        'tokyo' => 'HND', 'milan' => 'MXP', 'moscow' => 'SVO', 'bangkok' => 'BKK', 'jakarta' => 'CGK',
        'washington' => 'IAD', 'chicago' => 'ORD', 'toronto' => 'YYZ', 'seoul' => 'ICN', 'shanghai' => 'PVG',
        'beijing' => 'PEK', 'rome' => 'FCO', 'stockholm' => 'ARN', 'osaka' => 'KIX', 'sao paulo' => 'GRU',
        'buenos aires' => 'EZE', 'kuala lumpur' => 'KUL', 'houston' => 'IAH', 'montreal' => 'YUL',
        'manchester' => 'MAN', 'berlin' => 'BER', 'los angeles' => 'LAX', 'san francisco' => 'SFO',
    ];

    private const NUMBER_WORDS = [
        'a' => 1, 'an' => 1, 'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
        'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9,
    ];

    /**
     * @return array{success: bool, message?: string, url?: string, summary?: array}
     */
    public function search(string $query): array
    {
        $query = trim(preg_replace('/\s+/u', ' ', $query));

        $parsed = null;
        $source = 'rules';
        if (config('services.anthropic.key')) {
            $parsed = $this->parseWithClaude($query);
            if ($parsed !== null) {
                $source = 'ai';
            }
        }
        $parsed ??= $this->parseWithRules($query);

        return $this->buildResult($parsed, $source);
    }

    // ------------------------------------------------------------------
    // Claude extraction
    // ------------------------------------------------------------------

    private function parseWithClaude(string $query): ?array
    {
        $client = new Client(apiKey: config('services.anthropic.key'));
        $today = Carbon::today();

        $system = "You convert a traveller's flight request into search fields for a flight booking website. "
            . "Today is {$today->format('l, Y-m-d')}. Resolve relative dates (tomorrow, next Friday, 5 March) against today; "
            . 'a date without a year is the next upcoming occurrence. '
            . 'For origin and destination give the 3-letter IATA code of the main airport when you know it, otherwise the city name; use null when the request does not say. '
            . 'Leave departure_date null if no date is given. Set trip_type to "round" only when a return date or trip length is given or a return is asked for. '
            . 'Default to 1 adult, 0 children, 0 infants and economy when not stated. The request may be in any language.';

        $nullableString = ['type' => ['string', 'null']];
        $schema = [
            'type' => 'object',
            'properties' => [
                'origin' => $nullableString,
                'destination' => $nullableString,
                'departure_date' => ['type' => ['string', 'null'], 'description' => 'YYYY-MM-DD'],
                'return_date' => ['type' => ['string', 'null'], 'description' => 'YYYY-MM-DD'],
                'trip_type' => ['type' => 'string', 'enum' => ['oneway', 'round']],
                'cabin' => ['type' => 'string', 'enum' => self::CABINS],
                'adults' => ['type' => 'integer'],
                'children' => ['type' => 'integer'],
                'infants' => ['type' => 'integer'],
            ],
            'required' => ['origin', 'destination', 'departure_date', 'return_date', 'trip_type', 'cabin', 'adults', 'children', 'infants'],
            'additionalProperties' => false,
        ];

        try {
            $message = $client->messages->create(
                model: config('services.anthropic.model'),
                maxTokens: 4096,
                system: $system,
                messages: [['role' => 'user', 'content' => $query]],
                outputConfig: [
                    'effort' => 'low',
                    'format' => ['type' => 'json_schema', 'schema' => $schema],
                ],
            );
        } catch (RateLimitException $e) {
            Log::warning('AI flight search rate limited: ' . $e->getMessage());
            return null;
        } catch (APIStatusException $e) {
            Log::warning('AI flight search API error: ' . $e->getMessage());
            return null;
        } catch (APIConnectionException $e) {
            Log::warning('AI flight search connection error: ' . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
            Log::warning('AI flight search failed: ' . $e->getMessage());
            return null;
        }

        if ($message->stopReason !== 'end_turn') {
            return null;
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $data = json_decode($block->text, true);
                return is_array($data) ? $data : null;
            }
        }

        return null;
    }

    // ------------------------------------------------------------------
    // Rule-based fallback (English)
    // ------------------------------------------------------------------

    private function parseWithRules(string $query): array
    {
        $lower = mb_strtolower($query);
        [$origin, $destination] = $this->findPlaces($query);
        $dates = $this->findDates($lower);

        $departure = $dates[0] ?? null;
        $return = $dates[1] ?? null;

        // "for 5 days" / "for a week" sets the return date from the departure date
        if (!$return && $departure && preg_match('/\bfor\s+(\d+|' . $this->numberWordPattern() . ')\s+(day|night|week)s?\b/', $lower, $m)) {
            $n = $this->toNumber($m[1]);
            $return = Carbon::parse($departure)->addDays($m[2] === 'week' ? $n * 7 : $n)->toDateString();
        }

        $isOneWay = (bool) preg_match('/\bone[\s-]?way\b/', $lower);
        $wantsReturn = (bool) preg_match('/\b(round[\s-]?trip|return|returning|back)\b/', $lower);

        $cabin = 'economy';
        if (preg_match('/\bpremium(\s+economy)?\b/', $lower)) {
            $cabin = 'premium_economy';
        } elseif (preg_match('/\bbusiness\b/', $lower)) {
            $cabin = 'business';
        } elseif (preg_match('/\bfirst(\s+class)?\b/', $lower) && !preg_match('/\bfirst\s+(week|day)\b/', $lower)) {
            $cabin = 'first';
        }

        $num = '(\d+|' . $this->numberWordPattern() . ')';
        $adults = preg_match("/\\b{$num}\\s+(adult|adults|passenger|passengers|people|persons|person|travell?ers?|pax)\\b/", $lower, $m) ? $this->toNumber($m[1]) : 1;
        $children = preg_match("/\\b{$num}\\s+(child|children|kids?)\\b/", $lower, $m) ? $this->toNumber($m[1]) : 0;
        $infants = preg_match("/\\b{$num}\\s+(infants?|bab(y|ies))\\b/", $lower, $m) ? $this->toNumber($m[1]) : 0;

        return [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $departure,
            'return_date' => $isOneWay ? null : $return,
            'trip_type' => !$isOneWay && ($return || $wantsReturn) ? 'round' : 'oneway',
            'cabin' => $cabin,
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
        ];
    }

    /**
     * Finds airport mentions in reading order. "from X" marks the origin and
     * "to Y" the destination; otherwise the first place is the origin.
     *
     * @return array{0: ?string, 1: ?string} airport codes
     */
    private function findPlaces(string $query): array
    {
        preg_match_all('/[\p{L}]+/u', $query, $m, PREG_OFFSET_CAPTURE);
        $words = $m[0];
        $found = [];

        for ($i = 0; $i < count($words); $i++) {
            // longest phrase first so "New York" wins over "York"
            for ($len = 3; $len >= 1; $len--) {
                if ($i + $len > count($words)) {
                    continue;
                }
                $phrase = implode(' ', array_column(array_slice($words, $i, $len), 0));
                $code = $this->lookupAirport($phrase, $len === 1 && preg_match('/^[A-Z]{3}$/', $phrase));
                if ($code) {
                    $before = mb_strtolower($words[$i - 1][0] ?? '');
                    $found[] = ['code' => $code, 'role' => $before === 'from' ? 'origin' : (in_array($before, ['to', 'for']) ? 'destination' : null)];
                    $i += $len - 1;
                    break;
                }
            }
        }

        $origin = collect($found)->firstWhere('role', 'origin')['code'] ?? null;
        $destination = collect($found)->firstWhere('role', 'destination')['code'] ?? null;
        foreach ($found as $place) {
            if ($place['code'] === $origin || $place['code'] === $destination) {
                continue;
            }
            if (!$origin) {
                $origin = $place['code'];
            } elseif (!$destination) {
                $destination = $place['code'];
            }
        }

        return [$origin, $destination];
    }

    private function lookupAirport(string $phrase, bool $mayBeCode): ?string
    {
        static $stopWords = ['to', 'from', 'on', 'for', 'and', 'the', 'next', 'this', 'with', 'in', 'at', 'by', 'me', 'my', 'flight', 'flights', 'return', 'back', 'one', 'way', 'trip', 'round'];
        if (mb_strlen($phrase) < 3 || in_array(mb_strtolower($phrase), $stopWords, true)) {
            return null;
        }

        if ($mayBeCode) {
            $code = DB::table('flights_airports')->where('status', 1)->where('code', $phrase)->value('code');
            if ($code) {
                return $code;
            }
        }

        return $this->airportForCity($phrase);
    }

    /**
     * Main airport for a city name: a known hub for cities with several
     * airports, otherwise a real airport (not a rail station or heliport),
     * preferring international ones.
     */
    private function airportForCity(string $city): ?string
    {
        $hub = self::MAIN_AIRPORTS[mb_strtolower($city)] ?? null;
        if ($hub && DB::table('flights_airports')->where('status', 1)->where('code', $hub)->exists()) {
            return $hub;
        }

        return DB::table('flights_airports')
            ->where('status', 1)
            ->where('city', $city)
            ->where('airport', '!=', 'All Airports')
            ->orderByRaw("CASE WHEN type = 'airport' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN airport LIKE '%International%' THEN 0 WHEN airport LIKE '%Heliport%' THEN 2 ELSE 1 END")
            ->orderBy('id')
            ->value('code');
    }

    /** @return string[] dates (Y-m-d) in the order they appear */
    private function findDates(string $lower): array
    {
        $today = Carbon::today();
        $found = [];
        $months = 'jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:t(?:ember)?)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?';
        $weekdays = 'monday|tuesday|wednesday|thursday|friday|saturday|sunday';

        $patterns = [
            '/\bday after tomorrow\b/' => fn () => $today->copy()->addDays(2),
            '/\btomorrow\b/' => fn () => $today->copy()->addDay(),
            '/\btoday\b/' => fn () => $today->copy(),
            '/\bnext week\b/' => fn () => $today->copy()->addWeek(),
            '/\bnext month\b/' => fn () => $today->copy()->addMonthNoOverflow(),
            '/\bin\s+(\d+)\s+days?\b/' => fn ($m) => $today->copy()->addDays((int) $m[1]),
            "/\\b(?:next|this|on)?\\s*({$weekdays})\\b/" => fn ($m) => $today->copy()->next(ucfirst($m[1])),
            '/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/' => fn ($m) => $this->safeDate((int) $m[1], (int) $m[2], (int) $m[3]),
            '/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})\b/' => fn ($m) => $this->safeDate((int) (strlen($m[3]) === 2 ? '20' . $m[3] : $m[3]), (int) $m[2], (int) $m[1]),
            "/\\b(\\d{1,2})(?:st|nd|rd|th)?\\s+(?:of\\s+)?({$months})\\b\\.?(?:\\s+(\\d{4}))?/" => fn ($m) => $this->monthDayDate($m[2], (int) $m[1], $m[3] ?? null),
            "/\\b({$months})\\s+(\\d{1,2})(?:st|nd|rd|th)?\\b(?:,?\\s+(\\d{4}))?/" => fn ($m) => $this->monthDayDate($m[1], (int) $m[2], $m[3] ?? null),
        ];

        $taken = [];
        foreach ($patterns as $regex => $resolve) {
            if (!preg_match_all($regex, $lower, $all, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                continue;
            }
            foreach ($all as $match) {
                $start = $match[0][1];
                $end = $start + strlen($match[0][0]);
                foreach ($taken as [$s, $e]) {
                    if ($start < $e && $end > $s) {
                        continue 2;
                    }
                }
                $date = $resolve(array_map(fn ($g) => $g[0], $match));
                if ($date) {
                    $taken[] = [$start, $end];
                    $found[$start] = $date->toDateString();
                }
            }
        }

        ksort($found);
        return array_values($found);
    }

    private function monthDayDate(string $month, int $day, ?string $year): ?Carbon
    {
        $monthNumber = Carbon::parse('1 ' . substr($month, 0, 3) . ' 2000')->month;
        $date = $this->safeDate($year ? (int) $year : Carbon::today()->year, $monthNumber, $day);
        if ($date && !$year && $date->lt(Carbon::today())) {
            $date->addYear();
        }
        return $date;
    }

    private function safeDate(int $year, int $month, int $day): ?Carbon
    {
        return checkdate($month, $day, $year) ? Carbon::create($year, $month, $day)->startOfDay() : null;
    }

    private function numberWordPattern(): string
    {
        return implode('|', array_keys(self::NUMBER_WORDS));
    }

    private function toNumber(string $value): int
    {
        return is_numeric($value) ? (int) $value : (self::NUMBER_WORDS[$value] ?? 1);
    }

    // ------------------------------------------------------------------
    // Validation + URL
    // ------------------------------------------------------------------

    private function buildResult(array $p, string $source): array
    {
        $origin = $this->resolveAirport($p['origin'] ?? null);
        $destination = $this->resolveAirport($p['destination'] ?? null);

        if (!$origin || !$destination) {
            return [
                'success' => false,
                'message' => !$origin && !$destination
                    ? 'Please tell us where you are flying from and to, e.g. "Lahore to Dubai on 15 December".'
                    : (!$origin ? 'Where are you flying from?' : 'Where would you like to fly to?'),
            ];
        }
        if ($origin->code === $destination->code) {
            return ['success' => false, 'message' => 'The departure and destination airports are the same.'];
        }

        $today = Carbon::today();
        $departure = $this->toDate($p['departure_date'] ?? null) ?? $today->copy()->addDay();
        if ($departure->lt($today)) {
            return ['success' => false, 'message' => 'The departure date is in the past. Please choose a future date.'];
        }

        $trip = ($p['trip_type'] ?? 'oneway') === 'round' ? 'round' : 'oneway';
        $return = $trip === 'round' ? $this->toDate($p['return_date'] ?? null) : null;
        if ($trip === 'round' && !$return) {
            $return = $departure->copy()->addWeek();
        }
        if ($return && $return->lt($departure)) {
            return ['success' => false, 'message' => 'The return date is before the departure date.'];
        }

        $cabin = in_array($p['cabin'] ?? null, self::CABINS, true) ? $p['cabin'] : 'economy';
        $adults = max(1, min(9, (int) ($p['adults'] ?? 1)));
        $children = max(0, min(9 - $adults, (int) ($p['children'] ?? 0)));
        $infants = max(0, min($adults, (int) ($p['infants'] ?? 0)));

        $segments = [
            strtolower($origin->code), strtolower($destination->code), $trip, $cabin,
            $departure->format('d-m-Y'),
        ];
        if ($return) {
            $segments[] = $return->format('d-m-Y');
        }
        array_push($segments, $adults, $children, $infants);

        return [
            'success' => true,
            'url' => url('flights/' . implode('/', $segments)),
            'summary' => [
                'from' => "{$origin->city} ({$origin->code})",
                'to' => "{$destination->city} ({$destination->code})",
                'departure' => $departure->format('D, d M Y'),
                'return' => $return?->format('D, d M Y'),
                'cabin' => ucwords(str_replace('_', ' ', $cabin)),
                'travellers' => $adults + $children + $infants,
                'source' => $source,
            ],
        ];
    }

    /** Accepts an IATA code or a city/airport name and returns the airport row. */
    private function resolveAirport(?string $value): ?object
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $airports = DB::table('flights_airports')->where('status', 1);
        if (preg_match('/^[A-Za-z]{3}$/', $value)) {
            $row = (clone $airports)->where('code', strtoupper($value))->first(['code', 'city']);
            if ($row) {
                return $row;
            }
        }

        $code = $this->airportForCity($value);
        if (!$code) {
            $code = (clone $airports)->where('airport', 'like', '%' . $value . '%')->value('code');
        }

        return $code ? (clone $airports)->where('code', $code)->first(['code', 'city']) : null;
    }

    private function toDate(?string $value): ?Carbon
    {
        if (!$value || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
