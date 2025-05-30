<?php

namespace App\Providers;

use App\Models\Hav;
use App\Models\Idp;
use App\Models\Employee;
use App\Models\Assessment;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot()
    {
        View::composer('layouts.partials.sidebar', function ($view) {
            $user = auth()->user();
            if (!$user) {
                $view->with('allIdpTasks', collect()); // kalau belum login, kasih collection kosong
                return;
            }

            $employee = $user->employee;

            $company = $employee->company_name;

            $createLevel = $employee->getCreateAuth();
            $subCreate = $employee->getSubordinatesByLevel($createLevel)->pluck('id')->toArray();

            $checkLevel = $employee->getFirstApproval();
            $subCheck = $employee->getSubordinatesByLevel($checkLevel)->pluck('id')->toArray();

            $approveLevel = $employee->getFinalApproval();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel)->pluck('id')->toArray();

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
            foreach ($assessments as $assessment){
                foreach ($assessment->details as $detail){
                    if($detail->score < 3 &&!empty($detail->idp)){
                        $emp[$assessment->employee_id][] = [
                            'hav_detail_id' => $detail->id,
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

            $view->with('allIdpTasks', $allIdpTasks);
            $view->with('allHavTasks', $allHavTasks);
        });
    }
}
