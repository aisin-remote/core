@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee Details' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
@endsection

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
    @if (session()->has('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "{{ session('error') }}",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="container mt-4">
            <div class="row">
                <div class="col-4">
                    <div class="card mb-5 mb-xl-10" style="height: 1370px !important">
                        <div class="card-header bg-light-primary border-0 cursor-pointer" role="button"
                            data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true"
                            aria-controls="kt_account_profile_details">
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Profile Details</h3>
                            </div>
                        </div>
                        <form id="kt_account_profile_details_form" class="form fv-plugins-bootstrap5 fv-plugins-framework"
                            action="{{ route('employee.update', $employee->npk) }}" method="POST"
                            enctype="multipart/form-data" novalidate="novalidate">
                            @csrf
                            @method('PUT')
                            <div id="kt_account_settings_profile_details" class="collapse show">
                                <div class="card border-top rounded-4 p-9">
                                    <div class="col-lg-12 text-center mb-8">
                                        <div class="image-input image-input-outline" data-kt-image-input="true"
                                            style="background-image: url('/metronic8/demo1/assets/media/svg/avatars/blank.svg')">
                                            <div class="image-input-wrapper"
                                                style="background-image: url('{{ $employee->photo ? asset('storage/' . $employee->photo) : '/metronic8/demo1/assets/media/svg/avatars/blank.svg' }}'); height: 150px">
                                            </div>
                                            <label
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                title="Ubah avatar">
                                                <i class="fa fa-pencil-alt fs-7"></i>
                                                <input type="file" name="photo" accept=".png, .jpg, .jpeg"
                                                    id="photoInput">
                                                <input type="hidden" name="photo_remove">
                                            </label>
                                            <span
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                title="Hapus avatar" id="removePhoto">
                                                <i class="fa fa-times fs-2"></i>
                                            </span>
                                        </div>
                                        <div class="form-text">Tipe file yang diizinkan: png, jpg, jpeg.</div>
                                        @error('photo')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Details Section -->
                                    <div class="mt-4">
                                        <div class="mt-2">
                                            <div class="row">
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Name</label>
                                                    <input type="text" name="name"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Full Name" value="{{ old('name', $employee->name) }}">
                                                    @error('name')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">NPK</label>
                                                    <input type="text" name="npk"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Npk" value="{{ old('npk', $employee->npk) }}">
                                                    @error('npk')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Gender</label>
                                                    <input type="text" name="gender"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Gender"
                                                        value="{{ old('gender', $employee->gender) }}">
                                                    @error('gender')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Birthday Date</label>
                                                    <input type="date" name="birthday_date"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Birthday Date"
                                                        value="{{ old('birthday_date', $employee->birthday_date) }}">
                                                    @error('birthday_date')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Email</label>
                                                    <input type="text" name=""
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Email" value="arief.widodo@aisin-indonesia.co.id">
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Phone Number</label>
                                                    <input type="text" name="email"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Phone Number" value="-">
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Company Name</label>
                                                    <select name="company_name" class="form-select form-select-sm"
                                                        data-control="select2">
                                                        <option value="">-- Pilih Perusahaan --</option>
                                                        <option value="AII"
                                                            {{ old('company_name', $employee->company_name) == 'AII' ? 'selected' : '' }}>
                                                            Aisin Indonesia</option>
                                                        <option value="AIIA"
                                                            {{ old('company_name', $employee->company_name) == 'AIIA' ? 'selected' : '' }}>
                                                            Aisin Indonesia Automotive</option>
                                                    </select>
                                                    @error('company_name')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Company Group</label>
                                                    <input type="text" name="company_group"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Company Group"
                                                        value="{{ old('company_group', $employee->company_group) }}">
                                                    @error('company_group')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Join Date</label>
                                                    <input type="date" name="aisin_entry_date"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Join Date"
                                                        value="{{ old('aisin_entry_date', $employee->aisin_entry_date) }}">
                                                    @error('aisin_entry_date')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Working Period</label>
                                                    <input type="text" name="working_period"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Wokring Period"
                                                        value="{{ old('working_period', $employee->working_period) }}">
                                                    @error('working_period')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Position</label>
                                                    <select name="position" id="position-select"
                                                        class="form-select form-select-sm fw-semibold"
                                                        data-control="select2">
                                                        <option value="">Select Position</option>
                                                        @php
                                                            $positions = [
                                                                'GM' => 'General Manager',
                                                                'Manager' => 'Manager',
                                                                'Coordinator' => 'Coordinator',
                                                                'Section Head' => 'Section Head',
                                                                'Supervisor' => 'Supervisor',
                                                                'Act Leader' => 'Act Leader',
                                                                'Act JP' => 'Act JP',
                                                                'Operator' => 'Operator',
                                                                'Director' => 'Director',
                                                            ];
                                                        @endphp
                                                        @foreach ($positions as $value => $label)
                                                            <option value="{{ $value }}"
                                                                {{ old('position', $employee->position ?? '') == $value ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('position')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Sub Section --}}
                                                <div id="subsection-group" class="col-12 mb-8 d-none">
                                                    <label class="form-label fw-bold fs-6">Sub Section</label>
                                                    <select name="sub_section_id"
                                                        class="form-select form-select-sm fw-semibold"
                                                        data-control="select2">
                                                        <option value="">Pilih Sub Section</option>
                                                        @foreach ($subSections as $subSection)
                                                            <option value="{{ $subSection->id }}"
                                                                {{ old('sub_section_id', $employee->subSection->id ?? '') == $subSection->id ? 'selected' : '' }}>
                                                                {{ $subSection->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('sub_section_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Section --}}
                                                <div id="section-group" class="col-12 mb-8 d-none">
                                                    <label class="form-label fw-bold fs-6">Section</label>
                                                    <select name="section_id"
                                                        class="form-select form-select-sm fw-semibold"
                                                        data-control="select2">
                                                        <option value="">Pilih Section</option>
                                                        @foreach ($sections as $section)
                                                            <option value="{{ $section->id }}"
                                                                {{ old('section_id', (int) $employee->leadingSection?->id ?? '') == (int) $section->id ? 'selected' : '' }}>
                                                                {{ $section->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('section_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Department --}}
                                                <div id="department-group" class="col-12 mb-8 d-none">
                                                    <label class="form-label fw-bold fs-6">Department</label>
                                                    <select name="department_id"
                                                        class="form-select form-select-sm fw-semibold"
                                                        data-control="select2">
                                                        <option value="">Pilih Department</option>
                                                        @foreach ($departments as $department)
                                                            <option value="{{ $department->id }}"
                                                                {{ old('department_id', (int) $employee->leadingDepartment?->id ?? '') == (int) $department->id ? 'selected' : '' }}>
                                                                {{ $department->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('department_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Division --}}
                                                <div id="division-group" class="col-12 mb-8 d-none">
                                                    <label class="form-label fw-bold fs-6">Division</label>
                                                    <select name="division_id"
                                                        class="form-select form-select-sm fw-semibold"
                                                        data-control="select2">
                                                        <option value="">Pilih Division</option>
                                                        @foreach ($divisions as $division)
                                                            <option value="{{ $division->id }}"
                                                                {{ old('division_id', $employee->leadingDivision?->id ?? '') == $division->id ? 'selected' : '' }}>
                                                                {{ $division->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('division_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Plant --}}
                                                <div id="plant-group" class="col-12 mb-8 d-none">
                                                    <label class="form-label fw-bold fs-6">Plant</label>
                                                    <select name="plant_id" class="form-select form-select-sm fw-semibold"
                                                        data-control="select2">
                                                        <option value="">Pilih Plant</option>
                                                        @foreach ($plants as $plant)
                                                            <option value="{{ $plant->id }}"
                                                                {{ old('plant_id', $employee->plant->id ?? '') == $plant->id ? 'selected' : '' }}>
                                                                {{ $plant->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('plant_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Grade</label>
                                                    <input type="text" name="grade"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Grade" value="{{ old('grade', $employee->grade) }}">
                                                    @error('grade')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-2"><i class="bi bi-save"></i> Save
                                        Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Card 1: Educational Background -->
                <div class="col-8">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-5 mb-xl-10">
                                <div
                                    class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center">
                                    <h3 class="fw-bolder m-0">Educational Background</h3>

                                    <div class="d-flex gap-2">
                                        @if ($educations->count() >= 3)
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addEducationModal">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        @endif

                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#detailEducationModal">
                                            <i class="fas fa-info"></i> Detail
                                        </button>
                                    </div>
                                </div>

                                <div id="kt_account_settings_signin_method" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        @php
                                            $totalEducation = $educations->count();
                                            $maxSlots = 3;
                                        @endphp

                                        @for ($i = 0; $i < $maxSlots; $i++)
                                            @if (isset($educations[$i]))
                                                @php $education = $educations[$i]; @endphp
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold mb-2">
                                                            {{ $education->educational_level }} - {{ $education->major }}
                                                        </div>
                                                        <div
                                                            class="fw-semibold text-gray-600 d-flex flex-wrap align-items-center gap-2">
                                                            <span>{{ $education->institute }}</span>
                                                            <span class="text-muted fs-7">
                                                                [{{ \Carbon\Carbon::parse($education->start_date)->format('Y') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($education->end_date)->format('Y') }}]
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-light-warning edit-education-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editEducationModal{{ $education->id }}"
                                                            data-education-id="{{ $education->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-light-danger delete-education-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteEducationModal{{ $education->id }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                @include('website.modal.education.detail')
                                                @include('website.modal.education.update', [
                                                    'education' => $education,
                                                ])
                                                @include('website.modal.education.delete', [
                                                    'education' => $education,
                                                ])
                                            @else
                                                <!-- Slot kosong -->
                                                <div
                                                    class="d-flex justify-content-between align-items-center gap-3 border border-dashed p-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold text-muted">[Empty Slot]</div>
                                                        <div class="fw-semibold text-gray-600">Click to add education</div>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-light-primary"
                                                            data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                                            <i class="fas fa-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif

                                            @unless ($i == $maxSlots - 1)
                                                <div class="separator separator-dashed my-3"></div>
                                            @endunless
                                        @endfor
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- modal education --}}
                        @include('website.modal.education.create', [
                            'employee_id' => $employee->id,
                        ])
                        {{-- end of modal education --}}
                    </div>
                    <div class="row">
                        <!-- Working Experience -->
                        <div class="col-md-12">
                            <div class="card mb-5 mb-xl-10">
                                <div
                                    class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center">
                                    <h3 class="fw-bolder m-0">Work Experience</h3>

                                    <div class="d-flex gap-2">
                                        @if ($workExperiences->count() >= 3)
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addExperienceModal">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        @endif

                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#allExperienceDetailModal">
                                            <i class="fas fa-info"></i> Detail
                                        </button>
                                    </div>
                                </div>

                                <div id="kt_activity_year" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        @php
                                            $maxSlots = 3;
                                            $experienceCount = $workExperiences->count();
                                        @endphp

                                        @for ($i = 0; $i < $maxSlots; $i++)
                                            @if (isset($workExperiences[$i]))
                                                @php $experience = $workExperiences[$i]; @endphp
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold mb-2">{{ $experience->position }}</div>
                                                        <div
                                                            class="fw-semibold text-gray-600 d-flex flex-wrap align-items-center gap-2">
                                                            <span>{{ $experience->company }}</span>
                                                            <span class="text-muted fs-7">
                                                                [{{ \Carbon\Carbon::parse($experience->start_date)->format('Y') }}
                                                                -
                                                                {{ $experience->end_date ? \Carbon\Carbon::parse($experience->end_date)->format('Y') : 'Present' }}]
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-light-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#experienceModal{{ $experience->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-light-warning edit-experience-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editExperienceModal{{ $experience->id }}"
                                                            data-experience-id="{{ $experience->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-light-danger delete-experience-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteExperienceModal{{ $experience->id }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                @include('website.modal.work.all_detail')
                                                @include('website.modal.work.detail', [
                                                    'experience' => $experience,
                                                ])
                                                @include('website.modal.work.update', [
                                                    'experience' => $experience,
                                                ])
                                                @include('website.modal.work.delete', [
                                                    'experience' => $experience,
                                                ])
                                            @else
                                                <!-- Slot kosong -->
                                                <div
                                                    class="d-flex justify-content-between align-items-center gap-3 border border-dashed p-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold text-muted">[Empty Slot]</div>
                                                        <div class="fw-semibold text-gray-600">Click to add experience
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-light-primary"
                                                            data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                                                            <i class="fas fa-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif

                                            @unless ($i == $maxSlots - 1)
                                                <div class="separator separator-dashed my-3"></div>
                                            @endunless
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- work experience modal input --}}
                        @include('website.modal.work.create', [
                            'employee_id' => $employee->id,
                        ])
                        {{-- end of work experience modal input --}}
                    </div>
                    <div class="row">
                        <!-- Historical Performance Appraisal -->
                        <div class="col-md-12">
                            <div class="card mb-5">
                                <div
                                    class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center">
                                    <h3 class="fw-bolder m-0">Historical Performance Appraisal</h3>

                                    <div class="d-flex gap-3">
                                        @if ($performanceAppraisals->count() >= 3)
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addAppraisalModal">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        @endif

                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#alldetailAppraisalModal">
                                            <i class="fas fa-info"></i> Detail
                                        </button>
                                    </div>
                                </div>

                                <div id="kt_account_settings_signin_method" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        @php
                                            $maxSlots = 3;
                                            $appraisalCount = $performanceAppraisals->count();
                                        @endphp

                                        @for ($i = 0; $i < $maxSlots; $i++)
                                            @if (isset($performanceAppraisals[$i]))
                                                @php $appraisal = $performanceAppraisals[$i]; @endphp
                                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fs-6 fw-bold">Score - {{ $appraisal->score }}</div>
                                                        <div class="text-muted fs-7">
                                                            {{ \Illuminate\Support\Carbon::parse($appraisal->date)->format('d M Y') }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-light-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#detailModal{{ $appraisal->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-light-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editAppraisalModal{{ $appraisal->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-light-danger" data-bs-toggle="modal"
                                                            data-bs-target="#deleteAppraisalModal{{ $appraisal->id }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                @include('website.modal.appraisal.all_detail')

                                                @include('website.modal.appraisal.detail', [
                                                    'appraisal' => $appraisal,
                                                ])

                                                @include('website.modal.appraisal.update', [
                                                    'appraisal' => $appraisal,
                                                ])

                                                @include('website.modal.appraisal.delete', [
                                                    'appraisal' => $appraisal,
                                                ])
                                            @else
                                                <!-- Slot kosong -->
                                                <div
                                                    class="d-flex justify-content-between align-items-center gap-3 border border-dashed p-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold text-muted">[Empty Slot]</div>
                                                        <div class="fw-semibold text-gray-600">Click to add appraisal</div>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-light-primary"
                                                            data-bs-toggle="modal" data-bs-target="#addAppraisalModal">
                                                            <i class="fas fa-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif

                                            @unless ($i == $maxSlots - 1)
                                                <div class="separator separator-dashed my-3"></div>
                                            @endunless
                                        @endfor
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- appraisal modal --}}
                        @include('website.modal.appraisal.create', [
                            'employee_id' => $employee->id,
                        ])
                        {{-- end of appraisal modal --}}
                    </div>
                    <div class="row">
                        <!-- Card 2: Historical Human Assets Value -->
                        <div class="col-md-12">
                            <div class="card mb-5 mb-xl-10">
                                <div class="card-header bg-light-primary border-0 cursor-pointer" role="button"
                                    data-bs-toggle="collapse" data-bs-target="#kt_account_human_assets"
                                    aria-expanded="true" aria-controls="kt_account_human_assets">
                                    <div class="card-title m-0">
                                        <h3 class="fw-bolder m-0">Historical Human Assets Value</h3>
                                    </div>
                                </div>

                                <div id="kt_account_human_assets" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        <!-- Mengurangi padding agar card lebih kecil -->
                                        @php
                                            $humanAssets = [
                                                ['title' => 'Future Star', 'count' => 2, 'year' => 2024],
                                                ['title' => 'Potential Candidate', 'count' => 4, 'year' => 2023],
                                            ];
                                            $maxSlots = 3; // Set jumlah maksimum slot
                                            $humanAssetsCount = count($humanAssets);
                                        @endphp

                                        @if ($humanAssetsCount === 0)
                                            <div class="text-center text-muted">No data available</div>
                                            <div class="separator separator-dashed my-4"></div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Empty slot</span>
                                                <a class="fw-semibold text-primary"
                                                    href="{{ route('employee.edit', $employee->npk) }}">
                                                    Go to employee edit page
                                                </a>
                                            </div>
                                        @else
                                            @foreach ($humanAssets as $asset)
                                                <div class="d-flex flex-wrap align-items-center">
                                                    <div id="kt_signin_email">
                                                        <div class="fs-6 fw-bold mb-1">{{ $asset['title'] }}
                                                            [{{ $asset['count'] }}]
                                                            <div class="text-muted fs-7">
                                                                {{ $asset['year'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="separator separator-dashed my-4"></div>
                                            @endforeach

                                            <!-- If there are fewer than $maxSlots, show "Empty slot" -->
                                            @for ($i = $humanAssetsCount; $i < $maxSlots; $i++)
                                                <div
                                                    class="d-flex justify-content-between align-items-center gap-3 border border-dashed p-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold text-muted">[Empty Slot]</div>
                                                        <a class="fw-semibold"
                                                            href="{{ route('hav.index', $employee->npk) }}">
                                                            Go to hav page
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="separator separator-dashed mt-4"></div>
                                            @endfor
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Historical Astra  -->
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header bg-light-primary border-0 cursor-pointer d-flex justify-content-between align-items-center"
                            role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_connected_accounts"
                            aria-expanded="true" aria-controls="kt_account_connected_accounts">

                            <h3 class="fw-bolder m-0">Astra Training History</h3>

                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addAstraTrainingModal">
                                    <i class="fas fa-plus"></i> Add
                                </button>

                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#detailAstraTrainingModal">
                                    <i class="fas fa-info"></i> Detail
                                </button>
                            </div>
                        </div>

                        <div id="kt_account_settings_signin_method" class="collapse show">
                            <div class="card-body border-top p-5    ">
                                <!--begin::Table wrapper-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
                                        <!--begin::Thead-->
                                        <thead class="border-gray-200 fs-5 fw-semibold bg-lighten">
                                            <tr>

                                                <th class="text-center">Year</th>
                                                <th class="text-center">Program</th>
                                                <th class="text-center">ICT/Project/Total</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <!--end::Thead-->

                                        <!--begin::Tbody-->
                                        <tbody class="fw-6 fw-semibold text-gray-600">
                                            @forelse ($astraTrainings->take(3) as $astraTraining)
                                                <tr>

                                                    <td class="text-center">{{ $astraTraining->date_end }}</td>
                                                    <td class="text-center">{{ $astraTraining->program }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $astraTraining->ict_score }}/{{ $astraTraining->project_score }}/{{ $astraTraining->total_score }}
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <button class="btn btn-sm btn-light-warning"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editAstraTrainingModal{{ $astraTraining->id }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button
                                                                class="btn btn-sm btn-light-danger delete-experience-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteAstraTrainingModal{{ $astraTraining->id }}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                @include('website.modal.astra_training.update', [
                                                    'astraTraining' => $astraTraining,
                                                ])

                                                @include('website.modal.astra_training.delete', [
                                                    'astraTraining' => $astraTraining,
                                                ])
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table wrapper-->
                            </div>
                        </div>
                    </div>
                </div>

                {{-- astra_training modal --}}
                @include('website.modal.astra_training.detail')

                @include('website.modal.astra_training.create', [
                    'employee_id' => $employee->id,
                ])
                {{-- end of astra_training modal --}}

                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header bg-light-primary border-0 cursor-pointer d-flex justify-content-between align-items-center"
                            role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_connected_accounts"
                            aria-expanded="true" aria-controls="kt_account_connected_accounts">

                            <h3 class="fw-bolder m-0">External Training History</h3>

                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addExternalTrainingModal">
                                    <i class="fas fa-plus"></i> Add
                                </button>

                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#detailExternalTrainingModal">
                                    <i class="fas fa-info"></i> Detail
                                </button>
                            </div>
                        </div>

                        <div id="kt_account_settings_signin_method" class="collapse show">
                            <div class="card-body border-top p-5">
                                <!--begin::Table wrapper-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
                                        <!--begin::Thead-->
                                        <thead class="border-gray-200 fs-5 fw-semibold bg-lighten">
                                            <tr>
                                                <th>Training</th>

                                                <th class="text-center">Year</th>
                                                <th class="text-center">Vendor</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <!--end::Thead-->

                                        <!--begin::Tbody-->
                                        <tbody class="fw-6 fw-semibold text-gray-600">
                                            @forelse ($externalTrainings->take(3) as $externalTraining)
                                                <tr>
                                                    <td>{{ $externalTraining->program }}</td>

                                                    <td class="text-center">{{ $externalTraining->date_end }}</td>
                                                    <td class="text-center">{{ $externalTraining->vendor }}</td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <button class="btn btn-sm btn-light-warning"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editExternalTrainingModal{{ $externalTraining->id }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button
                                                                class="btn btn-sm btn-light-danger delete-experience-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteExternalTrainingModal{{ $externalTraining->id }}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                @include('website.modal.external_training.update', [
                                                    'externalTraining' => $externalTraining,
                                                ])

                                                @include('website.modal.external_training.delete', [
                                                    'externalTraining' => $externalTraining,
                                                ])
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table wrapper-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- external_training modal --}}
            @include('website.modal.external_training.detail')

            @include('website.modal.external_training.create', [
                'employee_id' => $employee->id,
            ])
            {{-- end of external_training modal --}}


            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header bg-light-primary d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        <h3>Promotion History</h3>
                    </div>

                    <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                        data-bs-target="#detailPromotionHistoryModal">
                        <i class="fas fa-info"></i> Detail
                    </button>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body p-0">
                    <!--begin::Table wrapper-->
                    <div class="table-responsive">
                        <!--begin::Table-->
                        <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
                            <!--begin::Thead-->
                            <thead class="border-gray-200 fs-5 fw-semibold bg-lighten">
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">Previous Grade</th>
                                    <th class="text-center">Previous Position</th>
                                    <th class="text-center">Current Grade</th>
                                    <th class="text-center">Current Position</th>
                                    <th class="min-w-250px text-center">Last Promotion Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <!--end::Thead-->

                            <!--begin::Tbody-->
                            <tbody class="fw-6 fw-semibold text-gray-600">
                                @forelse ($promotionHistories->take(3) as $promotionHistory)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $promotionHistory->previous_grade }}</td>
                                        <td class="text-center">{{ $promotionHistory->previous_position }}</td>
                                        <td class="text-center">{{ $promotionHistory->current_grade }}</td>
                                        <td class="text-center">{{ $promotionHistory->current_position }}</td>
                                        <td class="text-center">
                                            {{ Carbon\Carbon::parse($promotionHistory->last_promotion_date)->format('j F Y, g:i A') }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light-danger delete-experience-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePromotionModal{{ $promotionHistory->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- delete modal --}}
                                    @include('website.modal.promotion_history.delete', [
                                        'promotionHistory' => $promotionHistory,
                                    ])
                                    {{-- end of modal --}}
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Table wrapper-->
                </div>
                <!--end::Card body-->
            </div>

            @include('website.modal.promotion_history.detail')

            <div class="row">
                <!-- Strength -->
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header bg-light-primary border-0 cursor-pointer" role="button"
                            data-bs-toggle="collapse" data-bs-target="#strength_section">
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Strength</h3>
                            </div>
                        </div>
                        <div id="strength_section" class="collapse show">
                            <div class="card-body border-top p-10">
                                @if (!$assessment)
                                    <p class="text-center text-muted">No data available</p>
                                @elseif ($assessment->details->isEmpty() || !$assessment->details->where('strength', '!=', null)->count())
                                    <p class="text-center text-muted">No data available</p>
                                @else
                                    @foreach ($assessment->details as $detail)
                                        @if ($detail->strength)
                                            <div class="d-flex flex-wrap align-items-center mb-4">
                                                <div>
                                                    <div class="fs-6 fw-bold mb-1">
                                                        {{ $detail->alc->name ?? 'Unknown' }}
                                                    </div>
                                                    <div class="fw-semibold text-gray-600">
                                                        <span class="text-content"
                                                            data-fulltext="{!! htmlentities($detail->strength) !!}">
                                                            {!! Str::limit($detail->strength, 200) !!}
                                                        </span>
                                                        <br>
                                                        @if (strlen(strip_tags($detail->strength)) > 200)
                                                            <span class="show-more text-primary cursor-pointer">Show
                                                                More</span>
                                                            <span class="show-less text-primary cursor-pointer d-none">Show
                                                                Less</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="separator separator-dashed my-4"></div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            <div class="card-footer">
                                <a class="fw-semibold" href="{{ route('assessments.index', $employee->company_name) }}">
                                    Go to assesment page
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Areas for Development -->
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header bg-light-primary border-0 cursor-pointer" role="button"
                            data-bs-toggle="collapse" data-bs-target="#weakness_section">
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Areas for Development</h3>
                            </div>
                        </div>
                        <div id="weakness_section" class="collapse show">
                            <div class="card-body border-top p-10">
                                @if (!$assessment)
                                    <p class="text-center text-muted">No data available</p>
                                @elseif ($assessment->details->isEmpty() || !$assessment->details->where('weakness', '!=', null)->count())
                                    <p class="text-center text-muted">No data available</p>
                                @else
                                    @foreach ($assessment->details as $detail)
                                        @if ($detail->weakness)
                                            <div class="d-flex flex-wrap align-items-center mb-4">
                                                <div>
                                                    <div class="fs-6 fw-bold mb-1">
                                                        {{ $detail->alc->name ?? 'Unknown' }}
                                                    </div>
                                                    <div class="fw-semibold text-gray-600">
                                                        <span class="text-content"
                                                            data-fulltext="{!! htmlentities($detail->weakness) !!}">
                                                            {!! Str::limit($detail->weakness, 200) !!}
                                                        </span>
                                                        <br>
                                                        @if (strlen(strip_tags($detail->weakness)) > 200)
                                                            <span class="show-more text-primary cursor-pointer">Show
                                                                More</span>
                                                            <span class="show-less text-primary cursor-pointer d-none">Show
                                                                Less</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="separator separator-dashed my-4"></div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            <div class="card-footer">
                                <a class="fw-semibold" href="{{ route('assessments.index', $employee->company_name) }}">
                                    Go to assesment page
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table 2: Individual Development Plan -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-header bg-light-primary border-0 cursor-pointer" role="button"
                    data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">
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
                                <th class="text-center">Development Target</th>
                                <th class="text-center">Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($idps as $idp)
                                <tr>
                                    <td>{{ $idp->alc->name }}</td>
                                    <td>{{ $idp->development_program }}</td>
                                    <td class="text-center">{{ $idp->development_target }}</td>
                                    <td class="text-center">{{ Carbon\Carbon::parse($idp->date)->format('j F Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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

@push('scripts')
    <!-- Tambahkan SweetAlert -->
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

            $(".show-more").click(function() {
                var textContainer = $(this).siblings(".text-content");
                var fullText = textContainer.attr("data-fulltext");

                textContainer.html(fullText);
                $(this).addClass("d-none");
                $(this).siblings(".show-less").removeClass("d-none");
            });

            $(".show-less").click(function() {
                var textContainer = $(this).siblings(".text-content");
                var shortText = textContainer.text().substring(0, 200) + "...";

                textContainer.html(shortText);
                $(this).addClass("d-none");
                $(this).siblings(".show-more").removeClass("d-none");
            });

            function toggleHierarchySelects(position) {
                $('#subsection-group, #section-group, #department-group, #division-group, #plant-group').addClass(
                    'd-none');

                if (['Operator', 'Act JP', 'Act Leader'].includes(position)) {
                    $('#subsection-group').removeClass('d-none');
                } else if (['Supervisor', 'Section Head'].includes(position)) {
                    $('#section-group').removeClass('d-none');
                } else if (['Manager', 'Coordinator'].includes(position)) {
                    $('#department-group').removeClass('d-none');
                } else if (position === 'GM') {
                    $('#division-group').removeClass('d-none');
                } else if (position === 'Director') {
                    $('#plant-group').removeClass('d-none');
                }
            }

            // Saat halaman pertama kali dimuat
            toggleHierarchySelects($('#position-select').val());

            // Saat pilihan posisi berubah
            $('#position-select').on('change', function() {
                const selectedPosition = $(this).val();
                toggleHierarchySelects(selectedPosition);
            });
        });
    </script>
@endpush
