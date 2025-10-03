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
    {{-- Toastr (toast) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

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

        .badge-cat {
            background: #eef2ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
            border-radius: 9999px;
            padding: .15rem .55rem;
            font-weight: 700;
            font-size: .72rem
        }

        .badge-src {
            background: #ecfeff;
            color: #075985;
            border: 1px solid #a5f3fc;
            border-radius: 8px;
            padding: .05rem .4rem;
            font-weight: 700;
            font-size: .7rem
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

        .btn-edit {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e5e7eb
        }

        .btn-add {
            background: #2563eb;
            color: #fff;
            border: 1px solid #2563eb
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
    <style>
        /* === CAP badge (status per kategori) === */
        .cap-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            padding: .15rem .6rem;
            font-weight: 700;
            font-size: .72rem;
            background: #fff;
            color: #374151;
        }

        .cap-badge .dot {
            width: .5rem;
            height: .5rem;
            border-radius: 9999px;
            display: inline-block;
        }

        .cap-warn {
            background: #fff7ed;
            color: #9a3412;
            border-color: #fed7aa;
        }

        /* kuning */
        .cap-warn .dot {
            background: #f59e0b;
        }

        .cap-ok {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        /* hijau */
        .cap-ok .dot {
            background: #10b981;
        }

        .cap-over {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        /* merah */
        .cap-over .dot {
            background: #ef4444;
        }
    </style>
    <style>
        /* Fallback warna toast bila text-bg-* belum ada */
        .badge-success {
            background-color: #22c55e;
            color: #fff;
        }

        .badge-danger {
            background-color: #ef4444;
            color: #fff;
        }

        .badge-warning {
            background-color: #f59e0b;
            color: #212529;
        }

        .badge-info {
            background-color: #0ea5e9;
            color: #212529;
        }

        .badge-dark {
            background-color: #212529;
            color: #fff;
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
                <h3 class="mb-1">Individual Performance Appraisal</h3>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-add btn-sm" id="btn-add-activity">+ Tambah Activity</button>
                <button class="btn btn-light btn-sm" id="btn-recalc" title="Recalc dari server">Recalc</button>
                <button class="btn btn-primary btn-sm" id="btn-save">Simpan</button>
            </div>
        </div>

        {{-- ====== ACCORDION PER KATEGORI (LIST IPP POINT + CUSTOM) ====== --}}
        <div class="accordion ipa" id="accordionIPA">
            @foreach ($categories as $cat)
                <div class="accordion-item" data-cat="{{ $cat['key'] }}" data-cap="{{ $cat['cap'] }}">
                    <!-- NEW: data-cap -->
                    <h2 class="accordion-header" id="head-{{ $cat['key'] }}">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#col-{{ $cat['key'] }}" aria-expanded="true"
                            aria-controls="col-{{ $cat['key'] }}">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <span>{{ $cat['title'] }}</span>
                                <!-- NEW: badge placeholder -->
                                <span class="cap-badge ms-2" data-cat="{{ $cat['key'] }}">0.00 /
                                    {{ $cat['cap'] }}</span>
                            </div>
                        </button>
                    </h2>

                    <div id="col-{{ $cat['key'] }}" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                            <div class="card">
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
                                                <th class="sticky" style="width:120px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="js-tbody-ipp">
                                            <tr class="empty-row">
                                                <td colspan="6">Memuat...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-3 py-2 help-line">
                                    Baris bertanda <span class="badge-src">Custom</span> adalah activity yang kamu tambahkan
                                    di IPA (tidak mengubah IPP).
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

    {{-- ===== Modal Detail (IPP & Custom; langsung editable) ===== --}}
    @include('website.ipa.modal.point')

    {{-- ===== Modal Tambah Activity (Custom) ===== --}}
    @include('website.ipa.modal.achievement')
@endsection

@push('scripts')
    {{-- jQuery + Toastr --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        (function() {
            const $root = $('#ipa-edit');
            const URL_DATA = $root.data('url-data');
            const URL_UPDATE = $root.data('url-update');
            const URL_RECALC = $root.data('url-recalc');

            // Toast (pakai Bootstrap Toast dengan kelas badge-*)
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

            const $tAch = $('#total-achievement');
            const $tGnd = $('#total-grand');
            const $tGScore = $('#total-grand-score');
            const $barAch = $('#bar-ach');
            const $barG = $('#bar-grand');
            const $barGS = $('#bar-gscore');

            // cache
            let IPP_POINTS = []; // {id, category, activity/title, target_one, weight}
            let
                ACHS = []; // {id, ipp_point_id|null, category?, title?, one_year_target?, weight, self_score, one_year_achievement}

            let mdlDetail = new bootstrap.Modal(document.getElementById('modal-ipp-detail'));
            let mdlAdd = new bootstrap.Modal(document.getElementById('modal-add-activity'));

            // utils
            const esc = (s) => String(s || '').replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                '\'': '&#39;'
            } [m]));
            const toFixed2 = (n) => Number(n || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            function fmt(n) {
                const num = Number(n || 0);
                return num.toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }

            // nloc: baca angka lokal; "10" dianggap 10 (== 10%), "10%" juga aman
            function nloc(v) {
                if (typeof v === 'number') return v;
                if (v == null) return 0;
                let s = String(v).trim();
                if (!s) return 0;
                s = s.replace('%', ''); // support input dengan tanda %
                if (s.includes('.') && s.includes(',')) s = s.replace(/\./g, '').replace(',', '.');
                else if (s.includes(',')) s = s.replace(',', '.');
                s = s.replace(/[^\d.-]/g, '');
                const num = parseFloat(s);
                return isNaN(num) ? 0 : num;
            }
            const $tbody = (cat) => $(`.js-tbl-ipp[data-cat="${cat}"] .js-tbody-ipp`);

            // ===== Helpers: Score & Weight resolusi =====
            function scoreForIpp(id) {
                const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(id));
                return nloc(ex?.self_score ?? 0);
            }

            function weightForIpp(id) {
                const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(id));
                if (ex && ex.weight != null) return nloc(ex.weight);
                const p = IPP_POINTS.find(pp => Number(pp.id) === Number(id));
                return nloc(p?.weight ?? 0);
            }

            function scoreForCustom(a) {
                return nloc(a.self_score ?? 0);
            }

            function weightForCustom(a) {
                return nloc(a.weight ?? 0);
            }

            function rowTotal(weightPercent, score) {
                // Total Score per baris = (W% * R) / 100
                return (nloc(weightPercent) / 100) * nloc(score);
            }

            // ====== RENDER LIST ======
            function rowIPPHtml(p) {
                const score = scoreForIpp(p.id);
                const weight = weightForIpp(p.id); // dibaca sebagai persen
                const total = rowTotal(weight, score);
                return `<tr data-source="ipp" data-ipp-id="${p.id}" data-cat="${esc(p.category||'')}">
                    <td>${esc(p.activity||'-')}</td>
                    <td><div class="fw-semibold">${esc(p.target_one||'(tanpa judul)')}</div></td>
                    <td>${fmt(weight)}%</td>
                    <td>${fmt(score)}</td>
                    <td>${fmt(total)}</td>
                    <td><button class="btn btn-sm btn-edit btn-mini js-row-detail">Detail</button></td>
                </tr>`;
            }

            function rowCustomHtml(a) {
                const key = a.__key || a.id || '';
                const score = scoreForCustom(a);
                const weight = weightForCustom(a); // persen
                const total = rowTotal(weight, score);
                return `<tr data-source="custom" data-ach-key="${esc(key)}" data-cat="${esc(a.category||'')}">
                    <td>${esc(a.title||'(tanpa judul)')} <span class="ms-1 badge-src">Custom</span></td>
                    <td><div class="fw-semibold">${esc(a.one_year_target||'')}</div></td>
                    <td>${fmt(weight)}%</td>
                    <td>${fmt(score)}</td>
                    <td>${fmt(total)}</td>
                    <td><button class="btn btn-sm btn-edit btn-mini js-row-detail">Detail</button></td>
                </tr>`;
            }

            // ====== CAP BADGE: perhitungan & update ======
            function totalWeightByCat(cat) {
                let sum = 0;

                // IPP: gunakan override weight dari ACHS jika ada
                IPP_POINTS.filter(p => (p.category || '') === cat).forEach(p => {
                    const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(p.id));
                    const W = nloc(ex?.weight ?? p.weight ?? 0); // persen
                    sum += W;
                });

                // Custom: bukan turunan IPP
                ACHS.filter(x => !x.ipp_point_id && (x.category || '') === cat).forEach(c => {
                    const W = nloc(c.weight ?? 0); // persen
                    sum += W;
                });

                return sum;
            }

            function updateCapBadges() {
                $('.accordion-item[data-cat][data-cap]').each(function() {
                    const cat = String($(this).data('cat') || '');
                    const cap = nloc($(this).data('cap')); // persen
                    const sum = totalWeightByCat(cat); // persen

                    const $badge = $('.cap-badge[data-cat="' + cat + '"]');
                    if ($badge.length === 0) return;

                    // reset kelas
                    $badge.removeClass('cap-warn cap-ok cap-over');

                    const EPS = 0.0001;
                    let cls = 'cap-warn'; // default: kuning (belum penuh)
                    if (sum > cap + EPS) cls = 'cap-over';
                    else if (Math.abs(sum - cap) <= EPS) cls = 'cap-ok';

                    $badge.addClass(cls);

                    // inject dot jika belum ada
                    if ($badge.find('.dot').length === 0) {
                        $badge.prepend('<span class="dot"></span>');
                    }

                    // tampilkan "xx.xx% / yy.yy%"
                    $badge.contents().filter(function() {
                        return this.nodeType === 3;
                    }).remove();
                    $badge.append(document.createTextNode(' ' + fmt(sum) + '% / ' + fmt(cap) + '%'));
                });
            }

            function renderByCat(cat) {
                const $tb = $tbody(cat);
                const ippRows = IPP_POINTS.filter(p => (p.category || '') === cat);
                const custRows = ACHS.filter(x => !x.ipp_point_id && (x.category || '') === cat);

                let html = '';
                if (ippRows.length) html += ippRows.map(rowIPPHtml).join('');
                if (custRows.length) html += custRows.map(rowCustomHtml).join('');
                if (!html) html = '<tr class="empty-row"><td colspan="6">Belum ada item.</td></tr>';

                $tb.html(html);

                // update badge untuk kategori ini
                updateCapBadges();
            }

            function renderAll() {
                $('[data-cat]').each(function() {
                    const cat = $(this).data('cat');
                    renderByCat(cat);
                });
                recalcTotals();
                updateCapBadges();
            }

            // ====== TOTALS (tetap: Σ(W/100×R)) ======
            function recalcTotals() {
                let total = 0,
                    sumR = 0;

                // IPP-based
                IPP_POINTS.forEach(p => {
                    const ex = ACHS.find(x => Number(x.ipp_point_id) === Number(p.id));
                    const W = nloc(ex?.weight ?? p.weight ?? 0); // persen
                    const R = nloc(ex?.self_score ?? 0); // angka
                    total += (W / 100) * R;
                    sumR += R;
                });

                // Custom-based
                ACHS.filter(x => !x.ipp_point_id).forEach(c => {
                    const W = nloc(c.weight ?? 0); // persen
                    const R = nloc(c.self_score ?? 0);
                    total += (W / 100) * R;
                    sumR += R;
                });

                $tAch.text(fmt(total));
                $tGnd.text(fmt(total));
                $tGScore.text(fmt(sumR));

                const scale = v => Math.max(0, Math.min(100, (v / 10) * 100));
                $barAch.css('width', scale(total) + '%');
                $barG.css('width', scale(total) + '%');
                $barGS.css('width', Math.max(0, Math.min(100, (sumR / 10) * 100)) + '%');

                updateCapBadges();
            }

            // ====== LOAD ======
            function initLoad() {
                $('.js-tbody-ipp').html('<tr class="empty-row"><td colspan="6">Memuat...</td></tr>');
                $.getJSON(URL_DATA).done(function(res) {
                    if (!res || !res.ok) {
                        toast('Gagal memuat data.', 'danger');
                        return;
                    }
                    const d = res.data || {};

                    IPP_POINTS = Array.isArray(d.ipp_points) ? d.ipp_points.map(p => ({
                        id: p.id,
                        activity: p.activity || p.title,
                        title: p.title,
                        target_one: p.target_one,
                        weight: nloc(p.weight || 0), // persen
                        category: p.category
                    })) : [];

                    ACHS = Array.isArray(d.achievements) ? d.achievements.map(x => ({
                        id: x.id || null,
                        ipp_point_id: x.ipp_point_id || null,
                        category: x.category || null,
                        title: x.title || null,
                        one_year_target: x.one_year_target || '',
                        one_year_achievement: x.one_year_achievement || x.description || '',
                        weight: nloc(x.weight ?? 0), // persen
                        self_score: nloc(x.self_score || 0), // angka
                    })) : [];

                    renderAll();
                }).fail(function() {
                    toast('Error server saat memuat.', 'danger');
                });
            }

            // ====== DETAIL (open) ======
            $(document).on('click', '.js-row-detail', function() {
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

                    $('#ippd-weight').val(fmt(nloc(ex?.weight ?? p.weight ??
                        0))); // persen (tanpa % di input)
                    $('#ippd-score').val(nloc(ex?.self_score || 0)); // angka biasa
                    $('#ippd-achv').val(ex?.one_year_achievement || '');
                    $('#ippd-hint').text(
                        'Mode IPP: edit Weight (%) , Score (angka), Achievement. IPP tidak berubah.');
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

                    $('#ippd-weight').val(fmt(nloc(a.weight || 0))); // persen
                    $('#ippd-score').val(nloc(a.self_score || 0)); // angka
                    $('#ippd-achv').val(a.one_year_achievement || '');
                    $('#ippd-hint').text(
                        'Mode Custom: edit Activity, Target, Weight (%) , Score (angka), Achievement.');
                }

                mdlDetail.show();
            });

            // ====== DETAIL (save -> PUT IPA) ======
            $('#ippd-btn-save').on('click', function() {
                const source = ($('#ippd-source').val() || '').toString();

                if (source === 'ipp') {
                    const id = Number($('#ippd-id').val());
                    const W = nloc($('#ippd-weight').val()); // persen (boleh ketik "10" atau "10%")
                    const R = nloc($('#ippd-score').val()); // angka
                    const target = ($('#ippd-target').val() || '').trim();
                    const ach = ($('#ippd-achv').val() || '').trim();

                    const payload = {
                        achievements: [{
                            id: (ACHS.find(x => Number(x.ipp_point_id) === id)?.id) || null,
                            ipp_point_id: id,
                            one_year_target: target,
                            weight: W,
                            self_score: R,
                            one_year_achievement: ach
                        }]
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
                            let ex = ACHS.find(x => Number(x.ipp_point_id) === id);
                            if (ex) {
                                ex.weight = W;
                                ex.self_score = R;
                                ex.one_year_achievement = ach;
                                ex.one_year_target = target;
                            } else {
                                ACHS.push({
                                    id: null,
                                    ipp_point_id: id,
                                    weight: W,
                                    self_score: R,
                                    one_year_achievement: ach,
                                    one_year_target: target
                                });
                            }
                            const p = IPP_POINTS.find(x => Number(x.id) === id);
                            if (p && p.category) renderByCat(p.category);

                            recalcTotals();
                            toast('Tersimpan ke IPA.');
                            mdlDetail.hide();
                        } else {
                            toast(res?.message || 'Gagal menyimpan.', 'warning');
                        }
                    }).fail(function(xhr) {
                        toast(xhr.responseJSON?.message || 'Error server.', 'danger');
                    });

                } else {
                    // custom
                    const key = ($('#ippd-ach-key').val() || '').toString();
                    const a = ACHS.find(x => (!x.ipp_point_id) && (x.__key === key || (x.id && String(x.id) ===
                        key)));
                    if (!a) {
                        toast('Custom activity tidak ditemukan.', 'danger');
                        return;
                    }

                    const title = ($('#ippd-activity').val() || '').trim();
                    const target = ($('#ippd-target').val() || '').trim();
                    const W = nloc($('#ippd-weight').val()); // persen
                    const R = nloc($('#ippd-score').val()); // angka
                    const ach = ($('#ippd-achv').val() || '').trim();

                    const payload = {
                        achievements: [{
                            id: a.id || null,
                            category: a.category,
                            title: title,
                            one_year_target: target,
                            weight: W,
                            self_score: R,
                            one_year_achievement: ach
                        }]
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
                            a.title = title;
                            a.one_year_target = target;
                            a.weight = W;
                            a.self_score = R;
                            a.one_year_achievement = ach;
                            renderByCat(a.category);
                            recalcTotals();
                            toast('Custom activity diperbarui.');
                            mdlDetail.hide();
                        } else {
                            toast(res?.message || 'Gagal menyimpan.', 'warning');
                        }
                    }).fail(function(xhr) {
                        toast(xhr.responseJSON?.message || 'Error server.', 'danger');
                    });
                }
            });

            // ====== ADD ACTIVITY (open)
            $('#btn-add-activity').on('click', function() {
                $('#add-cat').val('');
                $('#add-activity').val('');
                $('#add-target').val('');
                $('#add-weight').val('0'); // persen
                $('#add-score').val('0'); // angka
                $('#add-achv').val('');
                mdlAdd.show();
            });

            // ====== ADD ACTIVITY (save -> PUT IPA)
            $('#add-btn-save').on('click', function() {
                const cat = ($('#add-cat').val() || '').toString();
                const tit = ($('#add-activity').val() || '').trim();
                const tgt = ($('#add-target').val() || '').trim();
                const W = nloc($('#add-weight').val()); // persen (boleh isi dengan %)
                const R = nloc($('#add-score').val()); // angka
                const ach = ($('#add-achv').val() || '').trim();

                if (!cat) {
                    toast('Kategori wajib dipilih.', 'warning');
                    return;
                }
                if (!tit) {
                    toast('Activity wajib diisi.', 'warning');
                    return;
                }
                if (!tgt) {
                    toast('One Year Target wajib diisi.', 'warning');
                    return;
                }

                const payload = {
                    achievements: [{
                        id: null,
                        category: cat,
                        title: tit,
                        one_year_target: tgt,
                        weight: W,
                        self_score: R,
                        one_year_achievement: ach
                    }]
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
                        const key = 'tmp_' + Date.now() + '_' + Math.random().toString(16).slice(2);
                        ACHS.push({
                            id: null,
                            __key: key,
                            ipp_point_id: null,
                            category: cat,
                            title: tit,
                            one_year_target: tgt,
                            weight: W,
                            self_score: R,
                            one_year_achievement: ach
                        });
                        renderByCat(cat);
                        recalcTotals();
                        toast('Custom activity ditambahkan.');
                        mdlAdd.hide();
                    } else {
                        toast(res?.message || 'Gagal menambah activity.', 'warning');
                    }
                }).fail(function(xhr) {
                    toast(xhr.responseJSON?.message || 'Error server.', 'danger');
                });
            });

            // Save (global button) — semua perubahan via modal
            function saveAll() {
                $.ajax({
                    url: URL_UPDATE,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        achievements: [],
                        activities: []
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (res && res.ok) {
                        toast('Tersimpan.');
                        initLoad();
                    } else {
                        toast(res?.message || 'Gagal menyimpan.', 'warning');
                    }
                }).fail(function(xhr) {
                    toast(xhr.responseJSON?.message || 'Error server.', 'danger');
                });
            }
            $('#btn-save, #btn-save-bottom').on('click', saveAll);

            // Recalc (opsional)
            $('#btn-recalc').on('click', function() {
                $.post({
                    url: URL_RECALC,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (res && res.ok && res.totals) {
                        $tAch.text(fmt(res.totals.achievement_total));
                        $tGnd.text(fmt(res.totals.achievement_total));
                        $tGScore.text(fmt(res.totals.grand_score || 0));
                        updateCapBadges(); // pastikan badge ikut update
                        toast('Recalc selesai.');
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
