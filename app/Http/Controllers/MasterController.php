<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Department;
use App\Models\SubSection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function employee(Request $request, $company = null)
    {
        $title = 'Employee';
        $user = auth()->user();
        $search = $request->get('search');

        if ($user->role === 'HRD') {
            $query = Employee::with(
                    'subSection.section.department',
                    'leadingSection.department',
                    'leadingDepartment.division'
                )
                ->when($company, fn($q) => $q->where('company_name', $company))
                ->where(function ($q) {
                    $q->where('user_id', '!=', auth()->id())
                    ->orWhereNull('user_id');
                })
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('npk', 'like', "%{$search}%")
                            ->orWhere('position', 'like', "%{$search}%");
                    });
                });

            $employees = $query->paginate(10);
        } else {
            $emp = Employee::with(
                        'subSection.section.department',
                        'leadingSection.department',
                        'leadingDepartment.division'
                    )
                    ->where('user_id', $user->id)
                    ->first();

            if (!$emp) {
                // Jika tidak ada data, kita buat paginator kosong
                $employees = new LengthAwarePaginator([], 0, 10);
            } else {
                $subordinates = $this->getSubordinates($emp->id)
                    ->where('company_name', $emp->company_name);

                // Jika $subordinates adalah Query Builder
                if ($subordinates instanceof \Illuminate\Database\Eloquent\Builder ||
                    $subordinates instanceof \Illuminate\Database\Query\Builder) {
                    $employees = $subordinates->when($search, function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                            ->orWhere('npk', 'like', "%{$search}%")
                            ->orWhere('position', 'like', "%{$search}%");
                        })
                        ->paginate(10);
                } elseif ($subordinates instanceof \Illuminate\Support\Collection) {
                    // Jika getSubordinates() mengembalikan Collection, gunakan manual pagination
                    $filtered = $subordinates->filter(function ($item) use ($search) {
                        return !$search || 
                            str_contains(strtolower($item->name), strtolower($search)) ||
                            str_contains(strtolower($item->npk), strtolower($search)) ||
                            str_contains(strtolower($item->position), strtolower($search));
                    });

                    $page = LengthAwarePaginator::resolveCurrentPage();
                    $perPage = 10;
                    $currentItems = $filtered->slice(($page - 1) * $perPage, $perPage)->values();
                    $employees = new LengthAwarePaginator($currentItems, $filtered->count(), $perPage, $page, [
                        'path'  => $request->url(),
                        'query' => $request->query(),
                    ]);
                } else {
                    // Jika bukan Query Builder ataupun Collection, set ke empty paginator
                    $employees = new LengthAwarePaginator([], 0, 10);
                }
            }
        }

        if ($request->ajax()) {
            return view('website.master.employee.partials.table', compact('employees'))->render();
        }

        return view('website.master.employee.index', compact('employees', 'title'));
    }


    public function department()
    {
        $division = Division::all();
        $departments = Department::paginate(10);
        return view('website.master.department.index', compact('departments','division'));
    }

    public function departmentStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'division_id' => 'required|string|max:255',
        ]);

            try {
                Department::create([
                    'name' => $request->name,
                    'division_id' => $request->division_id
                ]);

            return redirect()->back()->with('success', 'Department berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan department: ' . $e->getMessage());
        }
    }

    public function departmentDestroy($id)
    {
        try {
            $department = Department::where('id', $id)->firstOrFail();

            $department->delete();

            return redirect()->back()->with('success', 'Department berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus department: ' . $e->getMessage());
        }
    }

    public function divisionStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name',
            'plant_id' => 'required|string|max:255',
        ]);

        try {
            Division::create([
                'name' => $request->name,
                'plant_id' => $request->plant_id
            ]);

            return redirect()->back()->with('success', 'Division berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Division: ' . $e->getMessage());
        }
    }

    public function divisionDestroy($id)
    {
        try {
            $department = Division::where('id', $id)->firstOrFail();

            $department->delete();
            return redirect()->back()->with('success', 'Division berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus Division: ' . $e->getMessage());
        }
    }

    public function sectionStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sections,name',
            'department_id' => 'required|string|max:255',
        ]);

        try {
            Section::create([
                'name' => $request->name,
                'department_id' => $request->department_id
            ]);

            return redirect()->back()->with('success', 'Section berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Section: ' . $e->getMessage());
        }
    }

    public function sectionDestroy($id)
    {
        try {
            $department = Section::where('id', $id)->firstOrFail();

            $department->delete();

            return redirect()->back()->with('success', 'Section berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus Section: ' . $e->getMessage());
        }
    }


    public function subSectionStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sub_sections,name',
            'section_id' => 'required|string|max:255',
        ]);

        try {
            SubSection::create([
                'name' => $request->name,
                'section_id' => $request->section_id
            ]);

            return redirect()->back()->with('success', 'Sub Section berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Sub Section: ' . $e->getMessage());
        }
    }

    public function subSectionDestroy($id)
    {
        try {
            $department = SubSection::where('id', $id)->firstOrFail();

            $department->delete();

            return redirect()->back()->with('success', 'Sub Section berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus Sub Section: ' . $e->getMessage());
        }
    }
    public function division()
    {
        $plants =  Plant::all();
        $divisions = Division::all();
        return view('website.master.division.index', compact('divisions','plants'));
    }
    public function section()
    {
        $department  = Department::all();
        $sections = Section::paginate(10);
        return view('website.master.section.index', compact('sections','department'));
    }

    public function subSection()
    {
        $sections = Section::all();
        $subSections = SubSection::paginate(10);
        return view('website.master.subSection.index', compact('subSections','sections'));
    }


    public function grade()
    {
        return view('website.master.grade.index');
    }

    public function filter(Request $request)
    {
        $filter = $request->filter;
        $division_id = $request->division_id;

        $user = auth()->user();
        $employee = $user->employee;
        
        if($user->role === 'HRD' || $employee->position == 'Director'){     
            switch ($filter) {
                case 'department':
                    $data = Department::where('division_id', $division_id)->get();
                    break;
    
                case 'section':
                    $data = Section::whereHas('department', function ($q) use ($division_id) {
                        $q->where('division_id', $division_id);
                    })->get();
                    break;
    
                case 'sub_section':
                    $data = SubSection::whereHas('section.department', function ($q) use ($division_id) {
                        $q->where('division_id', $division_id);
                    })->get();
                    break;
    
                default:
                    $data = collect(); // kosong
                    break;
            }
        }else{
            switch ($filter) {
                case 'section':
                    $data = Section::where('department_id', $division_id)->get();
                    break;
                case 'sub_section':
                    $data = SubSection::with('section')->whereHas('section', function ($q) use ($division_id){
                        $q->where('department_id', $division_id);
                    })->get();
                    break;
    
                default:
                    $data = collect(); // kosong
                    break;
            }
        }

        return view('layouts.partials.filter', compact('data'))->render();
    }
}
