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
use Illuminate\Support\Facades\Log;

class DashboardController
{
    public function index()
    {
        return view('website.dashboard.index');
    }

    public function summary(Request $request)
    {
        $company = $request->query('company');

        // === batasan akses: HRD/VPD/President => all; selain itu => bawahan
        $empScope = $this->visibleEmployeeScope($company);
        $empIds   = $empScope->pluck('id')->values();

        // modul per-employee (pakai latest-per-employee)
        $idp = $this->idpPerEmployeeBucketsByEmpIds($empIds);
        $hav = $this->modulePerEmployeeBucketsByEmpIds((new Hav)->getTable(), 'status', $empIds, [
            'approved' => [3],
            'revised'  => [-1],
            'progress' => [1, 2],
        ]);
        $icp = $this->modulePerEmployeeBucketsByEmpIds((new Icp)->getTable(), 'status', $empIds, [
            'approved' => [3],
            'revised'  => [-1],
            'progress' => [1, 2],
        ]);

        // RTC tetap agregat struktur (division/department/section/sub_section)
        $rtc = $this->moduleRtcBucketsByStructure($company);

        // ALL gabungan modul, per-employee, latest-per-employee
        $all = $this->allPerEmployeeBucketsByEmpIds($empIds);

        return response()->json(compact('idp', 'hav', 'icp', 'rtc', 'all'));
    }

    /* ============================== IDP/HAV/ICP LIST (TIDAK DIUBAH KONSEP) ============================== */

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

        $empScope = $this->visibleEmployeeScope($company); // <<< penting: kirim $company
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

    private function listSimple($empModel, $modelClass, string $statusCol, $empIds, string $statusWant, $start, $end)
    {
        $approved = [3, 4];
        $revised  = [-1];

        // latest per employee
        $latestIds = $modelClass::query()
            ->whereIn('employee_id', $empIds)
            ->selectRaw('MAX(id) AS id')
            ->groupBy('employee_id')
            ->pluck('id');

        if ($statusWant === 'not') {
            $has = $modelClass::query()->whereIn('employee_id', $empIds)->distinct()->pluck('employee_id');
            $no  = collect($empIds)->diff($has)->values();

            return Employee::whereIn('id', $no)->orderBy('name')->get()
                ->map(fn($e) => [
                    'employee' => $e->name,
                    'pic'      => $this->short2(optional($e->getSuperiorsByLevel(1)->first())->name ?? '-'),
                ])->values();
        }

        if ($latestIds->isEmpty()) return collect();

        $q = $modelClass::query()->whereIn('id', $latestIds);
        if ($start && $end) $q->whereBetween('updated_at', [$start, $end]);

        if ($statusWant === 'approved')      $q->whereIn($statusCol, $approved);
        elseif ($statusWant === 'revised')   $q->whereIn($statusCol, $revised);
        elseif ($statusWant === 'progress')  $q->whereNotIn($statusCol, array_merge($approved, $revised));

        $matchedEmpIds = $q->pluck('employee_id')->unique()->values();

        return Employee::whereIn('id', $matchedEmpIds)->orderBy('name')->get()
            ->map(function ($e) {
                $pic = optional($e->getSuperiorsByLevel(1)->first())->name ?? '-';
                return ['employee' => $e->name, 'pic' => $this->short2($pic)];
            })->values();
    }

    private function listIdp($empIds, string $statusWant, $start, $end, ?string $company)
    {
        // latest IDP per employee
        $latest = DB::table('idp as i')
            ->join('assessments as a', 'i.assessment_id', '=', 'a.id')
            ->whereIn('a.employee_id', $empIds)
            ->selectRaw('a.employee_id AS emp_id, MAX(i.id) AS last_idp_id')
            ->groupBy('a.employee_id');

        $base = DB::table('idp as i2')
            ->joinSub($latest, 't', fn($j) => $j->on('i2.id', '=', 't.last_idp_id'))
            ->join('employees as e', 'e.id', '=', 't.emp_id')
            ->select('t.emp_id', 'i2.status', 'e.position');

        if ($start && $end) $base->whereBetween('i2.updated_at', [$start, $end]);

        if ($statusWant === 'approved') {
            $base->where(function ($w) {
                $w->whereRaw("LOWER(e.position) LIKE '%manager%' AND i2.status IN (4)")
                    ->orWhereRaw("LOWER(e.position) NOT LIKE '%manager%' AND i2.status IN (3,4)");
            });
        } elseif ($statusWant === 'revised') {
            $base->where('i2.status', -1);
        } elseif ($statusWant === 'progress') {
            $base->where(function ($w) {
                $w->whereRaw("LOWER(e.position) LIKE '%manager%' AND i2.status IN (1,2,3)")
                    ->orWhereRaw("LOWER(e.position) NOT LIKE '%manager%' AND i2.status IN (1,2)");
            });
        } else { // 'not'
            $has = DB::table('idp as i')
                ->join('assessments as a', 'i.assessment_id', '=', 'a.id')
                ->whereIn('a.employee_id', $empIds)
                ->distinct()->pluck('a.employee_id');

            $no = collect($empIds)->diff($has)->values();

            return Employee::whereIn('id', $no)->orderBy('name')->get()
                ->map(fn($e) => [
                    'employee' => $e->name,
                    'pic'      => $this->short2(optional($e->getSuperiorsByLevel(1)->first())->name ?? '-'),
                ])->values();
        }

        $matched = $base->pluck('emp_id')->unique()->values();

        return Employee::whereIn('id', $matched)->orderBy('name')->get()
            ->map(function ($e) {
                $pic = optional($e->getSuperiorsByLevel(1)->first())->name ?? '-';
                return ['employee' => $e->name, 'pic' => $this->short2($pic)];
            })->values();
    }

