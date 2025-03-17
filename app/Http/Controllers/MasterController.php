<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
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
    public function department()
    {
        $departments = Department::all();
        return view('website.master.department.index', compact('departments'));
    }

    public function departmentStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        try {
            Department::create(['name' => $request->name]);

            return redirect()->back()->with('success', 'Department berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan department: ' . $e->getMessage());
        }
    }

    public function departmentDestroy($id)
    {
        try {
            $department = Department::findOrFail($id);
            $department->delete();

            return redirect()->back()->with('success', 'Department berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus department: ' . $e->getMessage());
        }
    }

}
