@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">HAV List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
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
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Last HAV</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $item)
                            <tr data-position="{{ $item->employee->position }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->employee->npk }}</td>
                                <td>{{ $item->employee->name }}</td>
                                <td>{{ $item->employee->company_name }}</td>
                                <td>{{ $item->employee->position }}</td>
                                <td>{{ $item->employee->department?->name }}</td>
                                <td>{{ $item->employee->grade }}</td>
                                <td><span class="badge badge-light-warning fs-7 fw-bold">{{ $item->quadran->name }}</span></td>
                                <td class="text-center">
                                    {{-- Summary --}}
                                    <a href="#" data-employee-id="{{ $item->employee->id }}"
                                        class="btn btn-info btn-sm history-btn">
                                        History
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Komentar -->
    <div class="modal fade" id="commentHistoryModal" tabindex="-1" aria-labelledby="commentHistoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentHistoryModalLabel">Comment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <!-- Ganti bagian ini sesuai kebutuhan -->
                    <ul class="list-group" id="commentList">
                        <li class="list-group-item">
                            <strong>Dedi:</strong> Dokumen kurang lengkap
                            <br><small class="text-muted">01 Mei 2025 - 10:15</small>
                        </li>
                        <li class="list-group-item">
                            <strong>Aristoni:</strong> Dokumen tidak terbaca.
                            <br><small class="text-muted">02 Mei 2025 - 14:22</small>
                        </li>
                        <!-- Tambahan komentar lain -->
                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import HAV Employee Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
    
                <div class="modal-body">
                    <form id="importForm" action="{{ route('hav.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
    
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Pilih File Excel</label>
                            <input type="file" name="file" id="importFile" class="form-control" accept=".xlsx, .xls" required>
                            <small class="form-text text-muted">Format yang diperbolehkan: .xlsx atau .xls</small>
                        </div>
    
                        <div class="alert alert-info small">
                            <strong>Petunjuk:</strong> Gunakan format Excel yang sudah ditentukan.<br>
                            Download template format import:
                            <a href="{{ Storage::url('Import-HAV.xlsx') }}" target="_blank" class="fw-bold text-primary text-decoration-underline">Download Template</a>
                        </div>
    
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Upload</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="havDetail" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-900px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2 id="nameTitle">Herizal Arfiansyah</h2>
                    <!--end::Modal title-->

                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->

                <!--begin::Modal body-->
                <div class="modal-body py-lg-10 px-lg-10">
                    <!--begin::Stepper-->
                    <div class="stepper stepper-pills stepper-column d-flex flex-column flex-xl-row flex-row-fluid"
                        id="kt_modal_create_app_stepper">

                        <!--begin::Content-->
                        <div class="flex-row-fluid py-lg-5 px-lg-15">
                            <!--begin::Form-->
                            <form class="form" novalidate="novalidate" id="kt_modal_create_app_form">
                                <!--begin::Step 1-->
                                <div class="current" data-kt-stepper-element="content">
                                    <div class="w-100" style="margin-right: 10px;">

                                        <!--begin::Input group-->
                                        <div class="fv-row">
                                            <!--begin::Label-->
                                            <label class="d-flex align-items-center fs-5 fw-semibold mb-4">
                                                <span class="required">Astra Leadership Competency Score</span>


                                                <span class="ms-1" data-bs-toggle="tooltip"
                                                    title="Select your app category">
                                                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6"><span
                                                            class="path1"></span><span class="path2"></span><span
                                                            class="path3"></span></i></span> </label>
                                            <!--end::Label-->

                                            <!--begin:Options-->
                                            <div class="fv-row">
                                                <table class="table table-bordered" border="1" cellpadding="8"
                                                    cellspacing="0">
                                                    <thead>
                                                        <tr>
                                                            <th>ALC</th>
                                                            <th>Score</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Vision & Business Sense</td>
                                                            <td id="alc1">2</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Customer Focus</td>
                                                            <td id="alc2">3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Interpersonal Skill</td>
                                                            <td id="alc3">3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Analysis & Judgment</td>
                                                            <td id="alc4">3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Planning & Driving Action</td>
                                                            <td id="alc5">3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Leading & Motivating</td>
                                                            <td id="alc6">3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Teamwork</td>
                                                            <td id="alc7">3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Drive & Courage</td>
                                                            <td id="alc8">3</td>
                                                        </tr>
                                                    </tbody>
                                                </table>


                                            </div>
                                            <!--end:Options-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="w-100" style="margin-left: 10px;">

                                        <label class="d-flex align-items-center fs-5 fw-semibold mb-4">
                                            <span class="required">PK Last 3 Years</span>


                                            <span class="ms-1" data-bs-toggle="tooltip"
                                                title="Select your app category">
                                                <i class="ki-duotone ki-information-5 text-gray-500 fs-6"><span
                                                        class="path1"></span><span class="path2"></span><span
                                                        class="path3"></span></i></span> </label>


                                        <!--begin::Input group-->
                                        <div class="fv-row">

                                            <table class="table table-bordered" border="1" cellpadding="8"
                                                cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>PK</th>
                                                        <th>Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="performanceBody">
                                                </tbody>
                                            </table>
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
<div class="modal fade" id="detailAssessmentModal" tabindex="-1" aria-labelledby="detailAssessmentModalLabel"
aria-hidden="true">
<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title fw-bold" id="detailAssessmentModalLabel">History Assessment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <h1 class="text-center mb-4 fw-bold">History HAV</h1>

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
                            <th class="text-center">Quadran</th>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@push('scripts')
    <!-- jQuery dulu -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        
        $(document).ready(function() {
                $(document).on("click", ".history-btn", function(event) {
                    event.preventDefault();

                    let employeeId = $(this).data("employee-id");
                    console.log("Fetching history for Employee ID:", employeeId); // Debug

                    // Reset data modal sebelum request baru dilakukan
                    $("#npkText").text("-");
                    $("#positionText").text("-");
                    $("#kt_table_assessments tbody").empty();

                    $.ajax({
                        url: `/hav/history/${employeeId}`,
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

                            if (response.employee.hav.length > 0) {
                                response.employee.hav.forEach((hav, index) => {
                                    let row = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${hav.status || '-'}</td>
                            <td class="text-center">${hav.year}</td>
                            <td class="text-center">
                                <a
                                data-detail='${JSON.stringify(hav.details)}' 
                                data-tahun='${hav.year}'  
                                data-nama='${response.employee.name}' 
                                data-employeeid='${response.employee.id}' 
                                class="btn btn-info btn-sm btn-hav-detail" href="#">
                                    Detail
                                </a>
                              ${`<a class="btn btn-primary btn-sm"
                                    target="_blank"
                                    href="${hav.upload ? `/storage/${hav.upload}` : '#'}"
                                    onclick="${!hav.upload ? `event.preventDefault(); Swal.fire('Data tidak tersedia');` : ''}">
                                    Revise
                                </a>`}



                                            <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                data-id="${hav.id}">Delete</button>
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
                            alert("Failed to load assessment data!");
                        }
                    });
                });

                $(document).on("click", ".btn-hav-detail", function() {
                    event.preventDefault();


                    const havDetails = $(this).data("detail");
                    const employee_id = $(this).data("employeeid");
                    const year = $(this).data("tahun");
                    const nama = $(this).data("nama");
                    $(`#nameTitle`).text(nama + ' - ' + year);
                    console.log("Employee ID:", employee_id); // Debug

                    // Ambil data dari atribut data-upload
                    let url = "{{ url('/hav/get3-last-performance') }}/" + employee_id + "/" + year;
                    $.ajax({
                        url: url,
                        type: "GET",
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            console.log("Response received:", response); 
                            // Debug respons
                            let rows = '';
                                response.performanceAppraisals.forEach(function(item) {
                                    rows += `
                                        <tr>
                                            <td>${new Date(item.date).getFullYear()}</td>
                                            <td>${item.score}</td>
                                        </tr>
                                    `;
                                });
                            $('#performanceBody').html(rows);
                        },
                        error: function(xhr, status, error) {
                            rows = `
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No performance data found</td>
                                </tr>
                            `;
                            $('#performanceBody').html(rows);
                        }
                    });


                    // Cek apakah data ada
                    if (havDetails) {
                        try {

                            havDetails.forEach((item) => {
                                console.log(item.alc_id);
                                $(`#alc${item.alc_id}`).text(item.score);
                            });


                            // Tampilkan modal setelah data dimuat
                            $("#detailAssessmentModal").modal("hide");
                            $("#havDetail").modal("show");
                        } catch (error) {
                            console.error("Error parsing data:", error);
                            alert("Data tidak valid.");
                        }
                    } else {
                        alert("Data HAV tidak ditemukan.");
                    }
                   
                });

                // Pastikan overlay baru dibuat saat modal update ditutup dan kembali ke modal history
                $("#havDetail").on("hidden.bs.modal", function() {
                    setTimeout(() => {
                        $(".modal-backdrop").remove(); // Hapus overlay modal update
                        $("body").removeClass("modal-open");

                        $("#detailAssessmentModal").modal("show");

                        // Tambahkan overlay kembali untuk modal history
                        $("<div class='modal-backdrop fade show'></div>").appendTo(document.body);
                    }, 300);
                });

                $("#detailAssessmentModal").on("hidden.bs.modal", function() {
                    setTimeout(() => {
                        if (!$("#havDetail").hasClass("show")) {
                            $(".modal-backdrop").remove(); // Pastikan tidak ada overlay tertinggal
                            $("body").removeClass("modal-open");
                        }
                    }, 30);
                });

                // ===== CEGAH OVERLAY BERLAPIS =====
                $(".modal").on("shown.bs.modal", function() {
                    $(".modal-backdrop").last().css("z-index",
                        1050); // Atur overlay agar tidak bertumpuk terlalu tebal
                });

                $(document).on("click", ".delete-btn", function() {
                    let assessmentId = $(this).data("id");
                    console.log("ID yang akan dihapus:", assessmentId); // Debugging

                    if (!assessmentId) {
                        console.error("ID Hav tidak ditemukan!");
                        return;
                    }

                    Swal.fire({
                        title: "Apakah Anda yakin?",
                        text: "Data Hav ini akan dihapus secara permanen!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ya, Hapus!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log("Mengirim request DELETE untuk ID:", assessmentId); // Debugging

                            fetch(`/hav/${assessmentId}`, {
                                    method: "DELETE",
                                    headers: {
                                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                            "content"),
                                        "Content-Type": "application/json"
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log("Response dari server:", data); // Debugging

                                    if (data.success) {
                                        Swal.fire("Terhapus!", data.message, "success")
                                            .then(() => location.reload());
                                    } else {
                                        Swal.fire("Error!", "Gagal menghapus data!", "error");
                                    }
                                })
                                .catch(error => {
                                    console.error("Error saat menghapus:", error);
                                    Swal.fire("Error!", "Terjadi kesalahan!", "error");
                                });
                        }
                    });
                });

            });
    </script>
@endpush
