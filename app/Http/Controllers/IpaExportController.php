<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\IpaHeader;
use Carbon\Carbon;
use DateTimeInterface;
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
            // Tambahkan checkedBy & approvedBy supaya bisa tulis nama tanda tangan
            $ipa->loadMissing(['achievements', 'ipp.points', 'employee', 'checkedBy', 'approvedBy']);

            $ach = collect($ipa->achievements ?: []);
            Log::info('IPA Export: loaded achievements', $ctx + ['count' => $ach->count()]);

            // Peta kategori (urutan & CAP untuk judul)
            $catMeta = [
                'activity_management' => ['roman' => 'I.',  'title' => 'ACTIVITY MANAGEMENT',              'cap' => 70],
                'people_development'  => ['roman' => 'II.', 'title' => 'PEOPLE MANAGEMENT',                'cap' => 10],
                'crp'                 => ['roman' => 'III.', 'title' => 'COST REDUCTION PROGRAM',           'cap' => 10],
                'special_assignment'  => ['roman' => 'IV.', 'title' => 'SPECIAL ASSIGNMENT & IMPROVEMENT', 'cap' => 10],
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

            // Format tanggal
            $fmt = function ($dt) {
                if (empty($dt)) return null;

                // Sudah objek tanggal?
                if ($dt instanceof DateTimeInterface) {
                    return $dt->timezone(config('app.timezone'))->format('d M Y');
                }

                // Kadang string "0000-00-00 00:00:00" atau spasi kosong
                $s = trim((string)$dt);
                if ($s === '' || $s === '0000-00-00' || $s === '0000-00-00 00:00:00') {
                    return null;
                }

                try {
                    return Carbon::parse($s)->timezone(config('app.timezone'))->format('d M Y');
                } catch (\Throwable $e) {
                    // Kalau gagal parse, bisa kamu log untuk investigasi
                    // Log::warning('Failed to parse date', ['val' => $dt, 'msg' => $e->getMessage()]);
                    return null;
                }
            };

            // Kelompokkan per kategori (null/unknown taruh di ujung)
            $grouped = $rows->groupBy(function ($r) {
                return $r['category'] ?? '__unknown';
            });

            // Urutan kategori final
            $knownGroups = collect($catOrder)
                ->filter(fn($k) => $grouped->has($k) && $grouped[$k]->isNotEmpty())
                ->values();

            $otherGroups = $grouped->keys()
                ->reject(fn($k) => in_array($k, $catOrder, true))
                ->filter(fn($k) => $grouped[$k]->isNotEmpty())
                ->sort()
                ->values();

            $orderedCats = $knownGroups->merge($otherGroups)->all();

            Log::info('IPA Export: category ordering prepared', $ctx + ['ordered' => $orderedCats]);

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

            // Header identitas
            // dd($ipa->employee->company_name);
            $sheet->setCellValue('D6',  $ipa->employee->name);
            $sheet->setCellValue('D7',  $ipa->employee?->department?->name);
            $sheet->setCellValue('D8',  $ipa->employee?->division?->name);

            $sheet->setCellValue('S4',  $fmt($ipa->submitted_at));
            $sheet->setCellValue('S6', $ipa->employee?->company_name);
            $sheet->setCellValue('S7', $ipa?->checked_at);
            $checkedName  = $this->safePersonName($ipa, 'checkedBy')  ?: '-';
            $sheet->setCellValue('S8', $checkedName);

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
                $roman = $catMeta[$catKey]['roman'] ?? '';
                $label = $this->categoryLabel($catKey, $catMeta);
                $this->writeCategoryRow($sheet, $r, $roman, $label, $map, $firstCol, $lastCol);
                $r++;

                // --- baris data dalam kategori ---
                $no = 1;
                foreach ($items as $it) {
                    $this->mergeRow($sheet, $r, $map);

                    $sheet->setCellValueByColumnAndRow($map['no']['c1'],   $r, $no++);
                    $sheet->setCellValueByColumnAndRow($map['prog']['c1'], $r, $it['program']);
                    $sheet->setCellValueByColumnAndRow($map['tgt']['c1'],  $r, $it['target']);
                    $sheet->setCellValueByColumnAndRow($map['achv']['c1'], $r, $it['achievement']);

                    // weight (fraction)
                    $sheet->setCellValueByColumnAndRow($map['w']['c1'], $r, ((float)$it['weight']) / 100);
                    $sheet->getStyleByColumnAndRow($map['w']['c1'], $r)->getNumberFormat()->setFormatCode('0%');
                    $sheet->getStyleByColumnAndRow($map['w']['c1'], $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // score
                    $sheet->setCellValueByColumnAndRow($map['score']['c1'], $r, (float)$it['score']);
                    $sheet->getStyleByColumnAndRow($map['score']['c1'], $r)->getNumberFormat()->setFormatCode('0');
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
                $sheet->getStyleByColumnAndRow($map['w']['c1'], $totalRow)->getNumberFormat()->setFormatCode('0%');
                $sheet->getStyleByColumnAndRow($map['w']['c1'], $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // kolom Score
                $sheet->setCellValueByColumnAndRow(
                    $map['score']['c1'],
                    $totalRow,
                    sprintf('=SUM(%s:%s)', $this->addr($map['score']['c1'], $rowStart), $this->addr($map['score']['c1'], $lastDataRow))
                );
                $sheet->getStyleByColumnAndRow($map['score']['c1'], $totalRow)->getNumberFormat()->setFormatCode('0');
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

            // ========== 7) Tulis nama-nama di baris di atas "Date/Tanggal" ==========
            $signRow = $this->findSignatureNamesRow($sheet);   // <<-- dinamis
            Log::info('IPA Export: signature anchor', $ctx + ['sign_row' => $signRow]);

            if ($signRow) {
                // Ambil nama:
                $approvedName = $this->safePersonName($ipa, 'approvedBy') ?: '-'; // superior of superior
                $checkedName  = $this->safePersonName($ipa, 'checkedBy')  ?: '-'; // superior
                $empName      = $ipa->employee_name ?? optional($ipa->employee)->name ?? '-';

                // I–L (9..12) -> approved_by
                $sheet->mergeCellsByColumnAndRow(9,  $signRow, 11, $signRow);
                $sheet->setCellValueByColumnAndRow(9,  $signRow, $approvedName);
                $sheet->getStyleByColumnAndRow(9,  $signRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                // M–O (13..15) -> checked_by
                $sheet->mergeCellsByColumnAndRow(12, $signRow, 16, $signRow);
                $sheet->setCellValueByColumnAndRow(12, $signRow, $checkedName);
                $sheet->getStyleByColumnAndRow(12, $signRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                // Q–U (17..21) -> employee
                $sheet->mergeCellsByColumnAndRow(17, $signRow, 21, $signRow);
                $sheet->setCellValueByColumnAndRow(17, $signRow, $empName);
                $sheet->getStyleByColumnAndRow(17, $signRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension($signRow)->setRowHeight(20);
            } else {
                Log::warning('IPA Export: signature row not found; names skipped', $ctx);
            }


            // ===========================
            //  SIGNATURES BY STATUS
            // ===========================
            // 1) Temukan baris header label & baris tanggal
            $hdrRow  = $this->findLastRowByAnyLabelContains($sheet, ['superior of superior', 'superior', 'employee']);
            $dateRow = $this->findLastRowByAnyLabelContains($sheet, ['date', 'tanggal']);
            $signRow = $this->findSignatureNamesRow($sheet); // sudah dihitung di atas

            // Jika ketemu semua jangkar utama, siapkan area gambar & tanggalnya
            if ($hdrRow && $signRow && $dateRow) {
                $imgTop    = max($hdrRow + 1, 1);       // baris mulai gambar
                $imgBottom = max($signRow - 1, $imgTop); // baris akhir gambar

                // Util: buat rectangle "A1:C3" dari kol,baris
                $rect = function (int $c1, int $r1, int $c2, int $r2): string {
                    return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c1) . $r1 . ':' .
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c2) . $r2;
                };

                // Area per peran (kolom 1-based)
                $C = [
                    'approved' => ['c1' => 9,  'c2' => 11], // I..K
                    'checked'  => ['c1' => 12, 'c2' => 16], // L..P
                    'employee' => ['c1' => 17, 'c2' => 21], // Q..U
                ];

                // Util: tulis tanggal di baris dateRow, rata kiri
                $writeDate = function (Worksheet $sheet, int $c1, int $c2, int $dateRow, ?string $text) use ($rect): void {
                    if (!$text) return;
                    $range = $rect($c1, $dateRow, $c2, $dateRow);
                    $sheet->mergeCells($range);
                    $sheet->setCellValueByColumnAndRow($c1, $dateRow, $text);
                    $sheet->getStyle($range)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => false,
                        ],
                        'font' => ['name' => 'Tahoma', 'size' => 11],
                    ]);
                };

                // Util: pasang gambar tanda tangan di pojok kiri atas area (anchor)
                $placeSignature = function (Worksheet $sheet, int $c1, int $c2, int $rTop, int $rBottom, ?string $imgPath) use ($rect): void {
                    if (!$imgPath || !is_file($imgPath)) return;
                    // Merge area agar bersih
                    $area = $rect($c1, $rTop, $c2, $rBottom);
                    $sheet->mergeCells($area);

                    // Anchor di kiri-atas area
                    $anchorCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c1) . $rTop;

                    $drw = new Drawing();
                    $drw->setName('Signature');
                    $drw->setDescription('Signature');
                    $drw->setPath($imgPath);
                    $drw->setCoordinates($anchorCell);
                    $drw->setResizeProportional(true);
                    // Tinggi kira-kira menyesuaikan blok (silakan tweak jika perlu)
                    $drw->setHeight(120);
                    $drw->setOffsetX(8);
                    $drw->setOffsetY(0);
                    $drw->setWorksheet($sheet);
                };

                // Helper: resolve path tanda tangan dari Employee (punya kamu sudah ada)
                $resolveSignaturePath = function (?Employee $emp): ?string {
                    if (!$emp) return null;
                    if (!empty($emp->signature_path)) {
                        $p = storage_path('app/public/' . ltrim($emp->signature_path, '/'));
                        if (is_file($p)) return $p;
                    }
                    $p2 = public_path('storage/signatures/' . $emp->id . '.png');
                    return is_file($p2) ? $p2 : null;
                };

                // Tarik model utk ttd
                $emp      = optional($ipa)->employee;
                $checker  = optional($ipa)->checkedBy;
                $approver = optional($ipa)->approvedBy;

                // Path gambar
                $empSign      = $resolveSignaturePath($emp);
                $checkerSign  = $resolveSignaturePath($checker);
                $approverSign = $resolveSignaturePath($approver);

                $submittedAt = $fmt($ipa->submitted_at ?? null);
                $checkedAt   = $fmt($ipa->checked_at ?? null);
                $approvedAt  = $fmt($ipa->approved_at ?? null);

                // Gambar & tanggal sesuai status
                switch ($ipa->status) {
                    case 'submitted':
                        // employee only
                        $placeSignature($sheet, $C['employee']['c1'], $C['employee']['c2'], $imgTop, $imgBottom, $empSign);
                        $writeDate($sheet,  $C['employee']['c1'], $C['employee']['c2'], $dateRow, $submittedAt);
                        break;

                    case 'checked':
                        // employee + checker
                        $placeSignature($sheet, $C['employee']['c1'], $C['employee']['c2'], $imgTop, $imgBottom, $empSign);
                        $writeDate($sheet,  $C['employee']['c1'], $C['employee']['c2'], $dateRow, $submittedAt);

                        $placeSignature($sheet, $C['checked']['c1'],  $C['checked']['c2'],  $imgTop, $imgBottom, $checkerSign);
                        $writeDate($sheet,  $C['checked']['c1'],  $C['checked']['c2'],  $dateRow, $checkedAt);
                        break;

                    case 'approved':
                        // employee + checker + approver
                        $placeSignature($sheet, $C['employee']['c1'], $C['employee']['c2'], $imgTop, $imgBottom, $empSign);
                        $writeDate($sheet,  $C['employee']['c1'], $C['employee']['c2'], $dateRow, $submittedAt);

                        $placeSignature($sheet, $C['checked']['c1'],  $C['checked']['c2'],  $imgTop, $imgBottom, $checkerSign);
                        $writeDate($sheet,  $C['checked']['c1'],  $C['checked']['c2'],  $dateRow, $checkedAt);

                        $placeSignature($sheet, $C['approved']['c1'], $C['approved']['c2'], $imgTop, $imgBottom, $approverSign);
                        $writeDate($sheet,  $C['approved']['c1'], $C['approved']['c2'], $dateRow, $approvedAt);
                        break;

                    default:
                        // Draft/revise: kosongkan (atau bisa tulis watermark "Draft")
                        break;
                }
            } else {
                Log::warning('IPA Export: signature anchors incomplete', $ctx + [
                    'hdrRow'  => $hdrRow,
                    'signRow' => $signRow,
                    'dateRow' => $dateRow,
                ]);
            }

            // ========== 8) Print setup A4 ==========
            $sheet->getPageSetup()
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setFitToWidth(1)->setFitToHeight(0);

            // ========== 9) Download ==========
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
    /** Tulis baris header kategori TANPA MERGE; roman di kolom A, label di kolom B, warna baris utuh */
    private function writeCategoryRow(
        Worksheet $sheet,
        int $row,
        string $roman,
        string $label,
        array $map,
        int $colStart,
        int $colEnd
    ): void {
        // 1) Isi kolom A dengan roman, bold, center
        $sheet->setCellValueByColumnAndRow($map['no']['c1'], $row, $roman);
        $sheet->getStyleByColumnAndRow($map['no']['c1'], $row)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2) Isi kolom B dengan label kategori (kolom lain dikosongkan)
        $sheet->setCellValueByColumnAndRow($map['prog']['c1'], $row, $label);
        for ($c = $map['prog']['c1'] + 1; $c <= $colEnd; $c++) {
            $sheet->setCellValueByColumnAndRow($c, $row, null);
        }

        // 3) Style seluruh baris (B..U) tanpa merge: fill + bold + align kiri
        $range = $this->addr($colStart, $row) . ':' . $this->addr($colEnd, $row);
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF3F4F6'], // sama seperti sebelumnya
            ],
        ]);

        // 4) Border kiri & kanan baris
        $sheet->getStyleByColumnAndRow($colStart, $row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyleByColumnAndRow($colEnd,   $row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);

        // 5) Tinggi baris
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

    /** Cari baris mana saja yang berisi salah satu label (untuk "Date/Tanggal") */
    /** Cari baris terakhir yang MENGANDUNG salah satu label (case-insensitive). */
    private function findLastRowByAnyLabelContains(Worksheet $sheet, array $labels): ?int
    {
        // Normalisasi: lower, hilangkan ":" dan spasi ganda
        $norm = function ($s) {
            $s = mb_strtolower((string)$s);
            $s = str_replace(':', '', $s);
            return trim(preg_replace('/\s+/', ' ', $s));
        };

        $needles = array_map($norm, $labels);

        $maxRow = $sheet->getHighestRow();
        $maxCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $last   = null;

        for ($r = 1; $r <= $maxRow; $r++) {
            for ($c = 1; $c <= $maxCol; $c++) {
                $val = $sheet->getCellByColumnAndRow($c, $r)->getCalculatedValue();
                if (!is_string($val)) continue;
                $hay = $norm($val);
                foreach ($needles as $needle) {
                    if ($needle !== '' && str_contains($hay, $needle)) {
                        $last = max($last ?? 0, $r);
                        break;
                    }
                }
            }
        }
        return $last;
    }

    /**
     * Tentukan baris untuk MENULIS NAMA tanda tangan:
     * - Ambil baris "Date/Tanggal" paling bawah, lalu minus 1.
     * - Jika tidak ketemu, fallback dari header "Superior of Superior/Superior/Employee".
     */
    private function findSignatureNamesRow(Worksheet $sheet): ?int
    {
        // Utama: cari semua "Date / Tanggal", ambil yang TERAKHIR
        $dateRow = $this->findLastRowByAnyLabelContains($sheet, ['date', 'tanggal']);
        if ($dateRow) {
            return max(1, $dateRow - 1);
        }

        // Fallback: cari baris label area tanda tangan
        $hdrRow = $this->findLastRowByAnyLabelContains($sheet, [
            'superior of superior',
            'superior',
            'employee'
        ]);
        if ($hdrRow) {
            // Pada template umum kamu, nama ada ±6 baris di bawah header label tsb (22 -> 28).
            // Kalau suatu saat berubah, cukup sesuaikan offset ini.
            $offset = 6;
            return $hdrRow + $offset;
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

    /** Ambil nama dari relasi aman; fallback '-' bila tidak ada */
    private function safePersonName(IpaHeader $ipa, string $relation): ?string
    {
        try {
            $rel = optional($ipa)->{$relation};
            // Jika relasi langsung berupa employee model
            $name = data_get($rel, 'name');
            if ($name) return $name;

            // Jika relasi adalah user->employee / employee relation
            $name = data_get($rel, 'employee.name');
            if ($name) return $name;
        } catch (\Throwable $e) {
            Log::warning('IPA Export: safePersonName failed', [
                'ipa_id'    => $ipa->id ?? null,
                'relation'  => $relation,
                'message'   => $e->getMessage(),
            ]);
        }
        return null;
    }

    /** Buat label kategori tanpa roman (mis. "ACTIVITY MANAGEMENT (Cap 70%)") */
    private function categoryLabel(?string $key, array $catMeta): string
    {
        if ($key && isset($catMeta[$key])) {
            $m = $catMeta[$key];
            return sprintf('%s (Cap %d%%)', $m['title'], $m['cap']);
        }
        $safe = strtoupper(str_replace('_', ' ', (string)$key)) ?: 'OTHERS';
        return "— {$safe} —";
    }
}
