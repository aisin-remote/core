<?php

namespace App\Http\Controllers;

use App\Models\IpaHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class IpaExportController extends Controller
{
    /**
     * Export IPA ke Excel sesuai template.
     *
     * Kolom (1-based):
     *  A (1)        : No
     *  B-E (2-5)    : Program / Activity
     *  F-H (6-8)    : Weight (%)
     *  I-L (9-12)   : One Year Target
     *  M-Q (13-17)  : One Year Achievement
     *  R-S (18-19)  : Score (R)
     *  T-U (20-21)  : Total Score (W x R)
     *
     * Data mulai baris 13.
     */
    public function export(Request $request, IpaHeader $ipa)
    {
        $ctx = ['ipa_id' => $ipa->id, 'route' => 'ipa.export'];
        Log::info('IPA Export: start', $ctx);

        try {
            // ========== 1) Ambil data ==========
            $ipa->loadMissing(['achievements', 'ipp.points', 'employee']);

            $ach = collect($ipa->achievements ?: []);
            Log::info('IPA Export: loaded achievements', $ctx + ['count' => $ach->count()]);

            // Peta kategori (urutan & CAP untuk judul)
            $catMeta = [
                'activity_management' => ['roman' => 'I.',  'title' => 'ACTIVITY MANAGEMENT',               'cap' => 70],
                'people_development'  => ['roman' => 'II.', 'title' => 'PEOPLE MANAGEMENT',                 'cap' => 10],
                'crp'                 => ['roman' => 'III.', 'title' => 'COST REDUCTION PROGRAM',            'cap' => 10],
                'special_assignment'  => ['roman' => 'IV.', 'title' => 'SPECIAL ASSIGNMENT & IMPROVEMENT',  'cap' => 10],
            ];
            $catOrder = array_keys($catMeta);

            // Flatten baris data dari achievements + tentukan kategori
            $rows = $ach->map(function ($a) {
                $ipp = optional($a->ippPoint);
                $cat = $a->category ?? $ipp->category ?? null;

                $w = (float)($a->weight ?? $ipp->weight ?? 0);
                $r = (float)($a->self_score ?? 0);

                return [
                    'category'    => $cat, // bisa null
                    'program'     => (string)($a->title ?? $ipp->activity ?? $ipp->title ?? '(tanpa judul)'),
                    'target'      => (string)($a->one_year_target ?? $ipp->target_one ?? ''),
                    'achievement' => (string)($a->one_year_achievement ?? ''),
                    'weight'      => $w,
                    'score'       => $r,
                    'source'      => $a->ipp_point_id ? 'IPP' : 'Custom',
                ];
            });

            // Kelompokkan per kategori (null/unknown taruh di ujung)
            $grouped = $rows->groupBy(function ($r) {
                return $r['category'] ?? '__unknown';
            });

            // Buat urutan final kategori: yang dikenal sesuai $catOrder, sisanya (unknown/others) alfabetis di belakang
            $knownGroups = collect($catOrder)
                ->filter(fn($k) => $grouped->has($k) && $grouped[$k]->isNotEmpty())
                ->values();

            $otherGroups = $grouped->keys()
                ->reject(fn($k) => in_array($k, $catOrder, true))
                ->filter(fn($k) => $grouped[$k]->isNotEmpty())
                ->sort()
                ->values();

            $orderedCats = $knownGroups->merge($otherGroups)->all();

            Log::info('IPA Export: category ordering prepared', $ctx + [
                'ordered' => $orderedCats,
            ]);

            // ========== 2) Buka template ==========
            $templatePath = public_path('assets/file/Template IPA.xlsx');
            if (!is_file($templatePath)) {
                Log::error('IPA Export: template not found', $ctx + ['template' => $templatePath]);
                abort(404, 'Template tidak ditemukan: assets/file/Template IPA.xlsx');
            }
            $spreadsheet = IOFactory::load($templatePath);
            $sheet       = $spreadsheet->getActiveSheet();
            Log::info('IPA Export: template loaded', $ctx + ['sheet' => $sheet->getTitle()]);

            // (opsional) logo
            $logoPath = storage_path('app/public/company-logo.png');
            if (is_file($logoPath)) {
                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Company Logo');
                $drawing->setPath($logoPath);
                $drawing->setHeight(40);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);
            }

            // Header identitas pakai anchor (optional)
            $this->setByAnchor($sheet, '[[EMPLOYEE_NAME]]', $ipa->employee_name ?? optional($ipa->employee)->name ?? '-');
            $this->setByAnchor($sheet, '[[NIK]]',          $ipa->employee_nik ?? '-');
            $this->setByAnchor($sheet, '[[DEPARTMENT]]',   $ipa->department_name ?? '-');
            $this->setByAnchor($sheet, '[[PERIOD]]',       $ipa->period ?? ($ipa->year ?? date('Y')));

            // ========== 3) Mapping kolom & baris start ==========
            $map = [
                'no'    => ['c1' => 1,  'c2' => 1],   // A
                'prog'  => ['c1' => 2,  'c2' => 5],   // B-E
                'w'     => ['c1' => 6,  'c2' => 8],   // F-H
                'tgt'   => ['c1' => 9,  'c2' => 12],  // I-L
                'achv'  => ['c1' => 13, 'c2' => 17],  // M-Q
                'score' => ['c1' => 18, 'c2' => 19],  // R-S
                'total' => ['c1' => 20, 'c2' => 21],  // T-U
            ];
            $rowStart = 13;
            $firstCol = 1;
            $lastCol  = 21;

            // ========== 4) Cari baris "Total" bawaan template ==========
            $totalRow = $this->findLabeledRow($sheet, 'Total', $rowStart, $map['prog']['c1'], $map['total']['c2']);
            Log::info('IPA Export: total row probe', $ctx + ['template_total_row' => $totalRow]);

            // Hitung total baris yang dibutuhkan = jumlah item + jumlah judul kategori
            $lineCount = 0;
            foreach ($orderedCats as $catKey) {
                $cnt = $grouped[$catKey]->count();
                if ($cnt > 0) {
                    $lineCount += 1;       // header kategori
                    $lineCount += $cnt;    // item dalam kategori
                }
            }
            Log::info('IPA Export: line count', $ctx + ['line_count' => $lineCount]);

            // Sisipkan baris jika perlu (hanya kalau ada baris Total di template)
            if ($totalRow && $lineCount > 0) {
                $available = max(0, $totalRow - $rowStart);
                $extra     = $lineCount - $available;
                if ($extra > 0) {
                    $sheet->insertNewRowBefore($totalRow, $extra);
                    $totalRow += $extra;
                    Log::info('IPA Export: inserted rows', $ctx + ['inserted' => $extra, 'total_row_after' => $totalRow]);
                }
            }

            // ========== 5) Tulis kategori + data ==========
            $r = $rowStart;

            foreach ($orderedCats as $catKey) {
                $items = $grouped[$catKey];
                if ($items->isEmpty()) continue;

                // --- baris header kategori ---
                $title = $this->categoryTitle($catKey, $catMeta);
                $this->writeCategoryRow($sheet, $r, $title, $map, $firstCol, $lastCol);
                $r++;

                // --- baris data dalam kategori ---
                $no = 1;
                foreach ($items as $it) {
                    $this->mergeRow($sheet, $r, $map);

                    $sheet->setCellValueByColumnAndRow($map['no']['c1'],   $r, $no++);
                    $sheet->setCellValueByColumnAndRow($map['prog']['c1'], $r, $it['program']);
                    $sheet->setCellValueByColumnAndRow($map['tgt']['c1'],  $r, $it['target']);
                    $sheet->setCellValueByColumnAndRow($map['achv']['c1'], $r, $it['achievement']);

                    // weight (sebagai fraction)
                    $sheet->setCellValueByColumnAndRow($map['w']['c1'], $r, ((float)$it['weight']) / 100);
                    $sheet->getStyleByColumnAndRow($map['w']['c1'], $r)->getNumberFormat()->setFormatCode('0.00%');
                    $sheet->getStyleByColumnAndRow($map['w']['c1'], $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // score
                    $sheet->setCellValueByColumnAndRow($map['score']['c1'], $r, (float)$it['score']);
                    $sheet->getStyleByColumnAndRow($map['score']['c1'], $r)->getNumberFormat()->setFormatCode('0.00');
                    $sheet->getStyleByColumnAndRow($map['score']['c1'], $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // total = weight * score
                    $F = $this->addr($map['w']['c1'], $r);
                    $S = $this->addr($map['score']['c1'], $r);
                    $sheet->setCellValueByColumnAndRow($map['total']['c1'], $r, "=ROUND({$F}*{$S},2)");
                    $sheet->getStyleByColumnAndRow($map['total']['c1'], $r)->getNumberFormat()->setFormatCode('0.00');
                    $sheet->getStyleByColumnAndRow($map['total']['c1'], $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $this->styleRowLeftRight($sheet, $r, $firstCol, $lastCol);
                    $r++;
                }
            }

            $lastDataRow = max($rowStart - 1, $r - 1);
            Log::info('IPA Export: writing complete', $ctx + [
                'first_data_row' => $rowStart,
                'last_data_row'  => $lastDataRow,
                'total_row'      => $totalRow
            ]);

            // ========== 6) Update baris Total bawaan template ==========
            if ($totalRow && $lastDataRow >= $rowStart) {
                // kolom Weight
                $sheet->setCellValueByColumnAndRow(
                    $map['w']['c1'],
                    $totalRow,
                    sprintf('=SUM(%s:%s)', $this->addr($map['w']['c1'], $rowStart), $this->addr($map['w']['c1'], $lastDataRow))
                );
                $sheet->getStyleByColumnAndRow($map['w']['c1'], $totalRow)->getNumberFormat()->setFormatCode('0.00%');
                $sheet->getStyleByColumnAndRow($map['w']['c1'], $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // kolom Score
                $sheet->setCellValueByColumnAndRow(
                    $map['score']['c1'],
                    $totalRow,
                    sprintf('=SUM(%s:%s)', $this->addr($map['score']['c1'], $rowStart), $this->addr($map['score']['c1'], $lastDataRow))
                );
                $sheet->getStyleByColumnAndRow($map['score']['c1'], $totalRow)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyleByColumnAndRow($map['score']['c1'], $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // kolom Total
                $sheet->setCellValueByColumnAndRow(
                    $map['total']['c1'],
                    $totalRow,
                    sprintf('=SUM(%s:%s)', $this->addr($map['total']['c1'], $rowStart), $this->addr($map['total']['c1'], $lastDataRow))
                );
                $sheet->getStyleByColumnAndRow($map['total']['c1'], $totalRow)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyleByColumnAndRow($map['total']['c1'], $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $this->styleTotalRow($sheet, $totalRow, $firstCol, $lastCol);
                Log::info('IPA Export: totals updated', $ctx + ['total_row_final' => $totalRow]);
            } else {
                Log::warning('IPA Export: skip totals (no template total row or no data)', $ctx + [
                    'total_row' => $totalRow,
                    'last_data_row' => $lastDataRow
                ]);
            }

            // ========== 7) Print setup A4 ==========
            $sheet->getPageSetup()
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setFitToWidth(1)->setFitToHeight(0);

            // ========== 8) Download ==========
            $filename = 'IPA-' . $ipa->id . '-' . now()->format('Ymd_His') . '.xlsx';
            $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($sheet));
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            ob_start();
            $writer->save('php://output');
            $excel = ob_get_clean();

            Log::info('IPA Export: done', $ctx + ['filename' => $filename]);

            return response($excel, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
                'Pragma'              => 'public',
            ]);
        } catch (\Throwable $e) {
            Log::error('IPA Export: exception', $ctx + [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => substr($e->getTraceAsString(), 0, 4000),
            ]);
            report($e);
            abort(500, 'Gagal export IPA: ' . $e->getMessage());
        }
    }

    /* =================== Helpers =================== */

    private function addr(int $col, int $row): string
    {
        return Coordinate::stringFromColumnIndex($col) . $row;
    }

    /** Merge area per baris sesuai mapping */
    private function mergeRow(Worksheet $sheet, int $row, array $map): void
    {
        foreach ($map as $range) {
            if ($range['c1'] !== $range['c2']) {
                $sheet->mergeCellsByColumnAndRow($range['c1'], $row, $range['c2'], $row);
            }
        }
    }

    /** Tulis baris header kategori (merge B..U, tebal, fill) */
    private function writeCategoryRow(Worksheet $sheet, int $row, string $title, array $map, int $colStart, int $colEnd): void
    {
        // Kosongkan kolom A (No)
        $sheet->setCellValueByColumnAndRow($map['no']['c1'], $row, null);

        // Merge dari kolom Program sampai Total
        $sheet->mergeCellsByColumnAndRow($map['prog']['c1'], $row, $colEnd, $row);
        $sheet->setCellValueByColumnAndRow($map['prog']['c1'], $row, $title);

        // Style: bold, fill abu, border kiri-kanan tebal, vertical middle
        $range = $this->addr($map['prog']['c1'], $row) . ':' . $this->addr($colEnd, $row);
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F4F6');

        // Garis kiri-kanan keseluruhan baris
        $sheet->getStyleByColumnAndRow($colStart, $row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyleByColumnAndRow($colEnd,   $row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);

        // Tinggi baris
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    /** Border kiri-kanan + vertical center */
    private function styleRowLeftRight(Worksheet $sheet, int $row, int $colStart, int $colEnd): void
    {
        for ($c = $colStart; $c <= $colEnd; $c++) {
            $st = $sheet->getStyleByColumnAndRow($c, $row);
            if ($c === $colStart) {
                $st->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
            }
            if ($c === $colEnd) {
                $st->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
            }
            $st->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->getRowDimension($row)->setRowHeight(18);
    }

    /** Sorot baris "Total" (kiri-kanan medium + fill ringan) */
    private function styleTotalRow(Worksheet $sheet, int $row, int $colStart, int $colEnd): void
    {
        $range = $this->addr($colStart, $row) . ':' . $this->addr($colEnd, $row);
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyleByColumnAndRow($colStart, $row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyleByColumnAndRow($colEnd,   $row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF7F7F7');
    }

    /** Anchor exact (return [row,col] atau null) */
    private function findAnchor(Worksheet $sheet, string $needle): ?array
    {
        $maxRow = $sheet->getHighestRow();
        $maxCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        for ($r = 1; $r <= $maxRow; $r++) {
            for ($c = 1; $c <= $maxCol; $c++) {
                $v = (string)$sheet->getCellByColumnAndRow($c, $r)->getValue();
                if (trim($v) === $needle) return [$r, $c];
            }
        }
        return null;
    }

    /** Isi cell berdasarkan anchor bila ada */
    private function setByAnchor(Worksheet $sheet, string $anchor, $value, ?string $numFormat = null): void
    {
        if ($pos = $this->findAnchor($sheet, $anchor)) {
            [$r, $c] = $pos;
            $sheet->setCellValueByColumnAndRow($c, $r, $value);
            if ($numFormat) {
                $sheet->getStyleByColumnAndRow($c, $r)->getNumberFormat()->setFormatCode($numFormat);
            }
        }
    }

    /**
     * Cari baris label tertentu di rentang kolom.
     * Return nomor baris atau null.
     */
    private function findLabeledRow(Worksheet $sheet, string $label, int $searchFromRow, int $colFrom, int $colTo): ?int
    {
        $needle = mb_strtolower(trim($label));
        $maxRow = $sheet->getHighestRow();

        for ($r = $searchFromRow; $r <= $maxRow; $r++) {
            for ($c = $colFrom; $c <= $colTo; $c++) {
                $v = $sheet->getCellByColumnAndRow($c, $r)->getCalculatedValue();
                if (is_string($v) && mb_strtolower(trim($v)) === $needle) {
                    return $r;
                }
            }
        }
        return null;
    }

    /** Buat judul kategori + CAP (mis. "I. ACTIVITY MANAGEMENT (Cap 70%)") */
    private function categoryTitle(?string $key, array $catMeta): string
    {
        if ($key && isset($catMeta[$key])) {
            $m = $catMeta[$key];
            return sprintf('%s %s (Cap %d%%)', $m['roman'], $m['title'], $m['cap']);
        }
        // kategori tak dikenal
        $safe = strtoupper(str_replace('_', ' ', (string)$key));
        $safe = $safe ?: 'OTHERS';
        return "— {$safe} —";
    }
}
