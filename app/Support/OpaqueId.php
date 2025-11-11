<?php

namespace App\Support;

class OpaqueId
{
    protected static function key(): string
    {
        $key = (string) config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }
        return $key;
    }

    public static function encode(int $id): string
    {
        $payload = (string) $id;
        $sig = hash_hmac('sha256', $payload, self::key());
        // base64 url-safe tanpa padding
        return rtrim(strtr(base64_encode($payload . '.' . substr($sig, 0, 16)), '+/', '-_'), '=');
    }

    public static function decode(?string $tok): ?int
    {
        if (!$tok) return null;

        // Kembalikan karakter ke base64 standar
        $b64 = strtr($tok, '-_', '+/');

        // Tambahkan padding yang benar (panjang harus kelipatan 4)
        $padLen = strlen($b64) % 4;
        if ($padLen) {
            $b64 .= str_repeat('=', 4 - $padLen);
        }

        $raw = base64_decode($b64, true);
        if ($raw === false || !str_contains($raw, '.')) return null;

        [$payload, $shortSig] = explode('.', $raw, 2);
        $sig = hash_hmac('sha256', $payload, self::key());

        return hash_equals(substr($sig, 0, 16), $shortSig) ? (int) $payload : null;
    }
}
