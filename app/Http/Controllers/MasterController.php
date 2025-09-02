<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Department;
use App\Models\SubSection;
use App\Models\User;
use Illuminate\Support\Str;
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
            ->get();

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


        return view('website.master.employee.index', compact('employee', 'title', 'filter', 'company', 'search', 'visiblePositions'));
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
        // $request->validate([
        //     'name' => 'required|string',
        //     'section_id' => 'required|string|max:255',
        //     'leader_id' => 'required|string|max:255',
        // ]);

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
    public function department($company = null)
    {
        $divisions = Division::when($company, function ($q) use ($company) {
            $q->whereHas('gm', function ($sub) use ($company) {
                $sub->where('company_name', $company); // via GM relasi
            });
        })->get();

        $departments = Department::with(['division', 'manager'])
            ->when($company, function ($q) use ($company) {
                $q->whereHas('manager', function ($sub) use ($company) {
                    $sub->where('company_name', $company);
                });
            })->get();

        $managers = Employee::when($company, function ($q) use ($company) {
            $q->where('company_name', $company);
        })->get();

        return view('website.master.department.index', compact('departments', 'divisions', 'managers', 'company'));
    }


    public function division($company = null)
    {
        $gms = Employee::whereIn('position', ['GM', 'Act GM', 'Manager'])
            ->when($company, fn($q) => $q->where('company_name', $company))
            ->get();

        $plants = Plant::when($company, function ($q) use ($company) {
            $q->whereHas('director', function ($sub) use ($company) {
                $sub->where('company_name', $company);
            });
        })->get();

        $divisions = Division::with(['plant', 'gm'])
            ->when($company, function ($q) use ($company) {
                $q->whereHas('gm', function ($sub) use ($company) {
                    $sub->where('company_name', $company);
                });
            })->get();

        return view('website.master.division.index', compact('divisions', 'plants', 'gms', 'company'));
    }


    public function section($company = null)
    {
        $supervisors = Employee::whereIn('position', [
            'Section Head',
            'Act Section Head',
            'Supervisor',
            'Act Supervisor',
            'Act Manager',
            'Manager'
        ])
            ->when($company, fn($q) => $q->where('company_name', $company))
            ->get();

        $departments = Department::when($company, function ($q) use ($company) {
            $q->whereHas('manager', function ($sub) use ($company) {
                $sub->where('company_name', $company);
            });
        })->get();

        $sections = Section::with(['department', 'supervisor'])
            ->when($company, function ($q) use ($company) {
                $q->whereHas('supervisor', function ($sub) use ($company) {
                    $sub->where('company_name', $company);
                });
            })->get();

        return view('website.master.section.index', compact('sections', 'departments', 'supervisors', 'company'));
    }


    public function subSection($company = null)
    {
        $leaders = Employee::whereIn('position', ['Leader', 'Act Leader'])
            ->when($company, fn($q) => $q->where('company_name', $company))
            ->get();

        $sections = Section::when($company, function ($q) use ($company) {
            $q->whereHas('supervisor', function ($sub) use ($company) {
                $sub->where('company_name', $company);
            });
        })->get();

        $subSections = SubSection::with(['leader', 'section'])
            ->when($company, function ($q) use ($company) {
                $q->whereHas('leader', function ($sub) use ($company) {
                    $sub->where('company_name', $company);
                });
            })->paginate(10);

        return view('website.master.subSection.index', compact('subSections', 'leaders', 'sections', 'company'));
    }

    public function users(Request $request, $company = null)
    {
        $search = $request->input('search');

        $users = User::with('employee')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($q) use ($search) {
                        $q->where('npk', 'like', "%{$search}%");
                    });
            })
            ->when($company, function ($query) use ($company) {
                $query->whereHas('employee', function ($q) use ($company) {
                    $q->where('company_name', $company);
                });
            })
            ->orderBy('name')
            ->get();

        return view('website.master.users.index', compact('users', 'company'));
    }

    public function grade()
    {
        return view('website.master.grade.index');
    }

    public function filter(Request $request)
    {
        $filter      = $request->filter;
        $division_id = (int) $request->division_id;

        $user     = auth()->user();
        $employee = $user->employee;

        $mapLatest = function ($items, string $area = '') {
            return $items->map(function ($item) {
                $item->loadMissing([
                    'rtcShortLatest.employee:id,name,grade,birthday_date',
                    'rtcMidLatest.employee:id,name,grade,birthday_date',
                    'rtcLongLatest.employee:id,name,grade,birthday_date',
                ]);
                $item->setRelation('short', optional($item->rtcShortLatest)->employee);
                $item->setRelation('mid',   optional($item->rtcMidLatest)->employee);
                $item->setRelation('long',  optional($item->rtcLongLatest)->employee);
                return $item;
            });
        };

        $isTopRole = $user->role === 'HRD' || ($employee && $employee->position === 'Direktur');
        $isGM      = $employee && $employee->position === 'GM';

        // (opsional) batasi GM hanya ke divisi yang dipimpinnya
        if ($isGM) {
            $allowed = \App\Models\Division::where('gm_id', $employee->id)->pluck('id');
            if (!$allowed->contains($division_id)) {
                abort(403); // atau return view kosong
            }
        }

        switch ($filter) {
            case 'department':
                // Semua role (HRD/Direktur/GM) ambil department berdasarkan division_id
                $data = \App\Models\Department::where('division_id', $division_id)
                    ->orderBy('name')->get();
                $data = $mapLatest($data, 'department');
                break;

            case 'section':
                // Semua role: section by division via relasi department
                $data = \App\Models\Section::whereHas('department', function ($q) use ($division_id) {
                    $q->where('division_id', $division_id);
                })->orderBy('name')->get();
                $data = $mapLatest($data, 'section');
                break;

            case 'sub_section':
                // Semua role: sub section by division via relasi section->department
                $data = \App\Models\SubSection::whereHas('section.department', function ($q) use ($division_id) {
                    $q->where('division_id', $division_id);
                })->orderBy('name')->get();
                $data = $mapLatest($data, 'sub_section');
                break;

            default:
                $data = collect();
        }

        // kirim juga $filter kalau partial rows butuh beda kolom
        return view('layouts.partials.filter', compact('data', 'filter'))->render();
    }
}
