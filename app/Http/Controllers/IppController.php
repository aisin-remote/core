<?php

namespace App\Http\Controllers;

use App\Models\Ipp;
use App\Models\IppPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;



class IppController
{
    private const CAP = [
        'activity_management' => 70,
        'people_development'  => 10,
        'crp'                 => 10,
        'special_assignment'  => 10,
    ];

    public function index()
    {
        $title = 'IPP Create';
        return view('website.ipp.index', compact('title'));
    }

    /**
     * Load initial data (AJAX)
     */
    public function init(Request $request)
    {
        $user  = auth()->user();
        $emp   = $user->employee;
        $year  = now()->format('Y');
        $empId = (int) ($emp->id ?? 0);

        // Tentukan PIC review (atasan) berdasarkan business rule
        $picReviewId = null;
        try {
            $assignLevel = method_exists($emp, 'getCreateAuth') ? $emp->getCreateAuth() : null;
            $superior    = $assignLevel && method_exists($emp, 'getSuperiorsByLevel')
                ? $emp->getSuperiorsByLevel($assignLevel)->first()
                :  null;
            $picReviewId = (int) ($superior->id ?? 0) ?: null;
        } catch (\Throwable $e) {
            $picReviewId = null;
        }

        $identitas = [
            'nama'        => (string)($emp->name ?? $user->name ?? ''),
            'department'  => (string)($emp->bagian ?? ''),
            'division'    => (string)($emp->department->division->name ?? ''),
            'section'     => (string)($emp->leadingSection->name ?? ''),
            'date_review' => '-',
            'pic_review'  => $picReviewId,
            'on_year'     => $year,
            'no_form'     => '-',
        ];

        $pointByCat = [
            'activity_management' => [],
            'people_development'  => [],
            'crp'                 => [],
            'special_assignment'  => [],
        ];
        $summary = [
            'activity_management' => 0,
            'people_development'  => 0,
            'crp'                 => 0,
            'special_assignment'  => 0,
            'total'               => 0,
        ];
        $header = null;
        $locked = false;

        if ($empId) {
            $ipp = Ipp::where('employee_id', $empId)
                ->where('on_year', $year)
                ->first();

            // Jika header ada tapi belum ada pic_review_id, isi dari kalkulasi init
            if ($ipp && !$ipp->pic_review_id && $picReviewId) {
                $ipp->update(['pic_review_id' => $picReviewId]);
            }

            if ($ipp) {
                $locked = ($ipp->status === 'submitted');

                $points = IppPoint::where('ipp_id', $ipp->id)
                    ->orderBy('id')
                    ->get();

                foreach ($points as $p) {
                    $item = [
                        'id'         => $p->id,
                        'category'   => $p->category,
                        'activity'   => (string) $p->activity,
                        'target_mid' => (string) $p->target_mid,
                        'target_one' => (string) $p->target_one,
                        'due_date'   => $p->due_date ? substr((string)$p->due_date, 0, 10) : null,
                        'weight'     => (int) $p->weight,
                        'status'     => (string) ($p->status ?? 'draft'),
                    ];
                    if (isset($pointByCat[$p->category])) {
                        $pointByCat[$p->category][] = $item;
                        $summary[$p->category]      = ($summary[$p->category] ?? 0) + (int) $p->weight;
                    }
                }

                $summary['total'] = ($summary['activity_management'] ?? 0)
                    + ($summary['people_development'] ?? 0)
                    + ($summary['crp'] ?? 0)
                    + ($summary['special_assignment'] ?? 0);

                $header = [
                    'id'            => $ipp->id,
                    'employee_id'   => $ipp->employee_id,
                    'pic_review_id' => $ipp->pic_review_id,         // << expose ke FE bila perlu
                    'status'        => (string) $ipp->status,
                    'summary'       => $ipp->summary ?: $summary,
                    'locked'        => $locked,
                ];
            }
        }

        return response()->json([
            'identitas' => $identitas,
            'ipp'       => $header,
            'points'    => $pointByCat,
            'cap'       => self::CAP,
            'locked'    => $locked,
        ]);
    }

    public function store(Request $request)
    {
        $payloadRaw = $request->input('payload');
        $payload    = is_array($payloadRaw) ? $payloadRaw : json_decode($payloadRaw ?? '[]', true);

        if (isset($payload['single_point'])) {
            return $this->storeSinglePoint($request, $payload);
        }

        return response()->json(['message' => 'Unsupported payload form. Kirim via modal per-point'], 422);
    }

