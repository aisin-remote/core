<!-- Modal -->
<div class="modal fade" id="assessmentModal" tabindex="-1" aria-labelledby="assessmentModalLabel" >
    <div class="modal-dialog " style="max-width: 90%; width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assessmentModalLabel">Detail Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informasi Karyawan -->
                <div class="row mb-3">

                    <div class="col-md-4">
                        <p class="fw-bold"><strong>Departemen:</strong> <span id="modal-department"></span></p>
                    </div>
                    <div class="col-md-4">
                        <p class="fw-bold"><strong>Tanggal:</strong> <span id="modal-date"></span></p>
                    </div>
                </div>

                <!-- Chart -->
                <div class="mb-4">
                    <h5 class="text-center">Assessment Chart</h5>
                    <div style="width: 100%; height: 400px;">
                        <canvas id="assessmentChart"></canvas>
                    </div>
                </div>

                <!-- Strength & Weakness -->
                <!-- Strength Table -->
                <h5 class="text-center">Strengths</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 5%;">#</th>
                            <th class="text-center" style="width: 45%;">Strength</th>
                            <th class="text-center" style="width: 45%;">Description</th>
                        </tr>
                    </thead>
                    <tbody id="modal-strengths-body">
                        <tr>
                            <td colspan="3" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Weakness Table -->
                <h5 class="text-center">Weaknesses</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 5%;">#</th>
                            <th class="text-center" style="width: 45%;">Weakness</th>
                            <th class="text-center" style="width: 45%;">Description</th>
                        </tr>
                    </thead>
                    <tbody id="modal-weaknesses-body">
                        <tr>
                            <td colspan="3" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
