<div class="modal fade" id="allExperienceDetailModal" tabindex="-1" aria-labelledby="detailExperienceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-primary">
                <h5 class="modal-title" id="detailExperienceModalLabel">Work Experience Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @forelse ($workExperiences as $exp)
                    <div class="mb-3 d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">{{ $exp->department }}</div>
                            <div class="text-muted small">{{ $exp->position }}</div>
                            <div class="text-muted small">
                                {{ \Carbon\Carbon::parse($exp->start_date)->format('Y') }} -
                                {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('Y') : 'Present' }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-light-warning edit-experience-btn"
                                data-experience-id="{{ $exp->id }}"
                                data-edit-modal-id="editExperienceModal{{ $exp->id }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button class="btn btn-sm btn-light-danger delete-experience-btn"
                                data-delete-modal-id="deleteExperienceModal{{ $exp->id }}">

                                <i class="fas fa-trash-alt"></i>
                            </button>

                        </div>
                    </div>
                    @unless ($loop->last)
                        <hr class="my-2">
                    @endunless
                @empty
                    <div class="text-center text-muted">No work experience data available.</div>
                @endforelse
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on("click", ".edit-experience-btn", function() {
        const target = "#" + $(this).data("edit-modal-id");

        // Ambil instance modal detail yang sudah ada
        const detailModalEl = document.getElementById("allExperienceDetailModal");
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
    $(document).on("click", ".delete-experience-btn", function() {
        const target = "#" + $(this).data("delete-modal-id");

        const detailModalEl = document.getElementById("allExperienceDetailModal");
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
