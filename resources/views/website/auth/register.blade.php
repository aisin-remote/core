@extends('layouts.root.auth')

@section('main')
    <div class="d-flex justify-content-between flex-column-fluid flex-column w-100 mw-450px">
        <!--begin::Header-->
        <div class="d-flex flex-stack py-2">
            <!--begin::Back link-->
            <div class="me-2">
                <a href="sign-in.html" class="btn btn-icon bg-light rounded-circle">
                    <i class="ki-duotone ki-black-left fs-2 text-gray-800"></i> </a>

            </div>
            <!--end::Back link-->


            <!--begin::Sign Up link-->
            <div class="m-0">
                <span class="text-gray-500 fw-bold fs-5 me-2" data-kt-translate="sign-up-head-desc">
                    Already Registered ?
                </span>

                <a href="/login" class="link-primary fw-bold fs-5" data-kt-translate="sign-up-head-link">
                    Sign In
                </a>
            </div>
            <!--end::Sign Up link--->

        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="py-20">

            <!--begin::Form-->
            <form class="form w-100" novalidate="novalidate" id="kt_sign_up_form"
                data-kt-redirect-url="/metronic8/demo1/authentication/layouts/fancy/sign-in.html" action="#">
                <!--begin::Heading-->
                <div class="text-start mb-10">
                    <!--begin::Title-->
                    <h1 class="text-gray-900 mb-3 fs-3x" data-kt-translate="sign-up-title">
                        Create an Account
                    </h1>
                    <!--end::Title-->

                    <!--begin::Text-->
                    <div class="text-gray-500 fw-semibold fs-6" data-kt-translate="general-desc">
                        Get unlimited access & earn money
                    </div>
                    <!--end::Link-->
                </div>
                <!--end::Heading-->

                <!--begin::Input group-->
                <div class="row fv-row mb-7">
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <input class="form-control form-control-lg form-control-solid" type="text"
                            placeholder="First Name" name="first-name" autocomplete="off"
                            data-kt-translate="sign-up-input-first-name" />
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <input class="form-control form-control-lg form-control-solid" type="text"
                            placeholder="Last Name" name="last-name" autocomplete="off"
                            data-kt-translate="sign-up-input-last-name" />
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="fv-row mb-10">
                    <input class="form-control form-control-lg form-control-solid" type="email" placeholder="Email"
                        name="email" autocomplete="off" data-kt-translate="sign-up-input-email" />
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="fv-row mb-10" data-kt-password-meter="true">
                    <!--begin::Wrapper-->
                    <div class="mb-1">
                        <!--begin::Input wrapper-->
                        <div class="position-relative mb-3">
                            <input class="form-control form-control-lg form-control-solid" type="password"
                                placeholder="Password" name="password" autocomplete="off"
                                data-kt-translate="sign-up-input-password" />

                            <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                data-kt-password-meter-control="visibility">
                                <i class="ki-duotone ki-eye-slash fs-2"></i> <i class="ki-duotone ki-eye fs-2 d-none"></i>
                            </span>
                        </div>
                        <!--end::Input wrapper-->

                        <!--begin::Meter-->
                        <div class="d-flex align-items-center mb-3" data-kt-password-meter-control="highlight">
                            <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                            <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                            <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                            <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
                        </div>
                        <!--end::Meter-->
                    </div>
                    <!--end::Wrapper-->

                    <!--begin::Hint-->
                    <div class="text-muted" data-kt-translate="sign-up-hint">
                        Use 8 or more characters with a mix of letters, numbers & symbols.
                    </div>
                    <!--end::Hint-->
                </div>
                <!--end::Input group--->

                <!--begin::Input group-->
                <div class="fv-row mb-10">
                    <input class="form-control form-control-lg form-control-solid" type="password"
                        placeholder="Confirm Password" name="confirm-password" autocomplete="off"
                        data-kt-translate="sign-up-input-confirm-password" />
                </div>
                <!--end::Input group-->

                <!--begin::Actions-->
                <div class="d-flex flex-stack">
                    <!--begin::Submit-->
                    <button id="kt_sign_up_submit" class="btn btn-primary" data-kt-translate="sign-up-submit">

                        <!--begin::Indicator label-->
                        <span class="indicator-label">
                            Submit</span>
                        <!--end::Indicator label-->

                        <!--begin::Indicator progress-->
                        <span class="indicator-progress">
                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                        <!--end::Indicator progress--> </button>
                    <!--end::Submit-->
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Body-->
    </div>
@endsection
