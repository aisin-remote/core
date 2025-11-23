@php
    $employee = $icp->employee;
    $details = $icp->details ?? collect([]);
    $performanceData = $icp->performance_data ?? [];
    $formattedPerf = collect($header['performanceData'] ?? [])
        ->map(function ($score, $year) {
            return $year . ' = ' . $score;
        })
        ->implode(' | ');
@endphp

<style>
    /* Clean Professional Styles */
    .icp-modal-content {
        max-height: 85vh;
        overflow-y: auto;
        padding: 0.5rem;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .icp-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.25rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #3498db;
    }

    .icp-info-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #ecf0f1;
    }

    .icp-info-row:last-child {
        border-bottom: none;
    }

    .icp-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #5a6c7d;
    }

    .icp-value {
        font-size: 0.875rem;
        color: #2c3e50;
    }

    .icp-card {
        background: white;
        border: 1px solid #dce4ec;
        border-radius: 4px;
        margin-bottom: 1.5rem;
    }

    .icp-card-header {
        background: #f8f9fa;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #dce4ec;
    }

    .icp-card-body {
        padding: 1.25rem;
    }

    .icp-stage-card {
        border: 1px solid #dce4ec;
        border-radius: 4px;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .icp-stage-header {
        background: #34495e;
        color: white;
        padding: 0.875rem 1.25rem;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .icp-stage-body {
        padding: 1.25rem;
        background: #fafbfc;
    }

    .icp-field-group {
        margin-bottom: 1.25rem;
    }

    .icp-field-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #5a6c7d;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .icp-field-value {
        background: white;
        border: 1px solid #dce4ec;
        padding: 0.65rem 0.875rem;
        border-radius: 3px;
        font-size: 0.875rem;
        color: #2c3e50;
        min-height: 38px;
        display: flex;
        align-items: center;
    }

    .icp-subsection {
        background: white;
        border: 1px solid #e1e8ed;
        border-radius: 3px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .icp-subsection-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.875rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e1e8ed;
    }

    .icp-approval-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.875rem 1rem;
        background: white;
        border: 1px solid #dce4ec;
        border-radius: 3px;
        margin-bottom: 0.75rem;
    }

    .icp-approval-item:last-child {
        margin-bottom: 0;
    }

    .icp-approval-info {
        flex: 1;
    }

    .icp-approval-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .icp-approval-meta {
        font-size: 0.8rem;
        color: #7f8c8d;
    }

    .icp-status-badge {
        padding: 0.375rem 0.875rem;
        border-radius: 3px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .icp-status-done {
        background: #d4edda;
        color: #155724;
    }

    .icp-status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .icp-status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .icp-empty-state {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #95a5a6;
        background: #f8f9fa;
        border: 1px dashed #dce4ec;
        border-radius: 4px;
    }

    .icp-divider {
        height: 1px;
        background: #dce4ec;
        margin: 1.5rem 0;
    }

    /* Grid Layouts */
    .icp-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    .icp-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {

        .icp-grid-2,
        .icp-grid-3 {
            grid-template-columns: 1fr;
        }

        .icp-info-row {
            grid-template-columns: 1fr;
            gap: 0.25rem;
        }
    }

    /* Scrollbar */
    .icp-modal-content::-webkit-scrollbar {
        width: 6px;
    }

    .icp-modal-content::-webkit-scrollbar-track {
        background: #ecf0f1;
    }

    .icp-modal-content::-webkit-scrollbar-thumb {
        background: #95a5a6;
        border-radius: 3px;
    }

    .icp-modal-content::-webkit-scrollbar-thumb:hover {
        background: #7f8c8d;
    }
</style>

<div class="icp-modal-content">
    {{-- Header Information --}}
    <div class="icp-card">
        <div class="icp-card-header">
            <h5 class="mb-0 fw-bold" style="color: #2c3e50;">Individual Career Plan</h5>
        </div>

        <div class="icp-card-body">
            <div class="icp-grid-2">
                {{-- Left Column --}}
                <div>
                    <div class="icp-info-row">
                        <div class="icp-label">Employee Name</div>
                        <div class="icp-value">{{ $employee->name ?? '-' }}</div>
                    </div>

                    <div class="icp-info-row">
                        <div class="icp-label">Company</div>
                        <div class="icp-value">{{ $employee->company_name ?? '-' }}</div>
                    </div>

                    <div class="icp-info-row">
                        <div class="icp-label">Job Title</div>
                        <div class="icp-value">{{ $employee->position ?? '-' }}</div>
                    </div>

                    <div class="icp-info-row">
                        <div class="icp-label">Level / Grade</div>
                        <div class="icp-value">{{ $employee->grade ?? '-' }}</div>
                    </div>

                    <div class="icp-info-row">
                        <div class="icp-label">Entry Date</div>
                        <div class="icp-value">{{ $employee->formatted_date ?? '-' }}</div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div>
                    <div class="icp-info-row">
                        <div class="icp-label">Performance</div>
                        <div class="icp-value">{{ $formattedPerf ?: '-' }}</div>
                    </div>

                    <div class="icp-info-row">
                        <div class="icp-label">Assessment</div>
                        <div class="icp-value">{{ $icp->ass_centre_grade ?? '-' }}</div>
                    </div>

                    <div class="icp-info-row">
                        <div class="icp-label">Readiness</div>
                        <div class="icp-value">{{ $icp->readiness ?? '-' }}</div>
                    </div>

                    <div class="icp-info-row">
                        <div class="icp-label">Date of Birth</div>
                        <div class="icp-value">{{ $employee->formatted_birth ?? '-' }}</div>
                    </div>

                    <div class="icp-info-row">
                        <div class="icp-label">Education</div>
                        <div class="icp-value">
                            @php
                                $edu = $header['edu'] ?? null;
                            @endphp
                            {{ implode(' / ', array_filter([$edu->educational_level ?? '', $edu->major ?? '', $edu->institute ?? ''])) ?: '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="icp-divider"></div>

            {{-- Career Planning --}}
            <div class="icp-field-group">
                <div class="icp-field-label">Career Aspiration</div>
                <div class="icp-field-value" style="min-height: 70px; align-items: flex-start;">
                    {{ $icp->aspiration ?? '-' }}
                </div>
            </div>

            <div class="icp-grid-2">
                <div class="icp-field-group">
                    <div class="icp-field-label">Career Target</div>
                    <div class="icp-field-value">
                        {{ $icp->career_target ?? '-' }}
                    </div>
                </div>

                <div class="icp-field-group">
                    <div class="icp-field-label">Target Date</div>
                    <div class="icp-field-value">
                        {{ $icp->date ? $icp->date->format('d-m-Y') : '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Development Stages --}}
    <div class="icp-card">
        <div class="icp-card-header">
            <h5 class="mb-0 fw-bold" style="color: #2c3e50;">Development Stages</h5>
        </div>

        <div class="icp-card-body">
            @if ($details->isEmpty())
                <div class="icp-empty-state">
                    <p class="mb-0">No development stages available</p>
                </div>
            @else
                @foreach ($details as $index => $detail)
                    <div class="icp-stage-card">
                        <div class="icp-stage-header">
                            Stage Tahun {{ $detail->plan_year ?? $index + 1 }}
                        </div>

                        <div class="icp-stage-body">
                            {{-- Position Information --}}
                            <div class="icp-grid-3 mb-3">
                                <div>
                                    <div class="icp-field-label">Job Function</div>
                                    <div class="icp-field-value">
                                        {{ $detail->job_function ?? '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="icp-field-label">Position</div>
                                    <div class="icp-field-value">
                                        {{ $detail->position ?? '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="icp-field-label">Level</div>
                                    <div class="icp-field-value">
                                        {{ $detail->level ?? '-' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Technical Skills --}}
                            <div class="icp-subsection">
                                <div class="icp-subsection-title">Details</div>
                                <div class="icp-grid-3">
                                    <div>
                                        <div class="icp-field-label">Current Technical</div>
                                        <div class="icp-field-value">
                                            {{ $detail->current_technical ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div class="icp-field-label">Required Technical</div>
                                        <div class="icp-field-value">
                                            {{ $detail->required_technical ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div class="icp-field-label">Development Technical</div>
                                        <div class="icp-field-value">
                                            {{ $detail->development_technical ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="icp-grid-3 mt-2">
                                    <div>
                                        <div class="icp-field-label">Current</div>
                                        <div class="icp-field-value">
                                            {{ $detail->current_nontechnical ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div class="icp-field-label">Required</div>
                                        <div class="icp-field-value">
                                            {{ $detail->required_nontechnical ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div class="icp-field-label">Development Plan</div>
                                        <div class="icp-field-value">
                                            {{ $detail->development_nontechnical ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Approval Status --}}
    @if ($icp->steps && $icp->steps->isNotEmpty())
        <div class="icp-card">
            <div class="icp-card-header">
                <h5 class="mb-0 fw-bold" style="color: #2c3e50;">Approval Status</h5>
            </div>

            <div class="icp-card-body">
                @foreach ($icp->steps as $step)
                    <div class="icp-approval-item">
                        <div class="icp-approval-info">
                            <div class="icp-approval-title">{{ $step->label }}</div>
                            <div class="icp-approval-meta">
                                {{ ucfirst($step->role) }} • {{ ucfirst($step->type) }}
                                @if ($step->acted_at)
                                    • {{ \Carbon\Carbon::parse($step->acted_at)->format('d M Y, H:i') }}
                                @endif
                            </div>
                        </div>

                        <div>
                            @if ($step->status === 'done')
                                <span class="icp-status-badge icp-status-done">Done</span>
                            @elseif($step->status === 'pending')
                                <span class="icp-status-badge icp-status-pending">Pending</span>
                            @else
                                <span class="icp-status-badge icp-status-rejected">{{ ucfirst($step->status) }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
