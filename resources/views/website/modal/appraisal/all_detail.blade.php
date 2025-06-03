<div class="modal fade" id="alldetailAppraisalModal" tabindex="-1" aria-labelledby="detailAppraisalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title" id="detailAppraisalModalLabel">Performance Appraisal Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @forelse ($performanceAppraisals as $appraisal)
                    <div class="mb-3 d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">Score: {{ $appraisal->score }}</div>
                            <div class="text-muted small">
                                {{ \Carbon\Carbon::parse($appraisal->date)->format('d M Y') }}
                            </div>
                            @if ($appraisal->notes)
                                <div class="text-gray-700 small mt-1">
                                    {{ $appraisal->notes }}
                                </div>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @if ($mode === 'edit')
                                <button class="btn btn-sm btn-light-warning edit-appraisal-btn"
                                    data-appraisal-id={{ $appraisal->id }}
                                    data-edit-modal-id="editAppraisalModal{{ $appraisal->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-light-danger delete-appraisal-btn"
                                    data-delete-modal-id="deleteAppraisalModal{{ $appraisal->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    @unless ($loop->last)
                        <hr class="my-2">
                    @endunless
                @empty
                    <div class="text-center text-muted">No performance appraisal data available.</div>
                @endforelse
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on("click", ".edit-appraisal-btn", function() {
        const target = "#" + $(this).data("edit-modal-id");

        // Ambil instance modal detail yang sudah ada
        const detailModalEl = document.getElementById("alldetailAppraisalModal");
        const detailModalInstance = bootstrap.Modal.getInstance(detailModalEl);

        // Sembunyikan modal detail dulu
        detailModalInstance.hide();

        // Buka modal edit setelah delay
        setTimeout(() => {
            const editModalEl = document.querySelector(target);

            // Cek apakah modal edit sudah punya instance, kalau belum buat baru
            let editModalInstance = bootstrap.Modal.getInstance(editModalEl);
            if (!editModalInstance) {
                editModalInstance = new bootstrap.Modal(editModalEl);
            }
            editModalInstance.show();

            // Pasang event listener untuk buka kembali modal detail saat modal edit ditutup
            editModalEl.addEventListener('hidden.bs.modal', function handler() {
                detailModalInstance.show();

                // Hapus event listener supaya tidak double trigger
                editModalEl.removeEventListener('hidden.bs.modal', handler);
            });
        }, 300);
    });
    $(document).on("click", ".delete-appraisal-btn", function() {
        const target = "#" + $(this).data("delete-modal-id");

        const detailModalEl = document.getElementById("alldetailAppraisalModal");
        const detailModalInstance = bootstrap.Modal.getInstance(detailModalEl);
        detailModalInstance.hide();

        setTimeout(() => {
            const deleteModalEl = document.querySelector(target);
            let deleteModalInstance = bootstrap.Modal.getInstance(deleteModalEl);
            if (!deleteModalInstance) {
                deleteModalInstance = new bootstrap.Modal(deleteModalEl);
            }
            deleteModalInstance.show();

            deleteModalEl.addEventListener('hidden.bs.modal', function handler() {
                detailModalInstance.show();
                deleteModalEl.removeEventListener('hidden.bs.modal', handler);
            });
        }, 300);
    });
</script>
