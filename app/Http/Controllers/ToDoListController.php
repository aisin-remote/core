<?php

namespace App\Http\Controllers;

use App\Models\Hav;
use App\Models\Idp;
use App\Models\Rtc;
use App\Models\Employee;
use App\Models\Assessment;
use Illuminate\Http\Request;

class ToDoListController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;
        $company = $employee->company_name;

        $normalized = $employee->getNormalizedPosition();

        // Ambil bawahannya yang bisa create
        $createLevel = $employee->getCreateAuth();
        $subCreate = $employee->getSubordinatesByLevel($createLevel)->pluck('id')->toArray();

        $checkLevel = $employee->getFirstApproval();
        $approveLevel = $employee->getFinalApproval();

        if ($normalized === 'vpd') {
            // Untuk VPD, filter GM dan Manager
            $subCheck = $employee->getSubordinatesByLevel($checkLevel, ['gm'])->pluck('id')->toArray();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel, ['manager'])->pluck('id')->toArray();
        } else {
            // Untuk posisi lain, ambil semua bawahan
            $subCheck = $employee->getSubordinatesByLevel($checkLevel)->pluck('id')->toArray();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel)->pluck('id')->toArray();
        }
        
        // Ambil assessment terbaru hanya milik bawahannya
        $assessments = Hav::with(['employee', 'details.idp', 'details.alc']) // tambahkan details.alc
            ->whereIn('employee_id', $subCreate)
            // ->when(
            //     $company,
            //     fn($query) =>
            //     $query->whereHas('employee', fn($q) => $q->where('company_name', $company))
            // )
            ->whereIn('id', function ($query) {
                $query->selectRaw('id')
                    ->from('havs as a')
                    ->whereRaw('a.created_at = (SELECT MAX(created_at) FROM havs WHERE employee_id = a.employee_id)');
            })
            ->get();
        
        $emp = [];
        foreach ($assessments as $assessment) {
            foreach ($assessment->details as $detail) {
                $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);
        
                // Ambil IDP yang bukan status -1
                $validIdp = $idps->first(function ($idp) {
                    return $idp && $idp->status !== -1;
                });
        
                if (
                    ($detail->score < 3 || $detail->suggestion_development !== null) &&
                    $validIdp
                ) {
                    $emp[$assessment->employee_id][] = [
                        'hav_detail_id' => $detail->id,
                        'alc_id' => $detail->alc_id,
                        'alc_name' => $detail->alc->name ?? 'Unknown',
                    ];
                }
            }
        }        
        
        $employeeNames = $assessments->pluck('employee.name', 'employee_id')->toArray();
        $employeeNpk = $assessments->pluck('employee.npk', 'employee_id')->toArray();
        $employeeCompany = $assessments->pluck('employee.company_name', 'employee_id')->toArray();
        
        $notExistInIdp = [];
        foreach ($emp as $employeeId => $items) {
            foreach ($items as $item) {
                $exists = Idp::where('hav_detail_id', $item['hav_detail_id'])
                    ->where('alc_id', $item['alc_id'])
                    ->exists();
        
                if (!$exists) {
                    $notExistInIdp[] = [
                        'employee_name' => $employeeNames[$employeeId] ?? 'Unknown',
                        'employee_npk' => $employeeNpk[$employeeId] ?? 'Unknown',
                        'employee_company' => $employeeCompany[$employeeId] ?? 'Unknown',
                        'hav_detail_id' => $item['hav_detail_id'],
                        'alc_id' => $item['alc_id'],
                        'alc_name' => $item['alc_name'] ?? 'Unknown',
                    ];
                    break;
                }
            }
        }
        
        // === Draft ===
        $draftIdps = Employee::with(['hav.details' => function ($query) {
            $query->whereHas('idp', fn($q) => $q->where('status', 0))
                  ->with(['idp' => fn($q) => $q->where('status', 0)])
                  ->orderBy('created_at')
                  ->take(1);
        }])
        ->whereIn('id', $subCreate)
        ->whereHas('hav.details.idp', fn($q) => $q->where('status', 0))
        ->get();
        
        $draftIdpCollection = $draftIdps->map(function ($employee) {
            $hav = $employee->hav->first();
            $detail = optional($hav?->details->first());
            $idp = optional($detail?->idp);
        
            return [
                'type' => 'draft',
                'employee_name' => $employee->name,
                'employee_npk' => $employee->npk,
                'employee_company' => $employee->company_name,
                'category' => $idp?->category ?? '-',
                'program' => $idp?->development_program ?? '-',
                'target' => $idp?->development_target ?? '-',
            ];
        });
        
        // === Revisi ===
        $reviseIdps = Employee::with(['hav.details.idp' => fn($q) => $q->where('status', -1)->orderBy('created_at')])
            ->whereIn('id', $subCreate)
            ->whereHas('hav.details.idp', fn($q) => $q->where('status', -1))
            ->get();
        
        $reviseIdpCollection = $reviseIdps->flatMap(function ($employee) {
            return $employee->hav->flatMap(function ($hav) use ($employee) {
                return collect($hav->details)->flatMap(function ($detail) use ($employee) {
                    $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);
                    return $idps->filter(fn($idp) => $idp->status === -1)->map(function ($idp) use ($employee) {
                        return [
                            'type' => 'revise',
                            'employee_name' => $employee->name,
                            'employee_npk' => $employee->npk,
                            'employee_company' => $employee->company_name,
                            'category' => $idp->category ?? '-',
                            'program' => $idp->development_program ?? '-',
                            'target' => $idp->development_target ?? '-',
                            'created_at' => $idp->created_at,
                        ];
                    });
                });
            });
        });
        
        // === Pending (status 1 dan 2)
        $checkIdps = Employee::with(['hav.details.idp' => fn($q) => $q->where('status', 1)->orderBy('created_at')])
            ->whereIn('id', $subCheck)
            ->whereHas('hav.details.idp', fn($q) => $q->where('status', 1))
            ->get();
        
            $approveIdps = Employee::with(['hav.details.idp' => fn($q) => $q->orderBy('created_at')])
            ->whereIn('id', $subApprove)
            ->whereHas('hav.details.idp', fn($q) => $q->where('status', 2))
            ->get()
            ->filter(function ($employee) {
                return $employee->hav->every(function ($hav) {
                    $statuses = collect($hav->details)->flatMap(function ($detail) {
                        $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);
                        return $idps->pluck('status');
                    })->unique();
        
                    // Harus semua status 2 dan tidak ada -1
                    return $statuses->count() === 1 && $statuses->first() === 2;
                });
            });
        
        $pendingIdps = $checkIdps->merge($approveIdps);
        
        $pendingIdpCollection = $pendingIdps->flatMap(function ($employee) {
            return $employee->hav->flatMap(function ($hav) use ($employee) {
                return collect($hav->details)->flatMap(function ($detail) use ($employee) {
                    $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);
                    return $idps->filter(fn($idp) => in_array($idp->status, [1, 2]))->map(function ($idp) use ($employee) {
                        return [
                            'type' => $idp->status === 1 ? 'need_check' : 'need_approval',
                            'employee_name' => $employee->name,
                            'employee_npk' => $employee->npk,
                            'employee_company' => $employee->company_name,
                            'category' => $idp->category ?? '-',
                            'program' => $idp->development_program ?? '-',
                            'target' => $idp->development_target ?? '-',
                            'created_at' => $idp->created_at,
                        ];
                    });
                });
            });
        })
        ->sortBy('created_at')
        ->unique('employee_npk')
        ->values();
        
        // === Unassigned
        $unassignedIdps = collect($notExistInIdp)->map(function ($item) {
            $item['type'] = 'unassigned';
            return $item;
        });
        
        // === Gabung semua
        $allIdpTasks = $unassignedIdps
            ->merge($draftIdpCollection)
            ->merge($reviseIdpCollection)
            ->merge($pendingIdpCollection);        

        // HAV //
        $allHavTasks = Hav::with('employee')
            ->whereIn('employee_id', $subCheck)
            ->where('status', 0)
            ->get()
            ->unique('employee_id')
            ->values();
            
        // RTC //
        $allRtcTasks = Rtc::with('employee')
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
            
        // Kirim ke view
        return view('website.todolist.index', compact('allIdpTasks','allHavTasks','allRtcTasks'));
    }
}
