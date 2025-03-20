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
                    'Maximal Contributor',
                    'Top Performer',
                    'Future Star',
                    'Star',
                    'Contributor',
                    'Strong Performer',
                    'Potential Candidate',
                    'Future Star',
                    'Minimal Contributor',
                    'Career Person',
                    'Candidate',
                    'Raw Diamond',
                    'Dead Wood',
                    'Problem Employee',
                    'Unit Employee',
                    'Most Unfit Employee',
                ];

                $borderColors = [
                    'bg-light-warning',
                    'bg-light-success',
                    'bg-light-primary',
                    'bg-light-info',
                    'bg-light-secondary',
                    'bg-light-warning',
                    'bg-light-success',
                    'bg-light-primary',
                    'bg-light-danger',
                    'bg-light-dark',
                    'bg-light-secondary',
                    'bg-light-warning',
                    'bg-light-danger',
                    'bg-light-dark',
                    'bg-light-secondary',
                    'bg-light-danger',
                ];

                $textColors = [
                    'text-warning',
                    'text-success',
                    'text-primary',
                    'text-info',
                    'text-dark',
                    'text-warning',
                    'text-success',
                    'text-primary',
                    'text-danger',
                    'text-dark',
                    'text-dark',
                    'text-warning',
                    'text-danger',
                    'text-dark',
                    'text-dark',
                    'text-danger',
                ];

                $progressColors = [
                    'bg-warning',
                    'bg-success',
                    'bg-primary',
                    'bg-info',
                    'bg-dark',
                    'bg-warning',
                    'bg-success',
                    'bg-primary',
                    'bg-danger',
                    'bg-dark',
                    'bg-dark',
                    'bg-warning',
                    'bg-danger',
                    'bg-dark',
                    'bg-dark',
                    'bg-danger',
                ];
            @endphp

            <div class="row mt-5">
                @for ($i = 0; $i < count($titles); $i++)
                    <div class="col-3">
                        <!--begin: Statistics Widget 6-->
                        <div class="card {{ $borderColors[$i] }} card-xl-stretch mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body my-3">
                                <a href="#" class="card-title fw-bold {{ $textColors[$i] }} fs-5 mb-3 d-block">
                                    {{ $titles[$i] }} </a>

                                <div class="py-1">
                                    <span class="text-gray-900 fs-1 fw-bold me-2">50%</span>

                                    <span class="fw-semibold text-muted fs-7">Avarage</span>
                                </div>

                                <div class="progress h-7px {{ $progressColors[$i] }} bg-opacity-50 mt-7">
                                    <div class="progress-bar {{ $progressColors[$i] }}" role="progressbar"
                                        style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            <!--end:: Body-->
                        </div>
                        <!--end: Statistics Widget 6-->
                    </div>
                @endfor
            </div>

            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!--begin::Col-->
                <div class="col-xl-12">

                    <!--begin::Chart widget 22-->
                    <div class="card h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header position-relative py-0 border-bottom-2">
                            <!--begin::Nav-->
                            <ul class="nav nav-stretch nav-pills nav-pills-custom d-flex mt-3" role="tablist">
                                <!--begin::Item-->
                                <li class="nav-item p-0 ms-0 me-8" role="presentation">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-color-muted px-0 active" data-bs-toggle="tab"
                                        id="kt_chart_widgets_22_tab_1" href="#kt_chart_widgets_22_tab_content_1"
                                        aria-selected="true" role="tab">
                                        <!--begin::Subtitle-->
                                        <span class="nav-text fw-semibold fs-4 mb-3">
                                            Overview 2025
                                        </span>
                                        <!--end::Subtitle-->

                                        <!--begin::Bullet-->
                                        <span
                                            class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
                                        <!--end::Bullet-->
                                    </a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                            </ul>
                            <!--end::Nav-->

                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Daterangepicker(defined in src/js/layout/app.js)-->
                                <div data-kt-daterangepicker="true" data-kt-daterangepicker-opens="left"
                                    class="btn btn-sm btn-light d-flex align-items-center px-4" data-kt-initialized="1">
                                    <!--begin::Display range-->
                                    <span class="text-gray-600 fw-bold">
                                        Loading date range...
                                    </span>
                                    <!--end::Display range-->

                                    <i class="ki-duotone ki-calendar-8 text-gray-500 lh-0 fs-2 ms-2 me-0"><span
                                            class="path1"></span><span class="path2"></span><span
                                            class="path3"></span><span class="path4"></span><span
                                            class="path5"></span><span class="path6"></span></i>
                                </div>
                                <!--end::Daterangepicker-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <div class="container mt-3">
                            <h4 class="text-center mb-4">Employee Composition</h4>

                            <div class="row justify-content-center">
                                <div class="col-md-6">
                                    <canvas id="employeeChart2025" style="max-height: 300px;"></canvas>
                                    <p class="text-center small mt-2">Employee Composition 2025</p>
                                </div>

                                <div class="col-md-6">
                                    <canvas id="employeeChart2024" style="max-height: 300px;"></canvas>
                                    <p class="text-center small mt-2">Employee Composition 2024</p>
                                </div>
                            </div>
                        </div>
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



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
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


@endsection
