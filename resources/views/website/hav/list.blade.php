@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'HAV Quadran' }}
@endsection

@section('main')
<div id="kt_app_content_container" class="app-container container-fluid">
    <div class="app-content container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">HAV List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                        style="width: 200px;">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>

                    <a href="#" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#addAssessmentModal">Add</a>
                </div>
            </div>

            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="hav-table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>NPK</th>
                            <th>Age</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between">
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')<!-- jQuery dulu -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#hav-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("hav.ajax.list") }}',
            columns: [
                { data: 'npk', name: 'npk' },
                { data: 'nama', name: 'nama' },
                { data: 'status', name: 'status' }
            ]
        });
    });
</script>
@endpush
