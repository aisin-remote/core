@extends('layouts.root.main')

@section('title', 'My Skill Detail')

@section('main')
<style>
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
        <!-- Profile Section Horizontal (User Info) -->
        <div class="card mb-5">
            <div class="card-body p-0">
                <div class="profile-horizontal">
                    <div class="profile-image" 
                        style="background-image: {{ Auth::user()->photo ? "url('".asset('storage/'.Auth::user()->photo)."')" : 'none' }};
                              background-color: {{ Auth::user()->photo ? 'transparent' : '#f5f5f5' }};">
                    </div>
                    <div class="profile-info">
                        <h3 class="fw-bolder mb-2">{{ Auth::user()->name }}</h3>
                        <div class="compact-details">
                            <div>
                                <div class="text-muted small">Email</div>
                                <div class="fw-bold">{{ Auth::user()->email }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Position</div>
                                <div class="fw-bold">{{ Auth::user()->employee?->position ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Department</div>
                                <div class="fw-bold">{{ Auth::user()->employee?->department?->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail One Skill (EmployeeCompetency) -->
        <div class="card">
            <div class="card-header">
                <h4>Competency: {{ $employeeCompetency->competency->name }}</h4>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered m-0">
                    <thead class="bg-light-primary">
                        <tr>
                            <th class="text-center">Competency</th>
                            <th class="text-center">Group</th>
                            <th class="text-center">Weight</th>
                            <th class="text-center">Plan</th>
                            <th class="text-center">Act</th>
                            <th class="text-center">Status Level 1</th>
                            <th class="text-center">Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $employeeCompetency->competency->name }}</td>
                            <td class="text-center">{{ $employeeCompetency->competency->group_competency->name }}</td>
                            <td class="text-center">{{ $employeeCompetency->competency->weight }}</td>
                            <td class="text-center">{{ $employeeCompetency->competency->plan }}</td>
                            <td class="text-center">{{ $employeeCompetency->act }}</td>
                            <td class="text-center">
                                @if($employeeCompetency->act == 1)
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-warning">Waiting</span>
                                @endif
                            </td>    
                            <td class="text-center">{{ \Carbon\Carbon::parse($employeeCompetency->due_date)->format('M Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- History of uploaded files --}}
        @if($employeeCompetency->evidenceHistories->count())
        <div class="card mb-4">
            <div class="card-header">
                <h5>File Submission History</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($employeeCompetency->evidenceHistories as $history)
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                {{ $history->created_at->format('d M Y H:i') }} – 
                                <strong>{{ $history->actor->name }}</strong> 
                                {{ $history->action === 'approve' ? 'Approved' : 'Unapproved' }}
                            </div>
                            @if($employeeCompetency->file)
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <strong>Latest File</strong> – File utama yang di-submit
                                </div>
                                <a href="{{ asset('storage/' . $employeeCompetency->file) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </li>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="text-end mt-4">
            <a href="{{ route('skillMatrix.index') }}" class="btn btn-secondary">
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
