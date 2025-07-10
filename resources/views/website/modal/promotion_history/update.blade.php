<!-- Modal untuk Edit Promotion -->
<div class="modal fade" id="editPromotionModal{{ $experience->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Promotion</h5>
                <button type="button" class="btn-close close-edit-modal" data-bs-dismiss="modal" aria-label="Close"
                    data-experience-id="{{ $experience->id }}"></button>
            </div>
            <div class="modal-body">
                <!-- Form Edit -->
                <form action="{{ route('promotion.update', $experience->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Previous Grade</label>
                        <select name="previous_grade" class="form-control" required>
                            <option value="">-- Pilih Grade --</option>
                            @foreach ($grade as $g)
                                <option value="{{ $g->aisin_grade }}"
                                    {{ $experience->previous_grade === $g->aisin_grade ? 'selected' : '' }}>
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
                                    {{ $experience->previous_position === $position ? 'selected' : '' }}>
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
                                    {{ $experience->current_grade === $g->aisin_grade ? 'selected' : '' }}>
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
                                    {{ $experience->current_position === $position ? 'selected' : '' }}>
                                    {{ $position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Promotion Date</label>
                        <input type="date" class="form-control" name="last_promotion_date"
                            value="{{ \Carbon\Carbon::parse($experience->last_promotion_date)->format('Y-m-d') }}"
                            required>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
