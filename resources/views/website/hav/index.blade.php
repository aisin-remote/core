@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('main')
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <!--begin::Row-->
        <div class="row g-5 gx-xl-10 mb-2 mb-xl-10">
            @php
                $titles = [
                    '13. Maximal Contributor',
                    '7. Top Performer',
                    '3. Future Star',
                    '1. Star',
                    '14. Contributor',
                    '8. Strong Performer',
                    '4. Potential Candidate',
                    '2. Future Star',
                    '15. Minimal Contributor',
                    '9. Career Person',
                    '6. Candidate',
                    '5. Raw Diamond',
                    '16. Dead Wood',
                    '12. Problem Employee',
                    '11. Unfit Employee',
                    '10. Most Unfit Employee',
                ];

                $borderColors = [
                    'bg-light-secondary',
                    'bg-light-warning',
                    'bg-light-success',
                    'bg-light-primary',
                    'bg-light-secondary',
                    'bg-light-warning',
                    'bg-light-success',
                    'bg-light-success',
                    'bg-light-secondary',
                    'bg-light-warning',
                    'bg-light-warning',
                    'bg-light-warning',
                    'bg-light-secondary',
                    'bg-light-secondary',
                    'bg-light-secondary',
                    'bg-light-secondary',
                ];

                $textColors = [
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                    'text-dark',
                ];

                $percentage = [
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '3.3%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '3.3%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                    '0.0%',
                ];
            @endphp

            <div class="row mt-5">
                @for ($i = 0; $i < count($titles); $i++)
                    <div class="col-3">
                        <div class="card {{ $borderColors[$i] }} card-xl-stretch mb-xl-8 card-clickable"
                            data-title="{{ $titles[$i] }}">
                            <div class="card-body" style="padding: 10px;">
                                <a href="#"
                                    class="card-title fw-bold text-center {{ $textColors[$i] }} fs-5 mb-3 d-block">
                                    {{ $titles[$i] }}
                                </a>
                                <div class="card-body bg-white" style="height: 100px;"></div>
                                <div class="py-1 text-center">
                                    <span class="text-danger fw-bold me-2">{{ $percentage[$i] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <!--end::Content container-->
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Human Assets Value</h3>
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
            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Quadran</th>
                            <th>Departement</th>
                            <th>Grade</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>000019</td>
                            <td>Arif Kurniawan Dwi Haryadi</td>
                            <td>
                                <span class="badge badge-lg badge-warning">
                                    6 - [Candidate]
                                </span>
                            </td>
                            <td>PRO EC</td>
                            <td>11A</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light-warning">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-light-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <a class="btn btn-sm btn-light-info">
                                    <i class="fas fa-file-export"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>000026</td>
                            <td>Tegar Avrilla Kharismawan</td>
                            <td>
                                <span class="badge badge-lg badge-warning">
                                    8 - [Strong Performer]
                                </span>
                            </td>
                            <td>MMA</td>
                            <td>10A</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-light-warning">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-light-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <a href="{{ asset('assets/file/Tegar_Avrilia_HAV_Summary.xlsm') }}"
                                    class="btn btn-sm btn-light-info" download>
                                    <i class="fas fa-file-export"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="app-content  container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Pergeseran HAV</h3>
                </div>
                <div class="card-body">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Employee Name</th>
                                <th>Company</th>
                                <th>Grade</th>
                                <th>Age</th>
                                <th>Prev Hav</th>
                                <th>Current HAV</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>
        <!--end::Row-->
        <!--end::Content container-->
    </div>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const data2025 = {
                labels: ['Pria', 'Wanita'],
                datasets: [{
                    label: 'Employee 2025',
                    data: [556, 120],
                    backgroundColor: ['#50C878', '#3B5B92'],
                    hoverBackgroundColor: ['#C5E1C5', '#5A75C9']
                }]
            };

            const data2024 = {
                labels: ['Pria', 'Wanita'],
                datasets: [{
                    label: 'Employee 2024',
                    data: [405, 174],
                    backgroundColor: ['#50c878', '#3b5b92'],
                    hoverBackgroundColor: ['#c5e1c5', '#5a75c9']
                }]
            };

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            };

            new Chart(document.getElementById('employeeChart2025'), {
                type: 'doughnut',
                data: data2025,
                options: options
            });

            new Chart(document.getElementById('employeeChart2024'), {
                type: 'doughnut',
                data: data2024,
                options: options
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            var strongPerformers = [{
                npk: "000026",
                name: "Tegar Avrilla Kharismawan",
                department: "MMA",
                grade: "10A"
            }, ];

            var candidates = [{
                npk: "000019",
                name: "Arif Kurniawan Dwi Haryadi",
                department: "PRO EC",
                grade: "10A"
            }, ];

            $(".card-clickable").on("click", function() {
                var title = $(this).data("title");

                $("#infoModalLabel").text(title);
                $("#modalEmployeeList").empty();

                var selectedEmployees = [];

                if (title === "8. Strong Performer") {
                    selectedEmployees = strongPerformers;
                } else if (title === "6. Candidate") {
                    selectedEmployees = candidates;
                }

                if (selectedEmployees.length > 0) {
                    selectedEmployees.forEach(function(employee) {
                        $("#modalEmployeeList").append(`
            <li class="list-group-item border-0 py-3 d-flex align-items-center shadow-sm rounded" style="background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff;">
                <div>
                    <strong class="fs-6">${employee.name}</strong><br>
                    <small class="opacity-75">NPK: ${employee.npk} | ${employee.department} | Grade: ${employee.grade}</small>
                </div>
            </li>
        `);
                    });

                    $("#infoModal").modal("show");
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const data2025 = {
                labels: ['Pria', 'Wanita'],
                datasets: [{
                    label: 'Employee 2025',
                    data: [556, 120],
                    backgroundColor: ['#50C878', '#3B5B92'],
                    hoverBackgroundColor: ['#C5E1C5', '#5A75C9']
                }]
            };

            const data2024 = {
                labels: ['Pria', 'Wanita'],
                datasets: [{
                    label: 'Employee 2024',
                    data: [405, 174],
                    backgroundColor: ['#50c878', '#3b5b92'],
                    hoverBackgroundColor: ['#c5e1c5', '#5a75c9']
                }]
            };

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            };

            new Chart(document.getElementById('employeeChart2025'), {
                type: 'doughnut',
                data: data2025,
                options: options
            });

            new Chart(document.getElementById('employeeChart2024'), {
                type: 'doughnut',
                data: data2024,
                options: options
            });
        });
    </script>
@endpush
