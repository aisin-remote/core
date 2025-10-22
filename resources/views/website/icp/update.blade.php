@extends('layouts.root.main')

@section('title', $title ?? 'Edit ICP')
@section('breadcrumbs', $title ?? 'Edit ICP')

@push('custom-css')
    <style>
        /* =========================
                                                   ICP Stage – Neutral High-Contrast
                                                   ========================= */
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
            font-size: 1.1rem
        }

        .stage-body {
            padding: var(--space-card)
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

        .hc-mode .stage-head {
            background: #000
        }

        .hc-mode .stage-card {
            border-color: #000
        }

        /* Select2 kecil agar selaras form-select-sm */
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

        /* Readonly visual (dipakai saat evaluate tanpa men-disable supaya tetap terkirim) */
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

    @php $isEvaluate = ($mode ?? null) === 'evaluate'; @endphp

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            {{-- === Form action: beda untuk edit vs evaluate === --}}
            <form action="{{ $isEvaluate ? route('icp.evaluate.store', $icp->id) : route('icp.update', $icp->id) }}"
                method="POST">
                @csrf
                @unless ($isEvaluate)
                    @method('PUT')
                @endunless
                <input type="hidden" name="employee_id" value="{{ $icp->employee_id }}">

                {{-- HEADER --}}
                <div class="card p-4 shadow-sm rounded-3 mb-4">
                    <h3 class="text-center fw-bold mb-4">
                        {{ $isEvaluate ? 'Evaluate Individual Career Plan' : 'Update Individual Career Plan' }}
                    </h3>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Employee</label>
                            <input type="text" class="form-control form-select-sm"
                                value="{{ $icp->employee->name ?? '-' }}" disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Aspiration</label>
                            <textarea name="aspiration" class="form-control form-select-sm" rows="3" required>{{ old('aspiration', $icp->aspiration) }}</textarea>
                            @error('aspiration')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Career Target</label>
                            <select name="career_target" id="career_target" class="form-select form-select-sm">
                                <option value="">Select Position</option>
                                @php
                                    $careerTarget = [
                                        'GM' => 'General Manager',
                                        'Act GM' => 'Act General Manager',
                                        'Manager' => 'Manager',
                                        'Act Manager' => 'Act Manager',
                                        'Coordinator' => 'Coordinator',
                                        'Act Coordinator' => 'Act Coordinator',
                                        'Section Head' => 'Section Head',
                                        'Act Section Head' => 'Act Section Head',
                                        'Supervisor' => 'Supervisor',
                                        'Act Supervisor' => 'Act Supervisor',
                                        'Act Leader' => 'Act Leader',
                                        'Act JP' => 'Act JP',
                                        'Operator' => 'Operator',
                                        'Direktur' => 'Direktur',
                                    ];
                                @endphp
                                @foreach ($careerTarget as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('career_target', $icp->career_target) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('career_target')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Date Target</label>
                            <input type="date" name="date" id="date" class="form-control form-select-sm"
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

                    <div class="d-flex justify-content-center mt-3">
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

    @verbatim
        <template id="stage-template">
            <div class="stage-card">
                <div class="stage-head d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <strong>Stage Tahun</strong>
                        <input type="number" min="2000" max="2100" pattern="\d{4}" class="form-control form-control-sm"
                            name="stages[__S__][plan_year]" placeholder="YYYY" style="width:110px" required>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-stage">Remove</button>
                </div>

                <div class="stage-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Job Function</label>
                            <select class="form-select form-select-sm stage-job" name="stages[__S__][job_function]" required>
                                <option value="">Select Job Function</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Position</label>
                            <select class="form-select form-select-sm stage-position" name="stages[__S__][position]" required>
                                <option value="">Select Position</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Level</label>
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
                        <label class="form-label">Current Tech</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][current_technical]" data-value=""></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Required Tech</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][required_technical]" data-value=""></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Development Technical</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][development_technical]" data-value=""></select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Current Non-Tech</label>
                        <input type="text" class="form-control form-select-sm"
                            name="stages[__S__][details][__D__][current_nontechnical]" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Required Non-Tech</label>
                        <input type="text" class="form-control form-select-sm"
                            name="stages[__S__][details][__D__][required_nontechnical]" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Development Non-Tech</label>
                        <input type="text" class="form-control form-select-sm"
                            name="stages[__S__][details][__D__][development_nontechnical]" required>
                    </div>
                </div>
            </div>
        </template>
    @endverbatim

    <script>
        /* ====== Data dari server (sekali) ====== */
        const DEPARTMENTS = @json($departments->map(fn($d) => ['v' => $d->name, 't' => $d->name . ' — ' . $d->company])->values());
        const GRADES = @json($grades->pluck('aisin_grade'));
        const POSITIONS = {
            'GM': 'General Manager',
            'Act GM': 'Act General Manager',
            'Manager': 'Manager',
            'Act Manager': 'Act Manager',
            'Coordinator': 'Coordinator',
            'Act Coordinator': 'Act Coordinator',
            'Section Head': 'Section Head',
            'Act Section Head': 'Act Section Head',
            'Supervisor': 'Supervisor',
            'Act Supervisor': 'Act Supervisor',
            'Act Leader': 'Act Leader',
            'Act JP': 'Act JP',
            'Operator': 'Operator',
            'Direktur': 'Direktur'
        };
        const EXISTING_STAGES = @json($stages); // dari controller
        const TECHS = @json($technicalCompetencies->pluck('competency'));
        const IS_EVALUATE = @json($isEvaluate);

        /* ====== Select2 untuk Tech ====== */
        function initTechSelects(scope) {
            const data = (TECHS || []).map(t => ({
                id: t,
                text: t
            }));
            $(scope).find('.tech-select').each(function() {
                const $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                $el.select2({
                    data,
                    tags: true,
                    placeholder: 'Select or type…',
                    allowClear: true,
                    width: '100%'
                });

                const preset = $el.attr('data-value');
                if (preset && !$el.val()) {
                    if (!TECHS.includes(preset)) {
                        const opt = new Option(preset, preset, true, true);
                        $el.append(opt).trigger('change');
                    } else {
                        $el.val(preset).trigger('change');
                    }
                }
                if (IS_EVALUATE) { // kunci teknikal saat evaluate? kalau tidak mau dikunci, hapus blok ini
                    // $el.prop('disabled', true); // jika ingin benar2 terkunci
                }
            });
        }

        /* ====== Helpers isi opsi ====== */
        function fillOptions(selectEl, items, valueKey = 'v', textKey = 't') {
            selectEl.innerHTML = '<option value="">Select</option>';
            items.forEach(it => {
                const opt = document.createElement('option');
                opt.value = valueKey ? it[valueKey] : it;
                opt.textContent = textKey ? it[textKey] : it;
                selectEl.appendChild(opt);
            });
        }

        function fillPositions(selectEl) {
            selectEl.innerHTML = '<option value="">Select Position</option>';
            Object.entries(POSITIONS).forEach(([v, t]) => {
                const o = document.createElement('option');
                o.value = v;
                o.textContent = t;
                selectEl.appendChild(o);
            });
        }

        function fillGrades(selectEl) {
            selectEl.innerHTML = '<option value="">-- Select Level --</option>';
            GRADES.forEach(g => {
                const o = document.createElement('option');
                o.value = g;
                o.textContent = g;
                selectEl.appendChild(o);
            });
        }

        /* ====== Utils ====== */
        const THEME_CLASSES = ['theme-blue', 'theme-green', 'theme-amber', 'theme-purple', 'theme-rose'];
        const getStageCards = () => [...document.querySelectorAll('.stage-card')];
        const getPlanYearInputs = () => [...document.querySelectorAll('input[name^="stages"][name$="[plan_year]"]')];
        const getPlanYears = () => getPlanYearInputs().map(i => i.value.trim()).filter(Boolean);

        function applyTheme(stageEl, idx) {
            THEME_CLASSES.forEach(c => stageEl.classList.remove(c));
            stageEl.classList.add(THEME_CLASSES[idx % THEME_CLASSES.length]);
        }

        function updateAddBtn() {
            document.getElementById('btn-add-stage').disabled = getStageCards().length >= 5;
        }

        function scrollToEl(el) {
            el?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            })
        }

        /* ====== Reindex ====== */
        function reindexDetails(stage) {
            const sIdx = Number(stage.dataset.sIndex);
            const rows = stage.querySelectorAll('.details-container .detail-row');
            rows.forEach((row, dIdx) => {
                row.querySelectorAll('[name*="[details]"]').forEach(el => {
                    el.name = el.name.replace(/stages\[\d+]\[details]\[\d+]/,
                        `stages[${sIdx}][details][${dIdx}]`);
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
            updateAddBtn();
        }

        /* ====== Add Detail ====== */
        function addDetail(stageEl, data = null) {
            const sIndex = Number(stageEl.dataset.sIndex);
            const detailsBox = stageEl.querySelector('.details-container');
            const dIndex = detailsBox.querySelectorAll('.detail-row').length;

            const tpl = document.getElementById('detail-template').innerHTML.replaceAll('__S__', sIndex).replaceAll('__D__',
                dIndex);
            const wrap = document.createElement('div');
            wrap.innerHTML = tpl.trim();
            const row = wrap.firstElementChild;

            row.querySelector('.btn-remove-detail').addEventListener('click', () => {
                row.remove();
                reindexDetails(stageEl);
            });

            if (data) {
                row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][current_technical]"]`).setAttribute(
                    'data-value', data.current_technical ?? '');
                row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][required_technical]"]`).setAttribute(
                    'data-value', data.required_technical ?? '');
                row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][development_technical]"]`).setAttribute(
                    'data-value', data.development_technical ?? '');
                row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][current_nontechnical]"]`).value = data
                    .current_nontechnical ?? '';
                row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][required_nontechnical]"]`).value = data
                    .required_nontechnical ?? '';
                row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][development_nontechnical]"]`).value = data
                    .development_nontechnical ?? '';
            }

            detailsBox.appendChild(row);
            initTechSelects(row);

            if (IS_EVALUATE) {
                // jika saat evaluate detail tak boleh dihapus/ditambah:
                row.querySelector('.btn-remove-detail')?.classList.add('d-none');
            }
        }

        /* ====== Add Stage ====== */
        function addStage(data = null) {
            const container = document.getElementById('stages-container');
            const idx = container.querySelectorAll('.stage-card').length;
            if (idx >= 5) {
                Swal.fire('Batas 5 tahun', 'Maksimal 5 Stage Tahun per ICP.', 'info');
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

            fillOptions(stage.querySelector('.stage-job'), DEPARTMENTS);
            fillPositions(stage.querySelector('.stage-position'));
            fillGrades(stage.querySelector('.stage-level'));

            stage.querySelector('.btn-remove-stage').addEventListener('click', () => {
                stage.remove();
                reindexStages();
            });
            stage.querySelector('.btn-add-detail').addEventListener('click', () => addDetail(stage));

            const yearInput = stage.querySelector(`[name="stages[${idx}][plan_year]"]`);
            yearInput.addEventListener('change', () => {
                const val = yearInput.value.trim();
                if (!val) return;
                const years = getPlanYears();
                if (years.filter(y => y === val).length > 1) {
                    Swal.fire('Plan year duplikat', 'Tahun tersebut sudah dipakai pada stage lain.', 'warning');
                    yearInput.value = '';
                    yearInput.focus();
                }
            });

            if (data) {
                yearInput.value = data.plan_year ?? '';
                stage.querySelector(`[name="stages[${idx}][job_function]"]`).value = data.job_function ?? '';
                stage.querySelector(`[name="stages[${idx}][position]"]`).value = data.position ?? '';
                stage.querySelector(`[name="stages[${idx}][level]"]`).value = data.level ?? '';
                (data.details || []).forEach(d => addDetail(stage, d));
                if (!data.details || !data.details.length) addDetail(stage);
            } else {
                addDetail(stage);
            }

            updateAddBtn();
        }

        /* ====== Init + submit guard ====== */
        document.addEventListener('DOMContentLoaded', () => {
            // tombol add
            const addBtn = document.getElementById('btn-add-stage');
            addBtn.addEventListener('click', addStage);

            // render stages dari database
            if (Array.isArray(EXISTING_STAGES) && EXISTING_STAGES.length) {
                EXISTING_STAGES.forEach(s => addStage(s));
            } else {
                addStage();
            }

            // defaultkan tanggal jika kosong
            const dateEl = document.getElementById('date');
            if (dateEl && !dateEl.value) {
                const d = new Date();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                dateEl.value = `${d.getFullYear()}-${mm}-${dd}`;
            }

            // guard submit
            const form = document.querySelector('form[action]');
            form.addEventListener('submit', (e) => {
                form.querySelectorAll('input[name^="stages["], textarea[name^="stages["]').forEach(el => {
                    el.value = (el.value || '').replace(/<[^>]*>/g, '').trim();
                });

                const stages = getStageCards();
                if (stages.length === 0) {
                    e.preventDefault();
                    Swal.fire('Oops', 'Minimal 1 tahun harus ditambahkan.', 'warning');
                    return;
                }
                const years = getPlanYears();
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
                for (const stage of stages) {
                    const count = stage.querySelectorAll('.details-container .detail-row').length;
                    if (count === 0) {
                        e.preventDefault();
                        Swal.fire('Oops', 'Setiap tahun minimal punya 1 detail.', 'warning');
                        scrollToEl(stage);
                        return;
                    }
                }
            });
        });
    </script>
@endsection
