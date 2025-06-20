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
    /**
     * JSON endpoint untuk men‐fetch list pertanyaan (checksheet_users)
     * beserta flag apakah sudah ada evaluasi.
     */
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
        // Panggil method JSON‐based dan decode hasilnya
        $response = $this->getChecksheetUsers($employeeId, $competencyId);
        return json_decode($response->getContent(), true);
    }

    /**
     * Tampilkan form evaluasi.
     */
    public function index(int $employeeCompetencyId)
    {
        // 1) Load employee_competency + competency + employee
        $empComp = EmployeeCompetency::with(['employee','competency'])
            ->findOrFail($employeeCompetencyId);

        // 2) Pastikan relasi competency ada
        if (! $empComp->competency) {
            abort(404, 'Competency not found');
        }

        // 3) Panggil JSON helper untuk fetch checksheet_users + status
        $payload = $this->fetchChecksheetUsersData(
            $empComp->employee_id,
            $empComp->competency_id
        );

        // 4) Karena JSON-nya { competencies: [ { checksheet_users: [...] } ] }
        //    kita ambil array checksheet_users dari elemen pertama
        $checksheetUsers = $payload['competencies'][0]['checksheet_users'] ?? [];

        // 5) Ambil existing Evaluations, keyed by checksheet_user_id
        $existing = Evaluation::where('employee_competency_id', $empComp->id)
                              ->get()
                              ->keyBy('checksheet_user_id');

        // 6) Siapkan array Evaluations untuk view
        $evaluations = collect($checksheetUsers)
            ->map(function($q) use ($empComp, $existing) {
                // $q adalah array hasil JSON: [ 'id'=>..., 'question'=>..., ... ]
                $checksheetId = $q['id'];
                $eval = $existing->get($checksheetId);

                if (! $eval) {
                    // belum ada di DB, bikin instance baru
                    $eval = new Evaluation([
                        'employee_competency_id' => $empComp->id,
                        'checksheet_user_id'     => $checksheetId,
                        'answer'                 => null,
                        'score'                  => null,
                        'file'                   => null,
                    ]);

                }
                                    
                    // Tambahkan ID virtual (untuk keperluan input name di form)
                    $eval->id = $checksheetId;
                    
                    // Simpan teks pertanyaan dari JSON
                    $eval->question_text = $q['question'];
                    return $eval;
            });

        // 7) Render view
        return view('website.evaluation.index', [
            'employeeCompetency' => $empComp,
            'competency'         => $empComp->competency,
            'evaluations'        => $evaluations,
            'title'              => 'Evaluasi Kompetensi: ' . $empComp->competency->name,
        ]);
    }

    /**
     * Simpan hasil evaluasi (answer, score, file).
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_competency_id' => 'required|exists:employee_competency,id',
            'answer'                 => 'required|array',
            'score'                  => 'required|array',
        ]);

        $employeeCompetencyId = $request->employee_competency_id;
        $answers              = $request->answer;
        $scores               = $request->score;
        $files                = $request->file('file', []);

        try {
            DB::beginTransaction();

            foreach ($answers as $checksheetUserId => $answer) {
                // Cek apakah sudah ada evaluasi sebelumnya
                $evaluation = Evaluation::firstOrCreate(
                    [
                        'employee_competency_id' => $employeeCompetencyId,
                        'checksheet_user_id'     => $checksheetUserId
                    ],
                    [
                        'answer' => null,
                        'score'  => null,
                        'file'   => null,
                    ]
                );

                // Update answer dan score
                $evaluation->answer = $answer;
                $evaluation->score  = $scores[$checksheetUserId] ?? null;

                // Upload file jika ada
                if (isset($files[$checksheetUserId]) && $files[$checksheetUserId]->isValid()) {
                    if ($evaluation->file) {
                        Storage::disk('public')->delete($evaluation->file);
                    }
                    $evaluation->file = $files[$checksheetUserId]->store('evaluation_files', 'public');
                }

                $evaluation->save();
            }

            DB::commit();

            return redirect()
                ->route('evaluation.index', $employeeCompetencyId)
                ->with('success', 'Evaluasi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving evaluation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Utility: hitung filter level (sub_section, section, …) 
     * berdasarkan data di relasi Competency.
     */
    private function getLevelConditions(EmployeeCompetency $empComp): array
    {
        $comp     = $empComp->competency;
        $position = $empComp->position;

        $conds = [
            'sub_section_id' => null,
            'section_id'     => null,
            'department_id'  => null,
            'division_id'    => null,
            'plant_id'       => null,
        ];

        switch ($position) {
            case 'Operator':
            case 'JP':
            case 'Act JP':
            case 'Leader':
            case 'Act Leader':
                $conds['sub_section_id'] = $comp->sub_section_id;
                break;
            case 'Supervisor':
            case 'Section Head':
                $conds['section_id']     = $comp->section_id;
                break;
            case 'Coordinator':
            case 'Manager':
                $conds['department_id']  = $comp->department_id;
                break;
            case 'GM':
                $conds['division_id']    = $comp->division_id;
                break;
            case 'Director':
                $conds['plant_id']       = $comp->plant_id;
                break;
        }

        return $conds;
    }
}
