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

    private const COL_NO         = 'B';
    private const COL_OBJECTIVES_FROM = 'C';
    private const COL_OBJECTIVES_TO   = 'D';
    private const COL_KIND       = 'E';
    private const COL_PIC        = 'F';
    private const COL_TARGET     = 'G';
    private const COL_DUE        = 'H';

    private const COL_MONTHS = ['I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
    private const FIRST_COL  = 'A';  // Kolom pertama untuk border medium
    private const LAST_COL   = 'U';  // Kolom terakhir untuk border medium (data)
    private const OUTER_COL  = 'V';  // Kolom luar untuk border medium + signature

    // Mapping category ke format yang diinginkan
    private const CATEGORY_MAPPING = [
        'activity_management' => 'ACTIVITY MANAGEMENT',
        'people_development'  => 'PEOPLE DEVELOPMENT',
        'crp'                 => 'CRP',
        'special_assignment'  => 'SPECIAL ASSIGNMENT & IMPROVEMENT'
    ];

    public function render(Worksheet $sheet, int $ippId): void
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        if (is_null($sheet) || $sheet->getTitle() === '') {
            throw new \Exception('Worksheet untuk Activity Plan tidak valid');
        }

        $ipp = Ipp::with(['employee', 'picReviewer', 'approvedBy'])->findOrFail($ippId);
        $plan = ActivityPlan::with([
            'employee',
            'items' => fn($q) => $q->orderBy('id'),
            'items.pic',
            'items.ippPoint',
        ])->where('ipp_id', $ippId)->first();

        if (!empty($ipp->on_year)) {
            $sheet->setCellValue('B1', 'ACTIVITY PLAN YEAR ' . $ipp->on_year);
        }

        $this->setCellValueSafe(
            $sheet,
            self::CELL_NAME_NPK,
            trim(($ipp->nama ?? $ipp->employee?->name ?? '—') . ' / ' . ($ipp->employee?->npk ?? '—'))
        );
        $this->setCellValueSafe($sheet, self::CELL_DIVISION,  $plan?->division   ?: $ipp->division   ?: '—');
        $this->setCellValueSafe($sheet, self::CELL_DEPT,      $plan?->department ?: $ipp->department ?: '—');
        $this->setCellValueSafe($sheet, self::CELL_SECTION,   $plan?->section    ?: $ipp->section    ?: '—');

        $currentRow = self::ROW_START;
        $items = $plan?->items ?: collect();

        if ($items->isEmpty()) {
            $this->drawCategoryRow($sheet, $currentRow, 'No Data Available');
            $currentRow++;
            $this->drawEmptyDataRow($sheet, $currentRow);
            $this->addSignatureSection($sheet, $currentRow + 1, $ipp);
            $this->applyColumnSettings($sheet);
            return;
        }

        $groupedItems = $this->groupItemsByCategoryAndActivity($items);
        $no = 1;
        foreach ($groupedItems as $category => $activities) {
            $formattedCategory = $this->formatCategory($category);

            $this->drawCategoryRow($sheet, $currentRow, $formattedCategory);
            $currentRow++;

            foreach ($activities as $activity => $activityItems) {
                $isFirstRowOfActivity = true;

                foreach ($activityItems as $it) {
                    $kind   = $it->kind_of_activity ?: '—';
                    $pic    = $this->shortenName($it->pic?->name ?: ($it->pic_name ?: '—'));
                    $target = $it->target ?: '—';
                    $due    = $this->fmtDateMonthYear($it->cached_due_date ?: $it->ippPoint?->due_date);
                    $mask   = (int)($it->schedule_mask ?? 0);

                    $activityText = $isFirstRowOfActivity ? $activity : '';
                    $noText = $isFirstRowOfActivity ? $no : '';

                    // sekarang objectives diisi di row yang sama
                    $this->drawActivityDetailRow(
                        $sheet,
                        $currentRow,
                        $noText,
                        $activityText,
                        $kind,
                        $pic,
                        $target,
                        $due,
                        $mask
                    );

                    $currentRow++;
                    $isFirstRowOfActivity = false;
                }
                $no++;
            }
        }

        $dataEndRow = $currentRow - 1;

        $signatureEndRow = $this->addSignatureSection($sheet, $dataEndRow, $ipp);

        $this->applyLeftBorders($sheet, self::ROW_START, $dataEndRow);
        $this->applyScheduleBorders($sheet, self::ROW_START, $dataEndRow);
        $this->applyMediumBorders($sheet, self::ROW_START, $dataEndRow, $signatureEndRow);
        $this->applyColumnSettings($sheet, $dataEndRow);
    }

    /**
     * Tambahkan bagian tanda tangan setelah data activity plan
     * Mengembalikan baris terakhir yang terpakai (baris "Date :" paling bawah)
     */
    /**
     * Tambahkan bagian tanda tangan setelah data activity plan
     * dan kembalikan baris terakhir (baris "Date :" paling bawah).
     */
    private function addSignatureSection(Worksheet $sheet, int $lastDataRow, Ipp $ipp): int
    {
        // Mulai dari baris terakhir data + 2 baris
        $startRow = $lastDataRow + 2;
        $titleRow = $startRow;

        // Nama-nama
        $preparedByName         = $ipp->employee->name ?? $ipp->nama ?? '';
        $checkedByName          = $ipp->picReviewer->name ?? '';
        $superiorOfSuperiorName = $ipp->approvedBy->name ?? '';

        /**
         * Bagi area I..T (12 kolom) menjadi 3 blok sama lebar (4 kolom per blok):
         *  - Superior of Superior: I..L
         *  - Checked by         : M..P
         *  - Prepared by        : Q..T
         */
        $blocks = [
            [
                'title' => 'Superior of Superior',
                'name'  => $superiorOfSuperiorName,
                'from'  => 'I',
                'to'    => 'L',
            ],
            [
                'title' => 'Checked by',
                'name'  => $checkedByName,
                'from'  => 'M',
                'to'    => 'P',
            ],
            [
                'title' => 'Prepared by',
                'name'  => $preparedByName,
                'from'  => 'Q',
                'to'    => 'T',
            ],
        ];

        $maxDateRow = $titleRow;

        foreach ($blocks as $block) {
            $fromCol = $block['from'];
            $toCol   = $block['to'];

            // === 1. BARIS JUDUL ===
            $titleRange = $fromCol . $titleRow . ':' . $toCol . $titleRow;
            $sheet->mergeCells($titleRange);
            $sheet->setCellValue($fromCol . $titleRow, $block['title']);
            $sheet->getStyle($titleRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            // === 2. BARIS KOTAK TANDA TANGAN (TEMPAT IMAGE) ===
            $signatureRow   = $titleRow + 2;               // 1 baris kosong di bawah judul
            $signatureRange = $fromCol . $signatureRow . ':' . $toCol . $signatureRow;

            $sheet->mergeCells($signatureRange);
            // tinggi baris besar untuk gambar
            $sheet->getRowDimension($signatureRow)->setRowHeight(80);

            $sheet->getStyle($signatureRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // === 3. BARIS NAMA ===
            $nameRow   = $signatureRow + 1;
            $nameRange = $fromCol . $nameRow . ':' . $toCol . $nameRow;

            $sheet->mergeCells($nameRange);
            $sheet->setCellValue($fromCol . $nameRow, $block['name']);
            $sheet->getStyle($nameRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            // === 4. BARIS DATE ===
            $dateRow   = $nameRow + 1;
            $dateRange = $fromCol . $dateRow . ':' . $toCol . $dateRow;

            $sheet->mergeCells($dateRange);
            $sheet->setCellValue($fromCol . $dateRow, 'Date :');
            $sheet->getStyle($dateRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            if ($dateRow > $maxDateRow) {
                $maxDateRow = $dateRow;
            }
        }

        $bottomRow = $maxDateRow + 3;

        for ($r = $maxDateRow + 1; $r <= $bottomRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        return $bottomRow;
    }

    /**
     * Apply column settings dengan auto-wrap untuk semua kolom
     */
    private function applyColumnSettings(Worksheet $sheet, int $dataEndRow = 0): void
    {
        // Kolom B (NO) - fixed width dengan auto-wrap
        $sheet->getColumnDimension('B')->setWidth(8);
        $sheet->getColumnDimension('C')->setWidth(50);

        // Kolom C & D (OBJECTIVES)
        $sheet->getColumnDimension('D')->setAutoSize(true);

        // Kolom E (KIND OF ACTIVITY)
        $sheet->getColumnDimension('E')->setAutoSize(true);

        // Kolom F (PIC)
        $sheet->getColumnDimension('F')->setWidth(10);

        // Kolom H (DUE DATE)
        $sheet->getColumnDimension('H')->setWidth(12);

        // Kolom I-T (SCHEDULE BULANAN)
        foreach (self::COL_MONTHS as $col) {
            $sheet->getColumnDimension($col)->setWidth(7.5);
        }

        // Kolom A, U, V (border samping)
        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->getColumnDimension('U')->setWidth(3);
        $sheet->getColumnDimension('V')->setWidth(3);

        // Apply auto-wrap untuk semua kolom data (B sampai T)
        $this->applyAutoWrapToAllColumns($sheet, $dataEndRow);
    }

    /**
     * Apply auto-wrap text untuk semua kolom data
     */
    private function applyAutoWrapToAllColumns(Worksheet $sheet, int $dataEndRow): void
    {
        $firstDataRow = self::ROW_START;

        // HANYA range data, tidak menyentuh baris signature
        $dataRange = 'B' . $firstDataRow . ':T' . $dataEndRow;

        $sheet->getStyle($dataRange)->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);

        // Auto height hanya untuk baris data
        for ($row = $firstDataRow; $row <= $dataEndRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height
        }
    }


    private function formatCategory(string $category): string
    {
        return self::CATEGORY_MAPPING[$category] ?? strtoupper($category);
    }

    private function groupItemsByCategoryAndActivity($items)
    {
        $grouped = collect();

        foreach ($items as $item) {
            $category = $item->cached_category ?: ($item->ippPoint->category ?? '');
            $activity = $item->cached_activity ?: ($item->ippPoint->activity ?? '');

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
        $words     = explode(' ', trim($name));
        $initials  = '';

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
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => 'E6E6E6'], // Light gray background
            ],
        ]);

        // Set row height
        $s->getRowDimension($row)->setRowHeight(25);
    }

    private function drawActivityDetailRow(
        Worksheet $s,
        int $row,
        int|string $no,
        string $activity,   // <-- TAMBAHAN PARAMETER
        string $kind,
        string $pic,
        string $target,
        ?string $due,
        int $mask
    ): void {
        // NO (boleh kosong untuk baris kedua dst)
        $s->setCellValue(self::COL_NO . $row, $no);

        // OBJECTIVES (C–D) – hanya baris pertama yang diisi, sisanya dikosongkan
        $objRange = self::COL_OBJECTIVES_FROM . $row . ':' . self::COL_OBJECTIVES_TO . $row;
        $s->mergeCells($objRange);
        $s->setCellValue(self::COL_OBJECTIVES_FROM . $row, $activity);

        // KIND, PIC, TARGET, DUE
        $s->setCellValue(self::COL_KIND . $row, $kind);
        $s->setCellValue(self::COL_PIC . $row, $pic);
        $s->setCellValue(self::COL_TARGET . $row, $target);
        $s->setCellValue(self::COL_DUE . $row, $due ?? '—');

        // SCHEDULE BULANAN (I–T)
        foreach (self::COL_MONTHS as $i => $col) {
            $cellAddress = $col . $row;
            if ($mask & (1 << $i)) {
                $s->setCellValue($cellAddress, '');
                $s->getStyle($cellAddress)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('73bef8');
            } else {
                $s->setCellValue($cellAddress, '');
                $s->getStyle($cellAddress)->getFill()
                    ->setFillType(Fill::FILL_NONE);
            }
        }

        // Anggap baris yang punya activity (tidak kosong) sebagai "activity row"
        $this->applyActivityRowStyles($s, $row, $activity !== '');
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
        $s->getRowDimension($row)->setRowHeight(-1); // Auto height

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
                        'color'       => ['rgb' => '000000'],
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
                        'color'       => ['rgb' => '000000'],
                    ],
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '000000'],
                    ],
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        // Tambahkan border kanan untuk kolom terakhir schedule (T)
        $lastScheduleCol   = self::COL_MONTHS[array_key_last(self::COL_MONTHS)];
        $lastScheduleRange = $lastScheduleCol . $startRow . ':' . $lastScheduleCol . $endRow;

        $sheet->getStyle($lastScheduleRange)->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    /**
     * Border medium:
     * - kiri (A) dari startRow sampai signatureEndRow
     * - kanan data (U) dari startRow sampai dataEndRow
     * - kanan luar (V) dari startRow sampai signatureEndRow
     * - bottom A..V di dataEndRow
     * - bottom A..V di signatureEndRow
     */
    private function applyMediumBorders(Worksheet $sheet, int $startRow, int $dataEndRow, int $signatureEndRow): void
    {
        // 1) Border kiri (kolom A) dari awal data sampai akhir signature
        $sheet->getStyle(self::FIRST_COL . $startRow . ':' . self::FIRST_COL . $signatureEndRow)
            ->applyFromArray([
                'borders' => [
                    'right' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['rgb' => '000000'],
                    ],
                ],
            ]);

        // 2) Border kanan untuk area DATA (kolom U) - hanya sampai baris terakhir data
        $sheet->getStyle(self::LAST_COL . $startRow . ':' . self::LAST_COL . $dataEndRow)
            ->applyFromArray([
                'borders' => [
                    'left' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['rgb' => '000000'],
                    ],
                ],
            ]);

        // 3) Border kanan luar (kolom V) - sampai akhir signature
        $sheet->getStyle(self::OUTER_COL . $startRow . ':' . self::OUTER_COL . $signatureEndRow)
            ->applyFromArray([
                'borders' => [
                    'left' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['rgb' => '000000'],
                    ],
                ],
            ]);

        // 4) Garis bawah medium di baris terakhir DATA, dari A sampai V
        //    (biar sudut kanan bawah tidak "bolong")
        $bottomDataRange = 'B' . $dataEndRow . ':' . 'T' . $dataEndRow;
        $sheet->getStyle($bottomDataRange)->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);

        // 5) Garis bawah medium paling bawah (setelah signature), dari A sampai V
        $bottomSignRange = 'B' . $signatureEndRow . ':' . 'U' . $signatureEndRow;
        $sheet->getStyle($bottomSignRange)->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);
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
            return (string) $d;
        }
    }
}
