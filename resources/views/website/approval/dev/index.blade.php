@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Approval' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Approval' }}
@endsection

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Approval Development List</h3>
                <div class="d-flex align-items-center">
                    <form method="GET" class="d-flex align-items-center">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               class="form-control me-2"
                               placeholder="Search Employee..."
                               style="width: 200px;">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <button type="submit" class="btn btn-primary me-3">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if (auth()->user()->role == 'HRD')
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                        role="tablist"
                        style="cursor:pointer">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'all' ? 'active' : '' }}"
                           href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            Show All
                        </a>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Direktur' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Direktur']) }}">
                                Direktur
                            </a>
                        </li>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'GM' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'GM']) }}">
                                GM
                            </a>
                        </li>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Manager' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Manager']) }}">
                                Manager
                            </a>
                        </li>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Section Head' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Section Head']) }}">
                                Section Head
                            </a>
                        </li>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Coordinator' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Coordinator']) }}">
                                Coordinator
                            </a>
                        </li>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Supervisor' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Supervisor']) }}">
                                Supervisor
                            </a>
                        </li>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Leader' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Leader']) }}">
                                Leader
                            </a>
                        </li>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'JP' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'JP']) }}">
                                JP
                            </a>
                        </li>

                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Operator' ? 'active' : '' }}"
                               href="{{ route('development.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Operator']) }}">
                                Operator
                            </a>
                        </li>
                    </ul>
                @endif

                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable"
                       id="development_approval">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>NPK</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

{{-- Reuse modal revisi IPP untuk Development --}}
@include('website.modal.ipp.comment')

