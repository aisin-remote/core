@extends('layouts.root.main')

@section('title', $title ?? 'Edit Competency')
@section('breadcrumbs', $title ?? 'Edit Competency')

@section('main')
<div class="container">
    <h1 class="mb-4">{{ $title }}</h1>
    <form action="{{ route('competency.update', $competency->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="competency" class="form-label">Competency</label>
            <input type="text" class="form-control" id="competency" name="competency" value="{{ old('competency', $competency->competency) }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $competency->description) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="group_competency" class="form-label">Group Competency</label>
            <input type="text" class="form-control" id="group_competency" name="group_competency" value="{{ old('group_competency', $competency->group_competency) }}" required>
        </div>
        <div class="mb-3">
            <label for="department" class="form-label">Department</label>
            <select name="department" id="department" class="form-select">
                <option value="">Select Department</option>
                <option value="HR" {{ old('department', $competency->department) == 'HR' ? 'selected' : '' }}>HR</option>
                <option value="IT" {{ old('department', $competency->department) == 'IT' ? 'selected' : '' }}>IT</option>
                <option value="Finance" {{ old('department', $competency->department) == 'Finance' ? 'selected' : '' }}>Finance</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select">
                <option value="">Select Role</option>
                <option value="Admin" {{ old('role', $competency->role) == 'Admin' ? 'selected' : '' }}>Admin</option>
                <option value="User" {{ old('role', $competency->role) == 'User' ? 'selected' : '' }}>User</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
