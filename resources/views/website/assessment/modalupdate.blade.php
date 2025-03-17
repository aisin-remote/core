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
                    {{-- <div id="update-strength-container"></div> --}}
                    <div id="update-strengths-wrapper"></div>

                    <div class="section-title">Weakness</div>
                    {{-- <div id="update-weakness-container"></div> --}}
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
    document.addEventListener("DOMContentLoaded", function() {
        const updateAssessmentButtons = document.querySelectorAll(".updateAssessment");

        function updateDescriptionName(selectElement, type) {
            let card = selectElement.closest('.assessment-card');
            let textarea = card.querySelector(`.${type}-textarea`);
            let alcId = selectElement.value;

            if (alcId) {
                textarea.setAttribute('name', `${type}[${alcId}]`);
            } else {
                textarea.removeAttribute('name');
            }
        }

        function addAssessmentCard(type, containerClass) {
            let container = document.querySelector(`.${containerClass}`);
            let templateCard = document.querySelector(`.${type}-card`);
            let newCard = templateCard.cloneNode(true);

            let newDropdown = newCard.querySelector('.alc-dropdown');
            let newTextarea = newCard.querySelector(`.${type}-textarea`);

            newDropdown.value = '';
            newTextarea.value = '';
            newTextarea.removeAttribute('name');

            newDropdown.addEventListener('change', function() {
                updateDescriptionName(newDropdown, type);
            });

            let buttonContainer = newCard.querySelector('.d-flex');

            buttonContainer.innerHTML = `
        <button type="button" class="btn btn-danger btn-sm remove-card me-2">Hapus</button>
        <button type="button" class="btn btn-success btn-sm add-assessment" data-type="${type}">Tambah ${type.charAt(0).toUpperCase() + type.slice(1)}</button>
    `;

            newCard.querySelector('.remove-card').addEventListener('click', function() {
                newCard.remove();
            });

            newCard.querySelector('.add-assessment').addEventListener('click', function() {
                addAssessmentCard(type, containerClass);
            });

            container.appendChild(newCard);
        }

        document.querySelectorAll('.alc-dropdown').forEach(dropdown => {
            let type = dropdown.closest('.assessment-card').classList.contains('strength-card') ?
                'strength' : 'weakness';
            dropdown.addEventListener('change', function() {
                updateDescriptionName(this, type);
            });
        });

        function attachAddAssessmentListeners() {
            document.querySelectorAll('.add-assessment').forEach(button => {
                button.addEventListener('click', function() {
                    let type = this.getAttribute('data-type');
                    let containerClass = type === 'strength' ? 'update-strength-container' :
                        'update-weakness-container';
                    addAssessmentCard(type, containerClass);
                });
            });
        }

        attachAddAssessmentListeners();

        updateAssessmentButtons.forEach(button => {
            button.addEventListener("click", function() {
                const id = this.dataset.id;
                const employeeId = this.dataset.employeeId;
                const date = this.dataset.date;
                const upload = this.dataset.upload;
                const scores = JSON.parse(this.dataset.scores);
                const alcs = JSON.parse(this.dataset.alcs);
                const alcNames = JSON.parse(this.dataset.alc_name);
                const strengths = JSON.parse(this.dataset.strengths);
                const weaknesses = JSON.parse(this.dataset.weaknesses);

                document.getElementById("update_assessment_id").value = id;
                document.getElementById("update_employee_id").value = employeeId;
                document.getElementById("update_date").value = date;
                document.getElementById("update-upload-info").textContent = upload ?
                    `File: ${upload}` : '';

                alcs.forEach((alcId, index) => {
                    const score = scores[index];
                    const radio = document.getElementById(
                        `update_score_${alcId}_${score}`);
                    if (radio) {
                        radio.checked = true;
                    }
                });

                // const strengthContainer = document.getElementById("update-strength-container");
                const strengthContainer = document.getElementById("update-strengths-wrapper");
                strengthContainer.innerHTML = '';
                strengths.forEach((strength, idx) => {
                    if (strength) {
                        strengthContainer.innerHTML += `
                        <div class="card p-3 mb-3">
                            <label><strong>${alcNames[idx]}</strong></label>
                            <textarea name="strength[${alcs[idx]}]" class="form-control">${strength}</textarea>
                        </div>
                    `;
                    }
                });

                strengthContainer.innerHTML += `
    <div id="strength-container">
        <div class="assessment-card strength-card card p-3 mb-3">
            <div class="mb-3">
                <select class="form-control alc-dropdown" name="alc_ids[]">
                    <option value="">Pilih ALC</option>
                    @foreach ($alcs as $alc)
                        <option value="{{ $alc->id }}">{{ $alc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Strength</label>
                <textarea class="form-control strength-textarea" name="strength[]" rows="2"></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-success btn-sm add-assessment" data-type="strength">Tambah Strength</button>
            </div>
        </div>
    </div>
`;

                // const weaknessContainer = document.getElementById("update-weakness-container");
                const weaknessContainer = document.getElementById("update-weaknesses-wrapper");
                weaknessContainer.innerHTML = '';
                weaknesses.forEach((weakness, idx) => {
                    if (weakness) {
                        weaknessContainer.innerHTML += `
                        <div class="card p-3 mb-3">
                            <label><strong>${alcNames[idx]}</strong></label>
                            <textarea name="weakness[${alcs[idx]}]" class="form-control">${weakness}</textarea>
                        </div>
                    `;
                    }
                });

                weaknessContainer.innerHTML += `
    <div id="weakness-container">
        <div class="assessment-card weakness-card card p-3 mb-3">
            <div class="mb-3">
                <select class="form-control alc-dropdown" name="alc_ids[]">
                    <option value="">Pilih ALC</option>
                    @foreach ($alcs as $alc)
                        <option value="{{ $alc->id }}">{{ $alc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Weakness</label>
                <textarea class="form-control weakness-textarea" name="weakness[]" rows="2"></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-success btn-sm add-assessment" data-type="weakness">Tambah Weakness</button>
            </div>
        </div>
    </div>
`;

                attachAddAssessmentListeners();

                const modal = new bootstrap.Modal(document.getElementById(
                    "updateAssessmentModal"));
                modal.show();
            });
        });
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
