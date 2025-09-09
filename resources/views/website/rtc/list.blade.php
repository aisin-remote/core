@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@push('custom-css')
    <style>
        /* ==================== Status Chip ==================== */
        .status-chip {
            --bg: #eef2ff;
            --fg: #312e81;
            --bd: #c7d2fe;
            --dot: #6366f1;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .9rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: .9rem;
            line-height: 1;
            border: 1px solid var(--bd);
            background: var(--bg);
            color: var(--fg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .status-chip i {
            font-size: .95rem !important;
            opacity: .95
        }

        .status-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dot);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--dot) 20%, transparent);
        }

        .status-chip[data-status="approved"] {
            --bg: #ecfdf5;
            --fg: #065f46;
            --bd: #a7f3d0;
            --dot: #10b981;
        }

        .status-chip[data-status="checked"],
        .status-chip[data-status="waiting"] {
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b;
        }

        .status-chip[data-status="draft"] {
            --bg: #f8fafc;
            --fg: #334155;
            --bd: #e2e8f0;
            --dot: #94a3b8;
        }

        .status-chip[data-status="revise"] {
            --bg: #fef2f2;
            --fg: #7f1d1d;
            --bd: #fecaca;
            --dot: #ef4444;
        }

        .status-chip[data-status="not_created"],
        .status-chip[data-status="unknown"] {
            --bg: #f4f4f5;
            --fg: #27272a;
            --bd: #e4e4e7;
            --dot: #a1a1aa;
        }

        @keyframes pulseDot {
            0% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 30%, transparent)
            }

            70% {
                box-shadow: 0 0 0 8px color-mix(in srgb, var(--dot) 0%, transparent)
            }

            100% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 0%, transparent)
            }
        }

        .status-chip[data-status="waiting"]::before {
            animation: pulseDot 1.25s infinite;
        }

        @media (max-width:768px) {
            .status-chip {
                max-width: 210px
            }
        }

        /* ==================== PIC Badge ==================== */
        .pic-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .18rem .55rem;
            border-radius: 9999px;
            font-size: .82rem;
            font-weight: 600;
            color: #475569;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            white-space: nowrap;
            max-width: 16rem;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pic-badge .role {
            font-size: .72rem;
            text-transform: uppercase;
            color: #0F172A;
            font-weight: 700;
        }

        .pic-badge .sep {
            opacity: .35;
        }

        .pic-badge.empty {
            color: #B91C1C;
            background: #FEF2F2;
            border-color: #FECACA;
        }

        /* ==================== Term cells ==================== */
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

        /* ==================== Sticky header ==================== */
        #kt_table_users thead th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 1;
        }

        .table> :not(caption)>*>* {
            padding: .75rem .5rem;
        }

        /* ==================== Responsif ==================== */
        @media (max-width: 992px) {

            #kt_table_users th:nth-child(6),
            #kt_table_users td:nth-child(6) {
                display: none;
            }

            /* Long Term */
            #kt_table_users th:nth-child(8),
            #kt_table_users td:nth-child(8) {
                display: none;
            }

            /* Last Year */
        }

        /* ==================== Fullscreen modal detail viewer ==================== */
        #viewDetailModal .modal-dialog {
            max-width: 100vw;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            max-height: 100vh;
        }

        #viewDetailModal .modal-content {
            height: 100vh;
            border-radius: 0;
            display: flex;
            flex-direction: column;
        }

        #viewDetailModal .modal-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 1rem 2rem;
        }

        /* ==================== Button action ==================== */
        .actions-cell {
            min-width: 140px;
        }

        .action-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: stretch;
            gap: .5rem;
        }

        .action-stack .btn {
            width: 100%;
        }

        @media (max-width:576px) {
            .action-stack {
                flex-direction: row;
                flex-wrap: wrap;
                gap: .5rem;
            }

            .actions-cell {
                min-width: 0;
            }
        }

        /* ==================== Tab indicator ==================== */
        /* === Tab indicator: merah & berkedap-kedip === */
        .tab-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.125rem;
            height: 1.125rem;
            padding: 0 .25rem;
            margin-left: .5rem;
            border-radius: 9999px;
            font-size: .7rem;
            font-weight: 700;
            color: #fff;
            /* teks putih biar kontras */
            background: #ef4444;
            /* merah solid */
            border: none;
            line-height: 1;
            /* animasi blink + pulse */
            animation: tabBlink 1.1s infinite ease-in-out,
                tabPulse 1.4s infinite ease-out;
            will-change: opacity, box-shadow;
        }

        /* Kedip (opacity turun-naik) */
        @keyframes tabBlink {

            0%,
            50% {
                opacity: 1;
            }

            70% {
                opacity: .55;
            }

            100% {
                opacity: 1;
            }
        }

        /* Denyut (glow memudar) */
        @keyframes tabPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, .55);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* Hormati preferensi reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .tab-indicator {
                animation: none;
            }
        }
    </style>
