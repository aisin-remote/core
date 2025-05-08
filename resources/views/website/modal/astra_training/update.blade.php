<!-- Modal Update Astra Training -->
<div class="modal fade" id="editAstraTrainingModal{{ $astraTraining->id }}" tabindex="-1"
    aria-labelledby="editAstraTrainingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Astra Training</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('astra_training.update', $astraTraining->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="employee_id" value="{{ $astraTraining->employee_id }}">

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="year" class="form-control" required
                            value="{{ \Carbon\Carbon::parse($astraTraining->date_end)->format('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <input type="text" name="program" class="form-control" required
                            value="{{ $astraTraining->program }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ICT Score</label>
                        <input type="number" name="ict_score" class="form-control" required step="0.01"
                            value="{{ $astraTraining->ict_score }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Project Score</label>
                        <input type="number" name="project_score" class="form-control" required step="0.01"
                            value="{{ $astraTraining->project_score }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Score</label>
                        <input type="number" name="total_score" class="form-control" required step="0.01"
                            value="{{ $astraTraining->total_score }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
