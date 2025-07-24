@extends('layouts.root.main')

@section('title')
    Edit User
@endsection

@section('breadcrumbs')
    Edit User
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
    @if (session()->has('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "{{ session('error') }}",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">User List</h3>
                <div class="d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control me-2" placeholder="Search User..."
                        style="width: 200px;" value="{{ request('search') }}">
                    <button type="button" class="btn btn-primary me-3" id="searchButton">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-center nowrap" style="min-width: 120px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role }}</td>
                                <td class="text-center nowrap" style="min-width: 120px;">
                                    <a href="{{ route('users.master.edit', $user->id) }}" class="btn btn-warning btn-sm text-center">
                                        Edit
                                    </a>
                                    {{-- <button type="button" class="btn btn-danger btn-sm delete-btn"
                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                        <i class="fa fa-trash"></i>
                                    </button> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .nowrap {
            white-space: nowrap;
        }

        .btn-xs {
            padding: 2px 6px;
            font-size: 12px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Search functionality
            document.getElementById('searchButton').addEventListener('click', function() {
                const search = document.getElementById('searchInput').value;
                const url = new URL(window.location.href);

                url.searchParams.set('search', search);
                url.searchParams.set('page', 1);

                window.location.href = url.toString();
            });

            // Delete confirmation
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    const userName = this.getAttribute('data-name');

                    Swal.fire({
                        title: "Are you sure?",
                        text: `You are about to delete user "${userName}". This action cannot be undone!`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/users/delete/${userId}`;

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
@endsection
