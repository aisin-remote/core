<div class="modal fade" id="detailEducationModal" tabindex="-1" aria-labelledby="detailEducationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-primary">
                <h5 class="modal-title">Educational Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @forelse ($educations as $edu)
                    <div class="mb-3 d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">{{ $edu->educational_level }} - {{ $edu->major }}</div>
                            <div class="text-muted small">{{ $edu->institute }}</div>
                            <div class="text-muted small">
                                {{ \Carbon\Carbon::parse($edu->start_date)->format('Y') }} -
                                {{ \Carbon\Carbon::parse($edu->end_date)->format('Y') }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @if ($mode === 'edit')
                                <button class="btn btn-sm btn-light-warning edit-education-btn"
                                    data-education-id="{{ $edu->id }}"
                                    data-target="#editEducationModal{{ $edu->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-sm btn-light-danger delete-education-btn"
                                    data-education-id="{{ $edu->id }}"
                                    data-target="#deleteEducationModal{{ $edu->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endif
                        </div>

                    </div>
                    @unless ($loop->last)
                        <hr class="my-2">
                    @endunless
                @empty
                    <div class="text-center text-muted">No data available.</div>
                @endforelse
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on("click", ".edit-education-btn", function() {
        const target = $(this).data("target");

        // Ambil instance modal detail yang sudah ada
        const detailModalEl = document.getElementById("detailEducationModal");
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

    $(document).on("click", ".delete-education-btn", function() {
        const targetSelector = $(this).data("target");

        const detailModalEl = document.getElementById("detailEducationModal");
        const detailModalInstance = bootstrap.Modal.getInstance(detailModalEl);

        detailModalInstance.hide();

        setTimeout(() => {
            const deleteModalEl = document.querySelector(targetSelector);

            // Cek instance modal hapus
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
