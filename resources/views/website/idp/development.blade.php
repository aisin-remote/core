@extends('layouts.root.main')

@push('custom-css')
    <style>
        .legend-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .section-title {
            font-weight: 600;
            font-size: 1.1rem;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #0d6efd;
            font-size: 1.1rem;
        }

        .table-sm-custom th,
        .table-sm-custom td {
            padding: 0.5rem 0.75rem;
            vertical-align: top;
        }

        .badge-pill {
            border-radius: 999px;
            padding-inline: 0.7rem;
        }

        .text-muted-small {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .card-header-sticky {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #fff;
        }

        .invalid-feedback {
            display: none;
            font-size: 0.875em;
            color: #dc3545;
        }

        .is-invalid~.invalid-feedback {
            display: block;
        }

        @media (max-width: 768px) {
            .flex-wrap-sm {
                flex-wrap: wrap;
            }
        }

        /* ====== LOCKED ONE-YEAR TAB ====== */
        .nav-link-one-locked {
            opacity: 0.6;
        }

        .locked-wrapper {
            position: relative;
            min-height: 220px;
        }

        .locked-blur {
            filter: blur(2px);
            pointer-events: none;
            user-select: none;
            min-height: 220px;
        }

        .locked-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.85);
            z-index: 2;
            text-align: center;
        }
    </style>
@endpush

@section('title')
    {{ $title ?? 'IDP Development' }}
@endsection

@section('breadcrumbs')
    IDP / {{ $assessment->employee->name ?? '-' }} / Development
@endsection

