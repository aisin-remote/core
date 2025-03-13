@extends('layouts.root.auth')

@section('main')
    <!--begin::Body-->
    <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
        <!--begin::Wrapper-->
        <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
            <!--begin::Content-->
            <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                @if (session('error'))
                    <!--begin::Alert-->
                    <div
                        class="alert alert-dismissible bg-light-danger d-flex align-items-center px-sm-8 py-0 position-relative">
                        <!--begin::Content-->
                        <div class="flex-grow-1">
                            <span class="fw-semibold text-danger">{{ session('error') }}</span>
                        </div>
                        <!--end::Content-->

                        <!--begin::Close Button-->
                        <button type="button" class="btn btn-icon py-1" data-bs-dismiss="alert">
                            <i class="fa-solid fa-xmark fs-4 ps-4 text-danger"></i>
                        </button>
                        <!--end::Close Button-->
                    </div>
                    <!--end::Alert-->
                @endif
                <!--begin::Wrapper-->
                <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">

                    <!--begin::Form-->
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form"
                        action="{{ route('login.authenticate') }}" method="POST">
                        @csrf
                        <!--begin::Heading-->
                        <div class="text-center mb-11">
                            <!--begin::Title-->
                            <h1 class="text-gray-900 fw-bolder mb-3">
                                Sign In
                            </h1>
                            <!--end::Title-->

                            <!--begin::Subtitle-->
                            <div class="text-gray-500 fw-semibold fs-6">
                                Human Resources Management System
                            </div>
                            <!--end::Subtitle--->
                        </div>
                        <!--begin::Heading-->

                        <!--begin::Input group--->
                        <div class="fv-row mb-8">
                            <!--begin::Email-->
                            <input type="text" placeholder="Email" name="email" autocomplete="off"
                                class="form-control bg-transparent @error('email') is-invalid @enderror"" required />
                            <!--end::Email-->
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!--end::Input group--->
                        <div class="fv-row mb-3">
                            <!--begin::Password-->
                            <input type="password" placeholder="Password" name="password" autocomplete="off"
                                class="form-control bg-transparent @error('password') is-invalid @enderror"" required />
                            <!--end::Password-->
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <!--end::Input group--->

                        <!--begin::Wrapper-->
                        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                            <div></div>

                            <!--begin::Link-->
                            <a href="reset-password.html" class="link-primary">
                                Forgot Password ?
                            </a>
                            <!--end::Link-->
                        </div>
                        <!--end::Wrapper-->

                        <!--begin::Submit button-->
                        <div class="d-grid mb-10">
                            <button type="submit" class="btn btn-primary">

                                <!--begin::Indicator label-->
                                <span class="indicator-label">
                                    Sign In</span>
                                <!--end::Indicator label-->

                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">
                                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                                <!--end::Indicator progress--> </button>
                        </div>
                        <!--end::Submit button-->

                        <!--begin::Sign up-->
                        <div class="text-gray-500 text-center fw-semibold fs-6">
                            Not have account yet?

                            <a href="sign-up.html" class="link-primary">
                                contact ITD
                            </a>
                        </div>
                        <!--end::Sign up-->
                    </form>
                    <!--end::Form-->

                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Body-->
@endsection
