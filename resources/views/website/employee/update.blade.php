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
                    title: "Success!",
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
                    <div class="card mb-5 mb-xl-10" style="height: 1110px !important">
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
                                        <div class="form-text text-warning">
                                            <i class="bi bi-info-circle"></i> Format yang diizinkan: PNG, JPG, JPEG.
                                        </div>
                                        <div class="form-text text-warning">
                                            <i class="bi bi-info-circle"></i> Ukuran maksimal file: 2 MB.
                                        </div>

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
                                                @php
                                                    $age = $employee->birthday_date
                                                        ? Carbon\Carbon::parse($employee->birthday_date)->age
                                                        : null;
                                                @endphp
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Age</label>
                                                    <input readonly type="text" name="age"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Age" value="{{ $age }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Email</label>
                                                    <input type="text" name="email"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Email" value="{{ $employee->user->email ?? '-' }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Phone Number</label>
                                                    <input type="number" id="phone_number" name="phone_number"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Phone Number" value="{{ $employee->phone_number }}">
                                                </div>
                                                <div class="col-6 mb-8">
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
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Company Group</label>
                                                    <input type="text" name="company_group"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Company Group"
                                                        value="{{ old('company_group', $employee->company_group) }}">
                                                    @error('company_group')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Join Date</label>
                                                    <input type="date" name="aisin_entry_date"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Join Date"
                                                        value="{{ old('aisin_entry_date', $employee->aisin_entry_date) }}">
                                                    @error('aisin_entry_date')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Working Period</label>
                                                    <input type="text" name="working_period"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Wokring Period"
                                                        value="{{ old('working_period', $employee->working_period) }}">
                                                    @error('working_period')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Position</label>
                                                    <select name="position" id="position-select"
                                                        class="form-select form-select-sm fw-semibold"
                                                        data-control="select2">
                                                        <option value="">Select Position</option>
                                                        @php
                                                            $positions = [
                                                                'GM' => 'General Manager',
                                                                'Act GM' => 'Act General Manager',
                                                                'Manager' => 'Manager',
                                                                'Act Manager' => 'Act Manager',
                                                                'Coordinator' => 'Coordinator',
                                                                'Act Coordinator' => 'Act Coordinator',
                                                                'Section Head' => 'Section Head',
                                                                'Act Section Head' => 'Act Section Head',
                                                                'Supervisor' => 'Supervisor',
                                                                'Act Supervisor' => 'Act Supervisor',
                                                                'Act Leader' => 'Act Leader',
                                                                'Leader' => 'Leader',
                                                                'Staff' => 'Staff',
                                                                'Act JP' => 'Act JP',
                                                                'Operator' => 'Operator',
                                                                'Direktur' => 'Direktur',
                                                                'VPD' => 'VPD',
                                                                'President' => 'President',
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
                                                                {{ $subSection->name }} -
                                                                {{ $subSection->section->company }}
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
                                                                {{ $section->name }} - {{ $section->company }}
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
                                                                {{ $department->name }} - {{ $department->company }}
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
                                                                {{ $division->name }} - {{ $division->company }}
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
                                                                {{ $plant->name }} - {{ $plant->company }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('plant_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Aisin Grade</label>
                                                    <select name="grade"
                                                        class="form-control form-control-sm form-control-solid" required>
                                                        <option value="">-- Select Grade --</option>
                                                        @foreach ($grade as $g)
                                                            <option value="{{ $g->aisin_grade }}"
                                                                {{ old('grade', $employee->grade ?? '') == $g->aisin_grade ? 'selected' : '' }}>
                                                                {{ $g->aisin_grade }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('grade')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Astra Grade</label>
                                                    <input readonly type="text"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Grade" value="{{ $employee->astra_grade }}">
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
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#addEducationModal">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
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
                                        @endphp

                                        @if ($totalEducation > 0)
                                            @foreach ($educations->take(3) as $education)
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold mb-2">
                                                            {{ $education->educational_level }} - {{ $education->major }}
                                                        </div>
                                                        <div
                                                            class="fw-semibold text-gray-600 d-flex flex-wrap align-items-center gap-2">
                                                            <span>{{ $education->institute }}</span>
                                                            <span class="text-muted fs-7">
                                                                [{{ $education->start_date ? \Carbon\Carbon::parse($education->start_date)->format('Y') . ' - ' : '' }}{{ $education->end_date ? \Carbon\Carbon::parse($education->end_date)->format('Y') : 'Present' }}]
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
                                                        </button>
                                                        <button class="btn btn-sm btn-light-danger delete-education-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteEducationModal{{ $education->id }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>


                                                @if (!$loop->last)
                                                    <div class="separator separator-dashed my-3"></div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="text-center text-muted">
                                                No education data available.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- === Modal Detail Education (hanya 1 modal statis, bukan per-id) === --}}
                        @include('website.modal.education.detail')

                        {{-- === Modal Create Education === --}}
                        @include('website.modal.education.create', ['employee_id' => $employee->id])

                        {{-- === Loop untuk modal update dan delete agar tidak ikut terbatas 3 data === --}}
                        @foreach ($educations as $education)
                            @include('website.modal.education.update', ['education' => $education])
                            @include('website.modal.education.delete', ['education' => $education])
                        @endforeach

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
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#addExperienceModal">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#allExperienceDetailModal">
                                            <i class="fas fa-info"></i> Detail
                                        </button>
                                    </div>
                                </div>

                                <div id="kt_activity_year" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        @php
                                            $experienceCount = $workExperiences->count();
                                        @endphp

                                        @if ($experienceCount > 0)
                                            @foreach ($workExperiences->take(3) as $experience)
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold mb-2">{{ $experience->department }}</div>
                                                        <div
                                                            class="fw-semibold text-gray-600 d-flex flex-wrap align-items-center gap-2">
                                                            {{-- <span>{{ $experience->position }}</span> --}}
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




                                                @if (!$loop->last)
                                                    <div class="separator separator-dashed my-3"></div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="text-center text-muted">
                                                No work experience data available.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- work experience modal input --}}
                        @include('website.modal.work.create', [
                            'employee_id' => $employee->id,
                        ])
                        @include('website.modal.work.all_detail')
                        {{-- Modal update, delete, dan detail untuk setiap experience --}}
                        @foreach ($workExperiences as $experience)
                            @include('website.modal.work.detail', ['experience' => $experience])
                            @include('website.modal.work.update', ['experience' => $experience])
                            @include('website.modal.work.delete', ['experience' => $experience])
                        @endforeach

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
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#addAppraisalModal">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#alldetailAppraisalModal">
                                            <i class="fas fa-info"></i> Detail
                                        </button>
                                    </div>
                                </div>

                                <div id="kt_account_settings_signin_method" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        @php
                                            $appraisalCount = $performanceAppraisals->count();
                                        @endphp

                                        @if ($appraisalCount > 0)
                                            @foreach ($performanceAppraisals->take(3) as $appraisal)
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


                                                @if (!$loop->last)
                                                    <div class="separator separator-dashed my-3"></div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="text-center text-muted">
                                                No appraisal data available.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- appraisal modal --}}
                        @include('website.modal.appraisal.create', [
                            'employee_id' => $employee->id,
                        ])
                        @include('website.modal.appraisal.all_detail')

                        @foreach ($performanceAppraisals as $appraisal)
                            @include('website.modal.appraisal.detail', ['appraisal' => $appraisal])
                            @include('website.modal.appraisal.update', ['appraisal' => $appraisal])
                            @include('website.modal.appraisal.delete', ['appraisal' => $appraisal])
                        @endforeach
                        {{-- end of appraisal modal --}}
                    </div>

                    @if (auth()->user()->role == 'HRD')
                        <div class="row">
                            <!-- Card 2: Historical Human Assets Value -->
                            <div class="col-md-12">
                                <div class="card mb-5 mb-xl-10">
                                    <div
                                        class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center">
                                        <div class="card-title m-0">
                                            <h3 class="fw-bolder m-0">Historical Human Assets Value</h3>
                                        </div>
                                        <div class="d-flex gap-3">
                                            <a class="btn btn-sm btn-info"
                                                onclick="window.location.href='{{ route('hav.list', ['company' => $employee->company_name, 'npk' => $employee->npk]) }}'">
                                                <i class="fas fa-info"></i> Detail
                                            </a>
                                        </div>
                                    </div>


                                    <div id="kt_account_human_assets" class="collapse show">
                                        <div class="card-body border-top p-10">
                                            @php
                                                $humanAssetsCount = isset($humanAssets) ? count($humanAssets) : 0;
                                                $titles = [
                                                    1 => 'Star',
                                                    2 => 'Future Star',
                                                    3 => 'Future Star',
                                                    4 => 'Potential Candidate',
                                                    5 => 'Raw Diamond',
                                                    6 => 'Candidate',
                                                    7 => 'Top Performer',
                                                    8 => 'Strong Performer',
                                                    9 => 'Career Person',
                                                    10 => 'Most Unfit Employee',
                                                    11 => 'Unfit Employee',
                                                    12 => 'Problem Employee',
                                                    13 => 'Maximal Contributor',
                                                    14 => 'Contributor',
                                                    15 => 'Minimal Contributor',
                                                    16 => 'Dead Wood',
                                                ];
                                            @endphp


                                            @if ($humanAssetsCount > 0)
                                                @foreach ($humanAssets->take(3) as $asset)
                                                    <div class="d-flex flex-wrap align-items-center">
                                                        <div id="kt_signin_email">
                                                            <div class="fs-6 fw-bold mb-1">
                                                                {{ $titles[(int) $asset['quadrant']] ?? 'Unknown' }}
                                                                <div class="text-muted fs-7">
                                                                    {{ $asset['year'] }}
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                    @if (!$loop->last)
                                                        <div class="separator separator-dashed my-4"></div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <div class="text-center text-muted mb-3">
                                                    No human asset data available.
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
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

                                                    <td class="text-center">
                                                        {{ \Illuminate\Support\Carbon::parse($astraTraining->date_end)->format('Y') }}
                                                    </td>
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
                {{-- Include Modals Update & Delete (outside of foreach) --}}
                @foreach ($astraTrainings as $astraTraining)
                    @include('website.modal.astra_training.update', ['astraTraining' => $astraTraining])
                    @include('website.modal.astra_training.delete', ['astraTraining' => $astraTraining])
                @endforeach

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

                                                    <td class="text-center">
                                                        {{ \Illuminate\Support\Carbon::parse($externalTraining->date_end)->format('Y') }}
                                                    </td>
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
            @foreach ($externalTrainings as $externalTraining)
                @include('website.modal.external_training.update', [
                    'externalTraining' => $externalTraining,
                ])
                @include('website.modal.external_training.delete', [
                    'externalTraining' => $externalTraining,
                ])
            @endforeach

            @include('website.modal.external_training.detail')
            @include('website.modal.external_training.create', ['employee_id' => $employee->id])
            {{-- end of external_training modal --}}


            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header bg-light-primary d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        <h3>Promotion History</h3>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createPromotionModal">
                            <i class="fas fa-plus"></i> Add
                        </button>

                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                            data-bs-target="#detailPromotionHistoryModal">
                            <i class="fas fa-info"></i> Detail
                        </button>
                    </div>
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
                                            {{ Carbon\Carbon::parse($promotionHistory->last_promotion_date)->format('j F Y') }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light-warning me-1" data-bs-toggle="modal"
                                                data-bs-target="#editPromotionModal{{ $promotionHistory->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light-danger delete-experience-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePromotionModal{{ $promotionHistory->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
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

            @include('website.modal.promotion_history.create', [
                'employee_id' => $employee->id,
            ])

            @include('website.modal.promotion_history.detail')
            {{-- Promotion History Modals --}}
            @foreach ($promotionHistories as $promotionHistory)
                @include('website.modal.promotion_history.update', [
                    'experience' => $promotionHistory, // Pastikan file update expect key ini
                ])
                @include('website.modal.promotion_history.delete', [
                    'promotionHistory' => $promotionHistory,
                ])
            @endforeach


            <div class="row">
                <!-- Strength -->
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center cursor-pointer"
                            role="button">

                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Strength</h3>
                            </div>

                            @if ($assessment && $assessment->date)
                                <a class="btn btn-sm btn-info"
                                    href="{{ route('assessments.showByDate', ['assessment_id' => $assessment->id, 'date' => $assessment->date]) }}">
                                    <i class="fas fa-info"></i> Detail
                                </a>
                            @else
                                <button class="btn btn-sm btn-info" onclick="showAssessmentAlert()">
                                    Detail
                                </button>
                            @endif
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

                        </div>
                    </div>
                </div>
            </div>

            <!-- Table 2: Individual Development Plan -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-header bg-light-primary d-flex justify-content-between align-items-center">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Individual Development Plan</h3>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-info"
                            href="{{ route('idp.index', ['company' => $employee->company_name, 'npk' => $employee->npk]) }}">
                            <i class="fas fa-info"></i> Detail
                        </a>
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
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Back
                </a>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <!-- SweetAlert (kalau belum dimuat di layout) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function() {
            // Helper: jalankan saat DOM siap
            function onReady(fn) {
                if (document.readyState !== 'loading') fn();
                else document.addEventListener('DOMContentLoaded', fn);
            }

            onReady(function() {
                // === Hitung Working Period dari aisin_entry_date ===
                const joinInput = document.querySelector('input[name="aisin_entry_date"]');
                const periodInput = document.querySelector('input[name="working_period"]');
                if (joinInput && periodInput) {
                    joinInput.addEventListener('change', function() {
                        const joinDate = new Date(this.value);
                        const now = new Date();

                        if (!isNaN(joinDate.getTime())) {
                            let years = now.getFullYear() - joinDate.getFullYear();
                            const nowMD = (now.getMonth() * 100) + now.getDate();
                            const joinMD = (joinDate.getMonth() * 100) + joinDate.getDate();
                            if (nowMD < joinMD) years--;
                            periodInput.value = Math.max(years, 0);
                        } else {
                            periodInput.value = 0;
                        }
                    });
                }

                // === Show more / less (tanpa jQuery) ===
                document.querySelectorAll('.show-more').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const textContainer = this.parentElement.querySelector('.text-content');
                        const full = textContainer?.getAttribute('data-fulltext') || '';
                        textContainer.innerHTML = full;
                        this.classList.add('d-none');
                        const less = this.parentElement.querySelector('.show-less');
                        if (less) less.classList.remove('d-none');
                    });
                });

                document.querySelectorAll('.show-less').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const textContainer = this.parentElement.querySelector('.text-content');
                        const plain = textContainer?.textContent || '';
                        const shortText = plain.length > 200 ? plain.substring(0, 200) + '...' :
                            plain;
                        textContainer.textContent = shortText;
                        this.classList.add('d-none');
                        const more = this.parentElement.querySelector('.show-more');
                        if (more) more.classList.remove('d-none');
                    });
                });

                // === Toggle select hirarki berdasarkan Position ===
                function toggleHierarchySelects(position) {
                    ['subsection-group', 'section-group', 'department-group', 'division-group', 'plant-group']
                    .forEach(id => document.getElementById(id)?.classList.add('d-none'));

                    if (['Operator', 'Act JP', 'Act Leader', 'Leader'].includes(position)) {
                        document.getElementById('subsection-group')?.classList.remove('d-none');
                    } else if (['Supervisor', 'Section Head', 'Act Supervisor', 'Act Section Head'].includes(
                            position)) {
                        document.getElementById('section-group')?.classList.remove('d-none');
                    } else if (['Manager', 'Coordinator', 'Act Manager', 'Act Coordinator'].includes(
                            position)) {
                        document.getElementById('department-group')?.classList.remove('d-none');
                    } else if (['GM', 'Act GM'].includes(position)) {
                        document.getElementById('division-group')?.classList.remove('d-none');
                    } else if (['Direktur'].includes(position)) {
                        document.getElementById('plant-group')?.classList.remove('d-none');
                    }
                }

                const posSelect = document.getElementById('position-select');
                if (posSelect) {
                    toggleHierarchySelects(posSelect.value || '');
                    posSelect.addEventListener('change', function() {
                        toggleHierarchySelects(this.value);
                    });
                }

                // === Stacking multiple Bootstrap 5 modals (tanpa jQuery) ===
                let modalLevel = 0;
                document.addEventListener('show.bs.modal', function(ev) {
                    const modal = ev.target;
                    const z = 1050 + (10 * modalLevel);
                    modal.style.zIndex = z;
                    setTimeout(() => {
                        document.querySelectorAll('.modal-backdrop:not(.modal-stack)').forEach(
                            el => {
                                el.style.zIndex = z - 1;
                                el.classList.add('modal-stack');
                            });
                    }, 0);
                    modalLevel++;
                });

                document.addEventListener('hidden.bs.modal', function() {
                    modalLevel = Math.max(0, modalLevel - 1);
                    if (document.querySelectorAll('.modal.show').length > 0) {
                        document.body.classList.add('modal-open');
                    }
                });

                // === Batasan input nomor telp ===
                const phoneInput = document.getElementById('phone_number');
                if (phoneInput) {
                    phoneInput.addEventListener('input', function() {
                        this.value = this.value.replace(/\D/g, '').slice(0, 14);
                    });
                }
            });
        })();
    </script>
@endpush
