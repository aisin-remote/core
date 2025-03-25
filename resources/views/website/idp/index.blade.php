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
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                    role="tablist" style="cursor:pointer">
                    @php
                        $jobPositions = ['General Manager', 'Manager', 'Coordinator', 'Section Head', 'Supervisor'];
                    @endphp

                    @foreach ($jobPositions as $index => $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $index === 0 ? 'active' : '' }}"
                               data-bs-toggle="tab"
                               data-bs-target="#{{ Str::slug($position) }}"
                               role="tab"
                               aria-controls="{{ Str::slug($position) }}">
                                {{ $position }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content mt-3" id="employeeTabsContent">
                    @foreach ($jobPositions as $index => $position)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                             id="{{ Str::slug($position) }}" role="tabpanel"
                            aria-labelledby="{{ Str::slug($position) }}-tab">

                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 gs-0">
                                            <th style="width: 20px">No</th>
                                            <th class="text-center" style="width: 150px">Employee Name</th>
                                            @foreach ($alcs as $id => $title)
                                                <th class="text-center" style="width: 100px">{{ $title }}</th>
                                            @endforeach
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                            $filteredEmployees = $assessments->where('employee.position', $position)->groupBy('employee_id')->map(function ($group) {
                                                return $group->sortByDesc('created_at')->first();
                                            });
                                        @endphp

                                        @forelse ($filteredEmployees as $index => $assessment)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td class="text-center">{{ $assessment->employee->name ?? '-' }}</td>

                                                @foreach ($alcs as $id => $title)
                                                    @php
                                                        $detail = $assessment->details->where('alc_id', $id)->first();
                                                        $score = $detail->score ?? '-';
                                                        $idpExists = DB::table('idp')->where('assessment_id', $assessment->id)->where('alc_id', $id)->exists();
                                                    @endphp
                                                    <td class="text-center">
                                                        @if ($score >= 3 || $score === '-')
                                                            <span class="badge badge-lg badge-success d-block w-100">
                                                                {{ $score }}
                                                            </span>
                                                        @else
                                                            <span
                                                                class="badge badge-lg badge-danger d-block w-100"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#kt_modal_warning_{{ $assessment->id }}"
                                                                data-title="Update IDP - {{ $title }}"
                                                                data-assessment="{{ $assessment->id }}"
                                                                data-alc="{{ $id }}"
                                                                style="cursor: pointer;">
                                                                {{ $score }}
                                                                <i class="fas {{ $idpExists ? 'fa-check' : 'fa-exclamation-triangle' }} ps-2"></i>
                                                            </span>
                                                        @endif
                                                    </td>
                                                @endforeach

                                                <td class="text-center" style="width: 50px">
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                            data-bs-target="#addEntryModal-{{ $assessment->employee->id }}">
                                                            <i class="fas fa-pencil-alt"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                            data-bs-target="#notes_{{ $assessment->employee->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-success"
                                                            onclick="window.location.href='{{ route('idp.exportTemplate', ['employee_id' => $assessment->employee->id]) }}'">
                                                            <i class="fas fa-file-export"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($alcs) + 3 }}" class="text-center text-muted py-4">
                                                    No employees found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
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
                @if (isset($assessment->employee))
                    @php $employee = $assessment->employee; @endphp
                    <div class="modal fade" id="kt_modal_warning_{{ $assessment->id }}" tabindex="-1"
                        style="display: none;" aria-modal="true" role="dialog">
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
                                    <input type="hidden" name="employee_id" value="{{ $assessment->id }}">
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
                                        <button type="button" class="btn btn-primary"
                                            id="btn-create-idp">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @foreach ($assessments as $assessment)
        <div class="modal fade" id="addEntryModal-{{ $assessment->employee->id }}" tabindex="-1"
            aria-labelledby="addEntryModalLabel-{{ $assessment->employee->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Development for {{ $assessment->employee->name }}</h5>
                    </div>
                    <div class="modal-body">
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="developmentTabs-{{ $assessment->employee->id }}" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="midYear-tab-{{ $assessment->employee->id }}"
                                    data-bs-toggle="tab" data-bs-target="#midYear-{{ $assessment->employee->id }}"
                                    type="button" role="tab">Mid Year</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="oneYear-tab-{{ $assessment->employee->id }}"
                                    data-bs-toggle="tab" data-bs-target="#oneYear-{{ $assessment->employee->id }}"
                                    type="button" role="tab">One Year</button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content mt-3">
                            <!-- MID YEAR TAB -->
                            <div class="tab-pane fade show active" id="midYear-{{ $assessment->employee->id }}"
                                role="tabpanel">
                                <form
                                    action="{{ route('idp.storeMidYear', ['employee_id' => $assessment->employee->id]) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $assessment->employee->id }}">
                                    <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">

                                    <div id="programContainerMid_{{ $assessment->employee->id }}">
                                        @if (!empty($assessment->recommendedPrograms))
                                            @foreach ($assessment->recommendedPrograms as $index => $program)
                                                <div class="programItem">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Development Program</label>
                                                        <input type="text" class="form-control"
                                                            name="development_program[]" value="{{ $program }}"
                                                            readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Development Achievement</label>
                                                        <input type="text" class="form-control"
                                                            name="development_achievement[]" placeholder="Enter achievement" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Next Action</label>
                                                        <input type="text" class="form-control" name="next_action[]"
                                                            placeholder="Enter next action" required>
                                                    </div>
                                                    <hr>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-center text-muted">No data available</p>
                                        @endif
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>


                            <div class="tab-pane fade" id="oneYear-{{ $assessment->employee->id }}" role="tabpanel">
                                <form id="reviewForm2-{{ $assessment->employee->id }}"
                                    action="{{ route('idp.storeOneYear', ['employee_id' => $assessment->employee->id]) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $assessment->employee->id }}">

                                    <div id="programContainerOne_{{ $assessment->employee->id }}"
                                        class="programContainer">
                                        <div class="programItem">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Development Program</label>
                                                <select class="form-select"
                                                    name="development_program[{{ $assessment->employee->id }}][]"
                                                    required>
                                                    <option value="">-- Select Development Program --</option>
                                                    <option value="Superior (DGM & GM)">Superior (DGM & GM)</option>
                                                    <option value="Book Reading">Book Reading</option>
                                                    <option value="FIGURE LEADER">FIGURE LEADER</option>
                                                    <option value="Team Leader">Team Leader</option>
                                                    <option value="SR PROJECT">SR PROJECT</option>
                                                    <option value="People Development Program">People Development Program
                                                    </option>
                                                    <option value="Leadership">Leadership</option>
                                                    <option value="Developing Sub Ordinate">Developing Sub Ordinate
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Evaluation Result</label>
                                                <input type="text" class="form-control"
                                                    name="evaluation_result[{{ $assessment->employee->id }}][]"
                                                    placeholder="Evaluation Result" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-2">
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" class="btn btn-success btn-sm addMore"
                                            data-employee-id="{{ $assessment->employee->id }}">+ Add</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach


    <!-- Modal -->
    @foreach ($assessments as $assessment)
        <div class="modal fade" id="notes_{{ $assessment->employee->id }}" tabindex="-1" aria-modal="true"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered mw-800px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="fw-bold">Summary {{ $assessment->employee->name }}</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body scroll-y mx-2">
                        <form id="kt_modal_update_role_form_{{ $assessment->id }}" class="form">
                            <div class="row mt-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">I. Development Program</h3>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr class="text-start text-muted fw-bold fs-8 gs-0">
                                                    <th class="text-center">Strength</th>
                                                    <th class="text-center">Weakness</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($assessment->details as $detail)
                                                    @if (!empty($detail->strength) || !empty($detail->weakness))
                                                        <tr>
                                                            <td class="text-center">{{ $detail->strength ?? '-' }}</td>
                                                            <td class="text-center">{{ $detail->weakness ?? '-' }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Individual Development Program -->
                            <div class="row mt-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">II. Individual Development Program</h3>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <tr class="text-start text-muted fw-bold fs-8 gs-0">
                                                    <th class="text-center">Development Area</th>
                                                    <th class="text-center">Development Program</th>
                                                    <th class="text-center">Development Target</th>
                                                    <th class="text-center">Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($idps->where('employee_id', $assessment->employee->id) as $idp)
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

                            <!-- Mid Year Review -->
                            <div class="row mt-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">III. Mid Year Review</h3>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <tr class="text-start text-muted fw-bold fs-8 gs-0">
                                                    <th class="text-center">Development Program</th>
                                                    <th class="text-center">Development Achievement</th>
                                                    <th class="text-center">Next Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($mid->where('employee_id', $assessment->employee->id) as $items)
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
                                                @foreach ($details->where('employee_id', $assessment->employee->id) as $item)
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
            document.querySelectorAll(".addMore").forEach(button => {
                button.addEventListener("click", function() {
                    let employeeId = this.dataset.employeeId;
                    let containerId = `programContainerOne_${employeeId}`;
                    let container = document.getElementById(containerId);

                    if (!container) {
                        console.error("Container tidak ditemukan: " + containerId);
                        return;
                    }

                    // Buat elemen baru
                    let newProgram = document.createElement("div");
                    newProgram.classList.add("programItem");
                    newProgram.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Development Program</label>
                    <select class="form-select" name="development_program[${employeeId}][]" required>
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
                    <input type="text" class="form-control" name="evaluation_result[${employeeId}][]" placeholder="Evaluation Result" required>
                </div>
                <button type="button" class="btn btn-danger btn-sm removeProgram">Remove</button>
                <hr>
            `;

                    container.appendChild(newProgram);

                    // Event listener untuk menghapus program
                    newProgram.querySelector(".removeProgram").addEventListener("click",
                        function() {
                            newProgram.remove();
                        });
                });
            });
        });

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
    </script>
@endpush
