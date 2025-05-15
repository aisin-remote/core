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

class HavImport implements WithMultipleSheets, WithEvents
{
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function sheets(): array
    {
        return [0 => $this]; // Sheet pertama
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->getDelegate();

                $npk = $sheet->getCell('C7')->getValue();

                $employee = Employee::where('npk', $npk)->first();
                $comment = $sheet->getCell('C12')->getValue();
                $year = $sheet->getCell('C13')->getCalculatedValue();

                if (!$employee) {
                    throw new \Exception("NPK {$npk} tidak ditemukan.");
                }

                if ($comment == '') {
                    throw new \Exception("Kolom Comment tidak boleh kosong");
                }

                if ($year == '') {
                    throw new \Exception("Kolom Tahun tidak boleh kosong");
                }

                $cekpk = (new HavQuadrant())->getValidatedPerformanceScores($employee->id, $sheet->getCell('C13')->getCalculatedValue());

                if (is_string($cekpk)) {
                    throw new \Exception($cekpk);
                }

                DB::transaction(function () use ($employee, $year, $comment, $sheet) {

                    $hav = new Hav();
                    $hav->employee_id = $employee->id;
                    $hav->status = 0; // 0 = Create
                    $hav->year = $year;


                    $scoreMap = [
                        1 => 'D15',
                        2 => 'H15',
                        3 => 'M15',
                        4 => 'Q15',
                        5 => 'D25',
                        6 => 'H25',
                        7 => 'M25',
                        8 => 'Q25',
                    ];

                    $quadrant = (new HavQuadrant())->updateHavFromAssessment(
                        $employee->id,
                        $sheet->getCell('C13')->getCalculatedValue(),
                        $sheet->getCell($scoreMap[1])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[2])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[3])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[4])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[5])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[6])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[7])->getCalculatedValue(),
                        $sheet->getCell($scoreMap[8])->getCalculatedValue(),
                    );
                    $hav->quadrant = $quadrant;
                    $hav->save();

                    foreach ($scoreMap as $index => $cell) {
                        $alc = Alc::find($index);
                        if (!$alc) continue;

                        HavDetail::create([
                            'hav_id' => $hav->id,
                            'alc_id' => $alc->id,
                            'score' => floatval($sheet->getCell($cell)->getCalculatedValue()),
                            'evidence' => '',
                        ]);
                    }

                    // Step 4: Save Comment History (new functionality)

                    // Step 5: Handle File Upload with Renamed File
                    if ($this->filePath) {
                        // Using the filePath passed from controller
                        $filePath = $this->filePath;

                        // Store the file path and comment in hav_comment_histories table
                        HavCommentHistory::create([
                            'hav_id' => $hav->id,
                            'employee_id' => auth()->user()->employee->id,
                            'comment' => $comment,
                            'upload' => $filePath,  // Save file path in the database
                        ]);
                    }
                });
            }
        ];
    }
}
