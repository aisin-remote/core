@extends('layouts.root.main')

@push('custom-css')
<style>
  #evaluationModal .modal-content {
  border-radius: 15px; /* Rounded corners for a modern look */
}

#evaluationModal .modal-header {
  background-color: #f8f9fa; /* Light background for header */
  padding: 1rem 1.5rem; /* Add padding for better spacing */
}

#evaluationModal .modal-title {
  font-size: 1.25rem; /* Larger font size for title */
  font-weight: 600; /* Bold title */
}

#evaluationModal .list-group-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border: 1px solid #ddd; /* Add border for separation */
  border-radius: 8px; /* Round corners for list items */
  margin-bottom: 1rem;
  background-color: #ffffff;
}

#evaluationModal .list-group-item:hover {
  background-color: #f1f1f1; /* Hover effect for better interaction */
}

#evaluationModal .badge {
  font-size: 0.875rem;
  padding: 0.3rem 0.6rem;
  border-radius: 12px; /* Rounded badges */
}

#evaluationModal .badge.bg-success {
  background-color: #d1e7dd;
  color: #0f5132;
}

#evaluationModal .badge.bg-warning {
  background-color: #fff3cd;
  color: #664d03;
}

#evaluationModal #emptyCompetencies {
  text-align: center;
  font-size: 1.1rem;
  color: #888; /* Softer color for "no competencies found" */
}

#evaluationModal .modal-footer {
  border-top: 1px solid #ddd;
  padding: 0.75rem 1.5rem;
}

#evaluationModal .modal-footer .btn-outline-secondary {
  font-size: 0.875rem;
  font-weight: 500;
}

  #empCompTable,
  #empCompTable th,
  #empCompTable td {
    border: none !important;
  }
  /* Kalau masih ada border di <table>, override juga */
  #empCompTable {
    border: none !important;
  }

  /* 2) Sticky & layout tetap sama */
  .table-responsive { overflow-x: visible; }
  #empCompTable {
    width: 100%;
    border-collapse: separate;
    table-layout: fixed;
  }
  /* No & Employee */
  .col-no  { width: 60px;  min-width: 60px; }
  .col-emp { width: 200px; min-width: 200px; }
  .sticky-col {
    position: sticky; left: 0; z-index: 10; background: white;
  }
  .col-no  { left: 0; }
  .col-emp { left: 60px; }
  .col-act { width: 120px; min-width: 120px; }
  .sticky-action {
    position: sticky; right: 0; z-index: 10; background: white;
  }

  /* 3) Kompetency scroll-per-row */
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
    justify-content: space-between;
    align-items: center;
    margin: 0.5rem;
    width: 100px;
    height: 120px;
  }
  .comp-icon { width: 90px; height: 90px; margin: 0; }
  .comp-name {
    font-size: 0.85rem;
    text-align: center;
    white-space: normal;
    word-break: break-word;
    line-height: 1.2;
    margin-top: 0.25rem;
    max-height: 2.4em;
    overflow: hidden;
  }

  /* 4) Center “–” jika tidak ada competency */
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
        <h3 class="card-title">Employee List</h3>
        <div class="d-flex align-items-center">
          <input type="text" id="searchInputEmployee" class="form-control me-2" placeholder="Search Employee..."
              style="width: 200px;">
          <button type="button" class="btn btn-primary me-3" id="searchButton">
              <i class="fas fa-search"></i> Search
          </button>
      </div>
      </div>
      <div class="card-body">
        {{-- Position Tabs --}}
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8" id="positionTabs">
          <li class="nav-item">
            <a class="nav-link active" href="#" data-position="all">All</a>
          </li>
          @foreach($positionsAllowed as $pos)
            <li class="nav-item">
              <a class="nav-link" href="#" data-position="{{ $pos }}">{{ $pos }}</a>
            </li>
          @endforeach
        </ul>
        {{-- Group Tabs --}}
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8" id="groupTabs">
          @foreach($groups as $grp)
            <li class="nav-item">
              <a class="nav-link {{ $grp==='Basic'?'active':'' }}" href="#" data-group="{{ $grp }}">{{ $grp }}</a>
            </li>
          @endforeach
        </ul>

        <div class="table-responsive">
          <table class="table" id="empCompTable">
            <thead>
              <tr class="text-start text-muted fw-bold fs-7 gs-0">
                <th class="sticky-col col-no">No</th>
                <th class="sticky-col col-emp">Employee</th>
                <th class="text-center">Competencies</th>
                <th class="sticky-action col-act text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @include('website.employee_competency.modal')
  <div class="modal fade" id="evaluationModal" tabindex="-1" aria-labelledby="evaluationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
      <div class="modal-content border-0 shadow-sm">
        <div class="modal-header bg-light py-2">
          <h5 class="modal-title" id="evaluationModalLabel">
            Evaluation for <strong id="modalEmployeeName"></strong>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4 py-3">
          <ul class="list-group list-group-flush">
            <!-- JS akan inject <li> di sini -->
            <li class="list-group-item text-center text-muted" id="emptyCompetencies" style="display: none;">
              – No competencies found –
            </li>
          </ul>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>  
