@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
@endsection

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
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
                <h3 class="card-title">Employee List</h3>
            </div>

            <div class="card-body">
                {{-- Tabs Filter Posisi (client-side; tidak pindah halaman) --}}
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-5 fw-semibold mb-4" role="tablist"
                    style="cursor:pointer">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link fs-7 {{ request('filter') === 'all' || is_null(request('filter')) ? 'active' : '' }}"
                            href="{{ route('employee.index', ['company' => $company, 'search' => request('search'), 'filter' => 'all']) }}">
                            <i class="fas fa-list me-2"></i>Show All
                        </a>
                    </li>

                    {{-- Tab Dinamis Berdasarkan Posisi --}}
                    @foreach ($visiblePositions as $position)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-7 my-0 mx-3 {{ $filter == $position ? 'active' : '' }}"
                                href="{{ route('employee.index', ['company' => $company, 'search' => request('search'), 'filter' => $position]) }}">
                                <i class="fas fa-user-tag me-2"></i>{{ $position }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>No</th>
                                <th>Photo</th>
                                <th>NPK</th>
                                <th>Employee Name</th>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Grade</th>
                                <th>Age</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $index => $employee)
                                @php
                                    $unit = match ($employee->position) {
                                        'Direktur' => $employee->plant?->name,
                                        'GM', 'Act GM' => $employee->division?->name,
                                        default => $employee->department?->name,
                                    };
                                    $userId = (string) $employee->user_id;
                                    $tok = App\Support\OpaqueId::encode((int) $userId);
                                @endphp
                                <tr class="fs-7" data-position="{{ $employee->position }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                            alt="Employee Photo" class="rounded" width="40" height="40"
                                            style="object-fit: cover;">
                                    </td>
                                    <td>{{ $employee->npk }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->company_name }}</td>
                                    <td>{{ $employee->position }}</td>
                                    <td>{{ $unit }}</td>
                                    <td>{{ $employee->grade }}</td>
                                    <td>{{ \Carbon\Carbon::parse($employee->birthday_date)->age }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('employee.show', $tok) }}" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted fw-bold">
                        Catatan: Hubungi HRD Human Capital jika data karyawan yang dicari tidak tersedia.
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof $ === 'undefined') return console.error("jQuery not loaded.");

            // Destroy bila sudah ter-init
            if ($.fn.DataTable.isDataTable('#kt_table_users')) {
                $('#kt_table_users').DataTable().destroy();
            }

            // Init DataTables (client-side)
            const dt = $('#kt_table_users').DataTable({
                responsive: true,
                ordering: false,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No matching records found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        next: "Next",
                        previous: "Previous"
                    }
                },
                // biar rapi: dom default Bootstrap 5
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-end'f>>" +
                    "rt" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
            });

            // Filter tab posisi -> gunakan search pada kolom ke-6 (index 5)
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove(
                        'active'));
                    this.classList.add('active');

                    const val = this.getAttribute('data-filter');
                    if (val === 'all') {
                        dt.column(5).search('').draw();
                    } else {
                        // ^...$ untuk cocokkan persis posisi (hindari partial match)
                        dt.column(5).search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true,
                            false).draw();
                    }
                });
            });
        });
    </script>
@endpush
