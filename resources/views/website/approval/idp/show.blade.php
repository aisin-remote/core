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
        use Illuminate\Support\Str;

        $employee = optional($assessment)->employee;

        $alcMap = ($alcs ?? collect())->mapWithKeys(fn($a) => [$a->id => $a->name])->toArray();

        $allDetails = $assessment->details ?? collect();

        $strengthRows = $allDetails->filter(fn($d) => !empty(trim($d->strength ?? '')) && trim($d->strength) !== '-');
        $weaknessRows = $allDetails->filter(fn($d) => !empty(trim($d->weakness ?? '')) && trim($d->weakness) !== '-');

        $assessmentDateText = optional($assessment->created_at)
            ? \Carbon\Carbon::parse($assessment->created_at)->timezone('Asia/Jakarta')->format('d M Y')
            : '-';

        $latestIdpUpdated = optional(($assessment->idp ?? collect())->sortByDesc('updated_at')->first())->updated_at;
        $idpCreatedAtText = $latestIdpUpdated
            ? \Carbon\Carbon::parse($latestIdpUpdated)->timezone('Asia/Jakarta')->format('d M Y')
            : '-';

        $alcTitle = function ($alcId) use ($alcMap) {
            return $alcMap[$alcId] ?? 'ALC #' . $alcId;
        };
    @endphp

    <div id="kt_app_content_container" class="app-container container-fluid">

        {{-- =================== HEADER SUMMARY =================== --}}
        <div class="card mb-5">
            <div class="card-header align-items-center">
                <h3 class="card-title mb-2" style="font-size: 2rem; font-weight: bold;">
                    Summary {{ $employee->name ?? '-' }}
                </h3>

                <div class="d-flex align-items-start gap-3 ms-3">
                    <a href="{{ url()->previous() }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>

                    @if (!empty($assessment->upload))
                        <a href="{{ asset($assessment->upload) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> View PDF
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <style>
                    .section-title {
                        font-weight: 700;
                        font-size: 1.1rem;
                        border-left: 4px solid #0d6efd;
                        padding-left: 10px;
                        margin-top: 1.25rem;
                        margin-bottom: 0.75rem;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    }

                    table.custom-table { font-size: 0.93rem; }
                    table.custom-table th, table.custom-table td {
                        padding: 0.75rem 1rem;
                        vertical-align: top;
                    }
                    table.custom-table thead { background-color: #f8f9fa; font-weight: 700; }
                    table.custom-table tbody tr:hover { background-color: #f1faff; }

                    .sticky-ref { position: sticky; top: 90px; }
                    .ref-card { max-height: calc(100vh - 120px); overflow: auto; }

                    .table-sticky thead th {
                        position: sticky; top: 0; z-index: 2; background: #f8f9fa;
                    }

                    .ref-item { text-decoration: none; }
                    .ref-item:hover { text-decoration: none; }

                    .ref-list { display: grid; gap: 14px; }

                    .ref-preview {
                        display: -webkit-box;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        line-height: 1.35rem;
                        max-height: calc(1.35rem * 2);
                    }

                    /* SWEETALERT NO SCROLL */
                    .swal2-popup { max-height: none !important; height: auto !important; }
                    .swal2-html-container { max-height: none !important; overflow: visible !important; }
                    .swal2-content { overflow: visible !important; }
                </style>

                <div class="row g-6">

                    {{-- ================= LEFT: MAIN APPROVAL ================= --}}
                    <div class="col-lg-8">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="mb-0 fw-bold">Individual Development Program</h4>
                            <div class="text-muted small">
                                {{ $employee->npk ?? '-' }} • {{ $employee->position ?? '-' }} •
                                {{ $employee->company_name ?? '-' }}
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover custom-table mb-0 table-sticky">
                                        <thead>
                                            <tr>
                                                <th>ALC</th>
                                                <th>Category</th>
                                                <th>Development Program</th>
                                                <th>Development Target</th>
                                                <th style="width: 90px;">Due Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse (($assessment->idp ?? collect()) as $idp)
                                                <tr id="idp-row-{{ $idp->id }}">
                                                    <td>
                                                        {{ optional($idp->alc)->name ?? ($alcMap[$idp->alc_id] ?? 'ALC #' . $idp->alc_id) }}
                                                    </td>
                                                    <td>{{ $idp->category }}</td>
                                                    <td>{{ $idp->development_program }}</td>
                                                    <td>{!! nl2br(e($idp->development_target)) !!}</td>
                                                    <td>{{ $idp->date ? \Carbon\Carbon::parse($idp->date)->format('d-m-Y') : '-' }}</td>
                                                    <td>
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
                                                    <td colspan="6" class="text-center text-muted">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">
                                        Total IDP: <b>{{ ($assessment->idp ?? collect())->count() }}</b>
                                    </span>
                                    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= RIGHT: REFERENCE (STICKY) ================= --}}
                    <div class="col-lg-4">
                        <div class="sticky-ref">
                            <div class="card border ref-card">
                                <div class="card-body">

                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <div class="fw-bold" style="font-size: 1.05rem;">Reference</div>
                                            <div class="text-muted">{{ $employee->name ?? '-' }}</div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="text-muted small">Assessment Date</div>
                                        <div class="fw-bold">{{ $assessmentDateText }}</div>
                                    </div>

                                    <hr>

                                    {{-- Strength --}}
                                    @if ($strengthRows->isNotEmpty())
                                        <div class="section-title" style="margin-top:0;">
                                            <i class="bi bi-lightning-charge-fill"></i> Strength
                                        </div>

                                        <div class="ref-list">
                                            @foreach ($strengthRows as $row)
                                                @php
                                                    $title = $alcTitle($row->alc_id);
                                                    $full = trim((string) $row->strength);
                                                    $preview = Str::limit($full, 140);
                                                @endphp

                                                <button type="button" class="ref-item btn btn-link text-start p-0 w-100"
                                                    data-title="{{ e($title) }}" data-content="{{ e($full) }}">
                                                    <div class="fw-bold">{{ $title }}</div>
                                                    <div class="ref-preview text-muted">{{ $preview }}</div>
                                                </button>
                                            @endforeach
                                        </div>

                                        <hr>
                                    @endif

                                    {{-- Weakness --}}
                                    @if ($weaknessRows->isNotEmpty())
                                        <div class="section-title" style="margin-top:0;">
                                            <i class="bi bi-lightning-charge-fill"></i> Weakness
                                        </div>

                                        <div class="ref-list">
                                            @foreach ($weaknessRows as $row)
                                                @php
                                                    $title = $alcTitle($row->alc_id);
                                                    $full = trim((string) $row->weakness);
                                                    $preview = Str::limit($full, 140);
                                                @endphp

                                                <button type="button" class="ref-item btn btn-link text-start p-0 w-100"
                                                    data-title="{{ e($title) }}" data-content="{{ e($full) }}">
                                                    <div class="fw-bold">{{ $title }}</div>
                                                    <div class="ref-preview text-muted">{{ $preview }}</div>
                                                </button>
                                            @endforeach
                                        </div>

                                        <hr>
                                    @endif

                                    {{-- ✅ CHART SUDAH DIPINDAH KE BAWAH --}}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- row --}}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // ✅ route helpers (biar aman kalau ada prefix/group)
            const approveUrlTemplate = @json(route('idp.approve', ':id'));
            const reviseUrl = @json(route('idp.revise'));

            // klik reference item => tampilkan full text (tanpa scroll)
            document.querySelectorAll('.ref-item').forEach(btn => {
                btn.addEventListener('click', () => {
                    const title = btn.dataset.title || 'Detail';
                    const content = btn.dataset.content || '-';

                    Swal.fire({
                        title: title,
                        html: `
                            <div style="
                                text-align:left;
                                white-space:pre-wrap;
                                line-height:1.7;
                                font-size:15px;
                            ">
                                ${content}
                            </div>
                        `,
                        width: '900px',
                        padding: '2rem',
                        confirmButtonText: 'Tutup',
                        showCloseButton: true,
                        allowOutsideClick: true,
                        allowEscapeKey: true,
                    });
                });
            });


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
