<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Encrypts any existing plaintext api_credential_1..6 values on
 * travel_partners so the app's "encrypted" Eloquent cast (added to
 * App\Models\TravelPartner alongside this migration) can decrypt them on
 * read. Runs directly against the DB facade — not the Eloquent model — so it
 * is not affected by whatever the model's casts happen to be when it runs.
 *
 * Idempotent: a value that already decrypts successfully (i.e. is already an
 * encrypted payload) is left untouched, so running this more than once, or
 * after some rows were already encrypted by the app, is safe.
 */
return new class extends Migration
{
    /** @var string[] */
    private array $columns = [
        'api_credential_1',
        'api_credential_2',
        'api_credential_3',
        'api_credential_4',
        'api_credential_5',
        'api_credential_6',
    ];

    public function up(): void
    {
        DB::table('travel_partners')
            ->orderBy('id')
            ->select(array_merge(['id'], $this->columns))
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach ($this->columns as $column) {
                        $value = $row->$column;

                        if ($value === null || $value === '' || $this->isAlreadyEncrypted($value)) {
                            continue;
                        }

                        $updates[$column] = Crypt::encryptString($value);
                    }

                    if ($updates) {
                        DB::table('travel_partners')->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        DB::table('travel_partners')
            ->orderBy('id')
            ->select(array_merge(['id'], $this->columns))
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach ($this->columns as $column) {
                        $value = $row->$column;

                        if ($value === null || $value === '' || !$this->isAlreadyEncrypted($value)) {
                            continue;
                        }

                        $updates[$column] = Crypt::decryptString($value);
                    }

                    if ($updates) {
                        DB::table('travel_partners')->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    private function isAlreadyEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};
