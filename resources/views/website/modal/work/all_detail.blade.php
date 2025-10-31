<div class="modal fade" id="allExperienceDetailModal" tabindex="-1" aria-labelledby="detailExperienceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-primary">
                <h5 class="modal-title" id="detailExperienceModalLabel">
                    Work Experience Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @forelse ($workExperiences as $exp)
                    <div class="mb-3 d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">{{ $exp->department }}</div>
                            <div class="text-muted small">{{ $exp->position }}</div>
                            <div class="text-muted small">
                                {{ \Carbon\Carbon::parse($exp->start_date)->format('Y') }}
                                -
                                {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('Y') : 'Present' }}
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @if ($mode === 'edit')
                                <button type="button" class="btn btn-sm btn-light-warning edit-experience-btn"
                                    data-experience-id="{{ $exp->id }}"
                                    data-edit-modal-id="editExperienceModal{{ $exp->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-light-danger delete-experience-btn"
                                    data-delete-modal-id="deleteExperienceModal{{ $exp->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    @unless ($loop->last)
                        <hr class="my-2">
                    @endunless
                @empty
                    <div class="text-center text-muted">
                        No work experience data available.
                    </div>
                @endforelse
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Helper DOM Ready tanpa jQuery
        function onReady(fn) {
            if (document.readyState !== 'loading') fn();
            else document.addEventListener('DOMContentLoaded', fn);
        }

        onReady(function() {
            // Pastikan bootstrap & jQuery sudah loaded, biar gak meledak di halaman lain
            if (typeof bootstrap === 'undefined' || typeof window.$ === 'undefined') {
                console.warn(
                    '[allExperienceDetailModal] bootstrap/jQuery belum siap. Pastikan script ini dirender SETELAH plugins.bundle.js'
                    );
                return;
            }

            // State lokal supaya backdrop + modal parent bisa dipulihkan rapi
            let parentBackdropEl = null;
            let parentModalInstance = null;

            function openChildModal(childSelector) {
                const parentModalEl = document.getElementById("allExperienceDetailModal");
                if (!parentModalEl) return;

                // Ambil / buat instance modal parent
                parentModalInstance = bootstrap.Modal.getInstance(parentModalEl);
                if (!parentModalInstance) {
                    parentModalInstance = new bootstrap.Modal(parentModalEl);
                }

                // Simpan backdrop yang lagi aktif sekarang (punya parent)
                parentBackdropEl = document.querySelector('.modal-backdrop.show');

                // Hide modal parent dulu
                parentModalInstance.hide();

                // Setelah parent hide, baru buka child
                setTimeout(() => {
                    const childEl = document.querySelector(childSelector);
                    if (!childEl) {
                        console.warn('[allExperienceDetailModal] Target modal tidak ditemukan:',
                            childSelector);
                        return;
                    }

                    // Ambil / buat instance modal child
                    let childInstance = bootstrap.Modal.getInstance(childEl);
                    if (!childInstance) {
                        childInstance = new bootstrap.Modal(childEl, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }

                    // === HANDLE Z-INDEX NESTED MODAL ===
                    // 1. Turunin backdrop parent biar gak nutup child
                    // 2. Naikin modal child biar dia di atas backdrop parent

                    if (parentBackdropEl) {
                        parentBackdropEl.dataset._origZ = parentBackdropEl.style.zIndex || '';
                        parentBackdropEl.style.zIndex = '1059';
                    }

                    childEl.style.zIndex = '1060';

                    // Tampilkan modal child
                    childInstance.show();

                    // Ketika child ditutup:
                    function handleHidden() {
                        // Balikin z-index child
                        childEl.style.zIndex = '';

                        // Pulihin backdrop parent ke z-index awal
                        if (parentBackdropEl) {
                            parentBackdropEl.style.zIndex = parentBackdropEl.dataset._origZ || '';
                            delete parentBackdropEl.dataset._origZ;
                        }

                        // Tampilkan lagi modal parent TANPA bikin backdrop baru numpuk di depan
                        if (parentModalInstance) {
                            parentModalInstance.show();
                        }

                        // Cleanup listener supaya gak dobel tiap kali klik
                        childEl.removeEventListener('hidden.bs.modal', handleHidden);
                    }

                    childEl.addEventListener('hidden.bs.modal', handleHidden);
                }, 100);
            }

            // Klik tombol EDIT
            $(document).on("click", ".edit-experience-btn", function() {
                const targetId = $(this).data("edit-modal-id"); // e.g. "editExperienceModal12"
                if (!targetId) return;
                openChildModal("#" + targetId);
            });

            // Klik tombol DELETE
            $(document).on("click", ".delete-experience-btn", function() {
                const targetId = $(this).data("delete-modal-id"); // e.g. "deleteExperienceModal12"
                if (!targetId) return;
                openChildModal("#" + targetId);
            });
        });
    })();
</script>
