@extends('layouts.root.main')

@section('title', $title ?? 'IPP')
@section('breadcrumbs', $title ?? 'IPP')

@push('custom-css')
    <style>
        /* ==== IPP Theme (kontras ramah mata) ==== */
        :root {
            --ipp-border: #e5e7eb;
            --ipp-header-bg: #f8fafc;
            --ipp-row-alt: #fbfdff;
            --ipp-row-hover: #eef2ff;
            --ipp-text: #111827;

            --noteNew-bgTop: #eff6ff;
            --noteNew-bgBot: #3d67ff;
            --noteNew-border: #bfdbfe;
            --noteNew-dot: #3b82f6;
            --noteNew-ring: #93c5fd;
            --noteNew-text: #ffffff;
            --noteNew-shadow: rgba(59, 130, 246, .10);
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

        .period-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            padding: .35rem .7rem;
            font-weight: 700;
            letter-spacing: .2px;
            color: #0f172a;
        }

        .period-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .12)
        }

        .accordion.ipp .accordion-item {
            border: 1px solid var(--ipp-border);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            margin-bottom: .875rem;
            box-shadow: 0 1px 0 rgba(17, 24, 39, .02)
        }

        .accordion.ipp .accordion-button {
            background: #fafafa;
            color: var(--ipp-text);
            font-weight: 600
        }

        .accordion.ipp .accordion-button:not(.collapsed) {
            background: #f5f7fb;
            box-shadow: inset 0 -1px 0 var(--ipp-border)
        }

        .accordion.ipp .accordion-button:focus {
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .2)
        }

        .accordion.ipp .accordion-header {
            border-bottom: 1px solid var(--ipp-border)
        }

        .table.ipp-table {
            border-color: var(--ipp-border);
            font-size: .95rem
        }

        .table.ipp-table thead th {
            background: var(--ipp-header-bg) !important;
            color: var(--ipp-text);
            font-weight: 700;
            border-bottom: 1px solid var(--ipp-border)
        }

        .table.ipp-table tbody tr:nth-child(even) {
            background: var(--ipp-row-alt)
        }

        .table.ipp-table tbody tr:hover {
            background: var(--ipp-row-hover)
        }

        .table.ipp-table td,
        .table.ipp-table th {
            padding: .9rem 1rem;
            vertical-align: middle
        }

        .btn-group.btn-group-sm .btn {
            padding: .45rem .6rem
        }

        .container-xxl {
            max-width: 1360px
        }

        .req::after {
            content: "*";
            color: #dc3545;
            margin-left: .25rem
        }

        .table thead th {
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 1;
            background: #fff
        }

        .badge-cap {
            background: #f1f3f5;
            color: #6c757d
        }

        .empty-row td {
            padding: 1.25rem !important;
            color: #6c757d;
            font-style: italic
        }

        /* ===== COMMENT MODAL ===== */
        .comment-modal .modal-dialog {
            max-width: 860px;
        }

        .comment-modal .modal-content {
            border-radius: 14px;
            border: 1px solid #e6e8ee;
            box-shadow: 0 20px 50px rgba(2, 6, 23, .12)
        }

        .comment-modal .modal-header {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: .9rem 1rem;
            background: linear-gradient(180deg, #fbfcfe 0%, #f7f9fc 100%);
            border-bottom: 1px solid #e9eef5
        }

        .comment-modal .modal-title {
            font-weight: 800;
            letter-spacing: .2px
        }

        .cmt-timeline {
            position: relative;
            margin: .25rem 0 0 0;
            padding: .25rem 0 .25rem 1.6rem;
            max-height: min(62vh, 560px);
            overflow: auto;
            scrollbar-gutter: stable;
            -webkit-mask-image: linear-gradient(180deg, transparent 0, #000 12px, #000 calc(100% - 12px), transparent 100%);
            mask-image: linear-gradient(180deg, transparent 0, #000 12px, #000 calc(100% - 12px), transparent 100%)
        }

        .cmt-timeline::-webkit-scrollbar {
            height: 10px;
            width: 10px
        }

        .cmt-timeline::-webkit-scrollbar-thumb {
            background: #d2d8e3;
            border-radius: 9999px
        }

        .cmt-timeline::before {
            content: "";
            position: absolute;
            left: .6rem;
            top: .8rem;
            bottom: .8rem;
            width: 3px;
            background: linear-gradient(180deg, #eef2f7, #e5e9f0);
            border-radius: 9999px
        }

        .cmt-item {
            position: relative;
            padding: .9rem 1rem 1rem 1rem;
            margin-left: .35rem;
            border: 1px solid #eef2f7;
            border-radius: 12px;
            background: #fff;
            transition: .18s;
            animation: itemEnter .24s ease-out both
        }

        .cmt-item+.cmt-item {
            margin-top: .7rem
        }

        .cmt-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(2, 6, 23, .08);
            border-color: #e5eaf3
        }

        .cmt-dot {
            position: absolute;
            left: -1.1rem;
            top: 1.15rem;
            width: .72rem;
            height: .72rem;
            border-radius: 9999px;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e6ebf3;
            background: #94a3b8
        }

        .cmt-dot.revise {
            background: #ef4444
        }

        .cmt-dot.checked {
            background: #0ea5e9
        }

        .cmt-dot.approved {
            background: #22c55e
        }

        .cmt-dot.submitted {
            background: #f59e0b
        }

        .cmt-dot.draft {
            background: #94a3b8
        }

        .cmt-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem .7rem;
            line-height: 1.15
        }

        .cmt-name {
            font-weight: 800;
            color: #0f172a
        }

        .cmt-meta {
            color: #475569;
            font-size: .8rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: .16rem .55rem;
            border-radius: 9999px
        }

        .cmt-badge {
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e5e7eb;
            padding: .18rem .56rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: .78rem;
            letter-spacing: .2px
        }

        .cmt-badge.submitted {
            background: #fff7ed;
            color: #92400e;
            border-color: #fde68a
        }

        .cmt-badge.draft {
            background: #f8fafc;
            color: #334155;
            border-color: #e5e7eb
        }

        .cmt-badge.revise {
            background: #fef2f2;
            color: #7f1d1d;
            border-color: #fecaca
        }

        .cmt-badge.checked {
            background: #e0f2fe;
            color: #075985;
            border-color: #bae6fd
        }

        .cmt-badge.approved {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0
        }

        .cmt-body {
            margin-top: .55rem;
            color: #0f172a;
            font-size: .95rem;
            line-height: 1.6;
            word-break: break-word
        }

        .cmt-body .more {
            color: #2563eb;
            cursor: pointer;
            font-weight: 600;
            margin-left: .35rem;
            white-space: nowrap
        }

        @keyframes itemEnter {
            from {
                opacity: 0;
                transform: translateY(8px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .btn-comment-indicator {
            position: relative;
            border-radius: 9999px;
            padding: .35rem .7rem
        }

        .btn-comment-indicator .count {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #ef4444;
            color: #fff;
            border-radius: 9999px;
            font-size: .7rem;
            line-height: 1;
            min-width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 .35rem;
            border: 2px solid #fff
        }

        .btn-comment-indicator .count.is-hidden {
            display: none
        }

        .pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 9999px;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, .6);
            animation: pulse 1.5s infinite
        }

        @keyframes pulse {
            to {
                box-shadow: 0 0 0 12px rgba(239, 68, 68, 0)
            }
        }

        @media (max-width:576px) {
            .comment-modal .modal-dialog {
                max-width: calc(100% - 1rem);
                margin: .5rem auto
            }

            .cmt-meta {
                font-size: .8rem
            }

            .cmt-badge {
                font-size: .76rem
            }
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-3 px-6">

        {{-- Header --}}
        <div class="page-head mb-3">
            <h3 class="page-title">
                <i class="bi bi-clipboard2-check text-primary"></i>
                <span>Individual Performance Plan</span>
            </h3>

            {{-- Fiscal Period Pill (Apr–Mar) --}}
            <div class="period-pill" id="periodPill" title="Periode IPP mengikuti fiscal year: April–Maret">
                <span class="dot"></span>
                <span id="periodText">Periode: Apr — Mar</span>
            </div>
        </div>

        @php
            $categories = [
                ['key' => 'activity_management', 'title' => 'I. Activity Management', 'cap' => 70],
                ['key' => 'people_development', 'title' => 'II. People Development', 'cap' => 10],
                ['key' => 'crp', 'title' => 'III. CRP', 'cap' => 10],
                ['key' => 'special_assignment', 'title' => 'IV. Special Assignment & Improvement', 'cap' => 10],
            ];
        @endphp

        {{-- STATUS + tombol komentar --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">Status IPP:</span>
                <span id="ippStatusBadge" class="badge badge-secondary">—</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="btnComments" class="btn btn-primary btn-sm btn-comment-indicator d-none"
                    onclick="openCommentsModal(window.__currentIppId)">
                    <i class="bi bi-chat-left-text me-1"></i> Notes
                    <span id="commentCount" class="count">0</span>
                </button>
                <span id="commentPulse" class="pulse d-none" title="Ada komentar baru"></span>
            </div>
        </div>

        {{-- PROGRAM / ACTIVITY --}}
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="accordion mt-3 ipp" id="accordionPrograms">
                    @foreach ($categories as $cat)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $cat['key'] }}">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse-{{ $cat['key'] }}" aria-expanded="true"
                                    aria-controls="collapse-{{ $cat['key'] }}">
                                    <div class="d-flex flex-column w-100">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <span>{{ $cat['title'] }}</span>
                                            <span class="small text-muted">
                                                <span class="me-2">Cap
                                                    <span class="badge badge-cap js-cap-badge"
                                                        data-cat="{{ $cat['key'] }}">{{ $cat['cap'] }}%</span>
                                                </span>
                                                <span>Used
                                                    <span class="badge">
                                                        <span class="js-used" data-cat="{{ $cat['key'] }}">0</span>%
                                                    </span>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $cat['key'] }}" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <button type="button" class="btn btn-sm btn-primary js-open-modal"
                                            data-cat="{{ $cat['key'] }}"
                                            aria-label="Tambah point untuk {{ $cat['title'] }}">
                                            <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Point
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0 js-table ipp-table"
                                            data-cat="{{ $cat['key'] }}">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:25%">Program / Activity</th>
                                                    <th style="width:22%">Target One</th>
                                                    <th style="width:20%">Start → Due</th>
                                                    <th style="width:6%">W%</th>
                                                    <th style="width:8%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="js-tbody">
                                                <tr class="empty-row">
                                                    <td colspan="5">Klik "Tambah Point" untuk memulai.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Toolbar: Activity Plan link + Export --}}
                <div class="d-flex justify-content-end mt-3 gap-2">
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-success d-none" id="btnExportExcel"
                            data-href-template="{{ route('ipp.export.excel', '__ID__') }}">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
                        </a>

                        <a href="#" class="btn btn-danger d-none" id="btnExportPDF"
                            data-href-template="{{ route('ipp.export.pdf', '__ID__') }}">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </a>
                        <button id="btnSubmitAll" class="btn btn-warning">
                            <i class="bi bi-send-check"></i> Submit IPP + Activity Plan
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== Comment History Modal ===== --}}
    @include('website.ipp.modal.history-comment')

    {{-- Modal Tambah/Edit Point --}}
    @include('website.ipp.modal.create')
