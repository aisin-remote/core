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
use Illuminate\Support\Str;

class DashboardController
{
    public function index()
    {
        return view('website.dashboard.index');
    }

    /* =============================================================================
     * SUMMARY (semua modul) — DEDUP BY NPK (orang unik) + DOMAIN-FIRST COMPANY
     * ============================================================================= */
    public function summary(Request $request)
    {
        $company = $request->query('company');
        $company = $company === '' ? null : $company;

        // scope akses (tanpa filter company dulu)
        $empScope = $this->visibleEmployeeScope();

        // assignment per NPK (domain-first) + optional filter company
        [$npks, $repEmpMap, $assignBreakdown] = $this->assignCompanyPerNpk($empScope, $company);

        Log::info('summary:scope', [
            'company_param' => $company,
            'npks_count'    => $npks->count(),
            'breakdown'     => $assignBreakdown,
        ]);

        // per modul (tetap)
        $idp = $this->idpBucketsByNpks($npks);
        $hav = $this->moduleBucketsByNpks((new Hav)->getTable(), 'status', $npks, [
            'approved' => [3],
            'revised'  => [-1],
            'progress' => [1, 2],
        ]);
        $icp = $this->moduleBucketsByNpks((new Icp)->getTable(), 'status', $npks, [
            'approved' => [3],
            'revised'  => [-1],
            'progress' => [1, 2],
        ]);
        $rtc = $this->moduleRtcBucketsByStructure($company);

        // TAB ALL sekarang cuma butuh total unique NPK
        $all = ['scope' => $npks->count()];

        return response()->json(compact('idp', 'hav', 'icp', 'rtc', 'all'));
    }

    /* =============================================================================
     * LIST (IDP/HAV/ICP — DEDUP BY NPK) & RTC (by-structure)
     * ============================================================================= */

    public function list(Request $req)
    {
        $module     = $req->query('module');
        $statusWant = $req->query('status');
        $company    = $req->query('company');
        $company    = $company === '' ? null : $company;
        $division   = $req->query('division');
        $department = $req->query('department');
        $month      = $req->query('month');

        Log::info('list:request', compact('module', 'statusWant', 'company', 'division', 'department', 'month'));

        if (!in_array($module, ['idp', 'hav', 'icp', 'rtc'], true)) return response()->json(['rows' => []]);
        if (!in_array($statusWant, ['approved', 'progress', 'revised', 'not'], true)) return response()->json(['rows' => []]);

        if ($module === 'rtc') {
            $rows = $this->listRtcStructures($company, $statusWant);
            Log::info('list:rtc', ['rows_count' => count($rows)]);
            return response()->json(['rows' => $rows]);
        }

        // Scope akses (tanpa filter company dulu)
        $empScope = $this->visibleEmployeeScope();

        if ($department) {
            $empScope->whereHas('departments', fn($q) => $q->where('department_id', $department));
        }
        if ($division) {
            $empScope->whereHas('departments.division', fn($q) => $q->where('divisions_id', $division));
        }

        // Assignment per NPK (sesuai company filter)
        [$npks, $repEmpMap] = $this->assignCompanyPerNpk($empScope, $company);

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
                $rows = $this->listIdpByNpk($npks, $repEmpMap, $statusWant, $mStart, $mEnd);
                break;
            case 'hav':
                $rows = $this->listSimpleByNpk((new Hav)->getTable(), 'status', $npks, $repEmpMap, $statusWant, $mStart, $mEnd, [
                    'approved' => [3],
                    'revised'  => [-1],
                ]);
                break;
            case 'icp':
                $rows = $this->listSimpleByNpk((new Icp)->getTable(), 'status', $npks, $repEmpMap, $statusWant, $mStart, $mEnd, [
                    'approved' => [3],
                    'revised'  => [-1],
                ]);
                break;
            default:
                $rows = collect();
        }

