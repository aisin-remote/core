<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\WorkingExperience as ExperienceModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WorkingExperience implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $employee = Employee::where('npk', $row['npk'])->first();

            if (!$employee) {
                Log::warning("NPK {$row['npk']} tidak ditemukan untuk Experience.");
                continue;
            }

            ExperienceModel::create([
                'employee_id'   => $employee->id,
                'company'       => $row['company'],
                'position' => $row['position_name'],

                'department' => $row['department_name'],
                'start_date'    => $this->convertDate($row['start_date']),
                'end_date'      => $this->convertDate($row['end_date']),
            ]);
        }
    }

    private function convertDate($value)
    {
        if (is_numeric($value)) {
            return Carbon::createFromDate(1900, 1, 1)->addDays($value - 2)->format('Y-m-d');
        }

        if ($value) {
            try {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error("Gagal parsing tanggal: $value - " . $e->getMessage());
                return null;
            }
        }

        return null;
    }

}
