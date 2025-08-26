<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Icp;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\IcpDetail;
use App\Models\Department;
use App\Models\SubSection;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GradeConversion;
use App\Models\IcpApprovalStep;
use App\Helpers\ApprovalHelper;
use App\Models\MatrixCompetency;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\PerformanceAppraisalHistory;
use App\Services\IcpApproval;
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
        $title  = 'Employee ICP';
        $user   = auth()->user();
        $search = $request->input('search');
        $filter = $request->input('filter', 'all');

        if ($user->isHRDorDireksi()) {
            // HRD & Direksi melihat semua, + FILTER posisi (jika ada)
            $icps = Icp::with('employee')
                ->when($company, function ($q) use ($company) {
                    $q->whereHas('employee', fn($e) => $e->where('company_name', $company));
                })
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('employee', function ($e) use ($search) {
                        $e->where(function ($qq) use ($search) {
                            $qq->where('name', 'like', "%{$search}%")
                                ->orWhere('npk', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                    });
                })
                ->when($filter && $filter !== 'all', function ($q) use ($filter) {
                    $q->whereHas('employee', function ($e) use ($filter) {
                        // group: exact position ATAU "Act {position}"
                        $e->where(function ($g) use ($filter) {
                            $g->where('position', $filter)
                                ->orWhere('position', 'like', "Act %{$filter}");
                        });
                    });
                })
                ->paginate(10)
                ->appends(['search' => $search, 'filter' => $filter, 'company' => $company]);
        } else {
            // User non-HRD: hanya bawahan
            $employee = $user->employee;
            $cpm = $employee->company_name;
            if (!$employee) {
                $icps = collect();
            } else {
                $subordinatesQuery = $this->getSubordinatesFromStructure($employee);

                if ($subordinatesQuery instanceof \Illuminate\Database\Eloquent\Builder) {
                    $icps = Icp::with(['employee:id,name,npk,company_name,position,grade'])
                        ->whereHas('employee', function ($q) use ($subordinatesQuery, $search, $filter, $employee, $company) {
                            $q->whereIn('id', $subordinatesQuery->select('id'));

                            $q->where('company_name', $company ?: $employee->company_name);

                            // search
                            if (!empty($search)) {
                                $q->where(function ($q2) use ($search) {
                                    $q2->where('name', 'like', "%{$search}%")
                                        ->orWhere('npk', 'like', "%{$search}%")
                                        ->orWhere('company_name', 'like', "%{$search}%");
                                });
                            }

                            // filter posisi (exact atau "Act {posisi}")
                            if (!empty($filter) && $filter !== 'all') {
                                $q->where(function ($g) use ($filter) {
                                    $g->where('position', $filter)
                                        ->orWhere('position', 'like', "Act %{$filter}");
                                });
                            }
                        })
                        ->orderByDesc('created_at')
                        ->paginate(10)
                        ->appends(['search' => $search, 'filter' => $filter, 'company' => $company]);
                } else {
                    $icps = collect();
                }
            }
        }

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
        $currentPosition = Str::contains($rawPosition, 'Act ')
            ? trim(str_replace('Act', '', $rawPosition))
            : $rawPosition;

        $positionIndex = array_search($currentPosition, $allPositions);

        if ($positionIndex === false) {
            $positionIndex = array_search('Operator', $allPositions);
        }

        // Posisi yang terlihat di tab (dari posisi user ke bawah)
        $visiblePositions = $positionIndex !== false
            ? array_slice($allPositions, $positionIndex)
            : [];

        return view('website.icp.index', compact(
            'icps',
            'title',
            'visiblePositions',
            'search',
            'filter',
            'company'
        ));
    }

    public function assign(Request $request, $company = null)
    {
        $title   = 'ICP Assign';
        $user    = auth()->user();
        $emp     = $user->employee;

        $filter  = $request->input('filter', 'all');
        $search  = $request->input('search');

        // tab posisi yang terlihat (seperti sebelumnya)
        $allPositions = ['Direktur', 'GM', 'Manager', 'Coordinator', 'Section Head', 'Supervisor', 'Leader', 'JP', 'Operator'];
        $rawPosition  = $emp->position ?? 'Operator';
        $currentPos   = Str::startsWith($rawPosition, 'Act ') ? trim(substr($rawPosition, 4)) : $rawPosition;
        $posIdx       = array_search($currentPos, $allPositions);
        $visiblePositions = $posIdx !== false ? array_slice($allPositions, $posIdx) : $allPositions;

        // bawahan yg boleh dibuat
        $createLevel     = $emp->getCreateAuth();
        $subordinateIds  = $emp->getSubordinatesByLevel($createLevel)->pluck('id');

        $employees = Employee::with([
            'departments:id,name',
            'latestIcp.steps.actor',    // ambil steps + siapa check/approve
        ])
            ->whereIn('id', $subordinateIds)
            ->when($company ?: $emp->company_name, fn($q, $c) => $q->where('company_name', $c))
            ->when($filter && $filter !== 'all', function ($q) use ($filter) {
                $q->where(fn($x) => $x->where('position', $filter)->orWhere('position', 'like', "Act %{$filter}"));
            })
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        // siapkan baris
        $rows = $employees->map(function ($e) {
            $icp   = $e->latestIcp; // bisa null
            $steps = $icp?->steps?->sortBy('step_order') ?? collect();

            // garis status selesai
            $done = $steps->where('status', 'done')
                ->map(fn($s) => "✓ {$s->label}" . ($s->actor ? " ({$s->actor->name}, " . $s->acted_at?->format('d/m/Y') . ")" : ""))
                ->values()->all();

            // antrian berikutnya
            $next = $steps->where('status', 'pending')->sortBy('step_order')->first();
            $waiting = $next ? "⏳ Waiting: {$next->label}" : null;

            // gunakan waktu approve terakhir untuk expiry (bukan created_at)
            $lastApprovedStep = $steps->where('type', 'approve')->where('status', 'done')
                ->sortByDesc('acted_at')->first();
            $approvedAt = $lastApprovedStep?->acted_at;
            $statusCode = $icp?->status ?? null;
            $expired    = ($statusCode === 3 && $approvedAt)
                ? \Carbon\Carbon::parse($approvedAt)->addYear()->isPast()
                : false;

            $badgeMap = [
                null => ['No ICP', 'badge-light'],
                0    => ['Revise', 'badge-light-danger'],
                1    => ['Submitted', 'badge-light-primary'],
                2    => ['Checked', 'badge-light-warning'],
                3    => [$expired ? 'Approved (Expired)' : 'Approved', $expired ? 'badge-light-dark' : 'badge-light-success'],
            ];
            [$label, $badge] = $badgeMap[$statusCode] ?? ['-', 'badge-light'];

            $actions = [
                'add'    => (!$icp) || $expired,
                'revise' => ($statusCode === 0 && $icp),
                'export' => (bool) $icp,
            ];

            return compact('e', 'icp', 'done', 'waiting', 'label', 'badge', 'actions');
        });

        return view('website.icp.assign', compact('title', 'rows', 'filter', 'company', 'search', 'visiblePositions'));
    }

    public function create($employeeId)
    {
        $title = 'Add icp';

        $employee = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
            ->findOrFail($employeeId);
        $departments = Department::where('company', $employee->company_name)->get();
        $employees = Employee::where('company_name', $employee->company_name)->get();
        $grades = GradeConversion::all();

        $technicalCompetencies = MatrixCompetency::all();

        return view('website.icp.create', compact('title', 'employee', 'grades', 'departments', 'employees', 'technicalCompetencies'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => ['required', 'exists:employees,id'],
            'aspiration'    => ['required', 'string'],
            'career_target' => ['required', 'string'],
            'date'          => ['required', 'date'],

            'stages'                => ['required', 'array', 'min:1'],
            'stages.*.plan_year'    => ['required', 'digits:4', 'numeric', 'distinct'],
            'stages.*.job_function' => ['required', 'string'],
            'stages.*.position'     => ['required', 'string'],
            'stages.*.level'        => ['required', 'string'],

            'stages.*.details'                            => ['required', 'array', 'min:1'],
            'stages.*.details.*.current_technical'        => ['required', 'string'],
            'stages.*.details.*.current_nontechnical'     => ['required', 'string'],
            'stages.*.details.*.required_technical'       => ['required', 'string'],
            'stages.*.details.*.required_nontechnical'    => ['required', 'string'],
            'stages.*.details.*.development_technical'    => ['required', 'string'],
            'stages.*.details.*.development_nontechnical' => ['required', 'string'],
        ], [
            'stages.required'             => 'Minimal 1 tahun harus ditambahkan',
            'stages.*.plan_year.digits'   => 'Plan year harus 4 digit (YYYY)',
            'stages.*.plan_year.distinct' => 'Plan year tidak boleh sama antar stage.',
        ]);

        DB::beginTransaction();
        try {
            // Simpan data utama ICP
            $icp = Icp::create([
                'employee_id' => $request->employee_id,
                'aspiration' => $request->aspiration,
                'career_target' => $request->career_target,
                'date' => $request->date,
                'status' => '1',
            ]);
            $this->seedStepsForIcp($icp);

            // Loop semua detail dan simpan satu per satu
            foreach ($request->stages as $stage) {
                $year  = (int) $stage['plan_year'];
                $job   = $stage['job_function'];
                $pos   = $stage['position'];
                $level = $stage['level'];

                $rows = [];
                foreach ($stage['details'] as $d) {
                    $rows[] = [
                        'icp_id'                   => $icp->id,
                        'plan_year'                => $year,
                        'job_function'             => $job,
                        'position'                 => $pos,
                        'level'                    => $level,
                        'current_technical'        => $d['current_technical'],
                        'current_nontechnical'     => $d['current_nontechnical'],
                        'required_technical'       => $d['required_technical'],
                        'required_nontechnical'    => $d['required_nontechnical'],
                        'development_technical'    => $d['development_technical'],
                        'development_nontechnical' => $d['development_nontechnical'],
                    ];
                }

                $icp->details()->createMany($rows);
            }

            DB::commit();
            return redirect()->route('icp.assign')->with('success', 'Data ICP berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan data ICP: ' . $e->getMessage());
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
    public function export($employee_id)
    {
        $employee = Employee::with([
            'educations',
            'promotionHistory',
            'icp' => fn($q) => $q->latest()->with(['details' => fn($d) => $d->orderBy('plan_year')->orderBy('id')]),
        ])->findOrFail($employee_id);

        $icp = $employee->icp->first();
        if (!$icp)
            return back()->with('error', 'Data ICP tidak ditemukan untuk employee ini.');

        $edu = $employee->educations->first();
        $prom = $employee->promotionHistory->first();
        $ais = $employee->aisin_entry_date ? \Carbon\Carbon::parse($employee->aisin_entry_date)->format('d/m/Y') : '';

        $performanceData = PerformanceAppraisalHistory::where('employee_id', $employee->id)
            ->selectRaw('YEAR(date) as year, score')
            ->orderByDesc('date')->get()
            ->groupBy('year')->map(fn($it) => $it->first()->score)
            ->sortKeys()->take(3)->toArray();

        $filePath = public_path('assets/file/Template_ICP.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Header tahun dari rentang detail
        $years = $icp->details->pluck('plan_year')->filter()->map(fn($y) => (int) $y);
        $minYear = $years->min();
        $maxYear = $years->max();
        $sheet->setCellValue('G2', $minYear && $maxYear ? "Year  :  {$minYear} - {$maxYear}" : "Year  :  " . now()->year . " - " . (now()->year + 1));
        $sheet->getStyle('G2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Appraisal ke R5/T5/V5
        $cols = ['R', 'T', 'V'];
        $i = 0;
        foreach ($performanceData as $yr => $score) {
            if ($i >= 3)
                break;
            $sheet->setCellValue($cols[$i] . '5', "{$yr} = {$score}");
            $i++;
        }
        for (; $i < 3; $i++)
            $sheet->setCellValue($cols[$i] . '5', '-');

        // Data karyawan & ICP header
        $sheet->setCellValue('G5', $employee->name);
        $sheet->setCellValue('G6', $employee->company_name);
        $sheet->setCellValue('G7', $employee->position);
        $sheet->setCellValue('G8', $employee->grade);
        $sheet->setCellValue('R8', $employee->birthday_date);
        $sheet->setCellValue('R6', '3');
        if ($edu)
            $sheet->setCellValue('R9', $edu->educational_level . '/' . $edu->major . '/' . $edu->institute);
        $sheet->setCellValue('J8', $prom ? \Carbon\Carbon::parse($prom->last_promotion_date)->format('Y') : '-');
        $sheet->setCellValue('G9', $ais);
        $sheet->setCellValue('C13', (string) $icp->aspiration);
        $sheet->setCellValue('D16', (string) $icp->career_target);
        if ($minYear)
            $sheet->setCellValue('L16', $minYear);

        // ===== Development Stage (merge per tahun) =====
        // start row block (sesuai template: 1st..5th)
        $YEAR_TOP = [26, 36, 46, 56, 66];
        $BLOCK_SPAN = 10; // 26..35, 36..45, dst.

        // Range kolom sesuai template (ubah kalau beda)
        $RANGES = [
            'JP' => ['D', 'I'], // Job Function / Position / Level
            'CUR_TECH' => ['J', 'M'],
            'CUR_NON' => ['N', 'N'],
            'REQ_TECH' => ['O', 'R'],
            'REQ_NON' => ['T', 'T'],
            'DEV_TECH' => ['U', 'W'],
            'DEV_NON' => ['X', 'Z'],
        ];

        // helper: unmerge semua merge yang beririsan dengan $range
        $unmergeIntersections = function ($ws, string $range) {
            [$t1, $b1] = Coordinate::rangeBoundaries($range); // [[col,row],[col,row]]
            foreach ($ws->getMergeCells() as $m) {
                [$t2, $b2] = Coordinate::rangeBoundaries($m);
                $intersects = !($b1[0] < $t2[0] || $t1[0] > $b2[0] || $b1[1] < $t2[1] || $t1[1] > $b2[1]);
                if ($intersects)
                    $ws->unmergeCells($m);
            }
        };

        // group detail per tahun (maks 5)
        $groups = $icp->details->groupBy('plan_year')->sortKeys()->take(5)->values();

        foreach ($groups as $idx => $group) {
            $top = $YEAR_TOP[$idx];
            $bottom = $top + $BLOCK_SPAN - 1;

            // siapkan teks gabungan
            $bullet = fn($col) => $group->pluck($col)->filter()->map(fn($v) => '- ' . $v)->implode("\n");

            $jpLines = $group->map(fn($d) => trim("{$d->job_function} / {$d->position} / {$d->level}"))
                ->unique()->filter()->implode("\n");

            $targets = [
                'JP' => $jpLines,
                'CUR_TECH' => $bullet('current_technical'),
                'CUR_NON' => $bullet('current_nontechnical'),
                'REQ_TECH' => $bullet('required_technical'),
                'REQ_NON' => $bullet('required_nontechnical'),
                'DEV_TECH' => $bullet('development_technical'),
                'DEV_NON' => $bullet('development_nontechnical'),
            ];

            foreach ($targets as $key => $text) {
                [$c1, $c2] = $RANGES[$key];
                $range = "{$c1}{$top}:{$c2}{$bottom}";

                // pastikan tidak ada merge yang bertabrakan, lalu merge & isi di sel kiri-atas
                $unmergeIntersections($sheet, $range);
                $sheet->mergeCells($range);
                $sheet->setCellValue("{$c1}{$top}", $text);
                $sheet->getStyle($range)->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP);
            }
        }

        // simpan & download
        $filename = 'ICP_' . preg_replace('/[^\w\-]+/u', '_', $employee->name) . '.xlsx';
        $path = storage_path('app/' . $filename);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /* =================== APPROVAL LIST =================== */
    public function approval()
    {
        $me   = auth()->user()->employee;
        $role = ApprovalHelper::roleKeyFor($me);

        $rolesToMatch = [$role];
        // toleransi data lama
        if ($role === 'director') $rolesToMatch[] = 'direktur';
        if ($role === 'president') $rolesToMatch[] = 'presiden';
        if ($role === 'gm') $rolesToMatch[] = 'general manager';

        $steps = IcpApprovalStep::with(['icp.employee', 'icp.steps'])
            ->whereIn('role', $rolesToMatch)
            ->where('status', 'pending')
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
        $me    = auth()->user()->employee;
        $isHRD = auth()->user()->role === 'HRD';
        $role  = ApprovalHelper::roleKeyFor($me);

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
                'status'   => 'done',
                'actor_id' => $me->id,
                'acted_at' => now(),
            ]);

            // Hitung status ringkas ICP
            $remainingChecks   = $icp->steps->where('status', 'pending')->where('type', 'check')->count();
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
                $icp->commentHistory()->create([
                    'comment'     => (string) $request->input('comment', ''),
                    'employee_id' => $emp->id,
                ]);
            }
        });

        return response()->json(['message' => 'ICP has been revised.']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id'   => ['required', 'exists:employees,id'],
            'aspiration'    => ['required', 'string'],
            'career_target' => ['required', 'string'],
            'date'          => ['required', 'date'],

            'stages'                => ['required', 'array', 'min:1'],
            'stages.*.plan_year'    => ['required', 'digits:4', 'numeric', 'distinct'],
            'stages.*.job_function' => ['required', 'string'],
            'stages.*.position'     => ['required', 'string'],
            'stages.*.level'        => ['required', 'string'],
            'stages.*.details'      => ['required', 'array', 'min:1'],

            'stages.*.details.*.current_technical'        => ['required', 'string'],
            'stages.*.details.*.current_nontechnical'     => ['required', 'string'],
            'stages.*.details.*.required_technical'       => ['required', 'string'],
            'stages.*.details.*.required_nontechnical'    => ['required', 'string'],
            'stages.*.details.*.development_technical'    => ['required', 'string'],
            'stages.*.details.*.development_nontechnical' => ['required', 'string'],
        ]);

        // Update data utama ICP
        $icp = Icp::findOrFail($id);

        try {
            DB::beginTransaction();

            $icp->update([
                'employee_id'   => $request->employee_id,
                'aspiration'    => $request->aspiration,
                'career_target' => $request->career_target,
                'date'          => $request->date,
                'status'        => "1",
            ]);
            $this->seedStepsForIcp($icp);

            // Hapus semua detail lama (bisa diubah kalau ingin granular update)
            $icp->details()->delete();

            // sanitasi ringan
            $clean = fn($v, $max = 255) => mb_substr(trim(strip_tags((string) $v)), 0, $max);

            foreach ($request->stages as $stage) {
                $year = (int) $stage['plan_year'];
                $job = $clean($stage['job_function'], 100);
                $pos = $clean($stage['position'], 50);
                $level = $clean($stage['level'], 30);

                $rows = [];
                // Simpan ulang detail baru
                foreach ($stage['details'] as $d) {
                    $rows[] = [
                        'plan_year' => $year,
                        'job_function' => $job,
                        'position' => $pos,
                        'level' => $level,
                        'current_technical' => $clean($d['current_technical']),
                        'current_nontechnical' => $clean($d['current_nontechnical']),
                        'required_technical' => $clean($d['required_technical']),
                        'required_nontechnical' => $clean($d['required_nontechnical']),
                        'development_technical' => $clean($d['development_technical']),
                        'development_nontechnical' => $clean($d['development_nontechnical']),
                    ];
                }

                $icp->details()->createMany($rows);
            }

            DB::commit();

            return redirect()->route('icp.assign')->with('success', 'Data ICP berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui ICP: ' . $e->getMessage());
        }
    }
    public function edit($id)
    {
        $title                 = 'Update ICP';
        $icp                   = Icp::with('details', 'employee')->findOrFail($id);
        $employee              = Employee::findOrFail($icp->employee_id);
        $departments           = Department::where('company', $employee->company_name)->get();
        $employees             = Employee::where('company_name', $employee->company_name)->get();
        $grades                = GradeConversion::all();
        $technicalCompetencies = MatrixCompetency::all();

        // Tambahkan array posisi secara manual atau ambil dari konfigurasi/tabel
        $positions = [
            'Direktur' => 'Direktur',
            'GM' => 'GM',
            'Manager' => 'Manager',
            'Coordinator' => 'Coordinator',
            'Section Head' => 'Section Head',
            'Supervisor' => 'Supervisor',
            'Leader' => 'Leader',
            'JP' => 'JP',
            'Operator' => 'Operator',
        ];

        $now       = Carbon::now();
        $icp->date = $now->format('Y-m-d');

        $stages = $icp->details
            ->groupBy('plan_year')
            ->map(function ($g) {
                $first = $g->first();
                return [
                    'plan_year'    => (int) $first->plan_year,
                    'job_function' => (string) $first->job_function,
                    'position'     => (string) $first->position,
                    'level'        => (string) $first->level,
                    'details'      => $g->map(fn($d) => [
                        'current_technical'        => (string) $d->current_technical,
                        'current_nontechnical'     => (string) $d->current_nontechnical,
                        'required_technical'       => (string) $d->required_technical,
                        'required_nontechnical'    => (string) $d->required_nontechnical,
                        'development_technical'    => (string) $d->development_technical,
                        'development_nontechnical' => (string) $d->development_nontechnical,
                    ])->values()->all(),
                ];
            })
            ->values();

        return view('website.icp.update', compact(
            'title',
            'grades',
            'departments',
            'employees',
            'technicalCompetencies',
            'icp',
            'positions',
            'stages'
        ));
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
        $owner = $icp->employee()->first(); //pemilik icp
        $chain = ApprovalHelper::expectedChainForEmployee($owner);

        $icp->steps()->delete(); //reset jika update
        foreach ($chain as $i => $s) {
            IcpApprovalStep::create([
                'icp_id' => $icp->id,
                'step_order' => $i + 1,
                'type' => $s['type'],
                'role' => $s['role'],
                'label' => $s['label']
            ]);
        }

        //status awal "Submitted" (1) kalau masih ada step; kalau tidak ada -> langsung Approved (3)
        $icp->status = empty($chain) ? 3 : 1;
        $icp->save();
    }
}
