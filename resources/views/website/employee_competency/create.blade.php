@extends('layouts.root.main')

@section('title', $title ?? 'Employee Competency')

@section('breadcrumbs', $title ?? 'Employee Competency')

@section('main')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form id="competencyForm" action="{{ route('employeeCompetencies.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label>Employees</label>
                        <select name="employee_id[]" id="employeeSelect" class="form-select" multiple required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->name }} - 
                                    {{ $employee->position }} - 
                                    {{ $employee->departments->first()->name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="text-end mt-4">
                        <a href="{{ route('employeeCompetencies.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left-circle"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
       document.addEventListener('DOMContentLoaded', function() {
            $('#employeeSelect').select2({
                placeholder: "Pilih Karyawan",
                width: '100%',
                templateResult: function(employee) {
                    if (!employee.id) return employee.text;
                    const dept = $(employee.element).data('department');
                    return `${employee.text} (${dept})`;
                }
            });
            
            // const deptSelect = document.getElementById('departmentSelect');
            // const posSelect = document.getElementById('positionSelect');
            // const empSelect = $('#employeeSelect').select2();

            // function loadEmployees() {
            //     if (deptSelect.value && posSelect.value) {
            //         fetch(`/employee-competencies/get-employees?department_id=${deptSelect.value}&position=${posSelect.value}`)
            //             .then(res => res.json())
            //             .then(data => {
            //                 empSelect.empty();
            //                 data.forEach(emp => {
            //                     const option = new Option(emp.name, emp.id);
            //                     empSelect.append(option);
            //                 });
            //                 empSelect.trigger('change');
            //             });
            //     }
            // }

            // deptSelect.addEventListener('change', loadEmployees);
            // posSelect.addEventListener('change', loadEmployees);
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
            .then(response => {
                if (!response.ok) throw response;
                return response.json();
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = data.redirect;
                });
            })
            .catch(async (error) => {
                const errorData = await error.json();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorData.message || 'Terjadi kesalahan',
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-save"></i> Save';
            });
        });
    </script>
@endsection