<?php

namespace App\Support;

/**
 * RFC 6238 time-based one-time passwords, compatible with Google Authenticator
 * (SHA-1, 6 digits, 30-second step). Kept in-house so enabling 2FA doesn't
 * require pulling in a Composer package on every deployment.
 */
class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes($bytes));
    }

    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $key = self::base32Decode($secret);
        if ($key === '' || !preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $counter = (int) floor(time() / 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals(self::codeAt($key, $counter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    private static function codeAt(string $key, int $counter): string
    {
        $hash   = hash_hmac('sha1', pack('J', $counter), $key, true);
        $offset = ord($hash[19]) & 0x0F;

        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
                | ((ord($hash[$offset + 1]) & 0xFF) << 16)
                | ((ord($hash[$offset + 2]) & 0xFF) << 8)
                |  (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $buffer = 0;
        $bits   = 0;
        $output = '';

        foreach (str_split($data) as $char) {
            $buffer = (($buffer << 8) | ord($char)) & 0xFFFF;
            $bits  += 8;

            while ($bits >= 5) {
                $bits  -= 5;
                $output .= self::ALPHABET[($buffer >> $bits) & 31];
            }
        }

        if ($bits > 0) {
            $output .= self::ALPHABET[($buffer << (5 - $bits)) & 31];
        }

        return $output;
    }

    private static function base32Decode(string $input): string
    {
        $input  = strtoupper(preg_replace('/[\s=]/', '', $input));
        $buffer = 0;
        $bits   = 0;
        $output = '';

        foreach (str_split($input) as $char) {
            $value = strpos(self::ALPHABET, $char);
            if ($value === false) {
                return '';
            }

            $buffer = (($buffer << 5) | $value) & 0xFFFF;
            $bits  += 5;

            if ($bits >= 8) {
                $bits  -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        return $output;
    }
}
