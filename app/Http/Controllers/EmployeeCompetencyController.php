<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCompetency;
use App\Models\Department;
use App\Models\Competency;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeCompetencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Employee Competency';

        $employees = Employee::has('employeeCompetencies')
            ->with(['employeeCompetencies.competency.department', 'departments'])
            ->paginate(10);
    
        $departments = Department::all();
        $competencies = Competency::all();
    
        return view('website.employee_competency.index', compact('employees', 'competencies', 'departments', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $competencies = Competency::all();
        $departments = Department::all();
        $employees = Employee::all();

        return view('website.employee_competency.create', compact('competencies', 'departments', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'employee_id.*' => 'exists:employees,id',
            'competency_id' => 'required|array',
            'competency_id.*' => 'exists:competency,id',
            'weight' => 'nullable|integer',
            'plan' => 'nullable|integer',
            'act' => 'nullable|integer',
            'plan_date' => 'required|date|after_or_equal:today',
            'due_date' => 'required|date|after_or_equal:today'
        ]);

        $createdCount = 0;
        
        foreach ($request->employee_id as $employeeId) {
            foreach ($request->competency_id as $competencyId) {
                $exists = EmployeeCompetency::where('employee_id', $employeeId)
                    ->where('competency_id', $competencyId)
                    ->exists();
                
                if (!$exists) {
                    EmployeeCompetency::create([
                        'employee_id' => $employeeId,
                        'competency_id' => $competencyId,
                        'weight' => $request->weight,
                        'plan' => $request->plan,
                        'act' => $request->act,
                        'plan_date' => $request->plan_date,
                        'due_date' => $request->due_date
                    ]);
                    $createdCount++;
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $createdCount > 0 
                    ? 'Employee Competency added successfully!' 
                    : 'No new competencies added',
                'redirect' => route('employeeCompetencies.index')
            ], 200);
        }

        return redirect()->route('employeeCompetencies.index')
            ->with('success', $createdCount > 0 
                ? 'Employee Competency added successfully!' 
                : 'No new competencies added');
    }

    public function getEmployees(Request $request)
    {
        $position = $request->input('position');
        $departmentId = $request->input('department_id');

        $employees = Employee::where('position', $position)
            ->whereHas('departments', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->whereDoesntHave('employeeCompetencies')
            ->get();

        return response()->json($employees);
    }

    public function getCompetencies(Request $request)
    {
        $position = $request->input('position');
        $departmentId = $request->input('department_id');
        $employeeId = $request->input('employee_id');

        $existingCompetencyIds = EmployeeCompetency::where('employee_id', $employeeId)
            ->pluck('competency_id')
            ->toArray();
        $competencies = Competency::where('position', $position)
            ->where('department_id', $departmentId)
            ->whereNotIn('id', $existingCompetencyIds)
            ->get();

        return response()->json($competencies);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = Employee::with(['employeeCompetencies.competency.department'])->findOrFail($id);
        return view('website.employee_competency.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $employee_competencies = EmployeeCompetency::with(['competency', 'department', 'employee'])->find($id);

        if (!$employee_competencies) {
            return response()->json(['error' => 'Employee Competency not found'], 404);
        }

        return response()->json([
            'id' => $employee_competencies->id,
            'employee_id' => $employee_competencies->employee_id,
            'competency_id' => $employee_competencies->competency_id,
            'weight' => $employee_competencies->weight,
            'plan' => $employee_competencies->plan,
            'act' => $employee_competencies->act,
            'plan_date' => $employee_competencies->plan_date,
            'due_date' => $employee_competencies->due_date,
            'all_competency' => Competency::all(),
            'all_employee' => Employee::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $employeeCompetency = EmployeeCompetency::findOrFail($id);
        $employeeCompetency->update($request->all());

        return response()->json(['message' => 'Competency updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeCompetency $employeeCompetency)
    {
        $employeeCompetency->delete();
        
        return redirect()->back()
            ->with('success', 'Competency deleted successfully!');
    }
}
