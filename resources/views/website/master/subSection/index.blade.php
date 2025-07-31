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
                <h3 class="card-title">Sub Section List</h3>
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
                <table class="table table-hover table-bordered align-middle text-center" id="table-subSection">
                    <thead class="bg-primary">
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Sub Secion Name</th>
                            <th class="text-center">Section</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subSections as $subSection)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $subSection->name }}</td>
                                <td>{{ $subSection->section->name }}</td>
                                <td>{{ $subSection->leader->name }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editSubSectionModal{{ $subSection->id }}">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                            data-id="{{ $subSection->id }}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No employees found</td>
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

    <!-- Modal Tambah Department -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel">Add Sub Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('subSection.master.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Sub Section Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>



                        <div class="mb-3">
                            <label for="section_id" class="form-label">Section</label>
                            <select name="section_id" id="section_id" class="form-select" required>
                                <option value="">Pilih Section</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="section_id" class="form-label">Leader</label>
                            <select name="leader_id" id="section_id" class="form-select" required>
                                <option value="">Pilih Section</option>
                                @foreach ($leaders as $leader)
                                    <option value="{{ $leader->id }}">{{ $leader->name }}</option>
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
    @foreach ($subSections as $subSection)
        <div class="modal fade" id="editSubSectionModal{{ $subSection->id }}" tabindex="-1"
            aria-labelledby="editSubSectionLabel{{ $subSection->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('sub-section.master.update', $subSection->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editSubSectionLabel{{ $subSection->id }}">Edit Sub Section</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            {{-- Sub Section Name --}}
                            <div class="mb-3">
                                <label for="name{{ $subSection->id }}" class="form-label">Sub Section Name</label>
                                <input type="text" class="form-control" id="name{{ $subSection->id }}"
                                    name="name" value="{{ $subSection->name }}" required>
                            </div>

                            {{-- Section --}}
                            <div class="mb-3">
                                <label for="section_id{{ $subSection->id }}" class="form-label">Pilih Section</label>
                                <select name="section_id" id="section_id{{ $subSection->id }}" class="form-select"
                                    required>
                                    <option value="">Pilih Section</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}"
                                            {{ $subSection->section_id == $section->id ? 'selected' : '' }}>
                                            {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Leader --}}
                            <div class="mb-3">
                                <label for="leader_id{{ $subSection->id }}" class="form-label">Pilih Leader</label>
                                <select name="leader_id" id="leader_id{{ $subSection->id }}" class="form-select"
                                    required>
                                    <option value="">Pilih Leader</option>
                                    @foreach ($leaders as $leader)
                                        <option value="{{ $leader->id }}"
                                            {{ $subSection->leader_id == $leader->id ? 'selected' : '' }}>
                                            {{ $leader->name }}
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
            $('#table-subSection').DataTable({
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
            console.log("✅ Event Listeners Added Successfully");

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
                            form.action = `/master/subSection/delete/${employeeId}`;

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
