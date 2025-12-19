@extends('layouts.root.main')

@section('title', $title ?? 'Detail Approval')
@section('breadcrumbs', $title ?? 'Detail Approval')

@section('main')
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    title: "Sukses!",
                    text: @json(session('success')),
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    @php
        $employee = optional($assessment)->employee;

        $alcMap = ($alcs ?? collect())->mapWithKeys(fn($a) => [$a->id => $a->name])->toArray();
        $allDetails = $assessment->details ?? collect();

        // hanya Weakness (tanpa Strength)
        $weaknessRows = $allDetails
            ->filter(fn($d) => !empty(trim($d->weakness ?? '')) && trim($d->weakness) !== '-')
            ->values();

        $assessmentDateText = optional($assessment->created_at)
            ? \Carbon\Carbon::parse($assessment->created_at)->timezone('Asia/Jakarta')->format('d M Y')
            : '-';

        $alcTitle = function ($alcId) use ($alcMap) {
            return $alcMap[$alcId] ?? 'ALC #' . $alcId;
        };

        $idpRows = ($assessment->idp ?? collect());
    @endphp

    <div id="kt_app_content_container" class="app-container container-fluid">

        {{-- =================== HEADER SUMMARY =================== --}}
        <div class="card mb-5">
            <div class="card-header align-items-center">
                <div class="d-flex flex-column">
                    <h3 class="card-title mb-1" style="font-size: 2rem; font-weight: bold;">
                        Detail Approval - {{ $employee->name ?? '-' }}
                    </h3>
                    <div class="text-muted small">
                        {{ $employee->npk ?? '-' }} • {{ $employee->position ?? '-' }} • {{ $employee->company_name ?? '-' }} •
                        Assessment Date: <b>{{ $assessmentDateText }}</b>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 ms-3">
                    <a href="{{ url()->previous() }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>

                    @if (!empty($assessment->upload))
                        <a href="{{ asset($assessment->upload) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> View Assessment
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <style>
                    .section-title {
                        font-weight: 700;
                        font-size: 1.05rem;
                        border-left: 4px solid #0d6efd;
                        padding-left: 10px;
                        margin: 0;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    }

                    table.custom-table { font-size: 0.93rem; }
                    table.custom-table th,
                    table.custom-table td {
                        padding: 0.85rem 1rem;
                        vertical-align: top;
                    }

                    table.custom-table thead {
                        background-color: #f8f9fa;
                        font-weight: 700;
                    }

                    table.custom-table tbody tr:hover {
                        background-color: #f1faff;
                    }

                    .table-sticky thead th {
                        position: sticky;
                        top: 0;
                        z-index: 2;
                        background: #f8f9fa;
                    }

                    /* Highlight Card (IDP) */
                    .highlight-card {
                        border: 2px solid rgba(13, 110, 253, 0.25);
                        box-shadow: 0 10px 30px rgba(13, 110, 253, 0.08);
                    }

                    .highlight-badge {
                        background: rgba(13,110,253,0.1);
                        color: #0d6efd;
                        border: 1px solid rgba(13,110,253,0.2);
                        font-weight: 700;
                        border-radius: 999px;
                        padding: 0.35rem 0.7rem;
                        display: inline-flex;
                        align-items: center;
                        gap: .4rem;
                        white-space: nowrap;
                    }

                    /* tampil full content */
                    .cell-content {
                        white-space: pre-wrap;
                        word-break: break-word;
                        line-height: 1.7;
                    }

                    /* column widths */
                    .col-no { width: 70px; }
                    .col-alc { width: 240px; }
                    .col-due { width: 110px; white-space: nowrap; }
                    .col-action { width: 180px; }

                    .table-responsive { overflow-x: auto; }

                    /* SWEETALERT NO SCROLL */
                    .swal2-popup { max-height: none !important; height: auto !important; }
                    .swal2-html-container { max-height: none !important; overflow: visible !important; }
                    .swal2-content { overflow: visible !important; }
                </style>

                {{-- =================== TABLE 1: WEAKNESS (ALC) =================== --}}
                <div class="card border mb-5">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="section-title" style="border-left-color:#dc3545;">
                            <i class="fas fa-triangle-exclamation text-danger"></i>
                            Weakness by ALC
                        </div>

                        <span class="text-muted small">
                            Total ALC Weakness: <b>{{ $weaknessRows->count() }}</b>
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover custom-table mb-0 table-sticky">
                                <thead>
                                    <tr>
                                        <th class="col-no">No</th>
                                        <th class="col-alc">ALC</th>
                                        <th>Weakness</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($weaknessRows as $i => $row)
                                        @php
                                            $title = $alcTitle($row->alc_id);
                                            $full  = trim((string) $row->weakness);
                                        @endphp
                                        <tr>
                                            <td class="col-no">{{ $i + 1 }}</td>
                                            <td class="col-alc fw-bold">{{ $title }}</td>
                                            <td>
                                                <div class="cell-content">{{ $full }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                Data belum di input oleh HRD.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- =================== TABLE 2: IDP APPROVAL (HIGHLIGHT) =================== --}}
                <div class="card highlight-card mb-2">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="section-title">
                                <i class="fas fa-list-check text-primary"></i>
                                Individual Development Program (Approval)
                            </div>

                            <span class="highlight-badge">
                                <i class="fas fa-star"></i> Highlight
                            </span>
                        </div>

                        <span class="text-muted small">
                            Total IDP: <b>{{ $idpRows->count() }}</b>
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover custom-table mb-0 table-sticky">
                                <thead>
                                    <tr>
                                        <th style="min-width:220px;">ALC</th>
                                        <th style="min-width:160px;">Category</th>
                                        <th style="min-width:240px;">Development Program</th>
                                        <th style="min-width:340px;">Development Target</th>
                                        <th class="col-due">Due Date</th>
                                        <th class="text-center col-action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($idpRows as $idp)
                                        <tr id="idp-row-{{ $idp->id }}">
                                            <td>
                                                {{ optional($idp->alc)->name ?? ($alcMap[$idp->alc_id] ?? 'ALC #' . $idp->alc_id) }}
                                            </td>
                                            <td>{{ $idp->category }}</td>
                                            <td>
                                                <div class="cell-content">{{ $idp->development_program }}</div>
                                            </td>
                                            <td>
                                                <div class="cell-content">{{ $idp->development_target }}</div>
                                            </td>
                                            <td class="col-due">
                                                {{ $idp->date ? \Carbon\Carbon::parse($idp->date)->format('d-m-Y') : '-' }}
                                            </td>
                                            <td class="col-action">
                                                <div class="d-flex flex-column gap-2">
                                                    <button
                                                        class="btn btn-sm btn-danger w-100 d-flex align-items-center justify-content-center gap-1 btn-revise"
                                                        data-id="{{ $idp->id }}">
                                                        <i class="fas fa-edit"></i>
                                                        <span>Revise</span>
                                                    </button>

                                                    <button
                                                        class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-center gap-1 btn-approve"
                                                        data-idp-id="{{ $idp->id }}">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span>Approve</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No data IDP available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3 border-top d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                Setelah approve/revise, row akan hilang otomatis.
                            </span>
                            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const approveUrlTemplate = @json(route('idp.approve', ':id'));
            const reviseUrl = @json(route('idp.revise'));

            // =============================
            // APPROVE (Route: idp.approve)
            // =============================
            document.querySelectorAll('.btn-approve').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.dataset.idpId;
                    const url = approveUrlTemplate.replace(':id', id);

                    Swal.fire({
                        title: 'Approve this data?',
                        text: "Are you sure you want to approve this data?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, approve it!',
                        cancelButtonText: 'Cancel'
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch(url, {
                                method: 'GET',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire({
                                    title: 'Approved!',
                                    text: data.message || 'Approved.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                document.getElementById(`idp-row-${id}`)?.remove();

                                const remaining = document.querySelectorAll('tr[id^="idp-row-"]').length;
                                if (remaining === 0) {
                                    window.location.href = @json(route('idp.approval')) + '?done=1';
                                }
                            })
                            .catch(() => Swal.fire('Error!', 'Something went wrong.', 'error'));
                        }
                    });
                });
            });

            // =============================
            // REVISE (Route: idp.revise)
            // =============================
            document.querySelectorAll('.btn-revise').forEach(button => {
                button.addEventListener('click', () => {
                    Swal.fire({
                        title: 'Enter reason for revision',
                        input: 'textarea',
                        inputPlaceholder: 'Write your reason here...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: value => !value ? 'You need to write something!' : null
                    }).then(result => {
                        if (result.isConfirmed) {
                            const id = button.dataset.id;
                            const revisionReason = result.value;

                            Swal.fire({
                                title: 'Submitting...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            fetch(reviseUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    id,
                                    comment: revisionReason
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire({
                                    title: 'Revised!',
                                    text: data.message || 'Revised.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                document.getElementById(`idp-row-${id}`)?.remove();

                                const remaining = document.querySelectorAll('tr[id^="idp-row-"]').length;
                                if (remaining === 0) {
                                    window.location.href = @json(route('idp.approval')) + '?done=1';
                                }
                            })
                            .catch(() => Swal.fire('Error!', 'Something went wrong.', 'error'));
                        }
                    });
                });
            });
        });
    </script>
@endpush
