@extends('layouts.root.main')

@push('custom-css')
    <style>
        .legend-circle {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block
        }

        .table-responsive {
            overflow-x: auto;
            white-space: nowrap
        }

        .sticky-col {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 2;
            box-shadow: 2px 0 5px rgba(0, 0, 0, .06)
        }

        thead th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            z-index: 5
        }

        .emp-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: #f1f5f9
        }

        .emp-fallback {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #4338ca;
            font-weight: 700
        }

        #ipp-pagination {
            display: flex;
            justify-content: end;
            gap: .5rem;
            margin-top: 1rem
        }

        /* status chip */
        .status-chip {
            --bg: #eef2ff;
            --fg: #312e81;
            --bd: #c7d2fe;
            --dot: #6366f1;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .35rem .65rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: .8rem;
            line-height: 1;
            border: 1px solid var(--bd);
            background: var(--bg);
            color: var(--fg)
        }

        .status-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dot)
        }

        .status-chip[data-status="approved"] {
            --bg: #ecfdf5;
            --fg: #065f46;
            --bd: #a7f3d0;
            --dot: #10b981
        }

        .status-chip[data-status="checked"] {
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b
        }

        .status-chip[data-status="waiting"] {
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b
        }

        .status-chip[data-status="draft"] {
            --bg: #f8fafc;
            --fg: #334155;
            --bd: #e2e8f0;
            --dot: #94a3b8
        }

        .status-chip[data-status="revise"] {
            --bg: #fef2f2;
            --fg: #7f1d1d;
            --bd: #fecaca;
            --dot: #ef4444
        }

        .status-chip[data-status="not_created"],
        .status-chip[data-status="unknown"] {
            --bg: #f4f4f5;
            --fg: #27272a;
            --bd: #e4e4e7;
            --dot: #a1a1aa
        }
    </style>
@endpush

@section('title')
    {{ $title ?? 'IPP' }}
@endsection
@section('breadcrumbs')
    {{ $title ?? 'IPP' }}
