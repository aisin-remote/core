<?php
namespace App\Imports;

use App\Models\Employee;
use App\Models\PerformanceAppraisalHistory as AppraisalModel;
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
            $employee = Employee::where('npk', $row['npk'])->first();

            if (!$employee) {
                Log::warning("NPK {$row['npk']} tidak ditemukan untuk Appraisal.");
                continue;
            }

            AppraisalModel::create([
                'employee_id' => $employee->id,
                'score'       => $row['score'],
                'description'       => $row['description'],
                'date'        => $this->convertDate($row['date']),
            ]);
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
