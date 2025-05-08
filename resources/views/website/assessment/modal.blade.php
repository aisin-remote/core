<style>
    .section-title {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        padding: 15px 0;
        border-top: 3px solid #000;
        border-bottom: 3px solid #000;
        margin: 20px 0;
    }
    .is-invalid {
    border-color: red !important;
}

</style>

<div class="modal fade" id="addAssessmentModal" tabindex="-1" aria-labelledby="addAssessmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssessmentModalLabel">Create Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assessmentForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="assessment_id" name="assessment_id">

                    <div class="mb-4">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select class="form-control" id="employee_id" name="employee_id" required>
                            <option value="">Pilih Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="date" class="form-label">Date Assessment</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description Assessment</label>
                        <textarea type="text" class="form-control" id="description" name="description" required></textarea>
                    </div>

                    <div class="mb-4">
                        <div class="section-title">Assessment Scores</div>
                        @foreach ($alcs as $alc)
                            <div class="card p-3 mb-3">
                                <h6>{{ $alc->name }}</h6>
                                <input type="hidden" name="alc_ids[]" value="{{ $alc->id }}">
                                <div class="mb-2">
                                    <div class="d-flex gap-10">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="scores[{{ $alc->id }}]"
                                                    id="score_{{ $alc->id }}_{{ $i }}"
                                                    value="{{ $i }}" required>
                                                <label class="form-check-label"
                                                    for="score_{{ $alc->id }}_{{ $i }}">{{ $i }}</label>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="section-title">Strength</div>
                    <div id="strength-container"></div>

                    <div class="section-title">Weakness</div>
                    <div id="weakness-container"></div>

                    <div class="mb-4">
                        <label for="upload" class="form-label">Upload File Assessment (PDF, JPG, PNG)</label>
                        <input type="file" class="form-control" id="upload" name="upload" accept=".pdf,.jpg,.png">
                    </div>

                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function updateDescriptionName(selectElement, type) {
            const card = selectElement.closest('.assessment-card');
            const textarea = card.querySelector(`.${type}-textarea`);
            const alcId = selectElement.value;

            if (alcId) {
                textarea.setAttribute('name', `${type}[${alcId}]`);
            } else {
                textarea.removeAttribute('name');
            }
        }

        function updateDropdownOptions() {
            const selectedStrengths = new Set();
            const selectedWeaknesses = new Set();

            document.querySelectorAll('#strength-container .alc-dropdown').forEach(select => {
                if (select.value) selectedStrengths.add(select.value);
            });

            document.querySelectorAll('#weakness-container .alc-dropdown').forEach(select => {
                if (select.value) selectedWeaknesses.add(select.value);
            });

            document.querySelectorAll('.alc-dropdown').forEach(select => {
                const currentValue = select.value;
                select.querySelectorAll('option').forEach(option => {
                    option.hidden = (selectedStrengths.has(option.value) || selectedWeaknesses.has(option.value)) && option.value !== currentValue;
                });
            });
        }

        function createAssessmentCard(type, alcId, alcName) {
            const container = document.getElementById(`${type}-container`);
            const card = document.createElement('div');
            card.classList.add('card', 'p-3', 'mb-3', 'assessment-card', `${type}-card`);

            card.innerHTML = `
                <div class="mb-3">
                    <label>ALC</label>
                    <select class="form-control alc-dropdown" name="${type}_alc_ids[]" required>
                        <option value="${alcId}" selected>${alcName}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea class="form-control ${type}-textarea" name="${type}[${alcId}]" rows="2" required></textarea>
                </div>
            `;

            container.appendChild(card);

            const dropdown = card.querySelector('.alc-dropdown');
            dropdown.addEventListener('change', function () {
                updateDescriptionName(this, type);
                updateDropdownOptions();
            });

            updateDropdownOptions();
        }

        function handleAutoWeakness(alcId, alcName, score) {
            if (score < 3) {
                const container = document.getElementById('weakness-container');
                const existing = Array.from(container.querySelectorAll('.alc-dropdown')).some(select => select.value === alcId);
                if (!existing) createAssessmentCard('weakness', alcId, alcName);
                removeStrengthIfExists(alcId);
            }
        }

        function handleAutoStrength(alcId, alcName, score) {
            if (score >= 3) {
                const container = document.getElementById('strength-container');
                const existing = Array.from(container.querySelectorAll('.alc-dropdown')).some(select => select.value === alcId);
                if (!existing) createAssessmentCard('strength', alcId, alcName);
                removeWeaknessIfExists(alcId);
            }
        }

        function removeWeaknessIfExists(alcId) {
            document.querySelectorAll('#weakness-container .weakness-card').forEach(card => {
                const select = card.querySelector('.alc-dropdown');
                if (select && select.value === alcId) card.remove();
            });
            updateDropdownOptions();
        }

        function removeStrengthIfExists(alcId) {
            document.querySelectorAll('#strength-container .strength-card').forEach(card => {
                const select = card.querySelector('.alc-dropdown');
                if (select && select.value === alcId) card.remove();
            });
            updateDropdownOptions();
        }

        // Saat user memilih skor
        document.querySelectorAll('input[type=radio][name^="scores"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const match = this.name.match(/scores\[(\d+)\]/);
                if (match) {
                    const alcId = match[1];
                    const score = parseInt(this.value);
                    const alcName = document.querySelector(`input[name="alc_ids[]"][value="${alcId}"]`)?.closest('.card')?.querySelector('h6')?.innerText;

                    if (alcName) {
                        if (score < 3) {
                            handleAutoWeakness(alcId, alcName, score);
                        } else {
                            handleAutoStrength(alcId, alcName, score);
                        }
                    }
                }
            });
        });

        // Reset modal saat ditutup
        document.getElementById('addAssessmentModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('assessmentForm').reset();

            ['strength', 'weakness'].forEach(type => {
                const container = document.getElementById(`${type}-container`);
                container.innerHTML = '';
            });

            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.checked = false;
            });

            document.getElementById('upload').value = '';
            document.getElementById('assessment_id').value = '';

            updateDropdownOptions();
        });

        // Validasi sebelum submit
        document.getElementById('assessmentForm').addEventListener('submit', function (e) {
            let isValid = true;
            const strengthDropdowns = document.querySelectorAll('#strength-container .alc-dropdown');
            const weaknessDropdowns = document.querySelectorAll('#weakness-container .alc-dropdown');
            const strengthDescriptions = document.querySelectorAll('#strength-container textarea');
            const weaknessDescriptions = document.querySelectorAll('#weakness-container textarea');

            strengthDropdowns.forEach(select => {
                if (!select.value) {
                    isValid = false;
                    select.classList.add('is-invalid');
                } else {
                    select.classList.remove('is-invalid');
                }
            });

            weaknessDropdowns.forEach(select => {
                if (!select.value) {
                    isValid = false;
                    select.classList.add('is-invalid');
                } else {
                    select.classList.remove('is-invalid');
                }
            });

            strengthDescriptions.forEach(textarea => {
                if (!textarea.value.trim()) {
                    isValid = false;
                    textarea.classList.add('is-invalid');
                } else {
                    textarea.classList.remove('is-invalid');
                }
            });

            weaknessDescriptions.forEach(textarea => {
                if (!textarea.value.trim()) {
                    isValid = false;
                    textarea.classList.add('is-invalid');
                } else {
                    textarea.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Harap isi semua field Strength dan Weakness dengan lengkap.');
            }
        });

        updateDropdownOptions();
    });
</script>
