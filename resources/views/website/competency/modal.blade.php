<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Competency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    @csrf
                    <div class="mb-3">
                        <label for="name">Competency Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="group_competency_id">Group Competency</label>
                        <select class="form-control" id="group_competency_id" name="group_competency_id" required>
                            <option value="Manager">Manager</option>
                            <option value="Coordinator">Coordinator</option>
                            <option value="Section Head">Section Head</option>
                            {{-- @foreach ($group as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach --}}
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="department_id">Department</label>
                        <select class="form-control" id="department_id" name="department_id" required>
                            <option value="" disabled selected>-- Select Department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="position">Position</label>
                        <select name="position" class="form-select">
                            <option value="">Select Position</option>
                            <option value="General Manager">General Manager</option>
                            <option value="Manager">Manager</option>
                            <option value="Coordinator">Coordinator</option>
                            <option value="Section Head">Section Head</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Act Leader">Act Leader</option>
                            <option value="Act JP">Act JP</option>
                            <option value="Operator">Operator</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
