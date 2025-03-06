@extends('layouts.root.auth')

@section('main')
    <div class="d-flex justify-content-between flex-column-fluid flex-column w-100 mw-450px">
        <!--begin::Header-->



        <!--begin::Body-->
        <div class="py-2">

            <!--begin::Form-->
            <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="{{ route('login.authenticate') }}"
                method="POST">
                @csrf
                <!--begin::Body-->
                <div class="card-body" style="padding-top: 150px">
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
                        <input type="password" placeholder="Password" name="password" autocomplete="off"
                            data-kt-translate="sign-in-input-password" class="form-control form-control-solid" />
                        <!--end::Password-->
                    </div>
                    <!--end::Input group--->

                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-wrap fs-base fw-semibold mb-10">
                        <div></div>
                        <div class="d-flex gap-3">
                            <a href="/register" class="link-primary" data-kt-translate="sign-in-head-link">
                                Sign Up
                            </a>
                            <a href="reset-password.html" class="link-primary" data-kt-translate="sign-in-forgot-password">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    <!--end::Wrapper-->

                    <!--begin::Actions-->
                    <div class="d-flex flex-stack">
                        <!--begin::Submit-->
                        <button type="submit" class="btn btn-primary me-2 flex-shrink-0">
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
        <!--end::Footer-->
    </div>
@endsection
