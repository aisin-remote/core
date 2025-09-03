<?php

namespace App\Http\Controllers;

use App\Helpers\RtcHelper;
use App\Models\Rtc;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Plant;
use App\Models\SubSection;
use Illuminate\Http\Request;

class RtcController extends Controller
{
    public function index($company = null)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        // fallback company dari employee (tetap dipakai untuk cabang non-company)
        $company ??= $employee->company_name;

        // Normalisasi posisi
        $posRaw = trim((string)($employee->position ?? ''));
        $pos    = strtolower($posRaw);

        // Jika tersedia helper normalize
        $normalized = method_exists($employee, 'getNormalizedPosition')
            ? strtolower((string)$employee->getNormalizedPosition())
            : $pos;

        $isHRD       = ($user->role === 'HRD');
        $isPresident = in_array($pos, ['president', 'presdir', 'president director', 'president director'], true)
            || in_array($normalized, ['president', 'presdir'], true);
        $isVPD       = in_array($pos, ['vpd', 'vice president director', 'wakil presdir'], true)
            || ($normalized === 'vpd');

        $isGM        = ($pos === 'gm');
        $isDirektur  = ($pos === 'direktur');

        // =====================================================================
        // 0) PRESIDENT / VPD / HRD  -> Tampilkan daftar COMPANY (AII & AIIA)
        // =====================================================================
        if ($isHRD || $isPresident || $isVPD) {
            $title = 'RTC';
            $table = 'Company';

            // Karena tidak ada master company, define static di sini
            $companies = collect([
                (object)['id' => 'AII',  'name' => 'AII'],
                (object)['id' => 'AIIA', 'name' => 'AIIA'],
            ]);

            // Tidak ada plan & status di level company
            $showPlanColumns   = false;
            $showStatusColumn  = false;

            return view('website.rtc.index', [
                'title'            => $title,
                'table'            => $table,
                'divisions'        => $companies,   // blade pakai $divisions untuk list
                'items'            => $companies,   // kompat
                'employees'        => collect(),
                'rtcs'             => collect(),
                'showPlanColumns'  => $showPlanColumns,
                'showStatusColumn' => $showStatusColumn,
            ]);
        }

        // =====================================================================
        // 1) DIREKTUR → Tampilkan PLANT yang dia pegang
        // =====================================================================
        if ($isDirektur) {
            $table  = 'Plant';
            $title  = 'RTC';

            $items = Plant::query()
                ->where('company', $company)
                ->where('director_id', $employee->id)
                ->orderBy('name')
                ->get();

            $showPlanColumns   = false;
            $showStatusColumn  = false;

            return view('website.rtc.index', [
                'title'            => $title,
                'table'            => $table,
                'divisions'        => $items,
                'items'            => $items,
                'employees'        => collect(),
                'rtcs'             => collect(),
                'showPlanColumns'  => $showPlanColumns,
                'showStatusColumn' => $showStatusColumn,
            ]);
        }

        // =====================================================================
        // 2) HRD (fallback lama) → Division + plan + status  (tetap dipertahankan)
        //    (catatan: HRD sudah ditangani di branch company di atas. Jika ingin
        //    HRD melihat langsung Division, comment out blok "company" di atas.)
        // =====================================================================
        if ($user->role === 'HRD') {
            $table = 'Division';
            $title = 'RTC';

            $raw = Division::query()
                ->where('company', $company)
                ->orderBy('name')
                ->get();

            $items = $this->decoratePlansAndOverall($raw, 'division');

            $employees = Employee::whereIn('position', ['Manager', 'Coordinator'])
                ->where('company_name', $company)
                ->get();

            $showPlanColumns   = true;
            $showStatusColumn  = true;

            return view('website.rtc.index', [
                'title'            => $title,
                'table'            => $table,
                'divisions'        => $items,
                'items'            => $items,
                'employees'        => $employees,
                'rtcs'             => Rtc::all(),
                'showPlanColumns'  => $showPlanColumns,
                'showStatusColumn' => $showStatusColumn,
            ]);
        }

