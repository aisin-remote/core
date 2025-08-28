<?php

namespace App\Http\Controllers;

use App\Models\{Employee, Hav, Icp, Idp, Rtc};
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

        // Per modul (per employee, bucket eksklusif)
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
        $rtc = $this->modulePerEmployeeBuckets((new Rtc)->getTable(), 'status', $company, [
            'approved' => [3],
            'revised'  => [-1],
            'progress' => [1, 2],
        ]);

        // ALL per-employee (across modules)
        $all = $this->allPerEmployeeBuckets($company);

        return response()->json(compact('idp', 'hav', 'icp', 'rtc', 'all'));
    }

    /**
     * Hitung ALL per karyawan dengan prioritas:
     * Approved > Revised > Progress > Not Created
     */
    private function allPerEmployeeBuckets(?string $company): array
    {
        $scopeIds = Employee::forCompany($company)->pluck('id');

        // APPROVED: ada approved di salah satu modul
        $approvedIds = Employee::forCompany($company)
            ->where(function ($q) {
                // IDP approved: manager {4}, non-manager {3,4}
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
                    // HAV/ICP/RTC approved {3}
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->whereIn('havs.status', [3]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->whereIn('icp.status', [3]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->whereIn('rtc.status', [3]));
            })
            ->pluck('id')->unique();

        // REVISED: ada revised di salah satu modul & bukan approved
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

        // PROGRESS: ada progress di salah satu modul & bukan approved/revised
        $progressIds = Employee::forCompany($company)
            ->whereNotIn('id', $approvedIds)
            ->whereNotIn('id', $revisedIds)
            ->where(function ($q) {
                // IDP progress: manager {1,2,3}, non-manager {1,2}
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
                    // HAV/ICP/RTC progress {1,2}
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->whereIn('havs.status', [1, 2]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->whereIn('icp.status', [1, 2]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->whereIn('rtc.status', [1, 2]));
            })
            ->pluck('id')->unique();

        $covered = $approvedIds->merge($revisedIds)->merge($progressIds)->unique();
        $not     = $scopeIds->diff($covered)->count();

        return [
            'scope'    => $scopeIds->count(),
            'approved' => $approvedIds->count(),
            'revised'  => $revisedIds->count(),
            'progress' => $progressIds->count(),
            'not'      => $not,
        ];
    }

    /**
     * IDP per-employee (Manager vs Non-Manager)
     */
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

    /**
     * Modul generik per-employee (HAV/ICP/RTC)
     * Prioritas: Approved > Revised > Progress
     */
    private function modulePerEmployeeBuckets(string $table, string $statusCol, ?string $company, array $map, bool $joinViaAssessment = false): array
    {
        $scope = Employee::forCompany($company)->count();

        $base = DB::table($table);

        if ($joinViaAssessment) {
            $base->join('assessments', "$table.assessment_id", '=', 'assessments.id')
                ->join('employees',   'assessments.employee_id', '=', 'employees.id');
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
                        WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['approved'] ?? []) . ") THEN 1 ELSE 0 END) > 0
                        THEN 'approved'
                        WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['revised']  ?? []) . ") THEN 1 ELSE 0 END) > 0
                        THEN 'revised'
                        WHEN SUM(CASE WHEN $table.$statusCol IN (" . $in($map['progress'] ?? []) . ") THEN 1 ELSE 0 END) > 0
                        THEN 'progress'
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

    /*************************************************** LIST *********************************************/
    public function list(Request $req)
    {
        $module     = $req->query('module');
        $statusWant = $req->query('status');
        $company    = $req->query('company');
        $division   = $req->query('division');
        $department = $req->query('department');
        $month      = $req->query('month');

        if (!in_array($module, ['idp', 'hav', 'icp', 'rtc'], true)) {
            return response()->json(['rows' => []]);
        }
        if (!in_array($statusWant, ['approved', 'progress', 'revised', 'not'], true)) {
            return response()->json(['rows' => []]);
        }

        // Scope employee
        $empScope = Employee::query()->forCompany($company);
        if ($department) {
            $empScope->whereHas('departments', fn($q) => $q->where('department_id', $department));
        }
        if ($division) {
            $empScope->whereHas('departments.division', fn($q) => $q->where('divisions_id', $division));
        }
        $empIds = $empScope->pluck('id');

        // Month filter (opsional)
        [$mStart, $mEnd] = [null, null];
        if ($month) {
            try {
                $mStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $mEnd   = (clone $mStart)->endOfMonth();
            } catch (\Throwable $e) {
                $mStart = $mEnd = null;
            }
        }

        switch ($module) {
            case 'idp':
                $rows = $this->listIdp($empIds, $statusWant, $mStart, $mEnd, $company);
                break;
            case 'hav':
                // Selaras dengan summary: approved {3}, revised {-1}, progress {1,2}
                $rows = $this->listSimple(Hav::class, 'status', $empIds, $statusWant, $mStart, $mEnd, [
                    'approved' => [3],
                    'revised'  => [-1],
                    'progress' => [1, 2],
                ]);
                break;
            case 'icp':
                $rows = $this->listSimple(Icp::class, 'status', $empIds, $statusWant, $mStart, $mEnd, [
                    'approved' => [3],
                    'revised'  => [-1],
                    'progress' => [1, 2],
                ]);
                break;
            case 'rtc':
                $rows = $this->listSimple(Rtc::class, 'status', $empIds, $statusWant, $mStart, $mEnd, [
                    'approved' => [3],
                    'revised'  => [-1],
                    'progress' => [1, 2],
                ]);
                break;
        }

        return response()->json(['rows' => $rows]);
    }

    /**
     * List HAV/ICP/RTC per-employee (selaras dengan summary)
     */
    private function listSimple($modelClass, string $statusCol, $empIds, string $statusWant, $start, $end, array $map)
    {
        $q = $modelClass::query()->whereIn('employee_id', $empIds);
        if ($start && $end) $q->whereBetween('updated_at', [$start, $end]);

        if ($statusWant === 'approved') {
            $q->whereIn($statusCol, $map['approved'] ?? []);
        } elseif ($statusWant === 'revised') {
            $q->whereIn($statusCol, $map['revised'] ?? []);
        } elseif ($statusWant === 'progress') {
            $q->whereIn($statusCol, $map['progress'] ?? []);
        } else {
            // NOT CREATED → karyawan yang tidak punya record
            $has         = $modelClass::query()->whereIn('employee_id', $empIds)->pluck('employee_id')->unique();
            $noRecordIds = collect($empIds)->diff($has)->values();

            return Employee::whereIn('id', $noRecordIds)
                ->orderBy('name')
                ->get()
                ->map(fn($e) => ['employee' => $e->name])
                ->values();
        }

        // Per-employee unik
        $matchedEmpIds = $q->pluck('employee_id')->unique()->values();

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')
            ->get()
            ->map(fn($e) => ['employee' => $e->name])
            ->values();
    }

    /**
     * List IDP per-employee (Manager vs Non-Manager)
     */
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
            // NOT CREATED → tanpa IDP sama sekali
            $hasIdpEmp = Idp::query()
                ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
                ->whereIn('assessments.employee_id', $empIds)
                ->distinct()->pluck('assessments.employee_id');

            $noIdpEmpIds = collect($empIds)->diff($hasIdpEmp)->values();

            return Employee::whereIn('id', $noIdpEmpIds)
                ->orderBy('name')
                ->get()
                ->map(fn($e) => ['employee' => $e->name])
                ->values();
        }

        $matchedEmpIds = DB::query()->fromSub($q, 't')->distinct()->pluck('emp_id');

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')
            ->get()
            ->map(fn($e) => ['employee' => $e->name])
            ->values();
    }

    /** (unused helper—disimpan kalau nanti diperlukan) */
    private function employeesWithAnyModule(?string $company): int
    {
        return Employee::forCompany($company)
            ->where(function ($q) {
                $q->whereExists(function ($qq) {
                    $qq->selectRaw(1)->from('idp')
                        ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
                        ->whereColumn('assessments.employee_id', 'employees.id');
                })->orWhereExists(function ($qq) {
                    $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id');
                })->orWhereExists(function ($qq) {
                    $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id');
                })->orWhereExists(function ($qq) {
                    $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id');
                });
            })
            ->count();
    }
}
