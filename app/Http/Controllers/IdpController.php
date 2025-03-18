<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\Development;
use App\Models\DevelopmentOne;
use Illuminate\Http\Request;
use App\Models\DetailAssessment;
use App\Models\Idp;
use Illuminate\Support\Facades\DB;

class IdpController extends Controller
{
    public function index($reviewType = 'mid_year')
{
    $alcs = [
        1 => 'Vision & Business Sense',
        2 => 'Customer Focus',
        3 => 'Interpersonal Skill',
        4 => 'Analysis & Judgment',
        5 => 'Planning & Driving Action',
        6 => 'Leading & Motivating',
        7 => 'Teamwork',
        8 => 'Drive & Courage'
    ];

    $assessments = Assessment::with(['employee', 'details', 'idp'])->paginate(10);
    $employees = Employee::all();
    $idps = Idp::with('assessment', 'employee')->get();

    $programs = [
        'Superior (DGM & GM) + DIC PUR + BOD Member',
        'Book Reading / Journal Business and BEST PRACTICES (Asia Pasific Region)',
        'To find "FIGURE LEADER" with Strong in Drive and Courage in Their Team --> Sharing Success Tips',
        'Team Leader of TASK FORCE with MULTY FUNCTION --> (AII) HYBRID DUMPER Project  (CAPACITY UP) & (AIIA) EV Project',
        'SR Project (Structural Reform -->DM & SCM)',
        'PEOPLE Development Program of Team members (ICT, IDP)',
        '(Leadership) --> Courageously & Situational Leadership',
        '(Developing Sub Ordinate) --> Coaching Skill / Developing Talents'
    ];

    $details = DevelopmentOne::all();
    $mid = Development::all();

    // Ambil program dari IDP yang sudah tersimpan
    foreach ($assessments as $assessment) {
        $savedPrograms = $assessment->idp->pluck('development_program')->toArray();
        $assessment->recommendedPrograms = $savedPrograms;
    }

    foreach ($assessments as $assessment) {
        $assessment->strengths = $assessment->strength;  
        $assessment->weaknesses = $assessment->weakness;
    }

    return view('website.idp.index', compact('employees', 'assessments', 'alcs', 'programs', 'details', 'mid', 'idps'));
}


    public function store(Request $request)
    {
        // $request->validate([
        //     'development_program' => 'required|array',
        //     'category' => 'required|array',
        //     'development_target' => 'required',
        //     'date' => 'required',
        // ]);

        $assessment = Assessment::where('id', $request->assessment_id)->first();

        try {
            $idp = Idp::where('assessment_id', $request->assessment_id)
                        ->where('alc_id', $request->alc_id)
                        ->first();

            if ($idp) {
                $idp->update([
                    'development_program' => $request->development_program,
                    'category' => $request->category,
                    'development_target' => $request->development_target,
                    'date' => $request->date,
                ]);
                return redirect()->route('idp.index')->with('success', 'Development updated successfully.');
            } else {
                Idp::create([
                    'alc_id' => $request->alc_id,
                    'assessment_id' => $request->assessment_id,
                    'employee_id' => $assessment->employee_id,
                    'development_program' => $request->development_program,
                    'category' => $request->category,
                    'development_target' => $request->development_target,
                    'date' => $request->date,
                ]);
                return redirect()->route('idp.index')->with('success', 'Development added successfully.');
            }

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeMidYear(Request $request, $employee_id)
    {
        $request->validate([
            'development_program' => 'required|array',
            'development_achievement' => 'required|array',
            'next_action' => 'required|array',
        ]);

        foreach ($request->development_program as $key => $program) {
            Development::create([
                'employee_id' => $employee_id,
                'development_program' => $program,
                'development_achievement' => $request->development_achievement[$key] ?? '',
                'next_action' => $request->next_action[$key] ?? '',
            ]);
        }

        return redirect()->route('idp.index')->with('success', 'Mid-Year Development added successfully.');
    }

    public function storeOneYear(Request $request, $employee_id)
{
    $request->validate([
        'development_program' => 'required|array',
        'evaluation_result' => 'required|array',
    ]);

    foreach ($request->development_program as $key => $program) {
        DevelopmentOne::create([
            'employee_id' => $employee_id,
            'development_program' => $program,
            'evaluation_result' => $request->evaluation_result[$key] ?? '',
        ]);
    }

    return redirect()->route('idp.index')->with('success', 'One-Year Development added successfully.');
    }
    public function showDevelopmentData($employeeId)
    {
    $details = DevelopmentOne::where('employee_id', $employeeId)->get();
    return view('website.idp.index', compact('details'));
    }

    public function showDevelopmentMidData($employeeId)
    {
    $mid = Development::where('employee_id', operator: $employeeId)->get();
    return view('website.idp.index', compact('mid'));
    }

    public function exportTemplate($employee_id)
    {
        $filePath = 'public/templates/idp_template.xlsx';

        if (!Storage::exists($filePath)) {
            return back()->with('error', 'File template tidak ditemukan.');
        }

        $employee = Employee::find($employee_id);
        if (!$employee) {
            return back()->with('error', 'Employee tidak ditemukan.');
        }

        $fileName = 'IDP_' . str_replace(' ', '_', $employee->name) . '.xlsx';

        return Storage::download($filePath, $fileName);
    }

    public function getData(Request $request)
    {
        $assessmentId = $request->input('assessment_id');
        $alcId = $request->input('alc_id');

        $idp = DB::table('idp')
            ->where('assessment_id', $assessmentId)
            ->where('alc_id', $alcId)
            ->select('id', 'category', 'development_program', 'development_target', 'date')
            ->first();

        return response()->json(['idp' => $idp]);
    }
}
