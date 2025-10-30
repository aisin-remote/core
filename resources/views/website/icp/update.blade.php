@extends('layouts.root.main')

@section('title', $title ?? 'Edit ICP')
@section('breadcrumbs', $title ?? 'Edit ICP')

@push('custom-css')
    <style>
        :root {
            --stage-border: #3f4a5a;
            --stage-head-bg: #1f2937;
            --stage-head-fg: #ffffff;
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
            box-shadow: 0 6px 18px var(--shadow-card);
        }

        .stage-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--stage-head-bg);
            color: var(--stage-head-fg);
            border-bottom: 2px solid var(--stage-border);
            padding: 1rem 1.25rem;
            font-weight: 700;
        }

        .stage-head strong {
            font-size: 1.1rem;
        }

        .stage-body {
            padding: var(--space-card);
        }

        .detail-row {
            background: var(--detail-bg);
            border: 2px solid var(--detail-border);
            border-radius: var(--radius-detail);
            padding: 14px;
        }

        .stage-card.theme-blue,
        .stage-card.theme-green,
        .stage-card.theme-amber,
        .stage-card.theme-purple,
        .stage-card.theme-rose {
            border-color: var(--stage-border);
            box-shadow: 0 0 0 3px var(--shadow-inset) inset, 0 6px 18px var(--shadow-card);
        }

        .stage-card.theme-blue .stage-head,
        .stage-card.theme-green .stage-head,
        .stage-card.theme-amber .stage-head,
        .stage-card.theme-purple .stage-head,
        .stage-card.theme-rose .stage-head {
            background: var(--stage-head-bg);
            border-bottom-color: var(--stage-border);
            color: var(--stage-head-fg);
        }

        .stage-card :is(button, .btn, select, input, textarea):focus-visible {
            outline: 3px solid #000;
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, .15);
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

        /* mode evaluate: readonly feel */
        .ro {
            pointer-events: none;
            background: #f9fafb !important;
            color: #334155 !important;
        }

        .ro:focus {
            outline: none !important;
            box-shadow: none !important;
        }
    </style>
