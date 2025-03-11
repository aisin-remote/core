@extends('layouts.root.main')

@section('main')

<style>
   table {
    width: 90%; /* Atur agar tabel memiliki lebar proporsional */
    table-layout: fixed; /* Pastikan kolom memiliki ukuran tetap */
    border-collapse: collapse;
}

th, td {
    text-align: center; /* Center semua isi sel termasuk header */
    vertical-align: middle; /* Pastikan teks tetap di tengah secara vertikal */
    word-wrap: break-word; /* Pecah kata jika terlalu panjang */
    white-space: normal;
    padding: 12px;
    border: 1px solid #ddd; /* Tambahkan border agar lebih rapi */
}

th {
    background-color: #f8f9fa; /* Warna latar belakang header */
    font-weight: bold;
    text-align: center !important; /* Pastikan header benar-benar di tengah */
}


</style>

    <div class="container mt-2">
        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-4">Employee Profile</h4>
            <div class="row">
                <!-- Kiri: Foto Profil -->
                <div class="col-md-3 text-center">
                    <p class="fw-bold">Profile Picture</p>
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="Employee Photo"A
                    class="shadow img-fluid" width="500">
                </div>

                <!-- Kanan: Detail Profil -->
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="fw-bold">Employee Name</label>
                            <input type="text" class="form-control" value="{{ $employee->name }}" readonly>

                            <label class="fw-bold mt-2">Identity No</label>
                            <input type="text" class="form-control" value="{{ $employee->identity_number }}" readonly>

                            <label class="fw-bold mt-2">No. KTP</label>
                            <input type="text" class="form-control" value="{{ $employee->npk }}" readonly>

                            <label class="fw-bold mt-2">Gender</label>
                            <div>
                                <input type="radio" {{ $employee->gender == 'Male' ? 'checked' : '' }}> Male
                                <input type="radio" {{ $employee->gender == 'Female' ? 'checked' : '' }}> Female
                            </div>

                            <label class="fw-bold mt-2">Birth Date</label>
                            <input type="text" class="form-control" value="{{ $employee->birthday_date }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold">Aisin Entry Date</label>
                            <input type="text" class="form-control" value="{{ $employee->aisin_entry_date }}" readonly>

                            <label class="fw-bold mt-2">Working Period</label>
                            <div class="d-flex">
                                <input type="text" class="form-control" value="{{ $employee->working_period }}" readonly>
                                <span class="ms-2 align-self-center">Years</span>
                            </div>

                            <label class="fw-bold mt-2">Company Group</label>
                            <input type="text" class="form-control" value="{{ $employee->company_group }}" readonly>

                            <label class="fw-bold mt-2">Function Group</label>
                            <input type="text" class="form-control" value="{{ $employee->function }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="fw-bold">Company Name</label>
                    <input type="text" class="form-control" value="{{ $employee->company_name }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Position</label>
                    <input type="text" class="form-control" value="{{ $employee->position }}" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="fw-bold">Position Name</label>
                    <input type="text" class="form-control" value="{{ $employee->position_name }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Grade</label>
                    <input type="text" class="form-control" value="{{ $employee->grade }}" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="fw-bold">Last Promote Date</label>
                    <input type="text" class="form-control" value="{{ $employee->last_promote_date }}" readonly>
                </div>
            </div>
        </div>


            <div class="card  mb-5 mb-xl-10">
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_signin_method">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Educational Background</h3>
                    </div>
                </div>

                <div id="kt_account_settings_signin_method" class="collapse show">
                    <div class="card-body border-top p-9">
                        <div class="d-flex flex-wrap align-items-center">
                            <div id="kt_signin_email">
                                <div class="fs-6 fw-bold mb-1">S1 - Teknik Industri</div>
                                <div class="fw-semibold text-gray-600">Universitas Indonesia</div>
                            </div>

                            <div id="kt_signin_email_button" class="ms-auto">
                                <button class="btn btn-light btn-active-light-primary">2019 - 2022</button>
                            </div>
                        </div>

                        <div class="separator separator-dashed my-6"></div>

                        <div class="d-flex flex-wrap align-items-center mb-10">
                            <!--begin::Label-->
                            <div id="kt_signin_password">
                                <div class="fs-6 fw-bold mb-1">S2 - Manajement Industri</div>
                                <div class="fw-semibold text-gray-600">Universitas Harvard</div>
                            </div>
                            <!--end::Label-->

                            <!--begin::Action-->
                            <div id="kt_signin_password_button" class="ms-auto">
                                <button class="btn btn-light btn-active-light-primary">2022 - Current</button>
                            </div>
                            <!--end::Action-->
                        </div>
                        <!--end::Password-->

                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Sign-in Method-->

             <!--begin::HAV-->
             <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_connected_accounts" aria-expanded="true"
                    aria-controls="kt_account_connected_accounts">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Historical Human Assets Value</h3>
                    </div>
                </div>
                <!--end::Card header-->

                 <!--begin::Content-->
                 <div id="kt_account_settings_signin_method" class="collapse show">
                    <!--begin::Card body-->
                    <div class="card-body border-top p-9">
                        <!--begin::Email Address-->
                        <div class="d-flex flex-wrap align-items-center">
                            <!--begin::Label-->
                            <div id="kt_signin_email">
                                <div class="fs-6 fw-bold mb-1">2022 - HAV Result</div>
                                <div class="fw-semibold text-gray-600">Future Star [2]</div>
                            </div>
                            <!--end::Label-->

                <!--begin::Action-->
                <div id="kt_signin_email_button" class="ms-auto">
                    <button class="btn btn-light-primary">See Details</button>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Email Address-->

            <!--begin::Separator-->
            <div class="separator separator-dashed my-6"></div>
            <!--end::Separator-->

            <!--begin::Password-->
            <div class="d-flex flex-wrap align-items-center mb-10">
                <!--begin::Label-->
                <div id="kt_signin_password">
                    <div class="fs-6 fw-bold mb-1">2023 - HAV Result</div>
                    <div class="fw-semibold text-gray-600">Potential Candidate [4]</div>
                </div>
                <!--end::Label-->

                <!--begin::Action-->
                <div id="kt_signin_password_button" class="ms-auto">
                    <button class="btn btn-light-primary">See Details</button>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Password-->

        </div>
        <!--end::Card body-->
    </div>
    <!--end::Content-->
