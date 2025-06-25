<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\GradeConversion;
use App\Models\Icp;
use App\Models\IcpDetail;
use App\Models\MatrixCompetency;
use App\Models\PerformanceAppraisalHistory;
use App\Models\Section;
use App\Models\SubSection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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

    public function index(Request $request)
    {
        $title = 'Employee ICP';
        $user = auth()->user();
        $search = $request->input('search');
        $filter = $request->input('filter', 'all');
        $company = $request->input('company');

        if ($user->isHRDorDireksi()) {
            // HRD dan Direksi bisa melihat semua data ICP
            $icps = Icp::with('employee')
                ->when($company, fn($q) => $q->whereHas('employee', fn($e) => $e->where('company_name', $company)))
                ->when($search, function ($query, $search) {
                    $query->whereHas('employee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('npk', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
                })
                ->paginate(10)
                ->appends(['search' => $search, 'filter' => $filter, 'company' => $company]);
        } else {
            // Ambil employee milik user
            $employee = $user->employee;
            if (!$employee) {
                $icps = collect(); // Tidak punya bawahan
            } else {
                // Ambil query bawahan
                $subordinatesQuery = $this->getSubordinatesFromStructure($employee);
                if ($subordinatesQuery instanceof \Illuminate\Database\Eloquent\Builder) {
                    // Ambil NPK/Nama yang dicari hanya dari bawahan
                    $icps = Icp::with('employee')
                        ->whereHas('employee', function ($q) use ($subordinatesQuery, $search, $filter, $company) {
                            $q->whereIn('id', $subordinatesQuery->pluck('id'));

                            if ($search) {
                                $q->where(function ($q2) use ($search) {
                                    $q2->where('name', 'like', "%{$search}%")
                                        ->orWhere('npk', 'like', "%{$search}%");
                                });
                            }

                            if ($filter && $filter !== 'all') {
                                $q->where('position', $filter)
                                    ->orWhere('position', 'like', "Act %{$filter}");
                            }

                            if ($company) {
                                $q->where('company_name', $company);
                            }
                        })
                        ->paginate(10)
                        ->appends(['search' => $search, 'filter' => $filter, 'company' => $company]);
                } else {
                    $icps = collect(); // Tidak valid atau tidak punya akses
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

        return view('website.icp.index', compact('icps', 'title', 'visiblePositions', 'search', 'filter', 'company'));
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
        $subordinateIds = $employee->getSubordinatesByLevel($approvallevel)->pluck('id')->toArray();

        // Ambil semua subordinate (filtered)
        $icps = Employee::whereIn('id', $subordinateIds)
            ->when($company, fn($q) => $q->where('company_name', $company))
            ->when($filter && $filter !== 'all', function ($q) use ($filter) {
                $q->where(function ($sub) use ($filter) {
                    $sub->where('position', $filter)
                        ->orWhere('position', 'like', "Act %{$filter}");
                });
            })
            ->when($search, fn($q) => $q->where('name', 'like', '%' . $search . '%'))
            ->with(['icp' => function ($q) {
                $q->orderByDesc('created_at') // urutkan biar first() dapat yang terbaru
                    > with(['latestIcp.details']);
            }])
            ->get();


        return view('website.icp.assign', compact('title', 'icps', 'filter', 'company', 'search', 'visiblePositions'));
    }





    public function create()
    {
        $title = 'Add icp';
        $departments = Department::all();
        $employees = Employee::all();

        $grades = GradeConversion::all();

        $technicalCompetencies = MatrixCompetency::all();

        return view('website.icp.create', compact('title', 'grades', 'departments', 'employees', 'technicalCompetencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'aspiration' => 'required',
            'career_target' => 'required',
            'date' => 'required',
            'job_function' => 'required',
            'position' => 'required',
            'level' => 'required',


            // Validasi array untuk detail
            'details.*.current_technical' => 'required',
            'details.*.current_nontechnical' => 'required',
            'details.*.required_technical' => 'required',
            'details.*.required_nontechnical' => 'required',
            'details.*.development_technical' => 'required',
            'details.*.development_nontechnical' => 'required',
        ]);

        try {
            // Simpan data utama ICP
            $icp = Icp::create([
                'employee_id' => $request->employee_id,
                'aspiration' => $request->aspiration,
                'career_target' => $request->career_target,
                'date' => $request->date,
                'job_function' => $request->job_function,
                'position' => $request->position,
                'level' => $request->level,
                'status' => '1',
            ]);

            // Loop semua detail dan simpan satu per satu
            foreach ($request->details as $detail) {
                IcpDetail::create([
                    'icp_id' => $icp->id,
                    'current_technical' => $detail['current_technical'],
                    'current_nontechnical' => $detail['current_nontechnical'],
                    'required_technical' => $detail['required_technical'],
                    'required_nontechnical' => $detail['required_nontechnical'],
                    'development_technical' => $detail['development_technical'],
                    'development_nontechnical' => $detail['development_nontechnical'],
                ]);
            }

            return redirect()->route('icp.assign')->with('success', 'Data ICP berhasil ditambahkan.');
        } catch (\Exception $e) {
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
            ->select('id', 'employee_id',  'aspiration', 'career_target', 'date', 'job_function', 'position', 'level')
            ->orderBy('date', 'desc')
            ->with(['details' => function ($query) {
                $query->select('icp_id', 'current_technical', 'current_nontechnical', 'required_technical', 'required_nontechnical', 'development_technical', 'development_nontechnical');
            }])
            ->get();

        return response()->json([
            'employee' => $employee,
            'icp' => $assessments
        ]);
    }

    public function export($employee_id)
    {
        // Ambil employee + ICP terbaru + details + edukasi + promosi
        $employee = Employee::with([
            'educations',
            'promotionHistory',
            'icp' => function ($query) {
                $query->latest()->with('details');
            },
        ])->findOrFail($employee_id);

        // Ambil ICP terbaru
        $icp = $employee->icp->first();
        $edu = $employee->educations->first();
        $prom = $employee->promotionHistory->first();

        if (!$icp) {
            return back()->with('error', 'Data ICP tidak ditemukan untuk employee ini.');
        }

        $year = $prom ? Carbon::parse($prom->last_promotion_date)->format('Y') : '-';
        $ais = Carbon::parse($employee->aisin_entry_date)->format('d/m/Y');

        // Ambil 3 tahun terakhir dari performance_appraisal_histories
        $performanceData = PerformanceAppraisalHistory::where('employee_id', $employee->id)
            ->selectRaw('YEAR(date) as year, score')
            ->orderByDesc('date')
            ->get()
            ->groupBy('year')
            ->map(fn($items) => $items->first()->score)
            ->sortKeys()
            ->take(3)
            ->toArray();


        // Load Excel template
        $filePath = public_path('assets/file/Template_ICP.xlsx');
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Isi header tahun
        $yearStart = now()->year;
        $yearEnd = $yearStart + 1;
        $sheet->setCellValue('G2', "Year  :  {$yearStart} - {$yearEnd}");
        $sheet->getStyle('G2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('V5')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        // Isi 3 tahun appraisal ke R5, T5, dan V5
        $columns = ['R', 'T', 'V'];
        $i = 0;

        foreach ($performanceData as $year => $score) {
            $sheet->setCellValue($columns[$i] . '5', "{$year} = {$score}");
            $i++;
        }

        for (; $i < 3; $i++) {
            $sheet->setCellValue($columns[$i] . '5', '-');
        }



        // Data employee
        $sheet->setCellValue('G5', $employee->name);
        $sheet->setCellValue('G6', $employee->company_name);
        $sheet->setCellValue('G7', $employee->position);
        $sheet->setCellValue('G8', $employee->grade);
        $sheet->setCellValue('R8', $employee->birthday_date);
        $sheet->setCellValue('R6', '3');

        if ($edu) {
            $sheet->setCellValue('R9', $edu->educational_level . '/' . $edu->major . '/' . $edu->institute);
        }

        $sheet->setCellValue('D27', $icp->job_function . '/' . $icp->position . '/' . $icp->level);
        $sheet->setCellValue('J8', $year);
        $sheet->setCellValue('G9', $ais);
        $sheet->setCellValue('C13', $icp->aspiration);
        $sheet->setCellValue('F16', $icp->career_target);
        $sheet->setCellValue('L16', \Carbon\Carbon::parse($icp->date)->format('Y'));

        // Detail pengembangan
        $startRow = 27;
        foreach ($icp->details ?? [] as $i => $detail) {
            $row = $startRow + $i;
            $sheet->setCellValue("J{$row}", '- ' . $detail->current_technical);
            $sheet->setCellValue("N{$row}", '- ' . $detail->current_nontechnical);
            $sheet->setCellValue("O{$row}", '- ' . $detail->required_technical);
            $sheet->setCellValue("T{$row}", '- ' . $detail->required_nontechnical);
            $sheet->setCellValue("U{$row}", '- ' . $detail->development_technical);
            $sheet->setCellValue("X{$row}", '- ' . $detail->development_nontechnical);
        }

        // Simpan output ke file sementara dan download
        $filename = 'ICP_' . $employee->name . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
    public function approval()
    {
        $user = auth()->user();
        $employee = $user->employee;

        // Ambil bawahan menggunakan fungsi getSubordinatesFromStructure
        $checkLevel = $employee->getFirstApproval();
        $approveLevel = $employee->getFinalApproval();


        $normalized = $employee->getNormalizedPosition();

        if ($normalized === 'vpd') {
            // Jika VPD, filter GM untuk check dan Manager untuk approve
            $subCheck = $employee->getSubordinatesByLevel($checkLevel, ['gm'])->pluck('id')->toArray();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel, ['manager'])->pluck('id')->toArray();
        } else {
            // Default (tidak filter posisi bawahannya)
            $subCheck = $employee->getSubordinatesByLevel($checkLevel)->pluck('id')->toArray();
            $subApprove = $employee->getSubordinatesByLevel($approveLevel)->pluck('id')->toArray();
        }

        $checkIdps = Icp::with('employee')
            ->where('status', 1)
            ->whereHas('employee', function ($q) use ($subCheck) {
                $q->whereIn('employee_id', $subCheck);
            })
            ->get();

        $checkIdpIds = $checkIdps->pluck('id')->toArray();

        $approveIdps = Icp::with('employee')
            ->where('status', 2)
            ->whereHas('employee', function ($q) use ($subApprove) {
                $q->whereIn('employee_id', $subApprove);
            })
            ->whereNotIn('id', $checkIdpIds) // ← filter agar tidak muncul dua kali
            ->get();

        $idps = $checkIdps->merge($approveIdps);


        return view('website.approval.icp.index', compact('idps'));
    }
    public function approve($id)
    {
        $idp = Icp::findOrFail($id);

        if ($idp->status == 1) {
            $idp->status = 2;
            $idp->save();

            return response()->json([
                'message' => 'ICP has been approved!'
            ]);
        }

        if ($idp->status == 2) {
            $idp->status = 3;
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
        $idp = Icp::findOrFail($request->id);

        // Menyimpan status HAV sebagai disetujui
        $idp->status = 0; // Status disetujui


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
        return response()->json(['message' => 'ICP has been revise.']);
    }
    public function update(Request $request,$id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'aspiration' => 'required|string',
            'career_target' => 'required|string',
            'date' => 'required|date',
            'job_function' => 'required|string',
            'position' => 'required|string',
            'level' => 'required|string',

            'details.*.current_technical' => 'required',
            'details.*.current_nontechnical' => 'required',
            'details.*.required_technical' => 'required',
            'details.*.required_nontechnical' => 'required',
            'details.*.development_technical' => 'required',
            'details.*.development_nontechnical' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // Update data utama ICP
            $icp = Icp::findOrFail($id);
            $icp->update([
                'employee_id' => $request->employee_id,
                'aspiration' => $request->aspiration,
                'career_target' => $request->career_target,
                'date' => $request->date,
                'job_function' => $request->job_function,
                'position' => $request->position,
                'level' => $request->level,
                'status' => "1",
            ]);

            // Hapus semua detail lama (bisa diubah kalau ingin granular update)
            $icp->details()->delete();

            // Simpan ulang detail baru
            foreach ($request->details as $detail) {
                IcpDetail::create([
                    'icp_id' => $icp->id,
                    'current_technical' => $detail['current_technical'],
                    'current_nontechnical' => $detail['current_nontechnical'],
                    'required_technical' => $detail['required_technical'],
                    'required_nontechnical' => $detail['required_nontechnical'],
                    'development_technical' => $detail['development_technical'],
                    'development_nontechnical' => $detail['development_nontechnical'],
                ]);
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
        $title = 'Update ICP';
        $departments = Department::all();
        $employees = Employee::all();
        $grades = GradeConversion::all();
        $technicalCompetencies = MatrixCompetency::all();
        $icp = Icp::with('details', 'employee')->findOrFail($id);

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

        return view('website.icp.update', compact(
            'title',
            'grades',
            'departments',
            'employees',
            'technicalCompetencies',
            'icp',
            'positions' // tambahkan ini
        ));
    }
}
