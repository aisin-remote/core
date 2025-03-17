<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function employee()
    {
        $user = auth()->user();

        if ($user->role === 'HRD') {
            $employee = Employee::all();
        } else {
            $departmentIds = $user->employee->departments->pluck('id');
            $employee = Employee::whereHas('departments', function ($query) use ($departmentIds) {
                $query->whereIn('department_id', $departmentIds);
            })->get();
        }

        return view('website.master.employee.index', compact('employee'));
    }
    public function assesment()
    {
        
    }
}