    /* ============================== SUMMARY HELPERS (BARU / DISESUAIKAN) ============================== */

    private function idpPerEmployeeBucketsByEmpIds($empIds): array
    {
        $scope = collect($empIds)->count();

        if ($scope === 0) {
            return ['scope' => 0, 'approved' => 0, 'progress' => 0, 'revised' => 0, 'not' => 0];
        }

        // last IDP per employee
        $latest = DB::table('idp as i')
            ->join('assessments as a', 'i.assessment_id', '=', 'a.id')
            ->whereIn('a.employee_id', $empIds)
            ->selectRaw('a.employee_id AS emp_id, MAX(i.id) AS last_idp_id')
            ->groupBy('a.employee_id');

        $rows = DB::table('idp as i2')
            ->joinSub($latest, 't', fn($j) => $j->on('i2.id', '=', 't.last_idp_id'))
            ->join('employees as e', 'e.id', '=', 't.emp_id')
            ->select('t.emp_id', 'i2.status', 'e.position')
            ->get();

        $hasCount = $rows->count();
        $approved = 0;
        $revised = 0;
        $progress = 0;

        foreach ($rows as $r) {
            $isMgr = str_contains(strtolower($r->position), 'manager');

            if (($isMgr && in_array((int)$r->status, [4], true))
                || (!$isMgr && in_array((int)$r->status, [3, 4], true))
            ) {
                $approved++;
            } elseif ((int)$r->status === -1) {
                $revised++;
            } else {
                $progress++; // ada data tapi belum memenuhi approved/revised
            }
        }

        $not = max($scope - $hasCount, 0);

        return compact('scope', 'approved', 'progress', 'revised', 'not');
    }

