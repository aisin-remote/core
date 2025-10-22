@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'ICP' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'ICP' }}
@endsection

@push('custom-css')
    <style>
        /* ===== Status Chip ===== */
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
            text-overflow: ellipsis;
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
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--dot) 20%, transparent);
        }

        .status-chip[data-status="approved"] {
            --bg: #ecfdf5;
            --fg: #065f46;
            --bd: #a7f3d0;
            --dot: #10b981;
        }

        .status-chip[data-status="checked"],
        .status-chip[data-status="waiting"] {
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b;
        }

        .status-chip[data-status="draft"] {
            --bg: #f8fafc;
            --fg: #334155;
            --bd: #e2e8f0;
            --dot: #94a3b8;
        }

        .status-chip[data-status="revise"] {
            --bg: #fef2f2;
            --fg: #7f1d1d;
            --bd: #fecaca;
            --dot: #ef4444;
        }

        .status-chip[data-status="not_created"],
        .status-chip[data-status="unknown"] {
            --bg: #f4f4f5;
            --fg: #27272a;
            --bd: #e4e4e7;
            --dot: #a1a1aa;
        }

        @keyframes pulseDot {
            0% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 30%, transparent);
            }

            70% {
                box-shadow: 0 0 0 8px color-mix(in srgb, var(--dot) 0%, transparent);
            }

            100% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 0%, transparent);
            }
        }

        .status-chip[data-status="waiting"]::before {
            animation: pulseDot 1.25s infinite;
        }

        @media (max-width:768px) {
            .status-chip {
                max-width: 210px;
            }
        }

        /* ===== ACTION Buttons Layout ===== */
        .action-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: stretch;
            gap: .4rem;
            min-width: 150px;
        }

        @media (min-width:992px) {
            .action-stack {
                flex-wrap: wrap;
                flex-direction: column;
                column-gap: .4rem;
                row-gap: .4rem;
                min-width: 160px;
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
            font-size: .75rem;
        }

        .action-stack .btn i {
            font-size: .85rem
        }

        @media (max-width:576px) {
            .action-stack .btn {
                width: 100%;
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

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">ICP Assign</h3>
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
                            href="{{ route('icp.assign', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>
                    {{-- Tabs berdasarkan posisi yang terlihat --}}
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
                        @forelse ($rows as $i => $row)
                            @php
                                /** @var \App\Models\Employee $e */
                                $e = $row['e'];
                                $icp = $row['icp'];
                                $done = $row['done'] ?? [];
                                $wait = $row['waiting'] ?? null;
                                $label = $row['label'];
                                $badge = $row['badge'];
                                $act = $row['actions'];
                                $deptName =
                                    $e->department->name ??
                                    (optional(optional($e->subSection)->section)->department->name ?? '-');
                            @endphp

                            <tr class="fs-7" data-position="{{ $e->position }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $e->npk }}</td>
                                <td>{{ $e->name }}</td>
                                <td>{{ $e->company_name }}</td>
                                <td>{{ $e->position }}</td>
                                <td>{{ $deptName }}</td>
                                <td>{{ $e->grade }}</td>

                                {{-- Status --}}
                                <td>
                                    @php
                                        $historyLines = [];
                                        if ($icp && $icp->relationLoaded('steps')) {
                                            $sorted = $icp->steps->sortBy('step_order');
                                            foreach ($sorted as $s) {
                                                if ($s->status === 'done') {
                                                    $line = '✓ ' . $s->label;
                                                    if ($s->actor) {
                                                        $acted = optional($s->acted_at)->format('d/m/Y H:i');
                                                        $line .=
                                                            ' (' .
                                                            $s->actor->name .
                                                            ($acted ? ', ' . $acted : '') .
                                                            ')';
                                                    }
                                                    $historyLines[] = $line;
                                                }
                                            }
                                            $next = $sorted->firstWhere('status', 'pending');
                                            if ($next) {
                                                $historyLines[] = '⏳ Waiting: ' . $next->label;
                                            }
                                        }
                                        $tooltip = $historyLines
                                            ? implode('<br>', array_map('e', $historyLines))
                                            : e('No history yet');

                                        $chipStatus = 'unknown';
                                        $chipIcon = 'fa-regular fa-stack-exchange';
                                        $chipText = '-';
                                        if (!$icp) {
                                            $chipStatus = 'not_created';
                                            $chipText = 'Not created';
                                        } else {
                                            $expired =
                                                $icp->status === 3 && optional($icp->created_at)->addYear()->isPast();
                                            if ($icp->status === 4) {
                                                $chipStatus = 'draft';
                                                $chipIcon = 'fa-regular fa-file-lines';
                                                $chipText = 'Draft';
                                            } elseif ($icp->status === 0) {
                                                $chipStatus = 'revise';
                                                $chipIcon = 'fa-solid fa-triangle-exclamation';
                                                $chipText = 'Revise';
                                            } elseif ($icp->status === 1) {
                                                $chipStatus = 'waiting';
                                                $chipIcon = 'fa-solid fa-hourglass-half';
                                                $chipText = isset($next) && $next ? $next->label : 'Submitted';
                                            } elseif ($icp->status === 2) {
                                                $chipStatus = 'checked';
                                                $chipIcon = 'fa-solid fa-hourglass-half';
                                                $chipText = isset($next) && $next ? $next->label : 'Checked';
                                            } elseif ($icp->status === 3) {
                                                $chipStatus = $expired ? 'draft' : 'approved';
                                                $chipIcon = 'fa-solid fa-check-circle';
                                                $apprDone = $icp->steps
                                                    ->where('type', 'approve')
                                                    ->where('status', 'done')
                                                    ->sortBy('step_order')
                                                    ->last();
                                                $chipText = $apprDone?->label
                                                    ? str_replace('Approve', 'Approved', $apprDone->label)
                                                    : 'Approved';
                                                if ($expired) {
                                                    $chipText .= ' (Expired)';
                                                }
                                            }
                                        }
                                    @endphp

                                    <span class="status-chip w-100 justify-content-center"
                                        data-status="{{ $chipStatus }}" data-bs-toggle="tooltip" data-bs-html="true"
                                        title="{!! $tooltip !!}">
                                        <div>
                                            <i class="{{ $chipIcon }}"></i>
                                        </div>
                                        {{ $chipText }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    <div class="action-stack">
                                        @if ($act['add'] && !$icp)
                                            <a href="{{ route('icp.create', $e->id) }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add
                                            </a>
                                        @endif

                                        @if (($act['revise'] ?? false) && $icp)
                                            <a href="{{ route('icp.edit', $icp->id) }}" class="btn btn-warning">
                                                <i class="fas fa-edit"></i> Revise
                                            </a>
                                        @elseif ($icp && $icp->status === 4)
                                            <a href="{{ route('icp.edit', $icp->id) }}" class="btn btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        @endif

                                        @if (($act['export'] ?? false) && $icp && $icp->status !== 4)
                                            <a href="{{ route('icp.export', ['employee_id' => $e->id]) }}"
                                                class="btn btn-success">
                                                <i class="fas fa-file-excel"></i> Export
                                            </a>
                                        @endif

                                        @php $ev = $row['actions']['evaluate'] ?? null; @endphp
                                        @if ($icp && $icp->status === \App\Models\Icp::STATUS_APPROVED && $ev)
                                            @if (!empty($ev['show']))
                                                <a href="{{ route('icp.evaluate.create', $ev['icp_id']) }}"
                                                    class="btn btn-primary">
                                                    Evaluate
                                                </a>
                                            @else
                                                <span
                                                    @if (!empty($ev['next_date'])) data-bs-toggle="tooltip"
                                                        title="Next evaluation on {{ \Carbon\Carbon::parse($ev['next_date'])->format('d/m/Y') }}" @endif>
                                                    <button class="btn btn-light w-100" disabled>Evaluate</button>
                                                </span>
                                            @endif
                                        @endif

                                        @if (($act['submit'] ?? false) || ($icp && $icp->status === 4))
                                            <form action="{{ route('icp.submit', $icp->id) }}" method="POST"
                                                class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-primary w-100"
                                                    onclick="return confirm('Submit ICP ini untuk approval?');">
                                                    <i class="fas fa-paper-plane"></i> Submit
                                                </button>
                                            </form>
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

    {{-- Modal History --}}
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
                            <p class="fs-6 fw-bold m-0"><strong>NPK:</strong> <span id="npkText"></span></p>
                        </div>
                        <div class="col-auto">
                            <p class="fs-6 fw-bold m-0"><strong>Position:</strong> <span id="positionText"></span></p>
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
                            <tbody></tbody>
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
        // Search: pertahankan filter & company
        document.getElementById('searchButton').addEventListener('click', function() {
            const q = document.getElementById('searchInput').value || '';
            const params = new URLSearchParams(window.location.search);
            const filter = params.get('filter') || 'all';
            const url = @json(route('icp.assign', ['company' => $company])) +
                `?filter=${encodeURIComponent(filter)}&search=${encodeURIComponent(q)}`;
            window.location.href = url;
        });

        // Modal History
        $(document).on("click", ".history-btn", function(event) {
            event.preventDefault();
            const employeeId = $(this).data("employee-id");

            $("#npkText").text("-");
            $("#positionText").text("-");
            $("#kt_table_assessments tbody").empty();

            $.ajax({
                url: `/icp/history/${employeeId}`,
                type: "GET",
                success: function(response) {
                    if (!response.employee) {
                        alert("Employee not found!");
                        return;
                    }

                    $("#npkText").text(response.employee.npk);
                    $("#positionText").text(response.employee.position);
                    $("#kt_table_assessments tbody").empty();

                    if ((response.employee.icp || []).length > 0) {
                        response.employee.icp.forEach((icp, idx) => {
                            const row = `
                                <tr>
                                    <td class="text-center">${idx + 1}</td>
                                    <td class="text-center">${icp.aspiration || '-'}</td>
                                    <td class="text-center">${icp.career_target || '-'}</td>
                                    <td class="text-center">${icp.date || '-'}</td>
                                    <td class="text-center"></td>
                                </tr>`;
                            $("#kt_table_assessments tbody").append(row);
                        });
                    } else {
                        $("#kt_table_assessments tbody").append(`
                            <tr><td colspan="5" class="text-center text-muted">No assessment found</td></tr>
                        `);
                    }

                    $("#detailAssessmentModal").modal("show");
                },
                error: function() {
                    alert("Failed to load icp data!");
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el, {
                    container: 'body'
                });
            });
        });
    </script>
@endpush
