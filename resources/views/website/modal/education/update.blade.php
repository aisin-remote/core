<div class="modal fade" id="editEducationModal{{ $education->id }}" tabindex="-1"
    aria-labelledby="editEducationModalLabel{{ $education->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEducationModalLabel{{ $education->id }}">Edit
                    Education History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('education.update', $education->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="employee_id" value="{{ $education->employee_id }}">

                <div class="modal-body">
                    <div class="col-lg-12 mb-3">
                        <label class="fs-5 fw-bold form-label mb-2">
                            <span class="required">Education Level</span>
                        </label>
                        <select name="level" class="form-select form-select-lg fw-semibold">
                            <option value="">Select Category</option>
                            <option value="SMK"
                                {{ old('level', $education->educational_level) == 'SMK' ? 'selected' : '' }}>SMK
                            </option>
                            <option value="D3"
                                {{ old('level', $education->educational_level) == 'D3' ? 'selected' : '' }}>D3</option>
                            <option value="D4"
                                {{ old('level', $education->educational_level) == 'D4' ? 'selected' : '' }}>D4</option>
                            <option value="S1"
                                {{ old('level', $education->educational_level) == 'S1' ? 'selected' : '' }}>S1</option>
                            <option value="S2"
                                {{ old('level', $education->educational_level) == 'S2' ? 'selected' : '' }}>S2</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Major</label>
                        <input type="text" name="major" class="form-control" value="{{ $education->major }}"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Institution</label>
                        <input type="text" name="institute" class="form-control" value="{{ $education->institute }}"
                            required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Start Year</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ $education->start_date ? \Illuminate\Support\Carbon::parse($education->start_date)->format('Y-m-d') : '' }}"
                                required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Year
                                (Optional)
                            </label>
                            <input type="date" name="end_date" class="form-control"
                                value="{{ $education->end_date ? \Illuminate\Support\Carbon::parse($education->end_date)->format('Y-m-d') : '' }}">
                        </div>
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
