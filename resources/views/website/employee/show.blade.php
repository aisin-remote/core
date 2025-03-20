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
            <div class="card mb-5 mb-xl-10">
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_profile_details" aria-expanded="true"
                    aria-controls="kt_account_profile_details">
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
                            <div class="row">
                                <label class="col-lg-12 col-form-label fw-bold fs-6 text-center">Profile Photo</label>
                                <div class="col-lg-12 text-center mb-8">
                                    <div class="image-input image-input-outline" data-kt-image-input="true"
                                        style="background-image: url('/metronic8/demo1/assets/media/svg/avatars/blank.svg')">
                                        <div class="image-input-wrapper w-125px h-125px"
                                            style="background-image: url('{{ $employee->photo ? asset('storage/' . $employee->photo) : '/metronic8/demo1/assets/media/svg/avatars/blank.svg' }}')">
                                        </div>
                                        <label
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                            title="Ubah avatar">
                                            <i class="fa fa-pencil-alt fs-7"></i>
                                            <input type="file" name="photo" accept=".png, .jpg, .jpeg" id="photoInput">
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
                                <div class="col-lg-6">
                                    <!-- Kolom Pertama -->
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Full Name</label>
                                        <input type="text" name="name"
                                            class="form-control form-control-lg form-control-solid"
                                            placeholder="Nama Lengkap" value="{{ old('name', $employee->name) }}">
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">NPK</label>
                                        <input type="text" name="npk"
                                            class="form-control form-control-lg form-control-solid"
                                            placeholder="Nomor Pokok Karyawan" value="{{ old('npk', $employee->npk) }}">
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Gender</label>
                                        <select name="gender" class="form-select form-select-lg form-select-solid">
                                            <option value="Male"
                                                {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>Laki-laki
                                            </option>
                                            <option value="Female"
                                                {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>
                                                Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Birthday Date</label>
                                        <input type="date" name="birthday_date"
                                            class="form-control form-control-lg form-control-solid"
                                            value="{{ old('birthday_date', $employee->birthday_date) }}">
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Company Name</label>
                                        <select name="company_name" class="form-select form-select-lg form-select-solid">
                                            <option value="">-- Pilih Perusahaan --</option>
                                            <option value="AII"
                                                {{ old('company_name', $employee->company_name) == 'AII' ? 'selected' : '' }}>
                                                Aisin Indonesia</option>
                                            <option value="AIIA"
                                                {{ old('company_name', $employee->company_name) == 'AIIA' ? 'selected' : '' }}>
                                                Aisin Indonesia Automotive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <!-- Kolom Kedua -->
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Join Date</label>
                                        <input type="date" name="aisin_entry_date"
                                            class="form-control form-control-lg form-control-solid"
                                            value="{{ old('aisin_entry_date', $employee->aisin_entry_date) }}">
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Working Period</label>
                                        <input type="number" name="working_period" min="0"
                                            class="form-control form-control-lg form-control-solid"
                                            value="{{ old('working_period', $employee->working_period) }}">
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Company Group</label>
                                        <input type="text" name="company_group"
                                            class="form-control form-control-lg form-control-solid"
                                            value="{{ old('company_group', $employee->company_group) }}">
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Position</label>
                                        <select name="position" class="form-select form-select-lg fw-semibold">
                                            <option value="">Select Position</option>
                                            <option value="General Manager"
                                                {{ old('position', $employee->position ?? '') == 'General Manager' ? 'selected' : '' }}>
                                                General Manager</option>
                                            <option value="Manager"
                                                {{ old('position', $employee->position ?? '') == 'Manager' ? 'selected' : '' }}>
                                                Manager</option>
                                            <option value="Coordinator"
                                                {{ old('position', $employee->position ?? '') == 'Coordinator' ? 'selected' : '' }}>
                                                Coordinator</option>
                                            <option value="Section Head"
                                                {{ old('position', $employee->position ?? '') == 'Section Head' ? 'selected' : '' }}>
                                                Section Head</option>
                                            <option value="Supervisor"
                                                {{ old('position', $employee->position ?? '') == 'Supervisor' ? 'selected' : '' }}>
                                                Supervisor</option>
                                            <option value="Act Leader"
                                                {{ old('position', $employee->position ?? '') == 'Act Leader' ? 'selected' : '' }}>
                                                Act Leader</option>
                                            <option value="Act JP"
                                                {{ old('position', $employee->position ?? '') == 'Act JP' ? 'selected' : '' }}>
                                                Act JP</option>
                                            <option value="Operator"
                                                {{ old('position', $employee->position ?? '') == 'Operator' ? 'selected' : '' }}>
                                                Operator</option>
                                        </select>
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Department</label>
                                        <select name="department_id" aria-label="Pilih Departemen" data-control="select2"
                                            data-placeholder="Pilih departemen"
                                            class="form-select form-select-lg fw-semibold">
                                            <option value="">Pilih Departemen</option>
                                            @foreach ($departments as $department)
                                                <option data-kt-flag="flags/afghanistan.svg"
                                                    value="{{ $department->id }}"
                                                    {{ old('department_id', $employee->departments->first()->id ?? '') == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-6">
                                        <label class="form-label fw-bold fs-6">Grade</label>
                                        <input type="text" name="grade"
                                            class="form-control form-control-lg form-control-solid"
                                            placeholder="Grade Karyawan" value="{{ old('grade', $employee->grade) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save
                                Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Heading-->
                    <div class="card-title">
                        <h3>Promotion History</h3>
                    </div>
                    <!--end::Heading-->
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
                                @forelse ($promotionHistories as $promotionHistory)
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
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <!-- Modal dipindahkan ke luar tbody -->
                            @foreach ($promotionHistories as $promotionHistory)
                                <div class="modal fade" id="deletePromotionModal{{ $promotionHistory->id }}"
                                    tabindex="-1" aria-labelledby="deletePromotionLabel{{ $promotionHistory->id }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="deletePromotionLabel{{ $promotionHistory->id }}">
                                                    Hapus History Promotion
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('promotion.destroy', $promotionHistory->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus data
                                                        <strong>{{ $promotionHistory->id }}</strong> dari riwayat ini?
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <!--end::Tbody-->
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Table wrapper-->
                </div>
                <!--end::Card body-->
            </div>

            <div class="row">
                <!-- Card 1: Educational Background -->
                <div class="col-md-6">
                    <div class="card mb-5 mb-xl-10">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold m-0">Educational Background</h3>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#addEducationModal">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <div id="kt_account_settings_signin_method" class="collapse show">
                            <div class="card-body border-top p-10">
                                @if ($educations->isNotEmpty())
                                    @foreach ($educations as $education)
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div>
                                                <div class="fs-6 fw-bold">
                                                    {{ $education->educational_level }} - {{ $education->major }}
                                                </div>
                                                <div class="fw-semibold text-gray-600">
                                                    {{ $education->institute }}
                                                </div>
                                            </div>
                                            <div class="text-muted fs-7">
                                                {{ \Illuminate\Support\Carbon::parse($education->start_date)->format('Y') }}
                                                -
                                                {{ \Illuminate\Support\Carbon::parse($education->end_date)->format('Y') }}
                                            </div>
                                            <div>
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
                                        @unless ($loop->last)
                                            <div class="separator separator-dashed my-3"></div>
                                        @endunless
                                        <div class="modal fade" id="editEducationModal{{ $education->id }}"
                                            tabindex="-1" aria-labelledby="editEducationModalLabel{{ $education->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="editEducationModalLabel{{ $education->id }}">Edit
                                                            Education History</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('education.update', $education->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="employee_id"
                                                            value="{{ $education->employee_id }}">

                                                        <div class="modal-body">
                                                            <div class="col-lg-12 mb-3">
                                                                <label class="fs-5 fw-bold form-label mb-2">
                                                                    <span class="required">Education Level</span>
                                                                </label>
                                                                <select name="level" aria-label="Select a Category"
                                                                    data-control="select2"
                                                                    data-placeholder="Select categories..."
                                                                    class="form-select form-select-lg fw-semibold">
                                                                    <option value="">Select Category</option>
                                                                    <option value="SMK"
                                                                        {{ old('level', $education->educational_level) == 'SMK' ? 'selected' : '' }}>
                                                                        SMK</option>
                                                                    <option value="D3"
                                                                        {{ old('level', $education->educational_level) == 'D3' ? 'selected' : '' }}>
                                                                        D3</option>
                                                                    <option value="S1"
                                                                        {{ old('level', $education->educational_level) == 'S1' ? 'selected' : '' }}>
                                                                        S1</option>
                                                                    <option value="S2"
                                                                        {{ old('level', $education->educational_level) == 'S2' ? 'selected' : '' }}>
                                                                        S2</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Major</label>
                                                                <input type="text" name="major" class="form-control"
                                                                    value="{{ $education->major }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Institution</label>
                                                                <input type="text" name="institute"
                                                                    class="form-control"
                                                                    value="{{ $education->institute }}" required>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label class="form-label">Start Year</label>
                                                                    <input type="date" name="start_date"
                                                                        class="form-control"
                                                                        value="{{ $education->start_date ? \Illuminate\Support\Carbon::parse($education->start_date)->format('Y-m-d') : '' }}"
                                                                        required>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label">End Year (Optional)</label>
                                                                    <input type="date" name="end_date"
                                                                        class="form-control"
                                                                        value="{{ $education->end_date ? \Illuminate\Support\Carbon::parse($education->end_date)->format('Y-m-d') : '' }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="deleteEducationModal{{ $education->id }}"
                                            tabindex="-1"
                                            aria-labelledby="deleteEducationModalLabel{{ $education->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="deleteEducationModalLabel{{ $education->id }}">Hapus
                                                            Riwayat Pendidikan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('education.destroy', $education->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-body">
                                                            <p>Apakah Anda yakin ingin menghapus
                                                                <strong>{{ $education->educational_level }} -
                                                                    {{ $education->major }}</strong> dari riwayat
                                                                pendidikan?
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-5">
                                        <span class="text-gray-500 fs-6">Tidak ada riwayat pendidikan</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- modal education --}}
                @include('website.modal.education', [
                    'employee_id' => $employee->id,
                ])
                {{-- end of modal education --}}

                <!-- Card 2: Historical Human Assets Value -->
                <div class="col-md-6">
                    <div class="card mb-5 mb-xl-10">
                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                            data-bs-target="#kt_account_connected_accounts" aria-expanded="true"
                            aria-controls="kt_account_connected_accounts">
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Historical Human Assets Value</h3>
                            </div>
                        </div>

                        <div id="kt_account_settings_signin_method" class="collapse show">
                            <div class="card-body border-top p-4">
                                <!-- Mengurangi padding agar card lebih kecil -->
                                <div class="d-flex flex-wrap align-items-center">
                                    <div id="kt_signin_email">
                                        <div class="fs-6 fw-bold mb-1">2024 - Future Star [2]
                                        </div>
                                    </div>
                                </div>

                                <div class="separator separator-dashed my-4"></div>

                                <div class="d-flex flex-wrap align-items-center">
                                    <div id="kt_signin_password">
                                        <div class="fs-6 fw-bold mb-1">2023 - Potential
                                            Candidate [4]</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Working Experience -->
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header border-0 cursor-pointer d-flex justify-content-between align-items-center"
                            role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">

                            <!-- Judul Card -->
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Working Experience</h3>
                            </div>

                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#addExperienceModal">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div id="kt_activity_year" class="card-body ps-5 tab-pane fade show active border-top"
                            role="tabpanel">
                            <div class="timeline timeline-border-dashed">
                                @if ($workExperiences->isEmpty())
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                                        <p class="fs-6">No work experience available.</p>
                                    </div>
                                @else
                                    @foreach ($workExperiences as $experience)
                                        <div class="timeline-item d-flex">
                                            <div class="timeline-line"></div>
                                            <div class="timeline-icon">
                                                <i class="ki-duotone ki-abstract-26 fs-2 text-gray-500">
                                                    <span class="path1"></span><span class="path2"></span>
                                                </i>
                                            </div>
                                            <div class="timeline-content flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <a href="#"
                                                        class="fs-5 fw-semibold text-gray-800 text-hover-primary mb-0">
                                                        {{ $experience->position }}
                                                    </a>
                                                    <div>
                                                        <button class="btn btn-sm btn-light-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#experienceModal{{ $loop->index }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-light-warning edit-experience-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editExperienceModal{{ $loop->index }}"
                                                            data-experience-id="{{ $loop->index }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-light-danger delete-experience-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteExperienceModal{{ $loop->index }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Tambahkan informasi Company -->
                                                <div class="text-gray-700 fw-semibold fs-6">
                                                    {{ $experience->company }}
                                                </div>

                                                <div class="text-muted fs-7">
                                                    {{ \Illuminate\Support\Carbon::parse($experience->start_date)->format('Y') }}
                                                    -
                                                    {{ $experience->end_date ? \Illuminate\Support\Carbon::parse($experience->end_date)->format('Y') : 'Present' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal untuk Detail Pengalaman Kerja -->
                                        <div class="modal fade" id="experienceModal{{ $loop->index }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ $experience->position }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Company:</strong> {{ $experience->company }}</p>
                                                        <p><strong>Period:</strong>
                                                            {{ \Illuminate\Support\Carbon::parse($experience->start_date)->format('d M Y') }}
                                                            -
                                                            {{ $experience->end_date ? \Illuminate\Support\Carbon::parse($experience->end_date)->format('d M Y') : 'Present' }}
                                                        </p>
                                                        <p><strong>Job Description:</strong></p>
                                                        <p>{{ $experience->description }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal untuk Edit Pengalaman Kerja -->
                                        <div class="modal fade" id="editExperienceModal{{ $loop->index }}"
                                            tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Experience</h5>
                                                        <button type="button" class="btn-close close-edit-modal"
                                                            data-bs-dismiss="modal" aria-label="Close"
                                                            data-experience-id="{{ $loop->index }}"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Form Edit -->
                                                        <form
                                                            action="{{ route('work-experience.update', $experience->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="mb-3">
                                                                <label class="form-label">Position</label>
                                                                <input type="text" class="form-control"
                                                                    name="position" value="{{ $experience->position }}"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Company</label>
                                                                <input type="text" class="form-control" name="company"
                                                                    value="{{ $experience->company }}" required>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-6">
                                                                    <label class="form-label">Start Date</label>
                                                                    <input type="date" class="form-control"
                                                                        name="start_date"
                                                                        value="{{ \Illuminate\Support\Carbon::parse($experience->start_date)->format('Y-m-d') }}"
                                                                        required>

                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label">End Date</label>
                                                                    <input type="date" class="form-control"
                                                                        name="end_date"
                                                                        value="{{ $experience->end_date ? \Illuminate\Support\Carbon::parse($experience->end_date)->format('Y-m-d') : '' }}">

                                                                </div>
                                                            </div>
                                                            <div class="mb-10">
                                                                <label class="form-label">Job Description</label>
                                                                <textarea class="form-control" name="description" rows="3">{{ $experience->description }}</textarea>
                                                            </div>
                                                            <div class="text-end">
                                                                <button type="submit" class="btn btn-primary">Save
                                                                    Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Konfirmasi Delete -->
                                        <div class="modal fade" id="deleteExperienceModal{{ $loop->index }}"
                                            tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Delete Experience</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete the experience
                                                            <strong>{{ $experience->position }}</strong> at
                                                            <strong>{{ $experience->company }}</strong>?
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form
                                                            action="{{ route('work-experience.destroy', $experience->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- work experience modal input --}}
                @include('website.modal.work-experience', [
                    'employee_id' => $employee->id,
                ])
                {{-- end of work experience modal input --}}

                <!-- Historical Performance Appraisal -->
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header border-0 cursor-pointer d-flex justify-content-between align-items-center"
                            role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_connected_accounts"
                            aria-expanded="true" aria-controls="kt_account_connected_accounts">
                            <h3 class="fw-bold m-0">Historical Performance Appraisal</h3>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#addAppraisalModal">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <div id="kt_account_settings_signin_method" class="collapse show">
                            <div class="card-body border-top p-10">
                                <div class="d-flex flex-column">
                                    @forelse ($performanceAppraisals as $appraisal)
                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fs-6 fw-bold">Score - {{ $appraisal->score }}</div>
                                                <div class="text-muted fs-7">
                                                    {{ \Illuminate\Support\Carbon::parse($appraisal->date)->format('d M Y') }}
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-light-primary" data-bs-toggle="modal"
                                                    data-bs-target="#detailModal{{ $appraisal->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editAppraisalModal{{ $appraisal->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteAppraisalModal{{ $appraisal->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="editAppraisalModal{{ $appraisal->id }}"
                                            tabindex="-1" aria-labelledby="editAppraisalModalLabel{{ $appraisal->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Appraisal</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('appraisal.update', $appraisal->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Score</label>
                                                                <input type="text" name="score" class="form-control"
                                                                    value="{{ $appraisal->score }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Date</label>
                                                                <input type="date" name="date" class="form-control"
                                                                    value="{{ isset($appraisal) ? \Illuminate\Support\Carbon::parse($appraisal->date)->format('Y-m-d') : old('date') }}"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Description</label>
                                                                <textarea class="form-control" name="description" rows="3">
                                                                    {{ $appraisal->description }}
                                                                </textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        @if (!$loop->last)
                                            <div class="separator separator-dashed my-3"></div>
                                        @endif
                                    @empty
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                                            <p class="fs-6">Belum ada data appraisal yang tersedia.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach ($performanceAppraisals as $appraisal)
                    <!-- Modal Detail -->
                    <div class="modal fade" id="detailModal{{ $appraisal->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detail Appraisal {{ $appraisal->year }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Hasil:</strong> {{ $appraisal->score }}</p>
                                    <p><strong>Catatan:</strong> {{ $appraisal->description }}</p>
                                    <p><strong>Tahun:</strong> {{ $appraisal->date }}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- appraisal modal --}}
                @include('website.modal.appraisal', [
                    'employee_id' => $employee->id,
                ])
                {{-- end of appraisal modal --}}

                <div class="row">
                    <!-- Strength & Weakness -->
                    <div class="col-md-6">
                        <div class="card mb-5">
                            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                data-bs-target="#kt_account_signin_method">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Strength</h3>
                                </div>
                            </div>

                            <div id="kt_account_settings_signin_method" class="collapse show">
                                <div class="card-body border-top p-10">
                                    <div class="d-flex flex-wrap align-items-center">
                                        <div id="kt_signin_email">
                                            <div class="fs-6 fw-bold mb-1">Teamwork
                                            </div>
                                            <div class="fw-semibold text-gray-600">Memahami dan
                                                mengembangkan kompetensi bawahan</div>
                                            <div class="fw-semibold text-gray-600">
                                                Menginspirasi dan memotivasi</div>
                                        </div>
                                    </div>

                                    <div class="separator separator-dashed my-4"></div>

                                    <div class="d-flex flex-wrap align-items-center mb-4">
                                        <div id="kt_signin_password">
                                            <div class="fs-6 fw-bold mb-1">Customer Focus</div>
                                            <div class="fw-semibold text-gray-600">
                                                Pemahaman tentang bisnis inti dari perusahaan
                                                dan mengoptimalkan peluang-peluang
                                                yang ada di dalam/luar organisasi untuk
                                                meningkatkan unjuk kerja perusahaan.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Areas for Development -->
                    <div class="col-md-6">
                        <div class="card mb-5">
                            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                data-bs-target="#kt_account_signin_method">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Areas for Development</h3>
                                </div>
                            </div>

                            <div id="kt_account_settings_signin_method" class="collapse show">
                                <div class="card-body border-top p-10">
                                    <div class="d-flex flex-wrap align-items-center">
                                        <div id="kt_signin_email">
                                            <div class="fs-6 fw-bold mb-1">Leading & Motivating
                                            </div>
                                            <div class="fw-semibold text-gray-600">Memahami dan
                                                mengembangkan kompetensi bawahan</div>
                                            <div class="fw-semibold text-gray-600">
                                                Menginspirasi dan memotivasi</div>
                                        </div>
                                    </div>

                                    <div class="separator separator-dashed my-4"></div>

                                    <div class="d-flex flex-wrap align-items-center mb-4">
                                        <div id="kt_signin_password">
                                            <div class="fs-6 fw-bold mb-1">Vision & Business
                                                Sense</div>
                                            <div class="fw-semibold text-gray-600">
                                                Pemahaman tentang bisnis inti dari perusahaan
                                                dan mengoptimalkan peluang-peluang
                                                yang ada di dalam/luar organisasi untuk
                                                meningkatkan unjuk kerja perusahaan.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                    <td>Meningkatnya pemahaman peran sebagai pemimpin yang
                                        memahami kebutuhan & perasaan bawahan/orang lain</td>
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
        });
    </script>
@endpush
