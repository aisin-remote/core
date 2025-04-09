@extends('layouts.root.main')

@section('title', 'Employee Competency Details')

@section('main')
<style>
    .btn-link {
        max-width: 200px;   
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        vertical-align: middle;
    }
    .nowrap {
        white-space: nowrap;
    }

</style>
    <div class="container">
        <!-- Card Employee Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Details</h3>
                <div class="card-tools">
                    <a href="{{ route('employeeCompetencies.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Foto Profil di Atas -->
                <div class="text-center mb-4">
                    <div class="d-inline-block">
                        <p class="fw-bold mb-2">Profile Picture</p>
                        @if ($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="Employee Photo"
                                class="shadow-sm img-fluid rounded-2"
                                style="width: 200px; height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-light p-5 rounded-2"
                                style="width: 200px; height: 200px; display: inline-flex; align-items: center; justify-content: center;">
                                <span class="text-muted">No Photo Available</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Detail Profil -->
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="row g-4">
                            <!-- Kolom Kiri -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Name</label>
                                    <input type="text" class="form-control" value="{{ $employee->name }}" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">NPK</label>
                                    <input type="text" class="form-control" value="{{ $employee->npk }}" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Gender</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                {{ $employee->gender == 'Male' ? 'checked' : '' }} disabled>
                                            <label class="form-check-label">Male</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                {{ $employee->gender == 'Female' ? 'checked' : '' }} disabled>
                                            <label class="form-check-label">Female</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Birth Date</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->birthday_date ?? 'N/A' }}" readonly>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Join Date</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->aisin_entry_date ?? 'N/A' }}" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Working Period</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            value="{{ $employee->working_period ?? '0' }}" readonly>
                                        <span class="input-group-text">Years</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Company Group</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->company_group ?? 'N/A' }}" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Department</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->departments->first()->name ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Baris Bawah -->
                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Company Name</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->company_name ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Position</label>
                                    <input type="text" class="form-control" value="{{ $employee->position ?? 'N/A' }}"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Position Name</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->position_name ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Grade</label>
                                    <input type="text" class="form-control" value="{{ $employee->grade ?? 'N/A' }}"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Competencies -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title d-inline">Competencies</h3>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addCompetencyModal">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">Competency Name</th>
                            <th class="text-center">Department</th>
                            <th class="text-center">Weight</th>
                            <th class="text-center">Plan</th>
                            <th class="text-center">Act</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">File</th>
                            <th class="text-center">Plan Date</th>
                            <th class="text-center">Due Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employee->employeeCompetencies as $ec)
                            <tr>
                                <td>{{ $ec->competency->name }}</td>
                                <td>{{ $ec->competency->department->name }}</td>

                                <td>
                                    <input type="number" name="weight" value="{{ $ec->weight }}"
                                        class="form-control form-control-sm" form="form-{{ $ec->id }}">
                                </td>
                                <td>
                                    <input type="number" name="plan" value="{{ $ec->plan }}"
                                        class="form-control form-control-sm" form="form-{{ $ec->id }}">
                                </td>
                                <td>
                                    <input type="number" name="act" value="{{ $ec->act }}"
                                        class="form-control form-control-sm" form="form-{{ $ec->id }}">
                                </td>
                                <td class="text-center">
                                    @if($ec->status == 1)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        @if($ec->files)
                                            <i class="bi bi-exclamation-circle-fill text-warning"></i>
                                        @else
                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                        @endif
                                    @endif
                                </td>
                            
                                <td>
                                    @if($ec->files)
                                    <a href="{{ asset('storage/' . $ec->files) }}"
                                        download="{{ basename($ec->files) }}"
                                        class="btn btn-link btn-sm text-decoration-none"
                                        title="Download {{ basename($ec->files) }}">
                                        <i class="bi bi-file-earmark-check me-1"></i>
                                        {{ basename($ec->files) }}
                                    </a>
                                    @endif

                                    @if($ec->status != 1)
                                        <input type="file" name="file" class="form-control form-control-sm mt-1"
                                            form="form-{{ $ec->id }}">
                                    @endif
                                </td>

                                <td class="nowrap">{{ \Carbon\Carbon::parse($ec->plan_date)->format('Y F d') }}</td>
                                <td class="nowrap">{{ \Carbon\Carbon::parse($ec->due_date)->format('Y F d') }}</td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <form method="POST" action="{{ route('employeeCompetencies.update', $ec->id) }}"
                                            id="form-{{ $ec->id }}" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="bi bi-save"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('employeeCompetencies.approve', $ec->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm" 
                                                {{ $ec->files && $ec->status != 1 ? '' : 'disabled' }}>
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        
                                        <form method="POST"
                                            action="{{ route('employeeCompetencies.destroy', $ec->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                data-id="{{ $ec->id }}" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Competency Modal -->
    <div class="modal fade" id="addCompetencyModal" tabindex="-1" aria-labelledby="addCompetencyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCompetencyModalLabel">Add New Competency</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('employeeCompetencies.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="employee_id[]" value="{{ $employee->id }}">

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Competency</label>
                                <select name="competency_id[]" class="form-select" id="modalCompetencySelect" multiple
                                    required>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Weight</label>
                                <input type="number" class="form-control" name="weight" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plan</label>
                                <input type="number" class="form-control" name="plan" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Act</label>
                                <input type="number" class="form-control" name="act" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plan Date</label>
                                <input type="date" class="form-control" name="plan_date" id="modalPlanDate" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" id="modalDueDate" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete Confirmation
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Initialize Select2 for modal
        const modalCompetencySelect = $('#modalCompetencySelect');
        modalCompetencySelect.select2({
            placeholder: "Select Competencies",
            dropdownParent: $('#addCompetencyModal'),
            multiple: true,
            width: '100%'
        });

        // Handle modal show event
        $('#addCompetencyModal').on('show.bs.modal', function() {
            const today = new Date().toISOString().split('T')[0];
            $('#modalPlanDate, #modalDueDate').attr('min', today);
            const position = "{{ $employee->position }}";
            const departmentId = "{{ $employee->departments->first()->id ?? '' }}";
            const employeeId = "{{ $employee->id }}";

            modalCompetencySelect.empty().trigger('change');

            if (position && departmentId) {
                const timestamp = new Date().getTime();
                fetch(
                        `/employee-competencies/get-competencies?position=${position}&department_id=${departmentId}&employee_id=${employeeId}&_=${timestamp}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(data => {
                        if (data.length === 0) {
                            $('#competencySelect').append(
                                '<option disabled>No available competencies</option>');
                            return;
                        }
                        data.forEach(competency => {
                            const option = new Option(competency.name, competency.id);
                            if (!modalCompetencySelect.find(
                                    `option[value="${competency.id}"]`).length) {
                                modalCompetencySelect.append(option);
                            }
                        });
                        modalCompetencySelect.trigger('change');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        $('#competencySelect').append(
                            '<option disabled>Error loading competencies</option>');
                    });
            } else {
                $('#competencySelect').append(
                    '<option disabled>Select position and department first</option>');
            }
        });

        document.querySelector('#addCompetencyModal form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                        });
                        $('#addCompetencyModal').modal('hide');
                        setTimeout(() => window.location.reload(),
                        1500);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    });
                });
        });

        // Handle Update Forms
        document.querySelectorAll('form[action*="/employeeCompetencies/"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!this.action.includes('destroy')) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: data.message || 'Competency updated successfully',
                            });
                            setTimeout(() => window.location.reload(), 1500);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Failed to update competency',
                            });
                        });
                }
            });
        });

        // Improve Delete Handling
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                }
                            })
                        .then(response => {
                            if (response.ok) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Competency deleted successfully',
                                });
                                setTimeout(() => window.location.reload(),
                                1500);
                            }
                        });
                    }
                });
            });
        });
    });
</script>
