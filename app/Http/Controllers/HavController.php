<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Alc;
use App\Models\Hav;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\HavDetail;
use App\Imports\HavImport;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\SubSection;
use App\Models\HavQuadrant;
use App\Models\KeyBehavior;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\AstraTraining;
use App\Models\QuadranMaster;
use App\Exports\HavSummaryExport;
use App\Models\HavCommentHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\HavDetailKeyBehavior;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PerformanceAppraisalHistory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class HavController extends Controller
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($company = null)
    {
        $title = 'Employee List';
        $user = auth()->user();

        $titles = [
            13 => 'Maximal Contributor',
            7  => 'Top Performer',
            3  => 'Future Star',
            1  => 'Star',
            14 => 'Contributor',
            8  => 'Strong Performer',
            4  => 'Potential Candidate',
            2  => 'Future Star',
            15 => 'Minimal Contributor',
            9  => 'Career Person',
            6  => 'Candidate',
            5  => 'Raw Diamond',
            16 => 'Dead Wood',
            12 => 'Problem Employee',
            11 => 'Unfit Employee',
            10 => 'Most Unfit Employee',
        ];

        if ($user->isHRDorDireksi()) {
            $havGrouped = HavQuadrant::whereHas('hav', function (Builder $query) {
                $query->where('status', 2); // Filter dari tabel havs
            })
                ->whereHas('employee', function ($query) use ($company) {
                    $query->where('company_name', $company);
                })
                ->with('employee')
                ->get()
                ->groupBy('quadrant');
        } else {
            $subordinates = auth()->user()->subordinate()->unique()->values();

            $havGrouped = HavQuadrant::whereIn('employee_id', $subordinates)
                ->whereHas('hav', function (Builder $query) {
                    $query->where('status', 2);
                })
                ->with('employee', 'employee.departments')
                ->get()
                ->groupBy('quadrant');
        }

        $orderedHavGrouped = collect(array_keys($titles))->mapWithKeys(function ($quadrantId) use ($havGrouped) {
            return [$quadrantId => $havGrouped[$quadrantId] ?? collect()];
        });

        $positions = Employee::select('position')->distinct()->pluck('position')->filter()->values();

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

        $visiblePositions = $positionIndex !== false
            ? array_slice($allPositions, $positionIndex)
            : [];

        return view('website.hav.index', compact(
            'orderedHavGrouped',
            'titles',
            'positions',
            'visiblePositions'
        ));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request, $company = null)
    {
        $title = 'HAV List';
        $user = auth()->user();
        $filter = $request->input('filter', 'all');
        $search = $request->input('search'); // ambil input search dari request

        // Ambil npk dari query string
        $npk = $request->query('npk'); // Jika npk ada di query string

        if ($npk) {
            // Jika ada npk, tampilkan hanya data untuk npk tersebut

            $employees = Hav::with(['employee', 'quadran'])

                ->whereHas('employee', function ($query) use ($npk, $filter, $search) {
                    $query->where('npk', $npk); // Filter berdasarkan npk
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

                ->get()
                ->unique('employee_id')
                ->values();
        } else {
            // Logika untuk HRD atau selain HRD
            if ($user->isHRDorDireksi()) {
                $employees = Hav::with(['employee', 'quadran'])
                    ->whereIn('id', function ($query) use ($company, $filter, $search) {
                        $query->selectRaw('MAX(h.id)')
                            ->from('havs as h')
                            ->join('employees as e', 'e.id', '=', 'h.employee_id')
                            ->when($company, function ($q) use ($company) {
                                $q->where('e.company_name', $company);
                            })
                            ->when($filter && $filter !== 'all', function ($q) use ($filter) {
                                $q->where(function ($q2) use ($filter) {
                                    $q2->where('e.position', $filter)
                                        ->orWhere('e.position', 'like', "Act %{$filter}");
                                });
                            })
                            ->when($search, function ($q) use ($search) {
                                $q->where(function ($q2) use ($search) {
                                    $q2->where('e.name', 'like', '%' . $search . '%')
                                        ->orWhere('e.npk', 'like', '%' . $search . '%');
                                });
                            })
                            ->groupBy('h.employee_id');
                    })
                    ->get();
            } else {
                $employee = Employee::where('user_id', $user->id)->first();

                if (!$employee) {
                    $employees = collect();
                } else {
                    $subordinate = $this->getSubordinatesFromStructure($employee)->pluck('id');

                    $employees = Hav::with(['employee', 'quadran'])
                        ->whereIn('id', function ($query) use ($subordinate, $filter, $search) {
                            $query->selectRaw('MAX(h.id)')
                                ->from('havs as h')
                                ->join('employees as e', 'e.id', '=', 'h.employee_id')
                                ->whereIn('h.employee_id', $subordinate)
                                ->when($filter && $filter !== 'all', function ($q) use ($filter) {
                                    $q->where(function ($q2) use ($filter) {
                                        $q2->where('e.position', $filter)
                                            ->orWhere('e.position', 'like', "Act %{$filter}");
                                    });
                                })
                                ->when($search, function ($q) use ($search) {
                                    $q->where('e.name', 'like', '%' . $search . '%');
                                })
                                ->groupBy('h.employee_id');
                        })
                        ->get();
                }
            }
        }

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

        foreach ($employees as $employee) {
            $code = (int) ($employee->quadran->code ?? 0);
            if ($code >= 10 && $code <= 16) {
                $employee->bg_color = 'bg-light-secondary';
            } elseif ($code >= 5 && $code <= 9) {
                $employee->bg_color = 'bg-light-warning';
            } elseif ($code >= 2 && $code <= 4) {
                $employee->bg_color = 'bg-light-warning';
            } elseif ($code === 1) {
                $employee->bg_color = 'bg-light-primary';
            } else {
                $employee->bg_color = 'bg-light-secondary'; // Default
            }
        }

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

        return view('website.hav.list', compact('title', 'employees', 'filter', 'company', 'search', 'visiblePositions'));
    }

    public function assign(Request $request, $company = null)
    {
        $title = 'Assign HAV';
        $user = auth()->user();
        $employee = $user->employee;
        $filter = $request->input('filter', 'all');
        $search = $request->input('search');
        $normalized = $employee->getNormalizedPosition();

        $allPositions = ['Direktur', 'GM', 'Manager', 'Coordinator', 'Section Head', 'Supervisor', 'Leader', 'JP', 'Operator'];
        $rawPosition = $employee->position ?? 'Operator';
        $currentPosition = Str::startsWith($rawPosition, 'Act ')
            ? trim(Str::after($rawPosition, 'Act '))
            : $rawPosition;
        $positionIndex = array_search($currentPosition, $allPositions, true);
        $visiblePositions = $positionIndex !== false ? array_slice($allPositions, $positionIndex) : $allPositions;

        $approvallevel = $employee->getCreateAuth();
        $subordinateIds = $employee->getSubordinatesByLevel($approvallevel)->pluck('id')->toArray();

        $subordinates = Employee::whereIn('id', $subordinateIds)
            ->when($company, fn($q) => $q->where('company_name', $company))
            ->when($filter && $filter !== 'all', function ($q) use ($filter) {
                $q->where(function ($sub) use ($filter) {
                    $sub->where('position', $filter)
                        ->orWhere('position', 'like', "Act %{$filter}");
                });
            })
            ->when($search, fn($q) => $q->where('name', 'like', '%' . $search . '%'))
            ->with([
                'hav' => function ($q) {
                    $q->orderByDesc('created_at')
                        ->with(['details', 'commentHistory']);
                }
            ])
            ->get();

        $employees = $subordinates->map(function ($emp) {
            $latestHav = $emp->hav ? $emp->hav->first() : null;

            $allowAdd = false;
            $virtualStatus = optional($latestHav)->status;
            $badgeClass = 'badge-light';

            if ($latestHav && (int)$latestHav->status === 2) {
                $addAfter = Carbon::parse($latestHav->created_at)->addYear(); // perbaikan created_at
                if (now()->gte($addAfter)) {
                    $allowAdd = true;
                    $virtualStatus = null;
                }
            }

            $statusRaw = optional($latestHav)->status;
            $isAssessmentOne = $latestHav && $latestHav->details->contains('is_assessment', 1);

            $statusText = $this->setBadgeStatus($statusRaw, $latestHav, $isAssessmentOne);
            $approver = null;
            $level = 0;

            if ($statusText === 'Submitted') {
                $level = (int) $emp->getCreateAuth() + 1;
            } elseif ($statusText === 'Approved' || str_starts_with($statusText, 'Approved (Expired)')) {
                $level = (int) $emp->getCreateAuth() + 1;
            }

            if ($level > 0) {
                $sup = $emp->getSuperiorsByLevel($level)->last();
                $approver = $sup->position ?? null;
            }

            if ($approver) {
                if ($statusText === 'Submitted') {
                    $statusText = 'Checking by ' . $approver;
                } elseif ($statusText === 'Approved') {
                    $statusText = 'Approved by ' . $approver;
                } elseif (str_starts_with($statusText, 'Approved (Expired)')) {

                    $statusText = 'Approved (Expired) by ' . $approver;
                }
            }
            $badgeMap = [
                'Submitted'                   => 'badge-light-primary',
                'Checking'                    => 'badge-light-warning',
                'Revised'                     => 'badge-light-danger',
                'Approved'                    => 'badge-light-success',
                'Approved by'                 => 'badge-light-success',
                'Approved (Expired)'          => 'badge-light-dark',
                'Approved (Expired) by'       => 'badge-light-dark',
                'Not Created'                 => 'badge-light',
                '-'                           => 'badge-light',
            ];

            $badgeClass = 'badge-light';
            foreach ($badgeMap as $key => $class) {
                if ($statusText === $key || str_starts_with($statusText, $key . ' ')) {
                    $badgeClass = $class;
                    break;
                }
            }

            $showAdd = (!$latestHav) || $allowAdd || ($latestHav && !$allowAdd && (int)$isAssessmentOne === 1);
            $showRevise = ($latestHav && (int)$isAssessmentOne === 0 && (int)$latestHav->status == 1);

            $checkLevel  = $emp?->getFirstApproval();
            $subCheck   = $emp->getSubordinatesByLevel($checkLevel)->pluck('id')->toArray();

            return (object) [
                'employee' => $emp,
                'hav' => $latestHav,
                'allowAdd' => $allowAdd,
                'virtualStatus' => $virtualStatus,

                'status_text' => $statusText,
                'badge_class' => $badgeClass,

                'show_add' => $showAdd,
                'show_revise' => $showRevise,
                'revise_hav_id' => $latestHav?->id,
            ];
        });

        return view('website.hav.assign', compact('title', 'employees', 'filter', 'company', 'search', 'visiblePositions'));
    }

    public function ajaxList(Request $request)
    {
        $data = Hav::with('employee')->get(); // Pastikan relasi 'employee' ada

        return DataTables::of($data)
            ->addColumn('npk', fn($row) => $row->employee->npk ?? '-')
            ->addColumn('nama', fn($row) => $row->employee->name ?? '-')
            ->addColumn('status', fn($row) => $row->status)
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateCreate($id)
    {
        return redirect()->route('hav.update', $id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listCreate()
    {
        $title = 'Add Employee';
        $employees = Assessment::with('employee')
            ->whereHas('employee', function ($query) {
                $query->where('company_name', 'AII');
            })
            ->get();

        return view('website.hav.list-create', compact('title', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $title = 'Add Employee';
        $hav = Hav::with(['employee', 'details'])
            ->whereHas('employee', function ($query) use ($id) {
                return $query->where('id', $id);
            })
            ->first();

        $performanceAppraisals = PerformanceAppraisalHistory::with('employee')
            ->whereHas('employee', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->orderBy('date', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->limit(3) // Ambil hanya 3 data terbaru
            ->get();

        return view('website.hav.create', compact('title', 'hav', 'performanceAppraisals'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRating(Request $request)
    {
        $request->validate([
            'key_behavior_id' => 'required|exists:hav_detail_key_behaviors,key_behavior_id',
            'hav_detail_id' => 'required|exists:hav_detail_key_behaviors,hav_detail_id',
            'rating' => 'required|numeric|min:1|max:5'
        ]);

        $ratingUpdate = HavDetailKeyBehavior::where([
            'key_behavior_id' => $request->key_behavior_id,
            'hav_detail_id' => $request->hav_detail_id
        ])->first();

        if ($ratingUpdate) {
            $ratingUpdate->score = $request->rating;
            $ratingUpdate->save();

            // ✅ Hitung rata-rata dari semua score HavDetailKeyBehavior yang terkait dengan HavDetail ini
            $averageScore = HavDetailKeyBehavior::where('hav_detail_id', $request->hav_detail_id)
                ->avg('score'); // Menghitung rata-rata nilai

            // ✅ Update score di HavDetail dengan nilai rata-rata
            HavDetail::where('id', $request->hav_detail_id)
                ->update(['score' => $averageScore]);

            return response()->json([
                'success' => true,
                'message' => 'Rating updated successfully',
                'new_average' => floatval($averageScore) // Kirim rata-rata terbaru ke frontend
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Record not found'], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        try {
            $position = $request->input('position');
            $templatePath = public_path('assets/file/HAV_Summary.xls');
            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            $user = auth()->user();
            $role = strtolower($user->employee->position); // pastikan lowercase atau sesuai penulisan
            $hrd = strtolower($user->role);

            if ($hrd === 'hrd') {
                // HRD bisa akses semua data
                $subordinates = Employee::pluck('id');
            } elseif (in_array($role, ['president', 'vpd'])) {
                // Role VPD atau President (berdasarkan jabatan di employee)
                $subordinates = Employee::pluck('id');
            } else {
                // Role biasa hanya lihat bawahannya
                $subordinates = $this->getSubordinatesFromStructure($user->employee)->pluck('id');
            }
            $employees = Employee::with([
                'departments',
                'assessments.details',
                'havQuadrants' => fn($q) => $q->orderByDesc('created_at'),
                'performanceAppraisalHistories' => fn($q) => $q->orderBy('date')->with('masterPerformance')
            ])
                ->whereHas('havQuadrants')
                ->when($position, function ($q) use ($position) {
                    $q->where(function ($subQuery) use ($position) {
                        $subQuery->where('position', $position)
                            ->orWhere('position', 'like', "Act %{$position}");
                    });
                })
                ->whereIn('id', $subordinates)->get();

            $startRow = 13;
            $sheet->setCellValue("C6", auth()->user()->employee->name);
            $sheet->setCellValue("C7", date('d-m-Y H:i:s'));

            foreach ($employees->sortBy('name')->values() as $i => $emp) {
                $row = $startRow + $i;
                $assessment = $emp->hav->sortByDesc('created_at')->first();
                $details = $assessment ? $assessment->details->keyBy('alc_id') : collect();

                // Total Score
                $weights = [
                    1 => 0.15,
                    2 => 0.15,
                    3 => 0.10,
                    4 => 0.10,
                    5 => 0.10,
                    6 => 0.15,
                    7 => 0.10,
                    8 => 0.15,
                ];

                $totalScore = 0;
                foreach ($weights as $alcId => $weight) {
                    $score = floatval($details[$alcId]->score ?? 0);
                    $totalScore += $score * $weight;
                }
                $totalScorePercent = $totalScore ? $totalScore / 5 : '0';

                // HAV Quadrant
                $hav = $emp->havQuadrants->first();
                $quadrant = $hav->quadrant ?? null;

                // Performance Appraisal 3 tahun terakhir
                $appraisals = $emp->performanceAppraisalHistories
                    ->sortByDesc('date')
                    ->take(3)
                    ->sortBy('date')
                    ->values();

                // Mapping kolom (kolom D/Function dihapus, semua kolom setelahnya digeser ke kiri)
                $sheet->setCellValue("A{$row}", $i + 1);
                $sheet->setCellValue("B{$row}", $emp->npk);
                $sheet->setCellValue("C{$row}", $emp->name);
                // Kolom D (Function) dihapus
                if (!$emp->division) {
                    Log::warning("Division NULL untuk employee: {$emp->name} (ID: {$emp->id}), division_id: {$emp->division_id}");
                }

                $sheet->setCellValue("D{$row}", $emp->division?->name); // Divisi (sebelumnya E)
                $sheet->setCellValue("E{$row}", $emp->departments->pluck('name')->implode(', ')); // Departemen (sebelumnya F)
                $sheet->setCellValue("F{$row}", Carbon::parse($emp->birthday_date)->age ?? null); // Usia (sebelumnya G)
                $sheet->setCellValue("G{$row}", $emp->grade); // Sub Gol (sebelumnya H)
                $sheet->setCellValue("H{$row}", $emp->working_period); // Masa kerja (sebelumnya I)

                // ALCs (kolom dimulai dari I sekarang, sebelumnya J)
                $col = 'I';
                for ($j = 1; $j <= 8; $j++) {
                    $sheet->setCellValue("{$col}{$row}", $details[$j]->score ?? '-');
                    $col++;
                }

                $sheet->setCellValue("Q{$row}", $totalScore); // sebelumnya R
                $sheet->setCellValue("R{$row}", $totalScorePercent); // sebelumnya S

                $formulaS = "=IF(R{$row}>=0.71,\"C3\",IF(R{$row}>=0.61,\"C2\",IF(R{$row}>=0.5,\"C1\",IF(R{$row}<0.5,\"C0\",0))))"; // sebelumnya T
                $sheet->setCellValue("S{$row}", $formulaS);

                $sheet->setCellValue("T10", isset($appraisals[0]) ? substr($appraisals[0]->date, 0, 4) : '-'); // sebelumnya U11
                $sheet->setCellValue("U10", isset($appraisals[1]) ? substr($appraisals[1]->date, 0, 4) : '-'); // sebelumnya V11
                $sheet->setCellValue("V10", isset($appraisals[2]) ? substr($appraisals[2]->date, 0, 4) : '-'); // sebelumnya W11
                $sheet->setCellValue("W10", isset($appraisals[0]) ? substr($appraisals[0]->date, 0, 4) : '-'); // sebelumnya X11
                $sheet->setCellValue("X10", isset($appraisals[1]) ? substr($appraisals[1]->date, 0, 4) : '-'); // sebelumnya Y11
                $sheet->setCellValue("Y10", isset($appraisals[2]) ? substr($appraisals[2]->date, 0, 4) : '-'); // sebelumnya Z11

                $sheet->setCellValue("T{$row}", isset($appraisals[0]) ? $appraisals[0]->score : '-'); // sebelumnya U
                $sheet->setCellValue("U{$row}", isset($appraisals[1]) ? $appraisals[1]->score : '-'); // sebelumnya V
                $sheet->setCellValue("V{$row}", isset($appraisals[2]) ? $appraisals[2]->score : '-'); // sebelumnya W

                $sheet->setCellValue("W{$row}", isset($appraisals[0]) && $appraisals[0]->masterPerformance ? $appraisals[0]->masterPerformance->score : '-'); // sebelumnya X
                $sheet->setCellValue("X{$row}", isset($appraisals[1]) && $appraisals[1]->masterPerformance ? $appraisals[1]->masterPerformance->score : '-'); // sebelumnya Y
                $sheet->setCellValue("Y{$row}", isset($appraisals[2]) && $appraisals[2]->masterPerformance ? $appraisals[2]->masterPerformance->score : '-'); // sebelumnya Z

                // Gunakan optional() hanya jika objek $appraisals[0] aman diakses
                $score1 = isset($appraisals[0]) ? optional($appraisals[0]->masterPerformance)->score : null;
                $score2 = isset($appraisals[1]) ? optional($appraisals[1]->masterPerformance)->score : null;
                $score3 = isset($appraisals[2]) ? optional($appraisals[2]->masterPerformance)->score : null;

                $scores = array_filter([$score1, $score2, $score3], fn($s) => $s !== null);

                $avgScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : '-';
                $sheet->setCellValue("Z{$row}", $avgScore); // sebelumnya AA

                $formulaAA = "=IF(Z{$row}>=21,\"R3\",IF(Z{$row}>=16,\"R2\",IF(Z{$row}>=12,\"R1\",IF(Z{$row}>=1,\"R0\",0))))"; // sebelumnya AB
                $sheet->setCellValue("AA{$row}", $formulaAA);

                $formulaAB = '=IF(OR(T' . $row . '="C",T' . $row . '="C+",T' . $row . '="K",U' . $row . '="C",U' . $row . '="C+",U' . $row . '="K",V' . $row . '="C",V' . $row . '="C+",V' . $row . '="K"),"R0",AA' . $row . ')'; // sebelumnya AC
                $sheet->setCellValue("AB{$row}", $formulaAB);

                // HAV terakhir
                $sheet->setCellValue("AC{$row}", QuadranMaster::where('code', $quadrant)->first()->name ?? '-'); // Quadrant (sebelumnya AD)

                $getLastAssessment = $emp->assessments->sortByDesc('date')->first();

                $withWeakness = $getLastAssessment?->details->filter(fn($item) => !empty($item['weakness']))
                    ->values() // reset index agar rapi
                    ->take(8);
                $withStrength = $getLastAssessment?->details->filter(fn($item) => !empty($item['strength']))
                    ->values() // reset index agar rapi
                    ->take(8);

                $sheet->setCellValue("AD{$row}", $withStrength[0]->alc->name ?? '-'); // sebelumnya AE
                $sheet->setCellValue("AE{$row}", $withStrength[1]->alc->name ?? '-'); // sebelumnya AF
                $sheet->setCellValue("AF{$row}", $withStrength[2]->alc->name ?? '-'); // sebelumnya AG
                $sheet->setCellValue("AG{$row}", $withStrength[3]->alc->name ?? '-'); // sebelumnya AH
                $sheet->setCellValue("AH{$row}", $withStrength[4]->alc->name ?? '-'); // sebelumnya AI
                $sheet->setCellValue("AI{$row}", $withStrength[5]->alc->name ?? '-'); // sebelumnya AJ
                $sheet->setCellValue("AJ{$row}", $withStrength[6]->alc->name ?? '-'); // sebelumnya AK
                $sheet->setCellValue("AK{$row}", $withStrength[7]->alc->name ?? '-'); // sebelumnya AL

                $sheet->setCellValue("AL{$row}", $withWeakness[0]->alc->name ?? '-'); // sebelumnya AM
                $sheet->setCellValue("AM{$row}", $withWeakness[1]->alc->name ?? '-'); // sebelumnya AN
                $sheet->setCellValue("AN{$row}", $withWeakness[2]->alc->name ?? '-'); // sebelumnya AO
                $sheet->setCellValue("AO{$row}", $withWeakness[3]->alc->name ?? '-'); // sebelumnya AP
                $sheet->setCellValue("AP{$row}", $withWeakness[4]->alc->name ?? '-'); // sebelumnya AQ
                $sheet->setCellValue("AQ{$row}", $withWeakness[5]->alc->name ?? '-'); // sebelumnya AR
                $sheet->setCellValue("AR{$row}", $withWeakness[6]->alc->name ?? '-'); // sebelumnya AS
                $sheet->setCellValue("AS{$row}", $withWeakness[7]->alc->name ?? '-'); // sebelumnya AT

                $getLast3AstraTraining = AstraTraining::where('employee_id', $emp->id)
                    ->orderBy('created_at', 'desc')
                    ->take(4)
                    ->get();

                $sheet->setCellValue("AT{$row}", $getLast3AstraTraining[0]->program ?? '-'); // sebelumnya AU
                $sheet->setCellValue("AU{$row}", $getLast3AstraTraining[1]->program ?? '-'); // sebelumnya AV
                $sheet->setCellValue("AV{$row}", $getLast3AstraTraining[2]->program ?? '-'); // sebelumnya AW
                $sheet->setCellValue("AW{$row}", $getLast3AstraTraining[3]->program ?? '-'); // sebelumnya AX

                $getLastHav = Hav::where('employee_id', $emp->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $sheet->setCellValue("AX{$row}", $getLastHav->details[0]->evidence ?? '-'); // sebelumnya AY
                $sheet->setCellValue("AY{$row}", $getLastHav->details[1]->evidence ?? '-'); // sebelumnya AZ
                $sheet->setCellValue("AZ{$row}", $getLastHav->details[2]->evidence ?? '-'); // sebelumnya BA
                $sheet->setCellValue("BA{$row}", $getLastHav->details[3]->evidence ?? '-'); // sebelumnya BB
                $sheet->setCellValue("BB{$row}", $getLastHav->details[4]->evidence ?? '-'); // sebelumnya BC
                $sheet->setCellValue("BC{$row}", $getLastHav->details[5]->evidence ?? '-'); // sebelumnya BD
                $sheet->setCellValue("BD{$row}", $getLastHav->details[6]->evidence ?? '-'); // sebelumnya BE
                $sheet->setCellValue("BE{$row}", $getLastHav->details[7]->evidence ?? '-'); // sebelumnya BF
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $filename = 'HAV_Summary_Exported.xlsx';
            $writer->save(public_path($filename));

            return response()->download(public_path($filename))->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            // Log error detail
            Log::error('HAV Export Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Redirect atau response error user-friendly
            return back()->with('error', 'Terjadi kesalahan saat mengekspor data. Silakan coba lagi.');
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function exportassign($id)
    {
        $templatePath = public_path('assets/file/Import-HAV.xls');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $employees = Employee::with('departments')->find($id);
        $sheet->setCellValue("C6", $employees->name);
        $sheet->setCellValue("C7", $employees->npk);
        $sheet->setCellValue("C8", $employees->grade);
        $sheet->setCellValue("C9", $employees->company_name);
        $sheet->setCellValue("C10", $employees?->department?->name);
        $sheet->setCellValue("C11", $employees->position);
        $sheet->setCellValue("C13", date('Y'));

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'HAV_Template_' . $employees->name . '_' . date('Y') . '.xlsx';
        $writer->save(public_path($filename));

        return response()->download(public_path($filename))->deleteFileAfterSend(true);

        // return Excel::download(new HavSummaryExport, 'HAV_Summary_Exported.xlsx');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        $havId = $request->input('hav_id');

        try {
            // Handle file upload here in the controller
            $file = $request->file('file');
            $fileName = 'hav_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('/hav_uploads', $fileName);

            // Pass the file path to the import class
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\HavImport($filePath, $havId), $file);

            return back()->with('success', 'Import HAV berhasil.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
        return redirect()->back();
    }
    public function downloadLatestUpload($havId)
    {
        try {
            $latestUpload = DB::table('hav_comment_histories')
                ->where('hav_id', $havId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$latestUpload || !$latestUpload->upload) {
                Log::warning("Download gagal: Tidak ada upload untuk HAV ID: $havId");
                return abort(404, 'File upload tidak ditemukan.');
            }

            $filePath = $latestUpload->upload;

            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->download($filePath);
            } elseif (Storage::disk('local')->exists($filePath)) {
                Log::info("File ditemukan di disk lokal untuk HAV ID: $havId");
                return Storage::disk('local')->download($filePath);
            } else {
                Log::warning("Download gagal: File tidak ditemukan di public maupun local untuk HAV ID: $havId, path: $filePath");
                return abort(404, 'File tidak ditemukan di storage.');
            }
        } catch (\Exception $e) {
            Log::error("Terjadi error saat download file HAV ID: $havId. Pesan: " . $e->getMessage(), [
                'hav_id' => $havId,
                'trace' => $e->getTraceAsString(),
            ]);
            return abort(500, 'Terjadi kesalahan saat mengunduh file.');
        }
    }

    public function approval(Request $request, $company = null)
    {
        $company = $request->query('company');

        $title = 'Add Employee';
        $user = auth()->user();
        $filter = $request->input('filter', 'all');
        $search = $request->input('search');

        if ($user->role === 'HRD') {
            $employees = Hav::with('employee')
                ->whereIn('status', [0, 1])
                ->whereHas('employee', function ($query) use ($company, $filter, $search) {
                    if ($company) {
                        $query->where('company_name', $company);
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
        } else {
            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee) {
                $employees = collect();
            } else {

                $approvallevel = $employee->getFirstApproval();
                $subordinate   = $employee->getSubordinatesByLevel($approvallevel)->pluck('id');
                // dd($approvallevel);
                $employees = Hav::with('employee')
                    ->select('havs.*', 'havs.status as hav_status')
                    ->whereIn('employee_id', $subordinate)
                    ->whereIn('status', [0, 1])
                    ->get();
            }
        }


        return view('website.approval.hav.index', compact('title', 'employees', 'filter', 'company', 'search'));
    }
    public function approve(Request $request, $id)
    {
        $hav = Hav::findOrFail($id);
        $hav->status = 2;

        $comment = $request->input('comment');
        $employee = auth()->user()->employee;

        $filePath = null;
        $latestComment = $hav->commentHistory()->latest()->first();

        if ($latestComment && $latestComment->upload) {
            $filePath = $latestComment->upload; // Jangan pakai public_path
        }

        if ($employee) {
            $hav->commentHistory()->create([
                'comment' => $comment,
                'employee_id' => $employee->id,
                'upload' => $filePath, // Langsung simpan relative path-nya
            ]);
        }

        $hav->save();
        return response()->json(['message' => 'Data berhasil disetujui.']);
    }

    public function reject(Request $request, $id)
    {
        $hav = Hav::findOrFail($id);
        $hav->status = 1;

        $comment = $request->input('comment');
        $employee = auth()->user()->employee;

        $filePath = null;
        $latestComment = $hav->commentHistory()->latest()->first();

        if ($latestComment && $latestComment->upload) {
            $filePath = $latestComment->upload; // Langsung ambil relative path
        }

        if ($employee) {
            $hav->commentHistory()->create([
                'comment' => $comment,
                'employee_id' => $employee->id,
                'upload' => $filePath,
            ]);
        }

        $hav->save();
        return response()->json(['message' => 'Data berhasil disetujui.']);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = Employee::with([
            'hav' => function ($query) {
                $query->with([
                    'details' => function ($q) {
                        $q->orderBy('created_at', 'desc'); // urutkan detail dari yang terbaru
                    }
                ]);
            }
        ])->find($id);

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        return response()->json([
            'employee' => $employee
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get3LastPerformance($id, $year)
    {
        $performanceAppraisals = Employee::getLast3Performance($id, $year);
        if ($performanceAppraisals->isEmpty()) {
            return response()->json([
                'error' => true,
                'msg' => 'No performance appraisals found for this employee'
            ], 404);
        }
        return response()->json([
            'performanceAppraisals' => $performanceAppraisals
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $hav = Hav::findOrFail($id);

        HavDetail::where('hav_id', $id)->delete();

        $hav->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hav berhasil dihapus.'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getComment($hav_id)
    {
        $hav = HavCommentHistory::where('hav_id', $hav_id)->with('employee')->get();
        if (!$hav) {
            return response()->json([
                'error' => true,
                'msg' => 'No comment found for this employee'
            ], 404);
        }
        $lastUpload = HavCommentHistory::where('hav_id', $hav_id)
            ->orderByDesc('created_at')
            ->first();
        return response()->json([
            'comment' => $hav,
            'lastUpload' => $lastUpload,
            'hav' => ['id' => $hav_id],
        ]);
    }

    /**
     * PRIVATE FUNCTION
     */

    private function setBadgeStatus($statusRaw, $latestHav, $isAssessmentOne)
    {
        $baseText = match ((int) $statusRaw) {
            0 => 'Submitted',
            1 => 'Revised',
            2 => 'Approved',
            3 => 'Not Created',
            default => '-',
        };

        if ((int)$statusRaw === 0 && $isAssessmentOne) {
            return 'Not Created';
        } elseif ((int)$statusRaw === 2 && $latestHav && Carbon::parse($latestHav->created_at)->addYear()->isPast()) {
            return 'Approved (Expired)';
        }

        return $baseText;
    }
}
