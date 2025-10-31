@extends('layouts.root.main')

@section('title', $title ?? 'Edit ICP')
@section('breadcrumbs', $title ?? 'Edit ICP')

@push('custom-css')
    <style>
        :root {
            --stage-border: #3f4a5a;
            --stage-head-bg: #1f2937;
            --stage-head-fg: #fff;
            --detail-bg: #f3f4f6;
            --detail-border: #d1d5db;
            --shadow-inset: rgba(63, 74, 90, .20);
            --shadow-card: rgba(0, 0, 0, .08);
            --radius-card: 1rem;
            --radius-detail: .65rem;
        }

        .stage-card {
            border: 2.5px solid var(--stage-border);
            border-radius: var(--radius-card);
            background: #fff;
            box-shadow: 0 6px 18px var(--shadow-card);
            position: relative;
            overflow: hidden;
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
            font-size: 1.1rem;
        }

        .stage-body {
            padding: 1.25rem
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
            box-shadow: 0 0 0 3px var(--shadow-inset) inset, 0 6px 18px var(--shadow-card)
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

        /* select2 height = form-select-sm */
        .select2-container .select2-selection--single {
            height: calc(1.5em + .5rem + 2px);
            border: 1px solid #ced4da;
            padding: .25rem .5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            line-height: calc(1.5em + .5rem);
            height: calc(1.5em + .5rem);
        }

        .select2-selection__rendered {
            font-size: .875rem
        }

        /* readonly / evaluate mode */
        .ro {
            pointer-events: none;
            background: #f9fafb !important;
            color: #334155 !important;
        }

        .ro:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .locked-pos {
            pointer-events: none;
            background-color: #f9fafb !important;
            color: #6b7280 !important;
        }
    </style>
@endpush

@section('main')

    {{-- SweetAlert flash & validation --}}
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => Swal.fire({
                title: 'Sukses!',
                text: @json(session('success')),
                icon: 'success'
            }));
        </script>
    @endif
    @if (session('error'))
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

    @php
        $isEvaluate = ($mode ?? null) === 'evaluate';

        $formattedPerf = collect($performanceData ?? [])
            ->map(fn($score, $year) => "$year = $score")
            ->implode(' | ');

        $lastEdu = implode(
            ' / ',
            array_filter([$edu->educational_level ?? '', $edu->major ?? '', $edu->institute ?? '']),
        );
    @endphp

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <form id="icp-form"
                action="{{ $isEvaluate ? route('icp.evaluate.store', $icp->id) : route('icp.update', $icp->id) }}"
                method="POST">
                @csrf
                @unless ($isEvaluate)
                    @method('PUT')
                @endunless

                <input type="hidden" name="employee_id" value="{{ $icp->employee_id }}">
                <input type="hidden" id="employee_current_code" value="{{ $currentRtcCode ?? '' }}">

                {{-- ================= HEADER CARD ================= --}}
                <div class="card shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4">

                        <h3 class="text-center fw-bold mb-4">
                            {{ $isEvaluate ? 'Evaluate Individual Career Plan' : 'Update Individual Career Plan' }}
                        </h3>

                        <div class="row g-4">
                            {{-- Kiri --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Employee</label>
                                    <input type="text" class="form-control form-select-sm"
                                        value="{{ $employee->name ?? ($icp->employee->name ?? '-') }}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Company</label>
                                    <input type="text" class="form-control form-select-sm"
                                        value="{{ $employee->company_name ?? ($icp->employee->company_name ?? '-') }}"
                                        disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Job Title</label>
                                    <input type="text" class="form-control form-select-sm"
                                        value="{{ $employee->position ?? '-' }}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Level / Sub-Level</label>
                                    <input type="text" class="form-control form-select-sm"
                                        value="{{ $employee->grade ?? '-' }}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Date of entry in Aisin Ind.</label>
                                    <input type="text" class="form-control form-select-sm"
                                        value="{{ $employee->formatted_date ?? '-' }}" disabled>
                                </div>
                            </div>

                            {{-- Kanan --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Perf. Appraisal Grade</label>
                                    <input type="text" class="form-control form-select-sm" value="{{ $formattedPerf }}"
                                        disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ass. Centre Grade</label>
                                    <input type="text" class="form-control form-select-sm"
                                        value="{{ $icp->ass_center_grade ?? '3' }}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Readiness</label>
                                    <input type="text" class="form-control form-select-sm"
                                        value="{{ $icp->readiness ?? '' }}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Date of Birth</label>
                                    <input type="text" class="form-control form-select-sm"
                                        value="{{ $employee->formatted_birth ?? '-' }}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Education</label>
                                    <input type="text" class="form-control form-select-sm" value="{{ $lastEdu }}"
                                        disabled>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Aspiration / Career Target / Date Target --}}
                        <div class="row g-4">
                            {{-- Aspiration --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Aspiration</label>
                                    <textarea name="aspiration" class="form-control form-select-sm {{ $isEvaluate ? 'ro' : '' }}" rows="3" required>{{ old('aspiration', $icp->aspiration) }}</textarea>

                                    @error('aspiration')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Career Target --}}
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Career Target</label>
                                    <select name="career_target_code" id="career_target"
                                        class="form-select form-select-sm {{ $isEvaluate ? 'ro' : '' }}" required>
                                        <option value="">Select Position</option>
                                        @foreach ($rtcList as $rt)
                                            <option value="{{ $rt['code'] }}" @selected(old('career_target_code', $icp->career_target) == $rt['code'])>
                                                {{ $rt['position'] }} ({{ $rt['code'] }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="form-text">
                                        Stage terakhir harus sama dengan Career Target.
                                    </div>
                                    <div id="career-target-warn" class="text-danger small mt-1"></div>

                                    @error('career_target_code')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Date Target --}}
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Date Target</label>
                                    <input type="date" name="date" id="date"
                                        class="form-control form-select-sm {{ $isEvaluate ? 'ro' : '' }}"
                                        value="{{ optional(\Carbon\Carbon::parse($icp->date))->format('Y-m-d') }}">

                                    <div class="form-text">
                                        Development Stage akan dibuat atau disesuaikan otomatis berdasarkan tanggal ini.
                                    </div>

                                    @error('date')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ================= DEVELOPMENT STAGE ================= --}}
                <div class="card p-4 shadow-sm rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="fw-bold mb-0">Development Stage</h3>
                    </div>

                    <div id="stages-container" class="mt-3 d-grid gap-3"></div>
                </div>

                {{-- ================= ACTION BUTTONS ================= --}}
                <div class="text-end mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>

                    <button type="submit" class="btn {{ $isEvaluate ? 'btn-primary' : 'btn-warning' }} btn-submit-icp">
                        <i class="bi bi-save"></i>
                        {{ $isEvaluate ? 'Save Evaluation' : 'Update' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= TEMPLATES ================= --}}
    @verbatim
        <template id="stage-template">
            <div class="stage-card">
                <div class="stage-head">
                    <div class="d-flex align-items-center gap-3">
                        <strong>Stage Tahun</strong>
                        <input type="number" min="2000" max="2100" pattern="\d{4}"
                            class="form-control form-control-sm stage-year" name="stages[__S__][year]" style="width:110px"
                            required>
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

