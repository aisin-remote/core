@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Sukses!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content  flex-column-fluid ">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container  container-fluid ">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ $cardTitle ?? 'List' }}</h3>
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
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $user = auth()->user();
                            $pos = optional($user->employee)->position;
                            $showDeptTab = $user->role === 'HRD' || $pos === 'Direktur' || $pos === 'GM';
                        @endphp

                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8"
                            role="tablist" style="cursor:pointer">
                            @if ($showDeptTab)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link text-active-primary pb-4 filter-tab"
                                        data-filter="department">Department</a>
                                </li>
                            @endif
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
                                    <th class="text-center">Short Term</th>
                                    <th class="text-center">Mid Term</th>
                                    <th class="text-center">Long Term</th>
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

    <style>
        #viewDetailModal .modal-dialog {
            max-width: 100vw;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            max-height: 100vh;
        }

        #viewDetailModal .modal-content {
            height: 100vh;
            border-radius: 0;
            display: flex;
            flex-direction: column;
        }

        #viewDetailModal .modal-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 1rem 2rem;
        }
    </style>
    {{-- modal detail --}}
    <div class="modal fade" id="viewDetailModal" tabindex="-1" aria-labelledby="viewDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDetailLabel">Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewDetailContent">
                    <!-- Konten akan diisi lewat AJAX -->
                    <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- end of modal --}}
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function loadTable(filter) {
                $.get('{{ route('filter.master') }}', {
                    filter: filter,
                    division_id: @json($divisionId)
                }).done(res => $('#kt_table_users tbody').html(res));
            }

            // Default dari server (untuk GM: 'department')
            const titles = {
                department: 'Department List',
                section: 'Section List',
                sub_section: 'Sub Section List'
            };
            let currentFilter = @json($defaultFilter ?? 'department');
            if (!$('.filter-tab[data-filter="' + currentFilter + '"]').length) currentFilter = 'section';


            // Set tab aktif dan judul, lalu load data awal
            $('.filter-tab').removeClass('active');
            $('.filter-tab[data-filter="' + currentFilter + '"]').addClass('active');
            $('.card-title').text(titles[currentFilter] ?? 'List');
            loadTable(currentFilter);

            // Ganti tab
            $('.filter-tab').on('click', function() {
                $('.filter-tab').removeClass('active');
                $(this).addClass('active');
                currentFilter = $(this).data('filter');
                $('.card-title').text(titles[currentFilter] ?? 'List');
                loadTable(currentFilter);
            });

            const employees = @json($employees);
            const user = @json($user);

            let currentId = null;

            // Modal Add Plan
            $(document).on('click', '.btn-show-modal', function() {
                currentId = $(this).data('id');

                const positionMap = {
                    department: ['Supervisor', 'Section Head'],
                    section: ['Leader'],
                    sub_section: ['JP', 'Act JP', 'Act Leader'],
                };
                const targetPosition = (positionMap[currentFilter] || []).map(p => p.toLowerCase());
                const filtered = employees.filter(e => targetPosition.includes((e.position || '')
                    .toLowerCase()));

                ['#short_term', '#mid_term', '#long_term'].forEach(id => {
                    const select = $(id);
                    select.empty().append('<option value="">-- Select --</option>');
                    filtered.forEach(e => select.append(
                        `<option value="${e.id}">${e.name}</option>`));
                });
            });

            // Button View → summary
            $(document).on('click', '.btn-view', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                window.location.href = `{{ route('rtc.summary') }}?id=${id}&filter=${currentFilter}`;
            });

            // Submit Add Plan
            $('#addPlanForm').on('submit', function(e) {
                e.preventDefault();
                const formData = {
                    filter: currentFilter,
                    id: currentId,
                    short_term: $('#short_term').val(),
                    mid_term: $('#mid_term').val(),
                    long_term: $('#long_term').val(),
                };
                $.ajax({
                    url: '{{ route('rtc.update') }}',
                    type: 'GET',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        $('#addPlanModal').modal('hide');
                        window.location.reload();
                    }
                });
            });
        });
    </script>
@endpush
