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
            --ipp-acc: #0d6efd;
            --ipp-text: #111827;
        }

        .accordion.ipp .accordion-item {
            border: 1px solid var(--ipp-border);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            margin-bottom: .875rem;
            box-shadow: 0 1px 0 rgba(17, 24, 39, .02);
        }

        .accordion.ipp .accordion-button {
            background: #fafafa;
            color: var(--ipp-text);
            font-weight: 600;
        }

        .accordion.ipp .accordion-button:not(.collapsed) {
            background: #f5f7fb;
            box-shadow: inset 0 -1px 0 var(--ipp-border);
        }

        .accordion.ipp .accordion-button:focus {
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .2);
        }

        .accordion.ipp .accordion-header {
            border-bottom: 1px solid var(--ipp-border);
        }

        .table.ipp-table {
            border-color: var(--ipp-border);
            font-size: 0.95rem;
        }

        .table.ipp-table thead th {
            background: var(--ipp-header-bg) !important;
            color: var(--ipp-text);
            font-weight: 700;
            border-bottom: 1px solid var(--ipp-border);
        }

        .table.ipp-table tbody tr:nth-child(even) {
            background: var(--ipp-row-alt);
        }

        .table.ipp-table tbody tr:hover {
            background: var(--ipp-row-hover);
        }

        .table.ipp-table td,
        .table.ipp-table th {
            padding: .9rem 1rem;
            vertical-align: middle;
        }

        .btn-group.btn-group-sm .btn {
            padding: .45rem .6rem;
        }

        .container-xxl {
            max-width: 1360px;
        }

        .req::after {
            content: "*";
            color: #dc3545;
            margin-left: .25rem;
        }

        .table thead th {
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 1;
            background: #fff;
        }

        .badge-cap {
            background: #f1f3f5;
            color: #6c757d;
        }

        .empty-row td {
            padding: 1.25rem !important;
            color: #6c757d;
            font-style: italic;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-3">
        @php
            $categories = [
                ['key' => 'activity_management', 'title' => 'I. Activity Management', 'cap' => 70],
                ['key' => 'people_development', 'title' => 'II. People Development', 'cap' => 10],
                ['key' => 'crp', 'title' => 'III. CRP', 'cap' => 10],
                ['key' => 'special_assignment', 'title' => 'IV. Special Assignment & Improvement', 'cap' => 10],
            ];
            $catCapMap = array_column($categories, 'cap', 'key');
        @endphp

        {{-- RINGKASAN --}}
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold">Ringkasan Bobot</h6>
                        <span class="small text-muted">Total: <span id="totalUsed">0</span>% (Target 100%)</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($categories as $cat)
                                <div class="col-md-6 col-lg-3">
                                    <div class="border rounded p-3 summary-card h-100" aria-live="polite">
                                        <div class="title mb-1">{{ $cat['title'] }}</div>
                                        <div class="cap mb-2">Cap:
                                            <span class="badge badge-cap">{{ $cat['cap'] }}%</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="used js-used" data-cat="{{ $cat['key'] }}">0</span>%
                                                <span class="ms-2 js-status-cat" data-cat="{{ $cat['key'] }}">
                                                    <span class="status-ok d-none">OK</span>
                                                    <span class="status-over d-none">Over Cap</span>
                                                </span>
                                            </div>
                                            <div class="small text-muted">
                                                Sisa: <span class="js-left"
                                                    data-cat="{{ $cat['key'] }}">{{ $cat['cap'] }}</span>%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PROGRAM / ACTIVITY --}}
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="accordion mt-3 ipp" id="accordionPrograms">
                    @foreach ($categories as $i => $cat)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $cat['key'] }}">
                                <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse-{{ $cat['key'] }}"
                                    aria-expanded="{{ $i == 0 ? 'true' : 'false' }}"
                                    aria-controls="collapse-{{ $cat['key'] }}">
                                    <div class="d-flex flex-column w-100">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <span>{{ $cat['title'] }}</span>
                                            <span class="small text-muted">
                                                <span class="me-2">Cap <span
                                                        class="badge badge-cap">{{ $cat['cap'] }}%</span></span>
                                                <span>Used <span class="badge"><span class="js-used"
                                                            data-cat="{{ $cat['key'] }}">0</span>%</span></span>
                                                <span class="ms-2">
                                                    Status
                                                    <span class="badge badge-secondary js-cat-status"
                                                        data-cat="{{ $cat['key'] }}">—</span>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $cat['key'] }}"
                                class="accordion-collapse collapse {{ $i == 0 ? 'show' : '' }}"
                                data-bs-parent="#accordionPrograms">
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
                                                    {{-- <th style="width:22%">Target MID</th> --}}
                                                    <th style="width:22%">Target One</th>
                                                    <th style="width:10%">Due</th>
                                                    <th style="width:6%">W%</th>
                                                    <th style="width:6%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="js-tbody">
                                                <tr class="empty-row">
                                                    <td colspan="5">Belum ada point. Klik "Tambah Point" untuk memulai.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Submit All --}}
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-success" id="btnSubmitAll">
                        <i class="bi bi-send-check"></i> Submit IPP
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('website.ipp.modal.create')
@endsection

