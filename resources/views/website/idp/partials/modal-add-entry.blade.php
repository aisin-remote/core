<div class="modal fade" id="addEntryModal-{{ $assessment->employee->id }}" tabindex="-1"
    aria-labelledby="addEntryModalLabel-{{ $assessment->employee->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Add Development for {{ $assessment->employee->name }}
                </h5>
            </div>
            <div class="modal-body">
                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="developmentTabs-{{ $assessment->employee->id }}"
                    role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active"
                            id="midYear-tab-{{ $assessment->employee->id }}"
                            data-bs-toggle="tab"
                            data-bs-target="#midYear-{{ $assessment->employee->id }}"
                            type="button" role="tab">
                            Mid Year
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                            id="oneYear-tab-{{ $assessment->employee->id }}"
                            data-bs-toggle="tab"
                            data-bs-target="#oneYear-{{ $assessment->employee->id }}"
                            type="button" role="tab">
                            One Year
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    {{-- MID YEAR TAB --}}
                    <div class="tab-pane fade show active"
                        id="midYear-{{ $assessment->employee->id }}" role="tabpanel">
                        <form
                            action="{{ route('idp.storeMidYear', ['employee_id' => $assessment->employee->id]) }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="employee_id"
                                value="{{ $assessment->employee->id }}">
                            <input type="hidden" name="assessment_id"
                                value="{{ $assessment->id }}">

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
                                                <label class="form-label fw-bold">
                                                    Development Program
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="development_program[]"
                                                    value="{{ $program->recommendedProgramsMidYear[0]['program'] }}"
                                                    readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Date
                                                </label>
                                                <input type="text" class="form-control"
                                                    value="{{ $program->recommendedProgramsMidYear[0]['date'] ?? '-' }}"
                                                    readonly>
                                            </div>
                                            <div class="mb-3">
                                                <input type="hidden" name="idp_id[]"
                                                    value="{{ $program->idp[0]['id'] ?? '-' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Development Achievement
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="development_achievement[]"
                                                    placeholder="Enter achievement" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Next Action
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="next_action[]"
                                                    placeholder="Enter next action" required>
                                            </div>
                                            <hr>
                                        </div>
                                    @endforeach

                                    @if (!$hasMidYearData)
                                        <p class="text-center text-muted">
                                            No data available
                                        </p>
                                    @else
                                        <button type="submit"
                                            class="btn btn-primary">Save</button>
                                    @endif
                                @else
                                    <p class="text-center text-muted">
                                        No data available
                                    </p>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- ONE YEAR TAB --}}
                    <div class="tab-pane fade"
                        id="oneYear-{{ $assessment->employee->id }}"
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
                                                <label class="form-label fw-bold">
                                                    Development Program
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="development_program[]"
                                                    value="{{ $program->recommendedProgramsOneYear[0]['program'] }}"
                                                    readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Date
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="date[]"
                                                    value="{{ $program->recommendedProgramsOneYear[0]['date'] }}"
                                                    readonly>
                                            </div>
                                            <div class="mb-3">
                                                <input type="hidden" name="idp_id[]"
                                                    value="{{ $program->idp[0]->id ?? '-' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Evaluation Result
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="evaluation_result[]"
                                                    placeholder="Enter evaluation result"
                                                    required>
                                            </div>
                                            <hr>
                                        </div>
                                    @endforeach

                                    @if (!$hasData)
                                        <p class="text-center text-muted">
                                            No data available
                                        </p>
                                    @else
                                        <div class="d-flex justify-content-between mt-2">
                                            <button type="submit"
                                                class="btn btn-primary btn-sm">
                                                Save
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-center text-muted">
                                        No data available
                                    </p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
