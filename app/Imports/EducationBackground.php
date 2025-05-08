<?php
namespace App\Imports;

use App\Models\Employee;
use App\Models\EducationalBackground as EducationModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EducationBackground implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $employee = Employee::where('npk', $row['npk'])->first();

            if (!$employee) {
                Log::warning("NPK {$row['npk']} tidak ditemukan untuk Education.");
                continue;
            }

            EducationModel::create([
                'employee_id'     => $employee->id,
                'educational_level' => $row['education_level'],
                'major'           => $row['major'],
                'institute'       => $row['institute'],
                'start_date'      => $this->convertDate($row['start_date']),
                'end_date'        => $this->convertDate($row['end_date']),
            ]);
        }
    }

    private function convertDate($value)
    {
        // Jika hanya 4 digit angka (tahun), jadikan awal tahun
        if (preg_match('/^\d{4}$/', $value)) {
            return $value . '-01-01'; // atau gunakan '-12-31' jika akhir tahun
        }

        // Jika Excel number (numeric), konversi ke tanggal dari Excel format
        if (is_numeric($value)) {
            return Carbon::createFromDate(1900, 1, 1)->addDays($value - 2)->format('Y-m-d');
        }

        // Jika format tanggal biasa
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

}
