@extends('layouts.root.main')

@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="container mt-4">
            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_profile_details" aria-expanded="true"
                    aria-controls="kt_account_profile_details">
                    <!--begin::Card title-->
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Profile Details</h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->

                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                    <!--begin::Form-->
                    <form id="kt_account_profile_details_form" class="form fv-plugins-bootstrap5 fv-plugins-framework"
                        novalidate="novalidate">
                        <!--begin::Card body-->
                        <div class="card-body border-top p-9">
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Profile Picture</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Image input-->
                                    <div class="image-input image-input-outline" data-kt-image-input="true"
                                        style="background-image: url('/metronic8/demo1/assets/media/svg/avatars/blank.svg')">
                                        <!--begin::Preview existing avatar-->
                                        <div class="image-input-wrapper w-125px h-125px"
                                            style="background-image: url('{{ Storage::url($employee->photo) }}')">
                                        </div>
                                        <!--end::Preview existing avatar-->

                                        <!--begin::Label-->
                                        <label
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                            aria-label="Change avatar" data-bs-original-title="Change avatar"
                                            data-kt-initialized="1">
                                            <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span
                                                    class="path2"></span></i>
                                            <!--begin::Inputs-->
                                            <input type="file" name="avatar" accept=".png, .jpg, .jpeg">
                                            <input type="hidden" name="avatar_remove">
                                            <!--end::Inputs-->
                                        </label>
                                        <!--end::Label-->

                                        <!--begin::Cancel-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                            aria-label="Cancel avatar" data-bs-original-title="Cancel avatar"
                                            data-kt-initialized="1">
                                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span
                                                    class="path2"></span></i> </span>
                                        <!--end::Cancel-->

                                        <!--begin::Remove-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                            aria-label="Remove avatar" data-bs-original-title="Remove avatar"
                                            data-kt-initialized="1">
                                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span
                                                    class="path2"></span></i> </span>
                                        <!--end::Remove-->
                                    </div>
                                    <!--end::Image input-->

                                    <!--begin::Hint-->
                                    <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                                    <!--end::Hint-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Full Name</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row fv-plugins-icon-container">
                                            <input type="text" name="fname"
                                                class="form-control form-control-lg form-control-solid mb-3 mb-lg-0"
                                                placeholder="First name" value="{{ $employee->name }}" readonly>
                                            <div
                                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                            </div>
                                        </div>
                                        <!--end::Col-->

                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Gender</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row fv-plugins-icon-container">
                                            <input type="text" name="fname"
                                                class="form-control form-control-lg form-control-solid mb-3 mb-lg-0"
                                                placeholder="First name" value="{{ $employee->gender }}" readonly>
                                            <div
                                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                            </div>
                                        </div>
                                        <!--end::Col-->

                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Company</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                    <input type="text" name="company"
                                        class="form-control form-control-lg form-control-solid" placeholder="Company name"
                                        value="{{ $employee->company_name }}" readonly>
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    </div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Join Date</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                    <input type="text" name="company"
                                        class="form-control form-control-lg form-control-solid" placeholder="Company name"
                                        value="{{ $employee->aisin_entry_date }}" readonly>
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    </div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Working Period</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                    <input type="text" name="company"
                                        class="form-control form-control-lg form-control-solid" placeholder="Company name"
                                        value="{{ $employee->working_period }} Years" readonly>
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    </div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">
                                    <span>Department</span>
                                </label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                    <input type="text" name="phone"
                                        class="form-control form-control-lg form-control-solid"
                                        value="{{ $employee->departments->first()->name }}">
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    </div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Postion</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="website"
                                        class="form-control form-control-lg form-control-solid"
                                        placeholder="Company website" value="{{ $employee->position }}">
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Grade</label>
                                <!--end::Label-->

                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="website"
                                        class="form-control form-control-lg form-control-solid"
                                        placeholder="Company website" value="{{ $employee->grade }}">
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                        </div>
                        <!--end::Card body-->

                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->
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
                                        <td colspan="6" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
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
                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                            data-bs-target="#kt_account_signin_method">
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Educational Background</h3>
                            </div>
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
                                        </div>
                                        @unless ($loop->last)
                                            <div class="separator separator-dashed my-3"></div>
                                        @endunless
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
                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                            data-bs-target="#kt_account_signin_method">
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Working Experience</h3>
                            </div>
                        </div>

                        <div id="kt_activity_year" class="card-body ps-5 tab-pane fade show active border-top"
                            role="tabpanel" aria-labelledby="kt_activity_year_tab">
                            <div class="timeline timeline-border-dashed">
                                @if ($workExperiences->isEmpty())
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                                        <p class="fs-6">Belum ada pengalaman kerja yang tersedia.</p>
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
                                                    <button class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal"
                                                        data-bs-target="#experienceModal{{ $loop->index }}">
                                                        Lihat Detail
                                                    </button>
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
                                                        <p><strong>Perusahaan:</strong> {{ $experience->company }}</p>
                                                        <p><strong>Periode:</strong>
                                                            {{ \Illuminate\Support\Carbon::parse($experience->start_date)->format('d M Y') }}
                                                            -
                                                            {{ $experience->end_date ? \Illuminate\Support\Carbon::parse($experience->end_date)->format('d M Y') : 'Present' }}
                                                        </p>
                                                        <p><strong>Deskripsi Pekerjaan:</strong></p>
                                                        <p>{{ $experience->description }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Tutup</button>
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

                <!-- Historical Performance Appraisal -->
                <div class="col-md-6">
                    <div class="card mb-5">
                        <div class="card-header border-0 cursor-pointer d-flex justify-content-between align-items-center"
                            role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_connected_accounts"
                            aria-expanded="true" aria-controls="kt_account_connected_accounts">
                            <h3 class="fw-bold m-0">Historical Performance Appraisal</h3>
                        </div>

                        <div id="kt_account_settings_signin_method" class="collapse show">
                            <div class="card-body border-top p-10">
                                <div class="d-flex flex-column">
                                    @forelse ($performanceAppraisals as $appraisal)
                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fs-6 fw-bold">Score - {{ $appraisal->score }} </div>
                                                <div class="text-muted fs-7">
                                                    {{ \Illuminate\Support\Carbon::parse($appraisal->date)->format('d M Y') }}
                                                </div>
                                            </div>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#detailModal{{ $appraisal->id }}">
                                                Lihat Detail
                                            </button>
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
                                    <p><strong>Catatan:</strong> {{ $appraisal->notes }}</p>
                                    <p><strong>Tahun:</strong> {{ $appraisal->date }}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="row">
                    <!-- Strength & Weakness -->
                    <div class="col-md-6">
                        <div class="card mb-5">
                            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                                data-bs-target="#kt_account_signin_method">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Strength & Weakness</h3>
                                </div>
                            </div>

                            <div class="card-body">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable">
                                    <thead>
                                        <tr class="text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th>Strength</th>
                                            <th>Weakness</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <!-- Data bisa ditambahkan di sini -->
                                        </tr>
                                    </tbody>
                                </table>
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
                                <div class="card-body border-top p-4">
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
