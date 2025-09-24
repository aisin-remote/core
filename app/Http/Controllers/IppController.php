<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Ipp;
use App\Models\IppPoint;
use App\Models\Section;
use App\Models\SubSection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class IppController
{
    private const CAP = [
        'activity_management' => 70,
        'people_development'  => 10,
        'crp'                 => 10,
        'special_assignment'  => 10,
    ];

    public function index()
    {
        $title = 'IPP Create';
        return view('website.ipp.index', compact('title'));
    }

    public function list(Request $request, $company = null)
    {
        $title  = 'IPP List';
        $user   = auth()->user();
        $emp    = $user->employee;
        $filter = $request->query('filter', 'all');

        $allPositions = [
            'President',
            'VPD',
            'Direktur',
            'GM',
            'Manager',
            'Coordinator',
            'Section Head',
            'Supervisor',
            'Leader',
            'JP',
            'Operator',
        ];

        $currentNormalized = $emp && method_exists($emp, 'getNormalizedPosition')
            ? strtolower($emp->getNormalizedPosition())
            : 'operator';

        $normMap = [];
        foreach ($allPositions as $label) {
            $normMap[$label] = $this->mapTabToNormalized($label);
        }

        $positionIndex = 0;
        foreach ($allPositions as $i => $label) {
            if ($normMap[$label] === $currentNormalized) {
                $positionIndex = $i;
                break;
            }
        }
        $visiblePositions = array_slice($allPositions, $positionIndex);

        return view('website.ipp.list', compact('title', 'company', 'visiblePositions', 'filter'));
    }

    /**
     * JSON list untuk tabel: bawahan + karyawan yang menunjuk saya sebagai PIC (tahun berjalan),
     * dengan posisi dinormalisasi untuk filter tab.
     */
    public function listJson(Request $request)
    {
        try {
            $user = auth()->user();
            $me   = $user->employee;

            if (!$me) {
                Log::warning('IPP listJson: user tidak punya relasi employee', ['user_id' => $user->id ?? null]);
                return response()->json([
                    'data' => [],
                    'meta' => ['total' => 0, 'page' => 1, 'per_page' => 10, 'last_page' => 1]
                ]);
            }

            $search   = (string) $request->query('search', '');
            $npk      = (string) $request->query('npk', '');
            $filterUi = (string) $request->query('filter', 'all');
            $norm     = $this->mapTabToNormalized($filterUi);
            $year     = (string) $request->query('filter_year', now()->format('Y'));
            $status   = (string) $request->query('status', '');
            $page     = max(1, (int) $request->query('page', 1));
            $perPage  = min(100, max(5, (int) $request->query('per_page', 20)));

            $companyParam = strtolower((string) $request->query('company', ''));
            $isHRDTop = method_exists($user, 'isHRDorDireksi') ? $user->isHRDorDireksi() : false;

            Log::info('IPP listJson params', [
                'user_id'      => $user->id,
                'employee_id'  => $me->id,
                'isHRDTop'     => $isHRDTop,
                'companyParam' => $companyParam,
                'filterUi'     => $filterUi,
                'normalized'   => $norm,
                'status'       => $status,
                'page'         => $page,
                'per_page'     => $perPage,
            ]);

            $aliasesBucket = $this->aliasesByNormalized();

            $empQuery = null;
            $total    = 0;
            $records  = collect();

            // =========================================================
            //                HRD / DIREKSI  (FULL COMPANY)
            // =========================================================
            if ($isHRDTop) {
                $empQuery = Employee::query()
                    ->when(in_array($companyParam, ['aii', 'aiia'], true), function ($q) use ($companyParam) {
                        $q->whereRaw('LOWER(company_name) = ?', [$companyParam]);
                    })
                    ->select($this->baseEmpSelect())
                    ->addSelect($this->ippSelects($year));

                $this->applyCommonFilters($empQuery, $norm, $aliasesBucket, $npk, $search);
                $this->applyStatusFilter($empQuery, $status);

                $this->logQuery('IPP listJson HRD SQL', $empQuery);

                $total   = (clone $empQuery)->count();
                $records = $empQuery
                    ->orderByRaw('CASE WHEN ipp_updated_at IS NULL THEN 1 ELSE 0 END ASC')
                    ->orderByDesc('ipp_updated_at')
                    ->orderBy('employees.name')
                    ->forPage($page, $perPage)
                    ->get();

                // =========================================================
                //              NON-HRD (HANYA BAWAHAN ∪ PIC)
                // =========================================================
            } else {
                // hitung bawahan & karyawan yang menunjuk saya sebagai PIC → HANYA untuk non-HRD
                $subordinateIds = $this->getSubordinatesFromStructure($me)->pluck('id')->toArray();

                $picEmployeeIds = Ipp::where('on_year', $year)
                    ->where('pic_review_id', $me->id)
                    ->pluck('employee_id')
                    ->toArray();

                $targetEmployeeIds = array_values(array_unique(array_merge($subordinateIds, $picEmployeeIds)));
                if (empty($targetEmployeeIds)) {
                    Log::info('IPP listJson: non-HRD, targetEmployeeIds kosong', ['employee_id' => $me->id]);
                    return response()->json([
                        'data' => [],
                        'meta' => ['total' => 0, 'page' => $page, 'per_page' => $perPage, 'last_page' => 1],
                    ]);
                }

                $empQuery = Employee::query()
                    ->whereIn('id', $targetEmployeeIds)
                    ->where('company_name', $me->company_name) // batasi ke company user
                    ->select($this->baseEmpSelect())
                    ->addSelect($this->ippSelects($year));

                $this->applyCommonFilters($empQuery, $norm, $aliasesBucket, $npk, $search);
                $this->applyStatusFilter($empQuery, $status);

                $this->logQuery('IPP listJson Non-HRD SQL', $empQuery);

                $total   = (clone $empQuery)->count();
                $records = $empQuery
                    ->orderByRaw('CASE WHEN ipp_updated_at IS NULL THEN 1 ELSE 0 END ASC')
                    ->orderByDesc('ipp_updated_at')
                    ->orderBy('employees.name')
                    ->forPage($page, $perPage)
                    ->get();
            }

            // --- mapping output (dipakai kedua branch) ---
            $data = $records->values()->map(function (Employee $row, $idx) use ($page, $perPage, $year) {
                $hasIpp     = !is_null($row->ipp_id);
                $normalized = method_exists($row, 'getNormalizedPosition')
                    ? strtolower($row->getNormalizedPosition())
                    : strtolower($row->position ?? '');

                $department = $row->ipp_department ?: $row->bagian;

                return [
                    'no'       => ($page - 1) * $perPage + $idx + 1,
                    'id'       => $row->ipp_id,
                    'employee' => [
                        'id'                  => $row->id,
                        'npk'                 => (string) $row->npk,
                        'name'                => (string) $row->name,
                        'photo'               => $row->photo ? asset('storage/' . $row->photo) : null,
                        'company'             => (string) $row->company_name,
                        'position'            => (string) $row->position,
                        'normalized_position' => $normalized,
                        'department'          => (string) $department,
                        'grade'               => (string) $row->grade,
                    ],
                    'on_year'    => $hasIpp ? (string) $row->ipp_on_year : $year,
                    'status'     => $hasIpp ? (string) $row->ipp_status : 'not_created',
                    'summary'    => [],
                    'updated_at' => $hasIpp && $row->ipp_updated_at ? (string) $row->ipp_updated_at : null,
                ];
            });

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total'     => (int) $total,
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'last_page' => (int) ceil(((int)$total ?: 0) / $perPage),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('IPP listJson error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'data' => [],
                'meta' => ['total' => 0, 'page' => 1, 'per_page' => 10, 'last_page' => 1],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * BARU: List semua IPP milik satu employee (untuk modal "Show").
     * Params: employee_id (wajib)
     * Return: array IPP (per tahun): id, on_year, status, summary (ringkas), updated_at
     * - Tambahkan entri sintetis tahun berjalan bila belum ada (status: not_created).
     * - Akses dibatasi: hanya boleh lihat bawahan atau employee yang pernah menunjuk saya sebagai PIC.
     */
    public function employeeIppsJson(Request $request)
    {
        $user = auth()->user();
        $me   = $user->employee;
        if (!$me) {
            return response()->json(['message' => 'Employee not found for this account.'], 403);
        }

        $employeeId = (int) $request->query('employee_id', 0);
        if ($employeeId <= 0) {
            return response()->json(['message' => 'employee_id is required'], 422);
        }

        // Authorization: bawahan saya ATAU pernah menunjuk saya sebagai PIC
        $subordinateIds = $this->getSubordinatesFromStructure($me)->pluck('id')->toArray();
        $asPicIds = Ipp::where('pic_review_id', $me->id)->pluck('employee_id')->unique()->toArray();

        if (!in_array($employeeId, $subordinateIds) && !in_array($employeeId, $asPicIds) && $employeeId !== (int)$me->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $emp = Employee::find($employeeId);
        if (!$emp) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $ipps = Ipp::where('employee_id', $employeeId)
            ->orderByDesc('on_year')
            ->get();

        $nowYear = now()->format('Y');
        $haveCurrent = $ipps->contains(fn($x) => (string)$x->on_year === $nowYear);

        $rows = $ipps->map(function (Ipp $ipp) {
            return [
                'id'         => $ipp->id,
                'on_year'    => (string) $ipp->on_year,
                'status'     => (string) $ipp->status,
                'summary'    => is_array($ipp->summary) ? $ipp->summary : [],
                'updated_at' => $ipp->updated_at?->toDateTimeString(),
            ];
        })->values()->all();

        // entri sintetis kalau tahun berjalan belum ada
        if (!$haveCurrent) {
            array_unshift($rows, [
                'id'         => null,
                'on_year'    => $nowYear,
                'status'     => 'not_created',
                'summary'    => [],
                'updated_at' => null,
            ]);
        }

        return response()->json([
            'employee' => [
                'id'       => $emp->id,
                'npk'      => (string) $emp->npk,
                'name'     => (string) $emp->name,
                'position' => (string) $emp->position,
                'photo'    => $emp->photo ? asset('storage/' . $emp->photo) : null,
                'company'  => (string) $emp->company_name,
                'grade'    => (string) $emp->grade,
            ],
            'ipps' => $rows,
        ]);
    }

    /** ====== INIT / STORE / SUBMIT / DELETE / EXPORT (tetap) ====== */
    public function init(Request $request)
    {
        $user  = auth()->user();
        $emp   = $user->employee;
        $year  = now()->format('Y');
        $empId = (int) ($emp->id ?? 0);

        $picReviewId = null;
        try {
            $assignLevel = method_exists($emp, 'getCreateAuth') ? $emp->getCreateAuth() : null;
            $superior    = $assignLevel && method_exists($emp, 'getSuperiorsByLevel')
                ? $emp->getSuperiorsByLevel($assignLevel)->first()
                :  null;
            $picReviewId = (int) ($superior->id ?? 0) ?: null;
        } catch (\Throwable $e) {
            $picReviewId = null;
        }

        $identitas = [
            'nama'        => (string)($emp->name ?? $user->name ?? ''),
            'department'  => (string)($emp->bagian ?? ''),
            'division'    => (string)($emp->department->division->name ?? ''),
            'section'     => (string)($emp->leadingSection->name ?? ''),
            'date_review' => '-',
            'pic_review'  => $picReviewId,
            'on_year'     => $year,
            'no_form'     => '-',
        ];

        $pointByCat = [
            'activity_management' => [],
            'people_development'  => [],
            'crp'                 => [],
            'special_assignment'  => [],
        ];
        $summary = [
            'activity_management' => 0,
            'people_development'  => 0,
            'crp'                 => 0,
            'special_assignment'  => 0,
            'total'               => 0,
        ];
        $header = null;
        $locked = false;

        if ($empId) {
            $ipp = Ipp::where('employee_id', $empId)
                ->where('on_year', $year)
                ->where('status', '!=', 'approved')
                ->first();

            if ($ipp && !$ipp->pic_review_id && $picReviewId) {
                $ipp->update(['pic_review_id' => $picReviewId]);
            }

            if ($ipp) {
                $locked = ($ipp->status === 'submitted');

                $points = IppPoint::where('ipp_id', $ipp->id)->orderBy('id')->get();

                foreach ($points as $p) {
                    $item = [
                        'id'         => $p->id,
                        'category'   => $p->category,
                        'activity'   => (string) $p->activity,
                        'target_mid' => (string) $p->target_mid,
                        'target_one' => (string) $p->target_one,
                        'due_date'   => $p->due_date ? substr((string)$p->due_date, 0, 10) : null,
                        'weight'     => (int) $p->weight,
                        'status'     => (string) ($p->status ?? 'draft'),
                    ];
                    if (isset($pointByCat[$p->category])) {
                        $pointByCat[$p->category][] = $item;
                        $summary[$p->category]      = ($summary[$p->category] ?? 0) + (int) $p->weight;
                    }
                }

                $summary['total'] =
                    ($summary['activity_management'] ?? 0)
                    + ($summary['people_development'] ?? 0)
                    + ($summary['crp'] ?? 0)
                    + ($summary['special_assignment'] ?? 0);

                $header = [
                    'id'            => $ipp->id,
                    'employee_id'   => $ipp->employee_id,
                    'pic_review_id' => $ipp->pic_review_id,
                    'status'        => (string) $ipp->status,
                    'summary'       => $ipp->summary ?: $summary,
                    'locked'        => $locked,
                ];
            }
        }

        return response()->json([
            'identitas' => $identitas,
            'ipp'       => $header,
            'points'    => $pointByCat,
            'cap'       => self::CAP,
            'locked'    => $locked,
        ]);
    }

    public function store(Request $request)
    {
        $payloadRaw = $request->input('payload');
        $payload    = is_array($payloadRaw) ? $payloadRaw : json_decode($payloadRaw ?? '[]', true);

        if (isset($payload['single_point'])) {
            return $this->storeSinglePoint($request, $payload);
        }

        return response()->json(['message' => 'Unsupported payload form. Kirim via modal per-point'], 422);
    }

    private function storeSinglePoint(Request $request, array $payload)
    {
        $v = validator($payload, [
            'mode'             => ['required', Rule::in(['create', 'edit'])],
            'status'           => ['required', Rule::in(['draft', 'submitted'])],
            'cat'              => ['required', Rule::in(array_keys(self::CAP))],
            'row_id'           => ['nullable', 'integer'],
            'point.activity'   => ['required', 'string'],
            'point.target_mid' => ['nullable', 'string'],
            'point.target_one' => ['nullable', 'string'],
            'point.due_date'   => ['required', 'date'],
            'point.weight'     => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $mode   = $payload['mode'];
        $status = 'draft';
        $cat    = $payload['cat'];
        $rowId  = $payload['row_id'] ?? null;
        $p      = $payload['point'];

        $user  = auth()->user();
        $emp   = $user->employee;
        $empId = (int)($emp->id ?? 0);
        $year  = now()->format('Y');

        if (!$empId) {
            return response()->json(['message' => 'Employee tidak ditemukan pada akun ini.'], 422);
        }

        $picReviewId = null;
        try {
            $assignLevel = method_exists($emp, 'getCreateAuth') ? $emp->getCreateAuth() : null;
            $superior    = $assignLevel && method_exists($emp, 'getSuperiorsByLevel')
                ? $emp->getSuperiorsByLevel($assignLevel)->first()
                :  null;
            $picReviewId = (int) ($superior->id ?? 0) ?: null;
            $picReviewName = (string) ($superior->name ?? '');
        } catch (\Throwable $e) {
            $picReviewId = null;
            $picReviewName = '';
        }

        $headerAttrs = ['employee_id' => $empId, 'on_year' => $year];

        try {
            DB::beginTransaction();

            $ipp = Ipp::firstOrCreate(
                $headerAttrs,
                [
                    'nama'          => (string) $emp->name,
                    'department'    => (string)($emp->bagian ?? ''),
                    'division'      => (string)($emp->department->division->name ?? ''),
                    'section'       => (string)($emp->leadingSection->name ?? ''),
                    'date_review'   => null,
                    'pic_review'    => $picReviewName,
                    'pic_review_id' => $picReviewId,
                    'no_form'       => '',
                    'status'        => 'draft',
                    'summary'       => [],
                ]
            );

            if (!$ipp->pic_review_id && $picReviewId) {
                $ipp->pic_review_id = $picReviewId;
                $ipp->save();
            }

            if ($mode === 'create') {
                $point = IppPoint::create([
                    'ipp_id'     => $ipp->id,
                    'category'   => $cat,
                    'activity'   => $p['activity'],
                    'target_mid' => $p['target_mid'] ?? null,
                    'target_one' => $p['target_one'] ?? null,
                    'due_date'   => $p['due_date'],
                    'weight'     => (int)$p['weight'],
                    'status'     => $status,
                ]);
            } else {
                $point = IppPoint::where('id', $rowId)->first();
                if (!$point) {
                    DB::rollBack();
                    return response()->json(['message' => 'Point not found'], 404);
                }
                if ((int) $point->ipp->employee_id !== $empId || (string)$point->ipp->on_year !== $year) {
                    DB::rollBack();
                    return response()->json(['message' => 'Tidak diizinkan mengubah point ini.'], 403);
                }
                $point->update([
                    'category'   => $cat,
                    'activity'   => $p['activity'],
                    'target_mid' => $p['target_mid'] ?? null,
                    'target_one' => $p['target_one'] ?? null,
                    'due_date'   => $p['due_date'],
                    'weight'     => (int)$p['weight'],
                    'status'     => $status,
                ]);
            }

            $summary = IppPoint::where('ipp_id', $ipp->id)
                ->selectRaw('category, SUM(weight) as used')
                ->groupBy('category')
                ->pluck('used', 'category')
                ->toArray();
            $summary['total'] = array_sum($summary);

            $ipp->summary = $summary;
            $ipp->status  = 'draft';
            $ipp->save();

            DB::commit();

            return response()->json([
                'message' => 'Draft tersimpan',
                'row_id'  => $point->id,
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Gagal menyimpan data. Silakan coba lagi atau hubungi admin.'], 500);
        }
    }

    public function submit(Request $request)
    {
        $user  = auth()->user();
        $emp   = $user->employee;
        $year  = now()->format('Y');
        $empId = (int)($emp->id ?? 0);

        $ipp = Ipp::where('employee_id', $empId)->where('on_year', $year)->first();

        if (!$ipp) {
            return response()->json(['message' => 'Belum ada data IPP untuk disubmit.'], 422);
        }

        $points = IppPoint::where('ipp_id', $ipp->id)->get();
        if ($points->isEmpty()) {
            return response()->json(['message' => 'Tambahkan minimal satu point sebelum submit.'], 422);
        }

        $summary = [];
        foreach (self::CAP as $cat => $cap) {
            $used          = (int) $points->where('category', $cat)->sum('weight');
            $summary[$cat] = $used;
            if ($used > $cap) {
                return response()->json([
                    'message' => "Bobot kategori \"" . str_replace('_', ' ', $cat) . "\" melebihi cap {$cap}%. Kurangi W% dulu."
                ], 422);
            }
        }

        $summary['total'] = array_sum($summary);
        if ($summary['total'] !== 100) {
            return response()->json(['message' => 'Total bobot harus tepat 100% sebelum submit.'], 422);
        }

        DB::transaction(function () use ($ipp, $summary) {
            IppPoint::where('ipp_id', $ipp->id)->update(['status' => 'submitted']);
            $ipp->update([
                'status'  => 'submitted',
                'summary' => $summary,
            ]);
        });

        return response()->json(['message' => 'Berhasil submit IPP.', 'summary' => $summary]);
    }

    public function destroyPoint(Request $request, IppPoint $point)
    {
        $user  = auth()->user();
        $emp   = $user->employee;
        $year  = now()->format('Y');
        $empId = (int)($emp->id ?? 0);

        $ipp = $point->ipp;

        if (!$ipp || (int)$ipp->employee_id !== $empId || (string)$ipp->on_year !== $year) {
            return response()->json(['message' => 'Tidak diizinkan menghapus point ini.'], 403);
        }

        if ($ipp->status === 'submitted') {
            return response()->json(['message' => 'IPP sudah submitted, tidak dapat dihapus.'], 422);
        }

        try {
            DB::beginTransaction();

            $point->delete();

            $summary = IppPoint::where('ipp_id', $ipp->id)
                ->selectRaw('category, SUM(weight) as used')
                ->groupBy('category')
                ->pluck('used', 'category')
                ->toArray();
            $summary['total'] = array_sum($summary);

            $ipp->summary = $summary;
            $ipp->save();

            DB::commit();

            return response()->json([
                'message' => 'Point dihapus.',
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Gagal menghapus point.'], 500);
        }
    }

    public function exportExcel(?int $id = null)
    {
        try {
            // (opsional) beri waktu lebih panjang jika dataset besar
            @set_time_limit(120);

            $user    = auth()->user();
            $authEmp = $user->employee;
            $year    = now()->format('Y');

            abort_if(!$authEmp, 403, 'Employee not found for this account.');

            $ipp = $id
                ? Ipp::findOrFail($id)
                : Ipp::where('employee_id', $authEmp->id)->first();

            abort_if(!$ipp, 404, 'IPP not found.');

            // owner
            $owner = Employee::find($ipp->employee_id);
            abort_if(!$owner, 404, 'Owner employee not found.');

            $points = IppPoint::where('ipp_id', $ipp->id)->orderBy('id')->get();

            // Grouping
            $grouped = [
                'activity_management' => [],
                'people_development'  => [],
                'crp'                 => [],
                'special_assignment'  => [],
            ];
            foreach ($points as $p) {
                $cat = $p->category;
                if (!isset($grouped[$cat])) continue;
                $grouped[$cat][] = [
                    'activity'   => (string) $p->activity,
                    'target_mid' => (string) ($p->target_mid ?? ''),
                    'target_one' => (string) ($p->target_one ?? ''),
                    'due_date'   => $p->due_date ? substr((string)$p->due_date, 0, 10) : '',
                    'weight'     => (int) $p->weight,
                ];
            }

            $assignLevel = method_exists($owner, 'getCreateAuth') ? $owner->getCreateAuth() : null;
            $pic = $assignLevel && method_exists($owner, 'getSuperiorsByLevel')
                ? optional($owner->getSuperiorsByLevel($assignLevel)->first())->name
                : '';

            $identitas = [
                'nama'        => (string)($owner->name ?? $user->name ?? ''),
                'department'  => (string)($owner->bagian ?? ''),
                'section'     => (string)($ipp->section ?? ''),
                'division'    => (string)($ipp->division ?? ''),
                'date_review' => $ipp->date_review ? substr((string)$ipp->date_review, 0, 10) : '',
                'pic_review'  => $pic,
                'on_year'     => (string)$ipp->on_year,
            ];

            // === Open template
            $template = public_path('assets/file/Template IPP.xlsx');
            abort_unless(is_file($template), 500, 'Template file not found on server.');
            $spreadsheet = IOFactory::load($template);
            /** @var Worksheet $sheet */
            $sheet = $spreadsheet->getSheetByName('IPP form') ?? $spreadsheet->getActiveSheet();

            // Tahun (AA4:AC4)
            $sheet->mergeCells('AA4:AC4');
            $sheet->setCellValue('AA4', $identitas['on_year']);
            $sheet->getStyle("AA4:AC4")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_TOP,
                    'wrapText'   => true,
                ],
                'font' => ['name' => 'Tahoma', 'size' => 14],
            ]);

            // Header identitas
            $sheet->setCellValue('J7',  $identitas['nama']);
            $sheet->setCellValue('J8',  $identitas['department']);
            $sheet->setCellValue('J9',  $identitas['section']);
            $sheet->setCellValue('J10', $identitas['division']);
            $sheet->setCellValue('AV7', $identitas['date_review']);
            $sheet->setCellValue('AV8', $identitas['pic_review']);
            foreach (['J7', 'J8', 'J9', 'J10'] as $addr) {
                $sheet->getStyle($addr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }

            // Blok kolom tetap
            $R_ACTIVITY_FROM = 'B';
            $R_ACTIVITY_TO   = 'Q';
            $R_WEIGHT_FROM   = 'R';
            $R_WEIGHT_TO     = 'T';
            $R_MID_FROM      = 'U';
            $R_MID_TO        = 'AH';
            $R_ONE_FROM      = 'AI';
            $R_ONE_TO        = 'AU';
            $R_DUE_FROM      = 'AV';
            $R_DUE_TO        = 'BA';

            // Helper border outline
            $outlineThin = function (string $range) use ($sheet) {
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'right'  => ['borderStyle' => Border::BORDER_THIN,   'color' => ['rgb' => '000000']],
                        'left'   => ['borderStyle' => Border::BORDER_THIN,   'color' => ['rgb' => '000000']],
                    ],
                ]);
            };
            $outlineMedium = function (string $range) use ($sheet) {
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'right' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                    ],
                ]);
            };

            $lastColLtr = 'BA';
            $lastColIdx = $this->colIndex($lastColLtr);

            // Posisi header kategori pada template
            $HEADER = [
                'activity_management' => 14,
                'people_development'  => 18,
                'crp'                 => 23,
                'special_assignment'  => 27,
            ];
            $order = ['activity_management', 'people_development', 'crp', 'special_assignment'];

            $BASE_FONT_NAME = 'Tahoma';
            $BASE_FONT_SIZE = 14;

            $offset = 0;
            foreach ($order as $cat) {
                $items = $grouped[$cat] ?? [];
                $n = count($items);
                if ($n === 0) continue;

                $headerAnchor = ($HEADER[$cat] ?? 13) + $offset;
                $baseRow      = $headerAnchor + 1;

                // sisip baris tambahan (n-1) & cloning style dasar
                if ($n > 1) {
                    $sheet->insertNewRowBefore($baseRow + 1, $n - 1);
                    for ($r = $baseRow + 1; $r <= $baseRow + $n - 1; $r++) {
                        $sheet->duplicateStyle(
                            $sheet->getStyle("B{$baseRow}:{$lastColLtr}{$baseRow}"),
                            "B{$r}:{$lastColLtr}{$r}"
                        );
                        $sheet->getRowDimension($r)->setRowHeight(-1);
                    }
                }

                for ($i = 0; $i < $n; $i++) {
                    $r   = $baseRow + $i;
                    $row = $items[$i];

                    // bersihkan isi row
                    for ($c = 2; $c <= $lastColIdx; $c++) {
                        $sheet->setCellValueByColumnAndRow($c, $r, null);
                    }

                    $sheet->getStyle("B{$r}:{$lastColLtr}{$r}")
                        ->getBorders()->getInside()->setBorderStyle(Border::BORDER_NONE);
                    $sheet->getStyle("B{$r}:{$lastColLtr}{$r}")
                        ->getFont()->setName($BASE_FONT_NAME)->setSize($BASE_FONT_SIZE);

                    // Normalisasi & hitung baris
                    $activity = $this->xlText($row['activity']   ?? '');
                    $mid      = $this->xlText($row['target_mid'] ?? '');
                    $one      = $this->xlText($row['target_one'] ?? '');

                    $maxLines = max(
                        $this->countLines($activity),
                        $this->countLines($mid),
                        $this->countLines($one),
                        1
                    );

                    // PROGRAM / ACTIVITY
                    $sheet->mergeCells("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}");
                    $sheet->setCellValue("{$R_ACTIVITY_FROM}{$r}", $activity);
                    $sheet->getStyle("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                    $outlineThin("{$R_ACTIVITY_FROM}{$r}:{$R_ACTIVITY_TO}{$r}");

                    // WEIGHT (R:T)
                    $sheet->mergeCells("{$R_WEIGHT_FROM}{$r}:{$R_WEIGHT_TO}{$r}");
                    $sheet->setCellValue("{$R_WEIGHT_FROM}{$r}", ((int)($row['weight'] ?? 0)) / 100);
                    $sheet->getStyle("{$R_WEIGHT_FROM}{$r}:{$R_WEIGHT_TO}{$r}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("{$R_WEIGHT_FROM}{$r}:{$R_WEIGHT_TO}{$r}")
                        ->getNumberFormat()->setFormatCode('0%');
                    $outlineThin("{$R_WEIGHT_FROM}{$r}:{$R_WEIGHT_TO}{$r}");

                    // MID YEAR (U:AH)
                    $sheet->mergeCells("{$R_MID_FROM}{$r}:{$R_MID_TO}{$r}");
                    $sheet->setCellValue("{$R_MID_FROM}{$r}", $mid);
                    $sheet->getStyle("{$R_MID_FROM}{$r}:{$R_MID_TO}{$r}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                    $outlineThin("{$R_MID_FROM}{$r}:{$R_MID_TO}{$r}");

                    // ONE YEAR (AI:AU)
                    $sheet->mergeCells("{$R_ONE_FROM}{$r}:{$R_ONE_TO}{$r}");
                    $sheet->setCellValue("{$R_ONE_FROM}{$r}", $one);
                    $sheet->getStyle("{$R_ONE_FROM}{$r}:{$R_ONE_TO}{$r}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                    $outlineThin("{$R_ONE_FROM}{$r}:{$R_ONE_TO}{$r}");

                    // DUE DATE (AV:BA)
                    $sheet->mergeCells("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}");
                    $sheet->setCellValue("{$R_DUE_FROM}{$r}", (string)($row['due_date'] ?? ''));
                    $sheet->getStyle("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                    $outlineMedium("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}", 'right');

                    // tinggi baris
                    $sheet->getRowDimension($r)->setRowHeight(
                        $this->calcRowHeight($maxLines, 18.0, 4.0)
                    );
                }

                // Tambah offset baris sisipan
                $offset += max(0, $n - 1);
            }

            $fileName = 'IPP_' . $year . '_' . Str::slug((string)($owner->name ?? 'user')) . '.xlsx';
            $tmp = tempnam(sys_get_temp_dir(), 'ipp_') . '.xlsx';
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmp);

            return response()->download(
                $tmp,
                $fileName,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            )->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('IPP exportExcel failed', [
                'id'      => $id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Gagal mengekspor Excel. Silakan coba lagi.'], 500);
            }

            return redirect()->back()->with('warning', 'Gagal mengekspor Excel: ' . $e->getMessage());
        }
    }

    public function exportPdf(?int $id = null)
    {
        try {
            @set_time_limit(120);

            $user    = auth()->user();
            $authEmp = $user->employee;

            abort_if(!$authEmp, 403, 'Employee not found for this account.');

            // 1) Ambil header IPP
            $ipp = $id
                ? Ipp::find($id)
                : Ipp::where('employee_id', $authEmp->id)->where('on_year', now()->format('Y'))->first();

            abort_if(!$ipp, 404, 'IPP not found.');

            // 2) Owner IPP
            $owner = Employee::find($ipp->employee_id);
            abort_if(!$owner, 404, 'Owner employee not found.');

            // 3) Points
            $points = IppPoint::where('ipp_id', $ipp->id)->orderBy('id')->get();

            // 4) Grouping + summary
            $grouped = [
                'activity_management' => [],
                'people_development'  => [],
                'crp'                 => [],
                'special_assignment'  => [],
            ];
            foreach ($points as $p) {
                if (!array_key_exists($p->category, $grouped)) continue;
                $grouped[$p->category][] = [
                    'activity'   => (string) $p->activity,
                    'target_mid' => (string) ($p->target_mid ?? ''),
                    'target_one' => (string) ($p->target_one ?? ''),
                    'due_date'   => $p->due_date ? substr((string)$p->due_date, 0, 10) : '',
                    'weight'     => (int) $p->weight,
                ];
            }
            $summary = [
                'activity_management' => array_sum(array_column($grouped['activity_management'], 'weight')),
                'people_development'  => array_sum(array_column($grouped['people_development'], 'weight')),
                'crp'                 => array_sum(array_column($grouped['crp'], 'weight')),
                'special_assignment'  => array_sum(array_column($grouped['special_assignment'], 'weight')),
            ];
            $summary['total'] = array_sum($summary);

            // 5) PIC name
            $assignLevel = method_exists($owner, 'getCreateAuth') ? $owner->getCreateAuth() : null;
            $pic = $assignLevel && method_exists($owner, 'getSuperiorsByLevel')
                ? optional($owner->getSuperiorsByLevel($assignLevel)->first())->name
                : '';

            // 6) Identitas
            $identitas = [
                'nama'        => (string) ($owner->name ?? ''),
                'department'  => (string) ($owner->bagian ?? $ipp->department ?? ''),
                'section'     => (string) ($ipp->section ?? ''),
                'division'    => (string) ($ipp->division ?? ''),
                'date_review' => $ipp->date_review ? substr((string)$ipp->date_review, 0, 10) : '',
                'pic_review'  => $pic,
                'on_year'     => (string) $ipp->on_year,
                'company'     => (string) ($owner->company_name ?? ''),
                'grade'       => (string) ($owner->grade ?? ''),
                'npk'         => (string) ($owner->npk ?? ''),
                'position'    => (string) ($owner->position ?? ''),
            ];


            // 7) Render Blade → PDF

            $logoPath = public_path('assets/media/logos/aisin.png');
            $logoSrc  = null;
            if (is_file($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoSrc = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
            // dd($logoSrc);
            $pdf = Pdf::loadView('website.ipp.pdf', [
                'ipp'       => $ipp,
                'owner'     => $owner,
                'identitas' => $identitas,
                'grouped'   => $grouped,
                'summary'   => $summary,
                'logo'      => $logoSrc
            ])->setPaper('a4', 'landscape');

            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);

            $filename = 'IPP_' . $ipp->on_year . '_' . Str::slug($owner->name ?? 'user') . '.pdf';
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('IPP exportPdf failed', [
                'id'      => $id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Gagal mengekspor PDF. Silakan coba lagi.'], 500);
            }

            return redirect()->back()->with('warning', 'Gagal mengekspor PDF: ' . $e->getMessage());
        }
    }

    /** ===== Helpers ===== */
    private function xlText(?string $s): string
    {
        $s = (string)$s;
        return str_replace(["\r\n", "\r"], "\n", $s);
    }

    private function countLines(string $s): int
    {
        return max(1, substr_count($s, "\n") + 1);
    }

    private function calcRowHeight(int $lines, float $perLine = 18.0, float $padding = 4.0): float
    {
        return max($perLine, $lines * $perLine + $padding);
    }

    private function colIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $n = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $n = $n * 26 + (ord($letters[$i]) - 64);
        }
        return $n;
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
            return Employee::whereRaw('1=0'); // kosong
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

    /** Map label tab → normalized bucket (lowercase) */
    private function mapTabToNormalized(string $label): string
    {
        $label = strtolower(trim($label));
        $map = [
            'president'    => 'president',
            'vpd'          => 'vpd',
            'direktur'     => 'direktur',
            'director'     => 'direktur',
            'gm'           => 'gm',
            'manager'      => 'manager',
            'coordinator'  => 'manager',       // grouped to manager
            'section head' => 'supervisor',    // grouped to supervisor
            'supervisor'   => 'supervisor',
            'leader'       => 'leader',
            'staff'        => 'leader',        // grouped to leader
            'jp'           => 'jp',
            'operator'     => 'operator',
            'all'          => 'all',
        ];
        return $map[$label] ?? $label;
    }

    /** Alias per normalized bucket */
    private function aliasesByNormalized(): array
    {
        return [
            'president'  => ['president'],
            'vpd'        => ['vpd'],
            'direktur'   => ['direktur', 'director'],
            'gm'         => ['gm', 'act gm'],
            'manager'    => ['manager', 'act manager', 'coordinator', 'act coordinator'],
            'supervisor' => ['supervisor', 'section head', 'act section head', 'act supervisor'],
            'leader'     => ['leader', 'act leader', 'staff'],
            'jp'         => ['jp', 'act jp'],
            'operator'   => ['operator'],
        ];
    }

    /** ===== Helper ekstra untuk optimasi listJson ===== */

    private function baseEmpSelect(): array
    {
        return [
            'employees.id',
            'employees.npk',
            'employees.name',
            'employees.position',
            'employees.grade',
            'employees.company_name',
            'employees.photo',
        ];
    }

    private function ippSelects(string $year): array
    {
        return [
            'ipp_id' => Ipp::select('id')
                ->where('on_year', $year)
                ->whereColumn('employee_id', 'employees.id')
                ->limit(1),
            'ipp_on_year' => Ipp::select('on_year')
                ->where('on_year', $year)
                ->whereColumn('employee_id', 'employees.id')
                ->limit(1),
            'ipp_status' => Ipp::select('status')
                ->where('on_year', $year)
                ->whereColumn('employee_id', 'employees.id')
                ->limit(1),
            'ipp_department' => Ipp::select('department')
                ->where('on_year', $year)
                ->whereColumn('employee_id', 'employees.id')
                ->limit(1),
            'ipp_updated_at' => Ipp::select('updated_at')
                ->where('on_year', $year)
                ->whereColumn('employee_id', 'employees.id')
                ->limit(1),
        ];
    }

    private function applyCommonFilters($builder, string $norm, array $aliasesBucket, string $npk, string $search): void
    {
        $builder
            ->when($norm !== 'all', function ($q) use ($aliasesBucket, $norm) {
                $aliases = $aliasesBucket[$norm] ?? [];
                if (!empty($aliases)) {
                    $q->whereIn(DB::raw('LOWER(position)'), array_map('strtolower', $aliases));
                } else {
                    $q->whereRaw('LOWER(position) = ?', [$norm]);
                }
            })
            ->when($npk, fn($q) => $q->where('npk', 'like', "%{$npk}%"))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('npk', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            });
    }

    private function applyStatusFilter($builder, string $status): void
    {
        if ($status === '') return;

        if ($status === 'not_created') {
            $builder->whereNull('ipp_id');
        } else {
            $builder->where('ipp_status', $status);
        }
    }

    private function logQuery(string $label, $builder): void
    {
        try {
            $sql = vsprintf(
                str_replace('?', '%s', $builder->toSql()),
                collect($builder->getBindings())->map(fn($b) => is_string($b) ? "'$b'" : $b)->toArray()
            );
            Log::debug($label, ['sql' => $sql]);
        } catch (\Throwable $e) {
            Log::debug($label . ' (failed to render SQL)', ['error' => $e->getMessage()]);
        }
    }
}
