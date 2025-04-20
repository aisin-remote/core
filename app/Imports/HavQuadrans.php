<?php

namespace App\Imports;

use App\Models\Assessment;
use App\Models\DetailAssessment;
use App\Models\Employee;
use App\Models\HavQuadrant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class HavQuadrans implements WithEvents
{
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->getDelegate();
                $highestRow = $sheet->getHighestRow(); // total baris di sheet

                for ($row = 4; $row <= $highestRow; $row++) {
                    $npk         = $sheet->getCell("A{$row}")->getValue();
                    $date        = $sheet->getCell("B{$row}")->getValue();
                    $description = $sheet->getCell("C{$row}")->getValue();

                    $fields = [
                        1 => ['score' => $sheet->getCell("D{$row}")->getValue(), 'strength' => $sheet->getCell("E{$row}")->getValue(), 'weakness' => $sheet->getCell("F{$row}")->getValue()],
                        2 => ['score' => $sheet->getCell("G{$row}")->getValue(), 'strength' => $sheet->getCell("H{$row}")->getValue(), 'weakness' => $sheet->getCell("I{$row}")->getValue()],
                        3 => ['score' => $sheet->getCell("J{$row}")->getValue(), 'strength' => $sheet->getCell("K{$row}")->getValue(), 'weakness' => $sheet->getCell("L{$row}")->getValue()],
                        4 => ['score' => $sheet->getCell("M{$row}")->getValue(), 'strength' => $sheet->getCell("N{$row}")->getValue(), 'weakness' => $sheet->getCell("O{$row}")->getValue()],
                        5 => ['score' => $sheet->getCell("P{$row}")->getValue(), 'strength' => $sheet->getCell("Q{$row}")->getValue(), 'weakness' => $sheet->getCell("R{$row}")->getValue()],
                        6 => ['score' => $sheet->getCell("S{$row}")->getValue(), 'strength' => $sheet->getCell("T{$row}")->getValue(), 'weakness' => $sheet->getCell("U{$row}")->getValue()],
                        7 => ['score' => $sheet->getCell("V{$row}")->getValue(), 'strength' => $sheet->getCell("W{$row}")->getValue(), 'weakness' => $sheet->getCell("X{$row}")->getValue()],
                        8 => ['score' => $sheet->getCell("Y{$row}")->getValue(), 'strength' => $sheet->getCell("Z{$row}")->getValue(), 'weakness' => $sheet->getCell("AA{$row}")->getValue()],
                    ];

                    $employee = Employee::where('npk', $npk)->first();

                    if (!$employee) {
                        Log::warning("NPK {$npk} tidak ditemukan saat import HAV (row {$row})");
                        continue;
                    }

                    DB::transaction(function () use ($employee, $date, $description, $fields) {
                        $assessment = Assessment::create([
                            'employee_id' => $employee->id,
                            'date'        => $this->convertDate($date),
                            'description' => $description
                        ]);

                        foreach ($fields as $alcId => $values) {
                            if ($values['score'] !== null) {
                                DetailAssessment::create([
                                    'assessment_id' => $assessment->id,
                                    'alc_id' => $alcId,
                                    'score' => $values['score'],
                                    'strength' => $values['strength'],
                                    'weakness' => $values['weakness'],
                                ]);
                            }
                        }

                        (new HavQuadrant())->updateHavFromAssessment(
                            $employee->id,
                            $fields[1]['score'],
                            $fields[2]['score'],
                            $fields[3]['score'],
                            $fields[4]['score'],
                            $fields[5]['score'],
                            $fields[6]['score'],
                            $fields[7]['score'],
                            $fields[8]['score'],
                        );
                    });
                }
            }
        ];
    }

    private function convertDate($value)
    {
        if (is_numeric($value)) {
            return Carbon::createFromDate(1900, 1, 1)->addDays($value - 2)->format('Y-m-d');
        }
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }
}
