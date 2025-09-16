<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\LocalTemporaryFile;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class IppExport implements WithEvents
{
    use Exportable;

    private const SHEET_NAME = 'IPP form';

    // Posisi kolom di template
    private const COL = [
        'activity' => 'B',   // Program / Activity
        'weight'   => 'F',   // Weight (%)
        'mid'      => 'L',   // Mid Year (optional, biarkan kosong jika tidak dipakai)
        'one'      => 'R',   // One Year (Apr–Mar)
        'due'      => 'AD',  // Due Date
    ];

    // Baris header kategori di template (sebelum ada penyisipan)
    private const HEADER = [
        'activity_management' => 14, // B14
        'people_development'  => 18, // B18
        'crp'                 => 23, // B23
        'special_assignment'  => 27, // B27
    ];

    private array  $identitas;
    private array  $groupedPoints;
    private string $templatePath;
    private bool   $written = false;

    public function __construct(array $identitas, array $groupedPoints, string $templatePath)
    {
        $this->identitas     = $identitas;
        $this->groupedPoints = $groupedPoints;
        $this->templatePath  = $templatePath;

        Log::debug('[IPPExport] Construct', [
            'has_template' => file_exists($templatePath),
            'template'     => $templatePath,
            'counts'       => [
                'act' => count($groupedPoints['activity_management'] ?? []),
                'peo' => count($groupedPoints['people_development']  ?? []),
                'crp' => count($groupedPoints['crp']                 ?? []),
                'spe' => count($groupedPoints['special_assignment']  ?? []),
            ],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $event->writer->reopen(new LocalTemporaryFile($this->templatePath), Excel::XLSX);
                // Pastikan sheet aktif adalah IPP form (kalau belum, tetap bisa diambil nanti di AfterSheet)
                Log::debug('[IPPExport] BeforeWriting reopened', [
                    'active_sheet' => $event->writer->getSheetByIndex(0)->getTitle()
                ]);
            },

            AfterSheet::class => function (AfterSheet $event) {
                if ($this->written) return;

                /** @var Worksheet $ws */
                $ws = $event->sheet->getDelegate()->getParent()->getSheetByName(self::SHEET_NAME);
                if (!$ws) {
                    // Pass pertama (sebelum reopen) — sheet belum ada, skip
                    Log::debug('[IPPExport] AfterSheet pass skipped (template sheet not yet available)', [
                        'event_sheet' => $event->sheet->getTitle(),
                    ]);
                    return;
                }

                // ===== Isi identitas (sesuai permintaan) =====
                $ws->setCellValue('J7',  (string)($this->identitas['nama']        ?? ''));
                $ws->setCellValue('J8',  (string)($this->identitas['department']  ?? ''));
                $ws->setCellValue('J9',  (string)($this->identitas['section']     ?? ''));
                $ws->setCellValue('J10', (string)($this->identitas['division']    ?? ''));
                $ws->setCellValue('AV7', (string)($this->identitas['date_review'] ?? ''));
                $ws->setCellValue('AV8', (string)($this->identitas['pic_review']  ?? ''));

                foreach (['J7', 'J8', 'J9', 'J10'] as $addr) {
                    $ws->getStyle($addr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // ===== Tulis points per kategori dengan INSERT ROW =====
                // Urutan kategori harus sesuai vertical order di template:
                $order = [
                    'activity_management',
                    'people_development',
                    'crp',
                    'special_assignment',
                ];

                $offset = 0; // total baris yang sudah disisipkan sejauh ini
                foreach ($order as $cat) {
                    $headerRow = self::HEADER[$cat] + $offset; // posisi header terkini
                    $items     = $this->groupedPoints[$cat] ?? [];
                    $count     = count($items);

                    if ($count <= 0) continue;

                    $firstDataRow = $headerRow + 1; // sisip tepat di bawah header
                    // Sisip N baris kosong → bagian bawahnya terdorong turun
                    $ws->insertNewRowBefore($firstDataRow, $count);

                    // Copy style dari "baris template" (baris yang tadinya ada di bawah header).
                    // Setelah insert, baris template bergeser ke (firstDataRow + count)
                    $srcRow    = $firstDataRow + $count;
                    $srcRange  = self::COL['activity'] . $srcRow . ':' . self::COL['due'] . $srcRow;
                    $dstRange  = self::COL['activity'] . $firstDataRow . ':' . self::COL['due'] . ($firstDataRow + $count - 1);
                    try {
                        $ws->duplicateStyle($ws->getStyle($srcRange), $dstRange);
                    } catch (\Throwable $e) {
                        // aman di-skip kalau gagal copy style
                        Log::debug('[IPPExport] duplicateStyle skipped', ['err' => $e->getMessage()]);
                    }

                    // Tulis data per baris
                    for ($i = 0; $i < $count; $i++) {
                        $r   = $firstDataRow + $i;
                        $row = $items[$i];

                        $ws->setCellValue(self::COL['activity'] . $r, (string)($row['activity']   ?? ''));
                        $ws->setCellValue(self::COL['weight']  . $r, (int)   ($row['weight']     ?? 0));
                        if (!empty(self::COL['mid'])) {
                            $ws->setCellValue(self::COL['mid']   . $r, (string)($row['target_mid'] ?? ''));
                            $ws->getStyle(self::COL['mid'] . $r)->getAlignment()
                                ->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                        }
                        $ws->setCellValue(self::COL['one']     . $r, (string)($row['target_one'] ?? ''));
                        $ws->setCellValue(self::COL['due']     . $r, (string)($row['due_date']   ?? ''));

                        // wrap text untuk kolom teks panjang
                        foreach (['activity', 'one'] as $k) {
                            $ws->getStyle(self::COL[$k] . $r)->getAlignment()
                                ->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                        }
                    }

                    // Geser anchor kategori berikutnya sebanyak N baris yang baru disisipkan
                    $offset += $count;
                }

                $this->written = true;
                Log::debug('[IPPExport] Done writing with anchored inserts', ['inserted_rows_total' => $offset]);
            },
        ];
    }
}
