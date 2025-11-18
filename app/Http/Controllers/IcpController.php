<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Icp;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\IcpDetail;
use App\Helpers\RtcTarget;
use App\Models\Department;
use App\Models\SubSection;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GradeConversion;
use App\Models\IcpApprovalStep;
use App\Helpers\ApprovalHelper;
use App\Http\Requests\StoreIcpRequest;
use App\Models\MatrixCompetency;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\PerformanceAppraisalHistory;
use App\Services\IcpApproval;
use App\Services\IcpSnapshotUpdateService;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class IcpController extends Controller
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
            return Employee::whereRaw('1=0');  // tidak ada bawahan
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

    public function index(Request $request, $company = null)
    {
        $title = 'Employee ICP';
        $user = auth()->user();
        $search = $request->input('search');               // diteruskan ke querystring agar tab/URL konsisten
        $filter = $request->input('filter', 'all');

        // Posisi untuk tab
        $allPositions = [
            'President',
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
        $currentPosition = Str::startsWith($rawPosition, 'Act ')
            ? trim(Str::replaceFirst('Act', '', $rawPosition))
            : $rawPosition;

        $positionIndex = array_search($currentPosition, $allPositions);
        if ($positionIndex === false)
            $positionIndex = array_search('Operator', $allPositions);
        $visiblePositions = $positionIndex !== false ? array_slice($allPositions, $positionIndex) : [];

        // View tidak membawa $icp lagi; DataTables akan fetch via AJAX
        return view('website.icp.index', compact(
            'title',
            'visiblePositions',
            'search',
            'filter',
            'company'
        ));
    }

    public function data(Request $request, $company = null)
    {
        $user = auth()->user();

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim($request->input('search.value', ''));
        $filter = $request->input('filter', 'all');

        $base = Icp::query()->with('employee')
            ->when($company, fn($q) => $q->whereHas('employee', fn($e) => $e->where('company_name', $company)));

        if ($user->isHRDorDireksi()) {
            // tidak ada pembatasan bawahan
        } else {
            $employee = $user->employee;
            if (!$employee) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }
            $subordinates = $this->getSubordinatesFromStructure($employee);
            if ($subordinates instanceof \Illuminate\Database\Eloquent\Builder) {
                $base->whereHas('employee', fn($q) => $q->whereIn('id', $subordinates->select('id')));
            } else {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }
            // pastikan company konsisten untuk non-HRD
            $base->whereHas('employee', fn($q) => $q->where('company_name', $company ?: $employee->company_name));
        }

        // Hitung total (sebelum search/filter posisi)
        $recordsTotal = (clone $base)->count();

        // Filter posisi (exact atau 'Act {posisi}')
        if (!empty($filter) && $filter !== 'all') {
            $base->whereHas('employee', function ($e) use ($filter) {
                $e->where(function ($g) use ($filter) {
                    $g->where('position', $filter)
                        ->orWhere('position', 'like', "Act %{$filter}");
                });
            });
        }

        // Search global
        if ($searchValue !== '') {
            $base->whereHas('employee', function ($e) use ($searchValue) {
                $e->where(function ($qq) use ($searchValue) {
                    $qq->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('npk', 'like', "%{$searchValue}%")
                        ->orWhere('company_name', 'like', "%{$searchValue}%");
                });
            });
        }

        // Hitung setelah filter+search
        $recordsFiltered = (clone $base)->count();

        // Ordering (opsional; default by newest)
        $orderColIdx = (int) data_get($request->input('order.0'), 'column', 0);
        $orderDir = data_get($request->input('order.0'), 'dir', 'desc') === 'asc' ? 'asc' : 'desc';
        // mapping index kolom front-end
        $cols = ['id', 'photo', 'npk', 'name', 'company_name', 'position', 'department', 'grade', 'actions'];
        $orderCol = $cols[$orderColIdx] ?? 'id';

        if ($orderCol === 'name') {
            $base->join('employees', 'employees.id', '=', 'icp.employee_id')
                ->orderBy('employees.name', $orderDir)
                ->select('icp.*'); // pastikan kolom icp.* dipilih
        } else {
            $base->orderBy('icp.created_at', 'desc'); // fallback
        }

        // Paging
        $rows = $base->skip($start)->take($length)->get();

        // Susun data baris
        $data = [];
        foreach ($rows as $i => $icp) {
            $emp = $icp->employee;

            // Unit dinamis sesuai posisi
            $unit = match ($emp?->position) {
                'Direktur', 'Act Direktur' => $emp?->plant?->name,
                'GM', 'Act GM' => $emp?->division?->name,
                default => $emp?->department?->name,
            };

            $photoUrl = $emp?->photo ? asset('storage/' . $emp->photo) : asset('assets/media/avatars/300-1.jpg');

            $data[] = [
                // Serahkan "No" dihitung client dari start + row index, atau kirim kolom hidden index
                'no' => $start + $i + 1,
                'photo' => '<img src="' . $photoUrl . '" class="rounded" width="40" height="40" style="object-fit:cover" />',
                'npk' => e($emp?->npk ?? '-'),
                'name' => e($emp?->name ?? '-'),
                'company_name' => e($emp?->company_name ?? '-'),
                'position' => e($emp?->position ?? '-'),
                'department' => e($unit ?? '-'),
                'grade' => e($emp?->grade ?? '-'),
                'actions' => '<a href="#" data-employee-id="' . $emp?->id . '" class="btn btn-info btn-sm history-btn">History</a>',
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function assign(Request $request, $company = null)
    {
        $title = 'ICP Assign';
        $user = auth()->user();
        $emp = $user->employee;

        $filter = $request->input('filter', 'all');
        $search = $request->input('search');

        // tab posisi
        $allPositions = ['Direktur', 'GM', 'Manager', 'Coordinator', 'Section Head', 'Supervisor', 'Leader', 'JP', 'Operator'];
        $rawPosition = $emp->position ?? 'Operator';
        $currentPos = Str::startsWith($rawPosition, 'Act ') ? trim(substr($rawPosition, 4)) : $rawPosition;
        $posIdx = array_search($currentPos, $allPositions);
        $visiblePositions = $posIdx !== false ? array_slice($allPositions, $posIdx) : $allPositions;

        // bawahan yg boleh dibuat
        $createLevel = $emp->getCreateAuth();
        $subordinateIds = $emp->getSubordinatesByLevel($createLevel)->pluck('id');

        $employees = Employee::with([
            'departments:id,name',
            'latestIcp.steps.actor',
        ])
            ->whereIn('id', $subordinateIds)
            ->when($company ?: $emp->company_name, fn($q, $c) => $q->where('company_name', $c))
            ->when($filter && $filter !== 'all', function ($q) use ($filter) {
                $q->where(fn($x) => $x->where('position', $filter)->orWhere('position', 'like', "Act %{$filter}"));
            })
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $rows = $employees->map(function ($e) {
            $icp = $e->latestIcp; // bisa null
            $steps = $icp?->steps?->sortBy('step_order') ?? collect();

            $done = $steps->where('status', 'done')
                ->map(fn($s) => "✓ {$s->label}" . ($s->actor ? " ({$s->actor->name}, " . $s->acted_at?->format('d/m/Y') . ")" : ""))
                ->values()->all();

            $next = $steps->where('status', 'pending')->sortBy('step_order')->first();
            $waiting = $next ? "⏳ Waiting: {$next->label}" : null;

            $lastApprovedStep = $steps->where('type', 'approve')->where('status', 'done')
                ->sortByDesc('acted_at')->first();
            $approvedAt = $lastApprovedStep?->acted_at;

            $anchor = $icp?->last_evaluated_at ?: $approvedAt; // Carbon|string|null
            $anchorDate = $anchor ? Carbon::parse($anchor) : null;

            $isDue = $icp && $icp->status === Icp::STATUS_APPROVED
                && $anchorDate
                && $anchorDate->copy()->addYear()->isPast();

            $statusCode = $icp?->status ?? null;
            $expired = ($statusCode === Icp::STATUS_APPROVED && $approvedAt)
                ? Carbon::parse($approvedAt)->addYear()->isPast()
                : false;

            $badgeMap = [
                null => ['No ICP', 'badge-light'],
                Icp::STATUS_REVISE => ['Revise', 'badge-light-danger'],
                Icp::STATUS_SUBMITTED => ['Submitted', 'badge-light-primary'],
                Icp::STATUS_CHECKED => ['Checked', 'badge-light-warning'],
                Icp::STATUS_APPROVED => [$expired ? 'Approved (Expired)' : 'Approved', $expired ? 'badge-light-dark' : 'badge-light-success'],
            ];
            [$label, $badge] = $badgeMap[$statusCode] ?? ['-', 'badge-light'];

            $actions = [
                'add' => (!$icp) || $expired,
                'revise' => ($statusCode === Icp::STATUS_REVISE && $icp),
                'export' => (bool) $icp,
                'evaluate' => [
                    'show' => (bool) $isDue,
                    'next_date' => $anchorDate ? $anchorDate->copy()->addYear()->format('Y-m-d') : null,
                    'anchor' => $anchorDate?->format('Y-m-d'),
                    'icp_id' => $icp?->id,
                ],
            ];

            return compact('e', 'icp', 'done', 'waiting', 'label', 'badge', 'actions');
        });

        return view('website.icp.assign', compact('title', 'rows', 'filter', 'company', 'search', 'visiblePositions'));
    }

    public function create($employeeId)
    {
        $title = 'Add icp';

        $employee = Employee::with(
            'subSection.section.department',
            'leadingSection.department',
            'leadingDepartment.division'
        )->findOrFail($employeeId);

        $departments = Department::where('company', $employee->company_name)->get();
        $divisions   = Division::where('company', $employee->company_name)->get();
        $employees   = Employee::where('company_name', $employee->company_name)->get();
        $grades      = GradeConversion::all();

        $deptId = optional(optional($employee->subSection)->section)->department->id
            ?? optional($employee->leadingSection)->department->id
            ?? optional($employee->leadingDepartment)->id;

        $divId = optional(optional($employee->leadingDepartment)->division)->id;

        $technicalCompetencies = MatrixCompetency::with(['department:id,name', 'division:id,name'])
            ->when($deptId || $divId, function ($q) use ($deptId, $divId) {
                $q->where(function ($qq) use ($deptId, $divId) {
                    if ($deptId) {
                        $qq->orWhere(function ($x) use ($deptId) {
                            $x->whereNotNull('dept_id')
                                ->where('dept_id', $deptId);
                        });
                    }
                    if ($divId) {
                        $qq->orWhere(function ($x) use ($divId) {
                            $x->whereNotNull('divs_id')
                                ->where('divs_id', $divId);
                        });
                    }
                });
            })
            ->orderBy('competency')
            ->get();

        $deptCompetencies = $technicalCompetencies->whereNotNull('dept_id')->values();
        $divsCompetencies = $technicalCompetencies->whereNotNull('divs_id')->values();

        $currentYear = now()->year;
        $rtcMap  = RtcTarget::mapAll();
        $currentCodeGuess = RtcTarget::codeFromPositionName($employee->position);
        $orderedCodes = RtcTarget::codesFrom($currentCodeGuess);
        $rtcList = collect($orderedCodes)
            ->map(function ($code) use ($rtcMap) {
                return [
                    'code'     => $code,
                    'position' => $rtcMap[$code]['position'] ?? $code,
                ];
            })
            ->unique('position')
            ->values();

        if ($currentCodeGuess) {
            $map = RtcTarget::map($currentCodeGuess);
            $currentPositionName = $map['position'] ?? $employee->position;
        } else {
            $currentCodeGuess = RtcTarget::codeFromPositionName($employee->position);
        }

        $defaultStages = [
            [
                'year'          => $currentYear,
                'position_code' => null,
            ],
        ];

        // Ambil appraisal terakhir per tahun (max 3 tahun)
        $performanceData = PerformanceAppraisalHistory::where('employee_id', $employee->id)
            ->selectRaw('YEAR(date) as year, score')
            ->orderByDesc('date')
            ->get()
            ->groupBy('year')
            ->map(fn($it) => $it->first()->score)
            ->sortKeysDesc()
            ->take(3)
            ->toArray();

        $edu  = $employee->educations->first();

        return view('website.icp.create', compact(
            'title',
            'employee',
            'grades',
            'departments',
            'divisions',
            'employees',
            'technicalCompetencies',
            'deptCompetencies',
            'divsCompetencies',
            'deptId',
            'divId',
            'rtcList',
            'currentPositionName',
            'currentYear',
            'defaultStages',
            'performanceData',
            'edu'
        ));
    }

    public function store(StoreIcpRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $icp = Icp::create([
                'employee_id'   => $data['employee_id'],
                'aspiration'    => $data['aspiration'],
                'readiness'     => $data['readiness'],
                'career_target' => $data['career_target_code'],
                'date'          => $data['date'],
                'status'        => Icp::STATUS_DRAFT,
            ]);

            if (method_exists($this, 'seedStepsForIcp')) {
                $this->seedStepsForIcp($icp);
            }

            foreach ($data['stages'] as $idx => $stage) {
                $year       = (int) ($stage['year'] ?? null);
                $job        = $stage['job_function'] ?? null;
                $jobSource  = $stage['job_source'] ?? null;
                $level      = $stage['level'] ?? null;

                $effectivePos = $stage['_effective_position'] ?? ($stage['position_code'] ?? null);

                $rows = [];
                foreach ($stage['details'] as $d) {
                    $rows[] = [
                        'icp_id'                    => $icp->id,
                        'plan_year'                 => $year,
                        'job_function'              => $job,
                        'job_source'                => $jobSource,

                        'position'                  => $effectivePos,
                        'level'                     => $level,

                        'current_technical'         => $d['current_technical']        ?? null,
                        'current_nontechnical'      => $d['current_nontechnical']     ?? null,

                        'required_technical'        => $d['required_technical']       ?? null,
                        'required_nontechnical'     => $d['required_nontechnical']    ?? null,

                        'development_technical'     => $d['development_technical']    ?? null,
                        'development_nontechnical'  => $d['development_nontechnical'] ?? null,
                    ];
                }

                if (count($rows)) {
                    $icp->details()->createMany($rows);
                }
            }

            DB::commit();

            return redirect()
                ->route('icp.assign')
                ->with('success', 'Data ICP berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Gagal menambahkan data ICP: ' . $e->getMessage());
        }
    }

    public function show($employee_id)
    {
        $employee = Employee::with('icp')->find($employee_id);

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        $assessments = Icp::where('employee_id', $employee_id)
            ->select('id', 'employee_id', 'aspiration', 'career_target', 'date', 'job_function', 'position', 'level')
            ->orderBy('date', 'desc')
            ->with([
                'details' => function ($query) {
                    $query->select('icp_id', 'current_technical', 'current_nontechnical', 'required_technical', 'required_nontechnical', 'development_technical', 'development_nontechnical');
                }
            ])
            ->get();

        return response()->json([
            'employee' => $employee,
            'icp' => $assessments
        ]);
    }

    public function showModal($icp_id, Request $request)
    {

        $icp = Icp::select('id', 'employee_id', 'aspiration', 'career_target', 'date', 'job_function', 'position', 'level', 'readiness')
            ->orderBy('date', 'desc')
            ->with([
                'details' => function ($query) {
                    $query->select('icp_id', 'plan_year', 'job_function', 'position', 'level', 'current_technical', 'current_nontechnical', 'required_technical', 'required_nontechnical', 'development_technical', 'development_nontechnical');
                },
                'employee',
                'steps'
            ])
            ->find($icp_id);

        $performanceData = PerformanceAppraisalHistory::where('employee_id', $icp->employee_id)
            ->selectRaw('YEAR(date) as year, score')
            ->orderByDesc('date')->get()
            ->groupBy('year')
            ->map(fn($it) => $it->first()->score)
            ->sortKeys()
            ->take(3)
            ->toArray();

        $edu  = $icp->employee->educations->first();

        if (!$icp) {
            if ($request->ajax()) {
                return response(
                    '<div class="alert alert-danger mb-0">ICP tidak ditemukan.</div>',
                    404
                );
            }

            abort(404);
        }

        $header = [
            'performanceData' => $performanceData,
            'edu'             => $edu
        ];

        if ($request->ajax()) {
            return view('website.modal.icp.show-detail', [
                'icp' => $icp,
                'header' => $header
            ]);
        }
    }

    public function export($employee_id)
    {
        $employee = Employee::with([
            'educations',
            'promotionHistory' => fn($q) => $q->orderByDesc('last_promotion_date'),
            'icp' => fn($q) => $q->latest()->with([
                'details' => fn($d) => $d->orderBy('plan_year')->orderBy('id'),
                'steps',
            ]),
        ])->findOrFail($employee_id);

        $icp = $employee->icp->first();
        if (!$icp) {
            return back()->with('error', 'Data ICP tidak ditemukan untuk employee ini.');
        }

        $edu  = $employee->educations->first();
        $prom = $employee->promotionHistory->first();
        $ais  = $employee->aisin_entry_date
            ? \Carbon\Carbon::parse($employee->aisin_entry_date)->format('d/m/Y')
            : '';

        // Ambil appraisal terakhir per tahun (max 3 tahun)
        $performanceData = PerformanceAppraisalHistory::where('employee_id', $employee->id)
            ->selectRaw('YEAR(date) as year, score')
            ->orderByDesc('date')
            ->get()
            ->groupBy('year')
            ->map(fn($it) => $it->first()->score)
            ->sortKeysDesc()
            ->take(3)
            ->toArray();

        // Load template
        $filePath = public_path('assets/file/Template_ICP.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        /* =====================================================
       1) HEADER TAHUN / RANGE PLAN YEAR
    ======================================================*/
        $years = $icp->details->pluck('plan_year')->filter()->map(fn($y) => (int) $y);
        $minYear = $years->min();
        $maxYear = $years->max();

        $sheet->setCellValue(
            'G2',
            $minYear && $maxYear
                ? "Year  :  {$minYear} - {$maxYear}"
                : "Year  :  " . now()->year . " - " . (now()->year + 1)
        );
        $sheet->getStyle('G2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        /* =====================================================
       2) PERFORMANCE APPRAISAL (R5/T5/V5)
    ======================================================*/
        $cols = ['R', 'T', 'V'];
        $i = 0;
        foreach ($performanceData as $yr => $score) {
            if ($i >= 3) break;
            $sheet->setCellValue($cols[$i] . '5', "{$yr} = {$score}");
            $i++;
        }
        // filler "-" kalau kurang dari 3 data
        for (; $i < 3; $i++) {
            $sheet->setCellValue($cols[$i] . '5', '-');
        }

        /* =====================================================
       3) DATA KARYAWAN / ICP HEADER
    ======================================================*/
        $sheet->setCellValue('G5', $employee->name);
        $sheet->setCellValue('G6', $employee->company_name);
        $sheet->setCellValue('G7', $employee->position);
        $sheet->setCellValue('G8', $employee->grade);
        $sheet->setCellValue('R8', $employee->birthday_date);
        $sheet->setCellValue('R7', $icp->readiness);
        $sheet->setCellValue('R6', '3');
        if ($edu) {
            $sheet->setCellValue('R9', $edu->educational_level . '/' . $edu->major . '/' . $edu->institute);
        }
        $sheet->setCellValue('J8', $prom ? Carbon::parse($prom->last_promotion_date)->format('Y') : '-');
        $sheet->setCellValue('G9', $ais);
        $sheet->setCellValue('C13', (string) $icp->aspiration);
        $sheet->setCellValue('D16', (string) $icp->career_target);
        if ($maxYear) {
            $sheet->setCellValue('L16', $maxYear);
        }

        /* =====================================================
   4) DEVELOPMENT STAGE BLOK PER TAHUN
        Tiap blok: 10 rows
        1st year   -> row 26..35
        2nd year   -> row 36..45
        ...
        5th year   -> row 66..75
    ======================================================*/
        $YEAR_TOP = [26, 36, 46, 56, 66];
        $BLOCK_SPAN = 10; // row tinggi 10

        $RANGES = [
            'JP'        => ['D', 'I'], // job function / position / level
            'CUR_TECH'  => ['J', 'M'],
            'CUR_NON'   => ['N', 'N'],
            'REQ_TECH'  => ['O', 'R'],
            'REQ_NON'   => ['T', 'T'],
            'DEV_TECH'  => ['U', 'W'],
            'DEV_NON'   => ['X', 'Z'],
        ];

        // helper untuk unmerge kalau range kita overlap sama merge existing
        $unmergeIntersections = function ($ws, string $range) {
            [$t1, $b1] = Coordinate::rangeBoundaries($range);
            foreach ($ws->getMergeCells() as $m) {
                [$t2, $b2] = Coordinate::rangeBoundaries($m);
                $intersects = !(
                    $b1[0] < $t2[0] ||
                    $t1[0] > $b2[0] ||
                    $b1[1] < $t2[1] ||
                    $t1[1] > $b2[1]
                );
                if ($intersects) {
                    $ws->unmergeCells($m);
                }
            }
        };

        // group detail per tahun (maksimal 5 tahun)
        $groups = $icp->details
            ->groupBy('plan_year')
            ->sortKeys()
            ->take(5)
            ->values();

        foreach ($groups as $idx => $group) {
            $top = $YEAR_TOP[$idx];
            $bottom = $top + $BLOCK_SPAN - 1;

            // teks bullet per kolom
            $bullet = fn($col) => $group->pluck($col)
                ->filter()
                ->map(fn($v) => '- ' . $v)
                ->implode("\n");

            // gabungan job_function / position / level
            $jpLines = $group->map(function ($d) {
                $txt = trim("{$d->job_function} / {$d->position} / {$d->level}");
                return $txt;
            })->unique()->filter()->implode("\n");

            $targets = [
                'JP'        => $jpLines,
                'CUR_TECH'  => $bullet('current_technical'),
                'CUR_NON'   => $bullet('current_nontechnical'),
                'REQ_TECH'  => $bullet('required_technical'),
                'REQ_NON'   => $bullet('required_nontechnical'),
                'DEV_TECH'  => $bullet('development_technical'),
                'DEV_NON'   => $bullet('development_nontechnical'),
            ];

            foreach ($targets as $key => $text) {
                [$c1, $c2] = $RANGES[$key];
                $range = "{$c1}{$top}:{$c2}{$bottom}";

                // pastikan tidak ada merge conflict
                $unmergeIntersections($sheet, $range);

                // merge ulang dan isi value di sel kiri atas
                $sheet->mergeCells($range);
                $sheet->setCellValue("{$c1}{$top}", $text);

                // alignment per kolom
                $alignment = $sheet->getStyle($range)->getAlignment();
                $alignment->setWrapText(true);

                if ($key === 'JP') {
                    $alignment->setVertical(Alignment::VERTICAL_CENTER);
                } else {
                    $alignment->setVertical(Alignment::VERTICAL_TOP);
                }
            }
        }

        /* =====================================================
       5) APPROVAL STAMP & TANGGAL (ROW 78 AREA)
          - blok O..T  = APPROVE
          - blok U..Z  = CHECK
    ======================================================*/

        // pastikan steps sudah ada di $icp (karena kita with('steps') di query)
        $steps = $icp->steps ?? collect();

        // ambil step done terakhir bertipe approve
        $doneApprove = $steps
            ->where('type', 'approve')
            ->where('status', 'done')
            ->sortByDesc('step_order')
            ->first();

        // ambil step done terakhir bertipe check
        $doneCheck = $steps
            ->where('type', 'check')
            ->where('status', 'done')
            ->sortByDesc('step_order')
            ->first();

        // mapping role -> file stamp
        $stampMap = [
            'director'          => public_path('assets/media/stamp/DIR.jpg'),
            'gm'                => public_path('assets/media/stamp/GM.png'),
            'mgr'               => public_path('assets/media/stamp/MGR.jpg'),
        ];

        // helper untuk pasang stamp
        $placeStamp = function ($step, $cellDateTarget, $cellImageTarget, $cellNameTarget, $mergeNameRange) use ($sheet, $stampMap) {
            if (!$step) return;

            // tulis tanggal acted_at
            if ($step->acted_at) {
                $acted = Carbon::parse($step->acted_at)->format('d/m/Y');
                // taruh tanggal di sel target (misal P78 atau V78)
                $sheet->setCellValue($cellDateTarget, $acted);
            }

            // tulis name acted
            if ($step->actor_id && $step->actor) {
                [$t1, $b1] = Coordinate::rangeBoundaries($mergeNameRange);
                foreach ($sheet->getMergeCells() as $m) {
                    [$t2, $b2] = Coordinate::rangeBoundaries($m);
                    $intersects = !(
                        $b1[0] < $t2[0] ||
                        $t1[0] > $b2[0] ||
                        $b1[1] < $t2[1] ||
                        $t1[1] > $b2[1]
                    );
                    if ($intersects) {
                        $sheet->unmergeCells($m);
                    }
                }

                $sheet->mergeCells($mergeNameRange);
                $sheet->getStyle($mergeNameRange)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->setCellValue($cellNameTarget, $step->actor->name ?? '');
            }

            $roleKey = strtolower(trim($step->role ?? ''));
            $imgPath = $stampMap[$roleKey] ?? null;

            if ($imgPath && file_exists($imgPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath($imgPath);
                $drawing->setCoordinates($cellImageTarget);
                $drawing->setHeight(95);
                $drawing->setWorksheet($sheet);
            }
        };


        $placeStamp(
            $doneApprove,
            'P78',
            'S80',
            'O87',
            'O87:T87'
        );
        $placeStamp(
            $doneCheck,
            'V78',
            'W80',
            'U87',
            'U87:Z87'
        );

        /* =====================================================
       6) SAVE & DOWNLOAD
    ======================================================*/
        $filename = 'ICP_' . preg_replace('/[^\w\-]+/u', '_', $employee->name) . '.xlsx';
        $path = storage_path('app/' . $filename);

        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }


    /* =================== APPROVAL LIST =================== */
    public function approval()
    {
        $me = auth()->user()->employee;
        $role = ApprovalHelper::roleKeyFor($me);

        $rolesToMatch = [$role];
        // toleransi data lama
        if ($role === 'director')
            $rolesToMatch[] = 'direktur';
        if ($role === 'president')
            $rolesToMatch[] = 'presiden';
        if ($role === 'gm')
            $rolesToMatch[] = 'general manager';

        $steps = IcpApprovalStep::with(['icp.employee', 'icp.steps'])
            ->whereIn('role', $rolesToMatch)
            ->where('status', 'pending')
            ->whereHas('icp', function ($q) {
                $q->where('status', '!=', 4);
            })
            ->orderBy('step_order')
            ->get()
            ->filter(function ($s) {
                return $s->icp->steps->every(function ($x) use ($s) {
                    return $x->step_order >= $s->step_order || $x->status === 'done';
                });
            })
            ->values();

        return view('website.approval.icp.index', ['steps' => $steps, 'title' => 'Approval ICP']);
    }

    public function approve($icpId)
    {
        $me = auth()->user()->employee;
        $isHRD = auth()->user()->role === 'HRD';
        $role = ApprovalHelper::roleKeyFor($me);

        $icp = Icp::with('steps')->findOrFail($icpId);

        // Ambil step pending saat ini (next in turn)
        $pendingSorted = $icp->steps->where('status', 'pending')->sortBy('step_order');

        $step = $isHRD
            ? $pendingSorted->first()                             // HRD boleh eksekusi step berikutnya apa pun rolenya
            : $pendingSorted->firstWhere('role', $role);          // Non-HRD hanya step utk rolenya

        if (!$step) {
            return response()->json(['message' => 'No actionable step for your role.'], 400);
        }

        // Pastikan semua step sebelumnya sudah 'done'
        foreach ($icp->steps as $s) {
            if ($s->step_order < $step->step_order && $s->status !== 'done') {
                return response()->json(['message' => 'Previous steps are not completed yet.'], 400);
            }
        }

        DB::transaction(function () use ($icp, $step, $me) {
            $step->update([
                'status' => 'done',
                'actor_id' => $me->id,
                'acted_at' => now(),
            ]);

            // Hitung status ringkas ICP
            $remainingChecks = $icp->steps->where('status', 'pending')->where('type', 'check')->count();
            $hasApprovePending = $icp->steps->where('status', 'pending')->where('type', 'approve')->count() > 0;

            if ($remainingChecks > 0) {
                $icp->status = 1; // Submitted / masih proses cek
            } elseif ($hasApprovePending) {
                $icp->status = 2; // Semua check selesai → menunggu approve
            } else {
                $icp->status = 3; // Approve terakhir selesai
            }
            $icp->save();
        });

        return response()->json(['message' => 'Approved.']);
    }

    public function revise(Request $request)
    {
        $icp = Icp::with('steps')->findOrFail($request->id);

        DB::transaction(function () use ($icp, $request) {
            $icp->status = 0; // Revise
            $icp->save();

            // tandai step aktif sebagai revised (opsional):
            $current = $icp->steps->where('status', 'pending')->sortBy('step_order')->first();
            if ($current) {
                $current->update(['status' => 'revised']);
            }

            // catat komentar (kalau punya tabel comment history ICP)
            if ($emp = auth()->user()->employee) {
                $icp->snapshots()->create([
                    'reason' => (string) $request->input('comment', ''),
                    'employee_id' => $emp->id,
                ]);
            }
        });

        return response()->json(['message' => 'ICP has been revised.']);
    }

    public function edit($id)
    {
        $title = 'Update ICP';

        $icp = Icp::with([
            'details',
            'employee.subSection.section.department',
            'employee.leadingSection.department',
            'employee.leadingDepartment.division',
            'employee.educations',
        ])->findOrFail($id);

        $employee = $icp->employee;

        // referensi organisasi / dropdown
        $departments = Department::where('company', $employee->company_name)->get();
        $divisions   = Division::where('company', $employee->company_name)->get();
        $employees   = Employee::where('company_name', $employee->company_name)->get();
        $grades      = GradeConversion::all();

        // cari dept/div karyawan (untuk filter kompetensi awal)
        $deptId = optional(optional($employee->subSection)->section)->department->id
            ?? optional($employee->leadingSection)->department->id
            ?? optional($employee->leadingDepartment)->id;

        $divId = optional(optional($employee->leadingDepartment)->division)->id;

        $technicalCompetencies = MatrixCompetency::with(['department:id,name', 'division:id,name'])
            ->when($deptId || $divId, function ($q) use ($deptId, $divId) {
                $q->where(function ($qq) use ($deptId, $divId) {
                    if ($deptId) {
                        $qq->orWhere(function ($x) use ($deptId) {
                            $x->whereNotNull('dept_id')
                                ->where('dept_id', $deptId);
                        });
                    }
                    if ($divId) {
                        $qq->orWhere(function ($x) use ($divId) {
                            $x->whereNotNull('divs_id')
                                ->where('divs_id', $divId);
                        });
                    }
                });
            })
            ->orderBy('competency')
            ->get();

        $deptCompetencies = $technicalCompetencies->whereNotNull('dept_id')->values();
        $divsCompetencies = $technicalCompetencies->whereNotNull('divs_id')->values();

        // Career target dropdown (harus sama formatnya dengan create)
        $currentRtcCode = RtcTarget::codeFromPositionName($employee->position);
        $rtcMap  = RtcTarget::mapAll();
        $orderedCodes = RtcTarget::codesFrom($currentRtcCode);
        $rtcList = collect($orderedCodes)
            ->map(function ($code) use ($rtcMap) {
                return [
                    'code'     => $code,
                    'position' => $rtcMap[$code]['position'] ?? $code,
                ];
            })
            ->unique('position')
            ->values();


        // Performance appraisal terakhir per tahun (max 3 tahun) -> sama kayak create()
        $performanceData = PerformanceAppraisalHistory::where('employee_id', $employee->id)
            ->selectRaw('YEAR(date) as year, score')
            ->orderByDesc('date')
            ->get()
            ->groupBy('year')
            ->map(fn($it) => $it->first()->score)
            ->sortKeys()
            ->take(3)
            ->toArray();

        // Last education (buat header card)
        $edu = $employee->educations->first();

        // Bentuk ulang stages dari detail ICP supaya sesuai ekspektasi JS
        $stages = $icp->details
            ->sortBy('plan_year')
            ->groupBy('plan_year')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'year'           => (int) $first->plan_year,
                    'job_function'   => (string) ($first->job_function ?? ''),
                    'job_source'     => (string) ($first->job_source ?? ''),
                    'position_code'  => (string) ($first->position ?? ''), // kolom 'position' di DB = kode RTC
                    'level'          => (string) ($first->level ?? ''),
                    'details'        => $group->map(function ($d) {
                        return [
                            'current_technical'        => (string) ($d->current_technical ?? ''),
                            'required_technical'       => (string) ($d->required_technical ?? ''),
                            'development_technical'    => (string) ($d->development_technical ?? ''),
                            'current_nontechnical'     => (string) ($d->current_nontechnical ?? ''),
                            'required_nontechnical'    => (string) ($d->required_nontechnical ?? ''),
                            'development_nontechnical' => (string) ($d->development_nontechnical ?? ''),
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();

        // mode evaluate (readonly mode)
        $mode = request('mode'); // ex: ?mode=evaluate

        // buat konsistensi aja, kadang kepake
        $currentYear = now()->year;

        return view('website.icp.update', compact(
            'title',
            'icp',
            'employee',
            'stages',
            'mode',

            // dropdown/reference
            'grades',
            'departments',
            'divisions',
            'employees',

            // teknis/kompetensi
            'technicalCompetencies',
            'deptCompetencies',
            'divsCompetencies',
            'deptId',
            'divId',

            // career target data dan batas posisi
            'rtcList',
            'currentRtcCode',

            // header info
            'performanceData',
            'edu',
            'currentYear'
        ));
    }

    public function update(StoreIcpRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $icp = Icp::findOrFail($id);

            $icp->update([
                'employee_id'    => $data['employee_id'],
                'aspiration'     => $data['aspiration'],
                'readiness'     => $data['readiness'],
                'career_target'  => $data['career_target_code'],
                'date'           => $data['date'],
                'status'         => Icp::STATUS_DRAFT,
            ]);

            if (!empty($data['stages']) && is_array($data['stages'])) {
                $lastStage      = end($data['stages']);
                $careerTarget   = $data['career_target_code'];
                $lastPosition   = $lastStage['position_code'] ?? ($lastStage['_effective_position'] ?? null);

                if ($careerTarget && $lastPosition && $careerTarget !== $lastPosition) {
                    DB::rollBack();
                    return back()
                        ->with('error', 'Posisi di stage terakhir harus sama dengan Career Target.')
                        ->withInput();
                }
            }

            $icp->details()->delete();

            $rowsToInsert = [];

            foreach (($data['stages'] ?? []) as $stage) {

                $year      = isset($stage['year']) ? (int) $stage['year'] : null;
                $job       = $stage['job_function']    ?? null;
                $jobSource = $stage['job_source']      ?? null;
                $level     = $stage['level']           ?? null;

                $effectivePos = $stage['_effective_position'] ?? ($stage['position_code'] ?? null);
                foreach (($stage['details'] ?? []) as $d) {
                    $rowsToInsert[] = [
                        'icp_id'                    => $icp->id,
                        'plan_year'                 => $year,
                        'job_function'              => $job,
                        'job_source'                => $jobSource,
                        'position'                  => $effectivePos,
                        'level'                     => $level,

                        'current_technical'         => $d['current_technical']        ?? null,
                        'current_nontechnical'      => $d['current_nontechnical']     ?? null,

                        'required_technical'        => $d['required_technical']       ?? null,
                        'required_nontechnical'     => $d['required_nontechnical']    ?? null,

                        'development_technical'     => $d['development_technical']    ?? null,
                        'development_nontechnical'  => $d['development_nontechnical'] ?? null,
                    ];
                }
            }

            if (!empty($rowsToInsert)) {
                $icp->details()->createMany($rowsToInsert);
            }

            DB::commit();

            return redirect()
                ->route('icp.assign')
                ->with('success', 'Data ICP berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Gagal memperbarui ICP: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $icp = Icp::find($id);

        if (!$icp) {
            return response()->json(['message' => 'ICP not found'], 404);
        }

        $icp->delete();

        return response()->json(['message' => 'ICP deleted successfully']);
    }
    private function seedStepsForIcp(Icp $icp)
    {
        $owner = $icp->employee()->first();
        $chain = ApprovalHelper::expectedChainForEmployee($owner);
        $icp->steps()->delete();
        foreach ($chain as $i => $s) {
            IcpApprovalStep::create([
                'icp_id' => $icp->id,
                'step_order' => $i + 1,
                'type' => $s['type'],
                'role' => $s['role'],
                'label' => $s['label']
            ]);
        }

        // kalau chain kosong, langsung approve
        if (empty($chain)) {
            $icp->status = Icp::STATUS_APPROVED; // 3
            $icp->save();
        }
    }

    public function submit($id)
    {
        $icp = Icp::with('steps', 'employee')->findOrFail($id);

        if ($icp->status !== Icp::STATUS_DRAFT) {
            return back()->with('error', 'Hanya draft yang bisa disubmit.');
        }

        DB::beginTransaction();
        try {
            // seed steps sesuai chain, lalu set status ringkas:
            $this->seedStepsForIcp($icp); // method kamu yang sudah ada
            // override status jadi SUBMITTED (1) agar jelas
            $icp->status = Icp::STATUS_SUBMITTED; // 1
            $icp->save();

            DB::commit();
            return back()->with('success', 'ICP berhasil disubmit untuk approval.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal submit: ' . $e->getMessage());
        }
    }

    /* =================== EVALUATE =================== */
    public function evaluateCreate(Icp $icp)
    {
        abort_if($icp->status !== Icp::STATUS_APPROVED, 403);

        $title = 'Evaluate ICP';
        $employee = Employee::findOrFail($icp->employee_id);
        $departments = Department::where('company', $employee->company_name)->get();
        $employees = Employee::where('company_name', $employee->company_name)->get();
        $grades = GradeConversion::all();
        $technicalCompetencies = MatrixCompetency::all();

        $stages = $icp->details
            ->groupBy('plan_year')
            ->map(function ($g) {
                $first = $g->first();
                return [
                    'plan_year' => (int) $first->plan_year,
                    'job_function' => (string) $first->job_function,
                    'position' => (string) $first->position,
                    'level' => (string) $first->level,
                    'details' => $g->map(fn($d) => [
                        'current_technical' => (string) $d->current_technical,
                        'current_nontechnical' => (string) $d->current_nontechnical,
                        'required_technical' => (string) $d->required_technical,
                        'required_nontechnical' => (string) $d->required_nontechnical,
                        'development_technical' => (string) $d->development_technical,
                        'development_nontechnical' => (string) $d->development_nontechnical,
                    ])->values()->all(),
                ];
            })
            ->values();

        $mode = 'evaluate';
        return view('website.icp.update', compact('title', 'grades', 'departments', 'employees', 'technicalCompetencies', 'icp', 'stages', 'mode'));
    }

    public function evaluateStore(Request $r, Icp $icp, IcpSnapshotUpdateService $svc)
    {
        abort_if($icp->status !== Icp::STATUS_APPROVED, 403);
        $this->validate($r, [
            'employee_id' => ['required', 'exists:employees,id'],
            'aspiration' => ['required', 'string'],
            'career_target' => ['required', 'string'],
            'date' => ['required', 'date'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.plan_year' => ['required', 'digits:4', 'numeric', 'distinct'],
            'stages.*.job_function' => ['required', 'string'],
            'stages.*.position' => ['required', 'string'],
            'stages.*.level' => ['required', 'string'],
            'stages.*.details' => ['required', 'array', 'min:1'],
            'stages.*.details.*.current_technical' => ['required', 'string'],
            'stages.*.details.*.current_nontechnical' => ['required', 'string'],
            'stages.*.details.*.required_technical' => ['required', 'string'],
            'stages.*.details.*.required_nontechnical' => ['required', 'string'],
            'stages.*.details.*.development_technical' => ['required', 'string'],
            'stages.*.details.*.development_nontechnical' => ['required', 'string'],
        ]);

        // tentukan planYear yang dievaluasi (umumnya tahun lalu)
        $planYear = now()->subYear()->year;


        // backup lalu update
        $svc->run($icp, $r->all(), $planYear, "Annual Evaluation ICP {$planYear}");

        return redirect()->route('icp.assign')->with('success', 'Evaluation saved (Create backup ICP)');
    }

    /* =================== HELPERS =================== */
    public function levelsForPosition()
    {
        $map = RtcTarget::map();

        return response()->json([
            'ok' => true,
            'levels' => $map['levels'] ?? []
        ]);
    }

    public function techs(Request $r)
    {
        // Ambil nilai biasa, bukan Stringable
        $source = strtolower($r->input('source', ''));   // 'department' | 'division'
        $name = trim($r->input('name', ''));
        $company = trim($r->input('company', ''));

        // Validasi cepat
        if (!in_array($source, ['department', 'division'], true)) {
            return response()->json(['ok' => false, 'message' => 'Invalid source'], 422);
        }
        if ($name === '') {
            return response()->json(['ok' => false, 'message' => 'Missing name'], 422);
        }

        if ($source === 'department') {
            $dept = Department::where('name', $name)->where('company', $company)->first();
            if (!$dept) {
                return response()->json(['ok' => true, 'items' => []]); // tidak error, hanya kosong
            }
            $items = MatrixCompetency::where('dept_id', $dept->id)
                ->orderBy('competency')->pluck('competency')->values();
        } else { // 'division'
            $div = Division::where('name', $name)->where('company', $company)->first();
            if (!$div) {
                return response()->json(['ok' => true, 'items' => []]);
            }
            $items = MatrixCompetency::where('divs_id', $div->id)
                ->orderBy('competency')->pluck('competency')->values();
        }

        return response()->json(['ok' => true, 'items' => $items]);
    }
}