</div>
<!--end::Connected Accounts-->

<!--begin::Notifications-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
        data-bs-target="#kt_account_email_preferences" aria-expanded="true"
        aria-controls="kt_account_email_preferences">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Working Experience</h3>
        </div>
    </div>
    <!--begin::Card header-->

    <!--begin::Content-->
    <div id="kt_activity_year" class="card-body ps-5 tab-pane fade show active" role="tabpanel"
        aria-labelledby="kt_activity_year_tab">
        <!--begin::Timeline-->
        <div class="timeline timeline-border-dashed">
            <!--begin::Timeline item-->
            <div class="timeline-item">
                <!--begin::Timeline line-->
                <div class="timeline-line"></div>
                <!--end::Timeline line-->

                <!--begin::Timeline icon-->
                <div class="timeline-icon">
                    <i class="ki-duotone ki-abstract-26 fs-2 text-gray-500"><span
                            class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Timeline icon-->

                <!--begin::Timeline content-->
                <div class="timeline-content mb-10 mt-n1">
                    <!--begin::Timeline heading-->
                    <div class="mb-5 pe-3">
                        <!--begin::Title-->
                        <a href="#"
                            class="fs-5 fw-semibold text-gray-800 text-hover-primary mb-2">Human Resource
                            Manager</a>
                        <!--end::Title-->

                        <!--begin::Description-->
                        <div class="d-flex align-items-center mt-1 fs-6">
                            <!--begin::Info-->
                            <div class="text-muted me-2 fs-7">2024 - Now</div>
                            <!--end::Info-->

                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Timeline heading-->

                    <!--begin::Timeline details-->
                    <div class="overflow-auto pb-5">
                        <!--begin::Record-->
                        <div
                            class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-5">
                            <!--begin::Title-->
                            <a href="/metronic8/demo1/apps/projects/project.html"
                                class="fs-5 text-gray-900 text-hover-primary fw-semibold w-375px min-w-200px">Meeting
                                with customer</a>
                            <!--end::Title-->

                            <!--begin::Label-->
                            <div class="min-w-175px pe-2">
                                <span class="badge badge-light text-muted">Application Design</span>
                            </div>
                            <!--end::Label-->

                            <!--begin::Users-->
                            <div class="symbol-group symbol-hover flex-nowrap flex-grow-1 min-w-100px pe-2">


                                <!--begin::User-->
                                <div class="symbol symbol-circle symbol-25px">
                                </div>
                                <!--end::User-->
                            </div>
                            <!--end::Users-->



                            <!--begin::Action-->
                            <a href="/metronic8/demo1/apps/projects/project.html"
                                class="btn btn-sm btn-light btn-active-light-primary">View</a>
                            <!--end::Action-->
                        </div>
                        <!--end::Record-->

                        <!--begin::Record-->
                        <div
                            class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-0">
                            <!--begin::Title-->
                            <a href="/metronic8/demo1/apps/projects/project.html"
                                class="fs-5 text-gray-900 text-hover-primary fw-semibold w-375px min-w-200px">Project
                                Delivery Preparation</a>
                            <!--end::Title-->

                            <!--begin::Label-->
                            <div class="min-w-175px">
                                <span class="badge badge-light text-muted">CRM System Development</span>
                            </div>
                            <!--end::Label-->

                            <!--begin::Users-->
                            <div class="symbol-group symbol-hover flex-nowrap flex-grow-1 min-w-100px">
                                <!--begin::User-->
                                <div class="symbol symbol-circle symbol-25px">
                                </div>
                                <!--end::User-->
                            </div>
                            <!--end::Users-->

                            <!--begin::Action-->
                            <a href="/metronic8/demo1/apps/projects/project.html"
                                class="btn btn-sm btn-light btn-active-light-primary">View</a>
                            <!--end::Action-->
                        </div>
                        <!--end::Record-->
                    </div>
                    <!--end::Timeline details-->

                </div>
                <!--end::Timeline content-->
            </div>
            <!--end::Timeline item-->
            <!--begin::Timeline item-->
            <div class="timeline-item">
                <!--begin::Timeline line-->
                <div class="timeline-line"></div>
                <!--end::Timeline line-->

                <!--begin::Timeline icon-->
                <div class="timeline-icon">
                    <i class="ki-duotone ki-abstract-26 fs-2 text-gray-500"><span
                            class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Timeline icon-->

                <!--begin::Timeline content-->
                <div class="timeline-content mb-10 mt-n1">
                    <!--begin::Timeline heading-->
                    <div class="pe-3 mb-5">
                        <!--begin::Title-->
                        <div class="fs-5 fw-semibold mb-2">
                            Human Resource Section Head
                        </div>
                        <!--end::Title-->

                        <!--begin::Description-->
                        <div class="d-flex align-items-center mt-1 fs-6">
                            <!--begin::Info-->
                            <div class="text-muted me-2 fs-7">2018 - 2024</div>
                            <!--end::Info-->

                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Timeline heading-->
                </div>
                <!--end::Timeline content-->
            </div>
            <!--end::Timeline item-->

            <!--begin::Timeline item-->
            <div class="timeline-item">
                <!--begin::Timeline line-->
                <div class="timeline-line"></div>
                <!--end::Timeline line-->

                <!--begin::Timeline icon-->
                <div class="timeline-icon">
                    <i class="ki-duotone ki-abstract-26 fs-2 text-gray-500"><span
                            class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Timeline icon-->

                <!--begin::Timeline content-->
                <div class="timeline-content mb-10 mt-n1">
                    <!--begin::Timeline heading-->
                    <div class="pe-3 mb-5">
                        <!--begin::Title-->
                        <div class="fs-5 fw-semibold mb-2">Human Resource Staff</div>
                        <!--end::Title-->

                        <!--begin::Description-->
                        <div class="d-flex align-items-center mt-1 fs-6">
                            <!--begin::Info-->
                            <div class="text-muted me-2 fs-7">2010 - 2018</div>
                            <!--end::Info-->

                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Timeline heading-->

                    <!--begin::Timeline details-->
                    <div class="overflow-auto pb-5">
                        <!--begin::Record-->
                        <div
                            class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-5">
                            <!--begin::Title-->
                            <a href="/metronic8/demo1/apps/projects/project.html"
                                class="fs-5 text-gray-900 text-hover-primary fw-semibold w-375px min-w-200px">Meeting
                                with customer</a>
                            <!--end::Title-->

                            <!--begin::Label-->
                            <div class="min-w-175px pe-2">
                                <span class="badge badge-light text-muted">Application Design</span>
                            </div>
                            <!--end::Label-->

                            <!--begin::Users-->
                            <div class="symbol-group symbol-hover flex-nowrap flex-grow-1 min-w-100px pe-2">
                                <!--begin::User-->
                                <div class="symbol symbol-circle symbol-25px">

                                </div>
                                <!--end::User-->
                            </div>
                            <!--end::Users-->



                            <!--begin::Action-->
                            <a href="/metronic8/demo1/apps/projects/project.html"
                                class="btn btn-sm btn-light btn-active-light-primary">View</a>
                            <!--end::Action-->
                        </div>
                        <!--end::Record-->

                        <!--begin::Record-->
                        <div
                            class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-0">
                            <!--begin::Title-->
                            <a href="/metronic8/demo1/apps/projects/project.html"
                                class="fs-5 text-gray-900 text-hover-primary fw-semibold w-375px min-w-200px">Project
                                Delivery Preparation</a>
                            <!--end::Title-->

                            <!--begin::Label-->
                            <div class="min-w-175px">
                                <span class="badge badge-light text-muted">CRM System Development</span>
                            </div>
                            <!--end::Label-->

                            <!--begin::Users-->
                            <div class="symbol-group symbol-hover flex-nowrap flex-grow-1 min-w-100px">
                                <!--begin::User-->
                                <div class="symbol symbol-circle symbol-25px">

                                </div>
                                <!--end::User-->

                                <!--begin::User-->
                                <div class="symbol symbol-circle symbol-25px">

                                </div>
                                <!--end::User-->
                            </div>
                            <!--end::Users-->


                            <!--begin::Action-->
                            <a href="/metronic8/demo1/apps/projects/project.html"
                                class="btn btn-sm btn-light btn-active-light-primary">View</a>
                            <!--end::Action-->
                        </div>
                        <!--end::Record-->
                    </div>
                    <!--end::Timeline details-->
                    <!--end::Timeline details-->
                </div>
                <!--end::Timeline content-->
            </div>
            <!--end::Timeline item-->
        </div>
        <!--end::Timeline-->
    </div>
    <!--end::Content-->
