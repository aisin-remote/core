@extends('layouts.root.main')

@push('custom-css')
<style>
  #skillTable,
  #skillTable th,
  #skillTable td {
    border: none !important;
  }
  #skillTable {
    border: none !important;
  }
  .table-responsive { overflow-x: visible; }
  #skillTable {
    width: 100%;
    border-collapse: separate;
    table-layout: fixed;
  }
  .col-no   { width: 60px;  min-width: 60px; }
  .col-comp { width: 200px; min-width: 200px; }
  .sticky-col {
    position: sticky; left: 0; z-index: 10; background: white;
  }
  .col-no   { left: 0; }
  .col-comp { left: 60px; }
  .col-act { width: 120px; min-width: 120px; }
  .sticky-action {
    position: sticky; right: 0; z-index: 10; background: white;
  }
  .comp-wrapper {
    display: inline-block;
    overflow-x: auto;
    white-space: nowrap;
    width: 100%;
  }
  .comp-wrapper::-webkit-scrollbar {
    height: 6px;
  }
  .comp-wrapper::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
  }
  .comp-cell {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    margin: 0.5rem;
    width: 100px;
  }
  .comp-icon { width: 90px; height: 90px; margin: 4px 0; }
  .comp-name {
    font-size: 0.85rem;
    text-align: center;
    white-space: normal;
    word-break: break-word;
    line-height: 1.2;
    margin-top: 0.25rem;
    max-height: 3.6em;
  }
  .comp-wrapper > span {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 120px;
    color: #999;
    font-size: 1.2rem;
  }
</style>
@endpush

@section('main')
  <div class="app-container container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">{{ $title ?? 'My Skill Matrix' }}</h3>
        <div class="d-flex align-items-center">
          <input type="text" id="searchInputSkill" class="form-control me-2" placeholder="Search Competency..."
              style="width: 200px;">
          <button type="button" class="btn btn-primary me-3" id="searchButtonSkill">
              <i class="fas fa-search"></i> Search
          </button>
        </div>
      </div>
      <div class="card-body">
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8" id="groupTabs">
          <li class="nav-item"><a class="nav-link active" href="#" data-group="all">Show All</a></li>
          @foreach($groups as $grp)
            <li class="nav-item">
              <a class="nav-link" href="#" data-group="{{ $grp }}">{{ $grp }}</a>
            </li>
          @endforeach
        </ul>

        <div class="table-responsive">
          <table class="table" id="skillTable">
            <thead>
              <tr class="text-start text-muted fw-bold fs-7 gs-0">
                <th class="sticky-col col-no">No</th>
                <th class="sticky-col col-comp">Competency</th>
                <th class="text-center">Group</th>
                <th class="text-center">Position</th>
                <th class="text-center">Progress</th>
                <th class="sticky-action col-act text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              {{-- Akan di‐populate oleh JavaScript --}}
            </tbody>
          </table>
          @foreach($matrixData as $item)
            @if($item['act'] === 0)
              @include('website.skill_matrix.modal', ['id' => $item['employee_competency_id']])
            @endif
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  const matrixData  = @json($matrixData);
  const groups      = @json($groups);
  const baseShowUrl = "{{ route('skillMatrix.show', ':id') }}";

  let currentPosition = 'all';
  let currentGroup    = 'all';

  function makeIcon(act, plan) {
    const colors = ['lightgray','lightgray','lightgray','lightgray'];
    for (let i = 1; i <= plan && i <= 4; i++) colors[i-1] = 'gold';
    for (let i = 1; i <= act  && i <= 4; i++) colors[i-1] = 'red';

    const quads = [
      { d: 'M40,40 L5,40 A35,35 0 0,1 40,5 Z',   x:25, y:28 },
      { d: 'M40,40 L40,5 A35,35 0 0,1 75,40 Z',  x:55, y:28 },
      { d: 'M40,40 L75,40 A35,35 0 0,1 40,75 Z', x:55, y:58 },
      { d: 'M40,40 L40,75 A35,35 0 0,1 5,40 Z',  x:25, y:58 }
    ];
    const paths = quads.map((q,i) =>
      `<path d="${q.d}" fill="${colors[i]}" stroke="black" stroke-width="0.5"/>`
    ).join('');
    const texts = quads.map((q,i) =>
      `<text x="${q.x}" y="${q.y}" font-size="14" text-anchor="middle" fill="black">${i+1}</text>`
    ).join('');
    return `<svg class="comp-icon" viewBox="0 0 80 80">${paths}${texts}</svg>`;
  }

  function renderTable() {
    const tbody = document.querySelector('#skillTable tbody');
    tbody.innerHTML = '';

    let data = matrixData.filter(item =>
      currentPosition === 'all' || item.position === currentPosition
    );

    data = data.filter(item =>
      currentGroup === 'all' || item.group === currentGroup
    );

    const term = document.getElementById('searchInputSkill').value.trim().toLowerCase();
    if (term) {
      data = data.filter(item => item.name.toLowerCase().includes(term));
    }

    if (!data.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center text-muted py-3">No competencies found</td>
        </tr>`;
      return;
    }

    data.forEach((item, idx) => {
      const iconHTML = makeIcon(item.act, item.plan);
      const showUrl = baseShowUrl.replace(':id', item.employee_competency_id);

      tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td class="sticky-col col-no">${idx + 1}</td>
          <td class="sticky-col col-comp">${item.name}</td>
          <td class="text-center">${item.group}</td>
          <td class="text-center">${item.position}</td>
          <td class="text-center">
            <div class="comp-wrapper">
              ${iconHTML}
            </div>
          </td>
          <td class="sticky-action col-act">
            <div class="d-flex justify-content-center align-items-center gap-1">
              <a href="${showUrl}" class="btn btn-info btn-sm me-1">
                <i class="fas fa-eye"></i>
              </a>

              <a href="/skill-matrix/${item.employee_competency_id}/checksheet"
                  class="btn btn-success btn-sm">
                <i class="fas fa-file-alt"></i>
              </a>

              ${item.act === 0
                ? `<button
                    class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#uploadEvidenceModal-${item.employee_competency_id}">
                    <i class="fas fa-upload"></i>
                  </button>`
                : ``
              } 
            </td>
        </tr>
      `);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#positionTabs .nav-link').forEach(a => {
      a.addEventListener('click', e => {
        e.preventDefault();
        currentPosition = a.dataset.position;
        document.querySelectorAll('#positionTabs .nav-link').forEach(x => x.classList.remove('active'));
        a.classList.add('active');
        renderTable();
      });
    });

    document.querySelectorAll('#groupTabs .nav-link').forEach(a => {
      a.addEventListener('click', e => {
        e.preventDefault();
        currentGroup = a.dataset.group;
        document.querySelectorAll('#groupTabs .nav-link').forEach(x => x.classList.remove('active'));
        a.classList.add('active');
        renderTable();
      });
    });

    document.getElementById('searchButtonSkill').addEventListener('click', renderTable);
    document.getElementById('searchInputSkill').addEventListener('keyup', e => {
      if (e.key === 'Enter') renderTable();
    });

    renderTable();
  });
</script>
@endpush