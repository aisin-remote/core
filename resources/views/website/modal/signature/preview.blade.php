@push('custom-css')
    <style>
        /* checkerboard utk thumbnail (sudah ada) */
        .signature-wrap {
            background:
                conic-gradient(#0000 90deg, #f1f5f9 0 180deg, #0000 0) 0 0 / 12px 12px,
                conic-gradient(#0000 90deg, #e2e8f0 0 180deg, #0000 0) 6px 6px / 12px 12px;
            border-radius: .5rem;
            padding: .5rem;
        }

        #signature-view img.img-thumbnail {
            cursor: zoom-in;
        }

        /* ===== Preview Modal ===== */
        #sigZoomContainer {
            position: relative;
            height: clamp(360px, 70vh, 720px);
            /* tinggi responsif */
            overflow: hidden;
            background:
                conic-gradient(#0000 90deg, #f1f5f9 0 180deg, #0000 0) 0 0/14px 14px,
                conic-gradient(#0000 90deg, #e2e8f0 0 180deg, #0000 0) 7px 7px/14px 14px;
            border-radius: .25rem;
        }

        #sigZoomImg {
            position: absolute;
            left: 0;
            top: 0;
            max-width: none;
            /* boleh >100% */
            transform-origin: top left;
            /* transform pixel-based */
            user-select: none;
            -webkit-user-drag: none;
            cursor: grab;
        }

        #sigZoomImg.dragging {
            cursor: grabbing;
        }
    </style>
@endpush
<div class="modal fade" id="signaturePreviewModal" tabindex="-1" aria-labelledby="signaturePreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="signaturePreviewLabel">Signature Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body p-0">
                <div id="sigZoomContainer">
                    <img id="sigZoomImg" src="" alt="Signature Preview">
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <div class="btn-group" role="group" aria-label="zoom controls">
                    <button type="button" class="btn btn-light" id="sigZoomOut">−</button>
                    <button type="button" class="btn btn-light" id="sigZoomReset">100%</button>
                    <button type="button" class="btn btn-light" id="sigZoomIn">+</button>
                    <button type="button" class="btn btn-light" id="sigZoomFit">Fit</button>
                </div>
                <div class="d-flex gap-2">
                    <a id="sigDownload" class="btn btn-secondary" download="signature.png">Download PNG</a>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- end Signature Preview Modal --}}

