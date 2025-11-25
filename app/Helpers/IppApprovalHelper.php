<?php

namespace App\Helpers;

use App\Models\Ipp;
use App\Models\Employee;
use Illuminate\Support\Collection;

class IppApprovalHelper
{
    /**
     * Ambil semua IPP bawahan yg perlu check/approve
     * untuk seorang employee (me), dengan logic yg sama
     * seperti di IppController::approvalJson.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getApprovalRows(Employee $me, string $year = 'all', string $search = ''): Collection
    {
        if (! $me) {
            return collect();
        }

        $year   = strtolower((string) $year);
        $search = trim((string) $search);

        // Tentukan level check & approve
        $checkLevel   = method_exists($me, 'getCreateAuth') ? $me->getCreateAuth() : null;
        $approveLevel = method_exists($me, 'getFirstApproval') ? $me->getFirstApproval() : null;

        $subCheckIds   = collect();
        $subApproveIds = collect();

        if ($checkLevel && method_exists($me, 'getSubordinatesByLevel')) {
            $subCheckIds = $me->getSubordinatesByLevel($checkLevel)->pluck('id');
        }
        if ($approveLevel && method_exists($me, 'getSubordinatesByLevel')) {
            $subApproveIds = $me->getSubordinatesByLevel($approveLevel)->pluck('id');
        }

        // Base query IPP
        $baseIPP = Ipp::with(['employee.user'])
            ->when($year !== 'all', fn($q) => $q->where('on_year', $year))
            ->where('employee_id', '!=', $me->id) // jangan milik saya sendiri
            // skip owner role HRD
            ->whereHas('employee.user', function ($q) {
                $q->whereRaw('UPPER(role) != ?', ['HRD']);
            })
            // optional search by owner
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('employee', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('npk', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            });

        // Stage 1: CHECK
        $checkIpps = collect();
        if ($subCheckIds->isNotEmpty()) {
            $checkIpps = (clone $baseIPP)
                ->where('status', 'submitted')
                ->whereHas('employee', function ($q) use ($subCheckIds) {
                    $q->whereIn('id', $subCheckIds->all());
                })
                ->get()
                ->map(function (Ipp $ipp) {
                    $ipp->stage = 'check';
                    return $ipp;
                });
        }

        // Stage 2: APPROVE
        $approveIpps = collect();
        if ($subApproveIds->isNotEmpty()) {
            $approveIpps = (clone $baseIPP)
                ->where('status', 'checked')
                ->whereHas('employee', function ($q) use ($subApproveIds) {
                    $q->whereIn('id', $subApproveIds->all());
                })
                ->whereNotIn('id', $checkIpps->pluck('id')->all())
                ->get()
                ->map(function (Ipp $ipp) {
                    $ipp->stage = 'approve';
                    return $ipp;
                });
        }

        // Merge + sort + map ke bentuk array (tanpa "no")
        $all = $checkIpps->merge($approveIpps)
            ->sortByDesc(fn(Ipp $x) => $x->updated_at ?? $x->created_at)
            ->values();

        return $all->map(function (Ipp $ipp) {
            $e = $ipp->employee;

            return [
                'id'         => $ipp->id,
                'stage'      => $ipp->stage,            // 'check' atau 'approve'
                'status'     => (string) $ipp->status,  // 'submitted' atau 'checked'
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