@endSection

@push('scripts')
    <script>
        /* ======== DATA DARI SERVER ======== */
        const DEPARTMENTS = @json(
            $departments->map(fn($d) => [
                        'v' => $d->name,
                        't' => $d->name . ' — ' . $d->company,
                    ])->values());

        const DIVISIONS = @json(
            $divisions->map(fn($d) => [
                        'v' => $d->name,
                        't' => $d->name . ' - ' . $d->company,
                    ])->values());

        const TECHS = @json($technicalCompetencies->pluck('competency'));
        const COMPANY = @json($employee->company_name ?? $icp->employee->company_name);
        const GRADES = @json($grades->pluck('aisin_grade'));
        const RTC_LIST = @json($rtcList);
        const CURRENT_RTC_CODE = @json($currentRtcCode ?? null);

        // Stage hasil rebuild dari controller (plan_year-grouped)
        const EXISTING_STAGES = @json($stages);

        // value career target awal (batas atas posisi)
        const INITIAL_CAREER_TARGET = @json(old('career_target_code', $icp->career_target));

        const IS_EVALUATE = @json(($mode ?? null) === 'evaluate');
        const HARD_MAX_STAGE = 10;

        const THEMES = ['theme-blue', 'theme-green', 'theme-amber', 'theme-purple', 'theme-rose'];
        const RTC_RANK = Object.fromEntries(
            RTC_LIST.map((x, i) => [String(x.code || '').toUpperCase(), i])
        );

        // helpers dom
        const $id = id => document.getElementById(id);
        const getStageCards = () => [...document.querySelectorAll('.stage-card')];


        /* =======================================
           UTIL KECIL
        ========================================*/

        function showCareerTargetWarning(on) {
            const warn = $id('career-target-warn');
            if (!warn) return;
            warn.textContent = on ?
                'Please select Career Target first.' :
                '';
        }

        function applyTheme(stageEl, idx) {
            THEMES.forEach(c => stageEl.classList.remove(c));
            stageEl.classList.add(THEMES[idx % THEMES.length]);
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

            lockLastStageToCareerTarget();
        }


        /* =======================================
           DROPDOWN BUILDERS
        ========================================*/

        function fillJobs(sel) {
            sel.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = 'Select';
            ph.disabled = true;
            ph.selected = true;
            sel.appendChild(ph);

            function addGroup(label, items, src) {
                const og = document.createElement('optgroup');
                og.label = label;
                items.forEach(it => {
                    const opt = document.createElement('option');
                    opt.value = it.v;
                    opt.textContent = it.t;
                    opt.dataset.source = src;
                    og.appendChild(opt);
                });
                sel.appendChild(og);
            }

            addGroup('Departments', DEPARTMENTS, 'department');
            addGroup('Divisions', DIVISIONS, 'division');
        }

        function fillGrades(sel, selected = "") {
            sel.innerHTML = '<option value="">-- Select Level --</option>';
            GRADES.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g;
                opt.textContent = g;
                if (g === selected) opt.selected = true;
                sel.appendChild(opt);
            });
            sel.disabled = false;
        }

        function fillPositionsRanged(sel, minCode, maxCode) {
            sel.innerHTML = '<option value="">Select Position</option>';

            let lo = minCode,
                hi = maxCode;
            if (!lo && hi) lo = hi;
            if (!hi && lo) hi = lo;

            const validLo = !!lo && (lo.toUpperCase() in RTC_RANK);
            const validHi = !!hi && (hi.toUpperCase() in RTC_RANK);
            if (!validLo || !validHi) return;

            const a = RTC_RANK[lo.toUpperCase()];
            const b = RTC_RANK[hi.toUpperCase()];
            const start = Math.min(a, b);
            const end = Math.max(a, b);

            RTC_LIST.forEach(rt => {
                const r = RTC_RANK[rt.code.toUpperCase()];
                if (r >= start && r <= end) {
                    const opt = document.createElement('option');
                    opt.value = rt.code;
                    opt.textContent = `${rt.position} (${rt.code})`;
                    sel.appendChild(opt);
                }
            });
        }


        /* =======================================
           SELECT2 UNTUK TECH
        ========================================*/

        function initTechSelects(scope, techListOverride = null) {
            const base = (techListOverride ?? TECHS ?? []).map(t => ({
                id: String(t),
                text: String(t),
            }));

            $(scope).find('.tech-select').each(function() {
                const $el = $(this);
                const preset = $el.attr('data-value');
                const prevVal = $el.val();

                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }

                $el.empty();
                $el.append(new Option('', '', true, false));
                base.forEach(o => {
                    $el.append(new Option(o.text, o.id, false, false));
                });

                $el.select2({
                    tags: true,
                    placeholder: 'Select or type…',
                    allowClear: true,
                    width: '100%',
                    matcher: (params, data) => {
                        if ($.trim(params.term) === '') return data;
                        if (typeof data.text === 'undefined') return null;
                        const term = (params.term || '').toLowerCase();
                        const txt = String(data.text).toLowerCase();
                        const id = String(data.id || '').toLowerCase();
                        return (txt.includes(term) || id.includes(term)) ? data : null;
                    },
                    createTag: (params) => {
                        const term = (params.term || '').trim();
                        if (!term) return null;

                        const existsBase = base.some(o => o.text.toLowerCase() === term.toLowerCase());
                        const existsDom = $el.find('option').toArray().some(
                            o => o.text.toLowerCase() === term.toLowerCase()
                        );
                        if (existsBase || existsDom) return null;

                        return {
                            id: term,
                            text: term,
                            isNew: true
                        };
                    },
                    templateResult: data => data.isNew ?
                        $('<span>').text('Add: ' + data.text) : data.text
                });

                // restore value
                if (preset) {
                    if (!$el.find(`option[value="${preset.replaceAll('"', '\\"')}"]`).length) {
                        $el.append(new Option(preset, preset, true, true));
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

        async function refreshStageTechs(stageEl, source, jobName) {
            // kosongin dulu
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
            } catch {
                stageEl._techList = [];
                initTechSelects(stageEl.querySelector('.details-container'), []);
            }
        }


        /* =======================================
           DETAIL ROW BUILDER
        ========================================*/

        function addDetail(stageEl, data = null) {
            const sIndex = Number(stageEl.dataset.sIndex);
            const detailsBox = stageEl.querySelector('.details-container');
            const dIndex = detailsBox.querySelectorAll('.detail-row').length;

            const tpl = $id('detail-template').innerHTML
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
            if (IS_EVALUATE) {
                removeBtn.classList.add('d-none');
            }

            // prefill data
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


        /* =======================================
           STAGE BUILDER
        ========================================*/

        function buildStageDOM(data = null) {
            const container = $id('stages-container');
            const idx = container.querySelectorAll('.stage-card').length;

            if (idx >= HARD_MAX_STAGE && !data) {
                Swal.fire('Batas tercapai', `Maksimal ${HARD_MAX_STAGE} stage.`, 'info');
                return null;
            }

            const tpl = $id('stage-template').innerHTML.replaceAll('__S__', idx);
            const wrap = document.createElement('div');
            wrap.innerHTML = tpl.trim();
            const stage = wrap.firstElementChild;

            stage.dataset.sIndex = String(idx);
            container.appendChild(stage);
            applyTheme(stage, idx);

            stage.classList.add('added');
            setTimeout(() => stage.classList.remove('added'), 300);

            const yearEl = stage.querySelector('.stage-year');
            const jobSel = stage.querySelector('.stage-job');
            const posSel = stage.querySelector('.stage-position');
            const lvlSel = stage.querySelector('.stage-level');
            const rmBtn = stage.querySelector('.btn-remove-stage');

            // hidden job_source (disubmit ke backend)
            let jobSrc = stage.querySelector('.job-source');
            if (!jobSrc) {
                jobSrc = document.createElement('input');
                jobSrc.type = 'hidden';
                jobSrc.className = 'job-source';
                jobSrc.name = `stages[${idx}][job_source]`;
                stage.appendChild(jobSrc);
            }

            // isi dropdown
            fillJobs(jobSel);
            fillGrades(lvlSel);
            fillPositionsRanged(posSel, CURRENT_RTC_CODE, INITIAL_CAREER_TARGET);

            // event
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

            // Prefill data existing
            if (data) {
                // year
                yearEl.value = data.year ?? data.plan_year ?? '';

                // job function select
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

                // position (RTC code)
                if (data.position_code) {
                    if (![...posSel.options].some(o => o.value === data.position_code)) {
                        const opt = document.createElement('option');
                        opt.value = data.position_code;
                        const meta = RTC_LIST.find(r => r.code === data.position_code);
                        opt.textContent = meta ?
                            `${meta.position} (${meta.code})` :
                            data.position_code;
                        posSel.appendChild(opt);
                    }
                    posSel.value = data.position_code;
                }

                // level / grade
                if (data.level) {
                    fillGrades(lvlSel, data.level);
                }

                // detail list
                if (Array.isArray(data.details) && data.details.length) {
                    data.details.forEach(d => addDetail(stage, d));
                } else {
                    addDetail(stage);
                }
            } else {
                // stage baru kosong -> minimal 1 detail kosong
                addDetail(stage);
            }

            // mode evaluate = readonly
            if (IS_EVALUATE) {
                yearEl.classList.add('ro');
                jobSel.classList.add('ro');
                posSel.classList.add('ro');
                lvlSel.classList.add('ro');
                rmBtn.classList.add('d-none');

                // hilangkan tombol remove di detail juga di addDetail()
            }

            return stage;
        }


        /* =======================================
           LOCK LAST STAGE = CAREER TARGET
        ========================================*/

        function lockLastStageToCareerTarget() {
            const stages = getStageCards();
            if (!stages.length) return;

            const careerVal = $id('career_target').value;
            const lastStage = stages[stages.length - 1];

            const posSel = lastStage.querySelector('.stage-position');
            const lvlSel = lastStage.querySelector('.stage-level');

            // rebuild options sesuai range CURRENT_RTC_CODE..careerVal
            fillPositionsRanged(posSel, CURRENT_RTC_CODE, careerVal);

            // pastikan careerVal ada di dropdown
            if (careerVal && ![...posSel.options].some(o => o.value === careerVal)) {
                const injected = document.createElement('option');
                injected.value = careerVal;
                const meta = RTC_LIST.find(x => x.code === careerVal);
                injected.textContent = meta ?
                    `${meta.position} (${meta.code})` :
                    careerVal;
                posSel.appendChild(injected);
            }

            if (careerVal) {
                posSel.value = careerVal;
            }

            // LAST stage selalu locked
            posSel.disabled = true;

            if (!lvlSel.options.length) {
                fillGrades(lvlSel);
            }

            // stage selain terakhir => boleh edit (kecuali evaluate)
            const others = stages.slice(0, -1);
            others.forEach(s => {
                const p = s.querySelector('.stage-position');
                if (!IS_EVALUATE) {
                    p.disabled = false;
                    p.classList.remove('locked-pos');
                    p.style.pointerEvents = '';
                }
            });
        }


        /* =======================================
           AUTO BUILD DARI TANGGAL TARGET
           - Persis konsep create: jumlah stage = selisih tahun
        ========================================*/

        function generateStagesFromTargetDate() {
            const container = $id('stages-container');
            const dateEl = $id('date');
            const careerEl = $id('career_target');
            const careerVal = careerEl.value;

            // butuh career target
            if (!careerVal) {
                container.innerHTML = '';
                showCareerTargetWarning(true);
                return;
            } else {
                showCareerTargetWarning(false);
            }

            const val = dateEl.value; // 'YYYY-MM-DD'
            if (!val) {
                // kalau tanggal kosong: jangan generate apa2
                container.innerHTML = '';
                return;
            }

            const currentYear = (new Date()).getFullYear();
            const targetYear = Number(val.split('-')[0]);

            // minimal 1 stage, maksimal HARD_MAX_STAGE
            let diff = targetYear - currentYear;
            if (diff < 1) diff = 1;
            if (diff > HARD_MAX_STAGE) diff = HARD_MAX_STAGE;

            container.innerHTML = '';

            for (let i = 0; i < diff; i++) {
                const stage = buildStageDOM();
                if (!stage) break;
            }

            // set nilai year per stage (mulai currentYear+1, sama kayak create)
            const startYear = currentYear + 1;
            getStageCards().forEach((stage, i) => {
                const yearInput = stage.querySelector('.stage-year');
                if (yearInput && !yearInput.value) {
                    yearInput.value = startYear + i;
                }
            });

            reindexStages();
            lockLastStageToCareerTarget();
        }


        /* =======================================
           INIT PAGE
        ========================================*/

        document.addEventListener('DOMContentLoaded', () => {
            const careerSelect = $id('career_target');
            const dateEl = $id('date');
            const formEl = $id('icp-form');

            // set select career target (biar konsisten visual)
            if (careerSelect && INITIAL_CAREER_TARGET) {
                careerSelect.value = INITIAL_CAREER_TARGET;
            }
            showCareerTargetWarning(!careerSelect.value);

            // Prefill: jika controller sudah kasih $stages => render pakai itu
            // Kalau kosong fallback generate by date (kayak create)
            if (Array.isArray(EXISTING_STAGES) && EXISTING_STAGES.length) {
                const container = $id('stages-container');
                container.innerHTML = '';

                EXISTING_STAGES.forEach(s => {
                    const stage = buildStageDOM(s);
                    // setelah buildStageDOM(s) kita belum nyentuh lock, kita lock nanti dibawah
                });

                // Setelah semua stage masuk, kita reindex & force lock
                reindexStages();
                lockLastStageToCareerTarget();
            } else {
                // Auto-generate dari Date Target (behavior create)
                // kalau date kosong, isi default hari ini dulu
                if (dateEl && !dateEl.value) {
                    const d = new Date();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    dateEl.value = `${d.getFullYear()}-${mm}-${dd}`;
                }
                generateStagesFromTargetDate();
            }

            // on change date -> regenerate stage plan
            // NOTE: ini akan override stage yang udah ada!
            dateEl.addEventListener('change', () => {
                generateStagesFromTargetDate();
            });

            // on change career target:
            //  - toggle warning
            //  - refill posisi tiap stage sesuai range baru
            //  - set ulang last stage = career target
            //  - regenerate kalau belum ada stage
            careerSelect.addEventListener('change', () => {
                const careerNow = careerSelect.value;
                showCareerTargetWarning(!careerNow);

                const stages = getStageCards();

                if (!stages.length) {
                    // belum ada stage? coba generate ulang dari date
                    generateStagesFromTargetDate();
                    return;
                }

                // refill tiap stage position list + kosongin value
                stages.forEach(stage => {
                    const posSel = stage.querySelector('.stage-position');
                    const lvlSel = stage.querySelector('.stage-level');

                    fillPositionsRanged(posSel, CURRENT_RTC_CODE, careerNow);
                    posSel.value = '';
                    posSel.disabled = false;
                    fillGrades(lvlSel, "");
                });

                lockLastStageToCareerTarget();
            });

            // form submit validation — sama kayak create + edit sebelumnya
            formEl.addEventListener('submit', (e) => {
                // sanitize text di stage fields
                formEl.querySelectorAll('input[name^="stages["], textarea[name^="stages["]')
                    .forEach(el => {
                        el.value = (el.value || '').replace(/<[^>]*>/g, '').trim();
                    });

                const stages = getStageCards();
                if (!stages.length) {
                    e.preventDefault();
                    Swal.fire('Oops', 'Development Stage masih kosong. Pilih Date Target dulu.', 'warning');
                    return;
                }

                const careerVal = careerSelect.value;
                if (!careerVal) {
                    e.preventDefault();
                    Swal.fire('Oops', 'Please select Career Target.', 'warning');
                    return;
                }

                // last stage position wajib == career target
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

                // tiap stage harus punya year unik dan valid
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

                // minimal 1 detail / stage
                for (const stg of stages) {
                    const cnt = stg.querySelectorAll('.details-container .detail-row').length;
                    if (cnt === 0) {
                        e.preventDefault();
                        Swal.fire('Oops', 'Setiap tahun minimal punya 1 detail.', 'warning');
                        stg.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return;
                    }
                }
            });
        });
    </script>
@endpush
