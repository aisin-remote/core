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

        /* ===== COMMENT HISTORY — modal & timeline (polish) ===== */
        .comment-modal .modal-dialog {
            max-width: 820px;
        }

        .comment-modal .modal-content {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 12px 30px rgba(2, 6, 23, .12);
        }

        .comment-modal .modal-header {
            padding: .85rem 1rem;
            border-bottom: 1px solid #eef2f7;
        }

        .comment-modal .modal-title {
            font-weight: 700;
            letter-spacing: .2px;
        }

        .cmt-timeline {
            position: relative;
            margin: .25rem 0 0 0;
            padding: .25rem 0 .25rem 1.25rem;
            max-height: min(62vh, 560px);
            overflow: auto;
            scrollbar-gutter: stable;
        }

        .cmt-timeline::-webkit-scrollbar {
            height: 10px;
            width: 10px
        }

        .cmt-timeline::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 9999px
        }

        .cmt-timeline::-webkit-scrollbar-thumb:hover {
            background: #9ca3af
        }

        .cmt-timeline::before {
            content: "";
            position: absolute;
            left: .45rem;
            top: .6rem;
            bottom: .6rem;
            width: 2px;
            background: linear-gradient(180deg, #eef2f7, #e5e7eb);
        }

        .cmt-item {
            position: relative;
            padding: .9rem 0 1rem .25rem;
        }

        .cmt-item+.cmt-item {
            border-top: 1px dashed #eef2f7;
        }

        .cmt-dot {
            position: absolute;
            left: -.13rem;
            top: 1.2rem;
            width: .62rem;
            height: .62rem;
            border-radius: 9999px;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e5e7eb, 0 0 0 6px rgba(2, 132, 199, 0);
            transition: box-shadow .25s ease;
            background: #94a3b8;
        }

        .cmt-item:hover .cmt-dot {
            box-shadow: 0 0 0 2px #e5e7eb, 0 0 0 6px rgba(2, 132, 199, .08);
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
            gap: .5rem .65rem;
            line-height: 1.15;
            margin-left: .75rem;
        }

        .cmt-name {
            font-weight: 700;
            color: #0f172a;
        }

        .cmt-meta {
            color: #64748b;
            font-size: .84rem;
        }

        .cmt-status {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-top: .35rem;
            flex-wrap: wrap;
        }

        .cmt-badge {
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e5e7eb;
            padding: .18rem .56rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: .78rem;
            letter-spacing: .2px;
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
            line-height: 1.55;
            word-break: break-word;
        }

        .cmt-body .more {
            color: #2563eb;
            cursor: pointer;
            font-weight: 600;
            margin-left: .35rem;
            white-space: nowrap;
        }

        .comment-modal .modal-header .btn {
            border-radius: 8px;
        }

        .comment-modal .modal-header .btn-light {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            color: #0f172a;
        }

        .comment-modal .modal-header .btn-light:hover {
            background: #f3f4f6;
        }

        /* header badge “Comments” */
        .btn-comment-indicator {
            position: relative;
            border-radius: 9999px;
            padding: .35rem .7rem;
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
            border: 2px solid #fff;
        }

        .btn-comment-indicator .count.is-hidden {
            display: none;
        }

        /* << hide when zero */

        .pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 9999px;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, .6);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            to {
                box-shadow: 0 0 0 12px rgba(239, 68, 68, 0)
            }
        }

        @media (max-width:576px) {
            .comment-modal .modal-dialog {
                max-width: calc(100% - 1rem);
                margin: .5rem auto;
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
                    <i class="bi bi-chat-left-text me-1"></i> Comments
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
                                                    <th style="width:34%">Program / Activity</th>
                                                    <th style="width:22%">Target One</th>
                                                    <th style="width:10%">Due</th>
                                                    <th style="width:6%">W%</th>
                                                    <th style="width:6%">Action</th>
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

                {{-- Submit + Export --}}
                <div class="d-flex justify-content-end mt-3 gap-2">
                    <a href="{{ route('ipp.export.excel') }}" class="btn btn-success d-none" id="btnExportExcel">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
                    </a>
                    <a href="{{ route('ipp.export.pdf') }}" class="btn btn-danger d-none" id="btnExportPDF">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                    <button type="button" class="btn btn-warning" id="btnSubmitAll">
                        <i class="bi bi-send-check"></i> Submit IPP
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Comment History Modal ===== --}}
    <div class="modal fade comment-modal" id="commentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-left-text fs-5 text-primary"></i>
                        <h5 class="modal-title mb-0">Comment History</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="btnRefreshComments" class="btn btn-sm btn-light">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    <ul id="commentTimeline" class="cmt-timeline list-unstyled mb-0"></ul>
                    <div id="commentEmpty" class="text-muted fst-italic">Belum ada komentar.</div>
                </div>
            </div>
        </div>
    </div>

    @include('website.ipp.modal.create')
@endsection

