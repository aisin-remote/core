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
    protected $havId;

    public function __construct($filePath, $havId = null)
    {
        $this->filePath = $filePath;
        $this->havId = $havId;
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

                $cekpk = (new HavQuadrant())->getValidatedPerformanceScores($employee->id, $sheet->getCell('C13')->getCalculatedValue() - 1);

                if (is_string($cekpk)) {
                    throw new \Exception($cekpk);
                }

                DB::transaction(function () use ($employee, $year, $comment, $sheet) {

                    if ($this->havId) {
                        // Update HAV existing
                        $hav = Hav::findOrFail($this->havId);
                        $hav->status = 0; // reset status ke 'created' saat revisi
                        $hav->year = $year;

                        // Hapus detail lama
                        HavDetail::where('hav_id', $hav->id)->delete();

                        // Optional: hapus comment history sebelumnya kalau ingin bersih
                        // HavCommentHistory::where('hav_id', $hav->id)->delete();
                    } else {
                        // Insert baru
                        $hav = new Hav();
                        $hav->employee_id = $employee->id;
                        $hav->status = 0;
                        $hav->year = $year;
                    }



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

                    $developmentMap = [
                        1 => 'F17',
                        2 => 'K17',
                        3 => 'Q17',
                        4 => 'V17',
                        5 => 'F27',
                        6 => 'K27',
                        7 => 'Q27',
                        8 => 'V27',
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
                            'evidence' => $sheet->getCell($evidenceMap[$index])->getCalculatedValue(),
                            'suggestion_development' => $sheet->getCell($developmentMap[$index])->getCalculatedValue(),
                            'is_assessment' => 0,
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
