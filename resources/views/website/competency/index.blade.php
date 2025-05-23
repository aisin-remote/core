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
                            <th>Group Competency</th>
                            <th>Position</th>
                            <th>Sub Section</th>
                            <th>Department</th>
                            <th>Weight</th>
                            <th>Plan</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($competencies as $i => $c)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $c->name }}</td>
                                <td class="text-center">{{ $c->group_competency->name }}</td>
                                <td class="text-center">{{ $c->position }}</td>
                            
                                {{-- Sub Section --}}
                                <td class="text-center">{{ $c->sub_section?->name ?? '-' }}</td>
                            
                                {{-- Section --}}
                                {{-- <td>
                                    @if($c->sub_section)
                                        {{ $c->sub_section->section->name }}
                                    @elseif($c->section)
                                        {{ $c->section->name }}
                                    @else
                                        -
                                    @endif
                                </td> --}}
                            
                                {{-- Department --}}
                                <td class="text-center">
                                    @if($c->sub_section)
                                        {{ $c->sub_section->section->department->name }}
                                    @elseif($c->section)
                                        {{ $c->section->department->name }}
                                    @elseif($c->department)
                                        {{ $c->department->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                            
                                {{-- Division --}}
                                {{-- <td>
                                    @if($c->sub_section)
                                        {{ $c->sub_section->section->department->division->name }}
                                    @elseif($c->section)
                                        {{ $c->section->department->division->name }}
                                    @elseif($c->department)
                                        {{ $c->department->division->name }}
                                    @elseif($c->division)
                                        {{ $c->division->name }}
                                    @else
                                        -
                                    @endif
                                </td> --}}
                            
                                {{-- Plant --}}
                                {{-- <td>
                                    @if($c->sub_section)
                                        {{ $c->sub_section->section->department->division->plant->name }}
                                    @elseif($c->section)
                                        {{ $c->section->department->division->plant->name }}
                                    @elseif($c->department)
                                        {{ $c->department->division->plant->name }}
                                    @elseif($c->division)
                                        {{ $c->division->plant->name }}
                                    @elseif($c->plant)
                                        {{ $c->plant->name }}
                                    @else
                                        -
                                    @endif
                                </td> --}}
                                <td class="text-center">{{ $c->weight }}</td>
                                <td class="text-center">{{ $c->plan   }}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm edit-btn" data-bs-target="#editmodal"
                                        data-id="{{ $c->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $c->id }}">
                                        <i class="bi bi-trash"></i> 
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">No competencies found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('website.competency.modal')
    @include('website.competency.update')
@endsection



@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inisialisasi Select2
            $('#group_competency_id, #department_id, #employee_id').select2({
                placeholder: "Select an option",
                allowClear: true,
                minimumResultsForSearch: Infinity
            });

            // Elemen yang sering digunakan
            const addForm = document.getElementById('addForm');
            const editForm = document.getElementById('editForm');
            const searchInput = document.getElementById('searchInput');
            const openAddModal = document.getElementById('openAddModal');

            // Fungsi untuk menampilkan modal tambah
            openAddModal.addEventListener('click', function() {
                const addModal = new bootstrap.Modal(document.getElementById('addModal'));
                addModal.show();
            });

            // Fungsi untuk mencari data dalam tabel
            searchInput.addEventListener('keyup', function() {
                let input = searchInput.value.toLowerCase();
                document.querySelectorAll("#competencyTable tbody tr").forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
                });
            });

            // Handle Submit Form Tambah
            addForm.addEventListener('submit', function(event) {
                event.preventDefault();
                let formData = new FormData(addForm);

                fetch("{{ route('competencies.store') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire("Success", data.message, "success");
                        location.reload();
                    })
                    .catch(error => console.error('Error:', error));
            });

            // Delegasi Event untuk tombol Edit dan Delete
            document.addEventListener('click', function(event) {
                let target = event.target;

                // Jika tombol delete diklik
                if (target.classList.contains('delete-btn')) {
                    let competencyId = target.getAttribute('data-id');

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
                            fetch(`{{ url('competencies') }}/${competencyId}`, {
                                    method: "DELETE",
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector(
                                            'input[name="_token"]').value
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    Swal.fire("Deleted!", data.message, "success");
                                    location.reload();
                                })
                                .catch(error => console.error('Error:', error));
                        }
                    });
                }
            });
        });
    </script>
@endpush
