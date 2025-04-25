@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@section('main')
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content  flex-column-fluid ">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container  container-fluid ">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Division List</h3>
                        <div class="d-flex align-items-center">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                                style="width: 200px;">
                            <button type="button" class="btn btn-primary me-3" id="searchButton">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                                data-kt-menu-placement="bottom-end">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('employee.create') }}" class="btn btn-primary me-3">
                                <i class="fas fa-plus"></i>
                                Add
                            </a>
                            <button type="button" class="btn btn-info me-3" data-bs-toggle="modal"
                                data-bs-target="#importModal">
                                <i class="fas fa-upload"></i>
                                Import
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                            role="tablist" style="cursor:pointer">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4 filter-tab active"
                                    data-filter="department">Department</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="section">Section</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="sub_section">Sub
                                    Section</a>
                            </li>
                        </ul>
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Adding -->
    <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addPlanForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @foreach (['short_term' => 'Short Term', 'mid_term' => 'Mid Term', 'long_term' => 'Long Term'] as $key => $label)
                            <div class="mb-3">
                                <label for="{{ $key }}" class="form-label">{{ $label }}</label>
                                <select id="{{ $key }}" class="form-select" name="{{ $key }}">
                                    <option value="">-- Select --</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function loadTable(filter = 'Supervisor') {
                $.ajax({
                    url: '{{ route('filter.master') }}',
                    type: 'GET',
                    data: {
                        filter: filter
                    },
                    success: function(res) {
                        $('#kt_table_users tbody').html(res);
                    }
                });
            }

            // initial load = Supervisor (Department)
            loadTable('department');

            $('.filter-tab').on('click', function() {
                $('.filter-tab').removeClass('active');
                $(this).addClass('active');
                let filter = $(this).data('filter');
                loadTable(filter);
            });

            let currentFilter = 'department'; // default tab
            const employees = @json($employees);

            $('.filter-tab').on('click', function() {
                $('.filter-tab').removeClass('active');
                $(this).addClass('active');
                currentFilter = $(this).data('filter'); // update tab saat klik
                loadTable(currentFilter);
            });

            $(document).on('click', '.btn-show-modal', function() {
                let targetPosition = [];

                if (currentFilter === 'department') {
                    targetPosition = ['Manager'];
                } else if (currentFilter === 'section') {
                    targetPosition = ['Supervisor'];
                } else if (currentFilter === 'sub_section') {
                    targetPosition = ['JP', 'Leader', 'Act JP',
                        'Act Leader'
                    ]; // pakai lebih dari satu posisi di sini
                }

                const filtered = employees.filter(e =>
                    targetPosition.map(p => p.toLowerCase()).includes(e.position.toLowerCase())
                );

                console.log(filtered); // cek hasilnya

                ['#short_term', '#mid_term', '#long_term'].forEach(id => {
                    const select = $(id);
                    select.empty().append('<option value="">-- Select --</option>');
                    filtered.forEach(e => {
                        select.append(`<option value="${e.id}">${e.name}</option>`);
                    });
                });
            });
        });
    </script>
@endpush
