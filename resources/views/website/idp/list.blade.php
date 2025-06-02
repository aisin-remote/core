@extends('layouts.root.main')

@push('custom-css')
    <style>
        .legend-circle {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
        }
    </style>
@endpush

@section('title')
    {{ $title ?? 'IDP' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'IDP' }}
@endsection

<style>
    .table-responsive {
        overflow-x: auto;
        white-space: nowrap;
    }

    /* Make the Employee Name column sticky */
    .sticky-col {
        position: sticky;
        left: 0;
        background: white;
        z-index: 2;
        box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
    }

    .score {
        width: 55px;
    }
</style>

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">IDP List</h3>
                <div class="d-flex align-items-center">
                    <form method="GET" action="{{ url()->current() }}" class="d-flex mb-3">
                        <input type="text" id="searchInputEmployee" name="search" class="form-control me-2"
                            placeholder="Search..." style="width: 250px;" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary me-3" id="searchButton">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                    role="tablist" style="cursor:pointer">
                    {{-- Tab Show All --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'all' ? 'active' : '' }}"
                            href="{{ route('idp.list', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            Show All
                        </a>
                    </li>

                    {{-- Tab Dinamis --}}
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('idp.list', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                {{ $position }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <tr>
                        <th>No</th>
                        <th>Photo</th>
                        <th>NPK</th>
                        <th>Employee Name</th>
                        <th>Company</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Grade</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @forelse ($assessments as $index => $assessment)
                            @php
                                $hav = $assessment->hav; // karena hasMany
                                $employee = optional(optional($hav)->hav)->employee;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">
                                    <img src="{{ $employee?->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                        alt="Employee Photo" class="rounded" width="40" height="40"
                                        style="object-fit: cover;">
                                </td>
                                <td>{{ $employee?->npk ?? '-' }}</td>
                                <td>{{ $employee?->name ?? '-' }}</td>
                                <td>{{ $employee?->company_name ?? '-' }}</td>
                                <td>{{ $employee?->position ?? '-' }}</td>
                                <td>{{ $employee?->bagian ?? '-' }}</td>
                                <td>{{ $employee?->grade ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($employee)
                                        <a class="btn btn-info btn-sm history-btn" data-employee-id="{{ $employee->id }}"
                                            data-bs-toggle="modal" data-bs-target="#detailAssessmentModal">
                                            History
                                        </a>
                                    @else
                                        <span class="text-muted">No Employee</span>
                                    @endif

                                    <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $assessment->id }}">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailAssessmentModal" tabindex="-1" aria-labelledby="detailAssessmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="detailAssessmentModalLabel">History IDP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h1 class="text-center mb-4 fw-bold">History IDP</h1>

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
                                    <th class="text-center">Date</th>
                                    <th class="text-center" width="40%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.btn-delete').on('click', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This IDP will be permanently deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Deleting...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.ajax({
                            url: `/idp/delete/${id}`,
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr(
                                    'content') // ← CSRF Token
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message ||
                                        'IDP successfully deleted.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                setTimeout(() => location.reload(), 1500);
                            },
                            error: function() {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });

            $(document).on("click", ".history-btn", function(event) {
                event.preventDefault();

                let employeeId = $(this).data("employee-id");
                console.log("Fetching history for Employee ID:", employeeId); // Debug

                // Reset data modal sebelum request baru dilakukan
                $("#npkText").text("-");
                $("#positionText").text("-");
                $("#kt_table_assessments tbody").empty();

                $.ajax({
                    url: `/idp/history/${employeeId}`,
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

                        // Kosongkan tabel sebelum menambahkan data baru
                        $("#kt_table_assessments tbody").empty();

                        if (response.assessments.length > 0) {
                            response.assessments.forEach((assessment, index) => {
                                let row = `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${assessment.date}</td>
                <td class="text-center">
                    <a
                        class="btn btn-info btn-sm btn-idp-detail"
                        data-bs-toggle="modal"
                        data-bs-target="#notes_${response.employee.id}"
                    >
                        Detail
                    </a>
                </td>
            </tr>
        `;
                                $("#kt_table_assessments tbody").append(row);
                            });
                        } else {
                            $("#kt_table_assessments tbody").append(`
        <tr>
            <td colspan="3" class="text-center text-muted">No IDP found</td>
        </tr>
    `);
                        }


                        // Tampilkan modal setelah data dimuat
                        $("#detailAssessmentModal").modal("show");
                    },
                    error: function(error) {
                        console.error("Error fetching data:", error);
                        alert("Failed to load assessment data!");
                    }
                });
            });
            $(document).on('show.bs.modal', '.modal', function(event) {
                const modalId = $(this).attr('id');
                if (modalId.startsWith('notes_')) {
                    // Sembunyikan modal detailAssessmentModal secara sementara
                    $('.modal-backdrop').last().remove();

                    $('#detailAssessmentModal').modal('hide');
                }
            });

            // Ketika modal notes ditutup, tampilkan kembali modal detailAssessmentModal
            $(document).on('hidden.bs.modal', '.modal', function(event) {
                const modalId = $(this).attr('id');
                if (modalId.startsWith('notes_')) {
                    $('#detailAssessmentModal').modal('show');
                }
            });
        });
    </script>
@endpush
