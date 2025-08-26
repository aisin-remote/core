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

        if ($company == null) {
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
        $divisionId = $request->query('id');

        // Ambil semua employee dengan relasi yang diperlukan
        $employees = Employee::with('leadingDepartment', 'leadingSection', 'leadingSubSection')
            ->select('id', 'name', 'position')
            ->get();

        // Ambil data RTC dengan relasi yang sesuai
        $rtcs = Rtc::with([
            'employee',
            'department',
            'section',
            'subsection'
        ])->get();

        // Tambahkan data area_object berdasarkan tipe area
        $rtcs->each(function ($rtc) {
            $rtc->area_object = null;
            if ($rtc->area === 'department' && $rtc->department) {
                $rtc->area_object = $rtc->department;
            } elseif ($rtc->area === 'section' && $rtc->section) {
                $rtc->area_object = $rtc->section;
            } elseif ($rtc->area === 'sub_section' && $rtc->subsection) {
                $rtc->area_object = $rtc->subsection;
            }
        });

        // Jika request AJAX untuk filter
        if ($request->ajax()) {
            $filter = $request->filter;
            $filteredRtc = $rtcs->filter(function ($rtc) use ($filter) {
                return $rtc->area === $filter;
            });

            return view('website.rtc.partials.table', [
                'rtcs' => $filteredRtc,
                'employees' => $employees
            ]);
        }

        // Tentukan filter default berdasarkan role user
        $defaultFilter = (auth()->user()->role == 'HRD' || auth()->user()->employee->position == 'Direktur')
            ? 'department'
            : 'section';

        return view('website.rtc.list', [
            'employees' => $employees,
            'divisionId' => $divisionId,
            'user' => $user,
            'rtcs' => $rtcs->filter(fn($rtc) => $rtc->area === $defaultFilter),
            'defaultFilter' => $defaultFilter
        ]);
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
        $filter = strtolower($request->query('filter', 'department'));
        $id     = (int) $request->query('id');

        $main = [];
        $managers = [];
        $title = '-';

        // Palet warna panjang; tambahkan lagi jika perlu
        $palette = [
            'color-1',
            'color-2',
            'color-3',
            'color-4',
            'color-5',
            'color-6',
            'color-7',
            'color-8',
            'color-9',
            'color-10',
            'color-11',
            'color-12',
            'color-13',
            'color-14'
        ];

        // Helper pilih warna deterministik (fallback jika suatu hari perlu)
        $pickColor = fn(string $key) => $palette[crc32($key) % count($palette)];

        switch ($filter) {
            // ============================= DIVISION =============================
            case 'division': {
                    $div   = Division::with(['gm', 'short', 'mid', 'long'])->findOrFail($id);
                    $title = $div->name ?? 'Division';

                    // Warna root khusus
                    $mainColor = $pickColor("division-root-{$div->id}");

                    RtcHelper::setAreaContext('Division', $div->id);
                    $main = [
                        'title'      => $div->name ?? '-',
                        'person'     => RtcHelper::formatPerson($div->gm),
                        'shortTerm'  => RtcHelper::formatCandidate($div->short, 'short'),
                        'midTerm'    => RtcHelper::formatCandidate($div->mid,   'mid'),
                        'longTerm'   => RtcHelper::formatCandidate($div->long,  'long'),
                        'colorClass' => $mainColor,
                    ];

                    $depts = Department::with(['manager', 'short', 'mid', 'long'])
                        ->where('division_id', $div->id)
                        ->orderBy('name')
                        ->get();

                    // Daftar warna untuk dept = semua palet KECUALI warna root
                    $deptPalette  = array_values(array_filter($palette, fn($c) => $c !== $mainColor));
                    $deptIdx = 0;

                    foreach ($depts as $d) {
                        // Setiap department dapat warna berbeda (berurutan)
                        $deptColor = $deptPalette[$deptIdx % count($deptPalette)];
                        $deptIdx++;

                        RtcHelper::setAreaContext('department', $d->id);

                        $node = [
                            'title'           => $d->name,
                            'person'          => RtcHelper::formatPerson($d->manager),
                            'shortTerm'       => RtcHelper::formatCandidate($d->short, 'short'),
                            'midTerm'         => RtcHelper::formatCandidate($d->mid,   'mid'),
                            'longTerm'        => RtcHelper::formatCandidate($d->long,  'long'),
                            'colorClass'      => $deptColor,   // unik per dept
                            'supervisors'     => [],
                            'skipManagerNode' => false,
                        ];

                        $secs = Section::with(['supervisor', 'short', 'mid', 'long'])
                            ->where('department_id', $d->id)
                            ->orderBy('name')
                            ->get();

                        foreach ($secs as $s) {
                            RtcHelper::setAreaContext('section', $s->id);
                            $node['supervisors'][] = [
                                'title'      => $s->name,
                                'person'     => RtcHelper::formatPerson($s->supervisor),
                                'shortTerm'  => RtcHelper::formatCandidate($s->short, 'short'),
                                'midTerm'    => RtcHelper::formatCandidate($s->mid,   'mid'),
                                'longTerm'   => RtcHelper::formatCandidate($s->long,  'long'),
                                'colorClass' => $deptColor,       // mewarisi warna dept
                            ];
                        }

                        $managers[] = $node;
                    }
                    break;
                }

                // ============================ DEPARTMENT ============================
            case 'department': {
                    $d = Department::with(['manager', 'short', 'mid', 'long'])->findOrFail($id);
                    $title = $d->name ?? 'Department';

                    $mainColor = $pickColor("department-root-{$d->id}");

                    RtcHelper::setAreaContext('department', $d->id);
                    $main = [
                        'title'      => $d->name ?? '-',
                        'person'     => RtcHelper::formatPerson($d->manager),
                        'shortTerm'  => RtcHelper::formatCandidate($d->short, 'short'),
                        'midTerm'    => RtcHelper::formatCandidate($d->mid,   'mid'),
                        'longTerm'   => RtcHelper::formatCandidate($d->long,  'long'),
                        'colorClass' => $mainColor,
                    ];

                    // Root = manager; anak2 section mewarisi warna root
                    $container = [
                        'title'           => $d->name,
                        'person'          => RtcHelper::formatPerson($d->manager),
                        'shortTerm'       => RtcHelper::formatCandidate($d->short, 'short'),
                        'midTerm'         => RtcHelper::formatCandidate($d->mid,   'mid'),
                        'longTerm'        => RtcHelper::formatCandidate($d->long,  'long'),
                        'colorClass'      => $mainColor,
                        'supervisors'     => [],
                        'skipManagerNode' => true, // tidak render node manager duplikat
                    ];

                    $secs = Section::with(['supervisor', 'short', 'mid', 'long'])
                        ->where('department_id', $d->id)
                        ->orderBy('name')
                        ->get();

                    foreach ($secs as $s) {
                        RtcHelper::setAreaContext('section', $s->id);
                        $container['supervisors'][] = [
                            'title'      => $s->name,
                            'person'     => RtcHelper::formatPerson($s->supervisor),
                            'shortTerm'  => RtcHelper::formatCandidate($s->short, 'short'),
                            'midTerm'    => RtcHelper::formatCandidate($s->mid,   'mid'),
                            'longTerm'   => RtcHelper::formatCandidate($s->long,  'long'),
                            'colorClass' => $mainColor, // ikut root
                        ];
                    }

                    $managers[] = $container;
                    break;
                }

                // ============================== SECTION =============================
            case 'section': {
                    $s = Section::with(['supervisor', 'short', 'mid', 'long'])->findOrFail($id);
                    $title = $s->name ?? 'Section';

                    $mainColor = $pickColor("section-root-{$s->id}");

                    RtcHelper::setAreaContext('section', $s->id);
                    $main = [
                        'title'      => $s->name ?? '-',
                        'person'     => RtcHelper::formatPerson($s->supervisor),
                        'shortTerm'  => RtcHelper::formatCandidate($s->short, 'short'),
                        'midTerm'    => RtcHelper::formatCandidate($s->mid,   'mid'),
                        'longTerm'   => RtcHelper::formatCandidate($s->long,  'long'),
                        'colorClass' => $mainColor,
                    ];

                    $container = [
                        'title'           => $s->name,
                        'person'          => RtcHelper::formatPerson($s->supervisor),
                        'shortTerm'       => RtcHelper::formatCandidate($s->short, 'short'),
                        'midTerm'         => RtcHelper::formatCandidate($s->mid,   'mid'),
                        'longTerm'        => RtcHelper::formatCandidate($s->long,  'long'),
                        'colorClass'      => $mainColor,
                        'supervisors'     => [],
                        'skipManagerNode' => true,
                    ];

                    $subs = SubSection::with(['leader', 'short', 'mid', 'long'])
                        ->where('section_id', $s->id)
                        ->orderBy('name')
                        ->get();

                    foreach ($subs as $sub) {
                        RtcHelper::setAreaContext('sub_section', $sub->id);
                        $container['supervisors'][] = [
                            'title'      => $sub->name,
                            'person'     => RtcHelper::formatPerson($sub->leader),
                            'shortTerm'  => RtcHelper::formatCandidate($sub->short, 'short'),
                            'midTerm'    => RtcHelper::formatCandidate($sub->mid,   'mid'),
                            'longTerm'   => RtcHelper::formatCandidate($sub->long,  'long'),
                            'colorClass' => $mainColor, // ikut root
                        ];
                    }

                    $managers[] = $container;
                    break;
                }

                // ============================ SUB SECTION ===========================
            case 'sub_section': {
                    $sub = SubSection::with(['leader', 'short', 'mid', 'long'])->findOrFail($id);
                    $title = $sub->name ?? 'Sub Section';

                    $mainColor = $pickColor("subsection-root-{$sub->id}");

                    RtcHelper::setAreaContext('sub_section', $sub->id);
                    $main = [
                        'title'      => $sub->name ?? '-',
                        'person'     => RtcHelper::formatPerson($sub->leader),
                        'shortTerm'  => RtcHelper::formatCandidate($sub->short, 'short'),
                        'midTerm'    => RtcHelper::formatCandidate($sub->mid,   'mid'),
                        'longTerm'   => RtcHelper::formatCandidate($sub->long,  'long'),
                        'colorClass' => $mainColor,
                    ];
                    $managers = [];
                    break;
                }

            default:
                abort(404, 'Unsupported filter');
        }

        return view('website.rtc.detail', compact('main', 'managers', 'title'));
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
