<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Hav;
use App\Models\Idp;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\HavDetail;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\SubSection;
use App\Models\Development;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DevelopmentOne;
use App\Models\DetailAssessment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\IdpBackup;
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

        // Get assessments based on user role
        $assessments = $this->getAssessments($user, $company, $npk, $search);

        // Process assessments data
        $processedData = $this->processAssessments($assessments, $alcs);

        // Get job positions for tabs
        $jobPositions = $this->getJobPositions($assessments);

        // Other data
        $employees = Employee::all();
        $idps = Idp::with('hav', 'employee', 'commentHistory')->get();
        $programs = $this->getDevelopmentPrograms();
        $details = DevelopmentOne::all();
        $mid = Development::all();

        return view('website.idp.index', compact(
            'employees',
            'processedData',
            'alcs',
            'programs',
            'details',
            'mid',
            'idps',
            'company',
            'jobPositions'
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

        $assessments = collect();

        if ($user->isHRDorDireksi()) {
            $assessments = Idp::with('hav.hav.employee', 'assessment.details')
                ->when($company, fn($q) => $q->whereHas('hav.hav.employee', fn($q) => $q->where('company_name', $company)))
                ->when($npk, fn($q) => $q->whereHas('hav.hav.employee', fn($q) => $q->where('npk', $npk)))
                ->when($search, fn($q) => $q->whereHas('hav.hav.employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('npk', 'like', "%$search%");
                }))
                ->when(
                    $filter !== 'all',
                    fn($q) =>
                    $q->whereHas(
                        'hav.hav.employee',
                        fn($q) =>
                        $q->where(function ($q) use ($filter) {
                            $q->where('position', $filter)
                                ->orWhere('position', 'like', "Act%$filter");
                        })
                    )
                )
                ->orderByDesc('created_at')
                ->get();

            $assessments = new \Illuminate\Pagination\LengthAwarePaginator(
                $assessments,
                $assessments->count(),
                10,
                $request->get('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $assessments->setCollection(
                $assessments->getCollection()->map(function ($idp) {
                    // Ambil employee IDP
                    $employee = optional(optional($idp->hav)->hav)->employee;

                    // Cari atasan
                    $creator = null;
                    $assignLevel = $employee->getCreateAuth();
                    $creator = $employee->getSuperiorsByLevel($assignLevel)->first();

                    $idp->created_by = $creator;
                    $idp->created_by_name = optional($creator)->name ?? null;
                    $idp->created_by_id = optional($creator)->id ?? null;

                    return $idp;
                })
            );
        } else {
            $emp = Employee::where('user_id', $user->id)->first();

            if ($emp) {
                $subordinates = $this->getSubordinatesFromStructure($emp)->pluck('id')->toArray();

                $assessments = Idp::with(['hav.hav.employee', 'developments', 'assessment.details'])
                    ->whereHas('hav.hav.employee', fn($q) => $q->whereIn('id', $subordinates))
                    ->when($company, fn($q) => $q->whereHas('hav.hav.employee', fn($q) => $q->where('company_name', $company)))
                    ->when($npk, fn($q) => $q->whereHas('hav.hav.employee', fn($q) => $q->where('npk', $npk)))
                    ->when(
                        $filter !== 'all',
                        fn($q) =>
                        $q->whereHas(
                            'hav.hav.employee',
                            fn($q) =>
                            $q->where(function ($q) use ($filter) {
                                $q->where('position', $filter)
                                    ->orWhere('position', 'like', "Act%$filter");
                            })
                        )
                    )

                    ->when($search, function ($q) use ($search) {
                        $q->whereHas('hav.hav.employee', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%")
                                ->orWhere('npk', 'like', "%$search%");
                        });
                    })
                    ->orderByDesc('created_at')
                    ->get();

                $assessments = new \Illuminate\Pagination\LengthAwarePaginator(
                    $assessments,
                    $assessments->count(),
                    10,
                    $request->get('page', 1),
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                $assessments->setCollection(
                    $assessments->getCollection()->map(function ($idp) {
                        // Ambil employee IDP
                        $employee = optional(optional($idp->hav)->hav)->employee;

                        // Cari atasan
                        $creator = null;
                        $assignLevel = $employee->getCreateAuth();
                        $creator = $employee->getSuperiorsByLevel($assignLevel)->first();

                        $idp->created_by = $creator;
                        $idp->created_by_name = optional($creator)->name ?? null;
                        $idp->created_by_id = optional($creator)->id ?? null;

                        return $idp;
                    })
                );
            }
        }

        $employees = Employee::all();
        $idps = Idp::with(['assessment', 'employee', 'commentHistory', 'developments'])->get();
        $details = DevelopmentOne::all();
        $mid = Development::all();

        $allPositions = [
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

        $rawPosition = $user->employee->position ?? 'Operator';
        $currentPosition = Str::contains($rawPosition, 'Act ') ? trim(str_replace('Act', '', $rawPosition)) : $rawPosition;
        $positionIndex = array_search($currentPosition, $allPositions);
        $positionIndex = $positionIndex !== false ? $positionIndex : array_search('Operator', $allPositions);

        $visiblePositions = $positionIndex !== false ? array_slice($allPositions, $positionIndex) : [];

        $groupedAssessments = $assessments->groupBy(function ($item) {
            return optional(optional($item->hav)->hav)->employee->id;
        });

        return view('website.idp.list', compact(
            'employees',
            'assessments',
            'alcs',
            'visiblePositions',
            'filter',
            'details',
            'mid',
            'idps',
            'company',
            'groupedAssessments'
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

        $assessments = Idp::with('hav.hav.employee')
            ->whereHas('hav.hav.employee', function ($q) use ($employee) {
                $q->where('id', $employee->id);
            })
            ->get()
            ->groupBy('assessment_id') // Grouping berdasarkan assessment_id
            ->map(function ($group) {
                return $group->values(); // Reset key supaya array numerik
            });

        return response()->json([
            'employee' => $employee,
            'grouped_assessments' => $assessments,
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'hav_detail_id'       => 'nullable',
                'alc_id'              => 'required',
                'assessment_id'       => 'required',
                'development_program' => 'required',
                'category'            => 'required',
                'development_target'  => 'required',
                'date'                => 'required',
            ]);

            $idp = Idp::where('hav_detail_id', $request->hav_detail_id)
                ->where('alc_id', $request->alc_id)
                ->first();

            if ($idp) {
                $idp->update([
                    'development_program' => $request->development_program ?? $idp->development_program,
                    'category' => $request->category ?? $idp->category,
                    'development_target' => $request->development_target ?? $idp->development_target,
                    'status' => 0,
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
                    'hav_detail_id' => $request->hav_detail_id,
                    'alc_id' => $request->alc_id,
                    'assessment_id' => $request->assessment_id,
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
            'idp_id' => 'required|array',
            'development_program' => 'required|array',
            'development_achievement' => 'required|array',
            'next_action' => 'required|array',
        ]);

        foreach ($request->development_program as $key => $program) {
            Development::create([
                'employee_id' => $employee_id,
                'idp_id' => $request->idp_id[$key] ?? '',
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
            'idp_id' => 'required|array',
        ]);

        foreach ($request->development_program as $index => $program) {
            DevelopmentOne::create([
                'employee_id' => $employee_id,
                'idp_id' => $request->idp_id[$index] ?? '',
                'development_program' => $program,
                'evaluation_result' => $request->evaluation_result[$index] ?? '',
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
                $strengths[] = " - " . $detail->alc_name;
            }
            if (!empty($detail->weakness)) {
                $weaknesses[] = " - " . $detail->alc_name;
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

            // Ambil semua HAV terakhir untuk employee yang dimaksud
            $detailAssessments = Hav::with(['employee', 'details.idp', 'details.alc'])
                ->whereHas('employee', function ($query) use ($employeeId) {
                    $query->where('employee_id', $employeeId);
                })
                ->whereIn('id', function ($query) {
                    $query->selectRaw('id')
                        ->from('havs as a')
                        ->whereRaw('a.created_at = (
                        SELECT MAX(created_at)
                        FROM havs
                        WHERE employee_id = a.employee_id
                    )');
                })
                ->get();

            // Cek jika ada nilai < 3 tapi belum dibuat IDP
            foreach ($detailAssessments as $assessment) {
                foreach ($assessment->details as $detail) {
                    if ($detail->score < 3 && empty($detail->idp)) {
                        return response()->json(['message' => 'Ada data yang belum dibuat!'], 400);
                    }
                }
            }

            // Cek jika semua ALC nilainya >= 3
            $belowThree = $detailAssessments->flatMap(function ($assessment) {
                return $assessment->details->filter(function ($detail) {
                    return $detail->score < 3;
                });
            });


            // Ambil semua IDP yang masih status 0
            $idps = IDP::with('hav.hav.employee')
                ->whereHas('hav.hav.employee', function ($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId);
                })
                ->where('status', 0)
                ->get();

            if ($idps->isEmpty()) {
                return response()->json(['message' => 'Tidak ada IDP yang dikirim.'], 400);
            }

            // Update status IDP
            $idps->each->update(['status' => 1]);

            return response()->json(['message' => 'IDP berhasil dikirim ke atasan dan status diperbarui.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengirim IDP. Silakan coba lagi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function approval()
    {
        $user = auth()->user();
        $employee = $user->employee;

        $checkLevel = $employee->getFirstApproval();
        $approveLevel = $employee->getFinalApproval();
        $normalized = $employee->getNormalizedPosition();

        if ($normalized === 'vpd') {
            $subCheck = $employee->getSubordinatesByLevel($checkLevel, ['gm'])->pluck('id')->toArray();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel, ['manager'])->pluck('id')->toArray();
        } else {
            $subCheck = $employee->getSubordinatesByLevel($checkLevel)->pluck('id')->toArray();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel)->pluck('id')->toArray();
        }


        // === Tahap 1: CHECK ===
        $checkIdps = Idp::with('hav.hav.employee', 'hav')
            ->where('status', 1)
            ->whereHas('hav.hav.employee', function ($q) use ($subCheck) {
                $q->whereIn('employee_id', $subCheck);
            })
            ->get()
            ->filter(function ($idp) {
                $havId = $idp->hav->hav_id ?? null;
                if (!$havId)
                    return false;

                // Tidak ada yang status = -1
                return !Idp::whereHas('hav', function ($q) use ($havId) {
                    $q->where('hav_id', $havId);
                })->where('status', -1)->exists();
            });

        $checkIdpIds = $checkIdps->pluck('id')->toArray();

        // === Tahap 2: APPROVE ===
        $approveIdpsQuery = Idp::with('hav.hav.employee', 'hav')
            ->where('status', 2)
            ->whereHas('hav.hav.employee', function ($q) use ($subApprove) {
                $q->whereIn('employee_id', $subApprove);
            })
            ->whereNotIn('id', $checkIdpIds);

        // Only exclude managers if logged in user is president
        if ($normalized === 'president') {
            $approveIdpsQuery->whereHas('hav.hav.employee', function ($q) {
                $q->where('position', '!=', 'Manager');
            });
        }

        $approveIdps = $approveIdpsQuery->get()
            ->filter(function ($idp) {
                $havId = $idp->hav->hav_id ?? null;
                if (!$havId)
                    return false;

                $relatedStatuses = Idp::whereHas('hav', function ($q) use ($havId) {
                    $q->where('hav_id', $havId);
                })->pluck('status')->toArray();

                // - Minimal satu status = 2
                // - Tidak boleh ada status = -1
                return in_array(2, $relatedStatuses) && !in_array(-1, $relatedStatuses);
            });

        $approvePresidentIdps = collect();
        if ($normalized === 'president') {
            $approvePresidentIdps = Idp::with('hav.hav.employee', 'hav')
                ->where('status', 3)
                ->whereHas('hav.hav.employee', function ($q) use ($subApprove) {
                    $q->whereIn('employee_id', $subApprove);
                })
                ->whereNotIn('id', $checkIdpIds)
                ->get()
                ->filter(function ($idp) {
                    $havId = $idp->hav->hav_id ?? null;
                    if (!$havId)
                        return false;

                    $relatedStatuses = Idp::whereHas('hav', function ($q) use ($havId) {
                        $q->where('hav_id', $havId);
                    })->pluck('status')->toArray();

                    // - Minimal satu status = 3
                    // - Tidak boleh ada status = -1 atau 2
                    return in_array(3, $relatedStatuses) &&
                        !in_array(-1, $relatedStatuses) &&
                        !in_array(2, $relatedStatuses);
                });
        }

        $idps = $checkIdps->merge($approveIdps);
        if ($normalized === 'president') {
            $idps = $idps->merge($approvePresidentIdps);
        }


        return view('website.approval.idp.index', compact('idps'));
    }

    public function approve($id)
    {
        $idp = Idp::findOrFail($id);

        if ($idp->status == 1) {
            $idp->status = 2;
            $idp->save();

            return response()->json([
                'message' => 'IDP has been approved!'
            ]);
        }

        if ($idp->status == 2) {
            $idp->status = 3;
            $idp->save();

            return response()->json([
                'message' => 'IDP has been approved!'
            ]);
        }

        if ($idp->status == 3) {
            $idp->status = 4;
            $idp->save();

            return response()->json([
                'message' => 'IDP has been approved!'
            ]);
        }

        return response()->json([
            'message' => 'Something went wrong!'
        ], 400);
    }
    public function revise(Request $request)
    {
        $idp = Idp::findOrFail($request->id);

        // Menyimpan status HAV sebagai disetujui
        $idp->status = -1;

        // Ambil komentar dari input request
        $comment = $request->input('comment');
        $employee = auth()->user()->employee;
        // Menyimpan komentar ke dalam tabel hav_comment_history
        if ($employee) {
            $idp->commentHistory()->create([
                'comment' => $comment,
                'employee_id' => $employee->id  // Menyimpan siapa yang memberikan komentar
            ]);
        }

        // Simpan perubahan status HAV
        $idp->save();

        // Kembalikan respons JSON
        return response()->json(['message' => 'Data berhasil direvisi.']);
    }

    public function destroy($id)
    {
        Idp::where('assessment_id', $id)->delete();

        return redirect()->back()->with('success', 'IDP deleted successfully.');
    }

    public function manage(Request $request)
    {
        $company   = (string) $request->query('company', '');
        $positions = $request->query('positions', []);
        $backup    = $request->query('backup', []); // checkbox -> array

        if (!is_array($positions)) $positions = [$positions];
        if (!is_array($backup))    $backup    = [$backup];

        $synonymMap = [
            'President'  => ['President'],
            'VPD'        => ['VPD'],
            'Direktur'   => ['Direktur', 'Director'],
            'GM'         => ['GM', 'Act GM'],
            'Manager'    => ['Manager', 'Coordinator', 'Act Manager', 'Act Coordinator'],
            'Supervisor' => ['Supervisor', 'Section Head', 'Act Section Head'],
            'Leader'     => ['Leader', 'Staff', 'Act Leader'],
            'JP'         => ['JP', 'Act JP'],
            'Operator'   => ['Operator'],
        ];

        // Normalisasi
        $companyNorm = Str::of($company)->lower()->trim()->toString();

        $posNorms = collect($positions)->filter()->map(fn($p) => Str::of($p)->trim()->toString());
        $posAlts  = $posNorms
            ->flatMap(fn($p) => $synonymMap[$p] ?? [$p])
            ->map(fn($s) => Str::of($s)->lower()->trim()->toString())
            ->unique()->values();

        // Backup filter: terapkan hanya jika tepat satu opsi dipilih
        $backupSet = collect($backup)
            ->map(fn($v) => Str::of($v)->lower()->trim()->toString())
            ->filter(fn($v) => in_array($v, ['with', 'without'], true))
            ->unique()
            ->values();

        $onlyWith    = $backupSet->contains('with') && !$backupSet->contains('without');
        $onlyWithout = $backupSet->contains('without') && !$backupSet->contains('with');

        // Query
        $query = Idp::query()
            ->select(
                'idp.*',
                'employees.name as employee_name',
                'employees.company_name as employee_company_name',
                'employees.position as employee_position'
            )
            ->join('assessments', 'assessments.id', '=', 'idp.assessment_id')
            ->join('employees', 'employees.id', '=', 'assessments.employee_id')
            ->when($companyNorm !== '', function ($q) use ($companyNorm) {
                $q->whereRaw('LOWER(TRIM(employees.company_name)) = ?', [$companyNorm]);
                // atau partial match:
                // $q->whereRaw('LOWER(TRIM(employees.company_name)) LIKE ?', ["%{$companyNorm}%"]);
            })
            ->when($posAlts->isNotEmpty(), function ($q) use ($posAlts) {
                $q->whereIn(DB::raw('LOWER(TRIM(employees.position))'), $posAlts->all());
            })
            // filter backup (checkbox)
            ->when($onlyWith, function ($q) {
                $q->has('backups');
            })
            ->when($onlyWithout, function ($q) {
                $q->doesntHave('backups');
            })
            ->withCount('backups'); // untuk ikon centang di tabel

        $idps = $query->get();

        // Dropdown data
        $companies = Employee::query()
            ->whereNotNull('company_name')
            ->select('company_name')
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name');

        $allPositions = array_keys($synonymMap);

        return view('website.idp.manage.index', compact(
            'idps',
            'companies',
            'allPositions',
            'company',
            'positions',
            'backup' // kirim ke view supaya checkbox tetap tercentang
        ));
    }

    public function edit($id)
    {
        $idp = Idp::findOrFail($id);
        return view('website.idp.manage.edit', compact('idp'));
    }

    public function update(Request $request, Idp $idp)
    {
        $data = $request->validate([
            'category'             => ['required', 'string', 'max:100'],
            'alc_id'               => ['required', 'integer'],
            'development_program'  => ['required', 'string', 'max:160'],
            'development_target'   => ['required', 'string', 'max:1200'],
            'date'                 => ['required', 'date'],
            'status'               => ['nullable', 'integer', 'in:0,1,2,3,4'],
        ]);

        $data['alc_id'] = (int) $data['alc_id'];
        $data['status'] = (int) $data['status'];

        DB::transaction(function () use ($idp, $data) {
            $fields = ['hav_detail_id', 'assessment_id', 'alc_id', 'category', 'development_program', 'development_target', 'date', 'status'];

            $nextVersion = (int) $idp->backups()->max('version') + 1;

            IdpBackup::create([
                'idp_id'              => $idp->id,
                'assessment_id'       => $idp->assessment_id,
                'alc_id'              => $idp->alc_id,
                'hav_detail_id'       => $idp->hav_detail_id,
                'category'            => $idp->category,
                'development_program' => $idp->development_program,
                'development_target'  => $idp->development_target,
                'date'                => $idp->date,
                'status'              => $idp->status,
                'version'             => $nextVersion,
                'changed_by'          => auth()->id(),
                'changed_at'          => now(),
            ]);

            $idp->update($data);
        });

        return redirect()->route('idp.manage.all')
            ->with('success', 'IDP berhasil diperbarui.');
    }


    // PRIVATE FUNCTION
    private function getAssessments($user, $company, $npk, $search)
    {
        if ($user->isHRDorDireksi()) {
            return Hav::with(['details.idp', 'employee', 'details.alc'])
                ->when($company, function ($query) use ($company) {
                    $query->whereHas('employee', fn($q) => $q->where('company_name', $company));
                })
                ->when($npk, function ($query) use ($npk) {
                    $query->whereHas('employee', fn($q) => $q->where('npk', $npk));
                })
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('employee', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('npk', 'like', '%' . $search . '%');
                    });
                })
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id)')
                        ->from('havs')
                        ->groupBy('employee_id');
                })
                ->orderByDesc('created_at')
                ->paginate(10);
        }

        $emp = Employee::where('user_id', $user->id)->first();
        if (!$emp) {
            return collect();
        }

        $viewLevel = $emp->getCreateAuth();
        $subordinates = $emp->getSubordinatesByLevel($viewLevel)->pluck('id')->toArray();

        return Hav::with(['details.idp', 'employee', 'details.alc'])
            ->whereHas('employee', fn($query) => $query->whereIn('id', $subordinates))
            ->when($company, fn($query) => $query->whereHas('employee', fn($q) => $q->where('company_name', $company)))
            ->when($npk, fn($query) => $query->whereHas('employee', fn($q) => $q->where('npk', $npk)))
            ->when($search, fn($query) => $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('npk', 'like', '%' . $search . '%');
            }))
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('havs')
                    ->groupBy('employee_id');
            })
            ->get();
    }

    private function processAssessments($assessments, $alcs)
    {
        $badges = [
            'no_approval_needed' => ['text' => '-', 'class' => 'light-primary'],
            'not_created' => ['text' => 'Not Created', 'class' => 'light-dark'],
            'draft' => ['text' => 'Need Submit', 'class' => 'light-secondary'],
            'waiting' => ['text' => 'Waiting', 'class' => 'light-warning'],
            'checked' => ['text' => 'Checked', 'class' => 'light-info'],
            'approved' => ['text' => 'Approved', 'class' => 'light-success'],
            'revise' => ['text' => 'Need Revise', 'class' => 'light-danger'],
            'unknown' => ['text' => 'Unknown', 'class' => 'light-secondary'],
        ];

        return $assessments->map(function ($assessment) use ($alcs, $badges) {
            $assessment->has_score = $assessment->details->contains(function ($detail) {
                return !is_null($detail->score);
            });

            foreach ($assessment->details as $detail) {
                $detail->status = $this->determineIdpStatus($detail);

                $approverName = $this->getApproverName($detail);
                $badgeText = $badges[$detail->status]['text'];

                // Tambahkan nama approver untuk status approved
                if ($detail->status === 'approved' && $approverName) {
                    $badgeText = 'Approved by ' . $approverName;
                }

                $detail->badge = [
                    'text' => $badgeText,
                    'class' => $badges[$detail->status]['class']
                ];

                $detail->badge_class = $this->getBadgeClass($detail);
                $detail->show_icon = $this->shouldShowIcon($detail);
            }

            // Get overall status
            $overallStatus = $this->getOverallStatus($assessment);
            $assessment->overall_status = $overallStatus['status'];

            switch ($overallStatus['status']) {
                case 'approved':
                    $overallBadgeText = $overallStatus['approver']
                        ? 'Approved by ' . $overallStatus['approver']
                        : $badges['approved']['text'];
                    break;

                case 'waiting':
                    $overallBadgeText = $overallStatus['approver']
                        ? 'Waiting by ' . $overallStatus['approver']
                        : $badges['waiting']['text'];
                    break;

                case 'checked':
                    $overallBadgeText = $overallStatus['approver']
                        ? 'Checked by ' . $overallStatus['approver'] // (untuk manager status=3 otomatis nama step-2)
                        : $badges['checked']['text'];
                    break;

                default:
                    $overallBadgeText = $badges[$overallStatus['status']]['text'];
            }

            $assessment->overall_badge = [
                'text' => $overallBadgeText,
                'class' => $badges[$overallStatus['status']]['class']
            ];

            return $assessment;
        });
    }

    private function getApproverName($detail)
    {
        try {
            $employee = $detail->hav->employee ?? null;
            if (!$employee)
                return null;

            // kalau detail approved → pakai final approval; selain itu → first approval
            $level = ($detail->status ?? null) === 'approved'
                ? (int) $employee->getFinalApproval()
                : (int) $employee->getFirstApproval();

            if ($level <= 0)
                return null;

            $chain = $employee->getSuperiorsByLevel($level); // Collection<Employee>
            $finalSuperior = $chain->last();

            return $finalSuperior->name ?? null;
        } catch (\Throwable $e) {
            logger()->error('Error getting approver name: ' . $e->getMessage());
            return null;
        }
    }

    private function determineIdpStatus($detail)
    {
        if ($detail->score >= 3 && $detail->suggestion_development === null) {
            return 'no_approval_needed';
        }

        $idp = $detail->idp->first();
        $employee = $idp->assessment->employee ?? null;
        $normalized = $employee?->getNormalizedPosition();

        if (!$idp) {
            return 'not_created';
        }

        // Logika khusus untuk manager
        if ($normalized === 'manager') {
            switch ($idp->status) {
                case 0:
                    return 'draft';
                case 1:
                    return 'waiting';
                case 2:
                    return 'checked';
                case 3:
                    return 'checked';  // Khusus manager, status 3 dianggap checked
                case 4:
                    return 'approved'; // Khusus manager, status 4 adalah approved
                case -1:
                    return 'revise';
                default:
                    return 'unknown';
            }
        }

        // Logika normal untuk non-manager
        switch ($idp->status) {
            case 0:
                return 'draft';
            case 1:
                return 'waiting';
            case 2:
                return 'checked';
            case 3:
                return 'approved';
            case -1:
                return 'revise';
            default:
                return 'unknown';
        }
    }

    private function getBadgeClass($detail)
    {
        $class = 'badge-lg d-block w-100 ';

        if ($detail->status === 'revise') {
            return $class . 'badge-light-danger';
        }

        if ($detail->score < 3) {
            return $class . 'badge-danger';
        }

        if ($detail->suggestion_development !== null) {
            return $class . 'badge-warning';
        }

        return $class . 'badge-success';
    }

    private function shouldShowIcon($detail)
    {
        if ($detail->score < 3) {
            return true;
        }

        if ($detail->suggestion_development !== null && !$detail->idp->isEmpty()) {
            return true;
        }

        return !$detail->idp->isEmpty();
    }

    private function getOverallStatus($assessment)
    {
        $statuses = [];
        $waitingBy = null;   // step-1
        $checked1By = null;   // step-1
        $checked2By = null;   // step-2
        $approvedBy = null;   // step-3

        foreach ($assessment->details as $detail) {
            if ($detail->score <= 3 || $detail->suggestion_development !== null) {
                if ($detail->score >= 3 && $detail->suggestion_development === null)
                    continue;

                $statuses[] = $detail->status;

                $employee = $assessment->employee ?? ($detail->hav->employee ?? null);
                $idp = $detail->idp->first();

                if (!$employee || !$idp)
                    continue;

                $normalized = $employee->getNormalizedPosition();

                if ($normalized === 'manager') {
                    // mapping manager: 1(waiting) → step1, 2(checked1) → step1, 3(checked2) → step2, 4(approved) → step3
                    switch ((int) $idp->status) {
                        case 1:
                            if (!$waitingBy)
                                $waitingBy = $this->getSuperiorNameAtStep($employee, 2);
                            break;
                        case 2:
                            if (!$checked1By)
                                $checked1By = $this->getSuperiorNameAtStep($employee, 2);
                            break;
                        case 3:
                            if (!$checked2By)
                                $checked2By = $this->getSuperiorNameAtStep($employee, 5);
                            break;
                        case 4:
                            if (!$approvedBy)
                                $approvedBy = $this->getSuperiorNameAtStep($employee, 5);
                            break;
                    }
                } else {
                    // non-manager: logika seperti sebelumnya
                    if ($detail->status === 'waiting' && !$waitingBy)
                        $waitingBy = $this->getApproverName($detail); // step-1
                    if ($detail->status === 'checked' && !$checked1By)
                        $checked1By = $this->getApproverName($detail); // step-1
                    if ($detail->status === 'approved' && !$approvedBy)
                        $approvedBy = $this->getApproverName($detail); // final
                }
            }
        }

        if (empty($statuses))
            return ['status' => 'no_approval_needed', 'approver' => null];
        if (in_array('not_created', $statuses, true))
            return ['status' => 'not_created', 'approver' => null];

        // urutan prioritas tetap
        $priority = ['revise', 'draft', 'waiting', 'checked', 'approved', 'unknown'];
        foreach ($priority as $status) {
            if (in_array($status, $statuses, true)) {
                // untuk "checked", jika ada checked2 (status 3) tampilkan nama step-2; kalau tidak, pakai step-1
                $approver = match ($status) {
                    'waiting' => $waitingBy,
                    'checked' => $checked2By ?: $checked1By,
                    'approved' => $approvedBy,
                    default => null,
                };
                return ['status' => $status, 'approver' => $approver];
            }
        }

        return ['status' => 'unknown', 'approver' => null];
    }

    private function getSuperiorNameAtStep(Employee $employee, int $step): ?string
    {
        $chain = $employee->getSuperiorsByLevel(10);
        $target = $chain->get($step - 1) ?? $chain->last();
        return $target->name ?? null;
    }

    private function getJobPositions($assessments)
    {
        $positions = $assessments->pluck('employee.position')
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        array_unshift($positions, 'Show All');

        return $positions;
    }

    private function getDevelopmentPrograms()
    {
        return [
            'Superior (DGM & GM) + DIC PUR + BOD Member',
            'Book Reading / Journal Business and BEST PRACTICES (Asia Pasific Region)',
            'To find "FIGURE LEADER" with Strong in Drive and Courage in Their Team --> Sharing Success Tips',
            'Team Leader of TASK FORCE with MULTY FUNCTION --> (AII) HYBRID DUMPER Project  (CAPACITY UP) & (AIIA) EV Project',
            'SR Project (Structural Reform -->DM & SCM)',
            'PEOPLE Development Program of Team members (ICT, IDP)',
            '(Leadership) --> Courageously & Situational Leadership',
            '(Developing Sub Ordinate) --> Coaching Skill / Developing Talents'
        ];
    }
}
