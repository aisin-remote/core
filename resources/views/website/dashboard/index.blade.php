@extends('layouts.root.main')

@section('title', $title ?? 'Dashboard')
@section('breadcrumbs', $title ?? 'Dashboard')

@push('custom-css')
    <style>
        :root {
            --ok-green: #009E73;
            --ok-blue: #0072B2;
            --ok-orange: #E69F00;
            --ok-grey: #7F7F7F;
            --ok-navy: #0F172A;
            --ok-bg: #F8FAFC;
            --ok-line: #E5E7EB;
            --radius-lg: 14px;
        }

        body {
            background: var(--ok-bg);
        }

        .card {
            border: 1px solid var(--ok-line);
            border-radius: var(--radius-lg);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px dashed var(--ok-line);
        }

        .kpi-card .value {
            font-size: clamp(1.6rem, 2.8vw, 2.2rem);
            font-weight: 800;
        }

        .kpi-card .kpi-sub {
            font-size: .9rem;
            color: #64748B;
        }

        .kpi-card .bi {
            font-size: 1.1rem;
            opacity: .9;
        }

        .chart-card {
            min-height: 320px;
        }

        .chart-wrapper {
            position: relative;
            height: 260px;
        }

        .table.clean {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table.clean thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
            color: var(--ok-navy);
            font-weight: 800;
            border-bottom: 1px solid var(--ok-line);
        }

        .table.clean tbody tr:nth-child(even) {
            background: #fcfcfd;
        }

        .table.clean th,
        .table.clean td {
            padding: .7rem 1rem;
            font-size: .92rem;
        }

        .table.clean tbody td {
            font-weight: 600;
            color: #1f2937;
        }

        .table.clean tr:hover td {
            background: #EEF2F7;
        }

        /* Compact controls */
        .btn,
        .form-select,
        .form-control {
            min-height: 32px;
            font-size: .85rem;
            padding: .2rem .5rem;
        }

        .form-select-sm,
        .btn-sm {
            min-height: 30px;
            font-size: .8rem;
            padding: .2rem .45rem;
        }

        /* Module layout: chart left, KPI row (wrapping) on the right */
        .module-strip {
            display: grid;
            gap: 12px;
            grid-template-columns: 5fr 7fr;
            /* chart 5, kpis 7 */
            align-items: stretch;
        }

        .module-strip .module-chart {
            grid-column: 1 / span 1;
        }

        .module-kpis {
            grid-column: 2 / span 1;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-content: flex-start;
        }

        .module-kpis .kpi-card {
            flex: 1 1 calc(33.333% - 12px);
            min-width: 180px;
        }

        /* ~3 cards per row on wide */

        @media (max-width: 1200px) {
            .module-strip {
                grid-template-columns: 1fr;
            }

            .module-kpis {
                grid-column: 1 / span 1;
            }

            .module-kpis .kpi-card {
                flex-basis: calc(50% - 12px);
            }

            /* ~2 per row */
        }

        @media (max-width: 768px) {
            .module-kpis .kpi-card {
                flex-basis: 100%;
            }

            /* 1 per row */
        }

        /* High-contrast tabs for better visibility */
        .nav-tabs {
            border-bottom: 2px solid var(--ok-line);
            gap: .25rem;
        }

        .nav-tabs .nav-link {
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: var(--ok-navy);
            background: #F1F5F9;
        }

        .nav-tabs .nav-link:hover {
            background: #E2E8F0;
            color: var(--ok-navy);
        }

        .nav-tabs .nav-link.active {
            background: var(--ok-blue);
            color: #fff !important;
        }

        @media (min-width: 992px) {
            .nav-tabs .nav-link {
                font-size: 1rem;
            }
        }
    </style>
