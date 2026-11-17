<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * WebBeds API logs, stored in public/webbeds-logs.
 *
 * One file per journey ("trace"): a visitor's search → details → room block →
 * booking share the same trace, so the log of a failed booking holds every
 * WebBeds call that led to it. Each trace has:
 *   {trace}.log  — the readable log (requests / responses)
 *   {trace}.json — summary shown in the admin (steps, booking ref, errors)
 *
 * Card numbers, CVV and WebBeds passwords are masked before writing.
 * The folder is web-protected (.htaccess); admins download through the panel.
 */
class WebbedsLogger
{
    public const DIR            = 'webbeds-logs';
    private const MAX_BODY      = 200000; // bytes of one request/response kept in the log
    private const TRACE_PATTERN = '/^[A-Za-z0-9_-]{1,80}$/';

    public static function dir(): string
    {
        $dir = public_path(self::DIR);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_file($dir . '/.htaccess')) {
            @file_put_contents($dir . '/.htaccess', "Require all denied\nDeny from all\n");
            @file_put_contents($dir . '/index.html', '');
        }
        return $dir;
    }

    /** Trace for the current request: the one HotelController sends, else the booking ref/code, else a daily bucket. */
    public static function trace(): string
    {
        $r = request();
        foreach (['trace', 'booking_ref', 'booking_code'] as $key) {
            $v = (string) $r->input($key, '');
            $v = preg_replace('/[^A-Za-z0-9_-]/', '', $v);
            if ($v !== '') return Str::limit($v, 80, '');
        }
        return 'general-' . now()->format('Ymd');
    }

    /** Journey id for a website visitor (same across search → booking in one session). */
    public static function traceForSession(): string
    {
        return 'S' . substr(sha1(session()->getId() . config('app.key')), 0, 14);
    }

    /**
     * Write one entry.
     * $step: search, details, block, booking, cancel, ...  $error: message when the step failed.
     */
    public static function write(string $step, string $title, array $data = [], ?string $error = null): void
    {
        try {
            $dir   = self::dir();
            $trace = self::trace();
            $now   = now();

            $lines   = [];
            $lines[] = str_repeat('=', 90);
            $lines[] = '[' . $now->format('Y-m-d H:i:s') . '] ' . strtoupper($step) . ' — ' . $title
                . ($error ? '   ❌ ERROR' : '');
            if ($error) $lines[] = 'Error: ' . $error;
            foreach ($data as $label => $value) {
                if (!is_string($value)) {
                    $value = json_encode(self::mask($value), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } else {
                    $value = self::maskText($value);
                }
                if (strlen($value) > self::MAX_BODY) {
                    $value = substr($value, 0, self::MAX_BODY) . "\n… [truncated, " . strlen($value) . ' bytes]';
                }
                $lines[] = '--- ' . $label . ' ---';
                $lines[] = $value;
            }
            file_put_contents("$dir/$trace.log", implode("\n", $lines) . "\n\n", FILE_APPEND | LOCK_EX);

            // Summary
            $metaFile = "$dir/$trace.json";
            $meta = is_file($metaFile) ? (json_decode((string) file_get_contents($metaFile), true) ?: []) : [];
            $meta += ['trace' => $trace, 'started_at' => $now->toIso8601String(), 'steps' => [], 'errors' => 0];
            $meta['updated_at'] = $now->toIso8601String();
            $meta['env']        = request()->input('env') === 'pro' ? 'pro' : 'dev';
            if (!in_array($step, $meta['steps'], true)) $meta['steps'][] = $step;
            foreach (['booking_ref', 'hotel_id', 'city'] as $k) {
                $v = request()->input($k);
                if (is_scalar($v) && $v !== '') $meta[$k] = (string) $v;
            }
            if (!empty($data['booking_pnr'])) $meta['booking_pnr'] = (string) $data['booking_pnr'];
            if ($error) {
                $meta['errors']++;
                $meta['last_error']      = Str::limit($error, 300);
                $meta['last_error_step'] = $step;
            }
            if ($step === 'booking' && $title === 'Result') {
                $meta['booking_status'] = $error ? 'failed' : 'confirmed';
            }
            file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        } catch (\Throwable $e) {
            // Logging must never break a booking
        }
    }

    /** All traces, newest first. */
    public static function all(): array
    {
        $dir  = self::dir();
        $rows = [];
        foreach (glob("$dir/*.log") ?: [] as $file) {
            $trace = basename($file, '.log');
            $meta  = is_file("$dir/$trace.json") ? (json_decode((string) file_get_contents("$dir/$trace.json"), true) ?: []) : [];
            $rows[] = $meta + [
                'trace'      => $trace,
                'steps'      => [],
                'errors'     => 0,
                'updated_at' => date('c', filemtime($file)),
            ] + ['size' => filesize($file)];
        }
        usort($rows, fn ($a, $b) => strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? ''));
        return $rows;
    }

    public static function path(string $trace): ?string
    {
        if (!preg_match(self::TRACE_PATTERN, $trace)) return null;
        $file = self::dir() . "/$trace.log";
        return is_file($file) ? $file : null;
    }

    public static function delete(string $trace): bool
    {
        if (!preg_match(self::TRACE_PATTERN, $trace)) return false;
        $dir = self::dir();
        $ok  = @unlink("$dir/$trace.log");
        @unlink("$dir/$trace.json");
        return $ok;
    }

    public static function deleteAll(): int
    {
        $n = 0;
        foreach (glob(self::dir() . '/*.{log,json}', GLOB_BRACE) ?: [] as $f) {
            if (@unlink($f) && str_ends_with($f, '.log')) $n++;
        }
        return $n;
    }

    // ---------------------------------------------------------------- masking

    private const SECRET_KEYS = ['cardnumber', 'securitycode', 'cvv', 'password', 'api_credential_2', 'api_credential_4', 'api_credential_5', 'api_credential_6', 'device_payload'];

    public static function mask($value)
    {
        if (!is_array($value)) return is_string($value) ? self::maskText($value) : $value;
        foreach ($value as $k => $v) {
            $key = strtolower((string) $k);
            if (in_array($key, self::SECRET_KEYS, true) && is_scalar($v) && $v !== '') {
                $value[$k] = $key === 'cardnumber' ? '****' . substr((string) $v, -4) : '******';
            } else {
                $value[$k] = self::mask($v);
            }
        }
        return $value;
    }

    public static function maskText(string $text): string
    {
        $text = preg_replace('#<password>.*?</password>#s', '<password>******</password>', $text);
        $text = preg_replace('/("(?:cardNumber)"\s*:\s*")\d*(\d{4})"/', '$1****$2"', $text);
        $text = preg_replace('/("(?:securityCode|cvv)"\s*:\s*")[^"]*"/i', '$1***"', $text);
        return $text;
    }
}
