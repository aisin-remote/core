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
        $employeeCompetency = EmployeeCompetency::with('employee')
        ->where('employee_id', $employeeId)
        ->where('competency_id', $competencyId)
        ->first();

        // Jika sudah ada penilaian, periksa otorisasi untuk perbaikan
        if ($employeeCompetency && $employeeCompetency->checksheetAssessments()->exists()) {
            $hierarchy = [
                'Direktur',
                'GM',
                'Manager',
                'Coordinator',
                'Section Head',
                'Supervisor',
                'Leader',
                'JP',
                'Operator',
            ];
            
            $employeePosition = $employeeCompetency->employee->position ?? 'Operator';
            $userPosition = auth()->user()->employee->position ?? 'Operator';
            
            $employeeIndex = array_search($employeePosition, $hierarchy);
            $userIndex = array_search($userPosition, $hierarchy);

            $allowImprove = ($employeeCompetency->act != 2) && 
                        ($userIndex !== false && $employeeIndex !== false && $userIndex < $employeeIndex);
            
            if (!$allowImprove) {
                return redirect()->route('checksheet-assessment.view', $employeeCompetency->id);
            }
        }

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
        $employeeCompetency = EmployeeCompetency::with(['competency.checkSheets', 'employee'])
            ->findOrFail($employeeCompetencyId);

        // Ambil percobaan terakhir
        $lastAttempt = ChecksheetAssessment::where('employee_competency_id', $employeeCompetencyId)
            ->max('attempt') ?? 1;
            
        $existingAssessments = ChecksheetAssessment::where('employee_competency_id', $employeeCompetencyId)
            ->where('attempt', $lastAttempt)
            ->get()
            ->keyBy('checksheet_id');

        // Hitung status kelulusan
        $totalChecksheets = $employeeCompetency->competency->checkSheets->count();
        $score3Count = $existingAssessments->where('score', 3)->count();
        $percentage = $totalChecksheets > 0 ? ($score3Count / $totalChecksheets) * 100 : 0;
        $isPassed = $percentage >= 70;

        // Dapatkan hierarki posisi
        $hierarchy = [
            'Direktur',
            'GM',
            'Manager',
            'Coordinator',
            'Section Head',
            'Supervisor',
            'Leader',
            'JP',
            'Operator',
        ];

        // Dapatkan posisi karyawan yang dinilai
        $employeePosition = $employeeCompetency->employee->position ?? 'Operator';
        
        // Dapatkan posisi user saat ini
        $userPosition = auth()->user()->employee->position ?? 'Operator';
        
        // Cek apakah user adalah atasan yang berwenang
        $isAuthorizedSuperior = false;
        $employeeIndex = array_search($employeePosition, $hierarchy);
        $userIndex = array_search($userPosition, $hierarchy);
        
        if ($userIndex !== false && $employeeIndex !== false && $userIndex < $employeeIndex) {
            $isAuthorizedSuperior = true;
        }

        return view('website.checksheet_assessment.view', [
            'employeeCompetency' => $employeeCompetency,
            'competency' => $employeeCompetency->competency,
            'checksheets' => $employeeCompetency->competency->checkSheets,
            'existingAssessments' => $existingAssessments,
            'isPassed' => $isPassed,
            'percentage' => $percentage,
            'isAuthorizedSuperior' => $isAuthorizedSuperior,
            'attempt' => $lastAttempt // Kirim percobaan terakhir
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

        $ec = EmployeeCompetency::findOrFail($request->employee_competency_id);
        
        // Hitung attempt terbaru
        $lastAttempt = ChecksheetAssessment::where('employee_competency_id', $ec->id)
            ->max('attempt') ?? 0;
        $currentAttempt = $lastAttempt + 1;

        $totalChecksheets = count($request->score);
        $score3Count = 0;

        foreach ($request->score as $csId => $score) {
            if ($score == 3) $score3Count++;

            ChecksheetAssessment::create([
                'employee_competency_id' => $ec->id,
                'checksheet_id' => $csId,
                'score' => $score,
                'attempt' => $currentAttempt // Simpan attempt
            ]);
        }

        $percentage = ($score3Count / $totalChecksheets) * 100;
        $isPassed = $percentage >= 70;

        // Update status kompetensi
        $ec->update(['act' => $isPassed ? 3 : 2]);

        // Pesan
        if ($isPassed) {
            $message = 'Penilaian berhasil disimpan. Selamat, Anda lolos!';
        } else {
            // Gunakan link baru ke history spesifik kompetensi
            $historyLink = route('checksheet-assessment.competency-history', [
                'employeeId' => $ec->employee_id,
                'competencyId' => $ec->competency_id
            ]);
            
            $message = 'Penilaian berhasil disimpan. Maaf, Anda belum lolos. ';
            $message .= "<a href='$historyLink' class='alert-link'>Lihat detail checksheet yang perlu diperbaiki</a>";
        }
    
        return redirect()->route('checksheet-assessment.view', $ec->id)
            ->with('success', $message);
    }
    
    public function competencyHistory($employeeId, $competencyId)
    {
        $failedAttempts = ChecksheetAssessment::with([
                'employeeCompetency.competency', 
                'checksheet'
            ])
            ->whereHas('employeeCompetency', function($query) use ($employeeId, $competencyId) {
                $query->where('employee_id', $employeeId)
                    ->where('competency_id', $competencyId);
            })
            ->where('score', '<', 3)
            ->get()
            ->groupBy('attempt');

        $competency = Competency::findOrFail($competencyId);

        return view('website.checksheet_assessment.history', [
            'failedAttempts' => $failedAttempts,
            'employeeId' => $employeeId,
            'competency' => $competency
        ]);
    }
}