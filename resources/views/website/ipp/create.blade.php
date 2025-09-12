@extends('layouts.root.main')

@section('title', $title ?? 'IPP')
@section('breadcrumbs', $title ?? 'IPP')

@push('custom-css')
    <style>
        /* ==== IPP Theme (kontras ramah mata) ==== */
        :root {
            --ipp-border: #e5e7eb;
            /* abu-abu border */
            --ipp-header-bg: #f8fafc;
            /* header tabel */
            --ipp-row-alt: #fbfdff;
            /* zebra */
            --ipp-row-hover: #eef2ff;
            /* hover lembut */
            --ipp-acc: #0d6efd;
            /* aksen */
            --ipp-text: #111827;
            /* teks utama */
        }

        /* Accordion yang lebih jelas batasnya */
        .accordion.ipp .accordion-item {
            border: 1px solid var(--ipp-border);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            margin-bottom: .875rem;
            /* jarak antar aktivitas */
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

        /* Tabel: header beda warna, zebra, hover */
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

        /* Badge bobot per-baris tetap jelas */
        .table .badge {
            font-weight: 700;
            letter-spacing: .2px;
        }

        /* Tombol aksi: area klik besar */
        .btn-group.btn-group-sm .btn {
            padding: .45rem .6rem;
        }

        /* ===== Layout & Utilities ===== */
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

        .weight-warning {
            color: #b02a37;
            font-weight: 600;
            display: none;
        }

        /* ===== Animations (rows) ===== */
        .point-row.adding {
            opacity: 0;
            transform: translateY(-6px);
        }

        .point-row.adding.show {
            opacity: 1;
            transform: translateY(0);
            transition: all .25s ease;
        }

        .point-row.removing {
            opacity: 0;
            transform: translateX(12px);
            transition: all .2s ease;
        }

        /* ===== Summary cards ===== */
        .summary-card .title {
            font-weight: 600;
            line-height: 1.25;
            min-height: calc(1.25em * 2);
            /* tinggi 2 baris */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* potong di 2 baris jika lebih */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }


        .summary-card .cap {
            font-size: .85rem;
            color: #6c757d;
        }

        .summary-card .used {
            font-weight: 700;
        }

        .summary-card .status-ok {
            color: #198754;
            font-weight: 600;
        }

        .summary-card .status-over {
            color: #dc3545;
            font-weight: 600;
        }

        .summary-total .status-ok {
            color: #198754;
            font-weight: 600;
        }

        .summary-total .status-bad {
            color: #dc3545;
            font-weight: 600;
        }

        /* ===== Buttons & focus ===== */
        .btn:focus,
        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25);
        }

        /* ===== Empty state ===== */
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
            // Build a key=>cap map to keep UI and validation in sync
            $catCapMap = array_column($categories, 'cap', 'key');
        @endphp

        {{-- RINGKASAN (tanpa progress bar) --}}
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">Ringkasan Bobot</h6>
                        <span class="small text-muted">Target total 100%</span>
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

        {{-- PROGRAM / ACTIVITY (tanpa progress bar di header accordion) --}}
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
                                                    <td colspan="6">Belum ada point. Klik "Tambah Point" untuk memulai.
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

                {{-- ACTIONS --}}
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-primary" id="btnSubmit">
                        <i class="bi bi-send" aria-hidden="true"></i> Simpan IPP
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnDraft">
                        <i class="bi bi-save" aria-hidden="true"></i> Simpan Draft
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
            // Keep JS map in sync with PHP categories (no duplication)
            const CAT_CAP = @json($catCapMap);

            const pointModal = new bootstrap.Modal(document.getElementById('pointModal'));

            // ===== Helpers =====
            function sumWeights(cat) {
                let sum = 0;
                $(`table.js-table[data-cat="${cat}"] tbody tr`).each(function() {
                    const w = parseFloat($(this).data('weight')) || 0;
                    sum += w;
                });
                return sum;
            }

            function fmt(n) { // 0 decimals but clamp tiny floats
                return (Math.round((n + Number.EPSILON) * 1) / 1).toFixed(0);
            }

            function updateCategoryCard(cat) {
                const used = sumWeights(cat);
                const cap = CAT_CAP[cat];
                const left = Math.max(cap - used, 0);

                // update angka used di dua tempat (ringkasan & header accordion)
                $(`.js-used[data-cat="${cat}"]`).text(fmt(used));
                $(`.js-left[data-cat="${cat}"]`).text(fmt(left));

                // status text OK/Over
                const over = used > cap;
                const $statusWrap = $(`.js-status-cat[data-cat="${cat}"]`);
                $statusWrap.find('.status-ok').toggleClass('d-none', over);
                $statusWrap.find('.status-over').toggleClass('d-none', !over);

                const $usedBadges = $(`.js-used[data-cat="${cat}"]`).closest('.badge');
                $usedBadges
                    .removeClass(
                        'badge-primary badge-warning badge-danger badge-secondary'
                    )
                    .addClass(pickUsedBadgeClass(used, cap));


                $(`.weight-warning[data-cat="${cat}"]`).toggle(over);
            }

            function updateTotal() {
                let total = 0;
                Object.keys(CAT_CAP).forEach(cat => total += sumWeights(cat));
                $('#totalUsed').text(fmt(total));

                const ok = (fmt(total) == '100');
                $('#totalStatus').toggleClass('d-none', !ok);
                $('#totalStatusBad').toggleClass('d-none', ok);
                $('#totalWarn').toggle(!ok);
            }

            function recalcAll() {
                Object.keys(CAT_CAP).forEach(updateCategoryCard);
                updateTotal();
            }

            // ===== Row Renderer =====
            let autoRowId = 0;

            function makeRowHtml(rowId, data) {
                const midShort = (data.target_mid || '').length > 110 ? (data.target_mid.slice(0, 110) + '…') : (data
                    .target_mid || '-');
                const oneShort = (data.target_one || '').length > 110 ? (data.target_one.slice(0, 110) + '…') : (data
                    .target_one || '-');
                const dueTxt = data.due_date ? data.due_date : '-';
                const w = isNaN(parseFloat(data.weight)) ? 0 : parseFloat(data.weight);

                return `
      <tr class="align-middle point-row"
          data-row-id="${rowId}"
          data-activity="${_.escape(data.activity||'')}"
          data-mid="${_.escape(data.target_mid||'')}"
          data-one="${_.escape(data.target_one||'')}"
          data-due="${_.escape(dueTxt)}"
          data-weight="${w}">
        <td class="fw-semibold">${_.escape(data.activity||'-')}</td>
        <td class="text-muted">${_.escape(oneShort)}</td>
        <td><span class="badge text-bg-light">${_.escape(dueTxt)}</span></td>
        <td><span class="badge ${w>0?'text-bg-primary':'text-bg-secondary'}">${fmt(w)}</span></td>
        <td class="text-end">
          <div class="btn-group btn-group-sm" role="group" aria-label="Aksi baris">
            <button type="button" class="btn btn-warning js-edit" title="Edit" aria-label="Edit point"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-danger js-remove" title="Hapus" aria-label="Hapus point"><i class="bi bi-trash" aria-hidden="true"></i></button>
          </div>
        </td>
      </tr>
    `;
            }

            // Lodash-escape fallback (jaga-jaga bila lodash tidak tersedia)
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
                    $tbody.html(
                        '<tr class="empty-row"><td colspan="6">Belum ada point. Klik "Tambah Point" untuk memulai.</td></tr>'
                    );
                }
            }

            function removeEmptyState($tbody) {
                $tbody.find('.empty-row').remove();
            }

            // ===== Modal Create =====
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

            // ===== Modal Edit =====
            $(document).on('click', '.js-edit', function() {
                const $tr = $(this).closest('tr');
                const cat = $(this).closest('table').data('cat');

                $('#pmCat').val(cat);
                $('#pmMode').val('edit');
                $('#pmRowId').val($tr.data('row-id'));
                $('#pointModalLabel').text('Edit Point');

                $('#pmActivity').val($tr.data('activity'));
                $('#pmTargetMid').val($tr.data('mid'));
                $('#pmTargetOne').val($tr.data('one'));
                $('#pmDue').val($tr.data('due'));
                $('#pmWeight').val($tr.data('weight'));

                setTimeout(() => $('#pmActivity').trigger('focus'), 150);
                pointModal.show();
            });

            // ===== Simpan Point =====
            $('#pointForm').on('submit', function(e) {
                e.preventDefault();
                const $submitBtn = $('#pmSubmitBtn'); // optional if you have a submit btn id inside modal
                $submitBtn?.prop?.('disabled', true);

                const mode = $('#pmMode').val();
                const cat = $('#pmCat').val();

                const data = {
                    activity: ($('#pmActivity').val() || '').toString().trim(),
                    target_mid: ($('#pmTargetMid').val() || '').toString().trim(),
                    target_one: ($('#pmTargetOne').val() || '').toString().trim(),
                    due_date: $('#pmDue').val(),
                    weight: parseFloat($('#pmWeight').val())
                };

                if (!data.activity) {
                    toast('Isi "Program / Activity".', 'danger');
                    $submitBtn?.prop?.('disabled', false);
                    return;
                }
                if (!data.due_date) {
                    toast('Pilih "Due Date".', 'danger');
                    $submitBtn?.prop?.('disabled', false);
                    return;
                }
                if (isNaN(data.weight)) {
                    toast('Isi "Weight (%)" dengan angka.', 'danger');
                    $submitBtn?.prop?.('disabled', false);
                    return;
                }

                const before = sumWeights(cat);
                const delta = (mode === 'edit') ? computeEditDelta(cat, $('#pmRowId').val(), data.weight) : data
                    .weight;
                if (before + delta > CAT_CAP[cat]) {
                    if (!confirm(`Bobot kategori akan melebihi ${CAT_CAP[cat]}%.\nLanjutkan?`)) {
                        $submitBtn?.prop?.('disabled', false);
                        return;
                    }
                }

                const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);

                if (mode === 'create') {
                    const rowId = 'row-' + (++autoRowId);
                    const html = makeRowHtml(rowId, data);
                    removeEmptyState($tbody);
                    const $row = $(html).addClass('adding');
                    $tbody.append($row);
                    requestAnimationFrame(() => $row.addClass('show').removeClass('adding'));
                } else {
                    const rowId = $('#pmRowId').val();
                    const $tr = $(`table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`);
                    $tr.attr('data-activity', data.activity)
                        .attr('data-mid', data.target_mid)
                        .attr('data-one', data.target_one)
                        .attr('data-due', data.due_date)
                        .attr('data-weight', (isNaN(data.weight) ? 0 : data.weight));
                    $tr.replaceWith(makeRowHtml(rowId, data));
                }

                recalcAll();
                pointModal.hide();
                toast('Point tersimpan.');
                $submitBtn?.prop?.('disabled', false);
            });

            function computeEditDelta(cat, rowId, newW) {
                const $tr = $(`table.js-table[data-cat="${cat}"] tbody tr[data-row-id="${rowId}"]`);
                const oldW = parseFloat($tr.data('weight')) || 0;
                return (newW - oldW);
            }

            // ===== Hapus =====
            $(document).on('click', '.js-remove', function() {
                const $tr = $(this).closest('tr');
                const cat = $(this).closest('table').data('cat');
                $tr.addClass('removing');
                setTimeout(() => {
                    const $tbody = $(`table.js-table[data-cat="${cat}"] tbody.js-tbody`);
                    $tr.remove();
                    ensureNotEmpty($tbody);
                    recalcAll();
                    toast('Point dihapus.', 'warning');
                }, 180);
            });

            // Init
            $(document).ready(function() {
                // Pastikan setiap tbody punya empty state di awal
                $('table.js-table tbody.js-tbody').each(function() {
                    ensureNotEmpty($(this));
                });
                Object.keys(CAT_CAP).forEach(cat => updateCategoryCard(cat));
                updateTotal();
            });

            // Payload + Submit/Draft
            function collectPayload(status = 'submitted') {
                const identitas = {
                    nama: @json($identitas['nama'] ?? ''),
                    department: @json($identitas['department'] ?? ''),
                    division: @json($identitas['division'] ?? ''),
                    section: @json($identitas['section'] ?? ''),
                    date_review: @json($identitas['date_review'] ?? ''),
                    pic_review: @json($identitas['pic_review'] ?? ''),
                    on_year: @json($identitas['on_year'] ?? date('Y')),
                    no_form: @json($identitas['no_form'] ?? ''),
                };

                const programs = {};
                Object.keys(CAT_CAP).forEach(cat => {
                    programs[cat] = [];
                    $(`table.js-table[data-cat="${cat}"] tbody tr`).each(function() {
                        const $tr = $(this);
                        if ($tr.hasClass('empty-row')) return; // skip empty state
                        programs[cat].push({
                            activity: $tr.data('activity') || '',
                            target_mid: $tr.data('mid') || '',
                            target_one: $tr.data('one') || '',
                            due_date: $tr.data('due') || '',
                            weight: parseFloat($tr.data('weight')) || 0,
                        });
                    });
                });

                const summary = {};
                Object.keys(CAT_CAP).forEach(cat => summary[cat] = sumWeights(cat));
                summary.total = Object.values(summary).reduce((a, b) => a + b, 0);
                return {
                    identitas,
                    programs,
                    summary,
                    status
                };
            }

            function validatePayload(payload) {
                for (const cat in CAT_CAP) {
                    if (payload.summary[cat] > CAT_CAP[cat]) return {
                        ok: false,
                        msg: `Bobot ${cat.replace('_',' ')} melebihi ${CAT_CAP[cat]}%.`
                    };
                }
                if (payload.status === 'submitted' && Math.round(payload.summary.total) !== 100) {
                    return {
                        ok: false,
                        msg: 'Total bobot harus tepat 100% sebelum submit.'
                    };
                }
                const hasAny = Object.values(payload.programs).some(list => list.length > 0);
                if (!hasAny) return {
                    ok: false,
                    msg: 'Tambahkan minimal satu point aktivitas.'
                };
                return {
                    ok: true
                };
            }

            function ajaxStore(payload) {
                return $.ajax({
                    url: "{{ route('ipp.store') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        payload: JSON.stringify(payload)
                    },
                    dataType: "json"
                });
            }

            function withBtnLock($btn, fn) {
                $btn.prop('disabled', true);
                const done = () => $btn.prop('disabled', false);
                Promise.resolve().then(fn).finally(done);
            }

            $('#btnSubmit').on('click', function() {
                const $btn = $(this);
                withBtnLock($btn, () => {
                    const payload = collectPayload('submitted');
                    const valid = validatePayload(payload);
                    if (!valid.ok) {
                        toast(valid.msg, 'danger');
                        return;
                    }
                    return ajaxStore(payload)
                        .done(res => toast(res.message || 'Berhasil menyimpan IPP.'))
                        .fail(err => toast(err?.responseJSON?.message || 'Gagal menyimpan.', 'danger'));
                });
            });

            $('#btnDraft').on('click', function() {
                const $btn = $(this);
                withBtnLock($btn, () => {
                    const payload = collectPayload('draft');
                    const valid = validatePayload(payload);
                    if (!valid.ok && valid.msg.includes('point')) {
                        toast(valid.msg, 'danger');
                        return;
                    }
                    return ajaxStore(payload)
                        .done(res => toast(res.message || 'Draft tersimpan.'))
                        .fail(() => toast('Gagal simpan draft.', 'danger'));
                });
            });

            // Toast helper
            function toast(msg, type = 'success') {
                const id = 'toast-' + Date.now();
                const $t = $(`
      <div class="toast align-items-center text-bg-${type} border-0" id="${id}" role="status" aria-live="polite" aria-atomic="true" style="position:fixed;top:1rem;right:1rem;z-index:1080;">
        <div class="d-flex">
          <div class="toast-body">${msg}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
        </div>
      </div>
    `);
                $('body').append($t);
                const t = new bootstrap.Toast($t[0], {
                    delay: 2500
                });
                t.show();
                $t.on('hidden.bs.toast', () => $t.remove());
            }

            function pickUsedBadgeClass(used, cap) {
                const eps = 1e-6;
                if (Math.abs(used) <= eps) return 'badge-secondary';
                if (used > cap + eps) return 'badge-danger';
                if (Math.abs(used - cap) <= eps) return 'badge-primary';
                return 'badge-warning';
            }

        })(jQuery);
    </script>
@endpush
