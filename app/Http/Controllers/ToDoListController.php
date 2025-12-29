<?php

namespace App\Http\Controllers;

use App\Helpers\ApprovalHelper;
use App\Helpers\IppApprovalHelper;
use App\Models\Department;
use App\Models\DevelopmentApprovalStep;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Hav;
use App\Models\Icp;
use App\Models\IcpApprovalStep;
use App\Models\Idp;
use App\Models\IpaHeader;
use App\Models\Ipp;
use App\Models\Plant;
use App\Models\Rtc;
use App\Models\Section;
use App\Models\SubSection;

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
        $pendingDevCollection = $this->getPendingDevelopmentTasks($employee, $user);

        $allIdpTasks = $this->mergeAllIdpTasks(
            $unassignedIdps,
            $draftIdpCollection,
            $reviseIdpCollection,
            $pendingIdpCollection,
            $pendingDevCollection
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
    public function status()
    {
        $user     = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return response()->json([
                'show_todo_dot' => false,
                'total_pending' => 0,
                'counts'        => [
                    'idp'  => 0,
                    'hav'  => 0,
                    'rtc'  => 0,
                    'icp'  => 0,
                    'ipp'  => 0, // subordinate only
                    'ipa'  => 0, // subordinate only
                ],
            ]);
        }

        [$normalized, $subCreate, $subCheck, $subApprove] = $this->resolveScopes($user, $employee);

        $assessments    = $this->getLatestHavAssessments($subCreate);
        $unassignedIdps = $this->buildUnassignedIdps($assessments);
        $draftIdps      = $this->getDraftIdps($subCreate);
        $reviseIdps     = $this->getReviseIdps($subCreate);
        $pendingIdps    = $this->getPendingIdps($subCheck, $subApprove, $normalized);
        $pendingDevs    = $this->getPendingDevelopmentTasks($employee, $user);

        $allIdpTasks = $this->mergeAllIdpTasks(
            $unassignedIdps,
            $draftIdps,
            $reviseIdps,
            $pendingIdps,
            $pendingDevs
        );

        $allHavTasks = $this->getHavTasks($subCheck);
        $allRtcTasks = $this->getRtcTasks($subCheck, $subApprove);
        $allIcpTasks = $this->getIcpTasks();
        $allIppTasks = $this->getIppTasks($employee, $user);
        $allIpaTasks = $this->getIpaTasks($employee, $user);

        $subsIpps = $allIppTasks['subordinateIpps'] ?? [];
        $subsIpas = $allIpaTasks['subordinateIpas'] ?? [];

        $counts = [
            'idp' => $allIdpTasks->count(),
            'hav' => $allHavTasks->count(),
            'rtc' => $allRtcTasks->count(),
            'icp' => $allIcpTasks->count(),
            'ipp' => is_countable($subsIpps) ? count($subsIpps) : 0,
            'ipa' => is_countable($subsIpas) ? count($subsIpas) : 0,
        ];

        $totalPending = array_sum($counts);

        $showTodoDot = $totalPending > 0;

        return response()->json([
            'show_todo_dot' => $showTodoDot,
            'total_pending' => $totalPending,
            'counts'        => $counts,
        ]);
    }

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
     * Ambil IDP pending (status 1/2/3) sesuai peran (check/approve/president)
     * dengan aturan yang sama seperti method approval().
     */
    private function getPendingIdps(array $subCheck, array $subApprove, ?string $normalized)
    {
        // =========================
        // Tahap 1: CHECK (status=1)
        // =========================
        $checkIdps = Idp::with('hav.hav.employee', 'hav')
            ->where('status', 1)
            ->whereHas('hav.hav.employee', function ($q) use ($subCheck) {
                $q->whereIn('employee_id', $subCheck);
            })
            ->get()
            ->filter(function ($idp) {
                $havId = $idp->hav->hav_id ?? null;
                if (!$havId) return false;

                // Tidak ada status = -1 dalam 1 hav_id
                return !Idp::whereHas('hav', function ($q) use ($havId) {
                    $q->where('hav_id', $havId);
                })->where('status', -1)->exists();
            })
            ->values();

        $checkIdpIds = $checkIdps->pluck('id')->toArray();

        // ===========================
        // Tahap 2: APPROVE (status=2)
        // ===========================
        $approveQuery = Idp::with('hav.hav.employee', 'hav')
            ->where('status', 2)
            ->whereHas('hav.hav.employee', function ($q) use ($subApprove) {
                $q->whereIn('employee_id', $subApprove);
            })
            ->whereNotIn('id', $checkIdpIds);

        // tambahan rule: jika president, exclude employee position Manager untuk status=2
        if ($normalized === 'president') {
            $approveQuery->whereHas('hav.hav.employee', function ($q) {
                $q->where('position', '!=', 'Manager');
            });
        }

        $approveIdps = $approveQuery->get()
            ->filter(function ($idp) {
                $havId = $idp->hav->hav_id ?? null;
                if (!$havId) return false;

                $relatedStatuses = Idp::whereHas('hav', function ($q) use ($havId) {
                    $q->where('hav_id', $havId);
                })->pluck('status')->toArray();

                // Minimal ada status = 2, dan tidak boleh ada status = -1
                return in_array(2, $relatedStatuses) && !in_array(-1, $relatedStatuses);
            })
            ->values();

        // ==========================================
        // Tambahan khusus President (status=3)
        // ==========================================
        $presidentApproveIdps = collect();

        if ($normalized === 'president') {
            $presidentApproveIdps = Idp::with('hav.hav.employee', 'hav')
                ->where('status', 3)
                ->whereHas('hav.hav.employee', function ($q) use ($subApprove) {
                    $q->whereIn('employee_id', $subApprove);
                })
                ->whereNotIn('id', $checkIdpIds)
                ->get()
                ->filter(function ($idp) {
                    $havId = $idp->hav->hav_id ?? null;
                    if (!$havId) return false;

                    $relatedStatuses = Idp::whereHas('hav', function ($q) use ($havId) {
                        $q->where('hav_id', $havId);
                    })->pluck('status')->toArray();

                    // Minimal ada status = 3, dan tidak boleh ada status = -1 atau 2
                    return in_array(3, $relatedStatuses)
                        && !in_array(-1, $relatedStatuses)
                        && !in_array(2, $relatedStatuses);
                })
                ->values();
        }

        // Gabungkan semua pending
        $pendingIdps = $checkIdps->merge($approveIdps);
        if ($normalized === 'president') {
            $pendingIdps = $pendingIdps->merge($presidentApproveIdps);
        }

        // Mapping jadi task list (unik per employee_npk seperti code kamu)
        return $pendingIdps
            ->map(function ($idp) use ($normalized) {
                $emp = $idp->hav->hav->employee ?? null;

                $type = 'need_approval';
                if ((int) $idp->status === 1) $type = 'need_check';
                if ((int) $idp->status === 3 && $normalized === 'president') $type = 'need_president_approval';

                return [
                    'type'             => $type,
                    'employee_name'    => $emp->name ?? '-',
                    'employee_npk'     => $emp->npk ?? '-',
                    'employee_company' => $emp->company_name ?? '-',
                    'category'         => $idp->category ?? '-',
                    'program'          => $idp->development_program ?? '-',
                    'target'           => $idp->development_target ?? '-',
                    'created_at'       => $idp->created_at,
                ];
            })
            ->sortBy('created_at')
            ->unique('employee_npk')
            ->values();
    }

    /**
     * Gabungkan semua jenis IDP tasks.
     */
    private function mergeAllIdpTasks(
        $unassignedIdps,
        $draftIdpCollection,
        $reviseIdpCollection,
        $pendingIdpCollection,
        $pendingDevCollection = null
    ) {
        $out = $unassignedIdps
            ->merge($draftIdpCollection)
            ->merge($reviseIdpCollection)
            ->merge($pendingIdpCollection);

        if ($pendingDevCollection) {
            $out = $out->merge($pendingDevCollection);
        }

        return $out->values();
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
        $me      = auth()->user()->employee;
        $role    = ApprovalHelper::roleKeyFor($me);
        $company = $me->company_name;

        // =========================
        // A) Tasks approval (yang sudah ada)
        // =========================
        $rolesToMatch = [$role];

        if ($role === 'director')  $rolesToMatch[] = 'direktur';
        if ($role === 'president') $rolesToMatch[] = 'presiden';
        if ($role === 'gm')        $rolesToMatch[] = 'general manager';

        $pendingSteps = IcpApprovalStep::with(['icp.employee', 'icp.steps'])
            ->whereIn('role', $rolesToMatch)
            ->where('status', 'pending')
            ->whereHas('icp', function ($q) {
                $q->where('status', '!=', 4);
            })
            ->whereHas('icp.employee', function ($q) use ($company) {
                $q->where('company_name', $company);
            })
            ->orderBy('step_order')
            ->get()
            ->filter(function ($s) {
                return $s->icp->steps->every(function ($x) use ($s) {
                    return $x->step_order >= $s->step_order || $x->status === 'done';
                });
            })
            ->values();

        // =========================
        // B) Tasks revise (ICP status = 0) -> ditampilkan ke atasan yang punya scope create
        // =========================
        $createLevel = (int) $me->getCreateAuth();

        $reviseTasks = collect();

        if ($createLevel > 0) {
            // ambil subordinate ids sesuai rule kamu (bisa create ICP untuk siapa saja)
            $subordinateIds = $me->getSubordinatesByLevel($createLevel)->pluck('id');

            if ($subordinateIds->isNotEmpty()) {
                // Ambil ICP revise milik bawahan tsb (ambil yang latest kalau employee punya banyak ICP)
                // NOTE: kalau yang kamu maksud "ICP terakhir" saja, kita ambil latest per employee via PHP.
                $reviseIcps = Icp::with('employee')
                    ->where('status', Icp::STATUS_REVISE) // = 0
                    ->whereIn('employee_id', $subordinateIds)
                    ->whereHas('employee', fn($q) => $q->where('company_name', $company))
                    ->orderByDesc('updated_at')
                    ->get();

                // Kalau ada kemungkinan 1 employee punya banyak ICP revise, ambil 1 yang paling baru per employee
                $reviseIcps = $reviseIcps
                    ->groupBy('employee_id')
                    ->map(fn($g) => $g->first())
                    ->values();

                $reviseTasks = $reviseIcps->map(function ($icp) {
                    return (object) [
                        'task_type' => 'icp_revise',
                        'icp'       => $icp,
                        'employee'  => $icp->employee,
                        'label'     => 'ICP Need Revision',
                        // supaya bisa kamu sorting paling atas
                        'step_order' => 0,
                    ];
                });
            }
        }


        // =========================
        // C) Gabungkan + sorting
        // =========================
        return $reviseTasks
            ->concat($pendingSteps)
            ->sortBy(fn($x) => $x->step_order ?? 9999)
            ->values();
    }

    /**
     * RTC tasks (status 0/1 sesuai scope).
     */
    private function getRtcTasks(array $subCheck, array $subApprove)
    {
        $user = auth()->user();
        $employee = $user?->employee;

        if (!$employee) {
            return collect();
        }

        $norm = strtolower($employee->getNormalizedPosition());
        $queue = collect();

        if ($norm === 'gm') {
            $divIds     = Division::where('gm_id', $employee->id)->pluck('id');
            $deptIds    = Department::whereIn('division_id', $divIds)->pluck('id');
            $sectionIds = Section::whereIn('department_id', $deptIds)->pluck('id');
            $subIds     = SubSection::whereIn('section_id', $sectionIds)->pluck('id');

            $queue = Rtc::with(['employee', 'department', 'section', 'subsection', 'division', 'plant'])
                ->where('status', 1)
                ->whereNotNull('term')
                ->where(function ($q) use ($deptIds, $sectionIds, $subIds) {
                    $q->where(fn($qq) => $qq->where('area', 'department')->whereIn('area_id', $deptIds))
                        ->orWhere(fn($qq) => $qq->where('area', 'section')->whereIn('area_id', $sectionIds))
                        ->orWhere(fn($qq) => $qq->where('area', 'sub_section')->whereIn('area_id', $subIds));
                })
                ->get();
        } elseif (in_array($norm, ['direktur', 'director', 'act direktur'], true)) {
            $plantId = optional($employee->plant)->id;
            $divIds  = Division::where('plant_id', $plantId)->pluck('id');

            $queue = Rtc::with(['employee', 'department', 'section', 'subsection', 'division', 'plant'])
                ->where('status', 1)
                ->whereNotNull('term')
                ->whereIn('area', ['division', 'Division'])
                ->whereIn('area_id', $divIds)
                ->get();
        } elseif (in_array($norm, ['vpd', 'vice president director'], true)) {
            $plantIds = Plant::pluck('id');

            $queue = Rtc::with(['employee', 'department', 'section', 'subsection', 'division', 'plant'])
                ->where('status', 1)
                ->whereNotNull('term')
                ->whereIn('area', ['direksi', 'plant'])
                ->whereIn('area_id', $plantIds)
                ->get();
        } elseif ($norm === 'president') {
            $plantIds = Plant::pluck('id');

            $queue = Rtc::with(['employee', 'department', 'section', 'subsection', 'division', 'plant'])
                ->where('status', 1)
                ->whereNotNull('term')
                ->whereIn('area', ['direksi', 'plant'])
                ->whereIn('area_id', $plantIds)
                ->get();
        }

        $grouped = $queue
            ->groupBy(fn($rtc) => strtolower($rtc->area) . '#' . $rtc->area_id)
            ->map(function ($items) {
                $first   = $items->first();
                $areaKey = strtolower($first->area);

                $statusMap = [
                    -1 => 'Revised',
                    0  => 'Draft',
                    1  => 'Submitted',
                    2  => 'Approved',
                ];

                $statusCounts = $items->groupBy('status')
                    ->map(fn($col) => $col->count())
                    ->mapWithKeys(fn($count, $code) => [($statusMap[$code] ?? 'Unknown') => $count]);

                return [
                    'area'       => $areaKey,
                    'area_id'    => $first->area_id,
                    'area_name'  => $first->area_name,
                    'total_rtc'  => $items->count(),
                    'terms'      => $items->pluck('term')->unique()->values()->all(),
                    'status_info' => $statusCounts,
                ];
            })
            ->values();

        return $grouped;
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
                $message = "IPA belum dibuat.";

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

    /**
     * Ambil role-key approver untuk development approval step (toleransi data lama).
     */
    private function currentApproverRoleKeysForDevelopment($employee): array
    {
        $role = ApprovalHelper::roleKeyFor($employee);

        if (! $role) return [];

        $rolesToMatch = [$role];

        if ($role === 'director')   $rolesToMatch[] = 'direktur';
        if ($role === 'president')  $rolesToMatch[] = 'presiden';
        if ($role === 'gm')         $rolesToMatch[] = 'general manager';
        if ($role === 'vpd')        $rolesToMatch[] = 'vice president director';

        return array_values(array_unique($rolesToMatch));
    }

    private function getPendingDevelopmentTasks($employee = null, $user = null)
    {
        $authUser = auth()->user();
        $user     = $user ?? $authUser;
        $employee = $employee ?? $user?->employee;

        if (! $employee) {
            return collect();
        }

        $rolesToMatch = $this->currentApproverRoleKeysForDevelopment($employee);
        if (empty($rolesToMatch)) {
            return collect();
        }

        $query = DevelopmentApprovalStep::with([
            'oneDevelopment.employee.department',
            'oneDevelopment.steps',
        ])
            ->whereIn('role', $rolesToMatch)
            ->where('status', 'pending');

        $company = $employee->company_name ?? null;
        if ($company) {
            $query->whereHas('oneDevelopment.employee', function ($q) use ($company) {
                $q->where('company_name', $company);
            });
        }

        $steps = $query->orderBy('step_order', 'asc')->get();

        $steps = $steps->filter(function ($step) {
            $dev = $step->oneDevelopment;
            if (! $dev) return false;

            return $dev->steps->every(function ($x) use ($step) {
                if ($x->step_order < $step->step_order && $x->status !== 'done') return false;
                return true;
            });
        });

        $grouped = $steps->groupBy(fn($s) => $s->oneDevelopment?->employee_id)->filter();

        $rows = [];
        foreach ($grouped as $employeeId => $employeeSteps) {
            $devSample = $employeeSteps->first()->oneDevelopment;
            $emp       = $devSample?->employee;

            if (! $emp) continue;

            $rows[] = [
                'type'             => 'development_need_approval',
                'employee_id'      => $emp->id,
                'employee_name'    => $emp->name,
                'employee_npk'     => $emp->npk,
                'employee_company' => $emp->company_name,
                'position'         => $emp->position,
                'department'       => $emp->department->name ?? null,
                'grade'            => $emp->grade,

                'pending_count' => $employeeSteps
                    ->map(fn($s) => $s->development_one_id)
                    ->unique()
                    ->count(),
            ];
        }

        return collect($rows);
    }
}
 