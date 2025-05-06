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

        // Jika HRD, bisa melihat semua employee dan assessment dalam satu perusahaan (jika ada filter company)
        if ($user->role === 'HRD' || $employee->position == 'Director') {
            $table = 'Division';
            $divisions = Division::where('company', $company)->get();
            $employees = Employee::whereIn('position', ['Manager','Coordinator'])->get();
        } else {
            $table = 'Department';
            // Ambil data divisi yang dipegang user login
            $data = Division::where('gm_id', $employee->id)->first();
            $divisions = Department::where('division_id', $data->id)->get();
            $employees = Employee::whereIn('position', ['Supervisor','Section Head'])->get();
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
                // Ambil data berdasarkan department
                $data = Department::with(['manager', 'short','mid','long'])->find($id);
                break;

            case 'section':
                // Ambil data berdasarkan section
                $data = Section::with(['supervisor', 'short','mid','long'])->find($id);
                break;

            case 'sub_section':
                // Ambil data berdasarkan sub_section
                $data = SubSection::with(['leader', 'short','mid','long'])->find($id);
                break;

            default:
                // Jika filter tidak sesuai, beri pesan atau arahkan ke halaman lain
                return redirect()->route('rtc.index')->with('error', 'Invalid filter');
        }
    
        // Jika data tidak ditemukan
        if (!$data) {
            return redirect()->route('rtc.index')->with('error', ucfirst($filter) . ' not found');
        }

        if ($request->ajax()) {
            return view('website.modal.rtc.index', compact('data', 'filter'));
        }

        // Return view dengan data yang sesuai
        return view('website.rtc.detail', compact('data', 'filter'));
    }

    public function summary(Request $request)
    {
        $filter = $request->query('filter'); // e.g. 'division'
        $id = (int) $request->query('id');   // Division ID

        $data = Division::with(['gm', 'short', 'mid', 'long'])->findOrFail($id);

        // Ambil semua departments berdasarkan division_id
        $departments = Department::where('division_id', $data->id)->get();

        // Ambil semua sections berdasarkan department_id
        $departmentIds = $departments->pluck('id');
        $sections = Section::whereIn('department_id', $departmentIds)->get();

        // Ambil semua manager_id dari departments dan supervisor_id dari sections
        $managerIds = $departments->pluck('manager_id')->filter()->unique();
        $supervisorIds = $sections->pluck('supervisor_id')->filter()->unique();

        // Gabungkan dan ambil data karyawan dari ID tersebut
        $employeeIds = $managerIds->merge($supervisorIds)->unique();
        $bawahans = Employee::whereIn('id', $employeeIds)->get();

        foreach ($bawahans as $manager) {
            // Cari section yang berhubungan dengan manager
            $sections = Section::where('supervisor_id', $manager->id)->get();

            if($manager->position == 'Section Head' || $manager->position == 'Supervisor'){
                $related = Section::with(['short', 'mid', 'long'])
                    ->where('supervisor_id', $manager->id)
                    ->first();
            }else{
                $related = Department::with(['short', 'mid', 'long'])
                    ->where('manager_id', $manager->id)
                    ->first();
            }


            // Ambil supervisor dari setiap section yang berhubungan dengan manager
            $supervisors = $sections->map(function ($section) {
                return $section->supervisor; // Mengambil relasi supervisor di section
            });
        
            // Simpan supervisor (section head) pada manager
            $manager->supervisors = $supervisors->unique('id')->filter();
            $manager->planning = $related;
        }               
        
        return view('website.rtc.detail', compact('data', 'filter', 'bawahans'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'short_term' => 'nullable|exists:employees,id',
            'mid_term' => 'nullable|exists:employees,id',
            'long_term' => 'nullable|exists:employees,id',
        ]);

        $filter = $request->input('filter');
        $id = $request->input('id');

        $modelClass = match ($filter) {
            'division' => \App\Models\Division::class,
            'department' => \App\Models\Department::class,
            'section' => \App\Models\Section::class,
            'sub_section' => \App\Models\SubSection::class,
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
    }
}