@endpush

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => Swal.fire({
                title: 'Sukses!',
                text: @json(session('success')),
                icon: 'success'
            }));
        </script>
    @endif
    @if (session()->has('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => Swal.fire({
                title: 'Error!',
                text: @json(session('error')),
                icon: 'error'
            }));
        </script>
    @endif
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const errs = @json($errors->all());
                Swal.fire({
                    title: 'Validasi gagal',
                    html: errs.map(e => `<div style="text-align:left">• ${e}</div>`).join(''),
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    @php $isEvaluate = ($mode ?? null) === 'evaluate'; @endphp

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form action="{{ $isEvaluate ? route('icp.evaluate.store', $icp->id) : route('icp.update', $icp->id) }}"
                method="POST">
                @csrf
                @unless ($isEvaluate)
                    @method('PUT')
                @endunless

                <input type="hidden" name="employee_id" value="{{ $icp->employee_id }}">
                {{-- Current RTC code (batas bawah posisi) harus dikirim dari controller edit juga --}}
                <input type="hidden" id="employee_current_code" value="{{ $currentRtcCode ?? '' }}">

                {{-- HEADER --}}
                <div class="card p-4 shadow-sm rounded-3 mb-4">
                    <h3 class="text-center fw-bold mb-4">
                        {{ $isEvaluate ? 'Evaluate Individual Career Plan' : 'Update Individual Career Plan' }}
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Employee</label>
                            <input type="text" class="form-control form-select-sm"
                                value="{{ $icp->employee->name ?? '-' }}" disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Aspiration</label>
                            <textarea name="aspiration" class="form-control form-select-sm {{ $isEvaluate ? 'ro' : '' }}" rows="3" required>{{ old('aspiration', $icp->aspiration) }}</textarea>
                            @error('aspiration')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Career Target</label>
                            <select name="career_target_code" id="career_target"
                                class="form-select form-select-sm {{ $isEvaluate ? 'ro' : '' }}" required>
                                <option value="">Select Position</option>
                                @foreach ($rtcList as $rt)
                                    <option value="{{ $rt['code'] }}"
                                        {{ old('career_target_code', $icp->career_target) == $rt['code'] ? 'selected' : '' }}>
                                        {{ $rt['position'] }} ({{ $rt['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('career_target_code')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Position tiap Stage hanya boleh antara posisi kamu sekarang sampai target karier.
                            </small>
                            <div id="career-target-warn" class="text-danger small mt-1"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date Target</label>
                            <input type="date" name="date" id="date"
                                class="form-control form-select-sm {{ $isEvaluate ? 'ro' : '' }}"
                                value="{{ optional(\Carbon\Carbon::parse($icp->date))->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                {{-- DEVELOPMENT STAGE --}}
                <div class="card p-4 shadow-sm rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="fw-bold mb-0">Development Stage</h3>
                    </div>

                    <div id="stages-container" class="mt-3 d-grid gap-3"></div>

                    <div class="d-flex justify-content-center mt-3 {{ $isEvaluate ? 'd-none' : '' }}">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="btn-add-stage">
                            <i class="bi bi-plus-lg"></i> Add Year
                        </button>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>
                    <button type="submit" class="btn {{ $isEvaluate ? 'btn-primary' : 'btn-warning' }}">
                        <i class="bi bi-save"></i> {{ $isEvaluate ? 'Save Evaluation' : 'Update' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TEMPLATE --}}
    @verbatim
        <template id="stage-template">
            <div class="stage-card">
                <div class="stage-head d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <strong>Stage Tahun</strong>
                        <!-- Tahun editable di edit mode -->
                        <input type="number" min="2000" max="2100" pattern="\d{4}"
                            class="form-control form-control-sm stage-year" name="stages[__S__][year]" placeholder="YYYY"
                            style="width:110px" required>
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
                    <button type="button" class="btn btn-sm btn-danger btn-remove-detail"><i class="bi bi-x"></i></button>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Current Tech</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][current_technical]" data-value=""></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Required Tech</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][required_technical]" data-value=""></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Development Technical</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][development_technical]" data-value=""></select>
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
@endsection

<script>
    /* ===== Data dari server ===== */
    const DEPARTMENTS = @json($departments->map(fn($d) => ['v' => $d->name, 't' => $d->name . ' — ' . $d->company])->values());
    const DIVISIONS = @json($divisions->map(fn($d) => ['v' => $d->name, 't' => $d->name . ' - ' . $d->company])->values());
    const TECHS = @json($technicalCompetencies->pluck('competency'));
    const COMPANY = @json($icp->employee->company_name);

    const GRADES = @json($grades->pluck('aisin_grade'));

    const EXISTING_STAGES = @json($stages);

    const RTC_LIST = @json($rtcList);
    const RTC_RANK = Object.fromEntries(RTC_LIST.map((x, i) => [x.code.toUpperCase(), i]));

    const CURRENT_RTC_CODE = @json($currentRtcCode ?? null);

    const INITIAL_CAREER_TARGET = @json(old('career_target_code', $icp->career_target));

    const IS_EVALUATE = @json(($mode ?? null) === 'evaluate');
    const MAX_STAGE = 10;

    /* =========================
       helper: career target warning
    ========================== */
    function showCareerTargetWarning(on) {
        const warn = document.getElementById('career-target-warn');
        if (!warn) return;
        warn.textContent = on ? 'Please select Career Target first.' : '';
    }

    /* =========================
       UTIL: Tech Select (Select2)
    ========================== */
    function initTechSelects(scope, techListOverride = null) {
        const base = (techListOverride ?? TECHS ?? []).map(t => ({
            id: String(t),
            text: String(t)
        }));

        $(scope).find('.tech-select').each(function() {
            const $el = $(this);
            const preset = $el.attr('data-value');
            const prevVal = $el.val();

            if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');

            // rebuild <option> clean supaya gak duplikat
            $el.empty();
            $el.append(new Option('', '', true, false));
            base.forEach(optData => {
                const opt = new Option(optData.text, optData.id, false, false);
                $el.append(opt);
            });

            $el.select2({
                tags: true,
                placeholder: 'Select or type…',
                allowClear: true,
                width: '100%',
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    if (typeof data.text === 'undefined') return null;

                    const term = params.term.toLowerCase();
                    const text = String(data.text).toLowerCase();
                    const id = String(data.id || '').toLowerCase();
                    return (text.includes(term) || id.includes(term)) ? data : null;
                },
                createTag: function(params) {
                    const term = (params.term || '').trim();
                    if (!term) return null;

                    const existsInBase = base.some(o =>
                        o.text.toLowerCase() === term.toLowerCase()
                    );
                    const existsInDom = $el.find('option').toArray().some(o =>
                        o.text.toLowerCase() === term.toLowerCase()
                    );

                    if (existsInBase || existsInDom) return null;

                    return {
                        id: term,
                        text: term,
                        isNew: true
                    };
                },
                templateResult: function(data) {
                    return data.isNew ? $('<span>').text('Add: ' + data.text) : data.text;
                }
            });

            // restore
            if (preset && preset !== '') {
                if (!$el.find('option[value="' + preset.replaceAll('"', '\"') + '"]').length) {
                    const opt = new Option(preset, preset, true, true);
                    $el.append(opt);
                }
                $el.val(preset).trigger('change');
            } else if (prevVal) {
                $el.val(prevVal).trigger('change');
            } else {
                $el.val(null).trigger('change');
            }

            if (IS_EVALUATE) $el[0].classList.add('ro');
        });
    }

    /* =========================
       UTIL: Job Function dropdown
    ========================== */
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

    /* =========================
       UTIL: Positions dropdown (range RTC)
    ========================== */
    function fillPositionsRanged(selectEl, minCode, maxCode) {
        selectEl.innerHTML = '<option value="">Select Position</option>';

        let minC = minCode;
        let maxC = maxCode;

        if (!minC && maxC) minC = maxC;
        if (!maxC && minC) maxC = minC;

        const validMin = !!minC && (minC.toUpperCase() in RTC_RANK);
        const validMax = !!maxC && (maxC.toUpperCase() in RTC_RANK);

        if (!validMin || !validMax) {
            // tidak bisa bikin range -> biarkan kosong
            return;
        }

        const minRank = RTC_RANK[minC.toUpperCase()];
        const maxRank = RTC_RANK[maxC.toUpperCase()];
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
    }

    /* =========================
       UTIL: Level dropdown (Grades)
    ========================== */
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

    /* =========================
       Misc helpers
    ========================== */
    const THEME_CLASSES = ['theme-blue', 'theme-green', 'theme-amber', 'theme-purple', 'theme-rose'];
    const getStageCards = () => [...document.querySelectorAll('.stage-card')];

    function applyTheme(stageEl, idx) {
        THEME_CLASSES.forEach(c => stageEl.classList.remove(c));
        stageEl.classList.add(THEME_CLASSES[idx % THEME_CLASSES.length]);
    }

    function updateAddBtn() {
        const btn = document.getElementById('btn-add-stage');
        if (!btn) return;
        btn.disabled = getStageCards().length >= MAX_STAGE || IS_EVALUATE;
    }

    function reindexDetails(stage) {
        const sIdx = Number(stage.dataset.sIndex);
        stage.querySelectorAll('.details-container .detail-row').forEach((row, dIdx) => {
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

        // setelah reindex, kunci lagi last stage position
        forceLastStagePositionToCareerTarget();

        updateAddBtn();
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

    function addDetail(stageEl, data = null) {
        const sIndex = Number(stageEl.dataset.sIndex);
        const detailsBox = stageEl.querySelector('.details-container');
        const dIndex = detailsBox.querySelectorAll('.detail-row').length;

        const tpl = document.getElementById('detail-template').innerHTML
            .replaceAll('__S__', sIndex)
            .replaceAll('__D__', dIndex);

        const wrap = document.createElement('div');
        wrap.innerHTML = tpl.trim();
        const row = wrap.firstElementChild;

        const removeBtn = row.querySelector('.btn-remove-detail');
        removeBtn.addEventListener('click', () => {
            row.remove();
            reindexDetails(stageEl);
        });
        if (IS_EVALUATE) removeBtn.classList.add('d-none');

        if (data) {
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][current_technical]"]`)
                .setAttribute('data-value', data.current_technical ?? '');
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][required_technical]"]`)
                .setAttribute('data-value', data.required_technical ?? '');
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][development_technical]"]`)
                .setAttribute('data-value', data.development_technical ?? '');

            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][current_nontechnical]"]`).value =
                data.current_nontechnical ?? '';
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][required_nontechnical]"]`).value =
                data.required_nontechnical ?? '';
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][development_nontechnical]"]`).value =
                data.development_nontechnical ?? '';
        }

        detailsBox.appendChild(row);
        initTechSelects(row, stageEl._techList ?? TECHS);

        if (IS_EVALUATE) {
            row.querySelectorAll('input,select,textarea').forEach(el => el.classList.add('ro'));
        }
    }

    /* =========================
       Lock last stage position = career target
    ========================== */
    function forceLastStagePositionToCareerTarget() {
        const stages = getStageCards();
        if (stages.length === 0) return;

        const lastStage = stages[stages.length - 1];
        const careerVal = document.getElementById('career_target').value;

        const posSel = lastStage.querySelector('.stage-position');
        const lvlSel = lastStage.querySelector('.stage-level');

        // regenerate list posisi utk last stage berdasar CURRENT_RTC_CODE..careerVal
        fillPositionsRanged(posSel, CURRENT_RTC_CODE, careerVal);

        // pastikan careerVal ada di option. kalau belum ada -> inject
        if (careerVal && ![...posSel.options].some(o => o.value === careerVal)) {
            const manualOpt = document.createElement('option');
            manualOpt.value = careerVal;
            const rtMeta = RTC_LIST.find(x => x.code === careerVal);
            manualOpt.textContent = rtMeta ? `${rtMeta.position} (${rtMeta.code})` : careerVal;
            posSel.appendChild(manualOpt);
        }

        // set value career target di last stage
        if (careerVal) {
            posSel.value = careerVal;
        }

        // disable hanya untuk last stage
        posSel.disabled = true;

        // last stage level tetap editable (karena bisa pilih grade yg sesuai)
        if (!lvlSel.options.length) {
            fillGrades(lvlSel);
        }

        // semua stage sebelum last harus enabled (kecuali evaluate mode)
        const allButLast = stages.slice(0, -1);
        allButLast.forEach(s => {
            const p = s.querySelector('.stage-position');
            if (!IS_EVALUATE) {
                p.disabled = false;
            }
        });
    }

    function addStage(data = null) {
        const container = document.getElementById('stages-container');
        const idx = container.querySelectorAll('.stage-card').length;

        if (idx >= MAX_STAGE && !data) {
            Swal.fire('Batas tercapai', `Maksimal ${MAX_STAGE} Stage Tahun.`, 'info');
            return;
        }

        const tpl = document.getElementById('stage-template').innerHTML.replaceAll('__S__', idx);
        const wrap = document.createElement('div');
        wrap.innerHTML = tpl.trim();
        const stage = wrap.firstElementChild;

        stage.dataset.sIndex = String(idx);
        container.appendChild(stage);
        applyTheme(stage, idx);

        stage.classList.add('added');
        setTimeout(() => stage.classList.remove('added'), 300);

        const jobSel = stage.querySelector('.stage-job');
        const posSel = stage.querySelector('.stage-position');
        const lvlSel = stage.querySelector('.stage-level');
        const yearEl = stage.querySelector('.stage-year');
        const rmBtn = stage.querySelector('.btn-remove-stage');
        const addDet = stage.querySelector('.btn-add-detail');

        // hidden job_source
        let jobSrc = stage.querySelector('.job-source');
        if (!jobSrc) {
            jobSrc = document.createElement('input');
            jobSrc.type = 'hidden';
            jobSrc.className = 'job-source';
            jobSrc.name = `stages[${idx}][job_source]`;
            stage.appendChild(jobSrc);
        }

        // 1. Job Function
        fillJobs(jobSel);

        // 2. Level pakai grades global
        fillGrades(lvlSel);

        // 3. Position list range CURRENT_RTC_CODE..INITIAL_CAREER_TARGET
        fillPositionsRanged(
            posSel,
            CURRENT_RTC_CODE,
            INITIAL_CAREER_TARGET
        );

        // 4. Event handler:
        posSel.addEventListener('change', () => {
            if (!lvlSel.options.length) fillGrades(lvlSel);
        });

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

        rmBtn.addEventListener('click', () => {
            stage.remove();
            reindexStages();
        });

        addDet.addEventListener('click', () => addDetail(stage));

        // Prefill dari DB (edit mode)
        if (data) {
            yearEl.value = data.year ?? data.plan_year ?? '';

            if (data.job_function) {
                const match = [...jobSel.options].find(o => o.value === data.job_function);
                if (match) {
                    jobSel.value = data.job_function;
                }
                jobSrc.value = data.job_source || match?.dataset.source || '';
                if (jobSrc.value) {
                    refreshStageTechs(stage, jobSrc.value, data.job_function);
                }
            }

            if (data.position_code) {
                if (![...posSel.options].some(o => o.value === data.position_code)) {
                    const manualOpt = document.createElement('option');
                    manualOpt.value = data.position_code;
                    const foundRTC = RTC_LIST.find(r => r.code === data.position_code);
                    manualOpt.textContent = foundRTC ?
                        `${foundRTC.position} (${foundRTC.code})` :
                        data.position_code;
                    posSel.appendChild(manualOpt);
                }
                posSel.value = data.position_code;
            }

            if (data.level) {
                fillGrades(lvlSel, data.level);
            }

            if (Array.isArray(data.details) && data.details.length) {
                data.details.forEach(d => addDetail(stage, d));
            } else {
                addDetail(stage);
            }
        } else {
            // stage baru
            addDetail(stage);
        }

        // Evaluate mode lock fields
        if (IS_EVALUATE) {
            yearEl.classList.add('ro');
            jobSel.classList.add('ro');
            posSel.classList.add('ro');
            lvlSel.classList.add('ro');
            rmBtn.classList.add('d-none');
            addDet.classList.add('d-none');
        }

        updateAddBtn();
        forceLastStagePositionToCareerTarget(); // ensure lock after adding
    }

    document.addEventListener('DOMContentLoaded', () => {
        const careerSelect = document.getElementById('career_target');

        // set career target select explicit (menangkal old() kosong / autofill ngaco)
        if (careerSelect && INITIAL_CAREER_TARGET) {
            careerSelect.value = INITIAL_CAREER_TARGET;
        }
        showCareerTargetWarning(!careerSelect.value);

        // render stage dari DB / fallback 1 kosong
        if (Array.isArray(EXISTING_STAGES) && EXISTING_STAGES.length) {
            EXISTING_STAGES.forEach(s => addStage(s));
        } else {
            addStage();
        }

        // fallback isi date kalau kosong
        const dateEl = document.getElementById('date');
        if (dateEl && !dateEl.value) {
            const d = new Date();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            dateEl.value = `${d.getFullYear()}-${mm}-${dd}`;
        }

        // tombol add stage
        const addBtn = document.getElementById('btn-add-stage');
        if (addBtn) {
            addBtn.addEventListener('click', () => {
                addStage();
            });
        }
        updateAddBtn();

        // career target berubah:
        // - tampilkan/hilangkan warning
        // - refill posisi tiap stage
        // - kosongkan pilihannya
        // - isi ulang grades utk safety
        // - terakhir, kunci lagi last stage = career target
        careerSelect.addEventListener('change', () => {
            const careerNow = careerSelect.value;
            showCareerTargetWarning(!careerNow);

            getStageCards().forEach(stage => {
                const posSel = stage.querySelector('.stage-position');
                const lvlSel = stage.querySelector('.stage-level');

                fillPositionsRanged(
                    posSel,
                    CURRENT_RTC_CODE,
                    careerNow
                );

                posSel.value = '';
                posSel.disabled = false; // buka dulu semua
                fillGrades(lvlSel, "");
            });

            forceLastStagePositionToCareerTarget();
        });

        // submit validation
        const form = document.querySelector('form[action]');
        form.addEventListener('submit', (e) => {
            // sanitize potensi HTML injection
            form.querySelectorAll('input[name^="stages["], textarea[name^="stages["]').forEach(el => {
                el.value = (el.value || '').replace(/<[^>]*>/g, '').trim();
            });

            const stages = getStageCards();
            if (stages.length === 0) {
                e.preventDefault();
                Swal.fire('Oops', 'Minimal 1 tahun harus ditambahkan.', 'warning');
                return;
            }

            // career target wajib
            const careerVal = careerSelect.value;
            if (!careerVal) {
                e.preventDefault();
                Swal.fire('Oops', 'Please select Career Target.', 'warning');
                return;
            }

            // stage terakhir harus match career target
            const lastStage = stages[stages.length - 1];
            const lastPosSel = lastStage.querySelector('.stage-position');
            const lastPosVal = lastPosSel ? lastPosSel.value : '';
            if (lastPosVal !== careerVal) {
                e.preventDefault();
                Swal.fire(
                    'Oops',
                    'The last stage position must match the selected Career Target.',
                    'warning'
                );
                return;
            }

            // validasi tahun unik dan diisi
            const years = stages.map(s => s.querySelector('.stage-year')?.value?.trim()).filter(
            Boolean);
            if (years.length !== stages.length) {
                e.preventDefault();
                Swal.fire('Oops', 'Setiap Stage Tahun wajib diisi 4 digit.', 'warning');
                return;
            }
            if (new Set(years).size !== years.length) {
                e.preventDefault();
                Swal.fire('Oops', 'Plan year tidak boleh duplikat.', 'warning');
                return;
            }

            // minimal 1 detail per stage
            for (const stage of stages) {
                const cnt = stage.querySelectorAll('.details-container .detail-row').length;
                if (cnt === 0) {
                    e.preventDefault();
                    Swal.fire('Oops', 'Setiap tahun minimal punya 1 detail.', 'warning');
                    stage.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return;
                }
            }
        });
    });
</script>
