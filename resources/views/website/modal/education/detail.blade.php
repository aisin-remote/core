<div class="modal fade" id="detailEducationModal" tabindex="-1" aria-labelledby="detailEducationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light-primary">
                <h5 class="modal-title" id="detailEducationModalLabel">
                    Educational Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @forelse ($educations as $edu)
                    <div class="mb-3 d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">
                                {{ $edu->educational_level }} - {{ $edu->major }}
                            </div>
                            <div class="text-muted small">
                                {{ $edu->institute }}
                            </div>
                            <div class="text-muted small">
                                {{ \Carbon\Carbon::parse($edu->start_date)->format('Y') }}
                                -
                                {{ $edu->end_date ? \Carbon\Carbon::parse($edu->end_date)->format('Y') : 'Present' }}
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @if ($mode === 'edit')
                                <button type="button" class="btn btn-sm btn-light-warning edit-education-btn"
                                    data-education-id="{{ $edu->id }}"
                                    data-edit-modal-id="editEducationModal{{ $edu->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-light-danger delete-education-btn"
                                    data-delete-modal-id="deleteEducationModal{{ $edu->id }}">
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
                        No data available.
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
        // helper "DOM ready" tanpa jQuery
        function onReady(fn) {
            if (document.readyState !== 'loading') fn();
            else document.addEventListener('DOMContentLoaded', fn);
        }

        onReady(function() {
            // guard supaya gak error kalau script ini ketemu halaman yang belum load jQuery/Bootstrap
            if (typeof bootstrap === 'undefined' || typeof window.$ === 'undefined') {
                console.warn(
                    '[detailEducationModal] bootstrap/jQuery belum siap. Pastikan script ini dirender SETELAH plugins.bundle.js'
                    );
                return;
            }

            // state untuk simpan backdrop dan instance parent modal
            let parentBackdropEl = null;
            let parentModalInstance = null;

            function openChildModal(childSelector) {
                const parentModalEl = document.getElementById("detailEducationModal");
                if (!parentModalEl) return;

                // Ambil / buat instance modal parent
                parentModalInstance = bootstrap.Modal.getInstance(parentModalEl);
                if (!parentModalInstance) {
                    parentModalInstance = new bootstrap.Modal(parentModalEl);
                }

                // Simpan backdrop aktif (backdrop parent yang lagi tampil)
                parentBackdropEl = document.querySelector('.modal-backdrop.show');

                // Sembunyikan modal parent
                parentModalInstance.hide();

                // Setelah parent hidden, kita munculin modal child
                setTimeout(() => {
                    const childEl = document.querySelector(childSelector);
                    if (!childEl) {
                        console.warn('[detailEducationModal] Target modal tidak ditemukan:',
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
                    // backdrop parent diturunin z-index biar gak nutup child
                    // child dinaikin z-index supaya tombolnya bisa diklik
                    if (parentBackdropEl) {
                        parentBackdropEl.dataset._origZ = parentBackdropEl.style.zIndex || '';
                        parentBackdropEl.style.zIndex = '1059';
                    }

                    childEl.style.zIndex = '1060';

                    // Tampilkan modal child (edit / delete)
                    childInstance.show();

                    // Saat child ditutup → balikin parent lagi
                    function handleHidden() {
                        // balikin style child
                        childEl.style.zIndex = '';

                        // balikin backdrop parent ke nilai awal
                        if (parentBackdropEl) {
                            parentBackdropEl.style.zIndex = parentBackdropEl.dataset._origZ || '';
                            delete parentBackdropEl.dataset._origZ;
                        }

                        // show lagi modal parent (detailEducationModal)
                        if (parentModalInstance) {
                            parentModalInstance.show();
                        }

                        // cleanup listener supaya gak numpuk setiap klik
                        childEl.removeEventListener('hidden.bs.modal', handleHidden);
                    }

                    childEl.addEventListener('hidden.bs.modal', handleHidden);
                }, 100);
            }

            // klik tombol EDIT education
            $(document).on("click", ".edit-education-btn", function() {
                const modalId = $(this).data("edit-modal-id"); // ex: "editEducationModal5"
                if (!modalId) return;
                openChildModal("#" + modalId);
            });

            // klik tombol DELETE education
            $(document).on("click", ".delete-education-btn", function() {
                const modalId = $(this).data("delete-modal-id"); // ex: "deleteEducationModal5"
                if (!modalId) return;
                openChildModal("#" + modalId);
            });
        });
    })();
</script>
