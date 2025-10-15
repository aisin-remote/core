<?php

namespace App\Http\Controllers;

use App\Models\Ipp;
use App\Models\IppPoint;
use App\Models\Employee;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// (Opsional) aktifkan jika kamu pakai Maatwebsite Excel
use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\IppActivityPlanExport;

class ActivityPlanController extends Controller
{
    /** Halaman utama Activity Plan (pakai query ?ipp_id=..) */
    public function index(Request $request)
    {
        // view hanya butuh render blade; data diambil via /init (AJAX)
        return view('website.activity_plan.index', [
            'title' => 'Activity Plan',
        ]);
    }

    /** INIT: dipanggil JS untuk load data awal (IPP, Plan, Points, Items, Employees) */
    public function init(Request $request)
    {
        $ippId = (int) $request->query('ipp_id');
        $user  = $request->user();
        $emp   = $user?->employee;

        if (!$ippId || !$emp) {
            return response()->json(['message' => 'ipp_id tidak valid atau employee tidak ditemukan.'], 422);
        }

        $ipp = Ipp::with('employee')
            ->where('id', $ippId)
            ->where('employee_id', $emp->id)
            ->first();

        if (!$ipp) {
            return response()->json(['message' => 'IPP tidak ditemukan / bukan milik Anda.'], 404);
        }

        // Pastikan header ActivityPlan ada (draft)
        $plan = ActivityPlan::firstOrCreate(
            ['ipp_id' => $ipp->id],
            [
                'employee_id'   => $ipp->employee_id,
                'fy_start_year' => (int) $ipp->on_year,  // FY Apr-(Apr+1)
                'division'      => $ipp->division,
                'department'    => $ipp->department,
                'section'       => $ipp->section,
                'form_no'       => $ipp->no_form ?? null,
                'status'        => 'draft',
            ]
        );

        // Ambil IPP Points (yang tahun & employee sesuai)
        $points = IppPoint::query()
            ->where('ipp_id', $ipp->id)
            ->orderBy('category')
            ->orderBy('id')
            ->get(['id', 'ipp_id', 'category', 'activity', 'target_mid', 'target_one', 'start_date', 'due_date', 'weight', 'status']);

        // Items AP (relasi pic & ipp_point)
        $items = ActivityPlanItem::with([
            'pic:id,name,npk',
            'ippPoint:id,activity,category,start_date,due_date'
        ])
            ->where('activity_plan_id', $plan->id)
            ->orderBy('id')
            ->get();

        // Karyawan (PIC) — customize filter sesuai kebutuhan
        $employees = Employee::query()
            ->orderBy('name')
            ->get(['id', 'name', 'npk']);

        return response()->json([
            'ipp'       => [
                'id'        => $ipp->id,
                'nama'      => $ipp->nama,
                'on_year'   => $ipp->on_year,
                'status'    => $ipp->status,
            ],
            'plan'      => [
                'id'            => $plan->id,
                'ipp_id'        => $plan->ipp_id,
                'status'        => $plan->status,
                'form_no'       => $plan->form_no,
                'fy_start_year' => $plan->fy_start_year,
                'division'      => $plan->division,
                'department'    => $plan->department,
                'section'       => $plan->section,
            ],
            'points'    => $points,
            'items'     => $items->map(function ($it) {
                return [
                    'id'                => $it->id,
                    'ipp_point_id'      => $it->ipp_point_id,
                    'kind_of_activity'  => $it->kind_of_activity,
                    'target'            => $it->target,
                    'pic_employee_id'   => $it->pic_employee_id,
                    'schedule_mask'     => (int) $it->schedule_mask,
                    'cached_category'   => $it->cached_category,
                    'cached_activity'   => $it->cached_activity,
                    'cached_start_date' => $it->cached_start_date,
                    'cached_due_date'   => $it->cached_due_date,
                    'pic'               => $it->pic ? [
                        'id'   => $it->pic->id,
                        'name' => $it->pic->name,
                        'npk'  => $it->pic->npk,
                    ] : null,
                    'ipp_point'         => $it->ippPoint ? [
                        'id'         => $it->ippPoint->id,
                        'category'   => $it->ippPoint->category,
                        'activity'   => $it->ippPoint->activity,
                        'start_date' => optional($it->ippPoint->start_date)->toDateString(),
                        'due_date'   => optional($it->ippPoint->due_date)->toDateString(),
                    ] : null,
                ];
            }),
            'employees' => $employees,
        ]);
    }

