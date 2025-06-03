<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Checksheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm" method="POST" action="{{ route('checksheet.store') }}">
                    @csrf
                    <div class="mb-3">
                    <label for="competency_id">Category (Competency)</label>
                    <select
                        id="competency_id"
                        name="competency_id"
                        class="form-select"
                        required
                    >
                        <option value="" disabled selected>-- Select Competency --</option>
                        @foreach($competencies as $comp)
                        <option value="{{ $comp->id }}">
                            {{ $comp->name }}
                        </option>
                        @endforeach
                    </select>
                    </div>

                    <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>                 

                    <div class="mb-3">
                        <label for="position">Position</label>
                        <select name="position" class="form-select"required>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const addModal = document.getElementById("addModal");
        const addForm = document.getElementById("addForm");

        // Event listener ketika modal ditutup
        addModal.addEventListener("hidden.bs.modal", function() {
            addForm.reset(); // Reset semua input field ke default
        });
    });
</script>
