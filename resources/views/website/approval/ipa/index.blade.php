@extends('layouts.root.main')

@section('title', $title ?? 'Approval')
@section('breadcrumbs', $title ?? 'Approval')

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">IPA List</h3>

                <div class="d-flex align-items-center">
                    <form method="GET" class="d-flex align-items-center">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2"
                            placeholder="Search Employee..." style="width:200px;">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <button type="submit" class="btn btn-primary me-3">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if (optional(auth()->user())->role === 'HRD')
                    @php
                        $tabs = [
                            'all' => 'Show All',
                            'Direktur' => 'Direktur',
                            'GM' => 'GM',
                            'Manager' => 'Manager',
                            'Section Head' => 'Section Head',
                            'Coordinator' => 'Coordinator',
                            'Supervisor' => 'Supervisor',
                            'Leader' => 'Leader',
                            'JP' => 'JP',
                            'Operator' => 'Operator',
                        ];
                    @endphp
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                        role="tablist" style="cursor:pointer">
                        @foreach ($tabs as $key => $label)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4 {{ $filter === $key ? 'active' : '' }}"
                                    href="{{ route('ipa.approval', ['company' => $company, 'search' => request('search'), 'filter' => $key]) }}">
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="ipa_approval">
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
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@include('website.modal.ipa.comment')

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

            const dt = $('#ipa_approval').DataTable({
                processing: true,
                serverSide: false,
                searching: false,
                lengthChange: false,
                pageLength: 10,
                ordering: false,
                ajax: function(d, cb) {
                    const params = new URLSearchParams(window.location.search);
                    const url = new URL(@json(route('ipa.approval.json')));
                    ['company', 'filter', 'search', 'filter_year', 'status', 'npk', 'page', 'per_page']
                    .forEach(k => {
                        const v = params.get(k);
                        if (v) url.searchParams.set(k, v);
                    });
                    const page = Math.floor((d.start || 0) / (d.length || 10)) + 1;
                    url.searchParams.set('page', page);
                    url.searchParams.set('per_page', d.length || 10);

                    fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.json())
                        .then(json => {
                            const rows = (json.data || []).map((r, i) => {
                                const e = r.employee || {};
                                const canApprove = !!r.can_approve;

                                // Export button
                                let exportBtns = '';
                                if (r.id) {
                                    const excelHref =
                                        `{{ route('ipa.export', ['id' => '___ID___']) }}`
                                        .replace('___ID___', r.id);
                                    exportBtns = `<a class="btn btn-sm btn-success" href="${excelHref}">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
                            </a>`;
                                }

                                // Action buttons
                                let actBtns = '';
                                if (r.id) {
                                    if (canApprove) {
                                        actBtns = `
                                    <button type="button" class="btn btn-sm btn-primary btn-approve" data-ipa-id="${r.id}">
                                        <i class="fas fa-check me-1 fs-7"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning btn-revise" data-ipa-id="${r.id}">
                                        <i class="fas fa-times me-1 fs-7"></i> Revise
                                    </button>`;
                                    } else {
                                        actBtns =
                                            `<span class="text-muted">Not in your queue</span>`;
                                    }
                                } else {
                                    actBtns = `<span class="text-muted">No IPA</span>`;
                                }

                                const act =
                                    `<div class="d-flex justify-content-end flex-wrap gap-2">${exportBtns}${actBtns}</div>`;

                                return [
                                    r.no ?? ((d.start || 0) + i + 1),
                                    e.npk ?? '-',
                                    e.name ?? '-',
                                    e.company ?? '-',
                                    e.position ?? e.position_name ?? '-',
                                    e.department ?? e.department_name ?? '-',
                                    e.grade ?? '-',
                                    badgeStatus(r.status || '-'),
                                    act
                                ];
                            });

                            cb({
                                draw: d.draw,
                                recordsTotal: json.meta?.total || rows.length,
                                recordsFiltered: json.meta?.total || rows.length,
                                data: rows
                            });
                        })
                        .catch(_ => cb({
                            draw: d.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        }));
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
                if (!res.ok) throw new Error(`HTTP ${res.status} ${(await res.text().catch(()=>''))}`);
                return res.json().catch(() => ({}));
            }

            // ===== Revise modal refs
            const reviseModalEl = document.getElementById('reviseModal');
            const reviseModal = bootstrap.Modal.getOrCreateInstance(reviseModalEl);
            const reviseForm = document.getElementById('reviseForm');
            const reviseIppIdEl = document.getElementById('reviseIppId');
            const reviseNoteEl = document.getElementById('reviseNote');
            const reviseErrEl = document.getElementById('reviseError');
            const reviseSubmit = document.getElementById('reviseSubmitBtn');
            const reviseCount = document.getElementById('reviseCount');

            reviseNoteEl?.addEventListener('input', () => {
                reviseCount.textContent = (reviseNoteEl.value || '').length;
            });

            // Delegated clicks
            document.addEventListener('click', async (ev) => {
                // Approve
                const approveBtn = ev.target.closest('.btn-approve');
                if (approveBtn) {
                    const ipaId = approveBtn.getAttribute('data-ipa-id');
                    if (!ipaId) return;
                    if (!confirm('Yakin ingin APPROVE IPA ini?')) return;
                    approveBtn.disabled = true;
                    approveBtn.classList.add('disabled');
                    try {
                        const approveBase = @json(route('ipa.approve', ['ipa' => '___ID___']));
                        await postJSON(approveBase.replace('___ID___', ipaId));
                        $('#ipa_approval').DataTable().ajax.reload(null, false);
                    } catch (err) {
                        console.error(err);
                        alert('Gagal memproses APPROVE.');
                    } finally {
                        approveBtn.disabled = false;
                        approveBtn.classList.remove('disabled');
                    }
                    return;
                }

                // Revise → buka modal
                const reviseBtn = ev.target.closest('.btn-revise');
                if (reviseBtn) {
                    const ipaId = reviseBtn.getAttribute('data-ipa-id');
                    if (!ipaId) return;
                    reviseIppIdEl.value = ipaId;
                    reviseNoteEl.value = '';
                    reviseCount.textContent = '0';
                    reviseErrEl.classList.add('d-none');
                    reviseModal.show();
                }
            });

            // Submit revise
            reviseForm?.addEventListener('submit', async (e) => {
                e.preventDefault();

                const ipaId = reviseIppIdEl.value;
                const note = (reviseNoteEl.value || '').trim();

                if (!note) {
                    reviseErrEl.textContent = 'Please write a comment.';
                    reviseErrEl.classList.remove('d-none');
                    reviseNoteEl.focus();
                    return;
                }

                const reviseBase = @json(route('ipa.revise', ['ipa' => '___ID___']));
                const url = reviseBase.replace('___ID___', ipaId);

                reviseSubmit.disabled = true;
                reviseSubmit.classList.add('disabled');

                try {
                    await postJSON(url, {
                        note
                    });
                    reviseModal.hide();
                    $('#ipa_approval').DataTable().ajax.reload(null, false);
                } catch (err) {
                    console.error(err);
                    alert('Gagal mengirim revisi.');
                } finally {
                    reviseSubmit.disabled = false;
                    reviseSubmit.classList.remove('disabled');
                }
            });
        })();
    </script>
@endpush
