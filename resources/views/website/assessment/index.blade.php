@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Assessment' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Assessment' }}
@endsection

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="app-content container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Assessment List</h3>
                    <div class="d-flex align-items-center">
                        @if (auth()->user()->role == 'HRD' && auth()->user()->employee->position != 'President')
                            <a href="#" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addAssessmentModal">Add</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-5 fw-semibold mb-4" role="tablist"
                        style="cursor:pointer">
                        {{-- Tab Show All --}}
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                                href="{{ route('assessments.index', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                                <i class="fas fa-list me-2"></i>Show All
                            </a>
                        </li>

                        {{-- Tab Dinamis Berdasarkan Posisi --}}
                        @foreach ($visiblePositions as $position)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                    href="{{ route('assessments.index', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                    <i class="fas fa-user-tag me-2"></i>{{ $position }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="card-body">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th>Photo</th>
                                    <th>Employee Name</th>
                                    <th>NPK</th>
                                    <th>Company</th>
                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Grade</th>
                                    <th>Age</th>
                                    <th class="text-center">Last Assessment</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assessments as $index => $assessment)
                                    @php
                                        $employee = $assessment->employee;
                                        $birthdate = $employee->birthday_date;
                                        $age = $birthdate ? \Carbon\Carbon::parse($birthdate)->age : null;
                                        $lastAssessmentDate = optional($employee->assessments()->latest()->first())
                                            ->date;
                                    @endphp
                                    <tr data-position="{{ $employee->position ?? '-' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                                alt="Employee Photo" class="rounded" width="40" height="40"
                                                style="object-fit: cover;">
                                        </td>
                                        <td>{{ $employee->name ?? '-' }}</td>
                                        <td>{{ $employee->npk ?? '-' }}</td>
                                        <td>{{ $employee->company_name ?? '-' }}</td>
                                        <td>{{ $employee->position ?? '-' }}</td>
                                        <td>{{ $employee->bagian }}</td>

                                        <td>{{ $employee->grade ?? '-' }}</td>
                                        <td>{{ $age ?? '-' }}</td>
                                        <td class="text-center">
                                            {{ $lastAssessmentDate ? \Carbon\Carbon::parse($lastAssessmentDate)->format('d M Y') : '-' }}
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-info btn-sm history-btn"
                                                data-employee-id="{{ $employee->id }}" data-bs-toggle="modal"
                                                data-bs-target="#detailAssessmentModal">
                                                History
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted fw-bold">
                            Catatan: Hubungi HRD Human Capital jika data karyawan yang dicari tidak tersedia.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        @include('website.assessment.modal')
        @include('website.assessment.modaldetail')
        @include('website.assessment.show')
        @include('website.assessment.modalnote')
    @endsection

    @push('custom-css')
        <link rel="stylesheet" href="{{ asset('assets/plugins/custom/select2/css/select2.min.css') }}">
        <style>
            .modal-backdrop {
                pointer-events: none !important;
            }

            .select2-container {
                width: 100% !important;
                /* Pastikan Select2 mengambil seluruh lebar */
            }

            .select2-selection {
                height: calc(2.25rem + 2px) !important;
                /* Samakan tinggi dengan form-select Bootstrap */
                padding: 0.375rem 0.75rem !important;
                border-radius: 0.375rem !important;
                border: 1px solid #ced4da !important;
                cursor: pointer !important;
            }

            .select2-selection__rendered {
                line-height: 1.5 !important;
                /* Sesuaikan dengan Bootstrap */
            }

            .select2-selection__arrow {
                height: 100% !important;
            }

            /* Agar dropdown menyesuaikan dengan parent */
            .select2-container--default .select2-selection--single {
                display: flex !important;
                align-items: center !important;
            }

            /* Menghindari overlap dengan elemen lain */
            .select2-container--open {
                z-index: 99999 !important;
            }
        </style>

        <style>
            /* Default: selama body BELUM .page-ready, history-btn mati total */
            body:not(.page-ready) .history-btn,
            body:not(.page-ready) a.history-btn.btn {
                pointer-events: none !important;
                opacity: .5;
                cursor: not-allowed !important;
            }

            .history-btn .spinner-border {
                vertical-align: -0.125em;
            }

            /* Setelah siap (JS menambahkan .page-ready), tombol kembali normal */
            body.page-ready .history-btn,
            body.page-ready a.history-btn.btn {
                pointer-events: auto !important;
                opacity: 1;
                cursor: pointer !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('assets/plugins/custom/select2/js/select2.min.js') }}"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Pastikan jQuery terpasang (DataTables butuh jQuery)
                if (typeof $ === 'undefined') {
                    console.error("jQuery not loaded. DataTable won't initialize.");
                    return;
                }

                // Inisialisasi DataTable
                const dt = $('#kt_table_users').DataTable({
                    responsive: true,
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
                    },
                    ordering: false
                });
                console.log("✅ DataTable Initialized Successfully");
            });
        </script>
        <script>
            window.addEventListener('load', function() {
                document.body.classList.add('page-ready');
            });

            $(document).on('click', '.history-btn', function(e) {
                if (!document.body.classList.contains('page-ready')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            });
        </script>
        <script>
            class ModalManager {
                constructor() {
                    this.activeModals = [];
                    this.modalStack = [];
                    this.initEventListeners();
                }

                initEventListeners() {
                    // Global click handler for modal triggers
                    document.addEventListener('click', (e) => {
                        if (e.target.matches('[data-bs-toggle="modal"]')) {
                            const modalId = e.target.getAttribute('data-bs-target').replace('#', '');
                            this.openModal(modalId);
                        }
                    });

                    // Handle escape key - COMPLETELY DISABLE
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.activeModals.length > 0) {
                            e.preventDefault();
                            e.stopPropagation();
                            // Do not close modal on escape
                            return false;
                        }
                    });

                    // Prevent backdrop clicks from closing modals
                    document.addEventListener('click', (e) => {
                        if (e.target.classList.contains('modal-backdrop')) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    });
                }

                openModal(modalId) {
                    // Close all modals if this is a new root modal
                    if (this.modalStack.length === 0 || !this.modalStack.includes(modalId)) {
                        this.closeAllModals();
                    }

                    const modalElement = document.getElementById(modalId);
                    if (!modalElement) {
                        console.error(`Modal with ID ${modalId} not found`);
                        return;
                    }

                    // Force set attributes to prevent closing
                    modalElement.setAttribute('data-bs-backdrop', 'static');
                    modalElement.setAttribute('data-bs-keyboard', 'false');

                    // Initialize or get existing modal instance
                    let modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement, {
                            backdrop: 'static',
                            keyboard: false,
                            focus: true
                        });
                    } else {
                        // Update existing modal options
                        modal._config.backdrop = 'static';
                        modal._config.keyboard = false;
                    }

                    // Show the modal
                    modal.show();

                    // Add to active modals if not already there
                    if (!this.activeModals.includes(modalId)) {
                        this.activeModals.push(modalId);
                        this.modalStack.push(modalId);
                    }

                    // Manage backdrop
                    this.manageBackdrop();

                    // Add additional event listeners to prevent closing
                    this.preventModalClose(modalElement);
                }

                preventModalClose(modalElement) {
                    // Remove any existing event listeners that might close the modal
                    modalElement.removeEventListener('click', this.backdropClickHandler);
                    modalElement.removeEventListener('keydown', this.escapeKeyHandler);

                    // Add our custom handlers
                    this.backdropClickHandler = (e) => {
                        if (e.target === modalElement) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    };

                    this.escapeKeyHandler = (e) => {
                        if (e.key === 'Escape') {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    };

                    modalElement.addEventListener('click', this.backdropClickHandler);
                    modalElement.addEventListener('keydown', this.escapeKeyHandler);
                }

                closeModal(modalId) {
                    const modalElement = document.getElementById(modalId);
                    if (!modalElement) return;

                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }

                    // Remove from active modals
                    this.activeModals = this.activeModals.filter(id => id !== modalId);
                    this.modalStack = this.modalStack.filter(id => id !== modalId);

                    // Clean up event listeners
                    if (this.backdropClickHandler) {
                        modalElement.removeEventListener('click', this.backdropClickHandler);
                    }
                    if (this.escapeKeyHandler) {
                        modalElement.removeEventListener('keydown', this.escapeKeyHandler);
                    }

                    // Manage backdrop
                    this.manageBackdrop();
                }

                closeAllModals() {
                    this.activeModals.forEach(modalId => {
                        const modalElement = document.getElementById(modalId);
                        if (!modalElement) return;

                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }

                        // Clean up event listeners
                        if (this.backdropClickHandler) {
                            modalElement.removeEventListener('click', this.backdropClickHandler);
                        }
                        if (this.escapeKeyHandler) {
                            modalElement.removeEventListener('keydown', this.escapeKeyHandler);
                        }
                    });

                    this.activeModals = [];
                    this.modalStack = [];
                    this.cleanupBackdrops();
                }

                manageBackdrop() {
                    this.cleanupBackdrops();

                    if (this.activeModals.length > 0) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.style.pointerEvents = 'none'; // Disable click events on backdrop
                        document.body.appendChild(backdrop);
                        document.body.classList.add('modal-open');
                    } else {
                        document.body.classList.remove('modal-open');
                    }
                }

                cleanupBackdrops() {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                }
            }

            document.addEventListener("DOMContentLoaded", function() {
                window.modalManager = new ModalManager();

                // Initialize all modals with proper handlers
                const modals = ['detailAssessmentModal', 'updateAssessmentModal', 'noteAssessmentModal',
                    'addAssessmentModal'
                ];

                modals.forEach(modalId => {
                    const modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        // Force set static backdrop attributes
                        modalElement.setAttribute('data-bs-backdrop', 'static');
                        modalElement.setAttribute('data-bs-keyboard', 'false');

                        // Override Bootstrap's default behavior
                        modalElement.addEventListener('show.bs.modal', function(e) {
                            // Ensure modal cannot be closed by backdrop or keyboard
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            if (modal) {
                                modal._config.backdrop = 'static';
                                modal._config.keyboard = false;
                            }
                        });

                        modalElement.addEventListener('hidden.bs.modal', function() {
                            modalManager.closeModal(modalId);
                        });

                        // Prevent any clicks on the modal backdrop area from closing the modal
                        modalElement.addEventListener('click', function(e) {
                            if (e.target === modalElement) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }
                        });
                    }
                });

                // Additional global prevention
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modalManager.activeModals.length > 0) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }, true); // Use capture phase

                // Tab filtering functionality
                const tabs = document.querySelectorAll(".filter-tab");
                const rows = document.querySelectorAll("#kt_table_users tbody tr");

                tabs.forEach(tab => {
                    tab.addEventListener("click", function(e) {
                        e.preventDefault();
                        tabs.forEach(t => t.classList.remove("active"));
                        this.classList.add("active");

                        const filter = this.getAttribute("data-filter").toLowerCase();
                        rows.forEach(row => {
                            const position = row.getAttribute("data-position").toLowerCase();
                            row.style.display = (filter === "all" || position.includes(
                                filter)) ? "" : "none";
                        });
                    });
                });
            });

            function safeEncode(str) {
                return btoa(unescape(encodeURIComponent(str || '')));
            }

            function safeDecode(str) {
                return decodeURIComponent(escape(atob(str || '')));
            }

            $(document).ready(function() {
                $('.history-btn').prop('disabled', false).removeAttr('aria-disabled');

                // History button click handler
                $(document).on("click", ".history-btn", function(event) {
                    event.preventDefault();
                    let employeeId = $(this).data("employee-id");

                    $("#npkText").text("-");
                    $("#positionText").text("-");
                    $("#kt_table_assessments tbody").empty();

                    $.ajax({
                        url: `/assessment/history/${employeeId}`,
                        type: "GET",
                        success: function(response) {
                            if (!response.employee) {
                                alert("Employee not found!");
                                return;
                            }

                            $("#npkText").text(response.employee.npk);
                            $("#positionText").text(response.employee.position);
                            $("#kt_table_assessments tbody").empty();

                            let isHRD = "{{ strtolower(auth()->user()->role) }}" === "hrd";

                            if (response.assessments.length > 0) {
                                response.assessments.forEach((assessment, index) => {
                                    let editBtn = '';
                                    let deleteBtn = '';
                                    let noteBtn = '';

                                    if (isHRD) {
                                        editBtn = `
                                <button type="button" class="btn btn-warning btn-sm updateAssessment"
                                data-bs-toggle="modal" data-bs-target="#updateAssessmentModal"
                                data-id="${assessment.id}"
                                data-employee-id="${assessment.employee_id}"
                                data-date="${assessment.date}"
                                data-description="${safeEncode(assessment.description || '')}"
                                data-targetposition="${assessment.target_position}"
                                data-purpose="${assessment.purpose || ''}"
                                data-lembaga="${safeEncode(assessment.lembaga) || ''}"
                                data-upload="${assessment.upload || ''}"
                                data-note="${safeEncode(assessment.note || '')}"
                                data-scores='${btoa(JSON.stringify(assessment.details.map(d => d.score)))}'
                                data-alcs='${btoa(JSON.stringify(assessment.details.map(d => d.alc_id)))}'
                                data-alc_name='${safeEncode(JSON.stringify(assessment.details.map(d => d.alc?.name || "")))}'
                                data-strengths='${safeEncode(JSON.stringify(assessment.details.map(d => d.strength || "")))}'
                                data-weaknesses='${safeEncode(JSON.stringify(assessment.details.map(d => d.weakness || "")))}'
                                data-suggestion_development='${safeEncode(JSON.stringify(assessment.details.map(d => d.suggestion_development || "")))}'>Edit</button>
                        `;

                                        deleteBtn = `
                            <button type="button" class="btn btn-danger btn-sm delete-btn"
                                data-id="${assessment.id}">Delete</button>
                        `;

                                        noteBtn =
                                            `
                            <button type="button" class="btn btn-dark btn-sm noteAssessment" data-bs-toggle="modal"           data-bs-target="#noteAssessmentModal" data-note="${safeEncode(assessment.note || '')}">Note</button>`
                                    }

                                    let row = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${assessment.date}</td>
                            <td class="text-center">${assessment.target_position || '-'}</td>
                            <td class="text-center">
                                <a class="btn btn-info btn-sm" href="/assessment/${assessment.id}/${assessment.date}">
                                    Detail
                                </a>
                                <a class="btn btn-primary btn-sm"
                                   target="_blank"
                                   href="${assessment.upload ? `/storage/${assessment.upload}` : '#'}"
                                   onclick="${!assessment.upload ? `event.preventDefault(); Swal.fire('Data tidak tersedia');` : ''}">
                                    View PDF
                                </a>
                                ${editBtn}
                                ${noteBtn}
                                ${deleteBtn}
                            </td>
                        </tr>
                    `;

                                    $("#kt_table_assessments tbody").append(row);
                                });
                            } else {
                                $("#kt_table_assessments tbody").append(`
                    <tr>
                        <td colspan="4" class="text-center text-muted">No assessment found</td>
                    </tr>
                `);
                            }
                            // Use modalManager to open the modal
                            modalManager.openModal("detailAssessmentModal");
                        },
                        error: function() {
                            alert("Failed to load assessment data!");
                        }
                    });
                });

                // Update assessment button click handler
                $(document).on("click", ".updateAssessment", function() {
                    let assessmentId = $(this).data("id");
                    let employeeId = $(this).data("employee-id");
                    let date = $(this).data("date");

                    let description = safeDecode($(this).data("description"));
                    let upload = $(this).data("upload");
                    let note = safeDecode($(this).data("note"));
                    let targetPosition = $(this).data("targetposition");
                    let purpose = $(this).data("purpose");
                    let lembaga = safeDecode($(this).data("lembaga"));
                    console.log(purpose);


                    let scores = JSON.parse(atob($(this).attr("data-scores")));
                    let alcs = JSON.parse(atob($(this).attr("data-alcs")));
                    let alcNames = JSON.parse(safeDecode($(this).attr("data-alc_name")));
                    let strengths = JSON.parse(safeDecode($(this).attr("data-strengths")));
                    let weaknesses = JSON.parse(safeDecode($(this).attr("data-weaknesses")));

                    let suggestion_development = JSON.parse(safeDecode($(this).attr(
                        "data-suggestion_development")));

                    $("#update_assessment_id").val(assessmentId);
                    $("#update_employee_id").val(employeeId);
                    $("#update_date").val(date);
                    $("#update_description").val(description);
                    $("#update_upload").attr("href", `/storage/${upload}`).text("Lihat File");
                    $("#update_note").val(note);
                    $("#update_target").val(targetPosition);
                    $("#update_purpose").val(purpose);
                    $("#update_lembaga").val(lembaga);

                    scores.forEach((score, index) => {
                        let alcId = alcs[index];
                        $(`#update_score_${alcId}_${score}`).prop("checked", true);
                    });

                    syncStrengthWeaknessFromScores(scores, alcs, alcNames, strengths, weaknesses,
                        suggestion_development);

                    modalManager.closeModal("detailAssessmentModal");
                    modalManager.openModal("updateAssessmentModal");
                });

                // Note assessment button click handler
                $(document).on("click", ".noteAssessment", function() {
                    $("#assessmentNote").val(safeDecode($(this).data("note")));
                    modalManager.closeModal("detailAssessmentModal");
                    modalManager.openModal("noteAssessmentModal");
                });

                function syncStrengthWeaknessFromScores(scores, alcs, alcNames, strengths, weaknesses, suggestions) {
                    let strengthContainer = document.getElementById("update-strengths-wrapper");
                    let weaknessContainer = document.getElementById("update-weaknesses-wrapper");

                    strengthContainer.innerHTML = "";
                    weaknessContainer.innerHTML = "";

                    scores.forEach((score, index) => {
                        let alcId = alcs[index];
                        let alcName = alcNames[index];
                        let description = (score >= 3) ? strengths[index] : weaknesses[index];
                        let suggestion = suggestions[index] || "";

                        let type = score >= 3 ? "strength" : "weakness";
                        let containerId = type === "strength" ? "update-strengths-wrapper" :
                            "update-weaknesses-wrapper";

                        addAssessmentCard(type, containerId, alcId, description, alcName, suggestion);
                    });

                    updateDropdownOptions();
                }

                function populateAssessmentCards(type, containerId, data, alcs, alcNames) {
                    let container = document.getElementById(containerId);
                    container.innerHTML = "";

                    data.forEach((desc, idx) => {
                        if (desc.trim() !== "") {
                            addAssessmentCard(type, containerId, alcs[idx], desc, alcNames[idx]);
                        }
                    });

                    if (data.length === 0) {
                        addAssessmentCard(type, containerId);
                    }

                    updateDropdownOptions();
                }

                function addAssessmentCard(type, containerId, alcId = "", description = "", alcName = "", suggestion =
                    "") {
                    let container = document.getElementById(containerId);
                    let templateCard = document.createElement("div");
                    templateCard.classList.add("card", "p-3", "mb-3", "assessment-card", `${type}-card`);

                    if (alcId) {
                        templateCard.setAttribute("id", `assessment_card_${alcId}`);
                    }

                    templateCard.innerHTML = `
                <div class="mb-3">
                    <label>ALC</label>
                    <select class="form-control alc-dropdown" name="${type}_alc_ids[]" >
                        <option value="">Pilih ALC</option>
                        @foreach ($alcs as $alc)
                            <option value="{{ $alc->id }}" ${alcId == "{{ $alc->id }}" ? "selected" : ""}>
                                {{ $alc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea class="form-control ${type}-textarea" name="${type}[${alcId}]" rows="2">${description}</textarea>
                </div>
                <div class="mb-3">
                    <label>Suggestion Development</label>
                    <textarea class="form-control suggestion-textarea" name="suggestion_development[${alcId}]" rows="2">${suggestion}</textarea>
                </div>
            `;

                    let selectElement = templateCard.querySelector(".alc-dropdown");
                    selectElement.addEventListener("change", function() {
                        updateDescriptionName(selectElement, type);
                        updateDropdownOptions();
                    });

                    container.appendChild(templateCard);
                    updateDropdownOptions();
                }

                // Fungsi untuk memperbarui nama deskripsi berdasarkan ALC yang dipilih
                function updateDescriptionName(selectElement, type) {
                    let card = selectElement.closest(".assessment-card");
                    let textarea = card.querySelector(`.${type}-textarea`);
                    let alcId = selectElement.value;

                    if (alcId) {
                        textarea.setAttribute("name", `${type}[${alcId}]`);
                    } else {
                        textarea.removeAttribute("name");
                    }
                }

                function updateDropdownOptions() {
                    let selectedStrengths = new Set();
                    let selectedWeaknesses = new Set();

                    // Ambil ALC yang sudah dipilih di strength
                    document.querySelectorAll("#update-strengths-wrapper .alc-dropdown").forEach(select => {
                        if (select?.value) selectedStrengths.add(select.value);
                    });

                    // Ambil ALC yang sudah dipilih di weakness
                    document.querySelectorAll("#update-weaknesses-wrapper .alc-dropdown").forEach(select => {
                        if (select?.value) selectedWeaknesses.add(select.value);
                    });

                    // Update dropdown di strength
                    document.querySelectorAll("#update-strengths-wrapper .alc-dropdown").forEach(select => {
                        const currentValue = select.value;
                        select.querySelectorAll("option").forEach(option => {
                            option.hidden = (option.value !== currentValue) &&
                                (selectedStrengths.has(option.value) || selectedWeaknesses.has(option
                                    .value));
                        });
                    });

                    // Update dropdown di weakness
                    document.querySelectorAll("#update-weaknesses-wrapper .alc-dropdown").forEach(select => {
                        const currentValue = select.value;
                        select.querySelectorAll("option").forEach(option => {
                            option.hidden = (option.value !== currentValue) &&
                                (selectedStrengths.has(option.value) || selectedWeaknesses.has(option
                                    .value));
                        });
                    });
                }

                // Fungsi untuk menangani perubahan skor
                $(document).on("change", ".update-score", function() {
                    const radio = $(this);
                    const idParts = radio.attr("id").split("_");
                    const alcId = idParts[2];
                    const score = parseInt(idParts[3]);

                    const newType = score >= 3 ? "strength" : "weakness";
                    const oldType = score >= 3 ? "weakness" : "strength";

                    const containerMap = {
                        strength: "#update-strengths-wrapper",
                        weakness: "#update-weaknesses-wrapper",
                    };

                    let card = $(`#assessment_card_${alcId}`);
                    const newWrapper = $(containerMap[newType]);
                    const oldWrapper = $(containerMap[oldType]);
                    const oldCard = oldWrapper.find(`#assessment_card_${alcId}`);

                    console.log(`ALC ID: ${alcId}, score: ${score}, oldType: ${oldType}, newType: ${newType}`);
                    console.log("Old card found:", oldCard.length);
                    console.log("New wrapper length:", newWrapper.length);
                    console.log("Old wrapper length:", oldWrapper.length);

                    let description = "";
                    let suggestion = "";
                    let alcName = "";

                    if (oldCard.length) {
                        description = oldCard.find(`textarea.${oldType}-textarea`).val() || "";
                        suggestion = oldCard.find("textarea.suggestion-textarea").val() || "";
                        alcName = oldCard.find("select.alc-dropdown").val() || "";
                        console.log("Old card data:", {
                            description,
                            suggestion,
                            alcName
                        });

                        oldCard.remove();
                    }

                    if (card.length === 0) {
                        console.log(
                            `[INFO] Card ALC ${alcId} not found in newType container, creating new in ${newType}`
                        );
                        addAssessmentCard(newType, containerMap[newType].substring(1), alcId, description,
                            alcName, suggestion);
                        card = $(`#assessment_card_${alcId}`);
                    } else {
                        console.log(`[INFO] Moving ALC ${alcId} card from ${oldType} to ${newType}`);
                        card.detach().appendTo(newWrapper);
                    }

                    // Update name attributes
                    const textarea = card.find("textarea").first();
                    const suggestionTextarea = card.find("textarea.suggestion-textarea");
                    const select = card.find("select");

                    textarea.attr("name", `${newType}[${alcId}]`);
                    suggestionTextarea.attr("name", `suggestion_development[${alcId}]`);
                    select.attr("name", `${newType}_alc_ids[]`);

                    // Update card class
                    card.removeClass("strength-card weakness-card").addClass(`${newType}-card`);

                    updateDropdownOptions();
                });

                // Ensure proper modal event handling
                const modals = ['detailAssessmentModal', 'updateAssessmentModal', 'noteAssessmentModal'];
                modals.forEach(modalId => {
                    const modalElement = document.getElementById(modalId);
                    if (modalElement) {
                        modalElement.addEventListener('hidden.bs.modal', function() {
                            modalManager.closeModal(modalId);
                        });
                    }
                });

                // Prevent any modal from being closed unexpectedly
                $(".modal").on("shown.bs.modal", function() {
                    $(".modal-backdrop").css("z-index", 1050);
                    // Force static behavior
                    const modal = bootstrap.Modal.getInstance(this);
                    if (modal) {
                        modal._config.backdrop = 'static';
                        modal._config.keyboard = false;
                    }
                });

                // Search and filter functionality
                var searchInput = $("#searchInput");
                var filterItems = $(".filter-department");
                var table = $("#kt_table_users");
                var tbody = table.find("tbody");
                var rows = tbody.find("tr");

                if (!searchInput.length || !table.length) {
                    console.error("⚠️ Elemen pencarian atau tabel tidak ditemukan!");
                    return;
                }

                function filterTable(selectedDepartment = "") {
                    var searchValue = searchInput.val().toLowerCase();
                    var isMatchFound = false;

                    rows.each(function() {
                        var row = $(this);
                        var cells = row.find("td");

                        if (cells.length >= 5) {
                            var name = cells.eq(1).text().toLowerCase();
                            var department = cells.eq(2).text().toLowerCase();
                            var npk = cells.eq(3).text().toLowerCase();
                            var age = cells.eq(4).text().toLowerCase();

                            var searchMatch = name.includes(searchValue) || department.includes(searchValue) ||
                                npk.includes(searchValue) || age.includes(searchValue);

                            var departmentMatch = selectedDepartment === "" || department ===
                                selectedDepartment;

                            if (searchMatch && departmentMatch) {
                                row.show();
                                isMatchFound = true;
                            } else {
                                row.hide();
                            }
                        }
                    });
                }

                // Chart functionality
                let assessmentChartInstance = null;

                function renderChart(details) {
                    let canvas = document.getElementById('assessmentChart');
                    let ctx = canvas.getContext('2d');

                    if (assessmentChartInstance) {
                        assessmentChartInstance.destroy();
                    }

                    canvas.width = canvas.width;

                    let labels = details.map(d => d.alc.name);
                    let scores = details.map(d => d.score);

                    assessmentChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Assessment Score',
                                data: scores,
                                backgroundColor: scores.map(score => score < 3 ?
                                    'rgba(255, 99, 132, 0.6)' :
                                    'rgba(75, 192, 192, 0.6)'),
                                borderColor: scores.map(score => score < 3 ? 'rgba(255, 99, 132, 1)' :
                                    'rgba(75, 192, 192, 1)'),
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                datalabels: {
                                    anchor: 'center',
                                    align: 'top',
                                    color: 'black',
                                    font: {
                                        weight: 'bold',
                                        size: 14
                                    },
                                    formatter: value => value
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    suggestedMax: 5,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }

                filterItems.on("click", function(event) {
                    event.preventDefault();
                    var selectedDepartment = $(this).data("department").toLowerCase();
                    console.log("🔍 Filter dipilih: ", selectedDepartment);
                    filterTable(selectedDepartment);
                });

                $('#addAssessmentModal').on('show.bs.modal', function(event) {
                    let button = $(event.relatedTarget);
                    let assessment_id = button.data('id') || null;

                    if (assessment_id) {
                        $('#addAssessmentModalLabel').text('Edit Assessment');
                        $('#btnSubmit').text('Update');
                        $('#assessment_id').val(assessment_id);

                        $('#employee_id').val(button.data('employee_id'));
                        $('#date').val(button.data('date'));

                        let fields = [
                            'vision_business_sense',
                            'customer_focus',
                            'interpersonal_skil',
                            'analysis_judgment',
                            'planning_driving_action',
                            'leading_motivating',
                            'teamwork',
                            'drive_courage'
                        ];

                        fields.forEach(field => {
                            let value = button.data(field);
                            $('input[name="' + field + '"][value="' + value + '"]').prop('checked',
                                true);
                        });

                    } else {
                        $('#addAssessmentModalLabel').text('Tambah Assessment');
                        $('#btnSubmit').text('Simpan');
                        $('#assessmentForm')[0].reset();
                        $('#assessment_id').val('');
                    }
                });

                $('#assessmentForm').submit(function(e) {
                    e.preventDefault();
                    let assessment_id = $('#assessment_id').val();
                    const submitBtn = $('#btnSubmit');

                    // Disable tombol saya proses dimulai
                    submitBtn.prop('disabled', true);

                    const fileInput = document.getElementById("upload");
                    if (fileInput && fileInput.files.length > 0) {
                        const fileSize = fileInput.files[0].size;
                        if (fileSize > 2 * 1024 * 1024) {
                            Swal.fire({
                                title: "Gagal!",
                                text: "Ukuran file maksimal 2 MB.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                            submitBtn.prop('disabled', false);
                            return;
                        }
                    }

                    let formData = new FormData(this);
                    let url = assessment_id ? "{{ url('/assessment') }}/" + assessment_id :
                        "{{ route('assessments.store') }}";
                    let method = assessment_id ? "POST" : "POST";
                    if (assessment_id) {
                        formData.append('_method', 'PUT');
                    }

                    $.ajax({
                        url: url,
                        type: method,
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: assessment_id ? "Assessment berhasil diperbarui!" :
                                    "Assessment berhasil ditambahkan!",
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then(() => {
                                $('#addAssessmentModal').modal('hide');
                                location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = "Terjadi kesalahan!";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                title: "Gagal!",
                                text: errorMessage,
                                icon: "error",
                                confirmButtonText: "Coba Lagi"
                            });

                            submitBtn.prop('disabled', false);
                        }
                    });
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                const tabs = document.querySelectorAll(".filter-tab");
                const rows = document.querySelectorAll("#kt_table_users tbody tr");
                const searchInput = document.getElementById("searchInput");
                const departmentFilter = document.getElementById("departmentFilter");

                let currentPositionFilter = 'all';
                let currentDepartmentFilter = '';
                let currentSearchValue = '';

                // Event listener untuk tabs position
                tabs.forEach(tab => {
                    tab.addEventListener("click", function(e) {
                        e.preventDefault();
                        tabs.forEach(t => t.classList.remove("active"));
                        this.classList.add("active");
                        currentPositionFilter = this.getAttribute("data-filter").toLowerCase();
                        applyFilters();
                    });
                });

                function applyFilters() {
                    rows.forEach(row => {
                        const position = row.getAttribute("data-position").toLowerCase();
                        const departmentCell = row.querySelector("td:nth-child(3)");
                        const department = departmentCell ? departmentCell.textContent.toLowerCase() : '';
                        const departments = department.split(', ').map(d => d.trim());

                        const name = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
                        const npk = row.querySelector("td:nth-child(5)").textContent.toLowerCase();
                        const age = row.querySelector("td:nth-child(6)").textContent.toLowerCase();

                        const matchesPosition = currentPositionFilter === 'all' ||
                            position.includes(currentPositionFilter);

                        const matchesDepartment = currentDepartmentFilter === '' ||
                            departments.includes(currentDepartmentFilter);

                        const matchesSearch = currentSearchValue === '' ||
                            name.includes(currentSearchValue) ||
                            npk.includes(currentSearchValue) ||
                            age.includes(currentSearchValue);

                        if (matchesPosition && matchesDepartment && matchesSearch) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });
                }
            });

            document.getElementById('searchButton').addEventListener('click', function() {
                const search = document.getElementById('searchInput').value;
                const url = new URL(window.location.href);

                url.searchParams.set('search', search);
                url.searchParams.set('page', 1); // Reset to first page on new search

                window.location.href = url.toString();
            });

            // Enhanced CSS to prevent modal closing
            $('head').append(`
            <style>
                .modal-backdrop {
                    pointer-events: none !important;
                }
                .modal .btn-close {
                    pointer-events: auto !important;
                }
                .modal[data-bs-backdrop="static"] {
                    pointer-events: auto !important;
                }
                .modal-dialog {
                    pointer-events: auto !important;
                }
                /* Force prevent backdrop clicks */
                .modal-backdrop.show {
                    opacity: 0.5 !important;
                    pointer-events: none !important;
                }
                /* Ensure modal content is clickable */
                .modal-content {
                    pointer-events: auto !important;
                    position: relative;
                    z-index: 1060 !important;
                }
            </style>
        `);
        </script>
    @endpush
