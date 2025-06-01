<!-- Modal Update External Training -->
<div class="modal fade" id="editExternalTrainingModal{{ $externalTraining->id }}" tabindex="-1"
    aria-labelledby="editExternalTrainingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit External Training</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('external_training.update', $externalTraining->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="employee_id" value="{{ $externalTraining->employee_id }}">


                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <input type="text" name="program" class="form-control" required
                            value="{{ $externalTraining->program }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Start</label>
                        <input type="date" name="date_start" class="form-control" required
                            value="{{ \Carbon\Carbon::parse($externalTraining->date_start)->format('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date End</label>
                        <input type="date" name="date_end" class="form-control" required
                            value="{{ \Carbon\Carbon::parse($externalTraining->date_end)->format('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vendor</label>
                        <input type="text" name="vendor" class="form-control" required
                            value="{{ $externalTraining->vendor }}">
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
