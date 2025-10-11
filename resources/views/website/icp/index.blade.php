{{-- resources/views/website/icp/index.blade.php --}}
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
                    text: @json(session('success')),
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">ICP List</h3>
                <div class="d-flex align-items-center"></div>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-5 fw-semibold mb-4" role="tablist"
                    style="cursor:pointer">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link fs-7 {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('icp.list', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-7 my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('icp.list', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                <i class="fas fa-user-tag me-2"></i>{{ $position }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users" width="100%">
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
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="d-flex justify-content-between">
                    <small class="text-muted fw-bold">
                        Catatan: Hubungi HRD Human Capital jika data karyawan yang dicari tidak tersedia.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal History (tetap sama seperti punyamu) --}}
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
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof $ === 'undefined') {
                console.error("jQuery not loaded. DataTable won't initialize.");
                return;
            }

            const tableId = '#kt_table_users';
            if (!$.fn.DataTable.isDataTable(tableId)) {
                const ajaxUrl = @json(route('icp.data', ['company' => $company]));
                const urlParams = new URLSearchParams(window.location.search);
                const filter = urlParams.get('filter') || 'all';

                const dt = $(tableId).DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    searching: true,
                    ordering: true,
                    lengthChange: true,
                    pageLength: 10,
                    ajax: {
                        url: ajaxUrl,
                        data: function(d) {
                            // DataTables akan kirim: draw, start, length, search[value], order, dll.
                            d.filter = filter; // kirim filter tab
                        },
                        error: function(xhr) {
                            console.error('DT AJAX error:', xhr?.responseText || xhr);
                            Swal.fire("Error", "Gagal memuat data ICP.", "error");
                        }
                    },
                    columns: [{
                            data: 'no',
                            name: 'no',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'photo',
                            name: 'photo',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'npk',
                            name: 'npk'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'company_name',
                            name: 'company_name'
                        },
                        {
                            data: 'position',
                            name: 'position'
                        },
                        {
                            data: 'department',
                            name: 'department',
                            orderable: false
                        },
                        {
                            data: 'grade',
                            name: 'grade',
                            orderable: false
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    columnDefs: [{
                            targets: [1, 8],
                            render: function(data) {
                                return data;
                            }
                        } // biarkan HTML apa adanya
                    ],
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
                    }
                });

                console.log("✅ DataTable (server-side) Initialized");
            }
        });
    </script>

    {{-- History modal logic tetap, tidak diubah kecuali query selector --}}
    <script>
        $(document).on("click", ".history-btn", function(e) {
            e.preventDefault();
            const employeeId = $(this).data("employee-id");
            $("#npkText").text("-");
            $("#positionText").text("-");
            $("#kt_table_assessments tbody").empty();

            $.get(`/icp/history/${employeeId}`)
                .done(function(response) {
                    if (!response.employee) {
                        Swal.fire("Oops", "Employee not found!", "warning");
                        return;
                    }
                    $("#npkText").text(response.employee.npk);
                    $("#positionText").text(response.employee.position);

                    const tbody = $("#kt_table_assessments tbody");
                    if ((response.employee.icp || []).length) {
                        response.employee.icp.forEach((icp, idx) => {
                            const d = icp.date ? new Date(icp.date) : null;
                            const dd = d ? String(d.getDate()).padStart(2, '0') : '-';
                            const mm = d ? String(d.getMonth() + 1).padStart(2, '0') : '-';
                            const yy = d ? d.getFullYear() : '';
                            const dateStr = d ? `${dd}-${mm}-${yy}` : '-';
                            const isLast = idx === response.employee.icp.length - 1;
                            const exportBtn = isLast ?
                                `<a href="/icp/export/${employeeId}" class="btn btn-success btn-sm ms-2" target="_blank">Export</a>` :
                                '';
                            const delBtn =
                                `<button class="btn btn-danger btn-sm btn-delete" data-id="${icp.id}">Delete</button>`;
                            tbody.append(`
                        <tr>
                            <td class="text-center">${idx+1}</td>
                            <td class="text-center">${icp.aspiration ?? '-'}</td>
                            <td class="text-center">${icp.career_target ?? '-'}</td>
                            <td class="text-center">${dateStr}</td>
                            <td class="text-center">${exportBtn} ${delBtn}</td>
                        </tr>
                    `);
                        });
                    } else {
                        tbody.append(
                            `<tr><td colspan="5" class="text-center text-muted">No assessment found</td></tr>`
                        );
                    }
                    $("#detailAssessmentModal").modal("show");
                })
                .fail(function(xhr) {
                    console.error("Error fetching history:", xhr?.responseText || xhr);
                    Swal.fire("Error", "Failed to load ICP data!", "error");
                });
        });

        // Delete
        $(document).on("click", ".btn-delete", function() {
            const icpId = $(this).data("id");
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((res) => {
                if (!res.isConfirmed) return;
                $.ajax({
                    url: `/icp/delete/${icpId}`,
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    }
                }).done(function() {
                    Swal.fire("Deleted!", "ICP record has been deleted.", "success").then(() => {
                        // reload DataTable server-side, bukan reload halaman
                        if ($.fn.DataTable.isDataTable('#kt_table_users')) {
                            $('#kt_table_users').DataTable().ajax.reload(null, false);
                        } else {
                            location.reload();
                        }
                    });
                }).fail(function() {
                    Swal.fire("Failed!", "ICP record could not be deleted.", "error");
                });
            });
        });
    </script>
@endpush
