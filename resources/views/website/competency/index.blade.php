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
                            <th>Description</th>
                            <th>Group Competency</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($competencies as $index => $competency)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $competency->name }}</td>
                                <td>{{ $competency->description }}</td>
                                <td>{{ $competency->group_competency->name }}</td>
                                <td>{{ $competency->department->name }}</td>
                                <td>{{ $competency->position }}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm edit-btn" data-bs-target="#editmodal"
                                        data-id="{{ $competency->id }}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $competency->id }}">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No competencies found</td>
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

                // ✅ Handle Edit Button
                // if (target.classList.contains('edit-btn')) {
                //     let competencyId = target.getAttribute('data-id');
                //     console.log("Editing competency with ID:", competencyId);

                //     fetch(`{{ url('competencies') }}/${competencyId}/edit`)
                //         .then(response => response.json())
                //         .then(data => {
                //             console.log("Fetched Data:", data); // Debugging

                //             document.getElementById('edit_id').value = data.id;
                //             document.getElementById('edit_name').value = data.name;
                //             document.getElementById('edit_description').value = data.description;

                //             let groupSelect = document.getElementById('edit_group_competency_id');
                //             let departmentSelect = document.getElementById('edit_department_id');
                //             let positionSelect = document.getElementById('edit_position');

                //             // Reset dropdown sebelum mengisi ulang
                //             groupSelect.innerHTML = '<option value="">Select Group</option>';
                //             departmentSelect.innerHTML = '<option value="">Select Department</option>';
                //             positionSelect.innerHTML = '<option value="">Select Position</option>';

                //             // Tambahkan semua opsi untuk group competency
                //             data.all_groups.forEach(group => {
                //                 let selected = group.id == data.group_competency_id ?
                //                     "selected" : "";
                //                 groupSelect.innerHTML +=
                //                     `<option value="${group.id}" ${selected}>${group.name}</option>`;
                //             });

                //             // Tambahkan semua opsi untuk department
                //             data.all_departments.forEach(department => {
                //                 let selected = department.id == data.department_id ?
                //                     "selected" : "";
                //                 departmentSelect.innerHTML +=
                //                     `<option value="${department.id}" ${selected}>${department.name}</option>`;
                //             });

                //             // 🔹 Perbaiki pengisian dropdown position
                //             data.all_positions.forEach(pos => {
                //                 let selected = pos === data.position ? "selected" : "";
                //                 positionSelect.innerHTML +=
                //                     `<option value="${pos}" ${selected}>${pos}</option>`;
                //             });

                //             // Tampilkan modal edit
                //             const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                //             editModal.show();
                //         })
                //         .catch(error => console.error('Error fetching data:', error));


                // }

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

            // Handle Submit Form Edit
            // editForm.addEventListener('submit', function(event) {
            //     event.preventDefault();
            //     let formData = new FormData(editForm);
            //     let competencyId = document.getElementById('edit_id').value;

            //     fetch(`{{ url('competencies') }}/${competencyId}`, {
            //             method: "POST",
            //             body: formData,
            //             headers: {
            //                 "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
            //                 "X-HTTP-Method-Override": "PUT"
            //             }
            //         })
            //         .then(response => response.json())
            //         .then(data => {
            //             Swal.fire("Updated!", data.message, "success");
            //             location.reload();
            //         })
            //         .catch(error => console.error('Error:', error));
            // });
        });
    </script>
@endpush
