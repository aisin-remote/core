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
        $user     = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Employee profile is missing.');
        }

        $company ??= $employee->company_name;
        $pos   = trim($employee->position ?? '');
        $isGM  = strcasecmp($pos, 'GM') === 0;

        // Tentukan level list dan kandidat posisi untuk Add Plan
        if ($user->isHRDorDireksi()) {
            $table   = 'Division';
            $items   = Division::where('company', $company)->orderBy('name')->get();
            $candPos = ['Manager', 'Coordinator'];
        } elseif (strcasecmp($pos, 'Direktur') === 0) {
            $table   = 'Division';
            $items   = Division::where('company', $company)
                ->where('plant_id', optional($employee->plant)->id)
                ->orderBy('name')->get();
            $candPos = ['Manager', 'Coordinator'];
        } elseif ($isGM) {
            // GM: list Division milik dia
            $table   = 'Division';
            $items   = Division::where('gm_id', $employee->id)->orderBy('name')->get();
            $candPos = ['Manager', 'Coordinator'];
        } else {
            // Selain itu langsung ke Department di divisinya
            $table      = 'Department';
            $divisionId = $employee->division_id;
            $items      = Department::when($divisionId, fn($q) => $q->where('division_id', $divisionId))
                ->orderBy('name')->get();
            $candPos = ['Supervisor', 'Section Head'];
        }

        $employees = Employee::whereIn('position', $candPos)
            ->where('company_name', $company)
            ->orderBy('name')->get();

        // === Helper alias ===
        $termAliases = function (string $term): array {
            $t = strtolower(trim($term));
            return match ($t) {
                'short' => ['short', 'short_term', 'st', 's/t'],
                'mid'   => ['mid', 'mid_term', 'mt', 'm/t'],
                'long'  => ['long', 'long_term', 'lt', 'l/t'],
                default => [$t],
            };
        };
        $areaAliases = function (string $area): array {
            $a = strtolower(trim($area));
            $arr = [$a, ucfirst($a)];
            if ($a === 'division')    $arr[] = 'Division';
            if ($a === 'sub_section') $arr[] = 'Sub_section';
            return array_values(array_unique($arr));
        };

        $areaKey = strtolower($table);                  // 'division' | 'department'
        $areas   = $areaAliases($areaKey);
        $ids     = $items->pluck('id')->all();

        // Ambil semua RTC terkait (terbaru duluan)
        $rtcs = Rtc::whereIn('area', $areas)
            ->whereIn('area_id', $ids)
            ->with('employee:id,name,grade,birthday_date')
            ->orderByDesc('id')
            ->get();

        // Picker RTC terbaru per item+term (pakai aliases)
        $pickLatest = function ($list, int $areaId, string $term) use ($termAliases) {
            $aliases = array_map('strtolower', $termAliases($term));
            return $list->first(function ($r) use ($areaId, $aliases) {
                return (int)$r->area_id === $areaId && in_array(strtolower($r->term), $aliases, true);
            });
        };

        // Tempel kandidat dari RTC ke relasi 'short'/'mid'/'long' + hitung status overall
        $metaById = [];
        foreach ($items as $it) {
            $rtcS = $pickLatest($rtcs, $it->id, 'short');
            $rtcM = $pickLatest($rtcs, $it->id, 'mid');
            $rtcL = $pickLatest($rtcs, $it->id, 'long');

            // supaya blade lama `$division->short->name` tetap jalan
            $it->setRelation('short', optional($rtcS)->employee);
            $it->setRelation('mid',   optional($rtcM)->employee);
            $it->setRelation('long',  optional($rtcL)->employee);

            $hasS = !is_null(optional($rtcS)->employee);
            $hasM = !is_null(optional($rtcM)->employee);
            $hasL = !is_null(optional($rtcL)->employee);
            $complete3 = $hasS && $hasM && $hasL;

            $vals = collect([$rtcS?->status, $rtcM?->status, $rtcL?->status])
                ->filter(fn($v) => in_array($v, [0, 1, 2], true));

            // Mapping chip status (disamakan dengan list-rtc)
            $overall = ['text' => 'Not Set', 'code' => 'not_set', 'data_status' => 'not_created'];
            if ($complete3) {
                if ($vals->isEmpty()) {
                    $overall = ['text' => 'Complete', 'code' => 'complete_no_submit', 'data_status' => 'draft'];
                } else {
                    if ($vals->every(fn($v) => $v === 2)) {
                        $overall = ['text' => 'Approved', 'code' => 'approved', 'data_status' => 'approved'];
                    } elseif ($vals->every(fn($v) => $v === 1)) {
                        $overall = ['text' => 'Checked', 'code' => 'checked', 'data_status' => 'checked'];
                    } elseif ($vals->every(fn($v) => $v === 0)) {
                        $overall = ['text' => 'Submitted', 'code' => 'submitted', 'data_status' => 'waiting'];
                    } else {
                        $overall = ['text' => 'Partial', 'code' => 'partial', 'data_status' => 'draft'];
                    }
                }
            }

            $metaById[$it->id] = [
                'overall_badge' => $overall,
                'can_add'       => !$complete3,     // hide tombol Add bila sudah lengkap 3 term
                'short_status'  => $rtcS?->status,  // optional kalau butuh
                'mid_status'    => $rtcM?->status,
                'long_status'   => $rtcL?->status,
            ];
        }

        $title = 'RTC';

        // GM di halaman Division → kolom plan disembunyikan
        $showPlanColumns = !($isGM && $table === 'Division');

        // GM tidak melihat kolom Status di index
        $showStatusColumn = !$isGM;

        // Agar blade tetap kompatibel (loop pakai $divisions)
        $divisions = $items;

        return view('website.rtc.index', compact(
            'divisions',      // bisa Division/Department collection
            'employees',      // untuk modal Add (kalau diizinkan)
            'table',          // 'Division' | 'Department'
            'rtcs',           // RTC terfilter area (sudah with employee)
            'title',
            'showPlanColumns',
            'metaById',        // dipakai utk kolom Status & hide tombol Add
            'showStatusColumn'
        ));
    }

    public function showDivision($id)
    {
        $user = auth()->user();
        $employee = $user->employee;
        $isGM = strcasecmp(trim($employee->position ?? ''), 'GM') === 0;

        $division = Division::findOrFail($id);
        if ($isGM && $division->gm_id !== $employee->id) abort(403);

        // DETAIL = list Department → GM BOLEH lihat & edit plan
        $table = 'Department';
        $divisions = Department::where('division_id', $division->id)->get();
        $employees = Employee::whereIn('position', ['Supervisor', 'Section Head'])
            ->where('company_name', $employee->company_name)->get();

        $rtcs = Rtc::all();
        $title = 'RTC - ' . $division->name;

        $showPlanColumns = true; // penting!

        return view('website.rtc.index', compact(
            'divisions',
            'employees',
            'table',
            'rtcs',
            'title',
            'showPlanColumns'
        ));
    }

    public function list($id)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        $division    = Division::findOrFail($id);
        $divisionId  = $division->id;
        $employees   = Employee::where('company_name', $employee->company_name)->get();

        // --- default tab ---
        if ($user->role === 'HRD' || ($employee && $employee->position === 'Direktur')) {
            $defaultFilter = 'department';
        } elseif ($employee && $employee->position === 'GM') {
            $defaultFilter = 'department';              // <— GM wajib Department dulu
        } else {
            $defaultFilter = 'section';
        }

        $cardTitle = 'Department List'; // biar judul awal sesuai tab default
        return view('website.rtc.list', compact(
            'divisionId',
            'employees',
            'user',
            'defaultFilter',
            'cardTitle'
        ));
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

    // app/Http/Controllers/RtcController.php

    public function summary(Request $request)
    {
        $filter = strtolower($request->query('filter', 'department'));
        $id     = (int) $request->query('id');

        $user     = auth()->user();
        $employee = $user->employee ?? null;
        $isGM     = strcasecmp(trim($employee->position ?? ''), 'GM') === 0;

        $main = [];
        $managers = [];
        $title = '-';

        // flag untuk menyembunyikan kandidat (S/T, M/T, L/T) di node MAIN
        $hideMainPlans = false;

        // Palet warna
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
        $pickColor = fn(string $key) => $palette[crc32($key) % count($palette)];

        switch ($filter) {
            /* =========================== DIVISION =========================== */
            case 'division': {
                    // GM melihat summary division → sembunyikan S/T, M/T, L/T pada node utama
                    $hideMainPlans = $isGM;

                    $div   = Division::with(['gm', 'short', 'mid', 'long'])->findOrFail($id);
                    $title = $div->name ?? 'Division';

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

                    $deptPalette = array_values(array_filter($palette, fn($c) => $c !== $mainColor));
                    $deptIdx = 0;

                    foreach ($depts as $d) {
                        $deptColor = $deptPalette[$deptIdx++ % count($deptPalette)];

                        RtcHelper::setAreaContext('department', $d->id);
                        $node = [
                            'title'           => $d->name,
                            'person'          => RtcHelper::formatPerson($d->manager),
                            'shortTerm'       => RtcHelper::formatCandidate($d->short, 'short'),
                            'midTerm'         => RtcHelper::formatCandidate($d->mid,   'mid'),
                            'longTerm'        => RtcHelper::formatCandidate($d->long,  'long'),
                            'colorClass'      => $deptColor,
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
                                'colorClass' => $deptColor,
                            ];
                        }

                        $managers[] = $node;
                    }
                    break;
                }

                /* ========================== DEPARTMENT ========================== */
            case 'department': {
                    // GM di summary department → tampilkan S/T, M/T, L/T pada node utama
                    $hideMainPlans = false;

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

                    $container = [
                        'title'           => $d->name,
                        'person'          => RtcHelper::formatPerson($d->manager),
                        'shortTerm'       => RtcHelper::formatCandidate($d->short, 'short'),
                        'midTerm'         => RtcHelper::formatCandidate($d->mid,   'mid'),
                        'longTerm'        => RtcHelper::formatCandidate($d->long,  'long'),
                        'colorClass'      => $mainColor,
                        'supervisors'     => [],
                        'skipManagerNode' => true,
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
                            'colorClass' => $mainColor,
                        ];
                    }

                    $managers[] = $container;
                    break;
                }

                /* ============================ SECTION =========================== */
            case 'section': {
                    $hideMainPlans = false;

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
                            'colorClass' => $mainColor,
                        ];
                    }

                    $managers[] = $container;
                    break;
                }

                /* ========================== SUB SECTION ========================= */
            case 'sub_section': {
                    $hideMainPlans = false;

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

        return view('website.rtc.detail', compact('main', 'managers', 'title', 'hideMainPlans'));
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
                'division' => Division::class,
                'department' => Department::class,
                'section' => Section::class,
                'sub_section' => SubSection::class,
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
