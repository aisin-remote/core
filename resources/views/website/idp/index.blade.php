@extends('layouts.root.main')

@push('custom-css')
    <style>
        .legend-circle {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
        }
    </style>
@endpush

@section('title')
    {{ $title ?? 'IDP' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'IDP' }}
@endsection

<style>
    .table-responsive {
        overflow-x: auto;
        white-space: nowrap;
    }

    /* Make the Employee Name column sticky */
    .sticky-col {
        position: sticky;
        left: 0;
        background: white;
        z-index: 2;
        box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
    }

    .score {
        width: 55px;
    }
</style>
<style>
    /* Status Chip */
    .status-chip {
        --bg: #eef2ff;
        --fg: #312e81;
        --bd: #c7d2fe;
        --dot: #6366f1;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem .9rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: .9rem;
        line-height: 1;
        border: 1px solid var(--bd);
        background: var(--bg);
        color: var(--fg);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        max-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .status-chip i {
        font-size: 1rem;
        opacity: .95
    }

    /* Dot/pulse di kiri */
    .status-chip::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--dot);
        box-shadow: 0 0 0 4px color-mix(in srgb, var(--dot) 20%, transparent);
    }

    /* Variasi warna per status */
    .status-chip[data-status="approved"] {
        --bg: #ecfdf5;
        --fg: #065f46;
        --bd: #a7f3d0;
        --dot: #10b981;
    }

    .status-chip[data-status="checked"] {
        --bg: #fffbeb;
        --fg: #92400e;
        --bd: #fde68a;
        --dot: #f59e0b;
    }

    .status-chip[data-status="waiting"] {
        --bg: #fffbeb;
        --fg: #92400e;
        --bd: #fde68a;
        --dot: #f59e0b;
    }

    .status-chip[data-status="draft"] {
        --bg: #f8fafc;
        --fg: #334155;
        --bd: #e2e8f0;
        --dot: #94a3b8;
    }

    .status-chip[data-status="revise"] {
        --bg: #fef2f2;
        --fg: #7f1d1d;
        --bd: #fecaca;
        --dot: #ef4444;
    }

    .status-chip[data-status="not_created"],
    .status-chip[data-status="unknown"] {
        --bg: #f4f4f5;
        --fg: #27272a;
        --bd: #e4e4e7;
        --dot: #a1a1aa;
    }

    /* Animasi pulse utk Waiting */
    @keyframes pulseDot {
        0% {
            box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 30%, transparent);
        }

        70% {
            box-shadow: 0 0 0 8px color-mix(in srgb, var(--dot) 0%, transparent);
        }

        100% {
            box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 0%, transparent);
        }
    }

    .status-chip[data-status="waiting"]::before {
        animation: pulseDot 1.25s infinite;
    }

    /* Small screens: jangan terlalu lebar */
    @media (max-width: 768px) {
        .status-chip {
            max-width: 210px;
        }
    }

    .modal-header {
        position: sticky;
        top: 0;
        background: #ffffff;
        z-index: 1055;
    }
