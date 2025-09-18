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
                <form action="{{ route('work-experience.update', $experience->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <select name="position" class="form-select select2-basic" required>
                            <option value="">-- Pilih Position --</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position }}"
                                    {{ $experience->position === $position ? 'selected' : '' }}>
                                    {{ $position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @php $grouped = collect($allOptions)->groupBy('group'); @endphp

                    <div class="mb-3">
                        <label class="form-label">Organizational Scope</label>
                        <select name="org_scope" class="form-select select2-org-scope"
                            data-placeholder="Cari Plant/Division/Department/Section/Sub Section" required>
                            <option value=""></option>
                            @foreach ($grouped as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach ($items as $opt)
                                        <option value="{{ $opt['value'] }}"
                                            {{ old('org_scope', $experience->selected_org_scope ?? '') === $opt['value'] ? 'selected' : '' }}>
                                            {{ $opt['label'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <small class="text-muted">Prefix: [Plant], [Division], [Department], [Section], [Sub
                            Section].</small>
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
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
