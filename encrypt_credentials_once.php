<?php
/**
 * ONE-TIME SCRIPT — encrypts any existing plaintext api_credential_1..6
 * values on the travel_partners table using this app's own APP_KEY.
 *
 * WHY A PHP FILE AND NOT RAW SQL:
 * Laravel's encryption (AES-256-CBC + HMAC, via the app's APP_KEY) cannot be
 * reproduced by MySQL's own AES_ENCRYPT() — the output format is different
 * and Laravel would not be able to decrypt it. This script runs the exact
 * same encryption Laravel itself uses, directly against your live database,
 * with no SSH/terminal needed.
 *
 * HOW TO USE:
 *  1. Upload this file into your project's ROOT folder on the live server
 *     (the same folder that contains "artisan", "app", "vendor", etc.) —
 *     via cPanel File Manager.
 *  2. Open it once in your browser:
 *       https://YOUR-DOMAIN/encrypt_credentials_once.php?run=1
 *  3. Read the summary it prints.
 *  4. DELETE this file from the server immediately after running it —
 *     it must not be left publicly reachable.
 *
 * Safe to run more than once — it skips any value that's already encrypted.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain; charset=utf-8');

if (($_GET['run'] ?? '') !== '1') {
    echo "Add ?run=1 to the URL to actually run this (e.g. ?run=1).\n";
    exit;
}

$columns = [
    'api_credential_1',
    'api_credential_2',
    'api_credential_3',
    'api_credential_4',
    'api_credential_5',
    'api_credential_6',
];

function isAlreadyEncrypted(string $value): bool
{
    try {
        Crypt::decryptString($value);
        return true;
    } catch (\Throwable) {
        return false;
    }
}

$rowsUpdated = 0;
$fieldsEncrypted = 0;

DB::table('travel_partners')
    ->orderBy('id')
    ->select(array_merge(['id'], $columns))
    ->chunkById(100, function ($rows) use ($columns, &$rowsUpdated, &$fieldsEncrypted) {
        foreach ($rows as $row) {
            $updates = [];

            foreach ($columns as $column) {
                $value = $row->$column;

                if ($value === null || $value === '' || isAlreadyEncrypted($value)) {
                    continue;
                }

                $updates[$column] = Crypt::encryptString($value);
            }

            if ($updates) {
                DB::table('travel_partners')->where('id', $row->id)->update($updates);
                $rowsUpdated++;
                $fieldsEncrypted += count($updates);
            }
        }
    });

echo "Done.\n";
echo "Partners updated: {$rowsUpdated}\n";
echo "Credential fields encrypted: {$fieldsEncrypted}\n";
echo "\nNow DELETE this file from the server.\n";
