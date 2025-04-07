@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
@endsection

@section('main')
    <div id="kt_app_content" class="app-content  flex-column-fluid ">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container  container-fluid ">

            <div class="card mb-5 mb-xl-10">
                <div class="card-body pt-9 pb-0">
                    <!--begin::Details-->
                    <div class="d-flex flex-wrap flex-sm-nowrap">
                        <!--begin: Pic-->
                        <div class="me-7 mb-4">
                            <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                <img src="{{ asset('storage/' . $hav->employee->photo) }}" alt="image">
                            </div>
                        </div>
                        <!--end::Pic-->

                        <!--begin::Info-->
                        <div class="flex-grow-1">
                            <!--begin::Title-->
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                <!--begin::User-->
                                <div class="d-flex flex-column">
                                    <!--begin::Name-->
                                    <div class="d-flex align-items-center mb-2">
                                        <a href="#"
                                            class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{ $hav->employee->name }}</a>
                                        <a href="#"><i class="ki-duotone ki-verify fs-1 text-primary"><span
                                                    class="path1"></span><span class="path2"></span></i></a>
                                    </div>
                                    <!--end::Name-->
                                </div>
                                <!--end::User-->

                                <!--begin::Actions-->
                                <div class="d-flex my-4">
                                    <a href="#" class="btn btn-sm btn-danger me-2" id="kt_user_follow_button">
                                        <i class="ki-duotone ki-check fs-3 d-none"></i>
                                        <!--begin::Indicator label-->
                                        <span class="indicator-label">
                                            Cancel</span>
                                        <!--end::Indicator label-->
                                    </a>
                                    <!--end::Menu-->
                                </div>
                                <!--end::Actions-->
                            </div>
                            <!--end::Title-->

                            <!--begin::Stats-->
                            <div class="d-flex flex-wrap flex-stack">
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-column flex-grow-1 pe-8">
                                    <!--begin::Stats-->
                                    <div class="d-flex flex-wrap">
                                        <!--begin::Stat-->
                                        <div
                                            class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <!--begin::Number-->
                                            <div class="d-flex align-items-center">
                                                <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span
                                                        class="path1"></span><span class="path2"></span></i>
                                                <div class="fs-2 fw-bold counted" data-kt-countup="true"
                                                    data-kt-countup-value="4500" data-kt-countup-prefix="$"
                                                    data-kt-initialized="1">{{ $hav->employee->company_name }}</div>
                                            </div>
                                            <!--end::Number-->

                                            <!--begin::Label-->
                                            <div class="fw-semibold fs-6 text-gray-500">Company</div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Stat-->
                                        <!--begin::Stat-->
                                        <div
                                            class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <!--begin::Number-->
                                            <div class="d-flex align-items-center">
                                                <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span
                                                        class="path1"></span><span class="path2"></span></i>
                                                <div class="fs-2 fw-bold counted" data-kt-countup="true"
                                                    data-kt-countup-value="4500" data-kt-countup-prefix="$"
                                                    data-kt-initialized="1">{{ $hav->employee->position }}</div>
                                            </div>
                                            <!--end::Number-->

                                            <!--begin::Label-->
                                            <div class="fw-semibold fs-6 text-gray-500">Position</div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Stat-->
                                        <!--begin::Stat-->
                                        <div
                                            class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <!--begin::Number-->
                                            <div class="d-flex align-items-center">
                                                <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span
                                                        class="path1"></span><span class="path2"></span></i>
                                                <div class="fs-2 fw-bold counted" data-kt-countup="true"
                                                    data-kt-countup-value="4500" data-kt-countup-prefix="$"
                                                    data-kt-initialized="1">{{ $hav->employee->grade }}</div>
                                            </div>
                                            <!--end::Number-->

                                            <!--begin::Label-->
                                            <div class="fw-semibold fs-6 text-gray-500">Grade</div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Stat-->

                                    </div>
                                    <!--end::Stats-->
                                </div>
                                <!--end::Wrapper-->

                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Info-->
                    </div>
                </div>
            </div>


            <!--begin::Timeline-->
            <div class="card">
                <!--begin::Card head-->
                <div class="card-header card-header-stretch">
                    <!--begin::Title-->
                    <div class="card-title d-flex align-items-center">
                        <h3 class="fw-bold m-0 text-gray-800">ENTRY INDIVIDUAL SCORE</h3>
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Card head-->

                <!--begin::Card body-->
                <div class="card-body">
                    <!--begin::Tab Content-->
                    <div class="tab-content">
                        <!--begin::Tab panel-->
                        <div id="kt_activity_today" class="card-body p-0 tab-pane fade show active" role="tabpanel"
                            aria-labelledby="kt_activity_today_tab">
                            <!--begin::Timeline-->
                            <div class="timeline timeline-border-dashed">
                                @foreach ($hav->details as $item)
                                    <div class="timeline-item">
                                        <!--begin::Timeline line-->
                                        <div class="timeline-line"></div>
                                        <!--end::Timeline line-->

                                        <!--begin::Timeline icon-->
                                        <div class="timeline-icon">
                                            <i class="ki-duotone ki-abstract-26 fs-2 text-gray-500"><span
                                                    class="path1"></span><span class="path2"></span><span
                                                    class="path3"></span></i>
                                        </div>
                                        <!--end::Timeline icon-->

                                        <!--begin::Timeline content-->
                                        <div class="timeline-content mb-10 mt-n1">
                                            <!--begin::Timeline heading-->
                                            <div class="pe-3 mb-5">
                                                <!--begin::Title-->
                                                <div class="fs-5 fw-semibold mb-2"> {{ $item->alc->name }} </div>

                                                <div class="fs-6 text-gray-700">Score Rata-rata: <h2
                                                        id="hav-detail-score-{{ $item->id }}">
                                                        {{ $item->score ?? '0.0' }}</h2>
                                                </div>
                                                <!--end::Title-->

                                                <!--begin::Description-->
                                                <div class="d-flex align-items-center mt-1 fs-6">

                                                </div>
                                                <!--end::Description-->
                                            </div>
                                            <!--end::Timeline heading-->

                                            {{-- <!--begin::Timeline details-->
                                            <div class="overflow-auto pb-5">

                                                @foreach ($item->keyBehaviors as $keyBehavior)
                                                    <div
                                                        class="d-flex flex-wrap align-items-center border border-dashed border-gray-300 rounded p-3 mb-3">
                                                        <!--begin::Title-->
                                                        <a class="fs-5 text-gray-900 text-hover-primary fw-semibold"
                                                            style="flex: 80%; min-width: 70%; word-wrap: break-word; white-space: normal;">
                                                            {{ $keyBehavior->keyBehavior->description }}
                                                        </a>
                                                        <!--end::Title-->

                                                        <!--begin::Progress-->
                                                        <div class="px-2" style="flex: 10%; min-width: 15%;">
                                                            <select class="form-control form-control-sm rating-select"
                                                                value="50" style="width: 50px;" name="rating"
                                                                id="rating"
                                                                data-keybehavior-id="{{ $keyBehavior->keyBehavior->id }}"
                                                                data-havdetail-id="{{ $item->id }}">
                                                                <option value="0"
                                                                    {{ $keyBehavior->score == 0 ? 'selected' : '' }}>-
                                                                </option>
                                                                <option value="1"
                                                                    {{ $keyBehavior->score == 1 ? 'selected' : '' }}>1
                                                                </option>
                                                                <option value="1.5"
                                                                    {{ $keyBehavior->score == 1.5 ? 'selected' : '' }}>1.5
                                                                </option>
                                                                <option value="2"
                                                                    {{ $keyBehavior->score == 2 ? 'selected' : '' }}>2
                                                                </option>
                                                                <option value="2.5"
                                                                    {{ $keyBehavior->score == 2.5 ? 'selected' : '' }}>2.5
                                                                </option>
                                                                <option value="3"
                                                                    {{ $keyBehavior->score == 3 ? 'selected' : '' }}>3
                                                                </option>
                                                                <option value="3.5"
                                                                    {{ $keyBehavior->score == 3.5 ? 'selected' : '' }}>3.5
                                                                </option>
                                                                <option value="4"
                                                                    {{ $keyBehavior->score == 4 ? 'selected' : '' }}>4
                                                                </option>
                                                                <option value="4.5"
                                                                    {{ $keyBehavior->score == 4.5 ? 'selected' : '' }}>4.5
                                                                </option>
                                                                <option value="5"
                                                                    {{ $keyBehavior->score == 5 ? 'selected' : '' }}>5
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <!--end::Progress-->

                                                    </div>
                                                @endforeach
                                            </div>
                                            <!--end::Timeline details--> --}}
                                        </div>
                                        <!--end::Timeline content-->
                                    </div>
                                @endforeach


                            </div>
                        </div>
                        <!--end::Tab panel-->
                    </div>
                    <!--end::Tab Content-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Timeline-->




        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Event listener untuk setiap dropdown rating
            $(document).on('change', '.rating-select', function() {
                let rating = $(this).val(); // Ambil nilai rating yang dipilih
                let keyBehaviorId = $(this).data('keybehavior-id'); // Ambil ID key_behavior
                let havDetailId = $(this).data('havdetail-id'); // Ambil ID hav_detail

                // Kirim AJAX request ke server
                $.ajax({
                    url: "{{ route('update.rating') }}", // Pastikan ada named route ini
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}", // Laravel CSRF Token
                        key_behavior_id: keyBehaviorId,
                        hav_detail_id: havDetailId,
                        rating: rating
                    },
                    success: function(response) {

                        if (response.success) {
                            let newAvg = parseFloat(response.new_average) || 0;
                            // ✅ SweetAlert Toast Notification
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Rating berhasil diperbarui!',
                                showConfirmButton: false,
                                timer: 2000
                            });

                            $('#hav-detail-score-' + havDetailId).text(newAvg.toFixed(1));
                        } else {
                            // ❌ SweetAlert Toast Notification (Error)
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Gagal memperbarui rating!',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    error: function(xhr) {
                        // ❌ SweetAlert Error Toast
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Terjadi kesalahan!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        console.error("Error: " + xhr.responseText);
                    }
                });
            });

            $(".rating-select").on("change", function() {
                let keyBehaviorId = $(this).data("keybehavior-id");
                let havDetailId = $(this).data("havdetail-id");
                let score = $(this).val();

                $.ajax({
                    url: "/update-score",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        key_behavior_id: keyBehaviorId,
                        hav_detail_id: havDetailId,
                        score: score
                    },
                    success: function(response) {
                        if (response.success) {
                            iziToast.success({
                                title: "Success",
                                message: "Score updated successfully"
                            });

                            // Update skor rata-rata di UI
                            $("#hav-detail-score-" + havDetailId).text(response.new_avg_score);
                        } else {
                            iziToast.error({
                                title: "Error",
                                message: "Failed to update score"
                            });
                        }
                    },
                    error: function() {
                        iziToast.error({
                            title: "Error",
                            message: "Something went wrong!"
                        });
                    }
                });
            });
        });
    </script>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
