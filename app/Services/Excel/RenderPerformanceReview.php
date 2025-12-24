<?php

namespace App\Services\Excel;

use App\Models\IpaHeader;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RenderPerformanceReview
{
    /**
     * Render sheet Performance Review.
     *
     * @param Worksheet $sheet  Sheet yang sudah di-attach ke workbook utama dan diberi judul "Performance Review"
     * @param int       $ipaId
     */
    public function render(Worksheet $sheet, int $ipaId): void
    {
        $ctx = ['ipa_id' => $ipaId, 'service' => 'RenderPerformanceReview'];
        Log::info('Performance Review: start render', $ctx);

        /** @var IpaHeader $ipa */
        $ipa = IpaHeader::with(['employee', 'checkedBy', 'approvedBy', 'performanceReview', 'ipp'])
            ->findOrFail($ipaId);
            
        $prModel = $ipa->performanceReview()->first();
        $performanceReview = $prModel ? $prModel->toArray() : [];

        $fmtDate = function ($dt) {
            if (empty($dt)) return null;

            if ($dt instanceof DateTimeInterface) {
                return $dt->timezone(config('app.timezone'))->format('d M Y');
            }

            $s = trim((string) $dt);
            if ($s === '' || $s === '0000-00-00' || $s === '0000-00-00 00:00:00') {
                return null;
            }

            try {
                return Carbon::parse($s)->timezone(config('app.timezone'))->format('d M Y');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $employee   = $ipa->employee;
        $checkedBy  = $ipa->checkedBy;
        $approvedBy = $ipa->approvedBy;

        // =========================
        // 1) Header identitas
        // =========================
        $sheet->setCellValue('C7', $employee->name ?? $ipa->employee_name); // Employee Name
        $sheet->setCellValue('C8', $employee?->department?->name);         // Department
        $sheet->setCellValue('C9', $employee?->division?->name);           // Division

        // =========================
        // 2) Isi detail Performance Review
        // =========================

        $b1Items = (array) ($performanceReview['b1_items'] ?? []);
        $b2Items = (array) ($performanceReview['b2_items'] ?? []);

        $b1Map = [
            0 => ['E18', 'F18'],
            1 => ['E20', 'F20'],
            2 => ['E22', 'F22'],
            3 => ['E24', 'F24'],
            4 => ['E26', 'F26'],
            5 => ['E28', 'F28'],
            6 => ['E30', 'F30'],
        ];

        $b2Map = [
            0 => ['L18', 'M18'],
            1 => ['L20', 'M20'],
            2 => ['L22', 'M22'],
            3 => ['L24', 'M24'],
        ];

        // helper isi value ke 2 cell sekaligus
        $writeValueOnly = function (Worksheet $sheet, array $map, array $items) {
            foreach ($map as $i => [$cellA, $cellB]) {
                if (!array_key_exists($i, $items)) {
                    $sheet->setCellValue($cellA, null);
                    $sheet->setCellValue($cellB, null);
                    continue;
                }

                // pastikan numeric biar Excel kebaca angka
                $value = is_numeric($items[$i]) ? (float) $items[$i] : $items[$i];

                $sheet->setCellValue($cellA, $value);
                $sheet->setCellValue($cellB, $value);
            }
        };

        $writeValueOnly($sheet, $b1Map, $b1Items);
        $writeValueOnly($sheet, $b2Map, $b2Items);

        // =========================
        // 3) Summary / weighting / final
        // =========================

        // helper ambil numeric aman
        $toNum = function ($v): ?float {
            if ($v === null || $v === '') return null;
            if (is_string($v)) $v = str_replace(',', '.', $v);
            return is_numeric($v) ? (float) $v : null;
        };

        // ambil nilai dari data performance review
        $resultValue   = $performanceReview['result_value']   ?? null;  // "4.84" (string)
        $weightResult  = $performanceReview['weight_result']  ?? null;  // "0.500"
        $resultPercent = $performanceReview['result_percent'] ?? null;  // 4.84 (float)

        $b1Pdca    = $performanceReview['b1_pdca_values'] ?? null;      // 4.14
        $weightB1  = $performanceReview['weight_b1']      ?? null;      // "0.350"

        $b2People  = $performanceReview['b2_people_mgmt'] ?? null;      // 4.25
        $weightB2  = $performanceReview['weight_b2']      ?? null;      // "0.150"

        $finalVal  = $performanceReview['final_value'] ?? null;         // "4.51"
        $grading   = $performanceReview['grading']     ?? null;         // "BS+"

        // --- set cell sesuai mapping kamu ---
        $sheet->setCellValue('I38', $toNum($resultValue) ?? $resultValue);   // result_value
        $sheet->setCellValue('K38', $toNum($weightResult) ?? $weightResult); // weight_result
        $sheet->setCellValue('M38', $toNum($resultPercent) ?? $resultPercent); // result_percent

        $sheet->setCellValue('I41', $toNum($b1Pdca) ?? $b1Pdca);             // b1_pdca_values
        $sheet->setCellValue('K41', $toNum($weightB1) ?? $weightB1);         // weight_b1

        // M41 = b1_pdca_values * weight_b1
        $m41 = null;
        if (($nB1 = $toNum($b1Pdca)) !== null && ($wB1 = $toNum($weightB1)) !== null) {
            $m41 = round($nB1 * $wB1, 4);
        }
        $sheet->setCellValue('M41', $m41);

        $sheet->setCellValue('I44', $toNum($b2People) ?? $b2People);         // b2_people_mgmt
        $sheet->setCellValue('K44', $toNum($weightB2) ?? $weightB2);         // weight_b2

        // M44 = b2_people_mgmt * weight_b2
        $m44 = null;
        if (($nB2 = $toNum($b2People)) !== null && ($wB2 = $toNum($weightB2)) !== null) {
            $m44 = round($nB2 * $wB2, 4);
        }
        $sheet->setCellValue('M44', $m44);

        $sheet->setCellValue('M47', $toNum($finalVal) ?? $finalVal);         // final_value
        $sheet->setCellValue('M49', (string) $grading);                      // grading

        Log::info('Performance Review: render done', $ctx);
    }
}
