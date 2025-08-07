@extends('layouts.root.main')

@section('title', $title ?? 'Icp')

@section('breadcrumbs', $title ?? 'Icp')

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
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
            document.addEventListener("DOMContentLoaded", function () {
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
            <form action="{{ route('icp.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-lg-12">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h3 class="text-center fw-bold mb-4">Individual Career Plan</h3>

                            <div class="mb-3">
                                    <!-- Tampilkan sebagai teks dan kunci ID-nya -->
                                    <div class="mb-3">
                                        <label class="form-label">Employee</label>
                                    <input type="text" class="form-control" value="{{ $employee->name }}" disabled>
                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                    </div>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Aspiration</label>
                                <textarea name="aspiration" placeholder="Target karir yang ditentukan oleh Atasan berdasarkan kemampuan technical & non-technical competency kryawn ybs.)  & kebutuhan organisasi
        " class="form-control" rows="4" required>{{ old('aspiration') }}</textarea>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Career Target</label>
                                <input type="text" name="career_target" class="form-control"
                                    value="{{ old('career_target') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold fs-6">Job Function</label>
                                <input type="text" class="form-control" value="{{ $employee->bagian }}" disabled>
                                <input type="hidden" name="job_function" value="{{ $employee->bagian }}">
                            </div>


                            <div class="mb-3">
                                <label class="form-label fw-bold fs-6">Position</label>
                                <input type="text" class="form-control" value="{{ $employee->position }}" disabled>
                                <input type="hidden" name="position" value="{{ $employee->position }}">
                            </div>



                            <div class="mb-3">
                                <label class="form-label">Level</label>
                                <input type="text" class="form-control" value="{{ $employee->grade }}" disabled>
                                <input type="hidden" name="level" value="{{ $employee->grade }}">
                            </div>


                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-12 mb-6">
                            <div class="card p-4 shadow-sm rounded-3">
                                <h3 class="fw-bold mb-4 text-center">Development Stage</h3>
                                <div id="education-container"></div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-primary" onclick="addEducation()">Add
                                        More</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
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
<script>
    const technicalOptions = @json($technicalCompetencies);

    function addEducation() {
        let container = document.getElementById("education-container");
        let index = container.children.length;
        let newEntry = document.createElement("div");
        newEntry.classList.add("education-entry", "p-3", "rounded", "mt-3", "border", "bg-light");

        let technicalOptionsHtml = '<option value="">Select Technical</option>';
        technicalOptions.forEach(opt => {
            technicalOptionsHtml += `<option value="${opt.id}">${opt.competency}</option>`;
        });

        newEntry.innerHTML = `
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Current Tech</label>
                <input type="text" name="details[${index}][current_technical]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Current Non-Tech</label>
                <input type="text" name="details[${index}][current_nontechnical]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Required Tech</label>
                <input type="text" name="details[${index}][required_technical]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Required Non-Tech</label>
                <input type="text" name="details[${index}][required_nontechnical]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Development Technical</label>
                <input type="text" name="details[${index}][development_technical]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Development Non-Tech</label>
                <input type="text" name="details[${index}][development_nontechnical]" class="form-control form-control-sm" required>
            </div>
        </div>
        <div class="text-end mt-2">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">
                <i class="bi bi-trash"></i> Remove
            </button>
        </div>
    `;

        container.appendChild(newEntry);
    }

    function removeEntry(button) {
        button.closest(".education-entry").remove();
    }

    $(document).ready(function () {
        $('#position-select').select2();
    });
</script>