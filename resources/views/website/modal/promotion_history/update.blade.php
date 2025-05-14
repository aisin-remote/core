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
                        <input type="text" class="form-control" name="previous_grade"
                            value="{{ $experience->previous_grade }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Previous Position</label>
                        <input type="text" class="form-control" name="previous_position"
                            value="{{ $experience->previous_position }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Grade</label>
                        <input type="text" class="form-control" name="current_grade"
                            value="{{ $experience->current_grade }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Position</label>
                        <input type="text" class="form-control" name="current_position"
                            value="{{ $experience->current_position }}" required>
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
