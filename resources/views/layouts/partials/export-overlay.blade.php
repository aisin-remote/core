@push('custom-css')
    <style>
        .download-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(2px);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center
        }

        .download-overlay.show {
            display: flex
        }

        .download-box {
            background: #fff;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            width: min(92vw, 420px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .2);
            text-align: center
        }

        .download-spinner {
            width: 44px;
            height: 44px;
            margin: 0 auto 12px;
            border: 4px solid #e2e8f0;
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: spin 1s linear infinite
        }

        .download-title {
            font-weight: 700;
            color: #111827;
            margin-bottom: .25rem;
            font-size: 1.05rem
        }

        .download-desc {
            font-size: .9rem;
            color: #475569;
            margin-bottom: .5rem
        }

        .download-hint {
            font-size: .78rem;
            color: #64748b
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }
    </style>
@endpush

{{-- Backdrop/Overlay --}}
<div id="downloadOverlay" class="download-overlay" aria-hidden="true">
    <div class="download-box">
        <div class="download-spinner" aria-hidden="true"></div>
        <div class="download-title" id="downloadTitle">Menyiapkan file...</div>
        <div class="download-desc" id="downloadDesc">Mohon tunggu, proses export sedang berjalan.</div>
        <div class="download-hint">Jangan tutup halaman ini sampai unduhan dimulai.</div>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const overlay = document.getElementById('downloadOverlay');
            const oTitle = document.getElementById('downloadTitle');
            const oDesc = document.getElementById('downloadDesc');

            function showOverlay(kind = 'File', desc = 'Mohon tunggu, file sedang dibuat oleh server...') {
                oTitle.textContent = `Menyiapkan ${kind}...`;
                oDesc.textContent = desc;
                overlay.classList.add('show');
                overlay.setAttribute('aria-hidden', 'false');
            }

            function hideOverlay() {
                overlay.classList.remove('show');
                overlay.setAttribute('aria-hidden', 'true');
            }
            window.addEventListener('beforeunload', () => hideOverlay());

            function getFilenameFromDisposition(disposition) {
                if (!disposition) return null;
                const star = /filename\*\=([^']*)''([^;]+)/i.exec(disposition);
                if (star && star[2]) return decodeURIComponent(star[2]);
                const plain = /filename\=\"?([^\";]+)\"?/i.exec(disposition);
                return plain ? plain[1] : null;
            }

            function getFilenameFromUrl(url, fallback = 'download') {
                try {
                    const u = new URL(url, window.location.origin);
                    const last = u.pathname.split('/').filter(Boolean).pop();
                    return last || fallback;
                } catch {
                    return fallback;
                }
            }

            function guessKind(url, explicit) {
                if (explicit) return explicit;
                const m = (url || '').toLowerCase();
                if (m.endsWith('.pdf') || m.includes('export.pdf')) return 'PDF';
                if (m.endsWith('.xlsx') || m.includes('export.excel') || m.includes('export.xlsx')) return 'Excel';
                return 'File';
            }

            async function downloadViaFetch(url, kind, fallbackName) {
                showOverlay(kind);
                try {
                    const res = await fetch(url, {
                        method: 'GET',
                        credentials: 'include'
                    });
                    if (!res.ok) {
                        let msg = `Export ${kind} gagal (HTTP ${res.status}).`;
                        try {
                            const ct = res.headers.get('Content-Type') || '';
                            if (ct.includes('application/json')) {
                                const j = await res.json();
                                msg = j.message || msg;
                            } else {
                                const t = await res.text();
                                if (t) msg = t;
                            }
                        } catch {}
                        throw new Error(msg);
                    }
                    const disp = res.headers.get('Content-Disposition');
                    const headerName = getFilenameFromDisposition(disp);
                    const urlName = getFilenameFromUrl(url, fallbackName);
                    const filename = headerName || fallbackName || urlName;

                    const blob = await res.blob();
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(blobUrl);
                } catch (err) {
                    console.error(err);
                    hideOverlay();
                    alert(err.message || `Export ${kind} gagal. Coba lagi nanti.`);
                    return;
                }
                hideOverlay();
            }

            // Delegasi klik global:
            // - Elemen dengan class .btn-export
            // - atau [data-export] pada <a>/<button>
            // - (opsional) otomatis intersep link <a> yang punya data-export-auto
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-export,[data-export],[data-export-auto]');
                if (!btn) return;
                // Kalau ini <a> biasa tanpa data-url, ambil href
                const url = btn.getAttribute('data-url') || btn.getAttribute('href');
                if (!url) return;

                e.preventDefault(); // cegah navigasi default
                const kind = guessKind(url, btn.getAttribute('data-kind'));
                const fallback = btn.getAttribute('data-fallback') ||
                    (kind === 'PDF' ? 'export.pdf' : kind === 'Excel' ? 'export.xlsx' : 'download.bin');

                downloadViaFetch(url, kind, fallback);
            });
        })();
    </script>
@endpush
