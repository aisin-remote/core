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
    table td, table th {
        font-size: 14px;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .icon-eye {
    color: #ADD8E6; 
    vertical-align: middle;
  }
</style>
    <div class="container">
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
                            <th class="text-center">Competency</th>
                            <th class="text-center">Department</th>
                            <th class="text-center">Weight</th>
                            <th class="text-center">Plan</th>
                            <th class="text-center">Act</th>
                            <th class="text-center">Status Training</th>
                            <th class="text-center">Due Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employee->employeeCompetencies as $ec)
                            <tr>
                                <td class="text-center">{{ $ec->competency->name }}</td>
                                <td class="text-center">{{ $ec->competency->department->name }}</td>

                                <td class="text-center">{{ $ec->weight }}</td>
                                <td class="text-center">{{ $ec->plan }}</td>
                                <td class="text-center">{{ $ec->act }}</td>
                                <td class="text-center">
                                    @if($ec->status == 1)
                                        Approved
                                    @else
                                        Not Approved
                                    @endif
                                </td>

                                <td class="text-center">{{ \Carbon\Carbon::parse($ec->due_date)->format('Y F') }}</td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        @if($ec->act == 1)
                                            <form class="d-inline">
                                                <button type="button" class="btn btn-sm btn-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#checksheetIndexModal">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    
                                        @if($ec->files)
                                            <!-- View File -->
                                            <form method="GET"
                                                  action="{{ asset('storage/' . $ec->files) }}"
                                                  target="_blank"
                                                  class="d-inline">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            </form>

                                            @if($ec->status == 0)
                                                <!-- Edit File -->
                                                <form method="POST" 
                                                    action="{{ route('employeeCompetencies.update', $ec->id) }}" 
                                                    enctype="multipart/form-data"
                                                    class="ajax-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <label for="edit-file-input-{{ $ec->id }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </label>
                                                    <input type="file" 
                                                        name="file" 
                                                        id="edit-file-input-{{ $ec->id }}" 
                                                        style="display: none;">
                                                </form>
                                
                                                <!-- Approve Button -->
                                                <form method="POST" action="{{ route('employeeCompetencies.approve', $ec->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            @if($ec->status == 0)
                                                <!-- Upload File -->
                                                <form method="POST" 
                                                        action="{{ route('employeeCompetencies.update', $ec->id) }}" 
                                                        enctype="multipart/form-data"
                                                        class="ajax-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <label for="file-input-{{ $ec->id }}" class="btn btn-sm btn-info">
                                                        <i class="bi bi-upload"></i>
                                                    </label>
                                                    <input type="file" 
                                                            name="file" 
                                                            id="file-input-{{ $ec->id }}" 
                                                            style="display: none;">
                                                </form>
                                            @endif
                                        @endif
                                
                                        <!-- Delete Button (Selalu tampil) -->
                                        <form method="POST" 
                                                action="{{ route('employeeCompetencies.destroy', $ec->id) }}"
                                                class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm delete-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="card-tools">
                    <a href="{{ route('employeeCompetencies.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>
                </div>
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
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" id="modalDueDate" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('website.employee_competency.checksheet')
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

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const form = this.closest('form');
                const formData = new FormData(form);
                
                // AJAX request
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
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.error || 'Terjadi kesalahan',
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengupload file',
                    });
                });
            });
        });

        // Handle Update Forms (existing approve pop-up remains)
        document.querySelectorAll('form[action*="/employeeCompetencies/"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!this.action.includes('destroy')) {
                    e.preventDefault();
                    const isApprove = this.action.includes('approve');
                    const formData = new FormData(this);

                    if (isApprove) {
                        Swal.fire({
                            title: 'Approve Competency',
                            text: 'Are you sure you want to approve this competency?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, approve it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitForm(this, formData, isApprove);
                            }
                        });
                    } else {
                        submitForm(this, formData, isApprove);
                    }
                }
            });
        });

        function submitForm(form, formData, isApprove) {
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.error || data.message,
                    });
                }
                setTimeout(() => window.location.reload(), 1500);
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message,
                });
            });
        }

        // Improve Delete Handling
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 1500
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
