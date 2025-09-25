<div class="modal fade" id="reviseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Revise Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviseForm">
                <div class="modal-body">
                    <input type="hidden" id="reviseIppId">
                    <label class="form-label fw-semibold">Note <span class="text-danger">*</span></label>
                    <textarea id="reviseNote" class="form-control" rows="5" maxlength="1000"
                        placeholder="Explain what needs to be revised..."></textarea>
                    <div class="form-text mt-1"><span id="reviseCount">0</span>/1000</div>
                    <div class="invalid-feedback d-block d-none" id="reviseError">Please write a note.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="reviseSubmitBtn">
                        <i class="fas fa-paper-plane me-1"></i> Send Revise
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
