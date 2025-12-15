<div class="modal fade" id="notesHistory" tabindex="-1"
    aria-labelledby="notesHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notesHistoryLabel">
                    Comment History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6>Employee: <span id="employeeName">Ferry Avianto</span></h6>
                </div>

                <div class="list-group" id="commentHistory">
                    <a href="#" class="list-group-item list-group-item-action">
                        <h6 class="fw-bold">Customer Focus</h6>
                        <p>Dokumen kurang lengkap.</p>
                        <small class="text-muted">
                            Date: 2023-05-01 10:00 AM
                        </small>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <h6 class="fw-bold">Analysis & Judgment</h6>
                        <p>Dokumen tidak lengkap</p>
                        <small class="text-muted">
                            Date: 2023-05-02 11:30 AM
                        </small>
                    </a>
                    {{-- bisa diisi dinamis via JS --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
