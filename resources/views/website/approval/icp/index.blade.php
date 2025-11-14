@extends('layouts.root.main')

@section('title', $title ?? 'Approval')
@section('breadcrumbs', $title ?? 'Approval')

@push('custom-css')
    <style>
        :root {
            --stage-border: #3f4a5a;
            --stage-head-bg: #1f2937;
            --stage-head-fg: #fff;
            --stage-accent: #111827;
            --detail-bg: #f3f4f6;
            --detail-border: #d1d5db;
            --shadow-inset: rgba(63, 74, 90, .20);
            --shadow-card: rgba(0, 0, 0, .08);
            --radius-card: 1rem;
            --radius-detail: .65rem;
            --space-card: 1.25rem;
        }

        .stage-card {
            position: relative;
            border: 2.5px solid var(--stage-border);
            border-radius: var(--radius-card);
            background: #fff;
            overflow: hidden;
            box-shadow: 0 6px 18px var(--shadow-card)
        }

        .stage-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--stage-head-bg);
            color: var(--stage-head-fg);
            border-bottom: 2px solid var(--stage-border);
            padding: 1rem 1.25rem;
            font-weight: 700
        }

        .stage-head strong {
            font-size: 1.1rem
        }

        .stage-body {
            padding: var(--space-card)
        }

        .detail-row {
            background: var(--detail-bg);
            border: 2px solid var(--detail-border);
            border-radius: var(--radius-detail);
            padding: 14px
        }

        .stage-card.theme-blue,
        .stage-card.theme-green,
        .stage-card.theme-amber,
        .stage-card.theme-purple,
        .stage-card.theme-rose {
            border-color: var(--stage-border);
            box-shadow: 0 0 0 3px var(--shadow-inset) inset, 0 6px 18px var(--shadow-card)
        }

        .stage-card.theme-blue .stage-head,
        .stage-card.theme-green .stage-head,
        .stage-card.theme-amber .stage-head,
        .stage-card.theme-purple .stage-head,
        .stage-card.theme-rose .stage-head {
            background: var(--stage-head-bg);
            border-bottom-color: var(--stage-border);
            color: var(--stage-head-fg)
        }

        .icp-readonly-value {
            min-height: 38px;
            padding: .375rem .75rem;
            border-radius: .25rem;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            font-size: .875rem;
            white-space: pre-wrap;
        }
    </style>
@endpush

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

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Approval ICP List</h3>
            </div>

            <div class="card-body">
                @php
                    // Kelompokkan per karyawan (1 baris per ICP yang sedang menunggu action kamu)
                    $grouped = ($steps ?? collect())->groupBy(fn($s) => $s->icp->employee->id);
                    $no = 1;
                @endphp

                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grouped as $empId => $empSteps)
                            @php
                                /** @var \App\Models\IcpApprovalStep $step */
                                $step = $empSteps->first(); // step yang pending untuk role-mu
                                $icp = $step->icp;
                                $employee = $icp->employee;

                                // Aman untuk berbagai struktur organisasi
                                $deptName =
                                    $employee->department->name ??
                                    (optional(optional($employee->subSection)->section)->department->name ?? '-');

                                // Label step yang sedang menunggu (contoh: "Checking by GM")
                                $pendingLabel = $step->label ?? 'Pending';
                            @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $employee->npk ?? '-' }}</td>
                                <td>
                                    {{ $employee->name ?? '-' }}
                                    <div class="small text-muted">{{ $pendingLabel }}</div>
                                </td>
                                <td>{{ $deptName }}</td>
                                <td>{{ $employee->position ?? '-' }}</td>
                                <td class="text-center">
                                    {{-- Tombol LIHAT (buka modal dengan ICP ID) --}}
                                    <button type="button" class="btn btn-sm btn-primary btn-lihat"
                                        data-url="{{ route('icp.show-modal', ['icp' => $icp->id]) }}">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>

                                    {{-- Tombol REVISE --}}
                                    <button class="btn btn-sm btn-danger btn-revise" data-id="{{ $icp->id }}">
                                        <i class="fas fa-edit"></i> Revise
                                    </button>

                                    {{-- Approve step ini --}}
                                    <button class="btn btn-sm btn-success btn-approve" data-icp-id="{{ $icp->id }}">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada ICP yang menunggu approval.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Lihat ICP (wrapper; isi body akan di-load via AJAX) --}}
    <div class="modal fade" id="modalLihatIcp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail ICP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-0">Memuat data...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ================== LIHAT (SHOW MODAL) ==================
            const modalEl = document.getElementById('modalLihatIcp');
            const modalBody = modalEl.querySelector('.modal-body');
            const bsModal = new bootstrap.Modal(modalEl);

            document.querySelectorAll('.btn-lihat').forEach(btn => {
                btn.addEventListener('click', () => {
                    const url = btn.dataset.url;
                    if (!url) return;

                    // Placeholder saat loading
                    modalBody.innerHTML = '<p class="text-center mb-0">Loading...</p>';
                    bsModal.show();

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.text())
                        .then(html => {
                            modalBody.innerHTML = html;
                        })
                        .catch(() => {
                            modalBody.innerHTML =
                                '<div class="alert alert-danger mb-0">Gagal memuat data ICP.</div>';
                        });
                });
            });

            // ================== APPROVE ==================
            document.querySelectorAll('.btn-approve').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.icpId;
                    Swal.fire({
                        title: 'Approve this data?',
                        text: 'Are you sure you want to approve this employee?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, approve it!',
                        cancelButtonText: 'Cancel'
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch(`icp/${id}`, {
                                    method: 'GET',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    }
                                })
                                .then(r => r.json())
                                .then(d => {
                                    Swal.fire({
                                        title: 'Approved!',
                                        text: d.message || 'Approved.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => location.reload(), 1200);
                                })
                                .catch(() => Swal.fire('Error!', 'Something went wrong.',
                                    'error'));
                        }
                    });
                });
            });

            // ================== REVISE ==================
            document.querySelectorAll('.btn-revise').forEach(btn => {
                btn.addEventListener('click', () => {
                    Swal.fire({
                        title: 'Enter reason for revision',
                        input: 'textarea',
                        inputPlaceholder: 'Write your reason here...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: v => !v ? 'You need to write something!' : null
                    }).then(res => {
                        if (res.isConfirmed) {
                            const id = btn.dataset.id;
                            Swal.fire({
                                title: 'Submitting...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            fetch('icp/revise', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        id,
                                        comment: res.value
                                    })
                                })
                                .then(r => r.json())
                                .then(d => {
                                    Swal.fire({
                                        title: 'Revised!',
                                        text: d.message,
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => location.reload(), 1200);
                                })
                                .catch(() => Swal.fire('Error!', 'Something went wrong.',
                                    'error'));
                        }
                    });
                });
            });
        });
    </script>
@endpush