@push('scripts')
    <script>
        (function() {
            function $(id) {
                return document.getElementById(id);
            }

            function clamp(v, a, b) {
                return Math.max(a, Math.min(b, v));
            }

            var thumb = $('employee-signature-preview');

            // Elemen (diambil saat dibuka)
            var modalEl, container, img, dl, btnIn, btnOut, btnReset, btnFit;

            // State transform (pixel-based)
            var natW = 0,
                natH = 0; // ukuran asli gambar
            var scale = 1; // skala
            var posX = 0,
                posY = 0; // posisi kiri-atas gambar (px) relatif container
            var MIN_SCALE = 0.1,
                MAX_SCALE = 5;

            function ensureRefs() {
                modalEl = $('signaturePreviewModal');
                container = $('sigZoomContainer');
                img = $('sigZoomImg');
                dl = $('sigDownload');
                btnIn = $('sigZoomIn');
                btnOut = $('sigZoomOut');
                btnReset = $('sigZoomReset');
                btnFit = $('sigZoomFit');
            }

            function applyTransform() {
                if (!img) return;
                img.style.transform = 'translate(' + posX + 'px,' + posY + 'px) scale(' + scale + ')';
            }

            function fitToContainer() {
                if (!container || !img) return;
                var cw = container.clientWidth || 1;
                var ch = container.clientHeight || 1;
                if (!natW || !natH) {
                    natW = img.naturalWidth || img.width || cw;
                    natH = img.naturalHeight || img.height || ch;
                }
                var sFit = Math.min(cw / natW, ch / natH);
                scale = Math.min(1, Math.max(MIN_SCALE, sFit));
                var sw = natW * scale,
                    sh = natH * scale;
                posX = Math.round((cw - sw) / 2);
                posY = Math.round((ch - sh) / 2);
                applyTransform();
            }

            function reset100() {
                if (!container) return;
                var cw = container.clientWidth,
                    ch = container.clientHeight;
                scale = 1;
                var sw = natW * scale,
                    sh = natH * scale;
                posX = Math.round((cw - sw) / 2);
                posY = Math.round((ch - sh) / 2);
                applyTransform();
            }

            function clampPan() {
                var cw = container.clientWidth,
                    ch = container.clientHeight;
                var sw = natW * scale,
                    sh = natH * scale;

                if (sw <= cw) posX = Math.round((cw - sw) / 2);
                else posX = clamp(posX, cw - sw, 0);

                if (sh <= ch) posY = Math.round((ch - sh) / 2);
                else posY = clamp(posY, ch - sh, 0);
            }

            // Zoom relatif center container
            function zoomBy(factor) {
                var cw = container.clientWidth,
                    ch = container.clientHeight;
                var cx = cw / 2,
                    cy = ch / 2;

                // posisi titik center terhadap gambar sebelum zoom
                var preRelX = (cx - posX) / scale;
                var preRelY = (cy - posY) / scale;

                scale = clamp(scale * factor, MIN_SCALE, MAX_SCALE);

                // jaga center tetap di posisi yang sama (zoom ke tengah)
                posX = Math.round(cx - preRelX * scale);
                posY = Math.round(cy - preRelY * scale);

                clampPan();
                applyTransform();
            }

            // Pan/drag
            var dragging = false,
                sx = 0,
                sy = 0,
                spx = 0,
                spy = 0;

            function down(e) {
                if (!img) return;
                dragging = true;
                img.classList.add('dragging');
                sx = (e.clientX != null ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0));
                sy = (e.clientY != null ? e.clientY : (e.touches && e.touches[0] ? e.touches[0].clientY : 0));
                spx = posX;
                spy = posY;
                if (e.preventDefault) e.preventDefault();
            }

            function move(e) {
                if (!dragging || !container) return;
                var cx = (e.clientX != null ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0));
                var cy = (e.clientY != null ? e.clientY : (e.touches && e.touches[0] ? e.touches[0].clientY : 0));
                posX = spx + (cx - sx);
                posY = spy + (cy - sy);
                clampPan();
                applyTransform();
                if (e.preventDefault) e.preventDefault();
            }

            function up() {
                dragging = false;
                if (img) img.classList.remove('dragging');
            }

            // Mouse wheel zoom
            function onWheel(e) {
                zoomBy((e.deltaY || e.wheelDelta) > 0 ? 0.9 : 1.1);
                if (e.preventDefault) e.preventDefault();
            }

            // Double click / double tap
            var lastTap = 0;

            function onDblClick(e) {
                zoomBy(1.6);
                if (e.preventDefault) e.preventDefault();
            }

            function onTouchEnd(e) {
                var now = Date.now();
                if (now - lastTap < 300) {
                    zoomBy(1.6);
                    lastTap = 0;
                    if (e.preventDefault) e.preventDefault();
                    return;
                }
                lastTap = now;
            }

            // Buka modal dari thumbnail
            if (thumb) {
                thumb.addEventListener('click', function() {
                    if (thumb.classList && thumb.classList.contains('d-none')) return;

                    ensureRefs();
                    var src = thumb.src;
                    if (!modalEl || !container || !img || !dl) {
                        window.open(src, '_blank');
                        return;
                    }

                    dl.setAttribute('href', src);
                    img.onload = function() {
                        natW = img.naturalWidth || img.width;
                        natH = img.naturalHeight || img.height;
                        fitToContainer();
                    };
                    img.src = src;

                    if (window.bootstrap && bootstrap.Modal) var modalInstance = bootstrap.Modal
                        .getOrCreateInstance(modalEl);
                    modalInstance.show();;
                }, false);
            }

            // Pasang listener sekali saat DOM siap
            document.addEventListener('DOMContentLoaded', function() {
                ensureRefs();
                if (!container || !img) return;

                img.addEventListener('mousedown', down, false);
                img.addEventListener('touchstart', down, false);
                window.addEventListener('mousemove', move, false);
                window.addEventListener('touchmove', move, false);
                window.addEventListener('mouseup', up, false);
                window.addEventListener('touchend', up, false);

                container.addEventListener('wheel', onWheel, {
                    passive: false
                });
                container.addEventListener('dblclick', onDblClick, false);
                container.addEventListener('touchend', onTouchEnd, false);

                if (btnIn) btnIn.addEventListener('click', function() {
                    zoomBy(1.2);
                }, false);
                if (btnOut) btnOut.addEventListener('click', function() {
                    zoomBy(0.8);
                }, false);
                if (btnReset) btnReset.addEventListener('click', function() {
                    reset100();
                }, false);
                if (btnFit) btnFit.addEventListener('click', function() {
                    fitToContainer();
                }, false);

                if (modalEl) {
                    modalEl.addEventListener('shown.bs.modal', function() {
                        if (img && img.complete) fitToContainer();
                    });
                }
                window.addEventListener('resize', function() {
                    if (modalEl && modalEl.classList.contains('show')) fitToContainer();
                }, false);
            });
        })();
    </script>

    <script>
        (function() {
            function killBackdrops() {
                var backs = document.querySelectorAll('.modal-backdrop');
                for (var i = backs.length - 1; i >= 0; i--) {
                    if (backs[i] && backs[i].parentNode) backs[i].parentNode.removeChild(backs[i]);
                }
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }

            // bersihkan setelah SEMUA modal ditutup
            document.addEventListener('hidden.bs.modal', function(e) {
                // beri kesempatan bootstrap remove dulu
                setTimeout(killBackdrops, 10);
            });

            // jaga-jaga kalau ada error/ESC → tombol Close masih meninggalkan backdrop:
            document.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') setTimeout(killBackdrops, 50);
            });
        })();
    </script>
@endpush
