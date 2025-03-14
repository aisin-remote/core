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
                            <label class="form-label">Join Date</label>
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
                        </div>

                        <!-- Educational Background -->
                        <div class="col-md-6">
                            <div class="card p-4 shadow-sm rounded-3">
                                <h4 class="fw-bold mb-4 text-center">Educational Background</h4>
                                <div id="education-container">
                                    <div class="education-entry">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Degree</label>
                                                    <input type="text" name="degree[]" class="form-control"
                                                        placeholder="e.g., S1 - Teknik Industri">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">University</label>
                                                    <input type="text" name="university[]" class="form-control"
                                                        placeholder="e.g., Universitas Indonesia">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Year</label>
                                                    <input type="text" name="year[]" class="form-control"
                                                        placeholder="e.g., 2019 - 2022">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Button Add More -->
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-primary" onclick="addEducation()">Add
                                        More</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Card: Working Experience -->
                        <div class="col-md-6">
                            <div class="card p-4 shadow-sm rounded-3">
                                <h4 class="fw-bold mb-4 text-center">Working Experience</h4>
                                <div id="work-experience-container">
                                    <!-- Form Working Experience 1 -->
                                    <div class="work-entry">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Job Title</label>
                                                    <input type="text" name="job_title[]" class="form-control"
                                                        placeholder="e.g., Human Resource Manager">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Year</label>
                                                    <input type="text" name="work_period[]" class="form-control"
                                                        placeholder="e.g., 2024 - Present">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Button Add More -->
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-primary" onclick="addWorkExperience()">Add
                                        More</button>
                                </div>
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
    </div>


    <script>
        function addEducation() {
            let container = document.getElementById("education-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("education-entry", "mt-3");
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Degree</label>
                            <input type="text" name="degree[]" class="form-control" placeholder="e.g., S1 - Teknik Industri">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">University</label>
                            <input type="text" name="university[]" class="form-control" placeholder="e.g., Universitas Indonesia">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Year</label>
                            <input type="text" name="year[]" class="form-control" placeholder="e.g., 2019 - 2022">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">Remove</button>
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
        }

        function addWorkExperience() {
            let container = document.getElementById("work-experience-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("work-entry", "mt-3");
            newEntry.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="job_title[]" class="form-control" placeholder="e.g., Human Resource Manager" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="text" name="work_period[]" class="form-control" placeholder="e.g., 2024 - Present" required>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-danger btn-sm " onclick="removeWorkExperience(this)">Remove</button>
            </div>
        `;
            container.appendChild(newEntry);
        }

        function removeWorkExperience(button) {
            button.closest('.work-entry').remove();
        }

        function addPromotion() {
            let container = document.getElementById("promotion-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("promotion-entry", "mt-3");
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Previous Position</label>
                            <input type="text" name="previous_position[]" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Position</label>
                            <input type="text" name="current_position[]" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Promotion Date</label>
                            <input type="date" name="last_promotion_date[]" class="form-control" required>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">Remove</button>
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
        }

        function removeEntry(button) {
            button.closest('.promotion-entry, .education-entry').remove();
        }
    </script>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
