<div class="modal fade comment-modal" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text fs-5 text-primary"></i>
                    <h5 class="modal-title mb-0">Note History</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnRefreshComments" class="btn btn-sm btn-light">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <ul id="commentTimeline" class="cmt-timeline list-unstyled mb-0"></ul>
                <div id="commentEmpty" class="text-muted fst-italic">There are no notes yet.</div>
            </div>
        </div>
    </div>
</div>
