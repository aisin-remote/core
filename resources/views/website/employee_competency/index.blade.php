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
                <h3 class="card-title">Employee Competency List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" style="width: 200px;" class="form-control me-2" id="searchInput"
                        placeholder="Search...">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button class="btn btn-primary" onclick="window.location.href='{{ route('employeeCompetencies.create') }}'">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="competencyTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Employee</th>
                            <th>NPK</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Total Competencies</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->npk }}</td>
                                <td>{{ $employee->departments->first()->name ?? '-' }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $employee->company_name }}</td>
                                <td>{{ $employee->employeeCompetencies->count() }}</td>
                                <td class="text-center">
                                    <a href="{{ route('employeeCompetencies.show', $employee->id) }}" 
                                        class="btn btn-info btn-sm">
                                         <i class="bi bi-eye"></i> Detail
                                     </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No employee competencies found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('#competency_id, #employee_id').select2({
                placeholder: "Select an option",
                allowClear: true,
                minimumResultsForSearch: Infinity
            });

            // Fungsi pencarian
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const tableRows = document.querySelectorAll('#competencyTable tbody tr');

            const performSearch = () => {
                const filter = searchInput.value.toLowerCase();
                tableRows.forEach(row => {
                    const textContent = row.textContent.toLowerCase();
                    row.style.display = textContent.includes(filter) ? '' : 'none';
                });
            };

            // Event listeners untuk pencarian
            searchInput.addEventListener('keyup', performSearch);
            searchButton.addEventListener('click', performSearch);

            // Fungsi untuk delete
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('delete-btn')) {
                    const employeeCompetencyId = event.target.getAttribute('data-id');
                    
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
                            fetch(`/employeeCompetencies/${employeeCompetencyId}`, {
                                method: "DELETE",
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "Content-Type": "application/json"
                                }
                            })
                            .then(response => {
                                if (!response.ok) throw new Error('Network response was not ok');
                                return response.json();
                            })
                            .then(data => {
                                Swal.fire("Deleted!", data.message, "success");
                                setTimeout(() => window.location.reload(), 1000);
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire("Error!", "Failed to delete data", "error");
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush