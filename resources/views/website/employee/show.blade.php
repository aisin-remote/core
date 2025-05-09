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
                <div class="col-md-4 col-sm-12">
                    <div class="card mb-5 mb-xl-10" style="height: 1020px !important">
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
                                    <div class="col-lg-12 d-flex flex-column align-items-center text-center mb-8">
                                        <div class="image-input image-input-outline" data-kt-image-input="true"
                                            style="background-image: url('/metronic8/demo1/assets/media/svg/avatars/blank.svg')">
                                            <div class="image-input-wrapper d-flex justify-content-center align-items-center"
                                                style="background-image: url('{{ $employee->photo ? asset('storage/' . $employee->photo) : '/metronic8/demo1/assets/media/svg/avatars/blank.svg' }}');
                                                height: 150px; width: 150px; background-size: cover;">
                                            </div>
                                        </div>
                                        <h4 class="mt-6 fw-bolder text-center">{{ $employee->name }}</h4>
                                        @php
                                            $position = $employee->position;

                                            $positionLabelMap = [
                                                'Direktur' => $employee->plant?->name,
                                                'GM' => $employee->division?->name,
                                                'Act GM' => $employee->division?->name,
                                            ];

                                            $unitName = $positionLabelMap[$position] ?? $employee->department?->name;
                                        @endphp

                                        <p class="fw-bolder text-muted text-center">
                                            {{ $employee->position }} - {{ $unitName }}
                                        </p>
                                    </div>

                                    <!-- Details Section -->
                                    <div class="mt-4">
                                        <div class="mt-2">
                                            <div class="row">
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">NPK</label>
                                                    <input readonly type="text" name="npk"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap" value="{{ old('npk', $employee->npk) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Gender</label>
                                                    <input readonly type="text" name="gender"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Gender"
                                                        value="{{ old('gender', $employee->gender) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Birthday Date</label>
                                                    <input readonly type="date" name="birthday_date"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Birthday Date"
                                                        value="{{ old('birthday_date', $employee->birthday_date) }}">
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
                                                    <input readonly type="email" name="email"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Email"
                                                        value="{{ old('email', $employee->user?->email) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Phone Number</label>
                                                    <input readonly type="text" name="phone_number"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Phone Number"
                                                        value="{{ old('phone_number', $employee->phone_number) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Company Name</label>
                                                    <select disabled name="company_name" class="form-select form-select-sm"
                                                        data-control="select2">
                                                        <option value="">-- Pilih Perusahaan --</option>
                                                        <option value="AII"
                                                            {{ old('company_name', $employee->company_name) == 'AII' ? 'selected' : '' }}>
                                                            Aisin Indonesia</option>
                                                        <option value="AIIA"
                                                            {{ old('company_name', $employee->company_name) == 'AIIA' ? 'selected' : '' }}>
                                                            Aisin Indonesia Automotive</option>
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Company Group</label>
                                                    <input readonly type="text" name="company_group"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Company Group"
                                                        value="{{ old('company_group', $employee->company_group) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Join Date</label>
                                                    <input readonly type="date" name="aisin_entry_date"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Join Date"
                                                        value="{{ old('aisin_entry_date', $employee->aisin_entry_date) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Working Period</label>
                                                    <input readonly type="text" name="working_period"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Working Period"
                                                        value="{{ old('working_period', $employee->working_period) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Aisin Grade</label>
                                                    <input readonly type="text" name="grade"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Grade" value="{{ old('grade', $employee->grade) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Astra Grade</label>
                                                    <input readonly type="text" name="grade"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Grade" value="{{ $employee->astra_grade }}">
                                                </div>
                                                @php
                                                    $position = $employee->position;
                                                    $selectData = [
                                                        'Direktur' => [
                                                            'label' => 'Plant',
                                                            'name' => 'plant_id',
                                                            'options' => $plants,
                                                            'selected' => (int) old('plant_id', $employee->plant?->id),
                                                        ],
                                                        'GM' => [
                                                            'label' => 'Division',
                                                            'name' => 'division_id',
                                                            'options' => $divisions,
                                                            'selected' => (int) old(
                                                                'division_id',
                                                                $employee->division?->id,
                                                            ),
                                                        ],
                                                        'Act GM' => [
                                                            'label' => 'Division',
                                                            'name' => 'division_id',
                                                            'options' => $divisions,
                                                            'selected' => (int) old(
                                                                'division_id',
                                                                $employee->division?->id,
                                                            ),
                                                        ],
                                                    ];

                                                    // Default (untuk semua posisi lainnya)
                                                    $default = [
                                                        'label' => 'Department',
                                                        'name' => 'department_id',
                                                        'options' => $departments,
                                                        'selected' => (int) old(
                                                            'department_id',
                                                            $employee->department?->id,
                                                        ),
                                                    ];

                                                    $field = $selectData[$position] ?? $default;
                                                @endphp

                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">{{ $field['label'] }}</label>
                                                    <select disabled name="{{ $field['name'] }}"
                                                        aria-label="Pilih {{ $field['label'] }}" data-control="select2"
                                                        data-placeholder="Pilih {{ strtolower($field['label']) }}"
                                                        class="form-select form-select-sm fw-semibold">
                                                        <option value="">Pilih {{ $field['label'] }}</option>
                                                        @foreach ($field['options'] as $option)
                                                            <option value="{{ $option->id }}"
                                                                {{ $field['selected'] == (int) $option->id ? 'selected' : '' }}>
                                                                {{ $option->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Card 1: Educational Background -->
                <div class="col-md-8 col-sm-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-5 mb-xl-10">
                                <div
                                    class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center">
                                    <h3 class="fw-bolder m-0">Educational Background</h3>

                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#detailEducationModal">
                                            <i class="fas fa-info"></i> Detail
                                        </button>
                                    </div>
                                </div>

                                <div id="kt_account_settings_signin_method" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        @if ($educations->isNotEmpty())
                                            @foreach ($educations->take(3) as $index => $education)
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold">
                                                            {{ $education->educational_level }} - {{ $education->major }}
                                                        </div>
                                                        <div class="fw-semibold text-gray-600">
                                                            {{ $education->institute }}
                                                        </div>
                                                    </div>
                                                    <span class="text-muted fs-7">
                                                        {{ $education->start_date ? \Carbon\Carbon::parse($education->start_date)->format('Y') . ' - ' : '' }}{{ $education->end_date ? \Carbon\Carbon::parse($education->end_date)->format('Y') : 'Present' }}
                                                    </span>
                                                </div>

                                                @if (!$loop->last)
                                                    <div class="separator separator-dashed my-3"></div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="text-center text-muted mb-3">
                                                No educational background data available.
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-3">
                                                <a class="fw-semibold text-primary"
                                                    href="{{ route('employee.edit', $employee->npk) }}">
                                                    Go to employee edit page
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('website.modal.education.detail')

                    <div class="row">
                        <!-- Working Experience -->
                        <div class="col-md-12">
                            <div class="card mb-5 mb-xl-10">
                                <div
                                    class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center">
                                    <h3 class="fw-bolder m-0">Work Experience</h3>

                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#allExperienceDetailModal">
                                            <i class="fas fa-info"></i> Detail
                                        </button>
                                    </div>
                                </div>

                                <div id="kt_account_settings_signin_method" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        @if ($workExperiences->isNotEmpty())
                                            @foreach ($workExperiences->take(3) as $experience)
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold">
                                                            {{ $experience->department }}
                                                        </div>
                                                        <div class="fw-semibold text-gray-600">
                                                            {{ $experience->position }}
                                                        </div>
                                                    </div>
                                                    <div class="text-muted fs-7">
                                                        {{ \Illuminate\Support\Carbon::parse($experience->start_date)->format('Y') }}
                                                        -
                                                        {{ $experience->end_date ? \Illuminate\Support\Carbon::parse($experience->end_date)->format('Y') : 'Present' }}
                                                    </div>
                                                </div>

                                                @if (!$loop->last)
                                                    <div class="separator separator-dashed my-3"></div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="text-center text-muted mb-3">
                                                No work experience data available.
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-3">
                                                <a class="fw-semibold text-primary"
                                                    href="{{ route('employee.edit', $employee->npk) }}">
                                                    Go to employee edit page
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('website.modal.work.all_detail')

                    <div class="row">
                        <!-- Historical Performance Appraisal -->
                        <div class="col-md-12">
                            <div class="card mb-5">
                                <div
                                    class="card-header bg-light-primary border-0 d-flex justify-content-between align-items-center">
                                    <h3 class="fw-bolder m-0">Historical Performance Appraisal</h3>

                                    <div class="d-flex gap-3">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#alldetailAppraisalModal">
                                            <i class="fas fa-info"></i> Detail
                                        </button>
                                    </div>
                                </div>

                                <div id="kt_account_settings_signin_method" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        @if ($performanceAppraisals->isNotEmpty())
                                            @foreach ($performanceAppraisals->take(3) as $appraisal)
                                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fs-6 fw-bold">Score - {{ $appraisal->score }}</div>
                                                        <div class="text-muted fs-7">
                                                            {{ \Illuminate\Support\Carbon::parse($appraisal->date)->format('d M Y') }}
                                                        </div>
                                                    </div>
                                                </div>

                                                @if (!$loop->last)
                                                    <div class="separator separator-dashed my-3"></div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="text-center text-muted mb-3">
                                                No performance appraisal data available.
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-3">
                                                <a class="fw-semibold text-primary"
                                                    href="{{ route('employee.edit', $employee->npk) }}">
                                                    Go to employee edit page
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('website.modal.appraisal.all_detail')

                    @if (auth()->user()->role == 'HRD')
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
                                            @php
                                                $humanAssets = [];
                                                $humanAssetsCount = count($humanAssets);
                                            @endphp

                                            @if ($humanAssetsCount > 0)
                                                @foreach ($humanAssets->take(3) as $asset)
                                                    <div class="d-flex flex-wrap align-items-center">
                                                        <div id="kt_signin_email">
                                                            <div class="fs-6 fw-bold mb-1">
                                                                {{ $asset['title'] }} [{{ $asset['count'] }}]
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
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <a class="fw-semibold"
                                                        href="{{ route('hav.index', $employee->npk) }}">
                                                        Go to hav page
                                                    </a>
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
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#detailAstraTrainingModal">
                                    <i class="fas fa-info"></i> Detail
                                </button>
                            </div>
                        </div>

                        <div id="kt_account_astra_trainings" class="collapse show">
                            <div class="card-body border-top p-5">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
                                        <thead class="border-gray-200 fs-5 fw-semibold bg-lighten">
                                            <tr>

                                                <th class="text-center">Year</th>
                                                <th class="text-center">Program</th>
                                                <th class="text-center">ICT/Project/Total</th>
                                            </tr>
                                        </thead>

                                        <tbody class="fw-6 fw-semibold text-gray-600">
                                            @php
                                                $maxSlots = 3;
                                                $trainingCount = $astraTrainings->count();
                                            @endphp

                                            @if ($trainingCount === 0)
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No data available
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center gap-3">
                                                            <a class="fw-semibold text-primary"
                                                                href="{{ route('employee.edit', $employee->npk) }}">
                                                                Go to employee edit page
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @else
                                                @for ($i = 0; $i < $maxSlots; $i++)
                                                    @if (isset($astraTrainings[$i]))
                                                        @php $training = $astraTrainings[$i]; @endphp
                                                        <tr>

                                                            <td class="text-center">
                                                                {{ \Illuminate\Support\Carbon::parse($training->date_end)->format('Y') }}
                                                            </td>
                                                            <td class="text-center">{{ $training->program }}</td>
                                                            <td class="text-center">
                                                                {{ $training->ict_score }}/{{ $training->project_score }}/{{ $training->total_score }}
                                                            </td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td colspan="3">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center gap-3">
                                                                    <a class="fw-semibold text-primary"
                                                                        href="{{ route('employee.edit', $employee->npk) }}">
                                                                        Go to employee edit page
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endfor
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('website.modal.astra_training.detail')

                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header bg-light-primary border-0 cursor-pointer d-flex justify-content-between align-items-center"
                            role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_connected_accounts"
                            aria-expanded="true" aria-controls="kt_account_connected_accounts">

                            <h3 class="fw-bolder m-0">External Training History</h3>

                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#detailExternalTrainingModal">
                                    <i class="fas fa-info"></i> Detail
                                </button>
                            </div>
                        </div>

                        <div id="kt_account_external_trainings" class="collapse show">
                            <div class="card-body border-top p-5">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
                                        <thead class="border-gray-200 fs-5 fw-semibold bg-lighten">
                                            <tr>
                                                <th>Training</th>

                                                <th class="text-center">Year</th>
                                                <th class="text-center">Vendor</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-6 fw-semibold text-gray-600">
                                            @php
                                                $maxSlots = 3;
                                                $externalCount = $externalTrainings->count();
                                            @endphp

                                            @if ($externalCount === 0)
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No data available
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center gap-3">
                                                            <a class="fw-semibold text-primary"
                                                                href="{{ route('employee.edit', $employee->npk) }}">
                                                                Go to employee edit page
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @else
                                                @for ($i = 0; $i < $maxSlots; $i++)
                                                    @if (isset($externalTrainings[$i]))
                                                        @php $training = $externalTrainings[$i]; @endphp
                                                        <tr>
                                                            <td>{{ $training->program }}</td>

                                                            <td class="text-center">
                                                                {{ \Illuminate\Support\Carbon::parse($training->date_end)->format('Y') }}
                                                            </td>
                                                            <td class="text-center">{{ $training->vendor }}</td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td colspan="3">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center gap-3">
                                                                    <a class="fw-semibold text-primary"
                                                                        href="{{ route('employee.edit', $employee->npk) }}">
                                                                        Go to employee edit page
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endfor
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('website.modal.external_training.detail')

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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No data available</td>
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
        });
    </script>
@endpush
