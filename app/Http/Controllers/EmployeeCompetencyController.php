<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCompetency;
use App\Models\Competency;
use App\Models\Employee;
use App\Models\GroupCompetency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EmployeeCompetencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($company = null)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Unauthorized');
        }

        $title = 'Employee Competency';

        // Hierarki posisi tertinggi → terendah
        $hierarchy = [
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

        // Ambil semua karyawan (HRD akan melihat semua)
        $emps = Employee::with('employeeCompetencies.competency.group_competency')
            ->when($company, fn($q) => $q->where('company_name', $company))
            ->get();

        // Cek posisi user sekarang
        $myPos   = $user->employee->position;
        $myIndex = array_search($myPos, $hierarchy);
        if ($myIndex === false) {
            $myIndex = count($hierarchy) - 1;
        }

        // Posisi yang boleh dia lihat di tab
        $positionsAllowed = $user->role === 'HRD'
            ? $hierarchy
            : array_slice($hierarchy, $myIndex + 1);

        // Jika bukan HRD, filter data karyawan yang lebih rendah levelnya
        if ($user->role !== 'HRD') {
            $emps = $emps->filter(function($e) use ($hierarchy, $myIndex) {
                $otherIndex = array_search($e->position, $hierarchy);
                return $otherIndex !== false && $otherIndex > $myIndex;
            })->values();
        }

        // Mapping ke matrixData
        $matrixData = $emps->map(function($e) {
            return [
                'id'       => $e->id,
                'name'     => $e->name,
                'position' => $e->position,
                'comps'    => $e->employeeCompetencies->map(fn($ec) => [
                    'group' => $ec->competency->group_competency->name,
                    'name'  => $ec->competency->name,
                    'act'   => $ec->act,
                    'plan'  => $ec->competency->plan,
                ])->toArray(),
            ];
        })->toArray();

        $groups = GroupCompetency::pluck('name')->toArray();

        return view('website.employee_competency.index', compact(
            'title',
            'matrixData',
            'groups',
            'company',
            'positionsAllowed'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $employees = Employee::all();
            
        return view('website.employee_competency.create', compact('employees'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|array',
            'employee_id.*' => 'exists:employees,id',
            'due_date' => 'required|date|after_or_equal:today'
        ]);

        $createdCount = 0;

        foreach ($request->employee_id as $employeeId) {
            $employee = Employee::with('departments')->findOrFail($employeeId);
            $department = $employee->departments->first();

            // ambil competency yang cocok
            $competencies = collect();
            if ($department) {
                $competencies = Competency::where([
                    'department_id' => $department->id,
                    'position' => $employee->position
                ])->get();
            }

            // **Hanya** buat record kalau ada competency
            if ($competencies->isNotEmpty()) {
                foreach ($competencies as $competency) {
                    // hanya buat kalau belum ada
                    if (! EmployeeCompetency::where([
                        'employee_id'   => $employeeId,
                        'competency_id' => $competency->id
                    ])->exists()) {
                        EmployeeCompetency::create([
                            'employee_id'   => $employeeId,
                            'competency_id' => $competency->id,
                            'due_date'      => $request->due_date,
                            'weight'        => 0,
                            'plan'          => 0,
                            'act'           => 0,
                        ]);
                        $createdCount++;
                    }
                }
            }
        }

        // Response
        $message = $createdCount
            ? "Berhasil menambahkan {$createdCount} competency."
            : "Tidak ada competency yang cocok untuk ditambahkan.";

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => $message,
                'redirect' => route('employeeCompetencies.index'),
            ]);
        }

        return redirect()
            ->route('employeeCompetencies.index')
            ->with('success', $message);
    }

    public function checksheet($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Load checkSheets dengan filter position
        $employee->load(['employeeCompetencies.competency' => function($query) use ($employee) {
            $query->with(['checkSheets' => function($q) use ($employee) {
                $q->where('position', $employee->position);
            }]);
        }]);

        $competencies = $employee->employeeCompetencies->map(function($ec) {
            return [
                'id' => $ec->competency->id,
                'name' => $ec->competency->name,
                'checksheets' => $ec->competency->checkSheets->map(function($cs) {
                    return [
                        'id' => $cs->id,
                        'name' => $cs->name,
                        'date' => $cs->created_at->format('Y-m-d'),
                    ];
                })
            ];
        });

        return response()->json(['competencies' => $competencies]);
    }

    public function getEmployees(Request $request)
    {
        $position = $request->input('position');
        $departmentId = $request->input('department_id');

        $employees = Employee::where('position', $position)
            ->whereHas('departments', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })->get();

        return response()->json($employees);
    }

    
    public function getCompetencies(Request $request)
    {
        $position = $request->input('position');
        $departmentId = $request->input('department_id');
        $employeeId = $request->input('employee_id');

        $existingCompetencyIds = EmployeeCompetency::where('employee_id', $employeeId)
            ->pluck('competency_id')
            ->toArray();
        $competencies = Competency::where('position', $position)
            ->where('department_id', $departmentId)
            ->whereNotIn('id', $existingCompetencyIds)
            ->get();

        return response()->json($competencies);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = Employee::with([
            'employeeCompetencies.competency.department', 
            'departments'
        ])->findOrFail($id);

        // Ambil department dan posisi karyawan
        $department = $employee->departments->first();
        $position = $employee->position;

        // Query kompetensi berdasarkan department dan posisi
        $competencies = Competency::when($department, function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->where('position', $position)
            ->get();

        return view('website.employee_competency.show', compact('employee', 'competencies'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $employee_competencies = EmployeeCompetency::with(['competency', 'department', 'employee'])->find($id);

        if (!$employee_competencies) {
            return response()->json(['error' => 'Employee Competency not found'], 404);
        }

        return response()->json([
            'id' => $employee_competencies->id,
            'employee_id' => $employee_competencies->employee_id,
            'competency_id' => $employee_competencies->competency_id,
            'weight' => $employee_competencies->weight,
            'plan' => $employee_competencies->plan,
            'act' => $employee_competencies->act,
            'due_date' => $employee_competencies->due_date,
            'all_competency' => Competency::all(),
            'all_employee' => Employee::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);

        try {
            $employeeCompetency = EmployeeCompetency::findOrFail($id);

            if ($request->hasFile('file')) {
                if ($employeeCompetency->files) {
                    Storage::delete($employeeCompetency->files);
                }
                
                $originalName = $request->file('file')->getClientOriginalName();
                $directory = 'employee_competency_files/' . $employeeCompetency->employee_id;
                $path = $request->file('file')->storeAs($directory, $originalName, 'public');
                
                $employeeCompetency->update([
                    'files' => $path,
                    'status' => 0
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Upload gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeCompetency $employeeCompetency)
    {
        try {
            $employeeCompetency->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Competency berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }
}
