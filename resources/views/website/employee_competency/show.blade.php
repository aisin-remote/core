@extends('layouts.root.main')

@section('title', 'Employee Competency Details')

@section('main')
<style>
    /* Tambahkan style ini */
    .profile-horizontal {
        display: flex;
        align-items: center;
        gap: 2rem;
        padding: 1.5rem;
    }
    .profile-image {
        flex: 0 0 120px;
        height: 120px;
        width: 120px;
        border-radius: 12px;
        background-size: cover;
        background-position: center;
        border: 2px solid #ddd;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        background-color: #f5f5f5;
    }
    .profile-info {
        flex: 1;
    }
    .compact-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
</style>

<div id="kt_app_content_container" class="app-container container-fluid">
    <div class="container mt-4">
        <!-- Profile Section Horizontal -->
        <div class="card mb-5">
            <div class="card-body p-0">
                <div class="profile-horizontal">
                    <div class="profile-image" 
                        style="background-image: {{ $employee->photo ? "url('".asset('storage/'.$employee->photo)."')" : 'none' }};
                              background-color: {{ $employee->photo ? 'transparent' : '#f5f5f5' }};">
                    </div>
                    <div class="profile-info">
                        <h3 class="fw-bolder mb-2">{{ $employee->name }}</h3>
                        <div class="compact-details">
                            <div>
                                <div class="text-muted small">NPK</div>
                                <div class="fw-bold">{{ $employee->npk }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Position</div>
                                <div class="fw-bold">{{ $employee->position }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Department</div>
                                <div class="fw-bold">{{ $employee->department?->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Competency Table -->
        <div class="card">
            
            <div class="card-body p-0">
                <table class="table table-bordered m-0">
                    <thead class="bg-light-primary">
                        <tr>
                            <th class="text-center">Competency</th>
                            <th class="text-center">Weight</th>
                            <th class="text-center">Plan</th>
                            <th class="text-center">Act</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Due Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employee->employeeCompetencies as $ec)
                            <tr>
                                <td>{{ $ec->competency->name ?? '-' }}</td>
                                <td class="text-center">{{ $ec->competency->weight }}</td>
                                <td class="text-center">{{ $ec->competency->plan }}</td>
                                <td class="text-center">{{ $ec->act }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $ec->status ? 'bg-success' : 'bg-warning' }}">
                                        {{ $ec->status ? 'Approve' : 'Waiting' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($ec->due_date)->format('M Y') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <form method="POST" action="{{ route('employeeCompetencies.destroy', $ec->id) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger delete-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="bi bi-journal-x"></i>
                                    <h5 class="text-muted mt-2">Belum ada kompetensi</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-end mt-4">
            <a href="{{ route('employeeCompetencies.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle"></i> Back
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Konfirmasi Hapus
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Hapus Kompetensi?',
                    text: "Data tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.closest('form').submit();
                    }
                });
            });
        });
    });
</script>
@endpush