@endsection

@push('scripts')
    <script>
        (function($) {
            // === template URL ke Activity Plan utk point terpilih ===
            const AP_MANAGE_URL_TPL = "{{ route('activity-plan.byPoint', ['point' => '__POINT__']) }}?ipp_id=__IPP__";

            let CAT_CAP = {
                activity_management: 70,
                people_development: 10,
                crp: 10,
                special_assignment: 10
            };
            const pointModal = new bootstrap.Modal(document.getElementById('pointModal'), {
                backdrop: 'static',
                keyboard: false
            });
            let autoRowId = 0,
                LOCKED = false;

            /* ===== Utils ===== */
            const esc = (s) => String(s ?? '').replace(/[&<>"'`=\/]/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;',
            '`': '&#x60;',
                '=': '&#x3D;'
            } [c]));
            const fmt = (n) => (Math.round((parseFloat(n) || 0) + Number.EPSILON)).toFixed(0);

            function sumWeights(cat) {
                let sum = 0;
                $(`table.js-table[data-cat="${cat}"] tbody tr`).each(function() {
                    if ($(this).hasClass('empty-row')) return;
                    sum += parseFloat($(this).data('weight')) || 0;
                });
                return sum;
            }

            function pickUsedBadgeClass(used, cap) {
                const e = 1e-6;
                if (Math.abs(used) <= e) return 'badge-secondary';
                if (used > cap + e) return 'badge-danger';
                if (Math.abs(used - cap) <= e) return 'badge-primary';
                return 'badge-warning';
            }

            function updateCategoryCard(cat) {
                const used = sumWeights(cat),
                    cap = CAT_CAP[cat];
                $(`.js-used[data-cat="${cat}"]`).text(fmt(used));
                const cls = pickUsedBadgeClass(used, cap);
                $(`.js-used[data-cat="${cat}"]`).closest('.badge')
                    .removeClass('badge-primary badge-warning badge-danger badge-secondary')
                    .addClass(cls);
            }

            function recalcAll() {
                Object.keys(CAT_CAP).forEach(updateCategoryCard);
            }

            /* ===== Fiscal helpers (NEW) ===== */
            function getFiscalWindow() {
                // window.__onYear = fiscal start year (misal 2026 -> Apr 2026 - Mar 2027)
                const fy = Number(window.__onYear || 0);
                if (!fy) return null;

                // month index: 0=Jan ... 3=Apr
                const start = new Date(fy, 3, 1); // 1 Apr fy
                const end = new Date(fy + 1, 2, 31); // 31 Mar fy+1

                return {
                    fy,
                    start,
                    end
                };
            }

            function ymd(dateObj) {
                // YYYY-MM-DD
                const y = dateObj.getFullYear();
                const m = dateObj.getMonth() + 1;
                const d = dateObj.getDate();
                const mm = (m < 10 ? '0' : '') + m;
                const dd = (d < 10 ? '0' : '') + d;
                return `${y}-${mm}-${dd}`;
            }

            function applyFiscalDateLimits() {
                const win = getFiscalWindow();
                if (!win) return; // kalau belum tau fiscal year, skip

                const {
                    start,
                    end
                } = win;
                const minStr = ymd(start);
                const maxStr = ymd(end);

                const $start = $('#pmStart');
                const $due = $('#pmDue');

                // set min / max utk native datepicker
                $start.attr('min', minStr).attr('max', maxStr);
                $due.attr('min', minStr).attr('max', maxStr);

                // update hint bawah input date
                $('#pmStartHint').text(`Hanya boleh antara 1 Apr ${start.getFullYear()} – 31 Mar ${end.getFullYear()}`);
                $('#pmDueHint').text(`Hanya boleh antara 1 Apr ${start.getFullYear()} – 31 Mar ${end.getFullYear()}`);

                // clamp nilai existing supaya tidak keluar range
                function clampVal($inp) {
                    const v = $inp.val();
                    if (!v) return;
                    if (v < minStr) $inp.val(minStr);
                    if (v > maxStr) $inp.val(maxStr);
                }
                clampVal($start);
                clampVal($due);

                // auto prefilling utk mode create (biar enak)
                if ($('#pmMode').val() === 'create') {
                    if (!$start.val()) $start.val(minStr);
                    if (!$due.val()) $due.val(maxStr);
                }
            }

            /* ===== Row HTML (dengan tombol Manage Plan) ===== */
            function makeRowHtml(rowId, data) {
                const w = isNaN(parseFloat(data.weight)) ? 0 : parseFloat(data.weight);
                const oneShort = (data.target_one || '').length > 110 ? data.target_one.slice(0, 110) + '…' : (data
                    .target_one || '-');
                const startTxt = data.start_date ? data.start_date : '-';
                const dueTxt = data.due_date ? data.due_date : '-';
                const canManage = !!rowId && String(rowId).match(
                    /^\d+$/); // hanya aktif kalau sudah tersimpan di server
                return `
                    <tr class="align-middle point-row"
                        data-row-id="${esc(rowId)}"
                        data-activity="${esc(data.activity||'')}"
                        data-mid="${esc(data.target_mid||'')}"
                        data-one="${esc(data.target_one||'')}"
                        data-start="${esc(startTxt)}"
                        data-due="${esc(dueTxt)}"
                        data-status="${esc(data.status||'draft')}"
                        data-weight="${w}">
                        <td class="fw-semibold">${esc(data.activity||'-')}</td>
                        <td class="text-muted">${esc(oneShort)}</td>
                        <td>
                            <span class="badge badge-light">${esc(startTxt)}</span>
                            &rarr;
                            <span class="badge badge-light">${esc(dueTxt)}</span>
                        </td>
                        <td><span>${fmt(w)}</span></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-info js-manage" title="Manage Plan" ${canManage?'':'disabled'}>
                                    <i class="bi bi-kanban"></i>
                                </button>
                                <button type="button" class="btn btn-warning js-edit" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-danger js-remove" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
            }

            /* ===== Status badge ===== */
            function renderIppStatus(status) {
                const $b = $('#ippStatusBadge');
                const map = {
                    submitted: {
                        text: 'Submitted',
                        cls: 'badge-success'
                    },
                    draft: {
                        text: 'Draft',
                        cls: 'badge-warning'
                    },
                    revised: {
                        text: 'Revised',
                        cls: 'badge-danger'
                    },
                    revise: {
                        text: 'Revise',
                        cls: 'badge-danger'
                    },
                    checked: {
                        text: 'Checked',
                        cls: 'badge-info'
                    },
                    approved: {
                        text: 'Approved',
                        cls: 'badge-success'
                    }
                };
                const info = map[(status || '').toLowerCase()] || {
                    text: '—',
                    cls: 'badge-secondary'
                };
                $b.removeClass('badge-secondary badge-warning badge-success badge-danger badge-info')
                    .addClass(info.cls).text(info.text);
            }

            /* ===== Comments (tetap) ===== */
            function statusClass(s) {
                return (s || '').toLowerCase();
            }

            function toHtmlWithBreak(s) {
                return esc(s).replace(/\n/g, '<br>');
            }

            function renderComments(list) {
                const $ul = $('#commentTimeline').empty();
                const $empty = $('#commentEmpty');
                if (!Array.isArray(list) || list.length === 0) {
                    $empty.removeClass('d-none');
                    return;
                }
                $empty.addClass('d-none');
                const ippId = window.__currentIppId;
                const lastSeenId = Number(localStorage.getItem(lastIdKey(ippId)) || 0);
                const items = [...list].sort((a, b) => {
                    const ida = Number(a.id) || 0,
                        idb = Number(b.id) || 0;
                    if (idb !== ida) return idb - ida;
                    return String(b.created_at || '').localeCompare(String(a.created_at || ''));
                });
                items.forEach(c => {
                    const dotCls = statusClass(c.status_to) || 'draft';
                    const who = esc(c.employee?.name || c.user?.name || 'User');
                    const at = esc(c.created_at || '');
                    const raw = String(c.comment || '');
                    const short = raw.length > 220 ? raw.slice(0, 220) : raw;
                    const needMore = raw.length > 220;
                    const itemId = 'cmt-' + (c.id || Math.random().toString(36).slice(2));
                    const isNew = (Number(c.id) || 0) > lastSeenId;
                    const $li = $(`
                        <li class="cmt-item ${isNew?'is-new':''}">
                            <span class="cmt-dot ${dotCls}"></span>
                            <div class="cmt-head">
                                <span class="cmt-name ${isNew?'text-white':''}">${who}</span>
                                <span class="cmt-meta">• ${at}</span>
                            </div>
                            <div class="cmt-body ${isNew?'text-white':''}" id="${itemId}">
                                <span class="text">${toHtmlWithBreak(short)}</span>
                                ${needMore?`<span class="more" data-full="${esc(raw)}">…more</span>`:``}
                            </div>
                        </li>`);
                    $ul.append($li);
                });
            }

            function seenKey(ippId) {
                return `ipp:${ippId}:comments_seen`;
            }

            function lastIdKey(ippId) {
                return `ipp:${ippId}:last_seen_id`;
            }

            function setCommentIndicator(ippId, totalCount) {
                const $btn = $('#btnComments'),
                    $count = $('#commentCount'),
                    $pulse = $('#commentPulse');
                if (!ippId) {
                    $btn.addClass('d-none');
                    $pulse.addClass('d-none');
                    return;
                }
                $btn.removeClass('d-none');
                if (Number(totalCount) > 0) {
                    $count.text(totalCount).removeClass('is-hidden');
                } else {
                    $count.text('0').addClass('is-hidden');
                }
                const lastSeen = Number(localStorage.getItem(seenKey(ippId)) || 0);
                const hasNew = Number(totalCount) > lastSeen;
                $pulse.toggleClass('d-none', !hasNew);
            }

            function markCommentsSeen(ippId, totalCount, latestId) {
                try {
                    localStorage.setItem(seenKey(ippId), String(Number(totalCount) || 0));
                    if (latestId !== undefined) localStorage.setItem(lastIdKey(ippId), String(Number(latestId) || 0));
                } catch (e) {}
                setCommentIndicator(ippId, totalCount);
            }

            async function loadComments(ippId) {
                const url = "{{ route('ipp.comments', ['ipp' => '__ID__']) }}".replace('__ID__', ippId);
                try {
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    const list = json.data || [];
                    renderComments(list);
                    const ids = list.map(x => Number(x.id) || 0);
                    const latestId = ids.length ? Math.max(...ids) : 0;
                    return {
                        count: list.length,
                        latestId
                    };
                } catch (_) {
                    renderComments([]);
                    return {
                        count: 0,
                        latestId: 0
                    };
                }
            }

            window.openCommentsModal = async function(ippId) {
                if (!ippId) return;
                const {
                    count,
                    latestId
                } = await loadComments(ippId);
                const el = document.getElementById('commentModal');
                const modal = bootstrap.Modal.getOrCreateInstance(el, {
                    backdrop: true,
                    keyboard: true
                });
                modal.show();
                markCommentsSeen(ippId, count, latestId);
            };

            document.getElementById('btnRefreshComments')?.addEventListener('click', async () => {
                const ippId = window.__currentIppId;
                if (!ippId) return;
                const {
                    count,
                    latestId
                } = await loadComments(ippId);
                markCommentsSeen(ippId, count, latestId);
            });

            /* ===== OPEN/EDIT modal point ===== */
            $(document).on('click', '.js-open-modal', function() {
                const cat = $(this).data('cat');
                $('#pmCat').val(cat);
                $('#pmMode').val('create');
                $('#pmRowId').val('');
                $('#pointModalLabel').text('Tambah Point — ' + $(this).closest('.accordion-item').find(
                    '.accordion-button span:first').text());
                $('#pointForm')[0].reset();

                // fiscal range (NEW)
                applyFiscalDateLimits();

                setTimeout(() => $('#pmActivity').trigger('focus'), 150);
                pointModal.show();
            });

            $(document).on('click', '.js-edit', function() {
                const $tr = $(this).closest('tr');
                const cat = $(this).closest('table').data('cat');
                $('#pmCat').val(cat);
                $('#pmMode').val('edit');
                $('#pmRowId').val($tr.data('row-id'));
                $('#pointModalLabel').text('Edit Point');
                $('#pmActivity').val($tr.data('activity'));
                const $pmTargetMid = $('#pmTargetMid');
                if ($pmTargetMid.length) $pmTargetMid.val($tr.data('mid'));
                $('#pmTargetOne').val($tr.data('one'));
                $('#pmStart').val($tr.data('start') || '');
                $('#pmDue').val($tr.data('due'));
                $('#pmWeight').val($tr.data('weight'));

                // fiscal range + clamp (NEW)
                applyFiscalDateLimits();

                setTimeout(() => $('#pmActivity').trigger('focus'), 150);
                pointModal.show();
            });

            /* ===== NEW: Manage Activity Plan per point ===== */
            $(document).on('click', '.js-manage', function() {
                const $tr = $(this).closest('tr');
                const pointId = $tr.data('row-id');
                const ippId = window.__currentIppId;
                // if (!ippId) {
                //     toast('IPP belum terinisialisasi.', 'danger');
                //     return;
                // }
                if (!pointId || !String(pointId).match(/^\d+$/)) {
                    toast('Simpan dulu point ini sebelum manage Activity Plan.', 'warning');
                    return;
                }
                const url = AP_MANAGE_URL_TPL
                    .replace('__IPP__', encodeURIComponent(ippId))
                    .replace('__POINT__', encodeURIComponent(pointId));
                window.location.href = url;
            });

            /* ===== SUBMIT point (create/edit) ===== */
            $('#pointForm').on('submit', function(e) {
                e.preventDefault();
                if (LOCKED) {
                    toast('IPP sudah submitted. Tidak bisa mengubah data.', 'danger');
                    return;
                }
                const $btn = $('#pmSaveBtn').prop('disabled', true);
                const mode = $('#pmMode').val();
                const cat = $('#pmCat').val();
                const rowId = $('#pmRowId').val();
                const $pmTargetMid = $('#pmTargetMid');

                const data = {
                    activity: ($('#pmActivity').val() || '').toString().trim(),
                    target_mid: $pmTargetMid.length ? ($pmTargetMid.val() || '').toString().trim() : '',
                    target_one: ($('#pmTargetOne').val() || '').toString().trim(),
                    start_date: $('#pmStart').val() || null,
                    due_date: $('#pmDue').val(),
                    weight: parseFloat($('#pmWeight').val()),
                };

                // FE validation (termasuk fiscal check)
                if (!data.activity) {
                    toast('Isi "Program / Activity".', 'danger');
                    return unlock();
                }
                if (!data.start_date) {
                    toast('Pilih "Start Date".', 'danger');
                    return unlock();
                }
                if (!data.due_date) {
                    toast('Pilih "Due Date".', 'danger');
                    return unlock();
                }
                if (new Date(data.start_date) > new Date(data.due_date)) {
                    toast('Start Date tidak boleh setelah Due Date.', 'danger');
                    return unlock();
                }
                if (isNaN(data.weight)) {
                    toast('Isi "Weight (%)" dengan angka.', 'danger');
                    return unlock();
                }

                const fy = Number(window.__onYear || 0);
                if (fy) {
                    const fStart = new Date(fy, 3, 1),
                        fEnd = new Date(fy + 1, 2, 31);
                    const dStart = new Date(data.start_date),
                        dDue = new Date(data.due_date);
                    const strip = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate());
                    if (strip(dStart) < strip(fStart) ||
                        strip(dStart) > strip(fEnd) ||
                        strip(dDue) < strip(fStart) ||
                        strip(dDue) > strip(fEnd)
                    ) {
                        toast(`Tanggal harus dalam periode fiscal Apr ${fy} – Mar ${fy+1}.`, 'danger');
                        return unlock();
                    }
                    const fyOf = (d) => (d.getMonth() + 1) >= 4 ? d.getFullYear() : (d.getFullYear() - 1);
                    if (fyOf(dStart) !== fyOf(dDue)) {
                        toast(`Start & Due harus pada fiscal year yang sama (Apr ${fy} – Mar ${fy+1}).`,
                            'danger');
                        return unlock();
                    }
                }

                const payload = {
                    single_point: 1,
                    mode,
                    status: 'draft',
                    cat,
                    row_id: rowId || null,
                    point: data
                };

                $.ajax({
                    url: "{{ route('ipp.store') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        payload: JSON.stringify(payload)
                    },
                    dataType: "json"
                }).done((res) => {
                    const rowData = {
                        ...data,
                        status: 'draft'
                    };
                    const newRowId = res?.row_id || res?.id || ('row-' + ++autoRowId);
                    const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                    if (mode === 'create') {
                        $tbody.find('.empty-row').remove();
                        $tbody.append(makeRowHtml(newRowId, rowData));
                    } else {
                        const $old = $(
                            `table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`
                        );
                        if ($old.length) $old.replaceWith(makeRowHtml(newRowId, rowData));
                        else {
                            $tbody.find('.empty-row').remove();
                            $tbody.append(makeRowHtml(newRowId, rowData));
                        }
                    }
                    recalcAll();
                    pointModal.hide();
                    toast(res?.message || 'Draft tersimpan.');
                    updateExportVisibility();
                    if (res?.locked) applyLockDom(true);
                }).fail(err => {
                    toast(err?.responseJSON?.message || 'Gagal menyimpan. Data tidak ditambahkan.',
                        'danger');
                }).always(unlock);

                function unlock() {
                    $btn.prop('disabled', false);
                }
            });

            /* ===== Hapus point ===== */
            function ajaxDeletePoint(id) {
                const url = "{{ route('ipp.point.destroy', ':id') }}".replace(':id', id);
                return $.ajax({
                    url,
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "DELETE"
                    },
                    dataType: "json"
                });
            }

            $(document).on('click', '.js-remove', function() {
                if (LOCKED) {
                    toast('IPP sudah submitted. Tidak bisa menghapus point.', 'danger');
                    return;
                }
                const $tr = $(this).closest('tr');
                const cat = $(this).closest('table').data('cat');
                const id = $tr.data('row-id');
                if (!id) {
                    // belum pernah ke server -> hapus lokal aja
                    $tr.remove();
                    const $tb = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                    if ($tb.find('tr').not('.empty-row').length === 0) {
                        $tb.html(
                            '<tr class="empty-row"><td colspan="5">Klik "Tambah Point" untuk memulai.</td></tr>'
                        );
                    }
                    recalcAll();
                    toast('Point dihapus (lokal).', 'warning');
                    return;
                }
                if (!confirm('Hapus point ini?')) return;
                ajaxDeletePoint(id).done((res) => {
                    $tr.remove();
                    const $tb = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                    if ($tb.find('tr').not('.empty-row').length === 0) {
                        $tb.html(
                            '<tr class="empty-row"><td colspan="5">Klik "Tambah Point" untuk memulai.</td></tr>'
                        );
                    }
                    recalcAll();
                    updateExportVisibility();
                    toast(res?.message || 'Point dihapus.');
                }).fail(err => toast(err?.responseJSON?.message || 'Gagal menghapus point.', 'danger'));
            });

            /* ===== Export visibility ===== */
            function updateExportVisibility() {
                const hasRows = $('table.js-table tbody tr').not('.empty-row').length > 0;
                const hasId = !!window.__currentIppId;
                const show = hasRows && hasId;
                $('#btnExportExcel').toggleClass('d-none', !show);
                $('#btnExportPDF').toggleClass('d-none', !show);
            }

            /* ===== Link helper ===== */
            function syncExportLinks(ippId) {
                const $excel = $('#btnExportExcel'),
                    $pdf = $('#btnExportPDF');
                const tExcel = $excel.data('href-template'),
                    tPdf = $pdf.data('href-template');
                if (ippId) {
                    $excel.attr('href', String(tExcel || '').replace('__ID__', ippId));
                    $pdf.attr('href', String(tPdf || '').replace('__ID__', ippId));
                } else {
                    $excel.attr('href', '#');
                    $pdf.attr('href', '#');
                }
            }

            function syncActivityPlanLink(ippId) {
                const $ap = $('#btnGoActivityPlan');
                const tpl = $ap.data('href-template');
                if (ippId) {
                    $ap.attr('href', String(tpl).replace('__IPP__', ippId)).removeClass('disabled');
                } else {
                    $ap.attr('href', '#').addClass('disabled');
                }
            }

            /* ===== Lock tampilan ===== */
            function applyLockDom(locked) {
                LOCKED = !!locked;
                $('.js-open-modal').toggleClass('d-none', LOCKED);
                $('table.js-table').each(function() {
                    const $t = $(this);
                    $t.find('thead th:last-child').toggleClass('d-none', LOCKED);
                    $t.find('tbody tr').each(function() {
                        $(this).find('td:last-child').toggleClass('d-none', LOCKED);
                    });
                });
                $('#btnSubmitAll').toggleClass('d-none', LOCKED).prop('disabled', LOCKED);
            }

            /* ===== Fiscal period pill ===== */
            function setPeriodPill(onYearStr) {
                const y = parseInt(onYearStr || 0, 10);
                if (!y) return;
                $('#periodText').text(`Periode: Apr ${y} – Mar ${y+1}`);
            }

            /* ===== INIT ===== */
            function loadInitial() {
                $.ajax({
                        url: "{{ route('ipp.init') }}",
                        method: "GET",
                        dataType: "json"
                    })
                    .done((res) => {
                        if (res?.cap) CAT_CAP = res.cap;

                        const onYear = res?.identitas?.on_year || '';
                        setPeriodPill(onYear);
                        window.__onYear = parseInt(onYear, 10) || 0;

                        applyFiscalDateLimits();

                        if (res?.points) {
                            Object.keys(res.points).forEach((cat) => {
                                const list = res.points[cat] || [];
                                const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                                if (list.length) $tbody.find('.empty-row').remove();
                                list.forEach((pt) => {
                                    const html = makeRowHtml(pt.id, {
                                        activity: pt.activity,
                                        target_mid: pt.target_mid,
                                        target_one: pt.target_one,
                                        start_date: pt.start_date || null,
                                        due_date: pt.due_date,
                                        weight: pt.weight,
                                        status: pt.status || 'draft'
                                    });
                                    $tbody.append(html);
                                });
                            });
                        }

                        const ippStatus = res?.ipp?.status || 'draft';
                        renderIppStatus(ippStatus);

                        const locked = !!(res?.locked || res?.ipp?.locked || (res?.ipp?.status === 'submitted'));
                        applyLockDom(locked);

                        const ippId = res?.ipp?.id || null;
                        window.__currentIppId = ippId;
                        syncExportLinks(ippId);
                        syncActivityPlanLink(ippId);

                        const totalComments = Number(res?.comments_count || 0);
                        setCommentIndicator(ippId, totalComments);

                        if (!res?.ipp && res?.has_approved) {
                            const year = res?.identitas?.on_year || '';
                            const bannerKey = (y) => `ipp:${y}:approved_banner_dismissed`;
                            if (localStorage.getItem(bannerKey(year)) !== '1') {
                                if (!document.getElementById('ippApprovedBanner')) {
                                    const banner = $(`
                                        <div id="ippApprovedBanner"
                                            class="alert alert-info alert-dismissible fade show d-flex align-items-center"
                                            role="alert" style="border-radius:12px;">
                                            <i class="bi bi-info-circle me-2 fs-5"></i>
                                            <div class="me-3">
                                                <div class="fw-bold mb-0">
                                                    Anda sudah memiliki IPP ${year} yang <span class="text-success">APPROVED</span>.
                                                </div>
                                                <small class="text-muted">
                                                    Halaman ini untuk memulai IPP baru (siklus berikutnya). Silakan tambah point.
                                                </small>
                                            </div>
                                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>`);
                                    $('.accordion.mt-3.ipp').before(banner);
                                    banner.on('closed.bs.alert', () => {
                                        try {
                                            localStorage.setItem(bannerKey(year), '1');
                                        } catch (_) {}
                                    });
                                }
                            }

                            $('table.js-table tbody.js-tbody').each(function() {
                                if ($(this).find('tr').not('.empty-row').length === 0) {
                                    $(this).html(
                                        '<tr class="empty-row"><td colspan="5">Klik "Tambah Point" untuk memulai.</td></tr>'
                                    );
                                }
                            });

                            $('#btnExportExcel, #btnExportPDF').addClass('d-none');
                        }

                        recalcAll();
                        updateExportVisibility();
                    })
                    .fail(() => toast('Gagal memuat data awal IPP.', 'danger'));
            }

            $(document).ready(function() {
                $('#accordionPrograms .accordion-collapse').each(function() {
                    this.removeAttribute('data-bs-parent');
                    this.classList.add('show');
                    const btn = $(this).prev('.accordion-header').find('.accordion-button');
                    btn.removeClass('collapsed').attr('aria-expanded', 'true');
                });

                $('table.js-table tbody.js-tbody').each(function() {
                    const $tb = $(this);
                    if ($tb.find('tr').not('.empty-row').length === 0) {
                        $tb.html(
                            '<tr class="empty-row"><td colspan="5">Klik "Tambah Point" untuk memulai.</td></tr>'
                        );
                    }
                });

                loadInitial();
            });

            $(document).on('click', '#btnSubmitAll', async function() {
                // ===== Submit gabungan: IPP + Activity Plan =====
                if (LOCKED) {
                    toast('IPP sudah submitted. Tidak bisa submit ulang.', 'danger');
                    return;
                }
                if (!window.__currentIppId) {
                    toast('IPP belum terinisialisasi.', 'danger');
                    return;
                }
                if (!confirm('Submit IPP + Activity Plan sekarang?')) return;

                const $btn = $(this).prop('disabled', true);
                try {
                    const url = new URL(@json(route('activity-plan.submitAll')), window.location.origin);
                    url.searchParams.set('ipp_id', String(window.__currentIppId));
                    const res = await fetch(url.toString(), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json?.message || 'Submit gagal.');
                    toast(json?.message || 'Berhasil submit.');

                    location.reload();
                } catch (err) {
                    toast(esc(err?.message || 'Submit gagal.'), 'danger');
                } finally {
                    $btn.prop('disabled', false);
                }
            });

            /* ===== Toast kecil ===== */
            function toast(msg, type = 'success') {
                const id = 'toast-' + Date.now();
                const $t = $(`
                    <div class="toast align-items-center badge-${type} border-0" id="${id}"
                        role="status" aria-live="polite" aria-atomic="true"
                        style="position:fixed;top:1rem;right:1rem;z-index:1080;">
                        <div class="d-flex">
                            <div class="toast-body">${esc(msg)}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast" aria-label="Tutup"></button>
                        </div>
                    </div>`);
                $('body').append($t);
                const t = new bootstrap.Toast($t[0], {
                    delay: 2500
                });
                t.show();
                $t.on('hidden.bs.toast', () => $t.remove());
            }
        })(jQuery);
    </script>
@endpush
