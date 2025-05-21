<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeCompetency;
use App\Models\GroupCompetency;
use App\Models\Plant;
use App\Models\Section;
use App\Models\SubSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PHPUnit\TextUI\XmlConfiguration\GroupCollection;

class CompetencyController extends Controller
{
    /** Tampilkan daftar Competency */
    public function index()
    {
        $competencies = Competency::with([
            'group_competency',
            'sub_section.section.department.division.plant',
            'section.department.division.plant',
            'department.division.plant',
            'division.plant',
            'plant'
        ])->paginate(10);
    
        $group       = GroupCompetency::all();
        $subSections = SubSection::all();
        $sections    = Section::all();
        $departments = Department::all();
        $divisions   = Division::all();
        $plants      = Plant::all();
        $groups      = GroupCompetency::all();
    
        return view('website.competency.index', compact(
            'competencies','groups','subSections','sections',
            'departments','divisions','plants'
        ));
    }

    /** Form tambah Competency */
    public function create()
    {
        return view('website.competency.create', [
            'groupCompetencies' => GroupCompetency::all(),
            'departments'       => Department::all(),
            'subSections'       => SubSection::all(),
            'sections'          => Section::all(),
            'divisions'         => Division::all(),
            'plants'            => Plant::all(),
            'employees'         => Employee::all(),
        ]);
    }

