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
            $assessments = Assessment::with(['employee', 'details.alc', 'idp'])
                ->whereIn('employee_id', $subCreate)
                ->when(
                    $company,
                    fn($query) => $query->whereHas('employee', fn($q) => $q->where('company_name', $company))
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
                            'alc_name' => $detail->alc->name ?? 'Unknown',
                        ];
                    }
                }
            }

            $employeeNames = $assessments->pluck('employee.name','employee_id')->toArray();
            $employeeNpk = $assessments->pluck('employee.npk','employee_id')->toArray();
            $employeeCompany = $assessments->pluck('employee.company_name','employee_id')->toArray();
            $notExistInIdp = [];

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
                        break;
                    }
                }
            }

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

            $pendingIdps = $checkIdps->merge($approveIdps);

            $unassignedIdps = collect($notExistInIdp)->map(function ($item) {
                $item['type'] = 'unassigned';
                return $item;
            });

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

            // HAV //
            $allHavTasks = Hav::with('employee')
                ->whereIn('employee_id', $subCheck)
                ->where('status', 0)
                ->get();

            $allIdpTasks = $unassignedIdps->merge($pendingIdpCollection);

            $view->with('allIdpTasks', $allIdpTasks);
            $view->with('allHavTasks', $allHavTasks);
        });
    }
}
