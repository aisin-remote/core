@extends('layouts.root.main')

@section('title', $title ?? 'Activity Plan')
@section('breadcrumbs', $title ?? 'Activity Plan')

@push('custom-css')
    <style>
        :root {
            --ap-border: #e5e7eb;
            --ap-head: #f8fafc;
            --ap-alt: #fbfdff;
            --ap-hover: #eef2ff;
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
            box-shadow: 0 6px 20px rgba(24, 39, 75, .06)
        }

        .page-title {
            margin: 0;
            font-weight: 800;
            letter-spacing: .2px;
            display: flex;
            align-items: center;
            gap: .6rem
        }

        .table.ap-table thead th {
            background: var(--ap-head) !important;
            border-bottom: 1px solid var(--ap-border);
            white-space: nowrap
        }

        .table.ap-table tbody tr:nth-child(even) {
            background: var(--ap-alt)
        }

        .table.ap-table tbody tr:hover {
            background: var(--ap-hover)
        }

        .badge-mono {
            background: #f1f3f5;
            color: #475569
        }

        .sched-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace
        }

        .req::after {
            content: "*";
            color: #dc3545;
            margin-left: .25rem
        }

        .container-xxl {
            max-width: 1360px
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-3 px-6">

        {{-- Header --}}
        <div class="page-head mb-3">
            <h3 class="page-title">
                <i class="bi bi-list-check text-primary"></i>
                <span>Activity Plan</span>
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('ipp.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Kembali ke IPP
                </a>
                <button id="btnAddItem" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Item
                </button>
                <button id="btnSubmitAll" class="btn btn-warning">
                    <i class="bi bi-send-check"></i> Submit IPP + Activity Plan
                </button>
                <a id="btnExportExcel" href="#" class="btn btn-success d-none"
                    data-href-template="{{ route('activity-plan.export.excel') }}?ipp_id=__IPP__">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
                </a>
            </div>
        </div>

        {{-- Identitas / Info singkat --}}
        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap gap-3 align-items-center">
                <div><span class="text-muted">Nama/NPK:</span> <strong id="apEmpName">—</strong></div>
                <div><span class="text-muted">Div/Dept/Sec:</span> <strong id="apOrg">—</strong></div>
                <div><span class="text-muted">No Form:</span> <strong id="apFormNo">—</strong></div>
                <div><span class="text-muted">FY Start:</span> <strong id="apFy">—</strong></div>
                <div class="ms-auto">
                    <span class="text-muted me-1">Status:</span>
                    <span id="apStatus" class="badge bg-secondary">—</span>
                </div>
            </div>
        </div>

        {{-- Tabel items --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle ap-table mb-0" id="tblItems">
                        <thead>
                            <tr>
                                <th style="width:15%">Category</th>
                                <th style="width:28%">Activity (from IPP)</th>
                                <th style="width:15%">Start → Due</th>
                                <th style="width:18%">Kind of Activity</th>
                                <th style="width:16%">Target</th>
                                <th style="width:12%">PIC</th>
                                <th style="width:10%">Schedule</th>
                                <th style="width:6%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr class="empty-row">
                                <td colspan="8" class="text-muted fst-italic">Belum ada item. Klik “Tambah Item”.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Tambah/Edit Item --}}
    @include('website.activity_plan.modal.create')

@endsection

@push('scripts')
    <script>
        (function($) {
            // === Konstanta & util ===
            const MONTHS = ['APR', 'MAY', 'JUN', 'JUL', 'AGT', 'SEPT', 'OCT', 'NOV', 'DEC', 'JAN', 'FEB', 'MAR'];
            const MONTHS_ID = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

            const QS = new URLSearchParams(window.location.search);
            const IPP_ID = QS.get('ipp_id');

            let BOOT = {
                ipp: null,
                plan: null,
                points: [],
                items: [],
                employees: []
            };
            let LOCKED = false;

            // Sanitasi text
            function esc(s) {
                return String(s ?? '').replace(/[&<>"'`=\/]/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                    '=': '&#x3D;'
                } [c]));
            }

            // Parser ISO (YYYY-MM-DD) aman
            function parseISODate(str) {
                if (!str || typeof str !== 'string') return null;
                const m = str.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) return null;
                const y = Number(m[1]),
                    mo = Number(m[2]) - 1,
                    d = Number(m[3]);
                const dt = new Date(y, mo, d);
                // validasi: JS Date auto-roll; pastikan sama
                if (dt.getFullYear() !== y || dt.getMonth() !== mo || dt.getDate() !== d) return null;
                return dt;
            }

            // Format ke "DD Mon YYYY" (ID)
            function formatDateID(str) {
                const dt = parseISODate(str);
                if (!dt) return '-';
                const dd = String(dt.getDate()).padStart(2, '0');
                const mon = MONTHS_ID[dt.getMonth()] || '';
                const yyyy = dt.getFullYear();
                return `${dd} ${mon} ${yyyy}`;
            }

            function fmtDateRange(s, e) {
                return `${esc(formatDateID(s))} → ${esc(formatDateID(e))}`;
            }

            function schedToText(mask) {
                if (!mask || Number(mask) === 0) return '-';
                const arr = [];
                for (let i = 0; i < 12; i++)
                    if (mask & (1 << i)) arr.push(MONTHS[i]);
                return arr.join(', ');
            }

            function rowHtml(it) {
                const pic = (it.pic && it.pic.name) ? it.pic.name : (it.pic_name || '-');
                const start = it.cached_start_date || it.ipp_point?.start_date || null;
                const due = it.cached_due_date || it.ipp_point?.due_date || null;
                const cat = it.cached_category || it.ipp_point?.category || '-';
                const act = it.cached_activity || it.ipp_point?.activity || '-';
                return `
      <tr data-id="${esc(it.id||'')}" data-ipp-point-id="${esc(it.ipp_point_id)}">
        <td><span class="badge badge-mono">${esc(cat)}</span></td>
        <td class="fw-semibold">${esc(act)}</td>
        <td><span class="badge bg-light text-dark">${fmtDateRange(start, due)}</span></td>
        <td>${esc(it.kind_of_activity||'-')}</td>
        <td class="text-muted">${esc(it.target||'-')}</td>
        <td>${esc(pic)}</td>
        <td class="sched-badge"><small>${esc(schedToText(Number(it.schedule_mask)||0))}</small></td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button class="btn btn-warning js-edit" title="Edit"><i class="bi bi-pencil-square"></i></button>
            <button class="btn btn-danger js-del" title="Hapus"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      </tr>`;
            }

            function renderAll() {
                const $tb = $('#itemsBody').empty();
                if (!BOOT.items.length) {
                    $tb.html(
                        '<tr class="empty-row"><td colspan="8" class="text-muted fst-italic">Belum ada item. Klik “Tambah Item”.</td></tr>'
                    );
                } else {
                    BOOT.items.forEach(it => $tb.append(rowHtml(it)));
                }

                // Header info
                const empName = BOOT.ipp?.nama || (BOOT.plan?.employee?.name || '—');
                $('#apEmpName').text(empName);
                $('#apOrg').text([BOOT.plan?.division, BOOT.plan?.department, BOOT.plan?.section].filter(Boolean).join(
                    ' / ') || '—');
                $('#apFormNo').text(BOOT.plan?.form_no || '—');

                const fy = BOOT.plan?.fy_start_year;
                $('#apFy').text(fy ? `${fy}/04–${(Number(fy)+1)}/03` : '—');

                const st = (BOOT.plan?.status || 'draft').toLowerCase();
                $('#apStatus')
                    .removeClass('bg-secondary bg-warning bg-success')
                    .addClass(st === 'submitted' ? 'bg-warning' : (st === 'approved' ? 'bg-success' : 'bg-secondary'))
                    .text(st.toUpperCase());

                // export link
                const $exp = $('#btnExportExcel');
                if (IPP_ID) $exp.attr('href', ($exp.data('href-template') || '').replace('__IPP__', encodeURIComponent(
                    IPP_ID)));
                else $exp.attr('href', '#');

                // locking
                LOCKED = ['submitted', 'approved'].includes(st);
                $('#btnAddItem,#btnSubmitAll').prop('disabled', LOCKED);
                if (LOCKED) $('.js-edit,.js-del').prop('disabled', true);
            }

            function withIppId(url) {
                const u = new URL(url, window.location.origin);
                if (IPP_ID) u.searchParams.set('ipp_id', IPP_ID);
                return u.toString();
            }

            // ===== FY helpers & auto-schedule from dates =====
            function fyWindow(fyStartYear) {
                const start = new Date(fyStartYear, 3, 1); // Apr 1
                const end = new Date(fyStartYear + 1, 2, 31); // Mar 31
                return {
                    start,
                    end
                };
            }

            function rangeOverlap(a1, a2, b1, b2) {
                return a1 <= b2 && b1 <= a2;
            }

            function applyScheduleFromDates(startStr, dueStr, fyStartYear) {
                // kosongkan dulu
                MONTHS.forEach(m => $('#m' + m).prop('checked', false));
                if (!startStr || !dueStr || !fyStartYear) return;

                const s = parseISODate(startStr),
                    d = parseISODate(dueStr);
                if (!s || !d || s > d) return;

                const {
                    start: FY_S,
                    end: FY_E
                } = fyWindow(Number(fyStartYear));

                // loop month Apr..Mar
                for (let i = 0; i < 12; i++) {
                    const monthIdx = (3 + i) % 12; // 3=Apr
                    const year = Number(fyStartYear) + ((3 + i) >= 12 ? 1 : 0);
                    const monthStart = new Date(year, monthIdx, 1);
                    const monthEnd = new Date(year, monthIdx + 1, 0);
                    const segStart = monthStart < FY_S ? FY_S : monthStart;
                    const segEnd = monthEnd > FY_E ? FY_E : monthEnd;
                    if (rangeOverlap(segStart, segEnd, s, d)) $('#m' + MONTHS[i]).prop('checked', true);
                }
            }

            // ==== INIT (ambil data awal) ====
            async function init() {
                try {
                    if (!IPP_ID) {
                        toast('ipp_id tidak ditemukan di URL. Buka halaman ini dari IPP.', 'danger');
                        return;
                    }

                    const res = await fetch(withIppId("{{ route('activity-plan.init') }}"), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json?.message || 'Gagal memuat data.');

                    BOOT.ipp = json.ipp || null;
                    BOOT.plan = json.plan || null;
                    BOOT.points = json.points || [];
                    BOOT.items = json.items || [];
                    BOOT.employees = json.employees || [];

                    // seed select options (semua label di-esc)
                    const $selPoint = $('#apPoint').empty().append('<option value="">— pilih IPP Point —</option>');
                    BOOT.points.forEach(p => {
                        const label =
                            `[${p.category}] ${p.activity} — ${formatDateID(p.start_date)}→${formatDateID(p.due_date)}`;
                        $selPoint.append(
                            `<option value="${String(p.id).replace(/"/g,'&quot;')}">${esc(label)}</option>`
                        );
                    });

                    const $selPic = $('#apPic').empty().append('<option value="">— pilih PIC —</option>');
                    BOOT.employees.forEach(e => {
                        const val = String(e.id).replace(/"/g, '&quot;');
                        const label = `${e.name}${e.npk?(' — '+e.npk):''}`;
                        $selPic.append(`<option value="${val}">${esc(label)}</option>`);
                    });

                    renderAll();
                } catch (e) {
                    toast(esc(e?.message || 'Gagal memuat data Activity Plan.'), 'danger');
                }
            }

            // ==== Modal & Form ====
            const apModal = new bootstrap.Modal(document.getElementById('apItemModal'), {
                backdrop: 'static',
                keyboard: false
            });

            $('#btnAddItem').on('click', function() {
                if (LOCKED) return;
                resetForm();
                $('#apMode').val('create');
                $('#apRowId').val('');
                $('#apItemLabel').text('Tambah Activity Plan Item');
                apModal.show();
            });

            $(document).on('click', '.js-edit', function() {
                if (LOCKED) return;
                const $tr = $(this).closest('tr');
                const id = $tr.data('id');
                const it = BOOT.items.find(x => String(x.id) === String(id));
                if (!it) return;

                resetForm();
                $('#apMode').val('edit');
                $('#apRowId').val(it.id);
                $('#apPoint').val(it.ipp_point_id).trigger('change'); // schedule auto dari point
                $('#apKind').val(it.kind_of_activity || '');
                $('#apTarget').val(it.target || '');
                $('#apPic').val(it.pic_employee_id || '').trigger('change');

                $('#apItemLabel').text('Edit Activity Plan Item');
                apModal.show();
            });

            $(document).on('click', '.js-del', async function() {
                if (LOCKED) return;
                if (!confirm('Hapus item ini?')) return;
                const $tr = $(this).closest('tr');
                const id = $tr.data('id');
                try {
                    const res = await fetch("{{ route('activity-plan.item.destroy', ':id') }}".replace(
                        ':id', encodeURIComponent(id)), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            _method: 'DELETE'
                        })
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json?.message || 'Gagal menghapus item.');
                    BOOT.items = BOOT.items.filter(x => String(x.id) !== String(id));
                    renderAll();
                    toast('Item dihapus.');
                } catch (err) {
                    toast(esc(err?.message || 'Gagal menghapus item.'), 'danger');
                }
            });

            function resetForm() {
                $('#apForm')[0].reset();
                $('#apPoint,#apPic').val('').trigger('change');
                // Schedule read-only & kosong
                MONTHS.forEach(m => $('#m' + m).prop('checked', false).prop('disabled', true));
                $('#apYearly').prop('checked', false).prop('disabled', true);
            }

            // Yearly toggle (disabled, no-op)
            $('#apYearly').off('change').on('change', function() {
                /* read-only */
            });

            // Saat pilih IPP Point: auto-apply schedule dari Start–Due
            $('#apPoint').on('change', function() {
                const pid = $(this).val();
                const p = BOOT.points.find(x => String(x.id) === String(pid));

                // kunci tetap
                MONTHS.forEach(m => $('#m' + m).prop('disabled', true));
                $('#apYearly').prop('disabled', true);

                if (!p) {
                    MONTHS.forEach(m => $('#m' + m).prop('checked', false));
                    return;
                }

                const fy = BOOT.plan?.fy_start_year || BOOT.ipp?.on_year;
                applyScheduleFromDates(p.start_date, p.due_date, fy);
            });

            // Submit form (create/edit)
            $('#apForm').on('submit', async function(e) {
                e.preventDefault();
                if (LOCKED) return;

                const mode = $('#apMode').val();
                const rowId = $('#apRowId').val();
                const ipp_point_id = $('#apPoint').val();
                const kind_of_activity = ($('#apKind').val() || '').trim();
                const target = ($('#apTarget').val() || '').trim();
                const pic_employee_id = $('#apPic').val();

                if (!ipp_point_id) return toast('Pilih IPP Point.', 'danger');
                if (!kind_of_activity) return toast('Isi Kind of Activity.', 'danger');
                if (!pic_employee_id) return toast('Pilih PIC.', 'danger');

                // months dari auto-checked (tetap kebaca walau disabled)
                const months = MONTHS.filter(m => $('#m' + m).is(':checked'));

                const payload = {
                    mode,
                    row_id: rowId || null,
                    ipp_point_id,
                    kind_of_activity,
                    target,
                    pic_employee_id,
                    months
                };

                try {
                    const res = await fetch(withIppId("{{ route('activity-plan.item.store') }}"), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json?.message || 'Gagal menyimpan item.');

                    if (json.item) {
                        const idx = BOOT.items.findIndex(x => String(x.id) === String(json.item.id));
                        if (idx > -1) BOOT.items[idx] = json.item;
                        else BOOT.items.push(json.item);
                    }
                    apModal.hide();
                    renderAll();
                    toast(json?.message || 'Draft tersimpan.');
                } catch (err) {
                    toast(esc(err?.message || 'Gagal menyimpan item.'), 'danger');
                }
            });

            // Submit gabungan
            $('#btnSubmitAll').on('click', async function() {
                if (LOCKED) return;
                if (!confirm('Submit IPP + Activity Plan sekarang?')) return;
                const $btn = $(this).prop('disabled', true);
                try {
                    const res = await fetch(withIppId("{{ route('activity-plan.submitAll') }}"), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json?.message || 'Submit gagal.');
                    toast(json?.message || 'Berhasil submit.');
                    await init();
                } catch (err) {
                    toast(esc(err?.message || 'Submit gagal.'), 'danger');
                } finally {
                    $btn.prop('disabled', false);
                }
            });

            // tiny toast
            function toast(msg, type = 'success') {
                const id = 't' + Date.now();
                const $t = $(`
      <div class="toast align-items-center text-bg-${type} border-0" id="${id}" role="status" aria-live="polite" aria-atomic="true"
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

            // init page
            $(document).ready(function() {
                init();
                if ($.fn.select2) {
                    $('#apPoint').select2({
                        dropdownParent: $('#apItemModal')
                    });
                    $('#apPic').select2({
                        dropdownParent: $('#apItemModal')
                    });
                }
            });
        })(jQuery);
    </script>
@endpush
