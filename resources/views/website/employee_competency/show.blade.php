@extends('layouts.root.main')

@section('title', 'Employee Competency Details')

@section('main')
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
            <div class="row">
                <div class="col-md-6">
                    <p><strong>NPK:</strong> {{ $employee->npk }}</p>
                    <p><strong>Name:</strong> {{ $employee->name }}</p>
                    <p><strong>Gender:</strong> {{ $employee->gender }}</p>
                    <p><strong>Birthday:</strong> {{ $employee->birthday_date }}</p>
                    <p><strong>Position:</strong> {{ $employee->position }}</p>
                    <p><strong>Department:</strong> {{ $employee->departments->first()->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Competencies -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title d-inline">Competencies</h3>
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#addCompetencyModal">
                <i class="bi bi-plus"></i> Add Competency
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Competency Name</th>
                        <th>Department</th>
                        <th>Weight</th>
                        <th>Plan</th>
                        <th>Act</th>
                        <th>Plan Date</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employee->employeeCompetencies as $ec)
                    <tr>
                        <td>{{ $ec->competency->name }}</td>
                        <td>{{ $ec->competency->department->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('employeeCompetencies.update', $ec->id) }}">
                                @csrf
                                @method('PUT')
                                <input type="number" name="weight" value="{{ $ec->weight }}" class="form-control">
                        </td>
                        <td>
                            <input type="number" name="plan" value="{{ $ec->plan }}" class="form-control">
                        </td>
                        <td>
                            <input type="number" name="act" value="{{ $ec->act }}" class="form-control">
                        </td>
                        <td>{{ \Carbon\Carbon::parse($ec->plan_date)->format('Y F d') }}</td>
                        <td>{{ \Carbon\Carbon::parse($ec->due_date)->format('Y F d') }}</td>
                        <td>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-save"></i> Save
                            </button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('employeeCompetencies.destroy', $ec->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm delete-btn" 
                                    data-id="{{ $ec->id }}" 
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Competency Modal -->
<div class="modal fade" id="addCompetencyModal" tabindex="-1" aria-labelledby="addCompetencyModalLabel" aria-hidden="true">
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
                            <select name="competency_id[]" class="form-select" id="modalCompetencySelect" multiple required>
                                <!-- Options akan diisi via JavaScript -->
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
        
        // Clear existing options
        modalCompetencySelect.empty().trigger('change');
        
        if(position && departmentId) {
            // Tambahkan cache busting
            const timestamp = new Date().getTime();
            fetch(`/employee-competencies/get-competencies?position=${position}&department_id=${departmentId}&employee_id=${employeeId}&_=${timestamp}`)
                .then(response => {
                    if(!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    if(data.length === 0) {
                        $('#competencySelect').append('<option disabled>No available competencies</option>');
                        return;
                    }
                    
                    // Tambahkan opsi kompetensi
                    data.forEach(competency => {
                        const option = new Option(competency.name, competency.id);
                        if(!modalCompetencySelect.find(`option[value="${competency.id}"]`).length) {
                            modalCompetencySelect.append(option);
                        }
                    });
                    modalCompetencySelect.trigger('change');
                })
                .catch(error => {
                    console.error('Error:', error);
                    $('#competencySelect').append('<option disabled>Error loading competencies</option>');
                });
        } else {
            $('#competencySelect').append('<option disabled>Select position and department first</option>');
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
            if(data.message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                });
                $('#addCompetencyModal').modal('hide');
                setTimeout(() => window.location.reload(), 1500); // Refresh setelah 1.5 detik
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
                        if(response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Competency deleted successfully',
                            });
                            setTimeout(() => window.location.reload(), 1500);
                        }
                    });
                }
            });
        });
    });
});
</script>