@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@push('custom-css')
    <style>
        .status-chip {
            --bg: #eef2ff;
            --fg: #312e81;
            --bd: #c7d2fe;
            --dot: #6366f1;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .8rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: .85rem;
            line-height: 1;
            border: 1px solid var(--bd);
            background: var(--bg);
            color: var(--fg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .status-chip i {
            font-size: .95rem;
            opacity: .95
        }

        .status-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dot);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--dot) 20%, transparent)
        }

        .status-chip[data-status="approved"] {
            --bg: #ecfdf5;
            --fg: #065f46;
            --bd: #a7f3d0;
            --dot: #10b981
        }

        .status-chip[data-status="checked"],
        .status-chip[data-status="waiting"] {
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b
        }

        .status-chip[data-status="draft"] {
            --bg: #f8fafc;
            --fg: #334155;
            --bd: #e2e8f0;
            --dot: #94a3b8
        }

        .status-chip[data-status="revise"] {
            --bg: #fef2f2;
            --fg: #7f1d1d;
            --bd: #fecaca;
            --dot: #ef4444
        }

        .status-chip[data-status="not_created"],
        .status-chip[data-status="unknown"] {
            --bg: #f4f4f5;
            --fg: #27272a;
            --bd: #e4e4e7;
            --dot: #a1a1aa
        }

        @keyframes pulseDot {
            0% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 30%, transparent)
            }

            70% {
                box-shadow: 0 0 0 8px color-mix(in srgb, var(--dot) 0%, transparent)
            }

            100% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 0%, transparent)
            }
        }

        .status-chip[data-status="waiting"]::before {
            animation: pulseDot 1.25s infinite
        }

        @media (max-width:768px) {
            .status-chip {
                max-width: 210px
            }
        }

        .action-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: stretch;
            gap: .4rem;
            min-width: 150px
        }

        @media (min-width:992px) {
            .action-stack {
                flex-wrap: wrap;
                flex-direction: column;
                column-gap: .4rem;
                row-gap: .4rem;
                min-width: 160px
            }
        }

        .action-stack .btn,
        .action-stack button[type="submit"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            white-space: nowrap;
            padding: .32rem .6rem;
            font-weight: 600;
            border-radius: .5rem;
            font-size: .75rem
        }

        .action-stack .btn i {
            font-size: .85rem
        }

        @media (max-width:576px) {
            .action-stack .btn {
                width: 100%
            }
        }

        /* ===== Table polish ===== */
        #kt_table_users th,
        #kt_table_users td {
            vertical-align: middle
        }
    </style>
