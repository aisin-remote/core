<?php

namespace App\Http\Controllers;

use App\Models\{
    Employee,
    Hav,
    Icp,
    Idp,
    Rtc,
    Division,
    Department,
    Section,
    SubSection
};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function index()
    {
        return view('website.dashboard.index');
    }

    public function summary(Request $request)
    {
        $company = $request->query('company');

        $idp = $this->idpPerEmployeeBuckets($company);
        $hav = $this->modulePerEmployeeBuckets((new Hav)->getTable(), 'status', $company, [
            'approved' => [3],
            'revised'  => [-1],
            'progress' => [1, 2],
        ]);
        $icp = $this->modulePerEmployeeBuckets((new Icp)->getTable(), 'status', $company, [
            'approved' => [3],
            'revised'  => [-1],
            'progress' => [1, 2],
        ]);

        // RTC digabung per struktur (Division/Department/Section/SubSection)
        $rtc = $this->moduleRtcBucketsByStructure($company);

        // ALL tetap per-employee
        $all = $this->allPerEmployeeBuckets($company);

        return response()->json(compact('idp', 'hav', 'icp', 'rtc', 'all'));
    }

    /* ============================== ALL PER-EMPLOYEE ============================== */

    private function allPerEmployeeBuckets(?string $company): array
    {
        $scopeIds = Employee::forCompany($company)->pluck('id');

        $approvedIds = Employee::forCompany($company)
            ->where(function ($q) {
                // IDP
                $q->whereExists(function ($qq) {
                    $qq->selectRaw(1)->from('idp')
                        ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
                        ->join('employees as e2', 'assessments.employee_id', '=', 'e2.id')
                        ->whereColumn('assessments.employee_id', 'employees.id')
                        ->where(function ($w) {
                            $w->whereRaw("LOWER(e2.position) LIKE '%manager%' AND idp.status IN (4)")
                                ->orWhereRaw("LOWER(e2.position) NOT LIKE '%manager%' AND idp.status IN (3,4)");
                        });
                })
                    // HAV/ICP
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->whereIn('havs.status', [3]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->whereIn('icp.status', [3]))
                    // RTC (fallback jika ada row per-employee)
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->whereIn('rtc.status', [2]));
            })->pluck('id')->unique();

        $revisedIds = Employee::forCompany($company)
            ->whereNotIn('id', $approvedIds)
            ->where(function ($q) {
                $q->whereExists(function ($qq) {
                    $qq->selectRaw(1)->from('idp')
                        ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
                        ->whereColumn('assessments.employee_id', 'employees.id')
                        ->where('idp.status', -1);
                })
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->where('havs.status', -1))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->where('icp.status', -1))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->where('rtc.status', -1));
            })->pluck('id')->unique();

        $progressIds = Employee::forCompany($company)
            ->whereNotIn('id', $approvedIds)
            ->whereNotIn('id', $revisedIds)
            ->where(function ($q) {
                $q->whereExists(function ($qq) {
                    $qq->selectRaw(1)->from('idp')
                        ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
                        ->join('employees as e2', 'assessments.employee_id', '=', 'e2.id')
                        ->whereColumn('assessments.employee_id', 'employees.id')
                        ->where(function ($w) {
                            $w->whereRaw("LOWER(e2.position) LIKE '%manager%' AND idp.status IN (1,2,3)")
                                ->orWhereRaw("LOWER(e2.position) NOT LIKE '%manager%' AND idp.status IN (1,2)");
                        });
                })
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->whereIn('havs.status', [1, 2]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->whereIn('icp.status', [1, 2]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->whereIn('rtc.status', [0, 1]));
            })->pluck('id')->unique();

        $covered = $approvedIds->merge($revisedIds)->merge($progressIds)->unique();
        $not = $scopeIds->diff($covered)->count();

        return [
            'scope' => $scopeIds->count(),
            'approved' => $approvedIds->count(),
            'revised' => $revisedIds->count(),
            'progress' => $progressIds->count(),
            'not' => $not,
        ];
    }

    /* ============================== IDP/HAV/ICP AGG ============================== */

    private function idpPerEmployeeBuckets(?string $company): array
    {
        $scope = Employee::forCompany($company)->count();

        $base = DB::table('idp')
            ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
            ->join('employees', 'assessments.employee_id', '=', 'employees.id')
            ->when($company, fn($q) => $q->where('employees.company_name', $company));

        $distinctEmp = (clone $base)->distinct()->count('assessments.employee_id');

        $perEmp = (clone $base)
            ->select([
                'assessments.employee_id',
                DB::raw("
                CASE
                  WHEN
                    SUM(CASE WHEN LOWER(employees.position) LIKE '%manager%' AND idp.status IN (4) THEN 1 ELSE 0 END) > 0 OR
                    SUM(CASE WHEN LOWER(employees.position) NOT LIKE '%manager%' AND idp.status IN (3,4) THEN 1 ELSE 0 END) > 0
                  THEN 'approved'
                  WHEN SUM(CASE WHEN idp.status = -1 THEN 1 ELSE 0 END) > 0
                  THEN 'revised'
                  WHEN
                    SUM(CASE WHEN LOWER(employees.position) LIKE '%manager%' AND idp.status IN (1,2,3) THEN 1 ELSE 0 END) > 0 OR
                    SUM(CASE WHEN LOWER(employees.position) NOT LIKE '%manager%' AND idp.status IN (1,2) THEN 1 ELSE 0 END) > 0
                  THEN 'progress'
                  ELSE 'progress'
                END AS bucket")
            ])
            ->groupBy('assessments.employee_id');

        $counts = DB::query()->fromSub($perEmp, 't')
            ->select('bucket', DB::raw('COUNT(*) c'))->groupBy('bucket')->pluck('c', 'bucket');

        $approved = (int)($counts['approved'] ?? 0);
        $revised  = (int)($counts['revised']  ?? 0);
        $progress = (int)($counts['progress'] ?? 0);
        $not      = max($scope - $distinctEmp, 0);

        return compact('scope', 'approved', 'progress', 'revised', 'not');
    }

    private function modulePerEmployeeBuckets(string $table, string $statusCol, ?string $company, array $map, bool $joinViaAssessment = false): array
    {
        $scope = Employee::forCompany($company)->count();
        $base = DB::table($table);

        if ($joinViaAssessment) {
            $base->join('assessments', "$table.assessment_id", '=', 'assessments.id')
                ->join('employees', 'assessments.employee_id', '=', 'employees.id');
        } else {
            $base->join('employees', "$table.employee_id", '=', 'employees.id');
        }
        $base->when($company, fn($q) => $q->where('employees.company_name', $company));

        $distinctEmp = (clone $base)->distinct()->count($joinViaAssessment ? 'assessments.employee_id' : "$table.employee_id");

        $in = fn(array $a) => implode(',', array_map('intval', $a ?: [-99999]));

        $perEmp = (clone $base)->select([
            $joinViaAssessment ? 'assessments.employee_id' : "$table.employee_id AS employee_id",
            DB::raw("
              CASE
                WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['approved'] ?? []) . ") THEN 1 ELSE 0 END) > 0 THEN 'approved'
                WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['revised'] ?? []) . ") THEN 1 ELSE 0 END) > 0 THEN 'revised'
                WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['progress'] ?? []) . ") THEN 1 ELSE 0 END) > 0 THEN 'progress'
                ELSE 'progress'
              END AS bucket")
        ])->groupBy($joinViaAssessment ? 'assessments.employee_id' : "$table.employee_id");

        $counts = DB::query()->fromSub($perEmp, 't')
            ->select('bucket', DB::raw('COUNT(*) c'))->groupBy('bucket')->pluck('c', 'bucket');

        $approved = (int)($counts['approved'] ?? 0);
        $revised  = (int)($counts['revised']  ?? 0);
        $progress = (int)($counts['progress'] ?? 0);
        $not      = max($scope - $distinctEmp, 0);

        return compact('scope', 'approved', 'progress', 'revised', 'not');
    }

    /* ============================== RTC AGG & LIST ============================== */

    private function moduleRtcBucketsByStructure(?string $company): array
    {
        [$divs, $depts, $secs, $subs] = $this->allStructuresByCompany($company);
        $total = $divs->count() + $depts->count() + $secs->count() + $subs->count();

        $approved = 0;
        $revised = 0;
        $progress = 0;
        $hasAny = 0;

        $bucketOf = function (string $area, int $id) {
            $arr = Rtc::where('area', $area)->where('area_id', $id)->pluck('status')->all();
            if (empty($arr)) return null;
            $uniq = collect($arr)->unique()->values();
            if ($uniq->count() === 1) {
                $s = (int)$uniq[0];
                if ($s === 2) return 'approved';
                if ($s === -1) return 'revised';
                if (in_array($s, [0, 1])) return 'progress';
                return 'revised';
            }
            return 'revised'; // campur -> revised
        };

        foreach ($divs as $m) {
            $b = $bucketOf('division', $m->id);
            if ($b) {
                $hasAny++;
                $$b++;
            }
        }
        foreach ($depts as $m) {
            $b = $bucketOf('department', $m->id);
            if ($b) {
                $hasAny++;
                $$b++;
            }
        }
        foreach ($secs as $m) {
            $b = $bucketOf('section', $m->id);
            if ($b) {
                $hasAny++;
                $$b++;
            }
        }
        foreach ($subs as $m) {
            $b = $bucketOf('sub_section', $m->id);
            if ($b) {
                $hasAny++;
                $$b++;
            }
        }

        $not = max($total - $hasAny, 0);

        return [
            'scope' => $total,
            'approved' => $approved,
            'progress' => $progress,
            'revised' => $revised,
            'not' => $not,
        ];
    }

    public function list(Request $req)
    {
        $module     = $req->query('module');
        $statusWant = $req->query('status');
        $company    = $req->query('company');
        $division   = $req->query('division');
        $department = $req->query('department');
        $month      = $req->query('month');

        if (!in_array($module, ['idp', 'hav', 'icp', 'rtc'], true)) return response()->json(['rows' => []]);
        if (!in_array($statusWant, ['approved', 'progress', 'revised', 'not'], true)) return response()->json(['rows' => []]);

        if ($module === 'rtc') {
            $rows = $this->listRtcStructures($company, $statusWant);
            return response()->json(['rows' => $rows]);
        }

        // ===== modul lain tetap =====
        $empScope = Employee::query()->forCompany($company);
        if ($department) $empScope->whereHas('departments', fn($q) => $q->where('department_id', $department));
        if ($division)   $empScope->whereHas('departments.division', fn($q) => $q->where('divisions_id', $division));
        $empIds = $empScope->pluck('id');

        [$mStart, $mEnd] = [null, null];
        if ($month) {
            try {
                $mStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $mEnd = (clone $mStart)->endOfMonth();
            } catch (\Throwable $e) {
                $mStart = $mEnd = null;
            }
        }

        switch ($module) {
            case 'idp':
                $rows = $this->listIdp($empIds, $statusWant, $mStart, $mEnd, $company);
                break;
            case 'hav':
                $rows = $this->listSimple(Employee::class, Hav::class, 'status', $empIds, $statusWant, $mStart, $mEnd);
                break;
            case 'icp':
                $rows = $this->listSimple(Employee::class, Icp::class, 'status', $empIds, $statusWant, $mStart, $mEnd);
                break;
        }

        return response()->json(['rows' => $rows]);
    }

    private function listRtcStructures(?string $company, string $statusWant): array
    {
        [$divs, $depts, $secs, $subs] = $this->allStructuresByCompany($company);

        // tentukan bucket struktur & siapkan dua jenis PIC (structural + director)
        $bucketOf = function (string $area, int $id) {
            $arr = Rtc::where('area', $area)->where('area_id', $id)->pluck('status')->all();
            if (empty($arr)) return null;
            $uniq = collect($arr)->unique()->values();
            if ($uniq->count() === 1) {
                $s = (int)$uniq[0];
                if ($s === 2) return 'approved';
                if ($s === -1) return 'revised';
                if (in_array($s, [0, 1])) return 'progress';
                return 'revised';
            }
            return 'revised';
        };

        $rows = collect();

        $push = function (string $area, $model) use (&$rows, $bucketOf) {
            $b = $bucketOf($area, $model->id); // null -> not created
            $structPic = $this->getStructuralPIC($area, $model);
            $dirPic    = $this->getDirectorForStructure($area, $model);

            $rows->push([
                'area'         => $area,
                'name'         => $model->name ?? '-',
                'bucket'       => $b,                      // 'approved'|'progress'|'revised'|null
                'struct_pic'   => optional($structPic)->name ?? '-',
                'director_pic' => optional($dirPic)->name ?? '-',
            ]);
        };

        foreach ($divs as $m)  $push('division',   $m);
        foreach ($depts as $m) $push('department', $m);
        foreach ($secs as $m)  $push('section',    $m);
        foreach ($subs as $m)  $push('sub_section', $m);

        // filter by status
        $rows = $rows->filter(function ($r) use ($statusWant) {
            if ($statusWant === 'not')      return $r['bucket'] === null;
            if ($statusWant === 'approved') return $r['bucket'] === 'approved';
            if ($statusWant === 'revised')  return $r['bucket'] === 'revised';
            if ($statusWant === 'progress') return $r['bucket'] === 'progress';
            return false;
        });

        return $rows->sortBy('name')->values()->all();
    }

    private function allStructuresByCompany(?string $company)
    {
        $divs = Division::query()
            ->when($company, fn($q) => $q->where('company', $company))
            ->with('plant')->orderBy('name')->get();

        $depts = Department::query()
            ->whereHas('division', fn($q) => $q->when($company, fn($qq) => $qq->where('company', $company)))
            ->with('division.plant')->orderBy('name')->get();

        $secs = Section::query()
            ->whereHas('department.division', fn($q) => $q->when($company, fn($qq) => $qq->where('company', $company)))
            ->with('department.division.plant')->orderBy('name')->get();

        $subs = SubSection::query()
            ->whereHas('section.department.division', fn($q) => $q->when($company, fn($qq) => $qq->where('company', $company)))
            ->with('section.department.division.plant')->orderBy('name')->get();

        return [$divs, $depts, $secs, $subs];
    }

    /** PIC struktural:
     *  - Division  : Direktur plant
     *  - Dept/Sec/Subsec : GM division
     */
    private function getStructuralPIC(string $area, $model): ?Employee
    {
        if ($area === 'division') {
            return $this->getDirectorForStructure('division', $model);
        }
        if ($area === 'department') {
            $gmId = $model->division->gm_id ?? null;
            return $gmId ? Employee::find($gmId) : null;
        }
        if ($area === 'section') {
            $gmId = $model->department->division->gm_id ?? null;
            return $gmId ? Employee::find($gmId) : null;
        }
        if ($area === 'sub_section') {
            $gmId = $model->section->department->division->gm_id ?? null;
            return $gmId ? Employee::find($gmId) : null;
        }
        return null;
    }

    /** Direktur plant (approval PIC) berdasarkan struktur */
    private function getDirectorForStructure(string $area, $model): ?Employee
    {
        $plant = null;
        if ($area === 'division') {
            $plant = $model->plant ?? null;
        } elseif ($area === 'department') {
            $plant = $model->division->plant ?? null;
        } elseif ($area === 'section') {
            $plant = $model->department->division->plant ?? null;
        } elseif ($area === 'sub_section') {
            $plant = $model->section->department->division->plant ?? null;
        }
        if (!$plant) return null;
        return Employee::find($plant->director_id);
    }

    /* ============================== LISTS UNTUK MODUL LAIN (TETAP) ============================== */

    private function listSimple($empModel, $modelClass, string $statusCol, $empIds, string $statusWant, $start, $end)
    {
        $approved = [3, 4];
        $revised = [-1];
        $q = $modelClass::query()->whereIn('employee_id', $empIds);
        if ($start && $end) $q->whereBetween('updated_at', [$start, $end]);

        if ($statusWant === 'approved') $q->whereIn($statusCol, $approved);
        elseif ($statusWant === 'revised') $q->whereIn($statusCol, $revised);
        elseif ($statusWant === 'progress') $q->whereNotIn($statusCol, array_merge($approved, $revised));
        else {
            $has = $modelClass::query()->whereIn('employee_id', $empIds)->pluck('employee_id')->unique();
            $no  = collect($empIds)->diff($has)->values();
            return Employee::whereIn('id', $no)->orderBy('name')->get()
                ->map(fn($e) => [
                    'employee' => $e->name,
                    'pic' => $this->short2(optional($e->getSuperiorsByLevel(1)->first())->name ?? '-'),
                ])->values();
        }

        $matched = $q->pluck('employee_id')->unique()->values();
        return Employee::whereIn('id', $matched)->orderBy('name')->get()
            ->map(function ($e) {
                $pic = optional($e->getSuperiorsByLevel(1)->first())->name ?? '-';
                return ['employee' => $e->name, 'pic' => $this->short2($pic)];
            })->values();
    }

    private function listIdp($empIds, string $statusWant, $start, $end, ?string $company)
    {
        $base = Idp::query()
            ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
            ->join('employees', 'assessments.employee_id', '=', 'employees.id')
            ->whereIn('employees.id', $empIds)
            ->selectRaw('employees.id as emp_id, idp.updated_at');

        if ($start && $end) $base->whereBetween('idp.updated_at', [$start, $end]);

        $mgr = (clone $base)->whereRaw('LOWER(employees.position) LIKE ?', ['%manager%']);
        $non = (clone $base)->whereRaw('LOWER(employees.position) NOT LIKE ?', ['%manager%']);

        if ($statusWant === 'approved') {
            $mgr->whereIn('idp.status', [4]);
            $non->whereIn('idp.status', [3, 4]);
            $q = $mgr->unionAll($non);
        } elseif ($statusWant === 'revised') {
            $q = $base->where('idp.status', -1);
        } elseif ($statusWant === 'progress') {
            $mgr->whereIn('idp.status', [1, 2, 3]);
            $non->whereIn('idp.status', [1, 2]);
            $q = $mgr->unionAll($non);
        } else {
            $has = Idp::query()->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
                ->whereIn('assessments.employee_id', $empIds)->distinct()->pluck('assessments.employee_id');
            $no = collect($empIds)->diff($has)->values();
            return Employee::whereIn('id', $no)->orderBy('name')->get()
                ->map(fn($e) => [
                    'employee' => $e->name,
                    'pic' => $this->short2(optional($e->getSuperiorsByLevel(1)->first())->name ?? '-'),
                ])->values();
        }

        $matched = DB::query()->fromSub($q, 't')->distinct()->pluck('emp_id');

        return Employee::whereIn('id', $matched)->orderBy('name')->get()
            ->map(function ($e) {
                $pic = optional($e->getSuperiorsByLevel(1)->first())->name ?? '-';
                return ['employee' => $e->name, 'pic' => $this->short2($pic)];
            })->values();
    }

    private function short2(?string $name): string
    {
        $name = trim((string)$name);
        if ($name === '') return '-';
        $parts = preg_split('/\s+/', $name);
        return implode(' ', array_slice($parts, 0, 2));
    }
}
