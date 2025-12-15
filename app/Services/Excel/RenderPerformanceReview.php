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
        $ipa = IpaHeader::with(['employee', 'checkedBy', 'approvedBy'])
            ->findOrFail($ipaId);

        $fmtDate = function ($dt) {
            if (empty($dt)) return null;

            if ($dt instanceof DateTimeInterface) {
                return $dt->timezone->format('d M Y');
            }

            $s = trim((string)$dt);
            if ($s === '' || $s === '0000-00-00' || $s === '0000-00-00 00:00:00') {
                return null;
            }

            try {
                return Carbon::parse($s)->timezone(config('app.timezone'))->format('d M Y');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $employee = $ipa->employee;
        $checkedBy = $ipa->checkedBy;
        $approvedBy = $ipa->approvedBy;

        // =========================
        // 1) Header identitas
        // =========================
        // NOTE:
        // - Silakan sesuaikan koordinat cell di sini dengan template "Template_Performance_Review.xlsx"
        // - Ini hanya contoh posisi (B5, B6, dst).
        $sheet->setCellValue('B5', $employee->name ?? $ipa->employee_name);                 // Employee Name
        $sheet->setCellValue('B6', $employee?->department?->name);                         // Department
        $sheet->setCellValue('B7', $employee?->division?->name);                           // Division
        $sheet->setCellValue('B8', $employee?->company_name);                              // Company

        // Contoh kolom tanggal & status
        $sheet->setCellValue('F5', $fmtDate($ipa->submitted_at));                          // Submitted Date
        $sheet->setCellValue('F6', $fmtDate($ipa->checked_at));                            // Checked Date
        $sheet->setCellValue('F7', $fmtDate($ipa->approved_at));                           // Approved Date

        // Contoh nama atasan
        $sheet->setCellValue('B10', $checkedBy?->name ?? $checkedBy?->employee?->name);     // Superior
        $sheet->setCellValue('B11', $approvedBy?->name ?? $approvedBy?->employee?->name);   // Superior of Superior

        // =========================
        // 2) Isi detail Performance Review
        // =========================
        // Tergantung desain template kamu:
        // - Bisa ambil nilai dari IPA (total score, remarks, summary, dsb)
        // - Bisa juga gabungkan dengan informasi lain (misal final rating)
        //
        // Di sini aku kasih contoh placeholder sederhana:

        // Misal: total score / summary diambil dari field IPA (kalau ada)
        if (property_exists($ipa, 'final_score')) {
            // Contoh: tulis ke cell C15 (silakan sesuaikan)
            $sheet->setCellValue('C15', $ipa->final_score);
        }

        if (property_exists($ipa, 'final_rating')) {
            // Contoh: tulis ke cell C16
            $sheet->setCellValue('C16', $ipa->final_rating);
        }

        // Kalau belum ada field-nya, tinggal dihapus / sesuaikan.

        Log::info('Performance Review: render done', $ctx);
    }
}