    /** === SIMPAN 1 POINT (SELALU draft dari modal) === */
    private function storeSinglePoint(Request $request, array $payload)
    {
        $v = validator($payload, [
            'mode'             => ['required', Rule::in(['create', 'edit'])],
            'status'           => ['required', Rule::in(['draft', 'submitted'])],
            'cat'              => ['required', Rule::in(array_keys(self::CAP))],
            'row_id'           => ['nullable', 'integer'],
            'point.activity'   => ['required', 'string'],
            'point.target_mid' => ['nullable', 'string'],
            'point.target_one' => ['nullable', 'string'],
            'point.due_date'   => ['required', 'date'],
            'point.weight'     => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $mode   = $payload['mode'];
        $status = 'draft';                     // FE pakai draft; backend paksa draft juga
        $cat    = $payload['cat'];
        $rowId  = $payload['row_id'] ?? null;
        $p      = $payload['point'];

        $user  = auth()->user();
        $emp   = $user->employee;
        $empId = (int)($emp->id ?? 0);
        $year  = now()->format('Y');

        if (!$empId) {
            return response()->json(['message' => 'Employee tidak ditemukan pada akun ini.'], 422);
        }

        // Hitung PIC reviewer (jaga-jaga kalau belum ada header)
        $picReviewId = null;
        try {
            $assignLevel = method_exists($emp, 'getCreateAuth') ? $emp->getCreateAuth() : null;
            $superior    = $assignLevel && method_exists($emp, 'getSuperiorsByLevel')
                ? $emp->getSuperiorsByLevel($assignLevel)->first()
                :  null;
            $picReviewId = (int) ($superior->id ?? 0) ?: null;
            $picReviewName = (string) ($superior->name ?? '');
        } catch (\Throwable $e) {
            $picReviewId = null;
        }

        $headerAttrs = ['employee_id' => $empId, 'on_year' => $year];

        try {
            DB::beginTransaction();

            $ipp = Ipp::firstOrCreate(
                $headerAttrs,
                [
                    'nama'          => (string) $emp->name,
                    'department'    => (string)($emp->bagian ?? ''),
                    'division'      => (string)($emp->department->division->name ?? ''),
                    'section'       => (string)($emp->leadingSection->name ?? ''),
                    'date_review'   => null,
                    'pic_review'    => $picReviewName,                                     // legacy field (biarkan kosong)
                    'pic_review_id' => $picReviewId,                                       // << simpan relasi PIC
                    'no_form'       => '',
                    'status'        => 'draft',
                    'summary'       => [],
                ]
            );

            // Kalau header sudah ada tapi belum ada pic_review_id, isi sekarang
            if (!$ipp->pic_review_id && $picReviewId) {
                $ipp->pic_review_id = $picReviewId;
                $ipp->save();
            }

            if ($mode === 'create') {
                $point = IppPoint::create([
                    'ipp_id'     => $ipp->id,
                    'category'   => $cat,
                    'activity'   => $p['activity'],
                    'target_mid' => $p['target_mid'] ?? null,
                    'target_one' => $p['target_one'] ?? null,
                    'due_date'   => $p['due_date'],
                    'weight'     => (int)$p['weight'],
                    'status'     => $status,                    // draft
                ]);
            } else {
                $point = IppPoint::where('id', $rowId)->first();
                if (!$point) {
                    DB::rollBack();
                    return response()->json(['message' => 'Point not found'], 404);
                }

                // Cek kepemilikan
                if ((int) $point->ipp->employee_id !== $empId || (string)$point->ipp->on_year !== $year) {
                    DB::rollBack();
                    return response()->json(['message' => 'Tidak diizinkan mengubah point ini.'], 403);
                }

                $point->update([
                    'category'   => $cat,
                    'activity'   => $p['activity'],
                    'target_mid' => $p['target_mid'] ?? null,
                    'target_one' => $p['target_one'] ?? null,
                    'due_date'   => $p['due_date'],
                    'weight'     => (int)$p['weight'],
                    'status'     => $status,                    // tetap draft
                ]);
            }

            // Update summary
            $summary = IppPoint::where('ipp_id', $ipp->id)
                ->selectRaw('category, SUM(weight) as used')
                ->groupBy('category')
                ->pluck('used', 'category')
                ->toArray();
            $summary['total'] = array_sum($summary);

            $ipp->summary = $summary;
            $ipp->status  = 'draft';
            $ipp->save();

            DB::commit();

            return response()->json([
                'message' => 'Draft tersimpan',
                'row_id'  => $point->id,
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Gagal menyimpan data. Silakan coba lagi atau hubungi admin.'], 500);
        }
    }

    /** === SUBMIT ALL (ubah seluruh IPP jadi submitted) === */
    public function submit(Request $request)
    {
        $user  = auth()->user();
        $emp   = $user->employee;
        $year  = now()->format('Y');
        $empId = (int)($emp->id ?? 0);

        $ipp = Ipp::where('employee_id', $empId)->where('on_year', $year)->first();

        if (!$ipp) {
            return response()->json(['message' => 'Belum ada data IPP untuk disubmit.'], 422);
        }

        $points = IppPoint::where('ipp_id', $ipp->id)->get();
        if ($points->isEmpty()) {
            return response()->json(['message' => 'Tambahkan minimal satu point sebelum submit.'], 422);
        }

        // Validasi cap per kategori
        $summary = [];
        foreach (self::CAP as $cat => $cap) {
            $used          = (int) $points->where('category', $cat)->sum('weight');
            $summary[$cat] = $used;
            if ($used > $cap) {
                return response()->json([
                    'message' => "Bobot kategori \"" . str_replace('_', ' ', $cat) . "\" melebihi cap {$cap}%. Kurangi W% dulu."
                ], 422);
            }
        }

        // Total harus 100%
        $summary['total'] = array_sum($summary);
        if ($summary['total'] !== 100) {
            return response()->json(['message' => 'Total bobot harus tepat 100% sebelum submit.'], 422);
        }

        DB::transaction(function () use ($ipp, $summary) {
            IppPoint::where('ipp_id', $ipp->id)->update(['status' => 'submitted']);
            $ipp->update([
                'status'  => 'submitted',
                'summary' => $summary,
                // pastikan header punya pic_review_id (biarkan null kalau memang tidak ketemu)
            ]);
        });

        return response()->json(['message' => 'Berhasil submit IPP.', 'summary' => $summary]);
    }

    /** === DELETE POINT IPP === */
    public function destroyPoint(Request $request, IppPoint $point)
    {
        $user  = auth()->user();
        $emp   = $user->employee;
        $year  = now()->format('Y');
        $empId = (int)($emp->id ?? 0);

        $ipp = $point->ipp;

        if (!$ipp || (int)$ipp->employee_id !== $empId || (string)$ipp->on_year !== $year) {
            return response()->json(['message' => 'Tidak diizinkan menghapus point ini.'], 403);
        }

        if ($ipp->status === 'submitted') {
            return response()->json(['message' => 'IPP sudah submitted, tidak dapat dihapus.'], 422);
        }

        try {
            DB::beginTransaction();

            $point->delete();

            $summary = IppPoint::where('ipp_id', $ipp->id)
                ->selectRaw('category, SUM(weight) as used')
                ->groupBy('category')
                ->pluck('used', 'category')
                ->toArray();
            $summary['total'] = array_sum($summary);

            $ipp->summary = $summary;
            $ipp->save();

            DB::commit();

            return response()->json([
                'message' => 'Point dihapus.',
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Gagal menghapus point.'], 500);
        }
    }

    public function export(Request $request)
    {
        $user  = auth()->user();
        $emp   = $user->employee;
        $year  = now()->format('Y');

        abort_if(!$emp, 403, 'Employee not found for this account.');

        $ipp = Ipp::where('employee_id', $emp->id)
            ->where('on_year', $year)
            ->first();

        abort_if(!$ipp, 404, 'IPP not found.');

        $points = IppPoint::where('ipp_id', $ipp->id)->orderBy('id')->get();

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
                'weight'     => (int) $p->weight, // 0..100
            ];
        }

        $assignLevel = method_exists($emp, 'getCreateAuth') ? $emp->getCreateAuth() : null;
        $pic = $assignLevel && method_exists($emp, 'getSuperiorsByLevel')
            ? optional($emp->getSuperiorsByLevel($assignLevel)->first())->name
            : '';

        $identitas = [
            'nama'        => (string)($emp->name ?? $user->name ?? ''),
            'department'  => (string)($emp->bagian ?? ''),
            'section'     => (string)($ipp->section ?? ''),
            'division'    => (string)($ipp->division ?? ''),
            'date_review' => $ipp->date_review ? substr((string)$ipp->date_review, 0, 10) : '',
            'pic_review'  => $pic,
            'on_year'     => (string)$ipp->on_year,
        ];

        // === Open template
        $template = public_path('assets/file/Template IPP.xlsx');
        abort_unless(is_file($template), 500, 'Template file not found on server.');
        $spreadsheet = IOFactory::load($template);
        /** @var Worksheet $sheet */
        $sheet = $spreadsheet->getSheetByName('IPP form') ?? $spreadsheet->getActiveSheet();

        // Tahun (AA4:AC4) – sesuai template kamu
        $sheet->mergeCells('AA4:AC4');
        $sheet->setCellValue('AA4', $identitas['on_year']);
        $sheet->getStyle("AA4:AC4")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_TOP,
                'wrapText'   => true,
            ],
            'font' => ['name' => 'Tahoma', 'size' => 14],
        ]);

        // Header identitas
        $sheet->setCellValue('J7',  $identitas['nama']);
        $sheet->setCellValue('J8',  $identitas['department']);
        $sheet->setCellValue('J9',  $identitas['section']);
        $sheet->setCellValue('J10', $identitas['division']);
        $sheet->setCellValue('AV7', $identitas['date_review']);
        $sheet->setCellValue('AV8', $identitas['pic_review']);
        foreach (['J7', 'J8', 'J9', 'J10'] as $addr) {
            $sheet->getStyle($addr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // Blok kolom tetap
        $R_ACTIVITY_FROM = 'B';
        $R_ACTIVITY_TO = 'Q';
        $R_WEIGHT_FROM   = 'R';
        $R_WEIGHT_TO   = 'T';
        $R_MID_FROM      = 'U';
        $R_MID_TO      = 'AH';
        $R_ONE_FROM      = 'AI';
        $R_ONE_TO      = 'AU';
        $R_DUE_FROM      = 'AV';
        $R_DUE_TO      = 'BA';

        // Helper border outline
        $outlineThin = function (string $range) use ($sheet) {
            $sheet->getStyle($range)->applyFromArray([
                'borders' => [
                    'right'  => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '000000'],
                    ],
                    'left'   => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '000000'],
                    ],
                ],
            ]);
        };
        $outlineMedium = function (string $range) use ($sheet) {
            $sheet->getStyle($range)->applyFromArray([
                'borders' => [
                    'right' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['rgb' => '000000'],
                    ],
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

            // sisip baris tambahan (n-1) & cloning style dasar (agar grid rapi)
            if ($n > 1) {
                $sheet->insertNewRowBefore($baseRow + 1, $n - 1);
                for ($r = $baseRow + 1; $r <= $baseRow + $n - 1; $r++) {
                    $sheet->duplicateStyle(
                        $sheet->getStyle("B{$baseRow}:{$lastColLtr}{$baseRow}"),
                        "B{$r}:{$lastColLtr}{$r}"
                    );
                    // biarkan Excel auto-fit tinggi baris (nanti kita set manual)
                    $sheet->getRowDimension($r)->setRowHeight(-1);
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
                // Samakan font dasar seluruh baris
                $sheet->getStyle("B{$r}:{$lastColLtr}{$r}")
                    ->getFont()->setName($BASE_FONT_NAME)->setSize($BASE_FONT_SIZE);

                // --- Normalisasi teks (ganti \r\n jadi \n) dan hitung baris ---
                $activity = $this->xlText($row['activity']   ?? '');
                $mid      = $this->xlText($row['target_mid'] ?? '');
                $one      = $this->xlText($row['target_one'] ?? '');

                // berapa baris yang perlu ditampilin? ambil maksimum dari 3 kolom teks
                $maxLines = max(
                    $this->countLines($activity),
                    $this->countLines($mid),
                    $this->countLines($one),
                    1
                );

                // PROGRAM / ACTIVITY (wrap & merge)
                $sheet->mergeCells("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}");
                $sheet->setCellValue("{$R_ACTIVITY_FROM}{$r}", $activity);
                $sheet->getStyle("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);
                $outlineThin("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}");

                // WEIGHT (R:T) sebagai persen
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

                // DUE DATE (AV:BA) – tulis plain text yyyy-mm-dd
                $sheet->mergeCells("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}");
                $sheet->setCellValue("{$R_DUE_FROM}{$r}", (string)($row['due_date'] ?? ''));
                $sheet->getStyle("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $outlineMedium("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}", 'right');

                // --- Set tinggi baris berdasarkan jumlah line break ---
                // perkiraan: ~18pt per baris (Tahoma 14). Silakan adjust kalau perlu.
                $sheet->getRowDimension($r)->setRowHeight(
                    $this->calcRowHeight($maxLines, 18.0, 4.0) // 18pt/line + 4pt padding
                );
            }

            // Tambah offset baris sisipan
            $offset += max(0, $n - 1);
        }

        $fileName = 'IPP_' . $year . '_' . Str::slug((string)($emp->name ?? 'user')) . '.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'ipp_') . '.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmp);

        return response()->download(
            $tmp,
            $fileName,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /** Ganti \r\n / \r menjadi \n agar Excel paham line break. */
    private function xlText(?string $s): string
    {
        $s = (string)$s;
        return str_replace(["\r\n", "\r"], "\n", $s);
    }

    /** Hitung jumlah baris tampilan berdasarkan banyaknya "\n". */
    private function countLines(string $s): int
    {
        return max(1, substr_count($s, "\n") + 1);
    }

    /** Estimasi tinggi baris (point). */
    private function calcRowHeight(int $lines, float $perLine = 18.0, float $padding = 4.0): float
    {
        return max($perLine, $lines * $perLine + $padding);
    }


    private function colIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $n = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $n = $n * 26 + (ord($letters[$i]) - 64);
        }
        return $n;
    }
}
