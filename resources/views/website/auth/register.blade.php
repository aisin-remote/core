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
            <form class="form w-100" novalidate="novalidate" id="kt_sign_up_form" action="{{ route('register.store') }}"
                method="POST">
                @csrf
                <!--begin::Heading-->
                <div class="text-start mb-10">
                    <!--begin::Title-->
                    <h1 class="text-gray-900 mb-3 fs-3x" data-kt-translate="sign-up-title">
                        Create an Account
                    </h1>
                    <!--end::Title-->

                    <!--end::Link-->
                </div>
                <!--end::Heading-->

                <!--begin::Input group-->
                <div class="row fv-row mb-7">
                    <!--begin::Col-->
                    <div class="col-xl-12">
                        <input class="form-control form-control-lg form-control-solid" type="text" placeholder="Fullname"
                            name="name" autocomplete="off" data-kt-translate="sign-up-input-first-name" />
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

                <div class="col-lg-12 mb-10">
                    <select name="position" aria-label="Select a Country" data-control="select2"
                        data-placeholder="Select Position..."
                        class="form-select form-select-solid form-select-lg fw-semibold">
                        <option value="">Select
                            Position</option>
                        <option data-kt-flag="flags/aland-islands.svg" value="GM">General Manager</option>
                        <option data-kt-flag="flags/albania.svg" value="Manager">Manager</option>
                        <option data-kt-flag="flags/albania.svg" value="Coordinator">Coordinator</option>
                        <option data-kt-flag="flags/albania.svg" value="Section Head">Section Head</option>
                        <option data-kt-flag="flags/albania.svg" value="Supervisor">Supervisor</option>
                    </select>
                </div>

                <div class="col-lg-12 mb-10">
                    <select name="departments[]" aria-label="Select a Country" data-control="select2"
                        data-placeholder="Select Department..."
                        class="form-select form-select-solid form-select-lg fw-semibold" multiple>
                        <option value="">Select
                            Position</option>
                        @foreach ($departments as $department)
                            <option data-kt-flag="flags/aland-islands.svg" value="{{ $department->id }}">
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
                        placeholder="Confirm Password" name="password_confirmation" autocomplete="off"
                        data-kt-translate="sign-up-input-confirm-password" />
                </div>
                <!--end::Input group-->

                <!--begin::Actions-->
                <div class="d-flex flex-stack">
                    <!--begin::Submit-->
                    <button type="submit" class="btn btn-primary" data-kt-translate="sign-up-submit">
                        <!--begin::Indicator label-->
                        <span class="indicator-label">
                            Submit</span>
                        <!--end::Indicator label-->

                        <!--begin::Indicator progress-->
                        <span class="indicator-progress">
                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                        <!--end::Indicator progress-->
                    </button>
                    <!--end::Submit-->
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Body-->
    </div>
@endsection
