@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'ICP' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'ICP' }}
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
                <h3 class="card-title">ICP List</h3>
                <div class="d-flex align-items-center">
                    {{-- <form method="GET" action="#" class="d-flex align-items-center">
                        <input type="text" name="search" id="searchInput" class="form-control me-2"
                            placeholder="Search Employee..." style="width: 200px;" value="{{ request('search') }}">

                        <button type="submit" class="btn btn-primary me-3" id="searchButton">
                            <i class="fas fa-search"></i> Search
                        </button>

                    </form> --}}
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                    role="tablist" style="cursor:pointer">
                    {{-- Tab Show All --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4
                            {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('employee.index', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            Show All
                        </a>
                    </li>

                    {{-- Tab Dinamis --}}
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('employee.index', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                {{ $position }}
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
                        @forelse ($icps as $index => $icp)
                            @php $employee = $icp->employee; @endphp

                            @php
                                $unit = match ($employee->position) {
                                    'Direktur' => $employee->plant?->name,
                                    'GM', 'Act GM' => $employee->division?->name,
                                    default => $employee->department?->name,
                                };
                            @endphp
                            <tr data-position="{{ $employee->position }}">
                                <td>{{ $icps->firstItem() + $index }}</td>

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
                                {{-- @if (auth()->user()->role == 'HRD')
                                        <a href="{{ route('employee.edit', $employee->npk) }}"
                                            class="btn btn-warning btn-sm">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                    @endif --}}
                                <td class="text-center">
                                    {{-- Summary --}}
                                    <a href="#" data-employee-id="{{ $employee->id }}"
                                        class="btn btn-info btn-sm history-btn">
                                        History
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
                <div class="d-flex justify-content-end mt-4">
                    {{ $icps->links('pagination::bootstrap-5') }}
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted fw-bold">
                        Catatan: Hubungi HRD Human Capital jika data karyawan yang dicari tidak tersedia.
                    </small>
                </div>
            </div>


        </div>
    </div>
    <div class="modal fade" id="detailAssessmentModal" tabindex="-1" aria-labelledby="detailAssessmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="detailAssessmentModalLabel">History ICP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h1 class="text-center mb-4 fw-bold">History ICP</h1>

                    <div class="row mb-3 d-flex justify-content-end align-items-center gap-4">
                        <div class="col-auto">
                            <p class="fs-5 fw-bold"><strong>NPK:</strong><span id="npkText"></span></p>
                        </div>
                        <div class="col-auto">
                            <p class="fs-5 fw-bold"><strong>Position:</strong> <span id="positionText"></span></p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle table-hover fs-6"
                            id="kt_table_assessments" width="100%">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" width="10%">No</th>
                                    <th class="text-center">Aspiration</th>
                                    <th class="text-center">Career Target</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center" width="40%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center w-100">
                    <small class="text-muted fw-bold m-0">
                        Catatan: Hubungi HRD Human Capital jika data karyawan yang dicari tidak tersedia.
                    </small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    $(document).on("click", ".history-btn", function(event) {
        event.preventDefault();

        let employeeId = $(this).data("employee-id");
        console.log("Fetching history for Employee ID:", employeeId); // Debug

        // Reset data modal sebelum request baru dilakukan
        $("#npkText").text("-");
        $("#positionText").text("-");
        $("#kt_table_assessments tbody").empty();

        $.ajax({
            url: `/icp/history/${employeeId}`,
            type: "GET",
            success: function(response) {
                console.log("Response received:", response); // Debug respons

                if (!response.employee) {
                    console.error("Employee data not found in response!");
                    alert("Employee not found!");
                    return;
                }
                // Update informasi karyawan
                $("#npkText").text(response.employee.npk);
                $("#positionText").text(response.employee.position);
                const currentUserRole = "{{ auth()->user()->role }}";

                // Kosongkan tabel sebelum menambahkan data baru
                $("#kt_table_assessments tbody").empty();

                if (response.employee.icp.length > 0) {
                    response.employee.icp.forEach((icp, index) => {
                        let deleteButton = '';

                        let row = `
        <tr>
            <td class="text-center">${index + 1}</td>
            <td class="text-center">${icp.aspiration|| '-'}</td>
            <td class="text-center">${icp.career_target}</td>
             <td class="text-center">${icp.date}</td>
            <td class="text-center">

                ${deleteButton}
            </td>
        </tr>
        `;
                        $("#kt_table_assessments tbody").append(row);
                    });
                } else {
                    $("#kt_table_assessments tbody").append(`
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No assessment found</td>
                                    </tr>
                                `);
                }

                // Tampilkan modal setelah data dimuat
                $("#detailAssessmentModal").modal("show");
            },
            error: function(error) {
                console.error("Error fetching data:", error);
                alert("Failed to load icp data!");
            }
        });
    });
</script>
@endpush
