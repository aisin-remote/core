@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee Details' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
@endsection

@push('styles')
    <style>
        /* Kartu kiri nempel saat scroll */
        .sticky-card {
            position: sticky;
            top: 80px;
        }

        .card-header-min {
            min-height: 56px;
        }

        .image-input-wrapper {
            width: 150px;
            height: 150px;
            border-radius: .75rem
        }

        table thead th {
            vertical-align: middle;
        }

        .section-actions .btn {
            padding: .25rem .5rem
        }
    </style>
@endpush

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

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="container mt-4">
            <div class="row g-5">
                {{-- ================= LEFT: PROFILE ================= --}}
                <div class="col-12 col-lg-4">
                    <div class="card mb-5 mb-xl-10 sticky-card">
                        <div class="card-header bg-light-primary border-0 card-header-min">
                            <h3 class="fw-bold m-0">Profile Details</h3>
                        </div>

                        <form id="kt_account_profile_details_form" class="form fv-plugins-bootstrap5 fv-plugins-framework"
                            action="{{ route('employee.update', $employee->id) }}" method="POST"
                            enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="card border-top rounded-4 p-9">
                                <div class="col-lg-12 text-center mb-8">
                                    <div class="image-input image-input-outline" data-kt-image-input="true"
                                        style="background-image: url('/metronic8/demo1/assets/media/svg/avatars/blank.svg')">
                                        <div class="image-input-wrapper"
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

                                    <div class="form-text text-warning">
                                        <i class="bi bi-info-circle"></i> Format: PNG, JPG, JPEG. Maks 2 MB.
                                    </div>
                                    @error('photo')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Fields --}}
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Name</label>
                                        <input type="text" name="name"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ old('name', $employee->name) }}" placeholder="Full Name">
                                        @error('name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">NPK</label>
                                        <input type="text" name="npk"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ old('npk', $employee->npk) }}" placeholder="NPK">
                                        @error('npk')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Gender</label>
                                        <input type="text" name="gender"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ old('gender', $employee->gender) }}" placeholder="Gender">
                                        @error('gender')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Birthday Date</label>
                                        <input type="date" name="birthday_date"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ old('birthday_date', $employee->birthday_date) }}">
                                        @error('birthday_date')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @php $age = $employee->birthday_date ? Carbon\Carbon::parse($employee->birthday_date)->age : null; @endphp
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Age</label>
                                        <input readonly type="text"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ $age }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Email</label>
                                        <input type="text" class="form-control form-control-sm form-control-solid"
                                            value="{{ $employee->user->email ?? '-' }}" readonly>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Phone Number</label>
                                        <input type="number" id="phone_number" name="phone_number"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ $employee->phone_number }}" placeholder="Phone Number">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Company Name</label>
                                        <select name="company_name" class="form-select form-select-sm"
                                            data-control="select2">
                                            <option value="">-- Pilih Perusahaan --</option>
                                            <option value="AII" @selected(old('company_name', $employee->company_name) == 'AII')>Aisin Indonesia</option>
                                            <option value="AIIA" @selected(old('company_name', $employee->company_name) == 'AIIA')>Aisin Indonesia Automotive
                                            </option>
                                        </select>
                                        @error('company_name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Company Group</label>
                                        <input type="text" name="company_group"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ old('company_group', $employee->company_group) }}"
                                            placeholder="Company Group">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Join Date</label>
                                        <input type="date" name="aisin_entry_date"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ old('aisin_entry_date', $employee->aisin_entry_date) }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Working Period</label>
                                        <input type="text" name="working_period"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ old('working_period', $employee->working_period) }}"
                                            placeholder="Working Period">
                                    </div>

                                    {{-- Position + hirarki --}}
                                    <div class="col-12">
                                        <label class="form-label fw-bold fs-6">Position</label>
                                        <select name="position" id="position-select"
                                            class="form-select form-select-sm fw-semibold" data-control="select2">
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
                                                <option value="{{ $value }}" @selected(old('position', $employee->position ?? '') == $value)>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('position')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Sub Section --}}
                                    <div id="subsection-group" class="col-12 d-none">
                                        <label class="form-label fw-bold fs-6">Sub Section</label>
                                        <select name="sub_section_id" class="form-select form-select-sm fw-semibold"
                                            data-control="select2">
                                            <option value="">Pilih Sub Section</option>
                                            @foreach ($subSections as $subSection)
                                                <option value="{{ $subSection->id }}" @selected(old('sub_section_id', $employee->subSection->id ?? '') == $subSection->id)>
                                                    {{ $subSection->name }} - {{ $subSection->section->company }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('sub_section_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Section --}}
                                    <div id="section-group" class="col-12 d-none">
                                        <label class="form-label fw-bold fs-6">Section</label>
                                        <select name="section_id" class="form-select form-select-sm fw-semibold"
                                            data-control="select2">
                                            <option value="">Pilih Section</option>
                                            @foreach ($sections as $section)
                                                <option value="{{ $section->id }}" @selected(old('section_id', (int) $employee->leadingSection?->id ?? '') == (int) $section->id)>
                                                    {{ $section->name }} - {{ $section->company }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('section_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Department --}}
                                    <div id="department-group" class="col-12 d-none">
                                        <label class="form-label fw-bold fs-6">Department</label>
                                        <select name="department_id" class="form-select form-select-sm fw-semibold"
                                            data-control="select2">
                                            <option value="">Pilih Department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}" @selected(old('department_id', (int) $employee->leadingDepartment?->id ?? '') == (int) $department->id)>
                                                    {{ $department->name }} - {{ $department->company }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Division --}}
                                    <div id="division-group" class="col-12 d-none">
                                        <label class="form-label fw-bold fs-6">Division</label>
                                        <select name="division_id" class="form-select form-select-sm fw-semibold"
                                            data-control="select2">
                                            <option value="">Pilih Division</option>
                                            @foreach ($divisions as $division)
                                                <option value="{{ $division->id }}" @selected(old('division_id', $employee->leadingDivision?->id ?? '') == $division->id)>
                                                    {{ $division->name }} - {{ $division->company }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('division_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Plant --}}
                                    <div id="plant-group" class="col-12 d-none">
                                        <label class="form-label fw-bold fs-6">Plant</label>
                                        <select name="plant_id" class="form-select form-select-sm fw-semibold"
                                            data-control="select2">
                                            <option value="">Pilih Plant</option>
                                            @foreach ($plants as $plant)
                                                <option value="{{ $plant->id }}" @selected(old('plant_id', $employee->plant->id ?? '') == $plant->id)>
                                                    {{ $plant->name }} - {{ $plant->company }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('plant_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Aisin Grade</label>
                                        <select name="grade" class="form-control form-control-sm form-control-solid"
                                            required>
                                            <option value="">-- Select Grade --</option>
                                            @foreach ($grade as $g)
                                                <option value="{{ $g->aisin_grade }}" @selected(old('grade', $employee->grade ?? '') == $g->aisin_grade)>
                                                    {{ $g->aisin_grade }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('grade')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-6">Astra Grade</label>
                                        <input readonly type="text"
                                            class="form-control form-control-sm form-control-solid"
                                            value="{{ $employee->astra_grade }}" placeholder="Grade">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ================= RIGHT: CONTENT ================= --}}
                <div class="col-12 col-lg-8">
                    {{-- EDUCATION --}}
                    <div class="card mb-5 mb-xl-10">
                        <div
                            class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center card-header-min">
                            <h3 class="fw-bolder m-0">Educational Background</h3>
                            <div class="section-actions d-flex gap-2">
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
                        <div class="collapse show">
                            <div class="card-body border-top p-10">
                                @if ($educations->count())
                                    @foreach ($educations->take(3) as $education)
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div>
                                                <div class="fs-6 fw-bold mb-2">{{ $education->educational_level }} -
                                                    {{ $education->major }}</div>
                                                <div
                                                    class="fw-semibold text-gray-600 d-flex flex-wrap align-items-center gap-2">
                                                    <span>{{ $education->institute }}</span>
                                                    <span class="text-muted fs-7">
                                                        [{{ $education->start_date ? \Carbon\Carbon::parse($education->start_date)->format('Y') . ' - ' : '' }}
                                                        {{ $education->end_date ? \Carbon\Carbon::parse($education->end_date)->format('Y') : 'Present' }}]
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-light-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editEducationModal{{ $education->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteEducationModal{{ $education->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @unless ($loop->last)
                                            <div class="separator separator-dashed my-3"></div>
                                        @endunless
                                    @endforeach
                                @else
                                    <div class="text-center text-muted">No education data available.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- WORK EXPERIENCE --}}
                    <div class="card mb-5 mb-xl-10">
                        <div
                            class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center card-header-min">
                            <h3 class="fw-bolder m-0">Work Experience</h3>
                            <div class="section-actions d-flex gap-2">
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
                        <div class="collapse show">
                            <div class="card-body border-top p-10">
                                @if ($workExperiences->count())
                                    @foreach ($workExperiences->take(3) as $experience)
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div>
                                                <div class="fs-6 fw-bold mb-2">{{ $experience->department }}</div>
                                                <div
                                                    class="fw-semibold text-gray-600 d-flex flex-wrap align-items-center gap-2">
                                                    <span class="text-muted fs-7">
                                                        [{{ \Carbon\Carbon::parse($experience->start_date)->format('Y') }}
                                                        -
                                                        {{ $experience->end_date ? \Carbon\Carbon::parse($experience->end_date)->format('Y') : 'Present' }}]
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-light-primary" data-bs-toggle="modal"
                                                    data-bs-target="#experienceModal{{ $experience->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editExperienceModal{{ $experience->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteExperienceModal{{ $experience->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @unless ($loop->last)
                                            <div class="separator separator-dashed my-3"></div>
                                        @endunless
                                    @endforeach
                                @else
                                    <div class="text-center text-muted">No work experience data available.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- APPRAISAL --}}
                    <div class="card mb-5">
                        <div
                            class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center card-header-min">
                            <h3 class="fw-bolder m-0">Historical Performance Appraisal</h3>
                            <div class="section-actions d-flex gap-2">
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
                        <div class="collapse show">
                            <div class="card-body border-top p-10">
                                @if ($performanceAppraisals->count())
                                    @foreach ($performanceAppraisals->take(3) as $appraisal)
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
                                        @unless ($loop->last)
                                            <div class="separator separator-dashed my-3"></div>
                                        @endunless
                                    @endforeach
                                @else
                                    <div class="text-center text-muted">No appraisal data available.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- HUMAN ASSETS (HRD only) --}}
                    @if (auth()->user()->role == 'HRD')
                        <div class="card mb-5 mb-xl-10">
                            <div
                                class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center card-header-min">
                                <h3 class="fw-bolder m-0">Historical Human Assets Value</h3>
                                <div class="section-actions">
                                    <a class="btn btn-sm btn-info"
                                        onclick="window.location.href='{{ route('hav.list', ['company' => $employee->company_name, 'npk' => $employee->npk]) }}'">
                                        <i class="fas fa-info"></i> Detail
                                    </a>
                                </div>
                            </div>
                            <div class="collapse show">
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
                                                <div>
                                                    <div class="fs-6 fw-bold mb-1">
                                                        {{ $titles[(int) $asset['quadrant']] ?? 'Unknown' }}
                                                        <div class="text-muted fs-7">{{ $asset['year'] }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            @unless ($loop->last)
                                                <div class="separator separator-dashed my-4"></div>
                                            @endunless
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted mb-3">No human asset data available.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TRAININGS ROW --}}
            <div class="row g-5">
                {{-- ASTRA TRAINING --}}
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div
                            class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center card-header-min">
                            <h3 class="fw-bolder m-0">Astra Training History</h3>
                            <div class="section-actions d-flex gap-2">
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
                        <div class="collapse show">
                            <div class="card-body border-top p-5">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
                                        <thead class="border-gray-200 fs-5 fw-semibold bg-lighten">
                                            <tr>
                                                <th class="text-center">Year</th>
                                                <th class="text-center">Program</th>
                                                <th class="text-center">ICT/Project/Total</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-6 fw-semibold text-gray-600">
                                            @forelse ($astraTrainings->take(3) as $astraTraining)
                                                <tr>
                                                    <td class="text-center">
                                                        {{ \Illuminate\Support\Carbon::parse($astraTraining->date_end)->format('Y') }}
                                                    </td>
                                                    <td class="text-center">{{ $astraTraining->program }}</td>
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
                                                            <button class="btn btn-sm btn-light-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteAstraTrainingModal{{ $astraTraining->id }}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modals Astra Training --}}
                    @include('website.modal.astra_training.detail')
                    @include('website.modal.astra_training.create', ['employee_id' => $employee->id])
                    @foreach ($astraTrainings as $astraTraining)
                        @include('website.modal.astra_training.update', [
                            'astraTraining' => $astraTraining,
                        ])
                        @include('website.modal.astra_training.delete', [
                            'astraTraining' => $astraTraining,
                        ])
                    @endforeach
                </div>

                {{-- EXTERNAL TRAINING --}}
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div
                            class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center card-header-min">
                            <h3 class="fw-bolder m-0">External Training History</h3>
                            <div class="section-actions d-flex gap-2">
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
                        <div class="collapse show">
                            <div class="card-body border-top p-5">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
                                        <thead class="border-gray-200 fs-5 fw-semibold bg-lighten">
                                            <tr>
                                                <th>Training</th>
                                                <th class="text-center">Year</th>
                                                <th class="text-center">Vendor</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
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
                                                            <button class="btn btn-sm btn-light-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteExternalTrainingModal{{ $externalTraining->id }}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modals External Training --}}
                    @include('website.modal.external_training.detail')
                    @include('website.modal.external_training.create', ['employee_id' => $employee->id])
                    @foreach ($externalTrainings as $externalTraining)
                        @include('website.modal.external_training.update', [
                            'externalTraining' => $externalTraining,
                        ])
                        @include('website.modal.external_training.delete', [
                            'externalTraining' => $externalTraining,
                        ])
                    @endforeach
                </div>
            </div>

            {{-- PROMOTION HISTORY --}}
            <div class="card mb-5 mb-xl-10">
                <div
                    class="card-header bg-light-primary d-flex justify-content-between align-items-center card-header-min">
                    <h3 class="m-0">Promotion History</h3>
                    <div class="section-actions d-flex gap-2">
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
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
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
                                            <button class="btn btn-sm btn-light-danger" data-bs-toggle="modal"
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
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modals Promotion --}}
            @include('website.modal.promotion_history.create', ['employee_id' => $employee->id])
            @include('website.modal.promotion_history.detail')
            @foreach ($promotionHistories as $promotionHistory)
                @include('website.modal.promotion_history.update', ['experience' => $promotionHistory])
                @include('website.modal.promotion_history.delete', [
                    'promotionHistory' => $promotionHistory,
                ])
            @endforeach

            {{-- STRENGTH & WEAKNESS --}}
            <div class="row g-5">
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div
                            class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center card-header-min">
                            <h3 class="fw-bold m-0">Strength</h3>
                            @if ($assessment && $assessment->date)
                                <a class="btn btn-sm btn-info"
                                    href="{{ route('assessments.showByDate', ['assessment_id' => $assessment->id, 'date' => $assessment->date]) }}">
                                    <i class="fas fa-info"></i> Detail
                                </a>
                            @else
                                <button class="btn btn-sm btn-info" onclick="showAssessmentAlert()">Detail</button>
                            @endif
                        </div>
                        <div class="collapse show">
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
                                                    <div class="fs-6 fw-bold mb-1">{{ $detail->alc->name ?? 'Unknown' }}
                                                    </div>
                                                    <div class="fw-semibold text-gray-600">
                                                        <span class="text-content"
                                                            data-fulltext="{!! htmlentities($detail->strength) !!}">
                                                            {!! Str::limit($detail->strength, 200) !!}
                                                        </span><br>
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

                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header bg-light-primary border-0 card-header-min" role="button"
                            data-bs-toggle="collapse" data-bs-target="#weakness_section">
                            <h3 class="fw-bold m-0">Areas for Development</h3>
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
                                                    <div class="fs-6 fw-bold mb-1">{{ $detail->alc->name ?? 'Unknown' }}
                                                    </div>
                                                    <div class="fw-semibold text-gray-600">
                                                        <span class="text-content"
                                                            data-fulltext="{!! htmlentities($detail->weakness) !!}">
                                                            {!! Str::limit($detail->weakness, 200) !!}
                                                        </span><br>
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

            {{-- IDP --}}
            <div class="card mb-5 mb-xl-10">
                <div
                    class="card-header bg-light-primary d-flex justify-content-between align-items-center card-header-min">
                    <h3 class="fw-bold m-0">Individual Development Plan</h3>
                    <div class="section-actions d-flex gap-2">
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

            {{-- Back button --}}
            <div class="card-footer text-end mt-4">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Back
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            function onReady(fn) {
                if (document.readyState !== 'loading') fn();
                else document.addEventListener('DOMContentLoaded', fn);
            }
            onReady(function() {
                // Working period calc
                const joinInput = document.querySelector('input[name="aisin_entry_date"]');
                const periodInput = document.querySelector('input[name="working_period"]');
                if (joinInput && periodInput) {
                    joinInput.addEventListener('change', function() {
                        const d = new Date(this.value),
                            now = new Date();
                        if (!isNaN(d.getTime())) {
                            let y = now.getFullYear() - d.getFullYear();
                            const nowMD = (now.getMonth() * 100) + now.getDate();
                            const dMD = (d.getMonth() * 100) + d.getDate();
                            if (nowMD < dMD) y--;
                            periodInput.value = Math.max(y, 0);
                        } else {
                            periodInput.value = 0;
                        }
                    });
                }

                // Show more/less
                document.querySelectorAll('.show-more').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const t = this.parentElement.querySelector('.text-content');
                        const full = t?.getAttribute('data-fulltext') || '';
                        t.innerHTML = full;
                        this.classList.add('d-none');
                        const less = this.parentElement.querySelector('.show-less');
                        if (less) less.classList.remove('d-none');
                    });
                });
                document.querySelectorAll('.show-less').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const t = this.parentElement.querySelector('.text-content');
                        const plain = t?.textContent || '';
                        const short = plain.length > 200 ? plain.substring(0, 200) + '...' :
                            plain;
                        t.textContent = short;
                        this.classList.add('d-none');
                        const more = this.parentElement.querySelector('.show-more');
                        if (more) more.classList.remove('d-none');
                    });
                });

                // Toggle hierarchy selects by position
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

                // Stacking multiple Bootstrap modals
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

                // Phone number: numeric-only, 14 digits
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