@push('scripts')
    <script>
        (function($) {
            // ======= SETTINGS =======
            const REQUIRE_TOTAL_100 = true;
            // CAP map diset di Blade (array static di atas)
            const CAT_CAP = {
                activity_management: 70,
                people_development: 10,
                crp: 10,
                special_assignment: 10,
            };

            const pointModal = new bootstrap.Modal(document.getElementById('pointModal'));
            let autoRowId = 0;

            // counter status (untuk badge per kategori)
            const catCounters = {};
            Object.keys(CAT_CAP).forEach(k => catCounters[k] = {
                draft: 0,
                submitted: 0
            });

            // ======= UTIL =======
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

            function fmt(n) {
                return (Math.round((n + Number.EPSILON))).toFixed(0);
            }

            function pickUsedBadgeClass(used, cap) {
                const eps = 1e-6;
                if (Math.abs(used) <= eps) return 'text-bg-secondary';
                if (used > cap + eps) return 'text-bg-danger';
                if (Math.abs(used - cap) <= eps) return 'text-bg-primary';
                return 'text-bg-warning';
            }

            function renderCategoryStatus(cat) {
                const ctr = catCounters[cat] || {
                    draft: 0,
                    submitted: 0
                };
                const $b = $(`.js-cat-status[data-cat="${cat}"]`);
                let label = '—',
                    cls = 'text-bg-secondary';
                if (ctr.submitted > 0) {
                    label = 'Submitted';
                    cls = 'text-bg-primary';
                } else if (ctr.draft > 0) {
                    label = 'Draft';
                    cls = 'text-bg-warning';
                }
                $b.text(label).removeClass('text-bg-secondary text-bg-warning text-bg-primary').addClass(cls);
            }

            function bumpCounter(cat, prevStatus, newStatus) {
                const ctr = catCounters[cat] || (catCounters[cat] = {
                    draft: 0,
                    submitted: 0
                });
                if (prevStatus === 'draft') ctr.draft = Math.max(0, ctr.draft - 1);
                if (prevStatus === 'submitted') ctr.submitted = Math.max(0, ctr.submitted - 1);
                if (newStatus === 'draft') ctr.draft += 1;
                if (newStatus === 'submitted') ctr.submitted += 1;
                renderCategoryStatus(cat);
            }

            function updateCategoryCard(cat) {
                const used = sumWeights(cat);
                const cap = CAT_CAP[cat];
                const left = Math.max(cap - used, 0);
                $(`.js-used[data-cat="${cat}"]`).text(fmt(used));
                $(`.js-left[data-cat="${cat}"]`).text(fmt(left));
                const over = used > cap;
                const $wrap = $(`.js-status-cat[data-cat="${cat}"]`);
                $wrap.find('.status-ok').toggleClass('d-none', over);
                $wrap.find('.status-over').toggleClass('d-none', !over);
                const $badges = $(`.js-used[data-cat="${cat}"]`).closest('.badge');
                $badges.removeClass('text-bg-primary text-bg-warning text-bg-danger text-bg-secondary')
                    .addClass(pickUsedBadgeClass(used, cap));
            }

            function updateTotal() {
                const s = buildSummaryFromDom();
                $('#totalUsed').text(fmt(s.total));
            }

            function recalcAll() {
                Object.keys(CAT_CAP).forEach(updateCategoryCard);
                updateTotal();
                Object.keys(CAT_CAP).forEach(renderCategoryStatus);
            }

            // ======= RENDER ROW =======
            function makeRowHtml(rowId, data) {
                const oneShort = (data.target_one || '').length > 110 ? (data.target_one.slice(0, 110) + '…') : (data
                    .target_one || '-');
                const dueTxt = data.due_date ? data.due_date : '-';
                const w = isNaN(parseFloat(data.weight)) ? 0 : parseFloat(data.weight);
                const wCls = w > 0 ? 'text-bg-primary' : 'text-bg-secondary';
                return `
<tr class="align-middle point-row"
    data-row-id="${rowId}"
    data-activity="${_.escape(data.activity||'')}"
    data-mid="${_.escape(data.target_mid||'')}"
    data-one="${_.escape(data.target_one||'')}"
    data-due="${_.escape(dueTxt)}"
    data-status="${_.escape(data.status||'')}"
    data-weight="${w}">
  <td class="fw-semibold">${_.escape(data.activity||'-')}</td>
  <td class="text-muted">${_.escape(oneShort)}</td>
  <td><span class="badge text-bg-light">${_.escape(dueTxt)}</span></td>
  <td><span class="badge ${wCls}">${fmt(w)}</span></td>
  <td class="text-end">
    <div class="btn-group btn-group-sm" role="group" aria-label="Aksi baris">
      <button type="button" class="btn btn-warning js-edit" title="Edit" aria-label="Edit point"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
      <button type="button" class="btn btn-danger js-remove" title="Hapus" aria-label="Hapus point"><i class="bi bi-trash" aria-hidden="true"></i></button>
    </div>
  </td>
</tr>`;
            }
            window._ = window._ || {
                escape: (s) => String(s).replace(/[&<>"'`=\/]/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                    '=': '&#x3D;'
                } [c]))
            };

            function ensureNotEmpty($tbody) {
                const hasRows = $tbody.find('tr').length > 0 && !$tbody.find('.empty-row').length;
                if (!hasRows) {
                    const col = $tbody.closest('table').find('thead th').length;
                    $tbody.html(
                        `<tr class="empty-row"><td colspan="${col}">Belum ada point. Klik "Tambah Point" untuk memulai.</td></tr>`
                    );
                }
            }

            function removeEmptyState($tbody) {
                $tbody.find('.empty-row').remove();
            }

            // ======= OPEN MODAL (CREATE) =======
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

            // ======= OPEN MODAL (EDIT) =======
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

            // ======= SUBMIT PER-POINT (SELALU DRAFT) =======
            $('#pointForm').on('submit', function(e) {
                e.preventDefault();
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
                    weight: parseFloat($('#pmWeight').val())
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
                    })
                    .done(res => {
                        const rowData = {
                            ...data,
                            status: 'draft'
                        };
                        const newRowId = res?.row_id || res?.id || ('row-' + (++autoRowId));
                        const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);

                        if (mode === 'create') {
                            bumpCounter(cat, null, 'draft');
                            const html = makeRowHtml(newRowId, rowData);
                            removeEmptyState($tbody);
                            const $row = $(html).addClass('adding');
                            $tbody.append($row);
                            requestAnimationFrame(() => $row.addClass('show').removeClass('adding'));
                        } else {
                            const $old = $(
                                `table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`);
                            const prev = $old.data('status') || null;
                            bumpCounter(cat, prev, 'draft');
                            if ($old.length) $old.replaceWith(makeRowHtml(newRowId, rowData));
                            else {
                                const html = makeRowHtml(newRowId, rowData);
                                removeEmptyState($tbody);
                                $tbody.append(html);
                            }
                        }

                        recalcAll();
                        pointModal.hide();
                        toast(res?.message || 'Draft tersimpan.');
                    })
                    .fail(err => {
                        toast(err?.responseJSON?.message || 'Gagal menyimpan. Data tidak ditambahkan.',
                            'danger');
                    })
                    .always(unlock);

                function unlock() {
                    $btn.prop('disabled', false);
                }
            });

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

            // ======= HAPUS (lokal) =======
            $(document).on('click', '.js-remove', function() {
                const $tr = $(this).closest('tr');
                const cat = $(this).closest('table').data('cat');
                const id = $tr.data('row-id'); // id DB
                const prev = $tr.data('status') || null;

                if (!id) {
                    // fallback: kalau entah bagaimana tak ada id, hapus lokal saja
                    $tr.addClass('removing');
                    setTimeout(() => {
                        const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                        $tr.remove();
                        ensureNotEmpty($tbody);
                        recalcAll();
                        if (prev) bumpCounter(cat, prev, null);
                        toast('Point dihapus (lokal).', 'warning');
                    }, 180);
                    return;
                }

                if (!confirm('Hapus point ini?')) return;

                // kunci tombol agar tidak double click
                const $btn = $(this).prop('disabled', true);

                ajaxDeletePoint(id)
                    .done(res => {
                        $tr.addClass('removing');
                        setTimeout(() => {
                            const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                            $tr.remove();
                            ensureNotEmpty($tbody);
                            recalcAll();
                            if (prev) bumpCounter(cat, prev, null);
                            toast(res?.message || 'Point dihapus.');
                        }, 180);
                    })
                    .fail(err => {
                        toast(err?.responseJSON?.message || 'Gagal menghapus point.', 'danger');
                    })
                    .always(() => $btn.prop('disabled', false));
            });

            // ======= SUBMIT ALL =======
            $('#btnSubmitAll').on('click', function() {
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
                        // tandai semua row submitted & refresh counters
                        Object.keys(CAT_CAP).forEach(cat => {
                            const $rows = $(`table.js-table[data-cat="${cat}"] tbody tr`).not(
                                '.empty-row');
                            let count = 0;
                            $rows.each(function() {
                                $(this).attr('data-status', 'submitted');
                                count++;
                            });
                            catCounters[cat].submitted = count;
                            catCounters[cat].draft = 0;
                            renderCategoryStatus(cat);
                        });
                        toast(res?.message || 'IPP berhasil disubmit.');
                    })
                    .fail(err => {
                        toast(err?.responseJSON?.message || 'Gagal submit IPP.', 'danger');
                    })
                    .always(() => $btn.prop('disabled', false));
            });

            // ======= INIT: load data via AJAX (/ipp/init) =======
            function loadInitial() {
                $.ajax({
                        url: "{{ route('ipp.init') }}",
                        method: "GET",
                        dataType: "json"
                    })
                    .done(res => {
                        // Render points per kategori
                        if (res?.points) {
                            Object.keys(res.points).forEach(cat => {
                                const list = res.points[cat] || [];
                                const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                                if (list.length) removeEmptyState($tbody);
                                list.forEach(pt => {
                                    const html = makeRowHtml(pt.id, pt);
                                    $tbody.append(html);
                                    bumpCounter(cat, null, pt.status || 'draft');
                                });
                            });
                        }
                        recalcAll();
                    })
                    .fail(() => {
                        toast('Gagal memuat data awal IPP.', 'danger');
                    });
            }

            // ======= DOCUMENT READY =======
            $(document).ready(function() {
                // pastikan tabel ada empty state
                $('table.js-table tbody.js-tbody').each(function() {
                    const $tb = $(this);
                    if ($tb.find('tr').not('.empty-row').length === 0) ensureNotEmpty($tb);
                });

                // load data awal via AJAX
                loadInitial();
            });

            // ======= TOAST =======
            function toast(msg, type = 'success') {
                const id = 'toast-' + Date.now();
                const $t = $(`
<div class="toast align-items-center text-bg-${type} border-0" id="${id}"
     role="status" aria-live="polite" aria-atomic="true"
     style="position:fixed;top:1rem;right:1rem;z-index:1080;">
  <div class="d-flex">
    <div class="toast-body">${msg}</div>
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
