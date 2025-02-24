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
                        <label for="employee_npk" class="form-label">Employee</label>
                        <select class="form-control" id="employee_npk" name="employee_npk" required>
                            <option value="">Pilih Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->npk }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Assessment Scores</label>
                        @foreach ($alcs as $alc)
                            <div class="card p-3 mb-3">
                                <h6>{{ $alc->name }}</h6>
                                <input type="hidden" name="alc_ids[]" value="{{ $alc->id }}">
                                <div class="mb-2">
                                    <div class="d-flex gap-2">
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

                    <!-- Container untuk menampung cards -->
                    <div id="assessment-container">
                        <div class="assessment-card card p-3 mb-3">
                            <h6>Assessment</h6>

                            <!-- Dropdown ALC -->
                            <div class="mb-3">
                                <select class="form-control alc-dropdown" name="alc_ids[]" required>
                                    <option value="">Pilih ALC</option>
                                    @foreach ($alcs as $alc)
                                        <option value="{{ $alc->id }}">{{ $alc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Deskripsi -->
                            <div class="mb-3">
                                <label>Description</label>
                                <textarea class="form-control" name="descriptions[]" rows="2"></textarea>
                            </div>

                            <!-- Tombol Hapus & Tambah -->
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success btn-sm add-assessment">Tambah</button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="upload" class="form-label">Upload File (PDF, JPG, PNG)</label>
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
        let container = document.getElementById('assessment-container');

        // Fungsi untuk menambahkan card baru
        function addAssessmentCard() {
            let newCard = document.querySelector('.assessment-card').cloneNode(true);

            // Reset dropdown dan textarea
            newCard.querySelector('.alc-dropdown').value = '';
            newCard.querySelector('textarea').value = '';

            // Perbarui tombol pada card baru
            let buttonContainer = newCard.querySelector('.d-flex');
            buttonContainer.innerHTML = `
                <button type="button" class="btn btn-danger btn-sm remove-card me-2">Hapus</button>
                <button type="button" class="btn btn-success btn-sm add-assessment">Tambah</button>
            `;

            // Tambahkan event listener untuk tombol hapus
            newCard.querySelector('.remove-card').addEventListener('click', function () {
                newCard.remove();
            });

            // Tambahkan event listener untuk tombol tambah pada card baru
            newCard.querySelector('.add-assessment').addEventListener('click', addAssessmentCard);

            container.appendChild(newCard);
        }

        // Tambahkan event listener untuk tombol tambah pertama
        document.querySelector('.add-assessment').addEventListener('click', addAssessmentCard);
    });
</script>
