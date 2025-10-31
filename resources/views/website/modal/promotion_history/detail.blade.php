<div class="modal fade" id="detailPromotionHistoryModal" tabindex="-1" aria-labelledby="detailPromotionHistoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h5 class="modal-title fw-bold" id="detailPromotionHistoryModalLabel">
                    Promotion History Details
                </h5>
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
                                            @if ($mode === 'edit')
                                                <button type="button"
                                                    class="btn btn-sm btn-light-warning me-1 edit-promotion-btn"
                                                    data-edit-modal-id="editPromotionModal{{ $promotion->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-sm btn-light-danger delete-promotion-btn"
                                                    data-delete-modal-id="deletePromotionModal{{ $promotion->id }}">
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
                    <p class="text-center text-muted">No promotion history available.</p>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT KHUSUS UNTUK MODAL INI --}}
<script>
    (function() {
        // DOM ready helper
        function onReady(fn) {
            if (document.readyState !== 'loading') fn();
            else document.addEventListener('DOMContentLoaded', fn);
        }

        onReady(function() {
            // pastikan dependensi
            if (typeof bootstrap === 'undefined' || typeof window.$ === 'undefined') {
                console.warn(
                    '[detailPromotionHistoryModal] bootstrap/jQuery belum siap. Pastikan file ini dirender SETELAH plugins.bundle.js'
                    );
                return;
            }

            // --- state lokal untuk nested modal ---
            // kita simpan backdrop parent biar bisa kita balikin
            let parentBackdropEl = null;
            let parentModalInstance = null;

            function openChildModal(childSelector) {
                const parentModalEl = document.getElementById("detailPromotionHistoryModal");
                if (!parentModalEl) return;

                // ambil (atau buat) instance parent
                parentModalInstance = bootstrap.Modal.getInstance(parentModalEl);
                if (!parentModalInstance) {
                    parentModalInstance = new bootstrap.Modal(parentModalEl);
                }

                // step 1: kita HARUS tau backdrop yg lagi dipakai parent SEKARANG,
                // sebelum parent di-hide dan sebelum child dibuka
                parentBackdropEl = document.querySelector('.modal-backdrop.show');

                // hide modal parent
                parentModalInstance.hide();

                // setelah parent di-hide, buka child
                setTimeout(() => {
                    const childEl = document.querySelector(childSelector);
                    if (!childEl) {
                        console.warn('[detailPromotionHistoryModal] Target modal tidak ditemukan:',
                            childSelector);
                        return;
                    }

                    // ambil/buat instance child
                    let childInstance = bootstrap.Modal.getInstance(childEl);
                    if (!childInstance) {
                        childInstance = new bootstrap.Modal(childEl, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }

                    // *** Z-INDEX MANAGEMENT ***
                    // - Modal parent tadi sempat punya backdrop (parentBackdropEl).
                    //   Kita turunkan z-index backdrop parent supaya gak nutup child.
                    // - Kita juga naikin z-index modal child biar pasti di depan.

                    if (parentBackdropEl) {
                        parentBackdropEl.dataset._origZ = parentBackdropEl.style.zIndex || '';
                        parentBackdropEl.style.zIndex = '1059';
                    }

                    // naikin modal child
                    childEl.style.zIndex = '1060';

                    // show child
                    childInstance.show();

                    // ketika child ditutup:
                    function handleHidden() {
                        // balikkan styling child
                        childEl.style.zIndex = '';

                        // pulihkan backdrop parent ke kondisi awal
                        if (parentBackdropEl) {
                            parentBackdropEl.style.zIndex = parentBackdropEl.dataset._origZ || '';
                            delete parentBackdropEl.dataset._origZ;
                        }

                        // show lagi modal parent (tanpa bikin backdrop baru)
                        if (parentModalInstance) {
                            parentModalInstance.show();
                        }

                        // cleanup listener supaya tidak leak
                        childEl.removeEventListener('hidden.bs.modal', handleHidden);
                    }

                    childEl.addEventListener('hidden.bs.modal', handleHidden);
                }, 100);
            }

            // CLICK: Edit
            $(document).on("click", ".edit-promotion-btn", function() {
                const modalId = $(this).data("edit-modal-id"); // "editPromotionModal12"
                if (!modalId) return;
                openChildModal("#" + modalId);
            });

            // CLICK: Delete
            $(document).on("click", ".delete-promotion-btn", function() {
                const modalId = $(this).data("delete-modal-id"); // "deletePromotionModal12"
                if (!modalId) return;
                openChildModal("#" + modalId);
            });
        });
    })();
</script>
