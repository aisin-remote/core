@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Group Competency' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Group Competency' }}
@endsection

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Sukses!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Group Competency List</h3>
                <div class="d-flex align-items-center">
                    <div class="position-relative">
                        <input type="text" id="searchInput" class="form-control ps-10" placeholder="Search name or description..." style="width: 250px;">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-5">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <a href="{{ route('group_competency.create') }}" class="btn btn-primary ms-3">
                        <i class="fas fa-plus"></i> Add
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="groupCompetencyTable">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>No</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold">
                            @forelse ($group_competency as $index => $group)
                                <tr class="search-row">
                                    <td>{{ $index + 1 }}</td>
                                    <td class="search-name">{{ $group->name }}</td>
                                    <td class="search-desc">{{ $group->description }}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('group_competency.edit', $group->id) }}" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $group->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Enhanced Search Function
            const searchInput = document.getElementById('searchInput');
            
            if(searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchValue = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('#groupCompetencyTable tbody tr.search-row');
                    
                    let hasResults = false;
                    
                    rows.forEach(row => {
                        const name = row.querySelector('.search-name').textContent.toLowerCase();
                        const desc = row.querySelector('.search-desc').textContent.toLowerCase();
                        
                        if(name.includes(searchValue) || desc.includes(searchValue)) {
                            row.style.display = '';
                            hasResults = true;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    // Show no results message if needed
                    const noResultsRow = document.querySelector('#groupCompetencyTable tbody tr:not(.search-row)');
                    if(!hasResults && rows.length > 0) {
                        if(!noResultsRow) {
                            const tbody = document.querySelector('#groupCompetencyTable tbody');
                            const tr = document.createElement('tr');
                            tr.innerHTML = '<td colspan="4" class="text-center text-muted">No matching records found</td>';
                            tbody.appendChild(tr);
                        }
                    } else if(noResultsRow) {
                        noResultsRow.remove();
                    }
                });
            }

            // Delete Confirmation
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const groupId = this.getAttribute('data-id');

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
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/group_competency/${groupId}`;

                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';

                            const methodField = document.createElement('input');
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
@endpush