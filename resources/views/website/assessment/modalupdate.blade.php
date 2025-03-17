<div class="modal fade" id="updateAssessmentModal" tabindex="-1" aria-labelledby="updateAssessmentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateAssessmentModalLabel">Update Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateAssessmentForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="update_assessment_id" name="assessment_id">

                    <div class="mb-4">
                        <label for="update_employee_id" class="form-label">Employee</label>
                        <select class="form-control" id="update_employee_id" name="employee_id" required>
                            <option value="">Pilih Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="update_date" class="form-label">Date Assessment</label>
                        <input type="date" class="form-control" id="update_date" name="date" required>
                    </div>

                    <div class="mb-4">
                        <div class="section-title">Assessment Scores</div>
                        @foreach ($alcs as $alc)
                            <div class="card p-3 mb-3">
                                <h6>{{ $alc->name }}</h6>
                                <input type="hidden" name="alc_ids[]" value="{{ $alc->id }}">
                                <div class="mb-2">
                                    <div class="d-flex gap-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <div class="form-check">
                                                <input class="form-check-input update-score" type="radio"
                                                    name="scores[{{ $alc->id }}]"
                                                    id="update_score_{{ $alc->id }}_{{ $i }}"
                                                    value="{{ $i }}" required>
                                                <label class="form-check-label"
                                                    for="update_score_{{ $alc->id }}_{{ $i }}">{{ $i }}</label>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="section-title">Strength</div>
                    <div id="update-strength-container"></div>

                    <div class="section-title">Weakness</div>
                    <div id="update-weakness-container"></div>

                    <div class="mb-4">
                        <label for="update_upload" class="form-label">Upload File Assessment (PDF, JPG, PNG)</label>
                        <input type="file" class="form-control" id="update_upload" name="upload"
                            accept=".pdf,.jpg,.png">
                        <small id="update-upload-info"></small>
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Cek apakah elemen yang diperlukan ada sebelum dijalankan
        let updateModal = document.getElementById("updateAssessmentModal");
        let updateForm = document.getElementById("updateAssessmentForm");

        if (!updateModal || !updateForm) {
            console.error("Modal atau form tidak ditemukan!");
            return;
        }

        // Function untuk membuka modal update
        function openUpdateModal(data) {
            let idField = document.getElementById("update_assessment_id");
            let employeeField = document.getElementById("update_employee_id");
            let dateField = document.getElementById("update_date");
            let uploadInfo = document.getElementById("update-upload-info");

            if (!idField || !employeeField || !dateField || !uploadInfo) {
                console.error("Salah satu elemen form tidak ditemukan!");
                return;
            }

            // Set nilai form modal
            idField.value = data.id || "";
            employeeField.value = data.employee_id || "";
            dateField.value = data.date || "";
            uploadInfo.textContent = data.upload ? `File: ${data.upload}` : "";

            // Load Scores
            document.querySelectorAll('.update-score').forEach(input => {
                let alcId = input.name.replace('scores[', '').replace(']', '');
                if (data.scores && data.scores[alcId] == input.value) {
                    input.checked = true;
                }
            });

            // Load Strengths
            let strengthContainer = document.getElementById("update-strength-container");
            if (strengthContainer) {
                strengthContainer.innerHTML = "";
                data.strengths.forEach((strength, index) => {
                    let strengthCard = createAssessmentCard(strength, "strength", index);
                    strengthContainer.appendChild(strengthCard);
                });
            }

            // Load Weaknesses
            let weaknessContainer = document.getElementById("update-weakness-container");
            if (weaknessContainer) {
                weaknessContainer.innerHTML = "";
                data.weaknesses.forEach((weakness, index) => {
                    let weaknessCard = createAssessmentCard(weakness, "weakness", index);
                    weaknessContainer.appendChild(weaknessCard);
                });
            }

            let modalInstance = new bootstrap.Modal(updateModal);
            modalInstance.show();
        }

        // Function untuk membuat card Strength/Weakness
        function createAssessmentCard(data, type, index) {
            let card = document.createElement("div");
            card.classList.add("card", "p-3", "mb-3");

            let alcOptions = data.alc_options.map(alc =>
                `<option value="${alc.id}" ${alc.id == data.alc_id ? 'selected' : ''}>${alc.name}</option>`
            ).join("");

            card.innerHTML = `
                <div class="mb-3">
                    <select class="form-control" name="${type}_ids[]" required>
                        <option value="">Pilih ALC</option>
                        ${alcOptions}
                    </select>
                </div>
                <div class="mb-3">
                    <label>${type.charAt(0).toUpperCase() + type.slice(1)}</label>
                    <textarea class="form-control" name="${type}[${index}]" rows="2">${data.description}</textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-danger btn-sm remove-card">Hapus</button>
                </div>
            `;

            let removeButton = card.querySelector(".remove-card");
            if (removeButton) {
                removeButton.addEventListener("click", function() {
                    card.remove();
                });
            }

            return card;
        }

        // Event listener untuk tombol edit
        document.querySelectorAll(".edit-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                let id = this.getAttribute("data-id");

                fetch(`/assessment/${employee_id}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Gagal mengambil data");
                        }
                        return response.json();
                    })
                    .then(data => openUpdateModal(data))
                    .catch(error => console.error("Error:", error));
            });
        });

        // Submit form update
        if (updateForm) {
            updateForm.addEventListener("submit", function(event) {
                event.preventDefault();

                let formData = new FormData(this);
                let id = document.getElementById("update_assessment_id")?.value;

                fetch(`/assessment/update/${id}`, {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Gagal memperbarui assessment");
                        }
                        return response.json();
                    })
                    .then(data => {
                        alert("Assessment berhasil diperbarui!");
                        location.reload();
                    })
                    .catch(error => console.error("Error:", error));
            });
        }

        // Perbaiki error DataTable
        if (typeof $.fn.DataTable === 'undefined') {
            console.error("DataTable belum di-load, pastikan file DataTables sudah dimasukkan.");
        } else {
            $("#yourTableId").DataTable();
        }
    });
</script>

