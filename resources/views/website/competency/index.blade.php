@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Competency' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Competency' }}
@endsection

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
                <h3 class="card-title">Competency List</h3>
                <a href="{{ route('competency.create') }}" class="btn btn-success me-2">
                    <i class="fas fa-plus"></i> Add Competency
                </a>
            </div>

            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_competency">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Competency</th>
                            <th>Description</th>
                            <th>Group Competency</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($competencies as $index => $competency)
                            <tr>
                                <td>{{ $competencies->firstItem() + $index }}</td>
                                <td>{{ $competency->name }}</td>
                                <td>{{ $competency->description }}</td>
                                <td>{{ $competency->group_competency_id }}</td>
                                <td>{{ $competency->dept_id }}</td>
                                <td>{{ $competency->role_id }}</td>
                                <td class="text-center">
                                    <a href="{{ route('competency.edit', $competency->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="{{ route('competency.destroy', $competency->id) }}" method="POST" class="d-inline">
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
                                <td colspan="7" class="text-center text-muted">No Competency Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-between">
                    <span>Showing {{ $competencies->firstItem() }} to {{ $competencies->lastItem() }} of {{ $competencies->total() }} entries</span>
                    {{ $competencies->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