@section('main')
    @php
        // Persiapan data IDP
        $idpRows = [];
        $alcByIdp = [];

        foreach ($assessment->details as $detail) {
            $alcName = $detail->alc->name ?? ($detail->alc->title ?? 'ALC ' . $detail->alc_id);
            $alcId = $detail->alc_id;

            $detailIdps = $idps[$alcId] ?? collect();

            if ($detailIdps->isEmpty()) {
                continue;
            }

            $latestIdp = $detailIdps->sortByDesc('updated_at')->first();

            if ($latestIdp) {
                $idpRows[] = [
                    'alc_name' => $alcName,
                    'alc_id'   => $alcId,
                    'idp'      => $latestIdp,
                ];
            }

            foreach ($detailIdps as $idp) {
                $alcByIdp[$idp->id] = $alcName;
            }
        }

        $midDrafts    = $midDevs->flatten()->where('status', 'draft');
        $hasMidDraft  = $midDrafts->isNotEmpty();
        $oneDrafts    = $oneDevs->flatten()->where('status', 'draft');
        $hasOneDraft  = $oneDrafts->isNotEmpty();

        $midDraftIdpIds = $midDrafts->pluck('idp_id')->toArray();
        $oneDraftIdpIds = $oneDrafts->pluck('idp_id')->toArray();

        $hasMidSubmitted = $midDevs->flatten()->where('status', 'submitted')->isNotEmpty();
        $canAccessOne    = $hasMidSubmitted && !$hasMidDraft;
    @endphp

    {{-- Meta CSRF untuk AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap-sm gap-3">
            <h3 class="mb-0">{{ $title ?? 'IDP Development' }}</h3>
            <a href="{{ route('idp.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Back to IDP List
            </a>
        </div>

        {{-- CARD: Employee Info --}}
        <div class="card mb-5">
            <div class="card-body d-flex flex-wrap gap-4 align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-50px symbol-circle bg-light-primary text-primary fw-bold">
                        {{ Str::substr($assessment->employee->name ?? '?', 0, 1) }}
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ $assessment->employee->name ?? '-' }}</div>
                        <div class="text-muted">
                            {{ $assessment->employee->position ?? '-' }}<br>
                            {{ $assessment->employee->department_name ?? ($assessment->employee->department ?? '-') }}
                        </div>
                    </div>
                </div>
                <div class="border-start ps-4 ms-2">
                    <div><strong>Assessment Purpose:</strong> {{ $assessment->purpose ?? '-' }}</div>
                    <div><strong>Assessor:</strong> {{ $assessment->lembaga ?? '-' }}</div>
                    <div><strong>Date:</strong>
                        {{ optional($assessment->created_at)->timezone('Asia/Jakarta')->format('d M Y') ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD: IDP List (Read Only) --}}
        <div class="card mb-5">
            <div class="card-header card-header-sticky">
                <h4 class="card-title mb-0">Individual Development Program (IDP)</h4>
            </div>
            <div class="card-body">
                @if (count($idpRows))
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm-custom">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 20%;">ALC</th>
                                    <th style="width: 15%;">Category</th>
                                    <th>Development Program</th>
                                    <th style="width: 20%;">Development Target</th>
                                    <th style="width: 12%;">Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($idpRows as $idx => $row)
                                    @php $idp = $row['idp']; @endphp
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $row['alc_name'] }}</td>
                                        <td>{{ $idp->category ?? '-' }}</td>
                                        <td>{{ $idp->development_program ?? '-' }}</td>
                                        <td>{{ $idp->development_target ?? '-' }}</td>
                                        <td>
                                            {{ !empty($idp->date) ? \Illuminate\Support\Carbon::parse($idp->date)->timezone('Asia/Jakarta')->format('d-m-Y') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada IDP.</p>
                @endif
            </div>
        </div>

        {{-- CARD: Development Progress --}}
        <div class="card">
            <div class="card-header card-header-sticky">
                <ul class="nav nav-tabs card-header-tabs" role="tablist" style="cursor:pointer">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-mid" role="tab">
                            Mid-Year Review
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $canAccessOne ? '' : 'nav-link-one-locked' }}"
                           data-bs-toggle="tab"
                           href="#tab-one"
                           role="tab">
                            One-Year Review
                            @unless($canAccessOne)
                                <i class="bi bi-lock-fill ms-1 small"></i>
                            @endunless
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    {{-- ================= TAB: Mid-Year Review ================= --}}
                    <div class="tab-pane fade show active" id="tab-mid" role="tabpanel">
                        @if (!count($idpRows))
                            <p class="text-muted">Tidak ada IDP.</p>
                        @else
                            <form id="form-mid-year" method="POST"
                                  action="{{ route('idp.storeMidYear', ['employee_id' => $assessment->employee_id]) }}">
                                @csrf
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-hover table-sm-custom align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 20%;">ALC</th>
                                                <th style="width: 25%;">Development Program</th>
                                                <th style="width: 25%;">Achievement (Mid-Year)</th>
                                                <th style="width: 25%;">Next Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($idpRows as $idx => $row)
                                                @php
                                                    $idp   = $row['idp'];
                                                    $idpId = $idp->id;

                                                    $midList = $midDevs[$idpId] ?? collect();
                                                    $lastMid = $midList->first();
                                                @endphp
                                                <tr>
                                                    <td>{{ $idx + 1 }}</td>
                                                    <td>{{ $row['alc_name'] }}</td>
                                                    <td>
                                                        {{ $idp->development_program ?? '-' }}
                                                        <input type="hidden" name="idp_id[]" value="{{ $idpId }}">
                                                        <input type="hidden" name="development_program[]"
                                                               value="{{ $idp->development_program }}">
                                                    </td>
                                                    <td>
                                                        <textarea name="development_achievement[]" data-index="{{ $idx }}" class="form-control form-control-sm"
                                                                  rows="3" placeholder="Tuliskan capaian pengembangan">{{ old("development_achievement.$idx", $lastMid->development_achievement ?? '') }}</textarea>
                                                        <div class="invalid-feedback"
                                                             id="error-development_achievement-{{ $idx }}"></div>
                                                    </td>
                                                    <td>
                                                        <textarea name="next_action[]" data-index="{{ $idx }}" class="form-control form-control-sm" rows="3"
                                                                  placeholder="Next action">{{ old("next_action.$idx", $lastMid->next_action ?? '') }}</textarea>
                                                        <div class="invalid-feedback"
                                                             id="error-next_action-{{ $idx }}"></div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Tombol Save/Update Draft --}}
                                <div class="d-flex justify-content-end gap-2 mb-4">
                                    <button type="submit" class="btn btn-primary" id="btn-save-mid">
                                        <i class="fas fa-save me-2"></i>
                                        {{ $hasMidDraft ? 'Update Draft' : 'Save Draft' }}
                                    </button>
                                </div>
                            </form>
                        @endif

                        {{-- History Table Mid Year --}}
                        @if ($midDevs->isNotEmpty())
                            <hr class="my-4">
                            <div class="section-title mb-0">
                                <i class="bi bi-bar-chart-line-fill"></i> Mid-Year History
                            </div>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered table-hover table-sm-custom">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Program</th>
                                            <th>ALC</th>
                                            <th>Achievement</th>
                                            <th>Status</th>
                                            <th style="width: 120px;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($midDevs as $idpId => $devList)
                                            @foreach ($devList as $dev)
                                                <tr>
                                                    <td>{{ $dev->development_program ?? '-' }}</td>
                                                    <td>{{ $alcByIdp[$dev->idp_id] ?? '-' }}</td>
                                                    <td>{{ $dev->development_achievement ?? '-' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-{{ $dev->status == 'draft' ? 'warning' : ($dev->status == 'submitted' ? 'info' : 'success') }}">
                                                            {{ ucfirst($dev->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ optional($dev->created_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-end align-items-center">
                                    @if ($hasMidDraft)
                                        <button type="button" class="btn btn-success" id="btn-submit-mid">
                                            <i class="fas fa-paper-plane me-2"></i> Submit Draft
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ================= TAB: One-Year Review ================= --}}
                    <div class="tab-pane fade" id="tab-one" role="tabpanel">
                        @if (!$canAccessOne)
                            {{-- One-Year locked sampai Mid-Year submitted & tidak ada draft --}}
                            <div class="locked-wrapper">
                                <div class="locked-blur">
                                    {{-- hanya placeholder tinggi supaya ada area yang di-blur --}}
                                    <div style="height:220px;"></div>
                                </div>
                                <div class="locked-overlay">
                                    <i class="bi bi-lock-fill fs-1 mb-3 text-primary"></i>
                                    <h5 class="fw-bold mb-2">One-Year Review Locked</h5>
                                    <p class="text-muted mb-0">
                                        Silakan lengkapi dan <strong>submit Mid-Year Development</strong> terlebih dahulu
                                        sebelum mengisi <strong>One-Year Development</strong>.
                                    </p>
                                </div>
                            </div>
                        @else
                            {{-- ====== One-Year form & history hanya muncul kalau sudah boleh diakses ====== --}}
                            @if (!count($idpRows))
                                <p class="text-muted">Tidak ada IDP.</p>
                            @else
                                <form id="form-one-year" method="POST"
                                      action="{{ route('idp.storeOneYear', ['employee_id' => $assessment->employee_id]) }}">
                                    @csrf
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-hover table-sm-custom align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th style="width: 20%;">ALC</th>
                                                    <th style="width: 30%;">Development Program</th>
                                                    <th style="width: 45%;">Evaluation Result (One-Year)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($idpRows as $idx => $row)
                                                    @php
                                                        $idp   = $row['idp'];
                                                        $idpId = $idp->id;

                                                        $oneList = $oneDevs[$idpId] ?? collect();
                                                        $lastOne = $oneList->first();
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $idx + 1 }}</td>
                                                        <td>{{ $row['alc_name'] }}</td>
                                                        <td>
                                                            {{ $idp->development_program ?? '-' }}
                                                            <input type="hidden" name="idp_id[]" value="{{ $idpId }}">
                                                            <input type="hidden" name="development_program[]"
                                                                   value="{{ $idp->development_program }}">
                                                        </td>
                                                        <td>
                                                            <textarea name="evaluation_result[]" data-index="{{ $idx }}" class="form-control form-control-sm"
                                                                      rows="3" placeholder="Tuliskan hasil evaluasi">{{ old("evaluation_result.$idx", $lastOne->evaluation_result ?? '') }}</textarea>
                                                            <div class="invalid-feedback"
                                                                 id="error-evaluation_result-{{ $idx }}"></div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Tombol Save/Update Draft --}}
                                    <div class="d-flex justify-content-end gap-2 mb-4">
                                        <button type="submit" class="btn btn-primary" id="btn-save-one">
                                            <i class="fas fa-save me-2"></i>
                                            {{ $hasOneDraft ? 'Update Draft' : 'Save Draft' }}
                                        </button>
                                    </div>
                                </form>
                            @endif

                            {{-- History Table One Year --}}
                            @if ($oneDevs->isNotEmpty())
                                <hr class="my-4">
                                <div class="section-title mb-0">
                                    <i class="bi bi-calendar-check-fill"></i> One-Year History
                                </div>

                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-bordered table-hover table-sm-custom">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Program</th>
                                                <th>ALC</th>
                                                <th>Evaluation Result</th>
                                                <th>Status</th>
                                                <th style="width: 120px;">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($oneDevs as $idpId => $devList)
                                                @foreach ($devList as $dev)
                                                    <tr>
                                                        <td>{{ $dev->development_program ?? '-' }}</td>
                                                        <td>{{ $alcByIdp[$dev->idp_id] ?? '-' }}</td>
                                                        <td>{{ $dev->evaluation_result ?? '-' }}</td>
                                                        <td>
                                                            <span
                                                                class="badge badge-{{ $dev->status == 'draft' ? 'warning' : ($dev->status == 'submitted' ? 'info' : 'success') }}">
                                                                {{ ucfirst($dev->status) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ optional($dev->created_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i') }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end align-items-center">
                                        @if ($hasOneDraft)
                                            <button type="button" class="btn btn-success" id="btn-submit-one">
                                                <i class="fas fa-paper-plane me-2"></i>
                                                Submit {{ $oneDrafts->count() }} Draft
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // Array IDP ID Draft yang sudah diambil di PHP (digunakan untuk submit)
            const midDraftIdpIds = @json($midDraftIdpIds);
            const oneDraftIdpIds = @json($oneDraftIdpIds);

            // =========================================================
            // HANDLER 1: SAVE / UPDATE DRAFT (POST ke storeMidYear/storeOneYear)
            // =========================================================
            function handleFormSave(formId, btnId, successTitle) {
                $(formId).on('submit', function(e) {
                    e.preventDefault();

                    let form = $(this);
                    let btn = $(btnId);
                    let originalBtnText = btn.html();

                    // Reset Error States
                    form.find('.form-control').removeClass('is-invalid');
                    form.find('.invalid-feedback').text('');

                    // Button Loading State
                    btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
                    );

                    let formData = new FormData(this);

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Swal.fire({
                                title: successTitle || "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(originalBtnText);

                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, messages) {
                                    let parts = key.split('.');
                                    let fieldName = parts[0];
                                    let index = parts[1];

                                    let inputField = form.find(
                                        `[name="${fieldName}[]"][data-index="${index}"]`
                                    );

                                    if (inputField.length > 0) {
                                        inputField.addClass('is-invalid');
                                        $(`#error-${fieldName}-${index}`).text(messages[0]);
                                    }
                                });

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validasi Gagal',
                                    text: 'Mohon periksa kembali isian form Anda.'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan pada server. Silakan coba lagi.'
                                });
                            }
                        }
                    });
                });
            }

            // =========================================================
            // HANDLER 2: SUBMIT FINAL (POST ke submitMidYear/submitOneYear)
            // =========================================================
            function handleSubmission(btnId, submitUrl, draftIdpIds) {
                $(btnId).on('click', function() {
                    let btn = $(this);
                    let originalBtnText = btn.html();

                    let idpIds = draftIdpIds;

                    if (idpIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Tidak ada Draft baru untuk disubmit.'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Konfirmasi Submit?',
                        text: `Anda akan mengirim ${idpIds.length} item Draft. Pastikan semua data sudah benar!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Submit!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            btn.prop('disabled', true).html(
                                '<span class="spinner-border spinner-border-sm"></span> Submitting...'
                            );

                            $.ajax({
                                url: submitUrl,
                                method: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    idp_id: idpIds
                                },
                                success: function(response) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.message,
                                        icon: 'success',
                                    }).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    btn.prop('disabled', false).html(originalBtnText);
                                    let msg = xhr.responseJSON.message || 'Terjadi kesalahan saat submit.';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: msg
                                    });
                                }
                            });
                        }
                    });
                });
            }

            // --- INISIALISASI ---

            // Mid-Year
            handleFormSave('#form-mid-year', '#btn-save-mid', 'Mid-Year Development Saved!');
            if (midDraftIdpIds.length > 0) {
                handleSubmission(
                    '#btn-submit-mid',
                    '{{ route('development.submitMidYear', ['employee_id' => $assessment->employee_id]) }}',
                    midDraftIdpIds
                );
            }

            // One-Year
            // (Submission One-Year hanya di-init kalau memang ada draft; kalau One-Year masih locked, oneDraftIdpIds akan kosong)
            handleFormSave('#form-one-year', '#btn-save-one', 'One-Year Development Saved!');
            if (oneDraftIdpIds.length > 0) {
                handleSubmission(
                    '#btn-submit-one',
                    '{{ route('development.submitOneYear', ['employee_id' => $assessment->employee_id]) }}',
                    oneDraftIdpIds
                );
            }
        });
    </script>
@endpush
