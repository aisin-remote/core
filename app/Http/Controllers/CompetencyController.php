<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use App\Models\Department;
use App\Models\Employee;
use App\Models\GroupCompetency;
use Illuminate\Http\Request;

class CompetencyController extends Controller
{
    // Menampilkan daftar Competency
    public function index()
    {
        $title = 'Competency';

        $competencies = Competency::paginate(10);
        $group = GroupCompetency::all();
        $departments = Department::all();
        $employee =  Employee::all();

        return view('website.competency.index', compact('competencies', 'departments','title', 'group', 'employee'));
    }

    // Menampilkan form untuk membuat data baru
    public function create()
    {
        $groupCompetencies = GroupCompetency::all();
        $departments = Department::all();
        $employees = Employee::all();

        return view('competency.create', compact('groupCompetencies', 'departments', 'employees'));
    }


    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'group_competency_id' => 'required|exists:group_competencies,id',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required||string'
        ]);

        Competency::create($request->all());

        return response()->json(['message' => 'Competency added successfully!']);
    }

    public function update(Request $request, Competency $competency)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'group_competency_id' => 'required|exists:group_competencies,id',
            'department_id' => 'required|exists:departments,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $competency->update($request->all());

        return response()->json(['message' => 'Competency updated successfully!']);
    }

    public function destroy(Competency $competency)
    {
        $competency->delete();

        return response()->json(['message' => 'Competency deleted successfully!']);
    }
}
