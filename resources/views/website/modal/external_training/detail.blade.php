<div class="modal fade" id="detailExternalTrainingModal" tabindex="-1" aria-labelledby="detailExternalTrainingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> {{-- modal-xl untuk tabel lebih lebar --}}
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title fw-bold" id="detailExternalTrainingModalLabel">External Training History Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($externalTrainings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="bg-light fw-semibold">
                                <tr>
                                    <th>Training</th>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">Vendor</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @foreach ($externalTrainings as $externalTraining)
                                    <tr>
                                        <td>{{ $externalTraining->program }}</td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($externalTraining->date_end)->format('Y') }}
                                        </td>
                                        <td class="text-center">{{ $externalTraining->vendor }}</td>
                                        <td class="text-center">
                                              @if ($mode === 'edit')
                                            <button class="btn btn-sm btn-light-warning edit-external-btn"
                                                data-externalTraining-id={{ $externalTraining->id }}
                                               data-edit-modal-id="editExternalTrainingModal{{ $externalTraining->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light-danger delete-external-btn"
                                                 data-delete-modal-id="deleteExternalTrainingModal{{ $externalTraining->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            @endif  
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted">No External Training data available.</div>
                @endif
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on("click", ".edit-external-btn", function() {
        const target = "#" + $(this).data("edit-modal-id");

        // Ambil instance modal detail yang sudah ada
        const detailModalEl = document.getElementById("detailExternalTrainingModal");
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
    $(document).on("click", ".delete-external-btn", function() {
        const target = "#" + $(this).data("delete-modal-id");

        const detailModalEl = document.getElementById("detailExternalTrainingModal");
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
