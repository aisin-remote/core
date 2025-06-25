@extends('layouts.root.main')

@section('title', $title ?? 'Edit ICP')
@section('breadcrumbs', $title ?? 'Edit ICP')

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

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form action="{{ route('icp.update', $icp->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="employee_id" value="{{ $icp->employee_id }}">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card p-4 shadow-sm rounded-3">
                            <h3 class="text-center fw-bold mb-4">Update Individual Career Plan</h3>

                            <div class="mb-3">
                                <label class="form-label">Employee</label>
                                <input type="text" class="form-control" value="{{ $icp->employee->name ?? '-' }}"
                                    readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Aspiration</label>
                                <textarea name="aspiration" class="form-control" rows="4" required>{{ old('aspiration', $icp->aspiration) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Career Target</label>
                                <input type="text" name="career_target" class="form-control"
                                    value="{{ old('career_target', $icp->career_target) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ old('date', $icp->date) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Job Function</label>
                                <select name="job_function" class="form-select" required>
                                    <option value="">Select Job Function</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->name }}"
                                            {{ old('job_function', $icp->job_function) == $department->name ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Position</label>
                                <select name="position" class="form-select">
                                    @foreach ($positions as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('position', $icp->position) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Level</label>
                                <select name="level" class="form-select" required>
                                    <option value="">-- Select Level --</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->aisin_grade }}"
                                            {{ old('level', $icp->level) == $grade->aisin_grade ? 'selected' : '' }}>
                                            {{ $grade->aisin_grade }}
                                        </option>
                                    @endforeach
                                </select>
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
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

<script>
    const technicalOptions = @json($technicalCompetencies);
    const existingDetails = @json($icp->details);

    function addEducation(detail = null) {
        let container = document.getElementById("education-container");
        let index = container.children.length;
        let newEntry = document.createElement("div");
        newEntry.classList.add("education-entry", "p-3", "rounded", "mt-3", "border", "bg-light");

        let technicalOptionsHtml = '<option value="">Select Technical</option>';
        technicalOptions.forEach(opt => {
            let selected = detail && detail.current_technical == opt.id ? 'selected' : '';
            technicalOptionsHtml += `<option value="${opt.id}" ${selected}>${opt.competency}</option>`;
        });

        newEntry.innerHTML = `
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Current Tech</label>
                <select name="details[${index}][current_technical]" class="form-select form-select-sm" required>
                    ${technicalOptionsHtml}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Current Non-Tech</label>
                <input type="text" name="details[${index}][current_nontechnical]" class="form-control form-control-sm" value="${detail?.current_nontechnical ?? ''}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Required Tech</label>
                <input type="text" name="details[${index}][required_technical]" class="form-control form-control-sm" value="${detail?.required_technical ?? ''}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Required Non-Tech</label>
                <input type="text" name="details[${index}][required_nontechnical]" class="form-control form-control-sm" value="${detail?.required_nontechnical ?? ''}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Development Technical</label>
                <input type="text" name="details[${index}][development_technical]" class="form-control form-control-sm" value="${detail?.development_technical ?? ''}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Development Non-Tech</label>
                <input type="text" name="details[${index}][development_nontechnical]" class="form-control form-control-sm" value="${detail?.development_nontechnical ?? ''}" required>
            </div>
        </div>
        <div class="text-end mt-2">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">
                <i class="bi bi-trash"></i> Remove
            </button>
        </div>`;

        container.appendChild(newEntry);
    }

    function removeEntry(button) {
        button.closest(".education-entry").remove();
    }

    // Auto-populate existing data
    document.addEventListener("DOMContentLoaded", function () {
        if (Array.isArray(existingDetails)) {
            existingDetails.forEach(detail => {
                addEducation(detail);
            });
        }
    });
</script>
