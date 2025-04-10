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
                    <div id="strength-container">
                        <div class="assessment-card strength-card card p-3 mb-3">

                            <div class="mb-3">
                                <label>ALC</label>
                                <select class="form-control alc-dropdown" name="alc_ids[]">
                                    <option value="">Pilih ALC</option>
                                    @foreach ($alcs as $alc)
                                        <option value="{{ $alc->id }}">{{ $alc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Description</label>
                                <textarea class="form-control strength-textarea" name="strength[1]" rows="2"></textarea>
                            </div>

                        </div>
                    </div>
                    <div class="section-title">Weakness</div>
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
                                <label>Description</label>
                                <textarea class="form-control weakness-textarea" name="weakness[1]" rows="2"></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="upload" class="form-label">Upload File Assessment(PDF, JPG, PNG)</label>
                        <input type="file" class="form-control" id="upload" name="upload"
                            accept=".pdf,.jpg,.png">
                    </div>

                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan</button>
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

        function addAssessmentCard(type) {
            const container = document.getElementById(`${type}-container`);
            const newCard = document.createElement('div');
            newCard.classList.add('card', 'p-3', 'mb-3', 'assessment-card', `${type}-card`);

            let optionsHTML = `
                <option value="">Pilih ALC</option>
                @foreach ($alcs as $alc)
                    <option value="{{ $alc->id }}">{{ $alc->name }}</option>
                @endforeach
            `;

            newCard.innerHTML = `
                <div class="mb-3">
                    <label>ALC</label>
                    <select class="form-control alc-dropdown" name="${type}_alc_ids[]" required>
                        ${optionsHTML}
                    </select>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea class="form-control ${type}-textarea" name="${type}[]" rows="2"></textarea>
                </div>
                <div class="d-flex justify-content-end button-group">
                    <button type="button" class="btn btn-danger btn-sm remove-card me-2">Hapus</button>
                    <button type="button" class="btn btn-success btn-sm add-assessment" data-type="${type}">
                        Tambah ${type.charAt(0).toUpperCase() + type.slice(1)}
                    </button>
                </div>
            `;

            container.appendChild(newCard);

            const dropdown = newCard.querySelector('.alc-dropdown');
            dropdown.addEventListener('change', function() {
                updateDescriptionName(this, type);
                updateDropdownOptions();
            });

            newCard.querySelector('.remove-card').addEventListener('click', () => {
                newCard.remove();
                updateDropdownOptions();
            });

            newCard.querySelector('.add-assessment').addEventListener('click', () => {
                addAssessmentCard(type);
            });

            updateDropdownOptions();
        }

        function handleAutoWeakness(alcId, alcName, score) {
            if (score < 3) {
                const container = document.getElementById('weakness-container');
                const selects = container.querySelectorAll('.alc-dropdown');

                let exists = false;
                let emptySelect = null;

                selects.forEach(select => {
                    if (select.value === alcId) {
                        exists = true;
                    } else if (!select.value && !emptySelect) {
                        emptySelect = select;
                    }
                });

                if (exists) return;

                if (emptySelect) {
                    const option = document.createElement('option');
                    option.value = alcId;
                    option.text = alcName;
                    option.selected = true;
                    emptySelect.appendChild(option);
                    emptySelect.value = alcId;
                    emptySelect.dispatchEvent(new Event('change'));
                } else {
                    const newCard = document.createElement('div');
                    newCard.classList.add('card', 'p-3', 'mb-3', 'assessment-card', 'weakness-card');

                    newCard.innerHTML = `
                        <div class="mb-3">
                            <label>ALC</label>
                            <select class="form-control alc-dropdown" name="weakness_alc_ids[]" required>
                                <option value="${alcId}" selected>${alcName}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea class="form-control weakness-textarea" name="weakness[${alcId}]" rows="2"></textarea>
                        </div>
                    `;

                    container.appendChild(newCard);

                    const dropdown = newCard.querySelector('.alc-dropdown');
                    dropdown.addEventListener('change', function() {
                        updateDescriptionName(this, 'weakness');
                        updateDropdownOptions();
                    });

                    updateDropdownOptions();
                }
            }
        }

        function handleAutoStrength(alcId, alcName, score) {
            if (score >= 3) {
                const container = document.getElementById('strength-container');
                const selects = container.querySelectorAll('.alc-dropdown');

                let exists = false;
                let emptySelect = null;

                selects.forEach(select => {
                    if (select.value === alcId) {
                        exists = true;
                    } else if (!select.value && !emptySelect) {
                        emptySelect = select;
                    }
                });

                if (exists) return;

                if (emptySelect) {
                    const option = document.createElement('option');
                    option.value = alcId;
                    option.text = alcName;
                    option.selected = true;
                    emptySelect.appendChild(option);
                    emptySelect.value = alcId;
                    emptySelect.dispatchEvent(new Event('change'));
                } else {
                    const newCard = document.createElement('div');
                    newCard.classList.add('card', 'p-3', 'mb-3', 'assessment-card', 'strength-card');

                    newCard.innerHTML = `
                        <div class="mb-3">
                            <label>ALC</label>
                            <select class="form-control alc-dropdown" name="strength_alc_ids[]" required>
                                <option value="${alcId}" selected>${alcName}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea class="form-control strength-textarea" name="strength[${alcId}]" rows="2"></textarea>
                        </div>
                    `;

                    container.appendChild(newCard);

                    const dropdown = newCard.querySelector('.alc-dropdown');
                    dropdown.addEventListener('change', function() {
                        updateDescriptionName(this, 'strength');
                        updateDropdownOptions();
                    });

                    updateDropdownOptions();
                }
            }
        }

        function removeWeaknessIfExists(alcId) {
            document.querySelectorAll('#weakness-container .weakness-card').forEach(card => {
                const select = card.querySelector('.alc-dropdown');
                if (select && select.value === alcId) {
                    card.remove();
                }
            });
            updateDropdownOptions();
        }

        function removeStrengthIfExists(alcId) {
            document.querySelectorAll('#strength-container .strength-card').forEach(card => {
                const select = card.querySelector('.alc-dropdown');
                if (select && select.value === alcId) {
                    card.remove();
                }
            });
            updateDropdownOptions();
        }

        // ✅ Perbaikan utama ada di sini
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
                            removeStrengthIfExists(alcId);
                        } else {
                            handleAutoStrength(alcId, alcName, score);
                            removeWeaknessIfExists(alcId);
                        }
                    }
                }
            });
        });

        document.querySelectorAll('.alc-dropdown').forEach(dropdown => {
            const type = dropdown.closest('.assessment-card').classList.contains('strength-card') ?
                'strength' : 'weakness';
            dropdown.addEventListener('change', function() {
                updateDescriptionName(this, type);
                updateDropdownOptions();
            });
        });

        document.querySelectorAll('.add-assessment').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;
                addAssessmentCard(type);
            });
        });

        document.getElementById('addAssessmentModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('assessmentForm').reset();

            ['strength', 'weakness'].forEach(type => {
                const container = document.getElementById(`${type}-container`);
                const cards = container.querySelectorAll(`.${type}-card`);

                // Hapus semua card kecuali yang pertama (default)
                cards.forEach((card, index) => {
                    if (index > 0) {
                        card.remove();
                    }
                });

                // Reset isi dari card default
                const defaultCard = container.querySelector(`.${type}-card`);
                const dropdown = defaultCard.querySelector('.alc-dropdown');
                const textarea = defaultCard.querySelector(`.${type}-textarea`);

                dropdown.innerHTML = `
        <option value="">Pilih ALC</option>
        @foreach ($alcs as $alc)
            <option value="{{ $alc->id }}">{{ $alc->name }}</option>
        @endforeach
    `;
                dropdown.value = '';
                textarea.value = '';
                textarea.removeAttribute('name');
                dropdown.dispatchEvent(new Event('change'));
            });


            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.checked = false;
            });

            document.getElementById('upload').value = '';
            document.getElementById('assessment_id').value = '';

            updateDropdownOptions();
        });

        updateDropdownOptions();
    });
</script>
