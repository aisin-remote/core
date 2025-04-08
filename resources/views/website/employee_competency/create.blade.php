@extends('layouts.root.main')

@section('title', $title ?? 'Employee Competency')

@section('breadcrumbs', $title ?? 'Employee Competency')

@section('main')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form id="competencyForm" action="{{ route('employeeCompetencies.store') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4">Position & Department</h4>
                            <div class="mb-3">
                                <label class="form-label">Position</label>
                                <select class="form-select" id="positionSelect" required>
                                    <option value="">Select Position</option>
                                    <option value="General Manager">General Manager</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Coordinator">Coordinator</option>
                                    <option value="Section Head">Section Head</option>
                                    <option value="Supervisor">Supervisor</option>
                                    <option value="Act Leader">Act Leader</option>
                                    <option value="Act JP">Act JP</option>
                                    <option value="Operator">Operator</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="departmentSelect" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Employee</label>
                                <select name="employee_id[]" class="form-select" id="employeeSelect" multiple required>
                                    <option value="">Select Employee</option>
                                </select>
                                @error('employee_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Competency</label>
                                <select name="competency_id[]" class="form-select" id="competencySelect" multiple required>
                                    <option value="">Select Competency</option>
                                </select>
                                @error('competency_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4">Plan & Act</h4>
                            <div class="mb-3">
                                <label>Weight</label>
                                <input type="number" class="form-control" name="weight" required>
                            </div>
                            <div class="mb-3">
                                <label>Plan</label>
                                <input type="number" class="form-control" name="plan" required>
                            </div>
                            <div class="mb-3">
                                <label>Act</label>
                                <input type="number" class="form-control" name="act" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plan Date</label>
                                <input type="date" class="form-control" name="plan_date" min="{{ date('Y-m-d') }}" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('employeeCompetencies.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
       document.addEventListener('DOMContentLoaded', function() {
            $('#competencySelect').select2({
                placeholder: "Select Competencies",
                allowClear: true
            });

            $('#employeeSelect').select2({
                placeholder: "Select Employees",
                allowClear: true,
                multiple: true
            });

            const positionSelect = document.getElementById('positionSelect');
            const departmentSelect = document.getElementById('departmentSelect');

            function fetchFilteredData() {
                const position = positionSelect.value;
                const departmentId = departmentSelect.value;

                if (position && departmentId) {
                    // Fetch Employees
                    fetch(`/employee-competencies/get-employees?position=${position}&department_id=${departmentId}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#employeeSelect').empty().append('<option value="">Select Employee</option>');
                            data.forEach(employee => {
                                $('#employeeSelect').append(new Option(employee.name, employee.id));
                            });
                            $('#employeeSelect').trigger('change');
                        });

                    // Fetch Competencies
                    fetch(`/employee-competencies/get-competencies?position=${position}&department_id=${departmentId}`)
                        .then(response => response.json())
                        .then(data => {
                            $('#competencySelect').empty().append('<option value="">Select Competency</option>');
                            data.forEach(competency => {
                                $('#competencySelect').append(new Option(competency.name, competency.id));
                            });
                            $('#competencySelect').trigger('change');
                        });
                }
            }

            positionSelect.addEventListener('change', fetchFilteredData);
            departmentSelect.addEventListener('change', fetchFilteredData);
        });

        document.getElementById('competencyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

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
                if(data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-save"></i> Save';
            });
        });
    </script>
@endsection