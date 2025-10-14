<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Ipp;
use App\Models\IppComment;
use App\Models\IppPoint;
use App\Models\Section;
use App\Models\SubSection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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

        // Ambil target employee lebih awal agar bisa cek posisi
        $emp = Employee::find($employeeId);
        if (!$emp) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Exemption:
        // - Jika user adalah HRD -> bypass
        // - Jika target employee posisinya President atau VPD -> bypass
        $isHrd = isset($user->role) && strtoupper($user->role) === 'HRD';
        $targetPosition = strtoupper(trim((string) $emp->position));
        $targetIsExempt = in_array($targetPosition, ['PRESIDENT', 'VPD'], true);

        if (!$isHrd && !$targetIsExempt) {
            // Authorization: bawahan saya ATAU pernah menunjuk saya sebagai PIC ATAU diri sendiri
            $subordinateIds = $this->getSubordinatesFromStructure($me)->pluck('id')->toArray();
            $asPicIds = Ipp::where('pic_review_id', $me->id)->pluck('employee_id')->unique()->toArray();

            if (
                !in_array($employeeId, $subordinateIds, true) &&
                !in_array($employeeId, $asPicIds, true) &&
                $employeeId !== (int) $me->id
            ) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
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

        $header         = null;      // draft/aktif yang bisa diedit
        $locked         = false;     // default: tidak ngunci
        $commentsCount  = 0;
        $hasApproved    = false;
        $approvedHeader = null;

        if ($empId) {
            // Cari IPP yang masih bisa diedit (bukan approved)
            $ipp = Ipp::where('employee_id', $empId)
                ->where('on_year', $year)
                ->where('status', '!=', 'approved')
                ->first();

            // Cari IPP yang approved (info saja)
            $approved = Ipp::where('employee_id', $empId)
                ->where('on_year', $year)
                ->where('status', 'approved')
                ->first();

            if ($approved) {
                $hasApproved    = true;
                $approvedHeader = [
                    'id'            => $approved->id,
                    'employee_id'   => $approved->employee_id,
                    'pic_review_id' => $approved->pic_review_id,
                    'status'        => 'approved',
                    'summary'       => $approved->summary ?: null,
                    'locked'        => true,
                    // (opsional) tambahkan url jika ada:
                    // 'url_show'   => route('ipp.show', $approved->id),
                ];
                $commentsCount = $approved->comments->count();
            }

            if ($ipp && !$ipp->pic_review_id && $picReviewId) {
                $ipp->update(['pic_review_id' => $picReviewId]);
            }

            if ($ipp) {
                $locked = in_array($ipp->status, ['submitted', 'checked'], true);

                $points = IppPoint::where('ipp_id', $ipp->id)->orderBy('id')->get();
                foreach ($points as $p) {
                    $item = [
                        'id'         => $p->id,
                        'category'   => $p->category,
                        'activity'   => (string) $p->activity,
                        'target_mid' => (string) $p->target_mid,
                        'target_one' => (string) $p->target_one,
                        'start_date'   => $p->start_date ? substr((string)$p->start_date, 0, 10) : null,
                        'due_date'   => $p->due_date ? substr((string)$p->due_date, 0, 10) : null,
                        'weight'     => (int) $p->weight,
                        'status'     => (string) ($p->status ?? 'draft'),
                    ];
                    if (isset($pointByCat[$p->category])) {
                        $pointByCat[$p->category][] = $item;
                        $summary[$p->category]      += (int) $p->weight;
                    }
                }

                $summary['total'] =
                    $summary['activity_management']
                    + $summary['people_development']
                    + $summary['crp']
                    + $summary['special_assignment'];

                $header = [
                    'id'            => $ipp->id,
                    'employee_id'   => $ipp->employee_id,
                    'pic_review_id' => $ipp->pic_review_id,
                    'status'        => (string) $ipp->status,
                    'summary'       => $ipp->summary ?: $summary,
                    'locked'        => $locked,
                ];

                $commentsCount = $ipp->comments->count();
            }
        }

        return response()->json([
            'identitas'       => $identitas,
            'ipp'             => $header,
            'points'          => $pointByCat,
            'cap'             => self::CAP,
            'locked'          => $locked,
            'comments_count'  => $commentsCount,
            'has_approved'    => $hasApproved,
            'approved'        => $approvedHeader,
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
            'point.start_date' => ['required', 'date'],
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
                    'start_date' => $p['start_date'],
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
                    'start_date' => $p['start_date'],
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
                'status'       => 'submitted',
                'summary'      => $summary,
                'submitted_at' => now(),
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

    /** ====== APPROVAL / REVISE ====== */
    public function approval(Request $request, $company = null)
    {
        $title  = 'Approval';
        $filter = (string) $request->query('filter', 'all');

        return view('website.approval.ipp.index', [
            'title'   => $title,
            'company' => $company,
            'filter'  => $filter,
        ]);
    }

    /**
     * Data bawahan untuk halaman approval.
     * HRD/President/VPD => semua employee (boleh filter posisi & company).
     * Lainnya => bawahan struktur ∪ yang menunjuk saya sebagai PIC.
     * Selalu taruh baris "saya sendiri" paling atas.
     */
    public function approvalJson(Request $request)
    {
        try {
            $user = auth()->user();
            $me   = $user->employee;

            if (!$me) {
                return response()->json([
                    'data' => [],
                    'meta' => ['total' => 0, 'page' => 1, 'per_page' => 10, 'last_page' => 1]
                ]);
            }

            // Query params
            $year   = (string) $request->query('filter_year', now()->format('Y'));
            $search = trim((string) $request->query('search', ''));
            $page   = max(1, (int) $request->query('page', 1));
            $perPage = min(100, max(5, (int) $request->query('per_page', 10)));

            // Levels (guard if null / methods not exist)
            $checkLevel   = method_exists($me, 'getFirstApproval') ? $me->getFirstApproval() : null;
            $approveLevel = method_exists($me, 'getFinalApproval') ? $me->getFinalApproval() : null;

            $subCheckIds = collect();
            $subApproveIds = collect();

            if ($checkLevel && method_exists($me, 'getSubordinatesByLevel')) {
                $subCheckIds = $me->getSubordinatesByLevel($checkLevel)->pluck('id');
            }
            if ($approveLevel && method_exists($me, 'getSubordinatesByLevel')) {
                $subApproveIds = $me->getSubordinatesByLevel($approveLevel)->pluck('id');
            }

            // Helpers: common constraints
            $baseIPP = Ipp::with(['employee.user'])
                ->where('on_year', $year)
                ->where('employee_id', '!=', $me->id) // jangan tampilkan milik saya sendiri
                // skip owner role HRD
                ->whereHas('employee.user', function ($q) {
                    $q->whereRaw('UPPER(role) != ?', ['HRD']);
                })
                // optional search by owner
                ->when($search !== '', function ($q) use ($search) {
                    $q->whereHas('employee', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%")
                            ->orWhere('npk', 'like', "%{$search}%")
                            ->orWhere('position', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
                });

            // === Stage 1: CHECK (subordinates at check level) ===
            $checkIpps = collect();
            if ($subCheckIds->isNotEmpty()) {
                $checkIpps = (clone $baseIPP)
                    ->where('status', 'submitted')
                    ->whereHas('employee', function ($q) use ($subCheckIds) {
                        // IMPORTANT: filter by employees.id (not employee_id)
                        $q->whereIn('id', $subCheckIds->all());
                    })
                    ->get()
                    ->map(function (Ipp $ipp) {
                        $ipp->stage = 'check';
                        return $ipp;
                    });
            }

            // === Stage 2: APPROVE (subordinates at final approval level), excluding ones already in CHECK ===
            $approveIpps = collect();
            if ($subApproveIds->isNotEmpty()) {
                $approveIpps = (clone $baseIPP)
                    ->where('status', 'checked')
                    ->whereHas('employee', function ($q) use ($subApproveIds) {
                        $q->whereIn('id', $subApproveIds->all());
                    })
                    ->whereNotIn('id', $checkIpps->pluck('id')->all())
                    ->get()
                    ->map(function (Ipp $ipp) {
                        $ipp->stage = 'approve';
                        return $ipp;
                    });
            }

            // Merge both stages, latest updated first
            $all = $checkIpps->merge($approveIpps)
                ->sortByDesc(fn(Ipp $x) => $x->updated_at ?? $x->created_at)
                ->values();

            $total = $all->count();

            // Pagination on collection
            $paged = $all->forPage($page, $perPage)->values();

            // Map to rows
            $rows = $paged->map(function (Ipp $ipp, $idx) use ($page, $perPage) {
                $e = $ipp->employee;
                return [
                    'no'        => ($page - 1) * $perPage + $idx + 1,
                    'id'        => $ipp->id,
                    'stage'     => $ipp->stage,            // 'check' or 'approve'
                    'status'    => (string) $ipp->status,  // 'submitted' or 'checked'
                    'on_year'   => (string) $ipp->on_year,
                    'updated_at' => optional($ipp->updated_at)->toDateTimeString(),
                    'employee'  => [
                        'id'        => $e->id,
                        'npk'       => (string) $e->npk,
                        'name'      => (string) $e->name,
                        'company'   => (string) $e->company_name,
                        'position'  => (string) $e->position,
                        'department' => (string) ($e->bagian ?? ''),
                        'grade'     => (string) ($e->grade ?? ''),
                        'role'      => optional($e->user)->role, // for reference (already filtered != HRD)
                    ],
                ];
            });

            return response()->json([
                'data' => $rows,
                'meta' => [
                    'total'     => (int) $total,
                    'page'      => (int) $page,
                    'per_page'  => (int) $perPage,
                    'last_page' => (int) max(1, (int) ceil($total / $perPage)),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'data' => [],
                'meta' => ['total' => 0, 'page' => 1, 'per_page' => 10, 'last_page' => 1],
                'error' => 'Internal error',
            ], 500);
        }
    }

    public function approve(int $id): JsonResponse
    {
        try {
            $ipp  = Ipp::findOrFail($id);
            $from = strtolower((string) $ipp->status);
            $to   = null;

            if ($from === 'submitted') {
                $to = 'checked';
            } elseif ($from === 'checked') {
                $to = 'approved';
            } elseif ($from === 'approved') {
                return response()->json([
                    'message' => 'IPP already approved (final state).',
                    'id'      => $ipp->id,
                    'status'  => $ipp->status,
                ], 409);
            } else {
                return response()->json([
                    'message' => "Current status '{$ipp->status}' is not eligible for approval.",
                    'id'      => $ipp->id,
                    'status'  => $ipp->status,
                ], 422);
            }

            $updatePoints = 0;

            DB::transaction(function () use ($ipp, $to, &$updatePoints) {
                // siapkan payload update status + cap waktu sesuai transisi
                $updates = ['status' => $to];

                if ($to === 'checked' && is_null($ipp->checked_at)) {
                    $updates['checked_at'] = now();
                    $updates['checked_by'] = auth()->user()->employee->id ?? null;
                } elseif ($to === 'approved' && is_null($ipp->approved_at)) {
                    $updates['approved_at'] = now();
                    $updates['approved_by'] = auth()->user()->employee->id ?? null;
                }

                $ipp->update($updates);

                // sinkronkan status semua point
                $updatePoints = IppPoint::where('ipp_id', $ipp->id)
                    ->update(['status' => $to]);
            });

            return response()->json([
                'message'        => 'IPP status updated.',
                'id'             => $ipp->id,
                'from'           => $from,
                'to'             => $to,
                'points_updated' => $updatePoints,
                'updated_at'     => optional($ipp->updated_at)->toDateTimeString(),
                'submitted_at'   => optional($ipp->submitted_at)->toDateTimeString(),
                'checked_at'     => optional($ipp->checked_at)->toDateTimeString(),
                'approved_at'    => optional($ipp->approved_at)->toDateTimeString(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'IPP not found.'], 404);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to approve IPP. Please try again.'], 500);
        }
    }


    public function revise(Request $request, int $id)
    {
        try {
            $data = $request->validate([
                'note' => 'required|string|max:1000',
            ]);

            $empId = $request->user()->employee->id;

            $ipp = Ipp::findOrFail($id);
            $from = strtolower((string) $ipp->status);
            $to   = "revised";

            $updatePoints = 0;
            DB::transaction(function () use ($data, $empId, $ipp, $from, $to, &$updatePoints) {
                IppComment::create([
                    'ipp_id'      => $ipp->id,
                    'employee_id' => $empId,
                    'status_from' => $from,
                    'status_to'   => $to,
                    'comment'     => $data['note'],
                ]);

                $ipp->update(['status' => $to]);
                $updatePoints = IppPoint::where('ipp_id', $ipp->id)
                    ->update(['status' => $to]);
            });

            return response()->json([
                'message'        => 'IPP revised and comment saved.',
                'id'             => $ipp->id,
                'from'           => $from,
                'to'             => $to,
                'points_updated' => $updatePoints,
                'updated_at'     => optional($ipp->updated_at)->toDateTimeString(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'IPP not found.',
            ], 404);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Failed to revise IPP. Please try again.',
            ], 500);
        }
    }

    public function getComment(Ipp $ipp)
    {
        $comments = $ipp->comments()
            ->with('employee:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($c) {
                return [
                    'id'          => $c->id,
                    'employee'    => $c->employee,
                    'comment'     => $c->comment,
                    'status_from' => $c->status_from,
                    'status_to'   => $c->status_to,
                    'created_at'  => optional($c->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i'),
                ];
            });

        return response()->json(['data' => $comments]);
    }


    /** ====== EXPORT EXCEL DAN PDF ====== */
    public function exportExcel(?int $id = null)
    {
        try {
            // (opsional) beri waktu lebih panjang jika dataset besar
            @set_time_limit(120);

            $user    = auth()->user();
            $authEmp = $user->employee;

            abort_if(!$authEmp, 403, 'Employee not found for this account.');

            $ipp = $id
                ? Ipp::find($id)
                : Ipp::where('employee_id', $authEmp->id)->where('on_year', now()->format('Y'))->first();
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
                    $outlineMedium("{$R_DUE_FROM}{$r}:{$R_DUE_TO}{$r}");
                }

                // Tambah offset baris sisipan
                $offset += max(0, $n - 1);
            }

            // ===========================
            //  SIGNATURES BY STATUS
            // ===========================

            // Helper: render tanda tangan ke range + tanggal
            $renderSignature = function (string $rect, string $anchor, ?string $imgPath, ?string $date, string $dateRect) use ($sheet) {
                // merge cell area utk gambar & tanggal
                $sheet->mergeCells($rect);
                $sheet->mergeCells($dateRect);

                // tanggal (kalau ada)
                if (!empty($date)) {
                    $sheet->setCellValue(explode(':', $dateRect)[0], $date);
                    $sheet->getStyle($dateRect)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => false,
                        ],
                        'font' => ['name' => 'Tahoma', 'size' => 12],
                    ]);
                }

                // gambar tanda tangan (kalau ada)
                if ($imgPath && is_file($imgPath)) {
                    $drw = new Drawing();
                    $drw->setName('Signature');
                    $drw->setDescription('Signature');
                    $drw->setPath($imgPath);
                    $drw->setCoordinates($anchor);
                    $drw->setResizeProportional(true);
                    $drw->setHeight(165);
                    $drw->setOffsetX(49);
                    $drw->setOffsetY(-10);
                    $drw->setWorksheet($sheet);
                }
            };

            // Helper: resolve path signature dari employee
            $resolveSignaturePath = function (?Employee $emp): ?string {
                if (!$emp) return null;

                // Contoh 1: kolom signature_path di storage/app/public
                if (!empty($emp->signature_path)) {
                    $p = storage_path('app/public/' . ltrim($emp->signature_path, '/'));
                    if (is_file($p)) return $p;
                }

                // Contoh 2: fallback folder public/storage/signatures/{id}.png
                $p2 = public_path('storage/signatures/' . $emp->id . '.png');
                if (is_file($p2)) return $p2;

                return null;
            };

            // Area (silakan sesuaikan dgn template jika perlu)
            $AREAS = [
                'approved' => [ // kiri
                    'rect'   => 'H34:Q35',
                    'anchor' => 'H34',
                    'date'   => 'I42:S42',
                ],
                'checked' => [  // tengah
                    'rect'   => 'V34',
                    'anchor' => 'V34',
                    'date'   => 'U42:AG42',
                ],
                'employee' => [ // kanan (tetap)
                    'rect'   => 'AK34',
                    'anchor' => 'AK34',
                    'date'   => 'AJ42:AU42'
                ],
            ];

            // Ambil entity & tanggal
            $status = strtolower((string)$ipp->status);

            // Relasi untuk checked/approved by (pastikan sudah ada di model)
            $checkedByEmp   = method_exists($ipp, 'checkedBy')  ? $ipp->checkedBy : null;
            $approvedByEmp  = method_exists($ipp, 'approvedBy') ? $ipp->approvedBy : null;

            // Tanggal (pakai last_submitted_at jika ada)
            $submitAt   = $ipp->last_submitted_at ?? $ipp->submitted_at;
            $checkedAt  = $ipp->checked_at;
            $approvedAt = $ipp->approved_at;

            $submitDate   = $submitAt   ? substr((string)$submitAt,   0, 10) : null;
            $checkedDate  = $checkedAt  ? substr((string)$checkedAt,  0, 10) : null;
            $approvedDate = $approvedAt ? substr((string)$approvedAt, 0, 10) : null;

            // Resolve file path tanda tangan
            $ownerSig    = $resolveSignaturePath($owner);
            $checkedSig  = $resolveSignaturePath($checkedByEmp);
            $approvedSig = $resolveSignaturePath($approvedByEmp);

            // Render sesuai status
            if ($status === 'submitted') {
                // hanya employee
                $renderSignature($AREAS['employee']['rect'], $AREAS['employee']['anchor'], $ownerSig, $submitDate, $AREAS['employee']['date']);
            } elseif ($status === 'checked') {
                // employee + checked_by
                $renderSignature($AREAS['employee']['rect'], $AREAS['employee']['anchor'], $ownerSig,   $submitDate,  $AREAS['employee']['date']);
                $renderSignature($AREAS['checked']['rect'],  $AREAS['checked']['anchor'],  $checkedSig, $checkedDate, $AREAS['checked']['date']);
            } elseif ($status === 'approved') {
                // employee + checked_by + approved_by
                $renderSignature($AREAS['employee']['rect'],  $AREAS['employee']['anchor'],  $ownerSig,    $submitDate,   $AREAS['employee']['date']);
                $renderSignature($AREAS['checked']['rect'],   $AREAS['checked']['anchor'],   $checkedSig,  $checkedDate,  $AREAS['checked']['date']);
                $renderSignature($AREAS['approved']['rect'],  $AREAS['approved']['anchor'],  $approvedSig, $approvedDate, $AREAS['approved']['date']);
            }

            // ===========================
            //  OUTPUT FILE
            // ===========================
            $fileName = 'IPP_' . $identitas['on_year'] . '_' . Str::slug((string)($owner->name ?? 'user')) . '.xlsx';
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

            // ===== SIGNATURES (build data URI + tanggal berdasarkan status) =====
            $resolveSignaturePath = function (?Employee $emp): ?string {
                if (!$emp) return null;

                if (!empty($emp->signature_path)) {
                    $p = storage_path('app/public/' . ltrim($emp->signature_path, '/'));
                    if (is_file($p)) return $p;
                }
                $p2 = public_path('storage/signatures/' . $emp->id . '.png');
                if (is_file($p2)) return $p2;

                return null;
            };

            $toDataUri = function (?string $path): ?string {
                if (!$path || !is_file($path)) return null;
                $mime = 'image/' . strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'png');
                $data = base64_encode(@file_get_contents($path));
                return $data ? "data:{$mime};base64,{$data}" : null;
            };

            $status = strtolower((string) $ipp->status);

            $checkedBy   = method_exists($ipp, 'checkedBy')  ? $ipp->checkedBy  : ($ipp->checked_by  ? Employee::find($ipp->checked_by)  : null);
            $approvedBy  = method_exists($ipp, 'approvedBy') ? $ipp->approvedBy : ($ipp->approved_by ? Employee::find($ipp->approved_by) : null);

            $submitAt    = $ipp->last_submitted_at ?? $ipp->submitted_at;
            $checkedAt   = $ipp->checked_at;
            $approvedAt  = $ipp->approved_at;

            $sig = [
                // Superior of Superior (approved_by)
                'approved' => [
                    'img'  => in_array($status, ['approved'], true) ? $toDataUri($resolveSignaturePath($approvedBy)) : null,
                    'date' => $approvedAt ? substr((string)$approvedAt, 0, 10) : null,
                    'name' => $approvedBy?->name,
                ],
                // Superior (checked_by)
                'checked' => [
                    'img'  => in_array($status, ['checked', 'approved'], true) ? $toDataUri($resolveSignaturePath($checkedBy)) : null,
                    'date' => $checkedAt ? substr((string)$checkedAt, 0, 10) : null,
                    'name' => $checkedBy?->name,
                ],
                // Employee (owner)
                'employee' => [
                    'img'  => in_array($status, ['submitted', 'checked', 'approved'], true) ? $toDataUri($resolveSignaturePath($owner)) : null,
                    'date' => $submitAt ? substr((string)$submitAt, 0, 10) : null,
                    'name' => $owner?->name,
                ],
            ];

            // 7) Logo
            $logoPath = public_path('assets/media/logos/aisin.png');
            $logoSrc  = null;
            if (is_file($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoSrc = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($logoPath));
            }

            // 8) Render Blade → PDF
            $pdf = Pdf::loadView('website.ipp.pdf', [
                'ipp'       => $ipp,
                'owner'     => $owner,
                'identitas' => $identitas,
                'grouped'   => $grouped,
                'summary'   => $summary,
                'logo'      => $logoSrc,
                'sig'       => $sig,      // <— kirim signatures
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
