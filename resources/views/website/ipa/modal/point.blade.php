<div class="modal fade" id="modal-ipp-detail" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ippd-id">
                <input type="hidden" id="ippd-source"> {{-- 'ipp' | 'custom' --}}
                <input type="hidden" id="ippd-ach-key"> {{-- key untuk custom cache --}}

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" id="ippd-category" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Activity</label>
                        <input type="text" class="form-control" id="ippd-activity" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label">One Year Target</label>
                        <textarea class="form-control" id="ippd-target" rows="3" readonly></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">One Year Achievement</label>
                        <textarea class="form-control" id="ippd-achv" rows="3" placeholder="Capaian selama setahun..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Weight (W, %)</label>
                        <input type="text" class="form-control" id="ippd-weight">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Score (R)</label>
                        <input type="number" step="0.01" class="form-control" id="ippd-score">
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="ippd-btn-save">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
