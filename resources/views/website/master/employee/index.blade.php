@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
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
    @if (session()->has('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "{{ session('error') }}",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Employee List</h3>
                <div class="d-flex align-items-center">
                    <a href="{{ route('employee.create') }}" class="btn btn-primary me-3">
                        <i class="fas fa-plus"></i>
                        Add
                    </a>
                    <button type="button" class="btn btn-info me-3" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload"></i>
                        Import
                    </button>
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-5 fw-semibold mb-4" role="tablist"
                    style="cursor:pointer">
                    {{-- Tab Show All --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>

                    {{-- Tab Dinamis Berdasarkan Posisi --}}
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('employee.master.index', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                <i class="fas fa-user-tag me-2"></i>{{ $position }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="text-center">No</th>
                            <th class="text-center">Photo</th>
                            <th class="text-center">NPK</th>
                            <th class="text-center">Employee Name</th>
                            <th class="text-center">Company</th>
                            <th class="text-center">Position</th>
                            <th class="text-center">Department</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Age</th>
                            <th class="text-center nowrap" style="min-width: 120px;">Actions</th>
                            <th class="text-center nowrap" style="min-width: 120px;">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($employee as $index => $employees)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    <img src="{{ $employees->photo ? asset('storage/' . $employees->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                        alt="Employee Photo" class="rounded" width="40" height="40"
                                        style="object-fit: cover;">
                                </td>
                                <td>{{ $employees->npk }}</td>
                                <td>{{ $employees->name }}</td>
                                <td>{{ $employees->company_name }}</td>
                                <td>{{ $employees->position }}</td>
                                <td>{{ $employees->bagian }}</td>
                                <td>{{ $employees->grade }}</td>
                                <td>{{ \Carbon\Carbon::parse($employees->birthday_date)->age }}</td>

                                {{-- ✅ Modified Action buttons --}}
                                <td class="text-center nowrap" style="min-width: 120px;">
                                    <a href="{{ route('employee.edit', $employees->npk) }}"
                                        class="btn btn-warning btn-sm me-1">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn"
                                        data-id="{{ $employees->id }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>


                                {{-- ✅ Modified Status Button --}}
                                <td class="text-center nowrap" style="min-width: 120px;">
                                    <button type="button"
                                        class="btn {{ $employees->is_active ? 'btn-light-success' : 'btn-light-danger' }} btn-sm btn-xs status-btn"
                                        data-id="{{ $employees->id }}" data-name="{{ $employees->name }}"
                                        data-status="{{ $employees->is_active ? 'Non Active' : 'Active' }}">
                                        {{ $employees->is_active ? 'Active' : 'Non Active' }}
                                    </button>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No employees found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Import Employee Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Employee Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('employee.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">Select Excel File</label>
                            <input type="file" name="file" id="file" class="form-control" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nowrap {
            white-space: nowrap;
        }

        .btn-xs {
            padding: 2px 6px;
            font-size: 12px;
        }
    </style>



    <!-- Tambahkan SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Pastikan jQuery terpasang (DataTables butuh jQuery)
            if (typeof $ === 'undefined') {
                console.error("jQuery not loaded. DataTable won't initialize.");
                return;
            }

            // Inisialisasi DataTable
            const dt = $('#kt_table_users').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No matching records found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        next: "Next",
                        previous: "Previous"
                    }
                },
                ordering: false
            });
            console.log("✅ DataTable Initialized Successfully");

            // DELEGATION: klik Status
            $('#kt_table_users').on('click', '.status-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const employeeId = $btn.data('id');
                const employeeName = $btn.data('name');
                const newStatus = $btn.data('status');

                Swal.fire({
                    title: "Are you sure?",
                    text: `Do you want to change the status of ${employeeName} to ${newStatus}?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, change it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/employee/status/${employeeId}`;

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        form.appendChild(csrfToken);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // DELEGATION: klik Delete
            $('#kt_table_users').on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const employeeId = this.getAttribute('data-id');

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/employee/${employeeId}`;

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';

                        form.appendChild(csrfToken);
                        form.appendChild(methodField);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
