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
                <h3 class="card-title">Division List</h3>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary me-3" data-bs-toggle="modal"
                        data-bs-target="#addDepartmentModal">
                        <i class="fas fa-plus"></i> Add
                    </button>
                    <button type="button" class="btn btn-info me-3" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload"></i>
                        Import
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="table-division">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Name Division</th>
                            <th>Plant</th>
                            <th>Name</th>
                            <th clas>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($divisions as $division)
                            <tr class="fs-7">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $division->name }}</td>
                                <td>{{ $division->plant->name }}</td>
                                <td>{{ $division->gm->name }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#editDivisionModal{{ $division->id }}">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                            data-id="{{ $division->id }}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
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

    <!-- Modal Tambah Department -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel">Add Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('division.master.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Division Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="plant_id" class="form-label">Pilih Plant</label>
                            <select name="plant_id" id="plant_id" class="form-select" required>
                                <option value="">Pilih Plant</option>
                                @foreach ($plants as $plant)
                                    <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="gm_id" class="form-label">Pilih GM</label>
                            <select name="gm_id" id="gm_id" class="form-select" required>
                                <option value="">Pilih GM</option>
                                @foreach ($gms as $gm)
                                    <option value="{{ $gm->id }}">{{ $gm->name }} - {{ $gm->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($divisions as $division)
        <div class="modal fade" id="editDivisionModal{{ $division->id }}" tabindex="-1"
            aria-labelledby="editDivisionLabel{{ $division->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('division.master.update', $division->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editDivisionLabel{{ $division->id }}">Edit Division</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name{{ $division->id }}" class="form-label">Division Name</label>
                                <input type="text" class="form-control" id="name{{ $division->id }}" name="name"
                                    value="{{ $division->name }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="plant_id{{ $division->id }}" class="form-label">Pilih Plant</label>
                                <select name="plant_id" id="plant_id{{ $division->id }}" class="form-select" required>
                                    <option value="">Pilih Plant</option>
                                    @foreach ($plants as $plant)
                                        <option value="{{ $plant->id }}"
                                            {{ $plant->id == $division->plant_id ? 'selected' : '' }}>
                                            {{ $plant->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="gm_id{{ $division->id }}" class="form-label">Pilih GM</label>
                                <select name="gm_id" id="gm_id{{ $division->id }}" class="form-select" required>
                                    <option value="">Pilih GM</option>
                                    @foreach ($gms as $gm)
                                        <option value="{{ $gm->id }}"
                                            {{ $gm->id == $division->gm_id ? 'selected' : '' }}>
                                            {{ $gm->name }} - {{ $gm->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <!-- Tambahkan SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Pastikan jQuery tersedia
            if (typeof $ === 'undefined') {
                console.error("jQuery not loaded. DataTable won't initialize.");
                return;
            }

            // Inisialisasi DataTable
            $('#table-division').DataTable({
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
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("✅ Script Loaded!");
            // SweetAlert untuk Delete Button
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let employeeId = this.getAttribute('data-id');

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
                            let form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/master/division/delete/${employeeId}`;

                            let csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';

                            let methodField = document.createElement('input');
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
        });
    </script>
@endpush
