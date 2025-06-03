@extends('layouts.root.main')

@section('title') {{ $title ?? 'Competency' }} @endsection
@section('breadcrumbs') {{ $title ?? 'Competency' }} @endsection

@section('main')
<div class="container">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title">Competency List</h3>
      <div class="d-flex align-items-center">
        {{-- Form Search --}}
        <form method="GET" action="{{ route('competencies.index') }}" class="d-flex align-items-center me-3">
          <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" style="width:200px;" placeholder="Search…">
          <input type="hidden" name="position" value="{{ request('position', 'Show All') }}">
          <input type="hidden" name="group" value="{{ request('group', 'Show All') }}">
          <button type="submit" class="btn btn-primary me-2">
            <i class="fas fa-search"></i>
          </button>
        </form>
        {{-- Tombol “Add Competency” (modal) --}}
        <button type="button" class="btn btn-primary" id="openAddModal">
          <i class="fas fa-plus"></i> Add Competency
        </button>
      </div>
    </div>

    <div class="card-body">
      {{-- Tab posisi --}}
      <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8" role="tablist" style="cursor:pointer">
        @php
          $jobPositions = [
            'Show All','Operator','Leader','Act Leader','JP','Act JP','Supervisor','Section Head','Coordinator','Manager','GM','Direktur'
          ];
        @endphp

        @foreach ($jobPositions as $job)
          <li class="nav-item" role="presentation">
            <a
              class="nav-link text-active-primary pb-4 {{ request('position', 'Show All') === $job ? 'active' : '' }}"
              href="{{ route('competencies.index', array_merge(request()->query(), ['position' => $job])) }}"
              role="tab"
              aria-selected="{{ request('position', 'Show All') === $job ? 'true' : 'false' }}"
            >
              {{ $job }}
            </a>
          </li>
        @endforeach
      </ul>

      {{-- Tab group competency --}}
    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8" role="tablist" style="cursor:pointer">
        <li class="nav-item" role="presentation">
            <a
                class="nav-link text-active-primary pb-4 {{ $group === 'Show All' ? 'active' : '' }}"
                href="{{ route('competencies.index', array_merge(request()->query(), ['group' => 'Show All'])) }}"
                role="tab"
            >
                Show All
            </a>
        </li>
        @foreach($groups as $g)
            <li class="nav-item" role="presentation">
                <a
                    class="nav-link text-active-primary pb-4 {{ $group == $g->id ? 'active' : '' }}"
                    href="{{ route('competencies.index', array_merge(request()->query(), ['group' => $g->id])) }}"
                    role="tab"
                >
                    {{ $g->name }}
                </a>
            </li>
        @endforeach
    </ul>

      <div class="tab-content mt-3">
        <div class="tab-pane fade show active" role="tabpanel">
          {{-- Tabel Competency --}}
          <table class="table align-middle table-row-dashed fs-6 gy-5" id="competencyTable">
            <thead>
              <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th>No</th>
                <th>Competency</th>
                <th>Group Competency</th>
                <th>Position</th>
                <th class="text-center">Section</th>
                <th>Department</th>
                <th>Weight</th>
                <th>Plan</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($competencies as $i => $c)
                <tr id="row-competency-{{ $c->id }}">
                  <td>{{ ($competencies->currentPage() - 1) * $competencies->perPage() + $loop->iteration }}</td>
                  <td>{{ $c->name }}</td>
                  <td class="text-center">{{ $c->group_competency->name }}</td>
                  <td class="text-center">{{ $c->position }}</td>
                  <td class="text-center">
                    @if($c->sub_section)
                      {{ $c->sub_section->section->name }}
                    @elseif($c->section)
                      {{ $c->section->name }}
                    @else
                      -
                    @endif
                  </td>
                  <td class="text-center">
                    @if($c->sub_section)
                      {{ $c->sub_section->section->department->name }}
                    @elseif($c->section)
                      {{ $c->section->department->name }}
                    @elseif($c->department)
                      {{ $c->department->name }}
                    @else
                      -
                    @endif
                  </td>
                  <td class="text-center">{{ $c->weight }}</td>
                  <td class="text-center">{{ $c->plan }}</td>
                  <td class="text-center">
                    {{-- Tombol Edit (contoh saja, belum dibahas di sini) --}}
                    <button class="btn btn-sm btn-warning edit-btn" data-id="{{ $c->id }}">
                      <i class="fas fa-edit"></i>
                    </button>

                    {{-- Tombol Delete --}}
                    <button
                      class="btn btn-sm btn-danger delete-btn"
                      data-id="{{ $c->id }}"
                      data-url="{{ route('competencies.destroy', $c->id) }}"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-muted">
                    No competencies found
                    @if($position !== 'Show All')
                      for position: {{ $position }}
                    @endif
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>

          {{-- Pagination --}}
          <div class="d-flex justify-content-end mt-4">
            {{ $competencies
                ->appends([
                  'search' => request('search'),
                  'position' => request('position', 'Show All'),
                  'group' => request('group', 'Show All')
                ])
                ->links('vendor.pagination.bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('website.competency.modal')  
@include('website.competency.update') 
@endsection

@push('scripts')
  {{-- SweetAlert2 --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('openAddModal').addEventListener('click', function() {
      new bootstrap.Modal(document.getElementById('addModal')).show();
    });

    const addForm = document.getElementById('addForm');
    addForm.addEventListener('submit', function(event) {
      event.preventDefault();
      const formData = new FormData(addForm);

      fetch("{{ route('competencies.store') }}", {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
      })
      .then(response => {
        if (! response.ok) {
          return response.json().then(err => {
            throw new Error(err.message || 'Failed to save.');
          });
        }
        return response.json();
      })
      .then(data => {
        Swal.fire({
          icon: 'success',
          title: 'Saved!',
          text: data.message || 'Competency added successfully',
          timer: 1500,
          showConfirmButton: false
        });
        bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
        setTimeout(() => { location.reload() }, 1000);
      })
      .catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: err.message
        });
      });
    });

    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        const deleteBtn   = e.target.closest('.delete-btn');
        if (!deleteBtn) return;

        const competencyId = deleteBtn.getAttribute('data-id');
        const deleteUrl    = deleteBtn.getAttribute('data-url');
        const rowSelector  = '#row-competency-' + competencyId;

        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#3085d6",
          confirmButtonText: "Yes, delete it!",
          cancelButtonText: "Cancel"
        }).then((result) => {
          if (!result.isConfirmed) return;

          fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          })
          .then(response => {
            if (response.status === 404) {
              return Promise.reject(new Error('Data not found'));
            }
            if (response.status === 403) {
              return Promise.reject(new Error('No permission to delete.'));
            }
            if (!response.ok) {
              return response.json().then(errJson => {
                let msg = errJson.message || 'Failed to delete.';
                return Promise.reject(new Error(msg));
              });
            }
            return response.json();
          })
          .then(data => {
            // Hapus baris tabel di DOM
            document.querySelector(rowSelector)?.remove();
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: data.message || 'Competency has been deleted',
              timer: 1500,
              showConfirmButton: false
            });
          })
          .catch(err => {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: err.message
            });
          });
        });
      });
    });
  </script>
@endpush
