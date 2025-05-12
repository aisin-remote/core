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
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\HavDetailKeyBehavior;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PerformanceAppraisalHistory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Support\Str;


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

            $havGrouped = HavQuadrant::whereIn('employee_id', $subordinates)->with('employee')->get()->groupBy('quadrant');

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


        return view('website.hav.index', compact('orderedHavGrouped', 'titles'));
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
                $subordinateIds = $this->getSubordinatesFromStructure($employee)->pluck('id');

                $employees = Hav::with('employee')
                    ->whereIn('employee_id', $subordinateIds)
                    ->whereHas('employee', function ($query) use ($filter, $search, $employee) {
                        $query->where('company_name', $employee->company_name); // tetap batasi berdasarkan company milik atasan
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

        // Cari index posisi saat ini
        $positionIndex = array_search($currentPosition, $allPositions);

        // Fallback jika tidak ditemukan
        if ($positionIndex === false) {
            $positionIndex = array_search('Operator', $allPositions);
        }

        // Ambil posisi di bawahnya (tanpa posisi user)
        $visiblePositions = $positionIndex !== false
            ? array_slice($allPositions, $positionIndex)
            : [];


        return view('website.hav.list', compact('title', 'employees', 'filter', 'company', 'search','visiblePositions'));
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
    public function export()
    {
        $templatePath = public_path('assets/file/HAV_Summary.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $subordinates = auth()->user()->subordinate()->unique()->values();
        $employees = Employee::with([
            'assessments.details',
            'havQuadrants' => function ($q) {
                $q->orderByDesc('created_at');
            },
            'performanceAppraisalHistories' => function ($q) {
                $q->orderBy('date');
            }
        ])->whereHas('havQuadrants')->whereIn('id', $subordinates)->get();

        $startRow = 13;

        foreach ($employees as $i => $emp) {
            $row = $startRow + $i;
            $assessment = $emp->assessments->sortByDesc('created_at')->first();
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
            $sheet->setCellValue("B{$row}", $emp->npk);
            $sheet->setCellValue("C{$row}", $emp->name);
            $sheet->setCellValue("D{$row}", $emp->function);
            $sheet->setCellValue("E{$row}", $emp->foundation_group); // Divisi
            $sheet->setCellValue("F{$row}", $emp->company_group); // Departemen
            $sheet->setCellValue("G{$row}", Carbon::parse($emp->birthday_date)->age ?? null); // Usia
            $sheet->setCellValue("H{$row}", $emp->grade); // Sub Gol
            $sheet->setCellValue("I{$row}", $emp->working_period); // Masa kerja

            // ALCs
            $col = 'J';
            for ($j = 1; $j <= 8; $j++) {
                $sheet->setCellValue("{$col}{$row}", $details[$j]->score ?? '');
                $col++;
            }

            $sheet->setCellValue("R{$row}", $totalScore);
            $sheet->setCellValue("S{$row}", $totalScorePercent);

            // Kolom Appraisal 3 Tahun (T, U, V)
            $sheet->setCellValue("T{$row}", $appraisals[0]->score ?? '');
            $sheet->setCellValue("U{$row}", $appraisals[1]->score ?? '');
            $sheet->setCellValue("V{$row}", $appraisals[2]->score ?? '');

            // HAV terakhir (W, X, Y)
            $sheet->setCellValue("W{$row}", $hav->assessment_score ?? '');
            $sheet->setCellValue("X{$row}", $hav->performance_score ?? '');
            $sheet->setCellValue("Y{$row}", $quadrant);

            // Breakdown score terbaru (Z - AG)
            $breakdownCol = 'Z';
            for ($j = 1; $j <= 8; $j++) {
                $sheet->setCellValue("{$breakdownCol}{$row}", $details[$j]->score ?? '');
                $breakdownCol++;
            }
        }


        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'HAV_Summary_Exported.xlsx';
        $writer->save(public_path($filename));

        return response()->download(public_path($filename))->deleteFileAfterSend(true);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        dd('ass');
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\HavImport, $request->file('file'));
            return back()->with('success', 'Import HAV berhasil.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
        return redirect()->back();
    }
    public function approval(Request $request,$company = null)
    {
        $company = $request->query('company');

        $title = 'Add Employee';
        $user = auth()->user();
        $filter = $request->input('filter', 'all');
        $search = $request->input('search'); // ambil input search dari request

        if ($user->role === 'HRD') {
            $employees = Hav::with('employee')
                ->whereHas('employee', function ($query) use ($company, $filter, $search) {
                    if ($company) {
                        $query->where('company_name', $company);
                    }
                    if ($filter && $filter !== 'all') {
                        $query->where('position', $filter);
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
                $subordinate=  auth()->user()->employee->getSubordinatesByLevel($approvallevel)->pluck('id');

                $employees = Hav::with('employee')
                    ->whereIn('employee_id', $subordinate)
                    ->get();

            }
        }

        return view('website.approval.approvalhav', compact('title', 'employees', 'filter', 'company', 'search'));

    }
    public function approve($id)
{
    $hav = Hav::findOrFail($id); // Langsung ambil berdasarkan ID HAV
    $hav->status = 2;
    $hav->save();

    return redirect()->back()->with('success', 'HAV berhasil disetujui.');
}

public function reject($id)
{
    $hav = Hav::findOrFail($id);
    $hav->status = 1;
    $hav->save();

    return redirect()->back()->with('success', 'HAV berhasil ditolak.');
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
        //
    }
}