@endpush

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">HAV Assign</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width:200px;" value="{{ request('search') }}">
                    <button type="button" class="btn btn-primary" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-4" role="tablist"
                    style="cursor:pointer">
                    {{-- Show All --}}
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('hav.assign', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>
                    {{-- Tabs posisi terlihat --}}
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('hav.assign', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
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
                        @forelse ($employees as $i => $item)
                            @php
                                $e = $item->employee;
                                $deptName =
                                    $e->department->name ??
                                    (optional(optional($e->subSection)->section)->department->name ?? '-');

                                $text = trim($item->status_text ?? '-');
                                $chipStatus = 'unknown';
                                $chipIcon = 'fa-regular fa-stack-exchange';

                                $lower = Str::lower($text);

                                if ($lower === 'not created' || $lower === '-') {
                                    $chipStatus = 'not_created';
                                    $chipIcon = 'fa-regular fa-file';
                                } elseif (Str::startsWith($text, 'Checking')) {
                                    $chipStatus = 'waiting';
                                    $chipIcon = 'fa-solid fa-hourglass-half';
                                } elseif ($lower === 'revised' || $lower === 'need revise') {
                                    $chipStatus = 'revise';
                                    $chipIcon = 'fa-solid fa-triangle-exclamation';
                                } elseif (Str::startsWith($text, 'Approved (Expired)')) {
                                    $chipStatus = 'draft';
                                    $chipIcon = 'fa-solid fa-check-circle';
                                } elseif (Str::startsWith($text, 'Approved')) {
                                    $chipStatus = 'approved';
                                    $chipIcon = 'fa-solid fa-check-circle';
                                } else {
                                    if (
                                        Str::contains($lower, 'waiting') ||
                                        Str::contains($lower, 'submitted') ||
                                        Str::contains($lower, 'checking')
                                    ) {
                                        $chipStatus = 'waiting';
                                        $chipIcon = 'fa-solid fa-hourglass-half';
                                    }
                                }
                                $tooltip = e($text);
                            @endphp

                            <tr class="fs-7" data-position="{{ $e->position }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $e->npk }}</td>
                                <td>{{ $e->name }}</td>
                                <td>{{ $e->company_name }}</td>
                                <td>{{ $e->position }}</td>
                                <td>{{ $deptName }}</td>
                                <td>{{ $e->grade }}</td>

                                <td>
                                    <span class="status-chip w-100 justify-content-center"
                                        data-status="{{ $chipStatus }}" data-bs-toggle="tooltip" data-bs-html="true"
                                        title="{{ $tooltip }}">
                                        <div><i class="{{ $chipIcon }}"></i></div>
                                        {{ $text }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="action-stack">
                                        @if ($item->show_add)
                                            <a href="#" class="btn btn-primary btn-sm btn-add-import"
                                                data-bs-toggle="modal" data-bs-target="#importModal"
                                                data-employee-id="{{ $e->id }}">
                                                <i class="fas fa-plus"></i> Add
                                            </a>
                                        @endif

                                        @if ($item->show_revise)
                                            <a href="#" class="btn btn-warning btn-sm btn-revise-import"
                                                data-bs-toggle="modal" data-bs-target="#importModal"
                                                data-hav-id="{{ $item->revise_hav_id }}"
                                                data-employee-id="{{ $e->id }}">
                                                <i class="fas fa-upload"></i> Revise
                                            </a>
                                        @endif

                                        @if ($item->hav)
                                            <a data-id="{{ $item->hav->id }}" class="btn btn-info btn-sm btn-hav-comment"
                                                href="#">
                                                <i class="fas fa-comments"></i> Comment
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No employees found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: Comment History --}}
    <div class="modal fade" id="commentHistoryModal" tabindex="-1" aria-labelledby="commentHistoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="commentHistoryModalLabel">Comment History</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div id="lastUploadInfo" style="font-size: .875rem; color:#666;"></div>
                        <a href="#" id="btnExportExcel" class="btn btn-success btn-sm" target="_blank"
                            style="padding:.80rem .5rem;font-size:.75rem;">
                            Export HAV
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <ul class="list-group" id="commentList"></ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Import --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import HAV Employee Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importForm" action="{{ route('hav.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Pilih File Excel</label>
                            <input type="hidden" name="hav_id" id="havIdInput">
                            <input type="file" name="file" id="importFile" class="form-control"
                                accept=".xlsx,.xls" required>
                            <small class="form-text text-muted">Format: .xlsx atau .xls</small>
                        </div>

                        <div class="alert alert-info small">
                            <strong>Petunjuk:</strong> Gunakan format Excel yang sudah ditentukan.<br>
                            Download template:
                            <a href="#" target="_blank" id="downloadTemplateLink"
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
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables CSS/JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
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
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
                confirmButtonText: 'Ok'
            });
        </script>
    @endif

    <script>
        function performSearch() {
            const searchInput = document.getElementById('searchInput').value;
            const url = new URL(window.location.href);
            if (searchInput) {
                url.searchParams.set('search', searchInput)
            } else {
                url.searchParams.delete('search')
            }
            window.location.href = url.toString();
        }
        document.getElementById('searchButton').addEventListener('click', performSearch);
        document.getElementById('searchInput').addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                performSearch()
            }
        });

        $(function() {
            function showCommentHistoryModal(response) {
                $('#commentHistoryModal').modal('show');
                $('#commentList').empty();
                if (response.lastUpload) {
                    const date = new Date(response.lastUpload.created_at);
                    const formattedDate = date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                    $('#lastUploadInfo').html(`Last Submit: <strong>${formattedDate}</strong>`);
                    $('#btnExportExcel').attr('href', `/hav/download-upload/${response.hav.id}`);
                } else {
                    $('#lastUploadInfo').html('No uploads found');
                    $('#btnExportExcel').attr('href', '#');
                }
            }

            let pendingHavId = null;

            function setDownloadTemplateLink(employeeId) {
                const url = "{{ url('/hav/exportassign') }}/" + employeeId;
                $('#downloadTemplateLink').attr('href', url);
            }

            $(document).on('click', '.btn-revise-import', function() {
                const havId = $(this).data('hav-id');
                const employeeId = $(this).data('employee-id');
                $('#havIdInput').val(havId);
                setDownloadTemplateLink(employeeId);
                pendingHavId = havId;
            });
            $(document).on('click', '.btn-add-import', function() {
                const employeeId = $(this).data('employee-id');
                $('#havIdInput').val('');
                setDownloadTemplateLink(employeeId);
                pendingHavId = null;
            });
            $('#importModal').on('shown.bs.modal', function() {
                if (pendingHavId) {
                    $('#havIdInput').val(pendingHavId);
                    pendingHavId = null;
                }
            });

            $(document).on('click', '.btn-hav-comment', function(e) {
                e.preventDefault();
                const hav_id = $(this).data('id');
                $("#commentList").empty();
                $.get(`{{ url('/hav/get-history') }}/${hav_id}`, function(response) {
                        showCommentHistoryModal(response);
                        if (response.comment && response.comment.length) {
                            $("#commentList").empty();
                            response.comment.forEach(function(comment) {
                                const date = new Date(comment.created_at);
                                const formattedDate = new Intl.DateTimeFormat('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
                                }).format(date);
                                $("#commentList").append(`
                                <li class="list-group-item mb-2 d-flex justify-content-between align-items-start flex-column flex-sm-row">
                                    <div><strong>${comment.employee.name} :</strong><br>${comment.comment ?? '-'}</div>
                                    <div class="text-muted small text-end mt-2 mt-sm-0 d-flex justify-content-center align-items-center">
                                        <strong>${formattedDate}</strong>
                                    </div>
                                </li>`);
                            });
                        } else {
                            $("#commentList").append(
                                '<li class="list-group-item text-muted">No comments found.</li>');
                        }
                        $('#commentHistoryModal').modal('show');
                    })
                    .fail(function() {
                        alert('Failed to load comment history.')
                    });
            });
        });
    </script>
@endpush
