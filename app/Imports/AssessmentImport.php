<?php

namespace App\Imports;

use App\Models\Alc;
use App\Models\Hav;
use App\Models\Employee;
use App\Models\HavDetail;
use App\Models\HavQuadrant;
use App\Models\HavCommentHistory;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AssessmentImport implements WithMultipleSheets, WithEvents
{
    protected $filePath;
    protected $havId;
    protected $detailData;
    protected $updateHav; // Flag untuk menentukan apakah perlu update HAV

    public function __construct($filePath, $havId = null, array $detailData = [], $updateHav = true)
    {
        $this->filePath = $filePath;
        $this->havId = $havId;
        $this->detailData = $detailData;
        $this->updateHav = $updateHav; // Tambahkan parameter baru
    }

    public function sheets(): array
    {
        return [0 => $this];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->getDelegate();

                $npk = $sheet->getCell('C7')->getValue();
                $employee = Employee::where('npk', $npk)->first();
                $year = $sheet->getCell('C13')->getCalculatedValue();

                if (!$employee) {
                    throw new \Exception("NPK {$npk} tidak ditemukan.");
                }

                if ($year == '') {
                    throw new \Exception("Kolom Tahun tidak boleh kosong");
                }

                DB::beginTransaction();

                try {
                    // === Step 1: Quadrant & save ===
                    $scoreMap = [
                        1 => 'D15',
                        2 => 'I15',
                        3 => 'O15',
                        4 => 'T15',
                        5 => 'D25',
                        6 => 'I25',
                        7 => 'O25',
                        8 => 'T25',
                    ];

                    $quadrant = (new HavQuadrant())->updateHavFromAssessment(
                        $employee->id,
                        $year,
                        $sheet->getCell($scoreMap[1])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[2])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[3])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[4])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[5])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[6])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[7])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[8])->getCalculatedValue(),
                    );

                    // === Step 2: Buat atau ambil HAV ===
                    if ($this->havId) {
                        $hav = Hav::findOrFail($this->havId);
                    } else {
                        $hav = new Hav();
                        $hav->employee_id = $employee->id;
                    }

                    $hav->status = 2;
                    $hav->year = $year;
                    $hav->quadrant = $quadrant;
                    $hav->save();

                    // === Step 3: Buat atau update HavDetail ===
                    $evidenceMap = [
                        1 => 'E17',
                        2 => 'J17',
                        3 => 'P17',
                        4 => 'U17',
                        5 => 'E27',
                        6 => 'J27',
                        7 => 'P27',
                        8 => 'U27',
                    ];

                    foreach ($scoreMap as $index => $cell) {
                        $alc = Alc::find($index);
                        if (!$alc)
                            continue;

                        $detail = $this->detailData[$alc->id] ?? [
                            'score' => 0,
                            'strength' => '',
                            'weakness' => '',
                            'suggestion_development' => '',
                        ];

                        $data = [
                            'score' => floatval($sheet->getCell($cell)->getCalculatedValue()),
                            'evidence' => $sheet->getCell($evidenceMap[$index])->getCalculatedValue(),
                            'suggestion_development' => $detail['suggestion_development'] == '' ? null : $detail['suggestion_development'],
                            'is_assessment' => 1,
                            'updated_at' => now(),
                        ];

                        // update kalau sudah ada,kalau tidak ada create
                        $existing = HavDetail::where('hav_id', $hav->id)->where('alc_id', $alc->id)->first();
                        if ($existing) {
                            $existing->update($data);
                        } else {
                            HavDetail::create(array_merge($data, [
                                'hav_id' => $hav->id,
                                'alc_id' => $alc->id,
                            ]));
                        }
                    }

                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e; // Re-throw exception setelah rollback
                }
            }
        ];
    }
}
