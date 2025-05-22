<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Idp;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\SubSection;
use App\Models\Development;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DevelopmentOne;
use App\Models\DetailAssessment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IdpController extends Controller
{

    private function getSubordinatesFromStructure(Employee $employee)
    {
        $subordinateIds = collect();

        if ($employee->leadingPlant && $employee->leadingPlant->director_id === $employee->id) {
            $divisions = Division::where('plant_id', $employee->leadingPlant->id)->get();
            $subordinateIds = $this->collectSubordinates($divisions, 'gm_id', $subordinateIds);

            $departments = Department::whereIn('division_id', $divisions->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($departments, 'manager_id', $subordinateIds);

            $sections = Section::whereIn('department_id', $departments->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingDivision && $employee->leadingDivision->gm_id === $employee->id) {
            $departments = Department::where('division_id', $employee->leadingDivision->id)->get();
            $subordinateIds = $this->collectSubordinates($departments, 'manager_id', $subordinateIds);

            $sections = Section::whereIn('department_id', $departments->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingDepartment && $employee->leadingDepartment->manager_id === $employee->id) {
            $sections = Section::where('department_id', $employee->leadingDepartment->id)->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingSection && $employee->leadingSection->supervisor_id === $employee->id) {
            $subSections = SubSection::where('section_id', $employee->leadingSection->id)->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->subSection && $employee->subSection->leader_id === $employee->id) {
            $employeesInSameSubSection = Employee::where('sub_section_id', $employee->sub_section_id)
                ->where('id', '!=', $employee->id)
                ->pluck('id');

            $subordinateIds = $subordinateIds->merge($employeesInSameSubSection);
        }

        if ($subordinateIds->isEmpty()) {
            return Employee::whereRaw('1=0'); // tidak ada bawahan
        }

        return Employee::whereIn('id', $subordinateIds);
    }

    private function collectSubordinates($models, $field, $subordinateIds)
    {
        $ids = $models->pluck($field)->filter();
        return $subordinateIds->merge($ids);
    }

    private function collectOperators($subSections, $subordinateIds)
    {
        $subSectionIds = $subSections->pluck('id');
        $operatorIds = Employee::whereIn('sub_section_id', $subSectionIds)->pluck('id');
        return $subordinateIds->merge($operatorIds);
    }

    public function index(Request $request, $company = null, $reviewType = 'mid_year')
    {
        $user = auth()->user();
        $employee = $user->employee;
        $npk = $request->query('npk');
        $search = $request->query('search');
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
            $assessments = Assessment::whereIn('id', function ($query) {
                $query->selectRaw('id')
                    ->from('assessments as a')
                    ->whereRaw('a.created_at = (SELECT MAX(created_at) FROM assessments WHERE employee_id = a.employee_id)');
            })
                ->with(['employee', 'details', 'idp'])
                ->when(
                    $company,
                    fn($query) =>
                    $query->whereHas('employee', fn($q) => $q->where('company_name', $company))
                )
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('employee', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('npk', 'like', '%' . $search . '%');
                    });
                })
                ->orderByDesc('created_at')
                ->paginate(10);
        } else {
            // Ambil employee berdasarkan user login
            $emp = Employee::where('user_id', $user->id)->first();


            if (!$emp) {
                $assessments = collect(); // Kosong jika tidak ada employee
            } else {

                // Ambil bawahan menggunakan fungsi getSubordinatesFromStructure
                $viewLevel = $emp->getCreateAuth();
                $subordinates = $emp->getSubordinatesByLevel($viewLevel)->pluck('id')->toArray();

                // Ambil assessment terbaru hanya milik bawahannya
                $assessments = Assessment::with(['employee', 'details', 'idp'])
                    ->whereIn('employee_id', $subordinates)
                    ->when(
                        $company,
                        fn($query) =>
                        $query->whereHas('employee', fn($q) => $q->where('company_name', $company))
                    )
                    ->when($search, function ($query) use ($search) {
                        $query->whereHas('employee', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%')
                                ->orWhere('npk', 'like', '%' . $search . '%');
                        });
                    })
                    ->whereIn('id', function ($query) {
                        $query->selectRaw('id')
                            ->from('assessments as a')
                            ->whereRaw('a.created_at = (SELECT MAX(created_at) FROM assessments WHERE employee_id = a.employee_id)');
                    })
                    ->get();
            }
        }


        // Ambil semua karyawan
        $employees = Employee::all();

        // Ambil IDP
        $idps = Idp::with('assessment', 'employee', 'commentHistory')->get();

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

        foreach ($assessments as $assessment) {
            // Ambil semua program IDP yang tersimpan
            $savedPrograms = $assessment->idp->map(function ($idp) {
                return [
                    'program' => $idp->development_program,
                    'date' => $idp->date, // Gantilah 'due_date' menjadi 'date' sesuai dengan database
                ];
            });

            // Pisahkan berdasarkan due date
            $midYearPrograms = [];
            $oneYearPrograms = [];
            $currentDate = Carbon::now();

            foreach ($savedPrograms as $program) {
                $dueDate = Carbon::parse($program['date']); // Menggunakan 'date' dari database
                $midYearPrograms[] = $program;
                $oneYearPrograms[] = $program;
            }

            // Simpan ke objek assessment agar bisa diakses di Blade
            $assessment->recommendedProgramsMidYear = $midYearPrograms;
            $assessment->recommendedProgramsOneYear = $oneYearPrograms;

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
            'company',
        ));
    }

    public function list(Request $request, $company = null, $reviewType = 'mid_year')
    {
        $user = auth()->user();
        $search = $request->query('search');
        $npk = $request->query('npk');
        $filter = $request->query('filter', 'all');

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

        $assessments = collect(); // default kosong

        if ($user->role === 'HRD') {
            $assessments = Assessment::whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('assessments as a')
                    ->groupBy('a.employee_id');
            })
                ->with(['employee', 'details', 'idp'])
                ->when(
                    $company,
                    fn($q) =>
                    $q->whereHas('employee', fn($e) => $e->where('company_name', $company))
                )
                ->when(
                    $search,
                    fn($q) =>
                    $q->whereHas('employee', function ($e) use ($search) {
                        $e->where('name', 'like', "%$search%")
                            ->orWhere('npk', 'like', "%$search%");
                    })
                )
                ->orderByDesc('created_at')
                ->paginate(10);
        } else {
            $emp = Employee::with([
                'subSection.section.department.division.plant',
                'leadingSection.department.division.plant',
                'leadingDepartment.division.plant',
                'leadingPlant'
            ])->where('user_id', $user->id)->first();

            if ($emp) {
                $subordinateIds = $this->getSubordinatesFromStructure($emp)->pluck('id');

                $latestAssessmentIds = Assessment::selectRaw('MAX(id) as id')
                    ->whereIn('employee_id', $subordinateIds)
                    ->groupBy('employee_id')
                    ->pluck('id');

                $assessments = Assessment::with(['employee', 'details', 'idp'])
                    ->whereIn('id', $latestAssessmentIds)
                    ->when(
                        $company,
                        fn($q) =>
                        $q->whereHas(
                            'employee',
                            fn($e) =>
                            $e->where('company_name', $company)
                        )
                    )
                    ->whereHas('employee', function ($query) use ($npk, $filter, $search) {
                        if ($npk) {
                            $query->where('npk', $npk);
                        }

                        if ($filter && $filter !== 'all') {
                            $query->where(function ($q) use ($filter) {
                                $q->where('position', $filter)
                                    ->orWhere('position', 'like', "Act %{$filter}");
                            });
                        }

                        if ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        }
                    })
                    ->get();
            }
        }

        // Data tambahan
        $employees = Employee::all();
        $idps = Idp::with(['assessment', 'employee', 'commentHistory'])->get();
        $details = DevelopmentOne::all();
        $mid = Development::all();

        $allPositions = [
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

        $rawPosition = $user->employee->position ?? 'Operator';
        $currentPosition = Str::contains($rawPosition, 'Act ')
            ? trim(str_replace('Act', '', $rawPosition))
            : $rawPosition;

        $positionIndex = array_search($currentPosition, $allPositions);
        if ($positionIndex === false) {
            $positionIndex = array_search('Operator', $allPositions);
        }

        $visiblePositions = $positionIndex !== false
            ? array_slice($allPositions, $positionIndex)
            : [];

        // Static program list


        return view('website.idp.list', compact(
            'employees',
            'assessments',
            'alcs',
            'visiblePositions',
            'filter',
            'details',
            'mid',
            'idps',
            'company'
        ));
    }
    public function show($employee_id)
    {
        $employee = Employee::with('assessments')->find($employee_id);

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        $assessments = Assessment::where('employee_id', $employee_id)
            ->select('id', 'date',  'description', 'employee_id', 'upload')
            ->orderBy('date', 'desc')
            ->with(['details' => function ($query) {
                $query->select('assessment_id', 'alc_id', 'score', 'strength', 'weakness','suggestion_development')
                    ->with(['alc:id,name']);
            }])
            ->get();

        return response()->json([
            'employee' => $employee,
            'assessments' => $assessments
        ]);
    }

    public function store(Request $request)
    {
        $assessment = Assessment::where('id', $request->assessment_id)->first();

        try {
            DB::beginTransaction();
            $idp = Idp::where('assessment_id', $request->assessment_id)
                ->where('alc_id', $request->alc_id)
                ->first();

            if ($idp) {
                $idp->update([
                    'development_program' => $request->development_program ?? $idp->development_program,
                    'category' => $request->category ?? $idp->category,
                    'development_target' => $request->development_target ?? $idp->development_target,
                    'date' => $request->date ?? $idp->date,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Development updated successfully.',
                    'idp' => $idp, // opsional: kirim data IDP terbaru
                ]);
            } else {
                $newIdp = Idp::create([
                    'alc_id' => $request->alc_id,
                    'assessment_id' => $request->assessment_id,
                    'employee_id' => $assessment->employee_id,
                    'development_program' => $request->development_program,
                    'category' => $request->category,
                    'development_target' => $request->development_target,
                    'status' => 0,
                    'date' => $request->date,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Development added successfully.',
                    'idp' => $newIdp, // opsional: kirim data IDP yang baru dibuat
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
        $filePath = public_path('assets/file/idp_template.xlsx');


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

        $strengths = [];
        $weaknesses = [];

        foreach ($assessmentDetails as $detail) {
            if (!empty($detail->strength)) {
                $strengths[] =  " - " . $detail->alc_name;
            }
            if (!empty($detail->weakness)) {
                $weaknesses[] = " - "  . $detail->alc_name;
            }
        }

        $strengthText = implode("\n", $strengths);
        $weaknessText = implode("\n", $weaknesses);

        $sheet->setCellValue('B' . $startRow, $strengthText);
        $sheet->setCellValue('F' . $startRow, $weaknessText);



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

        foreach ($assessmentDetails as $detail) {
            if (!empty($detail->weakness)) {
                $sheet->setCellValue('C' . $startRow, $detail->alc_name);
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

    public function sendIdpToSupervisor(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');

            if (!$employeeId) {
                return response()->json(['message' => 'Employee ID tidak valid.'], 400);
            }

            // Ambil semua detail assessment ALC untuk employee
            $detailAssessments = DetailAssessment::whereHas('assessment.idp', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId);
            })->whereHas('alc')->get();


            if ($detailAssessments->isEmpty()) {
                return response()->json(['message' => 'Fitur ini belum dijadwalkan untuk pengembangan.'], 400);
            }

            // Filter nilai < 3
            $belowThree = $detailAssessments->filter(function ($detail) {
                return $detail->score < 3;
            });

            if ($belowThree->isEmpty()) {
                return response()->json(['message' => 'Tidak ada ALC dengan nilai di bawah 3.'], 400);
            }

            // Pastikan semua ALC < 3 sudah dinilai (dihitung berdasarkan jumlah unik ALC)
            $alcIdsBelowThree = $belowThree->pluck('alc_id')->unique();

            $totalExpected = $alcIdsBelowThree->count();
            $totalActual = DetailAssessment::whereIn('alc_id', $alcIdsBelowThree)
                ->whereHas('assessment', function ($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId);
                })->count();

            if ($totalActual < $totalExpected) {
                return response()->json(['message' => 'Ada nilai ALC < 3 yang belum dibuat.'], 400);
            }

            // Update semua IDP milik employee menjadi status = 2
            IDP::with('assessment')
                ->whereHas('assessment', function ($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId);
                })
                ->update([
                    'status' => 1
                ]);

            return response()->json(['message' => 'IDP berhasil dikirim ke atasan dan status diperbarui.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengirim IDP. Silakan coba lagi.',
                'error' => $e->getMessage(), // Boleh dihapus di production
            ], 500);
        }
    }


    public function approval()
    {
        $user = auth()->user();
        $employee = $user->employee;

        // Ambil bawahan menggunakan fungsi getSubordinatesFromStructure
        $checkLevel = $employee->getFirstApproval();
        $subCheck = $employee->getSubordinatesByLevel($checkLevel)->pluck('id')->toArray();

        $approveLevel = $employee->getFirstApproval();
        $subApprove = $employee->getSubordinatesByLevel($approveLevel)->pluck('id')->toArray();

        $checkIdps = Idp::with('assessment.employee', 'assessment.details')
            ->where('status', 1)
            ->whereHas('assessment.employee', function ($q) use ($subCheck) {
                $q->whereIn('employee_id', $subCheck); // Menggunakan whereIn jika $subordinates adalah array
            })
            ->get();

        $approveIdps = Idp::with('assessment.employee', 'assessment.details')
            ->where('status', 2)
            ->whereHas('assessment.employee', function ($q) use ($subApprove) {
                $q->whereIn('employee_id', $subApprove); // Menggunakan whereIn jika $subordinates adalah array
            })
            ->get();

        $idps = $checkIdps->merge($approveIdps);

        return view('website.approval.idp.index', compact('idps'));
    }
    public function approve($id)
    {
        $idp = Idp::findOrFail($id);
        $idp->status = 2;
        $idp->save();

        return response()->json([
            'message' => 'Employee approved successfully.'
        ]);
    }

    public function revise(Request $request)
    {
        $idp = Idp::findOrFail($request->id);

        // Menyimpan status HAV sebagai disetujui
        $idp->status = 0; // Status disetujui

        // Ambil komentar dari input request
        $comment = $request->input('comment');
        $employee = auth()->user()->employee;
        // Menyimpan komentar ke dalam tabel hav_comment_history
        if ($employee) {
            $idp->commentHistory()->create([
                'comment' => $comment,
                'employee_id' =>  $employee->id  // Menyimpan siapa yang memberikan komentar
            ]);
        }

        // Simpan perubahan status HAV
        $idp->save();

        // Kembalikan respons JSON
        return response()->json(['message' => 'Data berhasil direvisi.']);
    }
}