    /** Create / Edit item AP (body JSON) */
    public function storeItem(Request $request)
    {
        $user  = $request->user();
        $emp   = $user?->employee;
        $ippId = (int) $request->query('ipp_id'); // kirim di query agar konsisten

        if (!$emp) return response()->json(['message' => 'Employee tidak ditemukan.'], 422);

        $v = validator($request->all(), [
            'mode'             => ['required', Rule::in(['create', 'edit'])],
            'row_id'           => ['nullable', 'integer'],
            'ipp_point_id'     => ['required', 'integer'],
            'kind_of_activity' => ['required', 'string', 'max:255'],
            'target'           => ['nullable', 'string'],
            'pic_employee_id'  => ['required', 'integer', 'exists:employees,id'],
            'months'           => ['array'],
            'months.*'         => ['string', Rule::in($this->months())],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        // Pastikan IPP milik user
        $ipp = Ipp::where('id', $ippId ?: $request->input('ipp_id'))
            ->where('employee_id', $emp->id)
            ->first();

        if (!$ipp) return response()->json(['message' => 'IPP tidak ditemukan / bukan milik Anda.'], 404);

        // Pastikan Plan ada
        $plan = ActivityPlan::firstOrCreate(
            ['ipp_id' => $ipp->id],
            [
                'employee_id'   => $ipp->employee_id,
                'fy_start_year' => (int) $ipp->on_year,
                'division'      => $ipp->division,
                'department'    => $ipp->department,
                'section'       => $ipp->section,
                'form_no'       => $ipp->no_form ?? null,
                'status'        => 'draft',
            ]
        );

        // Validasi IPP Point milik IPP ini
        $point = IppPoint::where('id', $request->input('ipp_point_id'))
            ->where('ipp_id', $ipp->id)
            ->first();

        if (!$point) return response()->json(['message' => 'IPP Point tidak ditemukan / tidak sesuai IPP.'], 404);

        // Build schedule mask
        $months = $request->input('months', []);
        $mask   = $this->monthsToMask($months); // 12-bit

        // Cache field dari IPPPoint (biar stabil saat export)
        $cache = [
            'cached_category'   => $point->category,
            'cached_activity'   => $point->activity,
            'cached_start_date' => optional($point->start_date)->toDateString(),
            'cached_due_date'   => optional($point->due_date)->toDateString(),
        ];

        try {
            DB::beginTransaction();

            if ($request->input('mode') === 'edit') {
                $item = ActivityPlanItem::where('id', $request->input('row_id'))
                    ->where('activity_plan_id', $plan->id)
                    ->first();

                if (!$item) {
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

            // reload dengan relasi untuk dikirim balik
            $item->load(['pic:id,name,npk', 'ippPoint:id,activity,category,start_date,due_date']);

            DB::commit();

            return response()->json([
                'message' => 'Draft tersimpan.',
                'item'    => [
                    'id'                => $item->id,
                    'ipp_point_id'      => $item->ipp_point_id,
                    'kind_of_activity'  => $item->kind_of_activity,
                    'target'            => $item->target,
                    'pic_employee_id'   => $item->pic_employee_id,
                    'schedule_mask'     => (int) $item->schedule_mask,
                    'cached_category'   => $item->cached_category,
                    'cached_activity'   => $item->cached_activity,
                    'cached_start_date' => $item->cached_start_date,
                    'cached_due_date'   => $item->cached_due_date,
                    'pic' => $item->pic ? [
                        'id'   => $item->pic->id,
                        'name' => $item->pic->name,
                        'npk'  => $item->pic->npk,
                    ] : null,
                    'ipp_point' => $item->ippPoint ? [
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

    /** Hapus item AP */
    public function destroyItem(Request $request, ActivityPlanItem $item)
    {
        $user = $request->user();
        $emp  = $user?->employee;

        if (!$emp) return response()->json(['message' => 'Employee tidak ditemukan.'], 422);

        // Pastikan item milik plan milik employee
        $plan = ActivityPlan::where('id', $item->activity_plan_id)->first();
        if (!$plan) return response()->json(['message' => 'Activity Plan tidak ditemukan.'], 404);

        $ipp = Ipp::where('id', $plan->ipp_id)->where('employee_id', $emp->id)->first();
        if (!$ipp) return response()->json(['message' => 'Tidak diizinkan menghapus item ini.'], 403);

        try {
            $item->delete();
            return response()->json(['message' => 'Item dihapus.']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Gagal menghapus item.'], 500);
        }
    }

    /** Submit gabungan IPP + Activity Plan */
    public function submitAll(Request $request)
    {
        $user  = $request->user();
        $emp   = $user?->employee;
        $ippId = (int) ($request->query('ipp_id') ?: $request->input('ipp_id'));

        if (!$emp) return response()->json(['message' => 'Employee tidak ditemukan.'], 422);

        // Temukan IPP milik user (prioritas dari query ipp_id)
        $ippQuery = Ipp::with('points')->where('employee_id', $emp->id);
        if ($ippId) $ippQuery->where('id', $ippId);
        else $ippQuery->where('on_year', now()->format('Y')); // fallback: tahun berjalan

        $ipp = $ippQuery->first();
        if (!$ipp) return response()->json(['message' => 'IPP tidak ditemukan.'], 404);

        $plan = ActivityPlan::with('items')->where('ipp_id', $ipp->id)->first();
        if (!$plan) return response()->json(['message' => 'Activity Plan belum dibuat.'], 422);

        // ===== Validasi IPP (cap & total 100)
        // Ambil CAP dari controller IPP jika ada konstanta; fallback manual
        $caps = method_exists(\App\Http\Controllers\IppController::class, 'CAP')
            ? \App\Http\Controllers\IppController::CAP
            : [
                'activity_management' => 70,
                'people_development'  => 10,
                'crp'                 => 10,
                'special_assignment'  => 10,
            ];

        $grouped = $ipp->points->groupBy('category')->map->sum('weight');
        foreach ($caps as $cat => $cap) {
            if (($grouped[$cat] ?? 0) > $cap) {
                return response()->json(['message' => "Bobot kategori {$cat} melebihi cap {$cap}%."], 422);
            }
        }
        if ($ipp->points->isEmpty()) {
            return response()->json(['message' => 'Tambahkan minimal satu IPP point.'], 422);
        }
        $total = (int) $ipp->points->sum('weight');
        if ($total !== 100) {
            return response()->json(['message' => 'Total bobot IPP harus tepat 100%.'], 422);
        }

        // ===== Validasi Activity Plan
        if (!$plan->items || $plan->items->isEmpty()) {
            return response()->json(['message' => 'Tambahkan minimal satu Activity Plan item.'], 422);
        }
        $validPointIds = $ipp->points->pluck('id')->all();
        foreach ($plan->items as $it) {
            if (!in_array($it->ipp_point_id, $validPointIds, true)) {
                return response()->json(['message' => 'Terdapat item Activity Plan yang bukan berasal dari IPP ini.'], 422);
            }
            if (!$it->kind_of_activity) {
                return response()->json(['message' => 'Kind of activity wajib diisi.'], 422);
            }
            if (!$it->pic_employee_id) {
                return response()->json(['message' => 'PIC wajib dipilih.'], 422);
            }
        }

        // ===== Submit (lock) keduanya
        try {
            DB::transaction(function () use ($ipp, $plan) {
                IppPoint::where('ipp_id', $ipp->id)->update(['status' => 'submitted']);
                $ipp->update(['status' => 'submitted', 'submitted_at' => now()]);

                ActivityPlanItem::where('activity_plan_id', $plan->id)->update(['status' => 'submitted']); // jika ada kolom status
                $plan->update(['status' => 'submitted', 'submitted_at' => now()]);
            });

            return response()->json(['message' => 'IPP + Activity Plan berhasil disubmit.']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Gagal submit gabungan.'], 500);
        }
    }

    /** Export Excel multi-sheet (IPP & Activity Plan) – opsional */
    public function exportExcel(Request $request)
    {
        $ippId = (int) $request->query('ipp_id');
        $user  = $request->user();
        $emp   = $user?->employee;

        if (!$ippId || !$emp) {
            return back()->with('error', 'ipp_id tidak valid.');
        }

        $ipp = Ipp::where('id', $ippId)->where('employee_id', $emp->id)->first();
        if (!$ipp) return back()->with('error', 'IPP tidak ditemukan / bukan milik Anda.');

        // Jika kamu sudah punya export class, uncomment 3 baris ini:
        // $fname = 'IPP-ActivityPlan-'.$ipp->on_year.'-'.$emp->name.'.xlsx';
        // return Excel::download(new IppActivityPlanExport($ipp->id), $fname);

        // Placeholder jika belum ada export
        return back()->with('error', 'Export belum diimplementasikan (IppActivityPlanExport).');
    }

    // ===== Helpers =====

    /** Urutan bulan FY Apr–Mar (sesuai UI) */
    private function months(): array
    {
        return ['APR', 'MAY', 'JUN', 'JUL', 'AGT', 'SEPT', 'OCT', 'NOV', 'DEC', 'JAN', 'FEB', 'MAR'];
    }

    /** Encode array bulan ke bitmask 12-bit */
    private function monthsToMask(array $months): int
    {
        $list = $this->months();
        $map  = array_flip($list); // 'APR' => 0, 'MAY' => 1, ...
        $mask = 0;
        foreach ($months as $m) {
            if (isset($map[$m])) {
                $mask |= (1 << $map[$m]);
            }
        }
        return $mask;
    }
}
