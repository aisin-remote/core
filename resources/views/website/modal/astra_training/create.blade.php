<!-- Modal Tambah Astra Training -->
<div class="modal fade" id="addAstraTrainingModal" tabindex="-1" aria-labelledby="addAstraTrainingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Astra Training</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('astra_training.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="employee_id" value="{{ $employee_id }}">

                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" required placeholder="YYYY">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <input type="text" name="program" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ICT Score</label>
                        <input type="number" name="ict_score" class="form-control" required step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Project Score</label>
                        <input type="number" name="project_score" class="form-control" required step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Score</label>
                        <input type="number" name="total_score" class="form-control" required step="0.01">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
