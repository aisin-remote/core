<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Alc;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\SubSection;
use Illuminate\Support\Str;
use App\Imports\MasterImports;
use App\Models\DetailAssessment;
use App\Imports\AssessmentImport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;

class AssessmentController extends Controller
{
    private $allPositions = [
        'President',
        'VPD',
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

    /**
     * Display assessment index page
     */
    public function index(Request $request, $company = null)
    {
        $user = auth()->user();
        $title = 'Assessment';

        $currentPosition = $this->getCurrentPosition($user->employee->position);
        $visiblePositions = $this->getVisiblePositions($currentPosition);

        $filter = $request->input('filter');
        $search = $request->input('search');

        if ($this->isHRDorPresident($user)) {
            $employees = $this->getAllEmployees($company, $visiblePositions, $filter, $search);
        } else {
            $employees = $this->getSubordinateEmployees($user, $filter, $search);
        }

        $departments = Department::pluck('name');
        $assessments = $this->getAssessmentsForSubordinates($employees, $request);
        $employeesWithAssessments = $employees->filter(fn($emp) => $emp->assessments()->exists());
        $alcs = Alc::all();

        return view('website.assessment.index', compact(
            'assessments',
            'employees',
            'alcs',
            'employeesWithAssessments',
            'title',
            'company',
            'departments',
            'filter',
            'search',
            'visiblePositions'
        ));
    }

    /**
     * Show assessment details for an employee
     */
    public function show($employee_id)
    {
        $employee = Employee::with('assessments')->find($employee_id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $assessments = Assessment::where('employee_id', $employee_id)
            ->orderBy('date', 'desc')
            ->with([
                'details' => function ($query) {
                    $query->select('assessment_id', 'alc_id', 'score', 'strength', 'weakness', 'suggestion_development')
                        ->with(['alc:id,name']);
                }
            ])
            ->get();

        return response()->json([
            'employee' => $employee,
            'assessments' => $assessments
        ]);
    }

    /**
     * Show assessment details by date
     */
    public function showByDate($assessment_id, $date)
    {
        $assessment = Assessment::findOrFail($assessment_id);
        $employee = Employee::with(
            'departments',
            'subSection.section.department',
            'leadingSection.department',
            'leadingDepartment.division'
        )->findOrFail($assessment->employee_id);

        $assessments = DetailAssessment::with('alc')
            ->where('assessment_id', $assessment_id)
            ->get();

        if ($assessments->isEmpty()) {
            return back()->with('error', 'Tidak ada data assessment pada tanggal tersebut.');
        }

        $details = DB::table('detail_assessments')
            ->join('alc', 'detail_assessments.alc_id', '=', 'alc.id')
            ->where('detail_assessments.assessment_id', $assessment_id)
            ->select(
                'detail_assessments.*',
                'alc.name as alc_name',
                'detail_assessments.score'
            )
            ->get();

        return view('website.assessment.detail', compact('employee', 'assessments', 'date', 'details'));
    }

    /**
     * Get assessment detail for an employee
     */
    public function getAssessmentDetail($employee_id)
    {
        $assessment = Assessment::where('employee_id', $employee_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$assessment) {
            return response()->json(['error' => 'Data assessment tidak ditemukan'], 404);
        }

        $employee = Employee::findOrFail($employee_id);
        $details = DetailAssessment::with('alc')
            ->where('assessment_id', $assessment->id)
            ->get();

        $strengths = $details->filter(fn($d) => !empty($d->strength))->values();
        $weaknesses = $details->filter(fn($d) => !empty($d->weakness))->values();

        return response()->json([
            'employee' => $employee,
            'assessment' => $assessment,
            'date' => $assessment->date,
            'details' => $details,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
        ]);
    }

    /**
     * Show create assessment form
     */
    public function create()
    {
        $employees = Employee::all();
        $alcs = Alc::all();
        return view('assessments.create', compact('employees', 'alcs'));
    }

    /**
     * Store new assessment
     */
    public function store(Request $request)
    {
        $validated = $this->validateAssessmentRequest($request);
        $filePath = $this->handleFileUpload($request);

        $assessment = Assessment::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'target_position' => $request->target,
            'upload' => $filePath,
            'note' => $request->note,
        ]);

        $this->storeAssessmentDetails($request, $assessment);
        $this->processHav($request, $assessment);

        return response()->json([
            'success' => true,
            'message' => 'Data assessment dan HAV berhasil disimpan.',
            'assessment' => $assessment,
        ]);
    }

