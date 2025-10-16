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
                            placeholder="Search Employee..." style="width:200px;">
                        <input type="hidden" name="filter">
                        <button type="submit" class="btn btn-primary me-3">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                {{-- (opsional) Tab filter HRD tetap, kalau tidak dipakai boleh dihapus --}}
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
                            <th>No</th>
                            <th>NPK</th>
                            <th>Employee Name</th>
                            <th>Company</th>
                            <th>Department</th>
                            <th>Current Position</th>
                            <th>Area</th>
                            <th>Term</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rtcs as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->employee->npk }}</td>
                                <td>{{ $item->employee->name }}</td>
                                <td>{{ $item->employee->company_name }}</td>
                                <td>{{ $item->employee->department?->name }}</td>
                                <td>{{ $item->employee->position }}</td>
                                <td>{{ ucfirst($item->area) }}</td>
                                <td>{{ strtoupper($item->term) }}</td>
                                <td>
                                    @php
                                        $lbl = match ((int) $item->status) {
                                            0 => 'Submitted',
                                            1 => 'Checked',
                                            2 => 'Approved',
                                            default => 'Unknown',
                                        };
                                        $cls = match ((int) $item->status) {
                                            0 => 'bg-warning-subtle text-warning-emphasis',
                                            1 => 'bg-info-subtle text-info-emphasis',
                                            2 => 'bg-success-subtle text-success-emphasis',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $cls }}">{{ $lbl }}</span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-success btn-approve"
                                        onclick="confirmApprove({{ $item->id }})">
                                        <i class="fas fa-check-circle"></i>
                                        {{ $stage === 'check' ? 'Check' : 'Approve' }}
                                    </button>

                                    {{-- Revise: kirim balik ke pengusul (status -> 0).
                                 PD merevisi plant dari status=1; GM/Direktur merevisi dari status=0. --}}
                                    <button type="button" class="btn btn-sm btn-danger btn-revise"
                                        onclick="confirmRevise({{ $item->id }})">
                                        <i class="fas fa-rotate-left"></i> Revise
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- (opsional) modal import dibiarkan --}}
@endsection

@push('scripts')
    <script>
        function confirmApprove(id) {
            Swal.fire({
                title: '{{ $stage === 'check' ? 'Check Data?' : 'Approve Data?' }}',
                input: 'textarea',
                inputPlaceholder: 'Comment...',
                showCancelButton: true,
                confirmButtonText: '{{ $stage === 'check' ? 'Yes, Check' : 'Yes, Approve' }}',
                cancelButtonText: 'Cancel',
                preConfirm: (comment) => {
                    return $.ajax({
                        url: "{{ route('rtc.approve', ['id' => 'ID_REPLACE']) }}".replace('ID_REPLACE',
                            id),
                        method: 'GET',
                        data: {
                            comment: comment,
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

        function confirmRevise(id) {
            Swal.fire({
                title: 'Revise Data?',
                input: 'textarea',
                inputPlaceholder: 'Reason...',
                showCancelButton: true,
                confirmButtonText: 'Yes, Revise',
                cancelButtonText: 'Cancel',
                preConfirm: (comment) => {
                    return $.ajax({
                        url: "{{ route('rtc.revise', ['id' => 'ID_REPLACE']) }}".replace('ID_REPLACE',
                            id),
                        method: 'PATCH',
                        data: {
                            comment: comment,
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
    </script>
@endpush
