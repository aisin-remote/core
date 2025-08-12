@extends('layouts.root.main')

@section('title', $title ?? 'Edit ICP')
@section('breadcrumbs', $title ?? 'Edit ICP')

@section('main')
@if (session('success'))
<script>
    document.addEventListener("DOMContentLoaded", () => Swal.fire({
        title: "Sukses!"
        , text: @json(session('success'))
        , icon: "success"
    }));

</script>
@endif
@if (session('error'))
<script>
    document.addEventListener("DOMContentLoaded", () => Swal.fire({
        title: "Error!"
        , text: @json(session('error'))
        , icon: "error"
    }));

</script>
@endif

<style>
    .stage-card {
        border: 1px solid #e9ecef;
        border-radius: .75rem;
        background: #fff
    }

    .stage-head {
        border-bottom: 1px solid #e9ecef;
        padding: .75rem 1rem;
        background: #f8f9fa
    }

    .stage-body {
        padding: 1rem
    }

    .detail-row {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: .5rem;
        padding: 12px
    }

    /* === Stage color themes === */
    .stage-card.theme-blue   { border-color:#b6d8ff; box-shadow:0 0 0 2px rgba(182,216,255,.18) inset; }
    .stage-card.theme-blue .stage-head   { background:#eef6ff; border-bottom-color:#b6d8ff; }

    .stage-card.theme-green  { border-color:#bce7c6; box-shadow:0 0 0 2px rgba(188,231,198,.18) inset; }
    .stage-card.theme-green .stage-head  { background:#f1fbf3; border-bottom-color:#bce7c6; }

    .stage-card.theme-amber  { border-color:#ffd79a; box-shadow:0 0 0 2px rgba(255,215,154,.18) inset; }
    .stage-card.theme-amber .stage-head  { background:#fff7e8; border-bottom-color:#ffd79a; }

    .stage-card.theme-purple { border-color:#d3c2ff; box-shadow:0 0 0 2px rgba(211,194,255,.18) inset; }
    .stage-card.theme-purple .stage-head { background:#f4f0ff; border-bottom-color:#d3c2ff; }

    .stage-card.theme-rose   { border-color:#ffb7c5; box-shadow:0 0 0 2px rgba(255,183,197,.18) inset; }
    .stage-card.theme-rose .stage-head   { background:#fff0f3; border-bottom-color:#ffb7c5; }

    @keyframes popIn { from {transform:scale(.98);opacity:.0} to {transform:scale(1);opacity:1} }
    .stage-card.added { animation: popIn .22s ease-out both; }
</style>

<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-fluid">
        <form action="{{ route('icp.update', $icp->id) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="employee_id" value="{{ $icp->employee_id }}">

            {{-- HEADER --}}
            <div class="card p-4 shadow-sm rounded-3 mb-4">
                <h3 class="text-center fw-bold mb-4">Update Individual Career Plan</h3>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" value="{{ $icp->employee->name ?? '-' }}" disabled>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Aspiration</label>
                        <textarea name="aspiration" class="form-control" rows="3" required>{{ old('aspiration',$icp->aspiration) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Career Target</label>
                        <select name="career_target" class="form-select form-select-sm">
                            <option value="">Select Position</option>
                            @php
                            $careerTarget = [
                            'GM'=>'General Manager','Act GM'=>'Act General Manager','Manager'=>'Manager','Act Manager'=>'Act Manager',
                            'Coordinator'=>'Coordinator','Act Coordinator'=>'Act Coordinator','Section Head'=>'Section Head','Act Section Head'=>'Act Section Head',
                            'Supervisor'=>'Supervisor','Act Supervisor'=>'Act Supervisor','Act Leader'=>'Act Leader','Act JP'=>'Act JP',
                            'Operator'=>'Operator','Direktur'=>'Direktur',
                            ];
                            @endphp
                            @foreach ($careerTarget as $value => $label)
                            <option value="{{ $value }}" {{ old('career_target',$icp->career_target)==$value?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="date" class="form-control form-select-sm" value="{{ \Carbon\Carbon::parse($icp->date)->format('Y-m-d') }}">
                    </div>
                </div>
            </div>

            {{-- DEVELOPMENT STAGE --}}
            <div class="card p-4 shadow-sm rounded-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="fw-bold mb-0">Development Stage</h3>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add-stage"><i class="bi bi-plus-lg"></i> Add Tahun</button>
                </div>

                <div id="stages-container" class="mt-3 d-grid gap-3"></div>

                <datalist id="techList">
                    @foreach($technicalCompetencies as $tc)
                    <option value="{{ $tc->competency }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="text-end mt-4">
                <a href="{{ url()->previous() }}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Back</a>
                <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Templates --}}
@verbatim
<template id="stage-template">
    <div class="stage-card">
        <div class="stage-head d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <strong>Stage Tahun</strong>
                <input type="number" min="2000" max="2100" pattern="\d{4}" class="form-control form-control-sm"
                    name="stages[__S__][plan_year]" placeholder="YYYY" style="width:110px" required>
            </div>
            <button type="button" class="btn btn-light btn-sm btn-remove-stage">Remove</button>
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
                <button type="button" class="btn btn-outline-primary btn-sm btn-add-detail"><i class="bi bi-plus"></i> Add Detail</button>
            </div>

            <div class="details-container d-grid gap-2"></div>
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
                <input type="text" class="form-control form-control-sm" name="stages[__S__][details][__D__][current_technical]" list="techList" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Current Non-Tech</label>
                <input type="text" class="form-control form-control-sm" name="stages[__S__][details][__D__][current_nontechnical]" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Required Tech</label>
                <input type="text" class="form-control form-control-sm" name="stages[__S__][details][__D__][required_technical]" list="techList" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Required Non-Tech</label>
                <input type="text" class="form-control form-control-sm" name="stages[__S__][details][__D__][required_nontechnical]" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Development Technical</label>
                <input type="text" class="form-control form-control-sm" name="stages[__S__][details][__D__][development_technical]" list="techList" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Development Non-Tech</label>
                <input type="text" class="form-control form-control-sm" name="stages[__S__][details][__D__][development_nontechnical]" required>
            </div>
        </div>
    </div>
</template>
@endverbatim

@push('scripts')
<script>
    const positions = {
        'GM': 'General Manager'
        , 'Act GM': 'Act General Manager'
        , 'Manager': 'Manager'
        , 'Act Manager': 'Act Manager'
        , 'Coordinator': 'Coordinator'
        , 'Act Coordinator': 'Act Coordinator'
        , 'Section Head': 'Section Head'
        , 'Act Section Head': 'Act Section Head'
        , 'Supervisor': 'Supervisor'
        , 'Act Supervisor': 'Act Supervisor'
        , 'Act Leader': 'Act Leader'
        , 'Act JP': 'Act JP'
        , 'Operator': 'Operator'
        , 'Direktur': 'Direktur'
    };
    const departments = @json(
        $departments->map(fn($d) => ['v'=>$d->name, 't'=>$d->name.' — '.$d->company])->values()
        );
    const grades = @json($grades -> pluck('aisin_grade'));
    const existingStages = @json($stages); // dari controller

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
        Object.entries(positions).forEach(([v, t]) => {
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = t;
            selectEl.appendChild(opt);
        });
    }

    function fillGrades(selectEl) {
        selectEl.innerHTML = '<option value="">-- Select Level --</option>';
        grades.forEach(g => {
            const opt = document.createElement('option');
            opt.value = g;
            opt.textContent = g;
            selectEl.appendChild(opt);
        });
    }

    const THEME_CLASSES = ['theme-blue','theme-green','theme-amber','theme-purple','theme-rose'];
    function applyTheme(stageEl, idx){
        THEME_CLASSES.forEach(c => stageEl.classList.remove(c));
        stageEl.classList.add(THEME_CLASSES[idx % THEME_CLASSES.length]);
        }
    function stageCount(){ return document.querySelectorAll('.stage-card').length; }


    function addStage(data = null) {
        const container = document.getElementById('stages-container');
        const idx = container.querySelectorAll('.stage-card').length;
        if (idx >= 5) { Swal.fire('Batas 5 tahun', 'Maksimal 5 Stage Tahun per ICP.', 'info'); return; }

        const tpl = document.getElementById('stage-template').innerHTML.replaceAll('__S__', idx);
        const wrap = document.createElement('div');
        wrap.innerHTML = tpl.trim();
        const stage = wrap.firstElementChild;

        stage.dataset.sIndex = String(idx);
        container.appendChild(stage);

        // tema + animasi
        applyTheme(stage, idx);
        stage.classList.add('added'); setTimeout(()=>stage.classList.remove('added'), 300);

        // isi opsi
        fillOptions(stage.querySelector('.stage-job'), departments);
        fillPositions(stage.querySelector('.stage-position'));
        fillGrades(stage.querySelector('.stage-level'));

        // tombol
        stage.querySelector('.btn-remove-stage').addEventListener('click', () => {
            stage.remove();
            reindexStages();
        });
        stage.querySelector('.btn-add-detail').addEventListener('click', () => addDetail(stage));

        // guard plan_year unik
        const yearInput = stage.querySelector(`[name="stages[${idx}][plan_year]"]`);
        yearInput.addEventListener('change', ()=>{
            const val = yearInput.value.trim();
            if (!val) return;
            const all = [...document.querySelectorAll('input[name^="stages"][name$="[plan_year]"]')].map(i=>i.value.trim()).filter(Boolean);
            if (all.filter(y=>y===val).length > 1){
                Swal.fire('Plan year duplikat', 'Tahun tersebut sudah dipakai pada stage lain.', 'warning');
                yearInput.value = ''; yearInput.focus();
            }
        });

        // prefill existing (dari controller)
        if (data) {
            stage.querySelector(`[name="stages[${idx}][plan_year]"]`).value   = data.plan_year ?? '';
            stage.querySelector(`[name="stages[${idx}][job_function]"]`).value = data.job_function ?? '';
            stage.querySelector(`[name="stages[${idx}][position]"]`).value     = data.position ?? '';
            stage.querySelector(`[name="stages[${idx}][level]"]`).value        = data.level ?? '';
            (data.details || []).forEach(d => addDetail(stage, d));
            if (!data.details || !data.details.length) addDetail(stage);
        } else {
            // kalau user klik "Add Tahun" baru saat edit → mulai dengan 1 detail kosong
            addDetail(stage);
        }
    }

    function addDetail(stageEl, data = null) {
        const sIndex = Number(stageEl.dataset.sIndex);
        const detailsBox = stageEl.querySelector('.details-container');
        const dIndex = detailsBox.querySelectorAll('.detail-row').length;

        const tpl = document.getElementById('detail-template').innerHTML
            .replaceAll('__S__', sIndex).replaceAll('__D__', dIndex);
        const wrap = document.createElement('div');
        wrap.innerHTML = tpl.trim();
        const row = wrap.firstElementChild;

        row.querySelector('.btn-remove-detail').addEventListener('click', () => {
            row.remove();
            reindexDetails(stageEl);
        });

        if (data) {
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][current_technical]"]`).value = data.current_technical ?? '';
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][current_nontechnical]"]`).value = data.current_nontechnical ?? '';
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][required_technical]"]`).value = data.required_technical ?? '';
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][required_nontechnical]"]`).value = data.required_nontechnical ?? '';
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][development_technical]"]`).value = data.development_technical ?? '';
            row.querySelector(`[name="stages[${sIndex}][details][${dIndex}][development_nontechnical]"]`).value = data.development_nontechnical ?? '';
        }

        detailsBox.appendChild(row);
    }

    function reindexStages() {
        document.querySelectorAll('.stage-card').forEach((stage, sIdx) => {
            stage.dataset.sIndex = String(sIdx);
            stage.querySelectorAll('[name^="stages["]').forEach(el => {
                el.name = el.name.replace(/stages\[\d+]/, `stages[${sIdx}]`);
            });
            reindexDetails(stage);
        });
    }

    function reindexDetails(stage) {
        const sIdx = Number(stage.dataset.sIndex);
        const rows = stage.querySelectorAll('.details-container .detail-row');
        rows.forEach((row, dIdx) => {
            row.querySelectorAll('[name*="[details]"]').forEach(el => {
                el.name = el.name.replace(/stages\[\d+]\[details]\[\d+]/, `stages[${sIdx}][details][${dIdx}]`);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('btn-add-stage').addEventListener('click', () => addStage());

        if (Array.isArray(existingStages) && existingStages.length) {
            existingStages.forEach(s => addStage(s));
        } else {
            addStage(); // fallback
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            // sanitize
            this.querySelectorAll('input[name^="stages["], textarea[name^="stages["]')
                .forEach(el => el.value = (el.value || '').replace(/<[^>]*>/g, '').trim());

            // minimal 1 detail per stage
            for (const stage of document.querySelectorAll('.stage-card')) {
                const details = stage.querySelectorAll('.details-container .detail-row').length;
                if (details === 0) {
                    e.preventDefault();
                    Swal.fire('Oops', 'Setiap tahun minimal punya 1 detail.', 'warning');
                    stage.scrollIntoView({behavior:'smooth', block:'center'});
                    return;
                }
            }
        });
    });


</script>
@endpush
@endsection
