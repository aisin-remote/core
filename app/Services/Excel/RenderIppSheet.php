<?php
// app/Services/Excel/RenderIppSheet.php

namespace App\Services\Excel;

use App\Models\Employee;
use App\Models\Ipp;
use App\Models\IppPoint;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RenderIppSheet
{
    public function render(Spreadsheet $wb, int $ippId): void
    {
        /** @var Worksheet $sheet */
        $sheet = $wb->getSheetByName('IPP form') ?? $wb->getActiveSheet();

        // ==== ambil data ====
        $ipp     = Ipp::with(['employee', 'checkedBy', 'approvedBy'])->findOrFail($ippId);
        $owner   = Employee::findOrFail($ipp->employee_id);
        $points  = IppPoint::where('ipp_id', $ipp->id)->orderBy('id')->get();

        // Grouping
        $grouped = [
            'activity_management' => [],
            'people_development'  => [],
            'crp'                 => [],
            'special_assignment'  => [],
        ];
        foreach ($points as $p) {
            $cat = $p->category;
            if (!isset($grouped[$cat])) continue;
            $grouped[$cat][] = [
                'activity'   => (string) $p->activity,
                'target_mid' => (string) ($p->target_mid ?? ''),
                'target_one' => (string) ($p->target_one ?? ''),
                'due_date'   => $p->due_date ? substr((string)$p->due_date, 0, 10) : '',
                'weight'     => (int) $p->weight,
            ];
        }

        // PIC reviewer
        $assignLevel = method_exists($owner, 'getCreateAuth') ? $owner->getCreateAuth() : null;
        $pic = $assignLevel && method_exists($owner, 'getSuperiorsByLevel')
            ? optional($owner->getSuperiorsByLevel($assignLevel)->first())->name
            : '';

        $identitas = [
            'nama'        => (string)($owner->name ?? ''),
            'department'  => (string)($owner->bagian ?? $ipp->department ?? ''),
            'section'     => (string)($ipp->section ?? ''),
            'division'    => (string)($ipp->division ?? ''),
            'date_review' => $ipp->checked_at ? substr((string)$ipp->checked_at, 0, 10) : '',
            'pic_review'  => $pic,
            'on_year'     => (string)$ipp->on_year,
        ];

        // =========================
        // HEADER TAHUN & IDENTITAS
        // =========================
        $sheet->mergeCells('AA4:AC4');
        $sheet->setCellValue('AA4', $identitas['on_year']);
        $sheet->getStyle("AA4:AC4")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'font' => ['name' => 'Tahoma', 'size' => 14, 'bold' => true],
        ]);

        $sheet->setCellValue('J7',  $identitas['nama']);
        $sheet->setCellValue('J8',  $identitas['department']);
        $sheet->setCellValue('J9',  $identitas['section']);
        $sheet->setCellValue('J10', $identitas['division']);
        $sheet->setCellValue('AV7', $identitas['date_review']);
        $sheet->setCellValue('AV8', $identitas['pic_review']);
        foreach (['J7', 'J8', 'J9', 'J10'] as $addr) {
            $sheet->getStyle($addr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // =========================
        // TABEL KONTEN
        // =========================
        $R_ACTIVITY_FROM = 'B';
        $R_ACTIVITY_TO   = 'Q';
        $R_WEIGHT_FROM   = 'R';
        $R_WEIGHT_TO     = 'T';
        $R_MID_FROM      = 'U';
        $R_MID_TO        = 'AH';
        $R_ONE_FROM      = 'AI';
        $R_ONE_TO        = 'AU';
        $R_DUE_FROM      = 'AV';
        $R_DUE_TO        = 'BA';

        $outlineThin = function (string $range) use ($sheet) {
            $sheet->getStyle($range)->applyFromArray([
                'borders' => [
                    'right'  => ['borderStyle' => Border::BORDER_THIN,   'color' => ['rgb' => '000000']],
                    'left'   => ['borderStyle' => Border::BORDER_THIN,   'color' => ['rgb' => '000000']],
                ],
            ]);
        };
        $outlineMedium = function (string $range) use ($sheet) {
            $sheet->getStyle($range)->applyFromArray([
                'borders' => [
                    'right' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                ],
            ]);
        };

        $lastColLtr = 'BA';
        $lastColIdx = $this->colIndex($lastColLtr);

        // Posisi header kategori pada template
        $HEADER = [
            'activity_management' => 14,
            'people_development'  => 18,
            'crp'                 => 23,
            'special_assignment'  => 27,
        ];
        $order = ['activity_management', 'people_development', 'crp', 'special_assignment'];

        $BASE_FONT_NAME = 'Tahoma';
        $BASE_FONT_SIZE = 14;

        $offset = 0;
        foreach ($order as $cat) {
            $items = $grouped[$cat] ?? [];
            $n = count($items);
            if ($n === 0) continue;

            $headerAnchor = ($HEADER[$cat] ?? 13) + $offset;
            $baseRow      = $headerAnchor + 1;

            // tinggi dasar ambil dari template
            $baseRowHeight = $sheet->getRowDimension($baseRow)->getRowHeight();
            if ($baseRowHeight <= 0) {
                $baseRowHeight = 18;
            }

            // sisip baris tambahan (n-1) & clone style
            if ($n > 1) {
                $sheet->insertNewRowBefore($baseRow + 1, $n - 1);
                for ($r = $baseRow + 1; $r <= $baseRow + $n - 1; $r++) {
                    $sheet->duplicateStyle(
                        $sheet->getStyle("B{$baseRow}:{$lastColLtr}{$baseRow}"),
                        "B{$r}:{$lastColLtr}{$r}"
                    );
                    $sheet->getRowDimension($r)->setRowHeight($baseRowHeight);
                }
            }

            for ($i = 0; $i < $n; $i++) {
                $r   = $baseRow + $i;
                $row = $items[$i];

                // bersihkan isi row
                for ($c = 2; $c <= $lastColIdx; $c++) {
                    $sheet->setCellValueByColumnAndRow($c, $r, null);
                }

                $sheet->getStyle("B{$r}:{$lastColLtr}{$r}")
                    ->getBorders()->getInside()->setBorderStyle(Border::BORDER_NONE);
                $sheet->getStyle("B{$r}:{$lastColLtr}{$r}")
                    ->getFont()->setName($BASE_FONT_NAME)->setSize($BASE_FONT_SIZE);

                // Normalisasi + paksa word-wrap manual
                $activity = $this->autoWrapText(
                    $this->xlText($row['activity'] ?? ''),
                    60 // kira-kira 60 karakter per baris untuk kolom activity
                );
                $mid = $this->autoWrapText(
                    $this->xlText($row['target_mid'] ?? ''),
                    60
                );
                $one = $this->autoWrapText(
                    $this->xlText($row['target_one'] ?? ''),
                    60
                );

                // hitung jumlah baris
                $maxLines = max(
                    $this->countLines($activity),
                    $this->countLines($mid),
                    $this->countLines($one),
                    1
                );

                // PROGRAM / ACTIVITY
                $sheet->mergeCells("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}");
                $sheet->setCellValue("{$R_ACTIVITY_FROM}{$r}", $activity);
                $sheet->getStyle("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);
                $outlineThin("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}");

                // WEIGHT (R:T)
                $sheet->mergeCells("{$R_WEIGHT_FROM}{$r}:{$R_WEIGHT_TO}{$r}");
                $sheet->setCellValue("{$R_WEIGHT_FROM}{$r}", ((int)($row['weight'] ?? 0)) / 100);
                $sheet->getStyle("{$R_WEIGHT_FROM}{$r}:{$R_WEIGHT_TO}{$r}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("{$R_WEIGHT_FROM}{$r}:{$R_WEIGHT_TO}{$r}")
                    ->getNumberFormat()->setFormatCode('0%');
                $outlineThin("{$R_WEIGHT_FROM}{$r}:{$R_WEIGHT_TO}{$r}");

                // MID YEAR (U:AH)
                $sheet->mergeCells("{$R_MID_FROM}{$r}:{$R_MID_TO}{$r}");
                $sheet->setCellValue("{$R_MID_FROM}{$r}", $mid);
                $sheet->getStyle("{$R_MID_FROM}{$r}:{$R_MID_TO}{$r}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);
                $outlineThin("{$R_MID_FROM}{$r}:{$R_MID_TO}{$r}");

                // ONE YEAR (AI:AU)
                $sheet->mergeCells("{$R_ONE_FROM}{$r}:{$R_ONE_TO}{$r}");
                $sheet->setCellValue("{$R_ONE_FROM}{$r}", $one);
                $sheet->getStyle("{$R_ONE_FROM}{$r}:{$R_ONE_TO}{$r}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);
                $outlineThin("{$R_ONE_FROM}{$r}:{$R_ONE_TO}{$r}");

                // DUE DATE (AV:BA)
                $sheet->mergeCells("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}");
                $sheet->setCellValue("{$R_DUE_FROM}{$r}", (string)($row['due_date'] ?? ''));
                $sheet->getStyle("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $outlineMedium("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}");

                // wrap untuk seluruh range baris
                $sheet->getStyle("{$R_ACTIVITY_FROM}{$r}:{$R_DUE_TO}{$r}")
                    ->getAlignment()->setWrapText(true);

                // tinggi baris = tinggi dasar x jumlah baris
                $sheet->getRowDimension($r)->setRowHeight($baseRowHeight * $maxLines);
            }

            // offset baris sisipan
            $offset += max(0, $n - 1);
        }

        // =========================
        // SIGNATURE
        // =========================

        $renderSignature = function (string $rect, string $anchor, ?string $imgPath, ?string $date, string $dateRect) use ($sheet) {
            $sheet->mergeCells($rect);
            $sheet->mergeCells($dateRect);

            if (!empty($date)) {
                $sheet->setCellValue(explode(':', $dateRect)[0], $date);
                $sheet->getStyle($dateRect)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => false,
                    ],
                    'font' => ['name' => 'Tahoma', 'size' => 12],
                ]);
            }

            if ($imgPath && is_file($imgPath)) {
                $drw = new Drawing();
                $drw->setName('Signature');
                $drw->setDescription('Signature');
                $drw->setPath($imgPath);
                $drw->setCoordinates($anchor);
                $drw->setResizeProportional(true);
                $drw->setHeight(165);
                $drw->setOffsetX(49);
                $drw->setOffsetY(-10);
                $drw->setWorksheet($sheet);
            }
        };

        $resolveSignaturePath = function (?Employee $emp): ?string {
            if (!$emp) return null;
            if (!empty($emp->signature_path)) {
                $p = storage_path('app/public/' . ltrim($emp->signature_path, '/'));
                if (is_file($p)) return $p;
            }
            $p2 = public_path('storage/signatures/' . $emp->id . '.png');
            if (is_file($p2)) return $p2;
            return null;
        };

        // posisi signature mengikuti offset dari tabel
        $sigRowTop    = 34 + $offset; // baris pertama kotak tanda tangan
        $sigRowBottom = 35 + $offset; // baris kedua kotak tanda tangan
        $dateRow      = 42 + $offset; // baris date

        $AREAS = [
            'approved' => [ // kiri
                'rect'   => "H{$sigRowTop}:Q{$sigRowBottom}",
                'anchor' => "H{$sigRowTop}",
                'date'   => "I{$dateRow}:S{$dateRow}",
            ],
            'checked' => [  // tengah
                'rect'   => "V{$sigRowTop}",
                'anchor' => "V{$sigRowTop}",
                'date'   => "U{$dateRow}:AG{$dateRow}",
            ],
            'employee' => [ // kanan
                'rect'   => "AK{$sigRowTop}",
                'anchor' => "AK{$sigRowTop}",
                'date'   => "AJ{$dateRow}:AU{$dateRow}",
            ],
        ];

        $status = strtolower((string)$ipp->status);

        $checkedByEmp   = $ipp->checkedBy;
        $approvedByEmp  = $ipp->approvedBy;

        $submitAt   = $ipp->last_submitted_at ?? $ipp->submitted_at;
        $checkedAt  = $ipp->checked_at;
        $approvedAt = $ipp->approved_at;

        $submitDate   = $submitAt   ? substr((string)$submitAt,   0, 10) : null;
        $checkedDate  = $checkedAt  ? substr((string)$checkedAt,  0, 10) : null;
        $approvedDate = $approvedAt ? substr((string)$approvedAt, 0, 10) : null;

        $ownerSig    = $resolveSignaturePath($owner);
        $checkedSig  = $resolveSignaturePath($checkedByEmp);
        $approvedSig = $resolveSignaturePath($approvedByEmp);

        if ($status === 'submitted') {
            $renderSignature(
                $AREAS['employee']['rect'],
                $AREAS['employee']['anchor'],
                $ownerSig,
                $submitDate,
                $AREAS['employee']['date']
            );
        } elseif ($status === 'checked') {
            $renderSignature(
                $AREAS['employee']['rect'],
                $AREAS['employee']['anchor'],
                $ownerSig,
                $submitDate,
                $AREAS['employee']['date']
            );
            $renderSignature(
                $AREAS['checked']['rect'],
                $AREAS['checked']['anchor'],
                $checkedSig,
                $checkedDate,
                $AREAS['checked']['date']
            );
        } elseif ($status === 'approved') {
            $renderSignature(
                $AREAS['employee']['rect'],
                $AREAS['employee']['anchor'],
                $ownerSig,
                $submitDate,
                $AREAS['employee']['date']
            );
            $renderSignature(
                $AREAS['checked']['rect'],
                $AREAS['checked']['anchor'],
                $checkedSig,
                $checkedDate,
                $AREAS['checked']['date']
            );
            $renderSignature(
                $AREAS['approved']['rect'],
                $AREAS['approved']['anchor'],
                $approvedSig,
                $approvedDate,
                $AREAS['approved']['date']
            );
        }
    }

    // ===== helpers =====

    private function colIndex(string $letters): int
    {
        $letters = strtoupper(trim($letters));
        $sum = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $sum = $sum * 26 + (ord($letters[$i]) - 64);
        }
        return $sum;
    }

    private function xlText(string $s): string
    {
        // normalisasi CRLF agar wrapText konsisten
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        return $s;
    }

    private function countLines(string $s): int
    {
        return max(1, substr_count($s, "\n") + 1);
    }

    private function autoWrapText(string $s, int $width): string
    {
        $s = trim($s);
        if ($s === '') {
            return '';
        }

        // pakai wordwrap supaya pecah di spasi, bukan di tengah kata
        return wordwrap($s, $width, "\n", false);
    }
}
