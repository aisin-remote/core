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
    <div id="kt_app_content_container" class="app-container  container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Employee List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInputEmployee" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-end">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="card-body table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 gs-0">
                            <th style="width: 20px">No</th>
                            <th class="text-center" style="width: 100px">Employee Name</th>

                            @foreach ($alcs as $id => $title)
                                <th class="text-center" style="width: 50px">{{ $title }}</th>
                            @endforeach

                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assessments as $index => $assessment)
                            <tr>
                                <td class="text-center">
                                    {{ ($assessments->currentPage() - 1) * $assessments->perPage() + $index + 1 }}
                                </td>
                                <td class="text-center">{{ $assessment->employee->name ?? '-' }}</td>

                                @foreach ($alcs as $id => $title)
                                    @php
                                        $score = $assessment->details->where('alc_id', $id)->first()->score ?? '-';
                                    @endphp
                                    <td class="text-center">
                                        <span
                                            class="badge badge-lg {{ $score >= 3 ? 'badge-success' : 'badge-danger' }} score d-block w-100"
                                            @if ($score < 3 && $score !== '-') data-bs-toggle="modal"
                                            data-bs-target="#kt_modal_warning_{{ $assessment->id }}"
                                            data-title="Update IDP - {{ $title }}"
                                            data-assessment="{{ $assessment->id }}"
                                            data-alc="{{ $id }}"
                                            style="cursor: pointer;" @endif>
                                            {{ $score }}
                                            @if ($score < 3 && $score !== '-')
                                                <i class="fas fa-exclamation-triangle ps-2"></i>
                                            @endif
                                        </span>
                                    </td>
                                @endforeach
                                <td class="text-center" style="width: 50px">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addEntryModal">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#notes_{{ $assessment->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            {{-- <button type="button" class="btn btn-sm btn-success"

                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="window.location.href='{{ route('idp.exportTemplate', ['employee_id' => $assessment->employee->id]) }}'">
                                                <i class="fas fa-file-export"></i>
                                            </button> --}}
                                            <a type="button" class="btn btn-sm btn-success"
                                                href="{{ asset('assets/file/IDP_Tegar_2024.xlsx') }}" download>
                                                <i class="fas fa-file-export"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No employees found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex align-items-center gap-4 mt-3">
                    <div class="d-flex align-items-center">
                        <span class="legend-circle bg-danger"></span>
                        <span class="ms-2 text-muted">Below Standard</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="legend-circle bg-success"></span>
                        <span class="ms-2 text-muted">Above Standard</span>
                    </div>
                </div>
            </div>

            @foreach ($assessments as $assessment)
                <div class="modal fade" id="kt_modal_warning_{{ $assessment->id }}" tabindex="-1" style="display: none;"
                    aria-modal="true" role="dialog">
                    <div class="modal-dialog modal-dialog-centered mw-750px">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="fw-bold" id="modal-title-{{ $assessment->id }}">Update IDP</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            @php
                                $idp = DB::table('idp')
                                    ->where('assessment_id', $assessment->id)
                                    ->select('id', 'category', 'development_program', 'development_target', 'date')
                                    ->first();

                            @endphp
                            <div class="modal-body scroll-y mx-2 mt-5">
                                <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">
                                <input type="hidden" name="alc_id" id="alc_id_{{ $assessment->id }}" value="">

                                {{-- <input type="hidden" id="alc_input_{{ $assessment->id }}" name="alc" value=""> --}}
                                <div class="col-lg-12 mb-10">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <span class="required">Category</span>
                                    </label>
                                    <select id="category_select_{{ $assessment->id }}" name="idp"
                                        aria-label="Select Category" data-control="select2"
                                        data-placeholder="Select categories..."
                                        class="form-select form-select-solid form-select-lg fw-semibold">
                                        <option value="">Select Category</option>
                                        @foreach (['Feedback', 'Self Development', 'Shadowing', 'On Job Development', 'Mentoring', 'Training'] as $category)
                                            <option value="{{ $category }}"
                                                {{ isset($idp) && $idp->category == $category ? 'selected' : '' }}>
                                                {{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-12 mb-10">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <span class="required">Development Program</span>
                                    </label>
                                    <select id="program_select_{{ $assessment->id }}" name="idp"
                                        aria-label="Select Development Program" data-control="select2"
                                        data-placeholder="Select Programs..."
                                        class="form-select form-select-solid form-select-lg fw-semibold">
                                        <option value="">Select Development Program</option>
                                        @foreach (['Superior (DGM & GM)', 'Book Reading', 'FIGURE LEADER', 'Team Leader', 'SR PROJECT', 'People Development Program', 'Leadership', 'Developing Sub Ordinate'] as $program)
                                            <option value="{{ $program }}"
                                                {{ isset($idp) && $idp->development_program == $program ? 'selected' : '' }}>
                                                {{ $program }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-12 fv-row mb-10">
                                    <label for="target_{{ $assessment->id }}"
                                        class="fs-5 fw-bold form-label mb-2 required">Development Target</label>
                                    <textarea id="target_{{ $assessment->id }}" name="development_target" class="form-control">{{ isset($idp) ? $idp->development_target : '' }}</textarea>
                                </div>

                                <div class="col-lg-12 fv-row mb-5">
                                    <label for="due_date_{{ $assessment->id }}"
                                        class="fs-5 fw-bold form-label mb-2 required">Due
                                        Date</label>
                                    <input type="date" id="due_date_{{ $assessment->id }}" name="due_date"
                                        class="form-control" value="{{ isset($idp) ? $idp->date : '' }}" />
                                </div>

                                <div class="text-center pt-15">
                                    <button type="button" class="btn btn-primary" id="btn-create-idp">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modal fade" id="addEntryModal" tabindex="-1" aria-labelledby="addEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEntryModalLabel">Add Development</h5>
                </div>
                <div class="modal-body">
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs" id="developmentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="midYear-tab" data-bs-toggle="tab"
                                data-bs-target="#midYear" type="button" role="tab">Mid Year</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="oneYear-tab" data-bs-toggle="tab" data-bs-target="#oneYear"
                                type="button" role="tab">One Year</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="developmentTabsContent">
                        @foreach ($assessments as $assessment)
                            <div class="tab-pane fade show active" id="midYear" role="tabpanel">
                                <form id="developmentForm"
                                    action="{{ route('idp.storeMidYear', ['employee_id' => $assessment->employee->id]) }}"
                                    method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="hidden" name="employee_id"
                                            value="{{ $assessment->employee->id }}">
                                        <label class="form-label fw-bold">Development Program</label>
                                        <div>
                                            @foreach ($assessment->recommendedPrograms as $program)
                                                <input type="text" class="form-control mb-2"
                                                    name="development_program[]" value="{{ $program }}" readonly>
                                            @endforeach
                                        </div>
                                    </div>

                                    <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">
                                    <input type="hidden" name="employee_id" value="{{ $assessment->employee->id }}">

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Development Achievement</label>
                                        <input type="text" class="form-control" name="development_achievement[]">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Next Action</label>
                                        <input type="text" class="form-control" name="next_action[]">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                        @endforeach
                    </div>

                    <div class="tab-pane fade" id="oneYear" role="tabpanel">
                        @foreach ($assessments as $assessment)
                            <form id="reviewForm2"
                                action="{{ route('idp.storeOneYear', ['employee_id' => $assessment->employee->id]) }}"
                                method="POST">

                                @csrf
                                <div id="programContainer">
                                    <!-- Development Program & Evaluation Result (Default) -->
                                    <div class="programItem">
                                        <div class="mb-3">

                                            <input type="hidden" name="employee_id"
                                                value="{{ $assessment->employee->id }}">

                                            <label class="form-label fw-bold">Development Program</label>
                                            <select class="form-select" name="development_program[]" required>
                                                <option value="">-- Select Development Program --</option>
                                                <option value="Superior (DGM & GM)">Superior (DGM & GM)</option>
                                                <option value="Book Reading">Book Reading</option>
                                                <option value="FIGURE LEADER">FIGURE LEADER</option>
                                                <option value="Team Leader">Team Leader</option>
                                                <option value="SR PROJECT">SR PROJECT</option>
                                                <option value="People Development Program">People Development Program
                                                </option>
                                                <option value="Leadership">Leadership</option>
                                                <option value="Developing Sub Ordinate">Developing Sub Ordinate</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Evaluation Result</label>
                                            <input type="text" class="form-control" name="evaluation_result[]"
                                                placeholder="Evaluation Result" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    <button type="button" class="btn btn-success btn-sm addMore">+ Add</button>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="notes_{{ $assessment->id }}" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered mw-1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Summary</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body scroll-y mx-2">
                    <form id="kt_modal_update_role_form_{{ $assessment->id }}"
                        class="form fv-plugins-bootstrap5 fv-plugins-framework" action="#">
                        <div class="d-flex flex-column scroll-y me-n7 pe-7"
                            id="kt_modal_update_role_scroll_{{ $assessment->id }}" data-kt-scroll="true"
                            data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                            data-kt-scroll-dependencies="#kt_modal_update_role_header"
                            data-kt-scroll-wrappers="#kt_modal_update_role_scroll" data-kt-scroll-offset="300px">

                            <div class="row mt-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">I. Development Program</h3>
                                        <div class="d-flex align-items-center"></div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable"
                                            id="kt_table_users">
                                            <thead>
                                                <tr class="text-start text-muted fw-bold fs-8 gs-0">
                                                    <th class="text-center" style="width: 50px">Strength</th>
                                                    <th class="text-center" style="width: 50px">Weakness</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($assessments as $assessment)
                                                    @foreach ($assessment->details as $detail)
                                                        @if (!empty($detail->strength) || !empty($detail->weakness))
                                                            <tr>
                                                                <td class="text-center">{{ $detail->alc->name ?? '-' }}
                                                                </td>
                                                                <td class="text-center">{{ $detail->alc->name ?? '-' }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>

                            <div class="row mt-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">II. Individual Development Program</h3>
                                        <div class="d-flex align-items-center">
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable"
                                            id="kt_table_users">
                                            <thead>
                                                <tr class="text-start text-muted fw-bold fs-8 gs-0">
                                                    <th class="text-center" style="width: 100px">
                                                        Development Area
                                                    </th>
                                                    <th class="text-center" style="width: 50px">
                                                        Development Program
                                                    </th>
                                                    <th class="text-center" style="width: 50px">
                                                        Development Target
                                                    </th>
                                                    <th class="text-center" style="width: 50px">Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($idps as $idp)
                                                    <tr>
                                                        <td class="text-center">{{ $idp->category }}</td>
                                                        <td class="text-center">{{ $idp->development_program }}</td>
                                                        <td class="text-center">{{ $idp->development_target }}</td>
                                                        <td class="text-center">
                                                            {{ \Carbon\Carbon::parse($idp->date)->format('d-m-Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">III. Mid Year Review</h3>
                                        <div class="d-flex align-items-center">
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable"
                                            id="kt_table_users">
                                            <thead>
                                                <tr class="text-start text-muted fw-bold fs-8 gs-0">
                                                    <th class="text-center" style="width: 50px">
                                                        Development Program
                                                    </th>
                                                    <th class="text-center" style="width: 50px">
                                                        Development Achivement
                                                    </th>
                                                    <th class="text-center" style="width: 50px">
                                                        Next Action
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($mid as $index => $items)
                                                    <tr>
                                                        <td>{{ $items->development_program }}</td>
                                                        <td>{{ $items->development_achievement }}</td>
                                                        <td>{{ $items->next_action }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    </form>

                    <div class="row mt-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">IV. One Year Review</h3>
                                <div class="d-flex align-items-center">
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable"
                                    id="kt_table_users">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-8 gs-0">
                                            <th class="text-center" style="width: 50px">
                                                Development Program
                                            </th>
                                            <th class="text-center" style="width: 50px">
                                                Evaluation Result
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($details as $index => $item)
                                            <tr>
                                                <td>{{ $item->development_program }}</td>
                                                <td>{{ $item->evaluation_result }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // document.addEventListener("DOMContentLoaded", function() {
        //     document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(button) {
        //         button.addEventListener('click', function() {
        //             var targetModalId = this.getAttribute('data-bs-target');
        //             var title = this.getAttribute('data-title');
        //             var alc = this.getAttribute('data-alc');

        //             const modalAlcInput = document.querySelector(targetModalId +
        //                 ' input[name=\"alc\"]');
        //             if (modalAlcInput) {
        //                 modalAlcInput.value = alc;
        //             }

        //             const modalTitle = document.querySelector(targetModalId + ' .modal-header h2');
        //             if (modalAlcInput) {
        //                 modalAlcInput.value = alc;
        //             }
        //             if (title && modalTitle) {
        //                 modalTitle.textContent = title;
        //             }

        //             const alcInput = document.querySelector(
        //                 `${targetModalId} input[name="alc_id"]`);
        //             if (alcInput) {
        //                 alcInput.value = alc;
        //             }
        //         });
        //     });
        // });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(button) {
                button.addEventListener('click', function() {
                    let alc = this.getAttribute('data-alc');
                    let assessmentId = this.getAttribute('data-assessment');
                    let modalTarget = this.getAttribute('data-bs-target');
                    var title = this.getAttribute('data-title');
                    const modalTitle = document.querySelector(modalTarget + ' .modal-header h2');
                    if (title && modalTitle) {
                        modalTitle.textContent = title;
                    }
                    document.querySelector(`${modalTarget} input[name="alc_id"]`).value = alc;

                    // Fetch data IDP via AJAX untuk mengisi form modal
                    $.ajax({
                        url: '/idp/getData',
                        method: 'GET',
                        data: {
                            assessment_id: assessmentId,
                            alc_id: alc
                        },
                        success: function(response) {
                            if (response.idp) {
                                $(`${modalTarget} select[name="idp"]`).val(response.idp
                                    .category).trigger('change');
                                $(`${modalTarget} select[id^="program_select_"]`).val(
                                    response.idp.development_program).trigger(
                                    'change');
                                $(`${modalTarget} textarea[name="development_target"]`)
                                    .val(response.idp.development_target);
                                $(`${modalTarget} input[name="due_date"]`).val(response
                                    .idp.date);
                            } else {
                                $(`${modalTarget} select`).val(null).trigger('change');
                                $(`${modalTarget} textarea[name="development_target"]`)
                                    .val('');
                                $(`${modalTarget} input[name="due_date"]`).val('');
                            }
                        }
                    });
                });
            });
        });


        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('#btn-create-idp').forEach(button => {
                button.addEventListener('click', function() {
                    const modalBody = this.closest('.modal-body');
                    const assessmentId = modalBody.querySelector('input[name="assessment_id"]')
                        .value;
                    const alcId = modalBody.querySelector('input[name="alc_id"]')
                        .value;
                    const category = modalBody.querySelector(`#category_select_${assessmentId}`)
                        .value;
                    const program = modalBody.querySelector(`#program_select_${assessmentId}`)
                        .value;
                    const target = modalBody.querySelector(`#target_${assessmentId}`).value;
                    const dueDate = modalBody.querySelector(`#due_date_${assessmentId}`).value;

                    $.ajax({
                        url: "{{ route('idp.store') }}",
                        type: "POST",
                        data: {
                            alc_id: alcId,
                            assessment_id: assessmentId,
                            development_program: program,
                            category: category,
                            development_target: target,
                            date: dueDate,
                            '_token': "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: assessmentId ?
                                    "IDP berhasil diperbarui!" :
                                    "IDP berhasil ditambahkan!",
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then(() => {
                                $(`#kt_modal_warning_${assessmentId}`).modal(
                                    'hide');
                                location
                                    .reload(); // Refresh halaman setelah sukses
                            });
                        },
                        error: function(xhr, status, error) {
                            alert(error);
                        }
                    });
                });
            });
        });

        // document.addEventListener("DOMContentLoaded", function() {
        //     document.querySelectorAll("form").forEach(form => {
        //         form.addEventListener("submit", function(e) {
        //             let isValid = true;

        //             // Cek apakah kategori dan program dipilih
        //             const category = form.querySelector('select[name="category[]"]').value;
        //             const program = form.querySelector('select[name="development_program[]"]')
        //                 .value;
        //             const dueDate = form.querySelector('input[name="due_date"]').value;

        //             if (!category || !program || !dueDate) {
        //                 isValid = false;
        //                 alert("Semua bidang wajib diisi!");
        //             }

        //             if (!isValid) {
        //                 e.preventDefault(); // Hentikan submit jika ada error
        //             }
        //         });
        //     });
        // });

        document.addEventListener("DOMContentLoaded", function() {
            // Fungsi pencarian
            document.getElementById("searchButton").addEventListener("click", function() {
                var searchValue = document.getElementById("searchInput").value.toLowerCase();
                var table = document.getElementById("kt_table_users").getElementsByTagName("tbody")[0];
                var rows = table.getElementsByTagName("tr");

                for (var i = 0; i < rows.length; i++) {
                    var nameCell = rows[i].getElementsByTagName("td")[1];
                    if (nameCell) {
                        var nameText = nameCell.textContent || nameCell.innerText;
                        rows[i].style.display = nameText.toLowerCase().includes(searchValue) ? "" : "none";
                    }
                }
            });

            // document.addEventListener("DOMContentLoaded", function() {
            //     document.querySelectorAll(".score").forEach(function(badge) {
            //         badge.addEventListener("click", function() {
            //             var title = this.getAttribute("data-title");
            //             var assessmentId = this.getAttribute("data-assessment");

            //             if (assessmentId) {
            //                 var modalTitle = document.querySelector("#modal-title-" +
            //                     assessmentId);
            //                 if (modalTitle) {
            //                     modalTitle.textContent = title;
            //                 }
            //             }
            //         });
            //     });
            // });


            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".open-modal").forEach(button => {
                    button.addEventListener("click", function() {
                        let id = this.getAttribute("data-id");
                        let modal = new bootstrap.Modal(document.getElementById(
                            `notes_${id}`));
                        modal.show();
                    });
                });
            });


            // SweetAlert untuk tombol delete
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let employeeId = this.getAttribute('data-id');

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/employee/${employeeId}`;

                            let csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';

                            let methodField = document.createElement('input');
                            methodField.type = 'hidden';
                            methodField.name = '_method';
                            methodField.value = 'DELETE';

                            form.appendChild(csrfToken);
                            form.appendChild(methodField);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('kt_datepicker_7');

            flatpickr(dateInput, {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                mode: "range"
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            let dueDateInput = document.getElementById("kt_datepicker_7");

            let startDate = new Date(2025, 11, 8).toISOString().split("T")[0];
            let endDate = new Date(2025, 11, 17).toISOString().split("T")[0];

            dueDateInput.value = startDate;
            dueDateInput.min = startDate;
            dueDateInput.max = endDate;
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".addMore").addEventListener("click", function() {
                let programContainer = document.getElementById("programContainer");

                let newProgram = document.createElement("div");
                newProgram.classList.add("programItem");

                newProgram.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Development Program</label>
                    <select class="form-select" name="development_program[]" required>
                        <option value="">-- Select Development Program --</option>
                        <option value="Superior (DGM & GM)">Superior (DGM & GM)</option>
                        <option value="Book Reading">Book Reading</option>
                        <option value="FIGURE LEADER">FIGURE LEADER</option>
                        <option value="Team Leader">Team Leader</option>
                        <option value="SR PROJECT">SR PROJECT</option>
                        <option value="People Development Program">People Development Program</option>
                        <option value="Leadership">Leadership</option>
                        <option value="Developing Sub Ordinate">Developing Sub Ordinate</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Evaluation Result</label>
                    <input type="text" class="form-control" name="evaluation_result[]" placeholder="Evaluation Result" required>
                </div>
                <div class="d-flex justify-content-start mb-3">
                    <button type="button" class="btn btn-danger btn-sm removeProgram">Remove</button>
                </div>
            `;

                programContainer.appendChild(newProgram);
            });

            document.getElementById("programContainer").addEventListener("click", function(e) {
                if (e.target.classList.contains("removeProgram")) {
                    e.target.closest(".programItem").remove();
                }
            });
        });


        document.addEventListener("DOMContentLoaded", function() {
            Highcharts.chart('stackedGroupedChart', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Assessment Score [Actual vs Target]'
                },
                xAxis: {
                    categories: ['Vision & Business Sense', 'Customer Focus', 'Interpersonal Skill',
                        'Analysis & Judgment', 'Planning & Driving Action', 'Leading & Motivating',
                        'Teamwork', 'Drive & Courage'
                    ],
                    title: {
                        text: 'Competencies'
                    }
                },
                yAxis: {
                    min: 0,
                    max: 5,
                    title: {
                        text: 'Score'
                    },
                    stackLabels: {
                        enabled: true
                    }
                },
                tooltip: {
                    shared: true
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: true
                        }
                    },
                    line: {
                        dataLabels: {
                            enabled: true, // Show labels for target values
                            format: '{y}', // Display the target score
                            style: {
                                fontWeight: 'bold',
                                color: '#ff6347'
                            }
                        },
                        marker: {
                            symbol: 'circle',
                            radius: 5
                        }
                    }
                },
                series: [{
                        name: 'Actual Score',
                        type: 'column',
                        data: [4.5, 3, 2.5, 3.1, 2, 4.8, 3.7, 2.7],
                        color: '#007bff'
                    },
                    {
                        name: 'Target Score',
                        type: 'line', // Line for target scores
                        data: [4, 4.5, 4, 3.5, 4.5, 5, 3.5, 4.2],
                        color: '#ff6347'
                    }
                ]
            });
        });
    </script>
@endpush
