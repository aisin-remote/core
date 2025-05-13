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
    <div id="kt_app_content_container" class="app-container  container-fluid ">
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
                @if (auth()->user()->role == 'HRD')
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                        role="tablist" style="cursor:pointer">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 active filter-tab" data-filter="all">Show All</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="Manager">Manager</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="Supervisor">Supervisor</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="Leader">Leader</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="JP">JP</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="Operator">Operator</a>
                        </li>
                    </ul>
                @endif
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>000017</td>
                            <td>Ferry Avianto</td>
                            <td>AIIA</td>
                            <td>Manager</td>
                            <td>ITD</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-success btn-approve">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-warning btn-revise">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-info btn-view">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>000024</td>
                            <td>Junjunan Tri Setia</td>
                            <td>AIIA</td>
                            <td>Manager</td>
                            <td>Management System</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-success btn-approve">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-warning btn-revise">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-info btn-view">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>

                </table>
                <div class="d-flex justify-content-end mt-4">
                    {{-- {{ $employees->links('pagination::bootstrap-5') }} --}}
                </div>
            </div>

        </div>


    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const approveButtons = document.querySelectorAll('.btn-approve');

            approveButtons.forEach(function(button) {
                button.addEventListener('click', function() {
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
                            Swal.fire({
                                title: 'Approved!',
                                text: 'The employee has been approved.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Optionally trigger a real approval action here, like AJAX
                        }
                    });
                });
            });

            // Revise button with input
            document.querySelectorAll('.btn-revise').forEach(function(button) {
                button.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Enter reason for revision',
                        input: 'text',
                        inputPlaceholder: 'Write your reason here...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'You need to write something!';
                            }
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

                            // Di sini bisa kirim hasil revisi ke server via AJAX, dll.
                            console.log("Reason for revision:", result.value);
                        }
                    });
                });
            });
        });
    </script>
@endpush
