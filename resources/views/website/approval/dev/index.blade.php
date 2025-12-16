@extends('layouts.root.main')

@section('title', $title ?? 'Approval Development')
@section('breadcrumbs', $title ?? 'Approval Development')

@section('main')
<div id="kt_app_content_container" class="app-container container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Approval Development List</h3>

            <form method="GET" class="d-flex align-items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control me-2" placeholder="Search Employee..." style="width:200px;">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <div class="card-body">
            {{-- TAB FILTER (TETAP) --}}
            @if (auth()->user()->role === 'HRD')
                @include('website.approval.development._tabs')
            @endif

            <table class="table align-middle table-row-dashed fs-6 gy-5" id="development_approval">
                <thead>
                    <tr class="text-muted fw-bold text-uppercase">
                        <th>No</th>
                        <th>NPK</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const dt = $('#development_approval').DataTable({
        processing: true,
        paging: true,
        searching: false,
        ordering: false,
        ajax: {
            url: @json(route('development.approval.json')),
            dataSrc: 'data',
            data: function (d) {
                const params = new URLSearchParams(window.location.search);
                ['company','filter','search'].forEach(k => {
                    if (params.get(k)) d[k] = params.get(k);
                });
            }
        },
        columns: [
            { render: (d,t,r,m) => m.row + 1 },
            { data: 'employee.npk', defaultContent: '-' },
            { data: 'employee.name', defaultContent: '-' },
            { data: 'employee.department', defaultContent: '-' },
            { data: 'employee.position', defaultContent: '-' },
            {
                className: 'text-end',
                render: (d,t,row) => {
                    const showUrl = @json(route('development.approval.show', ['id' => '__ID__']))
                        .replace('__ID__', row.employee_id);

                    return `
                        <a href="${showUrl}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    `;
                }
            }
        ]
    });
})();
</script>
@endpush
