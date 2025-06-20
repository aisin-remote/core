<?php

namespace App\Http\Controllers;

use App\Models\Hav;
use App\Models\Idp;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\EmployeeCompetency;
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
            ->when(
                $company,
                fn($query) =>
                $query->whereHas('employee', fn($q) => $q->where('company_name', $company))
            )
            ->whereIn('id', function ($query) {
                $query->selectRaw('id')
                    ->from('havs as a')
                    ->whereRaw('a.created_at = (SELECT MAX(created_at) FROM havs WHERE employee_id = a.employee_id)');
            })
            ->get();

        
        $emp = [];
        foreach ($assessments as $assessment) {
            foreach ($assessment->details as $detail) {
                // Jika score < 3 atau ada saran pengembangan, dan IDP-nya tidak kosong
                if (($detail->score < 3 || $detail->suggestion_development !== null) && !empty($detail->idp)) {
                    $emp[$assessment->employee_id][] = [
                        'hav_detail_id' => $detail->id,
                        'alc_id' => $detail->alc_id,
                        'alc_name' => $detail->alc->name ?? 'Unknown',
                    ];
                }
            }
        }        
            
        // lalu crosscek di tabel idp, sudah ada atau belum
        $employeeNames = $assessments->pluck('employee.name','employee_id')->toArray();
        $employeeNpk = $assessments->pluck('employee.npk','employee_id')->toArray();
        $employeeCompany = $assessments->pluck('employee.company_name','employee_id')->toArray();
        $notExistInIdp = [];
        
        // idp yang belum di create
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
                    break; // ✅ Langsung lanjut ke karyawan berikutnya
                }
            }
        }  
                
        // Ambil IDP yang statusnya 0 (draft)
        $draftIdps = Employee::with(['hav.details' => function ($query) {
            $query->whereHas('idp', function ($q) {
                $q->where('status', 0);
            })->with(['idp' => function ($q) {
                $q->where('status', 0);
            }])->orderBy('created_at')->take(1);
        }])
        ->whereIn('id', $subCreate)
        ->whereHas('hav.details.idp', function ($query) {
            $query->where('status', 0);
        })
        ->get();

        // Ambil IDP yang statusnya 1 (perlu dicek)
        $checkIdps = Employee::with(['hav.details' => function ($query) {
            $query->whereHas('idp', function ($q) {
                $q->where('status', 1);
            })->with(['idp' => function ($q) {
                $q->where('status', 1);
            }])->orderBy('created_at')->take(1);
        }])
        ->whereIn('id', $subCheck)
        ->whereHas('hav.details.idp', function ($query) {
            $query->where('status', 1);
        })
        ->get();

        // Ambil IDP yang statusnya 2 (perlu di-approve)
        $approveIdps = Employee::with(['hav.details' => function ($query) {
            $query->whereHas('idp', function ($q) {
                $q->where('status', 2);
            })->with(['idp' => function ($q) {
                $q->where('status', 2);
            }])->orderBy('created_at')->take(1);
        }])
        ->whereIn('id', $subApprove)
        ->whereHas('hav.details.idp', function ($query) {
            $query->where('status', 2);
        })
        ->get();

        // Gabungkan IDP yang perlu dicek dan approve
        $pendingIdps = $checkIdps->merge($approveIdps);

        // Koleksi: unassigned
        $unassignedIdps = collect($notExistInIdp)->map(function ($item) {
        $item['type'] = 'unassigned';
        return $item;
        });

        // Koleksi: draft
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

        // Koleksi: pending (need_check / need_approval)
        $pendingIdpCollection = $pendingIdps->flatMap(function ($employee) {
            $hav = $employee->hav->first(); // ambil satu model Hav
            $detail = optional($hav?->details->first()); // ambil satu detail
        
            $idps = $detail?->idp ?? collect();
        
            return $idps->map(function ($idp) use ($employee) {
                return [
                    'type' => $idp->status === 1 ? 'need_check' : 'need_approval',
                    'employee_name' => $employee->name,
                    'employee_npk' => $employee->npk,
                    'employee_company' => $employee->company_name,
                    'category' => $idp->category ?? '-',
                    'program' => $idp->development_program ?? '-',
                    'target' => $idp->development_target ?? '-',
                ];
            });
        });

        $assessmentTasks = [];

        // Ambil employee yang sudah di-approve (act=1) tapi belum dinilai
        $needAssessment = EmployeeCompetency::with(['employee', 'competency'])
            ->where('act', 1) // Sudah di-approve
            ->whereDoesntHave('checksheetAssessments', function ($query) {
                $query->where('score', '>', 0); // Belum ada penilaian
            })
            ->whereIn('employee_id', $subCreate) // Hanya bawahan user
            ->get();

        foreach ($needAssessment as $ec) {
            $assessmentTasks[] = [
                'type' => 'need_assessment',
                'employee_name' => $ec->employee->name,
                'employee_npk' => $ec->employee->npk,
                'employee_company' => $ec->employee->company_name,
                'competency_name' => $ec->competency->name,
                'employee_competency_id' => $ec->id,
            ];
        }

        // Gabungkan semua ke satu koleksi
        $allIdpTasks = $unassignedIdps
        ->merge($draftIdpCollection)
        ->merge($pendingIdpCollection);


        // HAV //
        $allHavTasks = Hav::with('employee')
            ->whereIn('employee_id', $subCheck)
            ->where('status', 0)
            ->get()
            ->unique('employee_id')
            ->values();

        // Kirim ke view
        return view('website.todolist.index', compact('allIdpTasks','allHavTasks','assessmentTasks',));
    }
}
