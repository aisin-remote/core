<?php

namespace App\Exports;

use App\Models\Employee;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Concerns\FromSpreadsheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class HavSummaryExport implements FromSpreadsheet
{
    public function spreadsheet(): Spreadsheet
    {
        $templatePath = public_path('assets/file/HAV_Summary.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $subordinates = Auth::user()->subordinate()->unique()->values();
        $employees = Employee::with([
            'assessments.details',
            'havQuadrants' => fn($q) => $q->orderByDesc('created_at'),
            'performanceAppraisalHistories' => fn($q) => $q->orderBy('date'),
        ])
            ->whereHas('havQuadrants')
            ->whereIn('id', $subordinates)
            ->get();

        $startRow = 13;

        foreach ($employees as $i => $emp) {
            $row = $startRow + $i;
            $assessment = $emp->assessments->sortByDesc('created_at')->first();
            $details = $assessment ? $assessment->details->keyBy('alc_id') : collect();

            $totalScore = $details->sum(fn($d) => floatval($d->score ?? 0));
            $totalScorePercent = $totalScore ? round(($totalScore / (8 * 5)) * 100, 1) . '%' : '0%';

            $hav = $emp->havQuadrants->first();
            $quadrant = $hav->quadrant ?? null;

            $appraisals = $emp->performanceAppraisalHistories
                ->sortByDesc('date')
                ->take(3)
                ->sortBy('date')
                ->values();

            $sheet->setCellValue("B{$row}", $emp->npk);
            $sheet->setCellValue("C{$row}", $emp->name);
            $sheet->setCellValue("D{$row}", $emp->function);
            $sheet->setCellValue("E{$row}", $emp->foundation_group);
            $sheet->setCellValue("F{$row}", $emp->company_group);
            $sheet->setCellValue("G{$row}", Carbon::parse($emp->birthday_date)->age ?? '');
            $sheet->setCellValue("H{$row}", $emp->grade);
            $sheet->setCellValue("I{$row}", $emp->working_period);

            $col = 'J';
            for ($j = 1; $j <= 8; $j++) {
                $sheet->setCellValue("{$col}{$row}", $details[$j]->score ?? '');
                $col++;
            }

            $sheet->setCellValue("R{$row}", $totalScore);
            $sheet->setCellValue("S{$row}", $totalScorePercent);

            $sheet->setCellValue("T{$row}", $appraisals[0]->score ?? '');
            $sheet->setCellValue("U{$row}", $appraisals[1]->score ?? '');
            $sheet->setCellValue("V{$row}", $appraisals[2]->score ?? '');

            $sheet->setCellValue("W{$row}", $hav->assessment_score ?? '');
            $sheet->setCellValue("X{$row}", $hav->performance_score ?? '');
            $sheet->setCellValue("Y{$row}", $quadrant);

            $breakdownCol = 'Z';
            for ($j = 1; $j <= 8; $j++) {
                $sheet->setCellValue("{$breakdownCol}{$row}", $details[$j]->score ?? '');
                $breakdownCol++;
            }
        }

        return $spreadsheet;
    }
}