    /** Simpan Competency baru + assign ke karyawan */
    public function store(Request $r)
    {
        // 1. Validasi dengan conditional FK fields
        $data = $r->validate([
            'name'                => 'required|string|max:191',
            'group_competency_id' => 'required|exists:group_competency,id',
            'position'            => 'required|string',
            'weight'              => 'required|integer|min:0|max:4',
            'plan'                => 'required|integer|min:0|max:4',

            'sub_section_id' => [
                Rule::requiredIf(fn() => in_array($r->position, ['Operator','JP','Leader'])),
                'nullable','exists:sub_sections,id'
            ],
            'section_id' => [
                Rule::requiredIf(fn() => in_array($r->position, ['Supervisor','Section Head'])),
                'nullable','exists:sections,id'
            ],
            'department_id' => [
                Rule::requiredIf(fn() => in_array($r->position, ['Manager','Coordinator'])),
                'nullable','exists:departments,id'
            ],
            'division_id' => [
                Rule::requiredIf(fn() => $r->position === 'GM'),
                'nullable','exists:divisions,id'
            ],
            'plant_id' => [
                Rule::requiredIf(fn() => $r->position === 'Director'),
                'nullable','exists:plants,id'
            ],
        ]);

        // 2. Simpan master competency
        $competency = Competency::create($data);

        // 3. Cari karyawan yang match position + FK
        $query = Employee::where('position', $competency->position);

        switch ($competency->position) {
            case 'Operator':
            case 'JP':
            case 'Leader':
                $query->where('sub_section_id', $competency->sub_section_id);
                break;

            case 'Supervisor':
            case 'Section Head':
                $query->where('section_id', $competency->section_id);
                break;

            case 'Manager':
            case 'Coordinator':
                // jika di employees ada kolom department_id
                $query->where('department_id', $competency->department_id);
                break;

            case 'GM':
                $query->where('division_id', $competency->division_id);
                break;

            case 'Director':
                $query->where('plant_id', $competency->plant_id);
                break;

            default:
                // tidak assign ke siapa‑siapa
                $query->whereRaw('0 = 1');
        }

        $employees = $query->get();

        // 4. Bulk insert mapping ke employee_competency
        $now    = now();
        $insert = [];

        foreach ($employees as $emp) {
            if (! EmployeeCompetency::where([
                'employee_id'   => $emp->id,
                'competency_id' => $competency->id
            ])->exists()) {
                $insert[] = [
                    'employee_id'   => $emp->id,
                    'competency_id' => $competency->id,
                    'act'           => 0,
                    'status'        => 0,
                    'due_date'      => null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        if (count($insert)) {
            EmployeeCompetency::insert($insert);
        }

        return response()->json(['message' => 'Competency added successfully!'], 200);
    }

    /** Form edit Competency (untuk modal) */
    public function edit($id)
    {
        $c = Competency::findOrFail($id);

        return response()->json([
            'id'                   => $c->id,
            'name'                 => $c->name,
            'group_competency_id'  => $c->group_competency_id,
            'position'             => $c->position,
            'weight'               => $c->weight,
            'plan'                 => $c->plan,
            'sub_section_id'       => $c->sub_section_id,
            'section_id'           => $c->section_id,
            'department_id'        => $c->department_id,
            'division_id'          => $c->division_id,
            'plant_id'             => $c->plant_id,
            'all_groups'           => GroupCompetency::all(),
            'all_departments'      => Department::all(),
            'all_sub_sections'     => SubSection::all(),
            'all_sections'         => Section::all(),
            'all_divisions'        => Division::all(),
            'all_plants'           => Plant::all(),
            'all_positions'        => [
                'Director','GM','Manager','Coordinator',
                'Section Head','Supervisor','Leader','JP','Operator'
            ],
        ]);
    }

    /** Update Competency + re-map karyawan */
    public function update(Request $r, $id)
    {
        // validasi sama seperti store
        $data = $r->validate([
            'name'                => 'required|string|max:191',
            'group_competency_id' => 'required|exists:group_competency,id',
            'position'            => 'required|string',
            'weight'              => 'required|integer|min:0|max:4',
            'plan'                => 'required|integer|min:0|max:4',

            'sub_section_id' => [
                Rule::requiredIf(fn() => in_array($r->position, ['Operator','JP','Leader'])),
                'nullable','exists:sub_sections,id'
            ],
            'section_id' => [
                Rule::requiredIf(fn() => in_array($r->position, ['Supervisor','Section Head'])),
                'nullable','exists:sections,id'
            ],
            'department_id' => [
                Rule::requiredIf(fn() => in_array($r->position, ['Manager','Coordinator'])),
                'nullable','exists:departments,id'
            ],
            'division_id' => [
                Rule::requiredIf(fn() => $r->position === 'GM'),
                'nullable','exists:divisions,id'
            ],
            'plant_id' => [
                Rule::requiredIf(fn() => $r->position === 'Director'),
                'nullable','exists:plants,id'
            ],
        ]);

        $competency = Competency::findOrFail($id);
        $competency->update($data);

        // hapus mapping lama
        EmployeeCompetency::where('competency_id', $competency->id)->delete();

        // re-map pakai logic yang sama dengan store
        $query = Employee::where('position', $competency->position);
        switch ($competency->position) {
            case 'Operator':
            case 'JP':
            case 'Leader':
                $query->where('sub_section_id', $competency->sub_section_id);
                break;
            case 'Supervisor':
            case 'Section Head':
                $query->where('section_id', $competency->section_id);
                break;
            case 'Manager':
            case 'Coordinator':
                $query->where('department_id', $competency->department_id);
                break;
            case 'GM':
                $query->where('division_id', $competency->division_id);
                break;
            case 'Director':
                $query->where('plant_id', $competency->plant_id);
                break;
            default:
                $query->whereRaw('0 = 1');
        }
        $employees = $query->get();

        $now    = now();
        $insert = [];
        foreach ($employees as $emp) {
            $insert[] = [
                'employee_id'   => $emp->id,
                'competency_id' => $competency->id,
                'act'           => 0,
                'status'        => 0,
                'due_date'      => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }
        if (count($insert)) {
            EmployeeCompetency::insert($insert);
        }

        return response()->json(['message' => 'Competency updated successfully!']);
    }

    /** Hapus Competency */
    public function destroy(Competency $competency)
    {
        // otomatis FK di employee_competency akan terhapus jika onDelete cascade
        $competency->delete();

        return response()->json(['message' => 'Competency deleted successfully!']);
    }
}
