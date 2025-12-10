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
