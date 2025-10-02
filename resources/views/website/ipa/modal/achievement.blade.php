<div class="modal fade" id="modal-add-activity" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Activity (Custom)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="add-cat">
                            <option value="">-- Pilih kategori --</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c['key'] }}">{{ $c['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Activity <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-activity" placeholder="Nama activity">
                    </div>
                    <div class="col-12">
                        <label class="form-label">One Year Target <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="add-target" rows="3" placeholder="Target tahunan"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Weight (W, %)</label>
                        <input type="text" class="form-control" id="add-weight">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Score (R)</label>
                        <input type="number" step="0.01" class="form-control" id="add-score">
                    </div>
                    <div class="col-12">
                        <label class="form-label">One Year Achievement</label>
                        <textarea class="form-control" id="add-achv" rows="3" placeholder="Capaian selama setahun (opsional)"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="add-btn-save">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