@endsection

@push('scripts')
<script>
  let currentEmployeeId, currentEmployeeName;
  document.addEventListener('click', e => {
  if (!e.target.closest('.evaluation-btn')) return;
  const btn = e.target.closest('.evaluation-btn');
  const empId = btn.dataset.employeeId;
  const emp = employees.find(x => x.id == empId);
  const ul  = document.querySelector('#evaluationModal .list-group');
  ul.querySelectorAll('li.list-group-item:not(#emptyCompetencies)').forEach(n => n.remove());

  document.getElementById('modalEmployeeName').textContent = emp.name;
  
  if (!emp.comps.length) {
    document.getElementById('emptyCompetencies').style.display = '';
  } else {
    document.getElementById('emptyCompetencies').style.display = 'none';
    emp.comps.forEach(c => {
      const li = document.createElement('li');
      li.className = 'list-group-item';
      li.innerHTML = `
        <div>
          <div class="fw-medium">${c.name}</div>
          <small class="text-muted">${c.group}</small>
        </div>
        <div class="text-end">
          <span class="badge ${ (c.act === 2 || c.act === 3) ? 'bg-success' : 'bg-warning' } me-2">
            ${ (c.act === 2 || c.act === 3) ? 'Passes' : 'Not Pass' }
          </span>
          <a href="/evaluation/view/${c.employee_competency_id}" class="text-decoration-none">
              View
          </a>
        </div>`;
      ul.append(li);
    });
  }
  
  new bootstrap.Modal(document.getElementById('evaluationModal')).show();
});
const employees    = @json($matrixData);
const baseShowUrl  = "{{ route('employeeCompetencies.show', ':id') }}";
let currentPosition='all', currentGroup='Basic';

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

function render() {
  const tbody = document.querySelector('#empCompTable tbody');
  tbody.innerHTML = '';

  // filter posisi + search
  let data = employees.filter(e => {
    if (currentPosition === 'all') return true;
    return e.position === currentPosition || e.position === `Act ${currentPosition}`;
  });
  const term = document.getElementById('searchInputEmployee').value.trim().toLowerCase();
  if (term) data = data.filter(e => e.name.toLowerCase().includes(term));

  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">No records found</td></tr>`;
    return;
  }

  data.forEach((e,i) => {
    // bangun list competency
    const comps = e.comps
      .filter(c => currentGroup==='all' || c.group===currentGroup)
      .map(c => `
        <div class="comp-cell">
          <div class="comp-name">${c.name}</div>
          ${makeIcon(c.act, c.plan)}
        </div>
      `).join('') || '<span class="text-muted">–</span>';

    const showUrl = baseShowUrl.replace(':id', e.id);
    tbody.insertAdjacentHTML('beforeend', `
      <tr>
        <td class="sticky-col col-no">${i+1}</td>
        <td class="sticky-col col-emp">${e.name}</td>
        <td class="comp-wrapper">${comps}</td>
        <td class="sticky-action col-act text-center">
          <a href="${showUrl}" class="btn btn-info btn-sm me-1">
            <i class="fas fa-eye"></i>
          </a>
          <button type="button"
                  class="btn btn-success btn-sm checksheet-btn"
                  data-employee-id="${e.id}"
                  data-npk="${e.npk||''}"
                  data-position="${e.position}">
            <i class="fas fa-file-alt"></i>
          </button>
          <button type="button"
                  class="btn btn-primary btn-sm evaluation-btn"
                  data-employee-id="${e.id}">
            <i class="fas fa-clipboard-check"></i>
          </button>
        </td>
      </tr>
    `);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  // posisi tabs
  document.querySelectorAll('#positionTabs .nav-link').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      currentPosition = a.dataset.position;
      document.querySelectorAll('#positionTabs .nav-link').forEach(x=>x.classList.remove('active'));
      a.classList.add('active');
      render();
    });
  });
  // grup tabs
  document.querySelectorAll('#groupTabs .nav-link').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      currentGroup = a.dataset.group;
      document.querySelectorAll('#groupTabs .nav-link').forEach(x=>x.classList.remove('active'));
      a.classList.add('active');
      render();
    });
  });
  // search
  document.getElementById('searchButton').addEventListener('click', render);
  document.getElementById('searchInputEmployee').addEventListener('keyup', e => {
    if (e.key === 'Enter') render();
  });

  render();
});
</script>
@endpush
