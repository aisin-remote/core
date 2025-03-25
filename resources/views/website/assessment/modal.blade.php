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
                                <select class="form-control alc-dropdown" name="alc_ids[]" required>
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
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success btn-sm add-assessment"
                                    data-type="strength">Tambah Strength</button>
                            </div>
                        </div>
                    </div>
                    <div class="section-title">Weakness</div>
                    <div id="weakness-container">
                        <div class="assessment-card weakness-card card p-3 mb-3">
                            <div class="mb-3">
                                <select class="form-control alc-dropdown" name="alc_ids[]" required>
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
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success btn-sm add-assessment"
                                    data-type="weakness">Tambah Weakness</button>
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
            let card = selectElement.closest('.assessment-card');
            let textarea = card.querySelector(`.${type}-textarea`);
            let alcId = selectElement.value;

            if (alcId) {
                textarea.setAttribute('name', `${type}[${alcId}]`);
            } else {
                textarea.removeAttribute('name');
            }
        }

        function updateDropdownOptions() {
            let selectedStrengths = new Set();
            let selectedWeaknesses = new Set();

            // Ambil semua nilai yang sudah dipilih di Strength
            document.querySelectorAll('#strength-container .alc-dropdown').forEach(select => {
                if (select.value) selectedStrengths.add(select.value);
            });

            // Ambil semua nilai yang sudah dipilih di Weakness
            document.querySelectorAll('#weakness-container .alc-dropdown').forEach(select => {
                if (select.value) selectedWeaknesses.add(select.value);
            });

            // Perbarui opsi dropdown Strength (hilangkan yang sudah dipilih di Weakness dan di Strength lain)
            document.querySelectorAll('#strength-container .alc-dropdown').forEach(select => {
                let currentValue = select.value;
                select.querySelectorAll('option').forEach(option => {
                    option.hidden = (selectedWeaknesses.has(option.value) || selectedStrengths
                        .has(option.value)) && option.value !== currentValue;
                });
            });

            // Perbarui opsi dropdown Weakness (hilangkan yang sudah dipilih di Strength dan di Weakness lain)
            document.querySelectorAll('#weakness-container .alc-dropdown').forEach(select => {
                let currentValue = select.value;
                select.querySelectorAll('option').forEach(option => {
                    option.hidden = (selectedStrengths.has(option.value) || selectedWeaknesses
                        .has(option.value)) && option.value !== currentValue;
                });
            });
        }

        function addAssessmentCard(type) {
            let container = document.getElementById(`${type}-container`);

            let newCard = document.createElement('div');
            newCard.classList.add('card', 'p-3', 'mb-3', 'assessment-card', `${type}-card`);

            newCard.innerHTML = `
            <div class="mb-3">
                <label>ALC</label>
                <select class="form-control alc-dropdown" name="${type}_alc_ids[]" required>
                    <option value="">Pilih ALC</option>
                    @foreach ($alcs as $alc)
                        <option value="{{ $alc->id }}">{{ $alc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea class="form-control ${type}-textarea" name="${type}[]" rows="2"></textarea>
            </div>
            <div class="d-flex justify-content-end button-group">
                <button type="button" class="btn btn-danger btn-sm remove-card me-2">Hapus</button>
                <button type="button" class="btn btn-success btn-sm add-assessment" data-type="${type}">Tambah ${type.charAt(0).toUpperCase() + type.slice(1)}</button>
            </div>
        `;

            let newDropdown = newCard.querySelector('.alc-dropdown');
            let newTextarea = newCard.querySelector(`.${type}-textarea`);

            newDropdown.addEventListener('change', function() {
                updateDescriptionName(newDropdown, type);
                updateDropdownOptions();
            });

            newCard.querySelector('.remove-card').addEventListener('click', function() {
                newCard.remove();
                updateDropdownOptions();
            });

            newCard.querySelector('.add-assessment').addEventListener('click', function() {
                addAssessmentCard(type);
            });

            container.appendChild(newCard);
            updateDropdownOptions();
        }

        // Event listener untuk dropdown pertama
        document.querySelectorAll('.alc-dropdown').forEach(dropdown => {
            let type = dropdown.closest('.assessment-card').classList.contains('strength-card') ?
                'strength' : 'weakness';
            dropdown.addEventListener('change', function() {
                updateDescriptionName(this, type);
                updateDropdownOptions();
            });
        });

        // Event listener untuk tombol tambah pertama
        document.querySelectorAll('.add-assessment').forEach(button => {
            button.addEventListener('click', function() {
                let type = this.getAttribute('data-type');
                addAssessmentCard(type);
            });
        });

        // Jalankan filter dropdown saat halaman dimuat
        updateDropdownOptions();
    });
</script>
