<?php

namespace App\Services;

use App\Helpers\ApprovalHelper;
use App\Models\Icp;
use App\Models\IcpApprovalStep;
use App\Models\IcpSnapshot;
use Illuminate\Support\Facades\DB;

class IcpSnapshotUpdateService
{
    public function run(Icp $icp, array $payload, ?int $planYear = null, ?string $reason = null)
    {
        return DB::transaction(function () use ($icp, $payload, $planYear, $reason) {
            $icp->load(['details', 'steps']);

            IcpSnapshot::create([
                'icp_id'     => $icp->id,
                'plan_year'  => $planYear,
                'reason'     => $reason ?? 'Evalution',
                'icp'        => $icp->toArray(),
                'details'    => $icp->details->toArray(),
                'steps'      => $icp->steps->toArray(),
                'created_by' => auth()->user()->employee->id
            ]);

            $icp->update([
                'employee_id'   => $payload['employee_id'] ?? $icp->employee_id,
                'aspiration'    => $payload['aspiration'] ?? $icp->aspiration,
                'career_Target' => $payload['career_Target'] ?? $icp->career_Target,
                'date'          => $payload['date'] ?? $icp->date,
                'status' => Icp::STATUS_DRAFT, // status awal "Draft" (4)
            ]);
            $this->seedStepsForIcp($icp);

            $icp->details()->delete();

            $clean = fn($v, $m = 255) => mb_substr(trim(strip_tags((string)$v)), 0, $m);
            foreach ($payload['stages'] as $stage) {
                $year = (int)$stage['plan_year'];
                $job = $clean($stage['job_function'], 100);
                $pos = $clean($stage['position'], 50);
                $level = $clean($stage['level'], 30);

                $rows = [];
                foreach ($stage['details'] as $d) {
                    $rows[] = [
                        'plan_year'                => $year,
                        'job_function'             => $job,
                        'position'                 => $pos,
                        'level'                    => $level,
                        'current_technical'        => $clean($d['current_technical']),
                        'current_nontechnical'     => $clean($d['current_nontechnical']),
                        'required_technical'       => $clean($d['required_technical']),
                        'required_nontechnical'    => $clean($d['required_nontechnical']),
                        'development_technical'    => $clean($d['development_technical']),
                        'development_nontechnical' => $clean($d['development_nontechnical']),
                    ];
                }
                $icp->details()->createMany($rows);
            }
            $icp->last_evaluated_at = now();
            $icp->save();

            return $icp->fresh(['details', 'steps']);
        });
    }
    public function seedStepsForIcp(Icp $icp)
    {
        $owner = $icp->employee()->first();
        $chain = ApprovalHelper::expectedChainForEmployee($owner);
        $icp->steps()->delete();
        foreach ($chain as $i => $s) {
            IcpApprovalStep::create([
                'icp_id'     => $icp->id,
                'step_order' => $i + 1,
                'type'       => $s['type'],
                'role'       => $s['role'],
                'label'      => $s['label']
            ]);
        }

        // kalau chain kosong, langsung approve
        if (empty($chain)) {
            $icp->status = Icp::STATUS_APPROVED; // 3
            $icp->save();
        }
    }
}
