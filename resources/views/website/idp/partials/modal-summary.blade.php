@php
    $assmnt = $assessment->assessment;
@endphp

<div class="modal fade" id="notes_{{ $assessment->employee->id }}" tabindex="-1"
    aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header align-items-start">
                <div class="d-flex flex-column flex-grow-1">
                    <h5 class="modal-title mb-2"
                        style="font-size: 2rem; font-weight: bold;">
                        Summary {{ $assmnt?->employee->name }}
                    </h5>

                    <div class="d-flex flex-wrap gap-10" style="font-size: 1.3rem;">
                        <div class="d-flex flex-column align-items-start">
                            <span style="font-size: 1rem;">Assessment Purpose</span>
                            <span style="font-size: 1.4rem; font-weight: bold;">
                                {{ $assmnt->purpose ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="d-flex flex-column align-items-start">
                            <span style="font-size: 1rem;">Assessor</span>
                            <span style="font-size: 1.4rem; font-weight: bold;">
                                {{ $assmnt->lembaga ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="d-flex flex-column align-items-start">
                            <span style="font-size: 1rem;">Target Position</span>
                            <span style="font-size: 1.4rem; font-weight: bold;">
                                {{ $assmnt->target_position ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="d-flex flex-column align-items-start">
                            <span style="font-size: 1rem;">Assessment Date</span>
                            <span style="font-size: 1.4rem; font-weight: bold;">
                                {{ $assmnt?->created_at ? $assmnt?->created_at->timezone('Asia/Jakarta')->format('d M Y') : '-' }}
                            </span>
                        </div>

                        @php
                            $employee = $assessment->employee ?? null;

                            $group = $assessment->details
                                ->filter(fn($item) => $item->idp->isNotEmpty())
                                ->values();

                            $idps = $group->flatMap->idp;

                            $firstIdpForHeader = $idps->sortByDesc('updated_at')->first();

                            $creatorName = $firstIdpForHeader->created_by_name ?? null;
                            if (!$creatorName && $employee) {
                                $assignLevel = (int) ($employee->getCreateAuth() ?? 0);
                                $fallbackCreator = $employee
                                    ->getSuperiorsByLevel($assignLevel)
                                    ->first();
                                $creatorName = $fallbackCreator->name ?? '-';
                            }
                            $creatorName = $creatorName ?: '-';

                            $idpCreatedAtText = $firstIdpForHeader?->updated_at
                                ? \Illuminate\Support\Carbon::parse($firstIdpForHeader->updated_at)
                                    ->timezone('Asia/Jakarta')
                                    ->format('d M Y')
                                : '-';
                        @endphp

                        <div class="d-flex flex-column align-items-start">
                            <span style="font-size: 1rem;">IDP Created By</span>
                            <span style="font-size: 1.4rem; font-weight: bold;">
                                {{ $creatorName }}
                            </span>
                        </div>

                        <div class="d-flex flex-column align-items-start">
                            <span style="font-size: 1rem;">IDP Created At</span>
                            <span style="font-size: 1.4rem; font-weight: bold;">
                                {{ $idpCreatedAtText }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 ms-3">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body scroll-y mx-2">
                <form id="kt_modal_update_role_form_{{ $assessment->id }}" class="form">
                    {{-- style lokal --}}
                    <style>
                        .section-title {
                            font-weight: 600;
                            font-size: 1.3rem;
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

                            if ($weaknessDetail && $weaknessDetail->hav && $weaknessDetail->hav->employee) {
                                $employeeId = $weaknessDetail->hav->employee->id;

                                $detailAlc = \App\Models\DetailAssessment::with('assessment.employee')
                                    ->whereHas('assessment.employee', function ($query) use ($employeeId) {
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

                    {{-- Chart --}}
                    <h4 class="text-center">Assessment Chart</h4>
                    <div style="width: 90%; margin: 0 auto; height: 400px;">
                        <canvas data-chart="assessment"
                            data-employee-id="{{ $assessment->employee->id }}"></canvas>
                    </div>

                    {{-- Strength & Weakness --}}
                    @if (count($strengthRows) || count($weaknessRows))
                        <div class="section-title">
                            <i class="bi bi-lightning-charge-fill"></i>
                            Strength & Weakness
                        </div>

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
                                                <td colspan="2" class="text-center text-muted">
                                                    No data available
                                                </td>
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
                                                <td colspan="2" class="text-center text-muted">
                                                    No data available
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif

                    {{-- Individual Development Program --}}
                    <div class="section-title">
                        <i class="bi bi-person-workspace"></i>
                        Individual Development Program
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover custom-table">
                            <thead>
                                <tr>
                                    <th>ALC</th>
                                    <th>Category</th>
                                    <th>Development Program</th>
                                    <th>Development Target</th>
                                    <th>Due Date</th>
                                    <th>Created By</th>
                                    <th>Last Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rows = $assessment->details->filter(
                                        fn($detail) => $detail->idp->isNotEmpty(),
                                    );
                                @endphp

                                @forelse ($rows as $detail)
                                    @php
                                        $latest = $detail->idp->sortByDesc('updated_at')->first();

                                        $employee = $assessment->employee ?? null;

                                        $creatorName = $latest->created_by_name ?? null;
                                        if (!$creatorName && $employee) {
                                            $assignLevel = (int) ($employee->getCreateAuth() ?? 0);
                                            $fallbackCreator = $employee
                                                ->getSuperiorsByLevel($assignLevel)
                                                ->first();
                                            $creatorName = $fallbackCreator->name ?? null;
                                        }
                                        $creatorName = $creatorName ?: '-';

                                        $dueText = $latest?->date
                                            ? \Illuminate\Support\Carbon::parse($latest->date)
                                                ->timezone('Asia/Jakarta')
                                                ->format('d-m-Y')
                                            : '-';

                                        $updatedText = $latest?->updated_at
                                            ? \Illuminate\Support\Carbon::parse($latest->updated_at)
                                                ->timezone('Asia/Jakarta')
                                                ->format('d-m-Y')
                                            : '-';
                                    @endphp

                                    <tr>
                                        <td>{{ $detail->alc->name ?? '-' }}</td>
                                        <td>{{ $latest->category ?? '-' }}</td>
                                        <td>{{ $latest->development_program ?? '-' }}</td>
                                        <td>{{ $latest->development_target ?? '-' }}</td>
                                        <td>{{ $dueText }}</td>
                                        <td>{{ $creatorName }}</td>
                                        <td>{{ $updatedText }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mid Year Review --}}
                    <div class="section-title">
                        <i class="bi bi-bar-chart-line-fill"></i>
                        Mid Year Review
                    </div>
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
                                        <td colspan="3" class="text-center text-muted">
                                            No data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- One Year Review --}}
                    <div class="section-title">
                        <i class="bi bi-calendar-check-fill"></i>
                        One Year Review
                    </div>
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
                                        <td colspan="2" class="text-center text-muted">
                                            No data available
                                        </td>
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
