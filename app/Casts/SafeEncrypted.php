<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Same as Laravel's built-in 'encrypted' cast, except a value that fails to
 * decrypt (e.g. ciphertext written under an APP_KEY that has since changed —
 * "The MAC is invalid.") is treated as unset instead of throwing and taking
 * down every page/request that touches the model. The ciphertext is left in
 * the database untouched; the admin just needs to re-enter that credential.
 */
class SafeEncrypted implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Logging must never be able to turn a handled decrypt failure
            // into an unhandled one (e.g. storage/logs not writable).
            try {
                Log::warning("Undecryptable '{$key}' on " . get_class($model) . " #{$model->getKey()}: {$e->getMessage()} — treating as empty.");
            } catch (\Throwable $logError) {
                // Swallow — the decrypt failure above is already handled.
            }

            return null;
        }
    }

    public function set(Model $model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString($value);
    }
}
