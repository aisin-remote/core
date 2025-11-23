<?php

namespace App\Helpers;

class RtcTarget
{
    /**
     * Ranking jabatan dari terendah -> tertinggi.
     * Ini dipakai buat validasi range posisi stage (current .. career target).
     *
     * NOTE:
     * Pastikan urutan ini sesuai jalur karier perusahaanmu.
     * Saya pakai urutan dari kode lama kamu, tapi dirapikan namingnya.
     */
    public static function order(): array
    {
        return [
            'AL',   // Act Leader
            'L',    // Leader
            'SL',   // Act Section Head
            'AS',   // Supervisor
            'S',    // Section Head
            'SS',   // Coordinator
            'AM',   // Manager
            'DGM',  // DGM
            'M',    // Act GM
            'SM',   // GM
            'AGM',  // Act Direktur
            'GM',   // Direktur
            'SGM',  // Direktur (Senior?)
        ];
    }

    /**
     * Mapping kode -> nama posisi.
     * levels dibuang karena sekarang level/gradenya diambil dari GradeConversion,
     * bukan hardcoded per posisi lagi.
     */
    public static function mapAll(): array
    {
        return [
            'AL'  => ['position' => 'Act Leader'],
            'L'   => ['position' => 'Leader'],
            'SL'  => ['position' => 'Act Section Head'],
            'AS'  => ['position' => 'Supervisor'],
            'S'   => ['position' => 'Section Head'],
            'SS'  => ['position' => 'Coordinator'],
            'AM'  => ['position' => 'Manager'],
            'DGM' => ['position' => 'DGM'],
            'M'   => ['position' => 'Act GM'],
            'SM'  => ['position' => 'GM'],
            'AGM' => ['position' => 'Act Direktur'],
            'GM'  => ['position' => 'Direktur'],
            'SGM' => ['position' => 'Direktur'],
        ];
    }

    public static function codesFrom(?string $minCode = null): array
    {
        $ord = self::order();

        if (!$minCode) {
            return $ord;
        }

        $minCode = strtoupper(trim($minCode));
        $idx = array_search($minCode, $ord, true);

        if ($idx === false) {
            return $ord;
        }

        return array_slice($ord, $idx);
    }

    /**
     * Kalau dipanggil tanpa argumen, sebelumnya function ini ngembaliin seluruh map.
     * Behavior itu masih aku pertahankan supaya nggak ngerusak tempat lain.
     */
    public static function map(string $kode = ""): ?array
    {
        $kode = strtoupper(trim($kode));
        $all  = self::mapAll();
        return $all[$kode] ?? $all;
    }

    public static function allCodes(): array
    {
        return array_keys(self::mapAll());
    }

    /**
     * Bandingkan dua kode posisi berdasarkan ranking order()
     * Contoh: lte('L','AM') => true, lte('GM','AM') => false
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
     * Ambil semua kode dari level terbawah sampai target.
     * Ini dipakai buat dropdown posisi stage (range current -> career target).
     */
    public static function codesUpTo(string $careerTargetCode): array
    {
        $ord = self::order();
        $idx = array_search(strtoupper($careerTargetCode), $ord, true);
        if ($idx === false) return [];
        return array_slice($ord, 0, $idx + 1);
    }

    /**
     * Cari kode berdasarkan nama posisi.
     * Dipakai waktu nentuin posisi CURRENT employee di create().
     */
    public static function codeFromPositionName(string $positionName): ?string
    {
        $positionName = trim(strtoupper($positionName));
        foreach (self::mapAll() as $code => $info) {
            if (strtoupper($info['position']) === $positionName) {
                return $code;
            }
        }
        return null;
    }

    public static function positionFromCode(string $code): ?string
    {
        $code = strtoupper(trim($code));
        $all = self::mapAll();
        return $all[$code]['position'] ?? null;
    }
}
