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
                    <select class="form-select me-2" id="positionFilter" style="width: 200px;">
                        <option value="all">All Positions</option>
                        <option value="General Manager">General Manager</option>
                        <option value="Manager">Manager</option>
                        <option value="Coordinator">Coordinator</option>
                        <option value="Section Head">Section Head</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Act Leader">Act Leader</option>
                        <option value="Act JP">Act JP</option>
                        <option value="Operator">Operator</option>
                    </select>
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
                            <th>Photo</th>
                            <th>Name</th>
                            <th>NPK</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Progress</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            @php
                                $completed = 0;
                                $total = $employee->employeeCompetencies->count();
                                
                                foreach ($employee->employeeCompetencies as $competency) {
                                    if ($competency->act >= $competency->plan) {
                                        $completed++;
                                    }
                                }
                                
                                $progressPercentage = $total > 0 ? round(($completed / $total) * 100, 2) : 0;
                                $progressClass = $completed == $total ? 'bg-success' : 'bg-warning';
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : asset('assets/media/avatars/300-1.jpg') }}"
                                        alt="Employee Photo" class="rounded" width="40" height="40"
                                        style="object-fit: cover;">
                                </td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->npk }}</td>
                                <td>{{ $employee->departments->first()->name ?? '-' }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $employee->company_name }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress w-100" style="height: 20px;">
                                            <div class="progress-bar progress-bar-striped {{ $progressClass }}" 
                                                role="progressbar" 
                                                style="width: {{ $progressPercentage }}%; min-width: 40px;"
                                                aria-valuenow="{{ $completed }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="{{ $total }}">
                                                {{ $completed }}/{{ $total }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
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
            const positionFilter = document.getElementById('positionFilter');
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const tableRows = document.querySelectorAll('#competencyTable tbody tr');

            const performSearch = () => {
                const filter = searchInput.value.toLowerCase();
                const selectedPosition = positionFilter.value;
                
                tableRows.forEach(row => {
                    const position = row.children[4].textContent.trim();
                    const textContent = row.textContent.toLowerCase();
                    
                    // Filter berdasarkan position dan search input
                    const positionMatch = selectedPosition === 'all' || position === selectedPosition;
                    const searchMatch = textContent.includes(filter);
                    
                    row.style.display = (positionMatch && searchMatch) ? '' : 'none';
                });
            };

            // Event listeners untuk pencarian
            positionFilter.addEventListener('change', performSearch);
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