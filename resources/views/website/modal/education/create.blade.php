<!-- Add Education Modal -->
<div class="modal fade" id="addEducationModal" tabindex="-1" aria-labelledby="addEducationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEducationModalLabel">Add Education History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('education.store') }}" method="POST">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee_id }}">
                <div class="modal-body">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 fw-bold form-label mb-2">
                            <span class="required">Education Level</span>
                        </label>
                        <select name="level" aria-label="Select a Country" data-control="select2"
                            data-placeholder="Select categories..." class="form-select form-select-lg fw-semibold">
                            <option value="">Select Category</option>
                            <option data-kt-flag="flags/afghanistan.svg" value="SMK">SMK</option>
                            <option data-kt-flag="flags/afghanistan.svg" value="D3">D3</option>
                            <option data-kt-flag="flags/aland-islands.svg" value="S1">S1</option>
                            <option data-kt-flag="flags/albania.svg" value="S2">S2</option>

                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Major</label>
                        <input type="text" name="major" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Institute</label>
                        <input type="text" name="institute" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Year</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Year (Optional)</label>
                        <input type="date" name="end_date" class="form-control">
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
