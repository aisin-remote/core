<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Idp;
use App\Models\User;
use App\Models\Plant;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\SubSection;
use Illuminate\Http\Request;
use App\Models\AstraTraining;
use App\Imports\MasterImports;
use App\Imports\EmployeeImport;
use App\Models\MutationHistory;
use App\Models\ExternalTraining;
use App\Models\PromotionHistory;
use App\Models\WorkingExperience;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\EducationalBackground;
use Illuminate\Support\Facades\Storage;
use App\Models\PerformanceAppraisalHistory;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeController extends Controller
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

    public function index(Request $request, $company = null)
    {
        $title = 'Employee';
        $user = auth()->user();
        $search = $request->input('search');
        $filter = $request->input('filter', 'all'); // Menambahkan filter, default 'all'

        if ($user->role === 'HRD') {
            // HRD bisa mencari berdasarkan beberapa kolom, termasuk company_name
            $employees = Employee::with([
                'subSection.section.department',
                'leadingSection.department',
                'leadingDepartment.division'
            ])
            ->when($company, fn($query) => $query->where('company_name', $company))  // Filter berdasarkan perusahaan yang sedang diakses
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('npk', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");  // Pencarian di seluruh kolom
                });
            })
            ->when($filter && $filter != 'all', function ($query) use ($filter) {
                $query->where('position', $filter);  // Filter posisi jika diperlukan
            })
            ->paginate(10)
            ->appends(['search' => $search, 'filter' => $filter, 'company' => $company]);

        } else {
            // Untuk user biasa (misalnya Supervisor), pencarian hanya berlaku untuk 'company_name' yang terkait
            $employee = Employee::with([
                'subSection.section.department.division.plant',
                'leadingSection.department.division.plant',
                'leadingDepartment.division.plant'
            ])
            ->where('user_id', $user->id)
            ->first();

            if (!$employee) {
                $employees = collect();
            } else {
                $query = $this->getSubordinatesFromStructure($employee);

                if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
                    // Pastikan hanya pencarian berdasarkan company_name yang relevan
                    if ($search) {
                        $query->where(function ($q) use ($search, $employee) {
                            $q->where('company_name', $employee->company_name)  // Batasi pencarian hanya dalam company_name yang sama dengan user
                              ->where(function ($q2) use ($search) {
                                  $q2->where('name', 'like', "%{$search}%")
                                      ->orWhere('npk', 'like', "%{$search}%");
                              });
                        });
                    }

                    // Filter posisi jika diperlukan
                    if ($filter && $filter != 'all') {
                        $query->where('position', $filter);
                    }

                    // Paginate hasil
                    $employees = $query->paginate(10)->appends([
                        'search' => $search,
                        'filter' => $filter,
                        'company' => $company
                    ]);
                } else {
                    $employees = collect();
                }
            }
        }

        return view('website.employee.index', compact('employees', 'title', 'filter', 'company'));
    }



    public function status($id)
    {
        try {
            $employee = Employee::findOrFail($id);

            // Toggle status
            $employee->is_active = !$employee->is_active;
            $employee->save();

            return redirect()->back()->with('success', 'Employee status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update employee status: ' . $e->getMessage());
        }
    }
    /**
     * Form tambah karyawan
     */
    public function create()
    {
        $title = 'Add Employee';
        $departments = Department::all();
        $divisions = Division::all();
        $plants = Plant::all();
        $sections = Section::all();
        $subSections = SubSection::all();
        return view('website.employee.create', compact('title', 'departments', 'divisions', 'plants', 'sections', 'subSections'));
    }

    /**
     * Simpan data karyawan ke database
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi
            $validatedData = $request->validate([
                'npk' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'birthday_date' => 'required|date',
                'gender' => 'required|in:Male,Female',
                'company_name' => 'required|string',
                'aisin_entry_date' => 'required|date',
                'company_group' => 'required|string',
                'position' => 'required|string',
                'grade' => 'required|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

                // Struktur organisasi
                'plant_id' => 'nullable|exists:plants,id',
                'division_id' => 'nullable|exists:divisions,id',
                'department_id' => 'nullable|exists:departments,id',
                'section_id' => 'nullable|exists:sections,id',
                'sub_section_id' => 'nullable|exists:sub_sections,id',

                // Pendidikan
                'level' => 'array',
                'level.*' => 'nullable|string|max:255',
                'major.*' => 'nullable|string|max:255',
                'institute.*' => 'nullable|string|max:255',
                'start_date.*' => 'nullable|string|max:255',
                'end_date.*' => 'nullable|string|max:255',

                // Pengalaman kerja
                'company.*' => 'nullable|string|max:255',
                'work_position.*' => 'nullable|string|max:255',
                'work_start_date.*' => 'nullable|string|max:255',
                'work_end_date.*' => 'nullable|string|max:255',
            ]);

            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $request->file('photo')->store('employee_photos', 'public');
            }

            $validatedData['working_period'] = Carbon::parse($validatedData['aisin_entry_date'])->diffInYears(Carbon::now());

            // Supervisor (atasan langsung) berdasarkan hirarki
            $supervisorId = null;
            if ($validatedData['sub_section_id'] ?? false) {
                $supervisorId = DB::table('sub_sections')->where('id', $validatedData['sub_section_id'])->value('leader_id');
            } elseif ($validatedData['section_id'] ?? false) {
                $supervisorId = DB::table('sections')->where('id', $validatedData['section_id'])->value('supervisor_id');
            } elseif ($validatedData['department_id'] ?? false) {
                $supervisorId = DB::table('departments')->where('id', $validatedData['department_id'])->value('manager_id');
            } elseif ($validatedData['division_id'] ?? false) {
                $supervisorId = DB::table('divisions')->where('id', $validatedData['division_id'])->value('gm_id');
            } elseif ($validatedData['plant_id'] ?? false) {
                $supervisorId = DB::table('plants')->where('id', $validatedData['plant_id'])->value('director_id');
            }

            $validatedData['supervisor_id'] = $supervisorId;

            // Buat karyawan
            $employee = Employee::create($validatedData);

            // Simpan pendidikan
            foreach ($request->level ?? [] as $i => $level) {
                if ($level) {
                    EducationalBackground::create([
                        'employee_id' => $employee->id,
                        'educational_level' => $level,
                        'major' => $request->major[$i] ?? null,
                        'institute' => $request->institute[$i] ?? null,
                        'start_date' => $request->start_date[$i] ?? null,
                        'end_date' => $request->end_date[$i] ?? null,
                    ]);
                }
            }

            // Simpan pengalaman kerja
            foreach ($request->company ?? [] as $i => $company) {
                if ($company) {
                    WorkingExperience::create([
                        'employee_id' => $employee->id,
                        'company' => $company,
                        'position' => $request->work_position[$i] ?? null,
                        'start_date' => $request->work_start_date[$i] ?? null,
                        'end_date' => $request->work_end_date[$i] ?? null,
                    ]);
                }
            }

            // Simpan ke struktur sesuai posisi
            $pos = strtolower($validatedData['position']);

            switch ($pos) {
                case 'operator':
                case 'jp':
                    $employee->update([
                        'sub_section_id' => $validatedData['sub_section_id'] ?? null,
                    ]);
                    break;

                case 'leader':
                    if ($validatedData['sub_section_id']) {
                        DB::table('sub_sections')->where('id', $validatedData['sub_section_id'])
                            ->update(['leader_id' => $employee->id]);
                    }
                    break;

                case 'supervisor':
                case 'section head':
                    if ($validatedData['section_id']) {
                        DB::table('sections')->where('id', $validatedData['section_id'])
                            ->update(['supervisor_id' => $employee->id]);
                    }
                    break;

                case 'manager':
                case 'coordinator':
                    if ($validatedData['department_id']) {
                        DB::table('departments')->where('id', $validatedData['department_id'])
                            ->update(['manager_id' => $employee->id]);
                    }
                    break;

                case 'gm':
                    if ($validatedData['division_id']) {
                        DB::table('divisions')->where('id', $validatedData['division_id'])
                            ->update(['gm_id' => $employee->id]);
                    }
                    break;

                case 'director':
                    if ($validatedData['plant_id']) {
                        DB::table('plants')->where('id', $validatedData['plant_id'])
                            ->update(['director_id' => $employee->id]);
                    }
                    break;
            }

            // Buat user jika manager
            if ($pos === 'manager') {
                $user = User::create([
                    'name' => $validatedData['name'],
                    'email' => strtolower($validatedData['name']) . '@aiia.co.id',
                    'password' => bcrypt('aiia'),
                ]);
                $employee->update(['user_id' => $user->id]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Karyawan berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function findSupervisor($position, $departmentId)
    {
        $hierarchy = [
            'GM' => null,
            'Manager' => 'GM',
            'Coordinator' => 'Manager',
            'Section Head' => 'Coordinator',
            'Supervisor' => 'Section Head'
        ];

        $supervisorPosition = $hierarchy[$position] ?? null;

        if (!$supervisorPosition) {
            return null;
        }

        return Employee::where('position', $supervisorPosition)
            ->whereHas('departments', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->first();
    }
    /**
     * Tampilkan detail karyawan
     */
    public function show($npk)
    {
        $promotionHistories = PromotionHistory::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->get();

        $astraTrainings = AstraTraining::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })->get();

        $externalTrainings = ExternalTraining::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->get();

        $educations = EducationalBackground::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('end_date', 'desc') // Urutkan berdasarkan tanggal akhir terbaru
            ->get();

        $workExperiences = WorkingExperience::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('end_date', 'desc') // Urutkan berdasarkan tanggal akhir terbaru
            ->orderBy('start_date', 'desc') // Jika end_date sama, urutkan berdasarkan tanggal mulai terbaru
            ->get();

        $performanceAppraisals = PerformanceAppraisalHistory::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('date', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->get();

        $assessment = Assessment::with('details.alc', 'employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->latest()
            ->first();

        $idps = Idp::with('alc', 'assessment.employee')
            ->whereHas('assessment.employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->get();

        $employee = Employee::with('subSection.section.department', 'leadingSection.department', 'leadingDepartment.division')
            ->where('npk', $npk)
            ->firstOrFail();
        $departments = Department::all();
        return view('website.employee.show', compact('employee', 'promotionHistories', 'educations', 'workExperiences', 'performanceAppraisals', 'departments', 'astraTrainings', 'externalTrainings', 'assessment', 'idps'));
    }

    public function edit($npk)
    {
        $promotionHistories = PromotionHistory::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })->get();

        $astraTrainings = AstraTraining::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })->get();

        $externalTrainings = ExternalTraining::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })->get();

        $educations = EducationalBackground::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('end_date', 'desc') // Urutkan berdasarkan tanggal akhir terbaru
            ->get();

        $workExperiences = WorkingExperience::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('end_date', 'desc') // Urutkan berdasarkan tanggal akhir terbaru
            ->orderBy('start_date', 'desc') // Jika end_date sama, urutkan berdasarkan tanggal mulai terbaru
            ->get();

        $performanceAppraisals = PerformanceAppraisalHistory::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('date', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->get();

        $assessment = Assessment::with('details.alc', 'employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->latest()
            ->first();

        $idps = Idp::with('alc', 'assessment.employee')
            ->whereHas('assessment.employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->get();
        $employee = Employee::with([
            'subSection.section.department',
            'leadingSection.department',
            'leadingDepartment.division'
        ])
            ->where('npk', $npk)
            ->firstOrFail();
        $departments = Department::all();
        $divisions = Division::all();
        $plants = Plant::all();
        $sections = Section::all();
        $subSections = SubSection::all();
        return view('website.employee.update', compact('employee', 'promotionHistories', 'educations', 'workExperiences', 'performanceAppraisals', 'departments', 'astraTrainings', 'externalTrainings', 'assessment', 'idps',  'divisions', 'plants', 'sections', 'subSections'));
    }

    public function update(Request $request, $npk)
    {
        try {
            $employee = Employee::where('npk', $npk)->firstOrFail();

            $oldGrade = $employee->grade;
            $oldPosition = $employee->position;

            $validatedData = $request->validate([
                'npk' => 'required|string|max:255|unique:employees,npk,' . $employee->id,
                'name' => 'required|string|max:255',
                'birthday_date' => 'required|date',
                'gender' => 'required|in:Male,Female',
                'company_name' => 'required|string',
                'aisin_entry_date' => 'required|date',
                'company_group' => 'required|string',
                'position' => 'required|string',
                'grade' => 'required|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

                'plant_id' => 'nullable|exists:plants,id',
                'division_id' => 'nullable|exists:divisions,id',
                'department_id' => 'nullable|exists:departments,id',
                'section_id' => 'nullable|exists:sections,id',
                'sub_section_id' => 'nullable|exists:sub_sections,id',
            ]);

            DB::transaction(function () use ($request, $validatedData, $employee, $oldGrade, $oldPosition) {
                if ($request->hasFile('photo')) {
                    if ($employee->photo) {
                        Storage::delete('public/' . $employee->photo);
                    }
                    $validatedData['photo'] = $request->file('photo')->store('employee_photos', 'public');
                }

                $validatedData['working_period'] = Carbon::parse($validatedData['aisin_entry_date'])->diffInYears(Carbon::now());

                $supervisorId = null;
                if ($validatedData['sub_section_id'] ?? false) {
                    $supervisorId = DB::table('sub_sections')->where('id', $validatedData['sub_section_id'])->value('leader_id');
                } elseif ($validatedData['section_id'] ?? false) {
                    $supervisorId = DB::table('sections')->where('id', $validatedData['section_id'])->value('supervisor_id');
                } elseif ($validatedData['department_id'] ?? false) {
                    $supervisorId = DB::table('departments')->where('id', $validatedData['department_id'])->value('manager_id');
                } elseif ($validatedData['division_id'] ?? false) {
                    $supervisorId = DB::table('divisions')->where('id', $validatedData['division_id'])->value('gm_id');
                } elseif ($validatedData['plant_id'] ?? false) {
                    $supervisorId = DB::table('plants')->where('id', $validatedData['plant_id'])->value('director_id');
                }

                $validatedData['supervisor_id'] = $supervisorId;

                $employee->update($validatedData);

                $positionAliasMap = [
                    'section head' => 'supervisor',
                    'coordinator' => 'manager',
                ];

                $promotionPaths = [
                    'operator' => ['leader' => ['clear' => 'sub_section_id']],
                    'jp' => ['leader' => ['clear' => 'sub_section_id']],
                    'leader' => ['supervisor' => ['table' => 'sub_sections', 'column' => 'leader_id', 'key' => 'sub_section_id']],
                    'supervisor' => ['manager' => ['table' => 'sections', 'column' => 'supervisor_id', 'key' => 'section_id']],
                    'manager' => ['gm' => ['table' => 'departments', 'column' => 'manager_id', 'key' => 'department_id']],
                    'gm' => ['director' => ['table' => 'divisions', 'column' => 'gm_id', 'key' => 'division_id']],
                ];

                $normalizePosition = function ($position) use ($positionAliasMap) {
                    $lower = strtolower($position);
                    return $positionAliasMap[$lower] ?? $lower;
                };

                $old = $normalizePosition($oldPosition);
                $new = $normalizePosition($validatedData['position']);

                if (isset($promotionPaths[$old][$new])) {
                    $action = $promotionPaths[$old][$new];

                    if (isset($action['clear'])) {
                        $employee->update([$action['clear'] => null]);
                    } elseif (isset($action['table'], $action['column'], $action['key'])) {
                        $refId = DB::table($action['table'])->where($action['column'], $employee->id)->first();
                        if ($refId) {
                            DB::table($action['table'])->where('id', $refId->id)->update([$action['column'] => null]);
                        }
                    }
                }

                // Update struktur sesuai jabatan baru
                $pos = strtolower($validatedData['position']);

                switch ($pos) {
                    case 'operator':
                    case 'jp':
                        $employee->update([
                            'sub_section_id' => $validatedData['sub_section_id'] ?? null,
                        ]);
                        break;

                    case 'leader':
                        if ($validatedData['sub_section_id']) {
                            DB::table('sub_sections')->where('id', $validatedData['sub_section_id'])
                                ->update(['leader_id' => $employee->id]);
                        }
                        break;

                    case 'supervisor':
                    case 'section head':
                        if ($validatedData['section_id']) {
                            DB::table('sections')->where('id', $validatedData['section_id'])
                                ->update(['supervisor_id' => $employee->id]);
                        }
                        break;

                    case 'manager':
                    case 'coordinator':
                        if ($validatedData['department_id']) {
                            DB::table('departments')->where('id', $validatedData['department_id'])
                                ->update(['manager_id' => $employee->id]);
                        }
                        break;

                    case 'gm':
                        if ($validatedData['division_id']) {
                            DB::table('divisions')->where('id', $validatedData['division_id'])
                                ->update(['gm_id' => $employee->id]);
                        }
                        break;

                    case 'director':
                        if ($validatedData['plant_id']) {
                            DB::table('plants')->where('id', $validatedData['plant_id'])
                                ->update(['director_id' => $employee->id]);
                        }
                        break;
                }

                $positionFieldMap = [
                    'leader' => ['table' => 'sub_sections', 'column' => 'leader_id', 'key' => 'sub_section_id'],
                    'supervisor' => ['table' => 'sections', 'column' => 'supervisor_id', 'key' => 'section_id'],
                    'manager' => ['table' => 'departments', 'column' => 'manager_id', 'key' => 'department_id'],
                    'gm' => ['table' => 'divisions', 'column' => 'gm_id', 'key' => 'division_id'],
                    'director' => ['table' => 'plants', 'column' => 'director_id', 'key' => 'plant_id'],
                ];

                $oldPositionLower = strtolower($oldPosition);
                $newPositionLower = strtolower($validatedData['position']);


                // Cek jika posisi tidak berubah tapi tempat berubah (mutasi struktural lateral)
                if (
                    isset($positionFieldMap[$oldPositionLower]) &&
                    $oldPositionLower === $newPositionLower
                ) {
                    $config = $positionFieldMap[$oldPositionLower];

                    $oldRefId = DB::table($config['table'])->where($config['column'], $employee->id)->first(); // lokasi sebelum update
                    $newRefId = $validatedData[$config['key']] ?? null; // lokasi setelah update

                    if ($oldRefId && $oldRefId->id != (int) $newRefId) {
                        DB::table($config['table'])->where('id', $oldRefId->id)->update([$config['column'] => null]);

                        // Simpan riwayat mutasi lateral
                        $this->logLateralMutation($employee->id, $oldPositionLower, $oldRefId->id, $newRefId, $config['key']);
                    }
                }

                // Simpan riwayat promosi jika ada perubahan
                if ($oldGrade !== $validatedData['grade'] || $oldPosition !== $validatedData['position']) {
                    PromotionHistory::create([
                        'employee_id' => $employee->id,
                        'previous_grade' => $oldGrade,
                        'previous_position' => $oldPosition,
                        'current_grade' => $validatedData['grade'],
                        'current_position' => $validatedData['position'],
                        'last_promotion_date' => now(),
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Karyawan tidak ditemukan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    private function logLateralMutation(int $employeeId, string $position, int $fromId, int $toId, string $structureKey)
    {
        $lastMutation = MutationHistory::where('employee_id', $employeeId)
            ->where('position', $position)
            ->where('to_id', $fromId)
            ->orderByDesc('mutation_date')
            ->first();

        $startDate = $lastMutation->mutation_date ?? $this->getApproximateEntryDate($employeeId, $position);
        $durationMonths = Carbon::parse($startDate)->diffInMonths(now());
        $durationText = $this->formatDuration($durationMonths);
        try {
            DB::beginTransaction();
            // dd($durationText);

            MutationHistory::create([
                'employee_id' => $employeeId,
                'position' => $position,
                'structure_type' => $this->getStructureTypeFromKey($structureKey),
                'from_id' => $fromId,
                'to_id' => $toId,
                'mutation_date' => now(),
                'duration_in_previous_structure' => $durationMonths,
                'duration_text' => $durationText,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd('Error saat logLateralMutation:', $e->getMessage(), $e->getTraceAsString());
        }
    }
    private function getApproximateEntryDate(int $employeeId, string $position)
    {
        return Employee::where('id', $employeeId)->value('aisin_entry_date');
    }

    private function formatDuration($months)
    {
        $years = floor($months / 12);
        $remainingMonths = $months % 12;

        $yearText = $years > 0 ? "$years tahun" : '';
        $monthText = $remainingMonths > 0 ? "$remainingMonths bulan" : '';

        return trim("$yearText $monthText");
    }

    private function getStructureTypeFromKey(string $key): string
    {
        return match ($key) {
            'sub_section_id' => 'sub_section',
            'section_id' => 'section',
            'department_id' => 'department',
            'division_id' => 'division',
            'plant_id' => 'plant',
            default => 'unknown',
        };
    }

    public function destroy($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();

        if ($employee->photo) {
            Storage::delete('public/' . $employee->photo);
        }

        $employee->delete();

        return redirect()->back()->with('success', 'Karyawan berhasil dihapus!');
    }

    public function profile($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();
        return view('website.employee.profile.index', compact('employee'));
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        try {
            Excel::import(new MasterImports, $request->file('file'));
            session()->flash('success', 'Semua data berhasil diimport!');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function workExperienceStore(Request $request)
    {
        $employee = DB::table('employees')->where('id', $request->employee_id)->exists();

        if (!$employee) {
            return back()->with('error', 'Employee tidak ditemukan!');
        }

        $request->validate([
            'position' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            WorkingExperience::create([
                'employee_id' => $request->employee_id, // Sesuaikan dengan sistem autentikasi
                'position' => $request->position,
                'company' => $request->company,
                'department' => $request->department,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pengalaman kerja berhasil ditambahkan.');
        } catch (\Throwable $th) {

            dd($th);
            DB::rollback();
            return redirect()->back()->with('error', 'Pengalaman kerja gagal ditambahkan!');
        }
    }

    public function workExperienceUpdate(Request $request, $id)
    {
        $experience = WorkingExperience::findOrFail($id);

        $request->validate([
            'position'   => 'required|string|max:255',
            'company'    => 'required|string|max:255',
            'department'    => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $experience->update([
                'position'    => $request->position,
                'company'     => $request->company,
                'department'     => $request->department,
                'start_date'  => Carbon::parse($request->start_date),
                'end_date'    => $request->end_date ? Carbon::parse($request->end_date) : null,
                'description' => $request->description,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pengalaman kerja berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Pengalaman kerja gagal diupdate.');
        }
    }

    public function workExperienceDestroy($id)
    {
        $experience = WorkingExperience::findOrFail($id);

        try {
            DB::beginTransaction();

            $experience->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Pengalaman kerja berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Pengalaman kerja gagal dihapus.');
        }
    }

    public function educationStore(Request $request)
    {
        try {
            $employeeExists = DB::table('employees')->where('id', $request->employee_id)->exists();

            if (!$employeeExists) {
                return back()->with('error', 'Employee tidak ditemukan!');
            }

            $request->validate([
                'level' => 'required',
                'major' => 'required',
                'institute' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date',
            ]);

            // Debugging setelah validasi berhasil
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkap error validasi dan tampilkan dengan back()
            return redirect()->back()->with('error', $e->getMessage());
        }

        try {
            DB::beginTransaction();

            EducationalBackground::create([
                'employee_id' => $request->employee_id,
                'educational_level' => $request->level,
                'major' => $request->major,
                'institute' => $request->institute,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Riwayat pendidikan gagal ditambahkan!');
        }
    }

    public function educationUpdate(Request $request, $id)
    {
        $education = EducationalBackground::findOrFail($id);

        $validatedData = $request->validate([
            'level' => 'required',
            'major' => 'required',
            'institute' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $education->update([
                'educational_level'       => $validatedData['level'],
                'major'       => $validatedData['major'],
                'institute'   => $validatedData['institute'],
                'start_date'  => Carbon::parse($validatedData['start_date']),
                'end_date'    => $validatedData['end_date'] ? Carbon::parse($validatedData['end_date']) : null,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Riwayat pendidikan berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Riwayat pendidikan gagal diupdate.');
        }
    }

    public function educationDestroy($id)
    {
        $experience = EducationalBackground::findOrFail($id);

        try {
            DB::beginTransaction();

            $experience->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Riwayat pendidikan berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Riwayat pendidikan gagal dihapus.');
        }
    }

    public function appraisalStore(Request $request)
    {
        try {
            $employeeExists = DB::table('employees')->where('id', $request->employee_id)->exists();

            if (!$employeeExists) {
                return back()->with('error', 'Employee tidak ditemukan!');
            }

            $validatedData = $request->validate([
                'score' => 'required',
                'description' => 'required',
                'date' => 'required|date',
            ]);

            // Debugging setelah validasi berhasil
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkap error validasi dan tampilkan dengan back()
            return redirect()->back()->with('error', $e->getMessage());
        }

        try {
            DB::beginTransaction();

            // Simpan data appraisal
            PerformanceAppraisalHistory::create([
                'employee_id' => $request->employee_id,
                'score'       => $validatedData['score'],
                'description' => $validatedData['description'],
                'date'        => Carbon::parse($validatedData['date']),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Performance appraisal berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
            return redirect()->back()->with('error', 'Performance appraisal gagal ditambahkan : ' . $th->getMessage());
        }
    }

    public function appraisalUpdate(Request $request, $id)
    {
        $appraisal = PerformanceAppraisalHistory::findOrFail($id);

        $validatedData = $request->validate([
            'score' => 'required',
            'description' => 'required',
            'date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Update dengan field yang benar
            $appraisal->update([
                'score'       => $validatedData['score'],
                'description' => $validatedData['description'],
                'date'        => Carbon::parse($validatedData['date']), // Pastikan format tanggal benar
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Performance appraisal berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Performance appraisal gagal diperbarui.');
        }
    }

    public function appraisalDestroy($id)
    {
        $appraisal = PerformanceAppraisalHistory::findOrFail($id);

        try {
            DB::beginTransaction();

            $appraisal->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Performance appraisal berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Performance appraisal gagal dihapus.');
        }
    }

    public function promotionDestroy($id)
    {
        $promotion = PromotionHistory::findOrFail($id);

        try {
            DB::beginTransaction();

            // Ambil karyawan yang dipromosikan
            $employee = Employee::findOrFail($promotion->employee_id);

            // Cari promotion history sebelumnya sebelum yang dihapus
            $lastPromotion = PromotionHistory::where('employee_id', $employee->id)
                ->where('id', '<', $promotion->id) // Hanya cari promosi sebelum yang sedang dihapus
                ->orderBy('id', 'desc')
                ->first();

            if ($lastPromotion) {
                // Jika ada history sebelumnya, rollback grade & position ke data tersebut
                $employee->position = $lastPromotion->previous_position;
                $employee->grade = $lastPromotion->previous_grade;
            } else {
                // Jika tidak ada promotion history sebelumnya, reset ke nilai default awal
                $employee->position = $promotion->previous_position;
                $employee->grade = $promotion->previous_grade;
            }

            $employee->save();

            // Hapus promotion history
            $promotion->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Promotion history berhasil dihapus dan posisi/grade dikembalikan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Promotion history gagal dihapus.');
        }
    }

    public function astraTrainingStore(Request $request)
    {
        try {
            // Cek apakah employee_id ada di database
            $employeeExists = DB::table('employees')->where('id', $request->employee_id)->exists();
            if (!$employeeExists) {
                return back()->with('error', 'Employee tidak ditemukan!');
            }

            // Validasi input
            $validatedData = $request->validate([
                'year'           => 'required|digits:4|integer',
                'program'        => 'required|string|max:255',
                'ict_score'      => 'required',
                'project_score'  => 'required',
                'total_score'    => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Simpan data ke AstraTraining
            AstraTraining::create([
                'employee_id'   => $request->employee_id,
                'year'          => $validatedData['year'],
                'program'       => $validatedData['program'],
                'ict_score'     => $validatedData['ict_score'],
                'project_score' => $validatedData['project_score'],
                'total_score'   => $validatedData['total_score'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data Astra Training berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan data Astra Training: ' . $th->getMessage());
        }
    }

    public function astraTrainingUpdate(Request $request, $id)
    {
        try {
            // Cek apakah data AstraTraining dengan ID yang diberikan ada
            $astraTraining = AstraTraining::find($id);
            if (!$astraTraining) {
                return back()->with('error', 'Data Astra Training tidak ditemukan!');
            }

            // Validasi input
            $validatedData = $request->validate([
                'year'           => 'required|digits:4|integer',
                'program'        => 'required|string|max:255',
                'ict_score'      => 'required',
                'project_score'  => 'required',
                'total_score'    => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update data AstraTraining
            $astraTraining->update([
                'year'          => $validatedData['year'],
                'program'       => $validatedData['program'],
                'ict_score'     => $validatedData['ict_score'],
                'project_score' => $validatedData['project_score'],
                'total_score'   => $validatedData['total_score'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data Astra Training berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui data Astra Training: ' . $th->getMessage());
        }
    }

    public function astraTrainingDestroy($id)
    {
        try {
            // Cek apakah data AstraTraining dengan ID yang diberikan ada
            $astraTraining = AstraTraining::find($id);
            if (!$astraTraining) {
                return back()->with('error', 'Data Astra Training tidak ditemukan!');
            }

            DB::beginTransaction();

            // Hapus data AstraTraining
            $astraTraining->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Data Astra Training berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data Astra Training: ' . $th->getMessage());
        }
    }

    public function externalTrainingStore(Request $request)
    {
        try {
            // Cek apakah employee_id ada di database
            $employeeExists = DB::table('employees')->where('id', $request->employee_id)->exists();
            if (!$employeeExists) {
                return back()->with('error', 'Employee tidak ditemukan!');
            }

            // Validasi input
            $validatedData = $request->validate([
                'program' => 'required|string|max:255',
                'year'    => 'required|digits:4|integer',
                'vendor'  => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Simpan data ke ExternalTraining
            ExternalTraining::create([
                'employee_id' => $request->employee_id,
                'year'        => $validatedData['year'],
                'program'     => $validatedData['program'],
                'vendor'      => $validatedData['vendor'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data External Training berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan data External Training: ' . $th->getMessage());
        }
    }

    public function externalTrainingUpdate(Request $request, $id)
    {
        try {
            // Cek apakah data training dengan ID tersebut ada
            $externalTraining = ExternalTraining::findOrFail($id);

            // Validasi input
            $validatedData = $request->validate([
                'program' => 'required|string|max:255',
                'year'    => 'required|digits:4|integer',
                'vendor'  => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update data di database
            $externalTraining->update([
                'year'    => $validatedData['year'],
                'program' => $validatedData['program'],
                'vendor'  => $validatedData['vendor'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data External Training berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui data External Training: ' . $th->getMessage());
        }
    }


    public function externalTrainingDestroy($id)
    {
        try {
            // Cek apakah data training dengan ID tersebut ada
            $externalTraining = ExternalTraining::findOrFail($id);

            DB::beginTransaction();

            // Hapus data dari database
            $externalTraining->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Data External Training berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data External Training: ' . $th->getMessage());
        }
    }
}
