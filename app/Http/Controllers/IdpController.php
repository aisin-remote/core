<?php

namespace App\Http\Controllers;

use App\Models\Idp;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\Development;
use Illuminate\Http\Request;
use App\Models\DevelopmentOne;
use App\Models\DetailAssessment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IdpController extends Controller
{
    public function getSubordinates($employeeId, $processedIds = [])
    {
        // Cegah infinite loop dengan memeriksa apakah ID sudah diproses sebelumnya
        if (in_array($employeeId, $processedIds)) {
            return collect(); // Kembalikan collection kosong untuk menghindari loop
        }

        // Tambahkan ID saat ini ke daftar yang sudah diproses
        $processedIds[] = $employeeId;

        // Ambil hanya bawahan langsung (bukan atasan)
        $employees = Employee::where('supervisor_id', $employeeId)->get();
        $subordinates = collect($employees);

        // Lanjutkan rekursi untuk mendapatkan semua bawahan di level lebih dalam
        foreach ($employees as $employee) {
            $subordinates = $subordinates->merge($this->getSubordinates($employee->id, $processedIds));
        }

        return $subordinates;
    }
    public function index( $company = null, $reviewType = 'mid_year')
    {
        $user = auth()->user();
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

        
        // Ambil assessment dengan hubungan ke employee, dan filter berdasarkan company jika diberikan
        if ($user->role === 'HRD') {
            $assessments = Assessment::with(['employee', 'details', 'idp'])
                ->when($company, fn($query) => $query->whereHas('employee', fn($q) => $q->where('company_name', $company)))
                ->paginate(10);
        }else{
            // Ambil employee berdasarkan user login
            $emp = Employee::where('user_id', $user->id)->first();
            if (!$emp) {
                $assessments = collect(); // Kosong jika tidak ada employee
            } else {
                // Ambil semua bawahan
                $subordinates = $this->getSubordinates($emp->id)->pluck('id')->toArray();

                // Ambil assessment hanya milik bawahannya
                $assessments = Assessment::with(['employee', 'details', 'idp'])
                    ->whereIn('employee_id', $subordinates)
                    ->when($company, fn($query) => 
                        $query->whereHas('employee', fn($q) => $q->where('company_name', $company))
                    )
                    ->paginate(10);
            }
        }


        // Ambil semua karyawan
        $employees = Employee::all();

        // Ambil IDP
        $idps = Idp::with('assessment', 'employee')->get();

        // Daftar program
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

        // Proses assessment data
        foreach ($assessments as $assessment) {
            // Ambil IDP development program yang sudah tersimpan
            $savedPrograms = $assessment->idp->pluck('development_program')->toArray();
            $assessment->recommendedPrograms = $savedPrograms;

            // Tambahkan strengths & weaknesses
            $assessment->strengths = $assessment->strength;
            $assessment->weaknesses = $assessment->weakness;
        }

        return view('website.idp.index', compact(
            'employees',
            'assessments',
            'alcs',
            'programs',
            'details',
            'mid',
            'idps',
            'company'
        ));
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
