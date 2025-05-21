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
                        <select id="edit_position" class="form-select" required>
                            <option value="">Select Position</option>
                            @foreach (['Director','GM','Manager','Coordinator','Section Head','Supervisor','Leader','JP','Operator'] as $pos)
                            <option value="{{ $pos }}">{{ $pos }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="edit_additional_fields" class="mb-3"></div>

                    <div class="mb-3">
                        <label for="edit_weight" class="form-label">Weight</label>
                        <input type="number" class="form-control" id="edit_weight" max="4" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_plan" class="form-label">Plan</label>
                        <input type="number" class="form-control" id="edit_plan" max="4" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Data PHP → JS
        window.subSections = @json($subSections);
        window.sections    = @json($sections);
        window.departments = @json($departments);
        window.divisions   = @json($divisions);
        window.plants      = @json($plants);

        const editForm     = document.getElementById("editForm");
        const posEditSel   = document.getElementById("edit_position");
        const editFields   = document.getElementById("edit_additional_fields");

        // helper untuk rebuild dropdown sesuai posisi
        function rebuildEditAdditional(pos, data) {
            let label, name, options;
            switch(pos) {
            case 'Operator': case 'JP': case 'Leader':
                label   = 'Sub Section'; name='sub_section_id'; options=data.subSections; break;
            case 'Supervisor': case 'Section Head':
                label   = 'Section';     name='section_id';     options=data.sections;    break;
            case 'Manager': case 'Coordinator':
                label   = 'Department';  name='department_id';  options=data.departments; break;
            case 'GM':
                label   = 'Division';    name='division_id';    options=data.divisions;   break;
            case 'Director':
                label   = 'Plant';       name='plant_id';       options=data.plants;      break;
            default:
                editFields.innerHTML=''; return;
            }
            editFields.innerHTML = `
            <label class="form-label">${label}</label>
            <select name="${name}" id="edit_${name}" class="form-select">
                <option value="">-- Select ${label} --</option>
                ${options.map(o => `<option value="${o.id}" ${data.current[name]==o.id?'selected':''}>${o.name}</option>`).join('')}
            </select>`;
        }

        // Fungsi untuk menampilkan data di modal edit
         document.addEventListener("click", async e => {
            if (!e.target.classList.contains("edit-btn")) return;
            const id = e.target.getAttribute("data-id");
            const res= await fetch(`/competencies/${id}/edit`, { headers:{Accept:"application/json"} });
            const d  = await res.json();

            // isi form dasar
            document.getElementById("edit_id").value = d.id;
            document.getElementById("edit_name").value = d.name;
            document.getElementById("edit_weight").value = d.weight;
            document.getElementById("edit_plan").value = d.plan;

            // build dropdown group & department statis
            const grp = document.getElementById("edit_group_competency_id");
            grp.innerHTML = '<option value="">Select Group</option>';
            d.all_groups.forEach(g=>
            grp.innerHTML += `<option value="${g.id}" ${g.id==d.group_competency_id?'selected':''}>${g.name}</option>`
            );

            // set pilihan posisi
            posEditSel.value = d.position;

            // panggil dynamic dropdown
            rebuildEditAdditional(d.position, {
            subSections: d.all_sub_sections,
            sections:    d.all_sections,
            departments: d.all_departments,
            divisions:   d.all_divisions,
            plants:      d.all_plants,
            current:     d
            });

            // lalu show modal
            new bootstrap.Modal(document.getElementById("editModal")).show();
        });

        // ketika user ubah posisi di edit modal, rebuild dropdown
        posEditSel.addEventListener("change", () => {
            rebuildEditAdditional(posEditSel.value, {
            subSections: window.subSections,
            sections:    window.sections,
            departments: window.departments,
            divisions:   window.divisions,
            plants:      window.plants,
            current:     { sub_section_id:null, section_id:null, department_id:null, division_id:null, plant_id:null }
            });
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
            let weight = document.getElementById("edit_weight").value;
            let plan = document.getElementById("edit_plan").value;

            console.log("Submitting Data:", {
                name,
                description,
                groupId,
                departmentId,
                position,
                weight,
                plan
            });

            if (!name || !groupId || !departmentId || !position || !weight || !plan) {
                Swal.fire("Error", "Please fill in all required fields!", "error");
                return;
            }

            let formData = new FormData();
            formData.append("name", name);
            formData.append("description", description);
            formData.append("group_competency_id", groupId);
            formData.append("department_id", departmentId);
            formData.append("position", position);
            formData.append("weight", weight);
            formData.append("plan", plan);
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
