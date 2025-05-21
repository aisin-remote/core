<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use App\Models\EmployeeCompetency;
use App\Models\ChecksheetAssessment;
use Illuminate\Http\Request;

class ChecksheetAssessmentController extends Controller
{
    public function index($competencyId)
    {
        $competency = Competency::with('checkSheets')->findOrFail($competencyId);
        return view('website.checksheet_assessment.index', [
            'competency' => $competency,
            'checksheets' => $competency->checkSheets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'competency_id' => 'required|exists:competency,id',
            'score' => 'required|array',
            'description' => 'nullable|array'
        ]);

        $employeeId = auth()->user()->employee_id;

        $employeeCompetency = EmployeeCompetency::where('employee_id', $employeeId)
            ->where('competency_id', $request->competency_id)
            ->firstOrFail();

        foreach ($request->score as $checksheetId => $score) {
            ChecksheetAssessment::updateOrCreate(
                [
                    'checksheet_id' => $checksheetId,
                    'employee_competency_id' => $employeeCompetency->id
                ],
                [
                    'score' => $score,
                    'description' => $request->description[$checksheetId] ?? null
                ]
            );
        }

        return redirect()->route('employeeCompetencies.index')->with('success', 'Penilaian berhasil disimpan');
    }
}