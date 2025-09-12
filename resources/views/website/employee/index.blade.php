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
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-5 fw-semibold mb-4" role="tablist"
                    style="cursor:pointer">
                    {{-- Tab Show All --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link fs-7 {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('employee.index', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>

                    {{-- Tab Dinamis Berdasarkan Posisi --}}
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-7 my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('employee.index', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                <i class="fas fa-user-tag me-2"></i>{{ $position }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Photo</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th> {{-- Tetap static --}}
                            <th>Grade</th>
                            <th>Age</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $index => $employee)
                            @php
                                $unit = match ($employee->position) {
                                    'Direktur' => $employee->plant?->name,
                                    'GM', 'Act GM' => $employee->division?->name,
                                    default => $employee->department?->name,
                                };
                            @endphp
                            <tr class="fs-7" data-position="{{ $employee->position }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                        alt="Employee Photo" class="rounded" width="40" height="40"
                                        style="object-fit: cover;">
                                </td>
                                <td>{{ $employee->npk }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->company_name }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $unit }}</td> {{-- Dinamis berdasarkan posisi --}}
                                <td>{{ $employee->grade }}</td>
                                <td>{{ \Carbon\Carbon::parse($employee->birthday_date)->age }}</td>
                                <td class="text-center">
                                    {{-- @if (auth()->user()->role == 'HRD')
                                        <a href="{{ route('employee.edit', $employee->npk) }}"
                                            class="btn btn-warning btn-sm">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                    @endif --}}
                                    <a href="{{ route('employee.show', $employee->npk) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-between">
                    <small class="text-muted fw-bold">
                        Catatan: Hubungi HRD Human Capital jika data karyawan yang dicari tidak tersedia.
                    </small>
                </div>
            </div>


        </div>
    </div>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Pastikan jQuery tersedia
            if (typeof $ === 'undefined') {
                console.error("jQuery not loaded. DataTable won't initialize.");
                return;
            }

            // Inisialisasi DataTable
            $('#kt_table_users').DataTable({
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
