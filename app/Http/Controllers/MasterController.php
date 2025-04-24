<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Department;
use App\Models\SubSection;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function getSubordinates($employeeId, $processedIds = [])
    {
        // Cegah infinite loop dengan memeriksa apakah ID sudah diproses sebelumnya
        if (in_array($employeeId, $processedIds)) {
            return collect(); // Kembalikan collection kosong untuk menghindari loop
        }

        // Tambahkan ID saat ini ke daftar yang sudah diproses
        $processedIds[] = $employeeId;

        // Ambil hanya bawahan langsung (bukan atasan)
        $employees = Employee::where('supervisor_id', $employeeId)->get();
        $subordinates = collect($employees);

        // Lanjutkan rekursi untuk mendapatkan semua bawahan di level lebih dalam
        foreach ($employees as $employee) {
            $subordinates = $subordinates->merge($this->getSubordinates($employee->id, $processedIds));
        }

        return $subordinates;
    }
    
    public function employee($company = null)
    {
        $title = 'Employee';
        $user = auth()->user();

        // Jika HRD, bisa melihat semua karyawan
        if ($user->role === 'HRD') {
            $employee = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
                ->when($company, fn($query) => $query->where('company_name', $company))
                ->where(function ($query) {
                    $query->where('user_id', '!=', auth()->id())
                        ->orWhereNull('user_id');
                })
                ->get();
        } else {
            // Jika user biasa, hanya bisa melihat bawahannya dalam satu perusahaan
            $emp = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')->where('user_id', $user->id)->first();
            if (!$emp) {
                $employee = collect();
            } else {
                $employee = $this->getSubordinates($emp->id)
                    ->where('company_name', $emp->company_name);
            }
        }

        return view('website.master.employee.index', compact('employee'));
    }
    public function department()
    {
        $departments = Department::all();
        return view('website.master.department.index', compact('departments'));
    }

    public function departmentStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        try {
            Department::create(['name' => $request->name]);

            return redirect()->back()->with('success', 'Department berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan department: ' . $e->getMessage());
        }
    }

    public function departmentDestroy($id)
    {
        try {
            $department = Department::findOrFail($id);
            $department->delete();

            return redirect()->back()->with('success', 'Department berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus department: ' . $e->getMessage());
        }
    }

    public function division()
    {
        $divisions = Division::all();
        return view('website.master.division.index', compact('divisions'));
    }
    public function section()
    {
        $sections = Section::all();
        return view('website.master.section.index', compact('sections'));
    }

    public function subSection()
    {
        $subSections = SubSection::all();
        return view('website.master.subSection.index', compact('subSections'));
    }
    public function plant()
    {
        $plants = Plant::all();
        return view('website.master.plant.index', compact('plants'));
    }
    
    public function grade()
    {
        return view('website.master.grade.index');
    }

    public function filter(Request $request)
    {
        $filter = $request->filter;

        switch ($filter) {
            case 'department':
                $data = Department::all();
                break;
            case 'section':
                $data = Section::all();
                break;
            case 'sub_section':
                $data = SubSection::all();
                break;
            default:
                $data = collect(); // kosong atau tampilkan semua
                break;
        }

        return view('layouts.partials.filter', compact('data'))->render();
    }
}
