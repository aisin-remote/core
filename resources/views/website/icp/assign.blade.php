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
                <h3 class="card-title">ICP Assign</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;" value="{{ request('search') }}">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                    {{-- <button type="button" class="btn btn-info me-3" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_create_app">
                        <i class="fas fa-upload"></i>
                        Import2
                    </button> --}}
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-5 fw-semibold mb-4" role="tablist"
                    style="cursor:pointer">
                    {{-- Tab Show All --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('icp.assign', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>

                    {{-- Tab Dinamis Berdasarkan Posisi --}}
                    @foreach ($visiblePositions as $position)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                    href="{{ route('icp.assign', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                    <i class="fas fa-user-tag me-2"></i>{{ $position }}
                                </a>
                            </li>
                    @endforeach
                </ul>

                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($icps as $item)
                            <tr data-position="{{ $item->position }}">

                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->npk }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->company_name }}</td>
                                <td>{{ $item->position }}</td>
                                <td>{{ $item->department?->name }}</td>
                                <td>{{ $item->grade }}</td>
                                @php
                                    $latestIcp = $item->icp->first(); // Ambil ICP terbaru dari koleksi
                                    $status = optional($latestIcp)->status;
                                @endphp

                                <td>
                                    <span class="badge badge-light-warning fs-7 fw-bold">
                                        @if ($status === 0)
                                            Revise
                                        @elseif ($status === 3 && \Carbon\Carbon::parse($latestIcp?->created_at)->addYear()->isPast())
                                            -
                                        @else
                                            {{ match ($status) {
                                                1 => 'Submited',
                                                2 => 'Checked',
                                                3 => 'Approved',
                                                default => '-',
                                            } }}
                                        @endif
                                    </span>
                                </td>


                                <td class="text-center">
                                    @php
                                        $latestIcp = $item->latestIcp;
                                        // gunakan $item, bukan $employee
                                        $status = $latestIcp?->status;
                                        $createdAt = $latestIcp?->created_at;
                                        $icpExpired =
                                            $status === 3 && \Carbon\Carbon::parse($createdAt)->addYear()->isPast();
                                        $noIcp = is_null($latestIcp);
                                    @endphp

                                    @if ($status === 3 || $noIcp)
                                        {{-- Tombol Add jika belum ada data atau sudah Approved lebih dari 1 tahun --}}
                                        <a href="{{ route('icp.create',  $item->id) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Add
                                        </a>
                                    @elseif ($status === 0)
                                        {{-- Revise + Export jika status 0 --}}
                                        <a href="{{ route('icp.edit', $latestIcp->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Revise
                                        </a>
                                        <a href="{{ route('icp.export', ['employee_id' => $item->id]) }}"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-file-excel"></i> Export
                                        </a>
                                    @elseif ($status === 1)
                                        {{-- Export saja jika status Submitted --}}
                                        <a href="{{ route('icp.export', ['employee_id' => $item->id]) }}"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-file-excel"></i> Export
                                        </a>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
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
