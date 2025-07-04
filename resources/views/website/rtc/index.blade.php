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
                        <h3 class="card-title">{{ $table }} List</h3>
                        <div class="d-flex align-items-center">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Search ..."
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
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th class="text-center">{{ $table }}</th>
                                    <th class="text-center">Short Term</th>
                                    <th class="text-center">Mid Term</th>
                                    <th class="text-center">Long Term</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($divisions as $division)
                                    @php
                                        $file = null;
                                        if ($division->name == 'PRODUCTION & ELECTRIC') {
                                            $file = 'rtc_prod.xlsx';
                                        } elseif ($division->name == 'ENGINEERING') {
                                            $file = 'rtc_eng.xlsx';
                                        }

                                        $terms = ['short', 'mid', 'long'];
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $division->name }}</td>

                                        @foreach ($terms as $term)
                                            @php
                                                $rtcForTerm = $rtcs->firstWhere(
                                                    fn($rtc) => $rtc->area_id == $division->id && $rtc->term == $term,
                                                );
                                                $termName = $division->{$term}?->name ?? null;
                                                $status = $rtcForTerm?->status;
                                            @endphp
                                            <td class="text-center">
                                                {{-- Hanya tampilkan "not set" jika termName kosong dan tidak ada status --}}
                                                @if ($termName || $status !== null)
                                                    <span>{{ $termName }}</span>
                                                @elseif (!$termName && is_null($status))
                                                    <span class="text-danger">not set</span>
                                                @endif

                                                {{-- Show badge hanya jika status 0 atau 1 --}}
                                                @if ($status === 1)
                                                    <span class="text-center badge badge-info">Checked</span>
                                                @elseif ($status === 0)
                                                    <span class="text-center badge badge-warning">Submitted</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="text-center">
                                            <a href="{{ route('rtc.list', ['id' => $division->id]) }}"
                                                class="btn btn-sm btn-primary" title="Detail">
                                                <i class="fas fa-info-circle"></i>
                                            </a>
                                            <a href="{{ route('rtc.summary', ['id' => $division->id, 'filter' => $table]) }}"
                                                class="btn btn-sm btn-info" title="View" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-success open-add-plan-modal"
                                                title="Add" data-id="{{ $division->id }}" data-bs-toggle="modal"
                                                data-bs-target="#addPlanModal">
                                                <i class="fas fa-plus-circle"></i>
                                            </a>
                                            {{-- <a href="{{ asset('assets/file/' . $file) }}" class="btn btn-sm btn-warning" title="Export" download>
                                            <i class="fas fa-upload"></i>
                                        </a> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addPlanForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="filter" value="division">
                        @foreach (['short_term' => 'Short Term', 'mid_term' => 'Mid Term', 'long_term' => 'Long Term'] as $key => $label)
                            <div class="mb-3">
                                <label for="{{ $key }}" class="form-label">{{ $label }}</label>
                                <select id="{{ $key }}" class="form-select " name="{{ $key }}">
                                    <option value="">-- Select --</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="submitPlanBtn" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentDivisionId = null;

        $(document).on('click', '.open-add-plan-modal', function() {
            currentDivisionId = $(this).data('id');
            $('#addPlanModal').modal('show');
        });

        // Inisialisasi select2 hanya sekali saat modal pertama kali ditampilkan
        $('#addPlanModal').on('shown.bs.modal', function() {
            $(this).find('.select2').each(function() {
                if (!$(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2({
                        dropdownParent: $('#addPlanModal .modal-content')
                    });
                }
            });
        });

        let filter = @json($table);

        $('#submitPlanBtn').on('click', function() {
            const formData = {
                filter: filter,
                id: currentDivisionId,
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
                success: function(response) {
                    $('#addPlanModal').modal('hide');
                    window.location.reload();
                },
                error: function(xhr) {
                    alert('Something went wrong!');
                    console.log(xhr.responseText);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const table = document.getElementById('kt_table_users');
            const rows = table.querySelectorAll('tbody tr');

            searchInput.addEventListener('keyup', function() {
                const query = this.value.toLowerCase();

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const text = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(
                        ' ');

                    if (text.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endpush
