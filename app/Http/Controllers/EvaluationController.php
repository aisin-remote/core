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
        $empComp = EmployeeCompetency::with(['employee','competency', 'evaluations'])
            ->findOrFail($employeeCompetencyId);

        if (! $empComp->competency) {
            abort(404, 'Competency not found');
        }

        $payload = $this->fetchChecksheetUsersData(
            $empComp->employee_id,
            $empComp->competency_id
        );

        $checksheetUsers = $payload['competencies'][0]['checksheet_users'] ?? [];

        $existing = Evaluation::where('employee_competency_id', $empComp->id)
                              ->get()
                              ->keyBy('checksheet_user_id');

        $evaluations = collect($checksheetUsers)
            ->map(function($q) use ($empComp, $existing) {
                $checksheetId = $q['id'];
                $eval = $existing->get($checksheetId);

                if (! $eval) {
                    $eval = new Evaluation([
                        'employee_competency_id' => $empComp->id,
                        'checksheet_user_id'     => $checksheetId,
                        'answer'                 => null,
                        'score'                  => null,
                        'file'                   => null,
                    ]);
                }
                                    
                $eval->id = $checksheetId;
                $eval->question_text = $q['question'];
                return $eval;
            });

        // Hitung persentase jika sudah ada nilai
        $percentage = null;
        $isPassed = false;
        $hasScores = $empComp->evaluations->contains('score', '!==', null);
        
        if ($hasScores) {
            $totalScore = $empComp->evaluations->sum('score');
            $maxScore = $empComp->evaluations->count() * 5;
            $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
            $isPassed = $percentage >= 70;
        }

        // Tentukan apakah user boleh mengedit
        $allowEdit = false;
        $showScores = false;
        
        if ($empComp->act == 0) {
            // Belum mulai: boleh edit
            $allowEdit = true;
        } elseif ($empComp->act == 1) {
            // Sudah submit
            if ($hasScores) {
                // Sudah dinilai
                $showScores = true;
                if (!$isPassed) {
                    // Belum lulus: boleh edit
                    $allowEdit = true;
                }
            }
        } elseif ($empComp->act == 2) {
            // Lulus: tampilkan nilai
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

        $employeeCompetencyId = $request->employee_competency_id;
        $answers = $request->answer;
        $files = $request->file('file', []);

        try {
            DB::beginTransaction();

            $empComp = EmployeeCompetency::findOrFail($employeeCompetencyId);

            // Hanya izinkan penyimpanan jika belum lulus
            if ($empComp->act == 2) {
                throw new \Exception('Anda sudah lulus, tidak dapat mengubah jawaban');
            }

            foreach ($answers as $csId => $answer) {
                $eval = Evaluation::firstOrCreate(
                    [
                        'employee_competency_id' => $employeeCompetencyId,
                        'checksheet_user_id'     => $csId,
                    ],
                    [
                        'answer' => null,
                        'score'  => null,
                        'file'   => null,
                    ]
                );

                $eval->answer = $answer;
                
                if (array_key_exists($csId, $files) && $file = $files[$csId]) {
                    if ($eval->file) {
                        Storage::delete('public/'.$eval->file);
                    }
                    $path = $file->store('evaluation_files', 'public');
                    $eval->file = $path;
                }

                // Reset nilai jika ada perubahan
                $eval->score = null;
                $eval->save();
            }

            $empComp->act = 1; // Set status menunggu penilaian
            $empComp->save();

            DB::commit();

            return redirect()
                ->route('evaluation.index', $employeeCompetencyId)
                ->with('success', 'Evaluasi berhasil disimpan! Menunggu penilaian.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving evaluation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
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