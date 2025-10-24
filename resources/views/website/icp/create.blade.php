@extends('layouts.root.main')

@section('title', $title ?? 'Icp')
@section('breadcrumbs', $title ?? 'Icp')

@push('custom-css')
    <style>
        /* =========================
               ICP Stage – Neutral High-Contrast
               ========================= */
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

        .hc-mode .stage-head {
            background: #000
        }

        .hc-mode .stage-card {
            border-color: #000
        }

        .hc-mode .stage-card::before {
            background: #000
        }
    </style>
    <style>
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
            <form action="{{ route('icp.store') }}" method="POST">
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
                                    {{-- $rtcList: [['code'=>'AL','position'=>'Act Leader'], ...] urut terendah→tertinggi --}}
                                    <option value="{{ $rt['code'] }}">{{ $rt['position'] }} ({{ $rt['code'] }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilihan Position & Level di Development Stage dibatasi hingga target
                                ini.</small>
                            @error('career_target_code')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date Target</label>
                            <input type="date" name="date" id="date" class="form-control form-select-sm">
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
                        <!-- Tahun otomatis & readonly -->
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
                            <select class="form-select form-select-sm stage-level" name="stages[__S__][level]" required
                                disabled>
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
                            name="stages[__S__][details][__D__][current_technical]">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Required Tech</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][required_technical]">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Development Technical</label>
                        <select class="form-select form-select-sm tech-select"
                            name="stages[__S__][details][__D__][development_technical]">
                            <option value=""></option>
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
        const DEPARTMENTS = @json($departments->map(fn($d) => ['v'=>$d->name, 't'=>$d->name . ' — ' . $d->company])->values());
        const DIVISIONS  = @json($divisions->map(fn($d) => ['v'=>$d->name, 't'=>$d->name . ' - ' . $d->company])->values());
        const TECHS      = @json($technicalCompetencies->pluck('competency'));
        const COMPANY    = @json($employee->company_name);

        // rtcList dari server (URUT dari terendah→tertinggi)
        const RTC_LIST = @json($rtcList); // [{code:'AL', position:'Act Leader'}, ...]
        // rank map untuk pembatasan (semakin besar index = semakin tinggi)
        const RTC_RANK = Object.fromEntries(RTC_LIST.map((x, i) => [x.code.toUpperCase(), i]));

        const DEFAULTS = {
          plan_year: (new Date()).getFullYear(),
          job_function: @json($employee->job_function ?? '')
        };

        /* ====== Select2 Tech per-stage (mendukung override list) ====== */
        function initTechSelects(scope, techListOverride = null) {
          const base = (techListOverride ?? TECHS ?? []).map(t => ({ id: t, text: t }));

          $(scope).find('.tech-select').each(function () {
            const $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
            $el.select2({
              data: base,
              tags: true,
              placeholder: 'Select or type…',
              allowClear: true,
              width: '100%'
            });

            // restore preset bila ada
            const preset = $el.attr('data-value');
            if (preset && !$el.val()) {
              if (!base.some(x => x.id === preset)) {
                const opt = new Option(preset, preset, true, true);
                $el.append(opt).trigger('change');
              } else {
                $el.val(preset).trigger('change');
              }
            }
          });
        }

        /* ===== Helpers umum ===== */
        function fillOptions(selectEl, items, valueKey = 'v', textKey = 't') {
          selectEl.innerHTML = '<option value="">Select</option>';
          items.forEach(it => {
            const opt = document.createElement('option');
            opt.value = valueKey ? it[valueKey] : it;
            opt.textContent = textKey ? it[textKey] : it;
            selectEl.appendChild(opt);
          });
        }

        /* Job Function berkelompok + tandai sumber di data-source */
        function fillJobs(selectEl) {
          // reset + placeholder
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
              opt.dataset.source = src; // <<— penanda asal
              og.appendChild(opt);
            });
            selectEl.appendChild(og);
          };

          makeGroup('Departments', DEPARTMENTS, 'department');
          makeGroup('Divisions',   DIVISIONS,   'division');
        }

        /* Isi posisi dengan batas career target (≤ target) */
        function fillPositionsLimited(selectEl, careerCode) {
          selectEl.innerHTML = '<option value="">Select Position</option>';
          if (!careerCode || !(careerCode.toUpperCase() in RTC_RANK)) {
            selectEl.disabled = true;
            return;
          }
          const targetRank = RTC_RANK[careerCode.toUpperCase()];
          RTC_LIST.forEach(rt => {
            if (RTC_RANK[rt.code.toUpperCase()] <= targetRank) {
              const opt = document.createElement('option');
              opt.value = rt.code;
              opt.textContent = `${rt.position} (${rt.code})`;
              selectEl.appendChild(opt);
            }
          });
          selectEl.disabled = false;
        }

        /* Load levels dari backend untuk posisi tertentu (divalidasi ≤ career target) */
        async function loadLevels(levelSelect, positionCode, careerCode) {
          levelSelect.innerHTML = '<option value="">Loading...</option>';
          levelSelect.disabled = true;
          try {
            const qs = new URLSearchParams({
              position_code: positionCode,
              career_target_code: careerCode
            });
            const res = await fetch(`{{ route('icp.levels') }}?` + qs.toString(), {
              headers: { 'Accept': 'application/json' }
            });
            const js = await res.json();
            if (!js.ok) {
              levelSelect.innerHTML = `<option value="">${js.message || 'Level unavailable'}</option>`;
              return;
            }
            levelSelect.innerHTML = '<option value="">-- Select Level --</option>';
            (js.levels || []).forEach(lv => {
              const opt = document.createElement('option');
              opt.value = lv;
              opt.textContent = lv;
              levelSelect.appendChild(opt);
            });
            levelSelect.disabled = false;
          } catch (e) {
            levelSelect.innerHTML = '<option value="">Failed to load</option>';
          }
        }

        /* ===== Utils ===== */
        const getStageCards = () => [...document.querySelectorAll('.stage-card')];

        function updateAddBtn() {
          document.getElementById('btn-add-stage').disabled = false; // tidak dibatasi; ubah jika perlu
        }

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
          // Perbarui tahun otomatis berurutan mulai tahun sekarang
          const base = DEFAULTS.plan_year;
          getStageCards().forEach((stage, i) => {
            const yearInput = stage.querySelector('.stage-year');
            yearInput.value = base + i;
          });
        }

        /* ====== Refresh daftar tech berdasarkan Job Function stage ====== */
        async function refreshStageTechs(stageEl, source, jobName) {
          // beri feedback kosong sementara
          initTechSelects(stageEl.querySelector('.details-container'), []);

          try {
            const qs = new URLSearchParams({
              source,              // 'department' | 'division'
              name: jobName,       // nama job function (dept/div name)
              company: COMPANY
            });
            const res = await fetch(`{{ route('icp.techs') }}?` + qs.toString(), {
              headers: { 'Accept': 'application/json' }
            });
            const js = await res.json();
            const items = (js.ok ? js.items : []) || [];

            // cache ke stage (dipakai add detail berikutnya)
            stageEl._techList = items;

            // re-init semua tech-select di stage pakai daftar baru
            initTechSelects(stageEl.querySelector('.details-container'), items);
          } catch (e) {
            stageEl._techList = [];
            initTechSelects(stageEl.querySelector('.details-container'), []);
          }
        }

        /* ====== Detail row ====== */
        function addDetail(stageEl) {
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

          detailsBox.appendChild(row);

          // gunakan tech list spesifik stage bila tersedia, fallback ke global TECHS
          initTechSelects(row, stageEl._techList ?? TECHS);
        }

        /* ====== Stage card ====== */
        function addStage() {
          const container = document.getElementById('stages-container');
          const idx = container.querySelectorAll('.stage-card').length;

          const tpl = document.getElementById('stage-template').innerHTML.replaceAll('__S__', idx);
          const wrap = document.createElement('div');
          wrap.innerHTML = tpl.trim();
          const stage = wrap.firstElementChild;

          // index & inject
          stage.dataset.sIndex = String(idx);
          container.appendChild(stage);

          applyTheme(stage, idx);
          stage.classList.add('added');
          setTimeout(() => stage.classList.remove('added'), 300);

          // isi Job Function (dengan optgroup & data-source)
          const jobSel = stage.querySelector('.stage-job');
          fillJobs(jobSel);

          // siapkan hidden input untuk kirim sumber job
          let jobSrc = stage.querySelector('.job-source');
          if (!jobSrc) {
            jobSrc = document.createElement('input');
            jobSrc.type = 'hidden';
            jobSrc.className = 'job-source';
            jobSrc.name = `stages[${idx}][job_source]`; // ikut index stage
            stage.appendChild(jobSrc);
          }

          // posisi & level
          const careerSelect = document.getElementById('career_target');
          const posSel = stage.querySelector('.stage-position');
          const lvlSel = stage.querySelector('.stage-level');

          // set tahun otomatis (readonly)
          const yearInput = stage.querySelector('.stage-year');
          yearInput.readOnly = true;
          yearInput.value = DEFAULTS.plan_year + idx;

          // tombol remove stage
          stage.querySelector('.btn-remove-stage').addEventListener('click', () => {
            stage.remove();
            reindexStages();
          });

          // tombol add detail
          stage.querySelector('.btn-add-detail').addEventListener('click', () => addDetail(stage));

          // saat posisi berubah → ambil level dari backend
          posSel.addEventListener('change', () => {
            const pos = posSel.value;
            const career = careerSelect.value;
            if (!pos || !career) {
              lvlSel.innerHTML = '<option value="">-- Select Level --</option>';
              lvlSel.disabled = true;
              return;
            }
            loadLevels(lvlSel, pos, career);
          });

          // Job Function berubah → refresh competencies
          stage._techList = []; // awalnya kosong
          jobSel.addEventListener('change', () => {
            const opt = jobSel.options[jobSel.selectedIndex];
            const source = opt?.dataset.source || ''; // 'department' / 'division'
            const jobName = jobSel.value || '';

            // sinkronkan hidden job_source
            jobSrc.value = source;

            if (!source || !jobName) {
              stage._techList = [];
              initTechSelects(stage.querySelector('.details-container'), []);
              return;
            }
            refreshStageTechs(stage, source, jobName);
          });

          // detail awal minimal 1
          addDetail(stage);
          updateAddBtn();

          // pertama kali render, isi posisi sesuai target (kalau sudah dipilih)
          if (careerSelect.value) {
            fillPositionsLimited(posSel, careerSelect.value);
          }

          // (Opsional) preset job function default karyawan
          if (DEFAULTS.job_function) {
            const match = [...jobSel.options].find(o => o.value === DEFAULTS.job_function);
            if (match) {
              jobSel.value = DEFAULTS.job_function;
              jobSel.dispatchEvent(new Event('change'));
            }
          }
        }

        /* ====== Lifecycle halaman ====== */
        document.addEventListener('DOMContentLoaded', () => {
          // tombol add tahun
          document.getElementById('btn-add-stage').addEventListener('click', addStage);

          // buat 1 stage saat load (tahun sekarang)
          addStage();

          // defaultkan tanggal jika kosong
          const dateEl = document.getElementById('date');
          if (dateEl && !dateEl.value) {
            const d = new Date();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            dateEl.value = `${d.getFullYear()}-${mm}-${dd}`;
          }

          // ketika career target berubah: reset semua posisi/level & batasi opsi
          const careerSelect = document.getElementById('career_target');
          careerSelect.addEventListener('change', () => {
            const career = careerSelect.value;
            getStageCards().forEach(stage => {
              const posSel = stage.querySelector('.stage-position');
              const lvlSel = stage.querySelector('.stage-level');
              fillPositionsLimited(posSel, career);
              posSel.value = '';
              lvlSel.innerHTML = '<option value="">-- Select Level --</option>';
              lvlSel.disabled = true;
            });
          });

          // sebelum submit: sanitasi ringan & cek minimal 1 detail per stage
          const form = document.querySelector('form[action="{{ route('icp.store') }}"]');
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
            for (const stage of stages) {
              const cnt = stage.querySelectorAll('.details-container .detail-row').length;
              if (cnt === 0) {
                e.preventDefault();
                Swal.fire('Oops', 'Setiap tahun minimal punya 1 detail.', 'warning');
                return;
              }
            }
          });
        });
        </script>

@endsection
