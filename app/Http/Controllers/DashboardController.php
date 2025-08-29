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

        // === Per modul (per-employee) untuk KPI/Chart ===
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

        // RTC: mapping baru → 0/1 progress, 2 approved, -1 revised
        $rtc = $this->modulePerEmployeeBuckets((new Rtc)->getTable(), 'status', $company, [
            'approved' => [2],
            'revised'  => [-1],
            'progress' => [0, 1],
        ]);

        // === ALL = per karyawan unik lintas modul ===
        $all = $this->allPerEmployeeBuckets($company);

        return response()->json(compact('idp', 'hav', 'icp', 'rtc', 'all'));
    }

    /***********************************************************************
     * LIST untuk tabel per status di setiap tab
     ***********************************************************************/
    public function list(Request $req)
    {
        $module      = $req->query('module');
        $statusWant  = $req->query('status');
        $company     = $req->query('company');
        $division    = $req->query('division');   // (opsional, belum dipakai di contoh)
        $department  = $req->query('department'); // (opsional, belum dipakai di contoh)
        $month       = $req->query('month');      // (opsional)

        if (!in_array($module, ['idp', 'hav', 'icp', 'rtc'], true)) {
            return response()->json(['rows' => []]);
        }
        if (!in_array($statusWant, ['approved', 'progress', 'revised', 'not'], true)) {
            return response()->json(['rows' => []]);
        }

        // ==== Khusus RTC: tampilkan struktur + PIC (roll-up 3 term) ====
        if ($module === 'rtc') {
            $rows = $this->listRtcStructures($company, $statusWant);
            return response()->json(['rows' => $rows]);
        }

        // ==== Modul lain: tetap per-employee, tapi kirim juga PIC (atasan langsung) ====
        $empScope = Employee::query()->forCompany($company);
        if ($department) {
            $empScope->whereHas('departments', fn($q) => $q->where('department_id', $department));
        }
        if ($division) {
            $empScope->whereHas('departments.division', fn($q) => $q->where('divisions_id', $division));
        }
        $empIds = $empScope->pluck('id');

        // range bulan (opsional)
        [$mStart, $mEnd] = [null, null];
        if ($month) {
            try {
                $mStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $mEnd   = (clone $mStart)->endOfMonth();
            } catch (\Throwable $e) {
            }
        }

        switch ($module) {
            case 'hav':
                $rows = $this->listSimple(Employee::class, Hav::class, 'status', $empIds, $statusWant, $mStart, $mEnd, true);
                break;
            case 'icp':
                $rows = $this->listSimple(Employee::class, Icp::class, 'status', $empIds, $statusWant, $mStart, $mEnd, true);
                break;
            case 'idp':
            default:
                $rows = $this->listIdp($empIds, $statusWant, $mStart, $mEnd, $company, true);
        }

        return response()->json(['rows' => $rows]);
    }

    /***********************************************************************
     * ===== Helpers RTC (roll-up struktur) =====
     ***********************************************************************/
    /** Nama dipotong maks 2 kata */
    private function shortName(?string $name): string
    {
        if (!$name) return '-';
        $parts = preg_split('/\s+/', trim($name));
        return implode(' ', array_slice($parts, 0, 2));
    }

    /** PIC RTC:
     *  Division  -> Direktur (plant director)
     *  Dept/Sec/SubSec -> GM dari division
     */
    private function rtcPicFor(string $area, $model): string
    {
        if ($area === 'division') {
            $dirId = optional($model->plant)->director_id ?? null;
            $dir   = $dirId ? Employee::find($dirId) : null;
            return $this->shortName($dir?->name);
        }
        // Department/Section/SubSection → GM
        $gmId = match ($area) {
            'department' => optional($model->division)->gm_id ?? null,
            'section'    => optional($model->department?->division)->gm_id ?? null,
            'sub_section' => optional($model->section?->department?->division)->gm_id ?? null,
            default      => null,
        };
        $gm = $gmId ? Employee::find($gmId) : null;
        return $this->shortName($gm?->name);
    }

    /** Roll-up status 3 term:
     *  - all 2 -> approved
     *  - all -1 -> revised
     *  - all 1 -> progress
     *  - campur/ada 0/null -> revised
     *  - tidak ada satupun -> not
     */
    private function rtcAggregateStatus(array $termStatuses): string
    {
        if (empty($termStatuses) || array_filter($termStatuses, fn($v) => $v !== null) === []) {
            return 'not';
        }
        $norm = array_map(fn($s) => $s === null ? null : (int)$s, $termStatuses);
        $vals = array_values(array_unique(array_filter($norm, fn($v) => $v !== null)));

        if (count($vals) === 1) {
            return match ($vals[0]) {
                2   => 'approved',
                -1  => 'revised',
                1 => 'progress',
                default => 'revised',
            };
        }
        return 'revised';
    }

    /** List RTC per struktur sesuai status roll-up */
    private function listRtcStructures(?string $company, string $statusWant): array
    {
        $rows = [];

        $divs = Division::with('plant')
            ->when($company, fn($q) => $q->where('company', $company))
            ->orderBy('name')->get();

        $depts = Department::with('division')
            ->when($company, fn($q) => $q->whereHas('division', fn($qq) => $qq->where('company', $company)))
            ->orderBy('name')->get();

        $secs = Section::with('department.division')
            ->when($company, fn($q) => $q->whereHas('department.division', fn($qq) => $qq->where('company', $company)))
            ->orderBy('name')->get();

        $subs = SubSection::with('section.department.division')
            ->when($company, fn($q) => $q->whereHas('section.department.division', fn($qq) => $qq->where('company', $company)))
            ->orderBy('name')->get();

        $pushIf = function (string $label, string $pic, string $roll) use (&$rows, $statusWant) {
            if ($roll === $statusWant) {
                $rows[] = ['structure' => $label, 'pic' => $pic ?: '-'];
            }
        };

        foreach ($divs as $d) {
            $terms = Rtc::where('area', 'division')->where('area_id', $d->id)->pluck('status', 'term');
            $roll  = $this->rtcAggregateStatus([$terms->get('short'), $terms->get('mid'), $terms->get('long')]);
            $pushIf('Division - ' . ($d->name ?? '-'), $this->rtcPicFor('division', $d), $roll);
        }
        foreach ($depts as $dp) {
            $terms = Rtc::where('area', 'department')->where('area_id', $dp->id)->pluck('status', 'term');
            $roll  = $this->rtcAggregateStatus([$terms->get('short'), $terms->get('mid'), $terms->get('long')]);
            $pushIf('Department - ' . ($dp->name ?? '-'), $this->rtcPicFor('department', $dp), $roll);
        }
        foreach ($secs as $sc) {
            $terms = Rtc::where('area', 'section')->where('area_id', $sc->id)->pluck('status', 'term');
            $roll  = $this->rtcAggregateStatus([$terms->get('short'), $terms->get('mid'), $terms->get('long')]);
            $pushIf('Section - ' . ($sc->name ?? '-'), $this->rtcPicFor('section', $sc), $roll);
        }
        foreach ($subs as $sb) {
            $terms = Rtc::where('area', 'sub_section')->where('area_id', $sb->id)->pluck('status', 'term');
            $roll  = $this->rtcAggregateStatus([$terms->get('short'), $terms->get('mid'), $terms->get('long')]);
            $pushIf('Sub Section - ' . ($sb->name ?? '-'), $this->rtcPicFor('sub_section', $sb), $roll);
        }

        usort($rows, fn($a, $b) => strcasecmp($a['structure'], $b['structure']));
        return $rows;
    }

    /***********************************************************************
     * ===== Helpers modul per-employee (IDP/HAV/ICP) + ALL =====
     ***********************************************************************/
    /** PIC untuk employee = atasan langsung (level 1), dipotong 2 kata */
    private function employeePicName(int $empId): string
    {
        $emp = Employee::find($empId);
        if (!$emp) return '-';
        $sup = $emp->getSuperiorsByLevel(1)->first();
        return $this->shortName($sup?->name);
    }

    /** Modul HAV/ICP generic list (kembalikan employee + pic) */
    private function listSimple($empModel, $modelClass, string $statusCol, $empIds, string $statusWant, $start, $end, bool $withPic = false)
    {
        $approved = [3, 4]; // (aman untuk historis)
        $revised  = [-1];

        $q = $modelClass::query()->whereIn('employee_id', $empIds);
        if ($start && $end) $q->whereBetween('updated_at', [$start, $end]);

        if ($statusWant === 'approved')      $q->whereIn($statusCol, $approved);
        elseif ($statusWant === 'revised')   $q->whereIn($statusCol, $revised);
        elseif ($statusWant === 'progress')  $q->whereNotIn($statusCol, array_merge($approved, $revised));
        else {
            // NOT CREATED → employee tanpa record apa pun
            $has = $modelClass::query()->whereIn('employee_id', $empIds)->pluck('employee_id')->unique();
            $noRecordIds = collect($empIds)->diff($has)->values();

            return Employee::whereIn('id', $noRecordIds)
                ->orderBy('name')->get()
                ->map(fn($e) => [
                    'employee' => $e->name,
                    'pic'      => $withPic ? $this->employeePicName($e->id) : null,
                ])->values();
        }

        $matchedEmpIds = $q->pluck('employee_id')->unique()->values();

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')->get()
            ->map(fn($e) => [
                'employee' => $e->name,
                'pic'      => $withPic ? $this->employeePicName($e->id) : null,
            ])->values();
    }

    /** Modul IDP list (rule manager vs non-manager) */
    private function listIdp($empIds, string $statusWant, $start, $end, ?string $company, bool $withPic = false)
    {
        $base = Idp::query()
            ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
            ->join('employees',   'assessments.employee_id', '=', 'employees.id')
            ->whereIn('employees.id', $empIds)
            ->selectRaw('employees.id as emp_id, idp.updated_at, employees.position');

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
                ->orderBy('name')->get()
                ->map(fn($e) => [
                    'employee' => $e->name,
                    'pic'      => $withPic ? $this->employeePicName($e->id) : null,
                ])->values();
        }

        $matchedEmpIds = DB::query()->fromSub($q, 't')->distinct()->pluck('emp_id');

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')->get()
            ->map(fn($e) => [
                'employee' => $e->name,
                'pic'      => $withPic ? $this->employeePicName($e->id) : null,
            ])->values();
    }

    /** ALL buckets (per karyawan unik lintas modul) */
    private function allPerEmployeeBuckets(?string $company): array
    {
        $scopeIds = Employee::forCompany($company)->pluck('id');

        // Approved: ada approved di salah satu modul
        $approvedIds = Employee::forCompany($company)
            ->where(function ($q) {
                // IDP approved (manager {4}, non-manager {3,4})
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
                    // HAV/ICP approved {3}; RTC approved {2}
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->whereIn('havs.status', [3]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->whereIn('icp.status', [3]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->whereIn('rtc.status', [2]));
            })
            ->pluck('id')->unique();

        // Revised: ada revised tapi bukan approved
        $revisedIds = Employee::forCompany($company)
            ->whereNotIn('id', $approvedIds)
            ->where(function ($q) {
                $q->whereExists(fn($qq) => $qq->selectRaw(1)->from('idp')->join('assessments', 'idp.assessment_id', '=', 'assessments.id')->whereColumn('assessments.employee_id', 'employees.id')->where('idp.status', -1))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->where('havs.status', -1))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->where('icp.status', -1))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->where('rtc.status', -1));
            })
            ->pluck('id')->unique();

        // Progress: ada progress tapi bukan approved/revised
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

    /** IDP buckets (khusus manager vs non-manager) */
    private function idpPerEmployeeBuckets(?string $company): array
    {
        $scope = Employee::forCompany($company)->count();

        $base = DB::table('idp')
            ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
            ->join('employees',  'assessments.employee_id', '=', 'employees.id')
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
            ->groupBy('bucket')->pluck('c', 'bucket');

        $approved = (int)($counts['approved'] ?? 0);
        $revised  = (int)($counts['revised']  ?? 0);
        $progress = (int)($counts['progress'] ?? 0);
        $not      = max($scope - $distinctEmp, 0);

        return compact('scope', 'approved', 'progress', 'revised', 'not');
    }

    /** Modul HAV/ICP/RTC generic buckets per-employee (untuk KPI/Chart) */
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
            ->groupBy('bucket')->pluck('c', 'bucket');

        $approved = (int)($counts['approved'] ?? 0);
        $revised  = (int)($counts['revised']  ?? 0);
        $progress = (int)($counts['progress'] ?? 0);
        $not      = max($scope - $distinctEmp, 0);

        return compact('scope', 'approved', 'progress', 'revised', 'not');
    }
}
