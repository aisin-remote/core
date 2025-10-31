<div class="modal fade" id="detailAstraTrainingModal" tabindex="-1" aria-labelledby="detailAstraTrainingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> {{-- modal-xl karena tampilkan table --}}
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title fw-bold" id="detailAstraTrainingModalLabel">
                    Astra Training History Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($astraTrainings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="bg-light fw-semibold">
                                <tr>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">Program</th>
                                    <th class="text-center">ICT</th>
                                    <th class="text-center">Project</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @foreach ($astraTrainings as $astraTraining)
                                    <tr>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($astraTraining->date_end)->format('Y') }}
                                        </td>
                                        <td class="text-center">{{ $astraTraining->program }}</td>
                                        <td class="text-center">{{ $astraTraining->ict_score }}</td>
                                        <td class="text-center">{{ $astraTraining->project_score }}</td>
                                        <td class="text-center">{{ $astraTraining->total_score }}</td>
                                        <td class="text-center">
                                            @if ($mode === 'edit')
                                                <button type="button"
                                                    class="btn btn-sm btn-light-warning edit-astra-btn"
                                                    data-astratraining-id="{{ $astraTraining->id }}"
                                                    data-edit-modal-id="editAstraTrainingModal{{ $astraTraining->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-sm btn-light-danger delete-astra-btn"
                                                    data-delete-modal-id="deleteAstraTrainingModal{{ $astraTraining->id }}">
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
                    <div class="text-center text-muted">No Astra Training data available.</div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // DOM ready helper tanpa jQuery
        function onReady(fn) {
            if (document.readyState !== 'loading') fn();
            else document.addEventListener('DOMContentLoaded', fn);
        }

        onReady(function() {
            // pastikan jQuery & bootstrap sudah loaded
            if (typeof bootstrap === 'undefined' || typeof window.$ === 'undefined') {
                console.warn(
                    '[detailAstraTrainingModal] bootstrap/jQuery belum siap. Pastikan script ini dirender SETELAH plugins.bundle.js'
                    );
                return;
            }

            // state lokal untuk nested modal handling
            let parentBackdropEl = null;
            let parentModalInstance = null;

            function openChildModal(childSelector) {
                const parentModalEl = document.getElementById("detailAstraTrainingModal");
                if (!parentModalEl) return;

                // ambil / buat instance modal parent
                parentModalInstance = bootstrap.Modal.getInstance(parentModalEl);
                if (!parentModalInstance) {
                    parentModalInstance = new bootstrap.Modal(parentModalEl);
                }

                // simpan backdrop aktif saat ini (backdrop parent)
                parentBackdropEl = document.querySelector('.modal-backdrop.show');

                // hide modal parent dulu
                parentModalInstance.hide();

                // setelah parent hide, buka modal child
                setTimeout(() => {
                    const childEl = document.querySelector(childSelector);
                    if (!childEl) {
                        console.warn('[detailAstraTrainingModal] Target modal tidak ditemukan:',
                            childSelector);
                        return;
                    }

                    // ambil / buat instance modal child
                    let childInstance = bootstrap.Modal.getInstance(childEl);
                    if (!childInstance) {
                        childInstance = new bootstrap.Modal(childEl, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }

                    // === Z-INDEX MANAGEMENT ===
                    // turunin backdrop parent supaya gak nutup child
                    if (parentBackdropEl) {
                        parentBackdropEl.dataset._origZ = parentBackdropEl.style.zIndex || '';
                        parentBackdropEl.style.zIndex = '1059';
                    }

                    // naikin modal child di atas backdrop parent
                    childEl.style.zIndex = '1060';

                    // tampilkan child
                    childInstance.show();

                    // ketika child ditutup, balikin semua state lagi
                    function handleHidden() {
                        // reset z-index child
                        childEl.style.zIndex = '';

                        // balikin backdrop parent ke kondisi sebelum diutak-atik
                        if (parentBackdropEl) {
                            parentBackdropEl.style.zIndex = parentBackdropEl.dataset._origZ || '';
                            delete parentBackdropEl.dataset._origZ;
                        }

                        // show lagi modal parent tanpa nambah backdrop baru
                        if (parentModalInstance) {
                            parentModalInstance.show();
                        }

                        // cleanup listener supaya gak nambah terus
                        childEl.removeEventListener('hidden.bs.modal', handleHidden);
                    }

                    childEl.addEventListener('hidden.bs.modal', handleHidden);
                }, 100);
            }

            // klik tombol EDIT
            $(document).on("click", ".edit-astra-btn", function() {
                const targetId = $(this).data("edit-modal-id"); // "editAstraTrainingModal5"
                if (!targetId) return;
                openChildModal("#" + targetId);
            });

            // klik tombol DELETE
            $(document).on("click", ".delete-astra-btn", function() {
                const targetId = $(this).data("delete-modal-id"); // "deleteAstraTrainingModal5"
                if (!targetId) return;
                openChildModal("#" + targetId);
            });
        });
    })();
</script>
