<?php

namespace App\Http\Controllers;

use App\Models\ChecksheetAssessment;
use App\Models\EmployeeCompetency;
use App\Models\EvidenceHistory;
use App\Models\GroupCompetency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SkillMatrixController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $employee = $user->employee;
        if (! $employee) {
            abort(404, 'Employee record not found for this user.');
        }

        $ecs = $employee->employeeCompetencies()
                ->with(['competency.group_competency','evidenceHistories.actor'])
                ->get();

        $matrixData = $ecs->map(function($ec) {     
            return [
                'id'                    => $ec->competency->id,
                'employee_id'           => $ec->id,
                'group'                 => $ec->competency->group_competency->name,
                'name'                  => $ec->competency->name,
                'act'                   => $ec->act,
                'file'                  => $ec->file,
                'plan'                  => $ec->competency->plan,
                'employee_competency_id'=> $ec->id,
                'position'              => $ec->competency->position,
            ];
        })->toArray();

        $groups = GroupCompetency::pluck('name')->toArray();
        $title  = 'My Skill Matrix';

        return view('website.skill_matrix.index', compact(
            'title', 'matrixData', 'groups', 'ecs', 'employee'
        ));
    }

    public function checksheet($employeeCompetencyId)
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (! $employee) {
            abort(404, 'Employee record not found for this user.');
        }

        $ec = EmployeeCompetency::with(['competency.group_competency'])
            ->where('id', $employeeCompetencyId)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $competency = $ec->competency;
        $pos = $competency->position;

        $checksheets = $competency->checkSheets()
            ->where('position', $pos)
            ->get(['id', 'name', 'created_at']);

        // Ambil percobaan terakhir
        $lastAttempt = ChecksheetAssessment::where('employee_competency_id', $ec->id)
            ->max('attempt') ?? 1;
            
        // Ambil existing assessments untuk percobaan terakhir
        $existingAssessments = ChecksheetAssessment::where('employee_competency_id', $ec->id)
            ->where('attempt', $lastAttempt)
            ->get()
            ->keyBy('checksheet_id');

        // Hitung status kelulusan
        $totalChecksheets = $checksheets->count();
        $score3Count = $existingAssessments->where('score', 3)->count();
        $percentage = $totalChecksheets > 0 ? ($score3Count / $totalChecksheets) * 100 : 0;
        $isPassed = $percentage >= 70;

        return view('website.skill_matrix.checksheet', compact(
            'competency', 
            'checksheets', 
            'existingAssessments',
            'isPassed',
            'percentage',
            'lastAttempt',
            'score3Count'
        ));
    }
    public function uploadEvidence(Request $request, $id)
    {
        $request->validate([
            'evidence_file' => 'required|file|max:10240',
        ]);

        $ec = EmployeeCompetency::where('id', $id)
            ->where('employee_id', Auth::user()->employee->id)
            ->firstOrFail();

        // ambil original filename
        $originalName = $request->file('evidence_file')->getClientOriginalName();

        // simpan dengan nama asli
        $path = $request->file('evidence_file')
                ->storeAs('evidence', $originalName, 'public');

        $ec->file = $path;            // akan jadi "evidence/namafile.pdf"
        $ec->save();

        return back()->with('success', 'Evidence berhasil di-upload');
    }


    public function show($id)
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (! $employee) {
            abort(404, 'Employee record not found.');
        }

        $ec = EmployeeCompetency::with('competency.group_competency')
             ->where('id', $id)
             ->where('employee_id', $employee->id)
             ->firstOrFail();

        return view('website.skill_matrix.show', [
            'employeeCompetency' => $ec
        ]);
    }

    public function approval()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        
        // Pastikan user memiliki employee record
        if (!$user->employee) {
            abort(404, 'Employee record not found for this user.');
        }

        $hierarchy = [
            'Direktur',
            'GM',
            'Manager',
            'Coordinator',
            'Section Head',
            'Supervisor',
            'Leader',
            'Act Leader',
            'JP',
            'Act JP',
            'Operator',
        ];

        // Indeks posisi user
        $myPos = $user->employee->position;
        $myIndex = array_search($myPos, $hierarchy);
        if ($myIndex === false) {
            $myIndex = count($hierarchy) - 1;
        }

        // Daftar posisi yang bisa diapprove (hanya di bawah user)
        $approvablePositions = array_slice($hierarchy, $myIndex + 1);

        // Query untuk pending approvals
        $pendingQuery = EmployeeCompetency::with(['employee', 'competency'])
            ->whereNotNull('file')
            ->where('act', 0);

        // Filter berdasarkan posisi (kecuali HRD)
        if ($user->role !== 'HRD') {
            $pendingQuery->whereHas('employee', function ($query) use ($approvablePositions) {
                $query->whereIn('position', $approvablePositions);
            });
        }

        $pending = $pendingQuery->get();

        // Query untuk history
        $historyQuery = EvidenceHistory::with([
            'employeeCompetency.employee', 
            'employeeCompetency.competency',
            'actor'
        ]);

        // Filter history berdasarkan posisi (kecuali HRD)
        if ($user->role !== 'HRD') {
            $historyQuery->whereHas('employeeCompetency', function($query) use ($approvablePositions) {
                $query->whereHas('employee', function($q) use ($approvablePositions) {
                    $q->whereIn('position', $approvablePositions);
                });
            });
        }

        $history = $historyQuery->orderBy('created_at', 'desc')->get();

        return view('website.approval.skill_matrix.index', [
            'pending' => $pending,
            'history' => $history,
            'approvablePositions' => $approvablePositions // Kirim ke view untuk info
        ]);
    }

    /**
     * Approve: set act = 1
     */
    public function approve($id)
    {
        $ec = EmployeeCompetency::findOrFail($id);
        $ec->act = 1;
        $ec->save();

        // catat history
        EvidenceHistory::create([
            'employee_competency_id' => $ec->id,
            'user_id'                => Auth::id(),
            'action'                 => 'approve',
            'file_name'              => $ec->file
        ]);

        return redirect()->route('skillMatrix.approval')    
                        ->with('success', 'Evidence approved.');
    }

    public function unapprove($id)
    {
        $ec = EmployeeCompetency::findOrFail($id);

        $fileName = $ec->file;
        
        if ($ec->file) {
            Storage::disk('public')->delete($ec->file);
        }
        $ec->file = null;
        $ec->save();

        EvidenceHistory::create([
            'employee_competency_id' => $ec->id,
            'user_id'                => Auth::id(),
            'action'                 => 'unapprove',
            'file_name'              => $fileName
        ]);

        return redirect()->route('skillMatrix.approval')
                        ->with('success', 'Evidence unapproved.');
    }
}
