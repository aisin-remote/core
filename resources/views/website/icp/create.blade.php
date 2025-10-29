@extends('layouts.root.main')

@section('title', $title ?? 'Icp')
@section('breadcrumbs', $title ?? 'Icp')

@push('custom-css')
    <style>
        :root {
            --stage-border: #3f4a5a;
            --stage-head-bg: #1f2937;
            --stage-head-fg: #fff;
            --stage-accent: #111827;
            --detail-bg: #f3f4f6;
            --detail-border: #d1d5db;
            --shadow-inset: rgba(63, 74, 90, .20);
            --shadow-card: rgba(0, 0, 0, .08);
            --radius-card: 1rem;
            --radius-detail: .65rem;
            --space-card: 1.25rem;
        }

        .stage-card {
            position: relative;
            border: 2.5px solid var(--stage-border);
            border-radius: var(--radius-card);
            background: #fff;
            overflow: hidden;
            box-shadow: 0 6px 18px var(--shadow-card)
        }

        .stage-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--stage-head-bg);
            color: var(--stage-head-fg);
            border-bottom: 2px solid var(--stage-border);
            padding: 1rem 1.25rem;
            font-weight: 700
        }

        .stage-head strong {
            font-size: 1.1rem
        }

        .stage-body {
            padding: var(--space-card)
        }

        .detail-row {
            background: var(--detail-bg);
            border: 2px solid var(--detail-border);
            border-radius: var(--radius-detail);
            padding: 14px
        }

        .stage-card.theme-blue,
        .stage-card.theme-green,
        .stage-card.theme-amber,
        .stage-card.theme-purple,
        .stage-card.theme-rose {
            border-color: var(--stage-border);
            box-shadow: 0 0 0 3px var(--shadow-inset) inset, 0 6px 18px var(--shadow-card)
        }

        .stage-card.theme-blue .stage-head,
        .stage-card.theme-green .stage-head,
        .stage-card.theme-amber .stage-head,
        .stage-card.theme-purple .stage-head,
        .stage-card.theme-rose .stage-head {
            background: var(--stage-head-bg);
            border-bottom-color: var(--stage-border);
            color: var(--stage-head-fg)
        }

        .stage-card :is(button, .btn, select, input, textarea):focus-visible {
            outline: 3px solid #000;
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, .15)
        }

        @keyframes popIn {
            from {
                transform: scale(.97);
                opacity: 0
            }

            to {
                transform: scale(1);
                opacity: 1
            }
        }

        .stage-card.added {
            animation: popIn .22s ease-out both
        }

        /* Select2 kecil selaras form-select-sm */
        .select2-container .select2-selection--single {
            height: calc(1.5em + .5rem + 2px)
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + .5rem)
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .5rem)
        }

        .select2-container .select2-selection--single {
            border: 1px solid #ced4da;
            padding: .25rem .5rem
        }

        .select2-selection__rendered {
            font-size: .875rem
        }
    </style>