@endpush

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Sukses!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ $cardTitle ?? 'List' }}</h3>
                        <div class="d-flex align-items-center">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Search ..."
                                style="width:200px;">
                            <button type="button" class="btn btn-primary me-3" id="searchButton"><i
                                    class="fas fa-search"></i> Search</button>
                            <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                                data-kt-menu-placement="bottom-end">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        @php
                            $user = auth()->user();
                            $pos = optional($user->employee)->position;

                            // Division tab hanya untuk Direktur dengan role 'User'
                            $showDivTab = $user->role === 'User' && $pos === 'Direktur';

                            // Department tab untuk HRD, Direktur, GM/Act GM, President, VPD
                            $showDeptTab =
                                $user->role === 'HRD' ||
                                $pos === 'Direktur' ||
                                $pos === 'GM' ||
                                $pos === 'Act GM' ||
                                $pos === 'President' ||
                                $pos === 'VPD';

                            $readOnly = (bool) ($readOnly ?? false);
                        @endphp

                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8"
                            role="tablist" style="cursor:pointer">
                            @if ($showDivTab)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link text-active-primary pb-4 filter-tab"
                                        data-filter="division">Division</a>
                                </li>
                            @endif
                            @if ($showDeptTab)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link text-active-primary pb-4 filter-tab"
                                        data-filter="department">Department</a>
                                </li>
                            @endif
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="section">Section</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="sub_section">Sub
                                    Section</a>
                            </li>
                        </ul>

                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th class="text-start">Name</th>
                                    <th class="text-start">Current</th>
                                    <th class="text-start">ST</th>
                                    <th class="text-start">MT</th>
                                    <th class="text-start">LT</th>
                                    <th class="text-center">Status</th>
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

    {{-- Modal Add Plan: hanya render jika TIDAK read-only --}}
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
                            @foreach (['short_term' => 'Short Term', 'mid_term' => 'Mid Term', 'long_term' => 'Long Term'] as $key => $label)
                                <div class="mb-3">
                                    <label for="{{ $key }}" class="form-label">{{ $label }}</label>
                                    <select id="{{ $key }}" class="form-select" name="{{ $key }}">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                            @endforeach
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

    {{-- Fullscreen detail modal (opsional) --}}
    <div class="modal fade" id="viewDetailModal" tabindex="-1" aria-labelledby="viewDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDetailLabel">Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewDetailContent">
                    <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Preload dari controller (mode Plant → Division List) + flags --}}
    <script>
        window.PRELOADED_ITEMS = @json($items ?? []);
        window.IS_DIVISION_PRELOAD = Array.isArray(window.PRELOADED_ITEMS) && window.PRELOADED_ITEMS.length > 0;
        window.READ_ONLY = @json((bool) ($readOnly ?? false));

        // base urls untuk tombol action
        window.ROUTE_LIST_BASE = @json(route('rtc.list'));
        window.ROUTE_SUMMARY_BASE = @json(route('rtc.summary'));
    </script>

    <script>
        $(document).ready(function() {
            // Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

            function esc(s) {
                return $('<div>').text(s ?? '').html();
            }

            // status chip
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
                    },
                };
                const conf = map[overall?.code] ?? map['not_set'];
                const label = overall?.label || 'Not Set';
                return `<span class="status-chip" data-status="${conf.ds}" title="${esc(label)}">${conf.icon}<span>${esc(label)}</span></span>`;
            }

            /* ===== Not Created indicator helpers ===== */
            function countNotCreated(items) {
                return (items || []).reduce((acc, it) => acc + (it?.overall?.code === 'not_set' ? 1 : 0), 0);
            }

            function setTabIndicator(filter, count) {
                const $tab = $('.filter-tab[data-filter="' + filter + '"]');
                $tab.find('.tab-indicator').remove(); // clear old
                if (count > 0) {
                    const label = count > 99 ? '99+' : String(count);
                    $tab.append('<span class="tab-indicator" title="' + label + ' not created">' + label +
                        '</span>');
                }
            }

            function fetchItemsFor(filter) {
                return new Promise((resolve) => {
                    // preload division
                    if (window.IS_DIVISION_PRELOAD && filter === 'division') {
                        resolve(window.PRELOADED_ITEMS || []);
                        return;
                    }
                    $.getJSON('{{ route('filter.master') }}', {
                        filter: filter,
                        division_id: @json($divisionId ?? null)
                    }).done(function(res) {
                        resolve(res.items || []);
                    }).fail(function() {
                        resolve([]);
                    });
                });
            }

            async function updateAllTabIndicators() {
                const filters = [];
                $('.filter-tab').each(function() {
                    filters.push($(this).data('filter'));
                });
                for (const f of filters) {
                    const items = await fetchItemsFor(f);
                    setTabIndicator(f, countNotCreated(items));
                }
            }

            function limitWords(s, n = 2) {
                if (!s) return '';
                return s.trim().split(/\s+/).slice(0, n).join(' ');
            }

            function getQueryParam(name) {
                const urlParams = new URLSearchParams(window.location.search);
                return name ? urlParams.get(name) : null;
            }

            function getUrlId() {
                const idQS = getQueryParam('id'); // ?id=...
                if (idQS) return idQS;
                const parts = window.location.pathname.split('/').filter(Boolean); // /rtc/list/7
                const last = parts[parts.length - 1];
                return /^\d+$/.test(last) ? last : null;
            }

            function renderRows(items, currentFilter = 'division') {
                if (!items || !items.length) {
                    $('#kt_table_users tbody').html(
                        '<tr><td colspan="9" class="text-center text-muted">No data available</td></tr>');
                    return;
                }

                const rows = items.map((row, idx) => {
                    const st = row.short?.name ? esc(row.short.name) :
                        '<span class="text-not-set">not set</span>';
                    const mt = row.mid?.name ? esc(row.mid.name) :
                        '<span class="text-not-set">not set</span>';
                    const lt = row.long?.name ? esc(row.long.name) :
                        '<span class="text-not-set">not set</span>';
                    const statusHtml = statusChip(row.overall);
                    const lastYear = row.last_year ? esc(row.last_year) : '-';

                    const urlId = getUrlId();
                    const hasLevel = !!getQueryParam('level');
                    const shouldHideDetail = !!urlId && !hasLevel;

                    let detailBtn = '';
                    if (!shouldHideDetail) {
                        detailBtn = `<a href="${window.ROUTE_LIST_BASE}?id=${row.id}"
                                        class="btn btn-sm btn-primary"
                                        data-bs-toggle="tooltip" title="Open detail">Detail</a>`;
                    }

                    const summaryBtn = `<a href="${window.ROUTE_SUMMARY_BASE}?id=${row.id}&filter=${currentFilter}"
                                            class="btn btn-sm btn-info" target="_blank"
                                            data-bs-toggle="tooltip" title="Open structure">Preview</a>`;

                    // PIC badge
                    const fullName = row.pic?.name || '-';
                    const showName = limitWords(fullName, 2);
                    const pic = row.pic ?
                        `<span class="" title="${esc(fullName)}" data-bs-toggle="tooltip"><span class="name">${esc(showName)}</span></span>` :
                        `<span class="">not set</span>`;

                    // Add (hidden jika read-only)
                    let addBtn = '';
                    if (!window.READ_ONLY && row.can_add) {
                        addBtn = `<a href="#" class="btn btn-sm btn-success btn-show-modal"
                                    data-id="${row.id}" data-bs-toggle="modal"
                                    data-bs-target="#addPlanModal" title="Add plan">Add</a>`;
                    }

                    const actionsHtml =
                        `<div class="action-stack">${summaryBtn}${detailBtn}${addBtn}</div>`;

                    return `
                        <tr class="fs-7">
                            <td>${idx + 1}</td>
                            <td class="text-start">${esc(row.name)}</td>
                            <td class="text-start">${pic}</td>
                            <td class="text-start term-cell">${st}</td>
                            <td class="text-start term-cell">${mt}</td>
                            <td class="text-start term-cell">${lt}</td>
                            <td class="text-center">${statusHtml}</td>
                            <td class="text-center">${lastYear}</td>
                            <td class="text-center actions-cell">${actionsHtml}</td>
                        </tr>`;
                }).join('');

                $('#kt_table_users tbody').html(rows);
                $('[data-bs-toggle="tooltip"]').each(function() {
                    new bootstrap.Tooltip(this);
                });
            }

            function loadTable(filter) {
                if (window.IS_DIVISION_PRELOAD && filter === 'division') {
                    renderRows(window.PRELOADED_ITEMS, 'division');
                    setTabIndicator('division', countNotCreated(window.PRELOADED_ITEMS));
                    return;
                }
                $.getJSON('{{ route('filter.master') }}', {
                    filter: filter,
                    division_id: @json($divisionId ?? null)
                }).done(function(res) {
                    const items = (res.items || []).map(it => ({
                        ...it,
                        can_add: window.READ_ONLY ? false : !!it.can_add
                    }));
                    renderRows(items, filter);
                    setTabIndicator(filter, countNotCreated(items));
                }).fail(function(xhr) {
                    console.error(xhr.responseText || xhr.statusText);
                    $('#kt_table_users tbody').html(
                        '<tr><td colspan="9" class="text-center text-danger">Failed to load data</td></tr>'
                    );
                });
            }

            const titles = {
                division: 'Division List',
                department: 'Department List',
                section: 'Section List',
                sub_section: 'Sub Section List'
            };

            // Default tab
            let currentFilter = window.IS_DIVISION_PRELOAD ? 'division' : @json($defaultFilter ?? 'department');

            if (window.IS_DIVISION_PRELOAD) {
                $('.filter-tab').not('[data-filter="division"]').closest('li').hide();
            }

            // Init tab & title & load awal
            $('.filter-tab').removeClass('active');
            $('.filter-tab[data-filter="' + currentFilter + '"]').addClass('active');
            $('.card-title').text(titles[currentFilter] ?? 'List');

            updateAllTabIndicators();
            if (window.IS_DIVISION_PRELOAD && currentFilter === 'division') {
                renderRows(window.PRELOADED_ITEMS, 'division');
                setTabIndicator('division', countNotCreated(window.PRELOADED_ITEMS));
            } else {
                loadTable(currentFilter);
            }

            // Ganti tab
            $('.filter-tab').on('click', function() {
                const target = $(this).data('filter');
                if (window.IS_DIVISION_PRELOAD && target !== 'division') return;
                $('.filter-tab').removeClass('active');
                $(this).addClass('active');
                currentFilter = target;
                $('.card-title').text(titles[currentFilter] ?? 'List');

                if (window.IS_DIVISION_PRELOAD && currentFilter === 'division') {
                    renderRows(window.PRELOADED_ITEMS, 'division');
                    setTabIndicator('division', countNotCreated(window.PRELOADED_ITEMS));
                } else {
                    loadTable(currentFilter);
                }
            });

            // ===== READ-WRITE area (Add Plan) → aktif hanya jika !READ_ONLY =====
            @if (empty($readOnly) || !$readOnly)
                const employees = @json($employees);
                let currentId = null;
                let filteredForModal = [];

                function initSelect2ForModal() {
                    const $modal = $('#addPlanModal');
                    ['#short_term', '#mid_term', '#long_term'].forEach(sel => {
                        const $el = $modal.find(sel);
                        if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                        $el.select2({
                            dropdownParent: $modal,
                            width: '100%',
                            placeholder: '-- Select --',
                            allowClear: true
                        });
                    });
                }

                function populateModalOptions(list) {
                    const $modal = $('#addPlanModal');
                    ['#short_term', '#mid_term', '#long_term'].forEach(id => {
                        const $s = $modal.find(id);
                        $s.empty().append('<option value=""></option>');
                        list.forEach(e => $s.append(`<option value="${e.id}">${esc(e.name)}</option>`));
                    });
                    initSelect2ForModal();
                }

                $(document).on('click', '.btn-show-modal', function() {
                    currentId = $(this).data('id');
                    const positionMap = {
                        division: ['Act GM', 'GM'],
                        department: ['Supervisor', 'Section Head'],
                        section: ['Leader'],
                        sub_section: ['JP', 'Act JP', 'Act Leader']
                    };
                    const targetPosition = (positionMap[currentFilter] || []).map(p => p.toLowerCase());
                    filteredForModal = employees.filter(e => targetPosition.includes((e.position || '')
                        .toLowerCase()));
                });

                $('#addPlanModal').on('shown.bs.modal', function() {
                    populateModalOptions(filteredForModal || []);
                });
                $('#addPlanModal').on('hidden.bs.modal', function() {
                    $(this).find('select').each(function() {
                        try {
                            $(this).val(null).trigger('change');
                        } catch (e) {}
                    });
                });

                $('#addPlanForm').on('submit', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: '{{ route('rtc.update') }}',
                        type: 'GET', // idealnya POST/PUT
                        data: {
                            filter: currentFilter,
                            id: currentId,
                            short_term: $('#short_term').val(),
                            mid_term: $('#mid_term').val(),
                            long_term: $('#long_term').val(),
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).done(function() {
                        $('#addPlanModal').modal('hide');
                        if (window.IS_DIVISION_PRELOAD && currentFilter === 'division') {
                            location.reload();
                        } else {
                            loadTable(currentFilter);
                        }
                    });
                });
            @endif

            // Simple client-side search
            $('#searchButton').on('click', function() {
                const query = ($('#searchInput').val() || '').toLowerCase();
                $('#kt_table_users tbody tr').each(function() {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.includes(query));
                });
            });
        });
    </script>
@endpush
