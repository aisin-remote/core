<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Tampilkan daftar karyawan
     */

    public function getSubordinates($employeeId)
    {
        $employees = Employee::where('supervisor_id', $employeeId)->get();
        $subordinates = collect($employees);

        foreach ($employees as $employee) {
            $subordinates = $subordinates->merge($this->getSubordinates($employee->id));
        }

        return $subordinates;
    }

    public function index(Request $request)
    {
        $title = 'Employee';
        $query = Employee::query();

        // Filter berdasarkan perusahaan jika ada parameter 'company'
        if ($request->has('company')) {
            $query->where('company_name', $request->company);
        }

        $employees = $query->get();

        // Cek apakah halaman adalah '/master/employee' atau '/master/employee?company=...'
        $isMasterPage = $request->path() === 'master/employee' && !$request->has('company');

        return view('website.employee.index', compact('employees', 'title', 'isMasterPage'));
    }


    /**
     * Form tambah karyawan
     */
    public function create()
    {
        $title = 'Add Employee';
        return view('website.employee.create', compact('title'));
    }

    /**
     * Simpan data karyawan ke database
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi data input
            $validatedData = $request->validate([
                'npk' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'identity_number' => 'required|string|unique:employees',
                'birthday_date' => 'required|date',
                'gender' => 'required|in:Male,Female',
                'company_name' => 'required|string',
                'function' => 'required|string',
                'aisin_entry_date' => 'required|date',
                'working_period' => 'nullable|integer',
                'company_group' => 'required|string',
                'foundation_group' => 'required|string',
                'position' => 'required|string',
                'grade' => 'required|string',
                'last_promote_date' => 'nullable|date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Simpan file foto jika ada
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $request->file('photo')->store('employee_photos', 'public');
            }

            // Ambil user yang sedang login
            $loggedInUser = auth()->user();
            $loggedInEmployee = Employee::where('user_id', $loggedInUser->id)->first();

            // Cari atasan langsung berdasarkan posisi
            $supervisor = $this->findSupervisor($validatedData['position']);

            // Jika atasan langsung tidak ditemukan, gunakan user yang login sebagai atasan
            if (!$supervisor && $loggedInEmployee) {
                $supervisor = $loggedInEmployee;
            }

            // Tambahkan supervisor_id ke data yang disimpan
            $validatedData['supervisor_id'] = $supervisor ? $supervisor->id : null;

            // Simpan data employee ke database
            $employee = Employee::create($validatedData);

            // Jika ada supervisor, ambil department dari supervisor atau user yang login
            if ($supervisor) {
                $departments = DB::table('employee_departments')
                    ->where('employee_id', $supervisor->id)
                    ->pluck('department_id');

                foreach ($departments as $deptId) {
                    DB::table('employee_departments')->insert([
                        'employee_id' => $employee->id,
                        'department_id' => $deptId
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('employee.index')->with('success', 'Karyawan berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    private function findSupervisor($position)
    {
        $hierarchy = [
            'GM' => null,
            'Manager' => 'GM',
            'Coordinator' => 'Manager',
            'Section Head' => 'Coordinator',
            'Supervisor' => 'Section Head'
        ];

        $supervisorPosition = $hierarchy[$position] ?? null;

        if (!$supervisorPosition) {
            return null;
        }

        return Employee::where('position', $supervisorPosition)->first();
    }




    /**
     * Tampilkan detail karyawan
     */
    public function show($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();
        return view('website.employee.show', compact('employee'));
    }


    public function edit($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail(); // Cari berdasarkan npk
        return view('website.employee.update', compact('employee'));
    }

    public function update(Request $request, $npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'birthday_date' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'company_name' => 'required|string',
            'function' => 'required|string',
            'position_name' => 'required|string',
            'aisin_entry_date' => 'required|date',
            'working_period' => 'nullable|integer',
            'company_group' => 'required|string',
            'foundation_group' => 'required|string',
            'position' => 'required|string',
            'grade' => 'required|string',
            'last_promote_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::delete('public/' . $employee->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('employee_photos', 'public');
        }

        $employee->update($validatedData);

        return redirect()->route('employee.index')->with('success', 'Data karyawan berhasil diperbarui!');
    }

    public function destroy($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();

        if ($employee->photo) {
            Storage::delete('public/' . $employee->photo);
        }

        $employee->delete();

        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil dihapus!');
    }

    public function profile($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();
        return view('website.employee.profile.index', compact('employee'));
    }

}
