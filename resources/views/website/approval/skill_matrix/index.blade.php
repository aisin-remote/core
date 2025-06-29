@extends('layouts.root.main')

@section('title', 'Approval Skill Matrix')

@section('breadcrumbs')
    Approval Skill Matrix
@endsection

@section('main')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Approval Skill Matrix</h3>
            <div class="d-flex align-items-center">
                <input type="text" style="width: 200px;" class="form-control me-2" id="searchInput"
                    placeholder="Search..." onkeyup="searchData()">
                <button type="button" class="btn btn-primary me-3" id="searchButton">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Pending Approvals Section -->
            <div class="mb-5">
                <h4 class="mb-3 text-primary">Pending Approvals</h4>
                @if($pending->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Tidak ada evidence untuk di-approve.
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead class="table-light">
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px">No</th>
                                <th>Employee</th>
                                <th>Competency</th>
                                <th>Evidence File</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $i => $ec)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-40px symbol-circle me-3">
                                            <div class="symbol-label bg-light-primary">
                                                <span class="fs-4 text-primary">{{ substr($ec->employee->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $ec->employee->name }}</span>
                                            <span class="text-muted">{{ $ec->employee->nip }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $ec->competency->name }}</span>
                                </td>
                                <td>
                                    <a href="{{ asset('storage/'.$ec->file) }}"
                                        class="btn btn-sm btn-icon btn-light-primary"
                                        download="{{ basename($ec->file) }}"
                                        data-bs-toggle="tooltip" title="Download Evidence">
                                        <i class="fas fa-download fs-4"></i>
                                    </a>
                                    <span class="ms-2">{{ basename($ec->file) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Tombol Approve -->
                                        <button type="button" class="btn btn-sm btn-icon btn-success btn-approve"
                                                data-id="{{ $ec->id }}" 
                                                data-url="{{ route('skillMatrix.approve', $ec->id) }}">
                                            <i class="fas fa-check fs-4"></i>
                                        </button>
                                        
                                        <!-- Tombol Unapprove -->
                                        <button type="button" class="btn btn-sm btn-icon btn-danger btn-unapprove"
                                                data-id="{{ $ec->id }}"
                                                data-url="{{ route('skillMatrix.unapprove', $ec->id) }}">
                                            <i class="fas fa-times fs-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Approval History Section -->
            <div class="mt-7">
                <h4 class="mb-3 text-primary">Approval History</h4>
                @if($history->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Tidak ada histori approval.
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="historyTable">
                        <thead class="table-light">
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px">No</th>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Competency</th>
                                <th>Action</th>
                                <th>File</th>
                                <th class="text-center">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $i => $record)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>
                                    <span class="fw-bold">{{ $record->created_at->format('d M Y') }}</span>
                                    <div class="text-muted">{{ $record->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    @if($record->employeeCompetency->employee ?? false)
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $record->employeeCompetency->employee->name }}</span>
                                            <span class="text-muted">{{ $record->employeeCompetency->employee->nip }}</span>
                                        </div>
                                    </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($record->employeeCompetency->competency ?? false)
                                    <span class="fw-bold">{{ $record->employeeCompetency->competency->name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($record->action == 'approve')
                                        <span class="badge badge-light-success">Approved</span>
                                    @else
                                        <span class="badge badge-light-danger">Unapproved</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Gunakan file langsung dari history record
                                        $evidenceFile = $record->file_name ?? ($record->employeeCompetency->file ?? null);
                                    @endphp
                                    
                                    @if($evidenceFile)
                                        <a href="{{ asset('storage/'.$evidenceFile) }}"
                                          class="btn btn-sm btn-icon btn-light-primary"
                                          download="{{ basename($evidenceFile) }}"
                                          data-bs-toggle="tooltip" title="Download File">
                                            <i class="fas fa-download fs-4"></i>
                                        </a>
                                        <span class="ms-2">{{ basename($evidenceFile) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($record->actor ?? false)
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $record->actor->name }}</span>
                                        </div>
                                    </div>
                                    @else
                                        System
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fungsi untuk pencarian di tabel history
    function searchData() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('historyTable');
        const tr = table.getElementsByTagName('tr');
        
        for (let i = 1; i < tr.length; i++) {
            const td = tr[i].getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < td.length; j++) {
                if (td[j]) {
                    const txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            tr[i].style.display = found ? '' : 'none';
        }
    }
    
    // Aktifkan tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Handle search button click
        document.getElementById('searchButton').addEventListener('click', searchData);
        
        // Handle enter key in search input
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchData();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
    // Handle Approve Button
    document.querySelectorAll('.btn-approve').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const url = this.dataset.url;
            
            Swal.fire({
                title: 'Approve Evidence?',
                text: "Are you sure you want to approve this evidence?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Approve!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim form approve
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

    // Handle Unapprove Button
    document.querySelectorAll('.btn-unapprove').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const url = this.dataset.url;
            
            Swal.fire({
                title: 'Unapprove Evidence?',
                text: "Are you sure you want to unapprove this evidence?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Unapprove!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim form unapprove
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .symbol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        border-radius: 50%;
        width: 40px;
        height: 40px;
    }
    
    .symbol-label {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    
    .badge-light-success {
        background-color: #e8fff3;
        color: #50cd89;
        padding: 0.5rem 0.75rem;
        border-radius: 0.475rem;
    }
    
    .badge-light-danger {
        background-color: #fff5f8;
        color: #f1416c;
        padding: 0.5rem 0.75rem;
        border-radius: 0.475rem;
    }
    
    .btn-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    
    .badge.bg-primary {
        background-color: #3699FF !important;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
    }
</style>
@endpush