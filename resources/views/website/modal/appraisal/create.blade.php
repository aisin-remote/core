<!-- Modal Tambah -->
<div class="modal fade" id="addAppraisalModal" tabindex="-1" aria-labelledby="addAppraisalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Appraisal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('appraisal.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="employee_id" value="{{ $employee_id }}">
                    <div class="mb-3">
                        <label class="form-label">Score</label>
                        <select name="score" class="form-control" required>
                            <option value="">-- Select Score --</option>
                            @foreach ($scores as $score)
                                <option value="{{ $score }}"
                                    {{ old('score', $experience->score ?? '') === $score ? 'selected' : '' }}>
                                    {{ $score }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
