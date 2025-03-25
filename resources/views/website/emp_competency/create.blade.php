@extends('layouts.root.main')

@section('title', $title ?? 'Emp Competency')
@section('breadcrumbs', $title ?? 'Emp Competency')

@section('main')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create Employee Competency</h3>
        </div>
        <div class="card-body">
            <form id="competencyForm">
                <!-- Role Selection -->
                <div class="mb-3">
                    <label for="roleSelect" class="form-label">Role</label>
                    <select class="form-select" id="roleSelect" required>
                        <option value="" selected disabled>Select Role</option>
                        <option value="Operator">Operator</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Manager">Manager</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>

                <!-- Department Selection -->
                <div class="mb-3">
                    <label for="departmentSelect" class="form-label">Department</label>
                    <select class="form-select" id="departmentSelect" required>
                        <option value="" selected disabled>Select Department</option>
                        <option value="ENG">Engineering</option>
                        <option value="HRD">Human Resources</option>
                        <option value="ACC">Accounting</option>
                        <option value="PROD">Production</option>
                    </select>
                </div>

                <!-- User Selection (Combobox) -->
                <div class="mb-4">
                    <label for="userSelect" class="form-label">Employee</label>
                    <input list="userList" id="userSelect" class="form-control" placeholder="Type or select employee" required>
                    <datalist id="userList">
                        <option value="Tatang Sutarman">
                        <option value="Daniel Wijaya">
                        <option value="Budi Santoso">
                        <option value="Ani Rahayu">
                    </datalist>
                </div>

                <!-- Basic Competency Table -->
                <div class="mb-4">
                    <h4 class="mb-3">Basic Competency</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Competency</th>
                                    <th>Weight</th>
                                    <th>Plan</th>
                                    <th>Act</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Organizational Awareness</td>
                                    <td><input type="number" class="form-control form-control-sm" value="2" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="3" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="3" min="1" max="4"></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" style="transform: scale(1.5)">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Communication</td>
                                    <td><input type="number" class="form-control form-control-sm" value="3" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="4" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="2" min="1" max="4"></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" style="transform: scale(1.5)">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Teamwork</td>
                                    <td><input type="number" class="form-control form-control-sm" value="2" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="3" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="1" min="1" max="4"></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" style="transform: scale(1.5)">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Functional Competency Table -->
                <div class="mb-4">
                    <h4 class="mb-3">Functional Competency</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-warning">
                                <tr>
                                    <th>Competency</th>
                                    <th>Weight</th>
                                    <th>Plan</th>
                                    <th>Act</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Technical Skills</td>
                                    <td><input type="number" class="form-control form-control-sm" value="4" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="4" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="3" min="1" max="4"></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" style="transform: scale(1.5)">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Problem Solving</td>
                                    <td><input type="number" class="form-control form-control-sm" value="3" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="4" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="2" min="1" max="4"></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" style="transform: scale(1.5)">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Managerial Competency Table -->
                <div class="mb-4">
                    <h4 class="mb-3">Managerial Competency</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-success">
                                <tr>
                                    <th>Competency</th>
                                    <th>Weight</th>
                                    <th>Plan</th>
                                    <th>Act</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Leadership</td>
                                    <td><input type="number" class="form-control form-control-sm" value="4" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="4" min="1" max="4"></td>
                                    <td><input type="number" class="form-control form-control-sm" value="3" min="1" max="4"></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" style="transform: scale(1.5)">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-light me-2">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('competencyForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Collect form data
        const formData = {
            role: document.getElementById('roleSelect').value,
            department: document.getElementById('departmentSelect').value,
            employee: document.getElementById('userSelect').value,
            basic: [],
            functional: [],
            managerial: []
        };

        // Collect Basic competency data
        const basicTable = document.querySelectorAll('.table-primary + .table-responsive tbody tr');
        basicTable.forEach(row => {
            const cells = row.cells;
            formData.basic.push({
                competency: cells[0].textContent,
                weight: cells[1].querySelector('input').value,
                plan: cells[2].querySelector('input').value,
                act: cells[3].querySelector('input').value,
                completed: cells[4].querySelector('input').checked
            });
        });

        // Collect Functional competency data
        const functionalTable = document.querySelectorAll('.table-warning + .table-responsive tbody tr');
        functionalTable.forEach(row => {
            const cells = row.cells;
            formData.functional.push({
                competency: cells[0].textContent,
                weight: cells[1].querySelector('input').value,
                plan: cells[2].querySelector('input').value,
                act: cells[3].querySelector('input').value,
                completed: cells[4].querySelector('input').checked
            });
        });

        // Collect Managerial competency data
        const managerialTable = document.querySelectorAll('.table-success + .table-responsive tbody tr');
        managerialTable.forEach(row => {
            const cells = row.cells;
            formData.managerial.push({
                competency: cells[0].textContent,
                weight: cells[1].querySelector('input').value,
                plan: cells[2].querySelector('input').value,
                act: cells[3].querySelector('input').value,
                completed: cells[4].querySelector('input').checked
            });
        });

        // Here you would typically send the data to the server
        console.log('Form data:', formData);
        
        // Show success message
        Swal.fire({
            title: 'Success!',
            text: 'Employee competency has been saved',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    });
});
</script>
@endpush