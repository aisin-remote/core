<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use App\Models\EmployeeCompetency;
use App\Models\ChecksheetAssessment;
use Illuminate\Http\Request;

class ChecksheetAssessmentController extends Controller
{
    public function index($employeeId, $competencyId)
    {
        // Cek apakah sudah ada assessment
        $employeeCompetency = EmployeeCompetency::where('employee_id', $employeeId)
            ->where('competency_id', $competencyId)
            ->first();

        if ($employeeCompetency && $employeeCompetency->checksheetAssessments()->exists()) {
            return redirect()->route('checksheet-assessment.view', $employeeCompetency->id);
        }

        // Jika belum ada, buat record baru
        if (!$employeeCompetency) {
            $employeeCompetency = EmployeeCompetency::create([
                'employee_id' => $employeeId,
                'competency_id' => $competencyId
            ]);
        }

        $competency = Competency::with('checkSheets')->findOrFail($competencyId);

        return view('website.checksheet_assessment.index', [
            'competency' => $competency,
            'checksheets' => $competency->checkSheets,
            'employeeCompetency' => $employeeCompetency
        ]);
    }

    public function show($employeeCompetencyId)
    {
        $employeeCompetency = EmployeeCompetency::with(['competency.checkSheets'])
            ->findOrFail($employeeCompetencyId);

        $existingAssessments = ChecksheetAssessment::where('employee_competency_id', $employeeCompetencyId)
            ->get()
            ->keyBy('checksheet_id');

        return view('website.checksheet_assessment.view', [
            'competency' => $employeeCompetency->competency,
            'checksheets' => $employeeCompetency->competency->checkSheets,
            'existingAssessments' => $existingAssessments
        ]);
    }

    public function getChecksheets($employeeId)
    {
        $competencies = Competency::with(['checkSheets', 'employeeCompetencies' => function($q) use ($employeeId) {
            $q->where('employee_id', $employeeId)
            ->withCount(['checksheetAssessments as has_assessment' => function($query) {
                $query->where('score', '>', 0);
            }]);
        }])->get();

        return response()->json([
            'competencies' => $competencies->map(function($comp) {
                $employeeCompetency = $comp->employeeCompetencies->first();
                
                return [
                    'id' => $comp->id,
                    'name' => $comp->name,
                    'checksheets' => $comp->checkSheets,
                    'has_assessment' => $employeeCompetency ? $employeeCompetency->has_assessment > 0 : false,
                    'employee_competency_id' => $employeeCompetency->id ?? null
                ];
            })
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'employee_competency_id' => 'required|exists:employee_competency,id',
            'score' => 'required|array|min:1'
        ]);

        // Cek apakah sudah ada penilaian
        $existing = ChecksheetAssessment::where('employee_competency_id', $request->employee_competency_id)
            ->exists();

        if ($existing) {
            return redirect()->back()->with('error', 'Penilaian sudah pernah disimpan!');
        }

        // Simpan data baru
        foreach ($request->score as $checksheetId => $score) {
            ChecksheetAssessment::create([
                'checksheet_id' => $checksheetId,
                'employee_competency_id' => $request->employee_competency_id,
                'score' => $score,
                'description' => $request->description[$checksheetId] ?? null
            ]);
        }

        return redirect()->route('employeeCompetencies.index')
            ->with('success', 'Penilaian berhasil disimpan!');
    }
}