@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@push('custom-css')
    <style>
        /* Status Chip */
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
            font-size: 1rem;
            opacity: .95
        }

        /* Dot/pulse di kiri */
        .status-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dot);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--dot) 20%, transparent);
        }

        /* Variasi warna per status */
        .status-chip[data-status="approved"] {
            /* hijau */
            --bg: #ecfdf5;
            --fg: #065f46;
            --bd: #a7f3d0;
            --dot: #10b981;
        }

        .status-chip[data-status="checked"],
        .status-chip[data-status="waiting"] {
            /* kuning */
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b;
        }

        .status-chip[data-status="draft"] {
            /* abu */
            --bg: #f8fafc;
            --fg: #334155;
            --bd: #e2e8f0;
            --dot: #94a3b8;
        }

        .status-chip[data-status="revise"] {
            /* merah */
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

        /* Animasi pulse utk Waiting */
        @keyframes pulseDot {
            0% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 30%, transparent);
            }

            70% {
                box-shadow: 0 0 0 8px color-mix(in srgb, var(--dot) 0%, transparent);
            }

            100% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 0%, transparent);
            }
        }

        .status-chip[data-status="waiting"]::before {
            animation: pulseDot 1.25s infinite;
        }

        @media (max-width: 768px) {
            .status-chip {
                max-width: 210px;
            }
        }

        /* Fullscreen modal detail viewer */
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
                            <input type="text" id="searchInput" class="form-control me-2"
                                placeholder="Search Employee..." style="width: 200px;">
                            <button type="button" class="btn btn-primary me-3" id="searchButton">
                                <i class="fas fa-search"></i> Search
                            </button>
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

                            // Department tab untuk HRD, Direktur, GM
                            $showDeptTab =
                                $user->role === 'HRD' || $pos === 'Direktur' || $pos === 'GM' || $pos === 'Act GM';
                        @endphp

                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
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
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Short Term</th>
                                    <th class="text-center">Mid Term</th>
                                    <th class="text-center">Long Term</th>
                                    <th class="text-center">Status</th>
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

    <!-- Modal Add Plan -->
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

    {{-- Fullscreen detail modal (opsional, tetap disediakan) --}}
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
    {{-- Inject preload data dari controller (mode plant → list Division) --}}
    <script>
        window.PRELOADED_ITEMS = @json($items ?? []);
        window.IS_DIVISION_PRELOAD = Array.isArray(window.PRELOADED_ITEMS) && window.PRELOADED_ITEMS.length > 0;
    </script>

    <script>
        $(document).ready(function() {
            function esc(s) {
                return $('<div>').text(s ?? '').html();
            }

            // Map status backend -> style chip
            function statusChip(overall) {
                const map = {
                    approved: {
                        ds: 'approved',
                        icon: '<i class="fas fa-circle-check"></i>'
                    }, // 3
                    checked: {
                        ds: 'checked',
                        icon: '<i class="fas fa-clipboard-check"></i>'
                    }, // 1
                    submitted: {
                        ds: 'waiting',
                        icon: '<i class="fas fa-paper-plane"></i>'
                    }, // 0
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
                return `<span class="status-chip" data-status="${conf.ds}" title="${esc(label)}">
                            ${conf.icon}<span>${esc(label)}</span>
                        </span>`;
            }

            function renderRows(items) {
                if (!items || !items.length) {
                    $('#kt_table_users tbody').html(
                        '<tr><td colspan="7" class="text-center text-muted">No data available</td></tr>');
                    return;
                }
                const rows = items.map((row, idx) => {
                    const st = row.short?.name ? esc(row.short.name) :
                        '<span class="text-danger">not set</span>';
                    const mt = row.mid?.name ? esc(row.mid.name) :
                        '<span class="text-danger">not set</span>';
                    const lt = row.long?.name ? esc(row.long.name) :
                        '<span class="text-danger">not set</span>';
                    const statusHtml = statusChip(row.overall);

                    const viewBtn = `<a href="#" class="btn btn-sm btn-primary btn-view" data-id="${row.id}" title="Detail">
                                        <i class="fas fa-info-circle"></i>
                                     </a>`;
                    const addBtn = row.can_add ? `
                        <a href="#" class="btn btn-sm btn-success btn-show-modal"
                           data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#addPlanModal" title="Add">
                            <i class="fas fa-plus-circle"></i>
                        </a>` : '';

                    return `
                        <tr>
                            <td>${idx + 1}</td>
                            <td class="text-center">${esc(row.name)}</td>
                            <td class="text-center">${st}</td>
                            <td class="text-center">${mt}</td>
                            <td class="text-center">${lt}</td>
                            <td class="text-center">${statusHtml}</td>
                            <td class="text-center">${viewBtn} ${addBtn}</td>
                        </tr>`;
                }).join('');
                $('#kt_table_users tbody').html(rows);
            }

            function loadTable(filter) {
                // Saat preload Division (mode plant) → jangan panggil AJAX
                if (window.IS_DIVISION_PRELOAD && filter === 'division') {
                    renderRows(window.PRELOADED_ITEMS);
                    return;
                }

                $.getJSON('{{ route('filter.master') }}', {
                    filter: filter,
                    division_id: @json($divisionId ?? null)
                }).done(function(res) {
                    renderRows(res.items || []);
                }).fail(function(xhr) {
                    console.error(xhr.responseText || xhr.statusText);
                    $('#kt_table_users tbody').html(
                        '<tr><td colspan="7" class="text-center text-danger">Failed to load data</td></tr>'
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
            let currentFilter = window.IS_DIVISION_PRELOAD ?
                'division' :
                @json($defaultFilter ?? 'department');

            // Jika preload → sembunyikan tab selain Division
            if (window.IS_DIVISION_PRELOAD) {
                $('.filter-tab').not('[data-filter="division"]').closest('li').hide();
            }

            // Set active tab + title + load awal
            $('.filter-tab').removeClass('active');
            $('.filter-tab[data-filter="' + currentFilter + '"]').addClass('active');
            $('.card-title').text(titles[currentFilter] ?? 'List');

            if (window.IS_DIVISION_PRELOAD && currentFilter === 'division') {
                renderRows(window.PRELOADED_ITEMS);
            } else {
                loadTable(currentFilter);
            }

            // Ganti tab
            $('.filter-tab').on('click', function() {
                const target = $(this).data('filter');

                // Blokir pindah tab saat preload Division
                if (window.IS_DIVISION_PRELOAD && target !== 'division') return;

                $('.filter-tab').removeClass('active');
                $(this).addClass('active');
                currentFilter = target;
                $('.card-title').text(titles[currentFilter] ?? 'List');

                if (window.IS_DIVISION_PRELOAD && currentFilter === 'division') {
                    renderRows(window.PRELOADED_ITEMS);
                } else {
                    loadTable(currentFilter);
                }
            });

            // Data employees dari server
            const employees = @json($employees);
            let currentId = null;

            // Buka modal Add Plan
            $(document).on('click', '.btn-show-modal', function() {
                currentId = $(this).data('id');

                const positionMap = {
                    division: ['Act GM', 'GM'],
                    department: ['Supervisor', 'Section Head'],
                    section: ['Leader'],
                    sub_section: ['JP', 'Act JP', 'Act Leader']
                };
                const targetPosition = (positionMap[currentFilter] || []).map(p => p.toLowerCase());
                const filtered = employees.filter(e => targetPosition.includes((e.position || '')
                    .toLowerCase()));

                ['#short_term', '#mid_term', '#long_term'].forEach(id => {
                    const $s = $(id).empty().append('<option value="">-- Select --</option>');
                    filtered.forEach(e => $s.append(
                        `<option value="${e.id}">${esc(e.name)}</option>`));
                });
            });

            // Tombol View → summary
            $(document).on('click', '.btn-view', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                window.location.href = `{{ route('rtc.summary') }}?id=${id}&filter=${currentFilter}`;
            });

            // Submit Add Plan
            $('#addPlanForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('rtc.update') }}',
                    type: 'GET', // (idealnya POST/PUT)
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
                        // reload halaman agar sinkron (bisa juga re-fetch manual jika mau)
                        location.reload();
                    } else {
                        loadTable(currentFilter);
                    }
                });
            });

            // Simple client-side search
            $('#searchButton').on('click', function() {
                const query = ($('#searchInput').val() || '').toLowerCase();
                const rows = $('#kt_table_users tbody tr');
                rows.each(function() {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.includes(query));
                });
            });
        });
    </script>
@endpush
