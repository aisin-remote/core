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
                <h3 class="card-title">Section List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
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
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Name Section</th>
                            <th>Department</th>
                             <th>Company</th>
                              <th>Name</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sections as $section)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $section->name }}</td>
                                <td>{{ $section->department->name }}</td>
                                 <td>{{ $section->company }}</td>
                                   <td>{{ $section->supervisor->name }}</td>

                                <td class="text-center">
                                     <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editSectionModal{{ $section->id }}">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn"
                                        data-id="{{ $section->id }}">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No employees found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-4">
                    {{ $sections->links('vendor.pagination.bootstrap-5') }}
                </div>
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
                    <h5 class="modal-title" id="addDepartmentModalLabel">Add Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('section.master.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Section Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>



                        <div class="mb-3">
                            <label for="department_id" class="form-label">Pilih Department</label>
                            <select name="department_id" id="department_id" class="form-select" required>
                                <option value="">Pilih Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="company" class="form-label">Company</label>
                            <select name="company" id="company" class="form-select" required>
                                <option value="">Pilih Company</option>
                                <option value="AII">AII</option>
                                <option value="AIIA">AIIA</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="supervisor_id" class="form-label">Pilih Supervisor</label>
                            <select name="supervisor_id" id="supervisor_id" class="form-select" required>
                                <option value="">Pilih Supervisor</option>
                                @foreach ($supervisors as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
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
    @foreach ($sections as $section)
        <div class="modal fade" id="editSectionModal{{ $section->id }}" tabindex="-1"
            aria-labelledby="editSectionLabel{{ $section->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('section.master.update', $section->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editSectionLabel{{ $section->id }}">Edit Section</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            {{-- Section Name --}}
                            <div class="mb-3">
                                <label for="name{{ $section->id }}" class="form-label">Section Name</label>
                                <input type="text" class="form-control" id="name{{ $section->id }}" name="name"
                                    value="{{ $section->name }}" required>
                            </div>

                            {{-- Department --}}
                            <div class="mb-3">
                                <label for="department_id{{ $section->id }}" class="form-label">Pilih Department</label>
                                <select name="department_id" id="department_id{{ $section->id }}" class="form-select"
                                    required>
                                    <option value="">Pilih Department</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ $section->department_id == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Company --}}
                            <div class="mb-3">
                                <label for="company{{ $section->id }}" class="form-label">Company</label>
                                <select name="company" id="company{{ $section->id }}" class="form-select" required>
                                    <option value="">Pilih Company</option>
                                    <option value="AII" {{ $section->company == 'AII' ? 'selected' : '' }}>AII</option>
                                    <option value="AIIA" {{ $section->company == 'AIIA' ? 'selected' : '' }}>AIIA
                                    </option>
                                </select>
                            </div>

                            {{-- Supervisor --}}
                            <div class="mb-3">
                                <label for="supervisor_id{{ $section->id }}" class="form-label">Pilih Supervisor</label>
                                <select name="supervisor_id" id="supervisor_id{{ $section->id }}" class="form-select"
                                    required>
                                    <option value="">Pilih Supervisor</option>
                                    @foreach ($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}"
                                            {{ $section->supervisor_id == $supervisor->id ? 'selected' : '' }}>
                                            {{ $supervisor->name }}
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
            console.log("✅ Script Loaded!");

            var searchInput = document.getElementById("searchInput");
            var filterItems = document.querySelectorAll(".filter-department");
            var table = document.getElementById("kt_table_users");

            if (!searchInput || !table) {
                console.error("⚠️ Elemen pencarian atau tabel tidak ditemukan!");
                return;
            }

            var tbody = table.getElementsByTagName("tbody")[0];
            var rows = tbody.getElementsByTagName("tr");

            function filterTable(selectedDepartment = "") {
                var searchValue = searchInput.value.toLowerCase();

                for (var i = 0; i < rows.length; i++) {
                    var cells = rows[i].getElementsByTagName("td");
                    var match = false;

                    if (cells.length >= 7) {
                        var npk = cells[1].textContent.toLowerCase();
                        var name = cells[2].textContent.toLowerCase();
                        var company = cells[3].textContent.toLowerCase();
                        var position = cells[4].textContent.toLowerCase();
                        var functionName = cells[5].textContent.toLowerCase();
                        var grade = cells[6].textContent.toLowerCase();
                        var age = cells[7].textContent.toLowerCase();

                        var searchMatch = npk.includes(searchValue) || name.includes(searchValue) ||
                            company.includes(searchValue) || position.includes(searchValue) ||
                            functionName.includes(searchValue) || grade.includes(searchValue) ||
                            age.includes(searchValue);

                        var departmentMatch = selectedDepartment === "" || functionName === selectedDepartment;

                        if (searchMatch && departmentMatch) {
                            match = true;
                        }
                    }

                    rows[i].style.display = match ? "" : "none";
                }
            }

            // Event Pencarian
            searchInput.addEventListener("keyup", function() {
                filterTable();
            });

            // Event Filter Dropdown
            filterItems.forEach(item => {
                item.addEventListener("click", function(event) {
                    event.preventDefault();
                    var selectedDepartment = this.getAttribute("data-department").toLowerCase();
                    console.log("🔍 Filter dipilih: ", selectedDepartment);
                    filterTable(selectedDepartment);
                });
            });

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
                            form.action = `/master/section/delete/${employeeId}`;

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