@endpush

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div class="d-flex align-items-center gap-3">
                <h3 class="mb-0 fw-bolder">{{ $title ?? 'Dashboard' }}</h3>
            </div>
        </div>

        {{-- FILTER BAR (Company only, compact & auto-apply) --}}
        @php
            $isHRD = auth()->user()->role === 'HRD';
            $myCompany = optional(auth()->user()->employee)->company_name; // 'AII' atau 'AIIA'
        @endphp
        @if ($isHRD)
            <div class="card mb-4">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3 col-8">
                            <label class="form-label mb-1">Company</label>
                            <select id="filter-company" class="form-select form-select-sm" data-control="select2"
                                aria-label="Filter Company" {{ $isHRD ? '' : 'disabled' }}>
                                <option value="">All Companies</option>
                                <option value="AIIA">AIIA</option>
                                <option value="AII">AII</option>
                                <option value="{{ $myCompany }}" selected>{{ $myCompany }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-4">
                            <label class="form-label d-block mb-1">&nbsp;</label>
                            <button id="btn-clear" class="btn btn-warning btn-sm w-100"><i class="bi bi-x-circle"></i>
                                Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- TABS --}}
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-all" role="tab">All</a>
            </li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-idp" role="tab">IDP</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-hav" role="tab">HAV</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-icp" role="tab">ICP</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-rtc" role="tab">RTC</a></li>
        </ul>

        <div class="tab-content">
            {{-- ================= TAB ALL ================= --}}
            <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
                {{-- KPI CARDS --}}
                <div class="row g-3 mb-4">
                    @php
                        $kpis = [
                            ['label' => 'In Scope', 'id' => 'kpi-all-in-scope', 'icon' => 'bi-bullseye'],
                            [
                                'label' => 'Completion',
                                'id' => 'kpi-all-completion',
                                'icon' => 'bi-clipboard2-check',
                                'sub' => 'Approved / In scope',
                            ],
                            [
                                'label' => 'Approved',
                                'id' => 'kpi-all-approved',
                                'icon' => 'bi-check2-circle',
                                'class' => 'text-success',
                            ],
                            [
                                'label' => 'In Progress',
                                'id' => 'kpi-all-progress',
                                'icon' => 'bi-arrow-repeat',
                                'class' => 'text-primary',
                            ],
                            [
                                'label' => 'Revised',
                                'id' => 'kpi-all-revised',
                                'icon' => 'bi-pencil-square',
                                'class' => 'text-warning',
                            ],
                            [
                                'label' => 'Not Created',
                                'id' => 'kpi-all-not',
                                'icon' => 'bi-dash-circle',
                                'class' => 'text-gray-700',
                            ],
                        ];
                    @endphp
                    @foreach ($kpis as $card)
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card kpi-card h-auto">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="text-muted">{{ $card['label'] }}</div>
                                        <i class="bi {{ $card['icon'] }}"></i>
                                    </div>
                                    <div class="value {{ $card['class'] ?? '' }}" id="{{ $card['id'] }}">0</div>
                                    @if (isset($card['sub']))
                                        <div class="kpi-sub">{{ $card['sub'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- CHARTS per module --}}
                <div class="row g-4">
                    @foreach (['idp' => 'IDP', 'hav' => 'HAV', 'icp' => 'ICP', 'rtc' => 'RTC'] as $k => $label)
                        <div class="col-lg-6 col-md-12">
                            <div class="card chart-card">
                                <div class="card-header d-flex align-items-center gap-2">
                                    <h5 class="mb-0">{{ $label }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-wrapper">
                                        <canvas id="chart-all-{{ $k }}"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= MODULE TABS ================= --}}
            @php
                $modules = [
                    ['key' => 'idp', 'label' => 'IDP'],
                    ['key' => 'hav', 'label' => 'HAV'],
                    ['key' => 'icp', 'label' => 'ICP'],
                    ['key' => 'rtc', 'label' => 'RTC'],
                ];
            @endphp

            @foreach ($modules as $m)
                <div class="tab-pane fade" id="tab-{{ $m['key'] }}" role="tabpanel">

                    {{-- One-row strip: Chart (left) + KPI row (right, wrapping) --}}
                    <div class="module-strip">
                        {{-- Chart (left) --}}
                        <div class="card chart-card module-chart">
                            <div class="card-header d-flex align-items-center gap-2">
                                <h5 class="mb-0">{{ $m['label'] }} Status Share</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-wrapper"><canvas id="chart-{{ $m['key'] }}-doughnut"></canvas></div>
                            </div>
                        </div>

                        {{-- KPI row (right, wraps) --}}
                        <div class="module-kpis">
                            @php $k2 = $m['key']; @endphp
                            @foreach ([['l' => 'In Scope', 'i' => 'kpi-' . $k2 . '-scope', 'ic' => 'bi-bullseye'], ['l' => 'Completion', 'i' => 'kpi-' . $k2 . '-completion', 'ic' => 'bi-clipboard2-check', 'sub' => 'Approved / In scope'], ['l' => 'Approved', 'i' => 'kpi-' . $k2 . '-appr', 'ic' => 'bi-check2-circle', 'c' => 'text-success'], ['l' => 'In Progress', 'i' => 'kpi-' . $k2 . '-prog', 'ic' => 'bi-arrow-repeat', 'c' => 'text-primary'], ['l' => 'Revised', 'i' => 'kpi-' . $k2 . '-rev', 'ic' => 'bi-pencil-square', 'c' => 'text-warning'], ['l' => 'Not Created', 'i' => 'kpi-' . $k2 . '-not', 'ic' => 'bi-dash-circle']] as $card)
                                <div class="card kpi-card h-full">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="text-muted">{{ $card['l'] }}</div>
                                            <i class="bi {{ $card['ic'] }}"></i>
                                        </div>
                                        <div class="value {{ $card['c'] ?? '' }}" id="{{ $card['i'] }}">0</div>
                                        @if (isset($card['sub']))
                                            <div class="kpi-sub">{{ $card['sub'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tables --}}
                    <div class="row g-4 mt-1">
                        @foreach (['approved' => 'Approved', 'progress' => 'In Progress', 'not' => 'Not Created', 'revised' => 'Revised'] as $statusKey => $statusLabel)
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center gap-2">
                                        <h5 class="mb-0">{{ $statusLabel }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tbl-{{ $m['key'] }}-{{ $statusKey }}"
                                                class="table table-row-dashed align-middle clean">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Employee</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /** Colors & constants */
        const STATUS_COLORS = {
            approved: '#009E73',
            progress: '#0072B2',
            revised: '#E69F00',
            not: '#7F7F7F'
        };
        const MODULES = ['idp', 'hav', 'icp', 'rtc'];
        const IS_HRD = @json(auth()->user()->role === 'HRD');
        const MY_COMPANY = @json(optional(auth()->user()->employee)->company_name);

        /** Helpers */
        const ctx = (id) => document.getElementById(id)?.getContext('2d');
        const charts = {};
        const setText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text;
        };
        const pct = (num, den) => den ? Math.round(num / den * 100) : 0;

        function createOrUpdateChart(key, type, elCtx, data, options = {}) {
            if (!elCtx) return;
            if (charts[key]) charts[key].destroy();
            charts[key] = new Chart(elCtx, {
                type,
                data,
                options
            });
            return charts[key];
        }

        /** DataTable builder (single column: Employee) */
        function buildEmployeeTable($table, names = []) {
            if ($table.length === 0) return;
            if ($.fn.DataTable.isDataTable($table)) {
                $table.DataTable().clear().destroy();
            }
            const rows = [...new Set(names)]
                .filter(Boolean)
                .sort((a, b) => a.localeCompare(b))
                .map(n => ({
                    employee: n
                }));

            $table.DataTable({
                data: rows,
                columns: [{
                    data: 'employee',
                    title: 'Employee'
                }],
                pageLength: 10,
                lengthChange: false,
                searching: false,
                ordering: true,
                order: [
                    [0, 'asc']
                ],
                autoWidth: false,
                destroy: true,
                responsive: true
            });
        }

        /** API */
        async function fetchDashboard(params = {}) {
            const qs = new URLSearchParams();
            if (params.company !== undefined && params.company !== null) qs.set('company', params.company);
            const url = `{{ route('dashboard.summary') }}` + '?' + qs.toString();
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Failed to load dashboard summary');
            return await res.json();
        }

        /** Renderers */
        function renderAllTab(data) {
            setText('kpi-all-in-scope', data.all?.scope ?? 0);
            setText('kpi-all-approved', data.all?.approved ?? 0);
            setText('kpi-all-progress', data.all?.progress ?? 0);
            setText('kpi-all-revised', data.all?.revised ?? 0);
            setText('kpi-all-not', data.all?.not ?? 0);
            setText('kpi-all-completion', pct(data.all?.approved ?? 0, data.all?.scope ?? 0) + '%');

            // pies per module
            MODULES.forEach((mod) => {
                const s = data[mod] || {
                    approved: 0,
                    progress: 0,
                    revised: 0,
                    not: 0
                };
                const total = (s.approved + s.progress + s.revised + s.not) || 1;

                createOrUpdateChart(
                    `all-pie-${mod}`,
                    'doughnut',
                    ctx(`chart-all-${mod}`), {
                        labels: ['Approved', 'In Progress', 'Revised', 'Not Created'],
                        datasets: [{
                            data: [s.approved, s.progress, s.revised, s.not],
                            backgroundColor: [STATUS_COLORS.approved, STATUS_COLORS.progress,
                                STATUS_COLORS.revised, STATUS_COLORS.not
                            ]
                        }]
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                );
            });
        }

        function renderModuleTab(key, data) {
            setText(`kpi-${key}-scope`, data?.scope ?? 0);
            setText(`kpi-${key}-appr`, data?.approved ?? 0);
            setText(`kpi-${key}-prog`, data?.progress ?? 0);
            setText(`kpi-${key}-rev`, data?.revised ?? 0);
            setText(`kpi-${key}-not`, data?.not ?? 0);
            setText(`kpi-${key}-completion`, pct(data?.approved ?? 0, data?.scope ?? 0) + '%');

            createOrUpdateChart(
                `chart-${key}-doughnut`,
                'doughnut',
                ctx(`chart-${key}-doughnut`), {
                    labels: ['Approved', 'In Progress', 'Revised', 'Not Created'],
                    datasets: [{
                        data: [data?.approved ?? 0, data?.progress ?? 0, data?.revised ?? 0, data?.not ?? 0],
                        backgroundColor: [STATUS_COLORS.approved, STATUS_COLORS.progress, STATUS_COLORS.revised,
                            STATUS_COLORS.not
                        ]
                    }]
                }, {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            );

            // init empty tables
            ['approved', 'progress', 'revised', 'not'].forEach(status => {
                buildEmployeeTable($(`#tbl-${key}-${status}`), []);
            });

            // load lists per status
            (async function loadModuleTables() {
                let company = $('#filter-company').val();
                if (!IS_HRD) company = MY_COMPANY;

                const base = {
                    company,
                    module: key
                };
                for (const status of ['approved', 'progress', 'revised', 'not']) {
                    const qs = new URLSearchParams({
                        ...base,
                        status
                    }).toString();
                    const res = await fetch(`{{ route('dashboard.list') }}?${qs}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    const names = (json.rows || []).map(r => r.employee);
                    buildEmployeeTable($(`#tbl-${key}-${status}`), names);
                }
            })();
        }

        /** Load & UI */
        async function loadDashboard() {
            try {
                let company = $('#filter-company').val();
                if (!IS_HRD) company = MY_COMPANY;

                const params = {
                    company
                };
                localStorage.setItem('dash-filters', JSON.stringify(params));

                const data = await fetchDashboard(params);

                renderAllTab(data);
                MODULES.forEach(k => renderModuleTab(k, data[k] || {}));
            } catch (e) {
                console.error(e);
                await Swal.fire({
                    title: 'Error',
                    text: 'Failed to load dashboard data.',
                    icon: 'error'
                });
            }
        }

        // Fix charts resize when switching tabs
        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(el => {
            el.addEventListener('shown.bs.tab', () => {
                Object.values(charts).forEach(c => c && c.resize());
            });
        });

        // Select2 init
        $('[data-control="select2"]').select2({
            allowClear: true,
            width: '100%'
        });

        // Lock company for non-HRD
        if (!IS_HRD) {
            $('#filter-company').val(MY_COMPANY).trigger('change').prop('disabled', true);
        }

        // Auto-apply on change, clear
        $('#filter-company').on('change', loadDashboard);
        $('#btn-clear').on('click', function() {
            if (IS_HRD) $('#filter-company').val('').trigger('change');
        });

        // Restore saved filter
        const saved = localStorage.getItem('dash-filters');
        if (saved) {
            try {
                const f = JSON.parse(saved);
                if (f.company != null) $('#filter-company').val(f.company).trigger('change');
            } catch {}
        }

        // First load
        loadDashboard();
    </script>
@endpush
