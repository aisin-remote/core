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

        // ---- hitung per modul (per karyawan) ----
        $idp = $this->idpPerEmployeeBuckets($company);

        $hav = $this->modulePerEmployeeBuckets(
            table: (new Hav)->getTable(),        // "havs"
            statusCol: 'status',
            company: $company,
            // mapping status numerik
            map: [
                'approved' => [3],
                'revised'  => [-1],
                'progress' => [1, 2],
            ],
            // HAV langsung punya employee_id
            joinViaAssessment: false
        );

        $icp = $this->modulePerEmployeeBuckets(
            table: (new Icp)->getTable(),        // "icp"
            statusCol: 'status',
            company: $company,
            map: [
                'approved' => [3],
                'revised'  => [-1],
                'progress' => [1, 2],
            ],
            joinViaAssessment: false
        );

        $rtc = $this->modulePerEmployeeBuckets(
            table: (new Rtc)->getTable(),        // "rtc"
            statusCol: 'status',
            company: $company,
            // sesuaikan kalau mapping RTC beda
            map: [
                'approved' => [3],
                'revised'  => [-1],
                'progress' => [1, 2],
            ],
            joinViaAssessment: false
        );

        // ---- ALL = penjumlahan antar modul (tiap karyawan dihitung 1x per modul) ----
        $all = [
            'scope'    => ($idp['scope'] + $hav['scope'] + $icp['scope'] + $rtc['scope']),
            'approved' => ($idp['approved'] + $hav['approved'] + $icp['approved'] + $rtc['approved']),
            'progress' => ($idp['progress'] + $hav['progress'] + $icp['progress'] + $rtc['progress']),
            'revised'  => ($idp['revised']  + $hav['revised']  + $icp['revised']  + $rtc['revised']),
            'not'      => ($idp['not']      + $hav['not']      + $icp['not']      + $rtc['not']),
        ];

        return response()->json(compact('idp', 'hav', 'icp', 'rtc', 'all'));
    }

    /**
     * IDP per-employee buckets dengan aturan khusus Manager:
     *  Manager    : Approved {4}, Progress {1,2,3}, Revised {-1}
     *  Non-Manager: Approved {3,4}, Progress {1,2},   Revised {-1}
     */
    private function idpPerEmployeeBuckets(?string $company): array
    {
        // scope = jumlah karyawan pada company
        $scope = Employee::forCompany($company)->count();

        // base join: idp -> assessments -> employees
        $base = DB::table('idp')
            ->join('assessments', 'idp.assessment_id', '=', 'assessments.id')
            ->join('employees', 'assessments.employee_id', '=', 'employees.id')
            ->when($company, fn($q) => $q->where('employees.company_name', $company));

        // karyawan yang punya minimal 1 IDP
        $distinctEmp = (clone $base)->distinct()->count('assessments.employee_id');

        // subquery: 1 baris per employee_id + bucket final
        $perEmp = (clone $base)
            ->select([
                'assessments.employee_id',
                DB::raw("
                    CASE
                        /* Approved jika:
                           - manager & ada status 4, atau
                           - non-manager & ada status 3/4
                        */
                        WHEN
                            SUM(CASE WHEN LOWER(employees.position) LIKE '%manager%' AND idp.status IN (4) THEN 1 ELSE 0 END) > 0
                            OR
                            SUM(CASE WHEN LOWER(employees.position) NOT LIKE '%manager%' AND idp.status IN (3) THEN 1 ELSE 0 END) > 0
                        THEN 'approved'

                        /* Revised jika ada -1 dan belum approved */
                        WHEN SUM(CASE WHEN idp.status = -1 THEN 1 ELSE 0 END) > 0
                        THEN 'revised'

                        /* Progress jika:
                           - manager & ada 1/2/3, atau
                           - non-manager & ada 1/2
                        */
                        WHEN
                            SUM(CASE WHEN LOWER(employees.position) LIKE '%manager%' AND idp.status IN (1,2,3) THEN 1 ELSE 0 END) > 0
                            OR
                            SUM(CASE WHEN LOWER(employees.position) NOT LIKE '%manager%' AND idp.status IN (1,2) THEN 1 ELSE 0 END) > 0
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
     * Setiap karyawan diklasifikasikan sekali dengan prioritas:
     * Approved > Revised > Progress.
     *
     * @param string $table   nama tabel modul
     * @param string $statusCol nama kolom status
     * @param array  $map     ['approved'=>[...], 'revised'=>[...], 'progress'=>[...]]
     * @param bool   $joinViaAssessment true jika modul join ke employees via assessments (IDP); default false (punya employee_id langsung)
     */
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
        $module = $req->query('module');
        $statusWant = $req->query('status');
        $company = $req->query('company');
        $division = $req->query('division');
        $department = $req->query('department');
        $month = $req->query('month');

        if (!in_array($module, ['idp', 'hav', 'icp', 'rtc'], true)) {
            return response()->json(['rows' => []]);
        }
        if (!in_array($statusWant, ['approved', 'progress', 'revised', 'not'], true)) {
            return response()->json(['rows' => []]);
        }


        // === Helper scope employee (company + optional org filter) ===
        $empScope = Employee::query()->forCompany($company);
        //NOTE: sesuaikan filter division/department sesuai skema anda
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


        // === dispatcher per modul ===
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
            case 'rtc':
                $rows = $this->listSimple(Employee::class, Rtc::class, 'status', $empIds, $statusWant, $mStart, $mEnd);
                break;
        }

        return response()->json(['rows' => $rows]);
    }

    /**
     * Modul HAV/ICP/RTC: struktur sederhana karena ada employee_id di tabel.
     * $statusCol = 'status'
     */
    private function listSimple($empModel, $modelClass, string $statusCol, $empIds, string $statusWant, $start, $end)
    {
        $approved = [3, 4];
        $revised  = [-1];

        // base query record modul
        $q = $modelClass::query()->whereIn('employee_id', $empIds);
        if ($start && $end) $q->whereBetween('updated_at', [$start, $end]);

        // filter status
        if ($statusWant === 'approved')      $q->whereIn($statusCol, $approved);
        elseif ($statusWant === 'revised')   $q->whereIn($statusCol, $revised);
        elseif ($statusWant === 'progress')  $q->whereNotIn($statusCol, array_merge($approved, $revised));
        else {
            // NOT CREATED → karyawan yang TIDAK punya record sama sekali
            $has = $modelClass::query()->whereIn('employee_id', $empIds)->pluck('employee_id')->unique();
            $noRecordIds = collect($empIds)->diff($has)->values();

            return Employee::whereIn('id', $noRecordIds)
                ->orderBy('name')
                ->get()
                ->map(fn($e) => ['employee' => $e->name])
                ->values();
        }

        // → PER KARYAWAN: ambil employee_id unik yang match status
        $matchedEmpIds = $q->pluck('employee_id')->unique()->values();

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')
            ->get()
            ->map(fn($e) => ['employee' => $e->name])
            ->values();
    }

    /**
     * Modul IDP: special rule (manager vs non-manager) dan tidak ada employee_id.
     * Join: idp.assessment_id -> assessments.employee_id
     */
    private function listIdp($empIds, string $statusWant, $start, $end, ?string $company)
    {
        // pilih minimal kolom yang dibutuhkan untuk UNION
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
            // NOT CREATED → karyawan tanpa IDP sama sekali
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

        // dari subquery UNION, ambil emp_id unik → PER KARYAWAN
        $matchedEmpIds = DB::query()->fromSub($q, 't')
            ->distinct()->pluck('emp_id');

        return Employee::whereIn('id', $matchedEmpIds)
            ->orderBy('name')
            ->get()
            ->map(fn($e) => ['employee' => $e->name])
            ->values();
    }


    /** formatter untuk NOT CREATED (list karyawan) */
    private function formatEmployeesAsRows($employees, string $url = '#')
    {
        return collect($employees)->map(function ($e) {
            return [
                'employee'    => $e->name,
                'npk'         => $e->npk,
                'org'         => trim(($e->department->name ?? '-') . ' / ' . ($e->section?->name ?? '-'), ' /-'),
                'last_update' => '-',
                'aging'       => '-',
                'action'      => '#',
            ];
        })->values();
    }
}
