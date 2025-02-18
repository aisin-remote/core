@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@section('main')
    <div class="d-flex flex-column flex-column-fluid">

        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar  py-3 py-lg-6 ">

            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container  container-fluid d-flex flex-stack ">



                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3 ">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        RTC
                    </h1>
                    <!--end::Title-->


                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="index.html" class="text-muted text-hover-primary">
                                Home </a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->

                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            RTC </li>
                        <!--end::Item-->

                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content  flex-column-fluid ">


            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container  container-fluid ">
                <div class="row g-5 g-xl-8">
                    <!--begin::Col-->
                    <div class="col-xl-4">

                        <!--begin::Mixed Widget 1-->
                        <div class="card card-xl-stretch mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body p-0">
                                <!--begin::Header-->
                                <div class="px-9 pt-7 card-rounded h-275px w-100 bg-primary">
                                    <!--begin::Heading-->
                                    <div class="d-flex flex-stack">
                                        <h3 class="m-0 text-white fw-bold fs-3">Chief</h3>

                                        <div class="ms-1">
                                            <!--begin::Menu-->
                                            <button type="button"
                                                class="btn btn-sm btn-icon btn-color-white btn-active-white border-0 me-n3"
                                                data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                <i class="ki-outline ki-category fs-6"></i> </button>

                                            <!--begin::Menu 3-->
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3"
                                                data-kt-menu="true">
                                                <!--begin::Heading-->
                                                <div class="menu-item px-3">
                                                    <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">
                                                        Payments
                                                    </div>
                                                </div>
                                                <!--end::Heading-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3">
                                                        Create Invoice
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link flex-stack px-3">
                                                        Create Payment

                                                        <span class="ms-2" data-bs-toggle="tooltip"
                                                            aria-label="Specify a target name for future usage and reference"
                                                            data-bs-original-title="Specify a target name for future usage and reference"
                                                            data-kt-initialized="1">
                                                            <i class="ki-outline ki-information fs-6"></i> </span>
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3">
                                                        Generate Bill
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3" data-kt-menu-trigger="hover"
                                                    data-kt-menu-placement="right-end">
                                                    <a href="#" class="menu-link px-3">
                                                        <span class="menu-title">Subscription</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>

                                                    <!--begin::Menu sub-->
                                                    <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Plans
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Billing
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Statements
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu separator-->
                                                        <div class="separator my-2"></div>
                                                        <!--end::Menu separator-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <div class="menu-content px-3">
                                                                <!--begin::Switch-->
                                                                <label
                                                                    class="form-check form-switch form-check-custom form-check-solid">
                                                                    <!--begin::Input-->
                                                                    <input class="form-check-input w-30px h-20px"
                                                                        type="checkbox" value="1" checked="checked"
                                                                        name="notifications">
                                                                    <!--end::Input-->

                                                                    <!--end::Label-->
                                                                    <span class="form-check-label text-muted fs-6">
                                                                        Recuring
                                                                    </span>
                                                                    <!--end::Label-->
                                                                </label>
                                                                <!--end::Switch-->
                                                            </div>
                                                        </div>
                                                        <!--end::Menu item-->
                                                    </div>
                                                    <!--end::Menu sub-->
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3 my-1">
                                                    <a href="#" class="menu-link px-3">
                                                        Settings
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->
                                            </div>
                                            <!--end::Menu 3-->
                                            <!--end::Menu-->
                                        </div>
                                    </div>
                                    <!--end::Heading-->

                                    <!--begin::Balance-->
                                    <div class="d-flex text-center flex-column text-white pt-8">
                                        <span class="fw-semibold fs-7">Future Star (3) - Grade 5D-42th</span>
                                        <span class="fw-bold fs-2x pt-1">Justin Silalahi</span>
                                    </div>
                                    <!--end::Balance-->
                                </div>
                                <!--end::Header-->

                                <!--begin::Items-->
                                <div class="bg-body shadow-sm card-rounded mx-9 mb-9 px-6 py-9 position-relative z-index-1"
                                    style="margin-top: -100px">
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-6">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#"
                                                    class="fs-5 text-gray-800 text-hover-primary fw-bold">Length of
                                                    Service</a>
                                            </div>
                                            <!--end::Title-->

                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-gray-800 pe-1">26yrs</div>

                                                <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#"
                                                    class="fs-5 text-gray-800 text-hover-primary fw-bold">Length of Current
                                                    Position</a>
                                            </div>
                                            <!--end::Title-->

                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-gray-800 pe-1">4yrs</div>

                                                <i class="ki-outline ki-arrow-down fs-5 text-danger ms-1"></i>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->

                                <!--begin::Items-->
                                <div class="bg-body shadow-sm card-rounded mx-9 mb-9 px-6 py-9 position-relative mt-5"
                                    style="margin-top: -100px">
                                    <div class="text-gray-500 fw-semibold fs-7 mb-5">Successor</div>

                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <div class="text-gray-500 fw-semibold fs-7 mb-3">MT:
                                                    <span class="fw-bold fs-8 text-gray-800 pe-1">Pipit [Future Star(2) -
                                                        Grade 5A-49th]
                                                    </span>
                                                    <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>

                                                </div>

                                            </div>
                                            <!--end::Title-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <div class="text-gray-500 fw-semibold fs-7 mb-3">LT:
                                                    <span class="fw-bold fs-8 text-gray-800 pe-1">Kukuh [Future Star(2) -
                                                        Grade 5A-49th]
                                                    </span>
                                                    <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>

                                                </div>

                                            </div>
                                            <!--end::Title-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Mixed Widget 1-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-4">

                        <!--begin::Mixed Widget 1-->
                        <div class="card card-xl-stretch mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body p-0">
                                <!--begin::Header-->
                                <div class="px-9 pt-7 card-rounded h-275px w-100 bg-danger">
                                    <!--begin::Heading-->
                                    <div class="d-flex flex-stack">
                                        <h3 class="m-0 text-white fw-bold fs-3">Division 1</h3>

                                        <div class="ms-1">
                                            <!--begin::Menu-->
                                            <button type="button"
                                                class="btn btn-sm btn-icon btn-color-white btn-active-white border-0 me-n3"
                                                data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                <i class="ki-outline ki-category fs-6"></i> </button>

                                            <!--begin::Menu 3-->
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3"
                                                data-kt-menu="true">
                                                <!--begin::Heading-->
                                                <div class="menu-item px-3">
                                                    <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">
                                                        Payments
                                                    </div>
                                                </div>
                                                <!--end::Heading-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3">
                                                        Create Invoice
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link flex-stack px-3">
                                                        Create Payment

                                                        <span class="ms-2" data-bs-toggle="tooltip"
                                                            aria-label="Specify a target name for future usage and reference"
                                                            data-bs-original-title="Specify a target name for future usage and reference"
                                                            data-kt-initialized="1">
                                                            <i class="ki-outline ki-information fs-6"></i> </span>
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3">
                                                        Generate Bill
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3" data-kt-menu-trigger="hover"
                                                    data-kt-menu-placement="right-end">
                                                    <a href="#" class="menu-link px-3">
                                                        <span class="menu-title">Subscription</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>

                                                    <!--begin::Menu sub-->
                                                    <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Plans
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Billing
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Statements
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu separator-->
                                                        <div class="separator my-2"></div>
                                                        <!--end::Menu separator-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <div class="menu-content px-3">
                                                                <!--begin::Switch-->
                                                                <label
                                                                    class="form-check form-switch form-check-custom form-check-solid">
                                                                    <!--begin::Input-->
                                                                    <input class="form-check-input w-30px h-20px"
                                                                        type="checkbox" value="1" checked="checked"
                                                                        name="notifications">
                                                                    <!--end::Input-->

                                                                    <!--end::Label-->
                                                                    <span class="form-check-label text-muted fs-6">
                                                                        Recuring
                                                                    </span>
                                                                    <!--end::Label-->
                                                                </label>
                                                                <!--end::Switch-->
                                                            </div>
                                                        </div>
                                                        <!--end::Menu item-->
                                                    </div>
                                                    <!--end::Menu sub-->
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3 my-1">
                                                    <a href="#" class="menu-link px-3">
                                                        Settings
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->
                                            </div>
                                            <!--end::Menu 3-->
                                            <!--end::Menu-->
                                        </div>
                                    </div>
                                    <!--end::Heading-->

                                    <!--begin::Balance-->
                                    <div class="d-flex text-center flex-column text-white pt-8">
                                        <span class="fw-semibold fs-7">Future Star (3) - Grade 5C-46th</span>
                                        <span class="fw-bold fs-2x pt-1">Erik Simanjuntak</span>
                                    </div>
                                    <!--end::Balance-->
                                </div>
                                <!--end::Header-->

                                <!--begin::Items-->
                                <div class="bg-body shadow-sm card-rounded mx-9 mb-9 px-6 py-9 position-relative z-index-1"
                                    style="margin-top: -100px">
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-6">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#"
                                                    class="fs-5 text-gray-800 text-hover-primary fw-bold">Length of
                                                    Service</a>
                                            </div>
                                            <!--end::Title-->

                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-gray-800 pe-1">26yrs</div>

                                                <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#"
                                                    class="fs-5 text-gray-800 text-hover-primary fw-bold">Length of Current
                                                    Position</a>
                                            </div>
                                            <!--end::Title-->

                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-gray-800 pe-1">4yrs</div>

                                                <i class="ki-outline ki-arrow-down fs-5 text-danger ms-1"></i>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->

                                <!--begin::Items-->
                                <div class="bg-body shadow-sm card-rounded mx-9 mb-9 px-6 py-9 position-relative mt-5"
                                    style="margin-top: -100px">
                                    <div class="text-gray-500 fw-semibold fs-7 mb-5">Successor</div>

                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <div class="text-gray-500 fw-semibold fs-7 mb-3">MT:
                                                    <span class="fw-bold fs-8 text-gray-800 pe-1">Pipit [Future Star(2) -
                                                        Grade 5A-49th]
                                                    </span>
                                                    <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>

                                                </div>

                                            </div>
                                            <!--end::Title-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <div class="text-gray-500 fw-semibold fs-7 mb-3">LT:
                                                    <span class="fw-bold fs-8 text-gray-800 pe-1">Kukuh [Future Star(2) -
                                                        Grade 5A-49th]
                                                    </span>
                                                    <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>

                                                </div>

                                            </div>
                                            <!--end::Title-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Mixed Widget 1-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-4">

                        <!--begin::Mixed Widget 1-->
                        <div class="card card-xl-stretch mb-5 mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body p-0">
                                <!--begin::Header-->
                                <div class="px-9 pt-7 card-rounded h-275px w-100 bg-success">
                                    <!--begin::Heading-->
                                    <div class="d-flex flex-stack">
                                        <h3 class="m-0 text-white fw-bold fs-3">Division 2</h3>

                                        <div class="ms-1">
                                            <!--begin::Menu-->
                                            <button type="button"
                                                class="btn btn-sm btn-icon btn-color-white btn-active-white border-0 me-n3"
                                                data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                <i class="ki-outline ki-category fs-6"></i> </button>

                                            <!--begin::Menu 3-->
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3"
                                                data-kt-menu="true">
                                                <!--begin::Heading-->
                                                <div class="menu-item px-3">
                                                    <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">
                                                        Payments
                                                    </div>
                                                </div>
                                                <!--end::Heading-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3">
                                                        Create Invoice
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link flex-stack px-3">
                                                        Create Payment

                                                        <span class="ms-2" data-bs-toggle="tooltip"
                                                            aria-label="Specify a target name for future usage and reference"
                                                            data-bs-original-title="Specify a target name for future usage and reference"
                                                            data-kt-initialized="1">
                                                            <i class="ki-outline ki-information fs-6"></i> </span>
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3">
                                                        Generate Bill
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3" data-kt-menu-trigger="hover"
                                                    data-kt-menu-placement="right-end">
                                                    <a href="#" class="menu-link px-3">
                                                        <span class="menu-title">Subscription</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>

                                                    <!--begin::Menu sub-->
                                                    <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Plans
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Billing
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3">
                                                                Statements
                                                            </a>
                                                        </div>
                                                        <!--end::Menu item-->

                                                        <!--begin::Menu separator-->
                                                        <div class="separator my-2"></div>
                                                        <!--end::Menu separator-->

                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <div class="menu-content px-3">
                                                                <!--begin::Switch-->
                                                                <label
                                                                    class="form-check form-switch form-check-custom form-check-solid">
                                                                    <!--begin::Input-->
                                                                    <input class="form-check-input w-30px h-20px"
                                                                        type="checkbox" value="1" checked="checked"
                                                                        name="notifications">
                                                                    <!--end::Input-->

                                                                    <!--end::Label-->
                                                                    <span class="form-check-label text-muted fs-6">
                                                                        Recuring
                                                                    </span>
                                                                    <!--end::Label-->
                                                                </label>
                                                                <!--end::Switch-->
                                                            </div>
                                                        </div>
                                                        <!--end::Menu item-->
                                                    </div>
                                                    <!--end::Menu sub-->
                                                </div>
                                                <!--end::Menu item-->

                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3 my-1">
                                                    <a href="#" class="menu-link px-3">
                                                        Settings
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->
                                            </div>
                                            <!--end::Menu 3-->
                                            <!--end::Menu-->
                                        </div>
                                    </div>
                                    <!--end::Heading-->

                                    <!--begin::Balance-->
                                    <div class="d-flex text-center flex-column text-white pt-8">
                                        <span class="fw-semibold fs-7">Future Star (3) - Grade 5B-34th</span>
                                        <span class="fw-bold fs-2x pt-1">Bernard Setiawan</span>
                                    </div>
                                    <!--end::Balance-->
                                </div>
                                <!--end::Header-->

                                <!--begin::Items-->
                                <div class="bg-body shadow-sm card-rounded mx-9 mb-9 px-6 py-9 position-relative z-index-1"
                                    style="margin-top: -100px">
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-6">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#"
                                                    class="fs-5 text-gray-800 text-hover-primary fw-bold">Length of
                                                    Service</a>
                                            </div>
                                            <!--end::Title-->

                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-gray-800 pe-1">26yrs</div>

                                                <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#"
                                                    class="fs-5 text-gray-800 text-hover-primary fw-bold">Length of Current
                                                    Position</a>
                                            </div>
                                            <!--end::Title-->

                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-gray-800 pe-1">4yrs</div>

                                                <i class="ki-outline ki-arrow-down fs-5 text-danger ms-1"></i>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->

                                <!--begin::Items-->
                                <div class="bg-body shadow-sm card-rounded mx-9 mb-9 px-6 py-9 position-relative mt-5"
                                    style="margin-top: -100px">
                                    <div class="text-gray-500 fw-semibold fs-7 mb-5">Successor</div>

                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <div class="text-gray-500 fw-semibold fs-7 mb-3">MT:
                                                    <span class="fw-bold fs-8 text-gray-800 pe-1">Pipit [Future Star(2) -
                                                        Grade 5A-49th]
                                                    </span>
                                                    <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>

                                                </div>

                                            </div>
                                            <!--end::Title-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <div class="text-gray-500 fw-semibold fs-7 mb-3">LT:
                                                    <span class="fw-bold fs-8 text-gray-800 pe-1">Kukuh [Future Star(2) -
                                                        Grade 5A-49th]
                                                    </span>
                                                    <i class="ki-outline ki-arrow-up fs-5 text-success ms-1"></i>

                                                </div>

                                            </div>
                                            <!--end::Title-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Mixed Widget 1-->
                    </div>
                    <!--end::Col-->
                </div>
            </div>
        </div>
    </div>
@endsection
