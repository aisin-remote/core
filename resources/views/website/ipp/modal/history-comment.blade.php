<div class="modal fade" id="commentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-md">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="bi bi-chat-dots me-2"></i>Comment History</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light" id="btnRefreshComments">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <ul id="commentTimeline" class="timeline list-unstyled mb-0"></ul>
                <div id="commentEmpty" class="comment-empty">Belum ada komentar.</div>
            </div>
        </div>
    </div>
</div>