</div>
<!--end::Notifications-->

<!--begin::Notifications-->
<div class="card  mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
        data-bs-target="#kt_account_notifications" aria-expanded="true"
        aria-controls="kt_account_notifications">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Strength</h3>
        </div>
    </div>
    <!--begin::Card header-->

    <!--begin::Content-->
    <div id="kt_account_settings_notifications" class="collapse show">
        <!--begin::Form-->
        <form class="form">
            <!--begin::Card body-->
            <div class="card-body border-top px-9 pt-3 pb-4">
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table table-row-dashed border-gray-300 align-middle gy-6">
                        <tbody class="fs-6 fw-semibold">
                            <!--begin::Table row-->
                            <tr>
                                <td>Customer Focus</td>
                                <td>
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="1"
                                            id="billing1" checked data-kt-settings-notification="email" />
                                        <label class="form-check-label ps-2" for="billing1"></label>
                                    </div>
                                </td>
                            </tr>
                            <!--begin::Table row-->

                            <!--begin::Table row-->
                            <tr>
                                <td>Interpersonal Skill</td>
                                <td>
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value=""
                                            id="team2" data-kt-settings-notification="phone" />
                                        <label class="form-check-label ps-2" for="team2"></label>
                                    </div>
                                </td>
                            </tr>
                            <!--begin::Table row-->

                            <!--begin::Table row-->
                            <tr>
                                <td>Leadership</td>
                                <td>
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value=""
                                            id="project2" checked data-kt-settings-notification="phone" />
                                        <label class="form-check-label ps-2" for="project2"></label>
                                    </div>
                                </td>
                            </tr>
                            <!--begin::Table row-->

                            <!--begin::Table row-->
                            <tr>
                                <td class="border-bottom-0">Managerial Skill</td>
                                <td class="border-bottom-0">
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value=""
                                            id="newsletter2" data-kt-settings-notification="phone" />
                                        <label class="form-check-label ps-2" for="newsletter2"></label>
                                    </div>
                                </td>
                            </tr>
                            <!--begin::Table row-->
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </form>
        <!--end::Form-->
    </div>
    <!--end::Content-->