@push('scripts')
    <script>
        (function($) {
            const REQUIRE_TOTAL_100 = true;
            let CAT_CAP = {
                activity_management: 70,
                people_development: 10,
                crp: 10,
                special_assignment: 10
            };
            const pointModal = new bootstrap.Modal(document.getElementById('pointModal'));
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
            const fmt = (n) => (Math.round(n + Number.EPSILON)).toFixed(0);

            function sumWeights(cat) {
                let sum = 0;
                $(`table.js-table[data-cat="${cat}"] tbody tr`).each(function() {
                    if ($(this).hasClass('empty-row')) return;
                    sum += parseFloat($(this).data('weight')) || 0;
                });
                return sum;
            }

            function buildSummaryFromDom() {
                const s = {};
                Object.keys(CAT_CAP).forEach(c => s[c] = sumWeights(c));
                s.total = Object.values(s).reduce((a, b) => a + b, 0);
                return s;
            }

            function pickUsedBadgeClass(used, cap) {
                const e = 1e-6;
                if (Math.abs(used) <= e) return 'badge-secondary';
                if (used > cap + e) return 'badge-danger';
                if (Math.abs(used - cap) <= e) return 'badge-primary';
                return 'badge-warning';
            }

            /* ===== Rows ===== */
            function makeRowHtml(rowId, data) {
                const w = isNaN(parseFloat(data.weight)) ? 0 : parseFloat(data.weight);
                const oneShort = (data.target_one || '').length > 110 ? data.target_one.slice(0, 110) + '…' : (data
                    .target_one || '-');
                const dueTxt = data.due_date ? data.due_date : '-';
                return `
      <tr class="align-middle point-row"
          data-row-id="${esc(rowId)}"
          data-activity="${esc(data.activity||'')}"
          data-mid="${esc(data.target_mid||'')}"
          data-one="${esc(data.target_one||'')}"
          data-due="${esc(dueTxt)}"
          data-status="${esc(data.status||'draft')}"
          data-weight="${w}">
        <td class="fw-semibold">${esc(data.activity||'-')}</td>
        <td class="text-muted">${esc(oneShort)}</td>
        <td><span class="badge badge-light">${esc(dueTxt)}</span></td>
        <td><span>${fmt(w)}</span></td>
        <td class="text-end">
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-warning js-edit" title="Edit"><i class="bi bi-pencil-square"></i></button>
            <button type="button" class="btn btn-danger js-remove" title="Hapus"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      </tr>`;
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

            function updateExportVisibility() {
                const hasRows = $('table.js-table tbody tr').not('.empty-row').length > 0;
                $('#btnExportExcel').toggleClass('d-none', !hasRows);
                $('#btnExportPDF').toggleClass('d-none', !hasRows);
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

            /* ===== Comment UI ===== */
            function statusClass(s) {
                return (s || '').toLowerCase();
            }

            function statusChip(s) {
                const cls = statusClass(s);
                return `<span class="cmt-badge ${cls}">${(s||'-').toUpperCase()}</span>`;
            }

            function toHtmlWithBreak(s) {
                return esc(s).replace(/\n/g, '<br>');
            }

            function renderComments(list) {
                const $ul = $('#commentTimeline').empty();
                const $empty = $('#commentEmpty');
                if (!list || list.length === 0) {
                    $empty.removeClass('d-none');
                    return;
                }
                $empty.addClass('d-none');
                list.forEach(c => {
                    const dotCls = statusClass(c.status_to) || 'draft';
                    const who = esc(c.employee?.name || c.user?.name || 'User');
                    const at = esc(c.created_at || '');
                    const raw = String(c.comment || '');
                    const short = raw.length > 220 ? raw.slice(0, 220) : raw;
                    const needMore = raw.length > 220;
                    const itemId = 'cmt-' + (c.id || Math.random().toString(36).slice(2));
                    const $li = $(`
        <li class="cmt-item">
          <span class="cmt-dot ${dotCls}"></span>
          <div class="cmt-head">
            <span class="cmt-name">${who}</span>
            <span class="cmt-meta">• ${at}</span>
          </div>
          <div class="cmt-status">
            ${statusChip(c.status_from||'-')} <i class="bi bi-arrow-right-short"></i> ${statusChip(c.status_to||'-')}
          </div>
          <div class="cmt-body" id="${itemId}">
            <span class="text">${toHtmlWithBreak(short)}</span>
            ${needMore ? `<span class="more" data-full="${esc(raw)}">…more</span>` : ``}
          </div>
        </li>`);
                    $ul.append($li);
                });
            }

            // expand/less comment
            document.addEventListener('click', (e) => {
                const more = e.target.closest('.cmt-body .more');
                if (!more) return;
                const wrap = more.parentElement;
                const textEl = wrap.querySelector('.text');
                if (more.dataset.state === 'open') {
                    const full = more.getAttribute('data-full') || '';
                    const short = full.slice(0, 220);
                    textEl.innerHTML = toHtmlWithBreak(short);
                    more.textContent = '…more';
                    more.dataset.state = '';
                } else {
                    const full = more.getAttribute('data-full') || '';
                    textEl.innerHTML = toHtmlWithBreak(full);
                    more.textContent = 'less';
                    more.dataset.state = 'open';
                }
            });

            // ===== Indicator helpers (localStorage) =====
            function seenKey(ippId) {
                return `ipp:${ippId}:comments_seen`;
            }

            function setCommentIndicator(ippId, totalCount) {
                const $btn = $('#btnComments');
                const $count = $('#commentCount');
                const $pulse = $('#commentPulse');

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

            function markCommentsSeen(ippId, totalCount) {
                try {
                    localStorage.setItem(seenKey(ippId), String(Number(totalCount) || 0));
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
                    return list.length; // return count
                } catch (_) {
                    renderComments([]);
                    return 0;
                }
            }

            // dipanggil tombol
            window.openCommentsModal = async function(ippId) {
                if (!ippId) return;
                const total = await loadComments(ippId);
                const el = document.getElementById('commentModal');
                const modal = bootstrap.Modal.getOrCreateInstance(el, {
                    backdrop: true,
                    keyboard: true
                });
                modal.show();
                // dianggap sudah dilihat
                markCommentsSeen(ippId, total);
            };

            document.getElementById('btnRefreshComments')?.addEventListener('click', async () => {
                const ippId = window.__currentIppId;
                if (!ippId) return;
                const total = await loadComments(ippId);
                markCommentsSeen(ippId, total);
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
                $('#pmDue').val($tr.data('due'));
                $('#pmWeight').val($tr.data('weight'));
                setTimeout(() => $('#pmActivity').trigger('focus'), 150);
                pointModal.show();
            });

            /* ===== SUBMIT point ===== */
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
                    due_date: $('#pmDue').val(),
                    weight: parseFloat($('#pmWeight').val()),
                };
                if (!data.activity) {
                    toast('Isi "Program / Activity".', 'danger');
                    return unlock();
                }
                if (!data.due_date) {
                    toast('Pilih "Due Date".', 'danger');
                    return unlock();
                }
                if (isNaN(data.weight)) {
                    toast('Isi "Weight (%)" dengan angka.', 'danger');
                    return unlock();
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
                                `table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`);
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
                    })
                    .always(unlock);

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

            /* ===== Submit all ===== */
            $('#btnSubmitAll').on('click', function() {
                if (LOCKED) return;
                const summary = buildSummaryFromDom();
                for (const cat of Object.keys(CAT_CAP)) {
                    if ((summary[cat] || 0) > CAT_CAP[cat]) {
                        toast(`Bobot kategori "${cat.replace('_',' ')}" melebihi cap ${CAT_CAP[cat]}%.`,
                            'danger');
                        return;
                    }
                }
                if (REQUIRE_TOTAL_100 && Math.round(summary.total) !== 100) {
                    toast('Total bobot harus tepat 100% sebelum submit.', 'danger');
                    return;
                }
                const $btn = $(this).prop('disabled', true);
                $.ajax({
                        url: "{{ route('ipp.submit') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json"
                    })
                    .done(res => {
                        applyLockDom(true);
                        renderIppStatus('submitted');
                        updateExportVisibility();
                        toast(res?.message || 'IPP berhasil disubmit.');
                    })
                    .fail(err => toast(err?.responseJSON?.message || 'Gagal submit IPP.', 'danger'))
                    .always(() => $btn.prop('disabled', false));
            });

            /* ===== Lock tampilan ===== */
            function applyLockDom(locked) {
                LOCKED = !!locked;
                $('.js-open-modal').toggleClass('d-none', LOCKED);
                $('#btnSubmitAll').toggleClass('d-none', LOCKED);
                $('table.js-table').each(function() {
                    const $t = $(this);
                    $t.find('thead th:last-child').toggleClass('d-none', LOCKED);
                    $t.find('tbody tr').each(function() {
                        $(this).find('td:last-child').toggleClass('d-none', LOCKED);
                    });
                });
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
                                        due_date: pt.due_date,
                                        weight: pt.weight,
                                        status: pt.status || 'draft'
                                    });
                                    $tbody.append(html);
                                });
                            });
                        }

                        const ippStatus = res?.ipp?.status || null;
                        renderIppStatus(ippStatus);
                        updateExportVisibility();

                        const locked = !!(res?.locked || res?.ipp?.locked || (res?.ipp?.status === 'submitted'));
                        applyLockDom(locked);

                        // Indikator komentar
                        const ippId = res?.ipp?.id || null;
                        window.__currentIppId = ippId;
                        const totalComments = Number(res?.comments_count || 0);
                        setCommentIndicator(ippId, totalComments);

                        recalcAll();
                    })
                    .fail(() => toast('Gagal memuat data awal IPP.', 'danger'));
            }

            $(document).ready(function() {
                // buka semua panel
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
