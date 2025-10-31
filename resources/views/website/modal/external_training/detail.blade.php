<div class="modal fade" id="detailExternalTrainingModal" tabindex="-1" aria-labelledby="detailExternalTrainingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> {{-- modal-xl untuk tabel lebih lebar --}}
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title fw-bold" id="detailExternalTrainingModalLabel">
                    External Training History Details
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
                                                <button type="button"
                                                    class="btn btn-sm btn-light-warning edit-external-btn"
                                                    data-externaltraining-id="{{ $externalTraining->id }}"
                                                    data-edit-modal-id="editExternalTrainingModal{{ $externalTraining->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-sm btn-light-danger delete-external-btn"
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
                    <div class="text-center text-muted">
                        No External Training data available.
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT KHUSUS UNTUK MODAL INI --}}
<script>
    (function() {
        // helper DOM Ready tanpa jQuery
        function onReady(fn) {
            if (document.readyState !== 'loading') fn();
            else document.addEventListener('DOMContentLoaded', fn);
        }

        onReady(function() {
            // safety guard
            if (typeof bootstrap === 'undefined' || typeof window.$ === 'undefined') {
                console.warn(
                    '[detailExternalTrainingModal] bootstrap/jQuery belum siap. Pastikan script ini dirender SETELAH plugins.bundle.js'
                    );
                return;
            }

            // state lokal supaya backdrop & instance parent bisa dipulihkan dengan benar
            let parentBackdropEl = null;
            let parentModalInstance = null;

            function openChildModal(childSelector) {
                const parentModalEl = document.getElementById("detailExternalTrainingModal");
                if (!parentModalEl) return;

                // ambil / buat instance parent
                parentModalInstance = bootstrap.Modal.getInstance(parentModalEl);
                if (!parentModalInstance) {
                    parentModalInstance = new bootstrap.Modal(parentModalEl);
                }

                // simpan backdrop aktif sebelum parent ditutup
                parentBackdropEl = document.querySelector('.modal-backdrop.show');

                // hide parent
                parentModalInstance.hide();

                // setelah parent bener-bener hide, lanjut buka child
                setTimeout(() => {
                    const childEl = document.querySelector(childSelector);
                    if (!childEl) {
                        console.warn('[detailExternalTrainingModal] Target modal tidak ditemukan:',
                            childSelector);
                        return;
                    }

                    // ambil / buat instance child
                    let childInstance = bootstrap.Modal.getInstance(childEl);
                    if (!childInstance) {
                        childInstance = new bootstrap.Modal(childEl, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }

                    // === Z-INDEX HANDLING ===
                    // Turunkan backdrop parent supaya tidak nutup child,
                    // simpan nilai awal agar bisa dipulihkan nanti.
                    if (parentBackdropEl) {
                        parentBackdropEl.dataset._origZ = parentBackdropEl.style.zIndex || '';
                        parentBackdropEl.style.zIndex = '1059';
                    }

                    // Naikkan modal child
                    childEl.style.zIndex = '1060';

                    // Show child modal
                    childInstance.show();

                    // Waktu child ditutup:
                    function handleHidden() {
                        // balikin z-index modal child ke default
                        childEl.style.zIndex = '';

                        // pulihkan backdrop parent
                        if (parentBackdropEl) {
                            parentBackdropEl.style.zIndex = parentBackdropEl.dataset._origZ || '';
                            delete parentBackdropEl.dataset._origZ;
                        }

                        // munculkan lagi modal parent TANPA bikin backdrop baru numpuk
                        if (parentModalInstance) {
                            parentModalInstance.show();
                        }

                        // cleanup listener biar gak nambah tiap kali
                        childEl.removeEventListener('hidden.bs.modal', handleHidden);
                    }

                    childEl.addEventListener('hidden.bs.modal', handleHidden);
                }, 100);
            }

            // klik tombol EDIT
            $(document).on("click", ".edit-external-btn", function() {
                const targetId = $(this).data("edit-modal-id"); // "editExternalTrainingModal23"
                if (!targetId) return;
                openChildModal("#" + targetId);
            });

            // klik tombol DELETE
            $(document).on("click", ".delete-external-btn", function() {
                const targetId = $(this).data("delete-modal-id"); // "deleteExternalTrainingModal23"
                if (!targetId) return;
                openChildModal("#" + targetId);
            });
        });
    })();
</script>
