@extends('layouts.root.main')

@section('title', $title ?? 'Approval')
@section('breadcrumbs', $title ?? 'Approval')

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
                                    <a href="{{ route('icp.export', ['employee_id' => $employee->id]) }}"
                                        class="btn btn-sm btn-success">
                                        <i class="fas fa-file-excel"></i> Export
                                    </a>

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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Approve
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

            // Revise
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
