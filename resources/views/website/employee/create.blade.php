@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
@endsection

@section('main')
    <div id="kt_app_content" class="app-content  flex-column-fluid ">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container  container-fluid ">

            <form action="{{ route('employee.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">NPK</label>
                            <input type="text" name="npk" class="form-control" value="{{ old('npk') }}">
                            @error('npk')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Identity Number</label>
                            <input type="text" name="identity_number" class="form-control"
                                value="{{ old('identity_number') }}">
                            @error('identity_number')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Birthday Date</label>
                            <input type="date" name="birthday_date" class="form-control"
                                value="{{ old('birthday_date') }}">
                            @error('birthday_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control">
                            @error('photo')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control"
                                value="{{ old('company_name') }}">
                            @error('company_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Function</label>
                            <input type="text" name="function" class="form-control" value="{{ old('function') }}">
                            @error('function')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Aisin Entry Date</label>
                            <input type="date" name="aisin_entry_date" class="form-control"
                                value="{{ old('aisin_entry_date') }}">
                            @error('aisin_entry_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Working Period</label>
                            <input type="number" name="working_period" class="form-control" min="0"
                                value="{{ old('working_period', 0) }}">
                            @error('working_period')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Company Group</label>
                            <input type="text" name="company_group" class="form-control"
                                value="{{ old('company_group') }}">
                            @error('company_group')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foundation Group</label>
                            <input type="text" name="foundation_group" class="form-control"
                                value="{{ old('foundation_group') }}">
                            @error('foundation_group')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="col-lg-12 mb-10">
                                <label class="form-label">Position</label>
                                <select name="position" aria-label="Select a Country" data-control="select2"
                                    data-placeholder="Select Position..."
                                    class="form-select form-select form-select-lg fw-semibold">
                                    <option value="">Select
                                        Position</option>
                                    <option data-kt-flag="flags/albania.svg" value="Manager">Manager</option>
                                    <option data-kt-flag="flags/albania.svg" value="Coordinator">Coordinator</option>
                                    <option data-kt-flag="flags/albania.svg" value="Section Head">Section Head</option>
                                    <option data-kt-flag="flags/albania.svg" value="Supervisor">Supervisor</option>
                                </select>
                            </div>
                            @error('position')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grade</label>
                            <input type="text" name="grade" class="form-control" value="{{ old('grade') }}">
                            @error('grade')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Promote Date</label>
                            <input type="date" name="last_promote_date" class="form-control"
                                value="{{ old('last_promote_date') }}">
                            @error('last_promote_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="{{ route('employee.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
