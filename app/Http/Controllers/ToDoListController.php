<?php

namespace App\Http\Controllers;

use App\Helpers\ApprovalHelper;
use App\Helpers\IppApprovalHelper;
use App\Models\Employee;
use App\Models\Hav;
use App\Models\Icp;
use App\Models\IcpApprovalStep;
use App\Models\Idp;
use App\Models\IpaHeader;
use App\Models\Ipp;
use App\Models\Rtc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToDoListController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $employee = $user->employee;

        [$normalized, $subCreate, $subCheck, $subApprove] = $this->resolveScopes($user, $employee);

        $assessments    = $this->getLatestHavAssessments($subCreate);
        $unassignedIdps = $this->buildUnassignedIdps($assessments);

        $draftIdpCollection   = $this->getDraftIdps($subCreate);
        $reviseIdpCollection  = $this->getReviseIdps($subCreate);
        $pendingIdpCollection = $this->getPendingIdps($subCheck, $subApprove, $normalized);

        $allIdpTasks = $this->mergeAllIdpTasks(
            $unassignedIdps,
            $draftIdpCollection,
            $reviseIdpCollection,
            $pendingIdpCollection
        );

        $allHavTasks = $this->getHavTasks($subCheck);
        $allRtcTasks = $this->getRtcTasks($subCheck, $subApprove);
        $allIppTasks = $this->getIppTasks($employee, $user);
        $allIpaTasks = $this->getIpaTasks($employee, $user);
        $allIcpTasks = $this->getIcpTasks();

        return view('website.todolist.index', compact(
            'allIdpTasks',
            'allHavTasks',
            'allRtcTasks',
            'allIppTasks',
            'allIpaTasks',
            'allIcpTasks'
        ));
    }

    /**
     * Tentukan normalized position dan scope karyawan yang boleh dibuat/check/approve.
     */
    private function resolveScopes($user, $employee): array
    {
        $role = $user->role;

        // HRD = superadmin: akses semua karyawan
        if ($role === 'HRD') {
            $allIds     = Employee::pluck('id')->toArray();
            $subCreate  = $allIds;
            $subCheck   = $allIds;
            $subApprove = $allIds;
            $normalized = 'hrd';

            return [$normalized, $subCreate, $subCheck, $subApprove];
        }

        // Non-HRD: pakai aturan existing
        $normalized  = $employee?->getNormalizedPosition();
        $createLevel = $employee?->getCreateAuth();
        $subCreate   = $employee?->getSubordinatesByLevel($createLevel)->pluck('id')->toArray() ?? [];

        $checkLevel   = $employee?->getFirstApproval();
        $approveLevel = $employee?->getFinalApproval();

        if ($normalized === 'vpd') {
            // VPD: check = GM, approve = Manager
            $subCheck   = $employee->getSubordinatesByLevel($checkLevel, ['gm'])->pluck('id')->toArray();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel, ['manager'])->pluck('id')->toArray();
        } else {
            $subCheck   = $employee->getSubordinatesByLevel($checkLevel)->pluck('id')->toArray();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel)->pluck('id')->toArray();
        }

        return [$normalized, $subCreate, $subCheck, $subApprove];
    }

    /**
     * Ambil HAV terbaru per karyawan (hanya yang bisa dibuat oleh user/HRD).
     */
    private function getLatestHavAssessments(array $subCreate)
    {
        return Hav::with(['employee', 'details.idp', 'details.alc'])
            ->whereIn('employee_id', $subCreate)
            ->whereIn('id', function ($q) {
                $q->selectRaw('id')
                    ->from('havs as a')
                    ->whereRaw('a.created_at = (SELECT MAX(created_at) FROM havs WHERE employee_id = a.employee_id)');
            })
            ->get();
    }

    /**
     * Bangun list HAV detail yang belum dibuat IDP (unassigned).
     */
    private function buildUnassignedIdps($assessments)
    {
        $emp = [];

        foreach ($assessments as $assessment) {
            foreach ($assessment->details as $detail) {
                $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);

                if ((int) $detail->score < 3 || $detail->suggestion_development !== null) {
                    $emp[$assessment->employee_id][] = [
                        'hav_detail_id' => $detail->id,
                        'alc_id'        => $detail->alc_id,
                        'alc_name'      => $detail->alc->name ?? 'Unknown',
                    ];
                }
            }
        }

        $employeeNames   = $assessments->pluck('employee.name', 'employee_id')->toArray();
        $employeeNpk     = $assessments->pluck('employee.npk', 'employee_id')->toArray();
        $employeeCompany = $assessments->pluck('employee.company_name', 'employee_id')->toArray();

        $notExistInIdp = [];

        foreach ($emp as $employeeId => $items) {
            foreach ($items as $item) {
                $exists = Idp::where('hav_detail_id', $item['hav_detail_id'])
                    ->where('alc_id', $item['alc_id'])
                    ->exists();

                if (! $exists) {
                    $notExistInIdp[] = [
                        'employee_name'    => $employeeNames[$employeeId]   ?? 'Unknown',
                        'employee_npk'     => $employeeNpk[$employeeId]     ?? 'Unknown',
                        'employee_company' => $employeeCompany[$employeeId] ?? 'Unknown',
                        'hav_detail_id'    => $item['hav_detail_id'],
                        'alc_id'           => $item['alc_id'],
                        'alc_name'         => $item['alc_name'] ?? 'Unknown',
                    ];

                    // 1 task per employee
                    break;
                }
            }
        }

        return collect($notExistInIdp)->map(function ($item) {
            $item['type'] = 'unassigned';

            return $item;
        });
    }

    /**
     * Ambil IDP berstatus draft (status = 0).
     */
    private function getDraftIdps(array $subCreate)
    {
        $draftIdps = Employee::with([
            'hav.details' => function ($q) {
                $q->whereHas('idp', fn($qq) => $qq->where('status', 0))
                    ->with(['idp' => fn($qq) => $qq->where('status', 0)])
                    ->orderBy('created_at')
                    ->take(1);
            },
        ])
            ->whereIn('id', $subCreate)
            ->whereHas('hav.details.idp', fn($q) => $q->where('status', 0))
            ->get();

        return $draftIdps->map(function ($employee) {
            $hav    = $employee->hav->first();
            $detail = optional($hav?->details->first());
            $idp    = optional($detail?->idp);

            return [
                'type'             => 'draft',
                'employee_name'    => $employee->name,
                'employee_npk'     => $employee->npk,
                'employee_company' => $employee->company_name,
                'category'         => $idp?->category ?? '-',
                'program'          => $idp?->development_program ?? '-',
                'target'           => $idp?->development_target ?? '-',
            ];
        });
    }

    /**
     * Ambil IDP yang perlu revisi (status = -1).
     */
    private function getReviseIdps(array $subCreate)
    {
        $reviseIdps = Employee::with([
            'hav.details.idp' => fn($q) => $q->where('status', -1)->orderBy('created_at'),
        ])
            ->whereIn('id', $subCreate)
            ->whereHas('hav.details.idp', fn($q) => $q->where('status', -1))
            ->get();

        return $reviseIdps->flatMap(function ($employee) {
            return $employee->hav->flatMap(function ($hav) use ($employee) {
                return collect($hav->details)->flatMap(function ($detail) use ($employee) {
                    $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);

                    return $idps
                        ->filter(fn($idp) => $idp->status === -1)
                        ->map(function ($idp) use ($employee) {
                            return [
                                'type'             => 'revise',
                                'employee_name'    => $employee->name,
                                'employee_npk'     => $employee->npk,
                                'employee_company' => $employee->company_name,
                                'category'         => $idp->category ?? '-',
                                'program'          => $idp->development_program ?? '-',
                                'target'           => $idp->development_target ?? '-',
                                'created_at'       => $idp->created_at,
                            ];
                        });
                });
            });
        });
    }

    /**
     * Ambil IDP pending (status 1/2/3) sesuai peran (check/approve/president).
     */
    private function getPendingIdps(array $subCheck, array $subApprove, ?string $normalized)
    {
        // IDP status 1 (need check)
        $checkIdps = Employee::with([
            'hav.details.idp' => fn($q) => $q->where('status', 1)->orderBy('created_at'),
        ])
            ->whereIn('id', $subCheck)
            ->whereHas('hav.details.idp', fn($q) => $q->where('status', 1))
            ->get();

        // IDP status 2 (need approve)
        $approveIdpsQuery = Employee::with([
            'hav.details.idp' => fn($q) => $q->orderBy('created_at'),
        ])
            ->whereIn('id', $subApprove)
            ->whereHas('hav.details.idp', fn($q) => $q->where('status', 2));

        if ($normalized === 'president') {
            $approveIdpsQuery->where('position', '!=', 'Manager');
        }

        $approveIdps = $approveIdpsQuery
            ->get()
            ->filter(function ($employee) {
                return $employee->hav->every(function ($hav) {
                    $statuses = collect($hav->details)->flatMap(function ($detail) {
                        $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);

                        return $idps->pluck('status');
                    })->unique();

                    return $statuses->count() === 1 && $statuses->first() === 2;
                });
            });

        // IDP status 3 khusus president
        $presidenApproveIdps = collect();

        if ($normalized === 'president') {
            $presidenApproveIdps = Employee::with([
                'hav.details.idp' => fn($q) => $q->orderBy('created_at'),
            ])
                ->whereIn('id', $subApprove)
                ->whereHas('hav.details.idp', fn($q) => $q->where('status', 3))
                ->whereDoesntHave('hav.details', function ($q) {
                    $q->whereHas('idp', fn($qq) => $qq->where('status', 2));
                })
                ->get()
                ->filter(function ($employee) {
                    return $employee->hav->every(function ($hav) {
                        $statuses = collect($hav->details)->flatMap(function ($detail) {
                            $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);

                            return $idps->pluck('status');
                        })->unique();

                        return $statuses->count() === 1 && $statuses->first() === 3;
                    });
                });
        }

        $pendingIdps = $checkIdps->merge($approveIdps);

        if ($normalized === 'president') {
            $pendingIdps = $pendingIdps->merge($presidenApproveIdps)->unique();
        }

        return $pendingIdps
            ->flatMap(function ($employee) {
                return $employee->hav->flatMap(function ($hav) use ($employee) {
                    return collect($hav->details)->flatMap(function ($detail) use ($employee) {
                        $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);

                        return $idps
                            ->filter(fn($idp) => in_array($idp->status, [1, 2, 3]))
                            ->map(function ($idp) use ($employee) {
                                return [
                                    'type'             => $idp->status === 1 ? 'need_check' : 'need_approval',
                                    'employee_name'    => $employee->name,
                                    'employee_npk'     => $employee->npk,
                                    'employee_company' => $employee->company_name,
                                    'category'         => $idp->category ?? '-',
                                    'program'          => $idp->development_program ?? '-',
                                    'target'           => $idp->development_target ?? '-',
                                    'created_at'       => $idp->created_at,
                                ];
                            });
                    });
                });
            })
            ->sortBy('created_at')
            ->unique('employee_npk')
            ->values();
    }

    /**
     * Gabungkan semua jenis IDP tasks.
     */
    private function mergeAllIdpTasks($unassignedIdps, $draftIdpCollection, $reviseIdpCollection, $pendingIdpCollection)
    {
        return $unassignedIdps
            ->merge($draftIdpCollection)
            ->merge($reviseIdpCollection)
            ->merge($pendingIdpCollection);
    }

    /**
     * HAV tasks (status 0 untuk subCheck).
     */
    private function getHavTasks(array $subCheck)
    {
        return Hav::with('employee')
            ->whereIn('employee_id', $subCheck)
            ->where('status', 0)
            ->get()
            ->unique('employee_id')
            ->values();
    }

    /**
     * ICP tasks: pakai logic approval berbasis IcpApprovalStep.
     * Mengembalikan list step ICP yang pending untuk role user saat ini.
     */
    private function getIcpTasks()
    {
        $me   = auth()->user()->employee;
        $role = ApprovalHelper::roleKeyFor($me);

        // role utama
        $rolesToMatch = [$role];

        // toleransi data lama
        if ($role === 'director') {
            $rolesToMatch[] = 'direktur';
        }

        if ($role === 'president') {
            $rolesToMatch[] = 'presiden';
        }

        if ($role === 'gm') {
            $rolesToMatch[] = 'general manager';
        }

        $steps = IcpApprovalStep::with(['icp.employee', 'icp.steps'])
            ->whereIn('role', $rolesToMatch)
            ->where('status', 'pending')
            ->whereHas('icp', function ($q) {
                $q->where('status', '!=', 4);
            })
            ->orderBy('step_order')
            ->get()
            ->filter(function ($s) {
                return $s->icp->steps->every(function ($x) use ($s) {
                    return $x->step_order >= $s->step_order || $x->status === 'done';
                });
            })
            ->values();

        return $steps;
    }


    /**
     * RTC tasks (status 0/1 sesuai scope).
     */
    private function getRtcTasks(array $subCheck, array $subApprove)
    {
        return Rtc::with('employee')
            ->where(function ($query) use ($subCheck, $subApprove) {
                $query->where(function ($q) use ($subCheck) {
                    $q->whereIn('employee_id', $subCheck)
                        ->where('status', 0);
                })->orWhere(function ($q) use ($subApprove) {
                    $q->whereIn('employee_id', $subApprove)
                        ->where('status', 1);
                });
            })
            ->get();
    }

    private function getIppTasks(
        $employee = null,
        $user = null
    ): array {
        try {
            // ==== Ambil user & employee dari auth sebagai default ====
            $authUser = auth()->user();

            $user     = $user ?? $authUser;
            $employee = $employee ?? $user->employee;
            $year     = now()->year + 1; // hanya untuk pesan, kalau mau, boleh dihapus/diganti

            // =========================
            // 1. IPP SAYA (USER LOGIN)
            // =========================

            if (! $employee) {
                $ippTasks = [
                    'activity_management' => 0,
                    'crp'                 => 0,
                    'people_development'  => 0,
                    'special_assignment'  => 0,
                    'total'               => 0,
                    'status'              => 'Not Created',
                    'employee_name'       => optional($user)->name ?? '',
                    'employee_company'    => '',
                    'employee_npk'        => '',
                ];

                return [
                    'ippTasks'        => $ippTasks,
                    'message'         => 'Employee untuk user login tidak ditemukan.',
                    'subordinateIpps' => [],
                ];
            }

            // Ambil IPP milik employee login (tanpa filter tahun, latest)
            $ipp = Ipp::with('employee')
                ->where('employee_id', $employee->id)
                ->latest('created_at')
                ->first();

            if (! $ipp) {
                $message = "The {$year} IPP has not yet been created.";

                $ippTasks = [
                    'activity_management' => 0,
                    'crp'                 => 0,
                    'people_development'  => 0,
                    'special_assignment'  => 0,
                    'total'               => 0,
                    'status'              => 'Not Created',
                    'employee_name'       => $employee->name ?? '',
                    'employee_company'    => $employee->company_name ?? '',
                    'employee_npk'        => $employee->npk ?? '',
                ];
            } else {
                $summary = is_array($ipp->summary)
                    ? $ipp->summary
                    : (json_decode($ipp->summary, true) ?? []);

                $activityManagement = $summary['activity_management'] ?? 0;
                $crp                = $summary['crp'] ?? 0;
                $peopleDevelopment  = $summary['people_development'] ?? 0;
                $specialAssignment  = $summary['special_assignment'] ?? 0;

                $message = null;

                $ippTasks = [
                    'activity_management' => $activityManagement,
                    'crp'                 => $crp,
                    'people_development'  => $peopleDevelopment,
                    'special_assignment'  => $specialAssignment,
                    'total'               => $summary['total'] ?? array_sum([
                        $activityManagement,
                        $crp,
                        $peopleDevelopment,
                        $specialAssignment,
                    ]),
                    'status'              => $ipp->status ?? 'Not Created',
                    'employee_name'       => optional($ipp->employee)->name ?? '',
                    'employee_company'    => optional($ipp->employee)->company_name ?? '',
                    'employee_npk'        => optional($ipp->employee)->npk ?? '',
                ];
            }

            // =======================================
            // 2. IPP BAWAHAN (CHECK / APPROVE)
            // =======================================

            $subordinateRows = [];

            if ($employee) {
                $subordinateRows = IppApprovalHelper::getApprovalRows($employee, 'all', '')->all();
            }

            return [
                'ippTasks'        => $ippTasks,       // IPP milik user login
                'message'         => $message,        // pesan kalau belum ada IPP
                'subordinateIpps' => $subordinateRows // daftar IPP bawahan butuh check/approve
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'ippTasks'        => [
                    'activity_management' => 0,
                    'crp'                 => 0,
                    'people_development'  => 0,
                    'special_assignment'  => 0,
                    'total'               => 0,
                    'status'              => 'Error',
                    'employee_name'       => optional(optional(auth()->user())->employee)->name ?? '',
                    'employee_company'    => optional(optional(auth()->user())->employee)->company_name ?? '',
                    'employee_npk'        => optional(optional(auth()->user())->employee)->npk ?? '',
                ],
                'message'         => 'Terjadi kesalahan saat mengambil data IPP.',
                'subordinateIpps' => [],
            ];
        }
    }

    private function getIpaTasks($employee = null, $user = null)
    {
        try {
            $authUser = auth()->user();
            $user = $user ?? $authUser;
            $employee = $employee ?? $user->employee;
            $year = now()->year + 1;

            // ==================
            // 1. IPA SAYA (USER LOGIN)
            // ==================
            if (!$employee) {
                $ipaTasks = [
                    'status' => 'Not Created',
                    'year' => null,
                    'employee_name' => optional($user)->name ?? '',
                ];

                return [
                    'ipaTasks' => $ipaTasks,
                    'message' => 'Employee untuk user login tidak ditemukan,',
                    'subordinateIpas' => []
                ];
            }

            $ipaHeader = IpaHeader::with('employee')
                ->where('employee_id', $employee->id)
                ->latest("created_at")
                ->first();
            if (!$ipaHeader) {
                $message = "IPA belum dibuat.";

                $ipaTasks = [
                    'status' => 'Not Created',
                    'year' => null,
                    'employee_name' => $employee->name ?? ''
                ];
            } else {
                $message = null;

                $ipaTasks = [
                    'status' => $ipaHeader->status ?? 'draft',
                    'year' => $ipaHeader->year ?? null,
                    'employee_name' => optional($ipaHeader->employee)->name ?? ''
                ];
            }

            // ==================
            // 2. IPA BAWAHAN (QUEUE SAYA)
            // ==================
            $subordinateIpas = [];
            if ($employee) {
                $empId = $employee->id;
                $q = IpaHeader::with([
                    'employee:id,npk,name,company_name,position,grade',
                    'checkedBy:id,name',
                    'approvedBy:id,name',
                ])
                    ->where(function ($qq) use ($empId) {
                        $qq->where(function ($w) use ($empId) {
                            $w->where('status', 'submitted')
                                ->where('checked_by', $empId);
                        })->orWhere(function ($w) use ($empId) {
                            $w->where('status', 'checked')
                                ->where('approved_by', $empId);
                        });
                    });

                $rows = $q->orderBy('id', 'desc')->get();

                $subordinateIpas = $rows->map(function (IpaHeader $h) use ($empId) {
                    $e = $h->employee;

                    $stage = $h->status === 'submitted'
                        ? 'check'
                        : ($h->status === 'checked' ? 'approve' : $h->status);

                    $canApprove = ($h->status === 'submitted' && (int) $h->checked_by  === (int) $empId)
                        || ($h->status === 'checked'   && (int) $h->approved_by === (int) $empId);

                    return [
                        'id'          => $h->id,
                        'status'      => $h->status,
                        'stage'       => $stage,
                        'can_approve' => $canApprove,
                        'employee'    => [
                            'npk'        => optional($e)->npk,
                            'name'       => optional($e)->name,
                            'company'    => optional($e)->company_name,
                            'position'   => optional($e)->position,
                            'department' => optional($e)->department_name ?? null,
                            'grade'      => optional($e)->grade,
                        ],
                    ];
                })->values()->all();
            }

            return [
                'ipaTasks' => $ipaTasks,
                'message' => $message,
                'subordinateIpas' => $subordinateIpas
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'ipaTasks'        => [
                    'status'           => 'Error',
                    'year'             => null,
                    'employee_name'    => optional(optional(auth()->user())->employee)->name ?? '',
                ],
                'message'         => 'Terjadi kesalahan saat mengambil data IPA.',
                'subordinateIpas' => [],
            ];
        }
    }
}
