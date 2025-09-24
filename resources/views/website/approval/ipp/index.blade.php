@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Approval' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Approval' }}
@endsection
@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">IPP List</h3>
                <div class="d-flex align-items-center">
                    <form method="GET" class="d-flex align-items-center">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2"
                            placeholder="Search Employee..." style="width: 200px;">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <button type="submit" class="btn btn-primary me-3">
                            <i class="fas fa-search"></i> Search
                        </button>
                        {{-- <button type="button" class="btn btn-info me-3" data-bs-toggle="modal"
                            data-bs-target="#importModal">
                            <i class="fas fa-upload"></i>
                            Import
                        </button> --}}
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if (auth()->user()->role == 'HRD')
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                        role="tablist" style="cursor:pointer">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'all' ? 'active' : '' }}"
                            href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            Show All
                        </a>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Direktur' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Direktur']) }}">
                                Direktur
                            </a>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'GM' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'GM']) }}">GM</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Manager' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Manager']) }}">Manager</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Section Head' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Section Head']) }}">Section
                                Head</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Coordinator' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Coordinator']) }}">Coordinator</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Supervisor' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Supervisor']) }}">Supervisor</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Leader' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Leader']) }}">Leader</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'JP' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'JP']) }}">JP</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Operator' ? 'active' : '' }}"
                                href="{{ route('ipp.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Operator']) }}">Operator</a>
                        </li>
                    </ul>
                @endif
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="ipp_approval">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            function badgeStatus(s) {
                const map = {
                    submitted: 'badge-light-warning',
                    checked: 'badge-light-info',
                    approved: 'badge-light-success',
                    revise: 'badge-light-danger',
                    draft: 'badge-light-secondary'
                };
                const cls = map[(s || '').toLowerCase()] || 'badge-light';
                return `<span class="badge ${cls}">${(s || '').toUpperCase()}</span>`;
            }

            const dt = $('#ipp_approval').DataTable({
                processing: true,
                serverSide: false,
                searching: false,
                lengthChange: false,
                pageLength: 10,
                ordering: false,
                ajax: function(d, callback) {
                    const params = new URLSearchParams(window.location.search);
                    const url = new URL(@json(route('ipp.approval.json')));
                    ['company', 'filter', 'search', 'filter_year', 'status', 'npk', 'page', 'per_page']
                    .forEach(k => {
                        const v = params.get(k);
                        if (v) url.searchParams.set(k, v);
                    });
                    // DataTables pagination
                    const page = Math.floor(d.start / d.length) + 1;
                    url.searchParams.set('page', page);
                    url.searchParams.set('per_page', d.length);

                    fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.json())
                        .then(json => {
                            const rows = (json.data || []).map((r, i) => {
                                const e = r.employee || {};

                                // Link export (hanya kalau ada IPP id)
                                let exportBtns = '';
                                if (r.id) {
                                    const excelHref =
                                        `{{ route('ipp.export.excel', ['id' => '___ID___']) }}`
                                        .replace('___ID___', r.id);
                                    const pdfHref =
                                        `{{ route('ipp.export.pdf', ['id' => '___ID___']) }}`
                                        .replace('___ID___', r.id);

                                    exportBtns = `
                                        <a class="btn btn-sm btn-success" href="${excelHref}">
                                            <i class="bi bi-file-earmark-spreadsheet"></i> Excel
                                        </a>
                                        <a class="btn btn-sm btn-danger" href="${pdfHref}" rel="noopener">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                    `;
                                }

                                // Tombol action -> panggil API approve & revise
                                const actBtns = r.id ? `
                                    <button type="button" class="btn btn-sm btn-primary btn-approve" data-ipp-id="${r.id}">
                                        <i class="fas fa-check me-1 fs-7"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning btn-revise" data-ipp-id="${r.id}">
                                        <i class="fas fa-times me-1 fs-7"></i> Revise
                                    </button>
                                ` : `<span class="text-muted">No IPP</span>`;

                                // Gabungkan export + action
                                const act = r.id ?
                                    `<div class="d-flex justify-content-end flex-wrap gap-2">${exportBtns}${actBtns}</div>` :
                                    `<div class="text-end">${actBtns}</div>`;

                                return [
                                    r.no ?? ((d.start || 0) + i + 1),
                                    e.npk ?? '-',
                                    e.name ?? '-',
                                    e.company ?? '-',
                                    e.position ?? '-',
                                    e.department ?? '-',
                                    e.grade ?? '-',
                                    badgeStatus(r.status || '-'),
                                    act
                                ];
                            });

                            callback({
                                draw: d.draw,
                                recordsTotal: json.meta?.total || rows.length,
                                recordsFiltered: json.meta?.total || rows.length,
                                data: rows
                            });
                        })
                        .catch(_ => {
                            callback({
                                draw: d.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: []
                            });
                        });
                },
                columnDefs: [{
                        targets: '_all',
                        className: 'align-middle fs-7'
                    },
                    {
                        targets: [8],
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            async function postJSON(url, payload = {}) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) {
                    const msg = await res.text().catch(() => '');
                    throw new Error(`HTTP ${res.status} ${msg}`);
                }
                return res.json().catch(() => ({}));
            }

            // Delegated click handlers for Approve/Revise
            document.addEventListener('click', async (ev) => {
                const approveBtn = ev.target.closest('.btn-approve');
                const reviseBtn = ev.target.closest('.btn-revise');
                if (!approveBtn && !reviseBtn) return;

                const btn = approveBtn || reviseBtn;
                const ippId = btn.getAttribute('data-ipp-id');
                if (!ippId) return;

                try {
                    const action = approveBtn ? 'approve' : 'revise';
                    if (!confirm(`Yakin ingin ${action.toUpperCase()} IPP ini?`)) return;

                    btn.disabled = true;
                    btn.classList.add('disabled');

                    const approveBase = @json(route('ipp.approve', ['id' => '___ID___']));
                    const reviseBase = @json(route('ipp.revise', ['id' => '___ID___']));
                    const url = (approveBtn ? approveBase : reviseBase).replace('___ID___', ippId);

                    const payload = approveBtn ? {} : {
                        note: 'Please revise.'
                    };

                    await postJSON(url, payload);
                    $('#ipp_approval').DataTable().ajax.reload(null, false);
                } catch (err) {
                    console.error(err);
                    alert('Gagal memproses permintaan.');
                } finally {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                }
            });
        })();
    </script>
@endpush