        // =====================================================================
        // 3) GM → Division miliknya (tanpa plan & status)
        // =====================================================================
        if ($isGM) {
            $table = 'Division';
            $title = 'RTC';

            $items = Division::query()
                ->where('gm_id', $employee->id)
                ->orderBy('name')
                ->get();

            $employees = Employee::whereIn('position', ['Manager', 'Coordinator'])
                ->where('company_name', $employee->company_name)
                ->get();

            $showPlanColumns   = false;
            $showStatusColumn  = false;

            return view('website.rtc.index', [
                'title'            => $title,
                'table'            => $table,
                'divisions'        => $items,
                'items'            => $items,
                'employees'        => $employees,
                'rtcs'             => Rtc::all(),
                'showPlanColumns'  => $showPlanColumns,
                'showStatusColumn' => $showStatusColumn,
            ]);
        }

        // =====================================================================
        // 4) Role lain → Department + plan + status (berdasarkan division user)
        // =====================================================================
        $table      = 'Department';
        $title      = 'RTC';
        $divisionId = $employee->division_id ?? null;

        $raw = Department::query()
            ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
            ->orderBy('name')
            ->get();

        $items = $this->decoratePlansAndOverall($raw, 'department');

        $employees = Employee::whereIn('position', ['Supervisor', 'Section Head'])
            ->where('company_name', $employee->company_name)
            ->get();

        $showPlanColumns   = true;
        $showStatusColumn  = true;

