<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Department;
use App\Models\SubSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $filter = $request->input('filter', 'all');
        $search = $request->input('search');

        $employee = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
            ->when($company, fn($query) => $query->where('company_name', $company)) // Filter berdasarkan perusahaan
            ->where(function ($query) {
                $query->where('user_id', '!=', auth()->id())
                    ->orWhereNull('user_id');
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('npk', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($filter && $filter !== 'all', function ($query) use ($filter) {
                $query->where(function ($q) use ($filter) {
                    $q->where('position', $filter)
                        ->orWhere('position', 'like', "Act %{$filter}");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        $allPositions = [
            'President',
            'Direktur',
            'GM',
            'Manager',
            'Coordinator',
            'Section Head',
            'Supervisor',
            'Leader',
            'JP',
            'Operator',
        ];

        $rawPosition = $user->employee->position ?? 'Operator';
        $currentPosition = Str::contains($rawPosition, 'Act ')
            ? trim(str_replace('Act', '', $rawPosition))
            : $rawPosition;

        // Cari index posisi saat ini
        $positionIndex = array_search($currentPosition, $allPositions);

        // Fallback jika tidak ditemukan
        if ($positionIndex === false) {
            $positionIndex = array_search('Operator', $allPositions);
        }

        // Ambil posisi di bawahnya (tanpa posisi user)
        $visiblePositions = $positionIndex !== false
            ? array_slice($allPositions, $positionIndex)
            : [];


        return view('website.master.employee.index', compact('employee', 'title', 'filter', 'company', 'search','visiblePositions'));
    }




    public function departmentStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'company' => 'required',
            'division_id' => 'required|string|max:255',
            'manager_id' => 'required',
        ]);

        try {
            Department::create([
                'name' => $request->name,
                'division_id' => $request->division_id,
                'company' => $request->company,
                'manager_id' => $request->manager_id
            ]);

            return redirect()->back()->with('success', 'Department berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan department: ' . $e->getMessage());
        }
    }
    // public function getManagers($company)
    // {
    //     $managers = Employee::where('position', 'Manager')
    //         ->where('company_name', $company)
    //         ->get(['id', 'name']);

    //     return response()->json($managers);
    // }
    public function departmentUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'company' => 'required',
            'division_id' => 'required|string|max:255',
            'manager_id' => 'required',
        ]);

        try {
            $department = Department::findOrFail($id);
            $department->update([
                'name' => $request->name,
                'division_id' => $request->division_id,
                'company' => $request->company,
                'manager_id' => $request->manager_id
            ]);

            return redirect()->back()->with('success', 'Department berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate department: ' . $e->getMessage());
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
            'name' => 'required|string',
            'plant_id' => 'required|string|max:255',
            'gm_id' => 'required',
        ]);

        try {
            Division::create([
                'name' => $request->name,
                'plant_id' => $request->plant_id,
                'gm_id' => $request->gm_id
            ]);

            return redirect()->back()->with('success', 'Division berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Division: ' . $e->getMessage());
        }
    }
    public function updateDivision(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'plant_id' => 'required|string|max:255',
            'gm_id' => 'required',
        ]);

        try {
            $plant = Division::findOrFail($id);
            $plant->update([
                'name' => $request->name,
                'plant_id' => $request->plant_id,
                'gm_id' => $request->gm_id
            ]);

            return redirect()->back()->with('success', 'Plant berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui Plant: ' . $e->getMessage());
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
            'name' => 'required|string',
            'department_id' => 'required|string|max:255',
            'company' => 'required|string',
            'supervisor_id' => 'required|string',
        ]);

        try {
            Section::create([
                'name' => $request->name,
                'department_id' => $request->department_id,
                'company' => $request->company,
                'supervisor_id' => $request->supervisor_id
            ]);

            return redirect()->back()->with('success', 'Section berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Section: ' . $e->getMessage());
        }
    }
    public function sectionUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'department_id' => 'required|string|max:255',
            'company' => 'required|string',
            'supervisor_id' => 'required|string',
        ]);

        try {
            $section = Section::findOrFail($id);

            $section->update([
                'name' => $request->name,
                'department_id' => $request->department_id,
                'company' => $request->company,
                'supervisor_id' => $request->supervisor_id
            ]);

            return redirect()->back()->with('success', 'Section berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui Section: ' . $e->getMessage());
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
            'name' => 'required|string',
            'section_id' => 'required|string|max:255',
            'leader_id' => 'required|string|max:255',
        ]);

        try {
            SubSection::create([
                'name' => $request->name,
                'section_id' => $request->section_id,
                'leader_id' => $request->leader_id
            ]);

            return redirect()->back()->with('success', 'Sub Section berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Sub Section: ' . $e->getMessage());
        }
    }

    public function subSectionUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'section_id' => 'required',
            'leader_id' => 'string',
        ]);

        try {
            $subSection = SubSection::findOrFail($id);

            $subSection->update([
                'name' => $request->name,
                'section_id' => $request->section_id,
                'leader_id' => $request->leader_id
            ]);

            return redirect()->back()->with('success', 'Sub Section berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui Sub Section: ' . $e->getMessage());
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
    public function department()
    {
        $divisions = Division::all();

        // Ambil departments dengan relasi division dan manager, paging 10 per halaman
        $departments = Department::with(['division', 'manager'])->paginate(10);
        $managers = Employee::all();



        return view('website.master.department.index', compact('departments', 'divisions', 'managers'));
    }
    public function division()
    {
        $gms = Employee::whereIn('position', ['GM', 'Act GM', 'Manager'])->get();
        $plants = Plant::all();
        $divisions = Division::with(['plant', 'gm'])->get(); // <-- ini penting
        return view('website.master.division.index', compact('divisions', 'plants', 'gms'));
    }
    public function section()
    {
        $supervisors = Employee::whereIn('position', ['Section Head', 'Act Section Head', 'Supervisor', 'Act Supervisor', 'Act Manager', 'Manager'])->get();
        $departments  = Department::all();
        $sections = Section::with(['department', 'supervisor'])->paginate(10);
        return view('website.master.section.index', compact('sections', 'departments', 'supervisors'));
    }

    public function subSection()
    {
        $leaders = Employee::whereIn('position', ['Leader', 'Act Leader'])->get();
        $sections = Section::all();
        $subSections = SubSection::with('leader', 'section')->paginate(10);
        return view('website.master.subSection.index', compact('subSections', 'leaders', 'sections'));
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

        if ($user->role === 'HRD' || $employee->position == 'Direktur') {
            switch ($filter) {
                case 'department':
                    $data = Department::with('short', 'mid', 'long')->where('division_id', $division_id)->get();
                    break;

                case 'section':
                    $data = Section::with('short', 'mid', 'long')->whereHas('department', function ($q) use ($division_id) {
                        $q->where('division_id', $division_id);
                    })->get();
                    break;

                case 'sub_section':
                    $data = SubSection::with('short', 'mid', 'long')->whereHas('section.department', function ($q) use ($division_id) {
                        $q->where('division_id', $division_id);
                    })->get();
                    break;

                default:
                    $data = collect(); // kosong
                    break;
            }
        } else {
            switch ($filter) {
                case 'section':
                    $data = Section::with('short', 'mid', 'long')->where('department_id', $division_id)->get();
                    break;
                case 'sub_section':
                    $data = SubSection::with('section', 'short', 'mid', 'long')->whereHas('section', function ($q) use ($division_id) {
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
