<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        return view('website.auth.register', compact('departments'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction(); // Pastikan transaksi aman

        try {
            // Simpan data user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'is_first_login' => true,
                'password_changed_at' => null
            ]);

            // Buat data employee
            $employee = Employee::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'position' => $request->position,
                'supervisor_id' => null // Default null, akan diisi jika bukan GM
            ]);

            if ($request->position == 'GM') {
                // GM tidak punya supervisor, tetapi bisa punya banyak department
                if ($request->has('departments')) {
                    foreach ($request->departments as $deptId) {
                        DB::table('employee_departments')->insert([
                            'employee_id' => $employee->id,
                            'department_id' => $deptId
                        ]);
                    }
                }
            } else {
                // Cari GM yang membawahi department yang sama
                $gm = DB::table('employee_departments')
                    ->join('employees', 'employee_departments.employee_id', '=', 'employees.id')
                    ->where('employees.position', 'GM')
                    ->whereIn('employee_departments.department_id', $request->departments)
                    ->select('employees.id as gm_id')
                    ->first();

                if ($gm) {
                    // Set supervisor_id sebagai GM
                    $employee->update([
                        'supervisor_id' => $gm->gm_id
                    ]);
                }

                // Assign department ke employee
                foreach ($request->departments as $deptId) {
                    DB::table('employee_departments')->insert([
                        'employee_id' => $employee->id,
                        'department_id' => $deptId
                    ]);
                }
            }

            DB::commit(); // Simpan transaksi

            return redirect()->route('login')->with('success', 'Employee successfully registered!');
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan transaksi jika ada error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
