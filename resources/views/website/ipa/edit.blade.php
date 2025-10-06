@extends('layouts.root.main')

@section('title', $title ?? 'IPA - Edit')
@section('breadcrumbs', $title ?? 'IPA - Edit')

@php
    $categories = [
        ['key' => 'activity_management', 'title' => 'I. Activity Management', 'cap' => 70],
        ['key' => 'people_development', 'title' => 'II. People Development', 'cap' => 10],
        ['key' => 'crp', 'title' => 'III. CRP', 'cap' => 10],
        ['key' => 'special_assignment', 'title' => 'IV. Special Assignment & Improvement', 'cap' => 10],
    ];
@endphp

@push('custom-css')
    {{-- Bootstrap Icons untuk ikon ringan --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    {{-- Toastr (toast) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root {
            --bg-soft: #f6f9fc;
            --card: #ffffff;
            --ink: #0f172a;
            --muted: #6b7280;
            --primary: #2563eb;
            --primary-600: #1d4ed8;
            --ring: #b3c7ff;
            --row-hover: #eef2ff;
            --row-alt: #fbfdff;
            --ok: #10b981;
            --warn: #f59e0b;
            --over: #ef4444;
        }

        /* ====== Layout & Header ====== */
        body {
            background: var(--bg-soft);
        }

        .container-xxl {
            max-width: 1360px
        }

        .page-head {
            background: linear-gradient(90deg, #eaf2ff, #fff);
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 6px 20px rgba(24, 39, 75, .06);
        }

        .page-title {
            margin: 0;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: .2px;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .page-title .bi {
            font-size: 1.4rem;
            color: var(--primary);
        }

        .toolbar {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap
        }

        .toolbar .btn {
            border-radius: 12px;
            padding: .55rem .9rem;
            font-weight: 700;
            letter-spacing: .2px
        }

        .toolbar .btn.btn-light {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            color: #0f172a
        }

        .toolbar .btn.btn-add {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff
        }

        .toolbar .btn.btn-add:hover {
            background: var(--primary-600);
            border-color: var(--primary-600)
        }

        .toolbar .btn.btn-primary {
            padding: .55rem 1.05rem
        }

        /* ====== Accordion / Category ====== */
        .accordion.ipa .accordion-item {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: var(--card);
            margin-bottom: 1rem;
            box-shadow: 0 1px 0 rgba(17, 24, 39, .03)
        }

        .accordion.ipa .accordion-button {
            background: #fafafa;
            color: var(--ink);
            font-weight: 800;
            padding: 0.85rem 1rem
        }

        .accordion.ipa .accordion-button:focus {
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .18)
        }

        .accordion.ipa .accordion-button:not(.collapsed) {
            background: #f5f7fb;
            box-shadow: inset 0 -1px 0 #e5e7eb
        }

        /* Warna strip kiri per kategori (mudah dikenali semua umur) */
        .accordion-item[data-cat="activity_management"] {
            border-left: 6px solid #2563eb
        }

        .accordion-item[data-cat="people_development"] {
            border-left: 6px solid #10b981
        }

        .accordion-item[data-cat="crp"] {
            border-left: 6px solid #f59e0b
        }

        .accordion-item[data-cat="special_assignment"] {
            border-left: 6px solid #8b5cf6
        }

        /* ====== CAP badge ====== */
        .cap-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            padding: .22rem .65rem;
            font-weight: 800;
            font-size: .72rem;
            background: #fff;
            color: #111827
        }

        .cap-badge .dot {
            width: .55rem;
            height: .55rem;
            border-radius: 9999px;
            display: inline-block
        }

        .cap-warn {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #9a3412
        }

        .cap-warn .dot {
            background: var(--warn)
        }

        .cap-ok {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46
        }

        .cap-ok .dot {
            background: var(--ok)
        }

        .cap-over {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b
        }

        .cap-over .dot {
            background: var(--over)
        }

        /* ====== Tables ====== */
        .table.ipp-table {
            border-color: #e5e7eb;
            font-size: 1rem
        }

        .table.ipp-table thead th {
            background: #f0f5ff !important;
            color: #111827;
            font-weight: 800;
            border-bottom: 1px solid #e5e7eb
        }

        .table.ipp-table tbody tr:nth-child(even) {
            background: var(--row-alt)
        }

        .table.ipp-table tbody tr:hover {
            background: var(--row-hover)
        }

        .table.ipp-table td,
        .table.ipp-table th {
            padding: .85rem 1rem;
            vertical-align: middle
        }

        .empty-row td {
            padding: 1rem !important;
            color: #6c757d;
            font-style: italic
        }

        /* Status strip di kiri baris (tanpa mengubah kolom) */
        tr[data-status="draft"] {
            box-shadow: inset 4px 0 0 #facc15
        }

        tr[data-status="submitted"] {
            box-shadow: inset 4px 0 0 #60a5fa
        }

        tr[data-status="checked"] {
            box-shadow: inset 4px 0 0 #22d3ee
        }

        tr[data-status="approved"] {
            box-shadow: inset 4px 0 0 #34d399
        }

        /* Buttons kecil */
        .btn-mini {
            padding: .5rem .7rem;
            border-radius: 10px;
            font-weight: 700
        }

        .btn-edit {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e5e7eb
        }

        .btn-edit .bi {
            margin-right: .25rem
        }

        .btn-del {
            background: #fff1f2;
            color: #991b1b;
            border: 1px solid #fecaca
        }

        .btn-del .bi {
            margin-right: .25rem
        }

        .btn-del:hover {
            filter: brightness(.98)
        }

        /* Totals Card */
        .totals-card {
            min-width: 360px;
            border: 1px solid #e5e7eb;
            border-radius: 14px
        }

        .mini-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 99px;
            overflow: hidden
        }

        .mini-bar>span {
            display: block;
            height: 100%;
            width: 0%;
            background: var(--primary);
            transition: width .25s ease
        }

        /* Helper/Legend */
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem
        }

        .legend .pill {
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            background: #fff;
            padding: .15rem .6rem;
            font-weight: 700;
            font-size: .72rem;
            color: #374151;
            display: flex;
            align-items: center;
            gap: .4rem
        }

        .legend .dot {
            width: .55rem;
            height: .55rem;
            border-radius: 9999px;
            display: inline-block
        }

        .legend .d-draft {
            background: #facc15
        }

        .legend .d-submitted {
            background: #60a5fa
        }

        .legend .d-checked {
            background: #22d3ee
        }

        .legend .d-approved {
            background: #34d399
        }

        /* Toast fallback */
        .badge-success {
            background-color: #22c55e;
            color: #fff
        }

        .badge-danger {
            background-color: #ef4444;
            color: #fff
        }

        .badge-warning {
            background-color: #f59e0b;
            color: #212529
        }

        .badge-info {
            background-color: #0ea5e9;
            color: #212529
        }

        .badge-dark {
            background-color: #212529;
            color: #fff
        }

        /* Utility */
        .hidden {
            display: none !important
        }

        .muted {
            color: var(--muted)
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-3 px-6" id="ipa-edit" data-ipa-id="{{ $ipa->id }}"
        data-url-data="{{ route('ipa.data', $ipa->id) }}" data-url-update="{{ route('ipa.update', $ipa->id) }}"
        data-url-recalc="{{ route('ipa.recalc', $ipa->id) }}">

        {{-- Header --}}
        <div class="page-head mb-3">
            <h3 class="page-title">
                <i class="bi bi-clipboard2-check"></i>
                <span>Individual Performance Appraisal</span>
            </h3>
            <div class="toolbar">
                <button class="btn btn-add btn-sm" id="btn-add-activity" title="Tambah Activity">
                    <i class="bi bi-plus-lg text-white"></i> Tambah Activity
                </button>
            </div>
        </div>

        {{-- Legend Status & Info singkat --}}
        <div class="legend mb-3">
            <span class="pill"><span class="dot d-draft"></span> Draft</span>
            <span class="pill"><span class="dot d-submitted"></span> Submitted</span>
            <span class="pill"><span class="dot d-checked"></span> Checked</span>
            <span class="pill"><span class="dot d-approved"></span> Approved</span>
            <span class="pill"><span class="dot" style="background:var(--ok)"></span> CAP Pas</span>
            <span class="pill"><span class="dot" style="background:var(--warn)"></span> CAP Belum Penuh</span>
            <span class="pill"><span class="dot" style="background:var(--over)"></span> Melebihi CAP</span>
        </div>

        {{-- ====== ACCORDION PER KATEGORI ====== --}}
        <div class="accordion ipa" id="accordionIPA">
            @foreach ($categories as $cat)
                <div class="accordion-item" data-cat="{{ $cat['key'] }}" data-cap="{{ $cat['cap'] }}">
                    <h2 class="accordion-header" id="head-{{ $cat['key'] }}">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#col-{{ $cat['key'] }}" aria-expanded="true"
                            aria-controls="col-{{ $cat['key'] }}">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <span>{{ $cat['title'] }}</span>
                                <span class="cap-badge ms-2" data-cat="{{ $cat['key'] }}" title="Total Weight / CAP">
                                    0.00 / {{ $cat['cap'] }}
                                </span>
                            </div>
                        </button>
                    </h2>

                    <div id="col-{{ $cat['key'] }}" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                            <div class="card" style="border-radius:12px;border:1px solid #e5e7eb">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0 ipp-table js-tbl-ipp"
                                        data-cat="{{ $cat['key'] }}">
                                        <thead>
                                            <tr>
                                                <th class="sticky" style="width:280px">Program / Activity</th>
                                                <th class="sticky">One Year Target</th>
                                                <th class="sticky">Weight</th>
                                                <th class="sticky">Score</th>
                                                <th class="sticky">Total Score</th>
                                                <th class="sticky">Status</th>
                                                <th class="sticky" style="width:160px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="js-tbody-ipp">
                                            <tr class="empty-row">
                                                <td colspan="7">Memuat...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-3 py-2 help-line muted">
                                    Baris bertanda <span class="badge-src">Custom</span> adalah activity yang kamu tambahkan
                                    di IPA (tidak mengubah IPP).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="mt-3 d-flex flex-wrap gap-3 justify-content-end">
            <div class="card shadow-sm totals-card">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div><i class="bi bi-trophy"></i> Achievement Total (Σ(W/100×R))</div>
                        <div><strong id="total-achievement">0,00</strong></div>
                    </div>
                    <div class="mini-bar mb-2"><span id="bar-ach"></span></div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div><i class="bi bi-graph-up"></i> Grand Score (Σ R)</div>
                        <div><strong id="total-grand-score">0,00</strong></div>
                    </div>
                    <div class="mini-bar mb-2"><span id="bar-gscore"></span></div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div><i class="bi bi-star"></i> Grand Total</div>
                        <div><strong id="total-grand">0,00</strong></div>
                    </div>
                    <div class="mini-bar"><span id="bar-grand"></span></div>
                </div>
            </div>
            <div class="d-flex align-items-end">
                <button class="btn btn-primary" id="btn-save-bottom" title="Kirim sebagai Submitted">
                    <i class="bi bi-send-check"></i> Submit
                </button>
            </div>
        </div>
    </div>

    {{-- ===== Modal Detail (IPP & Custom) ===== --}}
    @include('website.ipa.modal.point')

    {{-- ===== Modal Tambah Activity (Custom) ===== --}}
    @include('website.ipa.modal.achievement')
@endsection

@push('scripts')
    {{-- jQuery + Toastr --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        (function() {
            const $root = $('#ipa-edit');
            const IPA_ID = Number($root.data('ipa-id') || 0);
            const URL_DATA = $root.data('url-data');
            const URL_UPDATE = $root.data('url-update');
            const URL_RECALC = $root.data('url-recalc');

            // Toast
            function toast(msg, type = 'success') {
                const id = 'toast-' + Date.now();
                const $t = $(`
<div class="toast align-items-center badge-${type} border-0" id="${id}"
     role="status" aria-live="polite" aria-atomic="true"
     style="position:fixed;top:1rem;right:1rem;z-index:1080;">
  <div class="d-flex">
    <div class="toast-body">${esc(msg)}</div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
  </div>
</div>`);
                $('body').append($t);
                const t = new bootstrap.Toast($t[0], {
                    delay: 2200
                });
                t.show();
                $t.on('hidden.bs.toast', () => $t.remove());
            }

            const $tAch = $('#total-achievement');
            const $tGnd = $('#total-grand');
            const $tGScore = $('#total-grand-score');
            const $barAch = $('#bar-ach');
            const $barG = $('#bar-grand');
            const $barGS = $('#bar-gscore');

            // permissions dari backend
            let PERMS = {
                can_add: true,
                can_edit: true,
                can_delete: true,
                can_recalc: true,
                can_submit: true
            };

            // cache
            let IPP_POINTS = [];
            let ACHS = []; // status diambil dari backend (bukan lokal)

            let mdlDetail = new bootstrap.Modal(document.getElementById('modal-ipp-detail'));
            let mdlAdd = new bootstrap.Modal(document.getElementById('modal-add-activity'));

            // utils
            const esc = (s) => String(s || '').replace(/[&<>\"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [m]));
            const fmt = (n) => Number(n || 0).toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });

            function nloc(v) {
                if (typeof v === 'number') return v;
                if (v == null) return 0;
                let s = String(v).trim();
                if (!s) return 0;
                s = s.replace('%', '');
                if (s.includes('.') && s.includes(',')) s = s.replace(/\./g, '').replace(',', '.');
                else if (s.includes(',')) s = s.replace(',', '.');
                s = s.replace(/[^\d.-]/g, '');
                const num = parseFloat(s);
                return isNaN(num) ? 0 : num;
            }
            const $tbody = (cat) => $(`.js-tbl-ipp[data-cat="${cat}"] .js-tbody-ipp`);

            // helper nilai
            const scoreForIpp = id => nloc((ACHS.find(x => Number(x.ipp_point_id) === Number(id))?.self_score) ?? 0);
            const weightForIpp = id => {
                const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(id));
                if (ex && ex.weight != null) return nloc(ex.weight);
                const p = IPP_POINTS.find(pp => Number(pp.id) === Number(id));
                return nloc(p?.weight ?? 0);
            };
            const statusForIpp = id => (ACHS.find(x => Number(x.ipp_point_id) === Number(id))?.status) || null;

            const scoreForCustom = a => nloc(a.self_score ?? 0);
            const weightForCustom = a => nloc(a.weight ?? 0);
            const rowTotal = (Wpct, R) => (nloc(Wpct) / 100) * nloc(R);

            function statusBadgeHtml(achOrStatus) {
                const status = (achOrStatus && typeof achOrStatus === 'object') ? (achOrStatus.status || null) : (
                    achOrStatus || null);
                if (!status) return '';
                const map = {
                    draft: {
                        bg: '#fefce8',
                        bd: '#fde68a',
                        fg: '#854d0e',
                        txt: 'Draft'
                    },
                    submitted: {
                        bg: '#eff6ff',
                        bd: '#bfdbfe',
                        fg: '#1e40af',
                        txt: 'Submitted'
                    },
                    checked: {
                        bg: '#ecfeff',
                        bd: '#a5f3fc',
                        fg: '#0e7490',
                        txt: 'Checked'
                    },
                    approved: {
                        bg: '#ecfdf5',
                        bd: '#a7f3d0',
                        fg: '#065f46',
                        txt: 'Approved'
                    }
                };
                const s = map[String(status).toLowerCase()] || {
                    bg: '#f3f4f6',
                    bd: '#e5e7eb',
                    fg: '#374151',
                    txt: String(status)
                };
                return `<span class="ms-2" style="background:${s.bg};border:1px solid ${s.bd};color:${s.fg};border-radius:8px;padding:.05rem .4rem;font-weight:700;font-size:.7rem">${s.txt}</span>`;
            }

            function actionButtonsHtml() {
                const btnDetail =
                    `<button class="btn btn-sm btn-edit btn-mini js-row-detail" aria-label="Detail"><i class="bi bi-pencil-square"></i> Detail</button>`;
                const btnDelete = PERMS.can_delete ?
                    `<button class="btn btn-sm btn-del btn-mini js-row-delete ms-1" aria-label="Hapus"><i class="bi bi-trash3"></i> Hapus</button>` :
                    '';
                return `<span class="text-nowrap">${btnDetail}${btnDelete}</span>`;
            }

            // render row (tambahkan data-status untuk strip warna kiri)
            function rowIPPHtml(p) {
                const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(p.id)) || null;
                const score = scoreForIpp(p.id);
                const weight = weightForIpp(p.id);
                const total = rowTotal(weight, score);
                const st = (ex?.status || '').toString().toLowerCase();

                return `<tr data-source="ipp" data-ipp-id="${p.id}" data-cat="${esc(p.category||'')}" data-status="${esc(st)}">
                    <td>${esc(p.activity||'-')}</td>
                    <td><div class="fw-semibold">${esc(p.target_one||'(tanpa judul)')}</div></td>
                    <td>${fmt(weight)}%</td>
                    <td>${fmt(score)}</td>
                    <td>${fmt(total)}</td>
                    <td>${statusBadgeHtml(ex)}</td>
                    <td>${actionButtonsHtml()}</td>
                </tr>`;
            }

            function rowCustomHtml(a) {
                const key = a.__key || a.id || '';
                const score = scoreForCustom(a);
                const weight = weightForCustom(a);
                const total = rowTotal(weight, score);
                const st = (a?.status || '').toString().toLowerCase();

                return `<tr data-source="custom" data-ach-key="${esc(key)}" data-cat="${esc(a.category||'')}" data-status="${esc(st)}">
                    <td>${esc(a.title||'(tanpa judul)')} <span class="ms-1 badge-src">Custom</span></td>
                    <td><div class="fw-semibold">${esc(a.one_year_target||'')}</div></td>
                    <td>${fmt(weight)}%</td>
                    <td>${fmt(score)}</td>
                    <td>${fmt(total)}</td>
                    <td>${statusBadgeHtml(a)}</td>
                    <td>${actionButtonsHtml()}</td>
                </tr>`;
            }

            // CAP badge
            function totalWeightByCat(cat) {
                let sum = 0;
                IPP_POINTS.filter(p => (p.category || '') === cat).forEach(p => {
                    const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(p.id));
                    sum += nloc(ex?.weight ?? p.weight ?? 0);
                });
                ACHS.filter(x => !x.ipp_point_id && (x.category || '') === cat).forEach(c => sum += nloc(c.weight ??
                    0));
                return sum;
            }

            function updateCapBadges() {
                $('.accordion-item[data-cat][data-cap]').each(function() {
                    const cat = String($(this).data('cat') || '');
                    const cap = nloc($(this).data('cap'));
                    const sum = totalWeightByCat(cat);
                    const $badge = $(`.cap-badge[data-cat="${cat}"]`);
                    if (!$badge.length) return;
                    $badge.removeClass('cap-warn cap-ok cap-over');
                    const EPS = 0.0001;
                    let cls = 'cap-warn';
                    if (sum > cap + EPS) cls = 'cap-over';
                    else if (Math.abs(sum - cap) <= EPS) cls = 'cap-ok';
                    $badge.addClass(cls);
                    if ($badge.find('.dot').length === 0) $badge.prepend('<span class="dot"></span>');
                    $badge.contents().filter(function() {
                        return this.nodeType === 3;
                    }).remove();
                    $badge.append(document.createTextNode(' ' + fmt(sum) + '% / ' + fmt(cap) + '%'));
                });
            }

            // Laporan CAP kategori (untuk validasi submit)
            function getAllCategoriesMeta() {
                return $('.accordion-item[data-cat][data-cap]').map(function() {
                    return {
                        key: String($(this).data('cat')),
                        cap: nloc($(this).data('cap'))
                    };
                }).get();
            }

            function categoriesCapReport() {
                const EPS = 0.0001;
                return getAllCategoriesMeta().map(({
                    key,
                    cap
                }) => {
                    const sum = totalWeightByCat(key);
                    let state = 'exact';
                    if (sum > cap + EPS) state = 'over';
                    else if (Math.abs(sum - cap) > EPS) state = 'under';
                    return {
                        key,
                        cap,
                        sum,
                        ok: state === 'exact',
                        state
                    };
                });
            }

            function catTitle(key) {
                const $item = $(`.accordion-item[data-cat="${key}"]`);
                const title = $item.find('.accordion-button span:first').text().trim();
                return title || key;
            }

            function allCategoriesAtCap() {
                return categoriesCapReport().every(r => r.ok);
            }

            function renderByCat(cat) {
                const $tb = $tbody(cat);
                const ippRows = IPP_POINTS.filter(p => (p.category || '') === cat);
                const custRows = ACHS.filter(x => !x.ipp_point_id && (x.category || '') === cat);
                let html = '';
                if (ippRows.length) html += ippRows.map(rowIPPHtml).join('');
                if (custRows.length) html += custRows.map(rowCustomHtml).join('');
                if (!html) html = '<tr class="empty-row"><td colspan="7">Belum ada item.</td></tr>';
                $tb.html(html);
            }

            function renderAll() {
                $('[data-cat]').each(function() {
                    renderByCat($(this).data('cat'));
                });
                recalcTotals();
                updateCapBadges();
                applyPermissions();
                updateSubmitButtons();
            }

            // totals
            function recalcTotals() {
                let total = 0,
                    sumR = 0;
                IPP_POINTS.forEach(p => {
                    const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(p.id));
                    total += (nloc(ex?.weight ?? p.weight ?? 0) / 100) * nloc(ex?.self_score ?? 0);
                    sumR += nloc(ex?.self_score ?? 0);
                });
                ACHS.filter(x => !x.ipp_point_id).forEach(c => {
                    total += (nloc(c.weight ?? 0) / 100) * nloc(c.self_score ?? 0);
                    sumR += nloc(c.self_score ?? 0);
                });
                $tAch.text(fmt(total));
                $tGnd.text(fmt(total));
                $tGScore.text(fmt(sumR));
                const scale = v => Math.max(0, Math.min(100, (v / 10) * 100));
                $barAch.css('width', scale(total) + '%');
                $barG.css('width', scale(total) + '%');
                $barGS.css('width', Math.max(0, Math.min(100, (sumR / 10) * 100)) + '%');
            }

            // Submit button rules
            function areAllPointsDraftStrict() {
                if (!Array.isArray(ACHS)) return false;
                const everyIPPHasDraft = IPP_POINTS.every(p => {
                    const ex = ACHS.find(a => Number(a.ipp_point_id) === Number(p.id));
                    return !!ex && String(ex.status || '').toLowerCase() === 'draft';
                });
                if (!everyIPPHasDraft) return false;
                const allCustomDraft = ACHS.filter(a => !a.ipp_point_id)
                    .every(a => String(a.status || '').toLowerCase() === 'draft');
                return allCustomDraft;
            }

            function updateSubmitButtons() {
                const $btns = $('#btn-save, #btn-save-bottom');
                if (!PERMS.can_submit) {
                    $btns.addClass('hidden').prop('disabled', true);
                    return;
                }
                $btns.removeClass('hidden');

                const allDraft = areAllPointsDraftStrict();
                const capsOk = allCategoriesAtCap();
                const enabled = allDraft && capsOk;

                $btns.prop('disabled', !enabled);

                if (!allDraft && !capsOk) {
                    $btns.attr('title', 'Semua poin harus Draft dan seluruh kategori harus pas dengan CAP.');
                } else if (!allDraft) {
                    $btns.attr('title', 'Semua poin harus berstatus Draft.');
                } else if (!capsOk) {
                    $btns.attr('title', 'Seluruh kategori harus pas sesuai CAP.');
                } else {
                    $btns.removeAttr('title');
                }
            }

            // Permissions dari backend
            function applyPermissions() {
                $('#btn-add-activity').toggle(!!PERMS.can_add);
                $('#btn-recalc').toggle(!!PERMS.can_recalc);
                $('#btn-save, #btn-save-bottom').toggle(!!PERMS.can_submit);
                $('#ippd-btn-save').prop('disabled', !PERMS.can_edit);
            }

            // LOAD
            function initLoad() {
                $('.js-tbody-ipp').html('<tr class="empty-row"><td colspan="7">Memuat...</td></tr>');
                $.getJSON(URL_DATA).done(function(res) {
                    if (!res || !res.ok) {
                        toast('Gagal memuat data.', 'danger');
                        return;
                    }
                    const d = res.data || {};

                    IPP_POINTS = Array.isArray(d.ipp_points) ? d.ipp_points.map(p => ({
                        id: p.id,
                        activity: p.activity || p.title,
                        title: p.title,
                        target_one: p.target_one,
                        weight: nloc(p.weight || 0),
                        category: p.category
                    })) : [];

                    ACHS = Array.isArray(d.achievements) ? d.achievements.map(x => ({
                        id: x.id || null,
                        ipp_point_id: x.ipp_point_id || null,
                        category: x.category || null,
                        title: x.title || null,
                        one_year_target: x.one_year_target || '',
                        one_year_achievement: x.one_year_achievement || x.description || '',
                        weight: nloc(x.weight ?? 0),
                        self_score: nloc(x.self_score || 0),
                        status: x.status || null
                    })) : [];

                    PERMS = d.permissions || derivePermsFromHeader(d.header?.status);
                    renderAll();
                }).fail(function() {
                    toast('Error server saat memuat.', 'danger');
                });
            }

            function derivePermsFromHeader(headerStatus) {
                const s = String(headerStatus || 'draft').toLowerCase();
                if (['submitted', 'checked', 'approved'].includes(s)) {
                    return {
                        can_add: false,
                        can_edit: false,
                        can_delete: false,
                        can_recalc: false,
                        can_submit: false
                    };
                }
                return {
                    can_add: true,
                    can_edit: true,
                    can_delete: true,
                    can_recalc: true,
                    can_submit: true
                };
            }

            // DETAIL
            $(document).on('click', '.js-row-detail', function() {
                const $tr = $(this).closest('tr');
                const source = ($tr.data('source') || '').toString();
                $('#ippd-source').val(source);
                $('#ippd-ach-key').val('');
                $('#ippd-id').val('');

                if (source === 'ipp') {
                    const id = $tr.data('ipp-id');
                    const p = IPP_POINTS.find(x => Number(x.id) === Number(id));
                    if (!p) {
                        toast('Data tidak ditemukan.', 'danger');
                        return;
                    }
                    const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(id)) || null;

                    $('#ippd-id').val(id);
                    $('#ippd-category').val(p.category || '').prop('readonly', true);
                    $('#ippd-activity').val(p.activity || p.title || '').prop('readonly', true);
                    $('#ippd-target').val(p.target_one || '').prop('readonly', true);
                    $('#ippd-weight').val(fmt(nloc(ex?.weight ?? p.weight ?? 0)));
                    $('#ippd-score').val(nloc(ex?.self_score || 0));
                    $('#ippd-achv').val(ex?.one_year_achievement || '');
                } else {
                    const key = ($tr.data('ach-key') || '').toString();
                    const a = ACHS.find(x => (!x.ipp_point_id) && (x.__key === key || (x.id && String(x.id) ===
                        key)));
                    if (!a) {
                        toast('Custom activity tidak ditemukan.', 'danger');
                        return;
                    }

                    $('#ippd-ach-key').val(a.__key || a.id || '');
                    $('#ippd-category').val(a.category || '').prop('readonly', true);
                    $('#ippd-activity').val(a.title || '').prop('readonly', false);
                    $('#ippd-target').val(a.one_year_target || '').prop('readonly', false);
                    $('#ippd-weight').val(fmt(nloc(a.weight || 0)));
                    $('#ippd-score').val(nloc(a.self_score || 0));
                    $('#ippd-achv').val(a.one_year_achievement || '');
                }
                $('#ippd-btn-save').prop('disabled', !PERMS.can_edit);
                mdlDetail.show();
            });

            // DETAIL save
            $('#ippd-btn-save').on('click', function() {
                if (!PERMS.can_edit) {
                    toast('Tidak bisa mengedit pada status saat ini.', 'warning');
                    return;
                }
                const source = ($('#ippd-source').val() || '').toString();

                if (source === 'ipp') {
                    const id = Number($('#ippd-id').val());
                    const W = nloc($('#ippd-weight').val());
                    const R = nloc($('#ippd-score').val());
                    const target = ($('#ippd-target').val() || '').trim();
                    const ach = ($('#ippd-achv').val() || '').trim();

                    const payload = {
                        achievements: [{
                            id: (ACHS.find(x => Number(x.ipp_point_id) === id)?.id) || null,
                            ipp_point_id: id,
                            one_year_target: target,
                            weight: W,
                            self_score: R,
                            one_year_achievement: ach,
                            status: 'draft'
                        }]
                    };

                    $.ajax({
                        url: URL_UPDATE,
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: payload,
                        dataType: 'json'
                    }).done(function(res) {
                        if (res && res.ok) {
                            toast('Tersimpan.', 'success');
                            initLoad();
                            mdlDetail.hide();
                        } else toast(res?.message || 'Gagal menyimpan.', 'warning');
                    }).fail(xhr => toast(xhr.responseJSON?.message || 'Error server.', 'danger'));

                } else {
                    const key = ($('#ippd-ach-key').val() || '').toString();
                    const a = ACHS.find(x => (!x.ipp_point_id) && (x.__key === key || (x.id && String(x.id) ===
                        key)));
                    if (!a) {
                        toast('Custom activity tidak ditemukan.', 'danger');
                        return;
                    }

                    const title = ($('#ippd-activity').val() || '').trim();
                    const target = ($('#ippd-target').val() || '').trim();
                    const W = nloc($('#ippd-weight').val());
                    const R = nloc($('#ippd-score').val());
                    const ach = ($('#ippd-achv').val() || '').trim();

                    const payload = {
                        achievements: [{
                            id: a.id || null,
                            category: a.category,
                            title: title,
                            one_year_target: target,
                            weight: W,
                            self_score: R,
                            one_year_achievement: ach,
                            status: 'draft'
                        }]
                    };

                    $.ajax({
                        url: URL_UPDATE,
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: payload,
                        dataType: 'json'
                    }).done(function(res) {
                        if (res && res.ok) {
                            toast('Custom activity diperbarui.', 'success');
                            initLoad();
                            mdlDetail.hide();
                        } else toast(res?.message || 'Gagal menyimpan.', 'warning');
                    }).fail(xhr => toast(xhr.responseJSON?.message || 'Error server.', 'danger'));
                }
            });

            // DELETE
            $(document).on('click', '.js-row-delete', function() {
                if (!PERMS.can_delete) return;
                const $tr = $(this).closest('tr');
                const source = ($tr.data('source') || '').toString();
                if (!confirm('Hapus item ini?')) return;

                if (source === 'ipp') {
                    const ippId = Number($tr.data('ipp-id'));
                    const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(ippId));
                    if (!ex || !ex.id) {
                        toast('Tidak ada data IPA terkait untuk dihapus.', 'warning');
                        return;
                    }

                    $.ajax({
                        url: URL_UPDATE,
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            delete_achievements: [ex.id]
                        },
                        dataType: 'json'
                    }).done(res => {
                        if (res && res.ok) {
                            toast('Override IPA dihapus.', 'success');
                            initLoad();
                        } else toast(res?.message || 'Gagal menghapus.', 'warning');
                    }).fail(xhr => toast(xhr.responseJSON?.message || 'Error server.', 'danger'));

                } else {
                    const key = ($tr.data('ach-key') || '').toString();
                    const a = ACHS.find(x => (!x.ipp_point_id) && (x.__key === key || (x.id && String(x.id) ===
                        key)));
                    if (!a || !a.id) {
                        toast('Custom activity tidak ditemukan/ belum tersimpan.', 'warning');
                        return;
                    }

                    $.ajax({
                        url: URL_UPDATE,
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            delete_achievements: [a.id]
                        },
                        dataType: 'json'
                    }).done(res => {
                        if (res && res.ok) {
                            toast('Custom activity dihapus.', 'success');
                            initLoad();
                        } else toast(res?.message || 'Gagal menghapus.', 'warning');
                    }).fail(xhr => toast(xhr.responseJSON?.message || 'Error server.', 'danger'));
                }
            });

            // ADD open
            $('#btn-add-activity').on('click', function() {
                if (!PERMS.can_add) return;
                $('#add-cat').val('');
                $('#add-activity').val('');
                $('#add-target').val('');
                $('#add-weight').val('0');
                $('#add-score').val('0');
                $('#add-achv').val('');
                mdlAdd.show();
            });

            // ADD save
            $('#add-btn-save').on('click', function() {
                if (!PERMS.can_add) return;

                const cat = ($('#add-cat').val() || '').toString();
                const tit = ($('#add-activity').val() || '').trim();
                const tgt = ($('#add-target').val() || '').trim();
                const W = nloc($('#add-weight').val());
                const R = nloc($('#add-score').val());
                const ach = ($('#add-achv').val() || '').trim();

                if (!cat) {
                    toast('Kategori wajib dipilih.', 'warning');
                    return;
                }
                if (!tit) {
                    toast('Activity wajib diisi.', 'warning');
                    return;
                }
                if (!tgt) {
                    toast('One Year Target wajib diisi.', 'warning');
                    return;
                }

                const payload = {
                    achievements: [{
                        id: null,
                        category: cat,
                        title: tit,
                        one_year_target: tgt,
                        weight: W,
                        self_score: R,
                        one_year_achievement: ach,
                        status: 'draft'
                    }]
                };

                $.ajax({
                    url: URL_UPDATE,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: payload,
                    dataType: 'json'
                }).done(function(res) {
                    toast('Custom activity ditambahkan.', 'success');
                    initLoad();
                    mdlAdd.hide();
                }).fail(xhr => toast(xhr.responseJSON?.message || 'Error server.', 'danger'));
            });

            // Submit All
            function submitAll() {
                if (!PERMS.can_submit) {
                    toast('Tidak dapat submit pada status saat ini.', 'warning');
                    return;
                }

                const allDraft = areAllPointsDraftStrict();
                if (!allDraft) {
                    toast('Semua poin harus berstatus Draft sebelum bisa Submit.', 'warning');
                    return;
                }

                const capIssues = categoriesCapReport().filter(r => !r.ok);
                if (capIssues.length) {
                    const msg = 'Cap kategori belum pas: ' + capIssues.map(i =>
                        `${catTitle(i.key)} (${fmt(i.sum)}% / ${fmt(i.cap)}%)`).join(', ');
                    toast(msg, 'warning');
                    return;
                }

                const achPayload = ACHS.map(a => ({
                    id: a.id || null,
                    ipp_point_id: a.ipp_point_id || null,
                    category: a.category || null,
                    title: a.title || null,
                    one_year_target: a.one_year_target || '',
                    one_year_achievement: a.one_year_achievement || '',
                    weight: nloc(a.weight ?? 0),
                    self_score: nloc(a.self_score ?? 0),
                    status: 'submitted'
                }));
                const payload = {
                    header: {
                        id: IPA_ID,
                        status: 'submitted'
                    },
                    achievements: achPayload
                };

                $.ajax({
                    url: URL_UPDATE,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: payload,
                    dataType: 'json'
                }).done(function(res) {
                    if (res && res.ok) {
                        toast('Berhasil submit.', 'success');
                        initLoad();
                    } else toast(res?.message || 'Gagal submit.', 'warning');
                }).fail(xhr => toast(xhr.responseJSON?.message || 'Error server.', 'danger'));
            }
            $('#btn-save, #btn-save-bottom').off('click').on('click', submitAll);

            // Recalc
            $('#btn-recalc').on('click', function() {
                if (!PERMS.can_recalc) return;
                $.post({
                    url: URL_RECALC,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (res && res.ok && res.totals) {
                        $tAch.text(fmt(res.totals.achievement_total));
                        $tGnd.text(fmt(res.totals.achievement_total));
                        $tGScore.text(fmt(res.totals.grand_score || 0));
                        updateCapBadges();
                        toast('Recalc selesai.');
                    }
                });
            });

            // buka semua accordion (UX: biar langsung kelihatan)
            $('#accordionIPA .accordion-collapse').each(function() {
                this.removeAttribute('data-bs-parent');
                this.classList.add('show');
                const btn = $(this).prev('.accordion-header').find('.accordion-button');
                btn.removeClass('collapsed').attr('aria-expanded', 'true');
            });

            // init
            initLoad();
        })();
    </script>
@endpush
