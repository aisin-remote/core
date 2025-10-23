@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Matrix Competencies' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Matrix Competencies' }}
@endsection

@push('custom-css')
    <style>
        .disabled {
            pointer-events: none;
            opacity: .6;
            cursor: not-allowed;
        }
    </style>
@endpush

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
    @if (session()->has('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: @json(session('error')),
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Matrix Competencies List</h3>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-primary me-3" data-bs-toggle="modal"
                        data-bs-target="#addMatrixCompetencyModal">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
                        {{-- kalau perlu teruskan param company --}}
                        @if (request()->has('company'))
                            <input type="hidden" name="company" value="{{ request('company') }}">
                        @endif

                        <select name="dept_id" id="filter_dept_id" class="form-select form-select-sm pair-a"
                            data-pair="#filter_divs_id" style="min-width:220px">
                            <option value="">-- Filter by Department --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ (int) ($activeDept ?? 0) === (int) $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="divs_id" id="filter_divs_id" class="form-select form-select-sm pair-b"
                            data-pair="#filter_dept_id" style="min-width:220px">
                            <option value="">-- Filter by Division --</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div->id }}"
                                    {{ (int) ($activeDivs ?? 0) === (int) $div->id ? 'selected' : '' }}>
                                    {{ $div->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-sm btn-secondary">Apply</button>
                        <a href="{{ request()->has('company') ? url()->current() . '?company=' . urlencode(request('company')) : url()->current() }}"
                            class="btn btn-sm btn-light">
                            Reset
                        </a>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="table-matrix">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Competency</th>
                            <th>Level</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                            <tr class="fs-7">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->competency ?? $data->compentecies }}</td>
                                <td>
                                    @if ($data->department)
                                        DEPARTMENT - {{ optional($data->department)->name ?? '-' }}
                                    @else
                                        DIVISION - {{ optional($data->division)->name ?? '-' }}
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $data->id }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $data->id }}">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: Add Matrix Competency --}}
    <div class="modal fade" id="addMatrixCompetencyModal" tabindex="-1" aria-labelledby="addMatrixCompetencyLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMatrixCompetencyLabel">Add Matrix Competency</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('matrix.master.store') }}" method="POST">
                    @csrf
                    {{-- hidden company dari params --}}
                    <input type="hidden" name="company" value="{{ $company ?? request('company') }}">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_competency" class="form-label">Competency</label>
                            <input type="text" class="form-control" id="add_competency" name="competency" required>
                        </div>

                        <div class="mb-3">
                            <label for="add_dept_id" class="form-label">
                                Department <small class="text-muted">(pilih salah satu)</small>
                            </label>
                            <select class="form-control pair-a" id="add_dept_id" name="dept_id" data-pair="#add_divs_id">
                                <option value="" selected>-- Select Department --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="add_divs_id" class="form-label">
                                Division <small class="text-muted">(pilih salah satu)</small>
                            </label>
                            <select class="form-control pair-b" id="add_divs_id" name="divs_id" data-pair="#add_dept_id">
                                <option value="" selected>-- Select Division --</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <small class="text-muted d-block">
                            *Wajib pilih <strong>Department</strong> atau <strong>Division</strong> (salah satu saja).
                        </small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @foreach ($datas as $row)
        {{-- Modal: Edit Matrix Competency --}}
        <div class="modal fade" id="editModal{{ $row->id }}" tabindex="-1"
            aria-labelledby="editModalLabel{{ $row->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('matrix.master.update', $row->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    {{-- hidden company dari params (kalau perlu ikut saat update) --}}
                    <input type="hidden" name="company" value="{{ $company ?? request('company') }}">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $row->id }}">Edit Matrix Competency</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="competency{{ $row->id }}" class="form-label">Competency</label>
                                <input type="text" class="form-control" id="competency{{ $row->id }}"
                                    name="competency" value="{{ $row->competency ?? $row->compentecies }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="dept_id{{ $row->id }}" class="form-label">
                                    Department <small class="text-muted">(pilih salah satu)</small>
                                </label>
                                <select class="form-control pair-a" id="dept_id{{ $row->id }}" name="dept_id"
                                    data-pair="#divs_id{{ $row->id }}">
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ (int) $row->dept_id === (int) $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="divs_id{{ $row->id }}" class="form-label">
                                    Division <small class="text-muted">(pilih salah satu)</small>
                                </label>
                                <select class="form-control pair-b" id="divs_id{{ $row->id }}" name="divs_id"
                                    data-pair="#dept_id{{ $row->id }}">
                                    <option value="">-- Select Division --</option>
                                    @foreach ($divisions as $div)
                                        <option value="{{ $div->id }}"
                                            {{ (int) ($row->divs_id ?? 0) === (int) $div->id ? 'selected' : '' }}>
                                            {{ $div->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <small class="text-muted d-block">
                                *Wajib pilih <strong>Department</strong> atau <strong>Division</strong> (salah satu saja).
                            </small>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    {{-- DataTable init --}}
    <script>
        document.addEventListener("DOMContentLoaded", initMatrixTable);

        function initMatrixTable() {
            if (typeof $ === 'undefined' || !$.fn || !$.fn.DataTable) return;
            const selector = '#table-matrix';
            if ($.fn.DataTable.isDataTable(selector)) return;
            $(selector).DataTable({
                responsive: true,
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
                ordering: false
            });
        }
    </script>

    {{-- Delete confirm --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let id = this.getAttribute('data-id');
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/master/matrixCompetencies/delete/${id}`;
                            let csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';
                            let methodField = document.createElement('input');
                            methodField.type = 'hidden';
                            methodField.name = '_method';
                            methodField.value = 'DELETE';
                            form.appendChild(csrfToken);
                            form.appendChild(methodField);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    {{-- Mutual exclusion Dept vs Division (select biasa) --}}
    <script>
        (function() {
            function syncPair(selectEl) {
                const otherSel = document.querySelector(selectEl.dataset.pair);
                if (!otherSel) return;
                if (selectEl.value) {
                    otherSel.value = '';
                    otherSel.disabled = true;
                    otherSel.classList.add('disabled');
                } else {
                    // enable hanya jika dirinya kosong & pasangan juga kosong
                    if (!otherSel.value) {
                        otherSel.disabled = false;
                        otherSel.classList.remove('disabled');
                    }
                }
            }

            // apply untuk Add modal
            document.addEventListener('shown.bs.modal', function(e) {
                const modal = e.target;
                modal.querySelectorAll('select[data-pair]').forEach(function(sel) {
                    // state awal
                    syncPair(sel);
                    // on change
                    sel.addEventListener('change', function() {
                        syncPair(this);
                    });
                });
            });

            // jika ada select tampil dari awal (tanpa buka modal)
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('select[data-pair]').forEach(function(sel) {
                    syncPair(sel);
                    sel.addEventListener('change', function() {
                        syncPair(this);
                    });
                });
            });
        })();
    </script>
@endpush
