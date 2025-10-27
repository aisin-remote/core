@extends('layouts.root.main')

@section('title', $title ?? 'Activity Plan')
@section('breadcrumbs', $title ?? 'Activity Plan')

@push('custom-css')
    <style>
        :root {
            --ap-border: #e5e7eb;
            --ap-head: #f8fafc;
            --ap-alt: #fbfdff;
            --ap-hover: #eef2ff
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

        .container-xxl {
            max-width: 1360px
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-3 px-6" id="apApp" data-ipp-id="{{ $ippId ?? '' }}" data-point-id="{{ $pointId ?? '' }}">

        {{-- Header --}}
        <div class="page-head mb-3">
            <h3 class="page-title">
                <i class="bi bi-list-check text-primary"></i>
                <span>Activity Plan</span>
            </h3>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('ipp.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Kembali ke IPP
                </a>
                <button id="btnAddItem" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Item
                </button>
                <a id="btnExportExcel" href="#" class="btn btn-success d-none"
                    data-href-template="{{ route('activity-plan.export.excel') }}?ipp_id=__IPP__">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
                </a>
            </div>
        </div>

        {{-- Identitas --}}
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

        {{-- Tabel items (selalu untuk 1 IPP Point ini saja) --}}
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

    {{-- Modal Tambah/Edit Item (lihat catatan field di atas) --}}
    @include('website.activity_plan.modal.create')
@endsection

@push('scripts')
    <script>
        (function($) {
            const MONTHS = ['APR', 'MAY', 'JUN', 'JUL', 'AGT', 'SEPT', 'OCT', 'NOV', 'DEC', 'JAN', 'FEB', 'MAR'];
            const MONTHS_ID = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

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

            function parseYmd(s) {
                const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) return null;
                return new Date(+m[1], +m[2] - 1, +m[3]);
            }

            function labelDate(s) {
                const d = parseYmd(s);
                if (!d) return '—';
                const dd = String(d.getDate()).padStart(2, '0');
                return `${dd} ${MONTHS_ID[d.getMonth()]||''} ${d.getFullYear()}`;
            }

            function fmtRange(s, e) {
                return `${esc(labelDate(s))} → ${esc(labelDate(e))}`;
            }

            function fiscalYearOf(d) {
                const dt = parseYmd(d);
                if (!dt) return null;
                return (dt.getMonth() + 1) >= 4 ? dt.getFullYear() : (dt.getFullYear() - 1);
            }

            function fyWindow(fy) {
                return {
                    start: new Date(fy, 3, 1), // 1 Apr fy
                    end: new Date(fy + 1, 2, 31) // 31 Mar fy+1
                };
            }

            function overlap(a1, a2, b1, b2) {
                return a1 <= b2 && b1 <= a2;
            }

            // Autogenerate schedule checkbox (hidden UI)
            function applyScheduleFromItemDates(startStr, dueStr, fyStartYear) {
                MONTHS.forEach(m => $('#m' + m).prop('checked', false));

                const s = parseYmd(startStr),
                    d = parseYmd(dueStr);
                if (!s || !d || s > d) return;

                const {
                    start: FY_S,
                    end: FY_E
                } = fyWindow(fyStartYear);

                for (let i = 0; i < 12; i++) {
                    const monthIdx = (3 + i) % 12;
                    const year = fyStartYear + ((3 + i) >= 12 ? 1 : 0);

                    const mS = new Date(year, monthIdx, 1);
                    const mE = new Date(year, monthIdx + 1, 0);

                    const segS = mS < FY_S ? FY_S : mS;
                    const segE = mE > FY_E ? FY_E : mE;

                    if (overlap(segS, segE, s, d)) {
                        $('#m' + MONTHS[i]).prop('checked', true);
                    }
                }
            }

            function schedText(mask) {
                if (!mask) return '-';
                const out = [];
                for (let i = 0; i < 12; i++) {
                    if (mask & (1 << i)) out.push(MONTHS[i]);
                }
                return out.join(', ');
            }

            /* ===== State ===== */
            const $root = $('#apApp');
            const IPP_ID = $root.data('ipp-id') || new URLSearchParams(location.search).get('ipp_id');
            const POINT_ID = $root.data('point-id') ||
                new URLSearchParams(location.search).get('point_id') ||
                (location.pathname.match(/\/activity-plan\/point\/(\d+)/)?.[1] ?? '');

            let BOOT = {
                ipp: null,
                plan: null,
                point: null,
                items: [],
                employees: []
            };
            let LOCKED = false;

            /* ===== Rendering table & header ===== */
            function rowHtml(it) {
                const picName = (it.pic && it.pic.name) ?
                    it.pic.name :
                    (it.pic_name || '-');

                const start = it.cached_start_date || it.ipp_point?.start_date || null;
                const due = it.cached_due_date || it.ipp_point?.due_date || null;
                const cat = it.cached_category || it.ipp_point?.category || '-';
                const act = it.cached_activity || it.ipp_point?.activity || '-';

                return `
                <tr data-id="${esc(it.id||'')}">
                    <td><span class="badge badge-mono">${esc(cat)}</span></td>
                    <td class="fw-semibold">${esc(act)}</td>
                    <td><span class="badge bg-light text-dark">${fmtRange(start,due)}</span></td>
                    <td>${esc(it.kind_of_activity||'-')}</td>
                    <td class="text-muted">${esc(it.target||'-')}</td>
                    <td>${esc(picName)}</td>
                    <td class="sched-badge"><small>${esc(schedText(Number(it.schedule_mask)||0))}</small></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-warning js-edit" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-danger js-del" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }

            function renderAll() {
                const $tb = $('#itemsBody').empty();
                const items = BOOT.items || [];
                if (!items.length) {
                    $tb.html(
                        '<tr class="empty-row"><td colspan="8" class="text-muted fst-italic">Belum ada item. Klik “Tambah Item”.</td></tr>'
                    );
                } else {
                    items.forEach(it => $tb.append(rowHtml(it)));
                }

                // header info employee/form/fy/status
                const empName = BOOT.ipp?.nama || (BOOT.plan?.employee?.name || '—');
                $('#apEmpName').text(empName);

                $('#apOrg').text(
                    [BOOT.plan?.division, BOOT.plan?.department, BOOT.plan?.section]
                    .filter(Boolean)
                    .join(' / ') || '—'
                );

                $('#apFormNo').text(BOOT.plan?.form_no || '—');

                const fy = BOOT.plan?.fy_start_year;
                $('#apFy').text(fy ? `Apr ${fy} – Mar ${Number(fy)+1}` : '—');

                const st = (BOOT.plan?.status || 'draft').toLowerCase();
                $('#apStatus')
                    .removeClass('bg-secondary bg-warning bg-success')
                    .addClass(
                        st === 'submitted' ? 'bg-warning' :
                        (st === 'approved' ? 'bg-success' :
                            'bg-secondary')
                    )
                    .text(st.toUpperCase());

                // export visibility
                const $exp = $('#btnExportExcel');
                if (IPP_ID) {
                    $exp.attr(
                        'href',
                        ($exp.data('href-template') || '')
                        .replace('__IPP__', encodeURIComponent(IPP_ID))
                    ).removeClass('d-none');
                } else {
                    $exp.attr('href', '#').addClass('d-none');
                }

                // lock if submitted/approved
                LOCKED = ['submitted', 'approved'].includes(st);
                $('#btnAddItem').prop('disabled', LOCKED);
                if (LOCKED) {
                    $('.js-edit,.js-del').prop('disabled', true);
                }
            }

            /* ===== AJAX routes ===== */
            const INIT_TPL = @json(route('activity-plan.init.byPoint', ['point' => '__POINT__']));
            const STORE_TPL = @json(route('activity-plan.item.store.byPoint', ['point' => '__POINT__']));

            function routeInit() {
                const url = new URL(
                    INIT_TPL.replace('__POINT__', encodeURIComponent(String(POINT_ID || ''))),
                    window.location.origin
                );
                if (IPP_ID) url.searchParams.set('ipp_id', IPP_ID);
                return url.toString();
            }

            function routeStore() {
                const url = new URL(
                    STORE_TPL.replace('__POINT__', encodeURIComponent(String(POINT_ID || ''))),
                    window.location.origin
                );
                if (IPP_ID) url.searchParams.set('ipp_id', IPP_ID);
                return url.toString();
            }

            /* ===== INIT data dari server ===== */
            async function init() {
                try {
                    if (!IPP_ID || !POINT_ID) {
                        toast('Parameter ipp_id/point_id tidak ditemukan.', 'danger');
                        return;
                    }

                    const res = await fetch(routeInit(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json?.message || 'Gagal memuat data.');

                    BOOT.ipp = json.ipp || null;
                    BOOT.plan = json.plan || null;
                    BOOT.point = json.point || null;
                    BOOT.items = json.items || [];
                    BOOT.employees = json.employees || [];

                    // hidden apPoint dropdown, still used for value
                    const $selPoint = $('#apPoint').empty();
                    if (BOOT.point) {
                        $selPoint
                            .append(
                                `<option value="${String(BOOT.point.id)}">
                                    [${esc(BOOT.point.category)}] ${esc(BOOT.point.activity)}
                                 </option>`
                            )
                            .val(String(BOOT.point.id))
                            .prop('disabled', true);
                    } else {
                        $selPoint
                            .append('<option value="">IPP Point tidak ditemukan</option>')
                            .val('')
                            .prop('disabled', true);
                    }

                    // PIC options
                    const $selPic = $('#apPic')
                        .empty()
                        .append('<option value="">PILIH PIC</option>');
                    BOOT.employees.forEach(e => {
                        const label = `${e.name}${e.npk ? (' — '+e.npk) : ''}`;
                        $selPic.append(
                            `<option value="${String(e.id)}">${esc(label)}</option>`
                        );
                    });

                    renderAll();
                } catch (e) {
                    toast(esc(e?.message || 'Gagal memuat data.'), 'danger');
                }
            }

            /* ===== Modal helper ===== */

            const apModal = new bootstrap.Modal(document.getElementById('apItemModal'), {
                backdrop: 'static',
                keyboard: false
            });

            // isi panel info point di modal
            function fillPointInfoInModal() {
                const p = BOOT.point;
                const $act = $('#apPointActivity');
                const $cat = $('#apPointCategory');
                const $rng = $('#apPointRange');

                if (!p) {
                    $act.text('—');
                    $cat.text('—');
                    $rng.text('—');
                    return;
                }

                const start = p.cached_start_date || p.start_date || '';
                const due = p.cached_due_date || p.due_date || '';
                const rangeLabel = fmtRange(start, due);

                $act.text(p.activity || '—');
                $cat.text(p.category || '—');
                $rng.text(rangeLabel || '—');
            }

            // set min/max date fields dari point + clamp value
            function applyPointDateLimits() {
                const p = BOOT.point;
                if (!p) return;

                const min = (p.cached_start_date || p.start_date || '').slice(0, 10);
                const max = (p.cached_due_date || p.due_date || '').slice(0, 10);

                const $start = $('#apStart');
                const $due = $('#apDue');

                $start.attr({
                    min,
                    max
                });
                $due.attr({
                    min,
                    max
                });

                function clamp($inp) {
                    const v = $inp.val();
                    if (!v) return;
                    if (min && v < min) $inp.val(min);
                    if (max && v > max) $inp.val(max);
                }
                clamp($start);
                clamp($due);

                return {
                    min,
                    max
                };
            }

            function resetForm() {
                $('#apForm')[0].reset();
                $('#apPic').val('').trigger('change');
                $('#apMode').val('create');
                $('#apRowId').val('');
                $('#apItemLabel').text('Tambah Activity Plan Item');

                // hidden schedule checkboxes reset
                MONTHS.forEach(m => $('#m' + m).prop('checked', false).prop('disabled', true));
                $('#apYearly').prop('checked', false).prop('disabled', true);

                // clear date attrs
                $('#apStart').attr({
                    min: '',
                    max: '',
                    value: ''
                });
                $('#apDue').attr({
                    min: '',
                    max: '',
                    value: ''
                });

                // clear info panel
                $('#apPointActivity').text('—');
                $('#apPointCategory').text('—');
                $('#apPointRange').text('—');
            }

            // open modal (create)
            $('#btnAddItem').on('click', function() {
                if (LOCKED) return;
                resetForm();

                $('#apMode').val('create');
                $('#apRowId').val('');
                $('#apItemLabel').text('Tambah Activity Plan Item');

                // apPoint hidden -> isi value
                const p = BOOT.point;
                if (p) {
                    $('#apPoint')
                        .val(String(p.id))
                        .prop('disabled', true);
                }

                // info panel
                fillPointInfoInModal();

                // limit datepicker & prefill
                const lim = applyPointDateLimits();
                if (lim && lim.min && !$('#apStart').val()) $('#apStart').val(lim.min);
                if (lim && lim.max && !$('#apDue').val()) $('#apDue').val(lim.max);

                apModal.show();
            });

            // open modal (edit)
            $(document).on('click', '.js-edit', function() {
                if (LOCKED) return;

                const id = $(this).closest('tr').data('id');
                const it = BOOT.items.find(x => String(x.id) === String(id));
                if (!it) return;

                resetForm();
                $('#apMode').val('edit');
                $('#apRowId').val(it.id);
                $('#apItemLabel').text('Edit Activity Plan Item');

                // apPoint hidden -> isi value
                const p = BOOT.point;
                if (p) {
                    $('#apPoint')
                        .val(String(p.id))
                        .prop('disabled', true);
                }

                // isi panel info point
                fillPointInfoInModal();

                // batas tanggal sesuai point
                applyPointDateLimits();

                // isi field item
                $('#apKind').val(it.kind_of_activity || '');
                $('#apTarget').val(it.target || '');
                $('#apPic').val(it.pic_employee_id || '').trigger('change');

                if (it.cached_start_date) {
                    $('#apStart').val(String(it.cached_start_date).slice(0, 10));
                }
                if (it.cached_due_date) {
                    $('#apDue').val(String(it.cached_due_date).slice(0, 10));
                }

                // clamp ulang karena kita baru set value
                applyPointDateLimits();

                // auto-generate schedule (hidden) biar months tetep masuk payload
                const startVal = $('#apStart').val();
                const dueVal = $('#apDue').val();
                const itemFy = fiscalYearOf(startVal) ||
                    BOOT.plan?.fy_start_year ||
                    BOOT.ipp?.on_year;
                if (startVal && dueVal && itemFy) {
                    applyScheduleFromItemDates(startVal, dueVal, itemFy);
                }

                apModal.show();
            });

            // ketika user ubah tanggal start/due di modal
            function onItemDatesChange() {
                const p = BOOT.point;
                if (!p) return;

                const min = (p.cached_start_date || p.start_date || '').slice(0, 10);
                const max = (p.cached_due_date || p.due_date || '').slice(0, 10);

                const s = $('#apStart').val();
                const d = $('#apDue').val();
                if (!s || !d) return;

                // kalau invalid, kosongkan schedule hidden
                if (s < min || s > max || d < min || d > max || s > d) {
                    MONTHS.forEach(m => $('#m' + m).prop('checked', false));
                    return;
                }

                const fy = fiscalYearOf(s) ||
                    BOOT.plan?.fy_start_year ||
                    BOOT.ipp?.on_year;
                if (!fy) return;

                applyScheduleFromItemDates(s, d, fy);
            }

            $('#apStart,#apDue').on('change', onItemDatesChange);

            /* ===== Delete item ===== */
            $(document).on('click', '.js-del', async function() {
                if (LOCKED) return;
                if (!confirm('Hapus item ini?')) return;

                const id = $(this).closest('tr').data('id');
                try {
                    const res = await fetch(
                        "{{ route('activity-plan.item.destroy', ':id') }}".replace(':id',
                            encodeURIComponent(id)), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new URLSearchParams({
                                _method: 'DELETE'
                            })
                        }
                    );
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json?.message || 'Gagal menghapus item.');

                    BOOT.items = BOOT.items.filter(x => String(x.id) !== String(id));
                    renderAll();
                    toast('Item dihapus.');
                } catch (err) {
                    toast(esc(err?.message || 'Gagal menghapus item.'), 'danger');
                }
            });

            /* ===== Submit (create/edit) ===== */
            $('#apForm').on('submit', async function(e) {
                e.preventDefault();
                if (LOCKED) return;

                const monthsSelected = MONTHS.filter(m => $('#m' + m).is(':checked'));

                const payload = {
                    mode: $('#apMode').val(),
                    row_id: $('#apRowId').val() || null,
                    ipp_point_id: $('#apPoint').val(), // hidden
                    kind_of_activity: ($('#apKind').val() || '').trim(),
                    target: ($('#apTarget').val() || '').trim(),
                    pic_employee_id: $('#apPic').val(),
                    start_date: $('#apStart').val(),
                    due_date: $('#apDue').val(),
                    months: monthsSelected
                };

                // FE validation
                if (!payload.ipp_point_id)
                    return toast('IPP Point tidak valid.', 'danger');
                if (!payload.kind_of_activity)
                    return toast('Isi Kind of Activity.', 'danger');
                if (!payload.pic_employee_id)
                    return toast('Pilih PIC.', 'danger');
                if (!payload.start_date)
                    return toast('Pilih Start Date item.', 'danger');
                if (!payload.due_date)
                    return toast('Pilih Due Date item.', 'danger');
                if (payload.start_date > payload.due_date)
                    return toast('Start Date item tidak boleh setelah Due Date item.', 'danger');

                // cek batas point
                const p = BOOT.point;
                if (p) {
                    const min = (p.cached_start_date || p.start_date || '').slice(0, 10);
                    const max = (p.cached_due_date || p.due_date || '').slice(0, 10);

                    if (payload.start_date < min || payload.start_date > max ||
                        payload.due_date < min || payload.due_date > max) {
                        return toast('Tanggal item harus dalam Start–Due IPP Point.', 'danger');
                    }
                }

                // Kita tidak wajib monthsSelected.length > 0 karena hidden dari user.

                try {
                    const res = await fetch(routeStore(), {
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
                        if (idx > -1) {
                            BOOT.items[idx] = json.item;
                        } else {
                            BOOT.items.push(json.item);
                        }
                    }

                    apModal.hide();
                    renderAll();
                    toast(json?.message || 'Draft tersimpan.');
                } catch (err) {
                    toast(esc(err?.message || 'Gagal menyimpan item.'), 'danger');
                }
            });

            /* ===== Toast helper ===== */
            function toast(msg, type = 'success') {
                const id = 't' + Date.now();
                const $t = $(`
                    <div class="toast align-items-center text-bg-${type} border-0" id="${id}"
                        role="status" aria-live="polite" aria-atomic="true"
                        style="position:fixed;top:1rem;right:1rem;z-index:1080;">
                        <div class="d-flex">
                            <div class="toast-body">${esc(msg)}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast" aria-label="Tutup"></button>
                        </div>
                    </div>`);
                $('body').append($t);
                new bootstrap.Toast($t[0], {
                    delay: 2200
                }).show();
                $t.on('hidden.bs.toast', () => $t.remove());
            }

            /* ===== Ready ===== */
            $(document).ready(function() {
                if (!IPP_ID || !POINT_ID) {
                    toast('Buka halaman ini dari IPP (parameter kurang).', 'danger');
                    setTimeout(() => {
                        window.location.href = "{{ route('ipp.index') }}";
                    }, 1200);
                    return;
                }

                init();

                if ($.fn.select2) {
                    // apPoint hidden, jadi gak perlu select2
                    $('#apPic').select2({
                        dropdownParent: $('#apItemModal')
                    });
                }
            });

        })(jQuery);
    </script>
@endpush