        return view('website.rtc.index', [
            'title'            => $title,
            'table'            => $table,
            'divisions'        => $items,
            'items'            => $items,
            'employees'        => $employees,
            'rtcs'             => Rtc::all(),
            'showPlanColumns'  => $showPlanColumns,
            'showStatusColumn' => $showStatusColumn,
        ]);
    }


    /**
     * Hias koleksi item (Division/Department) dengan:
     * - st_name / mt_name / lt_name (nama kandidat)
     * - overall_label / overall_code (Not Set / Complete / Submitted / Checked / Approved / Partial)
     * - can_add (false bila ST/MT/LT sudah lengkap)
     *
     * @param \Illuminate\Support\Collection $items
     * @param string $areaKey 'division' | 'department' | 'section' | 'sub_section'
     * @return \Illuminate\Support\Collection
     */
    private function decoratePlansAndOverall($items, string $areaKey)
    {
        // area variant agar tahan kasus 'Division' (huruf besar) / 'Sub_section'
        $areas = [$areaKey, ucfirst($areaKey)];
        if ($areaKey === 'division')    $areas[] = 'Division';
        if ($areaKey === 'sub_section') $areas[] = 'Sub_section';

        // alias term
        $termAliases = [
            'short' => ['short', 'short_term', 'st', 's/t'],
            'mid'   => ['mid',   'mid_term',   'mt', 'm/t'],
            'long'  => ['long',  'long_term',  'lt', 'l/t'],
        ];

        $findLatest = function ($areaId, $term) use ($areas, $termAliases) {
            return Rtc::whereIn('area', $areas)
                ->where('area_id', $areaId)
                ->whereIn('term', $termAliases[$term])
                ->with('employee:id,name')
                ->orderByDesc('id')
                ->first();
        };

        return $items->map(function ($item) use ($findLatest) {
            $sid = $item->id;

            $rS = $findLatest($sid, 'short');
            $rM = $findLatest($sid, 'mid');
            $rL = $findLatest($sid, 'long');

            $st = optional($rS?->employee)->name;
            $mt = optional($rM?->employee)->name;
            $lt = optional($rL?->employee)->name;

            $hasS = (bool) $st;
            $hasM = (bool) $mt;
            $hasL = (bool) $lt;
            $complete3 = $hasS && $hasM && $hasL;

            $statuses = collect([$rS?->status, $rM?->status, $rL?->status])
                ->filter(fn($v) => in_array($v, [0, 1, 3], true)); // 0=submitted, 1=checked, 3=approved

            $label = 'Not Set';
            $code  = 'not_set';

            if ($complete3) {
                if ($statuses->isEmpty()) {
                    $label = 'Complete';
                    $code  = 'complete_no_submit';
                } else {
                    $allApproved  = $statuses->every(fn($v) => $v === 3);
                    $allChecked   = $statuses->every(fn($v) => $v === 1);
                    $allSubmitted = $statuses->every(fn($v) => $v === 0);

                    if ($allApproved) {
                        $label = 'Approved';
                        $code = 'approved';
                    } elseif ($allChecked) {
                        $label = 'Checked';
                        $code = 'checked';
                    } elseif ($allSubmitted) {
                        $label = 'Submitted';
                        $code = 'submitted';
                    } else {
                        $label = 'Partial';
                        $code = 'partial';
                    }
                }
            }

            // set ke item agar langsung dipakai di blade
            $item->st_name        = $st;
            $item->mt_name        = $mt;
            $item->lt_name        = $lt;
            $item->overall_label  = $label;
            $item->overall_code   = $code;
            $item->can_add        = !$complete3;

            return $item;
        });
    }

    public function list(Request $request, $id = null)
    {
        $rawId = $id ?? $request->query('id'); // bisa string (AII/AIII) atau numeric (plant/division)
        $level = $request->query('level');

        $user     = auth()->user();
        $employee = $user->employee;

        $pos = strtolower(trim((string)($employee->position ?? '')));
        $normalized = method_exists($employee, 'getNormalizedPosition')
            ? strtolower((string)$employee->getNormalizedPosition())
            : $pos;

        $isHRD       = ($user->role === 'HRD');
        $isPresOrVpd = in_array($pos, ['president', 'vpd', 'vice president director', 'wakil presdir'], true)
            || in_array($normalized, ['president', 'vpd'], true);
        $isDirektur = ($user->role === 'User') && (
            in_array($pos, ['direktur', 'director'], true) || $normalized === 'direktur'
        );
        $isGM        = in_array($pos, ['gm', 'act gm'], true) || in_array($normalized, ['gm', 'act gm'], true);

        // flag read-only: Pres/VPD/HRD hanya bisa lihat (tanpa Add RTC)
        $readOnly = ($isPresOrVpd || $isHRD);

        // ====== MODE: Company -> tampilkan PLANTS by company ======
        if ($level === 'company') {
            $companyCode = strtoupper((string)$rawId);

            $plants = Plant::where('company', $companyCode)
                ->orderBy('name')
                ->get();

            // pakai blade index agar sederhana (tanpa plan/status)
            return view('website.rtc.index', [
                'title'            => 'RTC',
                'table'            => 'Plant',
                'divisions'        => $plants,
                'items'            => $plants,
                'employees'        => collect(),
                'rtcs'             => collect(),
                'showPlanColumns'  => false,
                'showStatusColumn' => false,
            ]);
        }

        // ====== MODE: Plant -> tampilkan DIVISION di Plant tsb ======
        if ($level === 'plant') {
            $plantId = (int) $rawId;
            $plant   = Plant::findOrFail($plantId);

            if ($isDirektur) {
                if ((int)$plant->director_id !== (int)$employee->id) {
                    abort(403, 'Unauthorized plant');
                }
            } elseif (!($isPresOrVpd || $isHRD)) {
                // selain Pres/VPD/HRD/Direktur → tolak
                abort(403, 'Unauthorized');
            }

            $divisions = Division::where('plant_id', $plant->id)
                ->orderBy('name')
                ->get();

            $decorated = $this->decoratePlansAndOverall($divisions, 'division');

            $itemsForJs = $decorated->map(function ($d) use ($readOnly) {
                return [
                    'id'      => $d->id,
                    'name'    => $d->name,
                    'short'   => ['name' => $d->st_name],
                    'mid'     => ['name' => $d->mt_name],
                    'long'    => ['name' => $d->lt_name],
                    'overall' => ['label' => $d->overall_label, 'code' => $d->overall_code],
                    // force non-add jika readOnly
                    'can_add' => !$readOnly && $d->can_add,
                ];
            })->values();

            return view('website.rtc.list', [
                'title'         => 'RTC',
                'cardTitle'     => 'Division List',
                'divisionId'    => null, // di halaman ini belum memilih division tertentu
                'employees'     => Employee::where('company_name', $employee->company_name)
                    ->get(['id', 'name', 'position', 'company_name']),
                'user'          => $user,
                'defaultFilter' => 'division',
                'items'         => $itemsForJs, // preload division list
                'readOnly'      => $readOnly,   // <— penting utk blade
            ]);
        }

        // ====== MODE default lama (dept/section/sub_section tabs) ======
        $divisionId = (int) $rawId;
        $title      = 'RTC';

        if ($isHRD || $isGM) {
            $defaultFilter = 'department';
        } elseif ($user->role === 'User' && $isDirektur) {
            $defaultFilter = 'division';
        } else {
            $defaultFilter = 'section';
        }

        return view('website.rtc.list', [
            'title'         => $title,
            'divisionId'    => $divisionId,
            'employees'     => Employee::where('company_name', $employee->company_name)
                ->get(['id', 'name', 'position', 'company_name']),
            'user'          => $user,
            'defaultFilter' => $defaultFilter,
            'cardTitle'     => 'List',
            'items'         => [],
            'readOnly'      => $readOnly, // Pres/VPD/HRD read-only juga di halaman bertab
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

    public function summary(Request $request, $id = null)
    {
        // Ambil id dari: route param -> route()->parameter -> query
        $id = (int) ($id ?? $request->route('id') ?? $request->query('id'));

        // Normalisasi filter (case-insensitive)
        $filter = strtolower($request->query('filter', 'department'));

        $user     = auth()->user();
        $employee = $user->employee ?? null;
        $isGM = $employee && (
            strcasecmp($employee->position, 'GM') === 0 ||
            strcasecmp($employee->position, 'Act GM') === 0
        );

        $main = [];
        $managers = [];
        $title = '-';
        $hideMainPlans = false;

        $palette = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7', 'color-8', 'color-9', 'color-10', 'color-11', 'color-12', 'color-13', 'color-14'];
        $pickColor = fn(string $key) => $palette[crc32($key) % count($palette)];

        switch ($filter) {
            case 'plant': {
                    $p = Plant::with('director')->findOrFail($id);
                    $title = $p->name ?? 'Plant';
                    $hideMainPlans = true;

                    $main = [
                        'title'      => $p->name ?? '-',
                        'person'     => RtcHelper::formatPerson($p->director ?? null),
                        'shortTerm'  => null,
                        'midTerm'    => null,
                        'longTerm'   => null,
                        'colorClass' => 'color-1',
                    ];

                    $divs = Division::with(['gm', 'short', 'mid', 'long'])
                        ->where('plant_id', $p->id)
                        ->orderBy('name')
                        ->get();

                    foreach ($divs as $d) {
                        RtcHelper::setAreaContext('division', $d->id);
                        $managers[] = [
                            'title'      => $d->name,
                            'person'     => RtcHelper::formatPerson($d->gm),
                            'shortTerm'  => RtcHelper::formatCandidate($d->short, 'short'),
                            'midTerm'    => RtcHelper::formatCandidate($d->mid,   'mid'),
                            'longTerm'   => RtcHelper::formatCandidate($d->long,  'long'),
                            'colorClass' => 'color-2',
                            'supervisors' => [],
                            'skipManagerNode' => false,
                        ];
                    }
                    break;
                }

            case 'division': {
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

            case 'department': {
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
