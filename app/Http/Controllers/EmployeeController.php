<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Hav;
use App\Models\Idp;
use App\Models\User;
use App\Models\Plant;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Assessment;
use App\Models\Department;
use App\Models\SubSection;
use App\Models\HavQuadrant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AstraTraining;
use App\Imports\MasterImports;
use App\Imports\EmployeeImport;
use App\Models\GradeConversion;
use App\Models\MutationHistory;
use App\Models\ExternalTraining;
use App\Models\PromotionHistory;
use App\Models\PerformanceMaster;
use App\Models\WorkingExperience;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\EducationalBackground;
use Illuminate\Support\Facades\Storage;
use App\Models\PerformanceAppraisalHistory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    private function getSubordinatesFromStructure(Employee $employee)
    {
        $subordinateIds = collect();

        if ($employee->leadingPlant && $employee->leadingPlant->director_id === $employee->id) {
            $divisions      = Division::where('plant_id', $employee->leadingPlant->id)->get();
            $subordinateIds = $this->collectSubordinates($divisions, 'gm_id', $subordinateIds);

            $departments    = Department::whereIn('division_id', $divisions->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($departments, 'manager_id', $subordinateIds);

            $sections       = Section::whereIn('department_id', $departments->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections    = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingDivision && $employee->leadingDivision->gm_id === $employee->id) {
            $departments    = Department::where('division_id', $employee->leadingDivision->id)->get();
            $subordinateIds = $this->collectSubordinates($departments, 'manager_id', $subordinateIds);

            $sections       = Section::whereIn('department_id', $departments->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections    = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingDepartment && $employee->leadingDepartment->manager_id === $employee->id) {
            $sections       = Section::where('department_id', $employee->leadingDepartment->id)->get();
            $subordinateIds = $this->collectSubordinates($sections, 'supervisor_id', $subordinateIds);

            $subSections    = SubSection::whereIn('section_id', $sections->pluck('id'))->get();
            $subordinateIds = $this->collectSubordinates($subSections, 'leader_id', $subordinateIds);

            $subordinateIds = $this->collectOperators($subSections, $subordinateIds);
        } elseif ($employee->leadingSection && $employee->leadingSection->supervisor_id === $employee->id) {
            $subSections    = SubSection::where('section_id', $employee->leadingSection->id)->get();
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
        $operatorIds   = Employee::whereIn('sub_section_id', $subSectionIds)->pluck('id');
        return $subordinateIds->merge($operatorIds);
    }

    public function index(Request $request, $company = null)
    {
        $title        = 'Employee';
        $user         = auth()->user();
        $search       = $request->input('search');
        $filter       = $request->input('filter', 'all');  // Menambahkan filter, default 'all'
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

        $rawPosition     = $user->employee->position ?? 'Operator';
        $currentPosition = Str::contains($rawPosition, 'Act ')
            ? trim(str_replace('Act', '', $rawPosition))
            :  $rawPosition;

        // Cari index posisi saat ini
        $positionIndex = array_search($currentPosition, $allPositions);

        // Fallback jika tidak ditemukan
        if ($positionIndex === false) {
            $positionIndex = array_search('Operator', $allPositions);
        }

        // Ambil posisi di bawahnya (tanpa posisi user)
        $visiblePositions = $positionIndex !== false
            ? array_slice($allPositions, $positionIndex)
            :  [];

        if ($user->isHRDorDireksi()) {
            // HRD bisa mencari berdasarkan beberapa kolom, termasuk company_name
            $employees = Employee::with([
                'subSection.section.department',
                'leadingSection.department',
                'leadingDepartment.division'
            ])
                ->when($company, fn($query) => $query->where('company_name', $company))  // Filter berdasarkan perusahaan yang sedang diakses
                ->where(function ($q) use ($visiblePositions) {
                    foreach ($visiblePositions as $pos) {
                        $q->orWhere('position', $pos)
                            ->orWhere('position', 'like', "Act %{$pos}");
                    }
                })
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('npk', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");  // Pencarian di seluruh kolom
                    });
                })
                ->when($filter && $filter != 'all', function ($query) use ($filter) {
                    $query->where(function ($q) use ($filter) {
                        $q->where('position', $filter)
                            ->orWhere('position', 'like', "Act %{$filter}");
                    });
                })

                ->get();
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
                        $query->where(function ($q) use ($filter) {
                            $q->where('position', $filter)
                                ->orWhere('position', 'like', "Act %{$filter}");
                        });
                    }


                    // Paginate hasil
                    $employees = $query->paginate(10)->appends([
                        'search'  => $search,
                        'filter'  => $filter,
                        'company' => $company
                    ]);
                } else {
                    $employees = collect();
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

        $rawPosition     = $user->employee->position ?? 'Operator';
        $currentPosition = Str::contains($rawPosition, 'Act ')
            ? trim(str_replace('Act', '', $rawPosition))
            :  $rawPosition;

        // Cari index posisi saat ini
        $positionIndex = array_search($currentPosition, $allPositions);

        // Fallback jika tidak ditemukan
        if ($positionIndex === false) {
            $positionIndex = array_search('Operator', $allPositions);
        }

        // Ambil posisi di bawahnya (tanpa posisi user)
        $visiblePositions = $positionIndex !== false
            ? array_slice($allPositions, $positionIndex)
            :  [];

        // Hilangkan 'Operator' jika posisi user ada 'President'
        if ($currentPosition === 'President') {
            $visiblePositions = array_filter($visiblePositions, function ($pos) {
                return $pos !== 'Operator' && $pos !== 'President';
            });
            $visiblePositions = array_values($visiblePositions);  // reset index
        }

        return view('website.employee.index', compact('employees', 'title', 'filter', 'company', 'visiblePositions'));
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
        $title       = 'Add Employee';
        $departments = Department::all();
        $divisions   = Division::all();
        $plants      = Plant::all();
        $sections    = Section::all();
        $grade       = GradeConversion::all();
        $subSections = SubSection::all();
        return view('website.employee.create', compact('title', 'departments', 'grade', 'divisions', 'plants', 'sections', 'subSections'));
    }

    /**
     * Simpan data karyawan ke database
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validasi
            $validator = Validator::make($request->all(), [
                'npk'              => 'required|string|max:255|unique:employees,npk',
                'name'             => 'required|string|max:255',
                'birthday_date'    => 'nullable|date',
                'gender'           => 'required|in:Male,Female',
                'company_name'     => 'required|string',
                'phone_number'     => 'nullable|string|max:14',
                'aisin_entry_date' => 'required|date',
                'company_group'    => 'nullable|string',
                'email'            => 'required|email',
                'position'         => 'required|string',
                'grade'            => 'nullable|string',
                'photo'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

                // Struktur organisasi
                'plant_id'       => 'nullable|exists:plants,id',
                'division_id'    => 'nullable|exists:divisions,id',
                'department_id'  => 'nullable|exists:departments,id',
                'section_id'     => 'nullable|exists:sections,id',
                'sub_section_id' => 'nullable|exists:sub_sections,id',

                // Pendidikan
                'level'        => 'array',
                'level.*'      => 'nullable|string|max:255',
                'major.*'      => 'nullable|string|max:255',
                'institute.*'  => 'nullable|string|max:255',
                'start_date.*' => 'nullable|string|max:255',
                'end_date.*'   => 'nullable|string|max:255',

                // Pengalaman kerja
                'company.*'         => 'nullable|string|max:255',
                'work_position.*'   => 'nullable|string|max:255',
                'work_start_date.*' => 'nullable|string|max:255',
                'work_end_date.*'   => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $validatedData = $validator->validate();

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

            $inputEmail = $validatedData['email'];
            if (User::where('email', $inputEmail)->exists()) {
                // Balikkan error 422 khusus 'email'
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['email' => ['This email is already used. Please enter a different email address.']]
                    ], 422);
                }
                return back()->withErrors(['email' => 'This email is already used.'])->withInput();
            }

            // Buat karyawan
            $employee = Employee::create($validatedData);

            // Simpan pendidikan
            foreach ($request->level ?? [] as $i => $level) {
                if ($level) {
                    EducationalBackground::create([
                        'employee_id'       => $employee->id,
                        'educational_level' => $level,
                        'major'             => $request->major[$i] ?? null,
                        'institute'         => $request->institute[$i] ?? null,
                        'start_date'        => $request->start_date[$i] ?? null,
                        'end_date'          => $request->end_date[$i] ?? null,
                    ]);
                }
            }

            // Simpan pengalaman kerja
            foreach ($request->company ?? [] as $i => $company) {
                if ($company) {
                    WorkingExperience::create([
                        'employee_id' => $employee->id,
                        'company'     => $company,
                        'position'    => $request->work_position[$i] ?? null,
                        'start_date'  => $request->work_start_date[$i] ?? null,
                        'end_date'    => $request->work_end_date[$i] ?? null,
                    ]);
                }
            }

            // Simpan ke struktur sesuai posisi
            $pos = strtolower($validatedData['position']);

            // Mapping posisi ke entitas dan kolom yang perlu diupdate
            $roleMappings = [
                'sub_section' => [
                    'roles'  => ['act jp', 'operator', 'jp'],
                    'update' => fn()                         => $employee->update([
                        'sub_section_id' => $validatedData['sub_section_id'] ?? null,
                    ]),
                ],
                'sub_section_leader' => [
                    'roles'  => ['act leader', 'leader'],
                    'update' => fn()                     => $validatedData['sub_section_id'] &&
                        DB::table('sub_sections')->where('id', $validatedData['sub_section_id'])
                        ->update(['leader_id' => $employee->id]),
                ],
                'section' => [
                    'roles'  => ['act supervisor', 'act section head', 'supervisor', 'section head'],
                    'update' => fn()                                                                 => $validatedData['section_id'] &&
                        DB::table('sections')->where('id', $validatedData['section_id'])
                        ->update(['supervisor_id' => $employee->id]),
                ],
                'department' => [
                    'roles'  => ['act manager', 'act coordinator', 'manager', 'coordinator'],
                    'update' => fn()                                                         => $validatedData['department_id'] &&
                        DB::table('departments')->where('id', $validatedData['department_id'])
                        ->update(['manager_id' => $employee->id]),
                ],
                'division' => [
                    'roles'  => ['act gm', 'gm'],
                    'update' => fn()             => $validatedData['division_id'] &&
                        DB::table('divisions')->where('id', $validatedData['division_id'])
                        ->update(['gm_id' => $employee->id]),
                ],
                'plant' => [
                    'roles'  => ['director'],
                    'update' => fn()         => $validatedData['plant_id'] &&
                        DB::table('plants')->where('id', $validatedData['plant_id'])
                        ->update(['director_id' => $employee->id]),
                ],
            ];

            // Jalankan update berdasarkan role
            foreach ($roleMappings as $map) {
                if (in_array($pos, $map['roles'])) {
                    $map['update']();
                    break;
                }
            }

            // Role yang butuh dibuatkan user
            $userRoles = [
                'manager',
                'act manager',
                'act supervisor',
                'act section head',
                'supervisor',
                'section head',
                'act gm',
                'gm',
                'director',
                'operator'
            ];

            if (in_array($pos, $userRoles)) {
                $user = User::create([
                    'name'                => $validatedData['name'],
                    'email'               => $inputEmail,
                    'password'            => bcrypt('aiia'),
                    'is_first_login'      => true,
                    'password_changed_at' => null
                ]);
                $employee->update(['user_id' => $user->id]);
            }

            DB::commit();
            // Jika AJAX, balas JSON success (tanpa refresh)
            if ($request->ajax()) {
                return response()->json([
                    'message'      => 'Employee added successfully!',
                    'redirect_url' => route('employee.master.index', ['company' => $validatedData['company_name']]),
                ], 200);
            }

            return redirect()->route('employee.master.index', ['company' => $employee->company_name])
                ->with('success', 'Employee added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('employee.master.index', ['company' => $request->input('company_name')])
                ->with('error', 'Error Message: ' . $e->getMessage());
        }
    }

    private function findSupervisor($position, $departmentId)
    {
        $hierarchy = [
            'GM'           => null,
            'Manager'      => 'GM',
            'Coordinator'  => 'Manager',
            'Section Head' => 'Coordinator',
            'Supervisor'   => 'Section Head'
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
    public function show($id)
    {
        try {
            // 1) Ambil user & employee berdasarkan user_id
            $user = User::findOrFail($id);

            $employee = Employee::with([
                'subSection.section.department',
                'leadingSection.department',
                'leadingDepartment.division',
                'user'
            ])
                ->where('user_id', $user->id)
                ->firstOrFail();

            // 2) Ambil data terkait berdasarkan employee_id (lebih tegas daripada by npk)
            $promotionHistories = PromotionHistory::with('employee')
                ->where('employee_id', $employee->id)
                ->orderBy('last_promotion_date', 'desc')
                ->get();

            $astraTrainings = AstraTraining::with('employee')
                ->where('employee_id', $employee->id)
                ->orderBy('date_end', 'desc')
                ->get();

            $externalTrainings = ExternalTraining::with('employee')
                ->where('employee_id', $employee->id)
                ->orderBy('date_end', 'desc')
                ->get();

            $educations = EducationalBackground::with('employee')
                ->where('employee_id', $employee->id)
                ->orderBy('end_date', 'desc')
                ->get();

            $workExperiences = WorkingExperience::with('employee')
                ->where('employee_id', $employee->id)
                ->orderByRaw('ISNULL(end_date) DESC') // untuk MySQL; kalau PostgreSQL pakai "end_date IS NULL DESC"
                ->orderByDesc('end_date')
                ->get();

            $performanceAppraisals = PerformanceAppraisalHistory::with('employee')
                ->where('employee_id', $employee->id)
                ->orderBy('date', 'desc')
                ->get();

            $assessment = Assessment::with(['details.alc', 'employee'])
                ->where('employee_id', $employee->id)
                ->latest()
                ->first();

            $idps = Idp::with(['alc', 'assessment.employee'])
                ->whereHas('assessment', function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id);
                })
                ->get();

            $humanAssets = Hav::with('employee')
                ->where('employee_id', $employee->id)
                ->select('quadrant', 'year', DB::raw('COUNT(*) as count'))
                ->groupBy('quadrant', 'year')
                ->orderByDesc('year')
                ->get();

            // 3) Master data untuk tampilan
            $departments = Department::all();
            $divisions   = Division::where('company', $employee->company_name)->get();
            $plants      = Plant::all();

            return view('website.employee.show', compact(
                'employee',
                'humanAssets',
                'promotionHistories',
                'educations',
                'workExperiences',
                'performanceAppraisals',
                'departments',
                'astraTrainings',
                'externalTrainings',
                'assessment',
                'idps',
                'divisions',
                'plants'
            ))->with('mode', 'view');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Data tidak ditemukan untuk user tersebut.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error Message: ' . $e->getMessage());
        }
    }

    public function edit($userId)
    {
        // 1) Ambil employee berdasarkan user_id (unik per jabatan)
        $employee = Employee::with([
            'subSection.section.department',
            'leadingSection.department',
            'leadingDepartment.division'
        ])->where('user_id', $userId)->firstOrFail();

        // 2) Simpan NPK untuk query person-level
        $npk = $employee->npk;

        $grade = GradeConversion::all();

        // ----- Person-level (pakai NPK) -----
        $promotionHistories = PromotionHistory::with('employee')
            ->whereHas('employee', fn($q) => $q->where('npk', $npk))
            ->orderByDesc('last_promotion_date')
            ->get();

        $astraTrainings = AstraTraining::with('employee')
            ->whereHas('employee', fn($q) => $q->where('npk', $npk))
            ->orderByDesc('date_end')
            ->get();

        $externalTrainings = ExternalTraining::with('employee')
            ->whereHas('employee', fn($q) => $q->where('npk', $npk))
            ->orderByDesc('date_end')
            ->get();

        $educations = EducationalBackground::with('employee')
            ->whereHas('employee', fn($q) => $q->where('npk', $npk))
            ->orderByDesc('end_date')
            ->get();

        $workExperiences = WorkingExperience::with('employee')
            ->whereHas('employee', fn($q) => $q->where('npk', $npk))
            ->orderByRaw('ISNULL(end_date) DESC')
            ->orderByDesc('end_date')
            ->get();

        $performanceAppraisals = PerformanceAppraisalHistory::with('employee')
            ->whereHas('employee', fn($q) => $q->where('npk', $npk))
            ->orderByDesc('date')
            ->get();

        $assessment = Assessment::with('details.alc', 'employee')
            ->whereHas('employee', fn($q) => $q->where('npk', $npk))
            ->latest()
            ->first();

        $idps = Idp::with('alc', 'assessment.employee')
            ->whereHas('assessment.employee', fn($q) => $q->where('npk', $npk))
            ->get();

        // ----- Jabatan-level (pakai employee_id spesifik baris yg dipilih) -----
        $humanAssets = Hav::with('employee')
            ->where('employee_id', $employee->id)
            ->select('quadrant', 'year', DB::raw('COUNT(*) as count'))
            ->groupBy('quadrant', 'year')
            ->orderByDesc('year')
            ->get();

        // ----- Dropdown/filter mengikuti company dari baris employee terpilih -----
        $positions   = Employee::where('npk', $npk)->pluck('position')->unique()->values();
        $plants      = Plant::all();
        $departments = Department::where('company', $employee->company_name)->get();
        $divisions   = Division::where('company', $employee->company_name)->get();
        $sections    = Section::where('company', $employee->company_name)->get();
        $subSections = SubSection::with('section')
            ->whereHas('section', fn($q) => $q->where('company', $employee->company_name))
            ->get();

        $scores      = PerformanceMaster::select('code')->distinct()->pluck('code');

        // ==== Gabungkan semua ke satu collection ====
        $allOptions = collect();

        $allOptions = $allOptions->merge(
            $plants->map(fn($x) => [
                'type'  => 'plant',
                'id'    => $x->id,
                'name'  => $x->name,
                'label' => '[Plant] ' . $x->name,
                'group' => 'Plant',
                'value' => 'plant|' . $x->id,
            ])
        )->merge(
            $divisions->map(fn($x) => [
                'type'  => 'division',
                'id'    => $x->id,
                'name'  => $x->name,
                'label' => '[Division] ' . $x->name,
                'group' => 'Division',
                'value' => 'division|' . $x->id,
            ])
        )->merge(
            $departments->map(fn($x) => [
                'type'  => 'department',
                'id'    => $x->id,
                'name'  => $x->name,
                'label' => '[Department] ' . $x->name,
                'group' => 'Department',
                'value' => 'department|' . $x->id,
            ])
        )->merge(
            $sections->map(fn($x) => [
                'type'  => 'section',
                'id'    => $x->id,
                'name'  => $x->name,
                'label' => '[Section] ' . $x->name,
                'group' => 'Section',
                'value' => 'section|' . $x->id,
            ])
        )->merge(
            $subSections->map(fn($x) => [
                'type'  => 'sub_section',
                'id'    => $x->id,
                'name'  => $x->name,
                'label' => '[Sub Section] ' . $x->name,
                'group' => 'Sub Section',
                'value' => 'sub_section|' . $x->id,
            ])
        );

        $allOptions = $allOptions->sortBy('label')->values();

        // === Selected per WorkExperience (PARSE dari kolom department yang berprefix)
        $resolveSelected = function ($exp) use ($plants, $divisions, $departments, $sections, $subSections) {
            // Format disimpan: "[Section] Assembly 1"
            if (
                !empty($exp->department) &&
                preg_match('/^\[(Plant|Division|Department|Section|Sub Section)\]\s*(.+)$/i', $exp->department, $m)
            ) {

                $typeLabel = strtolower($m[1]); // 'section' | 'sub section' | ...
                $name      = trim($m[2]);

                $typeMap = [
                    'plant'        => 'plant',
                    'division'     => 'division',
                    'department'   => 'department',
                    'section'      => 'section',
                    'sub section'  => 'sub_section',
                ];
                $typeKey = $typeMap[$typeLabel] ?? null;

                if ($typeKey) {
                    $id = match ($typeKey) {
                        'plant'       => optional($plants->firstWhere('name', $name))->id,
                        'division'    => optional($divisions->firstWhere('name', $name))->id,
                        'department'  => optional($departments->firstWhere('name', $name))->id,
                        'section'     => optional($sections->firstWhere('name', $name))->id,
                        'sub_section' => optional($subSections->firstWhere('name', $name))->id,
                        default       => null,
                    };
                    if ($id) return $typeKey . '|' . $id;
                }
            }

            // Fallback: kalau string department tanpa prefix, coba cocokkan Department
            if (!empty($exp->department)) {
                if ($id = optional($departments->firstWhere('name', $exp->department))->id) {
                    return 'department|' . $id;
                }
            }
            return null;
        };

        // tempelkan selected ke setiap experience
        $workExperiences = $workExperiences->map(function ($exp) use ($resolveSelected) {
            $exp->selected_org_scope = $resolveSelected($exp);
            return $exp;
        });

        return view('website.employee.update', compact(
            'employee',
            'grade',
            'humanAssets',
            'positions',
            'promotionHistories',
            'educations',
            'workExperiences',
            'performanceAppraisals',
            'departments',
            'astraTrainings',
            'externalTrainings',
            'assessment',
            'idps',
            'divisions',
            'plants',
            'sections',
            'subSections',
            'scores',
            'allOptions',
        ))->with('mode', 'edit');
    }

    public function update(Request $request,  Employee $employee)
    {
        try {
            $oldGrade    = $employee->grade;
            $oldPosition = $employee->position;

            $validatedData = $request->validate([
                // NPK tidak dibuat unique karena 1 orang (multi jabatan) bisa share NPK yang sama
                'npk'              => ['nullable', 'string', 'max:255'],
                'name'             => ['nullable', 'string', 'max:255'],
                'birthday_date'    => ['nullable', 'date'],
                'gender'           => ['nullable', 'in:Male,Female'],
                'company_name'     => ['nullable', 'string'],
                'phone_number'     => ['nullable', 'string'],
                'aisin_entry_date' => ['nullable', 'date'],
                'company_group'    => ['nullable', 'string'],
                'position'         => ['nullable', 'string'],
                'grade'            => ['nullable', 'string'],
                'photo'            => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

                'plant_id'         => ['nullable', 'exists:plants,id'],
                'division_id'      => ['nullable', 'exists:divisions,id'],
                'department_id'    => ['nullable', 'exists:departments,id'],
                'section_id'       => ['nullable', 'exists:sections,id'],
                'sub_section_id'   => ['nullable', 'exists:sub_sections,id'],
            ]);

            DB::transaction(function () use ($request, $employee, &$validatedData, $oldGrade, $oldPosition) {

                // Foto
                if ($request->hasFile('photo')) {
                    if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                        Storage::disk('public')->delete($employee->photo);
                    }
                    $validatedData['photo'] = $request->file('photo')->store('employee_photos', 'public');
                }

                // Working period (kalau join date diisi)
                if (!empty($validatedData['aisin_entry_date'])) {
                    $validatedData['working_period'] = Carbon::parse($validatedData['aisin_entry_date'])
                        ->diffInYears(now());
                }

                // Cari supervisor_id dari hirarki yang dipilih
                $supervisorId = null;
                if (!empty($validatedData['sub_section_id'])) {
                    $supervisorId = DB::table('sub_sections')->where('id', $validatedData['sub_section_id'])->value('leader_id');
                } elseif (!empty($validatedData['section_id'])) {
                    $supervisorId = DB::table('sections')->where('id', $validatedData['section_id'])->value('supervisor_id');
                } elseif (!empty($validatedData['department_id'])) {
                    $supervisorId = DB::table('departments')->where('id', $validatedData['department_id'])->value('manager_id');
                } elseif (!empty($validatedData['division_id'])) {
                    $supervisorId = DB::table('divisions')->where('id', $validatedData['division_id'])->value('gm_id');
                } elseif (!empty($validatedData['plant_id'])) {
                    $supervisorId = DB::table('plants')->where('id', $validatedData['plant_id'])->value('director_id');
                }
                $validatedData['supervisor_id'] = $supervisorId;

                // Update field utama employee
                $employee->update($validatedData);

                // ===================== PROMOSI / MUTASI STRUKTUR =====================
                $positionAliasMap = [
                    'section head'     => 'supervisor',
                    'act section head' => 'supervisor',
                    'coordinator'      => 'manager',
                    'act coordinator'  => 'manager',
                    'act manager'      => 'manager',
                    'act supervisor'   => 'supervisor',
                    'act leader'       => 'leader',
                    'act jp'           => 'jp',
                    'act gm'           => 'gm',
                ];

                $promotionPaths = [
                    'operator'   => ['leader'     => ['clear' => 'sub_section_id']],
                    'jp'         => ['leader'     => ['clear' => 'sub_section_id']],
                    'leader'     => ['supervisor' => ['table' => 'sub_sections', 'column' => 'leader_id',     'key' => 'sub_section_id']],
                    'supervisor' => ['manager'    => ['table' => 'sections',     'column' => 'supervisor_id', 'key' => 'section_id']],
                    'manager'    => ['gm'         => ['table' => 'departments',  'column' => 'manager_id',    'key' => 'department_id']],
                    'gm'         => ['director'   => ['table' => 'divisions',    'column' => 'gm_id',         'key' => 'division_id']],
                ];

                $normalize = function ($position) use ($positionAliasMap) {
                    $lower = strtolower($position);
                    return $positionAliasMap[$lower] ?? $lower;
                };

                $newPosition     = $validatedData['position'] ?? $employee->position; // fallback kalau tidak diubah
                $oldNorm         = $normalize($oldPosition);
                $newNorm         = $normalize($newPosition);

                // Jika ada path promosi, kosongkan penautan lama sesuai aturan
                if (isset($promotionPaths[$oldNorm][$newNorm])) {
                    $action = $promotionPaths[$oldNorm][$newNorm];

                    if (isset($action['clear'])) {
                        $employee->update([$action['clear'] => null]);
                    } elseif (isset($action['table'], $action['column'], $action['key'])) {
                        $ref = DB::table($action['table'])->where($action['column'], $employee->id)->first();
                        if ($ref) {
                            DB::table($action['table'])->where('id', $ref->id)->update([$action['column'] => null]);
                        }
                    }
                }

                // Mapping role → update struktur (penempatan baru)
                $posLower = strtolower($newPosition);

                $roleMappings = [
                    'sub_section' => [
                        'roles'  => ['act jp', 'operator', 'jp'],
                        'update' => function () use ($validatedData, $employee) {
                            $employee->update(['sub_section_id' => $validatedData['sub_section_id'] ?? null]);
                        },
                    ],
                    'sub_section_leader' => [
                        'roles'  => ['act leader', 'leader'],
                        'update' => function () use ($validatedData, $employee) {
                            if (!empty($validatedData['sub_section_id'])) {
                                DB::table('sub_sections')->where('id', $validatedData['sub_section_id'])
                                    ->update(['leader_id' => $employee->id]);
                            }
                        },
                    ],
                    'section' => [
                        'roles'  => ['act supervisor', 'act section head', 'supervisor', 'section head'],
                        'update' => function () use ($validatedData, $employee) {
                            if (!empty($validatedData['section_id'])) {
                                DB::table('sections')->where('id', $validatedData['section_id'])
                                    ->update(['supervisor_id' => $employee->id]);
                            }
                        },
                    ],
                    'department' => [
                        'roles'  => ['act manager', 'act coordinator', 'manager', 'coordinator'],
                        'update' => function () use ($validatedData, $employee) {
                            if (!empty($validatedData['department_id'])) {
                                DB::table('departments')->where('id', $validatedData['department_id'])
                                    ->update(['manager_id' => $employee->id]);
                            }
                        },
                    ],
                    'division' => [
                        'roles'  => ['act gm', 'gm'],
                        'update' => function () use ($validatedData, $employee) {
                            if (!empty($validatedData['division_id'])) {
                                DB::table('divisions')->where('id', $validatedData['division_id'])
                                    ->update(['gm_id' => $employee->id]);
                            }
                        },
                    ],
                    'plant' => [
                        'roles'  => ['director'],
                        'update' => function () use ($validatedData, $employee) {
                            if (!empty($validatedData['plant_id'])) {
                                DB::table('plants')->where('id', $validatedData['plant_id'])
                                    ->update(['director_id' => $employee->id]);
                            }
                        },
                    ],
                ];

                foreach ($roleMappings as $map) {
                    if (in_array($posLower, $map['roles'])) {
                        $map['update']();
                        break;
                    }
                }

                // Mutasi lateral (posisi sama, lokasi/entitas pindah)
                $positionFieldMap = [
                    'leader'     => ['table' => 'sub_sections', 'column' => 'leader_id',     'key' => 'sub_section_id'],
                    'supervisor' => ['table' => 'sections',     'column' => 'supervisor_id', 'key' => 'section_id'],
                    'manager'    => ['table' => 'departments',  'column' => 'manager_id',    'key' => 'department_id'],
                    'gm'         => ['table' => 'divisions',    'column' => 'gm_id',         'key' => 'division_id'],
                    'director'   => ['table' => 'plants',       'column' => 'director_id',   'key' => 'plant_id'],
                ];

                if (
                    isset($positionFieldMap[$normalize($oldPosition)]) &&
                    $normalize($oldPosition) === $normalize($newPosition)
                ) {

                    $cfg     = $positionFieldMap[$normalize($oldPosition)];
                    $oldRef  = DB::table($cfg['table'])->where($cfg['column'], $employee->id)->first();
                    $newRefId = $validatedData[$cfg['key']] ?? null;

                    if ($oldRef && (int)$oldRef->id !== (int)$newRefId) {
                        DB::table($cfg['table'])->where('id', $oldRef->id)->update([$cfg['column'] => null]);
                        // log mutasi lateral (optional – fungsi Anda sendiri)
                        if (method_exists($this, 'logLateralMutation')) {
                            $this->logLateralMutation($employee->id, $normalize($oldPosition), $oldRef->id, $newRefId, $cfg['key']);
                        }
                    }
                }

                // Riwayat promosi
                if (($oldGrade !== ($validatedData['grade'] ?? $employee->grade)) ||
                    ($oldPosition !== $newPosition)
                ) {
                    PromotionHistory::create([
                        'employee_id'         => $employee->id,
                        'previous_grade'      => $oldGrade,
                        'previous_position'   => $oldPosition,
                        'current_grade'       => $validatedData['grade'] ?? $employee->grade,
                        'current_position'    => $newPosition,
                        'last_promotion_date' => now(),
                    ]);
                }
            });

            return redirect()
                ->route('employee.master.index', ['company' => $employee->company_name])
                ->with('success', 'Employee data updated successfully!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->with('error', 'Karyawan tidak ditemukan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Error Message: ' . $e->getMessage());
        }
    }

    private function logLateralMutation(int $employeeId, string $position, int $fromId, int $toId, string $structureKey)
    {
        $lastMutation = MutationHistory::where('employee_id', $employeeId)
            ->where('position', $position)
            ->where('to_id', $fromId)
            ->orderByDesc('mutation_date')
            ->first();

        $startDate      = $lastMutation->mutation_date ?? $this->getApproximateEntryDate($employeeId, $position);
        $durationMonths = Carbon::parse($startDate)->diffInMonths(now());
        $durationText   = $this->formatDuration($durationMonths);
        try {
            DB::beginTransaction();

            MutationHistory::create([
                'employee_id'                    => $employeeId,
                'position'                       => $position,
                'structure_type'                 => $this->getStructureTypeFromKey($structureKey),
                'from_id'                        => $fromId,
                'to_id'                          => $toId,
                'mutation_date'                  => now(),
                'duration_in_previous_structure' => $durationMonths,
                'duration_text'                  => $durationText,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
    private function getApproximateEntryDate(int $employeeId, string $position)
    {
        return Employee::where('id', $employeeId)->value('aisin_entry_date');
    }

    private function formatDuration($months)
    {
        $years           = floor($months / 12);
        $remainingMonths = $months % 12;

        $yearText  = $years > 0 ? "$years tahun" : '';
        $monthText = $remainingMonths > 0 ? "$remainingMonths bulan" : '';

        return trim("$yearText $monthText");
    }

    private function getStructureTypeFromKey(string $key): string
    {
        return match ($key) {
            'sub_section_id' => 'sub_section',
            'section_id'     => 'section',
            'department_id'  => 'department',
            'division_id'    => 'division',
            'plant_id'       => 'plant',
            default          => 'unknown',
        };
    }

    public function destroy($npk)
    {
        $employee = Employee::where('id', $npk)->firstOrFail();

        if ($employee->photo) {
            Storage::delete('public/' . $employee->photo);
        }

        $employee->delete();  // otomatis hapus user juga via model

        return redirect()->route('employee.master.index')->with('success', 'Karyawan dan akun pengguna berhasil dihapus!');
    }

    public function profile($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();
        return view('website.employee.profile.index', compact('employee'));
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',   // hanya menerima .xlsx saja
        ], [
            'file.mimes' => 'File harus berformat .xlsx sesuai template yang disediakan.',
        ]);

        try {
            Excel::import(new MasterImports, $request->file('file'));
            session()->flash('success', 'Semua data berhasil diimport!');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat mengimport. Pastikan file sesuai template!');
        }

        return redirect()->back();
    }

    public function workExperienceStore(Request $request)
    {
        Log::debug($request->all());

        $exists = DB::table('employees')->where('id', $request->employee_id)->exists();
        if (!$exists) {
            return back()->with('error', 'Employee tidak ditemukan!');
        }

        $request->validate([
            'employee_id' => 'required|integer',
            'position'    => 'required|string|max:255',
            'org_scope'   => ['required', 'regex:/^(plant|division|department|section|sub_section)\|\d+$/'],
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            [$type, $id] = explode('|', $request->input('org_scope'), 2);

            [$labelPrefix, $name] = match ($type) {
                'plant'        => ['[Plant] ',       Plant::find($id)?->name],
                'division'     => ['[Division] ',    Division::find($id)?->name],
                'department'   => ['[Department] ',  Department::find($id)?->name],
                'section'      => ['[Section] ',     Section::find($id)?->name],
                'sub_section'  => ['[Sub Section] ', SubSection::find($id)?->name],
            };

            if (!$name) {
                return back()->with('error', 'Organizational scope tidak valid!');
            }

            $scopedText = $labelPrefix . $name;

            WorkingExperience::create([
                'employee_id' => $request->employee_id,
                'position'    => $request->position,
                'department'  => $scopedText,              // simpan gabungan ke field tunggal
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date ?: null,
                'description' => $request->description,
            ]);

            DB::commit();
            return back()->with('success', 'Pengalaman kerja berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return back()->with('error', 'Pengalaman kerja gagal ditambahkan!');
        }
    }

    public function workExperienceUpdate(Request $request, $id)
    {
        $experience = WorkingExperience::findOrFail($id);

        $request->validate([
            'position'    => 'required|string|max:255',
            // izinkan pola standar ATAU legacy
            'org_scope'   => ['required', 'regex:/^(?:(?:plant|division|department|section|sub_section)\|\d+|__legacy__\|.+)$/'],
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $org = $request->input('org_scope');

            if (str_starts_with($org, '__legacy__|')) {
                // user tidak memilih ulang -> pakai apa adanya dari DB/option legacy
                $scopedText = substr($org, strlen('__legacy__|'));
            } else {
                // pola standar type|id → ambil nama dan bentuk "[Prefix] Nama"
                [$type, $scopeId] = explode('|', $org, 2);

                [$prefix, $name] = match ($type) {
                    'plant'        => ['[Plant] ',       \App\Models\Plant::find($scopeId)?->name],
                    'division'     => ['[Division] ',    \App\Models\Division::find($scopeId)?->name],
                    'department'   => ['[Department] ',  \App\Models\Department::find($scopeId)?->name],
                    'section'      => ['[Section] ',     \App\Models\Section::find($scopeId)?->name],
                    'sub_section'  => ['[Sub Section] ', \App\Models\SubSection::find($scopeId)?->name],
                    default        => [null, null],
                };

                if (!$name) {
                    return back()->with('error', 'Organizational scope tidak valid!');
                }

                $scopedText = $prefix . $name;
            }

            $experience->update([
                'position'    => $request->position,
                'department'  => $scopedText, // tetap simpan ke kolom tunggal
                'start_date'  => $request->start_date ? \Carbon\Carbon::parse($request->start_date) : null,
                'end_date'    => $request->end_date ? \Carbon\Carbon::parse($request->end_date) : null,
                'description' => $request->description,
            ]);

            DB::commit();
            return back()->with('success', 'Pengalaman kerja berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error($th);
            return back()->with('error', 'Pengalaman kerja gagal diupdate.');
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
                'level'      => 'required',
                'major'      => 'required',
                'institute'  => 'required',
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date',
            ]);

            // Debugging setelah validasi berhasil
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkap error validasi dan tampilkan dengan back()
            return redirect()->back()->with('error', $e->getMessage());
        }

        try {
            DB::beginTransaction();

            EducationalBackground::create([
                'employee_id'       => $request->employee_id,
                'educational_level' => $request->level,
                'major'             => $request->major,
                'institute'         => $request->institute,
                'start_date'        => $request->start_date ?: null,
                'end_date'          => $request->end_date ?: null,
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
            'level'      => 'required',
            'major'      => 'required',
            'institute'  => 'required',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $education->update([
                'educational_level' => $validatedData['level'],
                'major'             => $validatedData['major'],
                'institute'         => $validatedData['institute'],
                'start_date'        => $validatedData['start_date'] ? Carbon::parse($validatedData['start_date']) : null,
                'end_date'          => $validatedData['end_date'] ? Carbon::parse($validatedData['end_date']) : null,
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
                // 'description' => 'required',
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
            $appraisal = PerformanceAppraisalHistory::create([
                'employee_id' => $request->employee_id,
                'score'       => $validatedData['score'],
                'description' => $validatedData['description'] ?? null,
                'date'        => Carbon::parse($validatedData['date']),
            ]);

            // Get year hav terakhir
            $havLastYear = Hav::where('employee_id', $appraisal->employee_id)->first()->year;

            // Update HAV Quadran
            (new HavQuadrant())->updateHavFromPerformance($appraisal->employee_id, (int) $havLastYear);

            DB::commit();
            return redirect()->back()->with('success', 'Performance appraisal berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Performance appraisal gagal ditambahkan : ' . $th->getMessage());
        }
    }

    public function appraisalUpdate(Request $request, $id)
    {
        $appraisal = PerformanceAppraisalHistory::findOrFail($id);

        $validatedData = $request->validate([
            'score' => 'required',
            'date'  => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Update dengan field yang benar
            $appraisal->update([
                'score'       => $validatedData['score'],
                'description' => $validatedData['description'] ?? null,
                'date'        => Carbon::parse($validatedData['date']),   // Pastikan format tanggal benar
            ]);

            // Get year hav terakhir
            $havLastYear = Hav::where('employee_id', $appraisal->employee_id)->first()->year;

            // Update HAV Quadran
            (new HavQuadrant())->updateHavFromPerformance($appraisal->employee_id, (int) $havLastYear);

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

            // Get year hav terakhir
            $havLastYear = Hav::where('employee_id', $appraisal->employee_id)->first()->year;

            // Update HAV Quadran
            (new HavQuadrant())->updateHavFromPerformance($appraisal->employee_id, (int) $havLastYear);

            DB::commit();
            return redirect()->back()->with('success', 'Performance appraisal berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Performance appraisal gagal dihapus.');
        }
    }

    public function promotionStore(Request $request)
    {
        $request->validate([
            'previous_grade'      => 'required|string|max:255',
            'previous_position'   => 'required|string|max:255',
            'current_grade'       => 'required|string|max:255',
            'current_position'    => 'required|string|max:255',
            'last_promotion_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            PromotionHistory::create([
                'employee_id'         => $request->employee_id,
                'previous_grade'      => $request->previous_grade,
                'previous_position'   => $request->previous_position,
                'current_grade'       => $request->current_grade,
                'current_position'    => $request->current_position,
                'last_promotion_date' => $request->last_promotion_date,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Promotion berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Promotion gagal ditambahkan.');
        }
    }

    public function promotionUpdate(Request $request, $id)
    {
        $experience = PromotionHistory::findOrFail($id);

        $request->validate([
            'previous_grade'      => 'required',
            'previous_position'   => 'required',
            'current_grade'       => 'required',
            'current_position'    => 'required',
            'last_promotion_date' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $experience->update([
                'previous_grade'      => $request->previous_grade,
                'previous_position'   => $request->previous_position,
                'current_grade'       => $request->current_grade,
                'current_position'    => $request->current_position,
                'last_promotion_date' => $request->last_promotion_date,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Promotion berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Promotion gagal diupdate.');
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
                ->where('id', '<', $promotion->id)  // Hanya cari promosi sebelum yang sedang dihapus
                ->orderBy('id', 'desc')
                ->first();

            $employee->save();

            // Hapus promotion history
            $promotion->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Promotion history berhasil dihapus.');
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
                'program'       => 'required|string|max:255',
                'ict_score'     => 'required',
                'project_score' => 'required',
                'total_score'   => 'required',
                'date_start'    => 'required',
                'date_end'      => 'required',
                'institusi'     => 'required',

            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Simpan data ke AstraTraining
            AstraTraining::create([
                'employee_id'   => $request->employee_id,
                'program'       => $validatedData['program'],
                'ict_score'     => $validatedData['ict_score'],
                'project_score' => $validatedData['project_score'],
                'total_score'   => $validatedData['total_score'],
                'date_start'    => $validatedData['date_start'],
                'date_end'      => $validatedData['date_end'],
                'institusi'     => $validatedData['institusi'],
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
                'date_start'    => 'required',
                'date_end'      => 'required',
                'program'       => 'required|string|max:255',
                'ict_score'     => 'required',
                'project_score' => 'required',
                'total_score'   => 'required',
                'institusi'     => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update data AstraTraining
            $astraTraining->update([
                'date_start'    => $validatedData['date_start'],
                'date_end'      => $validatedData['date_end'],
                'institusi'     => $validatedData['institusi'],
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
                'program'    => 'required|string|max:255',
                'vendor'     => 'required|string|max:255',
                'date_start' => 'required',
                'date_end'   => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Simpan data ke ExternalTraining
            ExternalTraining::create([
                'employee_id' => $request->employee_id,
                'date_start'  => $validatedData['date_start'],
                'date_end'    => $validatedData['date_end'],
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
                'program'    => 'required|string|max:255',
                'date_start' => 'required',
                'date_end'   => 'required',
                'vendor'     => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update data di database
            $externalTraining->update([
                'date_start' => $validatedData['date_start'],
                'date_end'   => $validatedData['date_end'],
                'program'    => $validatedData['program'],
                'vendor'     => $validatedData['vendor'],
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

    public function checkEmail(Request $request)
    {
        $email = trim($request->query('email', ''));

        $existsInUsers     = User::where('email', $email)->exists();

        return response()->json([
            'email' => $email,
            'exists_in_users'     => $existsInUsers,
            'exists'              => $existsInUsers,
        ]);
    }
}
