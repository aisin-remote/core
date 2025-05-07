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
                            <th>Photo</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Age</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @forelse ($employees as $index => $employee)
                            <tr data-position="{{ $employee->position }}">
                                <td>{{ $employees->firstItem() + $index }}</td>

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
                        @endforelse --}}
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-4">
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>


    </div>
@endsection
