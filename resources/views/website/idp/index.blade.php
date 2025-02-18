@extends('layouts.root.main')

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
                        <i class="ki-duotone ki-search fs-2"></i> Search
                    </button>
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>
                        Filter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 gs-0">
                            <th style="width: 20px">No</th>
                            <th class="sticky-col" style="width: 80px">Employee Name</th>
                            <th class="text-center" style="width: 50px">Vision & Business Sense</th>
                            <th class="text-center" style="width: 50px">Customer Focus</th>
                            <th class="text-center" style="width: 50px">Interpersonal Skill</th>
                            <th class="text-center" style="width: 50px">Analysis & Judgment</th>
                            <th class="text-center" style="width: 50px">Planning & Driving Action</th>
                            <th class="text-center" style="width: 50px">Leading & Motivating</th>
                            <th class="text-center" style="width: 50px">Teamwork</th>
                            <th class="text-center" style="width: 50px">Drive & Courage</th>
                            <th class="text-center sticky-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $index => $employee)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $employee->name }}</td>
                                <td class="text-center" style="width: 50px">
                                    <span class="badge badge-lg badge-success">4.5 - Good</span>
                                </td>
                                <td class="text-center" style="width: 50px">

                                    <span class="badge badge-lg badge-danger" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_update_role" style="cursor: pointer">2.3 - Weak</span>
                                </td>
                                <td class="text-center" style="width: 50px">

                                    <span class="badge badge-lg badge-warning">3 - OK</span>
                                </td>
                                <td class="text-center" style="width: 50px">

                                    <span class="badge badge-lg badge-success">4.1 - Good</span>
                                </td>
                                <td class="text-center" style="width: 50px">

                                    <span class="badge badge-lg badge-danger" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_update_role" style="cursor: pointer">2 - Weak</span>
                                </td>
                                <td class="text-center" style="width: 50px">
                                    <span class="badge badge-lg badge-success">4.8 - Good</span>
                                </td>
                                <td class="text-center" style="width: 50px">
                                    <span class="badge badge-lg badge-success">4.3 - Good</span>
                                </td>
                                <td class="text-center" style="width: 50px">
                                    <span class="badge badge-lg badge-danger" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_update_role" style="cursor: pointer">2.9 - Weak</span>
                                </td>
                                <td class="text-center" style="width: 50px">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#notes">
                                        notes
                                    </button>
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
                                        class="form-select form-select-solid form-select-lg fw-semibold" multiple>
                                        <option value="">Select Development Program</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AF">Feedback</option>
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
        <div class="modal-dialog modal-dialog-centered mw-750px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2 class="fw-bold">Strength & Weakness</h2>
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
                            data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_update_role_header"
                            data-kt-scroll-wrappers="#kt_modal_update_role_scroll" data-kt-scroll-offset="300px"
                            style="">

                            <div class="col-lg-12 fv-row mb-10">
                                <label for="" class="fs-5 fw-bold form-label mb-2">Strength</label>
                                <textarea class="form-control" data-kt-autosize="true"></textarea>
                            </div>

                            <div class="col-lg-12 fv-row mb-10">
                                <label for="" class="fs-5 fw-bold form-label mb-2">Weakness</label>
                                <textarea class="form-control" data-kt-autosize="true"></textarea>
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

    <!-- Tambahkan SweetAlert -->
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
    </script>
    <script>
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
@endsection
