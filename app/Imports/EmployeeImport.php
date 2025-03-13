<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToCollection, WithHeadingRow
{
    public function collection(\Illuminate\Support\Collection $rows)
    {
        try {
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    // **1. Cek apakah NPK sudah ada**
                    if (Employee::where('npk', $row['npk'])->exists()) {
                        continue; // Skip jika NPK sudah ada
                    }

                    // **2. Cari department berdasarkan nama**
                    $department = Department::where('name', $row['department'])->first();
                    if (!$department) {
                        continue; // Skip jika department tidak ditemukan
                    }

                    $position = strtolower($row['position']);

                    // **3. Konversi tanggal**
                    $joinDate = $this->convertExcelDate($row['join_date']);
                    $lastPromoteDate = $this->convertExcelDate($row['last_promote_date']);

                    // **4. Hitung Working Period (Tahun)**
                    $workingPeriod = $joinDate ? Carbon::parse($joinDate)->diffInYears(Carbon::now()) : null;

                    // **5. Simpan employee baru**
                    $employee = Employee::create([
                        'npk'              => $row['npk'],
                        'name'             => $row['name'],
                        'gender'           => $row['gender'],
                        'company_name'     => $row['company'],
                        'function'         => $row['function'],
                        'company_group'    => $row['company_group'],
                        'aisin_entry_date' => $joinDate,
                        'foundation_group' => $row['foundation_group'],
                        'position'         => $row['position'],
                        'grade'            => $row['grade'],
                        'last_promote_date'=> $lastPromoteDate,
                        'working_period'   => $workingPeriod,
                    ]);

                    // **6. Hubungkan Employee dengan Department**
                    $employee->departments()->attach($department->id);

                    // **7. Cari atasan dalam department yang sama**
                    $supervisor = Employee::whereHas('departments', function ($query) use ($department) {
                        $query->where('departments.id', $department->id);
                    })
                    ->whereIn('position', ['manager', 'section head', 'supervisor'])
                    ->orderByRaw("FIELD(position, 'manager', 'section head', 'supervisor')")
                    ->first();

                    // **8. Update employee dengan supervisor yang ditemukan**
                    $employee->update([
                        'supervisor_id' => $supervisor ? $supervisor->id : null,
                    ]);

                    // **9. Jika jabatan adalah "manager", buat akun user untuk login**
                    if ($position === 'manager') {
                        User::create([
                            'name'        => $row['name'],
                            'email'       => strtolower(str_replace(' ', '.', $row['name'])) . '@aiia.co.id',
                            'password'    => bcrypt('aiia'),
                            'role'        => 'manager',
                            'employee_id' => $employee->id,
                        ]);
                    }
                }
            });

            // **10. Simpan pesan sukses ke session**
            Session::flash('success', 'Data karyawan berhasil diimport!');
        } catch (\Exception $e) {
            // **11. Simpan pesan error ke session**
            Session::flash('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    /**
     * Helper function untuk mengonversi tanggal dari Excel.
     */
    private function convertExcelDate($date)
    {
        if (is_numeric($date)) {
            return Carbon::createFromDate(1900, 1, 1)->addDays($date - 2)->format('Y-m-d');
        } else {
            try {
                return Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}