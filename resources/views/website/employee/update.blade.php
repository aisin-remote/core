@extends('layouts.root.main')

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Sukses!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif
    <div id="kt_app_content_container" class="app-container  container-fluid">
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Detail Profil</h3>
                </div>
            </div>
            <div id="kt_account_settings_profile_details" class="collapse show">
                <form id="kt_account_profile_details_form" class="form fv-plugins-bootstrap5 fv-plugins-framework"
                    action="{{ route('employee.update', $employee->npk) }}" method="POST" enctype="multipart/form-data"
                    novalidate="novalidate">
                    @csrf
                    @method('PUT')
                    <div class="card-body border-top p-9">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Profile Photo</label>
                            <div class="col-lg-8">
                                <div class="image-input image-input-outline" data-kt-image-input="true"
                                    style="background-image: url('/metronic8/demo1/assets/media/svg/avatars/blank.svg')">
                                    @if ($employee->photo)
                                        <div class="image-input-wrapper w-125px h-125px"
                                            style="background-image: url('{{ asset('storage/' . $employee->photo) }}')">
                                        </div>
                                    @else
                                        <div class="image-input-wrapper w-125px h-125px"></div>
                                    @endif
                                    <label
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                        aria-label="Ubah avatar" data-bs-original-title="Ubah avatar"
                                        data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span
                                                class="path2"></span></i>
                                        <input type="file" name="photo" accept=".png, .jpg, .jpeg">
                                        <input type="hidden" name="photo_remove">
                                    </label>
                                    <span
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                        aria-label="Batalkan avatar" data-bs-original-title="Batalkan avatar"
                                        data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span
                                                class="path2"></span></i> </span>
                                    <span
                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                        aria-label="Hapus avatar" data-bs-original-title="Hapus avatar"
                                        data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span
                                                class="path2"></span></i> </span>
                                </div>
                                <div class="form-text">Tipe file yang diizinkan: png, jpg, jpeg.</div>
                                @error('photo')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Full Name</label>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-lg-12 fv-row fv-plugins-icon-container">
                                        <input type="text" name="name"
                                            class="form-control form-control-lg form-control-solid mb-3 mb-lg-0"
                                            placeholder="Nama Lengkap" value="{{ old('name', $employee->name) }}">
                                        @error('name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                        <div
                                            class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">NPK</label>
                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                <input type="text" name="npk" class="form-control form-control-lg form-control-solid"
                                    placeholder="Nomor Pokok Karyawan" value="{{ old('npk', $employee->npk) }}">
                                @error('npk')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Gender</label>
                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                <select name="gender" class="form-select form-select-lg form-select-solid">
                                    <option value="Male"
                                        {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>
                                        Laki-laki
                                    </option>
                                    <option value="Female"
                                        {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>
                                        Perempuan
                                    </option>
                                </select>
                                @error('gender')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Birthday Date</label>
                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                <input type="date" name="birthday_date"
                                    class="form-control form-control-lg form-control-solid"
                                    value="{{ old('birthday_date', $employee->birthday_date) }}">
                                @error('birthday_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Company Name</label>
                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                <select name="company_name" class="form-select form-select-lg form-select-solid">
                                    <option value="">-- Pilih Perusahaan --</option>
                                    <option value="AII"
                                        {{ old('company_name', $employee->company_name) == 'AII' ? 'selected' : '' }}>
                                        Aisin Indonesia
                                    </option>
                                    <option value="AIIA"
                                        {{ old('company_name', $employee->company_name) == 'AIIA' ? 'selected' : '' }}>
                                        Aisin Indonesia Automotive
                                    </option>
                                </select>
                                @error('company_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Join Date</label>
                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                <input type="date" name="aisin_entry_date"
                                    class="form-control form-control-lg form-control-solid"
                                    value="{{ old('aisin_entry_date', $employee->aisin_entry_date) }}">
                                @error('aisin_entry_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Working Period</label>
                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                <input type="number" name="working_period" min="0"
                                    class="form-control form-control-lg form-control-solid"
                                    value="{{ old('working_period', $employee->working_period) }}">
                                @error('working_period')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Company Group</label>
                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                <input type="text" name="company_group"
                                    class="form-control form-control-lg form-control-solid"
                                    value="{{ old('company_group', $employee->company_group) }}">
                                @error('company_group')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Position</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="position"
                                    class="form-control form-control-lg form-control-solid" placeholder="Jabatan Karyawan"
                                    value="{{ old('position', $employee->position) }}">
                                @error('position')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">Grade</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="grade"
                                    class="form-control form-control-lg form-control-solid" placeholder="Grade Karyawan"
                                    value="{{ old('grade', $employee->grade) }}">
                                @error('grade')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">
                                <span>Departement</span>
                            </label>
                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                <select name="department_id" aria-label="Pilih Departemen" data-control="select2"
                                    data-placeholder="Pilih departemen"
                                    class="form-select form-select-lg form-select-solid fw-semibold">
                                    <option value="">Pilih Departemen</option>
                                    @foreach ($departments as $department)
                                        <option data-kt-flag="flags/afghanistan.svg" value="{{ $department->id }}"
                                            {{ old('department_id', $employee->departments->first()->id ?? '') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <a href="{{ route('employee.master.index') }}"
                            class="btn btn-light btn-active-light-primary me-2">
                            <i class="bi bi-arrow-left-circle"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('input[name="aisin_entry_date"]').on('change', function() {
                var joinDate = new Date($(this).val());
                var currentDate = new Date();

                if (!isNaN(joinDate.getTime())) { // Check if valid date
                    var yearsDiff = currentDate.getFullYear() - joinDate.getFullYear();

                    // Adjust if the current date is before the join date anniversary this year
                    var currentMonthDay = (currentDate.getMonth() * 100) + currentDate.getDate();
                    var joinMonthDay = (joinDate.getMonth() * 100) + joinDate.getDate();
                    if (currentMonthDay < joinMonthDay) {
                        yearsDiff--;
                    }

                    $('input[name="working_period"]').val(Math.max(yearsDiff, 0));
                } else {
                    $('input[name="working_period"]').val(0);
                }
            });
        });
    </script>
@endpush
