<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use App\Models\Department;
use App\Models\Employee;
use App\Models\GroupCompetency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompetencyController extends Controller
{
    // Menampilkan daftar Competency
    public function index()
    {
        $title = 'Competency';

        $competencies = Competency::with(['group_competency', 'department', 'employee'])->paginate(10);
        $group = GroupCompetency::all();
        $departments = Department::all();
        $employee = Employee::all();

        return view('website.competency.index', compact('competencies', 'departments', 'title', 'group', 'employee'));
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
            'group_competency_id' => 'required|exists:group_competency,id',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string'
        ]);

        Competency::create($request->all());

        return response()->json(['message' => 'Competency added successfully!'], 200);
    }

    public function edit($id)
    {
        $competency = Competency::with(['group_competency', 'department'])->find($id);

        if (!$competency) {
            return response()->json(['error' => 'Competency not found'], 404);
        }

        $all_positions = ["General Manager", "Manager", "Coordinator", "Section Head", "Supervisor", "Act Leader", "Act JP", "Operator"];

        return response()->json([
            'id' => $competency->id,
            'name' => $competency->name,
            'description' => $competency->description,
            'group_competency_id' => $competency->group_competency_id,
            'department_id' => $competency->department_id,
            'position' => $competency->position,
            'all_groups' => GroupCompetency::all(),
            'all_departments' => Department::all(),
            'all_positions' => $all_positions
        ]);
    }
    public function update(Request $request, $id)
    {
        \Log::info("Received data:", $request->all());

        $competency = Competency::find($id);
        if (!$competency) {
            return response()->json(['error' => 'Competency not found'], 404);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:191',
                'description' => 'nullable|string',
                'group_competency_id' => 'required|exists:group_competency,id',
                'department_id' => 'required|exists:departments,id',
                'position' => 'required|string',
            ]);

            $competency->update($validatedData);
            return response()->json(['message' => 'Competency updated successfully!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Validation Failed:", $e->errors());
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        }
    }





    public function destroy(Competency $competency)
    {
        $competency->delete();

        return response()->json(['message' => 'Competency deleted successfully!']);
    }
}
