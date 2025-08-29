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
}
