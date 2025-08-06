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
            title: "Sukses!"
            , text: "{{ session('success') }}"
            , icon: "success"
            , confirmButtonText: "OK"
        });
    });

</script>
@endif
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">RTC List</h3>
                    <div class="d-flex align-items-center">
                        <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..." style="width: 200px;">
                        <button type="button" class="btn btn-primary me-3" id="searchButton">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8" role="tablist">
                        @if (auth()->user()->role == 'HRD' || auth()->user()->employee->position == 'Direktur')
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 filter-tab active" data-filter="department">Department</a>
                        </li>
                        @endif
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="section">Section</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-active-primary pb-4 filter-tab" data-filter="sub_section">Sub Section</a>
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
                            @include('website.rtc.partials.table', [
                            'rtcs' => $rtcs,
                            'employees' => $employees
                            ])
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   $(document).ready(function() {
    const employees = @json($employees);
    const user = @json($user);
    let currentFilter = @json($defaultFilter); // Gunakan defaultFilter dari controller

    function loadTable(filter = currentFilter) {
        $.ajax({
            url: '{{ route('filter.master') }}',
            type: 'GET',
            data: {
                filter: filter,
                division_id: @json($divisionId),
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                $('#kt_table_users tbody').html(res);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load data',
                    icon: 'error'
                });
            }
        });
    }

    // Event handler untuk tab filter
    $('.filter-tab').click(function() {
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        loadTable(currentFilter);
    });

    // Inisialisasi tabel dengan filter default
    loadTable();

    // Implementasi search
    $('#searchButton').click(function() {
        var searchText = $('#searchInput').val().toLowerCase();
        $('#kt_table_users tbody tr').each(function() {
            var name = $(this).find('td:eq(1)').text().toLowerCase();
            $(this).toggle(name.includes(searchText));
        });
    });

    // Tekan Enter untuk search
    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            $('#searchButton').click();
        }
    });

    let currentId = null;
    $(document).on('click', '.btn-show-modal', function() {
        currentId = $(this).data('id');
            // Mapping posisi berdasarkan filter
            const positionMap = {
                department: ['Supervisor', 'Section Head']
                , section: ['Leader']
                , sub_section: ['JP', 'Act JP', 'Act Leader']
            , };

            const targetPosition = positionMap[currentFilter] || [];

            // Filter karyawan berdasarkan posisi
            const filtered = employees.filter(e =>
                targetPosition.map(p => p.toLowerCase()).includes(e.position.toLowerCase())
            );

            // Populate semua select dropdown
            ['#short_term', '#mid_term', '#long_term'].forEach(id => {
                const select = $(id);
                select.empty().append('<option value="">-- Select --</option>');
                filtered.forEach(e => {
                    select.append(`<option value="${e.id}">${e.name}</option>`);
                });
            });

            // Cari karyawan yang jadi leader sesuai currentFilter dan currentId
            const currentEmployee = employees.find(e => {
                const lead = e[`leading_${currentFilter}`];
                return lead && lead.id === currentId;
            });

            // Jika ditemukan, set default value dari dropdown-nya
            if (currentEmployee) {
                const leadData = currentEmployee[`leading_${currentFilter}`];

                $('#short_term').val(leadData ? .short_term || '');
                $('#mid_term').val(leadData ? .mid_term || '');
                $('#long_term').val(leadData ? .long_term || '');
            }
    });

    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
            const filter = currentFilter;

            $('#viewDetailModal').modal('show');
            $('#viewDetailContent').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Loading data...</p></div>'
            );

            $.ajax({
                url: '{{ route('
                rtc.detail ') }}'
                , type: 'GET'
                , data: {
                    id: id
                    , filter: filter
                }
                , success: function(response) {
                    $('#viewDetailContent').html(response);
                }
                , error: function() {
                    $('#viewDetailContent').html(
                        '<p class="text-danger text-center">Failed to load data.</p>');
                }
            });
    });

    $('#addPlanForm').on('submit', function(e) {
        e.preventDefault();

            const formData = {
                filter: currentFilter, // misalnya ambil dari global JS variable
                id: currentId, // id dari entity (division/department/etc)
                short_term: $('#short_term').val()
                , mid_term: $('#mid_term').val()
                , long_term: $('#long_term').val()
            , };

            $.ajax({
                url: '{{ route('
                rtc.update ') }}'
                , type: 'GET'
                , data: formData
                , headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
                , success: function(response) {

                    $('#addPlanModal').modal('hide');
                    // optionally refresh the page or data
                    window.location.reload()
                }
                , error: function(xhr) {
                    iziToast.error({
                        title: 'Error'
                        , message: xhr.responseJSON ? .message ||
                            'Failed to update plan'
                    });
                }
            });
    });
});

</script>
@endpush
