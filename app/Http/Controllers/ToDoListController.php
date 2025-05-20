<?php

namespace App\Http\Controllers;

use App\Models\Hav;
use App\Models\Idp;
use App\Models\Employee;
use App\Models\Assessment;
use Illuminate\Http\Request;

class ToDoListController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;
        $company = $employee->company_name;

        $createLevel = $employee->getCreateAuth();
        $subCreate = $employee->getSubordinatesByLevel($createLevel)->pluck('id')->toArray();

        $checkLevel = $employee->getFirstApproval();
        $subCheck = $employee->getSubordinatesByLevel($checkLevel)->pluck('id')->toArray();

        $approveLevel = $employee->getFinalApproval();
        $subApprove = $employee->getSubordinatesByLevel($approveLevel)->pluck('id')->toArray();
        
        // Ambil assessment terbaru hanya milik bawahannya
        $assessments = Assessment::with(['employee', 'details.alc', 'idp']) // tambahkan details.alc
            ->whereIn('employee_id', $subCreate)
            ->when(
                $company,
                fn($query) =>
                $query->whereHas('employee', fn($q) => $q->where('company_name', $company))
            )
            ->whereIn('id', function ($query) {
                $query->selectRaw('id')
                    ->from('assessments as a')
                    ->whereRaw('a.created_at = (SELECT MAX(created_at) FROM assessments WHERE employee_id = a.employee_id)');
            })
            ->get();

        
        $emp = [];
        foreach ($assessments as $assessment){
            foreach ($assessment->details as $detail){
                if($detail->score < 3){
                    $emp[$assessment->employee_id][] = [
                        'assessment_id' => $detail->assessment_id,
                        'alc_id' => $detail->alc_id,
                        'alc_name' => $detail->alc->name ?? 'Unknown', // Ambil nama ALC
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
                $exists = Idp::where('assessment_id', $item['assessment_id'])
                    ->where('alc_id', $item['alc_id'])
                    ->exists();
        
                if (! $exists) {
                    $notExistInIdp[] = [
                        'employee_name' => $employeeNames[$employeeId] ?? 'Unknown',
                        'employee_npk' => $employeeNpk[$employeeId] ?? 'Unknown',
                        'employee_company' => $employeeCompany[$employeeId] ?? 'Unknown',
                        'assessment_id' => $item['assessment_id'],
                        'alc_id' => $item['alc_id'],
                        'alc_name' => $item['alc_name'] ?? 'Unknown',
                    ];
                    break; // ✅ Langsung lanjut ke karyawan berikutnya
                }
            }
        }            

        // ambil idp yang yang statusnya masih 1 (need check or first approval)
        $checkIdps = Employee::with(['assessments' => function ($query) {
                $query->whereHas('idp', function ($q) {
                    $q->where('status', 1);
                })->with(['idp' => function ($q) {
                    $q->where('status', 1);
                }])->orderBy('created_at')->take(1);
            }])
            ->whereIn('id', $subCheck)
            ->whereHas('assessments.idp', function ($query) {
                $query->where('status', 1);
            })
            ->get();
    
        $approveIdps = Employee::with(['assessments' => function ($query) {
                $query->whereHas('idp', function ($q) {
                    $q->where('status', 2);
                })->with(['idp' => function ($q) {
                    $q->where('status', 2);
                }])->orderBy('created_at')->take(1);
            }])
            ->whereIn('id', $subApprove)
            ->whereHas('assessments.idp', function ($query) {
                $query->where('status', 2);
            })
            ->get();
        
        // Gabungkan keduanya (idp yang perlu di aprove)
        $pendingIdps = $checkIdps->merge($approveIdps);
        
        // Tambahkan 'type' untuk unassigned
        $unassignedIdps = collect($notExistInIdp)->map(function ($item) {
            $item['type'] = 'unassigned';
            return $item;
        });

        // Tambahkan 'type' untuk pending IDPs (status 1 atau 2)
        $pendingIdpCollection = $pendingIdps->map(function ($employee) {
            $idp = optional($employee->assessments->first()->idp->first());
            return [
                'type' => $idp?->status === 1 ? 'need_check' : 'need_approval',
                'employee_name' => $employee->name,
                'employee_npk' => $employee->npk,
                'employee_company' => $employee->company_name,
                'category' => $idp?->category ?? '-',
                'program' => $idp?->development_program ?? '-',
                'target' => $idp?->development_target ?? '-',
            ];
        });

        // Gabungkan menjadi satu koleksi
        $allIdpTasks = $unassignedIdps->merge($pendingIdpCollection);

        // HAV //
        $allHavTasks = Hav::with('employee')
            ->whereIn('employee_id', $subCheck)
            ->where('status', 0)
            ->get()
            ->unique('employee_id')
            ->values();

        // Kirim ke view
        return view('website.todolist.index', compact('allIdpTasks','allHavTasks'));
    }
}
