<?php

namespace App\Http\Controllers;

use App\Helpers\RtcHelper;
use App\Models\Rtc;
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

        $rtcs = Rtc::all();
        
        return view('website.rtc.index', compact('divisions', 'employees', 'table', 'rtcs'));
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
        $filter = strtolower($request->query('filter'));
        $id = (int) $request->query('id');
        $data = null;

        $departmentIds = [$id];
        $managerIds = collect();

        if ($filter === 'division') {
            $data = Division::with(['gm', 'short', 'mid', 'long'])->findOrFail($id);
            $departments = Department::where('division_id', $data->id)->get();
            $departmentIds = $departments->pluck('id');
            $managerIds = $departments->pluck('manager_id')->filter()->unique();
        } else {
            $data = Department::with(['manager', 'short', 'mid', 'long'])->findOrFail($id);
            $managerIds = collect([$data->manager_id])->filter();
        }

        $sections = Section::whereIn('department_id', $departmentIds)->get();
        $supervisorIds = $sections->pluck('supervisor_id')->filter()->unique();

        $employeeIds = $managerIds->merge($supervisorIds)->unique();
        $bawahans = Employee::whereIn('id', $employeeIds)->get();

        // Generate color mapping
        $colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7'];
        $assignedColors = [];
        $colorIndex = 0;

        // Current main card
        $field = match ($filter) {
            'division' => 'gm',
            'department' => 'manager',
            default => null,
        };

        $main = [
            'title' => $data->name ?? '-',
            'person' => RtcHelper::formatPerson($data->{$field} ?? null),
            'shortTerm' => RtcHelper::formatCandidate($data->short ?? null),
            'midTerm' => RtcHelper::formatCandidate($data->mid ?? null),
            'longTerm' => RtcHelper::formatCandidate($data->long ?? null),
        ];

        $managers = [];

        foreach ($departmentIds as $departmentId) {
            $department = Department::with(['manager', 'short', 'mid', 'long'])
                ->find($departmentId);

            if (!$department)
                continue;

            if (!isset($assignedColors[$departmentId])) {
                $assignedColors[$departmentId] = $colors[$colorIndex++ % count($colors)];
            }

            $managerPerson = RtcHelper::formatPerson($department->manager);
            $isManagerSameAsMain = RtcHelper::isSamePerson($main['person'], $managerPerson);

            // Collect supervisors first
            $supervisors = [];
            $relatedSections = Section::where('department_id', $departmentId)
                ->with(['supervisor', 'short', 'mid', 'long'])
                ->get();

            foreach ($relatedSections as $section) {
                if (!$section->supervisor)
                    continue;

                $supervisorPerson = RtcHelper::formatPerson($section->supervisor);

                // Skip if supervisor is same as main person or manager
                if (
                    RtcHelper::isSamePerson($main['person'], $supervisorPerson) ||
                    RtcHelper::isSamePerson($managerPerson, $supervisorPerson)
                ) {
                    continue;
                }

                $supervisors[] = [
                    'title' => $section->name,
                    'person' => $supervisorPerson,
                    'shortTerm' => RtcHelper::formatCandidate($section->short),
                    'midTerm' => RtcHelper::formatCandidate($section->mid),
                    'longTerm' => RtcHelper::formatCandidate($section->long),
                    'colorClass' => $assignedColors[$departmentId],
                ];
            }

            // Only add manager if it's different from main OR if it has supervisors
            if (!$isManagerSameAsMain || count($supervisors) > 0) {
                $managers[$departmentId] = [
                    'title' => $department->name,
                    'person' => $managerPerson,
                    'shortTerm' => RtcHelper::formatCandidate($department->short),
                    'midTerm' => RtcHelper::formatCandidate($department->mid),
                    'longTerm' => RtcHelper::formatCandidate($department->long),
                    'colorClass' => $assignedColors[$departmentId],
                    'supervisors' => $supervisors,
                    'skipManagerNode' => $isManagerSameAsMain, // Flag untuk frontend
                ];
            }
        }

        return view('website.rtc.detail', [
            'main' => $main,
            'managers' => array_values($managers),
            'title' => $data->name,
        ]);
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
            
            $terms = [
                'short' => $request->input('short_term'),
                'mid' => $request->input('mid_term'),
                'long' => $request->input('long_term'),
            ];

            // update rtc table
            foreach ($terms as $term => $employeeId) {
                Rtc::create([
                    'employee_id' => $employeeId,
                    'area' => $filter,
                    'area_id' => $id,
                    'term' => $term,
                    'status' => 0,
                ]);
            }

            session()->flash('success', 'Plan submited successfully');

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

        $checkRtc = Rtc::with('employee')
            ->where('status', 0)
            ->whereIn('employee_id', $subCheck)
            ->get();        

        $checkRtcIds = $checkRtc->pluck('id')->toArray();

        $approveRtc = Rtc::with('employee')
            ->where('status', 1)
            ->whereIn('employee_id', $subApprove)
            ->get();

        $rtcs = $checkRtc->merge($approveRtc);

        return view('website.approval.rtc.index', compact('rtcs'));
    }

    public function approve($id)
    {
        $rtc = Rtc::findOrFail($id);

        if ($rtc->status == 0) {
            $rtc->status = 1;
            $rtc->save();

            return response()->json([
                'message' => 'rtc has been approved!'
            ]);
        }

        if ($rtc->status == 1) {
            $rtc->status = 2;
            $rtc->save();

            $area = strtolower($rtc->area);

            // update planning 
            $modelClass = match ($area) {
                'division' => \App\Models\Division::class,
                'department' => \App\Models\Department::class,
                'section' => \App\Models\Section::class,
                'sub_section' => \App\Models\SubSection::class,
                default => throw new \Exception("Invalid filter value: $area")
            };

            $record = $modelClass::findOrFail($rtc->area_id);
            
            $record->update([
                $rtc->term . '_term' => $rtc->employee_id
            ]);

            return response()->json([
                'message' => 'rtc has been approved!'
            ]);
        }

        return response()->json([
            'message' => 'Something went wrong!'
        ], 400);
    }
}
