<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                    Log::info('Memproses NPK: ' . $row['npk']);

                    // **1. Cek apakah NPK sudah ada**
                    if (Employee::where('npk', $row['npk'])->exists()) {
                        Log::warning("NPK {$row['npk']} sudah ada, dilewati.");
                        continue; // Skip jika NPK sudah ada
                    }

                    // // **2. Cari department berdasarkan nama**
                    // $department = Department::where('name', $row['department'])->first();
                    // if (!$department) {
                    //     Log::warning("Departemen {$row['department']} tidak ditemukan, dilewati.");
                    //     continue; // Skip jika department tidak ditemukan
                    // }

                    $position = strtolower($row['position']);

                    // **3. Konversi tanggal**
                    $joinDate = $this->convertExcelDate($row['join_date']);
                    $birthday = $this->convertExcelDate($row['birthday_date']);
                    // **4. Hitung Working Period (Tahun)**
                    $workingPeriod = $joinDate ? Carbon::parse($joinDate)->diffInYears(Carbon::now()) : null;

                    // **5. Simpan employee baru**
                    Log::info("Membuat Employee baru: {$row['name']}");
                    $employee = Employee::create([
                        'npk' => $row['npk'],
                        'name' => $row['name'],
                        'phone_number' => $row['phone_number'],
                        'birthday_date' => $birthday,
                        'gender' => $row['gender'],
                        'company_name' => $row['company_name'],
                        'aisin_entry_date' => $joinDate,
                        'position' => $row['position'],
                        'grade' => $row['grade'],
                        'grade_astra' => $row['grade_astra'],
                        'working_period' => $workingPeriod,
                    ]);

                    Log::info("Employee ID: {$employee->id} berhasil dibuat.");

                    // **6. Hubungkan Employee dengan Department**
                    // $employee->departments()->attach($department->id);
                    // Log::info("Employee {$employee->id} terhubung ke Department ID {$department->id}");

                    // **7. Cari atasan dalam department yang sama**
                    // $supervisor = Employee::whereHas('departments', function ($query) use ($department) {
                    //     $query->where('departments.id', $department->id);
                    // })
                    // ->whereIn('position', ['manager', 'section head', 'supervisor'])
                    // ->orderByRaw("FIELD(position, 'manager', 'section head', 'supervisor')")
                    // ->first();

                    // // **8. Update employee dengan supervisor yang ditemukan**
                    // $employee->update([
                    //     'supervisor_id' => $supervisor ? $supervisor->id : null,
                    // ]);
                    // Log::info("Employee {$employee->id} mendapatkan Supervisor ID " . ($supervisor ? $supervisor->id : 'NULL'));

                    // **9. Jika jabatan adalah "manager", buat akun user untuk login**
                    if ($position === 'manager') {
                        User::create([
                            'name' => $row['name'],
                            'email' => strtolower(str_replace(' ', '.', $row['name'])) . '@aiia.co.id',
                            'password' => bcrypt('aiia'),
                            'role' => 'manager',
                            'employee_id' => $employee->id,
                            'is_first_login' => true,
                            'password_changed_at' => null
                        ]);
                        Log::info("Akun user dibuat untuk {$row['name']} dengan email " . strtolower(str_replace(' ', '.', $row['name'])) . '@aiia.co.id');
                    }
                }
            });

            // **10. Simpan pesan sukses ke session**
            Session::flash('success', 'Data karyawan berhasil diimport!');
            Log::info("Import selesai, tidak ada error.");
        } catch (\Exception $e) {
            Log::error("Terjadi error saat import: " . $e->getMessage());
            Session::flash('error', 'Gagal mengimport data.');
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
