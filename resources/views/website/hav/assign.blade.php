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
                <h3 class="card-title">HAV Assign</h3>
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
                        <a class="nav-link {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>

                    {{-- Tab Dinamis Berdasarkan Posisi --}}
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
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
                        @foreach ($employees as $item)
                            <tr data-position="{{ $item->employee->position }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->employee->npk }}</td>
                                <td>{{ $item->employee->name }}</td>
                                <td>{{ $item->employee->company_name }}</td>
                                <td>{{ $item->employee->position }}</td>
                                <td>{{ $item->employee->department?->name }}</td>
                                <td>{{ $item->employee->grade }}</td>
                                @php
                                    $status = optional($item->hav)->status;
                                    $isAssessmentOne = $item->hav && $item->hav->details->contains('is_assessment', 1);
                                @endphp
                                <td>
                                    <span class="badge {{ $item->badge_class }} fs-7 fw-bold">
                                        {{ $item->status_text }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($item->show_add)
                                        <a href="#" class="btn btn-info btn-sm btn-add-import" data-bs-toggle="modal"
                                            data-bs-target="#importModal" data-employee-id="{{ $item->employee->id }}">
                                            <i class="fas fa-plus"></i> Add
                                        </a>
                                    @endif

                                    @if ($item->show_revise)
                                        <a href="#" class="btn btn-warning btn-sm btn-revise-import"
                                            data-bs-toggle="modal" data-bs-target="#importModal"
                                            data-hav-id="{{ $item->revise_hav_id }}"
                                            data-employee-id="{{ $item->employee->id }}">
                                            <i class="fas fa-upload"></i> Revise
                                        </a>
                                    @endif

                                    @if ($item->hav)
                                        <a data-id="{{ $item->hav->id }}" class="btn btn-primary btn-sm btn-hav-comment"
                                            href="#">
                                            Comment
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
                            <input type="hidden" name="hav_id" id="havIdInput">
                            <input type="file" name="file" id="importFile" class="form-control"
                                accept=".xlsx, .xls" required>
                            <small class="form-text text-muted">Format yang diperbolehkan: .xlsx atau .xls</small>
                        </div>

                        <div class="alert alert-info small">
                            <strong>Petunjuk:</strong> Gunakan format Excel yang sudah ditentukan.<br>
                            Download template format import:
                            <a href="#" target="_blank" id="downloadTemplateLink"
                                class="fw-bold text-primary text-decoration-underline">
                                Download Template
                            </a>
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
            // Misal response berisi data hav lengkap, termasuk lastUpload
            function showCommentHistoryModal(response) {
                $('#commentHistoryModal').modal('show');

                // Clear sebelumnya
                $('#commentList').empty();

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

            let pendingHavId = null;

            function setDownloadTemplateLink(employeeId) {
                const url = "{{ url('/hav/exportassign') }}/" + employeeId;
                $('#downloadTemplateLink').attr('href', url);
            }

            // Tombol Revisi
            $(document).on('click', '.btn-revise-import', function() {
                const havId = $(this).data('hav-id');
                const employeeId = $(this).data('employee-id');

                $('#havIdInput').val(havId);
                setDownloadTemplateLink(employeeId);
            });

            // Tombol + Add
            $(document).on('click', '.btn-add-import', function() {
                const employeeId = $(this).data('employee-id');
                $('#havIdInput').val(''); // tidak bawa HAV ID
                setDownloadTemplateLink(employeeId);
            });

            // Saat tombol Revisi diklik, simpan ID-nya
            $(document).on('click', '.btn-revise-import', function() {
                pendingHavId = $(this).data('hav-id');
                console.log('Clicked Revisi, will set hav_id:', pendingHavId);
            });

            // Setelah modal terbuka penuh, baru set value input
            $('#importModal').on('shown.bs.modal', function() {
                if (pendingHavId) {
                    $('#havIdInput').val(pendingHavId);
                    console.log('HAV ID input set:', pendingHavId);
                    pendingHavId = null; // reset
                }
            });

            // Tombol "+ Add" harus reset isian
            $(document).on('click', '#addButton', function() {
                $('#havIdInput').val('');
                pendingHavId = null;
            });

            // Ketika tombol "+ Add" diklik, kosongkan input hidden
            $('[data-bs-target="#importModal"]').on('click', function() {
                $('#havIdInput').val('');
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
                                ${`<a
                                                                                                                                                                                                                                                                                                data-id="${hav.id}"
                                                                                                                                                                                                                                                                                                class="btn btn-primary btn-sm btn-hav-comment" href="#">
                                                                                                                                                                                                                                                                                                    History
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
                                      ${comment.comment ?? '-'}

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