    /**
     * Edit assessment form
     */
    public function edit($id)
    {
        try {
            $assessment = Assessment::with('details.alc')->findOrFail($id);
            return response()->json([
                'id' => $assessment->id,
                'employee_id' => $assessment->employee_id,
                'date' => $assessment->date,
                'description' => $assessment->description,
                'upload' => $assessment->upload ? asset('storage/' . $assessment->upload) : null,
                'details' => $assessment->details->map(fn($d) => [
                    'alc_id' => $d->alc_id,
                    'score' => $d->score,
                    'strength' => $d->strength,
                    'weakness' => $d->weakness,
                    'suggestion_development' => $d->suggestion_development,
                    'alc' => $d->alc
                ]),
                'alc_options' => Alc::select('id', 'name')->get()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'messasge' => 'Assesment tidak ditemukan.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update assessment
     */
    public function update(Request $request)
    {
        $validated = $this->validateAssessmentRequest($request, true);
        $assessment = Assessment::findOrFail($request->assessment_id);

        // cek apakah ini assessment terbaru dari employee
        $isLatestAssessment = $this->isLatestAssessment($assessment);

        $this->updateAssessment($assessment, $request);
        $this->updateAssessmentDetails($assessment, $request);

        if ($isLatestAssessment) {
            $this->processHavUpdate($request, $assessment);
        }

        return response()->json([
            'message' => 'Assessment updated successfully',
            'hav_updated' => $isLatestAssessment
        ]);
    }

    /**
     * Delete assessment
     */
    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);
        DetailAssessment::where('assessment_id', $id)->delete();

        if ($assessment->upload) {
            Storage::delete('public/' . $assessment->upload);
        }

        $assessment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assessment berhasil dihapus.'
        ]);
    }

