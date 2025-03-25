@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Competency' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Competency' }}
@endsection

@section('main')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Competency List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" style="width: 200px;" class="form-control me-2" id="searchInput"
                        placeholder="Search..." onkeyup="searchData()">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button class="btn btn-primary" id="openAddModal">
                        <i class="fas fa-plus"></i> Add Competency
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="competencyTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Competency</th>
                            <th>Description</th>
                            <th>Group Competency</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($competencies as $index => $competency)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $competency->name }}</td>
                                <td>{{ $competency->description }}</td>
                                <td>{{ $competency->group_competency->name }}</td>
                                <td>{{ $competency->department->name }}</td>
                                <td>{{ $competency->employee->position }}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm edit-btn" data-id="{{ $competency->id }}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $competency->id }}">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No competencies found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@include('website.competency.modal')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#group_competency_id, #department_id, #employee_id').select2({
                placeholder: "Select an option",
                allowClear: true
            });
        });

        document.getElementById('addForm').addEventListener('submit', function(event) {
            event.preventDefault();
            let formData = new FormData(this);

            fetch("{{ route('competencies.store') }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                }
            }).then(response => response.json())
            .then(data => {
                Swal.fire("Success", data.message, "success");
                location.reload();
            }).catch(error => console.error('Error:', error));
        });

        document.getElementById('openAddModal').addEventListener('click', function() {
            var myModal = new bootstrap.Modal(document.getElementById('addModal'));
            myModal.show();
        });

        function searchData() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll("#competencyTable tbody tr");

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? "" : "none";
            });
        }
    </script>
@endpush
