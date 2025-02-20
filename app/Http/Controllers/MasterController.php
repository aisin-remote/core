<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function employee()
    {
        $employee = Employee::all();
        return view('website.master.employee.index', compact('employee'));
    }
    public function assesment()
    {
    }
}
