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

        /* ===== Used box (ringkasan) ===== */
        .used-box {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .2rem .6rem;
            border-radius: .6rem;
            font-weight: 600;
        }

        /* Warna saat class badge-* ditempel ke .used-box */
        .used-box.badge-secondary {
            background: #f1f3f5;
            color: #495057;
        }

        .used-box.badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .used-box.badge-primary {
            background: #e7f1ff;
            color: #0d6efd;
        }

        .used-box.badge-danger {
            background: #fde2e1;
            color: #842029;
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
                        <a href="{{ route('ipp.export') }}" class="btn btn-primary">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Export IPP
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($categories as $cat)
                                <div class="col-md-6 col-lg-3">
                                    <div class="border rounded p-3 summary-card h-100" aria-live="polite">
                                        <div class="title mb-1">{{ $cat['title'] }}</div>
                                        <div class="cap mb-2">Cap:
                                            <span class="badge badge-cap js-cap-badge"
                                                data-cat="{{ $cat['key'] }}">{{ $cat['cap'] }}%</span>
                                        </div>
                                        {{-- MODIF: box used + status diberi kelas badge used-box + data-cat --}}
                                        <div class="badge used-box js-used-box" data-cat="{{ $cat['key'] }}">
                                            <span class="used js-used" data-cat="{{ $cat['key'] }}">0</span>%
                                            <span class="ms-2 js-status-cat" data-cat="{{ $cat['key'] }}">
                                                <span class="status-ok d-none">OK</span>
                                                <span class="status-over d-none">Over Cap</span>
                                            </span>
                                        </div>
                                        {{-- /MODIF --}}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <span class="small text-muted">Total: <span id="totalUsed">0</span>% (Target 100%)</span>
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
                                    aria-expanded="true" aria-controls="collapse-{{ $cat['key'] }}">
                                    <div class="d-flex flex-column w-100">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <span>{{ $cat['title'] }}</span>
                                            <span class="small text-muted">
                                                <span class="me-2">Cap
                                                    <span class="badge badge-cap js-cap-badge"
                                                        data-cat="{{ $cat['key'] }}">{{ $cat['cap'] }}%</span>
                                                </span>
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
                                                    {{-- <th style="width:22%">Target MID</th> --}}
                                                    <th style="width:22%">Target One</th>
                                                    <th style="width:10%">Due</th>
                                                    <th style="width:6%">W%</th>
                                                    <th style="width:6%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="js-tbody">
                                                <tr class="empty-row">
                                                    <td colspan="5">Klik "Tambah Point" untuk memulai.
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
            const REQUIRE_TOTAL_100 = true;
            let CAT_CAP = {
                activity_management: 70,
                people_development: 10,
                crp: 10,
                special_assignment: 10,
            };

            const pointModal = new bootstrap.Modal(document.getElementById('pointModal'));
            let autoRowId = 0;
            let LOCKED = false;

            const catCounters = {};
            Object.keys(CAT_CAP).forEach(k => (catCounters[k] = {
                draft: 0,
                submitted: 0
            }));

            /* ===== Utils kecil ===== */
            const esc = (s) =>
                String(s ?? '').replace(/[&<>"'`=\/]/g, (c) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                    '=': '&#x3D;',
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
                Object.keys(CAT_CAP).forEach((c) => (s[c] = sumWeights(c)));
                s.total = Object.values(s).reduce((a, b) => a + b, 0);
                return s;
            }

            function pickUsedBadgeClass(used, cap) {
                const eps = 1e-6;
                if (Math.abs(used) <= eps) return 'badge-secondary';
                if (used > cap + eps) return 'badge-danger';
                if (Math.abs(used - cap) <= eps) return 'badge-primary';
                return 'badge-warning';
            }

            function renderCategoryStatus(cat) {
                const ctr = catCounters[cat] || {
                    draft: 0,
                    submitted: 0
                };
                const $b = $(`.js-cat-status[data-cat="${cat}"]`);
                let label = '—',
                    cls = 'badge-secondary';
                if (ctr.submitted > 0) {
                    label = 'Submitted';
                    cls = 'badge-primary';
                } else if (ctr.draft > 0) {
                    label = 'Draft';
                    cls = 'badge-warning';
                }
                $b.text(label).removeClass('badge-secondary badge-warning badge-primary').addClass(cls);
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

            /* ====== builder HTML baris (dipakai create, edit, init) ====== */
            function makeRowHtml(rowId, data) {
                const w = isNaN(parseFloat(data.weight)) ? 0 : parseFloat(data.weight);
                const oneShort =
                    (data.target_one || '').length > 110 ?
                    data.target_one.slice(0, 110) + '…' :
                    (data.target_one || '-');
                const dueTxt = data.due_date ? data.due_date : '-';

                return `
      <tr class="align-middle point-row"
          data-row-id="${esc(rowId)}"
          data-activity="${esc(data.activity || '')}"
          data-mid="${esc(data.target_mid || '')}"
          data-one="${esc(data.target_one || '')}"
          data-due="${esc(dueTxt)}"
          data-status="${esc(data.status || 'draft')}"
          data-weight="${w}">
        <td class="fw-semibold">${esc(data.activity || '-')}</td>
        <td class="text-muted">${esc(oneShort)}</td>
        <td><span class="badge badge-light">${esc(dueTxt)}</span></td>
        <td><span>${fmt(w)}</span></td>
        <td class="text-end">
          <div class="btn-group btn-group-sm" role="group" aria-label="Aksi baris">
            <button type="button" class="btn btn-warning js-edit" title="Edit"><i class="bi bi-pencil-square"></i></button>
            <button type="button" class="btn btn-danger js-remove" title="Hapus"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      </tr>`;
            }

            /* ====== ringkasan & warna ====== */
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

                const cls = pickUsedBadgeClass(used, cap);
                $(`.js-used[data-cat="${cat}"]`).closest('.badge')
                    .removeClass('badge-primary badge-warning badge-danger badge-secondary').addClass(cls);

                $(`.js-used-box[data-cat="${cat}"]`)
                    .removeClass('badge-primary badge-warning badge-danger badge-secondary').addClass(cls);
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

            /* ====== OPEN MODAL (CREATE) ====== */
            $(document).on('click', '.js-open-modal', function() {
                const cat = $(this).data('cat');
                $('#pmCat').val(cat);
                $('#pmMode').val('create');
                $('#pmRowId').val('');
                $('#pointModalLabel').text(
                    'Tambah Point — ' + $(this).closest('.accordion-item').find(
                        '.accordion-button span:first').text()
                );
                $('#pointForm')[0].reset();
                setTimeout(() => $('#pmActivity').trigger('focus'), 150);
                pointModal.show();
            });

            /* ====== OPEN MODAL (EDIT) ====== */
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

            /* ====== SUBMIT PER-POINT (CREATE/EDIT) ====== */
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
                    })
                    .done((res) => {
                        const rowData = {
                            ...data,
                            status: 'draft'
                        };
                        const newRowId = res?.row_id || res?.id || ('row-' + ++autoRowId);
                        const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);

                        if (mode === 'create') {
                            bumpCounter(cat, null, 'draft');
                            $tbody.find('.empty-row').remove();
                            $tbody.append(makeRowHtml(newRowId, rowData));
                        } else {
                            const $old = $(
                                `table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`);
                            const prev = $old.data('status') || null;
                            bumpCounter(cat, prev, 'draft');

                            // *** FIX: ganti row lama dengan markup BARU ***
                            if ($old.length) {
                                $old.replaceWith(makeRowHtml(newRowId, rowData));
                            } else {
                                $tbody.find('.empty-row').remove();
                                $tbody.append(makeRowHtml(newRowId, rowData));
                            }
                        }

                        recalcAll();
                        pointModal.hide();
                        toast(res?.message || 'Draft tersimpan.');
                        if (res?.locked) applyLockDom(true);
                    })
                    .fail((err) => {
                        toast(err?.responseJSON?.message || 'Gagal menyimpan. Data tidak ditambahkan.',
                            'danger');
                    })
                    .always(unlock);

                function unlock() {
                    $btn.prop('disabled', false);
                }
            });

            /* ====== HAPUS ====== */
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
                const prev = $tr.data('status') || null;

                if (!id) {
                    $tr.remove();
                    const $tb = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                    if ($tb.find('tr').not('.empty-row').length === 0) {
                        $tb.html(
                            '<tr class="empty-row"><td colspan="5">Klik "Tambah Point" untuk memulai.</td></tr>'
                        );
                    }
                    recalcAll();
                    if (prev) bumpCounter(cat, prev, null);
                    toast('Point dihapus (lokal).', 'warning');
                    return;
                }

                if (!confirm('Hapus point ini?')) return;

                ajaxDeletePoint(id)
                    .done((res) => {
                        $tr.remove();
                        const $tb = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                        if ($tb.find('tr').not('.empty-row').length === 0) {
                            $tb.html(
                                '<tr class="empty-row"><td colspan="5">Klik "Tambah Point" untuk memulai.</td></tr>'
                            );
                        }
                        recalcAll();
                        if (prev) bumpCounter(cat, prev, null);
                        toast(res?.message || 'Point dihapus.');
                    })
                    .fail((err) => toast(err?.responseJSON?.message || 'Gagal menghapus point.', 'danger'));
            });

            /* ====== SUBMIT ALL ====== */
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
                    .done((res) => {
                        Object.keys(CAT_CAP).forEach((cat) => {
                            const $rows = $(`table.js-table[data-cat="${cat}"] tbody tr`).not(
                                '.empty-row');
                            let cnt = 0;
                            $rows.each(function() {
                                $(this).attr('data-status', 'submitted');
                                cnt++;
                            });
                            catCounters[cat].submitted = cnt;
                            catCounters[cat].draft = 0;
                            renderCategoryStatus(cat);
                        });
                        applyLockDom(true);
                        toast(res?.message || 'IPP berhasil disubmit.');
                    })
                    .fail((err) => toast(err?.responseJSON?.message || 'Gagal submit IPP.', 'danger'))
                    .always(() => $btn.prop('disabled', false));
            });

            /* ====== Lock tampilan ====== */
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

            /* ====== INIT ====== */
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
                                        status: pt.status || 'draft',
                                    });
                                    $tbody.append(html);
                                    bumpCounter(cat, null, pt.status || 'draft');
                                });
                            });
                        }

                        const locked = !!(res?.locked || res?.ipp?.locked || (res?.ipp?.status === 'submitted'));
                        applyLockDom(locked);
                        recalcAll();
                    })
                    .fail(() => toast('Gagal memuat data awal IPP.', 'danger'));
            }

            $(document).ready(function() {
                $('table.js-table tbody.js-tbody').each(function() {
                    const $tb = $(this);
                    if ($tb.find('tr').not('.empty-row').length === 0) {
                        $tb.html(
                            '<tr class="empty-row"><td colspan="5">Klik "Tambah Point" untuk memulai.</td></tr>'
                        );
                    }
                });

                $('#accordionPrograms .accordion-ctollapse').each(function() {
                    this.removeAttribute('data-bs-parent');
                    this.classList.add('show');
                    const btn = $(this).prev('.accordion-header').find('.accordion-button');
                    btn.removeClass('collapsed').attr('aria-expanded', 'true');
                });

                loadInitial();
            });

            /* ====== Toast ====== */
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
