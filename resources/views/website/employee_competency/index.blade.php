@extends('layouts.root.main')

@push('custom-css')
<style>
  .badge-circle {
    border-radius: 0.75rem;
    padding: 0.5rem 0.75rem;
    color: white;
  }
  .sticky-col {
    position: sticky;
    left: 0;
    background: white;
    z-index: 2;
  }
</style>
@endpush

@section('main')
  <div id="kt_app_content_container" class="app-container container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Employee List</h3>
        <div class="d-flex align-items-center">
          <input type="text" id="searchInputEmployee" class="form-control me-2"
                 placeholder="Search Employee..." style="width: 200px;">
          <button type="button" class="btn btn-primary me-3" id="searchButton">
            <i class="fas fa-search"></i> Search
          </button>
        </div>
      </div>
      <div class="card-body">
        {{-- Position Tabs --}}
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8"
            id="positionTabs">
          <li class="nav-item"><a class="nav-link active" href="#" data-position="all">Show All</a></li>
          @foreach(['Direktur','GM','Manager','Coordinator','Section Head','Supervisor','Leader','JP','Operator'] as $pos)
            <li class="nav-item">
              <a class="nav-link" href="#" data-position="{{ $pos }}">{{ $pos }}</a>
            </li>
          @endforeach
        </ul>

        {{-- Group Competency Tabs --}}
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8"
            id="groupTabs">
          @foreach($groups as $grp)
            <li class="nav-item">
              <a class="nav-link {{ $grp==='Basic' ? 'active' : '' }}"
                 href="#" data-group="{{ $grp }}">{{ $grp }}</a>
            </li>
          @endforeach
        </ul>

        <div class="table-responsive">
          <table class="table align-middle table-row-dashed fs-6 gy-5" id="empCompTable">
            <thead>
              <tr class="text-start text-muted fw-bold fs-7 gs-0">
                <th class="sticky-col">No</th>
                <th class="sticky-col">Employee Name</th>
                {{-- dynamic competency headers --}}
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="100%" class="text-center text-muted py-5">No data available</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  @include('website.employee_competency.modal')
@endsection

@push('scripts')
<script>
const baseShowUrl = "{{ route('employeeCompetencies.show', ':id') }}";

document.addEventListener('DOMContentLoaded', () => {
  const employees    = @json($matrixData);
  const positionTabs = document.querySelectorAll('#positionTabs .nav-link');
  const groupTabs    = document.querySelectorAll('#groupTabs .nav-link');
  const table        = document.getElementById('empCompTable');
  const searchInput  = document.getElementById('searchInputEmployee');
  const searchButton = document.getElementById('searchButton');
  let currentPosition = 'all', currentGroup = 'Basic';

  function buildColumns(filtered) {
    const cols = [];
    filtered.forEach(e => e.comps.forEach(c => {
      if ((currentGroup==='all' || c.group===currentGroup) && !cols.includes(c.name)) {
        cols.push(c.name);
      }
    }));
    return cols;
  }

  function render() {
    let filtered = employees.filter(e =>
      currentPosition==='all' || e.position===currentPosition
    );
    const term = searchInput.value.toLowerCase();
    if (term) filtered = filtered.filter(e => e.name.toLowerCase().includes(term));

    const comps = buildColumns(filtered);
    const thead = table.tHead.rows[0];
    
    // Hapus kolom dinamis sampai tersisa 3 kolom dasar
    while (thead.cells.length > 3) thead.deleteCell(2);
    
    // Tambahkan header kompetensi sebelum kolom Actions
    comps.forEach(name => {
      const th = document.createElement('th');
      th.textContent = name;
      th.classList.add('text-center','align-middle');
      thead.insertBefore(th, thead.cells[thead.cells.length - 1]);
    });

    const tbody = table.tBodies[0];
    tbody.innerHTML = '';
    
    if (!filtered.length) {
      tbody.innerHTML = `<tr><td colspan="${comps.length + 3}"
        class="text-center text-muted py-3">No matching records</td></tr>`;
      return;
    }

    filtered.forEach((e,i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="sticky-col">${i+1}</td>
        <td class="sticky-col">${e.name}</td>
      `;
      
      // Tambahkan sel kompetensi
      comps.forEach(cn => {
        const ec = e.comps.find(c=>c.name===cn);
        if (ec) {
          const cls = ec.act>=ec.plan?'bg-success':'bg-danger';
          tr.innerHTML += `<td class="text-center">
            <span class="badge-circle ${cls}">${ec.act}</span>
          </td>`;
        } else {
          tr.innerHTML += `<td class="text-center text-muted">-</td>`;
        }
      });

      // Tambahkan tombol aksi
      const showUrl = baseShowUrl.replace(':id', e.id);
      tr.innerHTML += `<td class="text-center">
        <a href="${showUrl}" class="btn btn-sm btn-info me-1">
          <i class="fas fa-eye"></i>
        </a>
        <button
          type="button"
          class="btn btn-sm btn-success me-1 checksheet-btn"
          data-employee-id="${e.id}"
          data-npk="${e.npk||''}"
          data-position="${e.position}">
          <i class="fas fa-file-alt"></i>
        </button>
      </td>`;
      
      tbody.appendChild(tr);
    });
  }

  // tabs
  positionTabs.forEach(a=>a.addEventListener('click', e=>{ 
    e.preventDefault();
    currentPosition = a.dataset.position;
    positionTabs.forEach(x=>x.classList.remove('active'));
    a.classList.add('active');
    render();
  }));
  groupTabs.forEach(a=>a.addEventListener('click', e=>{ 
    e.preventDefault();
    currentGroup = a.dataset.group;
    groupTabs.forEach(x=>x.classList.remove('active'));
    a.classList.add('active');
    render();
  }));

  // search
  searchButton.addEventListener('click', () => render());
  searchInput.addEventListener('keyup', e => { if(e.key==='Enter') render(); });
  render();
});
</script>
@endpush
