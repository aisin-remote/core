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
use App\Models\MatrixCompetency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'VPD',
            'Direktur',
            'Act Direktur',
            'Act GM',
            'GM',
            'Act Manager',
            'Manager',
            'Coordinator',
            'Section Head',
            'Act Section Head',
            'Act Supervisor',
            'Supervisor',
            'Act Leader',
            'Leader',
            'Staff',
            'Act JP',
            'JP',
            'Operator'
        ];

        $rawPosition = $user->employee->position ?? 'Operator';

        $visiblePositions = [];

        if ($user->isHRDorDireksi()) {
            $visiblePositions = $allPositions;
        } else {

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
        }

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
            })
            ->get();

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
                $q->whereHas('section', function ($sub) use ($company) {
                    $sub->where('company', $company);
                });
            })
            ->get();

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
        $trace = (string) Str::uuid();
        $t0    = microtime(true);

        try {
            Log::info('[RTC][filter] start', [
                'trace'   => $trace,
                'params'  => [
                    'filter'       => $request->input('filter'),
                    'division_id'  => $request->input('division_id'),
                    'company'      => $request->input('company'),
                ],
                'url'     => $request->fullUrl(),
                'ip'      => $request->ip(),
            ]);

            // filter: company | direksi | division | department | section | sub_section
            $filter      = strtolower($request->input('filter', 'department'));
            // Catatan: filter=division → containerId = plant_id; lainnya (dept/section/sub) → division_id
            $containerId = (int) $request->input('division_id');
            $companyCode = strtoupper((string) $request->input('company', ''));

            $user     = auth()->user();
            $employee = $user->employee;

            $posRaw = $employee && method_exists($employee, 'getNormalizedPosition')
                ? (string) $employee->getNormalizedPosition()
                : (string) ($employee->position ?? '');
            $pos = strtolower(trim($posRaw));

            $isGM   = in_array($pos, ['gm', 'act gm'], true);
            $isDir  = (($user->role === 'User') && in_array($pos, ['direktur', 'director', 'act direktur'], true));
            $isHRD  = ($user->role === 'HRD');
            $isTop2 = in_array($pos, ['president', 'vpd', 'vice president director', 'wakil presdir'], true)
                || in_array(strtolower((string) ($employee->getNormalizedPosition() ?? '')), ['president', 'vpd'], true);
            $isMg   = in_array($pos, ['manager', 'coordinator'], true); // ✅ tambahkan flag Manager

            Log::debug('[RTC][filter] role flags', [
                'trace' => $trace,
                'user_id' => $user->id ?? null,
                'employee_id' => $employee->id ?? null,
                'pos_raw' => $employee->position ?? null,
                'pos_norm' => $posRaw,
                'isGM' => $isGM,
                'isDir' => $isDir,
                'isHRD' => $isHRD,
                'isTop2' => $isTop2,
                'isMg' => $isMg,
            ]);

            /* Guards (khusus GM untuk validasi division yang dia miliki) */
            if ($isGM && !in_array($filter, ['division', 'direksi', 'company'], true)) {
                if ($containerId > 0) {
                    $owns = Division::where('gm_id', $employee->id)
                        ->where('id', $containerId)->exists();
                    if (!$owns) {
                        Log::warning('[RTC][filter] GM unauthorized division', ['trace' => $trace, 'division_id' => $containerId]);
                        abort(403, 'Unauthorized division');
                    }
                }
            }

            if ($isDir && $filter === 'division') {
                if ($containerId === 0) $containerId = (int) optional($employee->plant)->id;
            }

            /* Data per filter */
            $data = collect();
            $areaKey = 'department';

            switch ($filter) {
                case 'company':
                    $data = collect([
                        (object)['id' => 1, 'name' => 'AII',  'code' => 'AII'],
                        (object)['id' => 2, 'name' => 'AIIA', 'code' => 'AIIA'],
                    ]);
                    $areaKey = 'company';
                    break;

                case 'direksi':
                    if ($isDir) {
                        $data = Plant::where('director_id', $employee->id)->orderBy('name')->get();
                    } elseif ($isHRD || $isTop2) {
                        $data = $companyCode
                            ? Plant::where('company', $companyCode)->orderBy('name')->get()
                            : collect();
                    } else {
                        $data = collect();
                    }
                    $areaKey = 'direksi';
                    break;

                case 'division':
                    if ($isGM) {
                        // GM: semua division yang dia pegang
                        $data = Division::where('gm_id', $employee->id)->orderBy('name')->get();
                    } else {
                        // Non-GM: by plant (untuk Direktur: plant yang dia pegang; untuk HRD/Top2: berdasarkan pilihan)
                        if ($containerId === 0) $containerId = (int) optional($employee->plant)->id;
                        $data = Division::where('plant_id', $containerId)->orderBy('name')->get();
                    }
                    $areaKey = 'division';
                    break;

                case 'department': {
                        $q = Department::query()
                            ->when($containerId > 0, fn($qq) => $qq->where('division_id', $containerId));

                        // ✅ Manager: hanya department yang dia pimpin
                        if ($isMg) {
                            $q->where('manager_id', $employee->id);
                        }
                        // Scope GM (opsional—agar GM hanya lihat division yang dia pegang)
                        if ($isGM) {
                            $q->whereHas('division', fn($dv) => $dv->where('gm_id', $employee->id));
                        }
                        // Scope Direktur: hanya division dalam plant yang dia pegang
                        if ($isDir && $employee->plant) {
                            $q->whereHas('division', fn($dv) => $dv->where('plant_id', $employee->plant->id));
                        }

                        $data = $q->orderBy('name')->get();
                        $areaKey = 'department';
                        break;
                    }

                case 'section': {
                        $q = Section::query()
                            // filter by selected division via parent department
                            ->when($containerId > 0, fn($qq) =>
                            $qq->whereHas('department', fn($d) => $d->where('division_id', $containerId)));

                        // ✅ Manager: hanya section di department yang dia pimpin
                        if ($isMg) {
                            $q->whereHas('department', fn($d) => $d->where('manager_id', $employee->id));
                        }
                        // Scope GM
                        if ($isGM) {
                            $q->whereHas('department.division', fn($dv) => $dv->where('gm_id', $employee->id));
                        }
                        // Scope Direktur
                        if ($isDir && $employee->plant) {
                            $q->whereHas('department.division', fn($dv) => $dv->where('plant_id', $employee->plant->id));
                        }

                        $data = $q->orderBy('name')->get();
                        $areaKey = 'section';
                        break;
                    }

                case 'sub_section': {
                        $q = SubSection::query()
                            ->when($containerId > 0, fn($qq) =>
                            $qq->whereHas('section.department', fn($d) => $d->where('division_id', $containerId)));

                        // ✅ Manager: hanya sub section di department yang dia pimpin
                        if ($isMg) {
                            $q->whereHas('section.department', fn($d) => $d->where('manager_id', $employee->id));
                        }
                        // Scope GM
                        if ($isGM) {
                            $q->whereHas('section.department.division', fn($dv) => $dv->where('gm_id', $employee->id));
                        }
                        // Scope Direktur
                        if ($isDir && $employee->plant) {
                            $q->whereHas('section.department.division', fn($dv) => $dv->where('plant_id', $employee->plant->id));
                        }

                        $data = $q->orderBy('name')->get();
                        $areaKey = 'sub_section';
                        break;
                    }
            }

            Log::debug('[RTC][filter] fetched', [
                'trace' => $trace,
                'filter' => $filter,
                'companyCode' => $companyCode,
                'containerId' => $containerId,
                'records' => $data instanceof \Illuminate\Support\Collection ? $data->count() : 0,
                'areaKey' => $areaKey,
            ]);

            /* Aliases & status */
            $termAliases = function (string $term): array {
                $t = strtolower(trim($term));
                return match ($t) {
                    'short' => ['short', 'short_term', 'st', 's/t'],
                    'mid'   => ['mid', 'mid_term', 'mt', 'm/t'],
                    'long'  => ['long', 'long_term', 'lt', 'l/t'],
                    default => [$t],
                };
            };
            $areaAliases = function (string $area): array {
                $a = strtolower(trim($area));
                $variants = [$a, ucfirst($a)];
                if ($a === 'division')    $variants[] = 'Division';
                if ($a === 'sub_section') $variants[] = 'Sub_section';
                if ($a === 'direksi') {
                    $variants[] = 'Direksi';
                    $variants[] = 'plant';
                    $variants[] = 'Plant';
                } // tetap match data lama
                if ($a === 'company')     $variants[] = 'Company';
                return array_values(array_unique($variants));
            };
            $areas = $areaAliases($areaKey);

            $items = $data->map(function ($item) use ($areas, $termAliases, $areaKey, $isGM, $isDir) {  // ✅ tambahkan $isGM
                $rtcShort = Rtc::whereIn('area', $areas)->where('area_id', $item->id)
                    ->whereIn('term', $termAliases('short'))->orderByDesc('id')
                    ->with(['employee:id,name,grade,birthday_date'])->first();
                $rtcMid   = Rtc::whereIn('area', $areas)->where('area_id', $item->id)
                    ->whereIn('term', $termAliases('mid'))->orderByDesc('id')
                    ->with(['employee:id,name,grade,birthday_date'])->first();
                $rtcLong  = Rtc::whereIn('area', $areas)->where('area_id', $item->id)
                    ->whereIn('term', $termAliases('long'))->orderByDesc('id')
                    ->with(['employee:id,name,grade,birthday_date'])->first();

                $shortEmp = optional($rtcShort)->employee;
                $midEmp   = optional($rtcMid)->employee;
                $longEmp  = optional($rtcLong)->employee;

                $hasShort  = !is_null($shortEmp);
                $hasMid    = !is_null($midEmp);
                $hasLong   = !is_null($longEmp);
                $complete3 = $hasShort && $hasMid && $hasLong;

                $s = optional($rtcShort)->status;
                $m = optional($rtcMid)->status;
                $l = optional($rtcLong)->status;

                $label = 'Not Set';
                $class = 'badge badge-danger';
                $code = 'not_set';
                if ($complete3) {
                    $vals = collect([$s, $m, $l])->filter(fn($v) => in_array($v, [0, 1, 2, -1], true));
                    if ($vals->isEmpty()) {
                        $label = 'Complete';
                        $class = 'badge badge-secondary';
                        $code = 'complete_no_submit';
                    } else {
                        $allApproved  = $vals->every(fn($v) => $v === 2);
                        $allChecked   = $vals->every(fn($v) => $v === 1);
                        $allSubmitted = $vals->every(fn($v) => $v === 0);
                        $allRevised   = $vals->every(fn($v) => $v === -1);
                        if ($allApproved) {
                            $label = 'Approved';
                            $class = 'badge badge-success';
                            $code = 'approved';
                        } elseif ($allChecked) {
                            $label = 'Checked';
                            $class = 'badge badge-info';
                            $code = 'checked';
                        } elseif ($allSubmitted) {
                            $label = 'Submitted';
                            $class = 'badge badge-warning';
                            $code = 'submitted';
                        } elseif ($allRevised) {
                            $label = 'Revised';
                            $class = 'badge badge-danger';
                            $code = 'revised';
                        } else {
                            $label = 'Not Set';
                            $class = 'badge badge-danger';
                            $code = 'not_set';
                        }
                    }
                }

                $picEmp = $this->currentPicFor($areaKey, $item);

                $canAddByRole =
                    !($isGM && in_array($areaKey, ['department', 'section', 'sub_section'], true))
                    && !($isDir && in_array($areaKey, ['division', 'department', 'section', 'sub_section'], true));

                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                    'pic'  => $picEmp ? ['id' => $picEmp->id, 'name' => $picEmp->name, 'position' => $picEmp->position] : null,
                    'short' => ['name' => $shortEmp?->name, 'status' => $s],
                    'mid'   => ['name' => $midEmp?->name,  'status' => $m],
                    'long'  => ['name' => $longEmp?->name, 'status' => $l],
                    'overall' => ['label' => $label, 'class' => $class, 'code' => $code],
                    'can_add' => $canAddByRole && !$complete3, // 🔒
                ];
            });


            $resp = response()->json(['items' => $items->values()]);

            Log::info('[RTC][filter] end', [
                'trace' => $trace,
                'filter' => $filter,
                'items'  => $items->count(),
                'duration_ms' => round((microtime(true) - $t0) * 1000, 2),
            ]);

            return $resp;
        } catch (\Throwable $e) {
            Log::error('[RTC][filter] ERROR', [
                'trace' => $trace,
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'stack' => substr($e->getTraceAsString(), 0, 4000),
            ]);
            return response()->json(['message' => 'Internal error', 'trace' => $trace], 500);
        }
    }

    private function currentPicFor(string $area, $model)
    {
        $empId = match ($area) {
            'plant'       => $model->director_id ?? null,
            'division'    => $model->gm_id ?? null,
            'department'  => $model->manager_id ?? null,
            'section'     => $model->supervisor_id ?? null,
            'sub_section' => $model->leader_id ?? null,
            default       => null,
        };

        return $empId ? Employee::select('id', 'name', 'position')->find($empId) : null;
    }

    public function matrixCompetencies(Request $request)
    {
        // validasi basic + mutual exclusion
        $request->validate([
            'dept_id' => 'nullable|exists:departments,id',
            'divs_id' => 'nullable|exists:divisions,id',
        ], [], [
            'dept_id' => 'department',
            'divs_id' => 'division',
        ]);

        if ($request->filled('dept_id') && $request->filled('divs_id')) {
            return back()->with('error', 'Pilih salah satu: Department atau Division saja.');
        }

        $query = MatrixCompetency::with(['department', 'division'])->latest('id');

        $query->when($request->filled('dept_id'), function ($q) use ($request) {
            $q->whereNotNull('dept_id')->where('dept_id', $request->dept_id);
        });

        $query->when($request->filled('divs_id'), function ($q) use ($request) {
            $q->whereNotNull('divs_id')->where('divs_id', $request->divs_id);
        });

        $datas = $query->get();

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $divisions   = Division::orderBy('name')->get(['id', 'name']);
        $title = 'Matrix Competencies';

        // kirim juga nilai filter aktif (untuk preselect di Blade)
        $activeDept = $request->dept_id;
        $activeDivs = $request->divs_id;

        return view('website.master.matrix-competencies.index', compact(
            'datas',
            'departments',
            'divisions',
            'title',
            'activeDept',
            'activeDivs'
        ));
    }

    public function matrixCompetenciesStore(Request $request)
    {
        $validated = $request->validate([
            'competency' => ['required', 'string', 'max:255'],
            // Minimal salah satu, dan saling melarang
            'dept_id'    => ['nullable', 'integer', 'exists:departments,id', 'required_without:divs_id', 'prohibits:divs_id'],
            'divs_id'    => ['nullable', 'integer', 'exists:divisions,id', 'required_without:dept_id', 'prohibits:dept_id'],
        ], [
            'dept_id.required_without' => 'Pilih Department atau Division.',
            'divs_id.required_without' => 'Pilih Department atau Division.',
        ]);

        DB::beginTransaction();
        try {
            MatrixCompetency::create([
                'competency' => $validated['competency'],
                'dept_id'    => $validated['dept_id'] ?? null,
                'divs_id'    => $validated['divs_id'] ?? null,
            ]);
            DB::commit();
            return back()->with('success', 'Data berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function matrixCompetenciesUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'competency' => ['required', 'string', 'max:255'],
            'dept_id'    => ['nullable', 'integer', 'exists:departments,id', 'required_without:divs_id', 'prohibits:divs_id'],
            'divs_id'    => ['nullable', 'integer', 'exists:divisions,id', 'required_without:dept_id', 'prohibits:dept_id'],
        ], [
            'dept_id.required_without' => 'Pilih Department atau Division.',
            'divs_id.required_without' => 'Pilih Department atau Division.',
        ]);

        DB::beginTransaction();
        try {
            $row = MatrixCompetency::findOrFail($id);
            $row->update([
                'competency' => $validated['competency'],
                'dept_id'    => $validated['dept_id'] ?? null,
                'divs_id'    => $validated['divs_id'] ?? null,
            ]);
            DB::commit();
            return back()->with('success', 'Data berhasil diupdate.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function matrixCompetenciesDestroy($id)
    {
        try {
            MatrixCompetency::findOrFail($id)->delete();
            return back()->with('success', 'Data berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
