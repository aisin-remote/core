<?php

namespace App\Http\Controllers;

use App\Models\Ipp;
use App\Models\IppPoint;
use App\Models\IpaHeader;
use App\Models\IpaActivity;
use App\Models\IpaAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class IpaController extends Controller
{
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

    /** JSON: data lengkap untuk halaman edit */
    public function getData(int $id)
    {
        $ipa = IpaHeader::with([
            'employee',
            'activities' => function ($q) {
                $q->withTrashed(false)
                    ->select('id', 'ipa_id', 'category', 'description', 'weight', 'self_score', 'calc_score', 'evidence', 'source');
            },
            'achievements' => function ($q) {
                $q->withTrashed(false)
                    ->select('id', 'ipa_id', 'ipp_point_id', 'category', 'title', 'one_year_target', 'one_year_achievement', 'weight', 'self_score', 'calc_score', 'evidence', 'status');
            },
            'ipp' => function ($q) {
                $q->select('id', 'on_year', 'status', 'employee_id');
            },
            'ipp.points' => function ($q) {
                $q->select('id', 'ipp_id', 'category', 'activity', 'target_one', 'weight');
            }
        ])->findOrFail($id);

        // Map IPP points untuk fallback kategori/title achievement berbasis IPP
        $ippPoints = collect();
        if ($ipa->ipp && $ipa->ipp->points) {
            $ippPoints = $ipa->ipp->points->map(function ($p) {
                return [
                    'id'         => (int)$p->id,
                    'category'   => $p->category,
                    'activity'   => $p->activity ?? $p->title ?? '',
                    'target_one' => $p->target_one ?? '',
                    'weight'     => (float)($p->weight ?? 0),
                ];
            })->keyBy('id');
        }

        $achievements = $ipa->achievements->map(function ($c) use ($ippPoints) {
            $p = $c->ipp_point_id ? $ippPoints->get((int)$c->ipp_point_id) : null;

            return [
                'id'                   => (int)$c->id,
                'ipp_point_id'         => $c->ipp_point_id ? (int)$c->ipp_point_id : null,
                'category'             => $c->category ?? ($p['category'] ?? null),
                'title'                => $c->title ?? ($p['activity'] ?? null),
                'one_year_target'      => $c->one_year_target ?? ($p['target_one'] ?? null),
                'one_year_achievement' => $c->one_year_achievement,
                'weight'               => (float)$c->weight,
                'self_score'           => (float)$c->self_score,
                'calc_score'           => (float)$c->calc_score,
                'evidence'             => $c->evidence,
                'status'               => $c->status
            ];
        })->values()->all();

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
                        'id'          => (int)$a->id,
                        'category'    => $a->category,
                        'description' => $a->description,
                        'weight'      => (float)$a->weight,
                        'self_score'  => (float)$a->self_score,
                        'calc_score'  => (float)$a->calc_score,
                        'evidence'    => $a->evidence,
                        'source'      => $a->source,
                    ];
                })->values()->all(),
                'achievements' => $achievements,
                'ipp_points'   => $ippPoints->values()->all(),
            ]
        ]);
    }

    public function update(Request $req, IpaHeader $ipa)
    {
        $user = auth()->user();
        $me   = optional($user)->employee;

        $validated = $req->validate([
            'header.status'                       => ['nullable', Rule::in(IpaAchievement::STATUSES)],
            'achievements'                        => ['nullable', 'array'],
            'achievements.*.id'                   => ['nullable', 'integer', 'exists:ipa_achievements,id'],
            'achievements.*.ipp_point_id'         => ['nullable', 'integer', 'exists:ipp_points,id'],
            'achievements.*.category'             => ['nullable', 'string', 'max:100'],
            'achievements.*.title'                => ['nullable', 'string', 'max:255'],
            'achievements.*.one_year_target'      => ['nullable', 'string'],
            'achievements.*.one_year_achievement' => ['nullable', 'string'],
            'achievements.*.weight'               => ['nullable', 'numeric', 'min:0', 'max:100'],
            'achievements.*.self_score'           => ['nullable', 'numeric'],
            'achievements.*.status'               => ['nullable', Rule::in(IpaAchievement::STATUSES)],
            'delete_achievements'                 => ['nullable', 'array'],
            'delete_achievements.*'               => ['integer', 'exists:ipa_achievements,id'],
        ]);

        $CAPS = [
            'activity_management' => 70.0,
            'people_development'  => 10.0,
            'crp'                 => 10.0,
            'special_assignment'  => 10.0,
        ];

        $fail = function (string $message, array $errors = []) {
            return response()->json([
                'ok'      => false,
                'message' => $message,
                'errors'  => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        };

        $currentStatus = strtolower((string)$ipa->status);
        $locked = in_array($currentStatus, ['submitted', 'checked', 'approved'], true);

        $hasMutation =
            !empty($validated['delete_achievements']) ||
            !empty($validated['achievements']) ||
            (!empty($validated['header']['status']));

        if ($locked && $hasMutation) {
            return $fail('IPA sudah submitted. Perubahan tidak diperbolehkan.', [
                'status' => ['IPA sudah submitted.']
            ]);
        }

        $existing = IpaAchievement::where('ipa_id', $ipa->id)->get();

        $byId = $existing->keyBy('id');

        $working = $existing->map(function ($row) {
            return [
                'id'                   => $row->id,
                'ipp_point_id'         => $row->ipp_point_id,
                'category'             => $row->category,
                'title'                => $row->title,
                'one_year_target'      => $row->one_year_target,
                'one_year_achievement' => $row->one_year_achievement,
                'weight'               => (float)($row->weight ?? 0),
                'self_score'           => (float)($row->self_score ?? 0),
                'status'               => $row->status,
            ];
        })->values()->all();

        $toDelete = (array)($validated['delete_achievements'] ?? []);
        if (!empty($toDelete)) {
            $working = array_values(array_filter($working, function ($w) use ($toDelete) {
                return !in_array((int)$w['id'], $toDelete, true);
            }));
        }

        $incomingAch = (array)($validated['achievements'] ?? []);
        foreach ($incomingAch as $a) {
            $data = [
                'ipp_point_id'         => $a['ipp_point_id']        ?? null,
                'category'             => $a['category']            ?? null,
                'title'                => $a['title']               ?? null,
                'one_year_target'      => $a['one_year_target']     ?? null,
                'one_year_achievement' => $a['one_year_achievement'] ?? null,
                'weight'               => array_key_exists('weight', $a) ? (float)$a['weight'] : null,
                'self_score'           => array_key_exists('self_score', $a) ? (float)$a['self_score'] : null,
                'status'               => (!empty($a['status']) && in_array($a['status'], IpaAchievement::STATUSES, true))
                    ? $a['status'] : null,
            ];

            if (!empty($a['id'])) {
                foreach ($working as &$w) {
                    if ((int)$w['id'] === (int)$a['id']) {
                        foreach ($data as $k => $v) {
                            if ($v !== null) $w[$k] = $v;
                        }
                        break;
                    }
                }
                unset($w);
            } else {
                $working[] = [
                    'id'                   => null,
                    'ipp_point_id'         => $data['ipp_point_id'],
                    'category'             => $data['category'],
                    'title'                => $data['title'],
                    'one_year_target'      => $data['one_year_target'],
                    'one_year_achievement' => $data['one_year_achievement'],
                    'weight'               => (float)($data['weight'] ?? 0),
                    'self_score'           => (float)($data['self_score'] ?? 0),
                    'status'               => $data['status'] ?? 'draft',
                ];
            }
        }

        $sumByCat = [];
        foreach ($working as $w) {
            $cat = (string)($w['category'] ?? '');
            if ($cat === '') continue;
            $sumByCat[$cat] = ($sumByCat[$cat] ?? 0) + (float)($w['weight'] ?? 0);
        }
        $grand = array_sum($sumByCat);

        $wantStatus = strtolower((string)($validated['header']['status'] ?? ''));

        if ($wantStatus === 'submitted') {
            $errors = [];

            foreach ($CAPS as $cat => $cap) {
                $sum = (float)($sumByCat[$cat] ?? 0.0);
                if (abs($sum - $cap) > 1e-8) {
                    $errors["categories.$cat"][] = "Kategori harus tepat {$cap}%, sekarang " . rtrim(rtrim(number_format($sum, 2, '.', ''), '0'), '.') . "%.";
                }
            }
            if (abs($grand - 100.0) > 1e-8) {
                $errors['grand'][] = "Total seluruh kategori harus 100%, sekarang " . rtrim(rtrim(number_format($grand, 2, '.', ''), '0'), '.') . "%.";
            }

            if (!empty($errors)) {
                return $fail('Validasi submit gagal.', $errors);
            }
        } else {
            $errors = [];
            foreach ($sumByCat as $cat => $sum) {
                if (!array_key_exists($cat, $CAPS)) continue;
                $cap = (float)$CAPS[$cat];
                if ($sum - $cap > 1e-8) {
                    $errors["categories.$cat"][] = "Total bobot melebihi CAP: {$sum}% / {$cap}%.";
                }
            }
            if (!empty($errors)) {
                return $fail('Validasi CAP per kategori gagal.', $errors);
            }
        }

        $createdIds = [];
        $updatedIds = [];
        $deletedIds = [];

        DB::transaction(function () use ($ipa, $validated, &$createdIds, &$updatedIds, &$deletedIds, $wantStatus, $me) {

            if (!empty($validated['delete_achievements'])) {
                $rows = IpaAchievement::query()
                    ->where('ipa_id', $ipa->id)
                    ->whereIn('id', $validated['delete_achievements'])
                    ->pluck('id')
                    ->all();

                if ($rows) {
                    IpaAchievement::whereIn('id', $rows)->delete();
                    $deletedIds = array_values($rows);
                }
            }

            if (!empty($validated['achievements'])) {
                foreach ($validated['achievements'] as $a) {
                    $data = [
                        'ipp_point_id'         => $a['ipp_point_id']         ?? null,
                        'category'             => $a['category']             ?? null,
                        'title'                => $a['title']                ?? null,
                        'one_year_target'      => $a['one_year_target']      ?? null,
                        'one_year_achievement' => $a['one_year_achievement'] ?? null,
                        'weight'               => array_key_exists('weight', $a) ? (float)$a['weight'] : null,
                        'self_score'           => array_key_exists('self_score', $a) ? (float)$a['self_score'] : null,
                    ];

                    if (!empty($a['status']) && in_array($a['status'], IpaAchievement::STATUSES, true)) {
                        $data['status'] = $a['status'];
                    }

                    if (!empty($a['id'])) {
                        $ach = IpaAchievement::where('ipa_id', $ipa->id)->findOrFail($a['id']);
                        $ach->fill(array_filter($data, fn($v) => $v !== null));
                        $ach->save();
                        $updatedIds[] = $ach->id;
                    } else {
                        $ach = new IpaAchievement();
                        $ach->ipa_id = $ipa->id;
                        foreach ($data as $k => $v) if ($v !== null) $ach->{$k} = $v;
                        if (empty($data['status'])) $ach->status = 'draft';
                        $ach->save();
                        $createdIds[] = $ach->id;
                    }
                }
            }

            if ($wantStatus === 'submitted') {
                $now = now();

                if (empty($ipa->submitted_at)) {
                    $ipa->submitted_at = $now;
                }

                $actorUser     = auth()->user();
                $actorEmployee = optional($actorUser)->employee;

                $resolveSuperior = function ($employee, int $level, string $pick) {
                    if (!$employee || !method_exists($employee, 'getSuperiorsByLevel')) {
                        Log::warning('IPA Update: getSuperiorsByLevel not available', ['level' => $level]);
                        return null;
                    }

                    $res = $employee->getSuperiorsByLevel($level) ?? null;

                    if ($res instanceof \Illuminate\Support\Collection) {
                        $candidate = $pick === 'last' ? $res->last() : $res->first();
                    } elseif (is_array($res)) {
                        $candidate = $pick === 'last'
                            ? (\count($res) ? end($res) : null)
                            : (\count($res) ? reset($res) : null);
                    } else {
                        $candidate = $res;
                    }

                    $id = data_get($candidate, 'id')
                        ?: data_get($candidate, 'employee_id')
                        ?: data_get($candidate, 'user_id');

                    return $id ?: null;
                };

                $resolvedChecker  = $resolveSuperior($actorEmployee, 1, 'first');
                if ($resolvedChecker)  $ipa->checked_by  = $resolvedChecker;

                $resolvedApprover = $resolveSuperior($actorEmployee, 3, 'last');
                if ($resolvedApprover) $ipa->approved_by = $resolvedApprover;

                $ipa->status = 'submitted';
                $ipa->save();

                Log::info('IPA Update: submitted set', [
                    'ipa_id'       => $ipa->id,
                    'submitted_at' => $ipa->submitted_at,
                    'checked_by'   => $ipa->checked_by,
                    'approved_by'  => $ipa->approved_by,
                ]);

                if (empty($validated['achievements'])) {
                    IpaAchievement::where('ipa_id', $ipa->id)->update(['status' => 'submitted']);
                }
            }
        });

        return response()->json([
            'ok'           => true,
            'created_ids'  => $createdIds,
            'updated_ids'  => $updatedIds,
            'deleted_ids'  => $deletedIds,
            'message'      => $wantStatus === 'submitted' ? 'IPA submitted.' : 'IPA updated.',
        ]);
    }

    /** Recalc (ikutkan grand_score) */
    public function recalc(int $id)
    {
        $ipa = IpaHeader::findOrFail($id);

        $actTotal = (float) IpaActivity::where('ipa_id', $ipa->id)->sum(DB::raw('calc_score'));
        $achTotal = (float) IpaAchievement::where('ipa_id', $ipa->id)->sum(DB::raw('calc_score'));

        $actScore = (float) IpaActivity::where('ipa_id', $ipa->id)->sum('self_score');
        $achScore = (float) IpaAchievement::where('ipa_id', $ipa->id)->sum('self_score');

        $ipa->update([
            'activity_total'    => $actTotal,
            'achievement_total' => $achTotal,
            'grand_total'       => $actTotal + $achTotal,
            'grand_score'       => $actScore + $achScore,
        ]);

        return response()->json([
            'ok' => true,
            'totals' => [
                'activity_total'    => $actTotal,
                'achievement_total' => $achTotal,
                'grand_total'       => $actTotal + $achTotal,
                'grand_score'       => $actScore + $achScore,
            ]
        ]);
    }

    public function createFromIpp(Request $request)
    {
        $request->validate([
            'ipp_id' => 'required|integer|exists:ipps,id',
        ]);

        $user = auth()->user();
        $ipp  = Ipp::findOrFail($request->ipp_id);

        if ($ipp->employee_id !== optional($user->employee)->id) {
            abort(403, 'Forbidden.');
        }

        $ipa = IpaHeader::firstOrCreate(
            ['ipp_id' => $ipp->id],
            [
                'employee_id' => $ipp->employee_id,
                'on_year'     => $ipp->on_year,
                'created_by'  => $user->id,
            ]
        );

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
