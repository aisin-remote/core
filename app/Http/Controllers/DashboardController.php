<?php

namespace App\Http\Controllers;

use App\Models\{Employee, Hav, Icp, Idp, Rtc, Division, Department, Section, SubSection};
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

        $hav = $this->modulePerEmployeeBuckets(
            (new Hav)->getTable(),
            'status',
            $company,
            ['approved' => [3], 'revised' => [-1], 'progress' => [1, 2]]
        );

        $icp = $this->modulePerEmployeeBuckets(
            (new Icp)->getTable(),
            'status',
            $company,
            ['approved' => [3], 'revised' => [-1], 'progress' => [1, 2]]
        );

        // RTC: 2 approved, -1 revised, 0/1 progress
        $rtc = $this->modulePerEmployeeBuckets(
            (new Rtc)->getTable(),
            'status',
            $company,
            ['approved' => [2], 'revised' => [-1], 'progress' => [0, 1]]
        );

        $all = $this->allPerEmployeeBuckets($company);

        return response()->json(compact('idp', 'hav', 'icp', 'rtc', 'all'));
    }

    private function allPerEmployeeBuckets(?string $company): array
    {
        $scopeIds = Employee::forCompany($company)->pluck('id');

        $approvedIds = Employee::forCompany($company)
            ->where(function ($q) {
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
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->whereIn('havs.status', [3]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->whereIn('icp.status', [3]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->whereIn('rtc.status', [2]));
            })
            ->pluck('id')->unique();

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
            })
            ->pluck('id')->unique();

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
            })
            ->pluck('id')->unique();

        $covered = $approvedIds->merge($revisedIds)->merge($progressIds)->unique();
        $not = $scopeIds->diff($covered)->count();

        return [
            'scope'    => $scopeIds->count(),
            'approved' => $approvedIds->count(),
            'revised'  => $revisedIds->count(),
            'progress' => $progressIds->count(),
            'not'      => $not,
        ];
    }

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
                            SUM(CASE WHEN LOWER(employees.position) LIKE '%manager%'     AND idp.status IN (4)   THEN 1 ELSE 0 END) > 0
                            OR
                            SUM(CASE WHEN LOWER(employees.position) NOT LIKE '%manager%' AND idp.status IN (3,4) THEN 1 ELSE 0 END) > 0
                        THEN 'approved'
                        WHEN SUM(CASE WHEN idp.status = -1 THEN 1 ELSE 0 END) > 0
                        THEN 'revised'
                        WHEN
                            SUM(CASE WHEN LOWER(employees.position) LIKE '%manager%'     AND idp.status IN (1,2,3) THEN 1 ELSE 0 END) > 0
                            OR
                            SUM(CASE WHEN LOWER(employees.position) NOT LIKE '%manager%' AND idp.status IN (1,2)   THEN 1 ELSE 0 END) > 0
                        THEN 'progress'
                        ELSE 'progress'
                    END AS bucket
                "),
            ])
            ->groupBy('assessments.employee_id');

        $counts = DB::query()->fromSub($perEmp, 't')
            ->select('bucket', DB::raw('COUNT(*) as c'))
            ->groupBy('bucket')
            ->pluck('c', 'bucket');

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

        $in = fn(array $nums) => implode(',', array_map('intval', $nums ?: [-99999]));

        $perEmp = (clone $base)
            ->select([
                $joinViaAssessment ? 'assessments.employee_id' : "$table.employee_id AS employee_id",
                DB::raw("
                    CASE
                        WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['approved'] ?? []) . ") THEN 1 ELSE 0 END) > 0 THEN 'approved'
                        WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['revised']  ?? []) . ") THEN 1 ELSE 0 END) > 0 THEN 'revised'
                        WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['progress'] ?? []) . ") THEN 1 ELSE 0 END) > 0 THEN 'progress'
                        ELSE 'progress'
                    END AS bucket
                "),
            ])
            ->groupBy($joinViaAssessment ? 'assessments.employee_id' : "$table.employee_id");

        $counts = DB::query()->fromSub($perEmp, 't')
            ->select('bucket', DB::raw('COUNT(*) as c'))
            ->groupBy('bucket')
            ->pluck('c', 'bucket');

        $approved = (int)($counts['approved'] ?? 0);
        $revised  = (int)($counts['revised']  ?? 0);
        $progress = (int)($counts['progress'] ?? 0);
        $not      = max($scope - $distinctEmp, 0);

        return compact('scope', 'approved', 'progress', 'revised', 'not');
    }

    /**************************************** LIST ****************************************/

    public function list(Request $req)
    {
        $module     = $req->query('module');
        $statusWant = $req->query('status');
        $company    = $req->query('company');

        if (!in_array($module, ['idp', 'hav', 'icp', 'rtc'], true)) return response()->json(['rows' => []]);
        if (!in_array($statusWant, ['approved', 'progress', 'revised', 'not'], true)) return response()->json(['rows' => []]);

        if ($module === 'rtc') {
            $rows = $this->listRtcStructures($statusWant, $company);
            return response()->json(['rows' => $rows]);
        }

        $empIds = Employee::query()->forCompany($company)->pluck('id');

        switch ($module) {
            case 'idp':
                $rows = $this->listIdp($empIds, $statusWant, null, null, $company);
                break;
            case 'hav':
                $rows = $this->listSimple(Employee::class, Hav::class, 'status', $empIds, $statusWant, null, null);
                break;
            case 'icp':
                $rows = $this->listSimple(Employee::class, Icp::class, 'status', $empIds, $statusWant, null, null);
                break;
        }

        return response()->json(['rows' => $rows]);
    }

    /** ===== Helpers ===== */

    private function short2(?string $name): string
    {
        $name = trim((string)$name);
        if ($name === '') return '-';
        $parts = preg_split('/\s+/', $name);
        return implode(' ', array_slice($parts, 0, 2));
    }

    /** Ambil PIC atasan langsung berbasis struktur; fallback supervisor relation */
    private function picForEmployee(Employee $e): string
    {
        $sup = $e->getSuperiorsByLevel(1)->first(); // gunakan logika struktural dari model
        if ($sup) return $this->short2($sup->name);

        // fallback terakhir
        if ($e->supervisor) return $this->short2($e->supervisor->name);

        return '-';
    }

    /** HAV/ICP simple list (per karyawan) */
    private function listSimple($empModel, $modelClass, string $statusCol, $empIds, string $statusWant, $start, $end)
    {
        $approved = [3, 4];
        $revised  = [-1];

        $q = $modelClass::query()->whereIn('employee_id', $empIds);
        if ($start && $end) $q->whereBetween('updated_at', [$start, $end]);

        if ($statusWant === 'approved') $q->whereIn($statusCol, $approved);
        elseif ($statusWant === 'revised')  $q->whereIn($statusCol, $revised);
        elseif ($statusWant === 'progress') $q->whereNotIn($statusCol, array_merge($approved, $revised));
        else {
            // NOT CREATED
            $has = $modelClass::query()->whereIn('employee_id', $empIds)->pluck('employee_id')->unique();
            $noRecordIds = collect($empIds)->diff($has)->values();

            return Employee::whereIn('id', $noRecordIds)
                ->orderBy('name')
                ->get()
                ->map(fn($e) => [
                    'employee' => $this->short2($e->name),
                    'pic'      => $this->picForEmployee($e),
                ])->values()->all();
        }

        $matchedEmpIds = $q->pluck('employee_id')->unique()->values();

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')
            ->get()
            ->map(fn($e) => [
                'employee' => $this->short2($e->name),
                'pic'      => $this->picForEmployee($e),
            ])->values()->all();
    }

    /** IDP list (per karyawan) */
    private function listIdp($empIds, string $statusWant, $start, $end, ?string $company)
    {
        $base = Idp::query()
            ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
            ->join('employees',   'assessments.employee_id', '=', 'employees.id')
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
            // NOT CREATED
            $hasIdpEmp = Idp::query()
                ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
                ->whereIn('assessments.employee_id', $empIds)
                ->distinct()->pluck('assessments.employee_id');

            $noIdpEmpIds = collect($empIds)->diff($hasIdpEmp)->values();

            return Employee::whereIn('id', $noIdpEmpIds)
                ->orderBy('name')
                ->get()
                ->map(fn($e) => [
                    'employee' => $this->short2($e->name),
                    'pic'      => $this->picForEmployee($e),
                ])->values()->all();
        }

        $matchedEmpIds = DB::query()->fromSub($q, 't')->distinct()->pluck('emp_id');

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')
            ->get()
            ->map(fn($e) => [
                'employee' => $this->short2($e->name),
                'pic'      => $this->picForEmployee($e),
            ])->values()->all();
    }

    /** RTC struktur list (Division/Department/Section/Sub Section) */
    private function listRtcStructures(string $status, ?string $company): array
    {
        $divs = Division::with(['plant.director', 'gm'])
            ->when($company, fn($q) => $q->where('company', $company))
            ->get();

        if ($divs->isEmpty()) return [];

        $divIds  = $divs->pluck('id');
        $depts   = Department::with(['division.gm'])->whereIn('division_id', $divIds)->get();
        $deptIds = $depts->pluck('id');

        $secs    = Section::with(['department.division.gm'])->whereIn('department_id', $deptIds)->get();
        $secIds  = $secs->pluck('id');

        $subs    = SubSection::with(['section.department.division.gm'])->whereIn('section_id', $secIds)->get();
        $subIds  = $subs->pluck('id');

        $statusMap = [
            'approved' => [2],
            'progress' => [0, 1],
            'revised'  => [-1],
        ];

        $idsWith = function (string $area, $ids, array $statuses) {
            if (empty($ids)) return collect();
            return Rtc::where('area', $area)->whereIn('area_id', $ids)->whereIn('status', $statuses)->distinct()->pluck('area_id');
        };

        $idsWithoutAny = function (string $area, $ids) {
            if (empty($ids)) return collect($ids);
            $has = Rtc::where('area', $area)->whereIn('area_id', $ids)->distinct()->pluck('area_id');
            return collect($ids)->diff($has)->values();
        };

        $rows = [];
        $short = fn($s) => $this->short2($s);

        // Division → PIC Director
        $pick = $status === 'not' ? $idsWithoutAny('division', $divIds) : $idsWith('division', $divIds, $statusMap[$status] ?? []);
        foreach ($divs->whereIn('id', $pick) as $d) {
            $rows[] = [
                'structure' => 'Division - ' . ($d->name ?? '-'),
                'pic'       => $short(optional($d->plant)->director?->name),
            ];
        }

        // Department → PIC GM
        $pick = $status === 'not' ? $idsWithoutAny('department', $deptIds) : $idsWith('department', $deptIds, $statusMap[$status] ?? []);
        foreach ($depts->whereIn('id', $pick) as $dept) {
            $rows[] = [
                'structure' => 'Department - ' . ($dept->name ?? '-'),
                'pic'       => $short(optional($dept->division)->gm?->name),
            ];
        }

        // Section → PIC GM
        $pick = $status === 'not' ? $idsWithoutAny('section', $secIds) : $idsWith('section', $secIds, $statusMap[$status] ?? []);
        foreach ($secs->whereIn('id', $pick) as $sec) {
            $rows[] = [
                'structure' => 'Section - ' . ($sec->name ?? '-'),
                'pic'       => $short(optional($sec->department?->division)->gm?->name),
            ];
        }

        // Sub Section → PIC GM
        $pick = $status === 'not' ? $idsWithoutAny('sub_section', $subIds) : $idsWith('sub_section', $subIds, $statusMap[$status] ?? []);
        foreach ($subs->whereIn('id', $pick) as $sub) {
            $rows[] = [
                'structure' => 'Sub Section - ' . ($sub->name ?? '-'),
                'pic'       => $short(optional($sub->section?->department?->division)->gm?->name),
            ];
        }

        usort($rows, fn($a, $b) => strcasecmp($a['structure'], $b['structure']));
        return $rows;
    }
}
