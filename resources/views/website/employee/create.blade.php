@extends('layouts.root.main')

@section('title', $title ?? 'Employee')

@section('breadcrumbs', $title ?? 'Employee')

@section('main')
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
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form action="{{ route('employee.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4">Personal Information</h4>
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
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female
                                    </option>
                                </select>
                                @error('gender')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4">Company Information</h4>
                            <div class="mb-3">
                                <label class="form-label">Join Date</label>
                                <input type="date" name="aisin_entry_date" class="form-control"
                                    value="{{ old('aisin_entry_date') }}">
                                @error('aisin_entry_date')
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
                                <div class="col-lg-12 mb-3">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <label class="form-label">Company</label>
                                    </label>
                                    <select name="company_name" aria-label="Select a Country" data-control="select2"
                                        data-placeholder="Select Company..." class="form-select form-select-lg fw-semibold">
                                        <option value="">Select Company</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AIIA">
                                            Aisin Indonesia Automotive
                                        </option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="AII">
                                            Aisin Indonesia
                                        </option>
                                    </select>
                                </div>
                                @error('company_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            @if (auth()->user()->role === 'HRD')
                                <div class="mb-3">
                                    <div class="col-lg-12 mb-3">
                                        <label class="fs-5 fw-bold form-label mb-2">
                                            <label class="form-label">Department</label>
                                        </label>
                                        <select name="department_id" aria-label="Pilih Departemen" data-control="select2"
                                            data-placeholder="Pilih departemen"
                                            class="form-select form-select-lg  fw-semibold">
                                            <option value="">Pilih Departemen</option>
                                            @foreach ($departments as $department)
                                                <option data-kt-flag="flags/afghanistan.svg" value="{{ $department->id }}">
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <div class="mb-3">
                                <div class="col-lg-12 mb-3">
                                    <label class="fs-5 fw-bold form-label mb-2">
                                        <label class="form-label">Position</label>
                                    </label>
                                    <select name="position" aria-label="Select a Country" data-control="select2"
                                        data-placeholder="Select Position..."
                                        class="form-select form-select-lg fw-semibold">
                                        <option value="">Select Position</option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="GM">General
                                            Manager
                                        </option>
                                        <option data-kt-flag="flags/afghanistan.svg" value="Manager">Manager</option>
                                        <option data-kt-flag="flags/aland-islands.svg" value="Coordinator">Coordinator
                                        </option>
                                        <option data-kt-flag="flags/albania.svg" value="Section Head">Section Head
                                        </option>
                                        <option data-kt-flag="flags/algeria.svg" value="Supervisor">Supervisor</option>
                                        <option data-kt-flag="flags/algeria.svg" value="Act Leader">Act Leader</option>
                                        <option data-kt-flag="flags/algeria.svg" value="Act JP">Act JP</option>
                                        <option data-kt-flag="flags/algeria.svg" value="Operator">Operator</option>
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
                        </div>
                    </div>
                </div>

                <!-- Educational Background & Working Experience -->
                <div class="row mt-4">
                    <div class="col-lg-12 mb-6">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4 text-center">Educational Background</h4>
                            <div id="education-container"></div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" onclick="addEducation()">Add More</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h4 class="fw-bold mb-4 text-center">Working Experience</h4>
                            <div id="work-experience-container"></div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" onclick="addWorkExperience()">Add
                                    More</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
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
@endsection

@push('scripts')
    <script>
        function addEducation() {
            let container = document.getElementById("education-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("education-entry", "p-3", "rounded", "mt-3", "position-relative");

            newEntry.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-1">
                    <label class="form-label">Degree</label>
                    <select name="level[]" aria-label="Select a Category"
                        data-control="select2"
                        data-placeholder="Select categories..."
                        class="form-select form-select-lg fw-semibold">
                        <option value="">Select Category</option>
                        <option value="SMK">
                            SMK</option>
                        <option value="D3">
                            D3</option>
                        <option value="S1">
                            S1</option>
                        <option value="S2">
                            S2</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Major</label>
                    <input type="text" name="major[]" class="form-control" placeholder="e.g., S1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">University</label>
                    <input type="text" name="institute[]" class="form-control" placeholder="e.g., Universitas Indonesia">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date[]" class="form-control" placeholder="e.g., 2019">
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <div class="w-100">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date[]" class="form-control" placeholder="e.g., 2022">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm ms-2 mt-8" onclick="removeEntry(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

            container.appendChild(newEntry);
        }

        // Fungsi untuk menghapus entry
        function removeEntry(button) {
            button.closest(".education-entry").remove();
        }


        function addWorkExperience() {
            let container = document.getElementById("work-experience-container");
            let newEntry = document.createElement("div");
            newEntry.classList.add("work-entry", "p-3", "rounded", "mt-3", "position-relative");

            newEntry.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <input type="text" name="company[]" class="form-control" placeholder="e.g., Human Resource Manager" required>
                </div>
                <div class="col-md-3    ">
                    <label class="form-label">Job Title</label>
                    <input type="text" name="work_position[]" class="form-control" placeholder="e.g., Human Resource Manager" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="work_start_date[]" class="form-control" placeholder="e.g., 2020" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">End Date</label>
                    <input type="date" name="work_end_date[]" class="form-control" placeholder="e.g., Present" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeWorkExperience(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
