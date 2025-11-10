<?php

namespace App\Support;

class OpaqueId
{
    public static function encode(int $id)
    {
        $payload = (string) $id;
        $sig = hash_hmac('sha256', $payload, config('app.key'));
        return rtrim(strtr(base64_encode($payload . '.' . substr($sig, 0, 16)), '+/', '-_'), '=');
    }

    public static function decode(?string $tok)
    {
        if (!$tok) return null;
        $raw = base64_decode(strtr($tok, '-_', '+/') . '==', true);
        if (!$raw || !str_contains($raw, '.')) return null;
        [$payload, $shortSig] = explode('.', $raw, 2);
        $sig = hash_hmac('sha256', $payload, config('app.key'));
        return hash_equals(substr($sig, 0, 16), $shortSig) ? (int) $payload : null;
    }
}
