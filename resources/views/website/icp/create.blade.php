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
@if ($errors->any())
<script>
document.addEventListener("DOMContentLoaded", function () {
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

<style>
    /* =========================
   ICP Stage – Neutral High-Contrast
   ========================= */
    :root{
    --stage-border: #3f4a5a;      /* abu-abu kebiruan gelap */
    --stage-head-bg: #1f2937;     /* gray-800 */
    --stage-head-fg: #ffffff;
    --stage-accent: #111827;      /* gray-900 */
    --detail-bg: #f3f4f6;         /* gray-100 */
    --detail-border: #d1d5db;     /* gray-300 */
    --shadow-inset: rgba(63,74,90,.20);
    --shadow-card: rgba(0,0,0,.08);
    --radius-card: 1rem;
    --radius-detail: .65rem;
    --space-card: 1.25rem;
    }

    /* Card utama */
    .stage-card{
    position: relative;
    border: 2.5px solid var(--stage-border);
    border-radius: var(--radius-card);
    background: #fff;
    overflow: hidden;
    box-shadow: 0 6px 18px var(--shadow-card);
    }

    /* Header */
    .stage-head{
    display:flex; align-items:center; justify-content:space-between;
    background: var(--stage-head-bg);
    color: var(--stage-head-fg);
    border-bottom: 2px solid var(--stage-border);
    padding: 1rem 1.25rem;
    font-weight: 700;
    }
    .stage-head strong{ font-size: 1.1rem; }

    /* Body */
    .stage-body{ padding: var(--space-card); }

    /* Detail baris */
    .detail-row{
    background: var(--detail-bg);
    border: 2px solid var(--detail-border);
    border-radius: var(--radius-detail);
    padding: 14px;
    }

    /* Samakan SEMUA tema ke palet netral di atas (biar tidak warna-warni) */
    .stage-card.theme-blue,
    .stage-card.theme-green,
    .stage-card.theme-amber,
    .stage-card.theme-purple,
    .stage-card.theme-rose{
    border-color: var(--stage-border);
    box-shadow: 0 0 0 3px var(--shadow-inset) inset, 0 6px 18px var(--shadow-card);
    }
    .stage-card.theme-blue .stage-head,
    .stage-card.theme-green .stage-head,
    .stage-card.theme-amber .stage-head,
    .stage-card.theme-purple .stage-head,
    .stage-card.theme-rose .stage-head{
    background: var(--stage-head-bg);
    border-bottom-color: var(--stage-border);
    color: var(--stage-head-fg);
    }
    .stage-card.theme-blue::before,
    .stage-card.theme-green::before,
    .stage-card.theme-amber::before,
    .stage-card.theme-purple::before,
    .stage-card.theme-rose::before{
    background: var(--stage-accent);
    }

    /* Aksesibilitas kecil: fokus jelas untuk tombol dalam card */
    .stage-card :is(button, .btn, select, input, textarea):focus-visible{
    outline: 3px solid #000; outline-offset: 2px;
    box-shadow: 0 0 0 4px rgba(0,0,0,.15);
    }

    /* Animasi masuk (opsional) */
    @keyframes popIn { from {transform:scale(.97);opacity:0} to {transform:scale(1);opacity:1} }
    .stage-card.added { animation: popIn .22s ease-out both; }

    /* Opsional: high-contrast mode (aktifkan dengan class .hc-mode pada container) */
    .hc-mode .stage-head{ background:#000; }
    .hc-mode .stage-card{ border-color:#000; }
    .hc-mode .stage-card::before{ background:#000; }
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
                            'GM'          => 'General Manager', 'Act GM'          => 'Act General Manager', 'Manager'      => 'Manager',      'Act Manager'      => 'Act Manager',
                            'Coordinator' => 'Coordinator',     'Act Coordinator' => 'Act Coordinator',     'Section Head' => 'Section Head', 'Act Section Head' => 'Act Section Head',
                            'Supervisor'  => 'Supervisor',      'Act Supervisor'  => 'Act Supervisor',      'Act Leader'   => 'Act Leader',   'Act JP'           => 'Act JP',
                            'Operator'    => 'Operator',        'Direktur'        => 'Direktur',
                            ];
                            @endphp
                            @foreach ($careerTarget as $value => $label)
                            <option value="{{ $value }}" {{ old('career_target', $employee->position ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('career_target')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date Target</label>
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
                <input type="number" min="2000" max="2100" pattern="\d{4}" class="form-control form-control-sm" name="stages[__S__][plan_year]" placeholder="YYYY" style="width:110px" required>
            </div>
            <button type="button" class="btn btn-danger btn-sm btn-remove-stage">Remove</button>
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
                <button type="button" class="btn btn-warning btn-sm btn-add-detail">
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
/* ====== Data dari server (sekali) ====== */
const DEPARTMENTS = @json($departments->map(fn($d)=>['v'=>$d->name,'t'=>$d->name.' — '.$d->company])->values());
const GRADES      = @json($grades->pluck('aisin_grade'));
const POSITIONS   = {
  'GM':'General Manager','Act GM':'Act General Manager','Manager':'Manager','Act Manager':'Act Manager',
  'Coordinator':'Coordinator','Act Coordinator':'Act Coordinator','Section Head':'Section Head','Act Section Head':'Act Section Head',
  'Supervisor':'Supervisor','Act Supervisor':'Act Supervisor','Act Leader':'Act Leader','Act JP':'Act JP',
  'Operator':'Operator','Direktur':'Direktur'
};
const DEFAULTS = {
  plan_year: (new Date()).getFullYear(),
  job_function: @json($employee->job_function ?? ''),
  position:     @json($employee->position ?? ''),
  level:        @json($employee->grade ?? '')
};

/* ====== Helpers isi opsi ====== */
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
  selectEl.innerHTML = '<option value="">Select Position</option>';
  Object.entries(POSITIONS).forEach(([v,t])=>{
    const opt = document.createElement('option'); opt.value=v; opt.textContent=t; selectEl.appendChild(opt);
  });
}
function fillGrades(selectEl){
  selectEl.innerHTML = '<option value="">-- Select Level --</option>';
  GRADES.forEach(g=>{
    const opt = document.createElement('option'); opt.value=g; opt.textContent=g; selectEl.appendChild(opt);
  });
}

/* ====== Utils ====== */
const getStageCards = () => [...document.querySelectorAll('.stage-card')];
const getPlanYearInputs = () => [...document.querySelectorAll('input[name^="stages"][name$="[plan_year]"]')];
const getPlanYears = () => getPlanYearInputs().map(i=>i.value.trim()).filter(Boolean);
function updateAddBtn(){ document.getElementById('btn-add-stage').disabled = getStageCards().length >= 5; }
function scrollToEl(el){ el?.scrollIntoView({behavior:'smooth', block:'center'}); }

/* ====== Reindex ====== */
function reindexDetails(stage){
  const sIdx = Number(stage.dataset.sIndex);
  const rows = stage.querySelectorAll('.details-container .detail-row');
  rows.forEach((row, dIdx)=>{
    row.querySelectorAll('[name*="[details]"]').forEach(el=>{
      el.name = el.name.replace(/stages\[\d+]\[details]\[\d+]/, `stages[${sIdx}][details][${dIdx}]`);
    });
  });
}
/* ====== Reindex (tambahkan applyTheme di akhir loop) ====== */
function reindexStages(){
  getStageCards().forEach((stage, sIdx)=>{
    stage.dataset.sIndex = String(sIdx);
    stage.querySelectorAll('[name^="stages["]').forEach(el=>{
      el.name = el.name.replace(/stages\[\d+]/, `stages[${sIdx}]`);
    });
    reindexDetails(stage);

    // terapkan tema sesuai index baru
    applyTheme(stage, sIdx);
  });
  updateAddBtn();
}

/* ====== Add Detail ====== */
function addDetail(stageEl){
  const sIndex = Number(stageEl.dataset.sIndex);
  const detailsBox = stageEl.querySelector('.details-container');
  const dIndex = detailsBox.querySelectorAll('.detail-row').length;

  const tpl = document.getElementById('detail-template').innerHTML
    .replaceAll('__S__', sIndex).replaceAll('__D__', dIndex);

  const wrap = document.createElement('div'); wrap.innerHTML = tpl.trim();
  const row = wrap.firstElementChild;

  row.querySelector('.btn-remove-detail').addEventListener('click', ()=>{
    row.remove(); reindexDetails(stageEl);
  });

  detailsBox.appendChild(row);
}

/* ====== Themes ====== */
const THEME_CLASSES = ['theme-blue','theme-green','theme-amber','theme-purple','theme-rose'];

function applyTheme(stageEl, idx){
  THEME_CLASSES.forEach(c => stageEl.classList.remove(c));
  stageEl.classList.add(THEME_CLASSES[idx % THEME_CLASSES.length]);
}

function addStage(){
  const container = document.getElementById('stages-container');
  const idx = container.querySelectorAll('.stage-card').length;
  if (idx >= 5) return;

  const tpl = document.getElementById('stage-template').innerHTML.replaceAll('__S__', idx);
  const wrap = document.createElement('div'); wrap.innerHTML = tpl.trim();
  const stage = wrap.firstElementChild;

  stage.dataset.sIndex = String(idx);
  container.appendChild(stage);

  /* (*) terapkan tema + animasi */
  applyTheme(stage, idx);
  stage.classList.add('added');
  setTimeout(()=>stage.classList.remove('added'), 300);

  // isi dropdown, tombol, guard, prefill, dll (tetap sama)
  fillOptions(stage.querySelector('.stage-job'), DEPARTMENTS);
  fillPositions(stage.querySelector('.stage-position'));
  fillGrades(stage.querySelector('.stage-level'));

  stage.querySelector('.btn-remove-stage').addEventListener('click', ()=>{
    stage.remove(); reindexStages();
  });
  stage.querySelector('.btn-add-detail').addEventListener('click', ()=> addDetail(stage));

  const yearInput = stage.querySelector(`[name="stages[${idx}][plan_year]"]`);
  yearInput.addEventListener('change', ()=>{
    const val = yearInput.value.trim();
    if (!val) return;
    const years = getPlanYears();
    if (years.filter(y=>y===val).length > 1){
      Swal.fire('Plan year duplikat', 'Tahun tersebut sudah dipakai pada stage lain.', 'warning');
      yearInput.value = '';
      yearInput.focus();
    }
  });

  if (idx === 0){
    yearInput.value = DEFAULTS.plan_year;
    stage.querySelector(`[name="stages[${idx}][job_function]"]`).value = DEFAULTS.job_function || '';
    stage.querySelector(`[name="stages[${idx}][position]"]`).value     = DEFAULTS.position || '';
    stage.querySelector(`[name="stages[${idx}][level]"]`).value        = DEFAULTS.level || '';
  }

  addDetail(stage);
  updateAddBtn();
}

/* ====== Init + submit guard ====== */
document.addEventListener('DOMContentLoaded', ()=>{
  // tombol add tahun
  document.getElementById('btn-add-stage').addEventListener('click', addStage);

  // buat 1 stage saat load
  addStage();

  // defaultkan tanggal jika kosong
  const dateEl = document.getElementById('date');
  if (dateEl && !dateEl.value){
    const d = new Date();
    const mm = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    dateEl.value = `${d.getFullYear()}-${mm}-${dd}`;
  }

  // sebelum submit: cek & sanitize
  const form = document.querySelector('form[action="{{ route('icp.store') }}"]');
  form.addEventListener('submit', (e)=>{
    // sanitize
    form.querySelectorAll('input[name^="stages["], textarea[name^="stages["]').forEach(el=>{
      el.value = (el.value || '').replace(/<[^>]*>/g,'').trim();
    });

    const stages = getStageCards();
    if (stages.length === 0){
      e.preventDefault(); Swal.fire('Oops','Minimal 1 tahun harus ditambahkan.','warning'); return;
    }
    const years = getPlanYears();
    if (years.length !== stages.length){
      e.preventDefault(); Swal.fire('Oops','Setiap Stage Tahun wajib diisi 4 digit.','warning'); return;
    }
    if (new Set(years).size !== years.length){
      e.preventDefault(); Swal.fire('Oops','Plan year tidak boleh duplikat.','warning'); return;
    }
    for (const stage of stages){
      const count = stage.querySelectorAll('.details-container .detail-row').length;
      if (count === 0){
        e.preventDefault();
        Swal.fire('Oops','Setiap tahun minimal punya 1 detail.','warning');
        scrollToEl(stage); return;
      }
    }
  });
});
</script>

@endsection
