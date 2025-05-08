<?php
namespace App\Imports;

use App\Models\Employee;
use App\Models\AstraTraining;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AstraTrainings implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $employee = Employee::where('npk', $row['npk'])->first();

            if (!$employee) {
                Log::warning("NPK {$row['npk']} tidak ditemukan untuk Education.");
                continue;
            }

            AstraTraining::create([
                'employee_id'     => $employee->id,
                'program'           => $row['materi_training'],
                'date_start'           =>$this->convertDate($row['tanggal_start_training']),
                'date_end'           => $this->convertDate($row['tanggal_end_training']),
                'institusi'           => $row['institusi'],
               
               
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
