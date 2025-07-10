<!-- Modal untuk Create Promotion -->
<div class="modal fade" id="createPromotionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Promotion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form Create -->
                <form action="{{ route('promotion.store') }}" method="POST">
                    @csrf

                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"> {{-- pastikan variabel $employee tersedia --}}

                    <div class="mb-3">
                        <label class="form-label">Previous Grade</label>
                        <select name="previous_grade" class="form-control" required>
                            <option value="">-- Pilih Grade --</option>
                            @foreach ($grade as $g)
                                <option value="{{ $g->aisin_grade }}"
                                    {{ old('previous_grade', $experience->previous_grade ?? '') === $g->aisin_grade ? 'selected' : '' }}>
                                    {{ $g->aisin_grade }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Previous Position</label>
                        <select name="previous_position" class="form-control" required>
                            <option value="">-- Pilih Position --</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position }}"
                                    {{ old('previous_position', $experience->previous_position ?? '') === $position ? 'selected' : '' }}>
                                    {{ $position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Grade</label>
                        <select name="current_grade" class="form-control" required>
                            <option value="">-- Pilih Grade --</option>
                            @foreach ($grade as $g)
                                <option value="{{ $g->aisin_grade }}"
                                    {{ old('current_grade', $experience->current_grade ?? '') === $g->aisin_grade ? 'selected' : '' }}>
                                    {{ $g->aisin_grade }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Position</label>
                        <select name="current_position" class="form-control" required>
                            <option value="">-- Pilih Position --</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position }}"
                                    {{ old('current_position', $experience->current_position ?? '') === $position ? 'selected' : '' }}>
                                    {{ $position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Promotion Date</label>
                        <input type="date" class="form-control" name="last_promotion_date" required>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Add Promotion</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
