<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Competency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit_id">

                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_group_competency_id" class="form-label">Group</label>
                        <select class="form-control" id="edit_group_competency_id">
                            <option value="">Select Group</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_department_id" class="form-label">Department</label>
                        <select class="form-control" id="edit_department_id">
                            <option value="">Select Department</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_position" class="form-label">Position</label>
                        <select class="form-control" id="edit_position">
                            <option value="">Select Position</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const editForm = document.getElementById("editForm");

        // Fungsi untuk menampilkan data di modal edit
        document.addEventListener("click", async function(event) {
            if (event.target.classList.contains("edit-btn")) {
                let competencyId = event.target.getAttribute("data-id");

                try {
                    let response = await fetch(`/competencies/${competencyId}/edit`, {
                        headers: {
                            "Accept": "application/json"
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    let data = await response.json();

                    document.getElementById("edit_id").value = data.id;
                    document.getElementById("edit_name").value = data.name;
                    document.getElementById("edit_description").value = data.description;

                    let groupSelect = document.getElementById("edit_group_competency_id");
                    let departmentSelect = document.getElementById("edit_department_id");
                    let positionSelect = document.getElementById("edit_position");

                    groupSelect.innerHTML = '<option value="">Select Group</option>';
                    data.all_groups.forEach(group => {
                        groupSelect.innerHTML +=
                            `<option value="${group.id}" ${group.id == data.group_competency_id ? "selected" : ""}>${group.name}</option>`;
                    });

                    departmentSelect.innerHTML = '<option value="">Select Department</option>';
                    data.all_departments.forEach(department => {
                        departmentSelect.innerHTML +=
                            `<option value="${department.id}" ${department.id == data.department_id ? "selected" : ""}>${department.name}</option>`;
                    });

                    positionSelect.innerHTML = '<option value="">Select Position</option>';
                    data.all_positions.forEach(pos => {
                        positionSelect.innerHTML +=
                            `<option value="${pos}" ${pos == data.position ? "selected" : ""}>${pos}</option>`;
                    });

                    new bootstrap.Modal(document.getElementById("editModal")).show();
                } catch (error) {
                    console.error("Error fetching data:", error);
                }
            }
        });

        // Handle submit form edit
        editForm.addEventListener("submit", async function(event) {
            event.preventDefault();

            let competencyId = document.getElementById("edit_id").value;
            let name = document.getElementById("edit_name").value.trim();
            let description = document.getElementById("edit_description").value.trim();
            let groupId = document.getElementById("edit_group_competency_id").value;
            let departmentId = document.getElementById("edit_department_id").value;
            let position = document.getElementById("edit_position").value;

            console.log("Submitting Data:", {
                name,
                description,
                groupId,
                departmentId,
                position
            });

            if (!name || !groupId || !departmentId || !position) {
                Swal.fire("Error", "Please fill in all required fields!", "error");
                return;
            }

            let formData = new FormData();
            formData.append("name", name);
            formData.append("description", description);
            formData.append("group_competency_id", groupId);
            formData.append("department_id", departmentId);
            formData.append("position", position);
            formData.append("_method", "PUT");

            try {
                let response = await fetch(`/competencies/${competencyId}`, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        "Accept": "application/json"
                    }
                });

                let data = await response.json();
                console.log("Response from server:", data);

                if (response.ok) {
                    Swal.fire("Updated!", data.message, "success");
                    location.reload();
                } else {
                    throw new Error(data.details || "Failed to update competency.");
                }
            } catch (error) {
                console.error("Update error:", error);
                Swal.fire("Error", error.message, "error");
            }
        });

    });
</script>
