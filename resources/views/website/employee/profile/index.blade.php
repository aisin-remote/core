@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Profile' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee - Profile' }}
@endsection

@section('main')
    <div id="kt_app_content" class="app-content  flex-column-fluid ">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container  container-fluid ">

            <!--begin::Navbar-->

            <div class="card mb-5 mb-xl-10">
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_profile_details" aria-expanded="true"
                    aria-controls="kt_account_profile_details">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Profile Details</h3>
                    </div>
                </div>


                <div id="kt_account_settings_profile_details" class="collapse show">
                    <form id="kt_account_profile_details_form" class="form">
                        <div class="card-body border-top p-9">
                            <div class="row">
                                <!-- Kiri: Foto Profil -->
                                <div class="col-md-3 text-center">
                                    <p class="fw-bold">Profile Picture</p>
                                    <img src="{{ asset('storage/' . $employee->photo) }}"
                                        alt="Employee Photo"
                                        class="shadow-sm img-fluid rounded-2"
                                        style="max-width: 200px; height: auto;">
                                </div>

                                <!-- Kanan: Detail Profil -->
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="fw-bold">Employee Name</label>
                                            <input type="text" class="form-control" value="{{ $employee->name }}" readonly>

                                            <label class="fw-bold mt-2">NPK</label>
                                            <input type="text" class="form-control" value="{{ $employee->npk }}" readonly>

                                            <label class="fw-bold mt-2">Gender</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        {{ $employee->gender == 'Male' ? 'checked' : '' }} disabled>
                                                    <label class="form-check-label">Male</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        {{ $employee->gender == 'Female' ? 'checked' : '' }} disabled>
                                                    <label class="form-check-label">Female</label>
                                                </div>
                                            </div>

                                            <label class="fw-bold mt-2">Birth Date</label>
                                            <input type="text" class="form-control" value="{{ $employee->birthday_date }}" readonly>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="fw-bold">Join Date</label>
                                            <input type="text" class="form-control" value="{{ $employee->aisin_entry_date ?? 'N/A' }}" readonly>

                                            <label class="fw-bold mt-2">Working Period</label>
                                            <div class="d-flex align-items-center">
                                                <input type="text" class="form-control" value="{{ $employee->working_period ?? '0' }}" readonly>
                                                <span class="ms-2">Years</span>
                                            </div>

                                            <label class="fw-bold mt-2">Company Group</label>
                                            <input type="text" class="form-control" value="{{ $employee->company_group }}" readonly>

                                            <label class="fw-bold mt-2">Department</label>
                                            <input type="text" class="form-control" value="{{ $employee->function }}" readonly>
                                        </div>
                                    </div>
                                </div>

                            <div class="row mt-4 g-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">Company Name</label>
                                    <input type="text" class="form-control" value="{{ $employee->company_name }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">Jabatan</label>
                                    <input type="text" class="form-control" value="{{ $employee->position }}" readonly>
                                </div>
                            </div>

                            <div class="row mt-3 g-3">
                                <div class="col-md-6">
                                    <label class="fw-bold">Position Name</label>
                                    <input type="text" class="form-control" value="{{ $employee->position_name }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold">Grade</label>
                                    <input type="text" class="form-control" value="{{ $employee->grade }}" readonly>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            </div>


                            <div class="card mb-5 mb-xl-10">
                                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                    data-bs-target="#kt_account_signin_method">
                                    <div class="card-title m-0">
                                        <h3 class="fw-bold m-0">Last Promote Date</h3>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable">
                                        <thead>
                                            <tr class="text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th>Previous Position</th>
                                                <th>Current Position</th>
                                                <th>Last Promotion Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>

                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <div class="row">
                                <!-- Card 1: Educational Background -->
                                <div class="col-md-6">
                                    <div class="card mb-5 mb-xl-10">
                                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                            data-bs-target="#kt_account_signin_method">
                                            <div class="card-title m-0">
                                                <h3 class="fw-bold m-0">Educational Background</h3>
                                            </div>
                                        </div>

                                        <div id="kt_account_settings_signin_method" class="collapse show">
                                            <div class="card-body border-top p-4"> <!-- Mengurangi padding agar card lebih kecil -->
                                                <div class="d-flex flex-wrap align-items-center">
                                                    <div id="kt_signin_email">
                                                        <div class="fs-6 fw-bold mb-1">S1 - Teknik Industri</div>
                                                        <div class="fw-semibold text-gray-600">Universitas Indonesia</div>
                                                    </div>
                                                    <div id="kt_signin_email_button" class="ms-auto">
                                                        <div class="text-muted fs-7">2019 - 2022</div>
                                                    </div>
                                                </div>

                                                <div class="separator separator-dashed my-4"></div> <!-- Mengurangi margin separator -->

                                                <div class="d-flex flex-wrap align-items-center">
                                                    <div id="kt_signin_password">
                                                        <div class="fs-6 fw-bold mb-1">S2 - Manajemen Industri</div>
                                                        <div class="fw-semibold text-gray-600">Universitas Harvard</div>
                                                    </div>
                                                    <div id="kt_signin_password_button" class="ms-auto">
                                                        <div class="text-muted fs-7">2022 - Current</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 2: Historical Human Assets Value -->
                                <div class="col-md-6">
                                    <div class="card mb-5 mb-xl-10">
                                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                            data-bs-target="#kt_account_connected_accounts" aria-expanded="true"
                                            aria-controls="kt_account_connected_accounts">
                                            <div class="card-title m-0">
                                                <h3 class="fw-bold m-0">Historical Human Assets Value</h3>
                                            </div>
                                        </div>

                                        <div id="kt_account_settings_signin_method" class="collapse show">
                                            <div class="card-body border-top p-4"> <!-- Mengurangi padding agar card lebih kecil -->
                                                <div class="d-flex flex-wrap align-items-center">
                                                    <div id="kt_signin_email">
                                                        <div class="fs-6 fw-bold mb-1">2024 - Future Star [2]</div>
                                                    </div>
                                                </div>

                                                <div class="separator separator-dashed my-4"></div>

                                                <div class="d-flex flex-wrap align-items-center">
                                                    <div id="kt_signin_password">
                                                        <div class="fs-6 fw-bold mb-1">2023 - Potential Candidate [4]</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Working Experience -->
                                <div class="col-md-6">
                                    <div class="card mb-5">
                                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                            data-bs-target="#kt_account_email_preferences" aria-expanded="true"
                                            aria-controls="kt_account_email_preferences">
                                            <div class="card-title m-0">
                                                <h3 class="fw-bold m-0">Working Experience</h3>
                                            </div>
                                        </div>

                                        <div class="separator separator-dashed my-6"></div>
                                        <div id="kt_activity_year" class="card-body ps-5 tab-pane fade show active" role="tabpanel"
                                            aria-labelledby="kt_activity_year_tab">
                                            <div class="timeline timeline-border-dashed">
                                                <!-- Item 1 -->
                                                <div class="timeline-item d-flex">
                                                    <div class="timeline-line"></div>
                                                    <div class="timeline-icon">
                                                        <i class="ki-duotone ki-abstract-26 fs-2 text-gray-500">
                                                            <span class="path1"></span><span class="path2"></span>
                                                        </i>
                                                    </div>
                                                    <div class="timeline-content flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <a href="#" class="fs-5 fw-semibold text-gray-800 text-hover-primary mb-0">
                                                                Human Resource Manager
                                                            </a>
                                                            <div class="text-muted fs-7">2024 - Present</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="separator separator-dashed my-6"></div>

                                                <!-- Item 2 -->
                                                <div class="timeline-item d-flex">
                                                    <div class="timeline-line"></div>
                                                    <div class="timeline-icon">
                                                        <i class="ki-duotone ki-abstract-26 fs-2 text-gray-500">
                                                            <span class="path1"></span><span class="path2"></span>
                                                        </i>
                                                    </div>
                                                    <div class="timeline-content flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <a href="#" class="fs-5 fw-semibold text-gray-800 text-hover-primary mb-0">
                                                                Human Resource Section Head
                                                            </a>
                                                            <div class="text-muted fs-7">2021 - 2024</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Historical Performance Appraisal -->
                                <div class="col-md-6">
                                    <div class="card mb-5">
                                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                            data-bs-target="#kt_account_connected_accounts" aria-expanded="true"
                                            aria-controls="kt_account_connected_accounts">
                                            <div class="card-title m-0">
                                                <h3 class="fw-bold m-0">Historical Performance Appraisal</h3>
                                            </div>
                                        </div>

                                        <div id="kt_account_settings_signin_method" class="collapse show">
                                            <div class="card-body border-top p-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div id="kt_signin_email">
                                                        <div class="fs-6 fw-bold mb-1">2020 - PK Result - Notes - B5+</div>
                                                    </div>
                                                </div>

                                                <div class="separator separator-dashed my-4"></div>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div id="kt_signin_email">
                                                        <div class="fs-6 fw-bold mb-1">2019 - PK Result - Notes - B5+</div>
                                                    </div>
                                                </div>

                                                <div class="separator separator-dashed my-4"></div>

                                                <div class="d-flex justify-content-between align-items-center mb-4">
                                                    <div id="kt_signin_password">
                                                        <div class="fs-6 fw-bold mb-1">2018 - PK Result - Notes - B5+</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                        <div class="row">
                                            <!-- Strength & Weakness -->
                                            <div class="col-md-6">
                                                <div class="card mb-5">
                                                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                                        data-bs-target="#kt_account_signin_method">
                                                        <div class="card-title m-0">
                                                            <h3 class="fw-bold m-0">Strength & Weakness</h3>
                                                        </div>
                                                    </div>

                                                    <div class="card-body">
                                                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable">
                                                            <thead>
                                                                <tr class="text-muted fw-bold fs-7 text-uppercase gs-0">
                                                                    <th>Strength</th>
                                                                    <th>Weakness</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <!-- Data bisa ditambahkan di sini -->
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Areas for Development -->
                                            <div class="col-md-6">
                                                <div class="card mb-5">
                                                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                                        data-bs-target="#kt_account_signin_method">
                                                        <div class="card-title m-0">
                                                            <h3 class="fw-bold m-0">Areas for Development</h3>
                                                        </div>
                                                    </div>

                                                    <div id="kt_account_settings_signin_method" class="collapse show">
                                                        <div class="card-body border-top p-4">
                                                            <div class="d-flex flex-wrap align-items-center">
                                                                <div id="kt_signin_email">
                                                                    <div class="fs-6 fw-bold mb-1">Leading & Motivating</div>
                                                                    <div class="fw-semibold text-gray-600">Memahami dan mengembangkan kompetensi bawahan</div>
                                                                    <div class="fw-semibold text-gray-600">Menginspirasi dan memotivasi</div>
                                                                </div>
                                                            </div>

                                                            <div class="separator separator-dashed my-4"></div>

                                                            <div class="d-flex flex-wrap align-items-center mb-4">
                                                                <div id="kt_signin_password">
                                                                    <div class="fs-6 fw-bold mb-1">Vision & Business Sense</div>
                                                                    <div class="fw-semibold text-gray-600">
                                                                        Pemahaman tentang bisnis inti dari perusahaan dan mengoptimalkan peluang-peluang
                                                                        yang ada di dalam/luar organisasi untuk meningkatkan unjuk kerja perusahaan.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                        <!-- Table 2: Individual Development Plan -->
                        <div class="card mb-5 mb-xl-10">
                            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                data-bs-target="#kt_account_signin_method">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Individual Development Plan</h3>
                                </div>
                            </div>

                            <div class="card-body">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable">
                                    <thead>
                                        <tr class="text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th>Development Area</th>
                                            <th>Development Program</th>
                                            <th>Development Target</th>
                                            <th>Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Leading & Motivating</td>
                                            <td>Job Assignment</td>
                                            <td>Meningkatnya pemahaman peran sebagai pemimpin yang memahami kebutuhan & perasaan bawahan/orang lain</td>
                                            <td>Smt-1 2024</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                        <!-- Tombol Back di bagian bawah card -->
                        <div class="card-footer text-end mt-4">
                            <a href="{{ route('employee.master.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left-circle"></i> Back
                            </a>
                        </div>
                    </div>

                        </div>
                    @endsection
