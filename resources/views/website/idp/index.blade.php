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
                                            $filteredEmployees =
                                                $position == 'Show All'
                                                    ? $assessments
                                                        ->groupBy('employee_id')
                                                        ->map(fn($group) => $group->sortByDesc('created_at')->first())
                                                    : $assessments
                                                        ->where('employee.position', $position)
                                                        ->groupBy('employee_id')
                                                        ->map(fn($group) => $group->sortByDesc('created_at')->first());
                                            $rowNumber = 1;
                                        @endphp


                                        @forelse ($filteredEmployees as $index => $assessment)
                                            @php
                                                // Cek apakah assessment memiliki minimal satu detail dengan score
                                                $hasScore = $assessment->details->contains(function ($detail) {
                                                    return !is_null($detail->score);
                                                });
                                            @endphp
                                            @if ($hasScore)
                                                <tr>
                                                    <td class="text-center">{{ $rowNumber++ }}</td>
                                                    <td class="text-center">{{ $assessment->employee->name ?? '-' }}</td>
                                                    @foreach ($assessment->details as $title)
                                                        @php
                                                            $score = $title->score ?? '-';
                                                            $idpExists = DB::table('idp')
                                                                ->where('hav_detail_id', $title->id)
                                                                ->where('alc_id', $title->alc_id)
                                                                ->exists();

                                                            $assessmentId = DB::table('assessments')
                                                                ->select('id')
                                                                ->where('employee_id', $index)
                                                                ->latest()
                                                                ->first();

                                                            $isRevise = false;

                                                            $idp = \App\Models\Idp::where('hav_detail_id', $title->id)
                                                                ->where('alc_id', $title->alc_id)
                                                                ->first();

                                                            if ($idp && $idp->status === -1) {
                                                                $isRevise = true;
                                                            }

                                                            // Ambil level approval dari employee yang sedang di-assess
                                                            $approvalLevel = $assessment->employee->getCreateAuth();

                                                            // Ambil atasan dari employee tsb berdasarkan level approval
                                                            $superiors = $assessment->employee->getSuperiorsByLevel(
                                                                $approvalLevel,
                                                            );

                                                            $isSuperior = $superiors->contains(
                                                                'id',
                                                                auth()->user()->employee->id,
                                                            );

                                                            $emp = [];
                                                            foreach ($assessment->details as $assessmentDetail) {
                                                                if ($assessmentDetail->score < 3) {
                                                                    $emp[$assessment->employee_id][] = [
                                                                        'assessment_id' =>
                                                                            $assessmentDetail->assessment_id,
                                                                        'alc_id' => $assessmentDetail->alc_id,
                                                                        'alc_name' =>
                                                                            $assessmentDetail->alc->name ?? 'Unknown',
                                                                    ];
                                                                }
                                                            }

                                                            $status = 'not_created'; // Default jika tidak ada satupun IDP

                                                            // Check dulu apakah semua sudah punya IDP
                                                            $collectedStatuses = [];
                                                            $hasNotCreated = false; // Flag untuk cek apakah ada yang belum punya IDP

                                                            foreach ($assessment->details as $detail) {
                                                                // Hanya proses jika score <= 3 ATAU ada suggesstion development
                                                                if (
                                                                    $detail->score <= 3 ||
                                                                    $detail->suggestion_development !== null
                                                                ) {
                                                                    // Jika score >= 3 DAN tidak ada suggesstion development, skip
                                                                    if (
                                                                        $detail->score >= 3 &&
                                                                        $detail->suggestion_development === null
                                                                    ) {
                                                                        continue;
                                                                    }

                                                                    // cek apakah detail memiliki relasi IDP yang valid
                                                                    $hasValidIdp = \App\Models\Idp::where(
                                                                        'hav_detail_id',
                                                                        $detail->id,
                                                                    )
                                                                        ->where('alc_id', $detail->alc_id)
                                                                        ->exists();

                                                                    if (!$hasValidIdp) {
                                                                        $collectedStatuses[] = 'not_created';
                                                                        $hasNotCreated = true; // Set flag
                                                                        continue;
                                                                    }

                                                                    // Jika IDP exists, ambil statusnya
                                                                    if (
                                                                        $detail->relationLoaded('idp') &&
                                                                        $detail->idp->isNotEmpty()
                                                                    ) {
                                                                        $idpRecord = $detail->idp->first();
                                                                    } else {
                                                                        $idpRecord = \App\Models\Idp::where(
                                                                            'hav_detail_id',
                                                                            $detail->id,
                                                                        )
                                                                            ->where('alc_id', $detail->alc_id)
                                                                            ->first();
                                                                    }

                                                                    if ($idpRecord && isset($idpRecord->status)) {
                                                                        switch ($idpRecord->status) {
                                                                            case 0:
                                                                                $collectedStatuses[] = 'draft';
                                                                                break;
                                                                            case 1:
                                                                                $collectedStatuses[] = 'waiting';
                                                                                break;
                                                                            case 2:
                                                                                $collectedStatuses[] = 'checked';
                                                                                break;
                                                                            case 3:
                                                                                $collectedStatuses[] = 'approved';
                                                                                break;
                                                                            case -1:
                                                                                $collectedStatuses[] = 'revise';
                                                                                break;
                                                                            default:
                                                                                $collectedStatuses[] = 'unknown';
                                                                        }
                                                                    } else {
                                                                        $collectedStatuses[] = 'not_created';
                                                                        $hasNotCreated = true;
                                                                    }
                                                                }
                                                            }
                                                            // dd($collectedStatuses);

                                                            // Jika ada yang belum punya IDP, prioritaskan not_created
                                                            if (count($collectedStatuses) > 0) {
                                                                // Jika ada yang belum punya IDP, langsung set ke not_created
                                                                if ($hasNotCreated) {
                                                                    $status = 'not_created';
                                                                } else {
                                                                    // Jika semua sudah punya IDP, baru sort berdasarkan prioritas normal
                                                                    $normalPriority = [
                                                                        'revise' => 0,
                                                                        'draft' => 1,
                                                                        'waiting' => 2,
                                                                        'checked' => 3,
                                                                        'approved' => 4,
                                                                        'unknown' => 5,
                                                                    ];

                                                                    usort($collectedStatuses, function ($a, $b) use (
                                                                        $normalPriority,
                                                                    ) {
                                                                        $priorityA = $normalPriority[$a] ?? 999;
                                                                        $priorityB = $normalPriority[$b] ?? 999;
                                                                        return $priorityA <=> $priorityB;
                                                                    });

                                                                    $status = $collectedStatuses[0];
                                                                }
                                                            }

                                                            $badges = [
                                                                'no_approval_needed' => [
                                                                    'text' => '-',
                                                                    'class' => 'light-primary',
                                                                ],
                                                                'not_created' => [
                                                                    'text' => 'Not Created',
                                                                    'class' => 'light-dark',
                                                                ],
                                                                'draft' => [
                                                                    'text' => 'Need Submit',
                                                                    'class' => 'light-secondary',
                                                                ],
                                                                'waiting' => [
                                                                    'text' => 'Waiting',
                                                                    'class' => 'light-warning',
                                                                ],
                                                                'checked' => [
                                                                    'text' => 'Checked',
                                                                    'class' => 'light-info',
                                                                ],
                                                                'approved' => [
                                                                    'text' => 'Approved',
                                                                    'class' => 'light-success',
                                                                ],
                                                                'revise' => [
                                                                    'text' => 'Need Revise',
                                                                    'class' => 'light-danger',
                                                                ],
                                                                'unknown' => [
                                                                    'text' => 'Unknown',
                                                                    'class' => 'light-secondary',
                                                                ],
                                                            ];

                                                            $badge = $badges[$status] ?? $badges['not_created'];

                                                            $badgeClass = 'badge-lg d-block w-100 ';
                                                            if ($isRevise) {
                                                                $badgeClass .= 'badge-light-danger';
                                                            } elseif ($score < 3) {
                                                                $badgeClass .= 'badge-danger';
                                                            } elseif ($title->suggestion_development !== null) {
                                                                $badgeClass .= 'badge-warning';
                                                            } else {
                                                                $badgeClass .= 'badge-success';
                                                            }

                                                            $showIcon = false;
                                                            if ($score < 3) {
                                                                $showIcon = true;
                                                            } elseif (
                                                                $title->suggestion_development !== null &&
                                                                !$idpExists
                                                            ) {
                                                                $showIcon = true;
                                                            } elseif ($idpExists) {
                                                                $showIcon = true;
                                                            }

                                                            $disableButton = $status == 'draft' ? '' : 'none';
                                                            $disableButton2 = $status == 'approved' ? '' : 'none';

                                                            // DEBUG - uncomment untuk melihat status yang dikumpulkan
                                                            // if ($assessment->employee->name == 'Rafie Afif Andika') {
                                                            //     dd([
                                                            //         'collectedStatuses' => $collectedStatuses,
                                                            //         'hasNotCreated' => $hasNotCreated,
                                                            //         'finalStatus' => $status
                                                            //     ]);
                                                            // }

                                                        @endphp
                                                        <td class="text-center">
                                                            @if ($score === '-')
                                                                <span class="badge badge-lg badge-success d-block w-100">
                                                                    {{ $score }}
                                                                </span>
                                                            @else
                                                                <span class="badge {{ $badgeClass }}"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#kt_modal_warning_{{ $assessment->id }}_{{ $title->alc_id }}"
                                                                    data-title="Update IDP - {{ $title->alc->name }}"
                                                                    data-assessment="{{ $assessmentId?->id }}"
                                                                    data-alc="{{ $title->alc_id }}"
                                                                    style="cursor: pointer;">

                                                                    {{ $score }}

                                                                    @if ($showIcon)
                                                                        <i
                                                                            class="fas {{ $idpExists ? 'fa-check' : 'fa-exclamation-triangle' }} ps-2"></i>
                                                                    @endif
                                                                </span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td class="text-center">
                                                        <span class="badge badge-{{ $badge['class'] }}"
                                                            style="
                                                            min-width: 90px;
                                                            display: inline-block;
                                                            padding: 0.75rem;
                                                            text-align: center;
                                                            font-size: 0.85rem;
                                                            font-weight: 600;
                                                            border-radius: 0.375rem;
                                                            white-space: nowrap;
                                                        ">
                                                            {{ $badge['text'] }}
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
                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                    style="display: {{ $disableButton2 }}"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#addEntryModal-{{ $assessment->employee->id }}">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                </button>
                                                            @endif

                                                            <button type="button" class="btn btn-sm btn-info"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#notes_{{ $assessment->employee->id }}">
                                                                <i class="fas fa-eye"></i>
                                                            </button>

                                                            @if (in_array($jobPositions, $exportablePositions))
                                                                <button type="button" class="btn btn-sm btn-success"
                                                                    onclick="window.location.href='{{ route('idp.exportTemplate', ['employee_id' => $assessment->employee->id]) }}'">
                                                                    <i class="fas fa-file-export"></i>
                                                                </button>
                                                            @endif

                                                            @if (!$isHRDorDireksi)
                                                                <button type="button" class="btn btn-sm btn-warning"
                                                                    onclick="sendDataConfirmation({{ $assessment->employee->id }})"
                                                                    style="display: {{ $disableButton }}">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </button>
                                                            @endif

                                                            {{-- <button type="button" class="btn btn-sm btn-success"
                                                            onclick="approveAction()">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="rejectAction()">
                                                            <i class="fas fa-times-circle"></i>
                                                        </button> --}}
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
                                @if ($assessments->count())
                                    @if ($assessments instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                        {{ $assessments->links('pagination::bootstrap-5') }}
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

            @foreach ($assessments as $assessment)
                @if (isset($assessment->employee))
                    @php
                        $employee = $assessment->employee;
                    @endphp
                    @foreach ($alcs as $id => $title)
                        @php
                            set_time_limit(60);

                            $weaknessDetail = $assessment->details->where('alc_id', $id)->first();

                            $assessmentId = null;
                            $weakness = null;
                            $strength = null;
                            $idp = null;
                            $assessment_detail_id = null;

                            if ($weaknessDetail) {
                                // Ambil assessment ID berdasarkan employee dari weaknessDetail
                                $assessmentId = DB::table('assessments')
                                    ->select('id')
                                    ->where('employee_id', $weaknessDetail->hav->employee->id)
                                    ->latest()
                                    ->first();

                                // Ambil data weakness
                                $weakness = \App\Models\DetailAssessment::with('assessment.employee')
                                    ->select('weakness', 'suggestion_development')
                                    ->whereHas('assessment.employee', function ($query) use ($weaknessDetail) {
                                        $query->where('id', $weaknessDetail->hav->employee->id);
                                    })
                                    ->where('alc_id', $weaknessDetail->alc_id)
                                    ->latest()
                                    ->first();

                                // Ambil data strength
                                $strength = \App\Models\DetailAssessment::with('assessment.employee')
                                    ->select('strength', 'suggestion_development')
                                    ->whereHas('assessment.employee', function ($query) use ($weaknessDetail) {
                                        $query->where('id', $weaknessDetail->hav->employee->id);
                                    })
                                    ->where('alc_id', $weaknessDetail->alc_id)
                                    ->latest()
                                    ->first();

                                // Ambil IDP
                                $idp = \App\Models\Idp::with('commentHistory')
                                    ->where('hav_detail_id', $weaknessDetail->id)
                                    ->where('alc_id', $id)
                                    ->first();

                                // Cari assessment_detail_id yang cocok
                                foreach ($assessment->details as $detail) {
                                    if ($idp && $detail->assesment_id == $idp->id) {
                                        $assessment_detail_id = $detail->id;
                                    }
                                }
                            }

                        @endphp
                        <div class="modal fade" id="kt_modal_warning_{{ $assessment->id }}_{{ $id }}"
                            tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
                            <div class="modal-dialog modal-dialog-centered mw-750px">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="fw-bold" id="modal-title-{{ $assessment->id }}_{{ $id }}">
                                            Update IDP</h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body scroll-y mx-2 mt-5">
                                        <input type="hidden" name="employee_id" value="{{ $assessment->id }}">
                                        <input type="hidden" name="assessment_id" value="{{ $assessment_detail_id }}">
                                        <input type="hidden" name="alc_id"
                                            id="alc_id_{{ $assessment->id }}_{{ $id }}"
                                            value="{{ $id }}">

                                        {{-- Weakness Section (always shown) --}}
                                        <div class="col-lg-12 mb-10">
                                            <label class="fs-5 fw-bold form-label mb-4">Description & Suggestion
                                                Development
                                                in {{ $title }}</label>
                                            <div class="border p-4 rounded bg-light mb-5"
                                                style="max-height: 400px; overflow-y: auto;">
                                                <h6 class="fw-bold mb-">Description</h6>
                                                <p class="mb-5">
                                                    {{ !empty($weakness?->weakness) ? $weakness->weakness : (!empty($strength?->strength) ? $strength->strength : '-') }}
                                                </p>


                                                <h6 class="fw-bold mb-2">Suggestion Development</h6>
                                                <p>{{ $weaknessDetail?->suggestion_development ?? ($weakness?->suggestion_development ?? ('-' ?? ($weakness?->suggestion_development ?? '-'))) }}
                                                </p>
                                            </div>

                                            <!-- Countdown Text -->
                                            <div id="countdownText_{{ $assessment->id }}_{{ $id }}"
                                                class="text-dark text-center mb-2">
                                                Please wait <span class="countdown-seconds">for a</span> seconds...
                                            </div>

                                            <!-- Checkbox -->
                                            <div class="form-check d-none"
                                                id="checkboxWrapper_{{ $assessment->id }}_{{ $id }}">
                                                <input class="form-check-input agree-checkbox" type="checkbox"
                                                    id="agreeCheckbox_{{ $assessment->id }}_{{ $id }}"
                                                    data-target="additionalContent_{{ $assessment->id }}_{{ $id }}">
                                                <label class="form-check-label text-dark"
                                                    for="agreeCheckbox_{{ $assessment->id }}_{{ $id }}">
                                                    I have read and understood the content above.
                                                </label>
                                            </div>

                                        </div>

                                        {{-- Additional content (hidden by default) --}}
                                        <div class="additional-content d-none"
                                            id="additionalContent_{{ $assessment->id }}_{{ $id }}">
                                            <div class="col-lg-12 mb-10">
                                                <hr>
                                            </div>

                                            <div class="col-lg-12 mb-10">
                                                <label class="fs-5 fw-bold form-label mb-2"><span
                                                        class="required">Category</span></label>
                                                <select id="category_select_{{ $assessmentId?->id }}_{{ $id }}"
                                                    name="category" class="form-select form-select-lg fw-semibold"
                                                    data-control="select2" data-placeholder="Select categories..."
                                                    required>
                                                    <option value="">Select Category</option>
                                                    @foreach (['Feedback', 'Self Development', 'Shadowing', 'On Job Development', 'Mentoring', 'Training'] as $category)
                                                        <option value="{{ $category }}"
                                                            {{ isset($idp) && $idp->category == $category ? 'selected' : '' }}>
                                                            {{ $category }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-lg-12 mb-10">
                                                <label class="fs-5 fw-bold form-label mb-2"><span
                                                        class="required">Development Program</span></label>
                                                <select id="program_select_{{ $assessmentId?->id }}_{{ $id }}"
                                                    name="development_program"
                                                    class="form-select form-select-lg fw-semibold" data-control="select2"
                                                    data-placeholder="Select Programs..." required>
                                                    <option value="">Select Development Program</option>
                                                    @foreach (['Superior (DGM & GM)', 'Book Reading', 'FIGURE LEADER', 'Team Leader', 'SR PROJECT', 'People Development Program', 'Leadership', 'Developing Sub Ordinate'] as $program)
                                                        <option value="{{ $program }}"
                                                            {{ isset($idp) && $idp->development_program == $program ? 'selected' : '' }}>
                                                            {{ $program }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-lg-12 fv-row mb-10">
                                                <label for="target_{{ $assessmentId?->id }}_{{ $id }}"
                                                    class="fs-5 fw-bold form-label mb-2 required">Development
                                                    Target</label>
                                                <textarea id="target_{{ $assessmentId?->id }}_{{ $id }}" name="development_target" class="form-control">{{ isset($idp) ? $idp->development_target : '' }}</textarea>
                                            </div>

                                            <div class="col-lg-12 fv-row mb-5">
                                                <label for="due_date_{{ $assessmentId?->id }}_{{ $id }}"
                                                    class="fs-5 fw-bold form-label mb-2 required">Due Date</label>
                                                <input type="date"
                                                    id="due_date_{{ $assessmentId?->id }}_{{ $id }}"
                                                    name="date" class="form-control" required
                                                    value="{{ isset($idp) ? $idp->date : '' }}" />
                                            </div>

                                            <div class="col-lg-12 fv-row mb-5">
                                                <hr>
                                            </div>

                                            @if ($idp && $idp->commentHistory && $idp->commentHistory->isNotEmpty())
                                                <div class="col-lg-12 fv-row mb-5">
                                                    <label class="fs-5 fw-bold form-label mb-2">Comment
                                                        History</label>
                                                    @foreach ($idp->commentHistory as $comment)
                                                        <div class="border rounded p-3 mb-3 bg-light">
                                                            <div class="fw-semibold mb-2">
                                                                {{ $comment->employee->name ?? 'Unknown Employee' }} —
                                                                <small class="text-muted">
                                                                    {{ \Carbon\Carbon::parse($comment->created_at)->format('d M Y H:i') }}
                                                                </small>
                                                            </div>
                                                            <div class="text-muted fst-italic">
                                                                {{ $comment->comment }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="text-center pt-15">
                                                <button type="button"
                                                    id="confirm-button-{{ $assessment->id }}-{{ $id }}"
                                                    class="btn btn-primary btn-create-idp interlock-submit"
                                                    data-assessment="{{ $assessmentId?->id }}"
                                                    data-hav="{{ $weaknessDetail?->id }}" data-alc="{{ $id }}"
                                                    disabled>
                                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                                        aria-hidden="true"></span>
                                                    <span class="btn-text">Submit</span>
                                                </button>
                                            </div>
                                        </div> <!-- end .additional-content -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach

            @foreach ($assessments as $assessment)
                <div class="modal fade" id="addEntryModal-{{ $assessment->employee->id }}" tabindex="-1"
                    aria-labelledby="addEntryModalLabel-{{ $assessment->employee->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Development for {{ $assessment->employee->name }}</h5>
                            </div>
                            <div class="modal-body">
                                <!-- Tab Navigation -->
                                <ul class="nav nav-tabs" id="developmentTabs-{{ $assessment->employee->id }}"
                                    role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="midYear-tab-{{ $assessment->employee->id }}"
                                            data-bs-toggle="tab"
                                            data-bs-target="#midYear-{{ $assessment->employee->id }}" type="button"
                                            role="tab">Mid Year</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="oneYear-tab-{{ $assessment->employee->id }}"
                                            data-bs-toggle="tab"
                                            data-bs-target="#oneYear-{{ $assessment->employee->id }}" type="button"
                                            role="tab">One Year</button>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content mt-3">
                                    <!-- MID YEAR TAB -->
                                    <div class="tab-pane fade show active" id="midYear-{{ $assessment->employee->id }}"
                                        role="tabpanel">
                                        <form
                                            action="{{ route('idp.storeMidYear', ['employee_id' => $assessment->employee->id]) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="employee_id"
                                                value="{{ $assessment->employee->id }}">
                                            <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">

                                            <div id="programContainerMid_{{ $assessment->employee->id }}">
                                                @if (!empty($assessment->details))
                                                    @php $hasMidYearData = false; @endphp

                                                    @foreach ($assessment->details as $program)
                                                        @if (empty($program->recommendedProgramsMidYear) || empty($program->recommendedProgramsMidYear[0]['program']))
                                                            @continue
                                                        @endif

                                                        @php $hasMidYearData = true; @endphp

                                                        <div class="programItem">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Development
                                                                    Program</label>
                                                                <input type="text" class="form-control"
                                                                    name="development_program[]"
                                                                    value="{{ $program->recommendedProgramsMidYear[0]['program'] }}"
                                                                    readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Date</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ $program->recommendedProgramsMidYear[0]['date'] ?? '-' }}"
                                                                    readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="hidden" name="idp_id[]"
                                                                    value="{{ $program->idp[0]['id'] ?? '-' }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Development
                                                                    Achievement</label>
                                                                <input type="text" class="form-control"
                                                                    name="development_achievement[]"
                                                                    placeholder="Enter achievement" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Next Action</label>
                                                                <input type="text" class="form-control"
                                                                    name="next_action[]" placeholder="Enter next action"
                                                                    required>
                                                            </div>
                                                            <hr>
                                                        </div>
                                                    @endforeach

                                                    @if (!$hasMidYearData)
                                                        <p class="text-center text-muted">No data available</p>
                                                    @else
                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                    @endif
                                                @else
                                                    <p class="text-center text-muted">No data available</p>
                                                @endif
                                            </div>
                                        </form>
                                    </div>

                                    <div class="tab-pane fade" id="oneYear-{{ $assessment->employee->id }}"
                                        role="tabpanel">
                                        <form id="reviewForm2-{{ $assessment->employee->id }}"
                                            action="{{ route('idp.storeOneYear', ['employee_id' => $assessment->employee->id]) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="employee_id"
                                                value="{{ $assessment->employee->id }}">

                                            <div id="programContainerOne_{{ $assessment->employee->id }}"
                                                class="programContainer">
                                                @if (!empty($assessment->details))
                                                    @php $hasData = false; @endphp

                                                    @foreach ($assessment->details as $program)
                                                        @if (empty($program->recommendedProgramsOneYear) || empty($program->recommendedProgramsOneYear[0]['program']))
                                                            @continue
                                                        @endif

                                                        @php $hasData = true; @endphp

                                                        <div class="programItem">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Development
                                                                    Program</label>
                                                                <input type="text" class="form-control"
                                                                    name="development_program[]"
                                                                    value="{{ $program->recommendedProgramsOneYear[0]['program'] }}"
                                                                    readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Date</label>
                                                                <input type="text" class="form-control" name="date[]"
                                                                    value="{{ $program->recommendedProgramsOneYear[0]['date'] }}"
                                                                    readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="hidden" name="idp_id[]"
                                                                    value="{{ $program->idp[0]->id ?? '-' }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Evaluation Result</label>
                                                                <input type="text" class="form-control"
                                                                    name="evaluation_result[]"
                                                                    placeholder="Enter evaluation result" required>
                                                            </div>
                                                            <hr>
                                                        </div>
                                                    @endforeach

                                                    @if (!$hasData)
                                                        <p class="text-center text-muted">No data available</p>
                                                    @else
                                                        <div class="d-flex justify-content-between mt-2">
                                                            <button type="submit"
                                                                class="btn btn-primary btn-sm">Save</button>
                                                        </div>
                                                    @endif
                                                @else
                                                    <p class="text-center text-muted">No data available</p>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal Footer -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Modal History Komentar -->
            <div class="modal fade" id="notesHistory" tabindex="-1" aria-labelledby="notesHistoryLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="notesHistoryLabel">Comment History</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-4">
                                <h6>Employee: <span id="employeeName">Ferry Avianto</span></h6>
                            </div>
                            <!-- List of comments -->
                            <div class="list-group" id="commentHistory">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <h6 class="fw-bold">Customer Focus</h6>
                                    <p>Dokumen kurang lengkap.</p>
                                    <small class="text-muted">Date: 2023-05-01 10:00 AM</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <h6 class="fw-bold">Analysis & Judgment</h6>
                                    <p>Dokumen tidak lengkap</p>
                                    <small class="text-muted">Date: 2023-05-02 11:30 AM</small>
                                </a>
                                <!-- More comments can be added dynamically -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            @foreach ($assessments as $assessment)
                <div class="modal fade" id="notes_{{ $assessment->employee->id }}" tabindex="-1" aria-modal="true"
                    role="dialog">
                    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="fw-bold">Summary {{ $assessment->employee->name }}</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body scroll-y mx-2">
                                <form id="kt_modal_update_role_form_{{ $assessment->id }}" class="form">
                                    <style>
                                        .section-title {
                                            font-weight: 600;
                                            font-size: 1.3rem;
                                            /* 16px */
                                            border-left: 4px solid #0d6efd;
                                            padding-left: 10px;
                                            margin-top: 2rem;
                                            margin-bottom: 1rem;
                                            display: flex;
                                            align-items: center;
                                            gap: 0.5rem;
                                        }

                                        .section-title i {
                                            color: #0d6efd;
                                            font-size: 1.2rem;
                                        }

                                        table.custom-table {
                                            font-size: 0.9375rem;
                                            /* 15px */
                                        }

                                        table.custom-table th,
                                        table.custom-table td {
                                            padding: 0.75rem 1rem;
                                            vertical-align: top;
                                        }

                                        table.custom-table thead {
                                            background-color: #f8f9fa;
                                            font-weight: 600;
                                            font-size: 1rem;
                                        }

                                        table.custom-table tbody tr:hover {
                                            background-color: #f1faff;
                                        }
                                    </style>
                                    @php
                                        $strengthRows = [];
                                        $weaknessRows = [];

                                        foreach ($alcs as $id => $title) {
                                            $weaknessDetail = $assessment->details->where('alc_id', $id)->first();
                                            $detailAlc = null;

                                            if (
                                                $weaknessDetail &&
                                                $weaknessDetail->hav &&
                                                $weaknessDetail->hav->employee
                                            ) {
                                                $employeeId = $weaknessDetail->hav->employee->id;

                                                $detailAlc = \App\Models\DetailAssessment::with('assessment.employee')
                                                    ->whereHas('assessment.employee', function ($query) use (
                                                        $employeeId,
                                                    ) {
                                                        $query->where('id', $employeeId);
                                                    })
                                                    ->where('alc_id', $weaknessDetail->alc_id)
                                                    ->latest()
                                                    ->first();
                                            }

                                            if ($detailAlc) {
                                                if ($detailAlc->strength) {
                                                    $strengthRows[] = [
                                                        'title' => $title ?? '-',
                                                        'value' => $detailAlc->strength,
                                                    ];
                                                }

                                                if ($detailAlc->weakness) {
                                                    $weaknessRows[] = [
                                                        'title' => $title ?? '-',
                                                        'value' => $detailAlc->weakness,
                                                    ];
                                                }
                                            }
                                        }
                                    @endphp

                                    @if (count($strengthRows) || count($weaknessRows))
                                        <div class="section-title"><i class="bi bi-lightning-charge-fill"></i>Strength
                                            & Weakness</div>

                                        @if (count($strengthRows))
                                            <div class="table-responsive mb-4">
                                                <table class="table table-bordered table-hover custom-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 30%;">Strength</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($strengthRows as $row)
                                                            <tr>
                                                                <td>{{ $row['title'] }}</td>
                                                                <td>{{ $row['value'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="text-center text-muted">No data
                                                                    available</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif

                                        @if (count($weaknessRows))
                                            <div class="table-responsive mb-4">
                                                <table class="table table-bordered table-hover custom-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 30%;">Weakness</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($weaknessRows as $row)
                                                            <tr>
                                                                <td>{{ $row['title'] }}</td>
                                                                <td>{{ $row['value'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="text-center text-muted">No data
                                                                    available</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @endif

                                    <div class="section-title"><i class="bi bi-person-workspace"></i>Individual
                                        Development Program</div>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
                                                    <th>ALC</th>
                                                    <th>Category</th>
                                                    <th>Program</th>
                                                    <th>Target</th>
                                                    <th>Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($assessment->details->filter(fn($item) => $item->idp->isNotEmpty()) as $idp)
                                                    <tr>
                                                        <td>{{ $idp->alc->name ?? '-' }}</td>
                                                        <td>{{ $idp->idp->first()?->category }}</td>
                                                        <td>{{ $idp->idp->first()?->development_program }}</td>
                                                        <td>{{ $idp->idp->first()?->development_target }}</td>
                                                        <td>
                                                            {{ optional($idp->idp->first())->date ? \Carbon\Carbon::parse($idp->idp->first()->date)->format('d-m-Y') : '-' }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No data
                                                            available</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="section-title"><i class="bi bi-bar-chart-line-fill"></i>Mid Year
                                        Review</div>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Development Program</th>
                                                    <th>Achievement</th>
                                                    <th>Next Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($mid->where('employee_id', $assessment->employee_id) as $items)
                                                    <tr>
                                                        <td>{{ $items->development_program }}</td>
                                                        <td>{{ $items->development_achievement }}</td>
                                                        <td>{{ $items->next_action }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">No data
                                                            available</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="section-title"><i class="bi bi-calendar-check-fill"></i>One Year
                                        Review</div>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Development Program</th>
                                                    <th>Evaluation Result</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($details->where('employee_id', $assessment->employee->id) as $item)
                                                    <tr>
                                                        <td>{{ $item->development_program }}</td>
                                                        <td>{{ $item->evaluation_result }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">No data
                                                            available</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // document.addEventListener("DOMContentLoaded", function() {
        //     document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(button) {
        //         button.addEventListener('click', function() {
        //             var targetModalId = this.getAttribute('data-bs-target');
        //             var title = this.getAttribute('data-title');
        //             var alc = this.getAttribute('data-alc');

        //             const modalAlcInput = document.querySelector(targetModalId +
        //                 ' input[name=\"alc\"]');
        //             if (modalAlcInput) {
        //                 modalAlcInput.value = alc;
        //             }

        //             const modalTitle = document.querySelector(targetModalId + ' .modal-header h2');
        //             if (modalAlcInput) {
        //                 modalAlcInput.value = alc;
        //             }
        //             if (title && modalTitle) {
        //                 modalTitle.textContent = title;
        //             }

        //             const alcInput = document.querySelector(
        //                 `${targetModalId} input[name="alc_id"]`);
        //             if (alcInput) {
        //                 alcInput.value = alc;
        //             }
        //         });
        //     });
        // });
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
        // document.addEventListener("DOMContentLoaded", function() {
        //     document.querySelectorAll("form").forEach(form => {
        //         form.addEventListener("submit", function(e) {
        //             let isValid = true;

        //             // Cek apakah kategori dan program dipilih
        //             const category = form.querySelector('select[name="category[]"]').value;
        //             const program = form.querySelector('select[name="development_program[]"]')
        //                 .value;
        //             const dueDate = form.querySelector('input[name="due_date"]').value;

        //             if (!category || !program || !dueDate) {
        //                 isValid = false;
        //                 alert("Semua bidang wajib diisi!");
        //             }

        //             if (!isValid) {
        //                 e.preventDefault(); // Hentikan submit jika ada error
        //             }
        //         });
        //     });
        // });

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

            // document.addEventListener("DOMContentLoaded", function() {
            //     document.querySelectorAll(".score").forEach(function(badge) {
            //         badge.addEventListener("click", function() {
            //             var title = this.getAttribute("data-title");
            //             var assessmentId = this.getAttribute("data-assessment");

            //             if (assessmentId) {
            //                 var modalTitle = document.querySelector("#modal-title-" +
            //                     assessmentId);
            //                 if (modalTitle) {
            //                     modalTitle.textContent = title;
            //                 }
            //             }
            //         });
            //     });
            // });


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

        // function approveAction() {
        //     Swal.fire({
        //         title: 'Setujui Data?',
        //         icon: 'success',
        //         text: 'Data ini akan disetujui dan diteruskan ke tahap selanjutnya.',
        //         showCancelButton: true,
        //         confirmButtonText: 'Ya, Setujui',
        //         cancelButtonText: 'Batal'
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             Swal.fire('Disetujui!', 'Data berhasil disetujui.', 'success');
        //             // TODO: Kirim ke server via AJAX atau redirect di sini
        //         }
        //     });
        // }

        // function rejectAction() {
        //     Swal.fire({
        //         title: 'Revisi Data?',
        //         input: 'textarea',
        //         inputLabel: 'Alasan Revisi',
        //         inputPlaceholder: 'Tuliskan catatan atau alasan revisi di sini...',
        //         inputAttributes: {
        //             'aria-label': 'Catatan Revisi'
        //         },
        //         showCancelButton: true,
        //         confirmButtonText: 'Revisi',
        //         cancelButtonText: 'Batal',
        //         inputValidator: (value) => {
        //             if (!value) {
        //                 return 'Catatan wajib diisi untuk Revisi!';
        //             }
        //         }
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             Swal.fire(
        //                 'Revisi!',
        //                 'Note: ' + result.value,
        //                 'error'
        //             );
        //             // TODO: Kirim data penolakan dan catatan via AJAX atau simpan ke server
        //         }
        //     });
        // }
    </script>
@endpush
