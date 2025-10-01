<?php

namespace App\Http\Controllers;

use App\Models\Ipp;
use App\Models\IppPoint;
use App\Models\IpaHeader;
use App\Models\IpaActivity;
use App\Models\IpaAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpaController extends Controller
{
    /**
     * Flow index:
     * - Cek IPP tahun ini milik user.
     * - Jika tidak ada / belum approved: tampilkan alert & CTA ke IPP.
     * - Jika approved: pastikan IPA 1:1 (buat jika belum), redirect ke /ipa/{id}/edit.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $emp  = $user->employee;
        if (!$emp) {
            abort(403, 'Employee profile not found.');
        }

        $year = now()->format('Y');

        $ipp = Ipp::where('employee_id', $emp->id)
            ->where('on_year', $year)
            ->first();

        if (!$ipp) {
            return view('website.ipa.index-empty', [
                'title' => 'IPA',
                'alert' => [
                    'type' => 'warning',
                    'message' => "Anda belum memiliki IPP untuk tahun {$year}. Silakan buat/selesaikan IPP terlebih dahulu.",
                    'cta_route' => route('ipp.index'),
                    'cta_text'  => 'Buka Halaman IPP',
                ],
            ]);
        }

        if (strtolower((string)$ipp->status) !== 'approved') {
            return view('website.ipa.index-empty', [
                'title' => 'IPA',
                'alert' => [
                    'type' => 'info',
                    'message' => "Proses IPP tahun {$year} belum selesai (status: {$ipp->status}). Silakan selesaikan hingga Approved.",
                    'cta_route' => route('ipp.index'),
                    'cta_text'  => 'Ke IPP Index',
                ],
            ]);
        }

        // IPP approved => pastikan IPA ada (1:1)
        $ipa = IpaHeader::where('ipp_id', $ipp->id)->first();

        if (!$ipa) {
            $ipa = IpaHeader::create([
                'employee_id' => $emp->id,
                'ipp_id'      => $ipp->id,
                'on_year'     => $ipp->on_year,
                'created_by'  => $user->id,
            ]);

            // Prefill activities dari IPP points
            $points = IppPoint::where('ipp_id', $ipp->id)->orderBy('id')->get();
            if ($points->count()) {
                $bulk = [];
                foreach ($points as $p) {
                    $bulk[] = [
                        'ipa_id'       => $ipa->id,
                        'source'       => 'from_ipp',
                        'ipp_point_id' => $p->id,
                        'category'     => $p->category ?? null,
                        'description'  => $p->description ?? $p->title ?? '',
                        'weight'       => (float)($p->weight ?? 0),
                        'self_score'   => 0,
                        'calc_score'   => 0,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
                if (!empty($bulk)) {
                    IpaActivity::insert($bulk);
                }
            }
        }

        return redirect()->route('ipa.edit', $ipa->id);
    }

    /** VIEW: halaman edit IPA */
    public function edit(int $id)
    {
        $ipa = IpaHeader::with(['employee', 'ipp'])->findOrFail($id);
        return view('website.ipa.edit', [
            'title' => 'IPA - Edit',
            'ipa'   => $ipa,
        ]);
    }

    /** JSON: data lengkap untuk halaman edit (termasuk ipp_points untuk dropdown program/activity) */
    public function getData(int $id)
    {
        $ipa = IpaHeader::with([
            'employee',
            'activities' => function ($q) {
                $q->withTrashed(false)->select('id', 'ipa_id', 'category', 'description', 'weight', 'self_score', 'calc_score', 'evidence', 'source');
            },
            'achievements' => function ($q) {
                $q->withTrashed(false)->select('id', 'ipa_id', 'ipp_point_id', 'title', 'one_year_target', 'one_year_achievement', 'weight', 'self_score', 'calc_score', 'evidence');
            },
            'ipp' => function ($q) {
                $q->select('id', 'on_year', 'status', 'employee_id');
            },
            'ipp.points' => function ($q) {
                // pastikan kolom-kolom ini tersedia di tabel ipp_points:
                // id, ipp_id, category, activity (atau title), target_one, weight
                $q->select('id', 'ipp_id', 'category', 'activity', 'target_one', 'weight');
            }
        ])->findOrFail($id);

        $ippPoints = ($ipa->ipp && $ipa->ipp->points)
            ? $ipa->ipp->points->map(function ($p) {
                return [
                    'id'         => (int)$p->id,
                    'category'   => $p->category,
                    // fallback ke title jika kamu memang pakai 'title'
                    'activity'   => $p->activity ?? $p->title ?? '',
                    'target_one' => $p->target_one ?? '',
                    'weight'     => (float)($p->weight ?? 0),
                ];
            })->values()->all()
            : [];

        return response()->json([
            'ok'   => true,
            'data' => [
                'header'       => [
                    'id'      => $ipa->id,
                    'on_year' => $ipa->on_year,
                    'notes'   => $ipa->notes,
                ],
                'activities'   => $ipa->activities->map(function ($a) {
                    return [
                        'id'         => (int)$a->id,
                        'category'   => $a->category,
                        'description' => $a->description,
                        'weight'     => (float)$a->weight,
                        'self_score' => (float)$a->self_score,
                        'calc_score' => (float)$a->calc_score,
                        'evidence'   => $a->evidence,
                        'source'     => $a->source,
                    ];
                })->values()->all(),
                'achievements' => $ipa->achievements->map(function ($c) {
                    return [
                        'id'                   => (int)$c->id,
                        'ipp_point_id'         => (int)$c->ipp_point_id,
                        'title'                => $c->title,
                        'one_year_target'      => $c->one_year_target,
                        'one_year_achievement' => $c->one_year_achievement,
                        'weight'               => (float)$c->weight,
                        'self_score'           => (float)$c->self_score,
                        'calc_score'           => (float)$c->calc_score,
                        'evidence'             => $c->evidence,
                    ];
                })->values()->all(),
                'ipp_points'   => $ippPoints,
            ]
        ]);
    }

    /** UPDATE: sinkron penuh + field achievement baru */
    public function update(Request $request, int $id)
    {
        $ipa = IpaHeader::with(['activities', 'achievements'])->find($id);
        if (!$ipa) return response()->json(['ok' => false, 'message' => 'IPA not found.'], 404);

        $validated = $request->validate([
            'notes' => 'nullable|string',

            'activities'                   => 'nullable|array',
            'activities.*.id'              => 'nullable|integer|exists:ipa_activities,id',
            'activities.*.category'        => 'nullable|string|max:100',
            'activities.*.description'     => 'nullable|string',
            'activities.*.weight'          => 'nullable|numeric|min:0',
            'activities.*.self_score'      => 'nullable|numeric|min:0',
            'activities.*.evidence'        => 'nullable|string',
            'activities.*.source'          => 'nullable|in:from_ipp,custom',

            'achievements'                        => 'array',
            'achievements.*.id'                   => 'nullable|integer|exists:ipa_achievements,id',
            'achievements.*.ipp_point_id'         => 'nullable|integer',
            'achievements.*.title'                => 'required|string|max:200',
            'achievements.*.one_year_target'      => 'nullable|string',
            'achievements.*.one_year_achievement' => 'nullable|string',
            'achievements.*.weight'               => 'required|numeric|min:0',
            'achievements.*.self_score'           => 'required|numeric|min:0',
            'achievements.*.evidence'             => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Header
            $ipa->update(['notes' => $validated['notes'] ?? $ipa->notes]);

            // ===== Activities (W × R where W is %, so calc = (W/100)*R)
            $acts = collect($validated['activities'] ?? []);
            $keepActIds = $acts->pluck('id')->filter()->map(fn($v) => (int)$v)->values();
            if ($ipa->activities()->exists()) {
                IpaActivity::where('ipa_id', $ipa->id)->whereNotIn('id', $keepActIds)->delete();
            }
            foreach ($acts as $row) {
                $w = (float)($row['weight'] ?? 0);
                $r = (float)($row['self_score'] ?? 0);
                $calc = ($w / 100.0) * $r;

                if (!empty($row['id'])) {
                    $act = IpaActivity::where('ipa_id', $ipa->id)->find($row['id']);
                    if ($act) {
                        $act->update([
                            'category'    => $row['category'] ?? null,
                            'description' => $row['description'],
                            'weight'      => $w,
                            'self_score'  => $r,
                            'calc_score'  => $calc,
                            'evidence'    => $row['evidence'] ?? null,
                        ]);
                    }
                } else {
                    IpaActivity::create([
                        'ipa_id'      => $ipa->id,
                        'source'      => $row['source'] ?? 'custom',
                        'category'    => $row['category'] ?? null,
                        'description' => $row['description'],
                        'weight'      => $w,
                        'self_score'  => $r,
                        'calc_score'  => $calc,
                        'evidence'    => $row['evidence'] ?? null,
                    ]);
                }
            }

            // ===== Achievements (W × R where W is %, so calc = (W/100)*R)
            $achs = collect($validated['achievements'] ?? []);
            $keepAchIds = $achs->pluck('id')->filter()->map(fn($v) => (int)$v)->values();
            if ($ipa->achievements()->exists()) {
                IpaAchievement::where('ipa_id', $ipa->id)->whereNotIn('id', $keepAchIds)->delete();
            }
            foreach ($achs as $row) {
                $w = (float)($row['weight'] ?? 0);
                $r = (float)($row['self_score'] ?? 0);
                $calc = ($w / 100.0) * $r;

                if (!empty($row['id'])) {
                    $ach = IpaAchievement::where('ipa_id', $ipa->id)->find($row['id']);
                    if ($ach) {
                        $ach->update([
                            'ipp_point_id'         => $row['ipp_point_id'] ?? null,
                            'title'                => $row['title'],
                            'one_year_target'      => $row['one_year_target'] ?? null,
                            'one_year_achievement' => $row['one_year_achievement'] ?? null,
                            'weight'               => $w,
                            'self_score'           => $r,
                            'calc_score'           => $calc,
                            'evidence'             => $row['evidence'] ?? null,
                        ]);
                    }
                } else {
                    IpaAchievement::create([
                        'ipa_id'               => $ipa->id,
                        'ipp_point_id'         => $row['ipp_point_id'] ?? null,
                        'title'                => $row['title'],
                        'one_year_target'      => $row['one_year_target'] ?? null,
                        'one_year_achievement' => $row['one_year_achievement'] ?? null,
                        'weight'               => $w,
                        'self_score'           => $r,
                        'calc_score'           => $calc,
                        'evidence'             => $row['evidence'] ?? null,
                    ]);
                }
            }

            // Scores from DB (akurasi)
            $actScore = (float) IpaActivity::where('ipa_id', $ipa->id)->sum('self_score');
            $achScore = (float) IpaAchievement::where('ipa_id', $ipa->id)->sum('self_score');

            // Totals from DB (akurasi)
            $actTotal = (float) IpaActivity::where('ipa_id', $ipa->id)->sum('calc_score');
            $achTotal = (float) IpaAchievement::where('ipa_id', $ipa->id)->sum('calc_score');
            $ipa->update([
                'activity_total'    => $actTotal,
                'achievement_total' => $achTotal,
                'grand_total'       => $actTotal + $achTotal,
                'grand_score'       => $actScore + $achScore,
            ]);

            DB::commit();
            return response()->json([
                'ok' => true,
                'message' => 'IPA updated.',
                'totals' => [
                    'activity_total'    => $actTotal,
                    'achievement_total' => $achTotal,
                    'grand_total'       => $actTotal + $achTotal,
                    'grand_score'       => $actScore + $achScore
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Opsional: tombol Recalc dari UI */
    public function recalc(int $id)
    {
        $ipa = IpaHeader::findOrFail($id);

        $actTotal = (float) IpaActivity::where('ipa_id', $ipa->id)->sum(DB::raw('calc_score'));
        $achTotal = (float) IpaAchievement::where('ipa_id', $ipa->id)->sum(DB::raw('calc_score'));

        $ipa->update([
            'activity_total'    => $actTotal,
            'achievement_total' => $achTotal,
            'grand_total'       => $actTotal + $achTotal,
        ]);

        return response()->json([
            'ok' => true,
            'totals' => [
                'activity_total'    => $actTotal,
                'achievement_total' => $achTotal,
                'grand_total'       => $actTotal + $achTotal,
            ]
        ]);
    }

    /**
     * (Opsional) Jika ada tombol "Create IPA" dari halaman list (index awal versi lama)
     * Pastikan hanya membuat 1 IPA per IPP (unique).
     */
    public function createFromIpp(Request $request)
    {
        $request->validate([
            'ipp_id' => 'required|integer|exists:ipps,id',
        ]);

        $user = auth()->user();
        $ipp  = Ipp::findOrFail($request->ipp_id);

        // guard employee pemilik IPP
        if ($ipp->employee_id !== optional($user->employee)->id) {
            abort(403, 'Forbidden.');
        }

        // pastikan 1:1
        $ipa = IpaHeader::firstOrCreate(
            ['ipp_id' => $ipp->id],
            [
                'employee_id' => $ipp->employee_id,
                'on_year'     => $ipp->on_year,
                'created_by'  => $user->id,
            ]
        );

        // jika baru dibuat: prefill activities dari points
        if ($ipa->wasRecentlyCreated) {
            $points = IppPoint::where('ipp_id', $ipp->id)->orderBy('id')->get();
            $bulk = [];
            foreach ($points as $p) {
                $bulk[] = [
                    'ipa_id'       => $ipa->id,
                    'source'       => 'from_ipp',
                    'ipp_point_id' => $p->id,
                    'category'     => $p->category ?? null,
                    'description'  => $p->description ?? $p->title ?? '',
                    'weight'       => (float)($p->weight ?? 0),
                    'self_score'   => 0,
                    'calc_score'   => 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
            if (!empty($bulk)) IpaActivity::insert($bulk);
        }

        return response()->json([
            'ok' => true,
            'redirect_url' => route('ipa.edit', $ipa->id),
            'ipa_id' => $ipa->id,
        ]);
    }
}
