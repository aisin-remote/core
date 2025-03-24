@extends('layouts.root.main')

@section('title', $title ?? 'Emp Competency')
@section('breadcrumbs', $title ?? 'Emp Competency')

@section('main')
    @if (session()->has('success'))
         <script>
             document.addEventListener("DOMContentLoaded", function() {
                 Swal.fire({
                     title: "Sukses!",
                     text: "{{ session('success') }}",
                     icon: "success",
                     confirmButtonText: "OK"
                 });
             });
         </script>
    @endif

    <div id="kt_app_content_container" class="app-container container-fluid">
         <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                   <h3 class="card-title">Emp Competency List</h3>
                   <a href="{{ route('emp_competency.create') }}" class="btn btn-success me-2">
                        <i class="fas fa-plus"></i> Add
                   </a>
              </div>
              <div class="card-body">
                   <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_emp_competency">
                        <thead>
                             <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                  <th>No</th>
                                  <th>Competency ID</th>
                                  <th>Employee ID</th>
                                  <th>Act</th>
                                  <th>Plan</th>
                                  <th>Progress</th>
                                  <th>Weight</th>
                                  <th class="text-center">Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                             @forelse ($empCompetencies as $index => $emp)
                                 <tr>
                                     <td>{{ $index + 1 }}</td>
                                     <td>{{ $emp['competency_id'] }}</td>
                                     <td>{{ $emp['employee_id'] }}</td>
                                     <td>{{ $emp['act'] }}</td>
                                     <td>{{ $emp['plan'] }}</td>
                                     <td>{{ $emp['progress'] }}</td>
                                     <td>{{ $emp['weight'] }}</td>
                                     <td class="text-center">
                                         <a href="{{ route('emp_competency.edit', [$emp['competency_id'], $emp['employee_id']]) }}" class="btn btn-warning btn-sm">
                                             <i class="bi bi-pencil-square"></i> Edit
                                         </a>
                                         <form action="{{ route('emp_competency.destroy', [$emp['competency_id'], $emp['employee_id']]) }}" method="POST" class="d-inline">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">
                                                 <i class="bi bi-trash"></i> Delete
                                             </button>
                                         </form>
                                     </td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td colspan="8" class="text-center text-muted">No Emp Competency Found</td>
                                 </tr>
                             @endforelse
                        </tbody>
                   </table>
              </div>
         </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
