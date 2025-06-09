<?php

namespace App\Http\Controllers;

use App\Models\ChecksheetAssessment;
use App\Models\EmployeeCompetency;
use App\Models\GroupCompetency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SkillMatrixController extends Controller
{
    public function index()
    {
        // 1) Ambil user yang sedang login
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        // 2) Dari user → langsung ambil employee lewat hasOne relasi
        $employee = $user->employee;
        if (! $employee) {
            abort(404, 'Employee record not found for this user.');
        }

        // 3) Ambil semua kompetensi milik employee ini
        $ecs = $employee->employeeCompetencies()
                        ->with('competency.group_competency')
                        ->get();

        // 4) Mapping menjadi matrixData untuk dikirim ke view (JS)
        $matrixData = $ecs->map(function($ec) {
            return [
                'id'                    => $ec->competency->id,
                'group'                 => $ec->competency->group_competency->name,
                'name'                  => $ec->competency->name,
                'act'                   => $ec->act,
                'plan'                  => $ec->competency->plan,
                'employee_competency_id'=> $ec->id,
                'position'              => $ec->competency->position,
            ];
        })->toArray();

        // 5) Ambil semua nama group competency untuk tab
        $groups = GroupCompetency::pluck('name')->toArray();
        $title  = 'My Skill Matrix';

        return view('website.skill_matrix.index', compact(
            'title', 'matrixData', 'groups'
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

        // Hanya pilih kolom yang memang ada: id, name, created_at
        $checksheets = $competency->checkSheets()
            ->where('position', $pos)
            ->get(['id', 'name', 'created_at']);

        // Ambil existing assessments (jika ada)
        $existingAssessments = ChecksheetAssessment::where('employee_competency_id', $ec->id)
            ->get()
            ->keyBy('checksheet_id');

        return view('website.skill_matrix.checksheet', compact(
            'competency', 'checksheets', 'existingAssessments'
        ));
    }

    public function uploadEvidence(Request $request, $id)
    {
        $request->validate([
            'evidence_file' => 'required|file|mimes:pdf,jpg,png,docx|max:5120',
        ]);

        $ec = EmployeeCompetency::where('id', $id)
            ->where('employee_id', Auth::user()->employee->id)
            ->firstOrFail();

        $path = $request->file('evidence_file')->store('evidence', 'public');

        $ec->file = $path;
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
        $pending = EmployeeCompetency::with(['employee','competency'])
            ->whereNotNull('file')
            ->where('act', 0)
            ->get();

        return view('website.approval.approvalskillmatrix', compact('pending'));
    }

    /**
     * Approve: set act = 1
     */
    public function approve($id)
    {
        $ec = EmployeeCompetency::findOrFail($id);
        $ec->act = 1;
        $ec->save();

        return redirect()->route('skillMatrix.approval')
                         ->with('success', 'Evidence approved.');
    }

    /**
     * Unapprove: hapus file, tetap act = 0
     */
    public function unapprove($id)
    {
        $ec = EmployeeCompetency::findOrFail($id);
        if ($ec->file) {
            Storage::disk('public')->delete($ec->file);
        }
        $ec->file = null;
        $ec->save();

        return redirect()->route('skillMatrix.approval')
                         ->with('success', 'Evidence unapproved.');
    }
}
