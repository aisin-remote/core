<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Alc;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\DetailAssessment;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Section;

use App\Models\SubSection;
use DataTables;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Str;

use Symfony\Component\HttpFoundation\Request;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssessmentController extends Controller
{
    /**
     * Menampilkan form create.
     *
     *
     */

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

    public function index(Request $request, $company = null)
    {
        $user = auth()->user();
        $title = 'Assessment';
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

        $rawPosition = $user->employee->position;
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

        $filter = $request->input('filter'); // Filter by position
        $search = $request->input('search'); // Search by name

        if ($user->role === 'HRD' || $user->employee->position == 'President' ||  $user->employee->position == 'VPD') {
            $employees = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
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
        } else {
            $employee = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
                ->where('user_id', $user->id)
                ->first();

            if (!$employee) {
                $employees = collect();
            } else {
                $subordinates = $this->getSubordinatesFromStructure($employee);

                $employees = collect([$employee])->merge($subordinates)->unique('id');

                // Filter posisi
                if ($filter && $filter !== 'all') {
                    $employees = $employees->filter(function ($emp) use ($filter) {
                        return $emp->position === $filter || str_starts_with($emp->position, "Act {$filter}");
                    });
                }

                // Search by name / npk
                if ($search) {
                    $employees = $employees->filter(function ($emp) use ($search) {
                        return stripos($emp->name, $search) !== false || stripos($emp->npk, $search) !== false;
                    });
                }

                // Hydrate ulang jadi Eloquent Collection agar bisa pakai relasi seperti ->assessments()
                $employees = Employee::hydrate($employees->toArray());
            }
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
     * Mendapatkan assessment terbaru dari bawahan
     */
    private function getAssessmentsForSubordinates($employees, $request)
    {
        return Assessment::with(['employee', 'alc',  'employee.leadingDepartment', 'employee.subSection.section.department', 'employee.leadingSection.department', 'employee.leadingDepartment.division'])
            ->whereHas('employee', function ($query) use ($employees) {
                // Filter berdasarkan list employee bawahan
                return $query->whereIn('id', $employees->pluck('id'));
            })
            ->when($request->search, function ($query) use ($request) {
                // Pencarian berdasarkan nama atau npk
                return $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('npk', 'like', '%' . $request->search . '%');
                });
            })
            ->whereIn('id', function ($query) {
                // Ambil assessment terbaru per employee
                $query->selectRaw('MAX(id)')
                    ->from('assessments')
                    ->groupBy('employee_id');
            })
            ->paginate(10);
    }

    // public function history_ajax(Request $request)
    // {
    //     $data = Assessment::select('assessments.id', 'assessments.employee_id', 'assessments.date',
    //                                 'assessments.upload', 'employees.npk as employee_npk', 'employees.name as employee_name',
    //                                 )
    //                         ->join('employees', 'assessments.employee_id', 'employees.id')
    //                         ->with('details')
    //                         ->with('alc')
    //                         ->with('employee')
    //                         ->orderBy('assessments.id', 'ASC');

    //     return DataTables::eloquent($data)->make(true);
    // }

    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);

        // Hapus juga data terkait di tabel detail_assessments
        DetailAssessment::where('assessment_id', $id)->delete();

        // Hapus file jika ada
        if ($assessment->upload) {
            \Storage::delete('public/' . $assessment->upload);
        }

        // Hapus assessment
        $assessment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assessment berhasil dihapus.'
        ]);
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
                $query->select('assessment_id', 'alc_id', 'score', 'strength', 'weakness', 'suggestion_development')
                    ->with(['alc:id,name']);
            }])
            ->get();

        return response()->json([
            'employee' => $employee,
            'assessments' => $assessments
        ]);
    }




    public function showByDate($assessment_id, $date)
    {
        // Ambil assessment berdasarkan ID
        $assessment = Assessment::findOrFail($assessment_id);

        // Ambil employee dari assessment (pastikan kolom employee_id ada di tabel assessments)
        $employee = Employee::with(
            'departments', // ← tambahkan ini
            'subSection.section.department',
            'leadingSection.department',
            'leadingDepartment.division'
        )->findOrFail($assessment->employee_id);


        // Ambil data detail_assessment dengan alc (menggunakan Eloquent)
        $assessments = DetailAssessment::with('alc')
            ->where('assessment_id', $assessment_id)
            ->get();

        // Debugging untuk memastikan data tidak null
        if ($assessments->isEmpty()) {
            return back()->with('error', 'Tidak ada data assessment pada tanggal tersebut.');
        }

        // Ambil detail menggunakan join untuk mendapatkan alc_name dan score dari detail_assessment
        $details = DB::table('detail_assessments')
            ->join('alc', 'detail_assessments.alc_id', '=', 'alc.id')
            ->where('detail_assessments.assessment_id', $assessment_id)
            ->select(
                'detail_assessments.*',
                'alc.name as alc_name', // Ambil nama ALC dari tabel alc
                'detail_assessments.score' // Ambil score dari detail_assessment
            )
            ->get();


        return view('website.assessment.detail', compact('employee', 'assessments', 'date', 'details'));
    }

    public function create()
    {
        $employees = Employee::all(); // Ambil semua employee
        $alcs = Alc::all(); // Ambil semua alc_id

        return view('assessments.create', compact('employees', 'alcs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'description' => 'required|string',
            'upload' => 'nullable|file|mimes:pdf|max:2048',
            'alc_ids' => 'required|array',
            'alc_ids.*' => 'exists:alc,id',
            'scores' => 'nullable|array',
            'scores.*' => 'nullable|string|max:2',
            'strenght' => 'nullable|array',
            'weakness' => 'nullable|array',
            'suggestion_development' => 'nullable|array',
        ]);

        // Simpan file jika ada
        $filePath = null;
        if ($request->hasFile('upload')) {
            $filePath = 'uploads/assessments/' . $request->file('upload')->hashName();
            $request->file('upload')->storeAs('public', $filePath);
        }

        // Simpan data utama ke tabel assessments
        $assessment = Assessment::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'description' => $request->description,
            'upload' => $filePath,
        ]);

        // Simpan data detail ke tabel assessment_details
        $assessmentDetails = [];
        foreach ($request->alc_ids as $index => $alc_id) {
            DB::table('detail_assessments')
                ->updateOrInsert(
                    [
                        'assessment_id' => $assessment->id,
                        'alc_id' => $alc_id
                    ],
                    [
                        'score' => $request->scores[$alc_id] ?? "0",  // Ambil nilai score berdasarkan ALC ID
                        'strength' => $request->strength[$alc_id] ?? "", // Ambil nilai strength berdasarkan ALC ID
                        'weakness' => $request->weakness[$alc_id] ?? "",
                        'suggestion_development' => $request->suggestion_development[$alc_id] ?? "",
                        'updated_at' => now()
                    ]
                );
        }
        // Simpan ke tabel hav
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

        // Load Excel template
        $templatePath = public_path('assets/file/Import-HAV.xls');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $employee = Employee::with('departments')->findOrFail($request->employee_id);
        $sheet->setCellValue("C6", $employee->name);
        $sheet->setCellValue("C7", $employee->npk);
        $sheet->setCellValue("C8", $employee->grade);
        $sheet->setCellValue("C9", $employee->company_name);
        $sheet->setCellValue("C10", $employee->department->name ?? '');
        $sheet->setCellValue("C11", $employee->position);
        $sheet->setCellValue("C13", date('Y'));

        // ALC mapping ke kolom & baris
        $alcMapping = [
            1 => ['D', [17, 18, 19]],                          // Vision & Business Sense
            2 => ['I', [17, 18, 19, 20, 21, 22, 23]],           // Customer Focus
            3 => ['O', [17, 18, 19, 20]],                      // Interpersonal Skill
            4 => ['T', [17, 18, 19, 20]],                      // Analysis & Judgment
            5 => ['D', [27, 28, 29, 30, 31]],                  // Planning & Driving Action
            6 => ['I', [27, 28, 29, 30, 31, 32, 33, 34, 35, 36]], // Leading & Motivating
            7 => ['O', [27, 28, 29, 30, 31, 32, 33]],          // Teamwork
            8 => ['T', [27, 28, 29, 30, 31, 32, 33]],          // Drive & Courage
        ];

        $suggestionMapping = [
            1 => ['F', [17, 18, 19]],                             // Vision & Business Sense
            2 => ['K', [17, 18, 19, 20, 21, 22, 23]],              // Customer Focus
            3 => ['Q', [17, 18, 19, 20]],                          // Interpersonal Skill
            4 => ['V', [17, 18, 19, 20]],                          // Analysis & Judgment
            5 => ['F', [27, 28, 29, 30, 31]],                      // Planning & Driving Action
            6 => ['K', [27, 28, 29, 30, 31, 32, 33, 34, 35, 36]],  // Leading & Motivating
            7 => ['Q', [27, 28, 29, 30, 31, 32, 33]],              // Teamwork
            8 => ['V', [27, 28, 29, 30, 31, 32, 33]],              // Drive & Courage
        ];
        // Simpan ke hav_details + isi Excel
        foreach ($request->alc_ids as $alc_id) {
            $score = $request->scores[$alc_id] ?? "0";
            $suggestion = $request->suggestion_development[$alc_id] ?? "";

            DB::table('hav_details')->insert([
                'hav_id' => $havId,
                'alc_id' => $alc_id,
                'score' => $score,
                'is_assessment' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (isset($alcMapping[$alc_id])) {
                [$col, $rows] = $alcMapping[$alc_id];
                foreach ($rows as $row) {
                    $sheet->setCellValue("{$col}{$row}", $score);
                }
            }
            if (isset($suggestionMapping[$alc_id])) {
                [$sugCol, $sugRows] = $suggestionMapping[$alc_id];

                // Gabungkan rows menjadi satu teks panjang dengan line break antar baris
                $mergedSuggestionRow = $sugRows[0]; // Tulis di baris awal saja
                $sheet->mergeCells("{$sugCol}{$sugRows[0]}:{$sugCol}" . end($sugRows)); // Merge kolom
                $sheet->setCellValue("{$sugCol}{$mergedSuggestionRow}", $suggestion);
                $sheet->getStyle("{$sugCol}{$mergedSuggestionRow}")
                    ->getAlignment()->setWrapText(true); // agar teks suggestion bisa panjang & terpotong otomatis
                $style = $sheet->getStyle("{$sugCol}{$mergedSuggestionRow}");
                $style->getFont()->setItalic(false);
            }
        }

        // Simpan file Excel ke storage
        $excelFileName = 'hav_uploads/hav_' . now()->timestamp . '.xlsx';
        $fullPath = storage_path("app/{$excelFileName}");
        (new Xlsx($spreadsheet))->save($fullPath);

        // Simpan ke hav_comment_histories
        DB::table('hav_comment_histories')->insert([
            'hav_id' => $havId,
            'employee_id' => $request->employee_id,
            'upload' => $excelFileName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // $token = "v2n49drKeWNoRDN4jgqcdsR8a6bcochcmk6YphL6vLcCpRZdV1";

        // $user = Auth::user();
        // $employee = $user->employee; // ambil employee yang login
        // $rawNumber = $employee->phone_number ?? null;
        // $formattedNumber = preg_replace('/^0/', '62', $rawNumber);

        // if (!$formattedNumber) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Nomor HP Anda tidak tersedia.',
        //     ]);
        // }

        // $message = sprintf(
        //     "Hallo Apakah Benar ini Nomor?"
        // "✅ Assessment berhasil dikirim!\nID Assessment: %s\nTanggal: %s\nNama Pegawai: %s",
        // $assessment->id,
        // $assessment->date,
        // $assessment->name ?? 'Anda'
        // );

        // $whatsappResponse = Http::asForm()
        //     ->withOptions(['verify' => false])
        //     ->post('https://app.ruangwa.id/api/send_message', [
        //         'token' => $token,
        //         'number' => $formattedNumber,
        //         'message' => $message
        //     ]);



        // Jika mau debug response dari API

        return response()->json([
            'success' => true,
            'message' => 'Data assessment berhasil disimpan.',
            'assessment' => $assessment,
            'assessment_details' => $assessmentDetails,
            // 'whatsapp_response' => $whatsappResponse->body()
        ]);
    }
    public function getAssessmentDetail($employee_id)
    {
        // 🔹 Cari assessment terbaru dari employee
        $assessment = Assessment::where('employee_id', $employee_id)
            ->orderBy('created_at', 'desc') // Ambil yang paling baru diinput
            ->first();

        if (!$assessment) {
            return response()->json(['error' => 'Data assessment tidak ditemukan'], 404);
        }

        // 🔹 Ambil data employee terbaru
        $employee = Employee::findOrFail($employee_id);

        // 🔹 Ambil detail assessment dengan ALC
        $details = DetailAssessment::with('alc')
            ->where('assessment_id', $assessment->id)
            ->get();

        // 🔹 Pisahkan Strength dan Weakness yang memiliki nilai
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
    public function edit($id)
    {
        $assessment = Assessment::with('details.alc')->findOrFail($id);
        \Log::info("DETAILS: ", $assessment->details->toArray()); // Tambah ini
        return response()->json([
            'id' => $assessment->id,
            'employee_id' => $assessment->employee_id,
            'date' => $assessment->date,
            'description' => $assessment->description,
            'upload' => $assessment->upload ? asset('storage/' . $assessment->upload) : null, // Buat URL file
            'details' => $assessment->details->map(fn($d) => [
                'alc_id' => $d->alc_id,
                'score' => $d->score,
                'strength' => $d->strength,
                'weakness' => $d->weakness,
                'suggestion_development' => $d->suggestion_development,
                'alc' => $d->alc
            ]),

            'alc_options' => Alc::select('id', 'name')->get()
        ]);
    }


    public function update(Request $request)
    {

        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'scores' => 'required|array',
            'strength' => 'nullable|array',
            'weakness' => 'nullable|array',
            'suggestion_development' => 'nullable|array',
            'upload' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        // **Update tabel `assessments`**
        $assessment = Assessment::findOrFail($request->assessment_id);
        $assessment->date = $request->date;
        $assessment->description = $request->description;

        // **Handle File Upload**
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $path = $file->store('assessments', 'public');
            $assessment->upload = $path;
        }

        $assessment->save();

        // **Update `detail_assessments` untuk scores, strengths, weaknesses**
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

        return response()->json(['message' => 'Assessment updated successfully']);
    }
}
