<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\TravelPartner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

/**
 * Imports WebBeds hotel content city-by-city into the `sqlite_webbeds`
 * database (database/webbeds.db) — same tables as the standalone
 * webbeds/index.php tool: cities, hotels, hotel_fetch_log.
 *
 * A city counts as complete once it has been imported in the current
 * calendar month; at the start of every month all cities become due
 * again, so a cron hitting runBatch() keeps the data refreshed monthly.
 */
class WebbedsCityImporter
{
    private const ENDPOINT = 'https://xmldev.dotwconnect.com/gatewayV4.dotw';
    private const LOCK_KEY = 'webbeds-city-import';
    private const PROGRESS_KEY = 'webbeds-city-import-progress';
    private const FAILED_RETRY_HOURS = 6;
    private const HOTEL_FIELDS = [
        'hotelName', 'address', 'rating', 'hotelImages', 'fullAddress', 'description1',
        'description2', 'location2', 'location3', 'cityName', 'cityCode', 'hotelPhone',
    ];

    private ?PDO $pdo = null;

    public function pdo(): PDO
    {
        if ($this->pdo) return $this->pdo;
        $pdo = DB::connection('sqlite_webbeds')->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA busy_timeout = 15000');

        $pdo->exec("CREATE TABLE IF NOT EXISTS hotel_fetch_log (city_code TEXT PRIMARY KEY, hotels_saved INTEGER, fetched_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
        $cols = array_column($pdo->query('PRAGMA table_info(hotel_fetch_log)')->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('status', $cols)) $pdo->exec('ALTER TABLE hotel_fetch_log ADD COLUMN status TEXT');
        if (!in_array('message', $cols)) $pdo->exec('ALTER TABLE hotel_fetch_log ADD COLUMN message TEXT');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_hotels_search_city_code ON hotels(search_city_code)');

        return $this->pdo = $pdo;
    }

    /** fetched_at is stored in UTC (SQLite CURRENT_TIMESTAMP convention). */
    public function monthStart(): string
    {
        return gmdate('Y-m-01 00:00:00');
    }

    public function stats(): array
    {
        $pdo = $this->pdo();
        $month = $this->monthStart();
        $total = (int) $pdo->query("SELECT COUNT(*) FROM cities WHERE code GLOB '[0-9]*'")->fetchColumn();
        $st = $pdo->prepare("SELECT
                SUM(CASE WHEN fetched_at >= :m AND IFNULL(status,'success') = 'success' THEN 1 ELSE 0 END),
                SUM(CASE WHEN fetched_at >= :m2 AND status = 'failed' THEN 1 ELSE 0 END),
                COUNT(*)
            FROM hotel_fetch_log");
        $st->execute([':m' => $month, ':m2' => $month]);
        [$doneMonth, $failedMonth, $everImported] = array_map('intval', $st->fetch(PDO::FETCH_NUM));

        return [
            'total_cities'   => $total,
            'imported_month' => $doneMonth,
            'failed_month'   => $failedMonth,
            'pending_month'  => max(0, $total - $doneMonth - $failedMonth),
            'ever_imported'  => $everImported,
            'total_hotels'   => (int) $pdo->query('SELECT COUNT(*) FROM hotels')->fetchColumn(),
            'progress'       => $total ? round($doneMonth / $total * 100, 2) : 0,
            'month_label'    => now()->format('F Y'),
            'last_run'       => json_decode((string) Setting::getValue('city_import_last_run', 'webbeds'), true),
            'running'        => !Cache::lock(self::LOCK_KEY, 1)->get(fn () => true),
            'current'        => Cache::get(self::PROGRESS_KEY),
        ];
    }

    /** Live state of the running batch, read by the admin page while it polls stats. */
    private function reportProgress(string $trigger, float $start, string $code, int $done, int $failed, int $hotels, ?array $last): void
    {
        $st = $this->pdo()->prepare('SELECT name, countryName FROM cities WHERE code = :c');
        $st->execute([':c' => $code]);
        $city = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        Cache::put(self::PROGRESS_KEY, [
            'trigger' => $trigger,
            'city' => $city['name'] ?? "City {$code}",
            'country' => $city['countryName'] ?? '',
            'code' => $code,
            'cities_done' => $done,
            'cities_failed' => $failed,
            'hotels_saved' => $hotels,
            'last' => $last ? ['city' => $last['city'], 'hotels_saved' => $last['hotels_saved'], 'success' => $last['success']] : null,
            'elapsed' => (int) round(microtime(true) - $start),
        ], 300);
    }

    public function cities(string $search = '', string $status = 'all', int $page = 1, int $perPage = 25): array
    {
        $pdo = $this->pdo();
        $where = ["c.code GLOB '[0-9]*'"];
        $params = [':m' => $this->monthStart()];
        if ($search !== '') {
            $where[] = '(c.name LIKE :q OR c.countryName LIKE :q OR c.code = :code)';
            $params[':q'] = '%' . $search . '%';
            $params[':code'] = $search;
        }
        $statusExpr = "CASE WHEN l.city_code IS NULL THEN 'pending'
                            WHEN l.status = 'failed' THEN 'failed'
                            WHEN l.fetched_at >= :m THEN 'imported'
                            ELSE 'outdated' END";
        if (in_array($status, ['imported', 'pending', 'failed', 'outdated'])) {
            $where[] = "$statusExpr = :status";
            $params[':status'] = $status;
        }
        $from = 'FROM cities c LEFT JOIN hotel_fetch_log l ON l.city_code = c.code WHERE ' . implode(' AND ', $where);

        $only = fn (string $sql) => array_filter($params, fn ($k) => preg_match('/' . preg_quote($k) . '\b/', $sql), ARRAY_FILTER_USE_KEY);

        $countSql = "SELECT COUNT(*) $from";
        $count = $pdo->prepare($countSql);
        $count->execute($only($countSql));
        $total = (int) $count->fetchColumn();

        $page = max(1, $page);
        $rowsSql = "SELECT c.code, c.name, c.countryName, l.hotels_saved, l.fetched_at, l.message, $statusExpr AS state
            $from ORDER BY (l.fetched_at IS NULL), l.fetched_at DESC, c.name LIMIT :lim OFFSET :off";
        $rows = $pdo->prepare($rowsSql);
        foreach ($only($rowsSql) as $k => $v) $rows->bindValue($k, $v);
        $rows->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $rows->bindValue(':off', ($page - 1) * $perPage, PDO::PARAM_INT);
        $rows->execute();

        return [
            'data' => $rows->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /** Runs due cities until the time budget is used up. Never overlaps with itself. */
    public function runBatch(int $seconds = 50, string $trigger = 'cron'): array
    {
        $lock = Cache::lock(self::LOCK_KEY, $seconds + 180);
        if (!$lock->get()) {
            return ['success' => false, 'running' => true, 'message' => 'An import batch is already running.'];
        }

        $start = microtime(true);
        $done = $failed = $hotels = 0;
        $last = null;
        try {
            $creds = $this->credentials();
            while (microtime(true) - $start < $seconds) {
                $code = $this->nextDueCity();
                if ($code === null) break;
                $this->reportProgress($trigger, $start, $code, $done, $failed, $hotels, $last);
                $res = $this->importCityWith($creds, $code);
                $res['success'] ? $done++ : $failed++;
                $hotels += $res['hotels_saved'] ?? 0;
                $last = $res;
            }
            $remaining = $this->stats()['pending_month'];
            $summary = [
                'success' => true,
                'trigger' => $trigger,
                'cities_imported' => $done,
                'cities_failed' => $failed,
                'hotels_saved' => $hotels,
                'remaining' => $remaining,
                'complete' => $remaining === 0,
                'seconds' => round(microtime(true) - $start, 1),
                'finished_at' => now()->toDateTimeString(),
                'last_city' => $last['city'] ?? null,
                'message' => $remaining === 0
                    ? 'All cities are imported for ' . now()->format('F Y') . '.'
                    : "Imported {$done} cities ({$hotels} hotels), {$failed} failed, {$remaining} remaining this month.",
            ];
        } catch (Throwable $e) {
            $summary = ['success' => false, 'trigger' => $trigger, 'message' => $e->getMessage(), 'finished_at' => now()->toDateTimeString()];
        } finally {
            Cache::forget(self::PROGRESS_KEY);
            $lock->release();
        }

        Setting::setValue('city_import_last_run', json_encode($summary), 'webbeds');
        return $summary;
    }

    public function importCity(string $code): array
    {
        return $this->importCityWith($this->credentials(), $code);
    }

    private function importCityWith(array $creds, string $code): array
    {
        if (!ctype_digit($code)) throw new RuntimeException('Invalid city code.');
        $pdo = $this->pdo();
        $name = $pdo->prepare('SELECT name FROM cities WHERE code = :c');
        $name->execute([':c' => $code]);
        $cityName = $name->fetchColumn() ?: "City {$code}";

        try {
            $fields = self::HOTEL_FIELDS;
            $xml = $this->searchHotels($creds, $code, $fields);
            $saved = $this->saveHotels($xml, $code);
            $this->log($code, $saved, 'success', null);
            return ['success' => true, 'city' => $cityName, 'code' => $code, 'hotels_saved' => $saved,
                'message' => $saved ? "Imported {$saved} hotels for {$cityName}." : "No hotels found for {$cityName}."];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->log($code, 0, 'failed', Str::limit($e->getMessage(), 480));
            return ['success' => false, 'city' => $cityName, 'code' => $code, 'hotels_saved' => 0, 'message' => $e->getMessage()];
        }
    }

    private function nextDueCity(): ?string
    {
        $st = $this->pdo()->prepare("SELECT c.code FROM cities c LEFT JOIN hotel_fetch_log l ON l.city_code = c.code
            WHERE c.code GLOB '[0-9]*' AND (l.city_code IS NULL OR l.fetched_at < :m OR (l.status = 'failed' AND l.fetched_at < :retry))
            ORDER BY (l.city_code IS NOT NULL), l.fetched_at LIMIT 1");
        $st->execute([':m' => $this->monthStart(), ':retry' => gmdate('Y-m-d H:i:s', time() - self::FAILED_RETRY_HOURS * 3600)]);
        $code = $st->fetchColumn();
        return $code === false ? null : (string) $code;
    }

    private function log(string $code, int $saved, string $status, ?string $message): void
    {
        $this->pdo()->prepare('INSERT OR REPLACE INTO hotel_fetch_log (city_code, hotels_saved, fetched_at, status, message) VALUES (:c, :n, :t, :s, :m)')
            ->execute([':c' => $code, ':n' => $saved, ':t' => gmdate('Y-m-d H:i:s'), ':s' => $status, ':m' => $message]);
    }

    private function credentials(): array
    {
        $partner = TravelPartner::whereRaw('LOWER(TRIM(company_name)) = ?', ['webbeds'])->first();
        $u = trim((string) $partner?->api_credential_1);
        $p = trim((string) $partner?->api_credential_2);
        $id = trim((string) $partner?->api_credential_3);
        if ($u === '' || $p === '' || $id === '') {
            throw new RuntimeException('WebBeds username, password and ID must be saved on the WebBeds partner first.');
        }
        return ['username' => $u, 'password' => md5($p), 'id' => $id];
    }

    private function searchHotels(array $c, string $code, array &$fields): SimpleXMLElement
    {
        for ($try = 0; $try < 15; $try++) {
            $raw = $this->post($this->buildRequest($c, $code, $fields));
            $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $isError = stripos($decoded, 'NOT XSD VALID') !== false
                || preg_match('/<successful>\s*false\s*<\/successful>/i', $decoded)
                || stripos($decoded, '<error') !== false;
            if (!$isError) {
                $xml = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_PARSEHUGE);
                if ($xml === false) throw new RuntimeException('Could not parse the WebBeds response.');
                return $xml;
            }
            // WebBeds rejects fields a customer account can't request — drop them and retry.
            if (!preg_match_all("/Element 'field':.*?'([^']+)'/i", $decoded, $m)) {
                preg_match('/<details>(.*?)<\/details>/is', $decoded, $d);
                throw new RuntimeException(trim(strip_tags($d[1] ?? $decoded)) ?: 'WebBeds returned an error.');
            }
            $fields = array_values(array_diff($fields, $m[1]));
        }
        throw new RuntimeException("Too many retries for city {$code}.");
    }

    private function buildRequest(array $c, string $code, array $fields): string
    {
        $from = date('Y-m-d');
        $to = date('Y-m-d', strtotime('+1 day'));
        $f = implode('', array_map(fn ($x) => "<field>{$x}</field>", $fields));
        $u = htmlspecialchars($c['username'], ENT_XML1);
        $id = htmlspecialchars($c['id'], ENT_XML1);
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<customer>
    <username>{$u}</username>
    <password>{$c['password']}</password>
    <id>{$id}</id>
    <source>1</source>
    <product>hotel</product>
    <language>en</language>
    <request command="searchhotels">
        <bookingDetails>
            <fromDate>{$from}</fromDate>
            <toDate>{$to}</toDate>
            <currency>520</currency>
            <rooms no="1"><room runno="0"><adultsCode>1</adultsCode><children no="0"></children><rateBasis>-1</rateBasis></room></rooms>
        </bookingDetails>
        <return>
            <filters xmlns:a="http://us.dotwconnect.com/xsd/atomicCondition" xmlns:c="http://us.dotwconnect.com/xsd/complexCondition">
                <city>{$code}</city>
                <noPrice>true</noPrice>
            </filters>
            <fields>{$f}</fields>
        </return>
    </request>
</customer>
XML;
    }

    private function post(string $xml): string
    {
        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => ['Content-Type: text/xml; charset=UTF-8'],
            CURLOPT_ENCODING => '', CURLOPT_TIMEOUT => 120, CURLOPT_CONNECTTIMEOUT => 15,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($err) throw new RuntimeException("Connection error: {$err}");
        if ($raw === false || $raw === '') throw new RuntimeException("Empty response (HTTP {$http}).");
        return $raw;
    }

    /** Replaces the city's hotels with the fresh result in one transaction. */
    private function saveHotels(SimpleXMLElement $xml, string $code): int
    {
        $pdo = $this->pdo();
        $cols = array_merge(['hotel_id', 'search_city_code'], self::HOTEL_FIELDS, ['raw_xml']);
        $insert = $pdo->prepare('INSERT OR REPLACE INTO hotels (' . implode(', ', array_map(fn ($c) => "\"{$c}\"", $cols))
            . ') VALUES (' . implode(', ', array_map(fn ($c) => ":{$c}", $cols)) . ')');

        $hotels = $xml->xpath('//hotels/hotel') ?: [];
        $saved = 0;
        $pdo->beginTransaction();
        $pdo->prepare('DELETE FROM hotels WHERE search_city_code = :c')->execute([':c' => $code]);
        foreach ($hotels as $hotel) {
            $hotelId = (string) ($hotel['hotelid'] ?? '') ?: $this->field($hotel, 'hotelid');
            $name = $this->field($hotel, 'hotelName');
            if ($hotelId === '' && $name === '') continue;
            $params = [':hotel_id' => $hotelId, ':search_city_code' => $code, ':raw_xml' => $hotel->asXML()];
            foreach (self::HOTEL_FIELDS as $f) $params[":{$f}"] = $this->field($hotel, $f);
            $insert->execute($params);
            $saved++;
        }
        $pdo->commit();
        return $saved;
    }

    private function field(SimpleXMLElement $node, string $field): string
    {
        $found = $node->xpath($field);
        if (!$found) return '';
        return $found[0]->count() > 0 ? trim((string) $found[0]->asXML()) : trim((string) $found[0]);
    }

    public function cronToken(bool $regenerate = false): string
    {
        $token = Setting::getValue('city_import_cron_token', 'webbeds');
        if ($regenerate || !$token) {
            $token = Str::random(40);
            Setting::setValue('city_import_cron_token', $token, 'webbeds');
        }
        return $token;
    }
}
