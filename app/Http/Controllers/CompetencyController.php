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
use Illuminate\Validation\Rule;

class CompetencyController extends Controller
{
    /** Tampilkan daftar Competency */
    public function index(Request $request)
    {
        // Ambil query search dan position
        $search = $request->query('search');
        $position = $request->query('position', 'Show All');
        $group = $request->query('group', 'Show All');

        // Mulai builder dengan eager-load relasi
        $query = Competency::with([
            'group_competency',
            'sub_section.section.department.division.plant',
            'section.department.division.plant',
            'department.division.plant',
            'division.plant',
            'plant'
        ]);

        // Jika ada kata kunci, tambahkan where/orWhereHas
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%")
                ->orWhereHas('group_competency', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter berdasarkan posisi jika dipilih dan bukan 'Show All'
        if ($position && $position !== 'Show All') {
            $query->where('position', $position);
        }

        if ($group && $group !== 'Show All') {
            $query->where('group_competency_id', $group);
        }

        // Paginate dan sertakan query string
        $competencies = $query
            ->orderBy('name')
            ->paginate(10)
            ->appends([
                'search' => $search,
                'position' => $position,
                'group' => $group
            ]);

        // Data pendukung untuk form/filter
        $groups = GroupCompetency::all();
        $subSections = SubSection::all();
        $sections = Section::all();
        $departments = Department::all();
        $divisions = Division::all();
        $plants = Plant::all();

        // Daftar posisi untuk tab
        $jobPositions = [
            'Show All',
            'Operator',
            'Leader',
            'Act Leader',
            'JP',
            'Act JP',
            'Supervisor',
            'Section Head',
            'Coordinator',
            'Manager',
            'GM',
            'Direktur',
        ];

        return view('website.competency.index', compact(
            'competencies', 
            'search', 
            'groups', 
            'subSections',
            'sections', 
            'departments', 
            'divisions', 
            'plants',
            'jobPositions',
            'position',
            'group'
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

    public function store(Request $r)
    {
        // 1. Validasi sama seperti semula
        $data = $r->validate([
            'name'                => 'required|string|max:191',
            'group_competency_id' => 'required|exists:group_competency,id',
            'position'            => 'required|string',
            'weight'              => 'required|integer|min:0|max:4',
            'plan'                => 'required|integer|min:0|max:4',
            'sub_section_id' => [
                Rule::requiredIf(fn() => in_array($r->position, ['Operator','JP','Leader','Act Leader'])),
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

        // 2. Simpan competency master
        $competency = Competency::create($data);

        // 3. Cari karyawan yang match position + hirarki (tanpa pakai kolom section_id di employees):
        $query = Employee::where('position', $competency->position);

        switch ($competency->position) {
            // Kasus: Operator, JP, Leader, Act Leader → filter lewat subSection.id
            case 'Operator':
            case 'JP':
            case 'Leader':
            case 'Act Leader':
                // Artinya kita akan “menembus” subSection langsung:
                $query->whereHas('subSection', function ($q) use ($competency) {
                    $q->where('id', $competency->sub_section_id);
                });
                break;

            // Kasus: Supervisor, Section Head → filter karyawan yang berada di Section tertentu
            // Karena employees tidak punya kolom section_id, kita “naik” satu level lewat subSection.section:
            case 'Supervisor':
            case 'Section Head':
                $query->whereHas('subSection.section', function ($q) use ($competency) {
                    $q->where('id', $competency->section_id);
                });
                break;

            // Kasus: Manager, Coordinator → filter lewat Department
            case 'Manager':
            case 'Coordinator':
                $query->whereHas('subSection.section.department', function ($q) use ($competency) {
                    $q->where('id', $competency->department_id);
                });
                break;

            // Kasus: GM → filter lewat Division
            case 'GM':
                $query->whereHas('subSection.section.department.division', function ($q) use ($competency) {
                    $q->where('id', $competency->division_id);
                });
                break;

            // Kasus: Director → filter lewat Plant
            case 'Director':
                $query->whereHas('subSection.section.department.division.plant', function ($q) use ($competency) {
                    $q->where('id', $competency->plant_id);
                });
                break;

            // Jika posisi lain, misalnya tidak di‐map ke hirarki, paksa return kosong:
            default:
                $query->whereRaw('0 = 1');
        }

        $employees = $query->get();

        // 4. Bulk insert ke employee_competency (sama seperti semula)
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

        if ($r->expectsJson()) {
            return response()->json(['message' => ' Competency added successfully!'], 200);
        }
        // kalau bukan AJAX, redirect balik ke index (misalnya):
        return redirect()->route('competencies.index')
                         ->with('success', 'Competency added successfully!');
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
            case 'Act Leader':
                $query->whereHas('subSection', fn($q) =>
                    $q->where('id', $competency->sub_section_id)
                );
                break;
        
            case 'Supervisor':
            case 'Section Head':
                $query->whereHas('subSection.section', fn($q) =>
                    $q->where('id', $competency->section_id)
                );
                break;
        
            case 'Manager':
            case 'Coordinator':
                $query->whereHas('subSection.section.department', fn($q) =>
                    $q->where('id', $competency->department_id)
                );
                break;
        
            case 'GM':
                $query->whereHas('subSection.section.department.division', fn($q) =>
                    $q->where('id', $competency->division_id)
                );
                break;
        
            case 'Director':
                $query->whereHas('subSection.section.department.division.plant', fn($q) =>
                    $q->where('id', $competency->plant_id)
                );
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
