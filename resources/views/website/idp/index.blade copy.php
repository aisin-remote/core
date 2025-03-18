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
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
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
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal"
                                                data-bs-target="#addEntryModal2">
                                                <i class="fas fa-pen"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#notes_{{ $assessment->id }}">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="window.location.href='{{ route('idp.exportTemplate', ['employee_id' => $assessment->employee->id]) }}'">
                                                <i class="fas fa-file-export"></i>
                                            </button>
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
                            <div class="modal-body scroll-y mx-2 mt-5">
                                <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">
                                <div class="col-lg-12 mb-10">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <span class="required">Category</span>
                                    </label>
                                    <select id="category_select_{{ $assessment->id }}" name="idp"
                                        aria-label="Select Category" data-control="select2"
                                        data-placeholder="Select categories..."
                                        class="form-select form-select-solid form-select-lg fw-semibold" multiple>
                                        <option value="">Select Category</option>
                                        <option data-kt-flag="" value="Feedback">Feedback</option>
                                        <option data-kt-flag="" value="Self Development">Self Development
                                        </option>
                                        <option data-kt-flag="" value="Shadowin">Shadowing</option>
                                        <option data-kt-flag="" value="On Job Development">On Job Development
                                        </option>
                                        <option data-kt-flag="" value="Mentoring">Mentoring</option>
                                        <option data-kt-flag="" value="Training">Training</option>
                                    </select>
                                </div>

                                <div class="col-lg-12 mb-10">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <span class="required">Development Program</span>
                                    </label>
                                    <select id="program_select_{{ $assessment->id }}" name="idp"
                                        aria-label="Select Development Program" data-control="select2"
                                        data-placeholder="Select Programs..."
                                        class="form-select form-select-solid form-select-lg fw-semibold" multiple>
                                        <option value="">Select Development Program</option>
                                        <option data-kt-flag="" value="Superior (DGM & GM)">Superior (DGM & GM) + DIC
                                            PUR + BOD Members</option>
                                        <option data-kt-flag="" value="Book Reading">Book Reading/ Journal Business
                                            and BEST PRACTICES (Asia Pacific Region)</option>
                                        <option data-kt-flag="" value="FIGURE LEADER">To find "FIGURE LEADER" with
                                            Strong in Drive and Courage in Their Team --> Sharing Success Tips</option>
                                        <option data-kt-flag="" value="Team Leader">Team Leader of TASK FORCE with
                                            MULTY FUNCTION --> (AII) HYBRID DUMPER Project (CAPACITY UP) & (AIIA) EV
                                            Project</option>
                                        <option data-kt-flag="" value="SR PROJECT">SR Project (Structural Reform -->
                                            DM & SCM)</option>
                                        <option data-kt-flag="" value="People Development Program">PEOPLE Development
                                            Program of Team members (ICT, IDP)</option>
                                        <option data-kt-flag="" value="Leadership">(Leadership) --> Courageously &
                                            Situational Leadership</option>
                                        <option data-kt-flag="" value="Developing Sub Ordinate">(Developing Sub
                                            Ordinate) --> Coaching Skill/ Developing Talents</option>
                                    </select>
                                </div>

                                <div class="col-lg-12 fv-row mb-10">
                                    <label for="" class="fs-5 fw-bold form-label mb-2">Development
                                        Target</label>
                                    <textarea id="target_{{ $assessment->id }}" name="development_target" class="form-control"></textarea>

                                </div>

                                <div class="col-lg-12 fv-row mb-5">
                                    <label for="" class="fs-5 fw-bold form-label mb-2">Due Date</label>
                                    <input type="date" id="due_date_{{ $assessment->id }}" name="due_date"
                                        class="form-control" />

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
                    <h5 class="modal-title" id="addEntryModalLabel">Add Development Mid Year</h5>
                </div>

                <!-- Tambahkan method="POST" dan action -->
                <form id="developmentForm" action="{{ route('idp.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div id="formContainer">
                            <!-- Group of input fields -->
                            <div class="entry-group">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Development Program</label>
                                    <select id="program_select_mid_year{{ $assessment->id }}" name="idp"
                                        aria-label="Select a Country" data-control="select2"
                                        data-placeholder="Select Programs..."
                                        class="form-select form-select-solid form-select-lg fw-semibold" multiple>

                                        <option value="">Select Development Program</option>

                                        @php
                                            // Ambil semua skor < 3 dari employee terkait
                                            $lowScores = $assessment->details->where('score', '<', 3);
                                            $recommendedPrograms = [];

                                            foreach ($lowScores as $lowScore) {
                                                // Sesuaikan program berdasarkan nilai assessment
                                                if ($lowScore->alc_id == 'some_id_1') {
                                                    $recommendedPrograms[] = 'Book Reading';
                                                } elseif ($lowScore->alc_id == 'some_id_2') {
                                                    $recommendedPrograms[] = 'Leadership';
                                                } elseif ($lowScore->alc_id == 'some_id_3') {
                                                    $recommendedPrograms[] = 'Developing Sub Ordinate';
                                                }
                                            }

                                            $recommendedPrograms = array_unique($recommendedPrograms);
                                        @endphp

                                        @foreach ($recommendedPrograms as $program)
                                            <option value="{{ $program }}" selected>{{ $program }}</option>
                                        @endforeach

                                        <!-- Opsi Default (jika tidak ada skor < 3) -->
                                        <option value="Superior (DGM & GM)">Superior (DGM & GM) + DIC PUR + BOD Members
                                        </option>
                                        <option value="Book Reading">Book Reading/ Journal Business and BEST PRACTICES
                                            (Asia Pacific Region)</option>
                                        <option value="FIGURE LEADER">To find "FIGURE LEADER" with Strong in Drive and
                                            Courage in Their Team --> Sharing Success Tips</option>
                                        <option value="Team Leader">Team Leader of TASK FORCE with MULTY FUNCTION --> (AII)
                                            HYBRID DUMPER Project (CAPACITY UP) & (AIIA) EV Project</option>
                                        <option value="SR PROJECT">SR Project (Structural Reform --> DM & SCM)</option>
                                        <option value="People Development Program">PEOPLE Development Program of Team
                                            members (ICT, IDP)</option>
                                        <option value="Leadership">(Leadership) --> Courageously & Situational Leadership
                                        </option>
                                        <option value="Developing Sub Ordinate">(Developing Sub Ordinate) --> Coaching
                                            Skill/ Developing Talents</option>
                                    </select>

                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Development Achievement</label>
                                    <input type="text" class="form-control developmentAchievement"
                                        name="development_achievement[]">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Next Action</label>
                                    <input type="text" class="form-control nextAction" name="next_action[]">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="addEntryModal2" tabindex="-1" aria-labelledby="addEntryModal2Label"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEntryModal2Label">Add Development One Year</h5>
                </div>

                <form id="reviewForm2" method="POST" action="{{ route('idp.storeOneYear') }}">
                    @csrf
                    <div class="modal-body">
                        <div id="formContainer">
                            <div class="entry-group">
                                <div class="mb-3">
                                    <label for="developmentProgram2" class="form-label fw-bold">Development
                                        Program</label>
                                    <select class="form-select" id="developmentProgram2" name="development_program[]"
                                        required>
                                        <option value="">-- Select Development Program --</option>
                                        <option value="Superior (DGM & GM) + DIC PUR + BOD Members">Superior (DGM & GM) +
                                            DIC PUR + BOD Members</option>
                                        <option
                                            value="Book Reading / Journal Business and BEST PRACTICES (Asia Pasific Region)">
                                            Book Reading / Journal Business and BEST PRACTICES (Asia Pasific Region)
                                        </option>
                                        <option
                                            value="To find FIGURE LEADER with Strong in Drive and Courage in Their Team --> Sharing Success Tips">
                                            To find "FIGURE LEADER" with Strong in Drive and Courage in Their Team -->
                                            Sharing Success Tips </option>
                                        <option
                                            value="Team Leader of TASK FORCE with MULTY FUNCTION --> (AII) HYBRID DUMPER Project (CAPACITY UP) & (AIIA) EV Project">
                                            Team Leader of TASK FORCE with MULTY FUNCTION --> (AII) HYBRID DUMPER Project
                                            (CAPACITY UP) & (AIIA) EV Project </option>
                                        <option value="SR Project (Structural Reform -->DM & SCM)">SR Project (Structural
                                            Reform -->DM & SCM)</option>
                                        <option value="PEOPLE Development Program of Team members (ICT, IDP)">PEOPLE
                                            Development Program of Team members (ICT, IDP)</option>
                                        <option value="(Leadership) --> Courageously & Situational Leadership">(Leadership)
                                            --> Courageously & Situational Leadership </option>
                                        <option value="(Developing Sub Ordinate) --> Coaching Skill / Developing Talents">
                                            (Developing Sub Ordinate) --> Coaching Skill / Developing Talents</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="evaluationResult2" class="form-label fw-bold">Evaluation Result</label>
                                    <input type="text" class="form-control" id="evaluationResult2"
                                        name="evaluation_result[]" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
            </div>
            </form>
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
                            data-kt-scroll-wrappers="#kt_modal_update_role_scroll" data-kt-scroll-offset="300px"
                            style="">
                            <h3 class="card-title mb-5">I. Development Program</h3>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-light ">
                                        <div class="card-body">
                                            <div class="mb-7">
                                                <h2 class="fs-1 text-gray-800 w-bolder mb-6">
                                                    Area of Strength
                                                </h2>

                                                <div class="mt-10 accordion accordion-icon-toggle">
                                                    <div class="m-0">
                                                        <div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed"
                                                            data-bs-toggle="collapse" data-bs-target="#strength_1"
                                                            aria-expanded="false">
                                                            <div
                                                                class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                                                <i
                                                                    class="fas fa-minus-square toggle-on text-primary fs-1"></i>
                                                                <i class="fas fa-plus-square toggle-off fs-1"></i>
                                                            </div>

                                                            <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                                                Customer Focus
                                                            </h4>
                                                        </div>

                                                        <div id="strength_1" class="fs-6 ms-1 collapse" style="">
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 ">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="separator separator-dashed"></div>
                                                    </div>
                                                    <div class="m-0">
                                                        <div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed"
                                                            data-bs-toggle="collapse" data-bs-target="#strength_2"
                                                            aria-expanded="false">
                                                            <div
                                                                class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                                                <i
                                                                    class="fas fa-minus-square toggle-on text-primary fs-1"></i>
                                                                <i class="fas fa-plus-square toggle-off fs-1"></i>
                                                            </div>
                                                            <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                                                Interpersonal
                                                                Skill
                                                            </h4>
                                                        </div>

                                                        <div id="strength_2" class="fs-6 ms-1 collapse" style="">
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 ">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="separator separator-dashed"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="card bg-light ">
                                        <div class="card-body">
                                            <div class="mb-7">
                                                <h2 class="fs-1 text-gray-800 w-bolder mb-6">
                                                    Area of Weakness
                                                </h2>
                                                <div class="mt-10">
                                                    <div class="m-0">
                                                        <div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed"
                                                            data-bs-toggle="collapse" data-bs-target="#weakness"
                                                            aria-expanded="false">
                                                            <div
                                                                class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                                                <i
                                                                    class="fas fa-minus-square toggle-on text-primary fs-1"></i>
                                                                <i class="fas fa-plus-square toggle-off fs-1"></i>
                                                            </div>

                                                            <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                                                Leading & Motivating
                                                            </h4>
                                                        </div>
                                                        <div id="weakness" class="fs-6 ms-1 collapse" style="">
                                                            <div class="mb-4">
                                                                <div class="d-flex align-items-center ps-10 mb-n1">
                                                                </div>
                                                                <div class="mb-4">
                                                                    <div class="d-flex align-items-center ps-10 mb-n1">
                                                                    </div>
                                                                    <div class="mb-4">
                                                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                                                        </div>
                                                                        <div class="mb-4">
                                                                            <div
                                                                                class="d-flex align-items-center ps-10 mb-n1">
                                                                            </div>
                                                                            <div class="mb-4">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-4">
                                                                        <div class="d-flex align-items-center ps-10 ">
                                                                        </div>
                                                                    </div>
                                                                    <div class="separator separator-dashed"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-8">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h3 class="card-title">II. Individual Development Program</h3>
                                            <div class="d-flex align-items-center">
                                                <input type="text" id="searchInput" class="form-control me-2"
                                                    placeholder="Search..." style="width: 200px;">
                                                <button type="button" class="btn btn-primary me-3" id="searchButton">
                                                    <i class="fas fa-search"></i> Search
                                                </button>
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

                                                </tbody>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="midYearModal" tabindex="-1" aria-labelledby="midYearModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="midYearModalLabel">III Mid Year Review</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Development Program</th>
                                                        <th>Development Achievement</th>
                                                        <th>Next Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">III Mid Year Riview</h3>
                                        <div class="d-flex align-items-center">
                                            <input type="text" id="searchInput" class="form-control me-2"
                                                placeholder="Search..." style="width: 200px;">
                                            <button type="button" class="btn btn-primary me-3" id="searchButton">
                                                <i class="fas fa-search"></i> Search
                                            </button>
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
                                <h3 class="card-title">IV. One Year Riview</h3>
                                <div class="d-flex align-items-center">
                                    <input type="text" id="searchInput" class="form-control me-2"
                                        placeholder="Search..." style="width: 200px;">
                                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                                        <i class="fas fa-search"></i> Search
                                    </button>
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
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(button) {
                button.addEventListener('click', function() {
                    var title = this.getAttribute('data-title');
                    var assessmentId = this.getAttribute('data-assessment');
                    var modalTitle = document.querySelector("#modal-title-" + assessmentId);
                    if (modalTitle) {
                        modalTitle.textContent = title;
                    }
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('#btn-create-idp').forEach(button => {
                button.addEventListener('click', function() {
                    const modalBody = this.closest('.modal-body');
                    const assessmentId = modalBody.querySelector('input[name="assessment_id"]')
                        .value;
                    const category = modalBody.querySelector(`#category_select_${assessmentId}`)
                        .value;
                    const program = modalBody.querySelector(`#program_select_${assessmentId}`)
                        .value;
                    const target = modalBody.querySelector(`#target_${assessmentId}`).value;
                    const dueDate = modalBody.querySelector(`#due_date_${assessmentId}`).value;

                    const data = {
                        assessment_id: assessmentId,
                        category: category,
                        program: program,
                        target: target,
                        due_date: dueDate,
                        _token: '{{ csrf_token() }}'
                    };

                    fetch('{{ route('idp.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': data._token
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('IDP updated successfully!');
                                location.reload();
                            } else {
                                alert('Failed to update IDP.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
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

            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".score").forEach(function(badge) {
                    badge.addEventListener("click", function() {
                        var title = this.getAttribute("data-title");
                        var assessmentId = this.getAttribute("data-assessment");

                        if (assessmentId) {
                            var modalTitle = document.querySelector("#modal-title-" +
                                assessmentId);
                            if (modalTitle) {
                                modalTitle.textContent = title;
                            }
                        }
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



                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $assessments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
