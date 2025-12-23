@props(['employee_id', 'has_signature' => false])

<div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="signatureModalLabel">Manage Signatures</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <form id="signatureForm" method="POST" enctype="multipart/form-data"
                action="{{ route('employees.signature.store', $employee_id) }}">
                @csrf

                <div class="modal-body">
                    {{-- Nav tabs --}}
                    <ul class="nav nav-tabs" id="sigTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-upload" data-bs-toggle="tab"
                                data-bs-target="#pane-upload" type="button" role="tab">Upload</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-draw" data-bs-toggle="tab" data-bs-target="#pane-draw"
                                type="button" role="tab">Draw</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">
                        {{-- ===== Upload Pane ===== --}}
                        <div class="tab-pane fade show active" id="pane-upload" role="tabpanel"
                            aria-labelledby="tab-upload">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Upload Signature (PNG/JPG/WEBP, max
                                        2MB)</label>
                                    <input type="file" class="form-control" name="signature" id="signatureInput"
                                        accept=".png,.jpg,.jpeg,.webp">
                                    <div class="form-text">Transparent PNG is recommended.</div>
                                    <div class="invalid-feedback" id="signature-error" style="display:none;"></div>

                                    <div class="mt-3">
                                        <label class="form-label fw-semibold">Background removal (fuzz)</label>
                                        <input type="range" id="fuzzInput" name="fuzz" class="form-range"
                                            min="0" max="0.40" step="0.01" value="0.18">
                                        <div class="form-text">
                                            Toleransi: <strong><span id="fuzzValue">0.18</span></strong> (0.00–0.40).
                                        </div>
                                    </div>

                                    <div class="mt-3 form-check">
                                        <input class="form-check-input" type="checkbox" id="forceBlackInput"
                                            name="force_black" value="1">
                                        <label class="form-check-label" for="forceBlackInput">Force black
                                            (pen/pencil)</label>
                                    </div>
                                    <div class="mt-2">
                                        <label class="form-label">Ink strength</label>
                                        <input type="range" id="inkStrengthInput" name="ink_strength" min="0"
                                            max="1" step="0.05" value="0.75">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold d-flex justify-content-between">
                                        Preview (Upload)
                                        <span class="badge bg-light text-muted">Client-side</span>
                                    </label>
                                    <div class="border rounded p-2"
                                        style="background:
                    conic-gradient(#0000 90deg,#f1f5f9 0 180deg,#0000 0) 0 0/12px 12px,
                    conic-gradient(#0000 90deg,#e2e8f0 0 180deg,#0000 0) 6px 6px/12px 12px;">
                                        <canvas id="previewCanvas"
                                            style="display:block;width:100%;max-width:100%;height:auto;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== Draw Pane ===== --}}
                        <div class="tab-pane fade" id="pane-draw" role="tabpanel" aria-labelledby="tab-draw">
                            <input type="hidden" name="from_draw" id="fromDraw" value="0">
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold d-flex justify-content-between">
                                        Draw here
                                        <span class="badge bg-light text-muted">Transparent PNG</span>
                                    </label>
                                    <div class="border rounded p-2" id="drawWrapper"
                                        style="background:
                    conic-gradient(#0000 90deg,#f1f5f9 0 180deg,#0000 0) 0 0/12px 12px,
                    conic-gradient(#0000 90deg,#e2e8f0 0 180deg,#0000 0) 6px 6px/12px 12px;">
                                        <canvas id="drawCanvas"
                                            style="display:block;width:100%;max-width:720px;height:240px;touch-action:none;"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Pen width</label>
                                        <input type="range" id="penWidth" min="1" max="6"
                                            step="0.5" value="2.5">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Pen color</label>
                                        <input type="color" id="penColor" value="#000000"
                                            class="form-control form-control-color">
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" id="btnClearCanvas"
                                            class="btn btn-light">Clear</button>
                                        <button type="button" id="btnUseCanvas" class="btn btn-secondary">Use (fill
                                            form)</button>
                                    </div>
                                    <div class="form-text mt-2">
                                        Klik “Use” untuk menjadikan hasil canvas sebagai file PNG yang akan dikirim.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- ./tab-content -->
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <div class="d-flex gap-2">
                        <button type="button" id="btnDeleteSignature"
                            class="btn btn-danger {{ $has_signature ? '' : 'd-none' }}">Delete</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveSignature">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            /* ================== DOM Refs ================== */
            var form = document.getElementById('signatureForm');
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
            var signatureError = document.getElementById('signature-error');
            var btnSave = document.getElementById('btnSaveSignature');
            var btnDelete = document.getElementById('btnDeleteSignature');

            var fileInput = document.getElementById('signatureInput');
            var fuzzInput = document.getElementById('fuzzInput');
            var fuzzValue = document.getElementById('fuzzValue');
            var forceBlack = document.getElementById('forceBlackInput');
            var inkStrengthInp = document.getElementById('inkStrengthInput');
            var prevCanvas = document.getElementById('previewCanvas');

            var tabUploadBtn = document.getElementById('tab-upload');
            var tabDrawBtn = document.getElementById('tab-draw');
            var fromDraw = document.getElementById('fromDraw');

            /* ================== Draw Canvas ================== */
            var drawCanvas = document.getElementById('drawCanvas');
            var ctx = drawCanvas ? drawCanvas.getContext('2d') : null;
            var penWidth = document.getElementById('penWidth');
            var penColor = document.getElementById('penColor');
            var btnClearCanvas = document.getElementById('btnClearCanvas');
            var btnUseCanvas = document.getElementById('btnUseCanvas');
            var canvasBlob = null;

            function get2d(canvas, opts) {
                if (!canvas) return null;
                try {
                    return canvas.getContext('2d', opts || undefined);
                } catch (e) {
                    return canvas.getContext('2d');
                }
            }

            function toast(msg, type) {
                type = type || 'success';
                if (window.toastr && typeof window.toastr[type] === 'function') window.toastr[type](msg);
                else if (window.Swal) window.Swal.fire({
                    icon: type,
                    text: msg,
                    timer: 1800,
                    showConfirmButton: false
                });
                else alert((type === 'success' ? '' : 'Error: ') + msg);
            }

            function setLoading(el, loading) {
                if (!el) return;
                el.disabled = !!loading;
                el.dataset.originalText = el.dataset.originalText || el.innerHTML;
                el.innerHTML = loading ? 'Memproses…' : el.dataset.originalText;
            }

            function showSignature(url) {
                var img = document.getElementById('employee-signature-preview');
                var empty = document.getElementById('signature-empty-state');
                if (!img) return;

                if (url) {
                    img.src = url + (url.indexOf('?') > -1 ? '&' : '?') + 't=' + Date.now();
                    img.classList.remove('d-none');
                    if (empty) empty.classList.add('d-none');
                    if (btnDelete) btnDelete.classList.remove('d-none');
                } else {
                    img.src = '';
                    img.classList.add('d-none');
                    if (empty) empty.classList.remove('d-none');
                    if (btnDelete) btnDelete.classList.add('d-none');
                }
            }

            /* ========== Upload Preview: adaptive remove-bg + force-black (client-side) ========== */
            var srcImg = null;

            function buildBgEstimate(img, w, h) {
                var scale = 8;
                var sw = Math.max(1, Math.round(w / scale));
                var sh = Math.max(1, Math.round(h / scale));

                var tmp = document.createElement('canvas');
                tmp.width = sw;
                tmp.height = sh;

                var tctx = get2d(tmp, {
                    willReadFrequently: true
                });
                tctx.imageSmoothingEnabled = true;
                tctx.imageSmoothingQuality = 'high';
                tctx.drawImage(img, 0, 0, sw, sh);

                for (var i = 0; i < 4; i++) {
                    tctx.filter = 'blur(1.6px)';
                    tctx.drawImage(tmp, 0, 0);
                }
                tctx.filter = 'none';

                var bg = document.createElement('canvas');
                bg.width = w;
                bg.height = h;

                var bctx = get2d(bg, {
                    willReadFrequently: true
                });
                bctx.imageSmoothingEnabled = true;
                bctx.imageSmoothingQuality = 'high';
                bctx.drawImage(tmp, 0, 0, sw, sh, 0, 0, w, h);

                return bctx.getImageData(0, 0, w, h);
            }

            function renderPreview() {
                if (!srcImg || !prevCanvas) return;

                var fuzz = Number(fuzzInput ? fuzzInput.value : 0.18) || 0.18;
                var useBlack = !!(forceBlack && forceBlack.checked);
                var inkStrength = Number(inkStrengthInp ? inkStrengthInp.value : 0.75) || 0.75;

                var maxH = 400;
                var w = srcImg.naturalWidth || srcImg.width;
                var h = srcImg.naturalHeight || srcImg.height;
                if (!w || !h) return;

                if (h > maxH) {
                    var r0 = maxH / h;
                    w = Math.round(w * r0);
                    h = Math.round(h * r0);
                }

                prevCanvas.width = w;
                prevCanvas.height = h;

                var ctxP = get2d(prevCanvas, {
                    willReadFrequently: true
                });
                if (!ctxP) return;

                // tampilkan original dulu (anti blank)
                ctxP.clearRect(0, 0, w, h);
                ctxP.drawImage(srcImg, 0, 0, w, h);

                try {
                    var src = ctxP.getImageData(0, 0, w, h);
                    var out = ctxP.createImageData(w, h);
                    var bg = buildBgEstimate(srcImg, w, h);

                    var tDiff = Math.round(25 + 160 * fuzz);
                    var soft = Math.max(5, Math.round(tDiff * 0.6));
                    var hard = tDiff + 30;

                    var hasInk = false;

                    for (var i = 0; i < src.data.length; i += 4) {
                        var r = src.data[i],
                            g = src.data[i + 1],
                            b = src.data[i + 2];

                        var rb = bg.data[i],
                            gb = bg.data[i + 1],
                            bb = bg.data[i + 2];
                        var grayS = Math.round(0.299 * r + 0.587 * g + 0.114 * b);
                        var grayB = Math.round(0.299 * rb + 0.587 * gb + 0.114 * bb);
                        var diff = grayB - grayS;

                        var alpha;
                        if (diff <= soft) alpha = 0;
                        else if (diff >= hard) alpha = 255;
                        else {
                            var t = (diff - soft) / Math.max(1, (hard - soft));
                            alpha = Math.round(255 * t);
                        }

                        if (alpha > 10) hasInk = true;

                        if (useBlack && alpha > 0) {
                            var gray = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                            var gamma = 1 + 1.2 * inkStrength;
                            var darkF = 1 - 0.55 * inkStrength;
                            var val = Math.max(0, Math.min(255, Math.round(255 * Math.pow(gray, gamma) * darkF)));
                            r = g = b = val;
                        }

                        out.data[i] = r;
                        out.data[i + 1] = g;
                        out.data[i + 2] = b;
                        out.data[i + 3] = alpha;
                    }

                    // kalau hasilnya kosong, biarkan original (sudah ter-draw)
                    if (!hasInk) return;

                    ctxP.putImageData(out, 0, 0);
                } catch (err) {
                    console.warn('renderPreview processing failed:', err);
                }
            }

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    var f = fileInput.files && fileInput.files[0];
                    if (!f) return;

                    if (signatureError) signatureError.style.display = 'none';

                    if (['image/png', 'image/jpeg', 'image/webp'].indexOf(f.type) === -1) {
                        if (signatureError) {
                            signatureError.textContent = 'Format harus PNG/JPG/WEBP.';
                            signatureError.style.display = 'block';
                        }
                        return;
                    }
                    if (f.size > 2 * 1024 * 1024) {
                        if (signatureError) {
                            signatureError.textContent = 'Ukuran maksimal 2MB.';
                            signatureError.style.display = 'block';
                        }
                        return;
                    }

                    var url = URL.createObjectURL(f);
                    var img = new Image();
                    img.onload = function() {
                        srcImg = img;
                        renderPreview();
                        URL.revokeObjectURL(url);
                    };
                    img.src = url;
                });
            }

            if (fuzzInput && fuzzValue) {
                fuzzInput.addEventListener('input', function() {
                    fuzzValue.textContent = Number(fuzzInput.value).toFixed(2);
                    renderPreview();
                });
            }
            if (forceBlack) forceBlack.addEventListener('change', renderPreview);
            if (inkStrengthInp) inkStrengthInp.addEventListener('input', renderPreview);

            /* ========== Draw: resize saat tab benar2 tampil ========== */
            function setModeDraw(on) {
                if (fromDraw) fromDraw.value = on ? '1' : '0';
                if (fuzzInput) fuzzInput.disabled = !!on;
            }

            function clearCanvas() {
                if (!drawCanvas || !ctx) return;
                ctx.clearRect(0, 0, drawCanvas.width, drawCanvas.height);
                // setelah clear, setTransform masih ok
                canvasBlob = null;
            }

            function resizeDrawCanvas() {
                if (!drawCanvas) return;

                var rect = drawCanvas.getBoundingClientRect();
                var cssW = rect.width || 720;
                var cssH = rect.height || 240;

                var dpr = window.devicePixelRatio || 1;
                drawCanvas.width = Math.round(cssW * dpr);
                drawCanvas.height = Math.round(cssH * dpr);

                ctx = get2d(drawCanvas, {
                    willReadFrequently: true
                });
                if (!ctx) return;

                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';

                clearCanvas();
            }

            function onDrawTabShown() {
                setModeDraw(true);
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        resizeDrawCanvas();
                    });
                });
            }

            function onUploadTabShown() {
                setModeDraw(false);
            }

            if (tabDrawBtn) tabDrawBtn.addEventListener('shown.bs.tab', onDrawTabShown);
            if (tabUploadBtn) tabUploadBtn.addEventListener('shown.bs.tab', onUploadTabShown);
            setModeDraw(false);

            // ===== Drawing events
            var drawing = false,
                lastX = 0,
                lastY = 0;

            function getXY(e) {
                var rect = drawCanvas.getBoundingClientRect();
                var cx = (e.clientX != null ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0));
                var cy = (e.clientY != null ? e.clientY : (e.touches && e.touches[0] ? e.touches[0].clientY : 0));
                return {
                    x: cx - rect.left,
                    y: cy - rect.top
                };
            }

            function start(e) {
                if (!drawCanvas || !ctx) return;
                drawing = true;
                var p = getXY(e);
                lastX = p.x;
                lastY = p.y;
                e.preventDefault && e.preventDefault();
            }

            function move(e) {
                if (!drawing || !drawCanvas || !ctx) return;
                var p = getXY(e);
                var color = penColor ? (penColor.value || '#000000') : '#000000';
                var width = penWidth ? Number(penWidth.value || 2.5) : 2.5;

                ctx.strokeStyle = color;
                ctx.lineWidth = width;
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();

                lastX = p.x;
                lastY = p.y;
                e.preventDefault && e.preventDefault();
            }

            function end() {
                drawing = false;
            }

            if (drawCanvas) {
                drawCanvas.addEventListener('pointerdown', start, false);
                drawCanvas.addEventListener('pointermove', move, false);
                window.addEventListener('pointerup', end, false);

                drawCanvas.addEventListener('mousedown', start, false);
                drawCanvas.addEventListener('mousemove', move, false);
                window.addEventListener('mouseup', end, false);

                drawCanvas.addEventListener('touchstart', start, false);
                drawCanvas.addEventListener('touchmove', move, false);
                window.addEventListener('touchend', end, false);
            }

            if (btnClearCanvas) btnClearCanvas.addEventListener('click', function() {
                clearCanvas();
            }, false);

            function canvasToBlob(canvas, cb) {
                if (!canvas) return cb(null);
                if (canvas.toBlob) return canvas.toBlob(function(b) {
                    cb(b);
                }, 'image/png');
                var dataURL = canvas.toDataURL('image/png');
                var parts = dataURL.split(',');
                var bstr = atob(parts[1]);
                var n = bstr.length;
                var u8 = new Uint8Array(n);
                for (var i = 0; i < n; i++) u8[i] = bstr.charCodeAt(i);
                cb(new Blob([u8], {
                    type: 'image/png'
                }));
            }

            function canvasToBlobAsync(canvas) {
                return new Promise(function(resolve) {
                    canvasToBlob(canvas, function(b) {
                        resolve(b);
                    });
                });
            }

            if (btnUseCanvas) {
                btnUseCanvas.addEventListener('click', async function() {
                    var b = await canvasToBlobAsync(drawCanvas);
                    canvasBlob = b;
                    toast('Canvas siap dikirim sebagai PNG.');
                }, false);
            }

            /* ================== Submit (UPLOAD & DRAW) ================== */
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    if (signatureError) signatureError.style.display = 'none';
                    setLoading(btnSave, true);

                    try {
                        var fd = new FormData(form);

                        if (fromDraw && fromDraw.value === '1') {
                            // DRAW MODE: kirim canvas
                            var b = canvasBlob || await canvasToBlobAsync(drawCanvas);
                            if (!b) {
                                toast('Canvas kosong / gagal dibuat. Coba gambar ulang.', 'error');
                                setLoading(btnSave, false);
                                return;
                            }
                            fd.delete('signature');
                            fd.append('signature', b, 'canvas-signature.png');
                            // from_draw sudah 1 → backend skip remove-bg
                        } else {
                            // UPLOAD MODE: kirim hasil previewCanvas (yang sudah diproses)
                            var f = fileInput && fileInput.files ? fileInput.files[0] : null;
                            if (!f) {
                                if (signatureError) {
                                    signatureError.textContent = 'Silakan pilih file tanda tangan.';
                                    signatureError.style.display = 'block';
                                }
                                setLoading(btnSave, false);
                                return;
                            }

                            if (prevCanvas && prevCanvas.width > 0 && prevCanvas.height > 0) {
                                var pb = await canvasToBlobAsync(prevCanvas);
                                fd.delete('signature');

                                if (pb) {
                                    fd.append('signature', pb, 'upload-signature.png');

                                    // FLAG: backend jangan remove-bg lagi
                                    fd.append('processed_on_client', '1');
                                } else {
                                    // fallback ke file asli
                                    fd.append('signature', f, f.name || 'signature.png');
                                }
                            }
                        }

                        fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json'
                                },
                                body: fd
                            })
                            .then(function(res) {
                                return res.text().then(function(t) {
                                    return {
                                        ok: res.ok,
                                        text: t
                                    };
                                });
                            })
                            .then(function(resp) {
                                var ok = resp.ok,
                                    text = resp.text,
                                    data;
                                try {
                                    data = JSON.parse(text);
                                } catch (err) {
                                    data = {
                                        message: text
                                    };
                                }

                                if (!ok) {
                                    if (data && data.errors && data.errors.signature &&
                                        signatureError) {
                                        signatureError.textContent = data.errors.signature.join(', ');
                                        signatureError.style.display = 'block';
                                    } else {
                                        toast(data.message || 'Gagal menyimpan tanda tangan', 'error');
                                    }
                                    return;
                                }

                                if (data && data.url) showSignature(data.url);
                                toast('Tanda tangan berhasil disimpan.');

                                var modalEl = document.getElementById('signatureModal');
                                if (modalEl && window.bootstrap && bootstrap.Modal.getInstance) {
                                    var instance = bootstrap.Modal.getInstance(modalEl);
                                    if (instance) instance.hide();
                                }

                                // reset
                                form.reset();
                                canvasBlob = null;
                                srcImg = null;

                                if (prevCanvas) {
                                    var pctx = get2d(prevCanvas, {
                                        willReadFrequently: true
                                    });
                                    pctx && pctx.clearRect(0, 0, prevCanvas.width, prevCanvas.height);
                                }
                                clearCanvas();
                            })
                            .catch(function(err) {
                                console.error(err);
                                toast('Terjadi kesalahan jaringan', 'error');
                            })
                            .finally(function() {
                                setLoading(btnSave, false);
                            });

                    } catch (err) {
                        console.error(err);
                        toast('Terjadi kesalahan', 'error');
                        setLoading(btnSave, false);
                    }
                }, false);
            }

            /* ================== Delete ================== */
            if (btnDelete) {
                btnDelete.addEventListener('click', function() {
                    if (!confirm('Hapus tanda tangan ini?')) return;
                    setLoading(btnDelete, true);

                    fetch(form.action, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            }
                        })
                        .then(function(res) {
                            return res.text().then(function(t) {
                                return {
                                    ok: res.ok,
                                    text: t
                                };
                            });
                        })
                        .then(function(resp) {
                            var ok = resp.ok,
                                text = resp.text,
                                data;
                            try {
                                data = JSON.parse(text);
                            } catch (err) {
                                data = {
                                    message: text
                                };
                            }
                            if (!ok) {
                                toast(data.message || 'Gagal menghapus tanda tangan', 'error');
                                return;
                            }

                            showSignature('');
                            toast('Tanda tangan dihapus');

                            var modalEl = document.getElementById('signatureModal');
                            if (modalEl && window.bootstrap && bootstrap.Modal.getInstance) {
                                var instance = bootstrap.Modal.getInstance(modalEl);
                                if (instance) instance.hide();
                            }
                        })
                        .catch(function(err) {
                            console.error(err);
                            toast('Terjadi kesalahan jaringan', 'error');
                        })
                        .finally(function() {
                            setLoading(btnDelete, false);
                        });
                }, false);
            }

            // Pastikan canvas di-resize saat modal dibuka jika tab Draw aktif
            var modalEl = document.getElementById('signatureModal');
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', function() {
                    var drawPane = document.getElementById('pane-draw');
                    if (drawPane && drawPane.classList.contains('active')) {
                        requestAnimationFrame(function() {
                            requestAnimationFrame(function() {
                                resizeDrawCanvas();
                            });
                        });
                    }
                });
                modalEl.addEventListener('hidden.bs.modal', function() {
                    if (signatureError) signatureError.style.display = 'none';
                });
            }

        })();
    </script>
@endpush
