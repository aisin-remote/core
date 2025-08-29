<?php

namespace App\Http\Controllers;

use App\Models\{Employee, Hav, Icp, Idp, Rtc, Division, Department, Section, SubSection, Plant};
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

        // Per-employee buckets
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

        // >>> RTC mapping khusus: 0/1 progress, 2 approved, -1 revised
        $rtc = $this->modulePerEmployeeBuckets(
            (new Rtc)->getTable(),
            'status',
            $company,
            ['approved' => [2], 'revised' => [-1], 'progress' => [0, 1]],
            joinViaAssessment: false
        );

        // ALL dihitung per-karyawan (distinct coverage seluruh modul)
        $all = $this->allPerEmployeeBuckets($company);

        return response()->json(compact('idp', 'hav', 'icp', 'rtc', 'all'));
    }

    private function allPerEmployeeBuckets(?string $company): array
    {
        $scopeIds = Employee::forCompany($company)->pluck('id');

        // Approved = punya approved di salah satu modul
        $approvedIds = Employee::forCompany($company)
            ->where(function ($q) {
                // IDP approved (manager:4, non-manager:3/4)
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
                    // HAV/ICP approved (3)
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->whereIn('havs.status', [3]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->whereIn('icp.status', [3]))
                    // RTC approved (2)
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->whereIn('rtc.status', [2]));
            })
            ->pluck('id')->unique();

        // Revised (bukan approved)
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
                    // RTC revised (-1)
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->where('rtc.status', -1));
            })
            ->pluck('id')->unique();

        // Progress (bukan approved / revised)
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
                    // HAV/ICP progress {1,2}
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('havs')->whereColumn('havs.employee_id', 'employees.id')->whereIn('havs.status', [1, 2]))
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('icp')->whereColumn('icp.employee_id', 'employees.id')->whereIn('icp.status', [1, 2]))
                    // RTC progress {0,1}
                    ->orWhereExists(fn($qq) => $qq->selectRaw(1)->from('rtc')->whereColumn('rtc.employee_id', 'employees.id')->whereIn('rtc.status', [0, 1]));
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
     * IDP per-employee buckets (aturan Manager / Non-Manager)
     */
    private function idpPerEmployeeBuckets(?string $company): array
    {
        $scope = Employee::forCompany($company)->count();

        $base = DB::table('idp')
            ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
            ->join('employees',   'assessments.employee_id', '=', 'employees.id')
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
     * Modul generik per-employee (HAV/ICP/RTC).
     * Prioritas bucket: Approved > Revised > Progress.
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

        // RTC → list struktur + PIC
        if ($module === 'rtc') {
            $rows = $this->listRtcStructures($company, $statusWant);
            return response()->json(['rows' => $rows]);
        }

        // === Helper scope employee (company + optional org filter) ===
        $empScope = Employee::query()->forCompany($company);
        if ($department) {
            $empScope->whereHas('departments', fn($q) => $q->where('department_id', $department));
        }
        if ($division) {
            $empScope->whereHas('departments.division', fn($q) => $q->where('divisions_id', $division));
        }
        $empIds = $empScope->pluck('id');

        // === Tanggal untuk filter month ===
        [$mStart, $mEnd] = [null, null];
        if ($month) {
            try {
                $mStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $mEnd   = (clone $mStart)->endOfMonth();
            } catch (\Throwable $e) {
                $mStart = $mEnd = null;
            }
        }

        // === dispatcher per modul employee ===
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

    /**
     * ==== RTC: list struktur per status + PIC ====
     * Bucket prioritas per area: Approved(2) > Revised(-1) > Progress(0/1) > Not
     */
    private function listRtcStructures(?string $company, string $statusWant): array
    {
        // Ambil struktur dalam company + eager loading PIC (GM/Director)
        $divisions = Division::with(['plant.director', 'gm'])
            ->when($company, fn($q) => $q->where('company', $company))
            ->get();

        $departments = Department::with(['division.gm'])
            ->whereIn('division_id', $divisions->pluck('id'))
            ->get();

        $sections = Section::with(['department.division.gm'])
            ->whereIn('department_id', $departments->pluck('id'))
            ->get();

        $subs = SubSection::with(['section.department.division.gm'])
            ->whereIn('section_id', $sections->pluck('id'))
            ->get();

        // Ambil semua RTC yg terkait id-id di atas (sekali query)
        $rtcRows = Rtc::query()
            ->where(function ($q) use ($divisions, $departments, $sections, $subs) {
                if ($divisions->isNotEmpty()) {
                    $q->orWhere(function ($qq) use ($divisions) {
                        $qq->where('area', 'division')->whereIn('area_id', $divisions->pluck('id'));
                    });
                }
                if ($departments->isNotEmpty()) {
                    $q->orWhere(function ($qq) use ($departments) {
                        $qq->where('area', 'department')->whereIn('area_id', $departments->pluck('id'));
                    });
                }
                if ($sections->isNotEmpty()) {
                    $q->orWhere(function ($qq) use ($sections) {
                        $qq->where('area', 'section')->whereIn('area_id', $sections->pluck('id'));
                    });
                }
                if ($subs->isNotEmpty()) {
                    $q->orWhere(function ($qq) use ($subs) {
                        $qq->where('area', 'sub_section')->whereIn('area_id', $subs->pluck('id'));
                    });
                }
            })
            ->get()
            ->groupBy(fn($r) => $r->area . '|' . $r->area_id); // group per area

        // Helper hitung bucket dari kumpulan status
        $bucketOf = function ($statuses) {
            $hasApproved = in_array(2, $statuses, true);
            $hasRevised  = in_array(-1, $statuses, true);
            $hasProgress = (in_array(0, $statuses, true) || in_array(1, $statuses, true));
            if ($hasApproved) return 'approved';
            if ($hasRevised)  return 'revised';
            if ($hasProgress) return 'progress';
            return 'not';
        };

        $rows = [];

        // Division
        foreach ($divisions as $d) {
            $key = 'division|' . $d->id;
            $sts = ($rtcRows[$key] ?? collect())->pluck('status')->all();
            $bucket = $bucketOf($sts);
            if ($bucket === $statusWant) {
                $pic = $this->rtcPicFor('division', $d);
                $rows[] = [
                    'label' => 'Division - ' . $d->name,
                    'pic'   => $pic ? $this->shortName($pic->name) : '-',
                ];
            }
        }
        // Department
        foreach ($departments as $dep) {
            $key = 'department|' . $dep->id;
            $sts = ($rtcRows[$key] ?? collect())->pluck('status')->all();
            $bucket = $bucketOf($sts);
            if ($bucket === $statusWant) {
                $pic = $this->rtcPicFor('department', $dep);
                $rows[] = [
                    'label' => 'Department - ' . $dep->name,
                    'pic'   => $pic ? $this->shortName($pic->name) : '-',
                ];
            }
        }
        // Section
        foreach ($sections as $sec) {
            $key = 'section|' . $sec->id;
            $sts = ($rtcRows[$key] ?? collect())->pluck('status')->all();
            $bucket = $bucketOf($sts);
            if ($bucket === $statusWant) {
                $pic = $this->rtcPicFor('section', $sec);
                $rows[] = [
                    'label' => 'Section - ' . $sec->name,
                    'pic'   => $pic ? $this->shortName($pic->name) : '-',
                ];
            }
        }
        // Sub Section
        foreach ($subs as $sub) {
            $key = 'sub_section|' . $sub->id;
            $sts = ($rtcRows[$key] ?? collect())->pluck('status')->all();
            $bucket = $bucketOf($sts);
            if ($bucket === $statusWant) {
                $pic = $this->rtcPicFor('sub_section', $sub);
                $rows[] = [
                    'label' => 'Sub Section - ' . $sub->name,
                    'pic'   => $pic ? $this->shortName($pic->name) : '-',
                ];
            }
        }

        // Not Created (struktur tanpa satupun RTC)
        if ($statusWant === 'not') {
            // sudah tercakup oleh loop di atas via bucketOf() yang mengembalikan 'not' ketika $sts kosong
            // jadi tidak perlu tambahan khusus
        }

        // Urutkan alfabetis
        usort($rows, fn($a, $b) => strcasecmp($a['label'], $b['label']));
        return $rows;
    }

    /** PIC RTC:
     *  - sub_section / section / department → GM (division)
     *  - division → Direktur (plant)
     */
    private function rtcPicFor(string $area, $model): ?Employee
    {
        return match ($area) {
            'sub_section' => $model?->section?->department?->division?->gm,
            'section'     => $model?->department?->division?->gm,
            'department'  => $model?->division?->gm,
            'division'    => $model?->plant?->director,
            default       => null,
        };
    }

    /** Potong nama jadi maks 2 kata */
    private function shortName(?string $name): string
    {
        if (!$name) return '-';
        $parts = preg_split('/\s+/', trim($name));
        return implode(' ', array_slice($parts, 0, 2));
    }

    private function resolvePicForEmployee(Employee $emp): ?Employee
    {
        $sup = $emp->getSuperiorsByLevel(1)->first();
        if ($sup) return $sup;

        if ($emp->department?->manager_id) {
            $mgr = Employee::find($emp->department->manager_id);
            if ($mgr) return $mgr;
        }
        if ($emp->division?->gm_id) {
            $gm = Employee::find($emp->division->gm_id);
            if ($gm) return $gm;
        }
        if ($emp->plant?->director_id) {
            $dir = Employee::find($emp->plant->director_id);
            if ($dir) return $dir;
        }
        return null;
    }

    /**
     * Modul HAV/ICP: list per karyawan (kolom employee)
     */
    private function listSimple($empModel, $modelClass, string $statusCol, $empIds, string $statusWant, $start, $end)
    {
        $approved = [3, 4];
        $revised  = [-1];

        $q = $modelClass::query()->whereIn('employee_id', $empIds);
        if ($start && $end) $q->whereBetween('updated_at', [$start, $end]);

        if ($statusWant === 'approved')      $q->whereIn($statusCol, $approved);
        elseif ($statusWant === 'revised')   $q->whereIn($statusCol, $revised);
        elseif ($statusWant === 'progress')  $q->whereNotIn($statusCol, array_merge($approved, $revised));
        else {
            // NOT CREATED → karyawan tanpa record
            $has = $modelClass::query()->whereIn('employee_id', $empIds)->pluck('employee_id')->unique();
            $noRecordIds = collect($empIds)->diff($has)->values();

            return Employee::whereIn('id', $noRecordIds)
                ->orderBy('name')
                ->get()
                ->map(function ($e) {
                    $pic = $this->resolvePicForEmployee($e);
                    return [
                        'employee' => $this->shortName($e->name),
                        'pic'      => $pic ? $this->shortName($pic->name) : '-',
                    ];
                })
                ->values();
        }

        $matchedEmpIds = $q->pluck('employee_id')->unique()->values();

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')
            ->get()
            ->map(function ($e) {
                $pic = $this->resolvePicForEmployee($e);
                return [
                    'employee' => $this->shortName($e->name),
                    'pic'      => $pic ? $this->shortName($pic->name) : '-',
                ];
            })
            ->values();
    }

    /**
     * IDP: list per karyawan (kolom employee) dgn aturan manager/non-manager
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
                ->map(function ($e) {
                    $pic = $this->resolvePicForEmployee($e);
                    return [
                        'employee' => $this->shortName($e->name),
                        'pic'      => $pic ? $this->shortName($pic->name) : '-',
                    ];
                })
                ->values();
        }

        $matchedEmpIds = DB::query()->fromSub($q, 't')
            ->distinct()->pluck('emp_id');

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')
            ->get()
            ->map(function ($e) {
                $pic = $this->resolvePicForEmployee($e);
                return [
                    'employee' => $this->shortName($e->name),
                    'pic'      => $pic ? $this->shortName($pic->name) : '-',
                ];
            })
            ->values();
    }
}