</style>


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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Employee List</h3>
                <div class="d-flex align-items-center">
                    <form method="GET" action="{{ url()->current() }}" class="d-flex mb-3">
                        <input type="text" id="searchInputEmployee" name="search" class="form-control me-2"
                            placeholder="Search..." style="width: 250px;" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary me-3" id="searchButton">
                            Search
                        </button>
                    </form>


                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8"
                    role="tablist" style="cursor:pointer">
                    @php
                        $jobPositions = [
                            'Show All',
                            'Direktur',
                            'GM',
                            'Manager',
                            'Coordinator',
                            'Section Head',
                            'Supervisor',
                            'Leader',
                            'JP',
                            'Operator',
                        ];
                    @endphp

                    @if (auth()->user()->role == 'HRD')
                        @foreach ($jobPositions as $index => $position)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4 {{ $index === 0 ? 'active' : '' }}"
                                    data-bs-toggle="tab" data-bs-target="#{{ Str::slug($position) }}" role="tab"
                                    aria-controls="{{ Str::slug($position) }}">
                                    {{ $position }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>

                <div class="tab-content mt-3" id="employeeTabsContent">
                    @foreach ($jobPositions as $index => $position)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ Str::slug($position) }}"
                            role="tabpanel" aria-labelledby="{{ Str::slug($position) }}-tab">

                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 gs-0">
                                            <th style="width: 20px">No</th>
                                            <th class="text-center" style="width: 150px">Employee Name</th>
                                            @foreach ($alcs as $id => $title)
                                                <th class="text-center" style="width: 100px">{{ $title }}</th>
                                            @endforeach
                                            <th class="text-center" style="width: 150px">Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                            $filteredEmployees = $position = 'Show All'
                                                ? $processedData
                                                : $processedData->filter(
                                                    fn($assessment) => $assessment->employee->position == $position,
                                                );
                                            $rowNumber = 1;
                                        @endphp


                                        @forelse ($filteredEmployees as $assessment)
                                            @if ($assessment->has_score)
                                                <tr>
                                                    <td class="text-center">{{ $rowNumber++ }}</td>
                                                    <td class="text-center">{{ $assessment->employee->name ?? '-' }}</td>
                                                    @foreach ($alcs as $alcId => $alcTitle)
                                                        @php
                                                            $detail = $assessment->details->firstWhere(
                                                                'alc_id',
                                                                $alcId,
                                                            );

                                                            // status detail: revise / approved / no_approval_needed / dll
                                                            $detailStatus = strtolower(
                                                                trim((string) ($detail->status ?? '')),
                                                            );

                                                            // tentukan icon berdasarkan status detail (prioritas)
                                                            // revise => !
                                                            // approved => check
                                                            // selain itu fallback sesuai kebutuhan
                                                            $iconClass = null;

                                                            if ($detailStatus === 'revise') {
                                                                $iconClass = 'fa-exclamation-triangle';
                                                            } elseif ($detailStatus === 'approved') {
                                                                $iconClass = 'fa-check';
                                                            } else {
                                                                $iconClass = null;
                                                            }

                                                        @endphp

                                                        <td class="text-center">
                                                            @if ($detail)
                                                                @if ($detail->score === '-')
                                                                    <span
                                                                        class="badge badge-lg badge-success d-block w-100">
                                                                        {{ $detail->score }}
                                                                    </span>
                                                                @else
                                                                    @php
                                                                        $scoreNum = is_numeric($detail->score)
                                                                            ? (float) $detail->score
                                                                            : null;

                                                                        $isClickable =
                                                                            ($scoreNum != null && $scoreNum == 0.0) ||
                                                                            str_contains(
                                                                                $detail->badge_class ?? '',
                                                                                'badge-danger',
                                                                            );

                                                                        $modalId = "kt_modal_warning_{$assessment->id}_{$detail->alc_id}";
                                                                    @endphp

                                                                    <span
                                                                        class="badge {{ $detail->badge_class }} {{ $isClickable ? '' : 'pe-none' }}"
                                                                        @if ($isClickable) data-bs-toggle="modal"
                                                                            data-bs-target="#{{ $modalId }}"
                                                                            data-title="Update IDP - {{ $alcTitle }}"
                                                                            data-assessment="{{ $assessment->id }}"
                                                                            data-alc="{{ $detail->alc_id }}"
                                                                            style="cursor: pointer;"
                                                                        @else
                                                                            style="cursor: default;" @endif>
                                                                        {{ $detail->score }}

                                                                        {{-- ✅ ICON LOGIC FINAL --}}
                                                                        @if ($iconClass)
                                                                            @if ($detailStatus === 'revise')
                                                                                <i
                                                                                    class="fas {{ $iconClass }} ps-2 text-danger"></i>
                                                                            @else
                                                                                <i
                                                                                    class="fas {{ $iconClass }} ps-2"></i>
                                                                            @endif
                                                                        @endif
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span
                                                                    class="badge badge-lg badge-success d-block w-100">-</span>
                                                            @endif
                                                        </td>
                                                    @endforeach

                                                    @php
                                                        // ikon per status
                                                        $statusIconMap = [
                                                            'approved' => 'fas fa-circle-check',
                                                            'checked' => 'fas fa-hourglass-half',
                                                            'waiting' => 'fas fa-hourglass-half',
                                                            'draft' => 'fas fa-file-pen',
                                                            'revise' => 'fas fa-rotate-left',
                                                            'not_created' => 'fas fa-circle-minus',
                                                            'unknown' => 'fas fa-circle-question',
                                                        ];
                                                        $s = $assessment->overall_status;
                                                        $icon = $statusIconMap[$s] ?? 'fa-circle-info';
                                                    @endphp

                                                    <td class="text-center">
                                                        <span class="status-chip" data-status="{{ $s }}"
                                                            title="{{ $assessment->overall_badge['text'] }}">
                                                            <i class="fa-solid {{ $icon }}"></i>
                                                            <span>{{ $assessment->overall_badge['text'] }}</span>
                                                        </span>
                                                    </td>


                                                    <td class="text-center" style="width: 50px">
                                                        <div class="d-flex gap-2 justify-content-center">
                                                            @php
                                                                $user = auth()->user();
                                                                $isHRDorDireksi = $user->isHRDorDireksi();
                                                                $exportablePositions = [
                                                                    'Manager',
                                                                    'GM',
                                                                    'Act Group Manager',
                                                                    'Direktur',
                                                                ];
                                                            @endphp

                                                            @if (!$isHRDorDireksi)
                                                                <a href="{{ route('development.index', $assessment->employee->id) }}"
                                                                    class="btn btn-sm btn-primary"
                                                                    style="display: {{ $assessment->overall_status == 'approved' ? '' : 'none' }}">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                </a>
                                                            @endif

                                                            <button type="button" class="btn btn-sm btn-info"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#notes_{{ $assessment->employee->id }}">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @if (!$isHRDorDireksi)
                                                                <button type="button" class="btn btn-sm btn-warning"
                                                                    onclick="sendDataConfirmation({{ $assessment->employee->id }})"
                                                                    style="display: {{ $assessment->overall_status == 'draft' ? '' : 'none' }}">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($alcs) + 3 }}" class="text-center text-muted py-4">
                                                    No employees found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between">
                                @if ($processedData->count())
                                    @if ($processedData instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                        {{ $processedData->links('pagination::bootstrap-5') }}
                                    @endif
                                @else
                                    <span>No data found.</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex align-items-center gap-4 mt-5">
                    <div class="d-flex align-items-center">
                        <span class="legend-circle bg-danger"></span>
                        <span class="ms-2 text-muted">Below Standard</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="legend-circle bg-light-danger"></span>
                        <span class="ms-2 text-muted">Need Revise</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="legend-circle bg-warning"></span>
                        <span class="ms-2 text-muted">Need Submit</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="legend-circle bg-success"></span>
                        <span class="ms-2 text-muted">Above Standard</span>
                    </div>
                </div>
            </div>

            {{-- 1. Modal Update IDP per employee & ALC --}}
            @foreach ($filteredEmployees as $assessment)
                @include('website.idp.partials.modal-update-idp', [
                    'assessment' => $assessment,
                    'alcs' => $alcs,
                ])
            @endforeach

            {{-- 2. Modal global Comment History (dummy) --}}
            @include('website.idp.partials.modal-notes-history')

            {{-- 3. Modal Summary / Notes per employee --}}
            @foreach ($filteredEmployees as $assessment)
                @include('website.idp.partials.modal-summary', [
                    'assessment' => $assessment,
                    'alcs' => $alcs,
                    'mid' => $mid,
                    'details' => $details,
                ])
            @endforeach
        </div>
    </div>
@endsection

@php
    // Build map: employee_id => [ { alc_id, alc_name, score }, ... ]
    $chartData = [];

    foreach ($processedData as $hav) {
        $eid = optional($hav->employee)->id;
        if (!$eid) {
            continue;
        }

        // Kalau 1 karyawan bisa muncul beberapa HAV, ambil satu saja yang pertama berisi detail
        if (!empty($chartData[$eid]) && count($chartData[$eid])) {
            continue;
        }

        $rows = [];
        foreach ($hav->details as $d) {
            $alcId = $d->alc_id;
            $alcName = $d->alc->title ?? ($alcs[$alcId] ?? 'ALC ' . $alcId); // fallback
            // score bisa '-', pastikan numerik
            $score = is_numeric($d->score) ? (int) $d->score : 0;

            $rows[] = [
                'alc_id' => $alcId,
                'alc_name' => $alcName,
                'score' => $score,
            ];
        }

        // urutkan biar konsisten
        usort($rows, fn($a, $b) => $a['alc_id'] <=> $b['alc_id']);

        $chartData[$eid] = $rows;
    }
@endphp

<script>
    window.IDP_CHART_DATA = @json($chartData);
</script>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');

            modals.forEach(modal => {
                let countdownInterval = null;

                modal.addEventListener('shown.bs.modal', function() {
                    const modalId = modal.getAttribute('id');
                    const [_, __, ___, assessId, alcId] = modalId.split('_');

                    const checkboxWrapper = document.getElementById(
                        `checkboxWrapper_${assessId}_${alcId}`);
                    const countdownText = document.getElementById(
                        `countdownText_${assessId}_${alcId}`);
                    const checkbox = document.getElementById(`agreeCheckbox_${assessId}_${alcId}`);
                    const target = document.getElementById(
                        `additionalContent_${assessId}_${alcId}`);
                    const btn = document.getElementById(`confirm-button-${assessId}-${alcId}`);

                    // Reset visual
                    checkboxWrapper.classList.add('d-none');
                    countdownText.classList.remove('d-none');
                    checkbox.checked = false;
                    btn.setAttribute('disabled', true);
                    target.classList.add('d-none');

                    // Jalankan countdown
                    startCountdown(() => {
                        countdownText.classList.add('d-none');
                        checkboxWrapper.classList.remove('d-none');
                    });

                    // Checkbox listener
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            target.classList.remove('d-none');
                            btn.removeAttribute('disabled');
                        } else {
                            target.classList.add('d-none');
                            btn.setAttribute('disabled', true);

                            // Ulang timer dan sembunyikan checkbox
                            checkboxWrapper.classList.add('d-none');
                            countdownText.classList.remove('d-none');
                            startCountdown(() => {
                                countdownText.classList.add('d-none');
                                checkboxWrapper.classList.remove('d-none');
                            });
                        }
                    });

                    function startCountdown(callback) {
                        clearInterval(countdownInterval); // prevent duplicate
                        const totalSeconds = 2;
                        let secondsLeft = totalSeconds;
                        let messageToggle = true;

                        updateMessage();

                        countdownInterval = setInterval(() => {
                            secondsLeft--;

                            if (secondsLeft >= 0) {
                                if (messageToggle) {
                                    countdownText.innerHTML =
                                        `Please wait <span class="countdown-seconds">for a</span> seconds...`;
                                } else {
                                    countdownText.textContent =
                                        "Make sure you read all content above...";
                                }

                                if ((totalSeconds - secondsLeft) % 2 === 0) {
                                    messageToggle = !messageToggle;
                                }
                            }

                            if (secondsLeft <= 0) {
                                clearInterval(countdownInterval);
                                callback();
                            }
                        }, 1000);

                        function updateMessage() {
                            if (messageToggle) {
                                countdownText.innerHTML =
                                    `Please wait <span class="countdown-seconds">for a</span> seconds...`;
                            } else {
                                countdownText.textContent =
                                    "Make sure you read all content above...";
                            }
                        }
                    }
                });

                modal.addEventListener('hidden.bs.modal', function() {
                    clearInterval(countdownInterval);
                    const modalId = modal.getAttribute('id');
                    const [_, __, ___, assessId, alcId] = modalId.split('_');

                    // Reset saat modal ditutup
                    const checkboxWrapper = document.getElementById(
                        `checkboxWrapper_${assessId}_${alcId}`);
                    const countdownText = document.getElementById(
                        `countdownText_${assessId}_${alcId}`);
                    const checkbox = document.getElementById(`agreeCheckbox_${assessId}_${alcId}`);
                    const target = document.getElementById(
                        `additionalContent_${assessId}_${alcId}`);
                    const btn = document.getElementById(`confirm-button-${assessId}-${alcId}`);

                    checkboxWrapper.classList.add('d-none');
                    countdownText.classList.remove('d-none');
                    checkbox.checked = false;
                    btn.setAttribute('disabled', true);
                    target.classList.add('d-none');
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const maxAge = 5 * 60 * 1000;

            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
                button.addEventListener('click', function() {
                    const alc = this.getAttribute('data-alc');
                    const assessmentId = this.getAttribute('data-assessment');
                    console.log(assessmentId);
                    const modalTarget = this.getAttribute('data-bs-target');
                    const title = this.getAttribute('data-title');

                    const modal = document.querySelector(modalTarget);
                    if (!modal) return;

                    const modalTitle = modal.querySelector('.modal-header h2');
                    if (title && modalTitle) {
                        modalTitle.textContent = title;
                    }

                    const alcInput = modal.querySelector(
                        `input[id="alc_id_${assessmentId}_${alc}"]`);
                    if (alcInput) alcInput.value = alc;

                    const key = `idp_modal_${assessmentId}_${alc}`;
                    const savedData = JSON.parse(localStorage.getItem(key));
                    const now = Date.now();

                    const categorySelect = modal.querySelector(
                        `select[id="category_select_${assessmentId}_${alc}"]`);
                    const programSelect = modal.querySelector(
                        `select[id="program_select_${assessmentId}_${alc}"]`);
                    const targetInput = modal.querySelector(
                        `textarea[id="target_${assessmentId}_${alc}"]`);
                    const dueDateInput = modal.querySelector(
                        `input[id="due_date_${assessmentId}_${alc}"]`);



                    if (savedData && (now - savedData.timestamp < maxAge)) {
                        if (categorySelect) {
                            categorySelect.value = savedData.category;
                            $(categorySelect).trigger('change');
                        }
                        if (programSelect) {
                            programSelect.value = savedData.program;
                            $(programSelect).trigger('change');
                        }
                        if (targetInput) targetInput.value = savedData.target ?? '';
                        if (dueDateInput) dueDateInput.value = savedData.date ?? '';
                    } else {
                        $.ajax({
                            url: '/idp/getData',
                            method: 'GET',
                            data: {
                                assessment_id: assessmentId,
                                alc_id: alc
                            },
                            success: function(response) {
                                if (response.idp) {
                                    if (categorySelect) {
                                        categorySelect.value = response.idp.category;
                                        $(categorySelect).trigger('change');
                                    }
                                    if (programSelect) {
                                        programSelect.value = response.idp
                                            .development_program;
                                        $(programSelect).trigger('change');
                                    }
                                    if (targetInput) targetInput.value = response.idp
                                        .development_target ?? '';
                                    if (dueDateInput) dueDateInput.value = response.idp
                                        .date ?? '';
                                } else {
                                    if (categorySelect) categorySelect.value = '';
                                    if (programSelect) programSelect.value = '';

                                }
                            }
                        });
                    }
                });
            });


            document.querySelectorAll('.btn-create-idp').forEach(button => {
                button.addEventListener('click', function() {
                    const assessmentId = this.getAttribute('data-assessment');
                    const havDetailId = this.getAttribute('data-hav');
                    const alcId = this.getAttribute('data-alc');
                    const category = document.getElementById(
                        `category_select_${assessmentId}_${alcId}`).value;
                    const program = document.getElementById(
                        `program_select_${assessmentId}_${alcId}`).value;
                    const target = document.getElementById(`target_${assessmentId}_${alcId}`).value;
                    const date = document.getElementById(`due_date_${assessmentId}_${alcId}`).value;


                    // Validasi
                    if (!category) {
                        Swal.fire("Peringatan", "Silakan pilih kategori!", "warning");
                        categoryInput?.focus();
                        return;
                    }

                    if (!program) {
                        Swal.fire("Peringatan", "Silakan pilih program pengembangan!", "warning");
                        programInput?.focus();
                        return;
                    }

                    if (!target) {
                        Swal.fire("Peringatan", "Silakan isi target pengembangan!", "warning");
                        targetInput?.focus();
                        return;
                    }

                    if (!date) {
                        Swal.fire("Peringatan", "Silakan pilih tanggal due date!", "warning");
                        dateInput?.focus();
                        return;
                    }

                    const key = `idp_modal_${assessmentId}_${alcId}`;

                    localStorage.setItem(key, JSON.stringify({
                        assessment_id: assessmentId,
                        hav_detail_id: havDetailId,
                        alc_id: alcId,
                        category: category,
                        program: program,
                        target: target,
                        date: date,
                        timestamp: Date.now()
                    }));

                    $.ajax({
                        url: "{{ route('idp.store') }}",
                        type: "POST",
                        data: {
                            hav_detail_id: havDetailId,
                            alc_id: alcId,
                            assessment_id: assessmentId,
                            development_program: program,
                            category: category,
                            development_target: target,
                            date: date,
                            '_token': "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then(() => {
                                localStorage.removeItem(key);
                                $(`#kt_modal_warning_${assessmentId}_${alcId}`)
                                    .modal('hide');
                                location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            alert(error);
                        }
                    });
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Fungsi pencarian
            document.getElementById("searchButton").addEventListener("click", function() {
                var searchValue = document.getElementById("searchInput").value.toLowerCase();
                var table = document.getElementById("kt_table_users").getElementsByTagName("tbody")[0];
                var rows = table.getElementsByTagName("tr");

                for (var i = 0; i < rows.length; i++) {
                    var nameCell = rows[i].getElementsByTagName("td")[1];
                    if (nameCell) {
                        var nameText = nameCell.textContent || nameCell.innerText;
                        rows[i].style.display = nameText.toLowerCase().includes(searchValue) ? "" : "none";
                    }
                }
            });

            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".open-modal").forEach(button => {
                    button.addEventListener("click", function() {
                        let id = this.getAttribute("data-id");
                        let modal = new bootstrap.Modal(document.getElementById(
                            `notes_${id}`));
                        modal.show();
                    });
                });
            });

            // SweetAlert untuk tombol delete
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let employeeId = this.getAttribute('data-id');

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/employee/${employeeId}`;

                            let csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';

                            let methodField = document.createElement('input');
                            methodField.type = 'hidden';
                            methodField.name = '_method';
                            methodField.value = 'DELETE';

                            form.appendChild(csrfToken);
                            form.appendChild(methodField);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('kt_datepicker_7');

            flatpickr(dateInput, {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                mode: "range"
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            let dueDateInput = document.getElementById("kt_datepicker_7");

            let startDate = new Date(2025, 11, 8).toISOString().split("T")[0];
            let endDate = new Date(2025, 11, 17).toISOString().split("T")[0];

            dueDateInput.value = startDate;
            dueDateInput.min = startDate;
            dueDateInput.max = endDate;
        });

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".open-modal").forEach(button => {
                button.addEventListener("click", function() {
                    let id = this.getAttribute("data-id");
                    let modal = new bootstrap.Modal(document.getElementById(
                        `notes_${id}`));
                    modal.show();
                });
            });
        });

        // const employeeId = "{{ $employees->first()->id }}"; // pastikan $employee dikirim dari controller
        // alert(employeeId);

        function sendDataConfirmation(employeeId) {
            Swal.fire({
                title: 'Kirim IDP ke atasan?',
                text: 'Pastikan semua ALC bernilai < 3 IDP sudah dibuat.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('send.idp') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                employee_id: employeeId
                            })
                        })
                        .then(async res => {
                            const data = await res.json();
                            if (!res.ok) {
                                throw new Error(data.message || "Terjadi kesalahan.");
                            }
                            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                // reload halaman setelah user klik OK pada alert sukses
                                location.reload();
                            });
                        })
                        .catch(err => {
                            Swal.fire('Gagal', err.message || 'Terjadi kesalahan saat mengirim IDP.', 'error');
                            console.error(err);
                        });
                }
            });
        }

        document.getElementById('searchInputEmployee').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchButton').click();
            }
        });
    </script>
    <script>
        (() => {
            function renderNotesChart(modalEl, employeeId) {
                const canvas = modalEl.querySelector(
                    `canvas[data-chart="assessment"][data-employee-id="${employeeId}"]`
                );
                if (!canvas) return;

                const rows = (window.IDP_CHART_DATA && window.IDP_CHART_DATA[employeeId]) || [];
                if (!rows.length) {
                    console.warn('No chart data for employee', employeeId);
                    return;
                }

                const labels = rows.map(r => r.alc_name);
                const scores = rows.map(r => Number(r.score) || 0);

                // destroy chart lama jika ada
                if (canvas.chart) {
                    try {
                        canvas.chart.destroy();
                    } catch (_) {}
                }

                const ctx = canvas.getContext('2d');
                canvas.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Assessment Scores',
                            data: scores,
                            backgroundColor: scores.map(s => s < 3 ? 'rgba(255,99,132,0.8)' :
                                'rgba(75,192,192,0.8)'),
                            borderColor: scores.map(s => s < 3 ? 'rgba(255,99,132,1)' :
                                'rgba(75,192,192,1)'),
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `Score: ${ctx.raw}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: 5,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        animation: {
                            duration: 800,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }

            // render saat modal notes dibuka
            $(document).on('shown.bs.modal', 'div.modal[id^="notes_"]', function() {
                const employeeId = this.id.replace('notes_', '');
                renderNotesChart(this, employeeId);
            });

            // bersihkan saat modal ditutup
            $(document).on('hidden.bs.modal', 'div.modal[id^="notes_"]', function() {
                const canvas = this.querySelector('canvas[data-chart="assessment"]');
                if (canvas?.chart) {
                    try {
                        canvas.chart.destroy();
                    } catch (_) {}
                    canvas.chart = null;
                }
            });
        })();
    </script>
@endpush
