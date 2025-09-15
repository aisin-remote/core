<?php

namespace App\Http\Controllers;

use App\Models\Ipp;
use App\Models\IppPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function init(Request $request)
    {
        $user      = auth()->user();
        $emp       = $user->employee;
        $year = now()->format('Y');

        $identitas = [
            'nama'        => (string)($emp->name ?? $user->name ?? ''),
            'department'  => (string)($emp->bagian ?? ''),
            'division'    => '-',
            'section'     => '-',
            'date_review' => '-',
            'pic_review'  => '',
            'on_year'     => now()->format('Y'),
            'no_form'     => '-',
        ];

        $ipp = Ipp::where('nama', $identitas['nama'])
            ->where('on_year', $year)
            ->first();
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
        if ($ipp) {

            $points = IppPoint::where('ipp_id', $ipp->id)
                ->orderBy('id')
                ->get();
            foreach ($points  as $p) {
                $item = [
                    'id'          => $p->id,
                    'category'    => $p->category,
                    'activity'    => (string) $p->activity,
                    'target_mid'  => (string) $p->target_mid,
                    'target_one'  => (string) $p->target_one,
                    'due_date'    => $p->due_date ? Carbon::parse($p->due_date)->format('Y-m-d') : null,
                    'weight'      => (int) $p->weight,
                    'status'      => (string) ($p->status ?? 'draft'),
                ];
                if (isset($pointByCat[$p->category])) {
                    $pointByCat[$p->category][] = $item;
                    $summary[$p->category] = ($summary[$p->category] ?? 0) + (int)$p->weight;
                }
            }

            $summary['total'] = ($summary['activity_management'] ?? 0)
                + ($summary['people_development'] ?? 0)
                + ($summary['crp'] ?? 0)
                + ($summary['special_assignment'] ?? 0);

            $header = [
                'id'      => $ipp->id,
                'status'  => (string) $ipp->status,
                'summary' => $ipp->summary ?: $summary,
            ];
        }

        return response()->json([
            'identitas' => $identitas,
            'ipp' => $header,
            'points' => $pointByCat,
            'cap' =>  self::CAP,
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

    /** === SIMPAN 1 POINT (draft) === */
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
        $status = $payload['status']; // frontend selalu 'draft'
        $cat    = $payload['cat'];
        $rowId  = $payload['row_id'] ?? null;
        $p      = $payload['point'];

        $user   = auth()->user();
        $emp    = $user->employee;
        $onYear = now()->format('Y');

        $headerAttrs = ['nama' => $emp->name, 'on_year' => $onYear];

        $ipp = Ipp::firstOrCreate(
            $headerAttrs,
            [
                'department'  => $emp->bagian,
                'division'    => 'MS & IT',
                'section'     => 'Policy Management',
                'date_review' => now(),
                'pic_review'  => 'Ferry Avianto',
                'no_form'     => 'FRM-HRD-S3-012-00',
                'status'      => 'draft',
                'summary'     => [],
            ]
        );

        try {
            DB::beginTransaction();

            if ($mode === 'create') {
                $point = IppPoint::create([
                    'ipp_id'     => $ipp->id,
                    'category'   => $cat,
                    'activity'   => $p['activity'],
                    'target_mid' => $p['target_mid'] ?? null,
                    'target_one' => $p['target_one'] ?? null,
                    'due_date'   => $p['due_date'],
                    'weight'     => (int)$p['weight'],
                    'status'     => $status, // draft
                ]);
            } else {
                $point = IppPoint::where('id', $rowId)->first();
                if (!$point) {
                    DB::rollBack();
                    return response()->json(['message' => 'Point not found'], 404);
                }

                $point->update([
                    'category'   => $cat,
                    'activity'   => $p['activity'],
                    'target_mid' => $p['target_mid'] ?? null,
                    'target_one' => $p['target_one'] ?? null,
                    'due_date'   => $p['due_date'],
                    'weight'     => (int)$p['weight'],
                    'status'     => $status, // tetap draft
                ]);
            }

            // update summary header
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

    /** === SUBMIT ALL (ubah seluruh IPP jadi submitted) === */
    public function submit(Request $request)
    {
        $user   = auth()->user();
        $emp    = $user->employee;
        $year   = now()->format('Y');

        $ipp = Ipp::where('nama', $emp->name)->where('on_year', $year)->first();

        if (!$ipp) {
            return response()->json(['message' => 'Belum ada data IPP untuk disubmit.'], 422);
        }

        $points = IppPoint::where('ipp_id', $ipp->id)->get();
        if ($points->isEmpty()) {
            return response()->json(['message' => 'Tambahkan minimal satu point sebelum submit.'], 422);
        }

        // Validasi cap per kategori
        $summary = [];
        foreach (self::CAP as $cat => $cap) {
            $used = (int) $points->where('category', $cat)->sum('weight');
            $summary[$cat] = $used;
            if ($used > $cap) {
                return response()->json([
                    'message' => "Bobot kategori \"" . str_replace('_', ' ', $cat) . "\" melebihi cap {$cap}%. Kurangi W% dulu."
                ], 422);
            }
        }

        // Total harus 100%
        $summary['total'] = array_sum($summary);
        if ($summary['total'] !== 100) {
            return response()->json(['message' => 'Total bobot harus tepat 100% sebelum submit.'], 422);
        }

        DB::transaction(function () use ($ipp, $summary) {
            IppPoint::where('ipp_id', $ipp->id)->update(['status' => 'submitted']);
            $ipp->update(['status' => 'submitted', 'summary' => $summary]);
        });

        return response()->json(['message' => 'Berhasil submit IPP.', 'summary' => $summary]);
    }
}
