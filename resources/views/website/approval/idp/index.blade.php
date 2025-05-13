@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Approval' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Approval' }}
@endsection

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

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Approval IDP List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>

            <div class="card-body">
                @php
                    $groupedIdps = $idps->groupBy(fn($idp) => $idp->assessment->employee->id);
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
                            @php
                                $employee = $employeeIdps->first()->assessment->employee;
                            @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $employee->npk ?? '-' }}</td>
                                <td>{{ $employee->name ?? '-' }}</td>
                                <td>{{ $employee->department?->name ?? '-' }}</td>
                                <td>{{ $employee->position ?? '-' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger btn-revise">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning btn-export"
                                        onclick="window.location.href='{{ route('idp.exportTemplate', ['employee_id' => $employee->id]) }}'">
                                        <i class="fas fa-upload"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info btn-toggle-accordion"
                                        data-employee-id="{{ $employeeId }}"
                                        data-bs-target="#collapse{{ $employeeId }}" aria-expanded="false"
                                        aria-controls="collapse{{ $employeeId }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr id="collapse{{ $employeeId }}" class="collapse">
                                <td colspan="7">
                                    <div class="accordion accordion-icon-toggle" id="accordionFlush{{ $employeeId }}">
                                        @foreach ($employeeIdps as $index => $idp)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header"
                                                    id="flush-heading{{ $employeeId }}-{{ $index }}">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#flush-collapse{{ $employeeId }}-{{ $index }}"
                                                        aria-expanded="false"
                                                        aria-controls="flush-collapse{{ $employeeId }}-{{ $index }}">
                                                        {{ $idp->category }} - {{ $idp->development_program }}
                                                    </button>
                                                </h2>
                                                <div id="flush-collapse{{ $employeeId }}-{{ $index }}"
                                                    class="accordion-collapse collapse"
                                                    aria-labelledby="flush-heading{{ $employeeId }}-{{ $index }}"
                                                    data-bs-parent="#accordionFlush{{ $employeeId }}">
                                                    <div class="accordion-body">
                                                        <table class="table table-sm">
                                                            <tbody>
                                                                <tr>
                                                                    <td><strong>Category:</strong></td>
                                                                    <td>{{ $idp->category }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Program:</strong></td>
                                                                    <td>{{ $idp->development_program }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Target:</strong></td>
                                                                    <td>{{ $idp->development_target }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Date:</strong></td>
                                                                    <td>{{ $idp->date }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Score:</strong></td>
                                                                    <td>
                                                                        <span class="badge badge-danger">2</span>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>

                                                        <!-- Tombol Approve per IDP -->
                                                        <button class="btn btn-sm btn-success btn-approve"
                                                            data-idp-id="{{ $idp->id }}">
                                                            <i class="fas fa-check-circle"></i> Approve
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada IDP yang menunggu approval.</td>
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
    <script>
        $(document).ready(function() {
            // Menangani klik tombol untuk membuka/tutup detail karyawan
            $('.btn-toggle-accordion').click(function() {
                var target = $(this).attr(
                    'data-bs-target'); // Ambil target collapse berdasarkan data-bs-target
                var $targetCollapse = $(target); // Target collapse (accordion)

                // Cek apakah collapse sedang terbuka atau tidak
                if ($targetCollapse.hasClass('show')) {
                    // Jika collapse sedang terbuka, kita tutup
                    $targetCollapse.collapse('hide');
                    $(this).attr('aria-expanded', 'false'); // Update status aria-expanded ke false
                } else {
                    // Jika collapse sedang tertutup, kita buka
                    $targetCollapse.collapse('show');
                    $(this).attr('aria-expanded', 'true'); // Update status aria-expanded ke true
                }
            });

            // Menangani tombol untuk membuka/menutup accordion dalam detail IDP
            $('.accordion-button').click(function() {
                var collapseTarget = $(this).attr('data-bs-target');
                var collapseElement = $(collapseTarget);

                if (collapseElement.hasClass('show')) {
                    // Jika accordion sudah terbuka, kita tutup
                    collapseElement.collapse('hide');
                } else {
                    // Jika accordion tertutup, kita buka
                    collapseElement.collapse('show');
                }
            });

            // Menangani tombol approve jika diperlukan
            $('.btn-approve').click(function() {
                var idpId = $(this).data('idp-id');
                console.log('Approved IDP ID: ' + idpId);
                // Tambahkan aksi untuk approval (misalnya AJAX)
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-approve').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-idp-id');
                    console.log(id)

                    Swal.fire({
                        title: 'Approve this data?',
                        text: "Are you sure you want to approve this employee?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, approve it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`idp/${id}`)
                                .then(data => {
                                    Swal.fire({
                                        title: 'Approved!',
                                        text: data.message ||
                                            'The employee has been approved.',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    // Reload page setelah 1.5 detik (selesai swal)
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1500);

                                    // Optional: refresh data, disable button, etc.
                                })
                                .catch(error => {
                                    console.log('Error:', error);
                                    Swal.fire('Error!', 'Something went wrong.',
                                        'error');
                                });
                        }
                    });
                });
            });


            document.querySelectorAll('.btn-revise').forEach(button => {
                button.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Enter reason for revision',
                        input: 'text',
                        inputPlaceholder: 'Write your reason here...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: (value) => {
                            if (!value) return 'You need to write something!';
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Revised!',
                                text: 'Your revision note: ' + result.value,
                                icon: 'info',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // TODO: send revision reason to server
                        }
                    });
                });
            });
        });
    </script>
@endpush
