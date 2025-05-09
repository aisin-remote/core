@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Approval' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Approval' }}
@endsection
@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">HAV List</h3>
                <div class="d-flex align-items-center">
                    <form method="GET" class="d-flex align-items-center">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2"
                            placeholder="Search Employee..." style="width: 200px;">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <button type="submit" class="btn btn-primary me-3">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="button" class="btn btn-info me-3" data-bs-toggle="modal"
                            data-bs-target="#importModal">
                            <i class="fas fa-upload"></i>
                            Import
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if (auth()->user()->role == 'HRD')
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                        role="tablist" style="cursor:pointer">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'all' ? 'active' : '' }}"
                            href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            Show All
                        </a>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Direktur' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'Direktur']) }}">
                                Direktur
                            </a>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'GM' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'GM']) }}">GM</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Manager' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'Manager']) }}">Manager</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Section Head' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'Section Head']) }}">Section
                                Head</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Coordinator' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'Coordinator']) }}">Coordinator</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Supervisor' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'Supervisor']) }}">Supervisor</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Leader' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'Leader']) }}">Leader</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'JP' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'JP']) }}">JP</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Operator' ? 'active' : '' }}"
                                href="{{ route('hav.list', ['company' => $company, 'search' => request('search'), 'filter' => 'Operator']) }}">Operator</a>
                        </li>
                    </ul>
                @endif
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($employees as $item)
                            <tr data-position="{{ $item->employee->position }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->employee->npk }}</td>
                                <td>{{ $item->employee->name }}</td>
                                <td>{{ $item->employee->company_name }}</td>
                                <td>{{ $item->employee->position }}</td>
                                <td>{{ $item->employee->department?->name }}</td>
                                <td>{{ $item->employee->grade }}</td>
                                <td>
                                    @if ($item->status == 0)
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif ($item->status == 2)
                                        <span class="badge bg-success">Approved</span>
                                    @elseif ($item->status == 1)
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    STATUS: {{ $item->status }} | TYPE: {{ gettype($item->status) }}
                                </td>




                                <td class="text-center">
                                    {{-- Summary --}}
                                    <a href="{{ url('hav/generate-create', ['id' => $item->employee_id]) }}"
                                        class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('hav.approve', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('hav.reject', $item->id) }}" method="POST" class="d-inline ms-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
<!-- Pastikan jQuery & SweetAlert sudah terpasang -->
<script>
    function confirmApproval(id, action) {
        const isApprove = action === 'approve';
        const title = isApprove ? 'Setujui Data?' : 'Tolak Data?';
        const text = isApprove
            ? 'Data ini akan disetujui dan diteruskan ke tahap selanjutnya.'
            : 'Data ini akan ditolak dan tidak akan diproses.';
        const confirmButton = isApprove ? 'Ya, Setujui' : 'Ya, Tolak';
        const successMessage = isApprove ? 'Data berhasil disetujui.' : 'Data berhasil ditolak.';
        const url = isApprove ? `/hav/approve/${id}` : `/hav/reject/${id}`;

        Swal.fire({
            title: title,
            text: text,
            icon: isApprove ? 'success' : 'warning',
            showCancelButton: true,
            confirmButtonText: confirmButton,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Kirim ke server via AJAX
                $.ajax({
                    url: url,
                    method: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function () {
                        Swal.fire('Berhasil!', successMessage, 'success')
                            .then(() => {
                                location.reload();
                            });
                    },
                    error: function () {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat memproses.', 'error');
                    }
                });
            }
        });
    }
    </script>
