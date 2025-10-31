<div class="modal fade" id="alldetailAppraisalModal" tabindex="-1" aria-labelledby="detailAppraisalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title" id="detailAppraisalModalLabel">
                    Performance Appraisal Details
                </h5>
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
                                <button type="button" class="btn btn-sm btn-light-warning edit-appraisal-btn"
                                    data-appraisal-id="{{ $appraisal->id }}"
                                    data-edit-modal-id="editAppraisalModal{{ $appraisal->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-light-danger delete-appraisal-btn"
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
                    <div class="text-center text-muted">
                        No performance appraisal data available.
                    </div>
                @endforelse
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // helper DOM ready tanpa jQuery
        function onReady(fn) {
            if (document.readyState !== 'loading') fn();
            else document.addEventListener('DOMContentLoaded', fn);
        }

        onReady(function() {
            // guard biar ga error kalau skrip dievaluasi sebelum jQuery/Bootstrap siap
            if (typeof bootstrap === 'undefined' || typeof window.$ === 'undefined') {
                console.warn(
                    '[alldetailAppraisalModal] bootstrap/jQuery belum siap. Pastikan script ini dirender SETELAH plugins.bundle.js'
                    );
                return;
            }

            // simpan state parent modal dan backdrop supaya bisa dipulihkan setelah child modal ditutup
            let parentBackdropEl = null;
            let parentModalInstance = null;

            function openChildModal(childSelector) {
                const parentModalEl = document.getElementById("alldetailAppraisalModal");
                if (!parentModalEl) return;

                // ambil / buat instance modal parent
                parentModalInstance = bootstrap.Modal.getInstance(parentModalEl);
                if (!parentModalInstance) {
                    parentModalInstance = new bootstrap.Modal(parentModalEl);
                }

                // sebelum kita hide parent, ambil backdrop aktif sekarang
                parentBackdropEl = document.querySelector('.modal-backdrop.show');

                // hide modal parent
                parentModalInstance.hide();

                setTimeout(() => {
                    const childEl = document.querySelector(childSelector);
                    if (!childEl) {
                        console.warn('[alldetailAppraisalModal] Target modal tidak ditemukan:',
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
                    // 1. Turunin backdrop parent biar gak nutup child
                    // 2. Naikin child modal
                    if (parentBackdropEl) {
                        parentBackdropEl.dataset._origZ = parentBackdropEl.style.zIndex || '';
                        parentBackdropEl.style.zIndex = '1059';
                    }

                    childEl.style.zIndex = '1060';

                    // Show modal child
                    childInstance.show();

                    // waktu child ditutup:
                    function handleHidden() {
                        // reset z-index child
                        childEl.style.zIndex = '';

                        // pulihin backdrop parent
                        if (parentBackdropEl) {
                            parentBackdropEl.style.zIndex = parentBackdropEl.dataset._origZ || '';
                            delete parentBackdropEl.dataset._origZ;
                        }

                        // munculkan lagi parent TANPA bikin backdrop baru nempel di depan layar
                        if (parentModalInstance) {
                            parentModalInstance.show();
                        }

                        // cleanup listener supaya gak nambah banyak listener tiap klik
                        childEl.removeEventListener('hidden.bs.modal', handleHidden);
                    }

                    childEl.addEventListener('hidden.bs.modal', handleHidden);
                }, 100);
            }

            // klik tombol EDIT appraisal
            $(document).on("click", ".edit-appraisal-btn", function() {
                const modalId = $(this).data("edit-modal-id"); // ex: "editAppraisalModal10"
                if (!modalId) return;
                openChildModal("#" + modalId);
            });

            // klik tombol DELETE appraisal
            $(document).on("click", ".delete-appraisal-btn", function() {
                const modalId = $(this).data("delete-modal-id"); // ex: "deleteAppraisalModal10"
                if (!modalId) return;
                openChildModal("#" + modalId);
            });
        });
    })();
</script>
