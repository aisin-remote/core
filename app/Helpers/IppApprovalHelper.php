<?php

namespace App\Helpers;

use App\Models\Ipp;
use App\Models\Employee;
use App\Models\IppApprovalStep;
use Illuminate\Support\Collection;

class IppApprovalHelper
{
    /**
     * Ambil semua IPP yang perlu di-check/approve oleh $me
     * berdasarkan ipp_approval_steps + subordinate level.
     */
    public static function getApprovalRows(Employee $me, string $year = 'all', string $search = ''): Collection
    {
        if (! $me) {
            return collect();
        }

        $year   = strtolower((string) $year);
        $search = trim((string) $search);

        // === 1. Tentukan role kanonik user sekarang ===
        $role = ApprovalHelper::roleKeyFor($me);

        $rolesToMatch = [$role];
        // toleransi label legacy
        if ($role === 'director') {
            $rolesToMatch[] = 'direktur';
        }
        if ($role === 'president') {
            $rolesToMatch[] = 'presiden';
        }
        if ($role === 'gm') {
            $rolesToMatch[] = 'general manager';
        }

        if ($role === 'vpd') {
            $rolesToMatch[] = 'vpd';
        }


        // === 2. Tentukan bawahan untuk CHECK & APPROVE (logic lama) ===
        $subCheckIds   = collect();
        $subApproveIds = collect();

        if (method_exists($me, 'getCreateAuth') && method_exists($me, 'getSubordinatesByLevel')) {
            $checkLevel = $me->getCreateAuth();
            if ($checkLevel) {
                $subCheckIds = $me->getSubordinatesByLevel($checkLevel)->pluck('id');
            }
        }

        if (method_exists($me, 'getFirstApproval') && method_exists($me, 'getSubordinatesByLevel')) {
            $approveLevel = $me->getFirstApproval();
            if ($approveLevel) {
                $subApproveIds = $me->getSubordinatesByLevel($approveLevel)->pluck('id');
            }
        }

        if ($role === 'vpd' && $subCheckIds->isEmpty() && $subApproveIds->isNotEmpty()) {
            $subCheckIds = $subApproveIds;
        }

        // Kalau nggak punya bawahan di level itu, ya kosong
        $checkSteps = collect();
        $approveSteps = collect();

        // Closure base filter IPP (tahun, search, skip HRD, skip diri sendiri)
        $baseIppFilter = function ($q) use ($year, $search, $me) {
            if ($year !== 'all') {
                $q->where('on_year', $year);
            }

            // jangan IPP milik saya sendiri
            $q->where('employee_id', '!=', $me->id);

            // skip owner role HRD
            $q->whereHas('employee.user', function ($qq) {
                $qq->whereRaw('UPPER(role) != ?', ['HRD']);
            });

            // optional search
            if ($search !== '') {
                $q->whereHas('employee', function ($qqq) use ($search) {
                    $qqq->where('name', 'like', "%{$search}%")
                        ->orWhere('npk', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            }
        };

        // === 3a. STEP CHECK: bawahan level check + role saya ===
        if ($subCheckIds->isNotEmpty()) {
            $checkSteps = IppApprovalStep::with(['ipp.employee.user', 'ipp.steps'])
                ->where('type', 'check')
                ->where('status', 'pending')
                ->whereIn('role', $rolesToMatch)
                ->whereHas('ipp', function ($q) use ($subCheckIds, $baseIppFilter) {
                    $q->whereIn('employee_id', $subCheckIds->all());
                    $baseIppFilter($q);
                })
                ->orderBy('step_order')
                ->get();
        }

        // === 3b. STEP APPROVE: bawahan level approve + role saya ===
        if ($subApproveIds->isNotEmpty()) {
            $approveSteps = IppApprovalStep::with(['ipp.employee.user', 'ipp.steps'])
                ->where('type', 'approve')
                ->where('status', 'pending')
                ->whereIn('role', $rolesToMatch)
                ->whereHas('ipp', function ($q) use ($subApproveIds, $baseIppFilter) {
                    $q->whereIn('employee_id', $subApproveIds->all());
                    $baseIppFilter($q);
                })
                ->orderBy('step_order')
                ->get();
        }

        // === 4. Gabungkan, filter hanya step yang "giliran saya" (previous step sudah done) ===
        $steps = $checkSteps->merge($approveSteps)
            ->filter(function (IppApprovalStep $s) {
                return $s->ipp->steps->every(function ($x) use ($s) {
                    return $x->step_order >= $s->step_order || $x->status === 'done';
                });
            })
            // sort mirip helper lama: paling baru di atas
            ->sortByDesc(function (IppApprovalStep $s) {
                $ipp = $s->ipp;
                return $ipp->updated_at ?? $ipp->created_at;
            })
            ->values();

        // === 5. Map ke bentuk array untuk API JSON ===
        return $steps->map(function (IppApprovalStep $step) {
            $ipp = $step->ipp;
            $e   = $ipp->employee;

            return [
                'id'         => $ipp->id,
                'step_id'    => $step->id,
                'stage'      => $step->type,                 // 'check' atau 'approve'
                'status'     => (string) $ipp->status,
                'on_year'    => (string) $ipp->on_year,
                'updated_at' => optional($ipp->updated_at)->toDateTimeString(),
                'employee'   => [
                    'id'         => $e->id,
                    'npk'        => (string) $e->npk,
                    'name'       => (string) $e->name,
                    'company'    => (string) $e->company_name,
                    'position'   => (string) $e->position,
                    'department' => (string) ($e->bagian ?? ''),
                    'grade'      => (string) ($e->grade ?? ''),
                    'role'       => optional($e->user)->role,
                ],
            ];
        });
    }
}
