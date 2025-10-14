{{-- === MODAL POINT === --}}
<div class="modal fade" id="pointModal" tabindex="-1" aria-labelledby="pointModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="pointForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="pointModalLabel">Tambah Point</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pmCat">
                    <input type="hidden" id="pmMode" value="create"> {{-- create|edit --}}
                    <input type="hidden" id="pmRowId"> {{-- id baris saat edit --}}

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fs-6">Program / Activity <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-lg" id="pmActivity" rows="3"></textarea>
                        </div>

                        {{-- <div class="col-md-6">
                            <label class="form-label fs-6">Target MID Year (Apr–Sept)</label>
                            <textarea class="form-control form-control-lg" id="pmTargetMid" rows="3"
                                placeholder="cth: 1 app go live; 5 feature improvement, dsb."></textarea>
                        </div> --}}

                        <div class="col-md-12">
                            <label class="form-label fs-6">Target One Year (Apr–Mar)</label>
                            <textarea class="form-control form-control-lg" id="pmTargetOne" rows="3"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-6">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="pmStart">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-6">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="pmDue">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-6">Weight (%) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <input type="number" min="0" max="100" step="1" class="form-control"
                                    id="pmWeight" placeholder="0">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>

                    {{-- Simpan sebagai Draft --}}
                    <button type="submit" class="btn btn-primary" id="pmSaveBtn">
                        <i class="bi bi-save2"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
