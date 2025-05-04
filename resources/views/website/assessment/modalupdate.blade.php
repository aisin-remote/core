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
                        <label for="update_description" class="form-label">Description Assessment</label>
                        <input type="text" class="form-control" id="update_description" name="description" required>
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
                                                    name="scores[{{ $alc->id }}]" id="update_score_{{ $alc->id }}_{{ $i }}"
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
                    <div id="update-strengths-wrapper"></div>

                    <div class="section-title">Weakness</div>
                    <div id="update-weaknesses-wrapper"></div>

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
    document.addEventListener("DOMContentLoaded", function () {
        const updateForm = document.getElementById("updateAssessmentForm");

        // Event listener untuk form submit
        updateForm.addEventListener("submit", function (event) {
            event.preventDefault(); // Mencegah halaman reload

            let formData = new FormData(updateForm); // Ambil semua data form

            fetch("/assessment/update", {
                method: "POST",
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) { // Jika update berhasil
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Assessment berhasil diperbarui!",
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            $("#updateAssessmentModal").modal('hide');
                            $("#detailAssessmentModal").modal('hide');

                            $(".modal-backdrop").remove();
                            $("body").removeClass("modal-open");
                        });
                    } else {
                        throw new Error("Gagal memperbarui assessment. Silakan coba lagi.");
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: "Gagal!",
                        text: "Terjadi kesalahan saat memperbarui assessment. " + error
                            .message,
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                });
        });

        // Load data ke modal saat tombol update diklik
        document.querySelectorAll(".updateAssessment").forEach(button => {
            button.addEventListener("click", function () {
                const id = this.dataset.id;
                const employeeId = this.dataset.employeeId;
                const date = this.dataset.date;
                const description = this.dataset.description;
                const upload = this.dataset.upload;
                const scores = JSON.parse(this.dataset.scores);
                const alcs = JSON.parse(this.dataset.alcs);
                const strengths = JSON.parse(this.dataset.strengths);
                const weaknesses = JSON.parse(this.dataset.weaknesses);

                document.getElementById("update_assessment_id").value = id;
                document.getElementById("update_employee_id").value = employeeId;
                document.getElementById("update_date").value = date;
                document.getElementById("update_description").value = description;
                document.getElementById("update-upload-info").textContent = upload ?
                    `File: ${upload}` : "";

                // Set nilai radio button untuk scores
                alcs.forEach((alcId, index) => {
                    const score = scores[index];
                    const radio = document.getElementById(
                        `update_score_${alcId}_${score}`);
                    if (radio) {
                        radio.checked = true;
                    }
                });

                // Mengisi Strengths
                const strengthContainer = document.getElementById("update-strengths-wrapper");
                strengthContainer.innerHTML = "";
                strengths.forEach((strength, idx) => {
                    if (strength) {
                        addAssessmentCard("strength", "update-strengths-wrapper", alcs[
                            idx], strength);
                    }
                });

                if (strengths.length === 0) {
                    addAssessmentCard("strength", "update-strengths-wrapper");
                }

                // Mengisi Weaknesses
                const weaknessContainer = document.getElementById("update-weaknesses-wrapper");
                weaknessContainer.innerHTML = "";
                weaknesses.forEach((weakness, idx) => {
                    if (weakness) {
                        addAssessmentCard("weakness", "update-weaknesses-wrapper", alcs[
                            idx], weakness);
                    }
                });

                if (weaknesses.length === 0) {
                    addAssessmentCard("weakness", "update-weaknesses-wrapper");
                }

                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById(
                    "updateAssessmentModal"));
                modal.show();
            });
        });

        function addAssessmentCard(type, containerId, selectedAlc = "", descriptions = "", alcName = "") {
            let container = document.getElementById(containerId);
            let templateCard = document.createElement("div");
            templateCard.classList.add("card", "p-3", "mb-3", "assessment-card", `${type}-card`);

            // Tambahkan ID sesuai ALC
            if (selectedAlc) {
                templateCard.setAttribute("id", `assessment_card_${selectedAlc}`);
            }

            templateCard.innerHTML = `
            <div class="mb-3">
                <label>ALC</label>
                <select class="form-control alc-dropdown" name="${type}_alc_ids[]" required>
                    <option value="">Pilih ALC</option>
                    @foreach ($alcs as $alc)
                        <option value="{{ $alc->id }}" ${selectedAlc == "{{ $alc->id }}" ? "selected" : ""}>
                            {{ $alc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea class="form-control ${type}-textarea" name="${type}[${selectedAlc}]" rows="2">${descriptions}</textarea>
            </div>
        `;

            let selectElement = templateCard.querySelector(".alc-dropdown");
            selectElement.addEventListener("change", function () {
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
                        removeButton.classList.add("btn", "btn-danger", "btn-sm", "remove-card",
                            "me-2");
                        removeButton.textContent = "Hapus";

                        removeButton.addEventListener("click", function () {
                            card.remove();
                            updateRemoveButtons(container);
                        });

                        buttonGroup.insertBefore(removeButton, buttonGroup.firstChild);
                    }
                });
            }
        }

        $(document).on("change", ".update-score", function () {
            const radio = $(this);
            const idParts = radio.attr("id").split("_"); // e.g. update_score_3_4
            const alcId = idParts[2];
            const score = parseInt(idParts[3]);

            const newType = score >= 3 ? "strength" : "weakness";
            const oldType = score >= 3 ? "weakness" : "strength";

            let card = $(`#assessment_card_${alcId}`);
            const newWrapper = $(`#update-${newType}s-wrapper`);

            if (card.length === 0) {
                // Card belum dibuat → buat ke wrapper yang sesuai
                addAssessmentCard(newType, `update-${newType}s-wrapper`, alcId);
                card = $(`#assessment_card_${alcId}`);
            } else {
                // Card sudah ada → pindahkan dari wrapper lama ke wrapper baru
                const detachedCard = $(`#update-${oldType}s-wrapper #assessment_card_${alcId}`).detach();
                newWrapper.append(detachedCard);
                card = detachedCard;
            }

            // Update name attributes agar sesuai dengan posisi baru
            const textarea = card.find("textarea");
            const select = card.find("select");

            textarea.attr("name", `${newType}[${alcId}]`);
            select.attr("name", `${newType}_alc_ids[]`);

            // Update class supaya style sesuai
            card.removeClass("strength-card weakness-card").addClass(`${newType}-card`);
        });



        function updateDropdownOptions(card, type) {
            // Memastikan bahwa dropdown ALC hanya bisa memilih ALC yang sesuai dengan kategori strength/weakness
            const select = card.find("select");
            const alcId = select.val();

            // Contoh: Jika ALC untuk strength, dropdown hanya menampilkan ALC yang relevan
            select.find("option").each(function () {
                const option = $(this);
                if (type === "strength" && option.val() !== alcId) {
                    option.prop("disabled", false); // ALC yang boleh dipilih
                } else if (type === "weakness" && option.val() !== alcId) {
                    option.prop("disabled", false); // ALC yang boleh dipilih
                } else {
                    option.prop("disabled", true); // ALC yang tidak relevan, disable
                }
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