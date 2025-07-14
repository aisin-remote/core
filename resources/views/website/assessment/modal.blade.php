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

    .select2-container .select2-selection--single {
        height: 38px;
        padding: 6px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
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
                <form id="assessmentForm" class="interlock-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="assessment_id" name="assessment_id">

                    <div class="mb-4">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select id="employee_id" name="employee_id" class="form-select">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" data-position="{{ $employee->position }}">
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <div class="mb-4">
                        <label for="date" class="form-label">Date Assessment</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>

                    <div class="mb-4">
                        <label for="target" class="form-label">Target Position</label>
                        <select id="target" name="target" data-placeholder="Select Position..."
                            class="form-select form-select-lg fw-semibold" required>
                            <option value="">Select Position</option>
                            <option data-kt-flag="flags/afghanistan.svg" value="Director">Director</option>
                            <option data-kt-flag="flags/afghanistan.svg" value="GM">General Manager</option>
                            <option data-kt-flag="flags/afghanistan.svg" value="Act GM">Act General Manager
                            </option>
                            <option data-kt-flag="flags/afghanistan.svg" value="Manager">Manager</option>
                            <option data-kt-flag="flags/afghanistan.svg" value="Act Manager">Act Manager
                            </option>
                            <option data-kt-flag="flags/aland-islands.svg" value="Coordinator">Coordinator
                            </option>
                            <option data-kt-flag="flags/aland-islands.svg" value="Act Coordinator">Act
                                Coordinator
                            </option>
                            <option data-kt-flag="flags/albania.svg" value="Section Head">Section Head
                            </option>
                            <option data-kt-flag="flags/albania.svg" value="Act Section Head">Act Section Head
                            </option>
                            <option data-kt-flag="flags/algeria.svg" value="Supervisor">Supervisor</option>
                            <option data-kt-flag="flags/algeria.svg" value="Act Supervisor">Act Supervisor
                            </option>
                            <option data-kt-flag="flags/algeria.svg" value="Leader">Leader</option>
                            <option data-kt-flag="flags/algeria.svg" value="Act Leader">Act Leader</option>
                            <option data-kt-flag="flags/algeria.svg" value="JP">JP</option>
                            <option data-kt-flag="flags/algeria.svg" value="Act JP">Act JP</option>
                            <option data-kt-flag="flags/algeria.svg" value="Operator">Operator</option>
                        </select>
                    </div>

                    <div class="mb-4">

                        <input type="hidden" id="description" name="description" required>

                    </div>

                    <div class="mb-4">
                        <div class="section-title">Assessment Scores</div>
                        @foreach ($alcs as $alc)
                            <div class="card p-3 mb-3">
                                <h6>{{ $alc->name }}</h6>
                                <input type="hidden" name="alc_ids[]" value="{{ $alc->id }}">
                                <div class="mb-2">
                                    <div class="d-flex gap-10">
                                        @for ($i = 0; $i <= 5; $i++)
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
                        <label for="upload" class="form-label">Upload File Assessment (PDF)</label>
                        <input type="file" class="form-control" id="upload" name="upload"
                            accept=".pdf,.jpg,.png">
                    </div>

                    <div class="mb-4">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" class="form-control" placeholder="Note..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary interlock-submit" id="btnSubmit">
                        <span class="spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                        <span class="btn-text">Simpan</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

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
                    option.hidden = (selectedStrengths.has(option.value) || selectedWeaknesses
                        .has(option.value)) && option.value !== currentValue;
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
                    <textarea class="form-control ${type}-textarea" name="${type}[${alcId}]" rows="2" ></textarea>
                </div>
                  <div class="mb-3">
                    <label>Suggestion Development</label>
                    <textarea class="form-control" name="suggestion_development[${alcId}]" rows="2"></textarea>
                </div>
            `;

            container.appendChild(card);

            const dropdown = card.querySelector('.alc-dropdown');
            dropdown.addEventListener('change', function() {
                updateDescriptionName(this, type);
                updateDropdownOptions();
            });

            updateDropdownOptions();
        }

        function handleAutoWeakness(alcId, alcName, score) {
            if (score < 3) {
                const container = document.getElementById('weakness-container');
                const existing = Array.from(container.querySelectorAll('.alc-dropdown')).some(select => select
                    .value === alcId);
                if (!existing) createAssessmentCard('weakness', alcId, alcName);
                removeStrengthIfExists(alcId);
            }
        }

        function handleAutoStrength(alcId, alcName, score) {
            if (score >= 3) {
                const container = document.getElementById('strength-container');
                const existing = Array.from(container.querySelectorAll('.alc-dropdown')).some(select => select
                    .value === alcId);
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
            radio.addEventListener('change', function() {
                const match = this.name.match(/scores\[(\d+)\]/);
                if (match) {
                    const alcId = match[1];
                    const score = parseInt(this.value);
                    const alcName = document.querySelector(
                            `input[name="alc_ids[]"][value="${alcId}"]`)?.closest('.card')
                        ?.querySelector('h6')?.innerText;

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
        document.getElementById('addAssessmentModal').addEventListener('hidden.bs.modal', function() {
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
            $('#employee_id').val(null).trigger('change');
            $('#description').val('');

            updateDropdownOptions();
        });

        // Validasi sebelum submit
        document.getElementById('assessmentForm').addEventListener('submit', function(e) {
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


        });

        updateDropdownOptions();
    });
    $(document).ready(function() {
        $('#employee_id').select2({
            dropdownParent: $('#addAssessmentModal'),
            placeholder: "Pilih Employee",
            allowClear: false,
            width: '100%'
        });
        $('#employee_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const position = selectedOption.data('position') || '';
            $('#description').val(position);
        });

        $('#target').select2({
            dropdownParent: $('#addAssessmentModal'),
            placeholder: "Pilih Employee",
            allowClear: false,
            width: '100%'
        });

        $('.interlock-form').on('submit', function() {
            const $btn = $(this).find('.interlock-submit');

            $btn.prop('disabled', true); // Disable button
            $btn.find('.spinner-border').removeClass('d-none'); // Show spinner
            $btn.find('.btn-text').addClass('d-none'); // Hide text
        });
    });
</script>