</div>

             <!--begin::HAV-->
             <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_connected_accounts" aria-expanded="true"
                    aria-controls="kt_account_connected_accounts">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Historical Performance Appraisal</h3>
                    </div>
                </div>
                <!--end::Card header-->

                 <!--begin::Content-->
                 <div id="kt_account_settings_signin_method" class="collapse show">
                    <!--begin::Card body-->
                    <div class="card-body border-top p-9">
                        <!--begin::Email Address-->
                        <div class="d-flex flex-wrap align-items-center">
                            <!--begin::Label-->
                            <div id="kt_signin_email">
                                <div class="fs-6 fw-bold mb-1">2020 - PK Result</div>
                                <P>Notes </P>
                                <div class="fw-semibold text-gray-600">B5+</div>
                            </div>
                            <!--end::Label-->

                <!--begin::Action-->
                <div id="kt_signin_email_button" class="ms-auto">
                    <button class="btn btn-light-primary">See Details</button>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Email Address-->

            <!--begin::Separator-->
            <div class="separator separator-dashed my-6"></div>
            <!--end::Separator-->

            <!--begin::Password-->
            <div class="d-flex flex-wrap align-items-center mb-10">
                <!--begin::Label-->
                <div id="kt_signin_password">
                    <div class="fs-6 fw-bold mb-1">2019 - PK Result</div>
                    <P>Notes</P>
                    <div class="fw-semibold text-gray-600">B5+</div>
                </div>
                <!--end::Label-->

                <!--begin::Action-->
                <div id="kt_signin_password_button" class="ms-auto">
                    <button class="btn btn-light-primary">See Details</button>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Password-->

        </div>
        <!--end::Card body-->
    </div>
    <!--end::Content-->
