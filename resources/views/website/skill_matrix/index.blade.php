@extends('layouts.root.main')

@section('title', 'Skill Matrix')

@section('main')
<style>
    .btn-link {
        max-width: 200px;   
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        vertical-align: middle;
    }
    table td, table th {
        font-size: 14px;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .icon-eye {
        color: #ADD8E6;
        vertical-align: middle;
    }
</style>
<div class="container">
    <!-- Card Competencies -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">My Skill Matrix</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Competency</th>
                        <th class="text-center">Department</th>
                        <th class="text-center">Weight</th>
                        <th class="text-center">Plan</th>
                        <th class="text-center">Act</th>
                        <th class="text-center">Status Training</th>
                        <th class="text-center">Due Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employee->employeeCompetencies as $ec)
                        <tr>
                            <td class="text-center">{{ $ec->competency->name }}</td>
                            <td class="text-center">{{ $ec->competency->department->name }}</td>
                            <td class="text-center">{{ $ec->weight }}</td>
                            <td class="text-center">{{ $ec->plan }}</td>
                            <td class="text-center">{{ $ec->act }}</td>
                            <td class="text-center">
                                @if($ec->status == 1)
                                    Approved
                                @else
                                    Not Approved
                                @endif
                            </td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($ec->due_date)->format('Y F') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    @if($ec->files)
                                        <!-- View File -->
                                        <form method="GET" 
                                              action="{{ asset('storage/' . $ec->files) }}" 
                                              target="_blank"
                                              class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </form>

                                        @if($ec->status == 0)
                                            <!-- Edit File -->
                                            <form method="POST" 
                                                action="{{ route('employeeCompetencies.update', $ec->id) }}" 
                                                enctype="multipart/form-data"
                                                class="ajax-form">
                                                @csrf
                                                @method('PUT')
                                                <label for="edit-file-input-{{ $ec->id }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </label>
                                                <input type="file" 
                                                    name="file" 
                                                    id="edit-file-input-{{ $ec->id }}" 
                                                    style="display: none;">
                                            </form>
                                        @endif
                                    @else
                                        @if($ec->status == 0)
                                            <!-- Upload File -->
                                            <form method="POST" 
                                                    action="{{ route('employeeCompetencies.update', $ec->id) }}" 
                                                    enctype="multipart/form-data"
                                                    class="ajax-form">
                                                @csrf
                                                @method('PUT')
                                                <label for="file-input-{{ $ec->id }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-upload"></i>
                                                </label>
                                                <input type="file" 
                                                        name="file" 
                                                        id="file-input-{{ $ec->id }}" 
                                                        style="display: none;">
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle file input changes
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function(e) {
            const form = this.closest('form');
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.error || 'Terjadi kesalahan',
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengupload file',
                });
            });
        });
    });
});
</script>