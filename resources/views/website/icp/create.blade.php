@extends('layouts.root.main')

@section('title', $title ?? 'Icp')
@section('breadcrumbs', $title ?? 'Icp')

@section('main')
@if (session()->has('success'))
<script>
    document.addEventListener("DOMContentLoaded", () => Swal.fire({
        title: "Sukses!"
        , text: @json(session('success'))
        , icon: "success"
    }));

</script>
@endif
@if (session()->has('error'))
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

</style>

<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-fluid">
        <form action="{{ route('icp.store') }}" method="POST">
            @csrf

            {{-- HEADER: cuma 3 field --}}
            <div class="card p-4 shadow-sm rounded-3 mb-4">
                <h3 class="text-center fw-bold mb-4">Individual Career Plan</h3>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control form-select-sm" value="{{ $employee->name }}" disabled>
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Aspiration</label>
                        <textarea name="aspiration" class="form-control form-select-sm" rows="3" required>{{ old('aspiration') }}</textarea>
                        @error('aspiration')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Career Target</label>
                        <select name="career_target" id="career_target" class="form-select form-select-sm">
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
                            <option value="{{ $value }}" {{ old('career_target', $employee->position ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('career_target')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="date" class="form-control form-select-sm">
                    </div>
                </div>
            </div>

            {{-- DEVELOPMENT STAGE (per Tahun) --}}
            <div class="card p-4 shadow-sm rounded-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="fw-bold mb-0">Development Stage</h3>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add-stage">
                        <i class="bi bi-plus-lg"></i> Add Tahun
                    </button>
                </div>

                <div id="stages-container" class="mt-3 d-grid gap-3"></div>

                {{-- Saran kompetensi (untuk autocomplete optional) --}}
                <datalist id="techList">
                    @foreach($technicalCompetencies as $tc)
                    <option value="{{ $tc->competency }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="text-end mt-4">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save
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
                <input type="number" min="2000" max="2100" class="form-control form-control-sm" name="stages[__S__][plan_year]" placeholder="YYYY" style="width:110px" required>
            </div>
            <button type="button" class="btn btn-light btn-sm btn-remove-stage">Remove</button>
        </div>

        <div class="stage-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Job Function</label>
                    <select class="form-select form-select-sm stage-job" name="stages[__S__][job_function]" required>
                        <option value="">Select Job Function</option>
                        <!-- akan diisi server-side via data-options, atau inject via JS -->
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
                <button type="button" class="btn btn-outline-primary btn-sm btn-add-detail">
                    <i class="bi bi-plus"></i> Add Detail
                </button>
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

<script>
// === helper isi opsi (tetap sama pun nggak masalah) ===
function fillOptions(selectEl, items, valueKey='v', textKey='t'){
  selectEl.innerHTML = '<option value="">Select</option>';
  items.forEach(it=>{
    const opt = document.createElement('option');
    opt.value = valueKey ? it[valueKey] : it;
    opt.textContent = textKey ? it[textKey] : it;
    selectEl.appendChild(opt);
  });
}
function fillPositions(selectEl){
  const positions = {
    'GM':'General Manager','Act GM':'Act General Manager','Manager':'Manager','Act Manager':'Act Manager',
    'Coordinator':'Coordinator','Act Coordinator':'Act Coordinator','Section Head':'Section Head','Act Section Head':'Act Section Head',
    'Supervisor':'Supervisor','Act Supervisor':'Act Supervisor','Act Leader':'Act Leader','Act JP':'Act JP',
    'Operator':'Operator','Direktur':'Direktur'
  };
  selectEl.innerHTML = '<option value="">Select Position</option>';
  Object.entries(positions).forEach(([v,t])=>{
    const opt = document.createElement('option'); opt.value=v; opt.textContent=t; selectEl.appendChild(opt);
  });
}
function fillGrades(selectEl, grades){
  selectEl.innerHTML = '<option value="">-- Select Level --</option>';
  grades.forEach(g=>{
    const opt=document.createElement('option'); opt.value=g; opt.textContent=g; selectEl.appendChild(opt);
  });
}

// === ADD STAGE ===
function addStage(){
  const container = document.getElementById('stages-container');
  const tpl = document.getElementById('stage-template').innerHTML;
  const idx = container.querySelectorAll('.stage-card').length;

  // pasang template dengan placeholder __S__ sementara
  const wrap = document.createElement('div');
  wrap.innerHTML = tpl.replaceAll('__S__', idx).trim();
  const stage = wrap.firstElementChild;

  // simpan index di dataset dan APPEND DULU ke DOM!
  stage.dataset.sIndex = String(idx);
  container.appendChild(stage);

  // inject options setelah append
  const departments = @json($departments->map(fn($d)=>['v'=>$d->name,'t'=>$d->name.' — '.$d->company]));
  const grades = @json($grades->pluck('aisin_grade'));
  fillOptions(stage.querySelector('.stage-job'), departments);
  fillPositions(stage.querySelector('.stage-position'));
  fillGrades(stage.querySelector('.stage-level'), grades);

  // bind tombol remove & add detail
  stage.querySelector('.btn-remove-stage').addEventListener('click', ()=>{
    stage.remove();
    reindexStages();
  });
  stage.querySelector('.btn-add-detail').addEventListener('click', ()=> addDetail(stage));

  // tambahkan 1 detail awal
  addDetail(stage);
}

// === ADD DETAIL ===
function addDetail(stageEl){
  // Ambil index stage dari data, jangan cari lewat DOM indexOf
  const sIndex = Number(stageEl.dataset.sIndex);
  const detailsBox = stageEl.querySelector('.details-container');
  const dIndex = detailsBox.querySelectorAll('.detail-row').length;

  const tpl = document.getElementById('detail-template').innerHTML
    .replaceAll('__S__', sIndex)
    .replaceAll('__D__', dIndex);

  const wrap = document.createElement('div');
  wrap.innerHTML = tpl.trim();
  const row = wrap.firstElementChild;

  row.querySelector('.btn-remove-detail').addEventListener('click', ()=>{
    row.remove();
    reindexDetails(stageEl);
  });

  detailsBox.appendChild(row);
}

// === REINDEX SAAT HAPUS STAGE/DETAIL ===
function reindexStages(){
  document.querySelectorAll('.stage-card').forEach((stage, sIdx)=>{
    stage.dataset.sIndex = String(sIdx);

    // perbarui semua name yg dimiliki stage-level & detail
    stage.querySelectorAll('[name^="stages["]').forEach(el=>{
      el.name = el.name.replace(/stages\[\d+]/, `stages[${sIdx}]`);
    });

    reindexDetails(stage);
  });
}

function reindexDetails(stage){
  const sIdx = Number(stage.dataset.sIndex);
  const rows = stage.querySelectorAll('.details-container .detail-row');
  rows.forEach((row, dIdx)=>{
    row.querySelectorAll('[name*="[details]"]').forEach(el=>{
      // ganti segmen stages[old][details][old] -> stages[sIdx][details][dIdx]
      el.name = el.name.replace(/stages\[\d+]\[details]\[\d+]/, `stages[${sIdx}][details][${dIdx}]`);
    });
  });
}

// init
document.addEventListener('DOMContentLoaded', ()=>{
  document.getElementById('btn-add-stage').addEventListener('click', addStage);
});
</script>

@endsection
