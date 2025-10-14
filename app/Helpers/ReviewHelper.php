<?php
// app/Helpers/ReviewHelper.php

namespace App\Helpers;

use App\Models\GradeConversion;

class ReviewHelper
{
    /**
     * Normalisasi grade menjadi bentuk standar:
     * - Group: IV, V, VI, VII
     * - Letter: A-F atau null (kalau tidak ada)
     *
     * Menerima variasi: "IV-A", "IV A", "IVC", "4C", "6A", "VII", "7", "7B", dsb.
     */
    public static function normalizeGrade(?string $gradeInput): ?array
    {
        if (!$gradeInput) return null;

        $raw = strtoupper(trim($gradeInput));
        $raw = str_replace(['–', '_'], ['-', '-'], $raw); // ganti dash variasi

        // 1) Coba cocokkan pola roman + optional letter (IV, V, VI, VII)
        if (preg_match('/^(IV|V|VI|VII)[\s\-]?([A-F])?$/', $raw, $m)) {
            return ['group' => $m[1], 'letter' => $m[2] ?? null, 'raw' => $raw];
        }

        // 2) Pola numeric + letter, contoh "4C", "6A", "7F"
        if (preg_match('/^([4-7])\s*([A-F])?$/', $raw, $m)) {
            $mapNumToRoman = ['4' => 'IV', '5' => 'V', '6' => 'VI', '7' => 'VII'];
            $group  = $mapNumToRoman[$m[1]];
            $letter = $m[2] ?? null;
            return ['group' => $group, 'letter' => $letter, 'raw' => $raw];
        }

        // 3) Pola numeric only "4", "5", "6", "7"
        if (preg_match('/^[4-7]$/', $raw)) {
            $mapNumToRoman = ['4' => 'IV', '5' => 'V', '6' => 'VI', '7' => 'VII'];
            return ['group' => $mapNumToRoman[$raw], 'letter' => null, 'raw' => $raw];
        }

        // 4) Jika kamu menyimpan mapping custom lain (mis. 3C → IV-C) di GradeConversion,
        //    silakan aktifkan lookup berikut (opsional).
        //    Asumsikan kolom astra_grade menyimpan bentuk "IV-A", "V", "VI", "VII", dst.
        $row = GradeConversion::query()
            ->where('astra_grade', $raw)
            ->first();

        if ($row?->astra_grade) {
            // Pastikan konsisten: "IV-A" → group=IV, letter=A
            if (preg_match('/^(IV|V|VI|VII)[\s\-]?([A-F])?$/', strtoupper($row->astra_grade), $m)) {
                return ['group' => $m[1], 'letter' => $m[2] ?? null, 'raw' => $raw];
            }
        }

        // Tidak dikenali → null (akan jatuh ke default di weightsForGrade)
        return null;
    }

    /**
     * Return string "IV-A" / "V" / "VI" / "VII" hasil normalisasi.
     * Berguna untuk logging & tampilan.
     */
    public static function resolveAstraGrade(?string $gradeInput): ?string
    {
        $norm = self::normalizeGrade($gradeInput);
        if (!$norm) return null;
        return $norm['group'] . ($norm['letter'] ? ('-' . $norm['letter']) : '');
    }

    /**
     * Bobot B1/B2 per grade (Result selalu 50%) dengan aturan:
     * - IV A–D: 40% / 10%
     * - IV E–F: 35% / 15%
     * - V:       30% / 20%
     * - VI (A–F): 35% / 15%  (tanpa membedakan A–D vs E–F)
     * - VII (A–F):35% / 15%  (tanpa membedakan A–D vs E–F)
     * - Default:  35% / 15%
     */
    public static function weightsForGrade(?string $gradeInput): array
    {
        $norm = self::normalizeGrade($gradeInput);
        $group  = $norm['group']  ?? null;
        $letter = $norm['letter'] ?? null;

        // DEFAULT aman:
        $b1 = 0.35; // PDCA
        $b2 = 0.15; // People

        if ($group === 'IV') {
            if ($letter && in_array($letter, ['A', 'B', 'C', 'D'], true)) {
                $b1 = 0.40;
                $b2 = 0.10; // IV A–D
            } elseif ($letter && in_array($letter, ['E', 'F'], true)) {
                $b1 = 0.35;
                $b2 = 0.15; // IV E–F
            } else {
                // Tidak ada letter → ambil mayoritas IV E–F sebagai default
                $b1 = 0.35;
                $b2 = 0.15;
            }
        } elseif ($group === 'V') {
            $b1 = 0.30;
            $b2 = 0.20;     // V
        } elseif ($group === 'VI') {
            $b1 = 0.35;
            $b2 = 0.15;     // VI (A–F sama)
        } elseif ($group === 'VII') {
            $b1 = 0.35;
            $b2 = 0.15;     // VII (A–F sama)
        }
        // selain itu jatuh ke default 0.35 / 0.15

        return [
            'result' => 0.50,
            'b1'     => $b1,
            'b2'     => $b2,
        ];
    }

    /**
     * Jika $score <= 5 -> dianggap sudah Result Value (1–5).
     * Jika > 5       -> dianggap Achievement % lalu di-map ke 1–5.
     */
    public static function computeResultValue(float $score): float
    {
        if ($score <= 5.0) {
            return round(max(1.0, min(5.0, $score)), 2);
        }
        return self::mapPercentToResultValue($score);
    }

    /**
     * Map Achievement % → Result Value (1–5) (piecewise linear).
     */
    public static function mapPercentToResultValue(float $pct): float
    {
        $pct = max(0, $pct);
        $lin = fn($x, $x1, $x2, $y1, $y2) => $x1 == $x2 ? $y1 : $y1 + ($y2 - $y1) * (($x - $x1) / ($x2 - $x1));

        if ($pct < 80)      return max(1.00, $lin($pct, 0,   80, 1.00, 1.99));
        if ($pct < 100)     return        $lin($pct, 80,  99, 2.00, 2.99);
        if ($pct <= 110)    return        $lin($pct, 100, 110, 3.00, 3.49);
        if ($pct <= 120)    return        $lin($pct, 111, 120, 3.50, 3.99);
        if ($pct <= 135)    return        $lin($pct, 121, 135, 4.00, 4.36);
        if ($pct <= 150)    return        $lin($pct, 136, 150, 4.37, 4.74);

        // >150%
        return min(5.00, 4.75 + ($pct - 150) * 0.01);
    }

    /** Hitung Final Value. */
    public static function calculateFinalValue(float $resultValue, float $b1, float $b2, array $weights): float
    {
        $fv = ($resultValue * $weights['result']) + ($b1 * $weights['b1']) + ($b2 * $weights['b2']);
        return round($fv, 2);
    }

    /** Tentukan grading dari Final Value. */
    public static function gradeFromFinalValue(float $fv): string
    {
        if ($fv > 4.75)                 return 'IST';
        if ($fv >= 4.37 && $fv <= 4.74) return 'BS+';
        if ($fv >= 4.00 && $fv <= 4.36) return 'BS';
        if ($fv >= 3.50 && $fv <= 3.99) return 'B+';
        if ($fv >= 3.00 && $fv <= 3.49) return 'B';
        if ($fv >= 2.50 && $fv <= 2.99) return 'C+';
        if ($fv >= 2.00 && $fv <= 2.49) return 'C';
        return 'K';
    }

    /** Convenience */
    public static function weightsForEmployee($employee): array
    {
        return self::weightsForGrade($employee?->grade_astra);
    }
}
