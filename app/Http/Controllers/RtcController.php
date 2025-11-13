<?php

namespace App\Http\Controllers;

use App\Helpers\RtcHelper;
use App\Models\Rtc;
use App\Models\Section;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Plant;
use App\Models\RtcComment;
use App\Models\SubSection;
use App\Services\RtcService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RtcController extends Controller
{
    private const RTC_STATUS = [
        'draft'     => 0,
        'submitted' => 1,
        'approved'  => 2,
        'ongoing'   => 3,
    ];

    private function resolveRoleFlags($user): array
    {
        $employee = $user->employee;

        $posRaw = $employee && method_exists($employee, 'getNormalizedPosition')
            ? strtolower((string) $employee->getNormalizedPosition())
            : strtolower((string) ($employee->position ?? ''));

        $isHRD      = ($user->role === 'HRD');
        $isTop2     = in_array($posRaw, ['president', 'vpd', 'vice president director', 'wakil presdir'], true);
        $isDirektur = ($user->role === 'User') && in_array($posRaw, ['direktur', 'director'], true);
        $isGM       = in_array($posRaw, ['gm', 'act gm'], true);
        $isMg       = in_array($posRaw, ['manager', 'coordinator'], true);

        return compact('employee', 'isHRD', 'isTop2', 'isDirektur', 'isGM', 'isMg');
    }

    /** Cek apakah user boleh mengisi RTC pada level-area tertentu */
    private function isAllowedToFill(string $filter, int $areaId, $employee, array $flags): bool
    {
        ['isDirektur' => $isDirektur, 'isGM' => $isGM, 'isMg' => $isMg] = $flags;

        switch ($filter) {
            case 'direksi':
                if ($isDirektur) {
                    return Plant::where('id', $areaId)
                        ->where('director_id', $employee->id)->exists();
                }
                return false;

            case 'division':
                if ($isGM) {
                    return Division::where('id', $areaId)
                        ->where('gm_id', $employee->id)->exists();
                }
                return false;

            case 'department':
                if ($isMg) {
                    return Department::where('id', $areaId)
                        ->where('manager_id', $employee->id)->exists();
                }
                return false;

            case 'section':
                if ($isMg) {
                    return Section::where('id', $areaId)
                        ->whereHas('department', fn($q) => $q->where('manager_id', $employee->id))
                        ->exists();
                }
                return false;

            case 'sub_section':
                if ($isMg) {
                    return SubSection::where('id', $areaId)
                        ->whereHas('section.department', fn($q) => $q->where('manager_id', $employee->id))
                        ->exists();
                }
                return false;

            default:
                return false;
        }
    }

    public function index($company = null)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        // Normalisasi posisi
        $posRaw     = trim((string)($employee->position ?? ''));
        $pos        = strtolower($posRaw);
        $normalized = method_exists($employee, 'getNormalizedPosition')
            ? strtolower((string)$employee->getNormalizedPosition())
            : $pos;

        $isHRD       = ($user->role === 'HRD');
        $isPresident = in_array($pos, ['president', 'presdir', 'president director'], true)
            || in_array($normalized, ['president', 'presdir'], true);
        $isVPD       = in_array($pos, ['vpd', 'vice president director', 'wakil presdir'], true)
            || $normalized === 'vpd';
        $isDirektur  = in_array($pos, ['direktur', 'director'], true) || $normalized === 'direktur';
        $isGM        = in_array($pos, ['gm', 'act gm'], true) || in_array($normalized, ['gm', 'act gm'], true);
        $isMg        = in_array($pos, ['manager', 'coordinator'], true) || in_array($normalized, ['manager', 'coordinator'], true);

        // Tentukan tab default (level) sesuai role
        if ($isHRD || $isPresident || $isVPD) {
            $level = 'company';
        } elseif ($isDirektur) {
            $level = 'direksi';
        } elseif ($isGM) {
            $level = 'division';
        } elseif ($isMg) {
            $level = 'department';
        } else {
            $level = 'division';
        }

        // Semua rendering halaman list dialihkan ke RtcController@list
        return redirect()->route('rtc.list', ['level' => $level]);
    }

    public function list(Request $request, $id = null)
    {
        $trace = (string) Str::uuid();
        $t0    = microtime(true);

        try {
            Log::info('[RTC][list] start', [
                'trace' => $trace,
                'url'   => $request->fullUrl(),
                'q.level' => $request->query('level'),
                'ip'    => $request->ip(),
                'ua'    => substr((string) $request->userAgent(), 0, 200),
            ]);

            $level    = $request->query('level');
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
            $isMg       = in_array($pos, ['manager', 'coordinator'], true) || in_array($normalized, ['manager', 'coordinator'], true);

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
                'isMg' => $isMg,
            ]);

            $readOnly = ($isTop2 || $isHRD);

            /* ===== HRD/Top2: companies & direksi map ===== */
            $companies       = collect();
            $plantsByCompany = [];
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
                'companies_count' => $companies instanceof \Illuminate\Support\Collection ? $companies->count() : 0,
                'plants_map_companies' => array_keys($plantsByCompany),
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

            /* ===== Tabs ===== */
            if ($isHRD || $isTop2) {
                $tabs = [
                    'company'     => ['label' => 'Company',     'show' => true, 'id' => null],
                    'direksi'     => ['label' => 'Direksi',     'show' => true, 'id' => null],
                    'division'    => ['label' => 'Division',    'show' => true, 'id' => null],
                    'department'  => ['label' => 'Department',  'show' => true, 'id' => null],
                    'section'     => ['label' => 'Section',     'show' => true, 'id' => null],
                    // 'sub_section' => ['label' => 'Sub Section', 'show' => true, 'id' => null],
                ];
            } elseif ($isDirektur) {
                $tabs = [
                    'direksi'     => ['label' => 'Direksi',     'show' => true, 'id' => null],
                    'division'    => ['label' => 'Division',    'show' => true, 'id' => null],
                    'department'  => ['label' => 'Department',  'show' => true, 'id' => null],
                    'section'     => ['label' => 'Section',     'show' => true, 'id' => null],
                ];
            } elseif ($isGM) {
                $tabs = [
                    'division'    => ['label' => 'Division',    'show' => true, 'id' => null],
                    'department'  => ['label' => 'Department',  'show' => true, 'id' => null],
                    'section'     => ['label' => 'Section',     'show' => true, 'id' => null],
                ];
            } elseif ($isMg) {
                $tabs = [
                    'department'  => ['label' => 'Department',  'show' => true, 'id' => null],
                    'section'     => ['label' => 'Section',     'show' => true, 'id' => null],
                ];
            } else {
                $tabs = [
                    'division'    => ['label' => 'Division',    'show' => true, 'id' => null],
                ];
            }

            // Default active tab
            if ($level) {
                $activeTab = $level;
            } elseif ($isGM) {
                $activeTab = 'division';
            } elseif ($isDirektur) {
                $activeTab = 'direksi';
            } elseif ($isHRD || $isTop2) {
                $activeTab = 'company';
            } elseif ($isMg) {
                $activeTab = 'department';
            } else {
                $activeTab = array_key_first($tabs) ?: 'division';
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

            // Divisions for select (dept/section/sub_section)
            $divisionsForSelect   = collect();
            $preselectedDivisionId = null;
            if (in_array($tableFilter, ['department', 'section', 'sub_section'], true)) {
                if ($isGM) {
                    $divisionsForSelect = Division::where('gm_id', $employee->id)
                        ->orderBy('name')->get(['id', 'name']);
                    $preselectedDivisionId = optional($divisionsForSelect->first())->id;
                } elseif ($isDirektur && $employee->plant) {
                    $divisionsForSelect = Division::where('plant_id', $employee->plant->id)
                        ->orderBy('name')->get(['id', 'name']);
                } elseif ($isMg) {
                    $divisionsForSelect = Division::whereHas('departments', function ($q) use ($employee) {
                        $q->where('manager_id', $employee->id);
                    })->orderBy('name')->get(['id', 'name']);
                }
            }

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

            if ($tableFilter === 'division') {
                if ($isGM) {
                    $containerId = null;
                } elseif ($isDirektur && $employee->plant) {
                    $containerId = (int) $employee->plant->id;
                } else {
                    $containerId = null;
                }
            } elseif (in_array($tableFilter, ['department', 'section', 'sub_section'], true)) {
                if ($isGM) {
                    $containerId = $preselectedDivisionId ? (int) $preselectedDivisionId : null;
                } elseif ($isMg) {
                    $managerDivisionId = Division::whereHas('departments', function ($q) use ($employee) {
                        $q->where('manager_id', $employee->id);
                    })->value('id');
                    $containerId = $managerDivisionId ? (int) $managerDivisionId : null;
                } else {
                    $containerId = null;
                }
            } elseif ($tableFilter === 'direksi') {
                $containerId = ($isDirektur && $employee->plant) ? (int) $employee->plant->id : null;
            } else {
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

            $hideKpiCols  = in_array($tableFilter, ['company'], true);
            $forceHideAdd = $hideKpiCols
                || ($isGM       && in_array($tableFilter, ['department', 'section', 'sub_section'], true))
                || ($isDirektur && in_array($tableFilter, ['division', 'department', 'section', 'sub_section'], true));

            // =============================== Not-Set count per tab ===============================
            $nz = fn($v) => (int) max(0, (int) ($v ?? 0));
            $counts = [
                'company'     => 0,
                'direksi'     => 0,
                'division'    => 0,
                'department'  => 0,
                'section'     => 0,
                'sub_section' => 0,
            ];

            if ($tabs['company']['show'] ?? false) {
                $counts['company'] = $nz(RtcService::countNotSet('company', []));
            }
            if ($tabs['direksi']['show'] ?? false) {
                if ($isDirektur && $employee->plant) {
                    $counts['direksi'] = $nz(RtcService::countNotSet('direksi', [
                        'plant_ids' => [$employee->plant->id],
                    ]));
                } else {
                    $plantIds = Plant::pluck('id')->all();
                    $counts['direksi'] = $nz(RtcService::countNotSet('direksi', [
                        'plant_ids' => $plantIds,
                    ]));
                }
            }
            if ($tabs['division']['show'] ?? false) {
                if ($isGM) {
                    $divIds = Division::where('gm_id', $employee->id)->pluck('id')->all();
                } elseif ($isDirektur && $employee->plant) {
                    $divIds = Division::where('plant_id', $employee->plant->id)->pluck('id')->all();
                } else {
                    $divIds = Division::pluck('id')->all();
                }
                $counts['division'] = $nz(RtcService::countNotSet('division', [
                    'division_ids' => $divIds,
                ]));
            }

            if ($isGM) {
                $baseDivisionIds = Division::where('gm_id', $employee->id)->pluck('id')->all();
            } elseif ($isDirektur && $employee->plant) {
                $baseDivisionIds = Division::where('plant_id', $employee->plant->id)->pluck('id')->all();
            } elseif ($isMg) {
                $baseDivisionIds = Division::whereHas('departments', function ($q) use ($employee) {
                    $q->where('manager_id', $employee->id);
                })->pluck('id')->all();
            } else {
                $baseDivisionIds = Division::pluck('id')->all();
            }

            foreach (['department', 'section', 'sub_section'] as $lvl) {
                if (($tabs[$lvl]['show'] ?? false) && !empty($baseDivisionIds)) {
                    $counts[$lvl] = $nz(RtcService::countNotSet($lvl, [
                        'division_ids' => $baseDivisionIds,
                    ]));
                }
            }

            foreach ($tabs as $k => $tab) {
                if (!($tab['show'] ?? false)) continue;
                $tabs[$k]['not_set_count'] = $counts[$k] ?? 0;
            }

            Log::debug('[RTC][list] view computed', [
                'trace'          => $trace,
                'activeTab'      => $activeTab,
                'tableFilter'    => $tableFilter,
                'containerId'    => $containerId,
                'cardTitle'      => $cardTitle,
                'hideKpiCols'    => $hideKpiCols,
                'forceHideAdd'   => $forceHideAdd,
                'not_set_counts' => $counts,
            ]);

            // === render view
            return view('website.rtc.list', [
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
        } catch (\Throwable $e) {
            Log::error('[RTC][list] ERROR', [
                'trace' => $trace,
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'stack' => substr($e->getTraceAsString(), 0, 5000),
            ]);

            return back()->with('error', 'Terjadi masalah saat membuka halaman RTC. (trace: ' . $trace . ')');
        }
    }

    public function detail(Request $request)
    {
        $filter = $request->query('filter');
        $id = (int) $request->query('id');

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
                    // mapping id -> nama company tampilan
                    $companyDisplay = match ((string)$rawId) {
                        '1' => 'AII',
                        '2' => 'AIIA',
                        default => strtoupper((string)$rawId),
                    };

                    $company   = strtoupper((string) $companyDisplay);
                    $title     = $companyDisplay . ' — Summary';

                    // layout flags
                    // kita MAU punya parent phantom utk sejajarkan PRESIDENT & VPD
                    $noRoot        = false;      // <- sekarang root ADA lagi (phantom)
                    $groupTop      = false;      // group layout lama dimatikan total
                    $hideMainPlans = true;       // root tidak punya ST/MT/LT

                    // helper format person
                    $fmtPerson = function ($emp) {
                        if (!$emp) return null;

                        $p = RtcHelper::formatPerson($emp) ?? [];

                        $age = $p['age'] ?? null;
                        if ($age === null && !empty($emp->birthday_date)) {
                            try {
                                $age = Carbon::parse($emp->birthday_date)->age;
                            } catch (\Throwable $e) {
                            }
                        }

                        $los = $p['los'] ?? null;
                        foreach (['join_date', 'start_date', 'hire_date', 'first_join_date'] as $col) {
                            if ($los === null && !empty($emp->{$col})) {
                                try {
                                    $los = Carbon::parse($emp->{$col})->diffInYears(now());
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

                    // ambil presiden & vpd
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

                    // build full subtree per plant
                    $plants = Plant::with('director')
                        ->where('company', $company)
                        ->orderBy('name')
                        ->get();

                    $plantTrees = [];
                    foreach ($plants as $p) {
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

                        $divs = Division::with(['gm', 'short', 'mid', 'long'])
                            ->where('plant_id', $p->id)
                            ->orderBy('name')
                            ->get();

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

                            $depts = Department::with(['manager', 'short', 'mid', 'long'])
                                ->where('division_id', $d->id)
                                ->orderBy('name')
                                ->get();

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

                                $secs = Section::with(['supervisor', 'short', 'mid', 'long'])
                                    ->where('department_id', $dept->id)
                                    ->orderBy('name')
                                    ->get();

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

                                    $subs = SubSection::with(['leader', 'short', 'mid', 'long'])
                                        ->where('section_id', $s->id)
                                        ->orderBy('name')
                                        ->get();

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

                    // PRESIDENT node (punya subtree plants)
                    $presidentNode = [
                        'title'           => 'PRESIDENT',
                        'person'          => $fmtPerson($president),
                        'shortTerm'       => null,
                        'midTerm'         => null,
                        'longTerm'        => null,
                        'colorClass'      => 'color-2',
                        'supervisors'     => $plantTrees, // turunannya
                        'skipManagerNode' => false,
                        'no_plans'        => true,
                    ];

                    // VPD node (tidak punya subtree, cuma tampil samping presiden)
                    $vpdNode = [
                        'title'           => 'VPD',
                        'person'          => $fmtPerson($vpd),
                        'shortTerm'       => null,
                        'midTerm'         => null,
                        'longTerm'        => null,
                        'colorClass'      => 'color-3',
                        'supervisors'     => [],
                        'skipManagerNode' => false,
                        'no_plans'        => true,
                    ];

                    // ini phantom parent = perusahaan
                    $main = [
                        'title'           => $companyDisplay, // "AII", "AIIA"
                        'person'          => null,
                        'shortTerm'       => null,
                        'midTerm'         => null,
                        'longTerm'        => null,
                        'colorClass'      => 'color-1', // boleh apa aja, nanti kita override template phantom
                        'phantom'         => true,      // FLAG penting utk blade
                    ];

                    // anak langsung dari phantom parent
                    $managers = [
                        $presidentNode,
                        $vpdNode,
                    ];

                    break;
                }

                /* ============================= PLANT (FULL SUBTREE) ============================= */
            case 'direksi': {
                    $id = (int) $rawId;

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

                    $managers = [];
                    $secs = Section::with(['supervisor', 'short', 'mid', 'long'])
                        ->where('department_id', $d->id)
                        ->orderBy('name')
                        ->get();

                    foreach ($secs as $s) {
                        RtcHelper::setAreaContext('section', $s->id);

                        $subNodes = [];
                        $subs = SubSection::with(['leader', 'short', 'mid', 'long'])
                            ->where('section_id', $s->id)
                            ->orderBy('name')
                            ->get();

                        foreach ($subs as $sub) {
                            RtcHelper::setAreaContext('sub_section', $sub->id);
                            $subNodes[] = [
                                'title'           => $sub->name,
                                'person'          => RtcHelper::formatPerson($sub->leader),
                                'shortTerm'       => RtcHelper::formatCandidate($sub->short, 'short'),
                                'midTerm'         => RtcHelper::formatCandidate($sub->mid,   'mid'),
                                'longTerm'        => RtcHelper::formatCandidate($sub->long,  'long'),
                                'colorClass'      => $mainColor,
                                'supervisors'     => [],
                                'skipManagerNode' => false,
                            ];
                        }

                        $managers[] = [
                            'title'           => $s->name,
                            'person'          => RtcHelper::formatPerson($s->supervisor),
                            'shortTerm'       => RtcHelper::formatCandidate($s->short, 'short'),
                            'midTerm'         => RtcHelper::formatCandidate($s->mid,   'mid'),
                            'longTerm'        => RtcHelper::formatCandidate($s->long,  'long'),
                            'colorClass'      => $mainColor,
                            'supervisors'     => $subNodes,
                            'skipManagerNode' => false,
                        ];
                    }

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

    public function save(Request $request)
    {
        try {
            $request->validate([
                'short_term' => 'nullable|exists:employees,id',
                'mid_term'   => 'nullable|exists:employees,id',
                'long_term'  => 'nullable|exists:employees,id',
                'filter'     => 'required|string|in:company,direksi,division,department,section,sub_section',
                'id'         => 'required|integer|min:1',
            ]);

            $filter = strtolower($request->input('filter'));
            $areaId = (int) $request->input('id');

            $terms = [
                'short' => $request->input('short_term'),
                'mid'   => $request->input('mid_term'),
                'long'  => $request->input('long_term'),
            ];
            $terms = array_filter($terms, fn($v) => !empty($v));

            $user   = auth()->user();
            $flags  = $this->resolveRoleFlags($user);

            // HRD/Top2 hanya view
            if ($flags['isHRD'] || $flags['isTop2']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak diizinkan: HRD/Top2 hanya dapat melihat.',
                ], 403);
            }

            if (!$this->isAllowedToFill($filter, $areaId, $flags['employee'], $flags)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak diizinkan untuk mengisi RTC pada level ini.',
                ], 403);
            }

            $areaKey = $filter;
            $areaModel = match ($areaKey) {
                'division'    => Division::find($areaId),
                'department'  => Department::find($areaId),
                'section'     => Section::find($areaId),
                'sub_section' => SubSection::find($areaId),
                'plant'       => Plant::find($areaId),
                default       => null,
            };

            $picEmp = $this->currentPicFor($areaKey, $areaModel)['id'] ?? null;

            DB::beginTransaction();

            foreach ($terms as $term => $employeeId) {
                $exists = Rtc::where([
                    'area'    => $filter,
                    'area_id' => $areaId,
                    'term'    => $term,
                ])->exists();

                if ($exists) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => "RTC untuk term {$term} sudah ada. Gunakan Update.",
                    ], 409);
                }
            }

            foreach ($terms as $term => $employeeId) {
                Rtc::create([
                    'area'        => $filter,
                    'area_id'     => $areaId,
                    'term'        => $term,
                    'employee_id' => $employeeId,
                    'status'      => self::RTC_STATUS['draft'], // 0
                ]);
            }

            $ongoingExists = Rtc::where([
                'area' => $filter,
                'area_id' => $areaId,
                'term' => null,
            ])->exists();

            $ongoingCreated = false;
            if (!$ongoingExists && $picEmp) {
                Rtc::create([
                    'area'        => $filter,
                    'area_id'     => $areaId,
                    'term'        => null,
                    'employee_id' => $picEmp,
                    'status'      => self::RTC_STATUS['ongoing'], // 3
                ]);
                $ongoingCreated = true;
            }

            DB::commit();

            $withTerms = count($terms) > 0;
            $baseMsg = $withTerms
                ? 'RTC (ST/MT/LT) berhasil disimpan sebagai draft.'
                : 'RTC tanpa ST/MT/LT berhasil disimpan.';

            $ongoingMsg = $ongoingCreated
                ? ' Entri PIC area (ongoing) dibuat.'
                : ($picEmp ? ' Entri PIC area sudah ada, tidak dibuat ulang.' : ' PIC area tidak ditemukan, entri ongoing dilewati.');

            session()->flash('success', trim($baseMsg . ' ' . $ongoingMsg));

            return response()->json([
                'status'  => 'success',
                'message' => trim($baseMsg . ' ' . $ongoingMsg),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function submit(Request $request)
    {
        try {
            $request->validate([
                'filter' => 'required|string|in:company,direksi,division,department,section,sub_section',
                'id'     => 'required|integer|min:1',
            ]);

            $filter = strtolower($request->input('filter'));
            $areaId = (int) $request->input('id');

            $user  = auth()->user();
            $flags = $this->resolveRoleFlags($user);

            if ($flags['isHRD'] || $flags['isTop2']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak diizinkan: HRD/Top2 hanya dapat melihat.',
                ], 403);
            }

            if (!$this->isAllowedToFill($filter, $areaId, $flags['employee'], $flags)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak diizinkan untuk submit RTC pada level ini.',
                ], 403);
            }

            // Pastikan ada minimal satu term utk area ini
            $existing = Rtc::where('area', $filter)->where('area_id', $areaId)->get();
            if ($existing->isEmpty()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'RTC belum diisi. Silakan Save terlebih dahulu.',
                ], 422);
            }

            // Opsional: blok kalau sudah submitted/lebih (idempotent boleh-boleh saja)
            $alreadySubmitted = $existing->every(fn($r) => (int)$r->status >= self::RTC_STATUS['submitted']);
            if ($alreadySubmitted) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'RTC sudah di-submit sebelumnya.',
                ], 409);
            }

            DB::beginTransaction();

            Rtc::where('area', $filter)
                ->where('area_id', $areaId)
                ->update(['status' => self::RTC_STATUS['submitted']]); // 1

            DB::commit();

            session()->flash('success', 'RTC berhasil di-submit.');

            return response()->json([
                'status'  => 'success',
                'message' => 'RTC submitted successfully',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat submit: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'short_term' => 'nullable|exists:employees,id',
                'mid_term'   => 'nullable|exists:employees,id',
                'long_term'  => 'nullable|exists:employees,id',
                'filter'     => 'required|string|in:company,direksi,division,department,section,sub_section',
                'id'         => 'required|integer|min:1',
            ]);

            $filter = strtolower($request->input('filter'));
            $areaId = (int) $request->input('id');

            $termsRaw = [
                'short' => $request->input('short_term'),
                'mid'   => $request->input('mid_term'),
                'long'  => $request->input('long_term'),
            ];

            $user  = auth()->user();
            $flags = $this->resolveRoleFlags($user);

            // HRD/Top2 hanya view
            if ($flags['isHRD'] || $flags['isTop2']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak diizinkan: HRD/Top2 hanya dapat melihat.',
                ], 403);
            }

            if (!$this->isAllowedToFill($filter, $areaId, $flags['employee'], $flags)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak diizinkan untuk mengisi RTC pada level ini.',
                ], 403);
            }

            $areaKey   = $filter;
            $areaModel = match ($areaKey) {
                'division'    => Division::find($areaId),
                'department'  => Department::find($areaId),
                'section'     => Section::find($areaId),
                'sub_section' => SubSection::find($areaId),
                'plant'       => Plant::find($areaId),
                default       => null,
            };

            $picEmp = $this->currentPicFor($areaKey, $areaModel)['id'] ?? null;

            $ongoingKey = \array_key_exists('ongoing', self::RTC_STATUS) ? 'ongoing' : 'continue';
            $ongoingVal = self::RTC_STATUS[$ongoingKey];

            DB::beginTransaction();

            foreach (['short', 'mid', 'long'] as $term) {
                $employeeId = $termsRaw[$term] ?? null;

                if (!empty($employeeId)) {
                    Rtc::updateOrCreate(
                        [
                            'area'    => $filter,
                            'area_id' => $areaId,
                            'term'    => $term,
                        ],
                        [
                            'employee_id' => $employeeId,
                            'status'      => self::RTC_STATUS['draft'], // 0
                        ]
                    );
                } else {
                    Rtc::where([
                        'area'    => $filter,
                        'area_id' => $areaId,
                        'term'    => $term,
                    ])->delete();
                }
            }

            $ongoing = Rtc::where([
                'area'    => $filter,
                'area_id' => $areaId,
                'term'    => null,
            ])->first();

            if ($picEmp) {
                if ($ongoing) {
                    if ((int)$ongoing->employee_id !== (int)$picEmp) {
                        $ongoing->employee_id = $picEmp;
                        $ongoing->status = $ongoingVal;
                        $ongoing->save();
                    } else {
                        if ((int)$ongoing->status !== (int)$ongoingVal) {
                            $ongoing->status = $ongoingVal;
                            $ongoing->save();
                        }
                    }
                } else {
                    Rtc::create([
                        'area'        => $filter,
                        'area_id'     => $areaId,
                        'term'        => null,
                        'employee_id' => $picEmp,
                        'status'      => $ongoingVal,
                    ]);
                }
            }

            DB::commit();

            session()->flash('success', 'RTC berhasil diupdate (draft).');

            return response()->json([
                'status'  => 'success',
                'message' => 'RTC berhasil diupdate (draft).',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function approveArea(Request $request)
    {
        $request->validate([
            'area'    => 'required|string',
            'area_id' => 'required|integer',
        ]);

        $user      = auth()->user();
        $employee  = $user->employee;
        $norm      = strtolower($employee->getNormalizedPosition());

        $area      = strtolower($request->input('area'));
        $areaId    = (int) $request->input('area_id');

        $allowed = false;

        // Semua approver (GM/Direktur/VPD/President) -> submitted (1) -> approved (2)
        if ($norm === 'gm') {
            $divIds     = Division::where('gm_id', $employee->id)->pluck('id');
            $deptIds    = Department::whereIn('division_id', $divIds)->pluck('id');
            $sectionIds = Section::whereIn('department_id', $deptIds)->pluck('id');
            $subIds     = SubSection::whereIn('section_id', $sectionIds)->pluck('id');

            $allowed =
                ($area === 'department'  && $deptIds->contains($areaId)) ||
                ($area === 'section'     && $sectionIds->contains($areaId)) ||
                ($area === 'sub_section' && $subIds->contains($areaId));
        } elseif (in_array($norm, ['direktur', 'director', 'act direktur'], true)) {
            $plantId = optional($employee->plant)->id;
            $divIds  = Division::where('plant_id', $plantId)->pluck('id');
            $allowed = ($area === 'division' && $divIds->contains($areaId));
        } elseif (in_array($norm, ['vpd', 'vice president director', 'president'], true)) {
            $plantIds = Plant::pluck('id');
            $allowed  = in_array($area, ['direksi', 'plant'], true) && $plantIds->contains($areaId);
        }

        if (!$allowed) {
            return response()->json(['message' => 'Not allowed.'], 403);
        }

        $fromStatus = self::RTC_STATUS['submitted']; // 1
        $toStatus   = self::RTC_STATUS['approved'];  // 2

        $rtcs = Rtc::whereIn('area', [$area, ucfirst($area)])
            ->where('area_id', $areaId)
            ->where('status', $fromStatus)
            ->get();

        if ($rtcs->isEmpty()) {
            return response()->json(['message' => 'No matching RTCs to update.'], 404);
        }

        DB::transaction(function () use ($rtcs, $toStatus, $area, $areaId) {
            foreach ($rtcs as $rtc) {
                $rtc->status = $toStatus; // 2
                $rtc->save();

                // Copy kandidat ke struktur saat APPROVED (2)
                if (
                    $rtc->status === self::RTC_STATUS['approved'] &&
                    in_array($area, ['division', 'department', 'section', 'sub_section'], true)
                ) {
                    $modelClass = match ($area) {
                        'division'    => Division::class,
                        'department'  => Department::class,
                        'section'     => Section::class,
                        'sub_section' => SubSection::class,
                        default       => null
                    };

                    if ($modelClass) {
                        $record = $modelClass::find($areaId);
                        if ($record) {
                            $record->update([
                                $rtc->term . '_term' => $rtc->employee_id
                            ]);
                        }
                    }
                }
            }
        });

        return response()->json(['message' => 'Approved.']);
    }

    public function reviseArea(Request $request)
    {
        $request->validate([
            'area'    => 'required|string',
            'area_id' => 'required|integer',
            'comment' => 'nullable|string',
        ]);

        $user      = auth()->user();
        $employee  = $user->employee;
        $norm      = strtolower($employee->getNormalizedPosition());

        $area    = strtolower($request->input('area'));
        $areaId  = (int) $request->input('area_id');
        $comment = $request->comment;

        $allowed = false;

        if ($norm === 'gm') {
            $divIds     = Division::where('gm_id', $employee->id)->pluck('id');
            $deptIds    = Department::whereIn('division_id', $divIds)->pluck('id');
            $sectionIds = Section::whereIn('department_id', $deptIds)->pluck('id');
            $subIds     = SubSection::whereIn('section_id', $sectionIds)->pluck('id');

            $allowed =
                ($area === 'department'  && $deptIds->contains($areaId)) ||
                ($area === 'section'     && $sectionIds->contains($areaId)) ||
                ($area === 'sub_section' && $subIds->contains($areaId));
        } elseif ($norm === 'direktur') {
            $plantId = optional($employee->plant)->id;
            $divIds  = Division::where('plant_id', $plantId)->pluck('id');
            $allowed = ($area === 'division' && $divIds->contains($areaId));
        } elseif ($norm === 'vpd' || $norm === 'president') {
            $plantIds = Plant::pluck('id');
            $allowed = in_array($area, ['direksi', 'plant'], true)
                && $plantIds->contains($areaId);
        }

        if (!$allowed) {
            return response()->json([
                'message' => 'Not allowed.'
            ], 403);
        }

        // IMPORTANT: exclude ongoing (term IS NULL) agar tidak ikut direvisi
        $rtcs = Rtc::whereRaw('LOWER(area) = ?', [$area])
            ->where('area_id', $areaId)
            ->whereNotNull('term')
            ->get();

        if ($rtcs->isEmpty()) {
            return response()->json([
                'message' => 'No RTCs found in this area.'
            ], 404);
        }

        DB::transaction(function () use ($rtcs, $comment, $employee) {
            foreach ($rtcs as $rtc) {
                $oldStatus = $rtc->status;
                $rtc->status = -1;
                $rtc->save();

                RtcComment::create([
                    'rtc_id'      => $rtc->id,
                    'employee_id' => $employee->id,
                    'status_from' => $oldStatus,
                    'status_to'   => -1,
                    'comment'     => $comment
                ]);
            }
        });

        return response()->json([
            'message' => 'Revised back to submitter.'
        ]);
    }

    public function approval()
    {
        $user     = auth()->user();
        $employee = $user->employee;
        $norm     = strtolower($employee->getNormalizedPosition());

        $queue = collect();
        $stage = 'approve';

        if ($norm === 'gm') {
            $divIds     = Division::where('gm_id', $employee->id)->pluck('id');
            $deptIds    = Department::whereIn('division_id', $divIds)->pluck('id');
            $sectionIds = Section::whereIn('department_id', $deptIds)->pluck('id');
            $subIds     = SubSection::whereIn('section_id', $sectionIds)->pluck('id');

            $queue = Rtc::with(['employee', 'department', 'section', 'subsection', 'division', 'plant'])
                ->where('status', 1)
                ->whereNotNull('term')
                ->where(function ($q) use ($deptIds, $sectionIds, $subIds) {
                    $q->where(fn($qq) => $qq->where('area', 'department')->whereIn('area_id', $deptIds))
                        ->orWhere(fn($qq) => $qq->where('area', 'section')->whereIn('area_id', $sectionIds))
                        ->orWhere(fn($qq) => $qq->where('area', 'sub_section')->whereIn('area_id', $subIds));
                })->get();
        } elseif (in_array($norm, ['direktur', 'director', 'act direktur'], true)) {
            $plantId = optional($employee->plant)->id;
            $divIds  = Division::where('plant_id', $plantId)->pluck('id');

            $queue = Rtc::with(['employee', 'department', 'section', 'subsection', 'division', 'plant'])
                ->where('status', 1)
                ->whereNotNull('term')
                ->whereIn('area', ['division', 'Division'])
                ->whereIn('area_id', $divIds)
                ->get();
        } elseif (in_array($norm, ['vpd', 'vice president director'], true)) {
            $plantIds = Plant::pluck('id');

            $queue = Rtc::with(['employee', 'department', 'section', 'subsection', 'division', 'plant'])
                ->where('status', 1)
                ->whereNotNull('term')
                ->whereIn('area', ['direksi', 'plant'])
                ->whereIn('area_id', $plantIds)
                ->get();
        } elseif ($norm === 'president') {
            $plantIds = Plant::pluck('id');

            $queue = Rtc::with(['employee', 'department', 'section', 'subsection', 'division', 'plant'])
                ->where('status', 1)
                ->whereNotNull('term')
                ->whereIn('area', ['direksi', 'plant'])
                ->whereIn('area_id', $plantIds)
                ->get();
        }

        $grouped = $queue
            ->groupBy(fn($rtc) => strtolower($rtc->area) . '#' . $rtc->area_id)
            ->map(function ($items) {
                $first   = $items->first();
                $areaKey = strtolower($first->area);

                $statusMap = [
                    -1 => 'Revised',
                    0  => 'Draft',
                    1  => 'Submitted',
                    2  => 'Approved',
                ];

                $statusCounts = $items->groupBy('status')
                    ->map(fn($col) => $col->count())
                    ->mapWithKeys(fn($count, $code) => [($statusMap[$code] ?? 'Unknown') => $count]);

                $areaModel = match ($areaKey) {
                    'division'    => $first->division    ?? Division::find($first->area_id),
                    'department'  => $first->department  ?? Department::find($first->area_id),
                    'section'     => $first->section     ?? Section::find($first->area_id),
                    'sub_section' => $first->subsection  ?? SubSection::find($first->area_id),
                    'plant'       => $first->plant       ?? Plant::find($first->area_id),
                    default       => null,
                };

                $picEmp = $areaModel ? $this->currentPicFor($areaKey, $areaModel) : null;
                $currentPic = $picEmp
                    ? trim($picEmp->name . ($picEmp->position ? ' (' . $picEmp->position . ')' : ''))
                    : '-';

                return [
                    'area'        => $areaKey,
                    'area_id'     => $first->area_id,
                    'area_name'   => $first->area_name,
                    'current_pic' => $currentPic,
                    'total_rtc'   => $items->count(),
                    'terms'       => $items->pluck('term')->unique()->values()->all(),
                    'status_info' => $statusCounts,
                    'sample_ids'  => $items->pluck('id')->take(3)->values()->all(),
                ];
            })->values();

        return view('website.approval.rtc.index', [
            'rtcs'  => $grouped,
            'stage' => 'approve',
            'title' => 'Approval'
        ]);
    }

    public function getAreaItems(Request $request)
    {
        $request->validate([
            'area'    => 'required|string',
            'area_id' => 'required|integer',
        ]);

        $area   = strtolower($request->input('area'));
        $areaId = (int) $request->input('area_id');

        $rtcs = Rtc::with(['employee', 'employee.department'])
            ->whereRaw('LOWER(area) = ?', [$area])
            ->where('area_id', $areaId)
            ->whereNotNull('term') // ⬅️ exclude ongoing
            ->get(['id', 'employee_id', 'area', 'area_id', 'term', 'status']);

        $payload = $rtcs->map(function ($rtc) {
            return [
                'id'           => $rtc->id,
                'term'         => $rtc->term,
                'status'       => $rtc->status,
                'employee_id'  => $rtc->employee_id,
                'employee'     => [
                    'id'            => $rtc->employee->id ?? null,
                    'npk'           => $rtc->employee->npk ?? null,
                    'name'          => $rtc->employee->name ?? null,
                    'company_name'  => $rtc->employee->company_name ?? null,
                    'position'      => $rtc->employee->position ?? null,
                    'department'    => ['name' => optional($rtc->employee->department)->name],
                ],
            ];
        })->values();

        return response()->json($payload);
    }

    public function approve($id)
    {
        $rtc = Rtc::with('employee')->findOrFail($id);

        $user = auth()->user();
        $employee = $user->employee;
        $norm = strtolower($employee->getNormalizedPosition());

        $area = strtolower($rtc->area);
        $areaId = (int)$rtc->area_id;

        // decide permission + next status
        $nextStatus = null;
        $allowed = false;

        if ($norm === 'gm') {
            // approve dept/section/sub_section in GM divisions (0 -> 2)
            $divIds     = Division::where('gm_id', $employee->id)->pluck('id');
            $deptIds    = Department::whereIn('division_id', $divIds)->pluck('id');
            $sectionIds = Section::whereIn('department_id', $deptIds)->pluck('id');
            $subIds     = SubSection::whereIn('section_id', $sectionIds)->pluck('id');

            $allowed =
                ($area === 'department'  && $deptIds->contains($areaId)) ||
                ($area === 'section'     && $sectionIds->contains($areaId)) ||
                ($area === 'sub_section' && $subIds->contains($areaId));

            if ($allowed && $rtc->status === 0) $nextStatus = 2;
        } elseif ($norm === 'direktur') {
            // approve division in own plant (0 -> 2)
            $plantId = optional($employee->plant)->id;
            $divIds  = Division::where('plant_id', $plantId)->pluck('id');
            $allowed = ($area === 'division' && $divIds->contains($areaId));
            if ($allowed && $rtc->status === 0) $nextStatus = 2;
        } elseif ($norm === 'vpd') {
            // check plant/direksi (0 -> 1)
            $plantIds = Plant::pluck('id');
            $allowed = in_array($area, ['direksi', 'plant'], true) && $plantIds->contains($areaId);
            if ($allowed && $rtc->status === 0) $nextStatus = 1;
        } elseif ($norm === 'president') {
            // approve plant/direksi (1 -> 2)
            $plantIds = Plant::pluck('id');
            $allowed = in_array($area, ['direksi', 'plant'], true) && $plantIds->contains($areaId);
            if ($allowed && $rtc->status === 1) $nextStatus = 2;
        }

        if (!$allowed || is_null($nextStatus)) {
            return response()->json(['message' => 'Not allowed or invalid status transition.'], 403);
        }

        $rtc->status = $nextStatus;
        $rtc->save();

        if ($rtc->status === 2 && in_array($area, ['division', 'department', 'section', 'sub_section'], true)) {
            $modelClass = match ($area) {
                'division'    => Division::class,
                'department'  => Department::class,
                'section'     => Section::class,
                'sub_section' => SubSection::class,
                default       => null
            };
            if ($modelClass) {
                $record = $modelClass::find($areaId);
                if ($record) {
                    $record->update([$rtc->term . '_term' => $rtc->employee_id]);
                }
            }
        }

        return response()->json(['message' => $nextStatus === 1 ? 'Checked.' : 'Approved.']);
    }

    public function revise($id, Request $request)
    {
        $rtc = Rtc::findOrFail($id);

        $user = auth()->user();
        $employee = $user->employee;
        $norm = strtolower($employee->getNormalizedPosition());

        $area = strtolower($rtc->area);
        $areaId = (int)$rtc->area_id;

        $allowed = false;

        if ($norm === 'gm') {
            $divIds     = Division::where('gm_id', $employee->id)->pluck('id');
            $deptIds    = Department::whereIn('division_id', $divIds)->pluck('id');
            $sectionIds = Section::whereIn('department_id', $deptIds)->pluck('id');
            $subIds     = SubSection::whereIn('section_id', $sectionIds)->pluck('id');

            $allowed =
                ($area === 'department'  && $deptIds->contains($areaId)) ||
                ($area === 'section'     && $sectionIds->contains($areaId)) ||
                ($area === 'sub_section' && $subIds->contains($areaId));
        } elseif ($norm === 'direktur') {
            $plantId = optional($employee->plant)->id;
            $divIds  = Division::where('plant_id', $plantId)->pluck('id');
            $allowed = ($area === 'division' && $divIds->contains($areaId));
        } elseif ($norm === 'vpd' || $norm === 'president') {
            $plantIds = Plant::pluck('id');
            $allowed = in_array($area, ['direksi', 'plant'], true) && $plantIds->contains($areaId);
        }

        if (!$allowed) {
            return response()->json(['message' => 'Not allowed.'], 403);
        }

        // set back to Submitted (0)
        $rtc->status = 0;
        $rtc->save();

        // (opsional) simpan comment revisi ke table audit/log terpisah

        return response()->json(['message' => 'Revised back to submitter.']);
    }

    private function currentPicFor(string $area, $model)
    {
        $empId = match ($area) {
            'plant'       => $model->director_id ?? null,
            'division'    => $model->gm_id ?? null,
            'department'  => $model->manager_id ?? null,
            'section'     => $model->supervisor_id ?? null,
            'sub_section' => $model->leader_id ?? null,
            default       => null,
        };

        return $empId ? Employee::get(['id', 'name', 'position'])->find($empId) : null;
    }
}
