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
                        <select name="position" class="form-control" required>
                            <option value="">-- Pilih Position --</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position }}"
                                    {{ $experience->position === $position ? 'selected' : '' }}>
                                    {{ $position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-control" required>
                            <option value="">-- Pilih Department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->name }}"
                                    {{ $experience->department === $department->name ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
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
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
