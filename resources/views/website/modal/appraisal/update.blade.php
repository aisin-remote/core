<div class="modal fade" id="editAppraisalModal{{ $appraisal->id }}" tabindex="-1"
    aria-labelledby="editAppraisalModalLabel{{ $appraisal->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Appraisal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('appraisal.update', $appraisal->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Score</label>
                        <input type="text" name="score" class="form-control" value="{{ $appraisal->score }}"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ isset($appraisal) ? \Illuminate\Support\Carbon::parse($appraisal->date)->format('Y-m-d') : old('date') }}"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3">
                    {{ $appraisal->description }}
                </textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
