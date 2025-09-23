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
        @endphp

        {{-- BADGE STATUS GLOBAL (diambil dari ipp->status via AJAX init) --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">Status IPP:</span>
                <span id="ippStatusBadge" class="badge badge-secondary">—</span>
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
                                                {{-- STATUS PER-ACCORDION DIHAPUS --}}
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

                {{-- Submit + Export (Export muncul hanya jika IPP sudah ada) --}}
                <div class="d-flex justify-content-end mt-3 gap-2">
                    <a href="{{ route('ipp.export.excel') }}" class="btn btn-primary d-none" id="btnExport">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Export IPP
                    </a>
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

            /* ====== ringkasan & warna di header kategori (Cap/Used) ====== */
            function updateCategoryCard(cat) {
                const used = sumWeights(cat);
                const cap = CAT_CAP[cat];

                $(`.js-used[data-cat="${cat}"]`).text(fmt(used));

                const cls = pickUsedBadgeClass(used, cap);
                $(`.js-used[data-cat="${cat}"]`).closest('.badge')
                    .removeClass('badge-primary badge-warning badge-danger badge-secondary')
                    .addClass(cls);
            }

            function updateTotal() {
                const s = buildSummaryFromDom();
                // total sekarang tidak ditampilkan (ringkasan dihapus),
                // tapi fungsi ini tetap dibiarkan jika nanti diperlukan.
            }

            function recalcAll() {
                Object.keys(CAT_CAP).forEach(updateCategoryCard);
                updateTotal();
            }

            function updateExportVisibility() {
                const hasDraft = $('table.js-table tbody tr').not('.empty-row').length > 0;
                $('#btnExport').toggleClass('d-none', !hasDraft);
            }

            /* ====== Status IPP global ====== */
            function renderIppStatus(status) {
                const $b = $('#ippStatusBadge');
                const map = {
                    'submitted': {
                        text: 'Submitted',
                        cls: 'badge-success'
                    },
                    'draft': {
                        text: 'Draft',
                        cls: 'badge-warning'
                    },
                };
                const info = map[(status || '').toLowerCase()] || {
                    text: '—',
                    cls: 'badge-secondary'
                };
                $b.removeClass('badge-secondary badge-warning badge-success').addClass(info.cls).text(info.text);
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
                            $tbody.find('.empty-row').remove();
                            $tbody.append(makeRowHtml(newRowId, rowData));
                        } else {
                            const $old = $(
                                `table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`);
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
                        updateExportVisibility();
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
                        updateExportVisibility();
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
                        applyLockDom(true);
                        renderIppStatus('submitted');
                        updateExportVisibility();
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
                                });
                            });
                        }

                        // Status global & Export visibility
                        const ippStatus = res?.ipp?.status || null;
                        renderIppStatus(ippStatus);
                        updateExportVisibility();

                        const locked = !!(res?.locked || res?.ipp?.locked || (res?.ipp?.status === 'submitted'));
                        applyLockDom(locked);
                        recalcAll();
                    })
                    .fail(() => toast('Gagal memuat data awal IPP.', 'danger'));
            }

            $(document).ready(function() {
                // Pastikan semua panel terbuka
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
