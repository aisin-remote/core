@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection
@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@push('custom-css')
    <style>
        /* ===== Status chip base ===== */
        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .9rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: .9rem;
            line-height: 1;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
        }

        /* Approved (green) */
        .status-chip[data-status="approved"] {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        /* Submitted/Waiting (yellow) */
        .status-chip[data-status="waiting"] {
            background: #fffbeb;
            color: #92400e;
            border-color: #fde68a;
        }

        /* Draft (gray) */
        .status-chip[data-status="draft"] {
            background: #f8fafc;
            color: #334155;
            border-color: #e2e8f0;
        }

        /* Not created (neutral) */
        .status-chip[data-status="not_created"] {
            background: #f4f4f5;
            color: #27272a;
            border-color: #e4e4e7;
        }

        /* NEW: Ongoing (blue-ish) */
        .status-chip[data-status="ongoing"] {
            background: #eff6ff;
            color: #1e40af;
            border-color: #bfdbfe;
        }

        .term-cell {
            max-width: 180px;
            white-space: normal;
            line-height: 1.25;
            word-break: break-word;
        }

        .text-not-set {
            color: #475569;
            opacity: .7;
        }

        .action-stack {
            display: inline-flex;
            gap: .5rem;
        }

        .select2-container {
            width: 100% !important;
        }

        /* ===== Tab alert badge (jumlah Not Set) ===== */
        .tab-alert-badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .15rem .55rem;
            border-radius: 9999px;
            font-size: .7rem;
            font-weight: 600;
            line-height: 1;
            border: 1px solid transparent;
        }

        /* Ada Not Set → merah */
        .tab-alert-badge--danger {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        /* Tidak ada Not Set → abu-abu kalem */
        .tab-alert-badge--muted {
            background: #f4f4f5;
            color: #52525b;
            border-color: #e4e4e7;
        }

        .tab-alert-badge i {
            font-size: .7rem;
        }
    </style>
@endpush

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: 'Sukses!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                {{-- ====== TABS ====== --}}
                @if (!empty($tabs))
                    <ul class="nav nav-tabs mb-4">
                        @foreach ($tabs as $key => $t)
                            @if ($t['show'])
                                @php
                                    $eligibleForBadge =
                                        !in_array($key, ['company', 'direksi'], true) &&
                                        !($isGM && $key === 'division');
                                    $count = (int) ($t['not_set_count'] ?? 0);
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link {{ ($activeTab ?? '') === $key ? 'active' : '' }}"
                                        href="{{ route('rtc.list', ['level' => $key]) }}"
                                        data-tab-key="{{ $key }}">
                                        {{ $t['label'] }}
                                        @if ($eligibleForBadge)
                                            <span
                                                class="tab-alert-badge {{ $count > 0 ? 'tab-alert-badge--danger' : 'tab-alert-badge--muted' }} ms-2"
                                                id="tabBadge-{{ $key }}">
                                                @if ($count > 0)
                                                    <i class="fas fa-circle-exclamation"></i>
                                                @else
                                                    <i class="far fa-circle"></i>
                                                @endif
                                                <span>{{ $count }}</span>
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ $cardTitle ?? 'List' }}</h3>
                        <div class="d-flex align-items-center">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Search ..."
                                style="width:200px;">
                            <button type="button" class="btn btn-primary" id="searchButton">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- COMPANY selector (HRD/Top2) untuk tab selain "company" --}}
                        @if (!empty($isCompanyScope) && ($tableFilter ?? '') !== 'company')
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <select id="companySelect" class="form-select">
                                        <option value="">-- Select Company --</option>
                                        @foreach ($companies ?? [] as $c)
                                            <option value="{{ $c['code'] }}">{{ $c['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        {{-- PLANT selector (SELALU muncul saat tab Division). --}}
                        @if (($tableFilter ?? '') === 'division' && !($isGM ?? false))
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Direksi</label>
                                    <select id="plantSelect" class="form-select"
                                        {{ !empty($isCompanyScope) ? 'disabled' : '' }}>
                                        @if (empty($isCompanyScope))
                                            @foreach ($plants ?? collect() as $p)
                                                <option value="{{ $p['id'] ?? $p->id }}"
                                                    {{ (string) ($divisionId ?? '') === (string) ($p['id'] ?? $p->id) ? 'selected' : '' }}>
                                                    {{ $p['name'] ?? $p->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if (!empty($isCompanyScope))
                                        <small class="text-muted">Pilih company terlebih dahulu untuk menampilkan
                                            direksi.</small>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- DIVISION selector (untuk Dept/Section/Sub Section) --}}
                        @if (in_array($tableFilter ?? '', ['department', 'section', 'sub_section'], true))
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Division</label>
                                    <select id="divisionSelect" class="form-select">
                                        <option value="">-- Select Division --</option>
                                        @foreach ($divisions ?? collect() as $d)
                                            <option value="{{ $d['id'] ?? $d->id }}"
                                                {{ (string) ($divisionId ?? '') === (string) ($d['id'] ?? $d->id) ? 'selected' : '' }}>
                                                {{ $d['name'] ?? $d->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Pilih division untuk menampilkan data
                                        {{ str_replace('_', ' ', $tableFilter ?? '') }}.</small>
                                </div>
                            </div>
                        @endif

                        @php
                            $showKpiCols = empty($hideKpiCols);
                            $colspan = 4 + ($showKpiCols ? 5 : 0);
                        @endphp

                        {{-- TABLE --}}
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th class="text-start">Name</th>
                                    @if ($showKpiCols)
                                        <th class="text-start">Current</th>
                                        <th class="text-start">ST</th>
                                        <th class="text-start">MT</th>
                                        <th class="text-start">LT</th>
                                        <th class="text-center">Status</th>
                                    @endif
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal Add/Edit RTC --}}
    @unless ($readOnly)
        <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="addPlanForm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPlanLabel">Add RTC</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            {{-- Ringkasan jumlah kandidat yang termuat --}}
                            <div id="rtc_counts" class="d-none small mb-3">
                                <span class="me-3">ST: <span id="cnt_st" class="fw-semibold">0</span></span>
                                <span class="me-3">MT: <span id="cnt_mt" class="fw-semibold">0</span></span>
                                <span>LT: <span id="cnt_lt" class="fw-semibold">0</span></span>
                            </div>

                            {{-- Tiga slot kandidat --}}
                            <div class="mb-3">
                                <label for="short_term" class="form-label">Short Term</label>
                                <select id="short_term" name="short_term" class="form-select rtc-s2"
                                    data-placeholder="Kandidat untuk ST (≤ 1 tahun)">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="mid_term" class="form-label">Mid Term</label>
                                <select id="mid_term" name="mid_term" class="form-select rtc-s2"
                                    data-placeholder="Kandidat untuk MT (2–3 tahun)">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="long_term" class="form-label">Long Term</label>
                                <select id="long_term" name="long_term" class="form-select rtc-s2"
                                    data-placeholder="Kandidat untuk LT (≥ 3 tahun)">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            {{-- Save digunakan untuk Add & Update (edit) --}}
                            <button type="button" class="btn btn-primary" id="btnSave">Save</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endunless
@endsection

@push('scripts')
    <script>
        // ===== Boot data (tetap dari server) =====
        window.ACTIVE_FILTER = @json($tableFilter ?? 'division');
        window.CONTAINER_ID = @json($divisionId ?? null);
        window.READ_ONLY = @json((bool) ($readOnly ?? false));
        window.ROUTE_FILTER = @json(route('filter.master'));
        window.ROUTE_SUMMARY = @json(route('rtc.summary'));
        window.IS_COMPANY_SCOPE = @json((bool) ($isCompanyScope ?? false));
        window.DIREKSI_BY_COMPANY = @json($plantsByCompany ?? []);
        window.IS_GM = @json((bool) ($isGM ?? false));
        window.IS_DIREKTUR = @json((bool) ($isDirektur ?? false));
        window.SHOW_KPI_COLS = @json(empty($hideKpiCols));
        window.HIDE_ADD = @json((bool) ($forceHideAdd ?? false));
        window.COLSPAN = @json(4 + (empty($hideKpiCols) ? 5 : 0));
        window.EMPLOYEES = @json($employees ?? []);
        window.VISIBLE_TABS = @json(collect($tabs)->filter(fn($t) => $t['show'])->keys()->values());

        window.ROUTE_RTC_CANDIDATES = @json(route('rtc.candidates'));
        window.ROUTE_RTC_SAVE = @json(route('rtc.save'));
        window.ROUTE_RTC_UPDATE = @json(route('rtc.update'));
        window.ROUTE_RTC_SUBMIT = @json(route('rtc.submit'));
    </script>

    <script>
        // CSRF untuk semua POST
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
    </script>

    <script>
        $(function() {
            if (window.__RTC_INIT__) return;
            window.__RTC_INIT__ = true;

            window.CURRENT_AREA_ID = null;
            window.EDIT_MODE = false; // false: Add, true: Edit
            window.__RTC_SAVING__ = false; // in-flight request guard

            // ===== Helpers =====
            function esc(s) {
                return $('<div>').text(s ?? '').html();
            }

            // ===== Helper: update badge di tab aktif berdasarkan items yang baru di-load =====
            function updateTabBadge(tabKey, items) {
                const $badge = $('#tabBadge-' + tabKey);
                if (!$badge.length) return; // tab ini memang tidak punya badge

                let notSetCount = 0;

                (items || []).forEach(function(row) {
                    const anyFilled =
                        (row.short && row.short.name) ||
                        (row.mid && row.mid.name) ||
                        (row.long && row.long.name);

                    if (!anyFilled) {
                        notSetCount++;
                    }
                });

                // Update angka
                $badge.find('span').last().text(notSetCount);

                // Update warna + icon
                $badge
                    .removeClass('tab-alert-badge--danger tab-alert-badge--muted')
                    .addClass(notSetCount > 0 ? 'tab-alert-badge--danger' : 'tab-alert-badge--muted');

                const $icon = $badge.find('i').first();
                if (notSetCount > 0) {
                    $icon.attr('class', 'fas fa-circle-exclamation');
                } else {
                    $icon.attr('class', 'far fa-circle');
                }
            }

            // ===== 3-status chip (0=draft,1=submitted,2=approved) + ONGOING =====
            function statusChip(overall) {
                let statusNum = null,
                    label = '';
                const codeToNum = {
                    draft: 0,
                    submitted: 1,
                    approved: 2,
                    waiting: 1,
                    ongoing: 0, // new
                    continue: 0 // alias if backend uses 'continue'
                };

                if (typeof overall === 'number') {
                    statusNum = overall;
                } else if (overall && typeof overall === 'object') {
                    if (typeof overall.status === 'number') statusNum = overall.status;
                    else if (overall.code && (overall.code in codeToNum)) statusNum = codeToNum[overall.code];
                    if (overall.label) label = overall.label;
                } else if (typeof overall === 'string' && overall in codeToNum) {
                    statusNum = codeToNum[overall];
                }

                if (statusNum === null || isNaN(statusNum)) {
                    const lab = label || (overall && overall.label) || 'Not Set';
                    return `<span class="status-chip" data-status="not_created" title="${esc(lab)}"><i class="far fa-circle"></i><span>${esc(lab)}</span></span>`;
                }

                const map = {
                    0: {
                        ds: 'draft',
                        icon: '<i class="far fa-pen-to-square"></i>',
                        label: 'Draft'
                    },
                    1: {
                        ds: 'waiting',
                        icon: '<i class="fas fa-paper-plane"></i>',
                        label: 'Submitted'
                    },
                    2: {
                        ds: 'approved',
                        icon: '<i class="fas fa-circle-check"></i>',
                        label: 'Approved'
                    },
                };

                const conf = map[statusNum] ?? {
                    ds: 'not_created',
                    icon: '<i class="far fa-circle"></i>',
                    label: 'Not Set'
                };

                // Default label dari map / server
                let finalLabel = label || conf.label;
                // NEW: jika code 'ongoing' / 'continue', pakai ds & label 'ongoing'
                let ds = conf.ds;
                if (overall && typeof overall === 'object' && (overall.code === 'ongoing' || overall.code ===
                        'continue')) {
                    ds = 'ongoing';
                    finalLabel = 'Ongoing';
                }

                return `<span class="status-chip" data-status="${ds}" title="${esc(finalLabel)}">${conf.icon}<span>${esc(finalLabel)}</span></span>`;
            }

            function renderEmpty(msg) {
                $('#kt_table_users tbody').html(
                    `<tr><td colspan="${window.COLSPAN}" class="text-center text-muted">${esc(msg||'No data')}</td></tr>`
                );
            }

            // ===== Tabel rows renderer =====
            function renderRows(items, filter) {
                if (!items || !items.length) return renderEmpty('No data');

                const codeToNum = {
                    draft: 0,
                    submitted: 1,
                    approved: 2,
                    waiting: 1
                };

                const rows = items.map((row, i) => {
                    const st = row.short?.name ? esc(row.short.name) :
                        '<span class="text-not-set">-</span>';
                    const mt = row.mid?.name ? esc(row.mid.name) : '<span class="text-not-set">-</span>';
                    const lt = row.long?.name ? esc(row.long.name) : '<span class="text-not-set">-</span>';

                    const anyFilled = !!(row.short?.name || row.mid?.name || row.long?.name);
                    const overallNotSet = !(row.overall && (row.overall.status !== null && row.overall
                            .status !== undefined)) &&
                        !(row.overall && row.overall.code && row.overall.code !== 'not_set');

                    const overallForChip = (anyFilled && overallNotSet) ? {
                            status: 0,
                            label: 'Draft',
                            code: 'draft'
                        } :
                        row.overall;

                    let overallNum = null;
                    if (overallForChip && typeof overallForChip === 'object') {
                        if (typeof overallForChip.status === 'number') overallNum = overallForChip.status;
                        else if (overallForChip.code && (overallForChip.code in codeToNum)) overallNum =
                            codeToNum[overallForChip.code];
                    } else if (typeof overallForChip === 'string' && (overallForChip in codeToNum)) {
                        overallNum = codeToNum[overallForChip];
                    }

                    const statusHtml = statusChip(overallForChip);

                    const fullName = row.pic?.name || '-';
                    const showName = (fullName || '').trim().split(/\s+/).slice(0, 2).join(' ');
                    const pic = row.pic ? `<span title="${esc(fullName)}">${esc(showName)}</span>` :
                        `<span>-</span>`;

                    const previewBtn =
                        `<a href="${window.ROUTE_SUMMARY}?id=${row.id}&filter=${filter}" class="btn btn-sm btn-info" target="_blank" title="Preview">Preview</a>`;

                    let actions = '';
                    if (anyFilled) {
                        let showUpdate = true,
                            showSubmit = true; // default: saat draft
                        if (overallNum === 1 || overallNum === 2) {
                            showUpdate = false;
                            showSubmit = false;
                        }

                        const updateBtn = `<a href="#" class="btn btn-sm btn-warning btn-edit"
                              data-id="${row.id}"
                              data-filter="${filter}"
                              data-mode="edit"
                              data-bs-toggle="modal" data-bs-target="#addPlanModal">Update</a>`;

                        const submitBtn = `<a href="#" class="btn btn-sm btn-primary btn-submit"
                              data-id="${row.id}"
                              data-filter="${filter}">Submit</a>`;

                        actions = `<div class="action-stack">
                          ${previewBtn}
                          ${showUpdate ? updateBtn : ''}
                          ${showSubmit ? submitBtn : ''}
                        </div>`;
                    } else {
                        const addBtn = (!window.READ_ONLY && !window.HIDE_ADD) ? `
                          <a href="#" class="btn btn-sm btn-success btn-show-modal"
                             data-id="${row.id}"
                             data-filter="${filter}"
                             data-mode="add"
                             data-bs-toggle="modal" data-bs-target="#addPlanModal">Add</a>` : '';
                        actions = `<div class="action-stack">${previewBtn}${addBtn}</div>`;
                    }

                    let kpiCells = '';
                    if (window.SHOW_KPI_COLS) {
                        kpiCells = `
                          <td class="text-start">${pic}</td>
                          <td class="text-start term-cell">${st}</td>
                          <td class="text-start term-cell">${mt}</td>
                          <td class="text-start term-cell">${lt}</td>
                          <td class="text-center">${statusHtml}</td>
                        `;
                    }

                    return `<tr class="fs-7">
                        <td>${i+1}</td>
                        <td class="text-start">${esc(row.name)}</td>
                        ${kpiCells}
                        <td class="text-center">${actions}</td>
                    </tr>`;
                }).join('');

                $('#kt_table_users tbody').html(rows);
            }

            // ===== Fetch list items =====
            function fetchItems(filter, containerId) {
                const needsId = ['department', 'section', 'sub_section'].includes(filter);

                if (needsId && !containerId) {
                    renderEmpty('Select division first');
                    return Promise.resolve([]);
                }
                if (filter === 'division' && window.IS_COMPANY_SCOPE && !containerId) {
                    renderEmpty('Select company & direksi first');
                    return Promise.resolve([]);
                }
                if (filter === 'division' && !window.IS_GM && !window.IS_COMPANY_SCOPE && !containerId) {
                    renderEmpty('Select direksi first');
                    return Promise.resolve([]);
                }

                const params = {
                    filter,
                    division_id: containerId ?? null
                };
                if (filter === 'direksi' && window.IS_COMPANY_SCOPE) {
                    const comp = ($('#companySelect').val() || '').toUpperCase();
                    if (!comp) {
                        renderEmpty('Select company first');
                        return Promise.resolve([]);
                    }
                    params.company = comp;
                }

                return $.getJSON(window.ROUTE_FILTER, params)
                    .then(res => {
                        const items = res.items || [];
                        renderRows(items, filter);
                        // === update badge untuk tab ini ===
                        updateTabBadge(filter, items);
                        return items;
                    })
                    .catch(() => {
                        renderEmpty('Failed to load data');
                        return [];
                    });
            }
            window.fetchItems = fetchItems;

            // ===== Direksi/Division helpers =====
            function loadDivisionsForCompany(companyCode) {
                const code = (companyCode || '').toUpperCase();
                const plants = (window.DIREKSI_BY_COMPANY && window.DIREKSI_BY_COMPANY[code]) ? window
                    .DIREKSI_BY_COMPANY[code] : [];
                const reqs = plants.map(p => $.getJSON(window.ROUTE_FILTER, {
                        filter: 'division',
                        division_id: p.id
                    })
                    .then(r => r.items || []).catch(() => []));
                return Promise.all(reqs).then(arr => {
                    const flat = arr.flat();
                    const uniq = Object.values(flat.reduce((acc, it) => {
                        acc[it.id] = it;
                        return acc;
                    }, {}));
                    const $div = $('#divisionSelect');
                    $div.empty().append('<option value="">-- Select Division --</option>');
                    uniq.forEach(it => $div.append(
                        `<option value="${it.id}">${$('<div>').text(it.name).html()}</option>`));
                    return uniq;
                });
            }

            function populatePlants(companyCode) {
                const $plant = $('#plantSelect');
                $plant.prop('disabled', !companyCode);
                $plant.empty().append('<option value="">-- Select Direksi --</option>');
                const list = window.DIREKSI_BY_COMPANY[(companyCode || '').toUpperCase()] || [];
                list.forEach(p => $plant.append(
                    `<option value="${p.id}">${$('<div>').text(p.name).html()}</option>`));
                renderEmpty('Select direksi first');
            }

            // ===== Initial load =====
            (function init() {
                if (window.IS_COMPANY_SCOPE) {
                    if (window.ACTIVE_FILTER === 'company') fetchItems('company', null);
                    else if (window.ACTIVE_FILTER === 'plant') renderEmpty('Select company first');
                    else if (window.ACTIVE_FILTER === 'division') renderEmpty('Select company & direksi first');
                    else renderEmpty('Select company first');
                } else {
                    if (window.ACTIVE_FILTER === 'plant') fetchItems('plant', null);
                    else if (window.ACTIVE_FILTER === 'division') {
                        if (window.IS_GM) fetchItems('division', null);
                        else if (window.CONTAINER_ID) fetchItems('division', window.CONTAINER_ID);
                        else renderEmpty('Select direksi first');
                    } else {
                        if (window.CONTAINER_ID) fetchItems(window.ACTIVE_FILTER, window.CONTAINER_ID);
                        else renderEmpty('Select division first');
                    }
                }
            })();

            // ===== Filters (company/plant/division) =====
            $(document).off('change', '#companySelect').on('change', '#companySelect', function() {
                const comp = $(this).val() || '';
                if (window.ACTIVE_FILTER === 'division') {
                    populatePlants(comp);
                } else if (window.ACTIVE_FILTER === 'direksi') {
                    if (!comp) {
                        renderEmpty('Select company first');
                        return;
                    }
                    fetchItems('direksi', null);
                } else {
                    $('#divisionSelect').empty().append('<option value="">-- Select Division --</option>');
                    if (!comp) {
                        renderEmpty('Select company first');
                        return;
                    }
                    loadDivisionsForCompany(comp).then(() => renderEmpty('Select division first'));
                }
            });

            $(document).off('change', '#plantSelect').on('change', '#plantSelect', function() {
                const pid = $(this).val() || '';
                window.CONTAINER_ID = pid || null;
                if (window.ACTIVE_FILTER === 'division') {
                    if (pid) fetchItems('division', pid);
                    else renderEmpty('Select plant first');
                }
            });

            $(document).off('change', '#divisionSelect').on('change', '#divisionSelect', function() {
                const did = $(this).val() || '';
                window.CONTAINER_ID = did || null;
                if (did) fetchItems(window.ACTIVE_FILTER, did);
                else renderEmpty('Select division first');
            });

            $(document).off('click', '#searchButton').on('click', '#searchButton', function() {
                const q = ($('#searchInput').val() || '').toLowerCase();
                $('#kt_table_users tbody tr').each(function() {
                    $(this).toggle($(this).text().toLowerCase().includes(q));
                });
            });

            // ====== Modal RTC: Select2 & Candidates ======
            function initSelect2InModal() {
                $('#addPlanModal .rtc-s2').each(function() {
                    const $el = $(this);
                    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                    $el.select2({
                        dropdownParent: $('#addPlanModal'),
                        width: '100%',
                        placeholder: $el.data('placeholder') || '-- Select --',
                        allowClear: true
                    });
                });
            }

            function resetRtcModal() {
                ['#short_term', '#mid_term', '#long_term'].forEach(sel => {
                    $(sel).empty().append('<option value="">-- Select --</option>').trigger('change');
                });
                $('#cnt_st').text(0);
                $('#cnt_mt').text(0);
                $('#cnt_lt').text(0);
                $('#rtc_counts').addClass('d-none');
            }

            function loadCandidatesForActiveTab() {
                return new Promise((resolve, reject) => {
                    const TAB_TO_KODES = {
                        direksi: ['GM', 'SGM', 'AGM'],
                        division: ['SM', 'M'],
                        department: ['AM', 'SS'],
                        section: ['S', 'AS'],
                        sub_section: []
                    };

                    const filter = (window.ACTIVE_FILTER || '').toLowerCase();
                    const kodes = TAB_TO_KODES[filter] || [];

                    resetRtcModal();
                    if (!kodes.length) {
                        resolve();
                        return;
                    }

                    const paramsBase = {};
                    const comp = ($('#companySelect').val() || '').toUpperCase();
                    if (comp) paramsBase.company = comp;

                    ['#short_term', '#mid_term', '#long_term'].forEach(sel => {
                        $(sel).html('<option value="">Loading...</option>').trigger('change');
                    });

                    const reqs = kodes.map(k =>
                        $.getJSON(window.ROUTE_RTC_CANDIDATES, {
                            ...paramsBase,
                            kode: k
                        })
                        .then(r => (r && r.data) ? r.data : []).catch(() => [])
                    );

                    Promise.all(reqs).then(chunks => {
                        const data = chunks.flat();

                        // Unik per employee saja
                        const uniq = {};
                        data.forEach(r => {
                            if (!r || !r.employee_id) return;

                            const key = r.employee_id;
                            const existing = uniq[key];

                            if (!existing) {
                                // belum ada: pakai yang ini dulu
                                uniq[key] = r;
                                return;
                            }

                            // sudah ada: pilih yang plan_year lebih besar
                            const currentYear = parseInt(existing.plan_year, 10) || 0;
                            const newYear = parseInt(r.plan_year, 10) || 0;

                            if (newYear > currentYear) {
                                uniq[key] = r;
                            }
                        });

                        const rows = Object.values(uniq);

                        const st = [],
                            mt = [],
                            lt = [];
                        rows.forEach(r => {
                            const opt = `
                                <option value="${r.employee_id}">
                                ${$('<div>').text(r.name).html()}
                                • ${$('<div>').text(r.job_function || '-').html()}
                                • ${$('<div>').text(r.level || '-').html()}
                                • ${$('<div>').text(r.plan_year || '-').html()}
                                </option>`;

                            if (r.term === 'short') st.push(opt);
                            else if (r.term === 'mid') mt.push(opt);
                            else lt.push(opt);
                        });

                        $('#short_term').html('<option value="">-- Select --</option>' + st.join(
                            '')).trigger('change');
                        $('#mid_term').html('<option value="">-- Select --</option>' + mt.join(''))
                            .trigger('change');
                        $('#long_term').html('<option value="">-- Select --</option>' + lt.join(''))
                            .trigger('change');

                        $('#cnt_st').text(st.length);
                        $('#cnt_mt').text(mt.length);
                        $('#cnt_lt').text(lt.length);
                        $('#rtc_counts').toggleClass('d-none', rows.length === 0);

                        resolve();
                    }).catch(err => {
                        resetRtcModal();
                        reject(err);
                    });
                });
            }

            // ===== Open modal: Add =====
            $(document).off('click', '.btn-show-modal').on('click', '.btn-show-modal', function(e) {
                e.preventDefault();
                window.CURRENT_AREA_ID = $(this).data('id') || null;
                window.EDIT_MODE = false;
                $('#addPlanLabel').text('Add RTC');
                $('#btnSave').text('Save');
                $('#addPlanModal').one('shown.bs.modal', initSelect2InModal);
                loadCandidatesForActiveTab().catch(() => {});
            });

            // ===== Open modal: Edit =====
            $(document).off('click', '.btn-edit').on('click', '.btn-edit', function(e) {
                e.preventDefault();
                window.CURRENT_AREA_ID = $(this).data('id') || null;
                window.EDIT_MODE = true;
                $('#addPlanLabel').text('Edit RTC');
                $('#btnSave').text('Save');

                const area = (window.ACTIVE_FILTER || '').toLowerCase();
                const area_id = window.CURRENT_AREA_ID;

                $('#addPlanModal').one('shown.bs.modal', initSelect2InModal);

                loadCandidatesForActiveTab()
                    .then(() => $.ajax({
                        url: "{{ route('rtc.area.items') }}",
                        method: 'GET',
                        data: {
                            area,
                            area_id
                        }
                    }))
                    .then((res) => {
                        const byTerm = {};
                        (res || []).forEach(r => {
                            byTerm[(r.term || '').toLowerCase()] = r.employee_id || '';
                        });
                        if (byTerm.short) $('#short_term').val(String(byTerm.short)).trigger('change');
                        if (byTerm.mid) $('#mid_term').val(String(byTerm.mid)).trigger('change');
                        if (byTerm.long) $('#long_term').val(String(byTerm.long)).trigger('change');
                    })
                    .catch(() => {});

                $('#addPlanModal').modal('show');
            });

            // ===== Submit (ajukan ke approval) =====
            $(document).off('click', '.btn-submit').on('click', '.btn-submit', function(e) {
                e.preventDefault();
                const areaId = $(this).data('id') || null;
                if (!areaId) return Swal.fire('Oops', 'Area tidak valid.', 'warning');

                const payload = {
                    filter: (window.ACTIVE_FILTER || '').toLowerCase(),
                    id: areaId
                };

                Swal.fire({
                    title: 'Submit RTC?',
                    text: 'Setelah submit, RTC akan diajukan untuk approval.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (!res.isConfirmed) return;

                    $.ajax({
                        url: window.ROUTE_RTC_SUBMIT,
                        type: 'POST',
                        data: payload
                    }).done(function() {
                        Swal.fire('Berhasil', 'RTC berhasil di-submit.', 'success');
                        const cid = window.CONTAINER_ID || null;
                        if (typeof fetchItems === 'function') fetchItems(window
                            .ACTIVE_FILTER, cid);
                        else location.reload();
                    }).fail(function(xhr) {
                        const msg = xhr?.responseJSON?.message || xhr?.statusText ||
                            'Gagal submit RTC';
                        Swal.fire('Gagal', msg, 'error');
                    });
                });
            });

            // ===== Modal lifecycle =====
            $(document).off('hidden.bs.modal', '#addPlanModal').on('hidden.bs.modal', '#addPlanModal', function() {
                $(this).find('.rtc-s2').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) $(this).select2('destroy');
                });
                window.CURRENT_AREA_ID = null;
                window.EDIT_MODE = false;
                resetRtcModal();
            });

            // ===== SAVE (Add & Update) — Satu-satunya handler submit =====
            $(document).off('click', '#btnSave').on('click', '#btnSave', function() {
                $('#addPlanForm').trigger('submit');
            });

            $(document).off('submit', '#addPlanForm').on('submit', '#addPlanForm', function(e) {
                e.preventDefault();

                if (window.__RTC_SAVING__) return;
                window.__RTC_SAVING__ = true;

                const $btn = $('#btnSave').prop('disabled', true);

                const areaId = window.CURRENT_AREA_ID || null;
                if (!areaId) {
                    window.__RTC_SAVING__ = false;
                    $btn.prop('disabled', false);
                    return Swal.fire('Oops', 'Area tidak valid.', 'warning');
                }

                const payload = {
                    filter: (window.ACTIVE_FILTER || '').toLowerCase(),
                    id: areaId,
                    short_term: $('#short_term').val() || '',
                    mid_term: $('#mid_term').val() || '',
                    long_term: $('#long_term').val() || ''
                };

                const url = window.EDIT_MODE ? window.ROUTE_RTC_UPDATE : window.ROUTE_RTC_SAVE;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: payload
                }).done(function(res) {
                    $('#addPlanModal').modal('hide');
                    const fallback = window.EDIT_MODE ? 'Perubahan RTC disimpan (draft).' :
                        'RTC berhasil disimpan (draft).';
                    Swal.fire('Berhasil', (res && res.message) ? res.message : fallback, 'success');

                    const cid = window.CONTAINER_ID || null;
                    if (typeof fetchItems === 'function') fetchItems(window.ACTIVE_FILTER, cid);
                    else location.reload();
                }).fail(function(xhr) {
                    const msg = xhr?.responseJSON?.message || xhr?.statusText ||
                        'Gagal menyimpan RTC';
                    Swal.fire('Gagal', msg, 'error');
                }).always(function() {
                    window.__RTC_SAVING__ = false;
                    $btn.prop('disabled', false);
                });
            });

        });
    </script>
@endpush
