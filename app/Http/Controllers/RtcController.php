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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        $isGM = $employee && in_array($pos, ['gm', 'act gm'], true);
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

            return view('website.rtc.list', [
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

            $dtS = $rS?->updated_at ?? $rS?->created_at;
            $dtM = $rM?->updated_at ?? $rM?->created_at;
            $dtL = $rL?->updated_at ?? $rL?->created_at;
            $max = collect([$dtS, $dtM, $dtL])->filter()->max();
            $item->last_year = $max ? Carbon::parse($max)->format('Y') : null;

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
        $trace = (string) Str::uuid();         // correlation id utk semua log di request ini
        $t0    = microtime(true);

        try {
            Log::info('[RTC][list] start', [
                'trace' => $trace,
                'url'   => $request->fullUrl(),
                'q.level' => $request->query('level'),
                'ip'    => $request->ip(),
                'ua'    => substr((string) $request->userAgent(), 0, 200),
            ]);

            $level    = $request->query('level'); // company|direksi|division|department|section|sub_section
            $user     = auth()->user();
            $employee = $user->employee;

            $pos = strtolower(trim((string)($employee->position ?? '')));
            $normalized = method_exists($employee, 'getNormalizedPosition')
                ? strtolower((string)$employee->getNormalizedPosition())
                : $pos;

            $isHRD      = ($user->role === 'HRD');
            $isTop2     = in_array($pos, ['president', 'vpd', 'vice president director', 'wakil presdir'], true)
                || in_array($normalized, ['president', 'vpd'], true);
            $isDirektur = ($user->role === 'User') && (in_array($pos, ['direktur', 'director'], true) || $normalized === 'direktur');
            $isGM       = in_array($pos, ['gm', 'act gm'], true) || in_array($normalized, ['gm', 'act gm'], true);

            Log::debug('[RTC][list] role flags', [
                'trace' => $trace,
                'user_id' => $user->id ?? null,
                'employee_id' => $employee->id ?? null,
                'position_raw' => $employee->position ?? null,
                'position_norm' => $normalized,
                'isHRD' => $isHRD,
                'isTop2' => $isTop2,
                'isDirektur' => $isDirektur,
                'isGM' => $isGM,
            ]);

            $readOnly   = ($isTop2 || $isHRD);

            /* ===== HRD/Top2: companies & direksi map ===== */
            $companies = [];
            $plantsByCompany = collect();
            if ($isHRD || $isTop2) {
                $companies = collect([
                    ['code' => 'AII',  'name' => 'AII'],
                    ['code' => 'AIIA', 'name' => 'AIIA'],
                ]);
                $plantsByCompany = Plant::orderBy('name')
                    ->get(['id', 'name', 'company'])
                    ->groupBy('company')
                    ->map(fn($g) => $g->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values())
                    ->toArray();
            }

            Log::debug('[RTC][list] company scope data', [
                'trace' => $trace,
                'company_scope' => ($isHRD || $isTop2),
                'companies_count' => is_array($companies) ? count($companies) : ($companies instanceof \Illuminate\Support\Collection ? $companies->count() : 0),
                'plants_map_companies' => array_keys(is_array($plantsByCompany) ? $plantsByCompany : []),
            ]);

            /* ===== Scope direksi utk tab Division (non GM) ===== */
            $plantIdForDivision = null;
            if ($isDirektur) {
                $plantIdForDivision = optional($employee->plant)->id;
            } elseif ($isGM) {
                $plantIdForDivision = null; // GM tak perlu selector direksi
            }

            // dropdown direksi (hanya jika bukan HRD/Top2 & bukan GM)
            $plants = collect();
            if (!($isHRD || $isTop2 || $isGM)) {
                $plants = Plant::query()
                    ->when($plantIdForDivision, fn($q) => $q->where('id', $plantIdForDivision))
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }

            Log::debug('[RTC][list] direksi scope', [
                'trace' => $trace,
                'plantIdForDivision' => $plantIdForDivision,
                'plants_dropdown_count' => $plants instanceof \Illuminate\Support\Collection ? $plants->count() : 0,
            ]);

            /* ===== Division dropdown utk Dept/Section/Sub ===== */
            $divisionsForSelect = collect();
            if ($isGM) {
                $divisionsForSelect = Division::where('gm_id', $employee->id)->orderBy('name')->get(['id', 'name']);
            } elseif ($plantIdForDivision) {
                $divisionsForSelect = Division::where('plant_id', $plantIdForDivision)->orderBy('name')->get(['id', 'name']);
            }

            /* ===== Employees utk modal Add (select2) ===== */
            $employeesQuery = Employee::select('id', 'name', 'position', 'company_name')->orderBy('name');
            if (!($isHRD || $isTop2)) {
                $employeesQuery->where('company_name', $employee->company_name);
            }
            $employees = $employeesQuery->get();

            Log::debug('[RTC][list] dropdown counts', [
                'trace' => $trace,
                'divisions_select_count' => $divisionsForSelect instanceof \Illuminate\Support\Collection ? $divisionsForSelect->count() : 0,
                'employees_select_count' => $employees instanceof \Illuminate\Support\Collection ? $employees->count() : 0,
            ]);

            /* ===== Tabs ===== */
            $tabs = [
                'company'     => ['label' => 'Company',  'show' => ($isHRD || $isTop2),                 'id' => null],
                'direksi'     => ['label' => 'Direksi',  'show' => ($isDirektur || $isHRD || $isTop2),  'id' => null],
                'division'    => ['label' => 'Division', 'show' => true,                                'id' => $plantIdForDivision],
                'department'  => ['label' => 'Department', 'show' => true,                              'id' => null],
                'section'     => ['label' => 'Section',   'show' => true,                               'id' => null],
                'sub_section' => ['label' => 'Sub Section', 'show' => true,                             'id' => null],
            ];

            // Default active tab
            if ($level) {
                $activeTab = $level;
            } elseif ($isGM) {
                $activeTab = 'division';
            } elseif ($isDirektur) {
                $activeTab = 'direksi';
            } elseif ($isHRD || $isTop2) {
                $activeTab = 'company';
            } else {
                $activeTab = 'division';
            }

            $tableFilter = match ($activeTab) {
                'company'     => 'company',
                'direksi'     => 'direksi',
                'division'    => 'division',
                'department'  => 'department',
                'section'     => 'section',
                'sub_section' => 'sub_section',
                default       => 'division',
            };

            // Container ID awal (yang butuh saja)
            if ($tableFilter === 'division') {
                $containerId = $isGM ? null : ($plantIdForDivision ? (int)$plantIdForDivision : null);
            } elseif (in_array($tableFilter, ['department', 'section', 'sub_section'], true)) {
                $containerId = $isGM ? (int) optional($employee->division)->id ?: null : null;
            } else { // company / direksi
                $containerId = null;
            }

            $cardTitle = match ($tableFilter) {
                'company'     => 'Company List',
                'direksi'     => 'Direksi List',
                'division'    => 'Division List',
                'department'  => 'Department List',
                'section'     => 'Section List',
                'sub_section' => 'Sub Section List',
                default       => 'List',
            };

            // Visibilitas KPI & Add
            $hideKpiCols  = in_array($tableFilter, ['company', 'direksi'], true) || ($isGM && $tableFilter === 'division');
            $forceHideAdd = $hideKpiCols;

            Log::debug('[RTC][list] view computed', [
                'trace' => $trace,
                'activeTab' => $activeTab,
                'tableFilter' => $tableFilter,
                'containerId' => $containerId,
                'cardTitle'   => $cardTitle,
                'hideKpiCols' => $hideKpiCols,
                'forceHideAdd' => $forceHideAdd,
            ]);

            $resp = view('website.rtc.list', [
                'title'            => 'RTC',
                'cardTitle'        => $cardTitle,

                'divisionId'       => $containerId,
                'companies'        => $companies,
                'plants'           => $plants,
                'divisions'        => $divisionsForSelect,
                'plantsByCompany'  => $plantsByCompany,
                'employees'        => $employees,

                'user'             => $user,
                'items'            => [],
                'readOnly'         => $readOnly,

                'isCompanyScope'   => (bool)($isHRD || $isTop2),
                'isGM'             => (bool)$isGM,
                'isDirektur'       => (bool)$isDirektur,

                'tabs'             => $tabs,
                'activeTab'        => $activeTab,
                'tableFilter'      => $tableFilter,
                'plantScopeId'     => $plantIdForDivision,

                'hideKpiCols'      => $hideKpiCols,
                'forceHideAdd'     => $forceHideAdd,
            ]);

            Log::info('[RTC][list] end', [
                'trace' => $trace,
                'duration_ms' => round((microtime(true) - $t0) * 1000, 2),
                'mem_mb' => round(memory_get_usage(true) / 1048576, 2),
            ]);

            return $resp;
        } catch (\Throwable $e) {
            Log::error('[RTC][list] ERROR', [
                'trace' => $trace,
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                // batasi panjang stack agar log tidak meledak
                'stack' => substr($e->getTraceAsString(), 0, 5000),
            ]);

            // opsional: tampilkan pesan ramah ke user
            return back()->with('error', 'Terjadi masalah saat membuka halaman RTC. (trace: ' . $trace . ')');
        }
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
        $rawId  = $id ?? $request->route('id') ?? $request->query('id'); // company / numeric id
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
        $noRoot  = false;   // hilangkan node root (khusus company)
        $groupTop = false;  // aktifkan grouping President & VPD (khusus company)

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
            /* ======================== COMPANY: PRESIDENT & VPD GROUP + FULL SUBTREE ======================== */
            case 'company': {
                    $company   = strtoupper((string) $rawId);
                    $title     = $company . ' — Summary';

                    $noRoot        = true;   // tidak ada node “company”
                    $groupTop      = true;   // tampilkan group (President & VPD)
                    $hideMainPlans = true;   // root tidak dipakai

                    // helper format person (lengkapi age/LOS kalau kosong)
                    $fmtPerson = function ($emp) {
                        if (!$emp) return null;
                        $p = RtcHelper::formatPerson($emp) ?? [];
                        $age = $p['age'] ?? null;
                        if ($age === null && !empty($emp->birthday_date)) {
                            try {
                                $age = \Carbon\Carbon::parse($emp->birthday_date)->age;
                            } catch (\Throwable $e) {
                            }
                        }
                        $los = $p['los'] ?? null;
                        foreach (['join_date', 'start_date', 'hire_date', 'first_join_date'] as $col) {
                            if ($los === null && !empty($emp->{$col})) {
                                try {
                                    $los = \Carbon\Carbon::parse($emp->{$col})->diffInYears(now());
                                } catch (\Throwable $e) {
                                }
                            }
                        }
                        return [
                            'name'  => $p['name']  ?? $emp->name,
                            'grade' => $p['grade'] ?? ($emp->grade ?? '-'),
                            'age'   => $age ?? '-',
                            'los'   => $los ?? '-',
                            'lcp'   => $p['lcp']   ?? ($emp->last_career_promotion ?? '-'),
                            'photo' => $p['photo'] ?? ($emp->photo_url ?? ($emp->photo ? asset('storage/' . $emp->photo) : null)),
                        ];
                    };

                    // ambil Presiden & VPD (usahakan yang company-nya cocok dulu)
                    $lcCompany = strtolower($company);
                    $prioritizeCompany = fn($q) => $q->orderByRaw(
                        "CASE WHEN LOWER(company_name)=? THEN 0 ELSE 1 END, id DESC",
                        [$lcCompany]
                    );

                    $president = Employee::query()
                        ->whereIn(DB::raw('LOWER(position)'), ['president', 'president director', 'presdir'])
                        ->where(function ($q) use ($lcCompany) {
                            $q->whereRaw('LOWER(company_name)=?', [$lcCompany])
                                ->orWhereRaw('LOWER(company_name) LIKE ?', [$lcCompany . '%']);
                        })
                        ->tap($prioritizeCompany)
                        ->first()
                        ?? Employee::whereIn(DB::raw('LOWER(position)'), ['president', 'president director', 'presdir'])
                        ->latest('id')->first();

                    $vpd = Employee::query()
                        ->whereIn(DB::raw('LOWER(position)'), ['vpd', 'vice president director', 'wakil presdir'])
                        ->where(function ($q) use ($lcCompany) {
                            $q->whereRaw('LOWER(company_name)=?', [$lcCompany])
                                ->orWhereRaw('LOWER(company_name) LIKE ?', [$lcCompany . '%']);
                        })
                        ->tap($prioritizeCompany)
                        ->first()
                        ?? Employee::whereIn(DB::raw('LOWER(position)'), ['vpd', 'vice president director', 'wakil presdir'])
                        ->latest('id')->first();

                    // === FULL SUBTREE: PLANT → DIVISION → DEPARTMENT → SECTION → SUB SECTION (untuk company)
                    $plants = Plant::with('director')
                        ->where('company', $company)->orderBy('name')->get();

                    $plantTrees = [];
                    foreach ($plants as $p) {
                        // PLANT (tanpa ST/MT/LT)
                        $plantNode = [
                            'title'           => $p->name,
                            'person'          => $fmtPerson($p->director ?? null),
                            'shortTerm'       => null,
                            'midTerm'         => null,
                            'longTerm'        => null,
                            'colorClass'      => 'color-1',
                            'supervisors'     => [],
                            'skipManagerNode' => false,
                            'no_plans'        => true,
                        ];

                        // DIVISION
                        $divs = Division::with(['gm', 'short', 'mid', 'long'])
                            ->where('plant_id', $p->id)->orderBy('name')->get();

                        foreach ($divs as $d) {
                            RtcHelper::setAreaContext('division', $d->id);
                            $divNode = [
                                'title'           => $d->name,
                                'person'          => RtcHelper::formatPerson($d->gm),
                                'shortTerm'       => RtcHelper::formatCandidate($d->short, 'short'),
                                'midTerm'         => RtcHelper::formatCandidate($d->mid,   'mid'),
                                'longTerm'        => RtcHelper::formatCandidate($d->long,  'long'),
                                'colorClass'      => 'color-4',
                                'supervisors'     => [],
                                'skipManagerNode' => false,
                            ];

                            // DEPARTMENT
                            $depts = Department::with(['manager', 'short', 'mid', 'long'])
                                ->where('division_id', $d->id)->orderBy('name')->get();

                            foreach ($depts as $dept) {
                                RtcHelper::setAreaContext('department', $dept->id);
                                $deptNode = [
                                    'title'           => $dept->name,
                                    'person'          => RtcHelper::formatPerson($dept->manager),
                                    'shortTerm'       => RtcHelper::formatCandidate($dept->short, 'short'),
                                    'midTerm'         => RtcHelper::formatCandidate($dept->mid,   'mid'),
                                    'longTerm'        => RtcHelper::formatCandidate($dept->long,  'long'),
                                    'colorClass'      => 'color-10',
                                    'supervisors'     => [],
                                    'skipManagerNode' => false,
                                ];

                                // SECTION
                                $secs = Section::with(['supervisor', 'short', 'mid', 'long'])
                                    ->where('department_id', $dept->id)->orderBy('name')->get();

                                foreach ($secs as $s) {
                                    RtcHelper::setAreaContext('section', $s->id);
                                    $secNode = [
                                        'title'           => $s->name,
                                        'person'          => RtcHelper::formatPerson($s->supervisor),
                                        'shortTerm'       => RtcHelper::formatCandidate($s->short, 'short'),
                                        'midTerm'         => RtcHelper::formatCandidate($s->mid,   'mid'),
                                        'longTerm'        => RtcHelper::formatCandidate($s->long,  'long'),
                                        'colorClass'      => 'color-8',
                                        'supervisors'     => [],
                                        'skipManagerNode' => false,
                                    ];

                                    // SUB SECTION
                                    $subs = SubSection::with(['leader', 'short', 'mid', 'long'])
                                        ->where('section_id', $s->id)->orderBy('name')->get();

                                    foreach ($subs as $sub) {
                                        RtcHelper::setAreaContext('sub_section', $sub->id);
                                        $secNode['supervisors'][] = [
                                            'title'           => $sub->name,
                                            'person'          => RtcHelper::formatPerson($sub->leader),
                                            'shortTerm'       => RtcHelper::formatCandidate($sub->short, 'short'),
                                            'midTerm'         => RtcHelper::formatCandidate($sub->mid,   'mid'),
                                            'longTerm'        => RtcHelper::formatCandidate($sub->long,  'long'),
                                            'colorClass'      => 'color-12',
                                            'supervisors'     => [],
                                            'skipManagerNode' => false,
                                        ];
                                    }

                                    $deptNode['supervisors'][] = $secNode;
                                }

                                $divNode['supervisors'][] = $deptNode;
                            }

                            $plantNode['supervisors'][] = $divNode;
                        }

                        $plantTrees[] = $plantNode;
                    }

                    // dua kepala (tanpa kandidat)
                    $managers = [
                        [
                            'title'           => 'PRESIDENT',
                            'person'          => $fmtPerson($president),
                            'shortTerm'       => null,
                            'midTerm'         => null,
                            'longTerm'        => null,
                            'colorClass'      => 'color-2',
                            'supervisors'     => $plantTrees,   // subtree bersama dimulai dari PLANT
                            'skipManagerNode' => false,
                            'no_plans'        => true,          // hilangkan S/T M/T L/T
                        ],
                        [
                            'title'           => 'VPD',
                            'person'          => $fmtPerson($vpd),
                            'shortTerm'       => null,
                            'midTerm'         => null,
                            'longTerm'        => null,
                            'colorClass'      => 'color-3',
                            'supervisors'     => [],            // anaknya di-link/di-share oleh group di blade
                            'skipManagerNode' => false,
                            'no_plans'        => true,
                        ],
                    ];

                    $main = [
                        'title'      => '',
                        'person'     => null,
                        'shortTerm'  => null,
                        'midTerm'    => null,
                        'longTerm'   => null,
                        'colorClass' => 'color-1',
                    ];

                    return view('website.rtc.detail', compact('main', 'managers', 'title', 'hideMainPlans', 'noRoot', 'groupTop'));
                }

                /* ============================= PLANT (FULL SUBTREE) ============================= */
            case 'plant': {
                    $id = (int) $rawId;

                    $p = Plant::with('director')->findOrFail($id);
                    $title = $p->name ?? 'Plant';
                    $hideMainPlans = true; // root plant tanpa S/T M/T L/T

                    $main = [
                        'title'      => $p->name ?? '-',
                        'person'     => RtcHelper::formatPerson($p->director ?? null),
                        'shortTerm'  => null,
                        'midTerm'    => null,
                        'longTerm'   => null,
                        'colorClass' => 'color-1',
                    ];

                    // FULL SUBTREE: division → department → section → sub section
                    $divs = Division::with(['gm', 'short', 'mid', 'long'])
                        ->where('plant_id', $p->id)->orderBy('name')->get();

                    foreach ($divs as $d) {
                        RtcHelper::setAreaContext('division', $d->id);
                        $divNode = [
                            'title'           => $d->name,
                            'person'          => RtcHelper::formatPerson($d->gm),
                            'shortTerm'       => RtcHelper::formatCandidate($d->short, 'short'),
                            'midTerm'         => RtcHelper::formatCandidate($d->mid,   'mid'),
                            'longTerm'        => RtcHelper::formatCandidate($d->long,  'long'),
                            'colorClass'      => 'color-4',
                            'supervisors'     => [],
                            'skipManagerNode' => false,
                        ];

                        // departments
                        $depts = Department::with(['manager', 'short', 'mid', 'long'])
                            ->where('division_id', $d->id)->orderBy('name')->get();

                        foreach ($depts as $dept) {
                            RtcHelper::setAreaContext('department', $dept->id);
                            $deptNode = [
                                'title'           => $dept->name,
                                'person'          => RtcHelper::formatPerson($dept->manager),
                                'shortTerm'       => RtcHelper::formatCandidate($dept->short, 'short'),
                                'midTerm'         => RtcHelper::formatCandidate($dept->mid,   'mid'),
                                'longTerm'        => RtcHelper::formatCandidate($dept->long,  'long'),
                                'colorClass'      => 'color-10',
                                'supervisors'     => [],
                                'skipManagerNode' => false,
                            ];

                            // sections
                            $secs = Section::with(['supervisor', 'short', 'mid', 'long'])
                                ->where('department_id', $dept->id)->orderBy('name')->get();

                            foreach ($secs as $s) {
                                RtcHelper::setAreaContext('section', $s->id);
                                $secNode = [
                                    'title'           => $s->name,
                                    'person'          => RtcHelper::formatPerson($s->supervisor),
                                    'shortTerm'       => RtcHelper::formatCandidate($s->short, 'short'),
                                    'midTerm'         => RtcHelper::formatCandidate($s->mid,   'mid'),
                                    'longTerm'        => RtcHelper::formatCandidate($s->long,  'long'),
                                    'colorClass'      => 'color-8',
                                    'supervisors'     => [],
                                    'skipManagerNode' => false,
                                ];

                                // sub sections
                                $subs = SubSection::with(['leader', 'short', 'mid', 'long'])
                                    ->where('section_id', $s->id)->orderBy('name')->get();

                                foreach ($subs as $sub) {
                                    RtcHelper::setAreaContext('sub_section', $sub->id);
                                    $secNode['supervisors'][] = [
                                        'title'           => $sub->name,
                                        'person'          => RtcHelper::formatPerson($sub->leader),
                                        'shortTerm'       => RtcHelper::formatCandidate($sub->short, 'short'),
                                        'midTerm'         => RtcHelper::formatCandidate($sub->mid,   'mid'),
                                        'longTerm'        => RtcHelper::formatCandidate($sub->long,  'long'),
                                        'colorClass'      => 'color-12',
                                        'supervisors'     => [],
                                        'skipManagerNode' => false,
                                    ];
                                }

                                $deptNode['supervisors'][] = $secNode;
                            }

                            $divNode['supervisors'][] = $deptNode;
                        }

                        $managers[] = $divNode;
                    }
                    break;
                }

                /* ============================ DIVISION ============================ */
            case 'division': {
                    $id = (int) $rawId;

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
                        ->where('division_id', $div->id)->orderBy('name')->get();

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
                            ->where('department_id', $d->id)->orderBy('name')->get();

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

                /* ============================ DEPARTMENT ============================ */
            case 'department': {
                    $id = (int) $rawId;

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
                        ->where('department_id', $d->id)->orderBy('name')->get();

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

                /* ============================== SECTION ============================== */
            case 'section': {
                    $id = (int) $rawId;

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
                        ->where('section_id', $s->id)->orderBy('name')->get();

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

                /* =========================== SUB SECTION =========================== */
            case 'sub_section': {
                    $id = (int) $rawId;

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

        // return view('website.rtc.detail', compact('main', 'managers', 'title', 'hideMainPlans', 'noRoot', 'groupTop'));
        return view('website.rtc.detail', compact('main', 'managers', 'title', 'hideMainPlans', 'noRoot', 'groupTop'));
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

    private function currentPicFor(string $area, $model)
    {
        $empId = match ($area) {
            'division' => $model->gm_id ?? null,
            'department' => $model->manager_id ?? null,
            'section' => $model->supervisor_id ?? null,
            'sub_section' => $model->leader_id ?? null,
            default => null,
        };

        return $empId ? Employee::select('id', 'name', 'position')->find($empId) : null;
    }
}
