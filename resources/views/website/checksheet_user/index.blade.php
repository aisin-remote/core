@extends('layouts.root.main')

@section('title')
    Evaluation
@endsection

@section('breadcrumbs')
    Evaluation
@endsection

@section('main')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Evaluation List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" style="width: 200px;" class="form-control me-2" id="searchInput"
                        placeholder="Search..." onkeyup="searchData()">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button class="btn btn-primary" id="openAddModal">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="checksheetUserTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>Question</th>
                            <th>Position</th>
                            <th>Competency</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checksheetUsers as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $user->question }}</td>
                                <td>{{ $user->position }}</td>
                                <td>{{ $user->competency->name }}</td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $user->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No Checksheet User found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-4">
                    {{ $checksheetUsers->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
    @include('website.checksheet_user.modal')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Elemen yang sering digunakan
            const addForm = document.getElementById('addForm');
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
                document.querySelectorAll("#checksheetUserTable tbody tr").forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
                });
            });

            // Handle Submit Form Tambah
            addForm.addEventListener('submit', function(event) {
                event.preventDefault();
                let formData = new FormData(addForm);

                fetch("{{ route('checksheet_user.store') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire("Success", data.message, "success");
                            location.reload();
                        } else {
                            Swal.fire("Error", data.message, "error");
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            // Delegasi Event untuk tombol Delete
            document.addEventListener('click', function(event) {
                let target = event.target;
                if (target.classList.contains('delete-btn')) {
                    let userId = target.getAttribute('data-id');

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
                            fetch(`{{ url('checksheet_user') }}/${userId}`, {
                                    method: "DELETE",
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire("Deleted!", data.message, "success");
                                        location.reload();
                                    } else {
                                        Swal.fire("Error", data.message, "error");
                                    }
                                })
                                .catch(error => console.error('Error:', error));
                        }
                    });
                }
            });
        });
    </script>
@endpush