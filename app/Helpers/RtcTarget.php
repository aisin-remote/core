<?php

namespace App\Helpers;

class RtcTarget
{
    /**
     * Mapping kode RTC ke posisi & level wajib
     *
     * @param  string  $kode  (contoh: 'AS', 'S', 'AM', dst.)
     * @return array|null
     */
    public static function map(string $kode): ?array
    {
        $mapping = [
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

        $kode = strtoupper(trim($kode));
        return $mapping[$kode] ?? null;
    }

    /**
     * Ambil semua kode RTC yang valid (untuk dropdown, dsb.)
     */
    public static function allCodes(): array
    {
        return array_keys(self::mapAll());
    }

    /**
     * Ambil full mapping (berguna untuk seeding atau referensi lain)
     */
    public static function mapAll(): array
    {
        return [
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
}
