@extends('layouts.root.main')

@section('title', $title ?? 'Create Competency')
@section('breadcrumbs', $title ?? 'Create Competency')

@section('main')
<div class="container">
    <h1 class="mb-4">{{ $title }}</h1>
    <form action="{{ route('competency.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan competency" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Masukkan deskripsi"></textarea>
        </div>
        <div class="mb-3">
            <label for="group_competency_id" class="form-label">Group Competency</label>
            <select name="group_competency_id" id="group_competency_id" class="form-select">
                <option value="">Select Department</option>
                <option value="1" {{ old('group_competency_id') == '1' ? 'selected' : '' }}>Basic</option>
                <option value="2" {{ old('group_competency_id') == '2' ? 'selected' : '' }}>Functional</option>
                <option value="3" {{ old('group_competency_id') == '3' ? 'selected' : '' }}>Managerial</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="dept_id" class="form-label">Department</label>
            <select name="dept_id" id="dept_id" class="form-select">
                <option value="">Select Department</option>
                <option value="1" {{ old('dept_id') == '1' ? 'selected' : '' }}>HR</option>
                <option value="2" {{ old('dept_id') == '2' ? 'selected' : '' }}>IT</option>
                <option value="3" {{ old('dept_id') == '3' ? 'selected' : '' }}>Finance</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="role_id" class="form-label">Role</label>
            <select name="role_id" id="role_id" class="form-select">
                <option value="">Select Role</option>
                <option value="1" {{ old('role_id') == '1' ? 'selected' : '' }}>Manager</option>
                <option value="2" {{ old('role_id') == '2' ? 'selected' : '' }}>Supervisor</option>
                <option value="3" {{ old('role_id') == '3' ? 'selected' : '' }}>JP</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
