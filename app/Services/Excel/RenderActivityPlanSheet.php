<?php

namespace App\Services\Excel;

use App\Models\Ipp;
use App\Models\ActivityPlan;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RenderActivityPlanSheet
{
    private const CELL_NAME_NPK  = 'E3';
    private const CELL_DIVISION  = 'E4';
    private const CELL_DEPT      = 'E5';
    private const CELL_SECTION   = 'E6';

    private const ROW_START      = 11; // Baris mulai data
    private const ROW_SIGNATURE  = 14; // Baris awal tanda tangan (akan disesuaikan)

    private const COL_NO         = 'B';
    private const COL_OBJECTIVES_FROM = 'C';
    private const COL_OBJECTIVES_TO   = 'D';
    private const COL_KIND       = 'E';
    private const COL_PIC        = 'F';
    private const COL_TARGET     = 'G';
    private const COL_DUE        = 'H';

    private const COL_MONTHS = ['I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
    private const FIRST_COL  = 'A';  // Kolom pertama untuk border medium
    private const LAST_COL   = 'U';  // Kolom terakhir untuk border medium

    // Mapping category ke format yang diinginkan
    private const CATEGORY_MAPPING = [
        'activity_management' => 'ACTIVITY MANAGEMENT',
        'people_development'  => 'PEOPLE DEVELOPMENT',
        'crp'                 => 'CRP',
        'special_assignment'  => 'SPECIAL ASSIGNMENT & IMPROVEMENT'
    ];

    public function render(Worksheet $sheet, int $ippId): void
    {
        // Validasi worksheet
        if (is_null($sheet) || $sheet->getTitle() === '') {
            throw new \Exception('Worksheet untuk Activity Plan tidak valid');
        }

        $ipp = Ipp::with('employee')->findOrFail($ippId);
        $plan = ActivityPlan::with([
            'employee',
            'items' => fn($q) => $q->orderBy('id'),
            'items.pic',
            'items.ippPoint',
        ])->where('ipp_id', $ippId)->first();

        // Set header data dengan error handling
        $this->setCellValueSafe($sheet, self::CELL_NAME_NPK,  trim(($ipp->nama ?? $ipp->employee?->name ?? '—') . ' / ' . ($ipp->employee?->npk ?? '—')));
        $this->setCellValueSafe($sheet, self::CELL_DIVISION,  $plan?->division   ?: $ipp->division   ?: '—');
        $this->setCellValueSafe($sheet, self::CELL_DEPT,      $plan?->department ?: $ipp->department ?: '—');
        $this->setCellValueSafe($sheet, self::CELL_SECTION,   $plan?->section    ?: $ipp->section    ?: '—');

        $currentRow = self::ROW_START;
        $items = $plan?->items ?: collect();

        if ($items->isEmpty()) {
            $this->drawCategoryRow($sheet, $currentRow, 'No Data Available');
            $currentRow++;
            $this->drawEmptyDataRow($sheet, $currentRow);
            $this->adjustSignatureSection($sheet, $currentRow + 1);
            return;
        }

        // Group items by category dan activity
        $groupedItems = $this->groupItemsByCategoryAndActivity($items);

        $no = 1;

        foreach ($groupedItems as $category => $activities) {
            // Convert category ke format yang diinginkan
            $formattedCategory = $this->formatCategory($category);

            // Draw category row
            $this->drawCategoryRow($sheet, $currentRow, $formattedCategory);
            $currentRow++;

            // Draw activity items untuk category ini
            foreach ($activities as $activity => $activityItems) {
                $firstItem = $activityItems->first();

                // Draw activity row (hanya sekali)
                $this->drawActivityRow($sheet, $currentRow, $no, $activity, '', '', '', '', 0, true);
                $currentRow++;
                $no++;

                // Draw detail items untuk activity ini
                foreach ($activityItems as $it) {
                    $kind   = $it->kind_of_activity ?: '—';
                    $pic    = $this->shortenName($it->pic?->name ?: ($it->pic_name ?: '—'));
                    $target = $it->target ?: '—';
                    $due    = $this->fmtDateMonthYear($it->cached_due_date ?: $it->ippPoint?->due_date);
                    $mask   = (int)($it->schedule_mask ?? 0);

                    $this->drawActivityDetailRow($sheet, $currentRow, $no, $kind, $pic, $target, $due, $mask);
                    $currentRow++;
                    $no++;
                }
            }
        }

        // Sesuaikan posisi tanda tangan berdasarkan jumlah data
        $signatureStartRow = $this->adjustSignatureSection($sheet, $currentRow);

        // Apply medium border untuk kolom A dan U dari awal data sampai satu baris setelah signature
        $this->applyMediumSideBorders($sheet, self::ROW_START, $signatureStartRow + 3);

        // Apply inner borders untuk data (B-T) - hanya border kiri untuk kolom B-H
        $this->applyLeftBorders($sheet, self::ROW_START, $currentRow - 1);

        // Apply special borders untuk schedule (I-T) - kiri, atas, bawah
        $this->applyScheduleBorders($sheet, self::ROW_START, $currentRow - 1);
    }

    private function formatCategory(string $category): string
    {
        return self::CATEGORY_MAPPING[$category] ?? strtoupper($category);
    }

    private function groupItemsByCategoryAndActivity($items)
    {
        $grouped = collect();

        foreach ($items as $item) {
            $category = $item->cached_category ?: ($item->ippPoint->category ?? 'Uncategorized');
            $activity = $item->cached_activity ?: ($item->ippPoint->activity ?? 'Uncategorized Activity');

            if (!$grouped->has($category)) {
                $grouped->put($category, collect());
            }

            if (!$grouped->get($category)->has($activity)) {
                $grouped->get($category)->put($activity, collect());
            }

            $grouped->get($category)->get($activity)->push($item);
        }

        return $grouped;
    }

    private function shortenName(string $name): string
    {
        if ($name === '—' || strlen(trim($name)) <= 3) {
            return $name;
        }

        // Ambil inisial (3 huruf pertama)
        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
            if (strlen($initials) >= 3) {
                break;
            }
        }

        // Pastikan panjang 3 karakter
        return str_pad(substr($initials, 0, 3), 3, ' ', STR_PAD_RIGHT);
    }

    private function drawCategoryRow(Worksheet $s, int $row, string $category): void
    {
        // Merge dari B sampai T untuk category
        $categoryRange = self::COL_NO . $row . ':' . self::COL_MONTHS[array_key_last(self::COL_MONTHS)] . $row;
        $s->mergeCells($categoryRange);
        $s->setCellValue(self::COL_NO . $row, $category);

        // Apply style untuk category row
        $s->getStyle($categoryRange)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E6E6E6'] // Light gray background
            ]
        ]);

        // Set row height
        $s->getRowDimension($row)->setRowHeight(25);
    }

    private function drawActivityRow(
        Worksheet $s,
        int $row,
        int|string $no,
        string $activity,
        string $kind,
        string $pic,
        string $target,
        ?string $due,
        int $mask,
        bool $isActivityRow = false
    ): void {
        // Set values - hanya activity yang diisi
        $s->setCellValue(self::COL_NO . $row, $no);

        // Merge cells untuk activity (C dan D)
        $objRange = self::COL_OBJECTIVES_FROM . $row . ':' . self::COL_OBJECTIVES_TO . $row;
        $s->mergeCells($objRange);
        $s->setCellValue(self::COL_OBJECTIVES_FROM . $row, $activity);

        // Kolom lainnya dikosongkan untuk activity row
        $s->setCellValue(self::COL_KIND . $row, '');
        $s->setCellValue(self::COL_PIC . $row, '');
        $s->setCellValue(self::COL_TARGET . $row, '');
        $s->setCellValue(self::COL_DUE . $row, '');

        // Set bulanan - dikosongkan untuk activity row
        foreach (self::COL_MONTHS as $col) {
            $s->setCellValue($col . $row, '');
        }

        // Apply styles untuk activity row
        $this->applyActivityRowStyles($s, $row, true);
    }

    private function drawActivityDetailRow(
        Worksheet $s,
        int $row,
        int|string $no,
        string $kind,
        string $pic,
        string $target,
        ?string $due,
        int $mask
    ): void {
        // Set values untuk detail row
        $s->setCellValue(self::COL_NO . $row, $no);
        $s->setCellValue(self::COL_KIND . $row, $kind);
        $s->setCellValue(self::COL_PIC . $row, $pic);
        $s->setCellValue(self::COL_TARGET . $row, $target);
        $s->setCellValue(self::COL_DUE . $row, $due ?? '—');

        // Objectives dikosongkan untuk detail row
        $objRange = self::COL_OBJECTIVES_FROM . $row . ':' . self::COL_OBJECTIVES_TO . $row;
        $s->mergeCells($objRange);
        $s->setCellValue(self::COL_OBJECTIVES_FROM . $row, '');

        // Set bulanan
        foreach (self::COL_MONTHS as $i => $col) {
            $flag = ($mask & (1 << $i)) ? '✓' : '';
            $s->setCellValue($col . $row, $flag);
        }

        // Apply styles untuk detail row
        $this->applyActivityRowStyles($s, $row, false);
    }

    private function drawEmptyDataRow(Worksheet $s, int $row): void
    {
        $s->setCellValue(self::COL_NO . $row, '1');
        $s->setCellValue(self::COL_OBJECTIVES_FROM . $row, 'No data available');
        $s->setCellValue(self::COL_KIND . $row, '—');
        $s->setCellValue(self::COL_PIC . $row, '—');
        $s->setCellValue(self::COL_TARGET . $row, '—');
        $s->setCellValue(self::COL_DUE . $row, '—');

        foreach (self::COL_MONTHS as $col) {
            $s->setCellValue($col . $row, '—');
        }

        $this->applyActivityRowStyles($s, $row, false);
    }

    private function applyActivityRowStyles(Worksheet $s, int $row, bool $isActivityRow = false): void
    {
        // Set row height untuk accommodate wrap text
        $s->getRowDimension($row)->setRowHeight(-1); // Auto height

        // Alignment styles dengan wrap text untuk semua kolom B-T
        $dataRange = self::COL_NO . $row . ':' . self::COL_MONTHS[array_key_last(self::COL_MONTHS)] . $row;

        $s->getStyle($dataRange)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        // Specific alignments
        $s->getStyle(self::COL_NO . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $s->getStyle(self::COL_OBJECTIVES_FROM . $row . ':' . self::COL_OBJECTIVES_TO . $row)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $s->getStyle(self::COL_KIND . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $s->getStyle(self::COL_PIC . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $s->getStyle(self::COL_TARGET . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $s->getStyle(self::COL_DUE . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (self::COL_MONTHS as $col) {
            $s->getStyle($col . $row)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Style khusus untuk activity row (teks bold)
        if ($isActivityRow) {
            $s->getStyle(self::COL_OBJECTIVES_FROM . $row . ':' . self::COL_OBJECTIVES_TO . $row)
                ->getFont()->setBold(true);
        }
    }

    private function applyLeftBorders(Worksheet $sheet, int $startRow, int $endRow): void
    {
        // Apply border kiri untuk kolom B-H
        for ($col = self::COL_NO; $col <= self::COL_DUE; $col++) {
            $range = $col . $startRow . ':' . $col . $endRow;

            $sheet->getStyle($range)->applyFromArray([
                'borders' => [
                    'left' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'top' => [
                        'borderStyle' => Border::BORDER_NONE,
                        'color' => ['rgb' => '000000'],
                    ],
                    'bottom' => [
                        'borderStyle' => Border::BORDER_NONE,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_NONE,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }
    }

    private function applyScheduleBorders(Worksheet $sheet, int $startRow, int $endRow): void
    {
        // Apply border khusus untuk schedule (I-T) - kiri, atas, bawah
        foreach (self::COL_MONTHS as $col) {
            $range = $col . $startRow . ':' . $col . $endRow;

            $sheet->getStyle($range)->applyFromArray([
                'borders' => [
                    'left' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_NONE,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        // Tambahkan border kanan untuk kolom terakhir schedule (T)
        $lastScheduleCol = self::COL_MONTHS[array_key_last(self::COL_MONTHS)];
        $lastScheduleRange = $lastScheduleCol . $startRow . ':' . $lastScheduleCol . $endRow;

        $sheet->getStyle($lastScheduleRange)->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    private function applyMediumSideBorders(Worksheet $sheet, int $startRow, int $endRow): void
    {
        // Apply medium border untuk kolom A dari startRow sampai endRow
        $sheet->getStyle(self::FIRST_COL . $startRow . ':' . self::FIRST_COL . $endRow)->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Apply medium border untuk kolom U dari startRow sampai endRow
        $sheet->getStyle(self::LAST_COL . $startRow . ':' . self::LAST_COL . $endRow)->applyFromArray([
            'borders' => [
                'left' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    private function adjustSignatureSection(Worksheet $sheet, int $lastDataRow): int
    {
        // Hitung baris untuk tanda tangan (2 baris setelah data terakhir)
        $signatureStartRow = $lastDataRow + 2;

        // Copy style dari baris tanda tangan template ke posisi baru
        $this->copySignatureStyles($sheet, $signatureStartRow);

        // Set nilai untuk tanda tangan
        $this->setSignatureValues($sheet, $signatureStartRow);

        return $signatureStartRow;
    }

    private function copySignatureStyles(Worksheet $sheet, int $signatureRow): void
    {
        // Area yang perlu di-copy style-nya
        $areasToCopy = [
            'G' . self::ROW_SIGNATURE . ':T' . (self::ROW_SIGNATURE + 3) // Area tanda tangan
        ];

        foreach ($areasToCopy as $range) {
            try {
                $style = $sheet->getStyle($range);
                $newRange = $this->shiftRange($range, $signatureRow - self::ROW_SIGNATURE);
                $sheet->duplicateStyle($style, $newRange);
            } catch (\Exception $e) {
                // Jika gagal copy style, buat style manual
                $this->createSignatureStyle($sheet, $signatureRow);
                break;
            }
        }

        // Set row height untuk area tanda tangan
        for ($i = 0; $i < 4; $i++) {
            $sheet->getRowDimension($signatureRow + $i)->setRowHeight(25);
        }
    }

    private function shiftRange(string $range, int $rowShift): string
    {
        $parts = explode(':', $range);
        $newRange = '';

        foreach ($parts as $part) {
            preg_match('/([A-Z]+)(\d+)/', $part, $matches);
            $col = $matches[1];
            $row = $matches[2] + $rowShift;
            $newRange .= $col . $row . ':';
        }

        return rtrim($newRange, ':');
    }

    private function createSignatureStyle(Worksheet $sheet, int $startRow): void
    {
        // Buat border untuk area tanda tangan
        $signatureRange = 'G' . $startRow . ':T' . ($startRow + 3);

        $sheet->getStyle($signatureRange)->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ]
            ],
        ]);

        // Set alignment untuk tanggal
        $dateCells = [
            'I' . ($startRow + 2), // Superior of Superior date
            'M' . ($startRow + 2), // Superior date
            'S' . ($startRow + 2)  // Employee date
        ];

        foreach ($dateCells as $cell) {
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    }

    private function setSignatureValues(Worksheet $sheet, int $startRow): void
    {
        // Set label untuk tanda tangan
        $sheet->setCellValue('G' . $startRow, 'Superior of Superior');
        $sheet->setCellValue('K' . $startRow, 'Superior');
        $sheet->setCellValue('Q' . $startRow, 'Employee');

        // Set label untuk tanggal
        $sheet->setCellValue('I' . ($startRow + 2), 'Date :');
        $sheet->setCellValue('M' . ($startRow + 2), 'Date :');
        $sheet->setCellValue('S' . ($startRow + 2), 'Date :');

        // Apply styles untuk label
        $labelCells = [
            'G' . $startRow,
            'K' . $startRow,
            'Q' . $startRow,
            'I' . ($startRow + 2),
            'M' . ($startRow + 2),
            'S' . ($startRow + 2)
        ];

        foreach ($labelCells as $cell) {
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    }

    private function setCellValueSafe(Worksheet $s, string $addr, string $val): void
    {
        try {
            $s->setCellValue($addr, $val);
            try {
                $s->getStyle($addr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            } catch (\Exception $e) {
                // Ignore alignment error
            }
        } catch (\Exception $e) {
            throw new \Exception("Gagal mengatur cell {$addr}: " . $e->getMessage());
        }
    }

    private function fmtDateMonthYear($d): ?string
    {
        if (!$d) return null;
        try {
            return \Carbon\Carbon::parse($d)->format('M Y'); // Hanya bulan dan tahun
        } catch (\Throwable) {
            return (string)$d;
        }
    }
}
