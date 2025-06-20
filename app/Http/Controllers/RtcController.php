<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Department;
use App\Models\SubSection;
use Illuminate\Http\Request;

class RtcController extends Controller
{
    public function index($company = null)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if($company == null){
            $company = $user->employee->company_name;
        }

        // Jika HRD, bisa melihat semua employee dan assessment dalam satu perusahaan (jika ada filter company)
        if ($user->isHRDorDireksi()) {
            $table = 'Division';
        
            $divisions = Division::where('company', $company)->get();
            $employees = Employee::whereIn('position', ['Manager', 'Coordinator'])->get();
        } else {
            if ($employee->position === 'Direktur') {
                $table = 'Division';
                $plant = $employee->plant;
        
                $divisions = Division::where('company', $company)
                    ->where('plant_id', $plant->id)
                    ->get();
        
                $employees = Employee::whereIn('position', ['Manager', 'Coordinator'])
                                        ->where('company_name', $employee->company_name)
                                        ->get();
            } else {
                $table = 'Department';
        
                $division = Division::where('gm_id', $employee->id)->first();
                $divisions = Department::where('division_id', $division?->id)->get();
                $employees = Employee::whereIn('position', ['Supervisor', 'Section Head'])
                                        ->where('company_name', $employee->company_name)
                                        ->get();
            }
        }        
        
        return view('website.rtc.index', compact('divisions', 'employees', 'table'));
    }

    public function list(Request $request)
    {
        $user = auth()->user()->load('employee');
        
        $divisionId = $request->query()['id'];
        $employees = Employee::with('leadingDepartment', 'leadingSection', 'leadingSubSection')->select('id', 'name', 'position')->get();
        return view('website.rtc.list', compact('employees', 'divisionId', 'user'));
    }

    public function detail(Request $request)
    {
        // Ambil filter dan id dari query string
        $filter = $request->query('filter'); // department / section / sub_section
        $id = (int) $request->query('id'); // id karyawan
        
        // Tentukan model yang akan dipanggil berdasarkan nilai filter
        switch ($filter) {
            case 'department':
                $relation = 'manager';
                $subLeading = 'leadingSection';
                $data = Department::with([$relation, 'short', 'mid', 'long'])->find($id);
                break;
        
            case 'section':
                $relation = 'supervisor';
                $subLeading = 'leadingSubSection';
                $data = Section::with([$relation, 'short', 'mid', 'long'])->find($id);
                break;
        
            case 'sub_section':
                $relation = 'leader';
                $subLeading = '';
                $data = SubSection::with([$relation, 'short', 'mid', 'long'])->find($id);
                break;
        
            default:
                return redirect()->route('rtc.index')->with('error', 'Invalid filter');
        }
    
        // Jika data tidak ditemukan
        if (!$data) {
            return redirect()->route('rtc.index')->with('error', ucfirst($filter) . ' not found');
        }

        if ($request->ajax() && $subLeading) {
            $subordinates = $data->$relation->getSubordinatesByLevel(1);
            $subordinates->each(function ($subordinate) use ($subLeading) {
                $subordinate->load([
                    $subLeading => function ($query) {
                        return $query->with(['short', 'mid', 'long']);
                    }
                ]);
            });

            return view('website.modal.rtc.index', compact('data', 'filter', 'subordinates'));
        }

        // Return view dengan data yang sesuai
        return view('website.rtc.detail', compact('data', 'filter'));
    }

    public function summary(Request $request)
    {
        $filter = $request->query('filter');
        $id = (int) $request->query('id');

        $data = null; // ✅ prevent undefined variable
        $departmentIds = [$id];
        $managerIds = collect();
        
        if ($filter === 'Division') {
            $data = Division::with(['gm', 'short', 'mid', 'long'])->findOrFail($id);
            $departments = Department::where('division_id', $data->id)->get();
            $departmentIds = $departments->pluck('id');
            $managerIds = $departments->pluck('manager_id')->filter()->unique();
        }else{
            $data = Department::with(['manager', 'short', 'mid', 'long'])->findOrFail($id);
        }

        $sections = Section::whereIn('department_id', $departmentIds)->get();
        $supervisorIds = $sections->pluck('supervisor_id')->filter()->unique();
        $employeeIds = $managerIds->merge($supervisorIds)->unique();
        $bawahans = Employee::whereIn('id', $employeeIds)->get();

        foreach ($bawahans as $employee) {
            $relatedSections = Section::where('supervisor_id', $employee->id)->get();

            if (in_array(strtolower($employee->position), ['supervisor', 'section head'])) {
                $related = $relatedSections->first()?->load(['short', 'mid', 'long']);
            } else {
                $related = Department::with(['short', 'mid', 'long'])
                    ->where('manager_id', $employee->id)
                    ->first();
            }

            $employee->supervisors = $relatedSections->map(fn($sec) => $sec->supervisor)->unique('id')->filter();
            $employee->planning = $related;

            $approvalLevel = $employee->getFirstApproval();
            $employee->superiors = $employee->getSuperiorsByLevel($approvalLevel);
        }

        $filter = strtolower($filter);
        
        return view('website.rtc.detail', compact('data', 'filter', 'bawahans'));
    }


    public function update(Request $request)
    {
        try {
            $request->validate([
                'short_term' => 'nullable|exists:employees,id',
                'mid_term' => 'nullable|exists:employees,id',
                'long_term' => 'nullable|exists:employees,id',
            ]);

            $filter = $request->input('filter');
            $id = $request->input('id');

            $filter = strtolower($filter);

            $modelClass = match ($filter) {
                'division' => \App\Models\Division::class,
                'department' => \App\Models\Department::class,
                'section' => \App\Models\Section::class,
                'sub_section' => \App\Models\SubSection::class,
                default => throw new \Exception("Invalid filter value: $filter")
            };

            $record = $modelClass::findOrFail($id);

            $updateData = collect(['short_term', 'mid_term', 'long_term'])
                ->filter(fn($field) => $request->filled($field))
                ->mapWithKeys(fn($field) => [$field => $request->input($field)])
                ->toArray();

            $record->update($updateData);

            session()->flash('success', 'Plan updated successfully');

            return response()->json([
                'status' => 'success',
                'message' => 'Plan updated successfully',
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $th->getMessage(),
            ], 500);
        }
    }

}
