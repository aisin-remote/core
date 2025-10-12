<!-- Add Work Experience Modal -->
<div class="modal fade" id="addExperienceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Work Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('work-experience.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="employee_id" value="{{ $employee_id }}">

                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <select name="position" class="form-select select2-basic" required>
                            <option value="">-- Pilih Position --</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position }}"
                                    {{ old('position') === $position ? 'selected' : '' }}>
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
                            <option value=""></option> <!-- untuk placeholder Select2 -->
                            @foreach ($grouped as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach ($items as $opt)
                                        <option value="{{ $opt['value'] }}"
                                            {{ old('org_scope') === $opt['value'] ? 'selected' : '' }}>
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
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date">
                            <small class="text-muted">Kosongkan bila masih aktif.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
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
