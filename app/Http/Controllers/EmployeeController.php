<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Tampilkan daftar karyawan
     */
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
        return view('website.employee.create');
    }

    /**
     * Simpan data karyawan ke database
     */
    public function store(Request $request)
    {
        // Validasi data input dari user
        $validatedData = $request->validate([
            'npk' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'identity_number' => 'required|string|unique:employees',
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

        // Simpan file foto jika ada
        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('employee_photos', 'public');
        }

        // Simpan data ke database
        Employee::create($validatedData);

        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil ditambahkan!');
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

    public function profile()
    {
        return view('website.employee.profile.index');
    }

}