    /********************
     * PRIVATE METHODS *
     ********************/
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
            return Employee::whereRaw('1=0');
        }

        return Employee::whereIn('id', $subordinateIds)->get();
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

    private function getAssessmentsForSubordinates($employees, $request)
    {
        return Assessment::with(['employee', 'alc', 'employee.leadingDepartment', 'employee.subSection.section.department', 'employee.leadingSection.department', 'employee.leadingDepartment.division'])
            ->whereHas('employee', function ($query) use ($employees) {
                return $query->whereIn('id', $employees->pluck('id'));
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('npk', 'like', '%' . $request->search . '%');
                });
            })
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('assessments')
                    ->groupBy('employee_id');
            })
            ->paginate(10);
    }

    private function getCurrentPosition($rawPosition)
    {
        return Str::contains($rawPosition, 'Act ')
            ? trim(str_replace('Act', '', $rawPosition))
            : $rawPosition;
    }

    private function getVisiblePositions($currentPosition)
    {
        $positionIndex = array_search($currentPosition, $this->allPositions);
        if ($positionIndex === false) {
            $positionIndex = array_search('Operator', $this->allPositions);
        }

        return $positionIndex !== false
            ? array_slice($this->allPositions, $positionIndex)
            : [];
    }

    private function isHRDorPresident($user)
    {
        return $user->role === 'HRD' ||
            $user->employee->position == 'President' ||
            $user->employee->position == 'VPD';
    }

    private function getAllEmployees($company, $visiblePositions, $filter, $search)
    {
        return Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
            ->when($company, fn($query) => $query->where('company_name', $company))
            ->where(function ($q) use ($visiblePositions) {
                foreach ($visiblePositions as $pos) {
                    $q->orWhere('position', $pos)
                        ->orWhere('position', 'like', "Act %{$pos}");
                }
            })
            ->when($filter && $filter != 'all', function ($query) use ($filter) {
                $query->where(function ($q) use ($filter) {
                    $q->where('position', $filter)
                        ->orWhere('position', 'like', "Act %{$filter}");
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('npk', 'like', '%' . $search . '%');
                });
            })
            ->get();
    }

    private function getSubordinateEmployees($user, $filter, $search)
    {
        $employee = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
            ->where('user_id', $user->id)
            ->first();

        if (!$employee) {
            return collect();
        }

        $subordinates = $this->getSubordinatesFromStructure($employee);
        $employees = collect([$employee])->merge($subordinates)->unique('id');

        if ($filter && $filter !== 'all') {
            $employees = $employees->filter(function ($emp) use ($filter) {
                return $emp->position === $filter || str_starts_with($emp->position, "Act {$filter}");
            });
        }

        if ($search) {
            $employees = $employees->filter(function ($emp) use ($search) {
                return stripos($emp->name, $search) !== false || stripos($emp->npk, $search) !== false;
            });
        }

        return Employee::hydrate($employees->toArray());
    }

    private function validateAssessmentRequest(Request $request, $isUpdate = false)
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'target' => 'required|string',
            'upload' => 'nullable|file|mimes:pdf|max:2048',
            'note' => 'nullable|string',
            'description' => 'nullable|string',
            'alc_ids' => 'required|array',
            'alc_ids.*' => 'exists:alc,id',
            'scores' => 'nullable|array',
            'scores.*' => 'nullable|string|max:2',
            'strength' => 'nullable|array',
            'weakness' => 'nullable|array',
            'suggestion_development' => 'nullable|array',
        ];

        if ($isUpdate) {
            $rules['assessment_id'] = 'required|exists:assessments,id';
            $rules['employee_id'] = 'nullable|exists:employees,id';
            $rules['target'] = 'nullable|string';
        }

        return $request->validate($rules);
    }

    private function handleFileUpload(Request $request, ?string $oldFilePath = null)
    {
        // Jika tidak ada file baru diupload
        if (!$request->hasFile('upload')) {
            return $oldFilePath; // Kembalikan path lama
        }
        // Validasi file
        $request->validate([
            'upload' => 'file|mimes:pdf|max:2048', // PDF maksimal 2MB
        ]);

        // Hapus file lama jika ada
        if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
            Storage::disk('public')->delete($oldFilePath);
        }

        // Simpan file baru
        $filePath = 'uploads/assessments/' . $request->file('upload')->hashName();
        $request->file('upload')->storeAs('public', $filePath);

        return $filePath;
    }

    private function storeAssessmentDetails(Request $request, Assessment $assessment)
    {
        foreach ($request->alc_ids as $index => $alc_id) {
            DB::table('detail_assessments')->updateOrInsert(
                [
                    'assessment_id' => $assessment->id,
                    'alc_id' => $alc_id
                ],
                [
                    'score' => $request->scores[$alc_id] ?? "0",
                    'strength' => $request->strength[$alc_id] ?? "",
                    'weakness' => $request->weakness[$alc_id] ?? "",
                    'suggestion_development' => $request->suggestion_development[$alc_id] ?? "",
                    'created_at' => now(),
                ]
            );
        }
    }

    private function processHav(Request $request, Assessment $assessment)
    {
        $latestHav = DB::table('havs')
            ->where('employee_id', $request->employee_id)
            ->latest('created_at')
            ->first();

        $havId = DB::table('havs')->insertGetId([
            'employee_id' => $request->employee_id,
            'year' => now()->year,
            'quadrant' => $latestHav->quadrant ?? null,
            'status' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $templatePath = public_path('assets/file/Import-HAV.xls');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $employee = Employee::with('departments')->findOrFail($request->employee_id);
        $this->fillEmployeeData($sheet, $employee);

        $alcMapping = $this->getAlcMapping();
        $suggestionMapping = $this->getSuggestionMapping();

        foreach ($request->alc_ids as $alc_id) {
            $score = $request->scores[$alc_id] ?? "0";
        }

        foreach ($request->alc_ids as $alc_id) {
            $score = $request->scores[$alc_id] ?? "0";
            $suggestion = $request->suggestion_development[$alc_id] ?? "";

            $this->fillScoreData($sheet, $alcMapping, $alc_id, $score);
            $this->fillSuggestionData($sheet, $suggestionMapping, $alc_id, $suggestion);
        }

        $relativePath = $this->saveHavFile($spreadsheet);
        $this->storeHavCommentHistory($havId, $request->employee_id, $relativePath);

        $this->importHav($relativePath, $havId, $request);
    }

    private function fillEmployeeData($sheet, $employee)
    {
        $sheet->setCellValue("C6", $employee->name);
        $sheet->setCellValue("C7", $employee->npk);
        $sheet->setCellValue("C8", $employee->grade);
        $sheet->setCellValue("C9", $employee->company_name);
        $sheet->setCellValue("C10", $employee->department->name ?? '');
        $sheet->setCellValue("C11", $employee->position);
        $sheet->setCellValue("C13", date('Y'));
    }

    private function getAlcMapping()
    {
        return [
            1 => ['D', [17, 18, 19]],
            2 => ['I', [17, 18, 19, 20, 21, 22, 23]],
            3 => ['O', [17, 18, 19, 20]],
            4 => ['T', [17, 18, 19, 20]],
            5 => ['D', [27, 28, 29, 30, 31]],
            6 => ['I', [27, 28, 29, 30, 31, 32, 33, 34, 35, 36]],
            7 => ['O', [27, 28, 29, 30, 31, 32, 33]],
            8 => ['T', [27, 28, 29, 30, 31, 32, 33]],
        ];
    }

    private function getSuggestionMapping()
    {
        return [
            1 => ['F', [17, 18, 19]],
            2 => ['K', [17, 18, 19, 20, 21, 22, 23]],
            3 => ['Q', [17, 18, 19, 20]],
            4 => ['V', [17, 18, 19, 20]],
            5 => ['F', [27, 28, 29, 30, 31]],
            6 => ['K', [27, 28, 29, 30, 31, 32, 33, 34, 35, 36]],
            7 => ['Q', [27, 28, 29, 30, 31, 32, 33]],
            8 => ['V', [27, 28, 29, 30, 31, 32, 33]],
        ];


        foreach ($request->alc_ids as $alc_id) {
            $score = $request->scores[$alc_id] ?? "0";
            dd($score);
        }
    }

    private function fillScoreData($sheet, $alcMapping, $alc_id, $score)
    {
        if (isset($alcMapping[$alc_id])) {
            [$col, $rows] = $alcMapping[$alc_id];
            foreach ($rows as $row) {
                $sheet->setCellValue("{$col}{$row}", $score);
            }
        }
    }

    private function fillSuggestionData($sheet, $suggestionMapping, $alc_id, $suggestion)
    {
        if (isset($suggestionMapping[$alc_id])) {
            [$sugCol, $sugRows] = $suggestionMapping[$alc_id];
            $mergedRow = $sugRows[0];
            $sheet->mergeCells("{$sugCol}{$sugRows[0]}:{$sugCol}" . end($sugRows));
            $sheet->setCellValue("{$sugCol}{$mergedRow}", $suggestion);
            $sheet->getStyle("{$sugCol}{$mergedRow}")->getAlignment()->setWrapText(true);
        }
    }

    private function saveHavFile($spreadsheet)
    {
        $directory = 'hav_uploads';
        $fileName = 'hav_' . now()->timestamp . '.xlsx';
        $relativePath = "{$directory}/{$fileName}";
        $fullPath = storage_path("app/public/{$relativePath}");

        Storage::disk('public')->makeDirectory($directory);
        $tempPath = storage_path("app/temp_{$fileName}");
        (new Xlsx($spreadsheet))->save($tempPath);

        try {
            Storage::disk('public')->put($relativePath, file_get_contents($tempPath));
        } catch (\Throwable $e) {
            Log::error('Gagal memindahkan file ke disk public', [
                'file' => $fileName,
                'temp_path' => $tempPath,
                'exception' => $e,
            ]);

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            throw $e;
        }

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        return $relativePath;
    }

    private function storeHavCommentHistory($havId, $employeeId, $relativePath)
    {
        DB::table('hav_comment_histories')->insert([
            'hav_id' => $havId,
            'employee_id' => $employeeId,
            'upload' => $relativePath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function importHav($relativePath, $havId, $request)
    {
        $fullPath = storage_path("app/public/{$relativePath}");
        $detailData = [];

        foreach ($request->alc_ids as $alc_id) {
            $detailData[$alc_id] = [
                'score' => $request->scores[$alc_id] ?? "0",
                'strength' => $request->strength[$alc_id] ?? "",
                'weakness' => $request->weakness[$alc_id] ?? "",
                'suggestion_development' => $request->suggestion_development[$alc_id] ?? "",
            ];
        }

        try {
            Excel::import(
                new AssessmentImport($relativePath, $havId, $detailData, false),
                $fullPath
            );
        } catch (\Throwable $e) {
            Log::error('Gagal import HAV', [
                'file' => $relativePath,
                'hav_id' => $havId,
                'path' => $fullPath,
                'exception' => $e,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function updateAssessment($assessment, $request)
    {
        $assessment->date = $request->date;
        $assessment->description = $request->description;
        $assessment->note = $request->note;

        if ($request->hasFile('upload')) {
            $path = $this->handleFileUpload($request, $assessment->upload);
            $assessment->upload = $path;
        }

        $assessment->save();
    }

    private function updateAssessmentDetails($assessment, $request)
    {
        DetailAssessment::where('assessment_id', $assessment->id)->delete();

        foreach ($request->scores as $alc_id => $score) {
            DetailAssessment::create([
                'assessment_id' => $assessment->id,
                'alc_id' => $alc_id,
                'score' => $score,
                'strength' => $request->strength[$alc_id] ?? null,
                'weakness' => $request->weakness[$alc_id] ?? null,
                'suggestion_development' => $request->suggestion_development[$alc_id] ?? null
            ]);
        }
    }


    /************
     *  FULL FUNCTION UPDATE ASSESSMENT
     */

    private function isLatestAssessment(Assessment $assessment)
    {
        $latestAssessment = Assessment::where('employee_id', $assessment->employee_id)
            ->orderBy('date', 'desc')
            ->first();

        return $latestAssessment && $latestAssessment->id === $assessment->id;
    }

    private function processHavUpdate(Request $request, Assessment $assessment)
    {
        // Cari HAV terbaru untuk employee dan assessment ini
        $latestHav = DB::table('havs')
            ->where('employee_id', $assessment->employee_id)
            ->latest('created_at')
            ->first();

        if (!$latestHav) {
            return; //Tidak ada hav untuk diupdate
        }

        // Load template HAV
        $templatePath = public_path('assets/file/Import-HAV.xls');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Isi data employee
        $employee = Employee::with('departments')->findOrFail($assessment->employee_id);
        $this->fillEmployeeData($sheet, $employee);

        // Mapping data assessment ke HAV
        $alcMapping = $this->getAlcMapping();
        $suggestionMapping = $this->getSuggestionMapping();

        foreach ($request->alc_ids as $alc_id) {
            $score = $request->scores[$alc_id] ?? "0";
            $suggestion = $request->suggestion_development[$alc_id] ?? "";

            $this->fillScoreData($sheet, $alcMapping, $alc_id, $score);
            $this->fillSuggestionData($sheet, $suggestionMapping, $alc_id, $suggestion);
        }

        // Simpan file HAV baru
        $relativePath = $this->saveHavFile($spreadsheet);

        DB::table('hav_comment_histories')->insert([
            'hav_id' => $latestHav->id,
            'employee_id' => $assessment->employee_id,
            'upload' => $relativePath,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Jalankan import HAV
        $this->importHavForUpdate($relativePath, $latestHav->id, $request);
    }

    private function importHavForUpdate($relativePath, $havId, $request)
    {
        $fullPath = storage_path("app/public/{$relativePath}");
        $detailData = [];

        foreach ($request->alc_ids as $alc_id) {
            $detailData[$alc_id] = [
                'score' => $request->score[$alc_id] ?? "0",
                'strength' => $request->strength[$alc_id] ?? "",
                'weakness' => $request->weakness[$alc_id] ?? "",
                "suggestion_development" => $request->suggestion_development[$alc_id] ?? ""
            ];
        }

        try {
            Excel::import(
                new AssessmentImport($relativePath, $havId, $detailData, true),
                $fullPath
            );
        } catch (\Throwable $e) {
            Log::error('Gagal import HAV untuk update', [
                'file' => $relativePath,
                'hav_id' => $havId,
                'exception' => $e->getMessage()
            ]);
            // Tidak perlu throw error, karena ini hanya update tambahan
        }
    }
}