</div>
<!--end::Connected Accounts-->

<div class="card  mb-5 mb-xl-10">
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
        data-bs-target="#kt_account_signin_method">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Areas for Development</h3>
        </div>
    </div>

    <div id="kt_account_settings_signin_method" class="collapse show">
        <div class="card-body border-top p-9">
            <div class="d-flex flex-wrap align-items-center">
                <div id="kt_signin_email">
                    <div class="fs-6 fw-bold mb-1">Leading & Motivating</div>
                    <div class="fw-semibold text-gray-600">Memahami dan mengembangkan kompetensi bawahan</div>
                    <div class="fw-semibold text-gray-600">Menginspirasi dan memotivasi</div>
                </div>
            </div>

            <div class="separator separator-dashed my-6"></div>

            <div class="d-flex flex-wrap align-items-center mb-10">
                <!--begin::Label-->
                <div id="kt_signin_password">
                    <div class="fs-6 fw-bold mb-1">Vision & Business Sense</div>
                    <div class="fw-semibold text-gray-600">Pemahaman tentang bisnis inti dari perusahaan dan mengoptimalkan peluang-peluang
                        yang ada di dalam/luar organisasi untuk meningkatkan unjuk kerja perusahaan
                    </div>
                </div>
                <!--end::Label-->
            </div>
            <!--end::Password-->

        </div>
        <!--end::Card body-->
    </div>
    <!--end::Content-->
</div>
<!--end::Sign-in Method-->

<div class="center-table-container">
    <!-- Table 1: Development History -->
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_signin_method">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Development History</h3>
            </div>
        </div>

        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable">
                <thead>
                    <tr class="text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Due Date</th>
                        <th>Competency</th>
                        <th>Category</th>
                        <th>Course</th>
                        <th>Institution</th>
                        <th>Name</th>
                        <th>Batch</th>
                        <th>Final Result</th>
                        <th>ADC</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>29/07/2022</td>
                        <td>Vision & Business</td>
                        <td>Training/ Workshop</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
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
        <a href="{{ route('employee.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle"></i> Back
        </a>
    </div>
</div>

    </div>
@endsection
