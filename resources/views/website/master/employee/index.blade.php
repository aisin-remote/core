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
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
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
                @if (auth()->user()->role == 'HRD')
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                    role="tablist" style="cursor:pointer">
                    <li class="nav-item" role="presentation">
                    <a class="nav-link text-active-primary pb-4 {{ $filter == 'all' ? 'active' : '' }}"
                        href="{{ route('employee.master.index', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                        Show All
                    </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'Direktur' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company, 'search' => request('search'), 'filter' => 'Direktur']) }}">
                            Direktur
                        </a>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'GM' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company,'search' => request('search'), 'filter' => 'GM']) }}">GM</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'Manager' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company,'search' => request('search'), 'filter' => 'Manager']) }}">Manager</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'Coordinator' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company,'search' => request('search'), 'filter' => 'Coordinator']) }}">Coordinator</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'Section Head' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company,'search' => request('search'), 'filter' => 'Section Head']) }}">Section
                            Head</a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'Supervisor' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company,'search' => request('search'), 'filter' => 'Supervisor']) }}">Supervisor</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'Leader' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company,'search' => request('search'), 'filter' => 'Leader']) }}">Leader</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'JP' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company,'search' => request('search'), 'filter' => 'JP']) }}">JP</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'Operator' ? 'active' : '' }}"
                            href="{{ route('employee.master.index', ['company' => $company,'search' => request('search'), 'filter' => 'Operator']) }}">Operator</a>
                    </li>
                </ul>
            @endif
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Photo</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Age</th>
                            <th class="text-center">Actions</th>
                            <th class="text-center">Status</th> {{-- Kolom Status --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employee as $index => $employee)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-center">
                                    <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                        alt="Employee Photo" class="rounded" width="40" height="40"
                                        style="object-fit: cover;">
                                </td>
                                <td>{{ $employee->npk }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->company_name }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $employee->department?->name }}</td>
                                <td>{{ $employee->grade }}</td>
                                <td>{{ \Carbon\Carbon::parse($employee->birthday_date)->age }}</td>
                                <td class="text-center">
                                    <a href="{{ route('employee.edit', $employee->npk) }}"
                                        class="btn btn-warning btn-sm"><i class="fa fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn"
                                        data-id="{{ $employee->npk }}"><i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                        class="btn {{ $employee->is_active ? 'btn-light-success' : 'btn-light-danger' }} btn-sm status-btn"
                                        data-id="{{ $employee->id }}" data-name="{{ $employee->name }}"
                                        data-status="{{ $employee->is_active ? 'Non Active' : 'Active' }}">
                                        {{ $employee->is_active ? 'Active' : 'Non Active' }}
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


            document.querySelectorAll('.status-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let employeeId = this.getAttribute('data-id');
                    let employeeName = this.getAttribute('data-name');
                    let newStatus = this.getAttribute('data-status');

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
                            let form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/employee/status/${employeeId}`;

                            let csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';

                            form.appendChild(csrfToken);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });


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
                            form.action = `/employee/${employeeId}`;

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
@endsection
