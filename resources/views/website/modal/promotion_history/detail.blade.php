<div class="modal fade" id="detailPromotionHistoryModal" tabindex="-1" aria-labelledby="detailPromotionHistoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title fw-bold" id="detailPromotionHistoryModalLabel">Promotion History Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($promotionHistories->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="bg-light fw-semibold">
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th class="text-center">Previous Grade</th>
                                    <th class="text-center">Previous Position</th>
                                    <th class="text-center">Current Grade</th>
                                    <th class="text-center">Current Position</th>
                                    <th class="text-center">Last Promotion Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($promotionHistories as $promotion)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $promotion->previous_grade }}</td>
                                        <td class="text-center">{{ $promotion->previous_position }}</td>
                                        <td class="text-center">{{ $promotion->current_grade }}</td>
                                        <td class="text-center">{{ $promotion->current_position }}</td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($promotion->last_promotion_date)->format('j F Y, g:i A') }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light-warning me-1 edit-promotion-btn"
                                                data-promotion-id={{ $promotion->id }}
                                                data-edit-modal-id="editPromotionModal{{ $promotion->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light-danger delete-promotion-btn"
                                               data-delete-modal-id="deletePromotionModal{{ $promotion->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">No promotion history available.</p>
                @endif
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on("click", ".edit-promotion-btn", function() {
        const target = "#" + $(this).data("edit-modal-id");

        // Ambil instance modal detail yang sudah ada
        const detailModalEl = document.getElementById("detailPromotionHistoryModal");
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
    $(document).on("click", ".delete-promotion-btn", function() {
        const target = "#" + $(this).data("delete-modal-id");

        const detailModalEl = document.getElementById("detailPromotionHistoryModal");
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
