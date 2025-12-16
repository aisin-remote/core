@extends('layouts.root.main')

@section('title', $title ?? 'Approval')
@section('breadcrumbs', $title ?? 'Approval')

@section('main')
@if (session('success'))
<script>
    document.addEventListener("DOMContentLoaded", () => {
        Swal.fire({
            title: "Sukses!",
            text: @json(session('success')),
            icon: "success",
            confirmButtonText: "OK"
        });
    });
</script>
@endif

<div id="kt_app_content_container" class="app-container container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Approval IDP List</h3>
            <div class="d-flex align-items-center">
                <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..." style="width: 200px;">
                <button type="button" class="btn btn-primary me-3" id="searchButton">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>

        <div class="card-body">
            @php
                $groupedIdps = $idps->groupBy(fn($idp) => optional(optional($idp->hav)->hav)->employee->id);
                $no = 1;
            @endphp

            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>NPK</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($groupedIdps as $employeeId => $employeeIdps)
                        @php
                            $employee = optional(optional($employeeIdps->first()->hav)->hav)->employee;
                        @endphp

                        @if($employee)
                        <tr data-employee-row="{{ $employeeId }}">
                            <td>{{ $no++ }}</td>
                            <td>{{ $employee->npk ?? '-' }}</td>
                            <td>{{ $employee->name ?? '-' }}</td>
                            <td>{{ $employee->bagian ?? '-' }}</td>
                            <td>{{ $employee->position ?? '-' }}</td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-warning"
                                    onclick="window.location.href='{{ route('idp.exportTemplate', ['employee_id' => $employee->id]) }}'">
                                    <i class="fas fa-upload"></i>
                                </button>

                                <a class="btn btn-sm btn-info"
                                    href="{{ route('idp.approval.show', ['employee_id' => $employee->id]) }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada IDP yang menunggu approval.</td>
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

<script>
document.addEventListener('DOMContentLoaded', () => {

    // Update nomor urut setelah ada baris yang dihapus
    function updateRowNumbers() {
        const rows = document.querySelectorAll('#kt_table_users tbody tr[data-employee-row]');
        rows.forEach((row, index) => {
            const noCell = row.querySelector('td:first-child');
            if (noCell) noCell.textContent = index + 1;
        });
    }
});
</script>
@endpush
