<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EmployeeCompetency;
use App\Models\ChecksheetUser;
use App\Models\Competency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EvaluationController extends Controller
{
    public function getChecksheetUsers($employeeId, $competencyId)
    {
        $competencies = Competency::with([
            'checksheetUsers',
            'employeeCompetencies' => function($q) use ($employeeId, $competencyId) {
                $q->where('employee_id', $employeeId)
                  ->where('competency_id', $competencyId)
                  ->withCount(['evaluations as has_evaluation' => function($query) {
                      $query->where('score', '>', 0);
                  }]);
            }
        ])
        ->where('id', $competencyId)
        ->get();

        return response()->json([
            'competencies' => $competencies->map(function($comp) {
                $empComp = $comp->employeeCompetencies->first();
                return [
                    'id'                     => $comp->id,
                    'name'                   => $comp->name,
                    'checksheet_users'       => $comp->checksheetUsers,
                    'has_evaluation'         => $empComp ? $empComp->has_evaluation > 0 : false,
                    'employee_competency_id' => $empComp->id ?? null,
                ];
            })
        ]);
    }

    protected function fetchChecksheetUsersData(int $employeeId, int $competencyId): array
    {
        $response = $this->getChecksheetUsers($employeeId, $competencyId);
        return json_decode($response->getContent(), true);
    }

    public function index(int $employeeCompetencyId)
    {
        $empComp = EmployeeCompetency::with(['evaluations'])
                    ->findOrFail($employeeCompetencyId);

        // ambil checksheet user terkait kompetensi
        $competencyId = $empComp->competency_id;
        $employeeId   = $empComp->employee_id;

        $competencies = Competency::with(['checksheetUsers'])       
                         ->where('id', $competencyId)
                         ->first();

        $checksheetUsers = $competencies->checksheetUsers ?? [];

        // existing evaluations
        $existing = Evaluation::where('employee_competency_id', $employeeCompetencyId)
                              ->get()
                              ->keyBy('checksheet_user_id');

        // siapkan collection evaluasi
        $evaluations = collect($checksheetUsers)
            ->map(function($cs) use ($employeeCompetencyId, $existing) {
                $eval = $existing->get($cs->id)
                      ?? new Evaluation([
                          'employee_competency_id' => $employeeCompetencyId,
                          'checksheet_user_id'     => $cs->id,
                          'answer'                 => null,
                          'score'                  => null,
                          'file'                   => null,
                      ]);
                // ubah ID untuk input name
                $eval->id = $cs->id;
                $eval->question_text = $cs->question;
                return $eval;
            });

        // status dan persentase
        $hasScores  = $empComp->evaluations->pluck('score')->filter()->count() > 0;
        $totalScore = $empComp->evaluations->sum('score');
        $maxScore   = $empComp->evaluations->count() * 5;
        $percentage = $maxScore ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $isPassed   = $percentage >= 70;

        // logika allowEdit & showScores
        $allowEdit = false;
        $showScores = false;

        if ($empComp->act === 0) {
            $allowEdit = true;
        } elseif ($empComp->act === 1) {
            $allowEdit = true;
            if ($hasScores) {
                $showScores = true;
                if ($isPassed) {
                    $allowEdit = false;
                }
            }
        } elseif ($empComp->act === 2) {
            $showScores = true;
        }

        return view('website.evaluation.index', [
            'employeeCompetency' => $empComp,
            'competency'         => $empComp->competency,
            'evaluations'        => $evaluations,
            'title'              => 'Evaluasi Kompetensi: ' . $empComp->competency->name,
            'allowEdit'          => $allowEdit,
            'showScores'         => $showScores,
            'percentage'         => $percentage,
            'isPassed'           => $isPassed,
            'hasScores'          => $hasScores,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_competency_id' => 'required|exists:employee_competency,id',
            'answer'                 => 'required|array',
            'file'                   => 'nullable|array',
            'file.*'                 => 'file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        $empCompId = $request->employee_competency_id;
        $answers   = $request->answer;
        $files     = $request->file('file', []);

        DB::beginTransaction();
        try {
            foreach ($answers as $csId => $answer) {
                $eval = Evaluation::firstOrNew([
                    'employee_competency_id' => $empCompId,
                    'checksheet_user_id'     => $csId,
                ]);
                $eval->answer = $answer;
                if (isset($files[$csId])) {
                    if ($eval->file) {
                        Storage::disk('public')->delete($eval->file);
                    }
                    $path = $files[$csId]->store('evaluation_files', 'public');
                    $eval->file = $path;
                }
                $eval->score = null;
                $eval->save();
            }

            $empComp = EmployeeCompetency::findOrFail($empCompId);
            $empComp->act = 1;
            $empComp->save();

            DB::commit();
            return redirect()->route('evaluation.index', $empCompId)
                             ->with('success', 'Evaluation successfully saved!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with('error', 'Gagal menyimpan evaluasi.');
        }
    }

    public function updateScores(Request $request, $employee_competency_id)
    {
        $request->validate([
            'score' => 'required|array',
        ]);

        $scores = $request->score;

        try {
            DB::beginTransaction();

            $empComp = EmployeeCompetency::findOrFail($employee_competency_id);

            foreach ($scores as $evaluationId => $score) {
                $evaluation = Evaluation::findOrFail($evaluationId);
                $evaluation->score = $score;
                $evaluation->save();
            }

            $evaluations = Evaluation::where('employee_competency_id', $employee_competency_id)->get();
            $totalScore = $evaluations->sum('score');
            $maxScore = $evaluations->count() * 5;
            $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
            $isPassed = $percentage >= 70;

            $empComp->act = $isPassed ? 2 : 1;
            $empComp->save();

            DB::commit();

            return redirect()->route('evaluation.view', $employee_competency_id)
                ->with('success', 'Penilaian berhasil disimpan. ' . 
                    ($isPassed ? 'Karyawan dinyatakan lulus.' : 'Belum lulus, persentase: ' . $percentage . '%. Harap perbaiki evaluasi.'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating scores: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function view($employee_competency_id)
    {
        $employeeCompetency = EmployeeCompetency::with([
            'employee',
            'competency',
            'evaluations.checksheet'
        ])->findOrFail($employee_competency_id);

        if (!$employeeCompetency->competency) {
            abort(404, 'Competency not found');
        }

        $evaluations = $employeeCompetency->evaluations;
        
        $totalScore = 0;
        $maxScore = count($evaluations) * 5;
        
        foreach ($evaluations as $eval) {
            $totalScore += $eval->score ?? 0;
        }
        
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $isPassed = $percentage >= 70;

        return view('website.evaluation.view', [
            'employeeCompetency' => $employeeCompetency,
            'evaluations'        => $evaluations,
            'percentage'         => $percentage,
            'isPassed'           => $isPassed,
        ]);
    }
}