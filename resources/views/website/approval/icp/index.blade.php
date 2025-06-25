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
                {{-- <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div> --}}
            </div>

            <div class="card-body">
                @php
                    $groupedIdps = $idps->groupBy(fn($idp) => $idp->employee->id);
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
                        @forelse($groupedIdps as $employeeId => $employeeIdps)
                            @php $employee = $employeeIdps->first()->employee; @endphp
                            @php $firstIdp = $employeeIdps->first(); @endphp
                            <tr>

                                <td>{{ $no++ }}</td>
                                <td>{{ $employee->npk ?? '-' }}</td>
                                <td>{{ $employee->name ?? '-' }}</td>
                                <td>{{ $employee->bagian ?? '-' }}</td>
                                <td>{{ $employee->position ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('icp.export', ['employee_id' => $firstIdp->employee_id]) }}"
                                        class="btn btn-sm btn-success">
                                        <i class="fas fa-file-excel"></i> Export
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-revise" data-id="{{ $firstIdp->id }}">
                                        <i class="fas fa-edit"></i> Revise
                                    </button>
                                    <button class="btn btn-sm btn-success btn-approve" data-idp-id="{{ $firstIdp->id }}">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </button>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada IDP yang menunggu approval.
                                </td>
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
        function toggleAccordion(id) {
            const el = document.getElementById(id);
            if (el.classList.contains('show')) {
                bootstrap.Collapse.getInstance(el)?.hide();
            } else {
                new bootstrap.Collapse(el);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Toggle accordion
            document.querySelectorAll('.btn-toggle-accordion').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-bs-target');
                    const row = document.querySelector(targetId);
                    document.querySelectorAll('.accordion-collapse').forEach(el => el.classList
                        .remove('show'));
                    row.classList.add('show');
                });
            });

            // Approve button
            document.querySelectorAll('.btn-approve').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.dataset.idpId;

                    Swal.fire({
                        title: 'Approve this data?',
                        text: "Are you sure you want to approve this employee?",
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
                                            'meta[name="csrf-token"]').getAttribute(
                                            'content')
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    Swal.fire({
                                        title: 'Approved!',
                                        text: data.message ||
                                            'The employee has been approved.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => location.reload(), 1500);
                                })
                                .catch(() => {
                                    Swal.fire('Error!', 'Something went wrong.',
                                        'error');
                                });
                        }
                    });
                });
            });

            // Revise button
            document.querySelectorAll('.btn-revise').forEach(button => {
                button.addEventListener('click', () => {
                    Swal.fire({
                        title: 'Enter reason for revision',
                        input: 'textarea',
                        inputPlaceholder: 'Write your reason here...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: value => !value ? 'You need to write something!' :
                            null
                    }).then(result => {
                        if (result.isConfirmed) {
                            const id = button.dataset.id;
                            const revisionReason = result.value;

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
                                            'meta[name="csrf-token"]').getAttribute(
                                            'content')
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
                                        text: data.message,
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => location.reload(), 1500);
                                })
                                .catch(() => {
                                    Swal.fire('Error!', 'Something went wrong.',
                                        'error');
                                });
                        }
                    });
                });
            });
        });
    </script>
@endpush
