<?php

namespace App\Helpers;

class RtcTarget
{
    /**
     * Urutan dari terendah ke tertinggi (pakai kode)
     */
    public static function order(): array
    {
        return [
            'AL',
            'L',
            'SL',
            'AS',
            'S',
            'SS',
            'AM',
            'M',
            'SM',
            'AGM',
            'GM',
            'SGM'
        ];
    }

    /**
     * Mapping kode -> posisi + levels
     */
    public static function mapAll(): array
    {
        return [
            'AL'  => ['position' => 'Act Leader',      'levels' => ['4A', '4B']],
            'L'   => ['position' => 'Leader',          'levels' => ['5A', '5B']],
            'SL'  => ['position' => 'Act Supervisor',  'levels' => ['6A', '6B']],
            'AS'  => ['position' => 'Supervisor',      'levels' => ['7A', '7B']],
            'S'   => ['position' => 'Section Head',    'levels' => ['8A', '8B']],
            'SS'  => ['position' => 'Coordinator',     'levels' => ['9A', '9B']],
            'AM'  => ['position' => 'Manager',         'levels' => ['10A', '10B']],
            'M'   => ['position' => 'Act GM',          'levels' => ['11A', '11B']],
            'SM'  => ['position' => 'GM',              'levels' => ['12A', '12B']],
            'AGM' => ['position' => 'Act Direktur',    'levels' => ['13A', '13B']],
            'GM'  => ['position' => 'Direktur',        'levels' => ['14A', '14B', '15A', '15B']],
            'SGM' => ['position' => 'Direktur',        'levels' => ['14A', '14B', '15A', '15B']],
        ];
    }

    public static function map(string $kode): ?array
    {
        $kode = strtoupper(trim($kode));
        $all  = self::mapAll();
        return $all[$kode] ?? null;
    }

    public static function allCodes(): array
    {
        return array_keys(self::mapAll());
    }

    /**
     * Apakah $kodeA ≤ $kodeB menurut ranking?
     */
    public static function lte(string $kodeA, string $kodeB): bool
    {
        $ord = self::order();
        $ia = array_search(strtoupper($kodeA), $ord, true);
        $ib = array_search(strtoupper($kodeB), $ord, true);
        if ($ia === false || $ib === false) return false;
        return $ia <= $ib;
    }

    /**
     * Ambil daftar kode yang ≤ career target
     */
    public static function codesUpTo(string $careerTargetCode): array
    {
        $ord = self::order();
        $idx = array_search(strtoupper($careerTargetCode), $ord, true);
        if ($idx === false) return [];
        return array_slice($ord, 0, $idx + 1);
    }
}