    private function modulePerEmployeeBucketsByEmpIds(string $table, string $statusCol, $empIds, array $map, bool $joinViaAssessment = false): array
    {
        $scope = collect($empIds)->count();

        if ($scope === 0) {
            return ['scope' => 0, 'approved' => 0, 'progress' => 0, 'revised' => 0, 'not' => 0];
        }

        if ($joinViaAssessment) {
            // tidak dipakai untuk HAV/ICP; siapkan kalau dibutuhkan modul lain
            $latest = DB::table("$table as t")
                ->join('assessments as a', 't.assessment_id', '=', 'a.id')
                ->whereIn('a.employee_id', $empIds)
                ->selectRaw('a.employee_id AS emp_id, MAX(t.id) AS last_id')
                ->groupBy('a.employee_id');

            $rows = DB::table("$table as x")
                ->joinSub($latest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
                ->select('l.emp_id as employee_id', "x.$statusCol as status")
                ->get();
        } else {
            $latest = DB::table("$table as t")
                ->whereIn('t.employee_id', $empIds)
                ->selectRaw('t.employee_id, MAX(t.id) AS last_id')
                ->groupBy('t.employee_id');

            $rows = DB::table("$table as x")
                ->joinSub($latest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
                ->select('x.employee_id', "x.$statusCol as status")
                ->get();
        }

        $hasCount = $rows->count();
        $approvedSet = array_map('intval', $map['approved'] ?? []);
        $revisedSet  = array_map('intval', $map['revised']  ?? []);
        $approved = 0;
        $revised = 0;
        $progress = 0;

        foreach ($rows as $r) {
            $s = (int)$r->status;
            if (in_array($s, $approvedSet, true))      $approved++;
            elseif (in_array($s, $revisedSet, true))   $revised++;
            else                                        $progress++; // ada data tapi belum memenuhi approved/revised
        }

        $not = max($scope - $hasCount, 0);

        return compact('scope', 'approved', 'progress', 'revised', 'not');
    }

    private function allPerEmployeeBucketsByEmpIds($empIds): array
    {
        $ids = collect($empIds)->values();
        $scope = $ids->count();
        if ($scope === 0) return ['scope' => 0, 'approved' => 0, 'progress' => 0, 'revised' => 0, 'not' => 0];

        // ==== IDP bucket per employee (latest)
        $latestIdp = DB::table('idp as i')
            ->join('assessments as a', 'i.assessment_id', '=', 'a.id')
            ->whereIn('a.employee_id', $ids)
            ->selectRaw('a.employee_id AS emp_id, MAX(i.id) AS last_id')
            ->groupBy('a.employee_id');

        $idpRows = DB::table('idp as x')
            ->joinSub($latestIdp, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->join('employees as e', 'e.id', '=', 'l.emp_id')
            ->select('l.emp_id as employee_id', 'x.status', 'e.position')
            ->get();

        $idpBucket = [];
        foreach ($idpRows as $r) {
            $isMgr = str_contains(strtolower($r->position), 'manager');
            $s = (int)$r->status;
            if (($isMgr && in_array($s, [4], true)) || (!$isMgr && in_array($s, [3, 4], true))) $idpBucket[$r->employee_id] = 'approved';
            elseif ($s === -1) $idpBucket[$r->employee_id] = 'revised';
            else $idpBucket[$r->employee_id] = 'progress';
        }

        // ==== HAV bucket (latest)
        $havMap = ['approved' => [3], 'revised' => [-1]];
        $havLatest = DB::table('havs as t')
            ->whereIn('t.employee_id', $ids)
            ->selectRaw('t.employee_id, MAX(t.id) AS last_id')
            ->groupBy('t.employee_id');

        $havRows = DB::table('havs as x')
            ->joinSub($havLatest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->select('x.employee_id', 'x.status')->get();

        $havBucket = [];
        foreach ($havRows as $r) {
            $s = (int)$r->status;
            if (in_array($s, $havMap['approved'], true)) $havBucket[$r->employee_id] = 'approved';
            elseif (in_array($s, $havMap['revised'], true)) $havBucket[$r->employee_id] = 'revised';
            else $havBucket[$r->employee_id] = 'progress';
        }

        // ==== ICP bucket (latest)
        $icpMap = ['approved' => [3], 'revised' => [-1]];
        $icpLatest = DB::table('icp as t')
            ->whereIn('t.employee_id', $ids)
            ->selectRaw('t.employee_id, MAX(t.id) AS last_id')
            ->groupBy('t.employee_id');

        $icpRows = DB::table('icp as x')
            ->joinSub($icpLatest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->select('x.employee_id', 'x.status')->get();

        $icpBucket = [];
        foreach ($icpRows as $r) {
            $s = (int)$r->status;
            if (in_array($s, $icpMap['approved'], true)) $icpBucket[$r->employee_id] = 'approved';
            elseif (in_array($s, $icpMap['revised'], true)) $icpBucket[$r->employee_id] = 'revised';
            else $icpBucket[$r->employee_id] = 'progress';
        }

        // ==== RTC per-employee (jika ada), latest
        // (RTC utama by-structure; tapi untuk ALL pakai fallback per-employee jika ada row employee_id)
        $rtcMap = ['approved' => [2], 'revised' => [-1]];
        $rtcLatest = DB::table('rtc as t')
            ->whereIn('t.employee_id', $ids)
            ->selectRaw('t.employee_id, MAX(t.id) AS last_id')
            ->groupBy('t.employee_id');

        $rtcRows = DB::table('rtc as x')
            ->joinSub($rtcLatest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->select('x.employee_id', 'x.status')->get();

        $rtcBucket = [];
        foreach ($rtcRows as $r) {
            $s = (int)$r->status;
            if (in_array($s, $rtcMap['approved'], true)) $rtcBucket[$r->employee_id] = 'approved';
            elseif (in_array($s, $rtcMap['revised'], true)) $rtcBucket[$r->employee_id] = 'revised';
            else $rtcBucket[$r->employee_id] = 'progress';
        }

        // ==== Gabungkan per employee
        $approved = 0;
        $revised = 0;
        $progress = 0;
        $not = 0;

        foreach ($ids as $eid) {
            $buckets = [
                $idpBucket[$eid] ?? null,
                $havBucket[$eid] ?? null,
                $icpBucket[$eid] ?? null,
                $rtcBucket[$eid] ?? null,
            ];

            if (in_array('approved', $buckets, true))      $approved++;
            elseif (in_array('revised', $buckets, true))   $revised++;
            elseif (in_array('progress', $buckets, true))  $progress++;
            else                                            $not++;
        }

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
            'scope'    => $total,
            'approved' => $approved,
            'progress' => $progress,
            'revised'  => $revised,
            'not'      => $not,
        ];
    }

    public function listRtcStructures(?string $company, string $statusWant): array
    {
        [$divs, $depts, $secs, $subs] = $this->allStructuresByCompany($company);

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
            $b = $bucketOf($area, $model->id);
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

    /* ============================== ACCESS HELPERS ============================== */

    /**
     * Query scope karyawan yang boleh dilihat user saat ini.
     * HRD / VPD / President => seluruh karyawan (sesuai filter company).
     * Lainnya => gabungan semua bawahan (semua level) dari employee yang login (via struktur).
     *
     * Return: Builder<Employee>
     */
    private function visibleEmployeeScope(?string $company)
    {
        $user = auth()->user();
        $emp  = $user->employee; // <-- BUKAN optional()

        $role = (string)($user->role ?? '');

        $posNorm = $emp && method_exists($emp, 'getNormalizedPosition')
            ? strtolower((string)$emp->getNormalizedPosition())
            : strtolower((string)($emp->position ?? ''));

        // base query selalu filter company
        $base = Employee::query()->forCompany($company);

        // HRD / VPD / President / tidak punya employee -> lihat semua
        if (($role === 'HRD') || in_array($posNorm, ['vpd', 'president'], true) || !$emp || !$emp->id) {
            return $base;
        }

        // Ambil bawahan via helper (Employee instance, bukan Optional)
        $ids = $this->getSubordinatesFromStructure($emp)->pluck('id')->unique()->values();

        Log::info($emp->name, ['bawahan' => $ids]);

        // Tidak ada bawahan -> kosong (bukan diri sendiri)
        if ($ids->isEmpty()) {
            return $base->whereRaw('1=0');
        }

        // Kembalikan Builder agar bisa di-whereHas, dst.
        return $base->whereIn('id', $ids);
    }


    private function getSubordinatesFromStructure(Employee $employee)
    {
        $subordinateIds = collect();

        if ($employee->leadingPlant && $employee->leadingPlant->director_id === $employee->id) {
            $divisions = Division::where('plant_id', $employee->leadingPlant->id)->get();
            $subordinateIds = $this->collectSubordinates($divisions, 'gm_id', $subordinateIds);

            $departments = Department::whereIn('division_id', $divisions->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($departments, 'manager_id', $subordinateIds);

            $sections = Section::whereIn('department_id', $departments->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingDivision && $employee->leadingDivision->gm_id === $employee->id) {
            $departments = Department::where('division_id', $employee->leadingDivision->id)->get();
            $subordinateIds = $this->collectSubordinates($departments, 'manager_id', $subordinateIds);

            $sections = Section::whereIn('department_id', $departments->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingDepartment && $employee->leadingDepartment->manager_id === $employee->id) {
            $sections = Section::where('department_id', $employee->leadingDepartment->id)->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingSection && $employee->leadingSection->supervisor_id === $employee->id) {
            $subSections = SubSection::where('section_id', $employee->leadingSection->id)->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->subSection && $employee->subSection->leader_id === $employee->id) {
            $employeesInSameSubSection = Employee::where('sub_section_id', $employee->sub_section_id)
                ->where('id', '!=', $employee->id)
                ->pluck('id');

            $subordinateIds = $subordinateIds->merge($employeesInSameSubSection);
        }

        if ($subordinateIds->isEmpty()) {
            return Employee::whereRaw('1=0'); // tidak ada bawahan
        }

        return Employee::whereIn('id', $subordinateIds);
    }

    private function collectSubordinates($models, $field, $subordinateIds)
    {
        $ids = $models->pluck($field)->filter();
        return $subordinateIds->merge($ids);
    }

    private function collectOperators($subSections, $subordinateIds)
    {
        $subSectionIds = $subSections->pluck('id');
        $operatorIds = Employee::whereIn('sub_section_id', $subSectionIds)->pluck('id');
        return $subordinateIds->merge($operatorIds);
    }

    /* ============================== UTIL ============================== */

    private function short2(?string $name): string
    {
        $name = trim((string)$name);
        if ($name === '') return '-';
        $parts = preg_split('/\s+/', $name);
        return implode(' ', array_slice($parts, 0, 2));
    }
}
