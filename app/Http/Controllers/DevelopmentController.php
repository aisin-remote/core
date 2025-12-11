<?php

namespace App\Http\Controllers;

use App\Helpers\ApprovalHelper;
use App\Models\Assessment;
use App\Models\Development;
use App\Models\DevelopmentApprovalStep;
use App\Models\DevelopmentOne;
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

        $idps = Idp::with('alc')
            ->where('assessment_id', $assessment->id)
            ->whereIn('alc_id', $relevantAlcIds)
            ->get()
            ->groupBy('alc_id');

        $midDevs = Development::where('employee_id', $employee_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('idp_id');

        $oneDevs = DevelopmentOne::where('employee_id', $employee_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('idp_id');

        $title = 'IDP Development - ' . ($assessment->employee->name ?? '-');

        return view('website.idp.development', compact('assessment', 'title', 'idps', 'midDevs', 'oneDevs'));
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

    public function approvalJson(Request $request)
    {
        $user = auth()->user();
        $me   = $user?->employee;

        if (!$me) {
            return response()->json([
                'data' => [],
                'meta' => ['total' => 0],
            ]);
        }

        $role = ApprovalHelper::roleKeyFor($me);

        $rolesToMatch = ApprovalHelper::synonymsForSearch($role);

        $filter  = (string) $request->query('filter', 'all');
        $search  = trim((string) $request->query('search', ''));
        $company = $request->query('company');

        $query = DevelopmentApprovalStep::query()
            ->with([
                'oneDevelopment.employee.department',
                'oneDevelopment.steps',
                'oneDevelopment.idp',
            ])
            ->whereIn('role', $rolesToMatch)
            ->where('status', 'pending')
            ->where(function ($q) {
                // Hanya ambil development yang belum final approve
                $q->whereHas('oneDevelopment', function ($q2) {
                    $q2->where('status', '!=', 'approved');
                });
            });

        if ($company) {
            $query->where(function ($q) use ($company) {
                $q->whereHas('midDevelopment.employee', function ($q2) use ($company) {
                    $q2->where('company', $company);
                })->orWhereHas('oneDevelopment.employee', function ($q2) use ($company) {
                    $q2->where('company', $company);
                });
            });
        }

        if ($filter !== 'all') {
            $query->where(function ($q) use ($filter) {
                $q->whereHas('midDevelopment.employee', function ($q2) use ($filter) {
                    $q2->where('position', $filter);
                })->orWhereHas('oneDevelopment.employee', function ($q2) use ($filter) {
                    $q2->where('position', $filter);
                });
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('midDevelopment.employee', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('npk', 'like', "%{$search}%");
                })->orWhereHas('oneDevelopment.employee', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('npk', 'like', "%{$search}%");
                });
            });
        }

        $steps = $query
            ->orderBy('step_order')
            ->get();

        $steps = $steps->filter(function ($step) {
            $dev = $step->midDevelopment ?: $step->oneDevelopment;
            if (!$dev) {
                return false;
            }

            $allSteps = $dev->steps;

            return $allSteps->every(function ($x) use ($step) {
                if ($x->step_order < $step->step_order && $x->status !== 'done') {
                    return false;
                }
                return true;
            });
        });

        // ====== GROUP BY EMPLOYEE ======
        $grouped = $steps
            ->groupBy(function ($step) {
                $dev = $step->midDevelopment ?: $step->oneDevelopment;
                return $dev?->employee_id;
            })
            ->filter(function ($group, $employeeId) {
                return !is_null($employeeId);
            })
            ->values();

        $rows = [];
        foreach ($grouped as $index => $group) {
            $firstStep = $group->first();
            $sampleDev = $firstStep->midDevelopment ?: $firstStep->oneDevelopment;
            $emp       = $sampleDev?->employee;

            if (!$emp) {
                continue;
            }

            // Kumpulkan unique Development One-Year
            $oneDevs = $group
                ->filter(function ($s) {
                    return !is_null($s->development_one_id) && $s->oneDevelopment;
                })
                ->map(function ($s) {
                    $dev = $s->oneDevelopment;
                    return [
                        'id'                  => $dev->id,
                        'idp_id'              => $dev->idp_id,
                        'development_program' => $dev->development_program,
                        'evaluation_result'   => $dev->evaluation_result,
                        'status'              => $dev->status,
                    ];
                })
                ->unique('id')
                ->values()
                ->all();

            // Status "headline" employee di-list ini boleh ambil dari salah satu dev
            $headlineStatus = $sampleDev?->status ?? 'submitted';

            $rows[] = [
                'no'          => $index + 1,
                'employee_id' => $emp->id,
                'status'      => $headlineStatus,
                'employee'    => [
                    'npk'        => $emp->npk,
                    'name'       => $emp->name,
                    'company'    => $emp->company,
                    'position'   => $emp->position,
                    'department' => $emp->department->name ?? null,
                    'grade'      => $emp->grade,
                ],
                'one_devs'    => $oneDevs,   // untuk accordion One-Year
            ];
        }

        return response()->json([
            'data' => $rows,
            'meta' => [
                'total' => count($rows),
            ],
        ]);
    }

    public function approve($id)
    {
        $user = auth()->user();
        $me = $user->employee;

        if(!$me){
            return response()->json([
                'status'  => 'error',
                'message' => 'Employee tidak ditemukan untuk user ini.',
            ], 403);
        }

        $role         = ApprovalHelper::roleKeyFor($me);
        $rolesToMatch = ApprovalHelper::synonymsForSearch($role);

        $step = DevelopmentApprovalStep::where('status', 'pending')
        ->whereIn('role', $rolesToMatch)
        ->where(function ($q) use ($id) {
            $q->where('development_mid_id', $id)
            ->orWhere('development_one_id', $id);
        })
        ->orderBy('step_order')
        ->first();

        if(!$step){
            return response()->json([
                'status'  => 'error',
                'message' => 'Approval step tidak ditemukan atau sudah diproses.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $step->status = 'done';
            $step->actor_id = $me->id;
            $step->acted_at = now();
            $step->save();

            $dev = $step->midDevelopment ?? $step->oneDevelopment;
            if(!$dev) {
                throw new RuntimeException('Development terkait tidak ditemukan.');
            }
            
            $pendingCount = $dev->steps()
            ->where('status', 'pending')
            ->count();

            if($pendingCount === 0){
                $dev->status = 'approved';
            } else {
                $dev->status = 'checked';
            }

            $dev->save();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Development berhasil di-approve.',
            ]);
        } catch (\Throwable $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses approve: ' . $e->getMessage(),
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
}
