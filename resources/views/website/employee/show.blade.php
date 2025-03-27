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
                    <div class="card mb-5 mb-xl-10" style="height: 1410px !important">
                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                            data-bs-target="#kt_account_profile_details" aria-expanded="true"
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
                                        <p class="fw-bolder text-muted text-center">
                                            {{ $employee->position }} - {{ $employee->departments->first()->name }}
                                        </p>
                                    </div>

                                    <!-- Details Section -->
                                    <div class="mt-4">
                                        <div class="mt-2">
                                            <div class="row">
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">NPK</label>
                                                    <input readonly type="text" name="npk"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap" value="{{ old('npk', $employee->npk) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Gender</label>
                                                    <input readonly type="text" name="gender"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap"
                                                        value="{{ old('name', $employee->gender) }}">
                                                </div>
                                                <div class="col-6 mb-8">
                                                    <label class="form-label fw-bold fs-6">Birthday Date</label>
                                                    <input readonly type="date" name="birthday_date"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap"
                                                        value="{{ old('name', $employee->birthday_date) }}">
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Email</label>
                                                    <input readonly type="text" name=""
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap"
                                                        value="arief.widodo@aisin-indonesia.co.id">
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Phone Number</label>
                                                    <input readonly type="text" name="email"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap" value="-">
                                                </div>
                                                <div class="col-12 mb-8">
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
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Company Group</label>
                                                    <input readonly type="text" name="company_group"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap"
                                                        value="{{ old('name', $employee->company_group) }}">
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Join Date</label>
                                                    <input readonly type="date" name="aisin_entry_date"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap"
                                                        value="{{ old('name', $employee->aisin_entry_date) }}">
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Working Period</label>
                                                    <input readonly type="text" name="working_period"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap"
                                                        value="{{ old('name', $employee->working_period) }}">
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Department</label>
                                                    <select disabled name="department_id" aria-label="Pilih Departemen"
                                                        data-control="select2" data-placeholder="Pilih departemen"
                                                        class="form-select form-select-sm fw-semibold">
                                                        <option value="">Pilih Departemen</option>
                                                        @foreach ($departments as $department)
                                                            <option data-kt-flag="flags/afghanistan.svg"
                                                                value="{{ $department->id }}"
                                                                {{ old('department_id', $employee->departments->first()->id ?? '') == $department->id ? 'selected' : '' }}>
                                                                {{ $department->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12 mb-8">
                                                    <label class="form-label fw-bold fs-6">Grade</label>
                                                    <input readonly type="text" name="grade"
                                                        class="form-control form-control-sm form-control-solid"
                                                        placeholder="Nama Lengkap"
                                                        value="{{ old('name', $employee->grade) }}">
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
                <div class="col-8">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-5 mb-xl-10">
                                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                    <h3 class="fw-bolder m-0">Educational Background</h3>
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
                                                </div>
                                            @else
                                                <!-- Slot kosong -->
                                                <div
                                                    class="d-flex justify-content-between align-items-center gap-3 border border-dashed p-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold text-muted">[Empty Slot]</div>
                                                        <a class="fw-semibold"
                                                            href="{{ route('employee.edit', $employee->npk) }}">
                                                            Go to employee edit page
                                                        </a>
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
                    </div>
                    <div class="row">
                        <!-- Working Experience -->
                        <div class="col-md-12">
                            <div class="card mb-5 mb-xl-10">
                                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                    <h3 class="fw-bolder m-0">Working Experience</h3>
                                </div>

                                <div id="kt_activity_year" class="card-body ps-5 tab-pane fade show active border-top"
                                    role="tabpanel">
                                    <div class="timeline timeline-border-dashed">
                                        @php
                                            $maxSlots = 3;
                                            $experienceCount = $workExperiences->count();
                                        @endphp

                                        @for ($i = 0; $i < $maxSlots; $i++)
                                            @if (isset($workExperiences[$i]))
                                                @php $experience = $workExperiences[$i]; @endphp
                                                <div class="timeline-item d-flex">
                                                    <div class="timeline-line"></div>
                                                    <div class="timeline-icon">
                                                        <i class="ki-duotone ki-abstract-26 fs-2 text-gray-500">
                                                            <span class="path1"></span><span class="path2"></span>
                                                        </i>
                                                    </div>
                                                    <div class="timeline-content flex-grow-1">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                            <a href="#"
                                                                class="fs-5 fw-semibold text-gray-800 text-hover-primary mb-0">
                                                                {{ $experience->position }}
                                                            </a>
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
                                            @else
                                                <!-- Slot kosong -->
                                                <div
                                                    class="d-flex justify-content-between align-items-center gap-3 border border-dashed p-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold text-muted">[Empty Slot]</div>
                                                        <a class="fw-semibold"
                                                            href="{{ route('employee.edit', $employee->npk) }}">
                                                            Go to employee edit page
                                                        </a>
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
                    </div>
                    <div class="row">
                        <!-- Historical Performance Appraisal -->
                        <div class="col-md-12">
                            <div class="card mb-5">
                                <div class="card-header border-0 cursor-pointer d-flex justify-content-between align-items-center"
                                    role="button" data-bs-toggle="collapse"
                                    data-bs-target="#kt_account_connected_accounts" aria-expanded="true"
                                    aria-controls="kt_account_connected_accounts">
                                    <h3 class="fw-bolder m-0">Historical Performance Appraisal</h3>
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
                                                </div>
                                            @else
                                                <!-- Slot kosong -->
                                                <div
                                                    class="d-flex justify-content-between align-items-center gap-3 border border-dashed p-3">
                                                    <div>
                                                        <div class="fs-6 fw-bold text-muted">[Empty Slot]</div>
                                                        <a class="fw-semibold"
                                                            href="{{ route('employee.edit', $employee->npk) }}">
                                                            Go to employee edit page
                                                        </a>
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
                    </div>
                    <div class="row">
                        <!-- Card 2: Historical Human Assets Value -->
                        <div class="col-md-12">
                            <div class="card mb-5 mb-xl-10">
                                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                    data-bs-target="#kt_account_connected_accounts" aria-expanded="true"
                                    aria-controls="kt_account_connected_accounts">
                                    <div class="card-title m-0">
                                        <h3 class="fw-bolder m-0">Historical Human Assets Value</h3>
                                    </div>
                                </div>

                                <div id="kt_account_settings_signin_method" class="collapse show">
                                    <div class="card-body border-top p-10">
                                        <!-- Mengurangi padding agar card lebih kecil -->
                                        <div class="d-flex flex-wrap align-items-center">
                                            <div id="kt_signin_email">
                                                <div class="fs-6 fw-bold mb-1">Future Star [2]
                                                    <div class="text-muted fs-7">
                                                        2024
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="separator separator-dashed my-4"></div>

                                        <div class="d-flex flex-wrap align-items-center">
                                            <div id="kt_signin_password">
                                                <div class="fs-6 fw-bold mb-1">Potential Candidate [4]</div>
                                                <div class="text-muted fs-7">
                                                    2023
                                                </div>
                                            </div>
                                        </div>
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
                        <div class="card-header border-0 cursor-pointer d-flex justify-content-between align-items-center"
                            role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_connected_accounts"
                            aria-expanded="true" aria-controls="kt_account_connected_accounts">
                            <h3 class="fw-bolder m-0">Astra Training History</h3>
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
                                            </tr>
                                        </thead>
                                        <!--end::Thead-->

                                        <!--begin::Tbody-->
                                        <tbody class="fw-6 fw-semibold text-gray-600">
                                            @forelse ($astraTrainings as $astraTraining)
                                                <tr>
                                                    <td class="text-center">{{ $astraTraining->year }}</td>
                                                    <td class="text-center">{{ $astraTraining->program }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $astraTraining->ict_score }}/{{ $astraTraining->project_score }}/{{ $astraTraining->total_score }}
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

                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header border-0 cursor-pointer d-flex justify-content-between align-items-center"
                            role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_connected_accounts"
                            aria-expanded="true" aria-controls="kt_account_connected_accounts">
                            <h3 class="fw-bolder m-0">External Training History</h3>
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
                                                <th class="text-center">Training</th>
                                                <th class="text-center">Year</th>
                                                <th class="text-center">Vendor</th>
                                            </tr>
                                        </thead>
                                        <!--end::Thead-->

                                        <!--begin::Tbody-->
                                        <tbody class="fw-6 fw-semibold text-gray-600">
                                            @forelse ($externalTrainings as $externalTraining)
                                                <tr>
                                                    <td class="text-center">{{ $externalTraining->program }}</td>
                                                    <td class="text-center">{{ $externalTraining->year }}</td>
                                                    <td class="text-center">
                                                        {{ $externalTraining->vendor }}
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
                                        <div class="fs-6 fw-bold mb-1">
                                            Vision & Business Sense
                                        </div>
                                        <div class="fw-semibold text-gray-600">
                                            Konsisten memperlihatkan pemahaman akan fokus perusahaan, serta membuat beberapa
                                            pertimbangan strategis yang cukup berimbang antara pengamatan kondisi internal
                                            dan kondisi eksernal. Memerhatikan faktor penentu bisnis, dan memberi
                                            rekomendasi strategi relevan misalkan untuk memanfaatkan kekuatan merek dan
                                            produk.
                                        </div>
                                    </div>
                                </div>

                                <div class="separator separator-dashed my-4"></div>

                                <div class="d-flex flex-wrap align-items-center mb-4">
                                    <div id="kt_signin_password">
                                        <div class="fs-6 fw-bold mb-1">
                                            Leading & Motivating
                                        </div>
                                        <div class="fw-semibold text-gray-600">
                                            Terbuka menyampaikan target, memberikan arahan tugas, serta mendorong
                                            kolaborasi dalam tim untuk mencapai tujuan. Mengidentifikasi isu kinerja,
                                            berusaha menggali minat dan kebutuhan individu, serta menyiapkan dukungan
                                            pengembangan yang relevan.
                                        </div>
                                    </div>
                                </div>

                                <div class="separator separator-dashed my-4"></div>

                                <div class="d-flex flex-wrap align-items-center mb-4">
                                    <div id="kt_signin_password">
                                        <div class="fs-6 fw-bold mb-1">
                                            Drive & Courage
                                        </div>
                                        <div class="fw-semibold text-gray-600">
                                            Menunjukkan usaha mengarahkan keputusan dan tindakannya agar tetap selaras
                                            kepentingan dan tujuan perusahaan. Tidak ragu mengambil keputusan, dan di tugas
                                            sehari hari menunjukkannya pada berbagai situasi, baik saat menghadapi situasi
                                            baru, maupun ketika harus memutuskan pilihan yang sulit.
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
                                        <div class="fs-6 fw-bold mb-1">
                                            Planning & Driving Action
                                        </div>
                                        <div class="fw-semibold text-gray-600">
                                            belum merincikan rencana secara terstruktur dan sistimatis. belum menentukan
                                            sasaran
                                            yang jelas untuk setiap penugasan, serta belum menetapkan aktivitas untuk
                                            evaluasi atau
                                            pemantauan kerja demi memastikan implementasi tuntas.
                                        </div>
                                    </div>
                                </div>

                                <div class="separator separator-dashed my-4"></div>

                                <div class="d-flex flex-wrap align-items-center mb-4">
                                    <div id="kt_signin_password">
                                        <div class="fs-6 fw-bold mb-1">
                                            Analysis & Judgment
                                        </div>
                                        <div class="fw-semibold text-gray-600">
                                            belum konsisten mengembangkan beberapa alternatif solusi yang akan dapat
                                            membantunya menghasilkan solusi yang paling relevan guna menyelesaikan berbagai
                                            isu operasional ataupun strategis. Pendekatannya juga lebih mengandalkan pada
                                            keputusan manajemen saja.
                                        </div>
                                    </div>
                                </div>

                                <div class="separator separator-dashed my-4"></div>

                                <div class="d-flex flex-wrap align-items-center mb-4">
                                    <div id="kt_signin_password">
                                        <div class="fs-6 fw-bold mb-1">
                                            Customer Focus
                                        </div>
                                        <div class="fw-semibold text-gray-600">
                                            Belum memberikan ide ide baru yang kreatif demi meningkatkan standar dan
                                            kualitas layanan. Selain itu, ia juga perlu menerapkan metode/pendekatan yang
                                            terstruktur untuk menggali kebutuhan dan mendapatkan umpan balik dari pelanggan.
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
