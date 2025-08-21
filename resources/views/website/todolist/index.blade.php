@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
@endsection

@push('custom-css')
    <style>
        /* Page enter */
        .page-enter {
            opacity: 0;
            transform: translateY(8px)
        }

        .page-enter.page-in {
            animation: pageFadeIn .55s cubic-bezier(.21, 1, .21, 1) forwards
        }

        @keyframes pageFadeIn {
            to {
                opacity: 1;
                transform: none
            }
        }

        /* Stagger item/kartu */
        .stagger {
            opacity: 0;
            transform: translateY(10px)
        }

        .stagger.show {
            animation: cardIn .55s cubic-bezier(.21, 1, .21, 1) forwards;
            animation-delay: var(--d, 0ms)
        }

        @keyframes cardIn {
            to {
                opacity: 1;
                transform: none
            }
        }

        /* Aksesibilitas: hormati preferensi reduce motion */
        @media (prefers-reduced-motion: reduce) {

            .page-enter,
            .stagger {
                opacity: 1;
                transform: none;
                animation: none
            }
        }
    </style>
@endpush


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
    @if (session()->has('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "{{ session('error') }}",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif
    <div id="kt_app_content_container" class="app-container container-fluid page-enter">
        <div class="row">

            {{-- IDP --}}
            <div class="col-4 mb-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-light text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-tasks me-2"></i><span class="fw-bold">IDP</span> - [{{ $allIdpTasks->count() }}
                            Items]
                        </h3>
                    </div>
                    <div class="card-body overflow-auto" style="max-height: 600px;">
                        <div class="row g-3">
                            @forelse ($allIdpTasks as $item)
                                <div class="col-md-12">
                                    <a href="{{ in_array($item['type'], ['need_check', 'need_approval'])
                                        ? route('idp.approval')
                                        : route('idp.index', ['company' => $item['employee_company'], 'npk' => $item['employee_npk']]) }}"
                                        class="text-decoration-none text-dark">
                                        <div
                                            class="card border-0 shadow-sm
                                            @if ($item['type'] === 'unassigned') bg-danger-subtle
                                            @elseif($item['type'] === 'need_check') bg-warning-subtle
                                            @elseif($item['type'] === 'draft') bg-warning-subtle
                                            @elseif($item['type'] === 'need_approval') bg-info-subtle
                                            @elseif($item['type'] === 'revise') bg-danger-subtle
                                            @else bg-light @endif
                                            hover-shadow">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-2">{{ $item['employee_name'] }}</h5>
                                                </div>
                                                <span
                                                    class="badge
                                                    @if ($item['type'] === 'unassigned') badge-danger
                                                    @elseif($item['type'] === 'need_check') badge-warning
                                                    @elseif($item['type'] === 'draft') badge-warning
                                                    @elseif($item['type'] === 'need_approval') badge-info
                                                    @elseif($item['type'] === 'revise') badge-danger
                                                    @else badge-secondary @endif
                                                    rounded-pill px-3 py-2">
                                                    @if ($item['type'] === 'unassigned')
                                                        <i class="fas fa-exclamation-circle me-2"></i> To Be Assign
                                                    @elseif($item['type'] === 'need_check')
                                                        <i class="fas fa-exclamation-circle me-2"></i> Need Check
                                                    @elseif($item['type'] === 'need_approval')
                                                        <i class="fas fa-hourglass-half me-2"></i> Need Approve
                                                    @elseif($item['type'] === 'draft')
                                                        <i class="fas fa-exclamation-circle me-2"></i> Need Submit
                                                    @elseif($item['type'] === 'revise')
                                                        <i class="fas fa-undo me-2"></i> Need Revise
                                                    @else
                                                        Unknown
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="col-md-12 py-1">
                                    <div class="card border-0 bg-light-success hover-shadow">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0 text-success">
                                                <i class="fas fa-check-circle me-2"></i> No Task.
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            {{-- end of IDP --}}

            {{-- HAV --}}
            <div class="col-4 mb-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-light text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-tasks me-2"></i><span class="fw-bold">HAV</span> - [{{ $allHavTasks->count() }}
                            Items]
                        </h3>
                    </div>
                    <div class="card-body overflow-auto" style="max-height: 600px;">
                        <div class="row g-3">
                            @forelse ($allHavTasks as $item)
                                <div class="col-md-12">
                                    <a href="{{ route('hav.approval') }}" class="text-decoration-none text-dark">
                                        <div
                                            class="card border-0 shadow-sm
                                            @if ($item->getRawOriginal('status') === 1) bg-info-subtle
                                            @elseif($item->getRawOriginal('status') === 0) bg-warning-subtle
                                            @else bg-light @endif
                                            hover-shadow">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-2">{{ $item->employee->name }}</h5>
                                                </div>
                                                <span
                                                    class="badge
                                                    @if ($item->getRawOriginal('status') === 1) badge-info
                                                    @elseif($item->getRawOriginal('status') === 0) badge-warning
                                                    @else badge-secondary @endif
                                                    rounded-pill px-3 py-2">
                                                    @if ($item->getRawOriginal('status') === 1)
                                                        <i class="fas fa-hourglass-half me-2"></i> To Be Assign
                                                    @elseif($item->getRawOriginal('status') === 0)
                                                        <i class="fas fa-exclamation-circle me-2"></i> Need Approve
                                                    @else
                                                        Unknown
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="col-md-12 py-1">
                                    <div class="card border-0 bg-light-success hover-shadow">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0 text-success">
                                                <i class="fas fa-check-circle me-2"></i> No Task.
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            {{-- end of HAV --}}

            {{-- RTC --}}
            <div class="col-4 mb-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-light text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-tasks me-2"></i><span class="fw-bold">RTC</span> -
                            [{{ $allRtcTasks->count() }}
                            Items]
                        </h3>
                    </div>
                    <div class="card-body overflow-auto" style="max-height: 600px;">
                        <div class="row g-3">
                            @forelse ($allRtcTasks as $item)
                                <div class="col-md-12">
                                    <a href="{{ route('rtc.approval') }}" class="text-decoration-none text-dark">
                                        <div
                                            class="card border-0 shadow-sm
                                            @if ($item->getRawOriginal('status') === 1) bg-info-subtle
                                            @elseif($item->getRawOriginal('status') === 0) bg-warning-subtle
                                            @else bg-light @endif
                                            hover-shadow">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-2">{{ $item->employee->name }}</h5>
                                                </div>
                                                <span
                                                    class="badge
                                                    @if ($item->getRawOriginal('status') === 1) badge-info
                                                    @elseif($item->getRawOriginal('status') === 0) badge-warning
                                                    @else badge-secondary @endif
                                                    rounded-pill px-3 py-2">
                                                    @if ($item->getRawOriginal('status') === 1)
                                                        <i class="fas fa-hourglass-half me-2"></i> Need Approve
                                                    @elseif($item->getRawOriginal('status') === 0)
                                                        <i class="fas fa-exclamation-circle me-2"></i> Need Check
                                                    @else
                                                        Unknown
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="col-md-12 py-1">
                                    <div class="card border-0 bg-light-success hover-shadow">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0 text-success">
                                                <i class="fas fa-check-circle me-2"></i> No Task.
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            {{-- end of RTC --}}

            {{-- Assessment --}}
            @if (auth()->user()->role === 'HRD')
                <div class="col-4 mb-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light text-white">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-tasks me-2"></i><span class="fw-bold">Assessment</span> -
                                [{{ $allHavTasks->count() }}
                                Items]
                            </h3>
                        </div>
                        <div class="card-body overflow-auto" style="max-height: 600px;">
                            <div class="row g-3">
                                @forelse ($allHavTasks as $item)
                                    <div class="col-md-12">
                                        <a href="{{ in_array($item['type'], ['need_check', 'need_approval'])
                                            ? route('idp.approval')
                                            : route('idp.index', ['company' => $item['employee_company'], 'npk' => $item['employee_npk']]) }}"
                                            class="text-decoration-none text-dark">
                                            <div
                                                class="card border-0 shadow-sm
                                            @if ($item->getRawOriginal('status') === 1) bg-danger-subtle
                                            @elseif($item->getRawOriginal('status') === 0) bg-warning-subtle
                                            @else bg-light @endif
                                            hover-shadow">
                                                <div class="card-body d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5 class="mb-2">{{ $item->employee->name }}</h5>
                                                    </div>
                                                    <span
                                                        class="badge
                                                    @if ($item->getRawOriginal('status') === 1) badge-danger
                                                    @elseif($item->getRawOriginal('status') === 0) badge-warning
                                                    @else badge-secondary @endif
                                                    rounded-pill px-3 py-2">
                                                        @if ($item->getRawOriginal('status') === 1)
                                                            <i class="fas fa-exclamation-circle me-2"></i> To Be Assign
                                                        @elseif($item->getRawOriginal('status') === 0)
                                                            <i class="fas fa-exclamation-circle me-2"></i> Need Approve
                                                        @else
                                                            Unknown
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div class="col-md-12 py-1">
                                        <div class="card border-0 bg-light-success hover-shadow">
                                            <div class="card-body text-center">
                                                <h5 class="mb-0 text-success">
                                                    <i class="fas fa-check-circle me-2"></i> No Task.
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            {{-- end of Assessment --}}

        </div>
    </div>

    <!-- Add Todo Modal -->
    <div class="modal fade" id="addTodoModal" tabindex="-1" aria-labelledby="addTodoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTodoModalLabel">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="taskName" class="form-label">Task</label>
                            <input type="text" class="form-control" id="taskName" placeholder="Enter task name">
                        </div>
                        <div class="mb-3">
                            <label for="taskStatus" class="form-label">Status</label>
                            <select class="form-select" id="taskStatus">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Tambahkan SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('kt_app_content_container');
            if (container) container.classList.add('page-in');

            const headerCards = document.querySelectorAll('.row > .col-4 .card.shadow-sm');
            headerCards.forEach((card, i) => {
                card.classList.add('stagger');
                card.style.setProperty('--d', (i * 120) + 'ms');
                requestAnimationFrame(() => card.classList.add('show'));
            });

            const listItems = document.querySelectorAll('.card-body .row.g-3 > .col-md-12');
            listItems.forEach((el, idx) => {
                el.classList.add('stagger');
                el.style.setProperty('--d', (idx * 40) + 'ms');
                requestAnimationFrame(() => el.classList.add('show'));
            });
        });
    </script>
@endpush
