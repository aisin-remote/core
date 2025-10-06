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
    <style>
        .container-xxl {
            max-width: 1360px
        }

        .card-header.bg-light {
            background: #f8fafc !important
        }

        .small-muted {
            color: #6b7280;
            font-size: .9rem
        }

        .help-line {
            color: #64748b;
            font-size: .85rem
        }

        .badge-tag {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            padding: .15rem .5rem;
            font-weight: 600;
            font-size: .75rem
        }

        .badge-src {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: .05rem .4rem;
            font-weight: 600;
            font-size: .7rem
        }

        .badge-cat {
            background: #eef2ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
            border-radius: 9999px;
            padding: .15rem .55rem;
            font-weight: 700;
            font-size: .72rem
        }

        .table.ipp-table {
            border-color: #e5e7eb;
            font-size: 1rem
        }

        .table.ipp-table thead th {
            background: #f0f5ff !important;
            color: #111827;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb
        }

        .table.ipp-table tbody tr:nth-child(even) {
            background: #fbfdff
        }

        .table.ipp-table tbody tr:hover {
            background: #eef2ff
        }

        .table.ipp-table td,
        .table.ipp-table th {
            padding: .85rem 1rem;
            vertical-align: middle
        }

        th.sticky {
            position: sticky;
            top: 0;
            z-index: 1
        }

        .empty-row td {
            padding: 1rem !important;
            color: #6c757d;
            font-style: italic
        }

        .btn-mini {
            padding: .5rem .7rem;
            border-radius: 10px
        }

        .btn-add {
            background: #2563eb;
            color: #fff;
            border: 1px solid #2563eb
        }

        .btn-edit {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e5e7eb
        }

        .btn-del {
            background: #fef2f2;
            color: #7f1d1d;
            border: 1px solid #fecaca
        }

        .calc-cell {
            font-variant-numeric: tabular-nums
        }

        .mini-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 8px;
            overflow: hidden
        }

        .mini-bar>span {
            display: block;
            height: 100%;
            width: 0%;
            background: #2563eb;
            transition: width .25s ease
        }

        /* Number inputs compact & accessible */
        .num-in {
            max-width: 110px
        }

        .num-in input {
            height: 40px;
            font-size: 1rem;
            text-align: right
        }

        .num-in input:focus {
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15)
        }

        /* Accordion (mirip IPP) */
        .accordion.ipa .accordion-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            margin-bottom: .875rem;
            box-shadow: 0 1px 0 rgba(17, 24, 39, .02)
        }

        .accordion.ipa .accordion-button {
            background: #fafafa;
            color: #111827;
            font-weight: 700
        }

        .accordion.ipa .accordion-button:not(.collapsed) {
            background: #f5f7fb;
            box-shadow: inset 0 -1px 0 #e5e7eb
        }

        .accordion.ipa .accordion-button:focus {
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .2)
        }

        /* Modals */
        .modal .form-label {
            font-weight: 600
        }

        @media (max-width:576px) {
            .modal .modal-dialog {
                max-width: calc(100% - 1rem);
                margin: .5rem auto
            }
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl py-3 px-6" id="ipa-edit" data-ipa-id="{{ $ipa->id }}"
        data-url-data="{{ route('ipa.data', $ipa->id) }}" data-url-update="{{ route('ipa.update', $ipa->id) }}"
        data-url-recalc="{{ route('ipa.recalc', $ipa->id) }}">

        {{-- Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div>
                <h3 class="mb-1">Individual Performance Achievement</h3>
                <div class="small-muted">
                    Employee: <strong>{{ optional($ipa->employee)->name }}</strong>
                    <span class="mx-2">•</span>
                    Tahun: <strong id="hdr-year">{{ $ipa->on_year }}</strong>
                    <span class="mx-2">•</span>
                    IPP: <span class="badge-tag">#{{ $ipa->ipp_id }}</span>
                    <span class="mx-2">•</span>
                    IPA: <span class="badge-tag">#{{ $ipa->id }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-light btn-sm" id="btn-recalc" title="Recalc dari server">Recalc</button>
                <button class="btn btn-primary btn-sm" id="btn-save">Simpan</button>
            </div>
        </div>

        {{-- Notes --}}
        <div class="card mb-3">
            <div class="card-body">
                <label class="form-label fw-bold">Catatan</label>
                <textarea class="form-control" id="fld-notes" rows="2" placeholder="Catatan umum (opsional)...">{{ $ipa->notes }}</textarea>
            </div>
        </div>

        {{-- ====== ACCORDION PER KATEGORI ====== --}}
        <div class="accordion ipa" id="accordionIPA">
            @foreach ($categories as $cat)
                <div class="accordion-item" data-cat="{{ $cat['key'] }}">
                    <h2 class="accordion-header" id="head-{{ $cat['key'] }}">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#col-{{ $cat['key'] }}" aria-expanded="true"
                            aria-controls="col-{{ $cat['key'] }}">
                            <div class="d-flex flex-column w-100">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span>{{ $cat['title'] }}</span>
                                    <span class="small-muted">Cap <span
                                            class="badge badge-cat">{{ $cat['cap'] }}%</span></span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="col-{{ $cat['key'] }}" class="accordion-collapse collapse show">
                        <div class="accordion-body">

                            {{-- A. Activities (Custom) --}}
                            <div class="card mb-3">
                                <div
                                    class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">Activities (Custom) — <span
                                            class="badge-cat">{{ $cat['title'] }}</span></div>
                                    <button class="btn btn-sm btn-add btn-mini js-add-activity"
                                        data-cat="{{ $cat['key'] }}">+ Tambah Activity</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle ipp-table mb-0 js-tbl-acts"
                                        data-cat="{{ $cat['key'] }}">
                                        <thead>
                                            <tr>
                                                <th class="sticky" style="width:280px">Program/Activity</th>
                                                <th class="sticky">One Year Target</th>
                                                <th class="sticky" style="width:140px">Weight (W, %)</th>
                                                <th class="sticky" style="width:120px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="js-tbody-acts">
                                            <tr class="empty-row">
                                                <td colspan="4">Belum ada activities.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-3 py-2 help-line">Tip: Activity yang kamu tambah akan muncul sebagai baris
                                    <b>Custom</b> di tabel Achievements kategori ini. W (%) bawaan dari sini, tetapi tetap
                                    bisa diubah di Achievements.
                                </div>
                            </div>

                            {{-- B. Achievements --}}
                            <div class="card mb-2">
                                <div class="card-header bg-light border-0">
                                    <div class="fw-bold">One Year Achievements — <span
                                            class="badge-cat">{{ $cat['title'] }}</span></div>
                                    <div class="help-line mt-1">Isi <b>Achievement</b>, ubah <b>Weight (W, %)</b> dan
                                        <b>Score (R)</b> jika perlu. Total dihitung otomatis: (W/100) × R.
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle ipp-table mb-0 js-tbl-achs"
                                        data-cat="{{ $cat['key'] }}">
                                        <thead>
                                            <tr>
                                                <th class="sticky" style="width:340px">Program/Activity <span
                                                        class="small-muted">(sumber)</span></th>
                                                <th class="sticky">One Year Target</th>
                                                <th class="sticky" style="width:150px">Weight (W, %)</th>
                                                <th class="sticky">One Year Achievement</th>
                                                <th class="sticky" style="width:150px">Score (R)</th>
                                                <th class="sticky" style="width:160px">Total (W × R ÷ 100)</th>
                                                <th class="sticky" style="width:120px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="js-tbody-achs">
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

        {{-- Totals --}}
        <div class="mt-3 d-flex flex-wrap gap-3 justify-content-end">
            <div class="card shadow-sm" style="min-width:360px">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>Achievement Total (Σ(W/100×R))</div>
                        <div><strong id="total-achievement">0,00</strong></div>
                    </div>
                    <div class="mini-bar mb-2"><span id="bar-ach"></span></div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>Grand Score (Σ R)</div>
                        <div><strong id="total-grand-score">0,00</strong></div>
                    </div>
                    <div class="mini-bar mb-2"><span id="bar-gscore"></span></div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>Grand Total</div>
                        <div><strong id="total-grand">0,00</strong></div>
                    </div>
                    <div class="mini-bar"><span id="bar-grand"></span></div>
                </div>
            </div>
            <div class="d-flex align-items-end">
                <button class="btn btn-primary" id="btn-save-bottom">Simpan Perubahan</button>
            </div>
        </div>
    </div>

    {{-- ===== Modals ===== --}}

    {{-- Activity Modal (Custom) --}}
    <div class="modal fade" id="modal-activity" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdlActTitle">Tambah Activity</h5>
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="act-id">
                    <input type="hidden" id="act-cat">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program/Activity <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="act-program"
                                placeholder="Nama program / aktivitas">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control" id="act-category-name" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">One Year Target <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="act-target" rows="3" placeholder="Target tahunan..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Weight (W, %)</label>
                            <input type="number" step="0.01" class="form-control" id="act-weight" value="0">
                            <div class="help-line">Contoh: 5, 7.5, 10</div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="help-line">Nilai W ini juga menjadi nilai awal di tabel Achievements (baris
                                Custom),
                                dan <b>tetap bisa diubah</b> di sana.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <span class="small-muted" id="act-hint">Tambah activity baru</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="act-save">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fill/Edit Achievement Modal (teks & evidence) --}}
    <div class="modal fade" id="modal-fill" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="mdlFillTitle">Isi / Edit Achievement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="fill-ach-id">
                    <input type="hidden" id="fill-ipp-point-id">
                    <input type="hidden" id="fill-source">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program/Activity</label>
                            <input type="text" class="form-control" id="fill-program" readonly>
                            <div class="small-muted" id="fill-subtitle"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Weight (W, %)</label>
                            <input type="text" class="form-control" id="fill-weight" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">One Year Target</label>
                            <textarea class="form-control" id="fill-target" rows="2" readonly></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">One Year Achievement <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="fill-achv" rows="3" placeholder="Capaian selama setahun..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Score (R)</label>
                            <input type="number" step="0.01" class="form-control" id="fill-score" value="0">
                            <div class="help-line">Kamu juga bisa ubah R langsung di tabel.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notes / Evidence (opsional)</label>
                            <input type="text" class="form-control" id="fill-evidence" placeholder="Link / catatan">
                        </div>
                        <div class="col-12">
                            <div class="small-muted">Total dihitung: <strong>(W/100) × R</strong>.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <span class="small-muted" id="fill-hint"></span>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btn-save-fill">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const $root = $('#ipa-edit');
            const URL_DATA = $root.data('url-data');
            const URL_UPDATE = $root.data('url-update');
            const URL_RECALC = $root.data('url-recalc');

            const $notes = $('#fld-notes');
            const $tAch = $('#total-achievement');
            const $tGnd = $('#total-grand');
            const $tGScore = $('#total-grand-score');
            const $barAch = $('#bar-ach');
            const $barG = $('#bar-grand');
            const $barGS = $('#bar-gscore');

            let mdlAct = new bootstrap.Modal(document.getElementById('modal-activity'));
            let mdlFill = new bootstrap.Modal(document.getElementById('modal-fill'));

            // cache data
            let IPP_POINTS = []; // {id, activity/title, target_one, weight, category}
            let ACTIVITIES = []; // ipa_activities (custom)
            let ACHS = []; // ipa_achievements existing

            // utils
            const esc = (s) => String(s || '').replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;'
            } [m]));
            const toFixed2 = (n) => Number(n || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            function nloc(v) {
                if (typeof v === 'number') return v;
                if (v == null) return 0;
                let s = String(v).trim();
                if (!s) return 0;
                if (s.includes('.') && s.includes(',')) s = s.replace(/\./g, '').replace(',', '.');
                else if (s.includes(',')) s = s.replace(',', '.');
                s = s.replace(/[^\d.-]/g, '');
                const num = parseFloat(s);
                return isNaN(num) ? 0 : num;
            }

            function $tbodyActs(cat) {
                return $(`.js-tbl-acts[data-cat="${cat}"] .js-tbody-acts`);
            }

            function $tbodyAchs(cat) {
                return $(`.js-tbl-achs[data-cat="${cat}"] .js-tbody-achs`);
            }

            // ===== Activities renderer =====
            function rowActHtml(a) {
                return `<tr data-type="activity" ${a.id?`data-id="${a.id}"`:''}>
            <td><div class="fw-semibold">${esc(a.description || a.title || a.activity || '-')}</div></td>
            <td>${esc(a.target_one || a.one_year_target || '')}</td>
            <td class="num-in"><input type="number" step="0.01" class="form-control js-act-weight" value="${esc(a.weight ?? 0)}" aria-label="Weight persen"></td>
            <td class="text-end">
                <button class="btn btn-sm btn-edit btn-mini js-edit-act">Edit</button>
                <button class="btn btn-sm btn-del  btn-mini js-del-act ms-1">Hapus</button>
            </td>
        </tr>`;
            }

            function renderActivitiesByCat(cat) {
                const rows = ACTIVITIES.filter(a => (a.category || '') === cat);
                const $tb = $tbodyActs(cat);
                if (!rows.length) {
                    $tb.html('<tr class="empty-row"><td colspan="4">Belum ada activities.</td></tr>');
                    return;
                }
                $tb.html(rows.map(rowActHtml).join(''));
            }

            // ===== Achievements renderer (IPP + Custom) =====
            function rowAchHtmlFromIpp(p, ex, cat) {
                const W = nloc(ex?.weight ?? p.weight ?? 0);
                const R = nloc(ex?.self_score ?? 0);
                const calc = (W / 100) * R;
                return `<tr data-type="achievement" data-source="ipp"
                    data-ipp-point-id="${p.id}"
                    ${ex?.id?`data-id="${ex.id}"`:''}
                    ${ex?.evidence?`data-evidence="${esc(ex.evidence)}"`:''}>
            <td><div class="fw-semibold">${esc(p.activity||p.title||'(tanpa judul)')}</div>
                <div class="small-muted"><span class="badge-src">IPP Point</span> • Kategori: ${esc(cat)}</div>
            </td>
            <td>${esc(p.target_one||'')}</td>
            <td class="num-in"><input type="number" step="0.01" class="form-control js-w" value="${esc(W)}" aria-label="Weight persen"></td>
            <td class="js-achv">${esc(ex?.one_year_achievement||ex?.description||'')}</td>
            <td class="num-in"><input type="number" step="0.01" class="form-control js-r" value="${esc(R)}" aria-label="Score"></td>
            <td class="calc-cell text-end js-calc"><strong>${toFixed2(calc)}</strong></td>
            <td class="text-end">
                <button class="btn btn-sm btn-edit btn-mini js-fill-ach">Isi/Edit</button>
            </td>
        </tr>`;
            }

            function rowAchHtmlFromCustom(a, ex, cat) {
                const W = nloc(ex?.weight ?? a.weight ?? 0);
                const R = nloc(ex?.self_score ?? 0);
                const calc = (W / 100) * R;
                return `<tr data-type="achievement" data-source="custom"
                    ${a.id?`data-custom-activity-id="${a.id}"`:''}
                    ${ex?.id?`data-id="${ex.id}"`:''}
                    ${ex?.evidence?`data-evidence="${esc(ex.evidence)}"`:''}>
            <td><div class="fw-semibold">${esc(a.description || a.title || a.activity || '')}</div>
                <div class="small-muted"><span class="badge-src">Custom</span> • Kategori: ${esc(cat)}</div>
            </td>
            <td>${esc(a.target_one || a.one_year_target || '')}</td>
            <td class="num-in"><input type="number" step="0.01" class="form-control js-w" value="${esc(W)}" aria-label="Weight persen"></td>
            <td class="js-achv">${esc(ex?.one_year_achievement||'')}</td>
            <td class="num-in"><input type="number" step="0.01" class="form-control js-r" value="${esc(R)}" aria-label="Score"></td>
            <td class="calc-cell text-end js-calc"><strong>${toFixed2(calc)}</strong></td>
            <td class="text-end">
                <button class="btn btn-sm btn-edit btn-mini js-fill-ach">Isi/Edit</button>
            </td>
        </tr>`;
            }

            function renderAchievementsByCat(cat) {
                const $tb = $tbodyAchs(cat);
                const ippRows = IPP_POINTS.filter(p => (p.category || '') === cat);
                const customRows = ACTIVITIES.filter(a => (a.category || '') === cat);

                const mapByIpp = {};
                const mapByCustom = {};
                ACHS.forEach(x => {
                    if (x.ipp_point_id) mapByIpp[Number(x.ipp_point_id)] = x;
                    else if (x.custom_activity_id) mapByCustom[Number(x.custom_activity_id)] = x;
                    else if (!x.ipp_point_id && x.title) {
                        mapByCustom['title:' + x.title] = x;
                    }
                });

                const ippHtml = ippRows.map(p => rowAchHtmlFromIpp(p, mapByIpp[Number(p.id)] || null, cat)).join('');
                const custHtml = customRows.map(a => {
                    const ex = (a.id && mapByCustom[Number(a.id)]) || mapByCustom['title:' + (a.description ||
                        '')] || null;
                    return rowAchHtmlFromCustom(a, ex, cat);
                }).join('');
                const html = (ippHtml + custHtml);
                $tb.html(html || '<tr class="empty-row"><td colspan="7">Tidak ada baris.</td></tr>');
            }

            function renderAll() {
                $('[data-cat]').each(function() {
                    const cat = $(this).data('cat');
                    renderActivitiesByCat(cat);
                    renderAchievementsByCat(cat);
                });
                recalcTotals();
            }

            // ===== Totals =====
            function recalcTotals() {
                let total = 0,
                    sumR = 0;
                $('.js-tbl-achs .js-tbody-achs tr[data-type="achievement"]').each(function() {
                    const W = nloc($(this).find('.js-w').val());
                    const R = nloc($(this).find('.js-r').val());
                    total += (W / 100) * R;
                    sumR += R;
                    $(this).find('.js-calc').html(`<strong>${toFixed2((W/100)*R)}</strong>`);
                });
                $tAch.text(toFixed2(total));
                $tGnd.text(toFixed2(total));
                $tGScore.text(toFixed2(sumR));

                const scale = v => Math.max(0, Math.min(100, (v / 10) * 100));
                $('#bar-ach').css('width', scale(total) + '%');
                $('#bar-grand').css('width', scale(total) + '%');
                $('#bar-gscore').css('width', Math.max(0, Math.min(100, (sumR / 10) * 100)) + '%');
            }

            // ===== Init Load =====
            function initLoad() {
                $('.js-tbody-acts').html('<tr class="empty-row"><td colspan="4">Memuat...</td></tr>');
                $('.js-tbody-achs').html('<tr class="empty-row"><td colspan="7">Memuat...</td></tr>');

                $.getJSON(URL_DATA).done(function(res) {
                    if (!res || !res.ok) {
                        $('.js-tbody-acts').html(
                            '<tr class="empty-row"><td colspan="4">Gagal memuat.</td></tr>');
                        $('.js-tbody-achs').html(
                            '<tr class="empty-row"><td colspan="7">Gagal memuat.</td></tr>');
                        return;
                    }
                    const d = res.data || {};
                    $('#hdr-year').text(d.header?.on_year || $('#hdr-year').text());
                    $notes.val(d.header?.notes || '');

                    IPP_POINTS = Array.isArray(d.ipp_points) ? d.ipp_points.map(p => ({
                        id: p.id,
                        activity: p.activity || p.title,
                        title: p.title,
                        target_one: p.target_one,
                        weight: p.weight,
                        category: p.category
                    })) : [];

                    ACTIVITIES = Array.isArray(d.activities) ? d.activities.map(a => ({
                        id: a.id || null,
                        description: a.description || a.title || a.activity,
                        target_one: a.target_one || a.one_year_target || '',
                        weight: nloc(a.weight ?? 0), // ambil kalau ada
                        category: a.category || '',
                    })) : [];

                    ACHS = Array.isArray(d.achievements) ? d.achievements.map(x => ({
                        id: x.id,
                        ipp_point_id: x.ipp_point_id || null,
                        custom_activity_id: x.custom_activity_id || null,
                        title: x.title,
                        one_year_target: x.one_year_target || '',
                        one_year_achievement: x.one_year_achievement || x.description || '',
                        weight: nloc(x.weight || 0),
                        self_score: nloc(x.self_score || 0),
                        evidence: x.evidence || ''
                    })) : [];

                    renderAll();
                }).fail(function() {
                    $('.js-tbody-acts').html('<tr class="empty-row"><td colspan="4">Error server.</td></tr>');
                    $('.js-tbody-achs').html('<tr class="empty-row"><td colspan="7">Error server.</td></tr>');
                });
            }

            // ===== Activities CRUD (modal) =====
            let EDIT_ACT_REF = null;
            $(document).on('click', '.js-add-activity', function() {
                const cat = $(this).data('cat');
                $('#act-id').val('');
                $('#act-cat').val(cat);
                $('#act-category-name').val($(`[data-cat="${cat}"] .accordion-button span:first`).text()
            .trim());
                $('#act-program').val('');
                $('#act-target').val('');
                $('#act-weight').val('0');
                $('#mdlActTitle').text('Tambah Activity');
                $('#act-hint').text('Tambah activity baru');
                EDIT_ACT_REF = null;
                mdlAct.show();
            });

            $(document).on('click', '.js-edit-act', function() {
                const $tr = $(this).closest('tr');
                const $card = $tr.closest('[data-cat]');
                const cat = $card.data('cat');
                const program = $tr.find('td').eq(0).text().trim();
                const target = $tr.find('td').eq(1).text().trim();
                const w = $tr.find('.js-act-weight').val();

                $('#act-id').val($tr.data('id') || '');
                $('#act-cat').val(cat);
                $('#act-category-name').val($(`[data-cat="${cat}"] .accordion-button span:first`).text()
            .trim());
                $('#act-program').val(program);
                $('#act-target').val(target);
                $('#act-weight').val(nloc(w));
                $('#mdlActTitle').text('Edit Activity');
                $('#act-hint').text($tr.data('id') ? `Editing activity #${$tr.data('id')}` :
                    'Editing (belum tersimpan)');
                EDIT_ACT_REF = $tr;
                mdlAct.show();
            });

            $('#act-save').on('click', function() {
                const id = $('#act-id').val() || null;
                const cat = $('#act-cat').val();
                const program = ($('#act-program').val() || '').trim();
                const target = ($('#act-target').val() || '').trim();
                const weight = nloc($('#act-weight').val());
                if (!program) {
                    alert('Program/Activity wajib diisi.');
                    return;
                }
                if (!cat) {
                    alert('Kategori tidak diketahui.');
                    return;
                }

                // update cache
                if (EDIT_ACT_REF) {
                    const rowId = EDIT_ACT_REF.data('id') || null;
                    if (rowId) {
                        const idx = ACTIVITIES.findIndex(a => a.id === rowId);
                        if (idx >= 0) {
                            ACTIVITIES[idx].description = program;
                            ACTIVITIES[idx].target_one = target;
                            ACTIVITIES[idx].weight = weight;
                        }
                    } else {
                        const name = EDIT_ACT_REF.find('td').eq(0).text().trim();
                        const idx = ACTIVITIES.findIndex(a => !a.id && a.category === cat && (a.description ||
                            '') === name);
                        if (idx >= 0) {
                            ACTIVITIES[idx].description = program;
                            ACTIVITIES[idx].target_one = target;
                            ACTIVITIES[idx].weight = weight;
                        }
                    }
                } else {
                    ACTIVITIES.push({
                        id: null,
                        category: cat,
                        description: program,
                        target_one: target,
                        weight: weight
                    });
                }

                renderActivitiesByCat(cat);
                renderAchievementsByCat(cat);
                recalcTotals();
                mdlAct.hide();
                EDIT_ACT_REF = null;
            });

            $(document).on('click', '.js-del-act', function() {
                const $tr = $(this).closest('tr');
                const $card = $tr.closest('[data-cat]');
                const cat = $card.data('cat');
                const rowId = $tr.data('id') || null;
                const name = $tr.find('td').eq(0).text().trim();

                if (rowId) {
                    ACTIVITIES = ACTIVITIES.filter(a => a.id !== rowId);
                    ACHS = ACHS.filter(x => x.custom_activity_id !== rowId);
                } else {
                    ACTIVITIES = ACTIVITIES.filter(a => !(!a.id && a.category === cat && (a.description ||
                        '') === name));
                    ACHS = ACHS.filter(x => !(!x.ipp_point_id && x.title === name));
                }
                renderActivitiesByCat(cat);
                renderAchievementsByCat(cat);
                recalcTotals();
            });

            // ===== Achievements inline W/R change =====
            $(document).on('input', '.js-tbl-achs .js-w, .js-tbl-achs .js-r', function() {
                recalcTotals();
            });

            // ===== Fill Achievement Modal (teks & evidence) =====
            let EDIT_ACH_REF = null;
            $(document).on('click', '.js-fill-ach', function() {
                const $tr = $(this).closest('tr');
                EDIT_ACH_REF = $tr;
                const t = $tr.children();
                const source = $tr.data('source') || 'ipp';
                const program = t.eq(0).find('.fw-semibold').text().trim() || t.eq(0).text().trim();
                const target = t.eq(1).text().trim();
                const W = nloc($tr.find('.js-w').val());
                const achv = t.eq(3).text().trim();
                const R = nloc($tr.find('.js-r').val());
                const ev = ($tr.data('evidence') || '').toString();

                $('#fill-source').val(source);
                $('#fill-ach-id').val($tr.data('id') || '');
                $('#fill-ipp-point-id').val($tr.data('ipp-point-id') || '');
                $('#fill-program').val(program);
                $('#fill-target').val(target);
                $('#fill-weight').val(toFixed2(W));
                $('#fill-achv').val(achv);
                $('#fill-score').val(R);
                $('#fill-evidence').val(ev);
                $('#fill-subtitle').html(source === 'ipp' ? '<span class="badge-src">IPP Point</span>' :
                    '<span class="badge-src">Custom</span>');
                $('#fill-hint').text($tr.data('id') ? `Editing achievement #${$tr.data('id')}` : (source ===
                    'ipp' ? 'Baris IPP' : 'Baris Custom'));
                mdlFill.show();
            });

            $('#btn-save-fill').on('click', function() {
                if (!EDIT_ACH_REF || !EDIT_ACH_REF.length) return;
                const achId = $('#fill-ach-id').val() || null;
                const achv = ($('#fill-achv').val() || '').trim();
                const R = nloc($('#fill-score').val());
                const ev = ($('#fill-evidence').val() || '').trim();
                if (achId) EDIT_ACH_REF.attr('data-id', achId);
                if (ev) EDIT_ACH_REF.attr('data-evidence', ev);
                else EDIT_ACH_REF.removeAttr('data-evidence');
                EDIT_ACH_REF.find('.js-achv').text(achv);
                EDIT_ACH_REF.find('.js-r').val(R);
                recalcTotals();
                mdlFill.hide();
                EDIT_ACH_REF = null;
            });

            // ===== Save (PUT) =====
            function collectPayload() {
                // activities (custom)
                const activities = [];
                $('.js-tbl-acts .js-tbody-acts tr[data-type="activity"]').each(function() {
                    const $tr = $(this),
                        t = $tr.children();
                    const cat = $tr.closest('[data-cat]').data('cat');
                    const program = t.eq(0).text().trim();
                    const target = t.eq(1).text().trim();
                    const w = nloc($tr.find('.js-act-weight').val());
                    activities.push({
                        id: $tr.data('id') || null,
                        category: cat,
                        description: program,
                        one_year_target: target,
                        weight: w, // disimpan agar saat reload tetap muncul
                        self_score: 0,
                        evidence: null,
                        source: 'custom'
                    });
                });

                // achievements
                const achievements = [];
                $('.js-tbl-achs .js-tbody-achs tr[data-type="achievement"]').each(function() {
                    const $tr = $(this),
                        t = $tr.children();
                    const source = $tr.data('source') || 'ipp';
                    const program = t.eq(0).find('.fw-semibold').text().trim() || t.eq(0).text().trim();
                    const target = t.eq(1).text().trim();
                    const W = nloc($tr.find('.js-w').val());
                    const achv = t.eq(3).text().trim();
                    const R = nloc($tr.find('.js-r').val());
                    const ev = ($tr.data('evidence') || '').toString();

                    if (achv || R > 0 || ($tr.data('id') || null)) {
                        const row = {
                            id: $tr.data('id') || null,
                            ipp_point_id: source === 'ipp' ? ($tr.data('ipp-point-id') || null) : null,
                            title: program,
                            one_year_target: target,
                            one_year_achievement: achv,
                            weight: W,
                            self_score: R,
                            evidence: ev
                        };
                        if (source === 'custom' && $tr.data('custom-activity-id')) {
                            row.custom_activity_id = $tr.data('custom-activity-id');
                        }
                        achievements.push(row);
                    }
                });

                return {
                    notes: ($notes.val() || '').trim(),
                    activities,
                    achievements
                };
            }

            function saveAll() {
                const payload = collectPayload();
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
                        initLoad();
                    } else {
                        alert(res?.message || 'Gagal menyimpan.');
                    }
                }).fail(function(xhr) {
                    alert(xhr.responseJSON?.message || 'Error server.');
                });
            }

            $('#btn-save, #btn-save-bottom').on('click', saveAll);

            $('#btn-recalc').on('click', function() {
                $.post({
                        url: URL_RECALC,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        dataType: 'json'
                    })
                    .done(function(res) {
                        if (res && res.ok && res.totals) {
                            $tAch.text(toFixed2(res.totals.achievement_total));
                            $tGnd.text(toFixed2(res.totals.achievement_total));
                            $tGScore.text(toFixed2(res.totals.grand_score || 0));
                        }
                    });
            });

            // buka semua accordion
            $('#accordionIPA .accordion-collapse').each(function() {
                this.removeAttribute('data-bs-parent');
                this.classList.add('show');
                const btn = $(this).prev('.accordion-header').find('.accordion-button');
                btn.removeClass('collapsed').attr('aria-expanded', 'true');
            });

            initLoad();
        })();
    </script>
@endpush
