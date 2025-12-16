<?php

namespace App\Http\Controllers;

use App\Helpers\ApprovalHelper;
use App\Models\Assessment;
use App\Models\Development;
use App\Models\DevelopmentApprovalStep;
use App\Models\DevelopmentOne;
use App\Models\Employee;
use App\Models\Idp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class DevelopmentController extends Controller
{

    public function developmentForm($employee_id)
    {
        $title = 'IDP Development - ' . ($assessment->employee->name ?? '-');

        return view('website.idp.development', compact('title', 'employee_id'));
    }

    public function developmentJson($employee_id)
    {
        $assessment = Assessment::with([
            'employee',
            'details' => function ($q) {
                $q->where(function ($q2) {
                    $q2->where('score', '<', 3)
                        ->orWhere(function ($q3) {
                            $q3->where('score', '>=', 3)
                                ->whereNotNull('suggestion_development')
                                ->where('suggestion_development', '!=', '');
                        })
                        ->orWhere(function ($q4) {
                            $q4->where('score', '<', 3)
                                ->whereNotNull('suggestion_development')
                                ->where('suggestion_development', '!=', '');
                        });
                })->with('alc');
            },
        ])
            ->where('employee_id', $employee_id)
            ->whereHas('details', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('score', '<', 3)
                        ->orWhere(function ($q3) {
                            $q3->where('score', '>=', 3)
                                ->whereNotNull('suggestion_development')
                                ->where('suggestion_development', '!=', '');
                        })
                        ->orWhere(function ($q4) {
                            $q4->where('score', '<', 3)
                                ->whereNotNull('suggestion_development')
                                ->where('suggestion_development', '!=', '');
                        });
                });
            })
            ->latest('date')
            ->firstOrFail();

        $relevantAlcIds = $assessment->details->pluck('alc_id')->unique()->values();

        // ambil IDP by alc
        $idps = Idp::with('alc')
            ->where('assessment_id', $assessment->id)
            ->whereIn('alc_id', $relevantAlcIds)
            ->get()
            ->groupBy('alc_id');

        /**
         * Susun idpRows seperti yang kamu lakukan di blade sebelumnya
         */
        $idpRows = [];
        $alcByIdp = [];

        foreach ($assessment->details as $detail) {
            $alcName = $detail->alc->name ?? ($detail->alc->title ?? ('ALC ' . $detail->alc_id));
            $alcId   = $detail->alc_id;

            $detailIdps = $idps[$alcId] ?? collect();
            if ($detailIdps->isEmpty()) continue;

            $latestIdp = $detailIdps->sortByDesc('updated_at')->first();
            if (!$latestIdp) continue;

            $idpRows[] = [
                'alc_name' => $alcName,
                'alc_id'   => $alcId,
                'idp'      => [
                    'id'                  => $latestIdp->id,
                    'category'            => $latestIdp->category,
                    'development_program' => $latestIdp->development_program,
                    'development_target'  => $latestIdp->development_target,
                    'date'                => $latestIdp->date,
                ],
            ];

            foreach ($detailIdps as $idp) {
                $alcByIdp[$idp->id] = $alcName;
            }
        }

        // Mid history
        $midDevs = Development::where('employee_id', $employee_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('idp_id');

        // One history
        $oneDevs = DevelopmentOne::where('employee_id', $employee_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('idp_id');

        // Flags lock
        $midDrafts   = $midDevs->flatten()->where('status', 'draft');
        $hasMidDraft = $midDrafts->isNotEmpty();

        $oneDrafts   = $oneDevs->flatten()->where('status', 'draft');
        $hasOneDraft = $oneDrafts->isNotEmpty();

        $midDraftIdpIds = $midDrafts->pluck('idp_id')->values()->all();
        $oneDraftIdpIds = $oneDrafts->pluck('idp_id')->values()->all();

        /**
         * ✅ MODIFIKASI DI SINI:
         * One-Year boleh diisi kalau Mid status sudah submitted/checked/approved & tidak ada Mid draft.
         */
        $midReadyStatuses = ['submitted', 'checked', 'approved'];

        $hasMidReady = $midDevs->flatten()
            ->whereIn('status', $midReadyStatuses)
            ->isNotEmpty();

        // Mid dikunci kalau sudah ada data & tidak ada draft
        $midLocked = $midDevs->flatten()->isNotEmpty() && !$hasMidDraft;

        // One dikunci kalau sudah ada data & tidak ada draft
        $oneLocked = $oneDevs->flatten()->isNotEmpty() && !$hasOneDraft;

        // ✅ One-Year akses (rule baru)
        $canAccessOne = $hasMidReady && !$hasMidDraft;

        /**
         * Ubah history ke format array siap render (tanpa HTML)
         */
        $midHistory = [];
        foreach ($midDevs as $idpId => $list) {
            foreach ($list as $dev) {
                $midHistory[] = [
                    'id'                      => $dev->id,
                    'idp_id'                  => $dev->idp_id,
                    'alc'                     => $alcByIdp[$dev->idp_id] ?? '-',
                    'development_program'     => $dev->development_program,
                    'development_achievement' => $dev->development_achievement,
                    'next_action'             => $dev->next_action,
                    'status'                  => $dev->status,
                    'created_at'              => optional($dev->created_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i'),
                ];
            }
        }

        $oneHistory = [];
        foreach ($oneDevs as $idpId => $list) {
            foreach ($list as $dev) {
                $oneHistory[] = [
                    'id'                  => $dev->id,
                    'idp_id'              => $dev->idp_id,
                    'alc'                 => $alcByIdp[$dev->idp_id] ?? '-',
                    'development_program' => $dev->development_program,
                    'evaluation_result'   => $dev->evaluation_result,
                    'status'              => $dev->status,
                    'created_at'          => optional($dev->created_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i'),
                ];
            }
        }

        $title = 'IDP Development - ' . ($assessment->employee->name ?? '-');

        return response()->json([
            'status' => 'success',
            'title'  => $title,
            'assessment' => [
                'id'      => $assessment->id,
                'date'    => $assessment->date,
                'purpose' => $assessment->purpose,
                'lembaga' => $assessment->lembaga,
            ],
            'employee' => [
                'id'         => $assessment->employee->id,
                'name'       => $assessment->employee->name,
                'npk'        => $assessment->employee->npk,
                'position'   => $assessment->employee->position,
                'department' => $assessment->employee->department_name ?? ($assessment->employee->department ?? null),
                'grade'      => $assessment->employee->grade ?? null,
            ],
            'idpRows'    => $idpRows,
            'midHistory' => $midHistory,
            'oneHistory' => $oneHistory,
            'flags' => [
                'hasMidDraft'     => $hasMidDraft,
                'hasOneDraft'     => $hasOneDraft,
                'midLocked'       => $midLocked,
                'oneLocked'       => $oneLocked,
                'canAccessOne'    => $canAccessOne,

                // biar tidak merusak FE lama yang baca hasMidSubmitted
                'hasMidSubmitted' => $hasMidReady,

                // opsional: key baru yang lebih jelas
                'hasMidReady'     => $hasMidReady,
            ],
            'draftIds' => [
                'midDraftIdpIds' => $midDraftIdpIds,
                'oneDraftIdpIds' => $oneDraftIdpIds,
            ],
        ]);
    }


    public function storeMidYear(Request $request, $employee_id)
    {
        $validator = Validator::make($request->all(), [
            'idp_id'                  => 'required|array',
            'idp_id.*'                => 'nullable|integer|exists:idp,id',
            'development_program'     => 'required|array',
            'development_program.*'   => 'nullable|string|max:255',
            'development_achievement' => 'required|array',
            'development_achievement.*' => 'nullable|string',
            'next_action'             => 'required|array',
            'next_action.*'           => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $idpIds      = $request->input('idp_id', []);
        $programs    = $request->input('development_program', []);
        $achievements = $request->input('development_achievement', []);
        $nextActions = $request->input('next_action', []);

        $created = [];

        DB::beginTransaction();
        try {
            foreach ($idpIds as $idx => $idpId) {
                $idpId   = $idpId ?: null;
                $program = $programs[$idx] ?? null;
                $ach     = trim($achievements[$idx] ?? '');
                $next    = trim($nextActions[$idx] ?? '');

                // Kalau semua kosong, skip
                if ($ach === '' && $next === '') {
                    continue;
                }

                $dev = Development::create([
                    'employee_id'             => $employee_id,
                    'idp_id'                  => $idpId,
                    'development_program'     => $program,
                    'development_achievement' => $ach,
                    'next_action'             => $next,
                    'status'                  => 'draft',
                ]);

                $created[] = [
                    'id'                     => $dev->id,
                    'idp_id'                 => $dev->idp_id,
                    'development_program'    => $dev->development_program,
                    'development_achievement' => $dev->development_achievement,
                    'status'                 => $dev->status,
                    'created_at'             => $dev->created_at
                        ? $dev->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i')
                        : now()->timezone('Asia/Jakarta')->format('d-m-Y H:i'),
                ];
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Mid-Year Development berhasil disimpan.',
                'data'    => $created, 
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan Mid-Year Development.',
            ], 500);
        }
    }

    public function storeOneYear(Request $request, $employee_id)
    {
        $validator = Validator::make($request->all(), [
            'idp_id'                => 'required|array',
            'idp_id.*'              => 'nullable|integer|exists:idp,id',
            'development_program'   => 'required|array',
            'development_program.*' => 'nullable|string|max:255',
            'evaluation_result'     => 'required|array',
            'evaluation_result.*'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $idpIds    = $request->input('idp_id', []);
        $programs  = $request->input('development_program', []);
        $results   = $request->input('evaluation_result', []);

        $created = [];

        DB::beginTransaction();
        try {
            foreach ($idpIds as $idx => $idpId) {
                $idpId  = $idpId ?: null;
                $program = $programs[$idx] ?? null;
                $result  = trim($results[$idx] ?? '');

                if ($result === '') {
                    continue;
                }

                $dev = DevelopmentOne::create([
                    'employee_id'        => $employee_id,
                    'idp_id'             => $idpId,
                    'development_program' => $program,
                    'evaluation_result'  => $result,
                    'status'             => 'draft',
                ]);

                $created[] = [
                    'id'                 => $dev->id,
                    'idp_id'             => $dev->idp_id,
                    'development_program' => $dev->development_program,
                    'evaluation_result'  => $dev->evaluation_result,
                    'status'             => $dev->status,
                    'created_at'         => $dev->created_at
                        ? $dev->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i')
                        : now()->timezone('Asia/Jakarta')->format('d-m-Y H:i'),
                ];
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'One-Year Development berhasil disimpan.',
                'data'    => $created,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan One-Year Development.',
            ], 500);
        }
    }

    public function submitMidYear(Request $request, $employee_id)
    {
        $validator = Validator::make($request->all(), [
            'idp_id'   => 'required|array',
            'idp_id.*' => 'required|integer|exists:idp,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal : IDP ID harus dikirim.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $idpIds = $request->input('idp_id');

        DB::beginTransaction();

        try {
            $midDevs = Development::with('employee')
                ->where('employee_id', $employee_id)
                ->where('status', 'draft')
                ->whereIn('idp_id', $idpIds)
                ->get();

            foreach ($midDevs as $mid) {
                $mid->status = 'submitted';
                $mid->save();

                $this->seedStepsForIcp($mid);
            }

            DB::commit();

            $updatedCount = $midDevs->count();

            return response()->json([
                'status'  => 'success',
                'message' => "$updatedCount data Mid-Year Development berhasil disubmit.",
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal submit mid-year development', [
                'employee_id' => $employee_id,
                'exception'   => $e,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat submit Mid-Year Development.',
            ], 500);
        }
    }

    public function submitOneYear(Request $request, $employee_id)
    {
        $validator = Validator::make($request->all(), [
            'idp_id'   => 'required|array',
            'idp_id.*' => 'required|integer|exists:idp,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal : IDP ID harus dikirim.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $idpIds = $request->input('idp_id');

        DB::beginTransaction();

        try {
            $oneDevs = DevelopmentOne::with('employee')
                ->where('employee_id', $employee_id)
                ->where('status', 'draft')
                ->whereIn('idp_id', $idpIds)
                ->get();

            foreach ($oneDevs as $one) {
                $one->status = 'submitted';
                $one->save();

                $this->seedStepsForIcp($one);
            }

            DB::commit();

            $updatedCount = $oneDevs->count();

            return response()->json([
                'status'  => 'success',
                'message' => "$updatedCount data One-Year Development berhasil disubmit.",
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal submit one-year development', [
                'employee_id' => $employee_id,
                'exception'   => $e,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat submit One-Year Development.',
            ], 500);
        }
    }

    public function approval(Request $request, $company = null)
    {
        $title  = 'Approval';
        $filter = (string) $request->query('filter', 'all');

        return view('website.approval.dev.index', [
            'title'   => $title,
            'company' => $company,
            'filter'  => $filter,
        ]);
    }

    public function approvalShow($employeeId)
    {
        $rolesToMatch = $this->currentApproverRoleKeys();
        if (empty($rolesToMatch)) {
            abort(403, 'Employee tidak ditemukan untuk user ini.');
        }

        $employee = Employee::with('department')->findOrFail($employeeId);

        $steps = $this->basePendingStepsQuery($rolesToMatch)
            ->whereHas('oneDevelopment', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->orderBy('step_order', 'asc')
            ->get();

        $steps = $steps->filter(function ($step) {
            $dev = $step->oneDevelopment;
            if (!$dev) return false;

            return $dev->steps->every(function ($x) use ($step) {
                if ($x->step_order < $step->step_order && $x->status !== 'done') return false;
                return true;
            });
        });

        $oneDevs = $steps->map(fn($s) => $s->oneDevelopment)
            ->filter()
            ->unique('id')
            ->values();

        $oneDevs->loadMissing([
            'idp.alc',
            'idp.developments'
        ]);

        return view('website.approval.dev.show', compact('employee', 'oneDevs'));
    }

    public function approvalJson(Request $request)
    {
        $rolesToMatch = $this->currentApproverRoleKeys();
        if (empty($rolesToMatch)) {
            return response()->json(['data' => [], 'meta' => ['total' => 0]]);
        }

        $filter  = (string) $request->query('filter', 'all');
        $search  = trim((string) $request->query('search', ''));
        $company = $request->query('company');

        $query = $this->basePendingStepsQuery($rolesToMatch);

        // filter company
        if ($company) {
            $query->whereHas('oneDevelopment.employee', function ($q) use ($company) {
                $q->where('company', $company);
            });
        }

        // filter posisi
        if ($filter !== 'all') {
            $query->whereHas('oneDevelopment.employee', function ($q) use ($filter) {
                $q->where('position', $filter);
            });
        }

        // search nama/npk
        if ($search !== '') {
            $query->whereHas('oneDevelopment.employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('npk', 'like', "%{$search}%");
            });
        }

        // ambil step pending
        $steps = $query->orderBy('step_order', 'asc')->get();

        // VALIDASI step-order: hanya step yang step sebelumnya sudah done
        $steps = $steps->filter(function ($step) {
            $dev = $step->oneDevelopment;
            if (!$dev) return false;

            return $dev->steps->every(function ($x) use ($step) {
                if ($x->step_order < $step->step_order && $x->status !== 'done') return false;
                return true;
            });
        });

        // GROUP BY EMPLOYEE
        $grouped = $steps->groupBy(fn($s) => $s->oneDevelopment?->employee_id)->filter();

        $rows = [];
        $no = 1;

        foreach ($grouped as $employeeId => $employeeSteps) {
            $devSample = $employeeSteps->first()->oneDevelopment;
            $emp = $devSample?->employee;

            if (!$emp) continue;

            $rows[] = [
                'no'          => $no++,
                'employee_id' => $emp->id,
                'employee'    => [
                    'npk'        => $emp->npk,
                    'name'       => $emp->name,
                    'company'    => $emp->company,
                    'position'   => $emp->position,
                    'department' => $emp->department->name ?? null,
                    'grade'      => $emp->grade,
                ],
                // optional: jumlah item pending (buat badge)
                'pending_count' => $employeeSteps
                    ->map(fn($s) => $s->development_one_id)
                    ->unique()
                    ->count(),
            ];
        }

        return response()->json([
            'data' => $rows,
            'meta' => ['total' => count($rows)],
        ]);
    }

    public function approve($id)
    {
        $user = auth()->user();
        $me   = $user?->employee;

        if (!$me) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Employee tidak ditemukan untuk user ini.',
            ], 403);
        }

        $role         = ApprovalHelper::roleKeyFor($me);
        $rolesToMatch = ApprovalHelper::synonymsForSearch($role);

        /**
         * Ambil semua step pending untuk role approver ini,
         * khusus untuk development milik employee target ($id).
         *
         * Kita ambil step pending TERAWAL per development (mid/one)
         * dengan grouping manual via key "mid:{id}" / "one:{id}".
         */
        $steps = DevelopmentApprovalStep::query()
            ->where('status', 'pending')
            ->whereIn('role', $rolesToMatch)
            ->where(function ($q) use ($id) {
                $q->whereHas('midDevelopment', function ($m) use ($id) {
                    $m->where('employee_id', $id);
                })->orWhereHas('oneDevelopment', function ($o) use ($id) {
                    $o->where('employee_id', $id);
                });
            })
            ->orderBy('step_order', 'asc')
            ->get();

        if ($steps->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Approval step tidak ditemukan atau sudah diproses.',
            ], 404);
        }

        // Ambil hanya step TERAWAL per development
        $firstStepsByDev = [];
        foreach ($steps as $st) {
            $key = $st->development_mid_id
                ? 'mid:' . $st->development_mid_id
                : 'one:' . $st->development_one_id;

            if (!isset($firstStepsByDev[$key])) {
                $firstStepsByDev[$key] = $st;
            }
        }

        DB::beginTransaction();
        try {
            $processed = 0;

            foreach ($firstStepsByDev as $step) {
                // mark step done
                $step->status   = 'done';
                $step->actor_id = $me->id;
                $step->acted_at = now();
                $step->save();

                // dev terkait
                $dev = $step->midDevelopment ?? $step->oneDevelopment;
                if (!$dev) {
                    throw new \RuntimeException('Development terkait tidak ditemukan.');
                }

                // cek sisa pending step untuk development ini
                $pendingCount = $dev->steps()
                    ->where('status', 'pending')
                    ->count();

                $dev->status = ($pendingCount === 0) ? 'approved' : 'checked';
                $dev->save();

                $processed++;
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Development berhasil di-approve. Terproses: {$processed} item.",
                'processed' => $processed,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses approve: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function revise(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $user = auth()->user();
        $me   = $user?->employee;

        if (!$me) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Employee tidak ditemukan untuk user ini.',
            ], 403);
        }

        $role         = ApprovalHelper::roleKeyFor($me);
        $rolesToMatch = ApprovalHelper::synonymsForSearch($role);

        $steps = DevelopmentApprovalStep::query()
            ->where('status', 'pending')
            ->whereIn('role', $rolesToMatch)
            ->where(function ($q) use ($id) {
                $q->whereHas('midDevelopment', function ($m) use ($id) {
                    $m->where('employee_id', $id);
                })->orWhereHas('oneDevelopment', function ($o) use ($id) {
                    $o->where('employee_id', $id);
                });
            })
            ->orderBy('step_order', 'asc')
            ->get();

        if ($steps->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Approval step tidak ditemukan atau sudah diproses.',
            ], 404);
        }

        // Ambil hanya step TERAWAL per development
        $firstStepsByDev = [];
        foreach ($steps as $st) {
            $key = $st->development_mid_id
                ? 'mid:' . $st->development_mid_id
                : 'one:' . $st->development_one_id;

            if (!isset($firstStepsByDev[$key])) {
                $firstStepsByDev[$key] = $st;
            }
        }

        DB::beginTransaction();
        try {
            $processed = 0;

            foreach ($firstStepsByDev as $step) {
                $step->status   = 'revised';
                $step->actor_id = $me->id;
                $step->acted_at = now();
                $step->note     = $request->note;
                $step->save();

                $dev = $step->midDevelopment ?? $step->oneDevelopment;
                if (!$dev) {
                    throw new \RuntimeException('Development terkait tidak ditemukan.');
                }

                // konsistenkan status dev kamu: 'revised' atau 'revise'
                $dev->status = 'revised';
                $dev->save();

                $processed++;
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Revisi berhasil dikirim. Terproses: {$processed} item.",
                'processed' => $processed,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim revisi: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function seedStepsForIcp($model): void
    {
        if (!($model instanceof Development) && !($model instanceof DevelopmentOne)) {
            return;
        }

        $owner = $model->employee;

        if (!$owner) {
            return;
        }

        $chain = ApprovalHelper::expectedChainForEmployee($owner);

        $model->steps()->delete();

        foreach ($chain as $i => $s) {
            DevelopmentApprovalStep::create([
                'development_mid_id'  => $model instanceof Development ? $model->id : null,
                'development_one_id'  => $model instanceof DevelopmentOne ? $model->id : null,
                'step_order'          => $i + 1,
                'type'                => $s['type'],
                'role'                => $s['role'],
                'label'               => $s['label'],
            ]);
        }

        if (empty($chain)) {
            $model->status = 'approved';
            $model->save();
        }
    }

    private function currentApproverRoleKeys()
    {
        $user = auth()->user();
        $me = $user->employee;

        if(!$me) return [];

        $role = ApprovalHelper::roleKeyFor($me);
        return ApprovalHelper::synonymsForSearch($role);
    }

    private function basePendingStepsQuery(array $rolesToMatch)
    {
        return DevelopmentApprovalStep::query()
        ->with([
            'oneDevelopment.employee.department',
            'oneDevelopment.idp',
            'oneDevelopment.steps',
        ])
        ->whereIn('role', $rolesToMatch)
        ->where('status', 'pending')
        ->whereHas('oneDevelopment', function ($q) {
            $q->where('status', '!=', 'approved');
        });
    }
}
