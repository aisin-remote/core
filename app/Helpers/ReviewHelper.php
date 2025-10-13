<?php
// app/Helpers/ReviewHelper.php

namespace App\Helpers;

use App\Models\GradeConversion;

class ReviewHelper
{
    public static function resolveAstraGrade(?string $gradeInput): ?string
    {
        if (!$gradeInput) return null;
        $val = strtoupper(trim($gradeInput));

        if (preg_match('/^\d+[A-F]$/', $val)) {
            static $cache = [];

            if (array_key_exists($val, $cache)) {
                return $cache[$val];
            }

            $row = GradeConversion::query()
                ->where('astra_grade', $val)
                ->first();

            $cache[$val] = $row?->astra_grade ?: null;
            return $cache[$val];
        }

        // Selain itu, dianggap sudah berupa Astra grade (IV-A, IV-B, V, VI, dll)
        return $val;
    }

    /**
     * Mapping bobot B1/B2 per ASTRA grade.
     * Result selalu 0.50, B1+B2 = 0.50.
     * NOTE: Silakan sesuaikan map ini dengan kebijakan perusahaan.
     */
    public static function weightsForGrade(?string $gradeInput): array
    {
        $astra = self::resolveAstraGrade($gradeInput);
        // Default & map contoh (silakan ubah sesuai master kalian)
        $default = ['b1' => 0.35, 'b2' => 0.15];
        $map = [
            'VI'   => ['b1' => 0.40, 'b2' => 0.10],
            'V'    => ['b1' => 0.30, 'b2' => 0.20],
            'IV-A' => ['b1' => 0.35, 'b2' => 0.15],
            'IV-B' => ['b1' => 0.35, 'b2' => 0.15],
            'IV-C' => ['b1' => 0.35, 'b2' => 0.15],
            'IV-D' => ['b1' => 0.35, 'b2' => 0.15],
            'IV-E' => ['b1' => 0.35, 'b2' => 0.15],
            'IV-F' => ['b1' => 0.35, 'b2' => 0.15],
        ];

        $w = $astra ? ($map[$astra] ?? $default) : $default;

        return [
            'result' => 0.50,
            'b1'     => $w['b1'],
            'b2'     => $w['b2'],
        ];
    }

    /**
     * Jika $score <= 5 -> dianggap sudah Result Value (1–5).
     * Jika > 5       -> dianggap Achievement % lalu di-map ke 1–5.
     */
    public static function computeResultValue(float $score): float
    {
        // tolerate "4,83" yang sudah di-cast ke float akan jadi 4.83
        if ($score <= 5.0) {
            return round(max(1.0, min(5.0, $score)), 2);
        }
        return self::mapPercentToResultValue($score);
    }

    /**
     * Memetakan Achievement Score (%) → Result Value (1–5) dengan piecewise linear.
     * (Tetap dipakai oleh computeResultValue bila input > 5)
     */
    public static function mapPercentToResultValue(float $pct): float
    {
        $pct = max(0, $pct);

        $lin = fn($x, $x1, $x2, $y1, $y2) => $x1 == $x2 ? $y1 : $y1 + ($y2 - $y1) * (($x - $x1) / ($x2 - $x1));

        if ($pct < 80)      return max(1.00, $lin($pct, 0, 80, 1.00, 1.99));
        if ($pct < 100)     return $lin($pct, 80, 99, 2.00, 2.99);
        if ($pct <= 110)    return $lin($pct, 100, 110, 3.00, 3.49);
        if ($pct <= 120)    return $lin($pct, 111, 120, 3.50, 3.99);
        if ($pct <= 135)    return $lin($pct, 121, 135, 4.00, 4.36);
        if ($pct <= 150)    return $lin($pct, 136, 150, 4.37, 4.74);

        // >150%
        return min(5.00, 4.75 + ($pct - 150) * 0.01);
    }

    /**
     * Hitung Final Value.
     */
    public static function calculateFinalValue(float $resultValue, float $b1, float $b2, array $weights): float
    {
        $fv = ($resultValue * $weights['result']) + ($b1 * $weights['b1']) + ($b2 * $weights['b2']);
        return round($fv, 2);
    }

    /**
     * Tentukan grading dari Final Value.
     */
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

    /**
     * Convenience: langsung dari employee->grade_astra (boleh '4C' atau 'IV-C')
     */
    public static function weightsForEmployee($employee): array
    {
        return self::weightsForGrade($employee?->grade_astra);
    }
}
