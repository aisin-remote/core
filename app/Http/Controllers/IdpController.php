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
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
    public function index($company = null, $reviewType = 'mid_year')
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

    // Ambil assessment terbaru berdasarkan created_at
    if ($user->role === 'HRD') {
        $assessments = Assessment::whereIn('id', function($query) {
                $query->selectRaw('id')
                      ->from('assessments as a')
                      ->whereRaw('a.created_at = (SELECT MAX(created_at) FROM assessments WHERE employee_id = a.employee_id)');
            })
            ->with(['employee', 'details', 'idp'])
            ->orderByDesc('created_at')
            ->paginate(10);
    } else {
        // Ambil employee berdasarkan user login
        $emp = Employee::where('user_id', $user->id)->first();
        if (!$emp) {
            $assessments = collect(); // Kosong jika tidak ada employee
        } else {
            // Ambil semua bawahan
            $subordinates = $this->getSubordinates($emp->id)->pluck('id')->toArray();

            // Ambil assessment terbaru hanya milik bawahannya
            $assessments = Assessment::with(['employee', 'details', 'idp'])
                ->whereIn('employee_id', $subordinates)
                ->when($company, fn($query) =>
                    $query->whereHas('employee', fn($q) => $q->where('company_name', $company))
                )
                ->whereIn('id', function($query) {
                    $query->selectRaw('id')
                          ->from('assessments as a')
                          ->whereRaw('a.created_at = (SELECT MAX(created_at) FROM assessments WHERE employee_id = a.employee_id)');
                })
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

    foreach ($request->development_program as $empId => $programs) {
        if (!is_array($programs)) {
            continue;
        }

        foreach ($programs as $key => $program) {
            DevelopmentOne::create([
                'employee_id' => $employee_id,
                'development_program' => $program,
                'evaluation_result' => $request->evaluation_result[$empId][$key] ?? '',
            ]);
        }
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
    $filePath = storage_path('app/public/templates/idp_template.xlsx');

    if (!file_exists($filePath)) {
        return back()->with('error', 'File template tidak ditemukan.');
    }

    $employee = Employee::find($employee_id);
    if (!$employee) {
        return back()->with('error', 'Employee tidak ditemukan.');
    }

    $assessment = Assessment::where('employee_id', $employee_id)->latest()->first();
    if (!$assessment) {
        return back()->with('error', 'Assessment tidak ditemukan.');
    }


    $assessmentDetails = DB::table('detail_assessments')
    ->join('alc', 'detail_assessments.alc_id', '=', 'alc.id')
    ->select('detail_assessments.*', 'alc.name as alc_name')
    ->where('detail_assessments.assessment_id', $assessment->id)
    ->get();


    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('H3', $employee->name);
    $sheet->setCellValue('K3', $employee->npk);
    $sheet->setCellValue('R3', $employee->position);
    $sheet->setCellValue('R4', $employee->position);
    $sheet->setCellValue('R5', $employee->birthday_date);
    $sheet->setCellValue('R6', $employee->aisin_entry_date);
    $sheet->setCellValue('R7', $assessment->date);
    $sheet->setCellValue('H6', $employee->grade);
    $sheet->setCellValue('H5', $employee->department_id);


    $startRow = 13;

    $latestAssessment = DB::table('assessments')
    ->where('employee_id', $employee_id)
    ->latest('created_at')
    ->first();

if (!$latestAssessment) {
    return back()->with('error', 'Assessment tidak ditemukan untuk employee ini.');
}


$assessmentDetails = DB::table('detail_assessments')
    ->join('alc', 'detail_assessments.alc_id', '=', 'alc.id')
    ->where('detail_assessments.assessment_id', $latestAssessment->id)
    ->select('detail_assessments.*', 'alc.name as alc_name')
    ->get();

foreach ($assessmentDetails as $detail) {
    if (!empty($detail->strength)) {
        $sheet->setCellValue('B' . $startRow, $detail->alc_name . " - " . $detail->strength);
    }

    if (!empty($detail->weakness)) {
        $sheet->setCellValue('F' . $startRow, $detail->alc_name . " - " . $detail->weakness);
    }

    if (!empty($detail->strength) || !empty($detail->weakness)) {
        $startRow++;
    }
}

    $startRow = 33;

    $assessment_id = $request->assessment_id ?? Assessment::where('employee_id', $employee_id)->latest()->value('id');

    if (!$assessment_id) {
        return back()->with('error', 'Assessment ID tidak ditemukan.');
    }

    $assessmentDetails = DB::table('detail_assessments')
    ->join('alc', 'detail_assessments.alc_id', '=', 'alc.id')
    ->where('detail_assessments.assessment_id', $latestAssessment->id)
    ->select('detail_assessments.*', 'alc.name as alc_name')
    ->get();

    foreach ($assessmentDetails as $detail) {
        if (!empty($detail->weakness)) {
            $sheet->setCellValue('C' . $startRow, $detail->alc_name . " - " . $detail->weakness);
        }

        if (!empty($detail->weakness)) {
            $startRow += 2;
        }
    }

    $startRow = 33;

    $assessment_id = $request->assessment_id ?? Assessment::where('employee_id', $employee_id)->latest()->value('id');

    if (!$assessment_id) {
        return back()->with('error', 'Assessment ID tidak ditemukan.');
    }


    $idpRecords = Idp::where('assessment_id', $assessment_id)->get();

    foreach ($idpRecords as $idp) {
        $sheet->setCellValue('C' . $startRow, $detail->alc_name . " - " . ($detail->weakness ?? "-"));
        $sheet->setCellValue('E' . $startRow, $idp->development_program ?? "-");
        $sheet->setCellValue('D' . $startRow, $idp->category ?? "-");
        $sheet->setCellValue('H' . $startRow, $idp->development_target ?? "-");
        $sheet->setCellValue('K' . $startRow, $idp->date ?? "-");

        $startRow += 2;
    }

    $startRow = 13;

    $assessment_id = $request->assessment_id ?? Assessment::where('employee_id', $employee_id)->latest()->value('id');

    if (!$assessment_id) {
        return back()->with('error', 'Assessment ID tidak ditemukan.');
    }

    $midYearRecords = Development::where('employee_id', $employee_id)->get();

    foreach ($midYearRecords as $record) {
        $sheet->setCellValue('O' . $startRow, $record->development_program ?? "-");
        $sheet->setCellValue('R' . $startRow, $record->development_achievement ?? "-");
        $sheet->setCellValue('U' . $startRow, $record->next_action ?? "-");

        $startRow++;
    }

    $startRow = 33;

    $assessment_id = $request->assessment_id ?? Assessment::where('employee_id', $employee_id)->latest()->value('id');

    if (!$assessment_id) {
        return back()->with('error', 'Assessment ID tidak ditemukan.');
    }

    $oneYearRecords = DevelopmentOne::where('employee_id', $employee_id)->get();

    foreach ($oneYearRecords as $record) {
        $sheet->setCellValue('O' . $startRow, $record->development_program ?? "-");
        $sheet->setCellValue('R' . $startRow, $record->evaluation_result ?? "-");

        $startRow += 2;
    }


    $tempDir = storage_path('app/public/temp');
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

    // Simpan file sementara
    $fileName = 'IDP_' . str_replace(' ', '_', $employee->name) . '.xlsx';
    $tempPath = storage_path('app/public/temp/' . $fileName);
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($tempPath);

    // Download file
    return response()->download($tempPath)->deleteFileAfterSend(true);
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