        Log::info("list:$module", ['status' => $statusWant, 'result_count' => count($rows ?? [])]);
        return response()->json(['rows' => $rows ?? []]);
    }

    /* =============================================================================
     * LIST HELPERS (per NPK)
     * ============================================================================= */

    private function listSimpleByNpk(string $table, string $statusCol, $npks, $repEmpMap, string $statusWant, $start, $end, array $map)
    {
        $npks = collect($npks)->filter()->values();
        if ($npks->isEmpty()) return collect();

        $expr = $this->npkNormExpr('e');

        // Ambil latest row per NPK (by normalized NPK)
        $latest = DB::table("$table as t")
            ->join('employees as e', 'e.id', '=', 't.employee_id')
            ->whereIn(DB::raw($expr), $npks)
            ->when($start && $end, fn($q) => $q->whereBetween('t.updated_at', [$start, $end]))
            ->selectRaw("$expr AS nk, MAX(t.id) AS last_id")
            ->groupBy('nk');

        $rows = DB::table("$table as x")
            ->joinSub($latest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->select('l.nk as npk_norm', "x.$statusCol as status")
            ->get();

        $approvedSet = array_map('intval', $map['approved'] ?? []);
        $revisedSet  = array_map('intval', $map['revised']  ?? []);

        $keepNk = collect();

        if ($statusWant === 'not') {
            $have = $rows->pluck('npk_norm')->unique();
            $keepNk = $npks->diff($have)->values();
        } else {
            foreach ($rows as $r) {
                $s = (int)$r->status;
                if ($statusWant === 'approved' && in_array($s, $approvedSet, true)) $keepNk->push($r->npk_norm);
                elseif ($statusWant === 'revised' && in_array($s, $revisedSet, true)) $keepNk->push($r->npk_norm);
                elseif ($statusWant === 'progress' && !in_array($s, array_merge($approvedSet, $revisedSet), true)) $keepNk->push($r->npk_norm);
            }
        }

        $keepNk = $keepNk->unique()->values();
        if ($keepNk->isEmpty()) return collect();

        // Ambil Employee representative utk render nama & PIC
        $repIds = $keepNk->map(fn($nk) => $repEmpMap[$nk] ?? null)->filter()->values();
        $employees = Employee::whereIn('id', $repIds)->orderBy('name')->get();

        return $employees->map(function ($e) {
            $pic = optional($e->getSuperiorsByLevel(1)->first())->name ?? '-';
            return ['employee' => $e->name, 'pic' => $this->short2($pic)];
        })->values();
    }

    private function listIdpByNpk($npks, $repEmpMap, string $statusWant, $start, $end)
    {
        $npks = collect($npks)->filter()->values();
        if ($npks->isEmpty()) return collect();

        $expr = $this->npkNormExpr('e');

        // latest IDP per normalized NPK
        $latest = DB::table('idp as i')
            ->join('assessments as a', 'i.assessment_id', '=', 'a.id')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->whereIn(DB::raw($expr), $npks)
            ->when($start && $end, fn($q) => $q->whereBetween('i.updated_at', [$start, $end]))
            ->selectRaw("$expr AS nk, MAX(i.id) AS last_id")
            ->groupBy('nk');

        $rows = DB::table('idp as x')
            ->joinSub($latest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->join('assessments as a', 'x.assessment_id', '=', 'a.id')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->select('l.nk as npk_norm', 'x.status', 'e.position')
            ->get();

        $keepNk = collect();

        if ($statusWant === 'not') {
            $have = $rows->pluck('npk_norm')->unique();
            $keepNk = $npks->diff($have)->values();
        } else {
            foreach ($rows as $r) {
                $isMgr = str_contains(strtolower($r->position), 'manager');
                $s = (int)$r->status;

                $isApproved = ($isMgr && in_array($s, [4], true)) || (!$isMgr && in_array($s, [3, 4], true));
                $isRevised  = ($s === -1);
                $isProgress = !$isApproved && !$isRevised;

                if ($statusWant === 'approved' && $isApproved)   $keepNk->push($r->npk_norm);
                if ($statusWant === 'revised'  && $isRevised)    $keepNk->push($r->npk_norm);
                if ($statusWant === 'progress' && $isProgress)   $keepNk->push($r->npk_norm);
            }
        }

        $keepNk = $keepNk->unique()->values();
        if ($keepNk->isEmpty()) return collect();

        // Ambil Employee representative utk render nama & PIC
        $repIds = $keepNk->map(fn($nk) => $repEmpMap[$nk] ?? null)->filter()->values();
        $employees = Employee::whereIn('id', $repIds)->orderBy('name')->get();

        return $employees->map(function ($e) {
            $pic = optional($e->getSuperiorsByLevel(1)->first())->name ?? '-';
            return ['employee' => $e->name, 'pic' => $this->short2($pic)];
        })->values();
    }

    /* =============================================================================
     * SUMMARY HELPERS (per NPK)
     * ============================================================================= */

    private function idpBucketsByNpks($npks): array
    {
        $npks = collect($npks)->filter()->values();
        $scope = $npks->count();
        if ($scope === 0) {
            $res = ['scope' => 0, 'approved' => 0, 'progress' => 0, 'revised' => 0, 'not' => 0];
            Log::info('summary:idp', $res);
            return $res;
        }

        $expr = $this->npkNormExpr('e');

        // IDP terakhir per normalized NPK
        $latest = DB::table('idp as i')
            ->join('assessments as a', 'i.assessment_id', '=', 'a.id')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->whereIn(DB::raw($expr), $npks)
            ->selectRaw("$expr AS nk, MAX(i.id) AS last_id")
            ->groupBy('nk');

        $rows = DB::table('idp as x')
            ->joinSub($latest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->join('assessments as a', 'x.assessment_id', '=', 'a.id')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->select('l.nk as npk_norm', 'x.status', 'e.position')
            ->get();

        $hasCount = $rows->count();
        $approved = 0;
        $revised  = 0;
        $progress = 0;

        foreach ($rows as $r) {
            $isMgr = str_contains(strtolower($r->position), 'manager');
            $s = (int)$r->status;
            if (($isMgr && in_array($s, [4], true)) || (!$isMgr && in_array($s, [3, 4], true))) {
                $approved++;
            } elseif ($s === -1) {
                $revised++;
            } else {
                $progress++;
            }
        }

        $not = max($scope - $hasCount, 0);
        $res = compact('scope', 'approved', 'progress', 'revised', 'not');
        Log::info('summary:idp', $res);
        return $res;
    }

    private function moduleBucketsByNpks(string $table, string $statusCol, $npks, array $map): array
    {
        $npks = collect($npks)->filter()->values();
        $scope = $npks->count();
        if ($scope === 0) {
            $res = ['scope' => 0, 'approved' => 0, 'progress' => 0, 'revised' => 0, 'not' => 0];
            Log::info("summary:$table", $res);
            return $res;
        }

        $expr = $this->npkNormExpr('e');

        // latest per normalized NPK
        $latest = DB::table("$table as t")
            ->join('employees as e', 'e.id', '=', 't.employee_id')
            ->whereIn(DB::raw($expr), $npks)
            ->selectRaw("$expr AS nk, MAX(t.id) AS last_id")
            ->groupBy('nk');

        $rows = DB::table("$table as x")
            ->joinSub($latest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->select('l.nk as npk_norm', "x.$statusCol as status")
            ->get();

        $hasCount   = $rows->count();
        $approvedSet = array_map('intval', $map['approved'] ?? []);
        $revisedSet  = array_map('intval', $map['revised']  ?? []);
        $approved = 0;
        $revised  = 0;
        $progress = 0;

        foreach ($rows as $r) {
            $s = (int)$r->status;
            if (in_array($s, $approvedSet, true))      $approved++;
            elseif (in_array($s, $revisedSet, true))   $revised++;
            else                                       $progress++;
        }

        $not = max($scope - $hasCount, 0);
        $res = compact('scope', 'approved', 'progress', 'revised', 'not');
        Log::info("summary:$table", $res);
        return $res;
    }

    private function allBucketsByNpks($npks): array
    {
        $npks = collect($npks)->filter()->values();
        $scope = $npks->count();
        if ($scope === 0) {
            $res = ['scope' => 0, 'approved' => 0, 'progress' => 0, 'revised' => 0, 'not' => 0];
            Log::info('summary:all', $res);
            return $res;
        }

        $expr = $this->npkNormExpr('e');

        // ==== IDP latest per NPK
        $latestIdp = DB::table('idp as i')
            ->join('assessments as a', 'i.assessment_id', '=', 'a.id')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->whereIn(DB::raw($expr), $npks)
            ->selectRaw("$expr AS nk, MAX(i.id) AS last_id")
            ->groupBy('nk');

        $idpRows = DB::table('idp as x')
            ->joinSub($latestIdp, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->join('assessments as a', 'x.assessment_id', '=', 'a.id')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->select('l.nk as npk_norm', 'x.status', 'e.position')->get();

        $idpBucket = [];
        foreach ($idpRows as $r) {
            $isMgr = str_contains(strtolower($r->position), 'manager');
            $s = (int)$r->status;
            if (($isMgr && in_array($s, [4], true)) || (!$isMgr && in_array($s, [3, 4], true))) $idpBucket[$r->npk_norm] = 'approved';
            elseif ($s === -1) $idpBucket[$r->npk_norm] = 'revised';
            else $idpBucket[$r->npk_norm] = 'progress';
        }

        // ==== HAV latest per NPK
        $havLatest = DB::table('havs as t')
            ->join('employees as e', 'e.id', '=', 't.employee_id')
            ->whereIn(DB::raw($expr), $npks)
            ->selectRaw("$expr AS nk, MAX(t.id) AS last_id")
            ->groupBy('nk');

        $havRows = DB::table('havs as x')
            ->joinSub($havLatest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->select('l.nk as npk_norm', 'x.status')->get();

        $havBucket = [];
        foreach ($havRows as $r) {
            $s = (int)$r->status;
            if ($s === 3) $havBucket[$r->npk_norm] = 'approved';
            elseif ($s === -1) $havBucket[$r->npk_norm] = 'revised';
            else $havBucket[$r->npk_norm] = 'progress';
        }

        // ==== ICP latest per NPK
        $icpLatest = DB::table('icp as t')
            ->join('employees as e', 'e.id', '=', 't.employee_id')
            ->whereIn(DB::raw($expr), $npks)
            ->selectRaw("$expr AS nk, MAX(t.id) AS last_id")
            ->groupBy('nk');

        $icpRows = DB::table('icp as x')
            ->joinSub($icpLatest, 'l', fn($j) => $j->on('x.id', '=', 'l.last_id'))
            ->select('l.nk as npk_norm', 'x.status')->get();

        $icpBucket = [];
        foreach ($icpRows as $r) {
            $s = (int)$r->status;
            if ($s === 3) $icpBucket[$r->npk_norm] = 'approved';
            elseif ($s === -1) $icpBucket[$r->npk_norm] = 'revised';
            else $icpBucket[$r->npk_norm] = 'progress';
        }

        // ==== Gabungkan per NPK
        $approved = 0;
        $revised  = 0;
        $progress = 0;
        $not      = 0;

        foreach ($npks as $nk) {
            $buckets = [
                $idpBucket[$nk] ?? null,
                $havBucket[$nk] ?? null,
                $icpBucket[$nk] ?? null,
            ];

            if (in_array('approved', $buckets, true))      $approved++;
            elseif (in_array('revised', $buckets, true))   $revised++;
            elseif (in_array('progress', $buckets, true))  $progress++;
            else                                            $not++;
        }

        $res = compact('scope', 'approved', 'progress', 'revised', 'not');
        Log::info('summary:all', $res);
        return $res;
    }

    /* =============================================================================
     * RTC AGG & LIST (by-structure) — tetap sama
     * ============================================================================= */

    private function moduleRtcBucketsByStructure(?string $company): array
    {
        [$divs, $depts, $secs, $subs] = $this->allStructuresByCompany($company);
        $total = $divs->count() + $depts->count() + $secs->count() + $subs->count();

        $approved = 0;
        $revised  = 0;
        $progress = 0;
        $hasAny   = 0;

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

    /* =============================================================================
     * ACCESS SCOPE & ASSIGNMENT PER NPK
     * ============================================================================= */

    /**
     * Scope karyawan yang boleh dilihat user saat ini.
     * HRD / VPD / President => seluruh karyawan.
     * Lainnya => semua bawahan (via struktur) dari employee login.
     *
     * NOTE: TIDAK mem-filter company di sini. Filter company dilakukan
     * pada level assignment per-NPK (domain-first).
     */
    private function visibleEmployeeScope()
    {
        $user = auth()->user();
        $emp  = $user->employee; // Employee instance (bisa null)
        $role = (string)($user->role ?? '');

        $posNorm = $emp && method_exists($emp, 'getNormalizedPosition')
            ? strtolower((string)$emp->getNormalizedPosition())
            : strtolower((string)($emp->position ?? ''));

        // HRD / VPD / President / tidak punya employee -> lihat semua
        if (($role === 'HRD') || in_array($posNorm, ['vpd', 'president'], true) || !$emp || !$emp->id) {
            Log::info('visibleEmployeeScope:ALL');
            return Employee::query();
        }

        // Ambil bawahan via struktur, lalu batasi ke set itu
        $ids = $this->getSubordinatesFromStructure($emp)->pluck('id')->unique()->values();

        Log::info('visibleEmployeeScope:subordinates', [
            'owner'       => $emp->name ?? '-',
            'ids_count'   => $ids->count(),
            'ids_preview' => $ids->take(50),
        ]);

        if ($ids->isEmpty()) {
            return Employee::query()->whereRaw('1=0'); // kosong
        }

        return Employee::query()->whereIn('employees.id', $ids);
    }

    /**
     * Assignment company per NPK berbasis domain email user (AII/AIIA).
     * - Normalisasi NPK agar unik (hapus spasi, -, ., / dan lower).
     * - Domain-first: pakai domain email; jika ada dua domain, pilih yang count email terbanyak; tie → majority company_name.
     * - Jika tidak ada email → pakai majority employees.company_name (AII/AIIA). Tie/kosong → Unassigned.
     * - Representative employee_id dipilih deterministik:
     *     * Prioritas baris yang match assigned company, prefer yg ada email domain → MIN(users.id); fallback MIN(employees.id).
     * - Jika $company != null → hanya include NPK yang ter-assign ke company tsb.
     *
     * Return:
     *  - Collection $npks (npk_norm list)
     *  - array $repEmpMap: npk_norm => representative employees.id
     *  - array $breakdown: ['AII'=>x, 'AIIA'=>y, 'Unassigned'=>z]
     */
    private function assignCompanyPerNpk($empScope, ?string $company = null): array
    {
        $expr = $this->npkNormExpr('employees');

        // Ambil data dasar
        $rows = (clone $empScope)
            ->leftJoin('users', 'users.id', '=', 'employees.user_id')
            ->whereNotNull('employees.npk')
            ->whereRaw("TRIM(employees.npk) <> ''")
            ->get([
                DB::raw("$expr AS npk_norm"),
                'employees.id as employee_id',
                'employees.company_name as emp_company',
                'users.id as uid',
                'users.email as email',
            ]);

        // Group per NPK normalisasi
        $grouped = $rows->groupBy('npk_norm');

        $repEmpMap   = [];
        $npks        = collect();
        $breakdown   = ['AII' => 0, 'AIIA' => 0, 'Unassigned' => 0];

        foreach ($grouped as $nk => $items) {
            // Hitung domain & company majority
            $domainCnt = ['AII' => 0, 'AIIA' => 0];
            $compCnt   = ['AII' => 0, 'AIIA' => 0];

            foreach ($items as $r) {
                $email = strtolower((string)($r->email ?? ''));
                if (Str::endsWith($email, '@aisin-indonesia.co.id')) $domainCnt['AII']++;
                elseif (Str::endsWith($email, '@aiia.co.id'))        $domainCnt['AIIA']++;

                $empCompany = strtoupper(trim((string)($r->emp_company ?? '')));
                if ($empCompany === 'AII')  $compCnt['AII']++;
                if ($empCompany === 'AIIA') $compCnt['AIIA']++;
            }

            // Tentukan assigned company (domain-first)
            $assigned = null;
            if (($domainCnt['AII'] + $domainCnt['AIIA']) > 0) {
                if ($domainCnt['AII'] > $domainCnt['AIIA'])      $assigned = 'AII';
                elseif ($domainCnt['AIIA'] > $domainCnt['AII'])  $assigned = 'AIIA';
                else {
                    // tie by email → majority company_name
                    if ($compCnt['AII']  > $compCnt['AIIA']) $assigned = 'AII';
                    elseif ($compCnt['AIIA'] > $compCnt['AII'])  $assigned = 'AIIA';
                    else $assigned = null; // tetap unassigned
                }
            } else {
                // tidak ada email → majority company_name
                if ($compCnt['AII']  > $compCnt['AIIA']) $assigned = 'AII';
                elseif ($compCnt['AIIA'] > $compCnt['AII'])  $assigned = 'AIIA';
                else $assigned = null; // unassigned
            }

            // Representative employee (deterministik)
            $repId = null;
            if ($assigned) {
                // Prefer baris dengan email domain yang match assigned
                $withDomain = $items->filter(function ($r) use ($assigned) {
                    $email = strtolower((string)($r->email ?? ''));
                    return ($assigned === 'AII'  && Str::endsWith($email, '@aisin-indonesia.co.id'))
                        || ($assigned === 'AIIA' && Str::endsWith($email, '@aiia.co.id'));
                });
                if ($withDomain->isNotEmpty()) {
                    $repId = $withDomain->sortBy('uid')->first()->employee_id;
                } else {
                    // Fallback: baris dengan emp_company = assigned
                    $withComp = $items->filter(function ($r) use ($assigned) {
                        $ec = strtoupper(trim((string)($r->emp_company ?? '')));
                        return $ec === $assigned;
                    });
                    if ($withComp->isNotEmpty()) {
                        $repId = $withComp->sortBy('employee_id')->first()->employee_id;
                    } else {
                        // Benar-benar tidak ada—pakai MIN employees.id
                        $repId = $items->sortBy('employee_id')->first()->employee_id;
                    }
                }
            } else {
                // Unassigned: tetap pilih rep agar bisa render list “not created”
                $repId = $items->sortBy('employee_id')->first()->employee_id;
            }

            // Filter company jika diminta
            if ($company && $assigned !== $company) {
                continue;
            }

            $repEmpMap[$nk] = $repId;
            $npks->push($nk);

            if ($assigned === 'AII')  $breakdown['AII']++;
            elseif ($assigned === 'AIIA') $breakdown['AIIA']++;
            else                          $breakdown['Unassigned']++;
        }

        $npks = $npks->unique()->values();

        Log::info('assignCompanyPerNpk:done', [
            'npk_count'      => $npks->count(),
            'breakdown'      => $breakdown,
            'company_filter' => $company,
        ]);

        return [$npks, $repEmpMap, $breakdown];
    }

    /**
     * Ekspresi SQL untuk normalisasi NPK.
     * $alias: alias table employees (misal 'e' atau 'employees')
     */
    private function npkNormExpr(string $alias): string
    {
        // LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE({alias}.npk,' ',''),'-',''),'.',''),'/','')))
        return "LOWER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE($alias.npk,' ',''),'-',''),'.',''),'/','')))";
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

    /* =============================================================================
     * MISC
     * ============================================================================= */

    private function short2(?string $name): string
    {
        $name = trim((string)$name);
        if ($name === '') return '-';
        $parts = preg_split('/\s+/', $name);
        return implode(' ', array_slice($parts, 0, 2));
    }
}
