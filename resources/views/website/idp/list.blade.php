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

    .modal-header {
        position: sticky;
        top: 0;
        background: #ffffff; /* supaya tidak transparan saat scroll */
        z-index: 1055; /* lebih tinggi dari isi modal */
    }
</style>

@section('main')
<div id="kt_app_content_container" class="app-container container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">IDP List</h3>
            <div class="d-flex align-items-center">
                <form method="GET" action="{{ url()->current() }}" class="d-flex mb-3">
                    <input type="text" id="searchInputEmployee" name="search" class="form-control me-2" placeholder="Search..." style="width: 250px;" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary me-3" id="searchButton">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body">
            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 text-sm font-medium mb-6" role="tablist" style="cursor:pointer">
                {{-- Tab Show All --}}
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-active-primary pb-4 {{ $filter == 'all' ? 'active' : '' }}" href="{{ route('idp.list', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                        Show All
                    </a>
                </li>

                {{-- Tab Dinamis --}}
                @foreach ($visiblePositions as $position)
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-active-primary pb-4 {{ $filter == $position ? 'active' : '' }}" href="{{ route('idp.list', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
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
                    @php
                    // Grouping by employee_id
                    $grouped = $assessments->groupBy(
                    fn($item) => optional(optional($item->hav)->hav)->employee->id,
                    );

                    @endphp

                    @forelse ($grouped as $employeeId => $group)
                    @php
                    $firstAssessment = $group->first();
                    $hav = $firstAssessment->hav;
                    $employee = optional(optional($hav)->hav)->employee;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            <img src="{{ $employee?->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}" alt="Employee Photo" class="rounded" width="40" height="40" style="object-fit: cover;">
                        </td>
                        <td>{{ $employee?->npk ?? '-' }}</td>
                        <td>{{ $employee?->name ?? '-' }}</td>
                        <td>{{ $employee?->company_name ?? '-' }}</td>
                        <td>{{ $employee?->position ?? '-' }}</td>
                        <td>{{ $employee?->bagian ?? '-' }}</td>
                        <td>{{ $employee?->grade ?? '-' }}</td>
                        <td class="text-center">
                            @if ($employee)
                            <a class="btn btn-info btn-sm history-btn" data-employee-id="{{ $employee->id }}" data-bs-toggle="modal" data-bs-target="#detailAssessmentModal">
                                History
                            </a>
                            @else
                            <span class="text-muted">No Employee</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Assessment -->
<div class="modal fade" id="detailAssessmentModal" tabindex="-1" aria-labelledby="detailAssessmentModalLabel" aria-hidden="true">
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
                    <table class="table table-striped table-bordered align-middle table-hover fs-6" id="kt_table_assessments" width="100%">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="10%">No</th>
                                <th class="text-center">IDP Year</th>
                                <th class="text-center" width="40%">Status</th>
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
<!-- Modal Detail Assessment -->


@foreach ($grouped as $employeeId => $group)
@php
$firstAssessment = $group->first();
$data = $firstAssessment->hav->hav;
$employee = optional($data)->employee;
@endphp

<div class="modal fade" id="notes_{{ $employee->id }}" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Summary {{ $employee->name }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body scroll-y mx-2">
                <div class="card mt-4 p-3">
                    <style>
                        .chart-container {
                            position: relative;
                            width: 100%;
                            min-height: 400px;
                        }

                        .modal .chart-container canvas {
                            animation: fadeIn 0.5s ease-in-out;
                        }

                        @keyframes fadeIn {
                            from {
                                opacity: 0;
                                transform: translateY(20px);
                            }

                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }

                    </style>
                    <h4 class="text-center">Assessment Chart</h4>
                    <div style="width: 100%; max-width: auto; margin: 0 auto;">
                        <canvas id="assessmentChart" data-employee-id="{{ $employee->id }}"></canvas>
                    </div>
                </div>
                <form class="form">
                    <style>
                        .section-title {
                            font-weight: 600;
                            font-size: 1.3rem;
                            border-left: 4px solid #0d6efd;
                            padding-left: 10px;
                            margin-top: 2rem;
                            margin-bottom: 1rem;
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        }

                        .section-title i {
                            color: #0d6efd;
                            font-size: 1.2rem;
                        }

                        table.custom-table {
                            font-size: 0.9375rem;
                        }

                        table.custom-table th,
                        table.custom-table td {
                            padding: 0.75rem 1rem;
                            vertical-align: top;
                        }

                        table.custom-table thead {
                            background-color: #f8f9fa;
                            font-weight: 600;
                            font-size: 1rem;
                        }

                        table.custom-table tbody tr:hover {
                            background-color: #f1faff;
                        }

                    </style>

                    @php
                    // Ambil satu assessment dari group
                    $assessment = $group->first()?->assessment;

                    // Pastikan ada details
                    $allDetails = $assessment?->details ?? collect();

                    // Filter strength yang valid (tidak kosong dan bukan '-')
                    $strengthRows = $allDetails->filter(
                    fn($d) => !empty(trim($d->strength)) && trim($d->strength) !== '-',
                    );

                    // Filter weakness yang valid (tidak kosong dan bukan '-')
                    $weaknessRows = $allDetails->filter(
                    fn($d) => !empty(trim($d->weakness)) && trim($d->weakness) !== '-',
                    );
                    @endphp

                    @if ($strengthRows->isNotEmpty() || $weaknessRows->isNotEmpty())
                    <div class="section-title"><i class="bi bi-lightning-charge-fill"></i>Strength & Weakness
                    </div>
                    @endif

                    @if ($strengthRows->isNotEmpty())
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover custom-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Strength</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($strengthRows as $row)
                                <tr>
                                    <td>{{ $row->alc->name ?? '-' }}</td>
                                    <td>{{ $row->strength }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if ($weaknessRows->isNotEmpty())
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover custom-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Weakness</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($weaknessRows as $row)
                                <tr>
                                    <td>{{ $row->alc->name ?? '-' }}</td>
                                    <td>{{ $row->weakness }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <div class="section-title"><i class="bi bi-person-workspace"></i>Individual Development
                        Program</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>ALC</th>
                                    <th>Category</th>
                                    <th>Development Program</th>
                                    <th>Development Target</th>
                                    <th>Due Date</th>
                                    <th>Created By</th>
                                    <th>Last Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($group as $idp)
                                <tr>
                                    <td>{{ $idp->alc->name ?? '-' }}</td>
                                    <td>{{ $idp->category }}</td>
                                    <td>{{ $idp->development_program }}</td>
                                    <td>{{ $idp->development_target }}</td>
                                    <td>
                                        {{ optional($idp)->date ? \Carbon\Carbon::parse($idp->date)->format('d-m-Y') :
                                        '-' }}
                                    </td>
                                    <td>
                                        {{ $idp->created_by_name ?? null }}
                                    </td>
                                    <td>
                                        {{ optional($idp)->updated_at ? \Carbon\Carbon::parse($idp->updated_at)->format('d-m-Y') :
                                        '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="section-title"><i class="bi bi-bar-chart-line-fill"></i>Mid Year Review</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>Development Program</th>
                                    <th>Achievement</th>
                                    <th>Next Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mid->where('employee_id', $employee->id) as $items)
                                <tr>
                                    <td>{{ $items->development_program }}</td>
                                    <td>{{ $items->development_achievement }}</td>
                                    <td>{{ $items->next_action }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="section-title"><i class="bi bi-calendar-check-fill"></i>One Year Review</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>Development Program</th>
                                    <th>Evaluation Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($details->where('employee_id', $employee->id) as $item)
                                <tr>
                                    <td>{{ $item->development_program }}</td>
                                    <td>{{ $item->evaluation_result }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Modal management system
        window.modalManager = {
            activeModals: []
            , modalStack: [],

            openModal: function(modalId) {
                // Cegah pembukaan ulang modal yang sama
                if (this.modalStack.length > 0 && this.modalStack[this.modalStack.length - 1] ===
                    modalId) {
                    return;
                }

                // Tutup semua modal dulu
                this.closeAllModals();

                const modalElement = document.getElementById(modalId);
                if (!modalElement) return;

                const modal = new bootstrap.Modal(modalElement);
                modal.show();

                this.activeModals.push(modalId);
                this.modalStack.push(modalId);

                this.cleanupBackdrops();
                this.addBackdrop();
            },

            closeModal: function(modalId) {
                const modalElement = document.getElementById(modalId);
                if (!modalElement) return;

                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }

                this.activeModals = this.activeModals.filter(id => id !== modalId);
                this.modalStack = this.modalStack.filter(id => id !== modalId);

                this.cleanupBackdrops();

                if (this.activeModals.length > 0) {
                    this.addBackdrop();
                } else {
                    document.body.classList.remove('modal-open');
                }
            },

            closeAllModals: function() {
                this.activeModals.forEach(modalId => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                    if (modal) modal.hide();
                });
                this.activeModals = [];
                this.modalStack = [];
                this.cleanupBackdrops();
                document.body.classList.remove('modal-open');
            },

            cleanupBackdrops: function() {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
            },

            addBackdrop: function() {
                if (!document.querySelector('.modal-backdrop')) {
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                    document.body.classList.add('modal-open');
                }
            },

            getPreviousModal: function() {
                if (this.modalStack.length > 1) {
                    return this.modalStack[this.modalStack.length - 2];
                }
                return null;
            }
        };

        initModals(); // Panggil init setelah modalManager dibuat
    });

    function initModals() {
        const modals = [
            'detailAssessmentModal'
            , 'updateAssessmentModal'
            , 'noteAssessmentModal'
            , 'addAssessmentModal'
        ];

        modals.forEach(modalId => {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function() {
                    modalManager.closeModal(modalId);
                });
            }
        });
    }

    $(document).ready(function() {
        // Buka modal detail dengan AJAX
        $(document).on("click", ".history-btn", function(event) {
            event.preventDefault();

            let employeeId = $(this).data("employee-id");
            console.log("Fetching history for Employee ID:", employeeId);

            $("#npkText").text("-");
            $("#positionText").text("-");
            $("#kt_table_assessments tbody").empty();

            $.ajax({
                url: `/idp/history/${employeeId}`
                , type: "GET"
                , success: function(response) {
                    console.log("Response received:", response);

                    if (!response.employee) {
                        console.error("Employee data not found in response!");
                        alert("Employee not found!");
                        return;
                    }

                    $("#npkText").text(response.employee.npk);
                    $("#positionText").text(response.employee.position);

                    const tbody = $("#kt_table_assessments tbody");
                    tbody.empty();

                    const grouped = response.grouped_assessments;
                    const currentUserRole = "{{ auth()->user()->role }}";
                    let index = 1;

                    if (grouped && Object.keys(grouped).length > 0) {
                        Object.entries(grouped).forEach(([assessmentId, assessments]) => {
                            const first = assessments[0];
                            const createdAt = new Date(first.created_at);
                            const year = createdAt.getFullYear();


                            let status = first.status;
                            switch (status) {
                                case 0:
                                    status = "draft";
                                    break;
                                case 1:
                                    status = "waiting";
                                    break;
                                case 2:
                                    status = "checked";
                                    break;
                                case 3:
                                    status = "approved";
                                    break;
                                case -1:
                                    status = "revise";
                                    break;
                                default:
                                    status = "unknown";
                                    break;
                            }

                            const badges = {
                                no_approval_needed: {
                                    text: "-"
                                    , class: "badge-light text-dark"
                                , }
                                , not_created: {
                                    text: "Not Created"
                                    , class: "badge-dark"
                                , }
                                , draft: {
                                    text: "Need Submit"
                                    , class: "badge-secondary"
                                , }
                                , waiting: {
                                    text: "Waiting"
                                    , class: "badge-warning"
                                , }
                                , checked: {
                                    text: "Checked"
                                    , class: "badge-info"
                                , }
                                , approved: {
                                    text: "Approved"
                                    , class: "badge-success"
                                , }
                                , revise: {
                                    text: "Need Revise"
                                    , class: "badge-danger"
                                , }
                                , unknown: {
                                    text: "Unknown"
                                    , class: "badge-secondary"
                                , }
                            , };
                            const badge = badges[status] || badges.unknown;

                            let deleteButton = "";
                            if (currentUserRole === "HRD") {
                                deleteButton = `
                                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="${assessmentId}" data-employee-id="${employeeId}">
                                            Delete
                                        </button>
                                    `;
                            }

                            const row = `
                                    <tr>
                                        <td class="text-center">${index++}</td>
                                        <td class="text-center">${year}</td>
                                        <td class="text-center">
                                            <span class="badge ${badge.class}"
                                            style="
                                                display: inline-block;
                                                padding: 0.75rem;
                                                text-align: center;
                                                font-size: 0.85rem;
                                                font-weight: 600;
                                                border-radius: 0.375rem;
                                                white-space: nowrap;
                                            ">
                                            ${badge.text}
                                        </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm btn-idp-detail" data-modal-id="notes_${first.hav.hav.employee.id}" data-employee-id="${employeeId}">
                                                Detail
                                            </button>
                                            ${deleteButton}
                                        </td>
                                    </tr>
                                `;
                            tbody.append(row);
                        });
                    } else {
                        tbody.append(`
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No IDP found</td>
                                </tr>
                            `);
                    }

                    modalManager.openModal("detailAssessmentModal");
                }
                , error: function(error) {
                    console.error("Error fetching data:", error);
                    alert("Failed to load assessment data!");
                }
            });
        });

        // Function to initialize assessment chart
        function initAssessmentChart(modalId, employeeId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            // Wait for modal to be fully shown
            $(modal).one('shown.bs.modal', function() {
                const canvas = modal.querySelector(`canvas[data-employee-id="${employeeId}"]`);
                if (!canvas) return;

                // Destroy previous chart if exists
                if (canvas.chart) {
                    canvas.chart.destroy();
                }

                // Get data from PHP
                const groupedAssessments = @json($groupedAssessments ?? []);
                console.log('Grouped assessments:', groupedAssessments);

                const employeeAssessments = groupedAssessments[employeeId];

                if (!employeeAssessments || !employeeAssessments.length) {
                    console.warn(`No assessment data for employee ${employeeId}`);
                    return;
                }

                // Find first assessment with details
                let assessmentWithDetails = null;
                for (const assessment of employeeAssessments) {
                    if (assessment.assessment?.details) {
                        assessmentWithDetails = assessment.assessment;
                        break;
                    }
                }

                if (!assessmentWithDetails || !assessmentWithDetails.details) {
                    console.warn(`No assessment details for employee ${employeeId}`);
                    return;
                }

                // Prepare chart data
                const labels = [];
                const scores = [];
                const alcNames = @json($alcs ?? []);

                // Sort details by ALC ID to maintain consistent order
                const sortedDetails = assessmentWithDetails.details.sort((a, b) => a.alc_id - b.alc_id);

                sortedDetails.forEach(detail => {
                    if (detail.alc_id) {
                        labels.push(alcNames[detail.alc_id] || `ALC ${detail.alc_id}`);
                        scores.push(parseInt(detail.score) || 0);
                    }
                });

                console.log('Chart data:', {labels, scores});

                // Create chart
                const ctx = canvas.getContext('2d');
                canvas.chart = new Chart(ctx, {
                    type: 'bar'
                    , data: {
                        labels: labels
                        , datasets: [{
                            label: 'Assessment Scores'
                            , data: scores
                            , backgroundColor: scores.map(score =>
                                score < 3 ? 'rgba(255, 99, 132, 0.8)' : 'rgba(75, 192, 192, 0.8)')
                            , borderColor: scores.map(score =>
                                score < 3 ? 'rgba(255, 99, 132, 1)' : 'rgba(75, 192, 192, 1)')
                            , borderWidth: 1
                            , borderRadius: 4
                        }]
                    }
                    , options: {
                        responsive: true
                        , maintainAspectRatio: false
                        , plugins: {
                            legend: {
                                display: false
                            }
                            , tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Score: ${context.raw}`;
                                    }
                                }
                            }
                        }
                        , scales: {
                            y: {
                                beginAtZero: true
                                , suggestedMax: 5
                                , ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                        , animation: {
                            duration: 1000
                            , easing: 'easeOutQuart'
                        }
                    }
                });
            });
        }

        // Handle detail button click
        $(document).on("click", ".btn-idp-detail", function(e) {
            e.preventDefault();
            const modalId = $(this).data("modal-id");
            const employeeId = $(this).data("employee-id");

            // Tutup modal saat ini terlebih dahulu
            $('#detailAssessmentModal').modal('hide');

            // Buka modal baru setelah yang sebelumnya tertutup
            setTimeout(() => {
                const modal = new bootstrap.Modal(document.getElementById(modalId));
                modal.show();
                initAssessmentChart(modalId, employeeId);
            }, 500);
        });

        $(document).on('shown.bs.modal', '.modal', function(e) {
            const modalId = $(this).attr('id');
            const employeeId = $(this).find('canvas').data('employee-id');

            if (employeeId) {
                initAssessmentChart(modalId, employeeId);
            }
        });

        $(document).on('hidden.bs.modal', '.modal', function() {
            const canvas = this.querySelector('canvas');
            if (canvas && canvas.chart) {
                canvas.chart.destroy();
                canvas.chart = null;
            }
        });

        // Saat modal notes ditutup, tampilkan modal sebelumnya
        $(document).on('hidden.bs.modal', '.modal', function(event) {
            const modalId = $(this).attr('id');
            modalManager.closeModal(modalId);

            const previousModal = modalManager.getPreviousModal();
            if (previousModal) {
                modalManager.openModal(previousModal);
            }
        });

        // Tombol delete IDP
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const employeeId = $(this).data('employee-id');
            const clickedButton = $(this);

            Swal.fire({
                title: 'Are you sure?'
                , text: 'This IDP will be permanently deleted!'
                , icon: 'warning'
                , showCancelButton: true
                , confirmButtonText: 'Yes, delete it!'
                , cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...'
                        , allowOutsideClick: false
                        , didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: `/idp/delete/${id}`
                        , type: 'POST'
                        , data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                        , success: function(response) {
                            Swal.fire({
                                title: 'Deleted!'
                                , text: response.message ||
                                    'IDP successfully deleted.'
                                , icon: 'success'
                                , timer: 2000
                                , showConfirmButton: false
                            }).then(() => {
                                modalManager.closeModal(
                                    "detailAssessmentModal");
                            });

                            clickedButton.closest('tr').remove();

                            // Cek apakah masih ada IDP lainnya
                            $.ajax({
                                url: `/idp/history/${employeeId}`
                                , type: 'GET'
                                , success: function(response) {
                                    if (!response.grouped_assessments ||
                                        Object.keys(response
                                            .grouped_assessments)
                                        .length === 0) {
                                        $(`.history-btn[data-employee-id="${employeeId}"]`)
                                            .closest('tr').remove();
                                    }
                                }
                            });
                        }
                        , error: function() {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });

</script>
@endpush
