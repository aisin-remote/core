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
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-end">
                        <i class="fas fa-filter"></i> Filter
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
                            <th>Photo</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Age</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $index => $employee)
                            <tr data-position="{{ $employee->position }}">
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
                                    <a href="{{ route('employee.edit', $employee->npk) }}" class="btn btn-warning btn-sm">
                                        <i class="fa fa-pencil-alt"></i>
                                    </a>
                                    <a href="{{ route('employee.show', $employee->npk) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No employees found</td>
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
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".filter-tab");
            const rows = document.querySelectorAll("#kt_table_users tbody tr");

            tabs.forEach(tab => {
                tab.addEventListener("click", function(e) {
                    e.preventDefault();

                    // Hapus class active dari semua tab
                    tabs.forEach(t => t.classList.remove("active"));
                    this.classList.add("active");

                    const filter = this.getAttribute("data-filter");

                    rows.forEach(row => {
                        const position = row.getAttribute("data-position");
                        if (filter === "all" || position === filter) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });
                });
            });
        });

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
    <!-- Tambahkan SweetAlert -->
@endpush