@push('scripts')
    <script>
        (function () {
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

            // ===== One-Year rows (tanpa action per baris) =====
            function buildOneRows(items) {
                if (!items || !items.length) {
                    return `<tr>
                        <td colspan="3" class="text-center text-muted py-4">No data.</td>
                    </tr>`;
                }

                return items.map(dev => {
                    const developmentProgram = dev.development_program || '-';
                    const evaluationResult   = dev.evaluation_result || '-';
                    const statusBadge        = badgeStatus(dev.status || '-');

                    return `
                        <tr>
                            <td class="fw-semibold">${developmentProgram}</td>
                            <td>${evaluationResult}</td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                }).join('');
            }

            // ===== Detail accordion: action 1x per employee =====
            function buildDetailHtml(row) {
                const oneTableRows = buildOneRows(row.one_devs || []);

                return `
                    <div class="p-5 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0">One-Year Development</h6>
                                <div class="text-muted fs-8 mt-1">
                                    Action berlaku untuk <span class="fw-semibold">semua</span> development milik employee ini.
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button"
                                        class="btn btn-sm btn-danger btn-revise-employee"
                                        data-employee-id="${row.employee_id}">
                                    <i class="fas fa-times me-1 fs-7"></i> Revise
                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-success btn-approve-employee"
                                        data-employee-id="${row.employee_id}">
                                    <i class="fas fa-check me-1 fs-7"></i> Approve
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-rounded table-row-dashed fs-7 gy-3">
                                <thead>
                                    <tr class="text-muted fw-bold text-uppercase">
                                        <th>Development Program</th>
                                        <th>Evaluation Result</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${oneTableRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }

            const dt = $('#development_approval').DataTable({
                processing: true,
                serverSide: false,
                paging: true,
                lengthChange: false,
                searching: false,
                ordering: false,
                ajax: {
                    url: @json(route('development.approval.json')),
                    dataSrc: 'data',
                    data: function (d) {
                        const params = new URLSearchParams(window.location.search);
                        ['company', 'filter', 'search'].forEach(k => {
                            const v = params.get(k);
                            if (v) d[k] = v;
                        });
                    }
                },
                columns: [
                    {
                        data: 'no',
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'employee.npk', defaultContent: '-' },
                    { data: 'employee.name', defaultContent: '-' },
                    { data: 'employee.department', defaultContent: '-' },
                    { data: 'employee.position', defaultContent: '-' },
                    {
                        data: 'status',
                        render: function (data) {
                            return badgeStatus(data);
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function (data, type, row) {
                            const exportUrl = `{{ route('idp.exportTemplate', ['employee_id' => '___EMP___']) }}`
                                .replace('___EMP___', row.employee_id);

                            return `
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="${exportUrl}"
                                       class="btn btn-sm btn-warning"
                                       title="Export Development">
                                        <i class="bi bi-upload"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-primary btn-toggle-accordion"
                                            title="Show Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                columnDefs: [
                    { targets: '_all', className: 'align-middle fs-7' }
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
                    throw new Error(`HTTP ${res.status} ${(await res.text().catch(() => ''))}`);
                }
                return res.json().catch(() => ({}));
            }

            // ====== Toggle accordion (row.child) ======
            $('#development_approval tbody').on('click', '.btn-toggle-accordion', function () {
                const tr = $(this).closest('tr');
                const row = dt.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    const html = buildDetailHtml(row.data());
                    row.child(html).show();
                    tr.addClass('shown');
                }
            });

            // ====== Modal Revise (reuse dari IPP) ======
            const reviseModalEl = document.getElementById('reviseModal');
            const reviseModal   = reviseModalEl ? bootstrap.Modal.getOrCreateInstance(reviseModalEl) : null;
            const reviseForm    = document.getElementById('reviseForm');
            const reviseIppIdEl = document.getElementById('reviseIppId'); // sekarang employee_id
            const reviseNoteEl  = document.getElementById('reviseNote');
            const reviseErrEl   = document.getElementById('reviseError');
            const reviseSubmit  = document.getElementById('reviseSubmitBtn');
            const reviseCount   = document.getElementById('reviseCount');

            if (reviseNoteEl && reviseCount) {
                reviseNoteEl.addEventListener('input', () => {
                    reviseCount.textContent = (reviseNoteEl.value || '').length;
                });
            }

            // ====== Delegated click: Approve / Revise PER EMPLOYEE ======
            document.addEventListener('click', async (ev) => {
                // Approve all development by employee
                const approveBtn = ev.target.closest('.btn-approve-employee');
                if (approveBtn) {
                    const employeeId = approveBtn.getAttribute('data-employee-id');
                    if (!employeeId) return;

                    const result = await Swal.fire({
                        title: 'Approve Development?',
                        text: 'Yakin ingin APPROVE semua development employee ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Approve',
                        cancelButtonText: 'Batal',
                    });

                    if (!result.isConfirmed) return;

                    approveBtn.disabled = true;
                    approveBtn.classList.add('disabled');

                    try {
                        const approveUrl = @json(route('development.approveByEmployee', ['id' => '___ID___']))
                            .replace('___ID___', employeeId);

                        await postJSON(approveUrl);

                        await Swal.fire({
                            icon: 'success',
                            title: 'Approved',
                            text: 'Semua development berhasil di-approve.',
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        dt.ajax.reload(null, false);
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Error', 'Gagal memproses APPROVE.', 'error');
                    } finally {
                        approveBtn.disabled = false;
                        approveBtn.classList.remove('disabled');
                    }
                    return;
                }

                // Open revise modal by employee
                const reviseBtn = ev.target.closest('.btn-revise-employee');
                if (reviseBtn && reviseModal) {
                    const employeeId = reviseBtn.getAttribute('data-employee-id');
                    if (!employeeId) return;

                    reviseIppIdEl.value = employeeId; // isi hidden input dengan employee_id
                    if (reviseNoteEl) reviseNoteEl.value = '';
                    if (reviseCount) reviseCount.textContent = '0';
                    if (reviseErrEl) reviseErrEl.classList.add('d-none');

                    reviseModal.show();
                }
            });

            // Submit revise all development by employee
            if (reviseForm && reviseModal) {
                reviseForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const employeeId = reviseIppIdEl?.value;
                    const note  = (reviseNoteEl?.value || '').trim();

                    if (!note) {
                        if (reviseErrEl) {
                            reviseErrEl.textContent = 'Please write a comment.';
                            reviseErrEl.classList.remove('d-none');
                        }
                        reviseNoteEl?.focus();
                        return;
                    }

                    const result = await Swal.fire({
                        title: 'Kirim Revisi?',
                        text: 'Yakin ingin mengirim revisi untuk semua development employee ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Kirim',
                        cancelButtonText: 'Batal',
                    });

                    if (!result.isConfirmed) return;

                    if (reviseSubmit) reviseSubmit.disabled = true;

                    try {
                        const reviseUrl = @json(route('development.reviseByEmployee', ['id' => '___ID___']))
                            .replace('___ID___', employeeId);

                        await postJSON(reviseUrl, { note });

                        reviseModal.hide();

                        await Swal.fire({
                            icon: 'success',
                            title: 'Revisi terkirim',
                            text: 'Revisi berhasil dikirim.',
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        dt.ajax.reload(null, false);
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Error', 'Gagal mengirim revisi.', 'error');
                    } finally {
                        if (reviseSubmit) reviseSubmit.disabled = false;
                    }
                });
            }
        })();
    </script>
@endpush
