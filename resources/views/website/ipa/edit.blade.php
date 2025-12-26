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
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    {{-- Toastr --}}
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

        /* Status strip */
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

        .btn-del {
            background: #fff1f2;
            color: #991b1b;
            border: 1px solid #fecaca
        }

        .btn-del:hover {
            filter: brightness(.98)
        }

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

        /* IMPORTANT: hidden untuk sembunyikan tombol submit saat locked */
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
                <a href="{{ route('ipa.export', $ipa->id) }}" class="btn btn-success btn-sm">
                    Export Excel
                </a>
            </div>
        </div>

        {{-- Legend --}}
        <div class="legend mb-3">
            <span class="pill"><span class="dot d-draft"></span> Draft</span>
            <span class="pill"><span class="dot d-submitted"></span> Submitted</span>
            <span class="pill"><span class="dot d-checked"></span> Checked</span>
            <span class="pill"><span class="dot d-approved"></span> Approved</span>
            <span class="pill"><span class="dot" style="background:var(--ok)"></span> CAP Pas</span>
            <span class="pill"><span class="dot" style="background:var(--warn)"></span> CAP Belum Penuh</span>
            <span class="pill"><span class="dot" style="background:var(--over)"></span> Melebihi CAP</span>
        </div>

        {{-- Accordion --}}
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
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Submit bottom --}}
        <div class="mt-3 d-flex flex-wrap gap-3 justify-content-end">
            <div class="d-flex align-items-end">
                <button class="btn btn-primary" id="btn-save-bottom" title="Kirim sebagai Submitted">
                    <i class="bi bi-send-check"></i> Submit
                </button>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    @include('website.ipa.modal.point')
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

            let HEADER_STATUS = ''; // akan di-set dari data load
            const MSG_SUBMITTED_LOCK = 'IPA sudah submitted. Perubahan tidak diperbolehkan.';

            // ===================== Toast =====================
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
                </div>
            `);
                $('body').append($t);

                const t = new bootstrap.Toast($t[0], {
                    delay: 2200
                });
                t.show();
                $t.on('hidden.bs.toast', () => $t.remove());
            }

            // ===================== Helpers =====================
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

            function extractBackendErrors(payload) {
                const msgs = [];
                if (!payload) return msgs;
                if (payload.errors && typeof payload.errors === 'object') {
                    Object.values(payload.errors).forEach(arr => {
                        if (Array.isArray(arr)) arr.forEach(m => msgs.push(String(m)));
                    });
                }
                if (payload.message && typeof payload.message === 'string') msgs.unshift(payload.message);
                return msgs.filter(Boolean);
            }

            /**
             * Derive status keseluruhan:
             * - Jika headerStatus ada → pakai header
             * - Jika headerStatus kosong → derive dari achievements (priority)
             */
            function deriveOverallStatus(headerStatus, achievements) {
                const hs = String(headerStatus || '').toLowerCase().trim();
                if (hs) return hs;

                const list = (Array.isArray(achievements) ? achievements : [])
                    .map(a => String(a?.status || '').toLowerCase().trim())
                    .filter(Boolean);

                // PRIORITY
                if (list.includes('approved')) return 'approved';
                if (list.includes('checked')) return 'checked';
                if (list.includes('submitted')) return 'submitted';

                if (list.includes('revise') || list.includes('revised') || list.includes('revision')) return 'revise';
                if (list.includes('draft')) return 'draft';

                return ''; // no status at all
            }

            /**
             * RULE sesuai request:
             * - hide submit kalau status = submitted/checked/approved
             * - kecuali status kosong / revise => tampil
             */
            function isLockedAfterSubmit() {
                const s = String(HEADER_STATUS || '').toLowerCase().trim();

                if (!s) return false; // belum ada status
                if (['revise', 'revised', 'revision'].includes(s)) return false;

                return ['submitted', 'checked', 'approved'].includes(s);
            }

            const $tbody = (cat) => $(`.js-tbl-ipp[data-cat="${cat}"] .js-tbody-ipp`);

            // ===================== State =====================
            let PERMS = {
                can_add: true,
                can_edit: true,
                can_delete: true,
                can_recalc: true,
                can_submit: true
            };
            let IPP_POINTS = [];
            let ACHS = [];

            let mdlDetail = new bootstrap.Modal(document.getElementById('modal-ipp-detail'));
            let mdlAdd = new bootstrap.Modal(document.getElementById('modal-add-activity'));

            // ===================== CAP utils =====================
            function capFor(cat) {
                const $it = $(`.accordion-item[data-cat="${cat}"]`);
                return nloc($it.data('cap') || 0);
            }

            function totalWeightByCat(cat) {
                let sum = 0;
                IPP_POINTS.filter(p => (p.category || '') === cat).forEach(p => {
                    const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(p.id));
                    sum += nloc(ex?.weight ?? p.weight ?? 0);
                });
                ACHS.filter(x => !x.ipp_point_id && (x.category || '') === cat)
                    .forEach(c => sum += nloc(c.weight ?? 0));
                return sum;
            }

            function totalWeightAllCats() {
                let sum = 0;
                $('.accordion-item[data-cat]').each(function() {
                    sum += totalWeightByCat(String($(this).data('cat')));
                });
                return sum;
            }

            function categoryTitle(key) {
                const $item = $(`.accordion-item[data-cat="${key}"]`);
                const title = $item.find('.accordion-button span:first').text().trim();
                return title || key;
            }

            function wouldExceedCapOnEditIPP(p, newW) {
                const cat = p.category || '';
                const cap = capFor(cat);
                const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(p.id));
                const oldW = nloc(ex?.weight ?? p.weight ?? 0);
                const others = totalWeightByCat(cat) - oldW;
                const newSum = others + nloc(newW);
                return {
                    exceed: newSum > cap + 1e-8,
                    newSum,
                    cap,
                    cat
                };
            }

            function wouldExceedCapOnEditCustom(a, newW) {
                const cat = a.category || '';
                const cap = capFor(cat);
                const oldW = nloc(a.weight ?? 0);
                const others = totalWeightByCat(cat) - oldW;
                const newSum = others + nloc(newW);
                return {
                    exceed: newSum > cap + 1e-8,
                    newSum,
                    cap,
                    cat
                };
            }

            function wouldExceedCapOnAdd(cat, addW) {
                const cap = capFor(cat);
                const newSum = totalWeightByCat(cat) + nloc(addW);
                return {
                    exceed: newSum > cap + 1e-8,
                    newSum,
                    cap
                };
            }

            // ===================== UI render =====================
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
                    },
                    revise: {
                        bg: '#fff7ed',
                        bd: '#fed7aa',
                        fg: '#9a3412',
                        txt: 'Revise'
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
                const btnDelete =
                    `<button class="btn btn-sm btn-del btn-mini js-row-delete ms-1" aria-label="Hapus"><i class="bi bi-trash3"></i> Hapus</button>`;
                return `<span class="text-nowrap">${btnDetail}${btnDelete}</span>`;
            }

            function rowIPPHtml(p) {
                const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(p.id)) || null;
                const score = nloc(ex?.self_score ?? 0);
                const weight = nloc(ex?.weight ?? p.weight ?? 0);
                const total = (weight / 100) * score;
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
                const score = nloc(a.self_score ?? 0);
                const weight = nloc(a.weight ?? 0);
                const total = (weight / 100) * score;
                const st = (a?.status || '').toString().toLowerCase();
                return `<tr data-source="custom" data-ach-key="${esc(key)}" data-cat="${esc(a.category||'')}" data-status="${esc(st)}">
                <td>${esc(a.title||'(tanpa judul)')}</td>
                <td><div class="fw-semibold">${esc(a.one_year_target||'')}</div></td>
                <td>${fmt(weight)}%</td>
                <td>${fmt(score)}</td>
                <td>${fmt(total)}</td>
                <td>${statusBadgeHtml(a)}</td>
                <td>${actionButtonsHtml()}</td>
            </tr>`;
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

                $('#total-achievement,#total-grand').text(fmt(total));
                $('#total-grand-score').text(fmt(sumR));

                const scale = v => Math.max(0, Math.min(100, (v / 10) * 100));
                $('#bar-ach,#bar-grand').css('width', scale(total) + '%');
                $('#bar-gscore').css('width', Math.max(0, Math.min(100, (sumR / 10) * 100)) + '%');
            }

            function renderAll() {
                $('[data-cat]').each(function() {
                    renderByCat($(this).data('cat'));
                });
                recalcTotals();
                updateCapBadges();
                updateSubmitButtons();
            }

            // ===================== Submit conditions =====================
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

            function allCategoriesAtCap() {
                const EPS = 1e-8;
                return $('.accordion-item[data-cat][data-cap]').toArray().every(el => {
                    const $el = $(el);
                    const cat = String($el.data('cat'));
                    const cap = nloc($el.data('cap'));
                    const sum = totalWeightByCat(cat);
                    return Math.abs(sum - cap) <= EPS;
                });
            }

            function updateSubmitButtons() {
                const $btns = $('#btn-save, #btn-save-bottom');
                if (!$btns.length) return;

                // 🔒 hide saat lock
                if (isLockedAfterSubmit()) {
                    $btns.addClass('hidden')
                        .prop('disabled', true)
                        .attr('title', MSG_SUBMITTED_LOCK);
                    return;
                }

                // ✅ tampil saat tidak lock
                $btns.removeClass('hidden')
                    .prop('disabled', false);

                const hints = [];
                if (!areAllPointsDraftStrict()) hints.push('Semua poin sebaiknya berstatus Draft.');
                if (!allCategoriesAtCap()) hints.push('Seluruh kategori sebaiknya pas sesuai CAP.');
                if (Math.abs(totalWeightAllCats() - 100) > 1e-8) hints.push(
                    'Total seluruh kategori sebaiknya tepat 100%.');

                if (hints.length) $btns.attr('title', hints.join(' '));
                else $btns.removeAttr('title');
            }

            // ===================== LOAD =====================
            function initLoad() {
                $('.js-tbody-ipp').html('<tr class="empty-row"><td colspan="7">Memuat...</td></tr>');

                $.getJSON(URL_DATA).done(function(res) {
                    if (!res || !res.ok) {
                        toast('Gagal memuat data.', 'danger');
                        return;
                    }

                    const d = res.data || {};

                    // points
                    IPP_POINTS = Array.isArray(d.ipp_points) ? d.ipp_points.map(p => ({
                        id: p.id,
                        activity: p.activity || p.title,
                        title: p.title,
                        target_one: p.target_one,
                        weight: nloc(p.weight || 0),
                        category: p.category
                    })) : [];

                    // achievements
                    ACHS = Array.isArray(d.achievements) ? d.achievements.map(x => ({
                        id: x.id || null,
                        ipp_point_id: x.ipp_point_id || null,
                        category: x.category || null,
                        title: x.title || null,
                        one_year_target: x.one_year_target || '',
                        one_year_achievement: x.one_year_achievement || x.description || '',
                        weight: nloc(x.weight ?? 0),
                        self_score: nloc(x.self_score || 0),
                        status: x.status || null,
                        __key: x.__key || (x.id ? String(x.id) : ('tmp-' + Math.random().toString(
                            16).slice(2)))
                    })) : [];

                    // ✅ status final (header atau derive dari achievements)
                    const headerRaw = d.header?.status ?? null;
                    HEADER_STATUS = deriveOverallStatus(headerRaw, ACHS);

                    // debug
                    console.log('HEADER_STATUS(final)=', HEADER_STATUS);
                    console.log('sample achievement statuses=', ACHS.slice(0, 5).map(a => a.status));

                    renderAll();
                }).fail(function() {
                    toast('Error server saat memuat.', 'danger');
                });
            }

            // ===================== Keyboard Enter untuk modal =====================
            function bindEnterToSave(modalSelector, saveBtnSelector) {
                $(document).on('keydown', modalSelector, function(e) {
                    const isEnter = (e.key === 'Enter' || e.keyCode === 13);
                    const $t = $(e.target);
                    const isTextarea = $t.is('textarea');
                    const isButton = $t.is('button, [type="button"], [type="submit"]');
                    if (isEnter && !e.shiftKey && !isTextarea && !isButton) {
                        e.preventDefault();
                        $(saveBtnSelector).trigger('click');
                    }
                });
            }
            bindEnterToSave('#modal-ipp-detail', '#ippd-btn-save');
            bindEnterToSave('#modal-add-activity', '#add-btn-save');

            // ===================== DETAIL (open) =====================
            $(document).on('click', '.js-row-detail', function() {
                if (isLockedAfterSubmit()) {
                    toast(MSG_SUBMITTED_LOCK, 'warning');
                    return;
                }

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

                mdlDetail.show();
            });

            // ===================== SUBMIT =====================
            function submitAll() {
                if (isLockedAfterSubmit()) {
                    toast(MSG_SUBMITTED_LOCK, 'warning');
                    return;
                }

                const EPS = 1e-8;
                const issues = [];

                $('.accordion-item[data-cat][data-cap]').each(function() {
                    const cat = String($(this).data('cat'));
                    const cap = nloc($(this).data('cap'));
                    const sum = totalWeightByCat(cat);
                    if (Math.abs(sum - cap) > EPS) issues.push(
                        `${categoryTitle(cat)} harus ${fmt(cap)}% (sekarang ${fmt(sum)}%)`);
                });

                const grand = totalWeightAllCats();
                if (Math.abs(grand - 100) > EPS) issues.unshift(
                    `Total seluruh kategori harus 100% (sekarang ${fmt(grand)}%)`);

                if (issues.length) {
                    issues.forEach((m, i) => toast(m, i ? 'dark' : 'warning'));
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
                        toast(res.message || 'Berhasil submit.', 'success');
                        initLoad();
                    } else {
                        const msgs = extractBackendErrors(res);
                        if (msgs.length) msgs.forEach((m, i) => toast(m, i ? 'dark' : 'warning'));
                        else toast('Gagal submit.', 'warning');
                    }
                }).fail(function(xhr) {
                    const msgs = extractBackendErrors(xhr.responseJSON || {});
                    if (msgs.length) msgs.forEach((m, i) => toast(m, i ? 'dark' : 'danger'));
                    else toast(xhr.statusText || 'Error server.', 'danger');
                });
            }

            $('#btn-save, #btn-save-bottom').off('click').on('click', submitAll);

            // ===================== init =====================
            initLoad();
        })();
    </script>
@endpush
