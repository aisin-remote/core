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
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="row">
            <div class="col-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light-danger text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Unassigned IDP -
                            [{{ count($notExistInIdp) }} Items]</h3>
                    </div>

                    <div class="card-body overflow-auto" style="max-height: 400px;">
                        <div class="row g-3">
                            @forelse ($notExistInIdp as $item)
                                @php
                                    $url = route('idp.index', [
                                        'company' => $item['employee_company'],
                                        'npk' => $item['employee_npk'],
                                    ]);
                                @endphp
                                <div class="col-md-12">
                                    <a href="{{ $url }}" class="text-decoration-none text-dark">
                                        <div class="card border-0 shadow-sm bg-danger-subtle hover-shadow">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-2">{{ $item['employee_name'] }}</h5>
                                                    <small>
                                                        Assessment ID: {{ $item['assessment_id'] }}<br>
                                                        ALC: {{ $item['alc_name'] }}
                                                    </small>
                                                </div>
                                                <span class="badge bg-danger rounded-pill px-3 py-2">
                                                    <i class="fas fa-exclamation-circle me-1"></i> Not Yet Created
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-success text-center">
                                        <i class="fas fa-check-circle me-1"></i> No Task.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- pending idp --}}
            <div class="col-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light-warning text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Pending IDP -
                            [{{ count($pendingIdps) }} Items]</h3>
                    </div>

                    <div class="card-body overflow-auto" style="max-height: 400px;">
                        <div class="row g-3">
                            @forelse ($pendingIdps as $item)
                                <div class="col-md-12">
                                    <a href="{{ $url }}" class="text-decoration-none text-dark">
                                        <div class="card border-0 shadow-sm bg-warning-subtle hover-shadow">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-2">{{ $item->name }}</h5>
                                                    <small>
                                                        Category :
                                                        {{ $item->assessments->first()->idp->first()->category }}<br>
                                                        Program:
                                                        {{ $item->assessments->first()->idp->first()->development_program }}<br>
                                                        Target:
                                                        {{ $item->assessments->first()->idp->first()->development_target }}
                                                    </small>
                                                </div>
                                                <span class="badge bg-warning rounded-pill px-3 py-2">
                                                    <i class="fas fa-exclamation-circle me-1"></i>Need Approve
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-success text-center">
                                        <i class="fas fa-check-circle me-1"></i> No Task .
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
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
