<?php

namespace App\Http\Controllers;

use App\Helpers\ApprovalHelper;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanItem;
use App\Models\Employee;
use App\Models\Ipp;
use App\Models\IppApprovalStep;
use App\Models\IppPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ActivityPlanController extends Controller
{
    public function index(Request $request)
    {
        return view('website.activity_plan.index', ['title' => 'Activity Plan']);
    }

    public function init(Request $request)
    {
        $ippId   = (int) $request->query('ipp_id');
        $pointId = (int) $request->query('point_id');
        $user    = $request->user();
        $emp     = $user?->employee;

        if (!$ippId || ! $emp) {
            return response()->json(['message' => 'ipp_id tidak valid atau employee tidak ditemukan.'], 422);
        }

        $ipp = Ipp::with('employee')
            ->where('id', $ippId)
            ->where('employee_id', $emp->id)
            ->first();

        if (! $ipp) {
            return response()->json(['message' => 'IPP tidak ditemukan / bukan milik Anda.'], 404);
        }

        // header plan (draft jika blm ada)
        $plan = ActivityPlan::firstOrCreate(
            ['ipp_id' => $ipp->id],
            [
                'employee_id' => $ipp->employee_id,
                'fy_start_year' => (int) $ipp->on_year,
                'division' => $ipp->division,
                'department' => $ipp->department,
                'section' => $ipp->section,
                'form_no' => $ipp->no_form ?? null,
                'status' => 'draft',
            ]
        );

        if (! $pointId) {
            return response()->json(['message' => 'point_id wajib diisi untuk single-point mode.'], 422);
        }

        $point = IppPoint::query()
            ->where('id', $pointId)
            ->where('ipp_id', $ipp->id)
            ->first(['id', 'ipp_id', 'category', 'activity', 'target_mid', 'target_one', 'start_date', 'due_date', 'weight', 'status']);

        if (! $point) {
            return response()->json(['message' => 'IPP Point tidak ditemukan / bukan milik IPP ini.'], 404);
        }

        $items = ActivityPlanItem::with([
            'pic:id,name,npk',
            'ippPoint:id,activity,category,start_date,due_date',
        ])
            ->where('activity_plan_id', $plan->id)
            ->where('ipp_point_id', $point->id)
            ->orderBy('id')
            ->get();

        $employees = Employee::query()->orderBy('name')->get(['id', 'name', 'npk']);

        return response()->json([
            'ipp' => [
                'id' => $ipp->id,
                'nama' => $ipp->nama,
                'on_year' => $ipp->on_year,
                'status' => $ipp->status,
            ],
            'plan' => [
                'id' => $plan->id,
                'ipp_id' => $plan->ipp_id,
                'status' => $plan->status,
                'form_no' => $plan->form_no,
                'fy_start_year' => $plan->fy_start_year,
                'division' => $plan->division,
                'department' => $plan->department,
                'section' => $plan->section,
            ],
            'points' => [$point],
            'focus_point' => [
                'id' => $point->id,
                'label' => sprintf(
                    '[%s] %s — %s→%s',
                    $point->category,
                    $point->activity,
                    optional($point->start_date)->toDateString(),
                    optional($point->due_date)->toDateString()
                ),
            ],
            'items' => $items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'ipp_point_id' => $it->ipp_point_id,
                    'kind_of_activity' => $it->kind_of_activity,
                    'target' => $it->target,
                    'pic_employee_id' => $it->pic_employee_id,
                    'schedule_mask' => (int) $it->schedule_mask,
                    'cached_category' => $it->cached_category,
                    'cached_activity' => $it->cached_activity,
                    'cached_start_date' => optional($it->cached_start_date)->toDateString() ?: $it->cached_start_date,
                    'cached_due_date' => optional($it->cached_due_date)->toDateString() ?: $it->cached_due_date,
                    'pic' => $it->pic ? ['id' => $it->pic->id, 'name' => $it->pic->name, 'npk' => $it->pic->npk] : null,
                    'ipp_point' => $it->ippPoint ? [
                        'id' => $it->ippPoint->id,
                        'category' => $it->ippPoint->category,
                        'activity' => $it->ippPoint->activity,
                        'start_date' => optional($it->ippPoint->start_date)->toDateString(),
                        'due_date' => optional($it->ippPoint->due_date)->toDateString(),
                    ] : null,
                ];
            }),
            'employees' => $employees,
        ]);
    }

    /** Create / Edit item AP – validasi: tanggal item ⊆ tanggal point; months ⊆ rentang tanggal item */
    public function storeItem(Request $request)
    {
        $user = $request->user();
        $emp = $user?->employee;
        $ippId = (int) $request->query('ipp_id');
        $pointId = (int) $request->query('point_id'); // <<— fokus point

        if (! $emp) {
            return response()->json(['message' => 'Employee tidak ditemukan.'], 422);
        }
        if (! $pointId) {
            return response()->json(['message' => 'point_id wajib diisi.'], 422);
        }

        $v = validator($request->all(), [
            'mode' => ['required', Rule::in(['create', 'edit'])],
            'row_id' => ['nullable', 'integer'],
            'ipp_point_id' => ['required', 'integer'],
            'kind_of_activity' => ['required', 'string', 'max:255'],
            'target' => ['nullable', 'string'],
            'pic_employee_id' => ['required', 'integer', 'exists:employees,id'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d'],
            'months' => ['array', 'min:1'],
            'months.*' => ['string', Rule::in($this->months())],
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        // ipp milik user
        $ipp = Ipp::where('id', $ippId ?: $request->input('ipp_id'))
            ->where('employee_id', $emp->id)->first();
        if (! $ipp) {
            return response()->json(['message' => 'IPP tidak ditemukan / bukan milik Anda.'], 404);
        }

        // header plan
        $plan = ActivityPlan::firstOrCreate(
            ['ipp_id' => $ipp->id],
            [
                'employee_id' => $ipp->employee_id,
                'fy_start_year' => (int) $ipp->on_year,
                'division' => $ipp->division,
                'department' => $ipp->department,
                'section' => $ipp->section,
                'form_no' => $ipp->no_form ?? null,
                'status' => 'draft',
            ]
        );

        // point fokus (harus sama dgn ipp_point_id dari body)
        $ippPointId = (int) $request->input('ipp_point_id');
        if ($ippPointId !== $pointId) {
            return response()->json(['message' => 'ipp_point_id tidak sesuai dengan point_id halaman ini.'], 422);
        }

        $point = IppPoint::where('id', $pointId)
            ->where('ipp_id', $ipp->id)
            ->first();
        if (! $point) {
            return response()->json(['message' => 'IPP Point tidak ditemukan / tidak sesuai IPP.'], 404);
        }

        // ——— validasi tanggal item ⊆ FY & ⊆ point ———
        $fyStartYear = (int) $ipp->on_year;
        [$fyStart, $fyEnd] = $this->fiscalBounds($fyStartYear);

        $pStart = Carbon::parse($point->start_date)->startOfDay();
        $pDue = Carbon::parse($point->due_date)->endOfDay();
        if ($pDue->lt($pStart)) {
            return response()->json(['message' => 'Data IPP Point tidak valid: Due < Start.'], 422);
        }

        $iStart = Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay();
        $iDue = Carbon::createFromFormat('Y-m-d', $request->input('due_date'))->endOfDay();
        if ($iDue->lt($iStart)) {
            return response()->json(['message' => 'Start Date item tidak boleh setelah Due Date item.'], 422);
        }

        if ($iStart->lt($fyStart) || $iStart->gt($fyEnd) || $iDue->lt($fyStart) || $iDue->gt($fyEnd)) {
            return response()->json(['message' => "Tanggal item harus dalam periode fiscal Apr {$fyStartYear} – Mar " . ($fyStartYear + 1) . '.'], 422);
        }
        if ($iStart->lt($pStart) || $iDue->gt($pDue)) {
            return response()->json(['message' => 'Tanggal item harus berada di dalam rentang Start–Due IPP Point.'], 422);
        }

        // months ⊆ rentang tanggal item
        $months = $request->input('months', []);
        $allowed = $this->monthIndicesInRange($iStart, $iDue, $fyStartYear);
        $selected = array_map(fn($m) => $this->monthIndex($m), $months);
        foreach ($selected as $idx) {
            if (! in_array($idx, $allowed, true)) {
                return response()->json(['message' => 'Schedule bulan harus berada di dalam rentang Start–Due item.'], 422);
            }
        }
        $mask = $this->monthsToMask($months);

        // cache: kategori & activity dari point + tanggal ITEM
        $cache = [
            'cached_category' => $point->category,
            'cached_activity' => $point->activity,
            'cached_start_date' => $iStart->toDateString(),
            'cached_due_date' => $iDue->toDateString(),
        ];

        try {
            DB::beginTransaction();
            if ($request->input('mode') === 'edit') {
                $item = ActivityPlanItem::where('id', $request->input('row_id'))
                    ->where('activity_plan_id', $plan->id)
                    ->first();
                if (! $item) {
                    DB::rollBack();

                    return response()->json(['message' => 'Item tidak ditemukan.'], 404);
                }
                $item->update(array_merge([
                    'ipp_point_id' => $point->id,
                    'kind_of_activity' => $request->input('kind_of_activity'),
                    'target' => $request->input('target'),
                    'pic_employee_id' => (int) $request->input('pic_employee_id'),
                    'schedule_mask' => $mask,
                ], $cache));
            } else {
                $item = ActivityPlanItem::create(array_merge([
                    'activity_plan_id' => $plan->id,
                    'ipp_point_id' => $point->id,
                    'kind_of_activity' => $request->input('kind_of_activity'),
                    'target' => $request->input('target'),
                    'pic_employee_id' => (int) $request->input('pic_employee_id'),
                    'schedule_mask' => $mask,
                ], $cache));
            }

            $item->load(['pic:id,name,npk', 'ippPoint:id,activity,category,start_date,due_date']);
            DB::commit();

            return response()->json([
                'message' => 'Draft tersimpan.',
                'item' => [
                    'id' => $item->id,
                    'ipp_point_id' => $item->ipp_point_id,
                    'kind_of_activity' => $item->kind_of_activity,
                    'target' => $item->target,
                    'pic_employee_id' => $item->pic_employee_id,
                    'schedule_mask' => (int) $item->schedule_mask,
                    'cached_category' => $item->cached_category,
                    'cached_activity' => $item->cached_activity,
                    'cached_start_date' => optional($item->cached_start_date)->toDateString() ?: $item->cached_start_date,
                    'cached_due_date' => optional($item->cached_due_date)->toDateString() ?: $item->cached_due_date,
                    'pic' => $item->pic ? ['id' => $item->pic->id, 'name' => $item->pic->name, 'npk' => $item->pic->npk] : null,
                    'ipp_point' => $item->ippPoint ? [
                        'id' => $item->ippPoint->id,
                        'category' => $item->ippPoint->category,
                        'activity' => $item->ippPoint->activity,
                        'start_date' => optional($item->ippPoint->start_date)->toDateString(),
                        'due_date' => optional($item->ippPoint->due_date)->toDateString(),
                    ] : null,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            DB::rollBack();

            return response()->json(['message' => 'Gagal menyimpan item.'], 500);
        }
    }

    public function destroyItem(Request $request, ActivityPlanItem $item)
    {
        $user = $request->user();
        $emp = $user?->employee;
        if (! $emp) {
            return response()->json(['message' => 'Employee tidak ditemukan.'], 422);
        }

        $plan = ActivityPlan::where('id', $item->activity_plan_id)->first();
        if (! $plan) {
            return response()->json(['message' => 'Activity Plan tidak ditemukan.'], 404);
        }

        $ipp = Ipp::where('id', $plan->ipp_id)->where('employee_id', $emp->id)->first();
        if (! $ipp) {
            return response()->json(['message' => 'Tidak diizinkan menghapus item ini.'], 403);
        }

        try {
            $item->delete();

            return response()->json(['message' => 'Item dihapus.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal menghapus item.'], 500);
        }
    }

    public function submitAll(Request $request)
    {
        $user = $request->user();
        $emp  = $user?->employee;

        $ippId = (int) ($request->query('ipp_id') ?: $request->input('ipp_id'));

        if (! $emp) {
            return response()->json(['message' => 'Employee tidak ditemukan.'], 422);
        }

        // ======================
        // Ambil IPP
        // ======================
        $ippQuery = Ipp::with('points')
            ->where('employee_id', $emp->id);

        if ($ippId) {
            $ippQuery->where('id', $ippId);
        } else {
            $ippQuery->where('on_year', now()->format('Y'));
        }

        $ipp = $ippQuery->first();

        if (! $ipp) {
            return response()->json(['message' => 'IPP tidak ditemukan.'], 404);
        }

        // ======================
        // Ambil Activity Plan
        // ======================
        $plan = ActivityPlan::with('items')
            ->where('ipp_id', $ipp->id)
            ->first();

        if (! $plan) {
            return response()->json(['message' => 'Activity Plan belum dibuat.'], 422);
        }

        $ipp->loadMissing('points');
        $plan->loadMissing('items');

        // =====================================================
        // 1️⃣ VALIDASI KATEGORI → HARUS ADA IPP POINT
        // =====================================================
        $caps = [
            'activity_management' => 70,
            'people_development'  => 10,
            'crp'                 => 10,
            'special_assignment'  => 10,
        ];

        if ($ipp->points->isEmpty()) {
            return response()->json(['message' => 'Tambahkan minimal satu IPP Point.'], 422);
        }

        $pointsByCategory = $ipp->points->groupBy('category');

        $missingCategories = collect(array_keys($caps))
            ->filter(fn($cat) => ($pointsByCategory->get($cat)?->count() ?? 0) === 0)
            ->values();

        if ($missingCategories->isNotEmpty()) {
            return response()->json([
                'message' => 'Kategori berikut belum memiliki IPP Point: ' . $missingCategories->implode(', ')
            ], 422);
        }

        // =====================================================
        // 2️⃣ VALIDASI CAP & TOTAL BOBOT
        // =====================================================
        $groupedWeight = $pointsByCategory->map(fn($rows) => (float) $rows->sum('weight'));

        foreach ($caps as $cat => $cap) {
            if (($groupedWeight[$cat] ?? 0) > $cap) {
                return response()->json(['message' => "Bobot kategori {$cat} melebihi cap {$cap}%."], 422);
            }
        }

        if ((int) $ipp->points->sum('weight') !== 100) {
            return response()->json(['message' => 'Total bobot IPP harus tepat 100%.'], 422);
        }

        // =====================================================
        // 3️⃣ VALIDASI ACTIVITY PLAN ITEM ↔ IPP POINT
        // =====================================================
        if ($plan->items->isEmpty()) {
            return response()->json(['message' => 'Tambahkan minimal satu Activity Plan item.'], 422);
        }

        $ippPointIds  = $ipp->points->pluck('id')->values();
        $itemPointIds = $plan->items
            ->pluck('ipp_point_id')
            ->filter()
            ->unique()
            ->values();

        // item tidak boleh pakai point luar IPP
        $foreignPointIds = $itemPointIds->diff($ippPointIds);
        if ($foreignPointIds->isNotEmpty()) {
            return response()->json([
                'message' => 'Terdapat Activity Plan item yang tidak berasal dari IPP ini.'
            ], 422);
        }

        // setiap IPP point harus punya minimal 1 item
        $missingPointIds = $ippPointIds->diff($itemPointIds);
        if ($missingPointIds->isNotEmpty()) {
            $missingPoints = $ipp->points
                ->whereIn('id', $missingPointIds)
                ->map(fn($p) => "{$p->category} - {$p->activity}")
                ->values();

            return response()->json([
                'message' => 'Masih ada IPP Point yang belum memiliki Activity Plan item.',
                'detail'  => $missingPoints
            ], 422);
        }

        // =====================================================
        // 4️⃣ VALIDASI DETAIL ITEM (TANGGAL, PIC, SCHEDULE)
        // =====================================================
        [$fyStart, $fyEnd] = $this->fiscalBounds((int) $ipp->on_year);

        foreach ($plan->items as $it) {
            if (! $it->kind_of_activity) {
                return response()->json(['message' => 'Kind of activity wajib diisi.'], 422);
            }

            if (! $it->pic_employee_id) {
                return response()->json(['message' => 'PIC wajib dipilih.'], 422);
            }

            $pt = $ipp->points->firstWhere('id', $it->ipp_point_id);

            $pStart = Carbon::parse($pt->start_date)->startOfDay();
            $pDue   = Carbon::parse($pt->due_date)->endOfDay();

            $iStart = Carbon::parse($it->cached_start_date ?? $pt->start_date)->startOfDay();
            $iDue   = Carbon::parse($it->cached_due_date ?? $pt->due_date)->endOfDay();

            if ($iDue->lt($iStart)) {
                return response()->json(['message' => 'Ada item dengan Due < Start.'], 422);
            }

            if ($iStart->lt($fyStart) || $iDue->gt($fyEnd)) {
                return response()->json(['message' => 'Ada item di luar periode fiscal IPP.'], 422);
            }

            if ($iStart->lt($pStart) || $iDue->gt($pDue)) {
                return response()->json(['message' => 'Ada item memiliki tanggal di luar rentang IPP Point.'], 422);
            }

            $allowedPointMonths = $this->monthIndicesInRange($pStart, $pDue, (int) $ipp->on_year);
            $selectedMonths     = $this->maskToMonthIndices((int) $it->schedule_mask);

            foreach ($selectedMonths as $idx) {
                if (! in_array($idx, $allowedPointMonths, true)) {
                    return response()->json([
                        'message' => 'Ada item dengan schedule di luar rentang Start–Due IPP Point.'
                    ], 422);
                }
            }
        }

        // =====================================================
        // 5️⃣ SUBMIT (TRANSACTION)
        // =====================================================
        try {
            DB::transaction(function () use ($ipp, $plan) {
                IppPoint::where('ipp_id', $ipp->id)->update(['status' => 'submitted']);

                $ipp->update([
                    'status'       => 'submitted',
                    'submitted_at' => now(),
                ]);

                ActivityPlanItem::where('activity_plan_id', $plan->id)
                    ->update(['status' => 'submitted']);

                $plan->update([
                    'status'       => 'submitted',
                    'submitted_at' => now(),
                ]);

                if (! $ipp->steps()->exists()) {
                    $this->seedStepForIpp($ipp);
                }
            });

            return response()->json([
                'message' => 'IPP + Activity Plan berhasil disubmit.'
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Gagal submit gabungan.'
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $ippId = (int) $request->query('ipp_id');
        $user = $request->user();
        $emp = $user?->employee;
        if (! $ippId || ! $emp) {
            return back()->with('error', 'ipp_id tidak valid.');
        }
        $ipp = Ipp::where('id', $ippId)->where('employee_id', $emp->id)->first();
        if (! $ipp) {
            return back()->with('error', 'IPP tidak ditemukan / bukan milik Anda.');
        }

        // $fname='IPP-ActivityPlan-'.$ipp->on_year.'-'.$emp->name.'.xlsx';
        // return Excel::download(new IppActivityPlanExport($ipp->id), $fname);
        return back()->with('error', 'Export belum diimplementasikan (IppActivityPlanExport).');
    }

    public function showByPoint(Request $request, int $point)
    {
        return view('website.activity_plan.index', [
            'title' => 'Activity Plan',
            'ippId' => (int) $request->query('ipp_id'),
            'pointId' => $point,
        ]);
    }

    public function initByPoint(Request $request, IppPoint $point)
    {
        $ippId = (int) $request->query('ipp_id');
        $user = $request->user();
        $emp = $user?->employee;

        if (! $ippId || ! $emp) {
            return response()->json(['message' => 'ipp_id tidak valid atau employee tidak ditemukan.'], 422);
        }

        $ipp = Ipp::with('employee')
            ->where('id', $ippId)
            ->where('employee_id', $emp->id)
            ->first();
        if (! $ipp) {
            return response()->json(['message' => 'IPP tidak ditemukan / bukan milik Anda.'], 404);
        }
        if ((int) $point->ipp_id !== (int) $ipp->id) {
            return response()->json(['message' => 'IPP Point tidak sesuai dengan IPP.'], 422);
        }

        $plan = ActivityPlan::firstOrCreate(
            ['ipp_id' => $ipp->id],
            [
                'employee_id' => $ipp->employee_id,
                'fy_start_year' => (int) $ipp->on_year,
                'division' => $ipp->division,
                'department' => $ipp->department,
                'section' => $ipp->section,
                'form_no' => $ipp->no_form ?? null,
                'status' => 'draft',
            ]
        );

        $items = ActivityPlanItem::with(['pic:id,name,npk', 'ippPoint:id,activity,category,start_date,due_date'])
            ->where('activity_plan_id', $plan->id)
            ->where('ipp_point_id', $point->id)
            ->orderBy('id')
            ->get();

        $employees = $this->subordinateEmployee($request);

        $pointPayload = [
            'id' => $point->id,
            'ipp_id' => $point->ipp_id,
            'category' => $point->category,
            'activity' => $point->activity,
            'target_mid' => $point->target_mid,
            'target_one' => $point->target_one,
            'weight' => $point->weight,
            'cached_start_date' => optional($point->cached_start_date)->toDateString() ?: ($point->cached_start_date ?? optional($point->start_date)->toDateString()),
            'cached_due_date' => optional($point->cached_due_date)->toDateString() ?: ($point->cached_due_date ?? optional($point->due_date)->toDateString()),
            'start_date' => optional($point->start_date)->toDateString(),
            'due_date' => optional($point->due_date)->toDateString(),
        ];

        return response()->json([
            'ipp' => ['id' => $ipp->id, 'nama' => $ipp->nama, 'on_year' => $ipp->on_year, 'status' => $ipp->status],
            'plan' => [
                'id' => $plan->id,
                'ipp_id' => $plan->ipp_id,
                'status' => $plan->status,
                'form_no' => $plan->form_no,
                'fy_start_year' => $plan->fy_start_year,
                'division' => $plan->division,
                'department' => $plan->department,
                'section' => $plan->section,
            ],
            'point' => $pointPayload,
            'items' => $items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'ipp_point_id' => $it->ipp_point_id,
                    'kind_of_activity' => $it->kind_of_activity,
                    'target' => $it->target,
                    'pic_employee_id' => $it->pic_employee_id,
                    'schedule_mask' => (int) $it->schedule_mask,
                    'cached_category' => $it->cached_category,
                    'cached_activity' => $it->cached_activity,
                    'cached_start_date' => optional($it->cached_start_date)->toDateString() ?: $it->cached_start_date,
                    'cached_due_date' => optional($it->cached_due_date)->toDateString() ?: $it->cached_due_date,
                    'pic' => $it->pic ? ['id' => $it->pic->id, 'name' => $it->pic->name, 'npk' => $it->pic->npk] : null,
                    'ipp_point' => $it->ippPoint ? [
                        'id' => $it->ippPoint->id,
                        'category' => $it->ippPoint->category,
                        'activity' => $it->ippPoint->activity,
                        'start_date' => optional($it->ippPoint->start_date)->toDateString(),
                        'due_date' => optional($it->ippPoint->due_date)->toDateString(),
                    ] : null,
                ];
            }),
            'employees' => $employees,
        ]);
    }

    public function storeItemByPoint(Request $request, IppPoint $point)
    {
        $user = $request->user();
        $emp = $user?->employee;
        $ippId = (int) $request->query('ipp_id');

        if (! $emp) {
            return response()->json(['message' => 'Employee tidak ditemukan.'], 422);
        }

        $v = validator($request->all(), [
            'mode'             => ['required', Rule::in(['create', 'edit'])],
            'row_id'           => ['nullable', 'integer'],
            'ipp_point_id'     => ['required', 'integer'],
            'kind_of_activity' => ['required', 'string', 'max:255'],
            'target'           => ['nullable', 'string'],
            'pic_employee_id'  => ['required', 'integer', 'exists:employees,id'],
            'start_date'       => ['required', 'date_format:Y-m-d'],
            'due_date'         => ['required', 'date_format:Y-m-d'],
            'months'           => ['array', 'min:1'],
            'months.*'         => ['string', Rule::in($this->months())],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $ipp = Ipp::where('id', $ippId ?: $request->input('ipp_id'))
            ->where('employee_id', $emp->id)->first();
        if (! $ipp) {
            return response()->json(['message' => 'IPP tidak ditemukan / bukan milik Anda.'], 404);
        }
        if ((int) $point->ipp_id !== (int) $ipp->id) {
            return response()->json(['message' => 'IPP Point tidak sesuai dengan IPP.'], 422);
        }

        $plan = ActivityPlan::firstOrCreate(
            ['ipp_id' => $ipp->id],
            [
                'employee_id' => $ipp->employee_id,
                'fy_start_year' => (int) $ipp->on_year,
                'division' => $ipp->division,
                'department' => $ipp->department,
                'section' => $ipp->section,
                'form_no' => $ipp->no_form ?? null,
                'status' => 'draft',
            ]
        );

        $pStartRaw = $point->cached_start_date ?? $point->start_date;
        $pDueRaw = $point->cached_due_date ?? $point->due_date;

        $pStart = Carbon::parse($pStartRaw)->startOfDay();
        $pDue = Carbon::parse($pDueRaw)->endOfDay();
        if ($pDue->lt($pStart)) {
            return response()->json(['message' => 'Data IPP Point tidak valid: Due < Start.'], 422);
        }

        $iStart = Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay();
        $iDue = Carbon::createFromFormat('Y-m-d', $request->input('due_date'))->endOfDay();
        if ($iDue->lt($iStart)) {
            return response()->json(['message' => 'Start Date item tidak boleh setelah Due Date item.'], 422);
        }

        if ($iStart->lt($pStart) || $iDue->gt($pDue)) {
            return response()->json(['message' => 'Tanggal item harus di dalam rentang Start–Due IPP Point terpilih.'], 422);
        }

        $fyStartYear = (int) ($iStart->month >= 4 ? $iStart->year : $iStart->year - 1);
        $allowed = $this->monthIndicesInRange($iStart, $iDue, $fyStartYear);
        $months = $request->input('months', []);
        $selected = array_map(fn($m) => $this->monthIndex($m), $months);
        foreach ($selected as $idx) {
            if (! in_array($idx, $allowed, true)) {
                return response()->json(['message' => 'Schedule bulan harus berada di dalam rentang Start–Due item.'], 422);
            }
        }
        $mask = $this->monthsToMask($months);

        $cache = [
            'cached_category'   => $point->category,
            'cached_activity'   => $point->activity,
            'cached_start_date' => $iStart->toDateString(),
            'cached_due_date'   => $iDue->toDateString(),
        ];

        try {
            DB::beginTransaction();

            if ($request->input('mode') === 'edit') {
                $item = ActivityPlanItem::where('id', $request->input('row_id'))
                    ->where('activity_plan_id', $plan->id)
                    ->where('ipp_point_id', $point->id) // pastikan item memang milik point ini
                    ->first();
                if (! $item) {
                    DB::rollBack();

                    return response()->json(['message' => 'Item tidak ditemukan.'], 404);
                }

                $item->update(array_merge([
                    'ipp_point_id'     => $point->id,
                    'kind_of_activity' => $request->input('kind_of_activity'),
                    'target'           => $request->input('target'),
                    'pic_employee_id'  => (int) $request->input('pic_employee_id'),
                    'schedule_mask'    => $mask,
                ], $cache));
            } else {
                $item = ActivityPlanItem::create(array_merge([
                    'activity_plan_id' => $plan->id,
                    'ipp_point_id'     => $point->id,
                    'kind_of_activity' => $request->input('kind_of_activity'),
                    'target'           => $request->input('target'),
                    'pic_employee_id'  => (int) $request->input('pic_employee_id'),
                    'schedule_mask'    => $mask,
                ], $cache));
            }

            $item->load(['pic:id,name,npk', 'ippPoint:id,activity,category,start_date,due_date']);
            DB::commit();

            return response()->json([
                'message' => 'Draft tersimpan.',
                'item' => [
                    'id'                => $item->id,
                    'ipp_point_id'      => $item->ipp_point_id,
                    'kind_of_activity'  => $item->kind_of_activity,
                    'target'            => $item->target,
                    'pic_employee_id'   => $item->pic_employee_id,
                    'schedule_mask'     => (int) $item->schedule_mask,
                    'cached_category'   => $item->cached_category,
                    'cached_activity'   => $item->cached_activity,
                    'cached_start_date' => optional($item->cached_start_date)->toDateString() ?: $item->cached_start_date,
                    'cached_due_date'   => optional($item->cached_due_date)->toDateString() ?: $item->cached_due_date,
                    'pic'               => $item->pic ? ['id' => $item->pic->id, 'name' => $item->pic->name, 'npk' => $item->pic->npk] : null,
                    'ipp_point'         => $item->ippPoint ? [
                        'id'         => $item->ippPoint->id,
                        'category'   => $item->ippPoint->category,
                        'activity'   => $item->ippPoint->activity,
                        'start_date' => optional($item->ippPoint->start_date)->toDateString(),
                        'due_date'   => optional($item->ippPoint->due_date)->toDateString(),
                    ] : null,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            DB::rollBack();

            return response()->json(['message' => 'Gagal menyimpan item.'], 500);
        }
    }

    // ===== Helpers =====
    private function months(): array
    {
        return ['APR', 'MAY', 'JUN', 'JUL', 'AGT', 'SEPT', 'OCT', 'NOV', 'DEC', 'JAN', 'FEB', 'MAR'];
    }

    private function monthIndex(string $token): int
    {
        $map = array_flip($this->months());

        return $map[$token] ?? -1;
    }

    private function monthsToMask(array $months): int
    {
        $mask = 0;
        foreach ($months as $m) {
            $idx = $this->monthIndex($m);
            if ($idx >= 0) {
                $mask |= (1 << $idx);
            }
        }

        return $mask;
    }

    private function maskToMonthIndices(int $mask): array
    {
        $out = [];
        for ($i = 0; $i < 12; $i++) {
            if ($mask & (1 << $i)) {
                $out[] = $i;
            }
        }

        return $out;
    }

    private function fiscalBounds(int $onYear): array
    {
        $start = Carbon::create($onYear, 4, 1, 0, 0, 0)->startOfDay();
        $end = Carbon::create($onYear + 1, 3, 31, 23, 59, 59)->endOfDay();

        return [$start, $end];
    }

    /** index bulan Apr..Mar yang tertutup rentang tanggal */
    private function monthIndicesInRange(Carbon $start, Carbon $due, int $onYear): array
    {
        [$fyStart, $fyEnd] = $this->fiscalBounds($onYear);
        $s = $start->copy()->max($fyStart);
        $e = $due->copy()->min($fyEnd);

        $toAprMarIdx = function (Carbon $d) use ($onYear): int {
            $y = (int) $d->year;
            $m = (int) $d->month;
            if ($y === $onYear) {
                return max(0, min(11, $m - 4));
            }     // Apr=4 -> 0

            return max(0, min(11, $m + 8));                       // Jan=1 -> 9 ... Mar=3 -> 11
        };

        $sIdx = $toAprMarIdx($s->copy()->startOfMonth());
        $eIdx = $toAprMarIdx($e->copy()->endOfMonth());
        $list = [];
        for ($i = $sIdx; $i <= $eIdx; $i++) {
            $list[] = $i;
        }

        return $list;
    }

    private function subordinateEmployee($request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        $search = $request->input('search');

        // Ambil subordinate berdasarkan level otorisasi
        $subordinateIds = $employee->getSubordinatesByLevel(1)->pluck('id')->toArray();

        $allIds = array_merge($subordinateIds, [$employee->id]);

        $employees = Employee::with([
            'departments:id,name',
        ])
            ->whereIn('id', $allIds)
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return $employees;
    }

    private function seedStepForIpp(Ipp $ipp): void
    {
        $owner = $ipp->employee()->first();
        if (! $owner) {
            return;
        }

        $chain = ApprovalHelper::expectedIppChainForEmployee($owner);

        // bersihkan step lama kalau ada
        $ipp->steps()->delete();

        foreach ($chain as $i => $s) {
            IppApprovalStep::create([
                'ipp_id'     => $ipp->id,
                'step_order' => $i + 1,
                'type'       => $s['type'],   // 'check' / 'approve'
                'role'       => $s['role'],   // 'jp','leader','manager','gm','director','vpd','president', dll
                'label'      => $s['label'],
            ]);
        }

        // kalau nggak ada chain (misal owner = vpd/president) → auto approve
        if (empty($chain)) {
            $ipp->status      = 'approved';
            $ipp->approved_at = now();
            $ipp->save();
        }
    }
}
