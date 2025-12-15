@foreach ($alcs as $id => $title)
    @if (isset($assessment->employee))
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

                        {{-- Weakness Section --}}
                        <div class="col-lg-12 mb-10">
                            <label class="fs-5 fw-bold form-label mb-4">
                                Description & Suggestion Development in {{ $title }}
                            </label>
                            <div class="border p-4 rounded bg-light mb-5"
                                style="max-height: 400px; overflow-y: auto;">
                                <h6 class="fw-bold mb-">Description</h6>
                                <p class="mb-5">
                                    {{ !empty($weakness?->weakness) ? $weakness->weakness : (!empty($strength?->strength) ? $strength->strength : '-') }}
                                </p>

                                <h6 class="fw-bold mb-2">Suggestion Development</h6>
                                <p>
                                    {{ $weaknessDetail?->suggestion_development ?? ($weakness?->suggestion_development ?? '-') }}
                                </p>
                            </div>

                            {{-- Countdown Text --}}
                            <div id="countdownText_{{ $assessment->id }}_{{ $id }}"
                                class="text-dark text-center mb-2">
                                Please wait <span class="countdown-seconds">for a</span> seconds...
                            </div>

                            {{-- Checkbox --}}
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
                                <label class="fs-5 fw-bold form-label mb-2">
                                    <span class="required">Category</span>
                                </label>
                                <select id="category_select_{{ $assessmentId?->id }}_{{ $id }}"
                                    name="category" class="form-select form-select-lg fw-semibold"
                                    data-control="select2"
                                    data-placeholder="Select categories..."
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
                                <label class="fs-5 fw-bold form-label mb-2">
                                    <span class="required">Development Program</span>
                                </label>
                                <select id="program_select_{{ $assessmentId?->id }}_{{ $id }}"
                                    name="development_program"
                                    class="form-select form-select-lg fw-semibold"
                                    data-control="select2"
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
                                    class="fs-5 fw-bold form-label mb-2 required">
                                    Development Target
                                </label>
                                <textarea id="target_{{ $assessmentId?->id }}_{{ $id }}"
                                    name="development_target"
                                    class="form-control">{{ isset($idp) ? $idp->development_target : '' }}</textarea>
                            </div>

                            <div class="col-lg-12 fv-row mb-5">
                                <label for="due_date_{{ $assessmentId?->id }}_{{ $id }}"
                                    class="fs-5 fw-bold form-label mb-2 required">
                                    Due Date
                                </label>
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
                                    <label class="fs-5 fw-bold form-label mb-2">Comment History</label>
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
                                    data-hav="{{ $weaknessDetail?->id }}"
                                    data-alc="{{ $id }}"
                                    disabled>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true"></span>
                                    <span class="btn-text">Submit</span>
                                </button>
                            </div>
                        </div> {{-- end .additional-content --}}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