@endpush

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => Swal.fire({
                title: "Sukses!",
                text: @json(session('success')),
                icon: "success"
            }));
        </script>
    @endif
    @if (session()->has('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () => Swal.fire({
                title: "Error!",
                text: @json(session('error')),
                icon: "error"
            }));
        </script>
    @endif
    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const errs = @json($errors->all());
                Swal.fire({
                    title: "Validasi gagal",
                    html: errs.map(e => `<div style="text-align:left">• ${e}</div>`).join(""),
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form id="icp-form" action="{{ route('icp.store') }}" method="POST">
                @csrf

                {{-- HEADER --}}
                <div class="card p-4 shadow-sm rounded-3 mb-4">
                    <h3 class="text-center fw-bold mb-4">Individual Career Plan</h3>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Employee</label>
                            <input type="text" class="form-control form-select-sm" value="{{ $employee->name }}"
                                disabled>
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                            <input type="hidden" name="employee_current_code" value="{{ $currentRtcCode }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Aspiration</label>
                            <textarea name="aspiration" class="form-control form-select-sm" rows="3" required>{{ old('aspiration') }}</textarea>
                            @error('aspiration')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Career Target</label>
                            <select name="career_target_code" id="career_target" class="form-select form-select-sm"
                                required>
                                <option value="">Select Position</option>
                                @foreach ($rtcList as $rt)
                                    <option value="{{ $rt['code'] }}" @selected(old('career_target_code') === $rt['code'])>
                                        {{ $rt['position'] }} ({{ $rt['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Position tiap Stage hanya boleh antara posisi kamu sekarang sampai target karier.
                            </small>
                            @error('career_target_code')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date Target</label>
                            <input type="date" name="date" id="date" class="form-control form-select-sm"
                                value="{{ old('date') }}">
                            <small class="text-muted">
                                Development Stage akan dibuat otomatis setelah memilih tanggal ini.
                            </small>
                            @error('date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- DEVELOPMENT STAGE --}}
                <div class="card p-4 shadow-sm rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="fw-bold mb-0">Development Stage</h3>
                    </div>

                    <div id="stages-container" class="mt-3 d-grid gap-3"></div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary btn-submit-icp">
                        <i class="bi bi-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TEMPLATE HTML UNTUK STAGE & DETAIL --}}
    @verbatim
        <template id="stage-template">
            <div class="stage-card">
                <div class="stage-head d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <strong>Stage Tahun</strong>
                        <input type="number" min="2000" max="2100" class="form-control form-control-sm stage-year"
                            name="stages[__S__][year]" style="width:110px" readonly required>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-stage">Remove</button>
                </div>

                <div class="stage-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Job Function</label>
                            <select class="form-select form-select-sm stage-job" name="stages[__S__][job_function]" required>
                                <option value="">Select Job Function</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Position</label>
                            <select class="form-select form-select-sm stage-position" name="stages[__S__][position_code]"
                                required disabled>
                                <option value="">Select Position</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Level</label>
                            <select class="form-select form-select-sm stage-level" name="stages[__S__][level]" required>
                                <option value="">-- Select Level --</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong>Details</strong>
                    </div>

                    <div class="details-container d-grid gap-2"></div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-warning btn-sm btn-add-detail">
                            <i class="bi bi-plus"></i> Add Detail
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <template id="detail-template">
            <div class="detail-row">
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-detail">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Current Tech</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][current_technical]">
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Required Tech</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][required_technical]">
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Development Technical</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][development_technical]">
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Current Non-Tech</label>
                        <input type="text" class="form-control form-select-sm"
                            name="stages[__S__][details][__D__][current_nontechnical]" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Required Non-Tech</label>
                        <input type="text" class="form-control form-select-sm"
                            name="stages[__S__][details][__D__][required_nontechnical]" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Development Non-Tech</label>
                        <input type="text" class="form-control form-select-sm"
                            name="stages[__S__][details][__D__][development_nontechnical]" required>
                    </div>
                </div>
            </div>
        </template>
    @endverbatim

    <script>
        /* ===== Data dari server ===== */
        const DEPARTMENTS = @json($departments->map(fn($d) => ['v' => $d->name, 't' => $d->name . ' — ' . $d->company])->values());
        const DIVISIONS = @json($divisions->map(fn($d) => ['v' => $d->name, 't' => $d->name . ' - ' . $d->company])->values());
        const TECHS = @json($technicalCompetencies->pluck('competency'));
        const COMPANY = @json($employee->company_name);
        const GRADES = @json($grades->pluck('aisin_grade'));

        // rtcList dari controller (urutan bawah -> atas)
        const RTC_LIST = @json($rtcList);
        // buat ranking numerik
        const RTC_RANK = Object.fromEntries(RTC_LIST.map((x, i) => [x.code.toUpperCase(), i]));

        // posisi awal karyawan dalam kode RTC, contoh "AS", "AM", dll
        const CURRENT_RTC_CODE = @json($currentRtcCode ?? null);

        const DEFAULTS = {
            plan_year: (new Date()).getFullYear(),
            job_function: @json($employee->job_function ?? '')
        };

        const HARD_MAX_STAGE = 10;

        /* ====== util select2 utk technical skills ====== */
        function initTechSelects(scope, techListOverride = null) {
            const base = (techListOverride ?? TECHS ?? []).map(t => ({
                id: String(t),
                text: String(t)
            }));

            $(scope).find('.tech-select').each(function() {
                const $el = $(this);
                const prevVal = $el.val();
                const prevDataValue = $el.attr('data-value');

                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }

                $el.select2({
                    data: base,
                    tags: true,
                    placeholder: 'Select or type…',
                    allowClear: true,
                    width: '100%',
                    matcher: function(params, data) {
                        if ($.trim(params.term) === '') return data;
                        if (typeof data.text === 'undefined') return null;

                        const term = params.term.toLowerCase();
                        const text = data.text.toLowerCase();
                        const id = String(data.id || '').toLowerCase();

                        if (text.indexOf(term) > -1 || id.indexOf(term) > -1) return data;
                        return null;
                    },
                    createTag: function(params) {
                        const term = params.term?.trim();
                        if (!term) return null;

                        const exists =
                            base.some(opt => String(opt.text).toLowerCase() === term.toLowerCase()) ||
                            $el.find('option').toArray().some(o => o.text.toLowerCase() === term
                                .toLowerCase());

                        if (exists) return null;

                        return {
                            id: term,
                            text: term,
                            isNew: true
                        };
                    },
                    templateResult: function(data) {
                        if (data.isNew) {
                            return $('<span>').text('Add: ' + data.text);
                        }
                        return data.text;
                    }
                });

                // restore value lama kalau ada
                if (prevDataValue && !$el.val()) {
                    if (!$el.find('option').toArray().some(o => o.value === prevDataValue)) {
                        const opt = new Option(prevDataValue, prevDataValue, true, true);
                        $el.append(opt).trigger('change');
                    } else {
                        $el.val(prevDataValue).trigger('change');
                    }
                } else if (prevVal) {
                    $el.val(prevVal).trigger('change');
                }
            });
        }

        /* ====== isi dropdown Job Function (gabungan dept/division) ====== */
        function fillJobs(selectEl) {
            selectEl.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = 'Select';
            ph.disabled = true;
            ph.selected = true;
            selectEl.appendChild(ph);

            const makeGroup = (label, items, src) => {
                const og = document.createElement('optgroup');
                og.label = label;
                items.forEach(it => {
                    const opt = document.createElement('option');
                    opt.value = it.v;
                    opt.textContent = it.t;
                    opt.dataset.source = src;
                    og.appendChild(opt);
                });
                selectEl.appendChild(og);
            };

            makeGroup('Departments', DEPARTMENTS, 'department');
            makeGroup('Divisions', DIVISIONS, 'division');
        }

        /**
         * Batasi posisi sesuai range [posisi sekarang .. career target]
         * RTC_RANK makin besar = makin tinggi
         */
        function fillPositionsRanged(selectEl, minCode, maxCode) {
            selectEl.innerHTML = '<option value="">Select Position</option>';

            if (
                !minCode || !maxCode ||
                !(minCode.toUpperCase() in RTC_RANK) ||
                !(maxCode.toUpperCase() in RTC_RANK)
            ) {
                selectEl.disabled = true;
                return;
            }

            const minRank = RTC_RANK[minCode.toUpperCase()];
            const maxRank = RTC_RANK[maxCode.toUpperCase()];
            const low = Math.min(minRank, maxRank);
            const high = Math.max(minRank, maxRank);

            RTC_LIST.forEach(rt => {
                const r = RTC_RANK[rt.code.toUpperCase()];
                if (r >= low && r <= high) {
                    const opt = document.createElement('option');
                    opt.value = rt.code;
                    opt.textContent = `${rt.position} (${rt.code})`;
                    selectEl.appendChild(opt);
                }
            });

            selectEl.disabled = false;
        }

        /* ====== isi Level dari GRADES (aisin_grade) ====== */
        function fillGrades(selectEl, selected = "") {
            selectEl.innerHTML = '<option value="">-- Select Level --</option>';

            GRADES.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g;
                opt.textContent = g;
                if (g === selected) {
                    opt.selected = true;
                }
                selectEl.appendChild(opt);
            });

            selectEl.disabled = false;
        }

        const getStageCards = () => [...document.querySelectorAll('.stage-card')];

        function applyTheme(stageEl, idx) {
            const THEMES = ['theme-blue', 'theme-green', 'theme-amber', 'theme-purple', 'theme-rose'];
            THEMES.forEach(c => stageEl.classList.remove(c));
            stageEl.classList.add(THEMES[idx % THEMES.length]);
        }

        function reindexDetails(stage) {
            const sIdx = Number(stage.dataset.sIndex);
            const rows = stage.querySelectorAll('.details-container .detail-row');
            rows.forEach((row, dIdx) => {
                row.querySelectorAll('[name*="[details]"]').forEach(el => {
                    el.name = el.name.replace(
                        /stages\[\d+]\[details]\[\d+]/,
                        `stages[${sIdx}][details][${dIdx}]`
                    );
                });
            });
        }

        function reindexStages() {
            getStageCards().forEach((stage, sIdx) => {
                stage.dataset.sIndex = String(sIdx);

                stage.querySelectorAll('[name^="stages["]').forEach(el => {
                    el.name = el.name.replace(/stages\[\d+]/, `stages[${sIdx}]`);
                });

                reindexDetails(stage);
                applyTheme(stage, sIdx);
            });

            // tahun stage otomatis:
            // stage-0 = tahun sekarang + 1
            const startYear = DEFAULTS.plan_year + 1;
            getStageCards().forEach((stage, i) => {
                const yearInput = stage.querySelector('.stage-year');
                yearInput.value = startYear + i;
            });
        }

        async function refreshStageTechs(stageEl, source, jobName) {
            initTechSelects(stageEl.querySelector('.details-container'), []);
            try {
                const qs = new URLSearchParams({
                    source,
                    name: jobName,
                    company: COMPANY
                });
                const res = await fetch(`{{ route('icp.techs') }}?` + qs.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const js = await res.json();
                const items = (js.ok ? js.items : []) || [];
                stageEl._techList = items;
                initTechSelects(stageEl.querySelector('.details-container'), items);
            } catch (e) {
                stageEl._techList = [];
                initTechSelects(stageEl.querySelector('.details-container'), []);
            }
        }

        function addDetail(stageEl) {
            const sIndex = Number(stageEl.dataset.sIndex);
            const detailsBox = stageEl.querySelector('.details-container');
            const dIndex = detailsBox.querySelectorAll('.detail-row').length;

            const tpl = document.getElementById('detail-template').innerHTML
                .replaceAll('__S__', sIndex)
                .replaceAll('__D__', dIndex);

            const wrap = document.createElement('div');
            wrap.innerHTML = tpl.trim();
            const row = wrap.firstElementChild;

            row.querySelector('.btn-remove-detail').addEventListener('click', () => {
                row.remove();
                reindexDetails(stageEl);
            });

            detailsBox.appendChild(row);
            initTechSelects(row, stageEl._techList ?? TECHS);
        }

        function addStage() {
            const container = document.getElementById('stages-container');
            const idx = container.querySelectorAll('.stage-card').length;

            if (idx >= HARD_MAX_STAGE) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Batas tercapai', `Maksimal ${HARD_MAX_STAGE} stage.`, 'info');
                }
                return null;
            }

            const tpl = document.getElementById('stage-template').innerHTML.replaceAll('__S__', idx);
            const wrap = document.createElement('div');
            wrap.innerHTML = tpl.trim();
            const stage = wrap.firstElementChild;

            stage.dataset.sIndex = String(idx);
            stage.classList.add('added');
            setTimeout(() => stage.classList.remove('added'), 300);

            const jobSel = stage.querySelector('.stage-job');
            const posSel = stage.querySelector('.stage-position');
            const lvlSel = stage.querySelector('.stage-level');
            const careerSelect = document.getElementById('career_target');

            // isi dropdown Job Function
            fillJobs(jobSel);

            // isi Level dropdown dari GRADES
            fillGrades(lvlSel);

            // hidden job_source
            let jobSrc = stage.querySelector('.job-source');
            if (!jobSrc) {
                jobSrc = document.createElement('input');
                jobSrc.type = 'hidden';
                jobSrc.className = 'job-source';
                jobSrc.name = `stages[${idx}][job_source]`;
                stage.appendChild(jobSrc);
            }

            // set year otomatis
            const yearInput = stage.querySelector('.stage-year');
            yearInput.readOnly = true;
            yearInput.value = (DEFAULTS.plan_year + 1) + idx;

            // remove stage
            stage.querySelector('.btn-remove-stage').addEventListener('click', () => {
                stage.remove();
                reindexStages();
            });

            // add detail row
            stage.querySelector('.btn-add-detail').addEventListener('click', () => addDetail(stage));

            // ketika Position diubah -> sekarang level TIDAK difilter lagi.
            posSel.addEventListener('change', () => {
                if (!lvlSel.options.length) {
                    fillGrades(lvlSel);
                }
            });

            // saat Job Function berubah → load competency via AJAX
            stage._techList = [];
            jobSel.addEventListener('change', () => {
                const opt = jobSel.options[jobSel.selectedIndex];
                const source = opt?.dataset.source || '';
                const jobName = jobSel.value || '';
                jobSrc.value = source;

                if (!source || !jobName) {
                    stage._techList = [];
                    initTechSelects(stage.querySelector('.details-container'), []);
                    return;
                }
                refreshStageTechs(stage, source, jobName);
            });

            // tambahin 1 detail default
            addDetail(stage);

            // batasi Position sesuai target career yang sudah dipilih
            const careerVal = careerSelect.value;
            if (careerVal) {
                fillPositionsRanged(posSel, CURRENT_RTC_CODE, careerVal);
            }

            // auto-select job function karyawan saat ini kalau ada
            if (DEFAULTS.job_function) {
                const match = [...jobSel.options].find(o => o.value === DEFAULTS.job_function);
                if (match) {
                    jobSel.value = DEFAULTS.job_function;
                    jobSel.dispatchEvent(new Event('change'));
                }
            }

            container.appendChild(stage);
            applyTheme(stage, idx);

            return stage;
        }

        function generateStagesFromTargetDate() {
            const container = document.getElementById('stages-container');
            const dateEl = document.getElementById('date');
            const val = dateEl.value; // "YYYY-MM-DD"

            if (!val) {
                container.innerHTML = '';
                return;
            }

            const currentYear = DEFAULTS.plan_year;
            const targetYear = Number(val.split('-')[0]);

            // contoh:
            // sekarang 2025, target 2029
            // diff = 4 → stage utk 2026,27,28,29
            let diff = targetYear - currentYear;

            if (diff < 1) diff = 1;
            if (diff > HARD_MAX_STAGE) diff = HARD_MAX_STAGE;

            container.innerHTML = '';

            for (let i = 0; i < diff; i++) {
                addStage();
            }

            reindexStages();
        }

        async function submitIcpAjax(e) {
            e.preventDefault();

            const formEl = document.getElementById('icp-form');
            const submitBtn = formEl.querySelector('.btn-submit-icp');

            // ==== Client-side minimal validation ====
            // 1. cek stages ada
            const stages = getStageCards();
            if (stages.length === 0) {
                Swal.fire('Oops', 'Development Stage masih kosong. Pilih Date Target dulu.', 'warning');
                return;
            }

            // 2. cek tiap stage punya minimal 1 detail
            for (const stage of stages) {
                const cnt = stage.querySelectorAll('.details-container .detail-row').length;
                if (cnt === 0) {
                    Swal.fire('Oops', 'Setiap stage minimal punya 1 detail.', 'warning');
                    return;
                }
            }

            // 3. sanitize text field dari HTML tag
            formEl.querySelectorAll('input[name^="stages["], textarea[name^="stages["]').forEach(el => {
                el.value = (el.value || '').replace(/<[^>]*>/g, '').trim();
            });

            // ==== Kirim AJAX ====
            const fd = new FormData(formEl);

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            try {
                const res = await fetch(formEl.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });

                const data = await res.json().catch(() => ({}));

                // Jika validasi Laravel gagal (422)
                if (res.status === 422) {
                    const errs = data.errors || {};
                    const flatErr = Object.values(errs).flat();
                    Swal.fire({
                        title: "Validasi gagal",
                        html: flatErr.map(e => `<div style="text-align:left">• ${e}</div>`).join(""),
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                    return;
                }

                // Jika server kirim ok:false
                if (!res.ok || data.ok === false) {
                    Swal.fire({
                        title: "Error",
                        text: data.message || 'Gagal menyimpan ICP.',
                        icon: "error"
                    });
                    return;
                }

                // Success
                Swal.fire({
                    title: "Sukses!",
                    text: data.message || 'ICP berhasil disimpan.',
                    icon: "success"
                }).then(() => {
                    // redirect manual kalau mau
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        // default balik previous page
                        window.location.href = "{{ url()->previous() }}";
                    }
                });

            } catch (err) {
                Swal.fire({
                    title: "Error",
                    text: "Terjadi error jaringan.",
                    icon: "error"
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-save"></i> Save';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const careerSelect = document.getElementById('career_target');
            const dateEl = document.getElementById('date');
            const formEl = document.getElementById('icp-form');

            // (1) default date -> auto generate stages di load awal
            if (dateEl && !dateEl.value) {
                const d = new Date();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                dateEl.value = `${d.getFullYear()}-${mm}-${dd}`;
            }
            generateStagesFromTargetDate();

            // (2) kalau user ubah target date → regenerate stage timeline
            dateEl.addEventListener('change', () => {
                generateStagesFromTargetDate();
            });

            // (3) kalau career target berubah → update dropdown Position tiap stage
            careerSelect.addEventListener('change', () => {
                const career = careerSelect.value;

                getStageCards().forEach(stage => {
                    const posSel = stage.querySelector('.stage-position');
                    const lvlSel = stage.querySelector('.stage-level');

                    fillPositionsRanged(posSel, CURRENT_RTC_CODE, career);

                    // reset posisi, tapi level tetap full list grades
                    posSel.value = '';
                    fillGrades(lvlSel, "");
                });
            });

            // (4) intercept submit -> AJAX
            formEl.addEventListener('submit', submitIcpAjax);
        });
    </script>
@endsection
