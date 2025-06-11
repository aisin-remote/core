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
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                    role="tablist" style="cursor:pointer">
                    {{-- Tab Show All --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary pb-4
        {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            Show All
                        </a>
                    </li>
                    {{-- Tab Dinamis --}}
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
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
                                <td class="text-center">
                                    <img src="{{ $item->employee->photo ? asset('storage/' . $item->employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                        alt="Employee Photo" class="rounded" width="40" height="40"
                                        style="object-fit: cover;">
                                </td>
                                <td>{{ $item->employee->npk }}</td>
                                <td>{{ $item->employee->name }}</td>
                                <td>{{ $item->employee->company_name }}</td>
                                <td>{{ $item->employee->position }}</td>
                                <td>{{ $item->employee->bagian }}</td>
                                <td>{{ $item->employee->grade }}</td>
                                <td><span class="badge badge-light-warning fs-7 fw-bold">{{ $item->quadran->name ?? '-' }}
                                    </span>
                                </td>
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
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="commentHistoryModalLabel">Comment History</h5>

                    <div class="d-flex align-items-center gap-3">
                        <div id="lastUploadInfo" style="font-size: 0.875rem; color: #666;">
                            <!-- Last upload info akan diisi via JS -->
                        </div>

                        <a href="#" id="btnExportExcel" class="btn btn-success btn-sm" target="_blank"
                            style="padding: 0.80rem 0.5rem; font-size: 0.75rem;">
                            Export HAV
                        </a>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>

                <div class="modal-body">

                    <ul class="list-group" id="commentList">
                        <!-- Comments will be dynamically loaded here -->
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
                            <input type="file" name="file" id="importFile" class="form-control" accept=".xlsx, .xls"
                                required>
                            <small class="form-text text-muted">Format yang diperbolehkan: .xlsx atau .xls</small>
                        </div>

                        <div class="alert alert-info small">
                            <strong>Petunjuk:</strong> Gunakan format Excel yang sudah ditentukan.<br>
                            Download template format import:
                            <a href="{{ asset('assets/file/Import-HAV.xlsx') }}" target="_blank"
                                class="fw-bold text-primary text-decoration-underline">Download Template</a>
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
                    <h2 id="nameTitle"></h2>
                    <!--end::Modal title-->
                    <a href="#" id="btnExportExcel" class="btn btn-success btn-sm position-absolute"
                        style="top: 1rem; right: 8rem; padding: 0.8rem 0.5rem; font-size: 0.75rem; z-index: 1050;"
                        target="_blank">
                        Export HAV
                    </a>
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
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                confirmButtonText: 'Ok'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            console.log("Error session:", '{{ session('error') }}'); // Debugging
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
                confirmButtonText: 'Ok'
            });
        </script>
    @endif

    <script>
        $(document).ready(function() {
            function showCommentHistoryModal(response) {
                // Tampilkan last upload info (jika ada)
                if (response.lastUpload) {
                    const date = new Date(response.lastUpload.created_at);
                    const formattedDate = date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });

                    $('#lastUploadInfo').html(`Last Submit: <strong>${formattedDate}</strong>`);

                    // Update link download dengan havId yg sesuai
                    $('#btnExportExcel').attr('href', `/hav/download-upload/${response.hav.id}`);
                } else {
                    $('#lastUploadInfo').html('No uploads found');
                    $('#btnExportExcel').attr('href', '#');
                }

                // Render komentar dst...
            }


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
                        const titles = {
                            1: 'Star',
                            2: 'Future Star',
                            3: 'Future Star',
                            4: 'Potential Candidate',
                            5: 'Raw Diamond',
                            6: 'Candidate',
                            7: 'Top Performer',
                            8: 'Strong Performer',
                            9: 'Career Person',
                            10: 'Most Unfit Employee',
                            11: 'Unfit Employee',
                            12: 'Problem Employee',
                            13: 'Maximal Contributor',
                            14: 'Contributor',
                            15: 'Minimal Contributor',
                            16: 'Dead Wood'
                        };

                        // Update informasi karyawan
                        $("#npkText").text(response.employee.npk);
                        $("#positionText").text(response.employee.position);
                        const currentUserRole = "{{ auth()->user()->role }}";

                        // Kosongkan tabel sebelum menambahkan data baru
                        $("#kt_table_assessments tbody").empty();

                        if (response.employee.hav.length > 0) {
                            response.employee.hav.forEach((hav, index) => {
                                let deleteButton = '';
                                if (currentUserRole === 'HRD') {
                                    deleteButton =
                                        `<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="${hav.id}">Delete</button>`;
                                }

                                let row = `
        <tr>
            <td class="text-center">${index + 1}</td>
            <td class="text-center">${titles[hav.quadrant] || '-'}</td>
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
                <a
                    data-id="${hav.id}"
                    class="btn btn-primary btn-sm btn-hav-comment" href="#">
                    History
                </a>
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
                          showCommentHistoryModal(response);

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

            $(document).on("click", ".btn-hav-comment", function() {
                let hav_id = $(this).data("id"); // Get the employee ID from the button's data attribute
                console.log("Fetching comment history for Employee ID:", hav_id); // Debugging

                // Clear any previous comment history in the modal
                $("#commentList").empty();

                // Make an AJAX request to get the comment history
                $.ajax({
                    url: "{{ url('/hav/get-history') }}/" +
                        hav_id, // Change this URL to your correct route
                    type: "GET",
                    success: function(response) {
                        console.log("Response received:", response
                            .comment); // Debugging the response
                        showCommentHistoryModal(response);

                        // Check if we have comment history
                        if (response.comment && response.comment.length > 0) {
                            // Clear existing comments
                            $("#commentList").empty();

                            // Loop through the comments and append them to the modal
                            response.comment.forEach(function(comment) {
                                const date = new Date(comment.created_at);
                                const formattedDate = new Intl.DateTimeFormat('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
                                }).format(date);

                                let commentHtml = `
                                <li class="list-group-item mb-2 d-flex justify-content-between align-items-start flex-column flex-sm-row">
                                    <div>
                                        <strong>${comment.employee.name} :</strong><br>
                                        ${comment.comment}
                                    </div>
                                   <div class="text-muted small text-end mt-2 mt-sm-0 d-flex justify-content-center align-items-center">
                                       <strong> ${formattedDate}</strong>
                                    </div>
                                </li>
                            `;
                                $("#commentList").append(commentHtml);
                            });


                        } else {
                            // If no comments found, display a message
                            $("#commentList").append(
                                '<li class="list-group-item text-muted">No comments found.</li>'
                            );
                        }

                        // Show the modal
                        $("#detailAssessmentModal").modal("hide");
                        $('#commentHistoryModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching comment history:", error);
                        alert("Failed to load comment history.");
                    }
                });

            });

            $("#commentHistoryModal").on("hidden.bs.modal", function() {
                setTimeout(() => {
                    $(".modal-backdrop").remove(); // Hapus overlay modal update
                    $("body").removeClass("modal-open");

                    $("#detailAssessmentModal").modal("show");
                }, 300);
            });


            // Pastikan overlay baru dibuat saat modal update ditutup dan kembali ke modal history
            $("#havDetail").on("hidden.bs.modal", function() {
                setTimeout(() => {
                    $(".modal-backdrop").remove(); // Hapus overlay modal update
                    $("body").removeClass("modal-open");

                    $("#detailAssessmentModal").modal("show");

                    // Tambahkan overlay kembali untuk havDetail
                    // $("<div class='modal-backdrop fade show'></div>").appendTo(document.body);
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

        function performSearch() {
            const searchInput = document.getElementById('searchInput').value;
            const url = new URL(window.location.href);

            if (searchInput) {
                url.searchParams.set('search', searchInput);
            } else {
                url.searchParams.delete('search');
            }

            window.location.href = url.toString();
        }

        // Event saat tombol Search diklik
        document.getElementById('searchButton').addEventListener('click', performSearch);

        // Event saat tekan Enter di input pencarian
        document.getElementById('searchInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                performSearch(); // Trigger fungsi search langsung
            }
        });
    </script>
@endpush
