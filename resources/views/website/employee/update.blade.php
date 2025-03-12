@extends('layouts.root.main')

@section('main')
    <div class="container mt-4">
        <h3>Update Employee</h3>

        <form action="{{ route('employee.update', $employee->npk) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}">
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NPK</label>
                        <input type="text" name="npk" class="form-control" value="{{ old('npk', $employee->npk) }}">
                        @error('npk') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male" {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birthday_date" class="form-control" value="{{ old('birthday_date', $employee->birthday_date) }}">
                        @error('birthday_date') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="photo" class="form-control">
                        @error('photo') <div class="text-danger">{{ $message }}</div> @enderror
                        @if ($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="Employee Photo" class="img-thumbnail mt-2" width="100">
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <select name="company_name" class="form-control">
                            <option value="">-- Select Company --</option>
                            <option value="Aisin Indonesia"
                                {{ old('company_name', $employee->company_name) == 'Aisin Indonesia' ? 'selected' : '' }}>
                                Aisin Indonesia
                            </option>
                            <option value="Aisin Indonesia Automotive"
                                {{ old('company_name', $employee->company_name) == 'Aisin Indonesia Automotive' ? 'selected' : '' }}>
                                Aisin Indonesia Automotive
                            </option>
                        </select>
                        @error('company_name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" name="function" class="form-control" value="{{ old('function', $employee->function) }}">
                        @error('function') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Join Date</label>
                        <input type="date" name="aisin_entry_date" class="form-control" value="{{ old('aisin_entry_date', $employee->aisin_entry_date) }}">
                        @error('aisin_entry_date') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Working Period</label>
                        <input type="number" name="working_period" class="form-control" min="0" value="{{ old('working_period', $employee->working_period) }}">
                        @error('working_period') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Company Group</label>
                        <input type="text" name="company_group" class="form-control" value="{{ old('company_group', $employee->company_group) }}">
                        @error('company_group') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}">
                        @error('position') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grade</label>
                        <input type="text" name="grade" class="form-control" value="{{ old('grade', $employee->grade) }}">
                        @error('grade') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position Name</label>
                        <input type="text" name="position_name" class="form-control" value="{{ old('position_name', $employee->position_name) }}">
                        @error('position_name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
                <a href="{{ route('employee.master.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save
                </button>
            </div>
        </form>
    </div>
@endsection
