@extends('layouts.root.auth')

@section('main')
    <div class="d-flex justify-content-between flex-column-fluid flex-column w-100 mw-450px">
        <!--begin::Header-->
        <div class="d-flex flex-stack py-2">
            <!--begin::Back link-->
            <div class="me-2">

            </div>
            <!--end::Back link-->

            <!--begin::Sign Up link-->
            <div class="m-0">
                <span class="text-gray-500 fw-bold fs-5 me-2" data-kt-translate="sign-in-head-desc">
                    Not Register yet?
                </span>

                <a href="/register" class="link-primary fw-bold fs-5" data-kt-translate="sign-in-head-link">
                    Sign Up
                </a>
            </div>
            <!--end::Sign Up link--->

        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="py-20">

            <!--begin::Form-->
            <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form"
                data-kt-redirect-url="/metronic8/demo1/index.html" action="#">
                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Heading-->
                    <div class="text-start mb-10">
                        <!--begin::Title-->
                        <h1 class="text-gray-900 mb-3 fs-3x" data-kt-translate="sign-in-title">
                            Sign In
                        </h1>
                        <!--end::Title-->

                        <!--begin::Text-->
                        <div class="text-gray-500 fw-semibold fs-6" data-kt-translate="general-desc">
                            Skill up your employee
                        </div>
                        <!--end::Link-->
                    </div>
                    <!--begin::Heading-->

                    <!--begin::Input group--->
                    <div class="fv-row mb-8">
                        <!--begin::Email-->
                        <input type="text" placeholder="Email" name="email" autocomplete="off"
                            data-kt-translate="sign-in-input-email" class="form-control form-control-solid" />
                        <!--end::Email-->
                    </div>

                    <!--end::Input group--->
                    <div class="fv-row mb-7">
                        <!--begin::Password-->
                        <input type="text" placeholder="Password" name="password" autocomplete="off"
                            data-kt-translate="sign-in-input-password" class="form-control form-control-solid" />
                        <!--end::Password-->
                    </div>
                    <!--end::Input group--->

                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-10">
                        <div></div>

                        <!--begin::Link-->
                        <a href="reset-password.html" class="link-primary" data-kt-translate="sign-in-forgot-password">
                            Forgot Password ?
                        </a>
                        <!--end::Link-->
                    </div>
                    <!--end::Wrapper-->

                    <!--begin::Actions-->
                    <div class="d-flex flex-stack">
                        <!--begin::Submit-->
                        <button id="kt_sign_in_submit" class="btn btn-primary me-2 flex-shrink-0">
                            <!--begin::Indicator label-->
                            <span class="indicator-label" data-kt-translate="sign-in-submit">
                                Sign In
                            </span>
                            <!--end::Indicator label-->

                            <!--begin::Indicator progress-->
                            <span class="indicator-progress">
                                <span data-kt-translate="general-progress">Please wait...</span>
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                            <!--end::Indicator progress-->
                        </button>
                        <!--end::Submit-->
                    </div>
                    <!--end::Actions-->
                </div>
                <!--begin::Body-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Body-->
        <div class="m-0">
            <!--begin::Toggle-->
            <button class="btn btn-flex btn-link rotate" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start"
                data-kt-menu-offset="0px, 0px">
                <img data-kt-element="current-lang-flag" class="w-25px h-25px rounded-circle me-3"
                    src="../../../assets/media/flags/united-states.svg" alt="" />

                <span data-kt-element="current-lang-name" class="me-2">English</span>

                <i class="ki-duotone ki-down fs-2 text-muted rotate-180 m-0"></i> </button>
            <!--end::Toggle-->

            <!--begin::Menu-->
            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-4"
                data-kt-menu="true" id="kt_auth_lang_menu">
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="#" class="menu-link d-flex px-5" data-kt-lang="English">
                        <span class="symbol symbol-20px me-4">
                            <img data-kt-element="lang-flag" class="rounded-1"
                                src="../../../assets/media/flags/united-states.svg" alt="" />
                        </span>
                        <span data-kt-element="lang-name">English</span>
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="#" class="menu-link d-flex px-5" data-kt-lang="Spanish">
                        <span class="symbol symbol-20px me-4">
                            <img data-kt-element="lang-flag" class="rounded-1" src="../../../assets/media/flags/spain.svg"
                                alt="" />
                        </span>
                        <span data-kt-element="lang-name">Spanish</span>
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="#" class="menu-link d-flex px-5" data-kt-lang="German">
                        <span class="symbol symbol-20px me-4">
                            <img data-kt-element="lang-flag" class="rounded-1" src="../../../assets/media/flags/germany.svg"
                                alt="" />
                        </span>
                        <span data-kt-element="lang-name">German</span>
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="#" class="menu-link d-flex px-5" data-kt-lang="Japanese">
                        <span class="symbol symbol-20px me-4">
                            <img data-kt-element="lang-flag" class="rounded-1" src="../../../assets/media/flags/japan.svg"
                                alt="" />
                        </span>
                        <span data-kt-element="lang-name">Japanese</span>
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="#" class="menu-link d-flex px-5" data-kt-lang="French">
                        <span class="symbol symbol-20px me-4">
                            <img data-kt-element="lang-flag" class="rounded-1"
                                src="../../../assets/media/flags/france.svg" alt="" />
                        </span>
                        <span data-kt-element="lang-name">French</span>
                    </a>
                </div>
                <!--end::Menu item-->
            </div>
            <!--end::Menu-->
        </div>
        <!--end::Footer-->

    </div>
@endsection
