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
                        {{-- <button type="button" class="btn btn-info me-3" data-bs-toggle="modal"
                            data-bs-target="#importModal">
                            <i class="fas fa-upload"></i>
                            Import
                        </button> --}}
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if (auth()->user()->role == 'HRD')
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                        role="tablist" style="cursor:pointer">
                        <a class="nav-link text-active-primary pb-4 {{ $filter == 'all' ? 'active' : '' }}"
                            href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            Show All
                        </a>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Direktur' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Direktur']) }}">
                                Direktur
                            </a>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'GM' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'GM']) }}">GM</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Manager' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Manager']) }}">Manager</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Section Head' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Section Head']) }}">Section
                                Head</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Coordinator' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Coordinator']) }}">Coordinator</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Supervisor' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Supervisor']) }}">Supervisor</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Leader' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Leader']) }}">Leader</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'JP' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'JP']) }}">JP</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 {{ $filter == 'Operator' ? 'active' : '' }}"
                                href="{{ route('hav.approval', ['company' => $company, 'search' => request('search'), 'filter' => 'Operator']) }}">Operator</a>
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
                                    @if ($item->hav_status == 0)
                                    <span class="badge bg-warning fw-normal">Pending</span>
                                @elseif ($item->hav_status == 2)
                                    <span class="badge bg-success fw-normal">Approved</span>
                                @elseif ($item->hav_status == 1)
                                    <span class="badge bg-danger fw-normal">Revise</span>
                                @endif
                                </td>

                                <td class="text-center">
                                    {{-- Summary --}}
                                    <a href="{{ url('hav/generate-create', ['id' => $item->employee_id]) }}"
                                        class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if ($item->hav_status == 0)
                                    <!-- Tombol APPROVE -->
                                    <button type="button" class="btn btn-success btn-sm" onclick="confirmApprove({{ $item->id }})">
                                        <i class="fas fa-check"></i> Approve
                                    </button>

                                    <!-- Tombol REJECT -->
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmReject({{ $item->id }})">
                                        <i class="fas fa-times"></i> Revise
                                    </button>
                                @endif

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
    function confirmApprove(id) {
        Swal.fire({
            title: 'Approve Data?',
            input: 'textarea',
            inputPlaceholder: 'Comment...',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel',
            preConfirm: (comment) => {
                return $.ajax({
                    url: `/hav/approve/${id}`,
                    method: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        comment: comment

                    }
                }).then(() => {
                    Swal.fire('Berhasil!', 'Data berhasil disetujui.', 'success')
                        .then(() => location.reload());
                }).catch(() => {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan.', 'error');
                });
            }
        });
    }

    function confirmReject(id) {
        Swal.fire({
            title: 'Revise Data?',
            input: 'textarea',
            inputPlaceholder: 'Comment...',
            showCancelButton: true,
            confirmButtonText: 'Yes, Revise',
            cancelButtonText: 'Cancel',
            preConfirm: (comment) => {
                return $.ajax({
                    url: `/hav/reject/${id}`,
                    method: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        comment: comment

                    }
                }).then(() => {
                    Swal.fire('Berhasil!', 'Data berhasil direvisi.', 'success')
                        .then(() => location.reload());
                }).catch(() => {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan.', 'error');
                });
            }
        });
    }
</script>
