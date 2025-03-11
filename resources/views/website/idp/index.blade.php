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
    <div id="kt_app_content_container" class="app-container  container-fluid ">
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
                            <th class="text-center" style="width: 50px">Vision & Business Sense</th>
                            <th class="text-center" style="width: 50px">Customer Focus</th>
                            <th class="text-center" style="width: 50px">Interpersonal <br> Skill</th>
                            <th class="text-center" style="width: 50px">Analysis & Judgment</th>
                            <th class="text-center" style="width: 50px">Planning & Driving Action</th>
                            <th class="text-center" style="width: 50px">Leading & Motivating</th>
                            <th class="text-center" style="width: 50px">Teamwork</th>
                            <th class="text-center" style="width: 50px">Drive & Courage</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $index => $employee)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $employee->name }}</td>
                                <td class="text-center">
                                    <span class="badge badge-lg badge-success score d-block w-100">4.5</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-lg badge-danger score d-block w-100" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_update_role" style="cursor: pointer;">
                                        2.3
                                        <i class="fas fa-exclamation-triangle ps-2"></i>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-lg badge-success score d-block w-100">3</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-lg badge-success score d-block w-100">4.1</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-lg badge-danger score d-block w-100" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_update_role" style="cursor: pointer;">
                                        2.9
                                        <i class="fas fa-exclamation-triangle ps-2"></i>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-lg badge-success score d-block w-100">4.8</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-lg badge-success score d-block w-100">4.3</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-lg badge-danger score d-block w-100" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_update_role" style="cursor: pointer;">
                                        2.1
                                        <i class="fas fa-exclamation-triangle ps-2"></i>
                                    </span>
                                </td>
                                <td class="text-center" style="width: 50px">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#notes">
                                            Summary
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#notes">
                                            Export
                                        </button>
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
        </div>
    </div>

    {{-- modal --}}
    @foreach ($employees as $index => $employee)
        <div class="modal fade" id="kt_modal_update_role" tabindex="-1" style="display: none;" aria-modal="true"
            role="dialog">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered mw-750px">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header">
                        <!--begin::Modal title-->
                        <h2 class="fw-bold">Update IDP</h2>
                        <!--end::Modal title-->

                        <!--begin::Close-->
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->

                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-2 mt-5">
                        <!--begin::Form-->
                        <form id="kt_modal_update_role_form" class="form fv-plugins-bootstrap5 fv-plugins-framework"
                            action="#">
                            <!--begin::Scroll-->
                            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_role_scroll"
                                data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                                data-kt-scroll-max-height="auto"
                                data-kt-scroll-dependencies="#kt_modal_update_role_header"
                                data-kt-scroll-wrappers="#kt_modal_update_role_scroll" data-kt-scroll-offset="300px"
                                style="">
                                <!--begin::Input group-->
                                <div class="fv-row mb-10 fv-plugins-icon-container">
                                    <!--begin::Label-->
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <span class="required">Employee Name</span>
                                    </label>
                                    <!--end::Label-->

                                    <!--begin::Input-->
                                    <input class="form-control form-control-solid" placeholder="Enter a role name"
                                        name="role_name" value="{{ $employee->name }}">
                                    <!--end::Input-->
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    </div>
                                </div>
                                <!--end::Input group-->

                                <div class="col-lg-12 mb-10">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <span class="required">Development Program</span>
                                    </label>
                                    <select name="idp" aria-label="Select a Country" data-control="select2"
                                        data-placeholder="Select Programs..."
                                        class="form-select form-select-solid form-select-lg fw-semibold">
                                        <option value="">Select Development Program</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AF">Feedback</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AF">Self Development</option>
                                        <option data-kt-flag="flags/aland-islands.svg" value="AX">Shadowing</option>
                                        <option data-kt-flag="flags/albania.svg" value="AL">On Job Development
                                        </option>
                                        <option data-kt-flag="flags/algeria.svg" value="DZ">Mentoring</option>
                                        <option data-kt-flag="flags/american-samoa.svg" value="AS">Training</option>
                                    </select>
                                </div>

                                <div class="col-lg-12 mb-10">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <span class="required">Category</span>
                                    </label>
                                    <select name="idp" aria-label="Select a Country" data-control="select2"
                                        data-placeholder="Select categories..."
                                        class="form-select form-select-solid form-select-lg fw-semibold" multiple>
                                        <option value="">Select Category</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AF">Feedback</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AF">Self Development</option>
                                        <option data-kt-flag="flags/aland-islands.svg" value="AX">Shadowing</option>
                                        <option data-kt-flag="flags/albania.svg" value="AL">On Job Development
                                        </option>
                                        <option data-kt-flag="flags/algeria.svg" value="DZ">Mentoring</option>
                                        <option data-kt-flag="flags/american-samoa.svg" value="AS">Training</option>
                                    </select>
                                </div>

                                <!--begin::Permissions-->
                                <div class="col-lg-12 fv-row mb-10">
                                    <label for="" class="fs-5 fw-bold form-label mb-2">Development Target</label>
                                    <textarea class="form-control" data-kt-autosize="true"></textarea>
                                </div>

                                <div class="col-lg-12 fv-row mb-5">
                                    <label for="" class="fs-5 fw-bold form-label mb-2">Due Date</label>
                                    <input class="form-control form-control-solid" placeholder="Pick date & time"
                                        id="kt_datepicker_7" />
                                </div>
                                <!--end::Permissions-->
                            </div>
                            <!--end::Scroll-->

                            <!--begin::Actions-->
                            <div class="text-center pt-15">
                                <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    Discard
                                </button>

                                <button type="submit" class="btn btn-primary" data-kt-roles-modal-action="submit">
                                    <span class="indicator-label">
                                        Submit
                                    </span>
                                    <span class="indicator-progress">
                                        Please wait... <span
                                            class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                            </div>
                            <!--end::Actions-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Modal body-->
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
    @endforeach
    {{-- end of modal --}}

    {{-- modal --}}
    <div class="modal fade" id="notes" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-1000px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2 class="fw-bold">Summary</h2>
                    <!--end::Modal title-->

                    <!--begin::Close-->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->

                <!--begin::Modal body-->
                <div class="modal-body scroll-y mx-2">
                    <!--begin::Form-->
                    <form id="kt_modal_update_role_form" class="form fv-plugins-bootstrap5 fv-plugins-framework"
                        action="#">
                        <!--begin::Scroll-->
                        <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_role_scroll"
                            data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                            data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_update_role_header"
                            data-kt-scroll-wrappers="#kt_modal_update_role_scroll" data-kt-scroll-offset="300px"
                            style="">
                            <h3 class="card-title mb-5">I. Development Program</h3>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-light ">
                                        <!--begin::Body-->
                                        <div class="card-body">
                                            <!--begin::Top-->
                                            <div class="mb-7">
                                                <!--begin::Title-->
                                                <h2 class="fs-1 text-gray-800 w-bolder mb-6">
                                                    Area of Strength
                                                </h2>
                                                <!--end::Title-->

                                                <!--begin::Text-->
                                                <p class="fw-semibold fs-6 text-gray-600">
                                                    Area of Strength refers to key skills, qualities, or competencies where
                                                    an individual or team excels.
                                                </p>
                                                <!--end::Text-->

                                                {{-- accordion --}}
                                                <div class="mt-10 accordion accordion-icon-toggle">
                                                    <!--begin::Section-->
                                                    <div class="m-0">
                                                        <!--begin::Heading-->
                                                        <div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed"
                                                            data-bs-toggle="collapse" data-bs-target="#strength_1"
                                                            aria-expanded="false">
                                                            <!--begin::Icon-->
                                                            <div
                                                                class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                                                <i
                                                                    class="fas fa-minus-square toggle-on text-primary fs-1"></i>
                                                                <i class="fas fa-plus-square toggle-off fs-1"></i>
                                                            </div>
                                                            <!--end::Icon-->

                                                            <!--begin::Title-->
                                                            <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                                                Customer Focus
                                                            </h4>
                                                            <!--end::Title-->
                                                        </div>
                                                        <!--end::Heading-->

                                                        <!--begin::Body-->
                                                        <div id="strength_1" class="fs-6 ms-1 collapse" style="">
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 ">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                        </div>
                                                        <!--end::Content-->


                                                        <!--begin::Separator-->
                                                        <div class="separator separator-dashed"></div>
                                                        <!--end::Separator-->
                                                    </div>
                                                    <!--end::Section-->

                                                    <!--begin::Section-->
                                                    <div class="m-0">
                                                        <!--begin::Heading-->
                                                        <div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed"
                                                            data-bs-toggle="collapse" data-bs-target="#strength_2"
                                                            aria-expanded="false">
                                                            <!--begin::Icon-->
                                                            <div
                                                                class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                                                <i
                                                                    class="fas fa-minus-square toggle-on text-primary fs-1"></i>
                                                                <i class="fas fa-plus-square toggle-off fs-1"></i>
                                                            </div>
                                                            <!--end::Icon-->

                                                            <!--begin::Title-->
                                                            <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                                                Interpersonal
                                                                Skill
                                                            </h4>
                                                            <!--end::Title-->
                                                        </div>
                                                        <!--end::Heading-->

                                                        <!--begin::Body-->
                                                        <div id="strength_2" class="fs-6 ms-1 collapse" style="">
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 ">

                                                                </div>
                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                        </div>
                                                        <!--end::Content-->


                                                        <!--begin::Separator-->
                                                        <div class="separator separator-dashed"></div>
                                                        <!--end::Separator-->
                                                    </div>
                                                    <!--end::Section-->
                                                </div>
                                            </div>
                                            <!--end::Top-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="card bg-light ">
                                        <!--begin::Body-->
                                        <div class="card-body">
                                            <!--begin::Top-->
                                            <div class="mb-7">
                                                <!--begin::Title-->
                                                <h2 class="fs-1 text-gray-800 w-bolder mb-6">
                                                    Area of Weakness
                                                </h2>
                                                <!--end::Title-->

                                                <!--begin::Text-->
                                                <p class="fw-semibold fs-6 text-gray-600">
                                                    Area of Weakness refers to skills, competencies, or attributes that
                                                    require improvement.
                                                </p>
                                                <!--end::Text-->

                                                {{-- accordion --}}
                                                <div class="mt-10">
                                                    <!--begin::Section-->
                                                    <div class="m-0">
                                                        <!--begin::Heading-->
                                                        <div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed"
                                                            data-bs-toggle="collapse" data-bs-target="#weakness"
                                                            aria-expanded="false">
                                                            <!--begin::Icon-->
                                                            <div
                                                                class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                                                <i
                                                                    class="fas fa-minus-square toggle-on text-primary fs-1"></i>
                                                                <i class="fas fa-plus-square toggle-off fs-1"></i>
                                                            </div>
                                                            <!--end::Icon-->

                                                            <!--begin::Title-->
                                                            <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                                                Leading & Motivating
                                                            </h4>
                                                            <!--end::Title-->
                                                        </div>
                                                        <!--end::Heading-->

                                                        <!--begin::Body-->
                                                        <div id="weakness" class="fs-6 ms-1 collapse" style="">
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 mb-n1">

                                                                <!--end::Item-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">

                                                            </div>
                                                            </div>
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div class="mb-4">
                                                                <!--begin::Item-->
                                                                <div class="d-flex align-items-center ps-10 ">

                                                            </div>
                                                            <!--end::Item-->
                                                        </div>
                                                        <!--end::Content-->


                                                        <!--begin::Separator-->
                                                        <div class="separator separator-dashed"></div>
                                                        <!--end::Separator-->
                                                    </div>
                                                    <!--end::Section-->
                                                </div>
                                            </div>
                                            <!--end::Top-->
                                        </div>
                                        <!--end::Body-->
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
                                                @forelse ($employees as $index => $employee)
                                                    <tr class=" fs-8 ">
                                                        <td class="text-center">VISION & BUSINESS SENSE </td>
                                                        <td class="text-center">
                                                            Superior (DGM & GM) + DIC PUR + BOD Members
                                                        </td>
                                                        <td class="text-center">
                                                            Level Up of CONFIDENCE Level as MANAGER
                                                        </td>
                                                        <td class="text-center">
                                                            2nd SEM Year 2024 (July-Dec)
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">No employees
                                                            found</td>
                                                    </tr>
                                                @endforelse
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
                                                    <th class="text-center" style="width: 100px">
                                                        Development Achievement
                                                    </th>
                                                    <th class="text-center" style="width: 50px">
                                                        Next Action
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($employees as $index => $employee)
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">Data Not
                                                            Available</td>
                                                    </tr>
                                                @empty
                                                    <tr class=" fs-8 ">
                                                        <td class="text-center">VISION & BUSINESS SENSE </td>
                                                        <td class="text-center">
                                                            Superior (DGM & GM) + DIC PUR + BOD Members
                                                        </td>
                                                        <td class="text-center">
                                                            Level Up of CONFIDENCE Level as MANAGER
                                                        </td>
                                                        <td class="text-center">
                                                            2nd SEM Year 2024 (July-Dec)
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
                                                    <th class="text-center" style="width: 100px">
                                                        Evaluation Result
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($employees as $index => $employee)
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">Data Not
                                                            Available</td>
                                                    </tr>
                                                @empty
                                                    <tr class=" fs-8 ">
                                                        <td class="text-center">VISION & BUSINESS SENSE </td>
                                                        <td class="text-center">
                                                            Superior (DGM & GM) + DIC PUR + BOD Members
                                                        </td>
                                                        <td class="text-center">
                                                            Level Up of CONFIDENCE Level as MANAGER
                                                        </td>
                                                        <td class="text-center">
                                                            2nd SEM Year 2024 (July-Dec)
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Scroll-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    {{-- end of modal --}}
@endsection

@push('custom-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