@endsection

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">IPP List</h3>
                <div class="d-flex align-items-center">
                    <form method="GET" action="{{ url()->current() }}" class="d-flex mb-3" onsubmit="return false;">
                        <input type="text" id="searchInputEmployee" name="search" class="form-control me-2"
                            placeholder="Search..." style="width:250px" value="{{ request('search') }}">
                        <button type="button" class="btn btn-primary me-3" id="searchButton">Search</button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 text-sm font-medium mb-6"
                    role="tablist" style="cursor:pointer">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link fs-7 {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('ipp.list', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-7 my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('ipp.list', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                <i class="fas fa-user-tag me-2"></i>{{ $position }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>No</th>
                                <th>Photo</th>
                                <th>NPK</th>
                                <th>Employee Name</th>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Grade</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody><!-- rows via JS --></tbody>
                    </table>
                </div>

                <div id="ipp-pagination"></div>
            </div>
        </div>
    </div>

    {{-- Modal: daftar IPP si karyawan --}}
    <div class="modal fade" id="ippShowModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: fit-content; margin: auto;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        IPP - <span id="ippShowEmpName">Employee</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body py-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="text-muted text-uppercase fs-7">
                                    <th style="width: 200px;">Year</th>
                                    <th style="width: 200px;">Status</th>
                                    <th style="width: 200px;" class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="ippShowRows">
                                <tr class="fs-7">
                                    <td colspan="3" class="text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const tableBody = document.querySelector('#kt_table_users tbody');
            const searchInput = document.getElementById('searchInputEmployee');
            const searchBtn = document.getElementById('searchButton');

            const params = new URLSearchParams(window.location.search);
            const company = @json($company ?? '');
            const filter = params.get('filter') || 'all';
            const initialSearch = params.get('search') || '';
            const year = params.get('filter_year') || (new Date().getFullYear());
            const status = params.get('status') || '';

            if (!searchInput.value) searchInput.value = initialSearch;

            function statusLabel(s) {
                const map = {
                    draft: 'Draft',
                    submitted: 'Submitted',
                    checked: 'Checked',
                    approved: 'Approved',
                    revise: 'Revise',
                    not_created: 'Not Created'
                };
                return map[(s || '').toLowerCase()] || 'Unknown';
            }

            function chip(s) {
                return `<span class="status-chip" data-status="${(s||'unknown').toLowerCase()}">${statusLabel(s)}</span>`;
            }

            function actionBtn(r) {
                return `
        <button type="button" class="btn btn-sm btn-secondary btn-show"
                data-employee-id="${r.employee?.id ?? ''}"
                data-employee='${encodeURIComponent(JSON.stringify(r.employ ee||{}))}'
                data-current-year="${r.on_year || ''}"  >
        Show
        </button>`;
            }

            // === INIT DATATABLES ===
            const dt = $('#kt_table_users').DataTable({
                processing: true,
                serverSide: false,
                searching: true,
                lengthChange: false,
                pageLength: 10,
                ordering: false,
                ajax: function(d, callback, settings) {
                    const page = Math.floor(d.start / d.length) + 1;
                    const perPage = d.length;

                    const url = new URL(@json(route('ipp.list.json')));
                    if (company) url.searchParams.set('company', company);
                    url.searchParams.set('filter', filter);
                    url.searchParams.set('search', searchInput.value || '');
                    url.searchParams.set('filter_year', year);
                    if (status) url.searchParams.set('status', status);
                    url.searchParams.set('page', page);
                    url.searchParams.set('per_page', perPage);

                    fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(json => {
                            const rows = (json.data || []).map(r => {
                                const emp = r.employee || {};
                                const photo = emp.photo ?
                                    `<img src="${emp.photo}" class="emp-photo" alt="${emp.name||''}">` :
                                    `<div class="emp-fallback">${(emp.name||'?').slice(0,2)}</div>`;
                                return [
                                    r.no,
                                    photo,
                                    emp.npk ?? '-',
                                    `<span class="sticky-col">${emp.name ?? '-'}</span>`,
                                    emp.company ?? '-',
                                    emp.position ?? '-',
                                    emp.department ?? '-',
                                    emp.grade ?? '-',
                                    actionBtn(r)
                                ];
                            });

                            callback({
                                draw: d.draw,
                                recordsTotal: json.meta?.total ?? 0,
                                recordsFiltered: json.meta?.total ?? 0,
                                data: rows
                            });
                        })
                        .catch(err => {
                            console.error('DataTables ajax error', err);
                            callback({
                                draw: d.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: []
                            });
                        });
                },
                columnDefs: [{
                        targets: [1, 8],
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: '_all',
                        className: 'align-middle fs-7'
                    }
                ]
            });

            // Trigger reload saat klik Search
            searchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                dt.ajax.reload();
            });
            // Enter di input Search
            searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') dt.ajax.reload();
            });

            // === SHOW MODAL HANDLER (delegated, aman utk redraw) ===
            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('.btn-show');
                if (!btn) return;

                const employeeId = btn.getAttribute('data-employee-id');
                const empRaw = btn.getAttribute('data-employee') || '{}';
                const emp = JSON.parse(decodeURIComponent(empRaw) || '{}');

                document.getElementById('ippShowEmpName').textContent = emp.name || '-';

                const modalEl = document.getElementById('ippShowModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();

                const url = new URL(@json(route('ipp.employee.ipps.json')));
                url.searchParams.set('employee_id', employeeId);

                const tbody = document.getElementById('ippShowRows');
                tbody.innerHTML = `<tr><td colspan="5" class="text-muted">Loading...</td></tr>`;

                try {
                    const res = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();

                    const rows = (json.ipps || []).map(item => {
                        const year = item.on_year || '';
                        const stat = item.status || 'unknown';

                        let actions = '';

                        if (item.id) {
                            const excelHref =
                                `{{ route('ipp.export.excel', ['id' => '___ID___']) }}`.replace(
                                    '___ID___', item.id);
                            const pdfHref = `{{ route('ipp.export.pdf', ['id' => '___ID___']) }}`
                                .replace('___ID___', item.id);

                            actions = `
                            <a class="btn btn-sm btn-success me-2" href="${excelHref}">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
                            </a>
                            <a class="btn btn-sm btn-danger" href="${pdfHref}" rel="noopener">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                            `;
                        } else {
                            actions = `<span class="text-muted">No Data</span>`;
                        }

                        return `
                            <tr>
                                <td><span class="fw-bold">${year}</span></td>
                                <td>${chip(stat)}</td>
                                <td class="text-end">${actions}</td>
                            </tr>`;
                    }).join('');

                    tbody.innerHTML = rows ||
                        `<tr><td colspan="5" class="text-muted">Belum ada IPP</td></tr>`;
                } catch (err) {
                    tbody.innerHTML =
                        `<tr><td colspan="5" class="text-danger">Gagal memuat data IPP</td></tr>`;
                }
            });
        })();
    </script>
@endpush
