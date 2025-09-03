<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Rtc;
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
            'Act GM',
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
        // division | department | section | sub_section
        $filter = strtolower($request->input('filter', 'department'));
        // Catatan: untuk filter=division, param ini berisi plant_id (bukan division_id)
        $containerId = (int) $request->input('division_id');

        $user     = auth()->user();
        $employee = $user->employee;

        // Normalisasi posisi
        $pos   = strtolower(trim($employee->position ?? ''));
        $isGM  = in_array($pos, ['gm', 'act gm'], true);
        $isDir = ($pos === 'direktur');

        // ===== Guard Akses =====
        // GM: hanya boleh lihat resource yang berada di divisi yang dia pegang.
        // Ini HANYA relevan untuk filter yang berbasis division_id (dept/section/sub_section),
        // bukan untuk filter=division (karena itu berbasis plant_id).
        if ($isGM && $filter !== 'division') {
            $owns = \App\Models\Division::where('gm_id', $employee->id)
                ->where('id', $containerId)
                ->exists();
            if (!$owns) abort(403, 'Unauthorized division');
        }

        // Direktur: saat filter=division (menarik daftar division per PLANT),
        // pastikan plant tersebut memang plant yang dia pegang.
        if ($isDir && $filter === 'division') {
            $ownsPlant = \App\Models\Plant::where('id', $containerId)
                ->where('director_id', $employee->id)
                ->exists();
            if (!$ownsPlant) abort(403, 'Unauthorized plant');
        }

        // ===== Ambil list item per filter =====
        switch ($filter) {
            case 'division':
                // containerId di sini = plant_id
                $data    = \App\Models\Division::where('plant_id', $containerId)->orderBy('name')->get();
                $areaKey = 'division';
                break;

            case 'department':
                // containerId di sini = division_id
                $data    = \App\Models\Department::where('division_id', $containerId)->orderBy('name')->get();
                $areaKey = 'department';
                break;

            case 'section':
                $data = \App\Models\Section::whereHas('department', function ($q) use ($containerId) {
                    $q->where('division_id', $containerId);
                })->orderBy('name')->get();
                $areaKey = 'section';
                break;

            case 'sub_section':
                $data = \App\Models\SubSection::whereHas('section.department', function ($q) use ($containerId) {
                    $q->where('division_id', $containerId);
                })->orderBy('name')->get();
                $areaKey = 'sub_section';
                break;

            default:
                $data    = collect();
                $areaKey = 'department';
                break;
        }

        // ===== Aliases agar data lama kebaca (term & area case-insensitive) =====
        $termAliases = function (string $term): array {
            $t = strtolower(trim($term));
            return match ($t) {
                'short' => ['short', 'short_term', 'st', 's/t'],
                'mid'   => ['mid',   'mid_term',   'mt', 'm/t'],
                'long'  => ['long',  'long_term',  'lt', 'l/t'],
                default => [$t],
            };
        };
        $areaAliases = function (string $area): array {
            $a = strtolower(trim($area));
            $variants = [$a, ucfirst($a)];
            if ($a === 'division')    $variants[] = 'Division';
            if ($a === 'sub_section') $variants[] = 'Sub_section';
            return array_values(array_unique($variants));
        };
        $areas = $areaAliases($areaKey);

        // ===== Bentuk payload JSON untuk tabel =====
        $items = $data->map(function ($item) use ($areas, $termAliases) {
            $rtcShort = \App\Models\Rtc::whereIn('area', $areas)
                ->where('area_id', $item->id)
                ->whereIn('term', $termAliases('short'))
                ->orderByDesc('id')
                ->with(['employee:id,name,grade,birthday_date'])
                ->first();

            $rtcMid = \App\Models\Rtc::whereIn('area', $areas)
                ->where('area_id', $item->id)
                ->whereIn('term', $termAliases('mid'))
                ->orderByDesc('id')
                ->with(['employee:id,name,grade,birthday_date'])
                ->first();

            $rtcLong = \App\Models\Rtc::whereIn('area', $areas)
                ->where('area_id', $item->id)
                ->whereIn('term', $termAliases('long'))
                ->orderByDesc('id')
                ->with(['employee:id,name,grade,birthday_date'])
                ->first();

            $shortEmp = optional($rtcShort)->employee;
            $midEmp   = optional($rtcMid)->employee;
            $longEmp  = optional($rtcLong)->employee;

            // status keseluruhan
            $hasShort  = !is_null($shortEmp);
            $hasMid    = !is_null($midEmp);
            $hasLong   = !is_null($longEmp);
            $complete3 = $hasShort && $hasMid && $hasLong;

            // status per-term: 0=submitted, 1=checked, 3=approved, null=belum submit
            $s = optional($rtcShort)->status;
            $m = optional($rtcMid)->status;
            $l = optional($rtcLong)->status;

            $label = 'Not Set';
            $class = 'badge badge-danger';
            $code  = 'not_set';

            if ($complete3) {
                // Ambil hanya nilai valid (0,1,2)
                $vals = collect([$s, $m, $l])->filter(fn($v) => in_array($v, [0, 1, 2], true));

                if ($vals->isEmpty()) {
                    // Lengkap kandidat, belum submit semua
                    $label = 'Complete';
                    $class = 'badge badge-secondary';
                    $code  = 'complete_no_submit';
                } else {
                    $allApproved  = $vals->every(fn($v) => $v === 2);
                    $allChecked   = $vals->every(fn($v) => $v === 1);
                    $allSubmitted = $vals->every(fn($v) => $v === 0);

                    if ($allApproved) {
                        $label = 'Approved';
                        $class = 'badge badge-success';
                        $code  = 'approved';
                    } elseif ($allChecked) {
                        $label = 'Checked';
                        $class = 'badge badge-info';
                        $code  = 'checked';
                    } elseif ($allSubmitted) {
                        $label = 'Submitted';
                        $class = 'badge badge-warning';
                        $code  = 'submitted';
                    } else {
                        // kombinasi campur 0/1/3 → partial
                        $label = 'Partial';
                        $class = 'badge badge-primary';
                        $code  = 'partial';
                    }
                }
            }

            return [
                'id'   => $item->id,
                'name' => $item->name,

                'short' => [
                    'name'   => $shortEmp?->name,
                    'status' => $s, // 0/1/3/null
                ],
                'mid' => [
                    'name'   => $midEmp?->name,
                    'status' => $m,
                ],
                'long' => [
                    'name'   => $longEmp?->name,
                    'status' => $l,
                ],

                'overall' => [
                    'label' => $label,
                    'class' => $class,
                    'code'  => $code, // approved|checked|submitted|partial|complete_no_submit|not_set
                ],
                // tombol Add disembunyikan bila 3 term sudah lengkap
                'can_add' => !$complete3,
            ];
        });

        return response()->json(['items' => $items->values()]);
    }
}
