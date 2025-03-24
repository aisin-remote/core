@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Group Competency' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Group Competency' }}
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
                <h3 class="card-title">Group Competency</h3>
                <div class="d-flex align-items-center">
                    <a href="{{ route('group_competency.create') }}" class="btn btn-success me-2">
                        <i class="fas fa-plus"></i> Add
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Nama Group</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($group as $index => $data)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $data->name }}</td>
                                <td>{{ $data->description }}</td>
                                <td class="text-center">
                                    <a href="{{ route('group_competency.edit', $data->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('group_competency.destroy', $data->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm delete-btn" type="submit" onclick="return confirm('Yakin hapus data ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Data tidak ditemukan</td>
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
    <!-- Script tambahan jika diperlukan -->
@endpush
