@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Approval' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Approval' }}
@endsection

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">RTC List</h3>
                <div class="d-flex align-items-center">
                    <form method="GET" class="d-flex align-items-center">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2"
                            placeholder="Search Area..." style="width:200px;">
                        <input type="hidden" name="filter">
                        <button type="submit" class="btn btn-primary me-3">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if (auth()->user()->role == 'HRD')
                    {{-- ... tab yang lama ... --}}
                @endif

                <div class="mb-3">
                    <span class="badge bg-secondary">
                        Stage: {{ $stage === 'check' ? 'Check' : 'Approve' }}
                    </span>
                </div>

                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th style="width:50px;">No</th>
                            <th>Area</th>
                            <th>Type</th>
                            <th>Current PIC</th>
                            <th class="text-center" style="width:140px;">Detail</th>
                            <th class="text-center" style="width:160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rtcs as $item)
                            @php
                                $currentPic = $item['current_pic'] ?? '-';
                            @endphp

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item['area_name'] ?? '-' }}</td>
                                <td>{{ ucfirst($item['area'] ?? '-') }}</td>

                                {{-- Current PIC (fallback '-') --}}
                                <td>{{ $currentPic }}</td>

                                {{-- Detail --}}
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-light btn-detail" data-bs-toggle="modal"
                                        data-bs-target="#rtcDetailModal" data-area="{{ $item['area'] }}"
                                        data-area_id="{{ $item['area_id'] }}" data-area_name="{{ $item['area_name'] }}">
                                        <i class="fas fa-eye"></i> View Detail
                                    </button>
                                </td>

                                {{-- Action --}}
                                <td class="text-center">
                                    <div class="d-flex flex-column">
                                        <button type="button" class="btn btn-sm btn-success btn-approve mb-1"
                                            onclick="confirmApproveArea('{{ $item['area'] }}', '{{ $item['area_id'] }}')">
                                            <i class="fas fa-check-circle"></i>
                                            {{ $stage === 'check' ? 'Check' : 'Approve' }}
                                        </button>

                                        <button type="button" class="btn btn-sm btn-danger btn-revise"
                                            onclick="confirmReviseArea('{{ $item['area'] }}', '{{ $item['area_id'] }}')">
                                            <i class="fas fa-rotate-left"></i>
                                            Revise
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div class="modal fade" id="rtcDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        RTC Detail - <span id="rtcDetailAreaName">Area Name</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="rtcDetailLoading" class="py-5 text-center text-muted d-none">
                        <div class="spinner-border" role="status" style="width:2rem;height:2rem;"></div>
                        <div class="mt-3">Loading...</div>
                    </div>

                    <div id="rtcDetailError" class="alert alert-danger d-none"></div>

                    <div id="rtcDetailContent" class="table-responsive d-none">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="text-muted">
                                <tr>
                                    <th style="width:40px;">No</th>
                                    <th>NPK</th>
                                    <th>Employee Name</th>
                                    <th>Term</th>
                                </tr>
                            </thead>
                            <tbody id="rtcDetailTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmApproveArea(area, area_id) {
            Swal.fire({
                title: '{{ $stage === 'check' ? 'Check Area?' : 'Approve Area?' }}',
                showCancelButton: true,
                confirmButtonText: '{{ $stage === 'check' ? 'Yes, Check' : 'Yes, Approve' }}',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ route('rtc.approve.area') }}",
                        method: 'POST',
                        data: {
                            area,
                            area_id,
                            _token: '{{ csrf_token() }}'
                        }
                    }).then(() => {
                        Swal.fire('Success', 'Saved.', 'success').then(() => location.reload());
                    }).catch(xhr => {
                        Swal.fire('Failed', xhr?.responseJSON?.message || 'Error.', 'error');
                    });
                }
            });
        }

        function confirmReviseArea(area, area_id) {
            Swal.fire({
                title: 'Revise Area?',
                input: 'textarea',
                inputPlaceholder: 'Reason...',
                showCancelButton: true,
                confirmButtonText: 'Yes, Revise',
                cancelButtonText: 'Cancel',
                preConfirm: (comment) => {
                    return $.ajax({
                        url: "{{ route('rtc.revise.area') }}",
                        method: 'POST',
                        data: {
                            area,
                            area_id,
                            comment,
                            _token: '{{ csrf_token() }}'
                        }
                    }).then(() => {
                        Swal.fire('Success', 'Revised.', 'success').then(() => location.reload());
                    }).catch(xhr => {
                        Swal.fire('Failed', xhr?.responseJSON?.message || 'Error.', 'error');
                    });
                }
            });
        }

        const detailModal = document.getElementById('rtcDetailModal');
        detailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const area = button.getAttribute('data-area');
            const area_id = button.getAttribute('data-area_id');
            const area_name = button.getAttribute('data-area_name');

            document.getElementById('rtcDetailAreaName').textContent = area_name || '-';

            document.getElementById('rtcDetailLoading').classList.remove('d-none');
            document.getElementById('rtcDetailError').classList.add('d-none');
            document.getElementById('rtcDetailContent').classList.add('d-none');
            document.getElementById('rtcDetailTableBody').innerHTML = '';

            $.ajax({
                url: "{{ route('rtc.area.items') }}",
                method: 'GET',
                data: {
                    area,
                    area_id
                }
            }).done(function(res) {
                const tbody = document.getElementById('rtcDetailTableBody');

                if (!Array.isArray(res) || res.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">No RTC found in this area.</td>
                    </tr>`;
                } else {
                    res.forEach(function(rtc, index) {
                        tbody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${rtc.employee?.npk ?? '-'}</td>
                            <td>${rtc.employee?.name ?? '-'}</td>
                            <td>${(rtc.term ?? '').toUpperCase()}</td>
                        </tr>
                    `;
                    });
                }

                document.getElementById('rtcDetailLoading').classList.add('d-none');
                document.getElementById('rtcDetailContent').classList.remove('d-none');
            }).fail(function(xhr) {
                document.getElementById('rtcDetailLoading').classList.add('d-none');
                document.getElementById('rtcDetailError').classList.remove('d-none');
                document.getElementById('rtcDetailError').textContent =
                    xhr?.responseJSON?.message || 'Failed to load area detail.';
            });
        });
    </script>
@endpush
