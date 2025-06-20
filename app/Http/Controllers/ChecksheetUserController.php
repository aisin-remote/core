<?php

namespace App\Http\Controllers;

use App\Models\ChecksheetUser;
use App\Models\Competency;
use App\Models\SubSection;
use App\Models\Section;
use App\Models\Department;
use App\Models\Division;
use App\Models\Plant;
use Illuminate\Http\Request;

class ChecksheetUserController extends Controller
{
    public function index()
    {
        $checksheetUsers = ChecksheetUser::with([
            'competency',
            'subSection',
            'section',
            'department',
            'division',
            'plant'
        ])->paginate(10);
        
        $subSections = SubSection::all();
        $sections = Section::all();
        $departments = Department::all();
        $divisions = Division::all();
        $plants = Plant::all();

        return view('website.checksheet_user.index', compact(
            'checksheetUsers',
            'subSections',
            'sections',
            'departments',
            'divisions',
            'plants'
        ));
    }

    public function create()
    {
        return view('checksheet_user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'position' => 'required|in:Operator,JP,Act JP,Leader,Act Leader,Supervisor,Section Head,Coordinator,Manager,GM,Director',
            'competency_id' => 'required|exists:competency,id',
            'sub_section_id' => 'nullable|exists:sub_sections,id',
            'section_id' => 'nullable|exists:sections,id',
            'department_id' => 'nullable|exists:departments,id',
            'division_id' => 'nullable|exists:divisions,id',
            'plant_id' => 'nullable|exists:plants,id',
        ]);

        // Set level sesuai posisi
        $levelData = $this->getLevelData($request->position, $request);

        ChecksheetUser::create(array_merge([
            'question' => $request->question,
            'competency_id' => $request->competency_id,
            'position' => $request->position,
        ], $levelData));

        return response()->json([
            'success' => true,
            'message' => 'Checksheet User created successfully'
        ]);
    }

    public function destroy(ChecksheetUser $checksheetUser)
    {
        $checksheetUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'Checksheet User deleted successfully'
        ]);
    }

    public function getCompetencies(Request $request)
    {
        $query = Competency::query();
        
        // Filter berdasarkan level yang dipilih
        switch ($request->position) {
            case 'Operator':
            case 'JP':
            case 'Act JP':
            case 'Leader':
            case 'Act Leader':
                $query->where('sub_section_id', $request->sub_section_id);
                break;
            
            case 'Supervisor':
            case 'Section Head':
                $query->where('section_id', $request->section_id);
                break;
            
            case 'Coordinator':
            case 'Manager':
                $query->where('department_id', $request->department_id);
                break;
            
            case 'GM':
                $query->where('division_id', $request->division_id);
                break;
            
            case 'Director':
                $query->where('plant_id', $request->plant_id);
                break;
        }

        // Filter berdasarkan posisi
        $query->where('position', $request->position);

        $competencies = $query->get();

        return response()->json($competencies);
    }

    private function getLevelData($position, $request)
    {
        $levelData = [
            'sub_section_id' => null,
            'section_id' => null,
            'department_id' => null,
            'division_id' => null,
            'plant_id' => null,
        ];

        switch ($position) {
            case 'Operator':
            case 'JP':
            case 'Act JP':
            case 'Leader':
            case 'Act Leader':
                $levelData['sub_section_id'] = $request->sub_section_id;
                break;
            
            case 'Supervisor':
            case 'Section Head':
                $levelData['section_id'] = $request->section_id;
                break;
            
            case 'Coordinator':
            case 'Manager':
                $levelData['department_id'] = $request->department_id;
                break;
            
            case 'GM':
                $levelData['division_id'] = $request->division_id;
                break;
            
            case 'Director':
                $levelData['plant_id'] = $request->plant_id;
                break;
        }

        return $levelData;
    }
}