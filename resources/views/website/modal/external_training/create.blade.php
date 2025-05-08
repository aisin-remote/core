<!-- Modal Tambah External Training -->
<div class="modal fade" id="addExternalTrainingModal" tabindex="-1" aria-labelledby="addExternalTrainingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add External Training</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('external_training.store') }}" method="POST">
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
                        <label class="form-label">Vendor</label>
                        <input type="text" name="vendor" class="form-control" required>
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
