<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\PerformanceAppraisalHistory as AppraisalModel;
use App\Models\HavQuadrant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PerformanceAppraisal implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Cek jika data kosong atau score tidak ada
            if (empty($row['score']) || empty($row['npk'])) {
                Log::warning("Data kosong atau tidak lengkap untuk NPK {$row['npk']}");
                continue;  // Skip baris ini
            }

            // Proses selanjutnya
            $employee = Employee::where('npk', $row['npk'])->first();

            if (!$employee) {
                Log::warning("NPK {$row['npk']} tidak ditemukan untuk Appraisal.");
                continue;  // Skip baris ini
            }

            // Proses penyimpanan data
            AppraisalModel::create([
                'employee_id' => $employee->id,
                'score'       => $row['score'],
                'date'        => \Carbon\Carbon::parse($row['date'])->startOfYear()->toDateString(), // hasil: "1970-01-01"
            ]);


            // (new HavQuadrant())->updateHavFromPerformance($employee->id);
        }

    }

    private function convertDate($value)
    {
        if (is_numeric($value)) {
            return Carbon::createFromDate(1900, 1, 1)->addDays($value - 2)->format('Y-m-d');
        }
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }
}
