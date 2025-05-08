<!-- Modal untuk Edit Pengalaman Kerja -->
<div class="modal fade" id="editExperienceModal{{ $experience->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Work Experience</h5>
                <button type="button" class="btn-close close-edit-modal" data-bs-dismiss="modal" aria-label="Close"
                    data-experience-id="{{ $experience->id }}"></button>
            </div>
            <div class="modal-body">
                <!-- Form Edit -->
                <form action="{{ route('work-experience.update', $experience->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" name="position" value="{{ $experience->position }}"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Company</label>
                        <input type="text" class="form-control" name="company" value="{{ $experience->company }}"
                            required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date"
                                value="{{ \Illuminate\Support\Carbon::parse($experience->start_date)->format('Y-m-d') }}"
                                required>

                        </div>
                        <div class="col-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date"
                                value="{{ $experience->end_date ? \Illuminate\Support\Carbon::parse($experience->end_date)->format('Y-m-d') : '' }}">

                        </div>
                    </div>
                    <div class="mb-10">
                        <label class="form-label">Job Description</label>
                        <textarea class="form-control" name="description" rows="3">{{ $experience->description }}</textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
