<?php

namespace App\Providers;

use App\Models\Hav;
use App\Models\Idp;
use App\Models\Rtc;
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
            $checkIdps = Employee::with(['hav.details.idp' => function ($query) {
                    $query->where('status', 1)->orderBy('created_at');
                }])
                ->whereIn('id', $subCheck)
                ->whereHas('hav.details.idp', function ($query) {
                    $query->where('status', 1);
                })
                ->get();
                
            $approveIdps = Employee::with(['hav.details.idp' => function ($query) {
                    $query->where('status', 2)->orderBy('created_at');
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
                return $employee->hav->flatMap(function ($hav) use ($employee) {
                    return collect($hav->details)->flatMap(function ($detail) use ($employee) {
                        $idps = is_iterable($detail->idp) ? collect($detail->idp) : collect([$detail->idp]);
            
                        return $idps->map(function ($idp) use ($employee) {
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
            // Sort by IDP creation time if needed
            ->sortBy('created_at')
            // Hapus duplikat berdasarkan employee_npk
            ->unique('employee_npk')
            ->values(); // Reset index
            
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
            
            // RTC //
            $allRtcTasks = Rtc::with('employee')
                ->whereIn('employee_id', $subCheck)
                ->where('status', 0)
                ->get()
                ->unique('employee_id')
                ->values();

            $view->with('allIdpTasks', $allIdpTasks);
            $view->with('allHavTasks', $allHavTasks);
            $view->with('allRtcTasks', $allRtcTasks);
        });
    }
}
