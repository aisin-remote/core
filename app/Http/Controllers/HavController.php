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
        if ($user->role === 'HRD') {
            $havGrouped = HavQuadrant::whereHas('employee', function ($query) use ($company) {
                $query->where('company_name', $company);
            })->with('employee')->get()->groupBy('quadrant');

            // Quadrant ID => Judul
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

            $orderedHavGrouped = collect(array_keys($titles))->mapWithKeys(function ($quadrantId) use ($havGrouped) {
                return [$quadrantId => $havGrouped[$quadrantId] ?? collect()];
            });
        } else {

            $subordinates = auth()->user()->subordinate()->unique()->values();

            $havGrouped = HavQuadrant::whereIn('employee_id', $subordinates)->with('employee', 'employee.departments')->get()->groupBy('quadrant');

            // Quadrant ID => Judul
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

            $orderedHavGrouped = collect(array_keys($titles))->mapWithKeys(function ($quadrantId) use ($havGrouped) {
                return [$quadrantId => $havGrouped[$quadrantId] ?? collect()];
            });
        }
        $positions = Employee::select('position')->distinct()->pluck('position')->filter()->values();

        return view('website.hav.index', compact('orderedHavGrouped', 'titles', 'positions'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request, $company = null)
    {
        $title = 'Add Employee';
        $user = auth()->user();
        $filter = $request->input('filter', 'all');
        $search = $request->input('search'); // ambil input search dari request

        // Ambil npk dari query string
        $npk = $request->query('npk'); // Jika npk ada di query string

        if ($npk) {
            // Jika ada npk, tampilkan hanya data untuk npk tersebut

            $employees = Hav::with('employee')

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
            if ($user->role === 'HRD') {
                $employees = Hav::with('employee')
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
                    ->get()
                    ->unique('employee_id')
                    ->values();
            } else {
                $employee = Employee::where('user_id', $user->id)->first();

                if (!$employee) {
                    $employees = collect();
                } else {
                    $subordinate =  $this->getSubordinatesFromStructure($user->employee)->pluck('id');

                    $employees = Hav::with('employee')
                        ->whereIn('employee_id', $subordinate)
                        ->whereHas('employee', function ($query) use ($filter, $search, $employee) {

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
                }
            }
        }


        // Daftar posisi
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

        return view('website.hav.list', compact('title', 'employees', 'filter', 'company', 'search', 'visiblePositions'));
    }

    public function assign(Request $request, $company = null)
    {
        $title = 'Assign HAV';
        $user = auth()->user();
        $employee = $user->employee;
        $filter = $request->input('filter', 'all');
        $search = $request->input('search');

        // Posisi yang terlihat
        $allPositions = ['Direktur', 'GM', 'Manager', 'Coordinator', 'Section Head', 'Supervisor', 'Leader', 'JP', 'Operator'];
        $rawPosition = $employee->position ?? 'Operator';
        $currentPosition = Str::contains($rawPosition, 'Act ') ? trim(str_replace('Act', '', $rawPosition)) : $rawPosition;
        $positionIndex = array_search($currentPosition, $allPositions);
        $visiblePositions = $positionIndex !== false ? array_slice($allPositions, $positionIndex) : [];

        // Ambil subordinate berdasarkan level otorisasi
        $approvallevel = $employee->getCreateAuth();
        $subordinateIds = $employee->getSubordinatesByLevel($approvallevel)->pluck('id');

        // Ambil semua subordinate (filtered)
        $subordinates = Employee::whereIn('id', $subordinateIds)
            ->when($company, fn($q) => $q->where('company_name', $company))
            ->when($filter && $filter !== 'all', function ($q) use ($filter) {
                $q->where(function ($sub) use ($filter) {
                    $sub->where('position', $filter)
                        ->orWhere('position', 'like', "Act %{$filter}");
                });
            })
            ->when($search, fn($q) => $q->where('name', 'like', '%' . $search . '%'))
            ->with(['hav' => function ($q) {
                $q->orderByDesc('created_at') // urutkan biar first() dapat yang terbaru
                    ->with(['details', 'commentHistory']);
            }])
            ->get();
        $employees = $subordinates->map(function ($emp) {
            $latestHav = $emp->hav ? $emp->hav->first() : null;
            return (object)[
                'employee' => $emp,
                'hav' => $latestHav,
            ];
        });

        return view('website.hav.assign', compact('title', 'employees', 'filter', 'company', 'search', 'visiblePositions'));
    }





    // public function assign(Request $request, $company = null)
    // {
    //     $title = 'Add Employee';
    //     $user = auth()->user();
    //     $filter = $request->input('filter', 'all');
    //     $search = $request->input('search'); // ambil input search dari request

    //     // Ambil npk dari query string
    //     $npk = $request->query('npk'); // Jika npk ada di query string

    //     if ($npk) {
    //         // Jika ada npk, tampilkan hanya data untuk npk tersebut

    //         $employees = Hav::with('employee')

    //             ->whereHas('employee', function ($query) use ($npk, $filter, $search) {
    //                 $query->where('npk', $npk); // Filter berdasarkan npk
    //                 if ($filter && $filter !== 'all') {
    //                     $query->where(function ($q) use ($filter) {
    //                         $q->where('position', $filter)
    //                             ->orWhere('position', 'like', "Act %{$filter}");
    //                     });
    //                 }
    //                 if ($search) {
    //                     $query->where('name', 'like', '%' . $search . '%');
    //                 }
    //             })

    //             ->get()
    //             ->unique('employee_id')
    //             ->values();
    //     } else {
    //         // Logika untuk HRD atau selain HRD
    //         if ($user->role === 'HRD') {
    //             $employees = Hav::with('employee')
    //                 ->whereHas('employee', function ($query) use ($company, $filter, $search) {
    //                     if ($company) {
    //                         $query->where('company_name', $company);
    //                     }
    //                     if ($filter && $filter !== 'all') {
    //                         $query->where(function ($q) use ($filter) {
    //                             $q->where('position', $filter)
    //                                 ->orWhere('position', 'like', "Act %{$filter}");
    //                         });
    //                     }
    //                     if ($search) {
    //                         $query->where('name', 'like', '%' . $search . '%');
    //                     }
    //                 })
    //                 ->get()
    //                 ->unique('employee_id')
    //                 ->values();
    //         } else {
    //             $employee = Employee::where('user_id', $user->id)->first();

    //             if (!$employee) {
    //                 $employees = collect();
    //             } else {
    //                 $approvallevel = (auth()->user()->employee->getCreateAuth());
    //                 $subordinate =  auth()->user()->employee->getSubordinatesByLevel($approvallevel)->pluck('id');

    //                 $employees = Hav::with('employee')
    //                     ->whereIn('employee_id', $subordinate)
    //                     ->whereHas('employee', function ($query) use ($filter, $search, $employee) {

    //                         if ($filter && $filter !== 'all') {
    //                             $query->where(function ($q) use ($filter) {
    //                                 $q->where('position', $filter)
    //                                     ->orWhere('position', 'like', "Act %{$filter}");
    //                             });
    //                         }
    //                         if ($search) {
    //                             $query->where('name', 'like', '%' . $search . '%');
    //                         }
    //                     })
    //                     ->get()
    //                     ->unique('employee_id')
    //                     ->values();
    //             }
    //         }
    //     }

    //     // Daftar posisi
    //     $allPositions = [
    //         'Direktur',
    //         'GM',
    //         'Manager',
    //         'Coordinator',
    //         'Section Head',
    //         'Supervisor',
    //         'Leader',
    //         'JP',
    //         'Operator',
    //     ];

    //     $rawPosition = $user->employee->position ?? 'Operator';
    //     $currentPosition = Str::contains($rawPosition, 'Act ')
    //         ? trim(str_replace('Act', '', $rawPosition))
    //         : $rawPosition;

    //     $positionIndex = array_search($currentPosition, $allPositions);
    //     if ($positionIndex === false) {
    //         $positionIndex = array_search('Operator', $allPositions);
    //     }

    //     $visiblePositions = $positionIndex !== false
    //         ? array_slice($allPositions, $positionIndex)
    //         : [];

    //     return view('website.hav.assign', compact('title', 'employees', 'filter', 'company', 'search', 'visiblePositions'));
    // }

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
        $position = $request->input('position');
        $templatePath = public_path('assets/file/HAV_Summary.xls');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $subordinates = auth()->user()->subordinate()->unique()->values();
        $employees = Employee::with([
            'departments',
            'assessments.details',
            'havQuadrants' => function ($q) {
                $q->orderByDesc('created_at');
            },
            'performanceAppraisalHistories' => function ($q) {
                $q->orderBy('date');
            }
        ])
            ->whereHas('havQuadrants')
            ->when($position, fn($q) => $q->where('position', $position)) // filter posisi
            ->whereIn('id', $subordinates)->get();
        // dd($employees);
        $startRow = 13;
        $sheet->setCellValue("C6", auth()->user()->employee->name);
        $sheet->setCellValue("C7", date('d-m-Y H:i:s'));

        foreach ($employees as $i => $emp) {
            $row = $startRow + $i;
            $assessment = $emp->hav->sortByDesc('created_at')->first();
            $details = $assessment ? $assessment->details->keyBy('alc_id') : collect();

            // Total Score
            $totalScore = $details->sum(fn($d) => floatval($d->score ?? 0));
            $totalScorePercent = $totalScore ? round(($totalScore / (8 * 5)) * 100, 1) . '%' : '0%';

            // HAV Quadrant
            $hav = $emp->havQuadrants->first();
            $quadrant = $hav->quadrant ?? null;

            // Performance Appraisal 3 tahun terakhir
            $appraisals = $emp->performanceAppraisalHistories
                ->sortByDesc('date')
                ->take(3)
                ->sortBy('date')
                ->values();

            // Mapping kolom (semua geser 1 ke kanan)
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $emp->npk);
            $sheet->setCellValue("C{$row}", $emp->name);
            $sheet->setCellValue("D{$row}", $emp->function);
            $sheet->setCellValue("E{$row}", $emp->division->name); // Divisi
            $sheet->setCellValue("F{$row}", $emp->department->name); // Departemen
            $sheet->setCellValue("G{$row}", Carbon::parse($emp->birthday_date)->age ?? null); // Usia
            $sheet->setCellValue("H{$row}", $emp->grade); // Sub Gol
            $sheet->setCellValue("I{$row}", $emp->working_period); // Masa kerja

            // ALCs
            $col = 'J';
            for ($j = 1; $j <= 8; $j++) {
                $sheet->setCellValue("{$col}{$row}", $details[$j]->score ?? '-');
                $col++;
            }

            $sheet->setCellValue("R{$row}", $totalScore);
            $sheet->setCellValue("S{$row}", $totalScorePercent);

            // Kolom Appraisal 3 Tahun (T, U, V)
            // dd(substr($appraisals[0]->date, 0, 4));
            $sheet->setCellValue("U11", substr($appraisals[0]->date, 0, 4) ?? '-');
            $sheet->setCellValue("V11", substr($appraisals[0]->date, 0, 4) ?? '-');
            $sheet->setCellValue("W11", substr($appraisals[0]->date, 0, 4) ?? '-');
            $sheet->setCellValue("X11", substr($appraisals[0]->date, 0, 4) ?? '-');
            $sheet->setCellValue("Y11", substr($appraisals[0]->date, 0, 4) ?? '-');
            $sheet->setCellValue("Z11", substr($appraisals[0]->date, 0, 4) ?? '-');
            $sheet->setCellValue("U{$row}", $appraisals[0]->score ?? '-');
            $sheet->setCellValue("V{$row}", $appraisals[1]->score ?? '-');
            $sheet->setCellValue("W{$row}", $appraisals[2]->score ?? '-');

            $sheet->setCellValue("X{$row}", $appraisals[0]->score ?? '-');
            $sheet->setCellValue("Y{$row}", $appraisals[1]->score ?? '-');
            $sheet->setCellValue("Z{$row}", $appraisals[2]->score ?? '-');

            // HAV terakhir (W, X, Y)
            $sheet->setCellValue("AD{$row}", QuadranMaster::where('id', $quadrant)->first()->name ?? '-'); // Quadrant

            $getLastAssessment = $emp->assessments->sortByDesc('date')->first();

            $withWeakness = $getLastAssessment->details->filter(fn($item) => !empty($item['weakness']))
                ->values() // reset index agar rapi
                ->take(2);
            $withStrength = $getLastAssessment->details->filter(fn($item) => !empty($item['strength']))
                ->values() // reset index agar rapi
                ->take(3);

            $sheet->setCellValue("AE{$row}", $withStrength[0]->alc->name ?? '-');
            $sheet->setCellValue("AF{$row}", $withStrength[1]->alc->name ?? '-');
            $sheet->setCellValue("AG{$row}", $withStrength[2]->alc->name ?? '-');
            $sheet->setCellValue("AH{$row}", $withWeakness[0]->alc->name ?? '-');
            $sheet->setCellValue("AI{$row}", $withWeakness[1]->alc->name ?? '-');

            $getLast3AstraTraining = AstraTraining::where('employee_id', $emp->id)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();

            $sheet->setCellValue("AJ{$row}", $getLast3AstraTraining[0]->training_name ?? '-');
            $sheet->setCellValue("AK{$row}", $getLast3AstraTraining[1]->training_name ?? '-');
            $sheet->setCellValue("AL{$row}", $getLast3AstraTraining[2]->training_name ?? '-');


            $getLastHav = Hav::where('employee_id', $emp->id)
                ->orderBy('created_at', 'desc')
                ->first();
            $sheet->setCellValue("AM{$row}", $getLastHav->details[0]->evidence ?? '-');
            $sheet->setCellValue("AN{$row}", $getLastHav->details[1]->evidence ?? '-');
            $sheet->setCellValue("AO{$row}", $getLastHav->details[2]->evidence ?? '-');
            $sheet->setCellValue("AP{$row}", $getLastHav->details[3]->evidence ?? '-');
            $sheet->setCellValue("AQ{$row}", $getLastHav->details[4]->evidence ?? '-');
            $sheet->setCellValue("AR{$row}", $getLastHav->details[5]->evidence ?? '-');
            $sheet->setCellValue("AS{$row}", $getLastHav->details[6]->evidence ?? '-');
            $sheet->setCellValue("AT{$row}", $getLastHav->details[7]->evidence ?? '-');
        }


        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'HAV_Summary_Exported.xlsx';
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
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\HavImport($filePath,  $havId), $file);

            return back()->with('success', 'Import HAV berhasil.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
        return redirect()->back();
    }
    public function downloadLatestUpload($havId)
    {
        $latestUpload = DB::table('hav_comment_histories')
            ->where('hav_id', $havId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestUpload || !$latestUpload->upload) {
            return abort(404, 'File upload tidak ditemukan.');
        }

        // path yang disimpan di DB, misalnya "hav_uploads/hav_1747960986.xlsx"
        $filePath = $latestUpload->upload;

        // cek apakah file ada di disk 'local' (storage/app)
        if (!Storage::disk('local')->exists($filePath)) {
            return abort(404, 'File tidak ditemukan di storage.');
        }

        // download file dari disk 'local'
        return Storage::disk('local')->download($filePath);
    }

    public function approval(Request $request, $company = null)
    {
        $company = $request->query('company');

        $title = 'Add Employee';
        $user = auth()->user();
        $filter = $request->input('filter', 'all');
        $search = $request->input('search'); // ambil input search dari request

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

                $approvallevel = (auth()->user()->employee->getFirstApproval());
                $subordinate =  auth()->user()->employee->getSubordinatesByLevel($approvallevel)->pluck('id');

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
        $employee = Employee::with('hav', 'hav.details')->find($id);

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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
}
