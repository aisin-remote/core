@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection
@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@push('custom-css')
    <style>
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
            color: #334155
        }

        .status-chip[data-status="approved"] {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0
        }

        .status-chip[data-status="checked"],
        .status-chip[data-status="waiting"] {
            background: #fffbeb;
            color: #92400e;
            border-color: #fde68a
        }

        .status-chip[data-status="draft"] {
            background: #f8fafc;
            color: #334155;
            border-color: #e2e8f0
        }

        .status-chip[data-status="not_created"] {
            background: #f4f4f5;
            color: #27272a;
            border-color: #e4e4e7
        }

        .term-cell {
            max-width: 180px;
            white-space: normal;
            line-height: 1.25;
            word-break: break-word
        }

        .text-not-set {
            color: #475569;
            opacity: .7
        }

        .action-stack {
            display: inline-flex;
            gap: .5rem
        }

        .select2-container {
            width: 100% !important;
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
                                <li class="nav-item">
                                    <a class="nav-link {{ ($activeTab ?? '') === $key ? 'active' : '' }}"
                                        href="{{ route('rtc.list', ['level' => $key]) }}">
                                        {{ $t['label'] }}
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
                            <button type="button" class="btn btn-primary" id="searchButton"><i class="fas fa-search"></i>
                                Search</button>
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

                        {{-- PLANT selector (SELALU muncul saat tab Division).
     - HRD/Top2: disabled dulu sampai pilih company → options diisi via JS
     - Direktur: server-side menaruh 1 plant yg dipimpin
     - GM: tidak butuh plant selector (divisi fixed) --}}
                        @if (($tableFilter ?? '') === 'division' && !($isGM ?? false))
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Plant</label>
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
                                            plant.</small>
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
                                    <th class="text-center fs-8">Last Modified</th>
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

    {{-- Modal Add Plan --}}
    @unless ($readOnly)
        <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="addPlanForm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Plan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="short_term" class="form-label">Short Term</label>
                                <select id="short_term" name="short_term" class="form-select rtc-s2"
                                    data-placeholder="Cari karyawan (ST) ...">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="mid_term" class="form-label">Mid Term</label>
                                <select id="mid_term" name="mid_term" class="form-select rtc-s2"
                                    data-placeholder="Cari karyawan (MT) ...">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="long_term" class="form-label">Long Term</label>
                                <select id="long_term" name="long_term" class="form-select rtc-s2"
                                    data-placeholder="Cari karyawan (LT) ...">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
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
        // Boot data
        window.ACTIVE_FILTER = @json($tableFilter ?? 'division');
        window.CONTAINER_ID = @json($divisionId ?? null);
        window.READ_ONLY = @json((bool) ($readOnly ?? false));
        window.ROUTE_FILTER = @json(route('filter.master'));
        window.ROUTE_SUMMARY = @json(route('rtc.summary'));
        window.IS_COMPANY_SCOPE = @json((bool) ($isCompanyScope ?? false));
        window.PLANTS_BY_COMPANY = @json($plantsByCompany ?? []);
        window.IS_GM = @json((bool) ($isGM ?? false));
        window.IS_DIREKTUR = @json((bool) ($isDirektur ?? false));
        window.SHOW_KPI_COLS = @json(empty($hideKpiCols));
        window.HIDE_ADD = @json((bool) ($forceHideAdd ?? false));
        window.COLSPAN = @json(4 + (empty($hideKpiCols) ? 5 : 0));
        window.EMPLOYEES = @json($employees ?? []);
    </script>
    <script>
        $(function() {
            function esc(s) {
                return $('<div>').text(s ?? '').html();
            }

            function statusChip(overall) {
                const map = {
                    approved: {
                        ds: 'approved',
                        icon: '<i class="fas fa-circle-check"></i>'
                    },
                    checked: {
                        ds: 'checked',
                        icon: '<i class="fas fa-clipboard-check"></i>'
                    },
                    submitted: {
                        ds: 'waiting',
                        icon: '<i class="fas fa-paper-plane"></i>'
                    },
                    partial: {
                        ds: 'draft',
                        icon: '<i class="far fa-pen-to-square"></i>'
                    },
                    complete_no_submit: {
                        ds: 'draft',
                        icon: '<i class="far fa-pen-to-square"></i>'
                    },
                    not_set: {
                        ds: 'not_created',
                        icon: '<i class="far fa-circle"></i>'
                    }
                };
                const conf = map[overall?.code] ?? map['not_set'];
                const label = overall?.label || 'Not Set';
                return `<span class="status-chip" data-status="${conf.ds}" title="${esc(label)}">${conf.icon}<span>${esc(label)}</span></span>`;
            }

            function renderEmpty(msg) {
                $('#kt_table_users tbody').html(
                    `<tr><td colspan="${window.COLSPAN}" class="text-center text-muted">${esc(msg||'No data')}</td></tr>`
                );
            }

            function renderRows(items, filter) {
                if (!items || !items.length) return renderEmpty('No data');
                const rows = items.map((row, i) => {
                    const st = row.short?.name ? esc(row.short.name) :
                        '<span class="text-not-set">-</span>';
                    const mt = row.mid?.name ? esc(row.mid.name) : '<span class="text-not-set">-</span>';
                    const lt = row.long?.name ? esc(row.long.name) : '<span class="text-not-set">-</span>';
                    const statusHtml = statusChip(row.overall);
                    const lastYear = row.last_year ? esc(row.last_year) : '-';

                    const previewBtn =
                        `<a href="${window.ROUTE_SUMMARY}?id=${row.id}&filter=${filter}" class="btn btn-sm btn-info" target="_blank" title="Preview">Preview</a>`;
                    let addBtn = '';
                    if (!window.READ_ONLY && !window.HIDE_ADD && row.can_add) {
                        addBtn =
                            `<a href="#" class="btn btn-sm btn-success btn-show-modal" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#addPlanModal">Add</a>`;
                    }

                    const fullName = row.pic?.name || '-';
                    const showName = (fullName || '').trim().split(/\s+/).slice(0, 2).join(' ');
                    const pic = row.pic ? `<span title="${esc(fullName)}">${esc(showName)}</span>` :
                        `<span>-</span>`;

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
                <td class="text-center">${lastYear}</td>
                <td class="text-center"><div class="action-stack">${previewBtn}${addBtn}</div></td>
            </tr>`;
                }).join('');
                $('#kt_table_users tbody').html(rows);
            }

            // allow extra params (e.g. company for plant list)
            function fetchItems(filter, containerId) {
                const needsId = ['department', 'section', 'sub_section'].includes(filter);

                // Validasi kebutuhan ID
                if (needsId && !containerId) {
                    renderEmpty('Select division first');
                    return Promise.resolve([]);
                }
                if (filter === 'division' && window.IS_COMPANY_SCOPE && !containerId) {
                    renderEmpty('Select company & plant first');
                    return Promise.resolve([]);
                }
                if (filter === 'division' && !window.IS_GM && !window.IS_COMPANY_SCOPE && !containerId) {
                    renderEmpty('Select plant first');
                    return Promise.resolve([]);
                }

                // === Tambahan: untuk tab Plant (HRD/Top2) kirim company
                const params = {
                    filter: filter,
                    division_id: containerId ?? null
                };
                if (filter === 'plant' && window.IS_COMPANY_SCOPE) {
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
                        return items;
                    })
                    .catch(() => {
                        renderEmpty('Failed to load data');
                        return [];
                    });
            }

            function loadDivisionsForCompany(companyCode) {
                const code = (companyCode || '').toUpperCase();
                const plants = window.PLANTS_BY_COMPANY[code] || [];
                const reqs = plants.map(p => $.getJSON(window.ROUTE_FILTER, {
                    filter: 'division',
                    division_id: p.id
                }).then(r => r.items || []).catch(() => []));
                return Promise.all(reqs).then(arr => {
                    const flat = arr.flat();
                    const uniq = Object.values(flat.reduce((acc, it) => {
                        acc[it.id] = it;
                        return acc;
                    }, {}));
                    const $div = $('#divisionSelect');
                    $div.empty().append('<option value="">-- Select Division --</option>');
                    uniq.forEach(it => $div.append(`<option value="${it.id}">${esc(it.name)}</option>`));
                    return uniq;
                });
            }

            // === Populate employee selects on Add
            function populateEmployeeSelects() {
                const all = window.EMPLOYEES || [];
                const comp = ($('#companySelect').val() || '').toUpperCase();
                const list = comp ? all.filter(e => (String(e.company_name || '').toUpperCase() === comp)) : all;
                const opts = ['<option value="">-- Select --</option>'].concat(list.map(e =>
                    `<option value="${e.id}">${esc(e.name)}</option>`));
                $('#short_term,#mid_term,#long_term').each(function() {
                    $(this).html(opts.join(''));
                });
            }
            $(document).on('click', '.btn-show-modal', populateEmployeeSelects);

            // Initial load
            (function init() {
                if (window.IS_COMPANY_SCOPE) {
                    if (window.ACTIVE_FILTER === 'company') {
                        // opsional: tampilkan daftar company (statis)
                        fetchItems('company', null);
                    } else if (window.ACTIVE_FILTER === 'plant') {
                        renderEmpty('Select company first');
                    } else if (window.ACTIVE_FILTER === 'division') {
                        renderEmpty('Select company & plant first');
                    } else {
                        renderEmpty('Select company first');
                    }
                } else {
                    if (window.ACTIVE_FILTER === 'plant') {
                        fetchItems('plant', null); // direktur
                    } else if (window.ACTIVE_FILTER === 'division') {
                        if (window.IS_GM) fetchItems('division', null);
                        else if (window.CONTAINER_ID) fetchItems('division', window.CONTAINER_ID);
                        else renderEmpty('Select plant first');
                    } else {
                        if (window.CONTAINER_ID) fetchItems(window.ACTIVE_FILTER, window.CONTAINER_ID);
                        else renderEmpty('Select division first');
                    }
                }
            })();

            // Events
            function populatePlants(companyCode) {
                const $plant = $('#plantSelect');
                $plant.prop('disabled', !companyCode);
                $plant.empty().append('<option value="">-- Select Plant --</option>');
                const list = window.PLANTS_BY_COMPANY[(companyCode || '').toUpperCase()] || [];
                list.forEach(p => $plant.append(`<option value="${p.id}">${esc(p.name)}</option>`));
                renderEmpty('Select plant first');
            }

            $('#companySelect').on('change', function() {
                const comp = $(this).val() || '';

                if (window.ACTIVE_FILTER === 'division') {
                    // Tab Division → tampilkan PLANT dari company tsb
                    populatePlants(comp);
                } else if (window.ACTIVE_FILTER === 'plant') {
                    // Tab Plant (HRD/Top2) → langsung fetch daftar plant by company
                    if (!comp) {
                        renderEmpty('Select company first');
                        return;
                    }
                    fetchItems('plant', null);
                } else {
                    // Tab Dept/Section/Sub → populate DIVISIONS berdasarkan semua plant di company tsb
                    $('#divisionSelect').empty().append('<option value="">-- Select Division --</option>');
                    if (!comp) {
                        renderEmpty('Select company first');
                        return;
                    }
                    loadDivisionsForCompany(comp).then(() => renderEmpty('Select division first'));
                }
            });

            $('#plantSelect').on('change', function() {
                const pid = $(this).val() || '';
                window.CONTAINER_ID = pid || null;
                if (window.ACTIVE_FILTER === 'division') {
                    if (pid) fetchItems('division', pid);
                    else renderEmpty('Select plant first');
                }
            });

            $('#divisionSelect').on('change', function() {
                const did = $(this).val() || '';
                window.CONTAINER_ID = did || null;
                if (did) fetchItems(window.ACTIVE_FILTER, did);
                else renderEmpty('Select division first');
            });

            $('#searchButton').on('click', function() {
                const q = ($('#searchInput').val() || '').toLowerCase();
                $('#kt_table_users tbody tr').each(function() {
                    $(this).toggle($(this).text().toLowerCase().includes(q));
                });
            });
        });
    </script>
    <script>
        $(function() {
            // helper untuk aktifkan select2 di dalam modal
            function initSelect2InModal() {
                $('#addPlanModal .rtc-s2').each(function() {
                    const $el = $(this);
                    // destroy dulu kalau sudah pernah di-init
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }
                    $el.select2({
                        dropdownParent: $('#addPlanModal'),
                        width: '100%',
                        placeholder: $el.data('placeholder') || '-- Select --',
                        allowClear: true
                    });
                });
            }

            // panggil saat isi dropdown karyawan selesai diganti
            function populateEmployeeSelects() {
                const all = window.EMPLOYEES || [];
                const comp = ($('#companySelect').val() || '').toUpperCase();
                const list = comp ? all.filter(e => (String(e.company_name || '').toUpperCase() === comp)) : all;

                const opts = ['<option value=""></option>'] // biar allowClear berfungsi
                    .concat(list.map(e => `<option value="${e.id}">${$('<div>').text(e.name).html()}</option>`));

                $('#short_term, #mid_term, #long_term').each(function() {
                    $(this).html(opts.join(''));
                });

                // aktifkan select2 setelah opsi terisi
                initSelect2InModal();
            }

            // buka modal via tombol "Add"
            $(document).on('click', '.btn-show-modal', function() {
                populateEmployeeSelects();
                $('#addPlanModal').one('shown.bs.modal', initSelect2InModal);
            });

            // bila company berubah (HRD/Top2) & modal terbuka, perbarui pilihan
            $('#companySelect').on('change', function() {
                if ($('#addPlanModal').is(':visible')) {
                    populateEmployeeSelects();
                }
            });

            // opsional: bersihkan select2 ketika modal ditutup
            $('#addPlanModal').on('hidden.bs.modal', function() {
                $(this).find('.rtc-s2').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                });
            });

            // expose ke fungsi lain yang memanggil populateEmployeeSelects sebelumnya
            window.__populateEmployeeSelects = populateEmployeeSelects;
        });
    </script>
    <script>
        $(function() {
            // simpan area yang diklik (id row pada tabel)
            window.CURRENT_AREA_ID = null;

            // saat klik tombol Add → buka modal + simpan id area
            $(document).on('click', '.btn-show-modal', function() {
                window.CURRENT_AREA_ID = $(this).data('id') || null;

                // (opsional) filter kandidat sesuai level
                // contoh sederhana: tidak dibatasi (sudah diisi via __populateEmployeeSelects)
                if (typeof window.__populateEmployeeSelects === 'function') {
                    window.__populateEmployeeSelects();
                }
            });

            // submit Add via GET ke route('rtc.update')
            $('#addPlanForm').on('submit', function(e) {
                e.preventDefault();

                if (!window.CURRENT_AREA_ID) {
                    Swal.fire('Oops', 'Area tidak valid.', 'warning');
                    return;
                }

                const payload = {
                    // filter: division | department | section | sub_section (atau plant jika ada)
                    filter: window.ACTIVE_FILTER,
                    id: window.CURRENT_AREA_ID,
                    short_term: $('#short_term').val() || '',
                    mid_term: $('#mid_term').val() || '',
                    long_term: $('#long_term').val() || '',
                };

                $.ajax({
                    url: @json(route('rtc.update')),
                    type: 'GET',
                    data: payload,
                }).done(function() {
                    $('#addPlanModal').modal('hide');
                    Swal.fire('Berhasil', 'RTC berhasil disimpan.', 'success');

                    // refresh tabel sesuai tab aktif
                    const cid = window.CONTAINER_ID || null;
                    if (typeof fetchItems === 'function') {
                        fetchItems(window.ACTIVE_FILTER, cid);
                    } else {
                        // fallback
                        location.reload();
                    }
                }).fail(function(xhr) {
                    const msg = xhr?.responseJSON?.message || xhr?.statusText ||
                        'Gagal menyimpan RTC';
                    Swal.fire('Gagal', msg, 'error');
                });
            });

            // bersihkan state ketika modal ditutup
            $('#addPlanModal').on('hidden.bs.modal', function() {
                window.CURRENT_AREA_ID = null;
                $(this).find('select').val('').trigger('change');
            });
        });
    </script>
@endpush
