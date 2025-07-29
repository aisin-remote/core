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
                        <select class="form-control" id="update_employee_id" name="employee_id" disabled>
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
                        <label for="update_target" class="form-label">Target Position</label>
                        <select id="update_target" name="target" data-placeholder="Select Position..."
                            class="form-select form-select-lg fw-semibold" required>
                            <option value="">Select Position</option>
                            @php
                                $positions = [
                                    'GM' => 'General Manager',
                                    'Act GM' => 'Act General Manager',
                                    'Manager' => 'Manager',
                                    'Act Manager' => 'Act Manager',
                                    'Coordinator' => 'Coordinator',
                                    'Act Coordinator' => 'Act Coordinator',
                                    'Section Head' => 'Section Head',
                                    'Act Section Head' => 'Act Section Head',
                                    'Supervisor' => 'Supervisor',
                                    'Act Supervisor' => 'Act Supervisor',
                                    'Act Leader' => 'Act Leader',
                                    'Leader' => 'Leader',
                                    'Staff' => 'Staff',
                                    'Act JP' => 'Act JP',
                                    'Operator' => 'Operator',
                                    'Direktur' => 'Direktur',
                                ];
                            @endphp
                            @foreach ($positions as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('position', $employee->position ?? '') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('position')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4" style="display: none;">
                        <label for="update_description" class="form-label">Description Assessment</label>
                        <input type="hidden" class="form-control" id="update_description" name="description" required>
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
                                                    value="{{ $i }}">
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
                    <div id="update-strengths-wrapper"></div>

                    <div class="section-title">Weakness</div>
                    <div id="update-weaknesses-wrapper"></div>

                    <div class="mb-4">
                        <label for="update_upload" class="form-label">Upload File Assessment (PDF)</label>
                        <input type="file" class="form-control" id="update_upload" name="upload"
                            accept=".pdf,.jpg,.png">
                        <small id="update-upload-info"></small>
                    </div>
                    <div class="mb-4">
                        <label for="update_note" class="form-label">Note</label>
                        <textarea name="note" id="update_note" class="form-control" placeholder="Note..."></textarea>
                    </div>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const updateForm = document.getElementById("updateAssessmentForm");

        updateForm.addEventListener("submit", function(event) {
            event.preventDefault();

            const fileInput = document.getElementById("update_upload");
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;
                if (fileSize > 2 * 1024 * 1024) { // 2 MB limit
                    Swal.fire({
                        title: "Gagal!",
                        text: "Ukuran file maksimal 2 MB.",
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                    return; // stop submit
                }
            }

            let formData = new FormData(updateForm);

            fetch("/assessment/update", {
                    method: "POST",
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    }
                })
                .then(async response => {
                    if (!response.ok) {
                        // Kalau response error, ambil pesan validasi Laravel
                        const errorData = await response.json();
                        let errorMsg = "Terjadi kesalahan.";
                        if (errorData.errors) {
                            errorMsg = Object.values(errorData.errors).flat().join("\n");
                        } else if (errorData.message) {
                            errorMsg = errorData.message;
                        }
                        throw new Error(errorMsg);
                    }
                    return response.json();
                })
                .then(data => {
                    Swal.fire({
                        title: "Berhasil!",
                        text: data.message || "Assessment berhasil diperbarui!",
                        icon: "success",
                        confirmButtonText: "OK"
                    }).then(() => {
                        $("#updateAssessmentModal").modal('hide');
                        $("#detailAssessmentModal").modal('hide');
                        $(".modal-backdrop").remove();
                        $("body").removeClass("modal-open");
                        // Bisa tambah reload data atau refresh halaman jika perlu
                        location.reload();
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: "Gagal!",
                        text: error.message,
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                });
        });



        // Load data ke modal saat tombol update diklik
        function addAssessmentCard(type, containerId, selectedAlc = "", descriptions = "", alcName = "",
            suggestion = "") {
            console.log(`addAssessmentCard called with type=${type}, alcId=${selectedAlc}`);
            console.log({
                descriptions,
                alcName,
                suggestion
            });

            let container = document.getElementById(containerId);
            let templateCard = document.createElement("div");
            templateCard.classList.add("card", "p-3", "mb-3", "assessment-card", `${type}-card`);

            if (selectedAlc) {
                templateCard.setAttribute("id", `assessment_card_${selectedAlc}`);
            }

            // Build options HTML dynamically
            let optionsHtml = `<option value="">Pilih ALC</option>`;
            @foreach ($alcs as $alc)
                optionsHtml +=
                    `<option value="{{ $alc->id }}" ${selectedAlc == "{{ $alc->id }}" ? "selected" : ""}>{{ $alc->name }}</option>`;
            @endforeach

            templateCard.innerHTML = `
        <div class="mb-3">
            <label>ALC</label>
            <select class="form-control alc-dropdown" name="${type}_alc_ids[]">
                ${optionsHtml}
            </select>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea class="form-control ${type}-textarea" name="${type}[${selectedAlc}]" rows="2">${descriptions}</textarea>
        </div>
        <div class="mb-3">
            <label>Suggestion Development</label>
            <textarea class="form-control suggestion-textarea" name="suggestion_development[${selectedAlc}]" rows="2">${suggestion}</textarea>
        </div>
    `;

            let selectElement = templateCard.querySelector(".alc-dropdown");
            selectElement.addEventListener("change", function() {
                updateDescriptionName(selectElement, type);
                updateDropdownOptions();
            });

            container.appendChild(templateCard);
            updateDropdownOptions();
        }

        // Fungsi untuk memperbarui nama deskripsi berdasarkan ALC yang dipilih
        function updateDescriptionName(selectElement, type) {
            let card = selectElement.closest(".assessment-card");
            let textarea = card.querySelector(`.${type}-textarea`);
            let alcId = selectElement.value;

            if (alcId) {
                textarea.setAttribute("name", `${type}[${alcId}]`);
            } else {
                textarea.removeAttribute("name");
            }
        }


        function updateRemoveButtons(container) {
            let cards = container.querySelectorAll(".assessment-card");
            let removeButtons = container.querySelectorAll(".remove-card");

            removeButtons.forEach(button => button.remove());

            if (cards.length > 1) {
                cards.forEach((card, index) => {
                    if (index !== 0) {
                        let buttonGroup = card.querySelector(".button-group");
                        let removeButton = document.createElement("button");
                        removeButton.type = "button";
                        removeButton.classList.add("btn", "btn-danger", "btn-sm",
                            "remove-card",
                            "me-2");
                        removeButton.textContent = "Hapus";

                        removeButton.addEventListener("click", function() {
                            card.remove();
                            updateRemoveButtons(container);
                        });

                        buttonGroup.insertBefore(removeButton, buttonGroup.firstChild);
                    }
                });
            }
        }

        $(document).on("change", ".update-score", function() {
            const radio = $(this);
            const idParts = radio.attr("id").split("_");
            const alcId = idParts[2];
            const score = parseInt(idParts[3]);

            const newType = score >= 3 ? "strength" : "weakness";
            const oldType = score >= 3 ? "weakness" : "strength";

            const containerMap = {
                strength: "#update-strengths-wrapper",
                weakness: "#update-weaknesses-wrapper",
            };

            let card = $(`#assessment_card_${alcId}`);
            const newWrapper = $(containerMap[newType]);
            const oldWrapper = $(containerMap[oldType]);
            const oldCard = oldWrapper.find(`#assessment_card_${alcId}`);

            console.log(`ALC ID: ${alcId}, score: ${score}, oldType: ${oldType}, newType: ${newType}`);
            console.log("Old card found:", oldCard.length);
            console.log("New wrapper length:", newWrapper.length);
            console.log("Old wrapper length:", oldWrapper.length);

            let description = "";
            let suggestion = "";
            let alcName = "";

            if (oldCard.length) {
                description = oldCard.find(`textarea.${oldType}-textarea`).val() || "";
                suggestion = oldCard.find("textarea.suggestion-textarea").val() || "";
                alcName = oldCard.find("select.alc-dropdown").val() || "";
                console.log("Old card data:", {
                    description,
                    suggestion,
                    alcName
                });

                oldCard.remove();
            }

            if (card.length === 0) {
                console.log(
                    `[INFO] Card ALC ${alcId} not found in newType container, creating new in ${newType}`
                );
                addAssessmentCard(newType, containerMap[newType].substring(1), alcId, description,
                    alcName, suggestion);
                card = $(`#assessment_card_${alcId}`);
            } else {
                console.log(`[INFO] Moving ALC ${alcId} card from ${oldType} to ${newType}`);
                card.detach().appendTo(newWrapper);
            }

            // Update name attributes
            const textarea = card.find("textarea").first();
            const suggestionTextarea = card.find("textarea.suggestion-textarea");
            const select = card.find("select");

            textarea.attr("name", `${newType}[${alcId}]`);
            suggestionTextarea.attr("name", `suggestion_development[${alcId}]`);
            select.attr("name", `${newType}_alc_ids[]`);

            // Update card class
            card.removeClass("strength-card weakness-card").addClass(`${newType}-card`);

            updateDropdownOptions();
        });

        function updateDropdownOptions() {
            let selectedStrengths = new Set();
            let selectedWeaknesses = new Set();

            // Ambil ALC yang sudah dipilih di strength
            document.querySelectorAll("#update-strengths-wrapper .alc-dropdown").forEach(select => {
                if (select?.value) selectedStrengths.add(select.value);
            });

            // Ambil ALC yang sudah dipilih di weakness
            document.querySelectorAll("#update-weaknesses-wrapper .alc-dropdown").forEach(select => {
                if (select?.value) selectedWeaknesses.add(select.value);
            });

            // Update dropdown di strength
            document.querySelectorAll("#update-strengths-wrapper .alc-dropdown").forEach(select => {
                const currentValue = select.value;
                select.querySelectorAll("option").forEach(option => {
                    option.hidden = (option.value !== currentValue) &&
                        (selectedStrengths.has(option.value) || selectedWeaknesses.has(option
                            .value));
                });
            });

            // Update dropdown di weakness
            document.querySelectorAll("#update-weaknesses-wrapper .alc-dropdown").forEach(select => {
                const currentValue = select.value;
                select.querySelectorAll("option").forEach(option => {
                    option.hidden = (option.value !== currentValue) &&
                        (selectedStrengths.has(option.value) || selectedWeaknesses.has(option
                            .value));
                });
            });
        }


    });
</script>


@push('custom-css')
    <style>
        .section-title {
            font-size: 24px;
            /* Ukuran teks lebih besar */
            font-weight: bold;
            text-align: center;
            /* Pusatkan teks */
            padding: 15px 0;
            border-top: 3px solid #000;
            /* Garis atas sebagai pembatas */
            border-bottom: 3px solid #000;
            /* Garis bawah sebagai pembatas */
            margin: 20px 0;
            /* Jarak antara elemen */
        }
    </style>
@endpush
