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
use App\Models\IDPEntry;

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

        // Ambil data assessment beserta employee
        $assessments = Assessment::with(['employee', 'details'])->paginate(10);
        $employees = Employee::all();

        // Ambil data development program berdasarkan jenis review

        $programs = ['Superior (DGM & GM) + DIC PUR + BOD Member', 'Book Reading / Journal Business and BEST PRACTICES (Asia Pasific Region)',
            'To find "FIGURE LEADER" with Strong in Drive and Courage in Their Team --> Sharing Success Tips ', 'Team Leader of TASK FORCE with MULTY FUNCTION --> (AII) HYBRID DUMPER Project  (CAPACITY UP) & (AIIA) EV Project  ',
            'SR Project (Structural Reform -->DM & SCM) ', 'PEOPLE Development Program of Team members (ICT, IDP)', '(Leadership) --> Courageously & Situational Leadership ', '(Developing Sub Ordinate) --> Coaching Skill / Developing Talents'];

        $details = DevelopmentOne::all();

        $mid = Development::all();

        return view('website.idp.index', compact('employees', 'assessments', 'alcs', 'programs', 'details', 'mid'));
    }

    public function store(Request $request)
{
    $request->validate([
        'development_program' => 'required|array',
        'development_achievement' => 'required|array',
        'next_action' => 'required|array',
    ]);

    foreach ($request->development_program as $key => $program) {
        Development::create([
            'development_program' => $program,
            'development_achievement' => $request->development_achievement[$key] ?? '',
            'next_action' => $request->next_action[$key] ?? '',
        ]);
    }

    return redirect()->route('idp.index')->with('success', 'Mid-Year Development added successfully.');
}

// Simpan data One-Year Development
public function storeOneYear(Request $request)
{
    $request->validate([
        'development_program' => 'required|array',
        'evaluation_result' => 'required|array',
    ]);

    foreach ($request->development_program as $key => $program) {
        DevelopmentOne::create([
            'development_program' => $program,
            'evaluation_result' => $request->evaluation_result[$key] ?? '',
        ]);
    }

    return redirect()->route('idp.index')->with('success', 'One-Year Development added successfully.');
}


public function showDevelopmentData()
{
    // Ambil data berdasarkan employee_id
    return view('website.idp.index', compact('details'));
}

public function showDevelopmentMidData()
{
    // Ambil data berdasarkan employee_id
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
}
