@extends('layouts.root.main')

@section('title')
    Detail Assessment - {{ $employee->name }}
@endsection

@section('main')
    <div class="container mt-4">
        <h1 class="text-center mb-6 fw-bold">History Assessment</h1>
        <div class="card shadow-lg">
            <div class="card-body">
                <div class="row mb-3 d-flex justify-content-end align-items-center gap-4">
                    <div class="col-auto">
                        <p class="fs-5 fw-bold"><strong>NPK:</strong> {{ $employee->npk }}</p>
                    </div>
                    <div class="col-auto">
                        <p class="fs-5 fw-bold"><strong>Position:</strong> {{ $employee->position }}</p>
                    </div>
                </div>


                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle table-hover fs-6"
                        id="kt_table_assessments">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="10%">No</th>
                                <th class="text-center">Date</th>
                                <th class="text-center" width="40%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assessments as $index => $assessment)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">{{ $assessment->date }}</td>
                                    <td class="text-center">
                                        <a class="btn btn-info btn-sm"
                                            href="{{ route('assessments.showByDate', ['assessment_id' => $assessment->id, 'date' => $assessment->date]) }}">
                                            Detail
                                        </a>

                                        @if (!empty($assessment->upload))
                                            <a class="btn btn-primary btn-sm" target="_blank"
                                                href="{{ asset('storage/' . $assessment->upload) }}">
                                                View PDF
                                            </a>
                                        @else
                                            <span class="text-muted">No PDF Available</span>
                                        @endif
                                        <button class="btn btn-warning btn-sm editAssessment"
                                            data-id="{{ $assessment->id }}"
                                            data-employee-id="{{ $assessment->employee_id }}"
                                            data-date="{{ $assessment->date }}" data-upload="{{ $assessment->upload }}"
                                            data-scores='@json($assessment->scores ?? [])'
                                            data-strengths='@json($assessment->strengths ?? [])'
                                            data-weaknesses='@json($assessment->weaknesses ?? [])'>
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                            data-id="{{ $assessment->id }}">Delete</button>

                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('assessments.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
    @include('website.assessment.modalupdate')
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inisialisasi DataTable
            $(document).ready(function() {
                $('#kt_table_assessments').DataTable({
                    paging: true,
                    searching: false,
                    lengthChange: false,
                    ordering: true,
                    responsive: true
                });
            });

            // Event listener untuk tombol Edit
            document.querySelectorAll(".editAssessment").forEach(button => {
                button.addEventListener("click", function() {
                    openUpdateModal(this);
                });
            });

            // Fungsi untuk membuka modal Edit
            function openUpdateModal(button) {
                let id = button.getAttribute("data-id");
                let employeeId = button.getAttribute("data-employee-id");
                let date = button.getAttribute("data-date");
                let upload = button.getAttribute("data-upload");
                let scores = JSON.parse(button.getAttribute("data-scores") ||
                    "[]"); // Gunakan array kosong jika null
                let strengths = JSON.parse(button.getAttribute("data-strengths") || "[]");
                let weaknesses = JSON.parse(button.getAttribute("data-weaknesses") || "[]");
                console.log("🔍 Debug Data Edit:");
        console.log("ID:", id);
        console.log("Employee ID:", employeeId);
        console.log("Date:", date);
        console.log("Upload:", upload);
        console.log("Scores:", scores);
        console.log("Strengths:", strengths);
        console.log("Weaknesses:", weaknesses);



                document.getElementById("update_assessment_id").value = id;
                document.getElementById("update_employee_id").value = employeeId;
                document.getElementById("update_date").value = date;
                document.getElementById("update-upload-info").textContent = upload ? `File: ${upload}` :
                    "Tidak ada file";

                let strengthContainer = document.getElementById("update-strength-container");
                strengthContainer.innerHTML = "";
                if (!Array.isArray(strengths)) {
                    console.error("strengths bukan array:", strengths);
                    return;
                }
                strengths.forEach((strength, index) => {
                    let strengthCard = createAssessmentCard(strength, "strength", index);
                    strengthContainer.appendChild(strengthCard);
                });

                let weaknessContainer = document.getElementById("update-weakness-container");
                weaknessContainer.innerHTML = "";
                if (!Array.isArray(weaknesses)) {
                    console.error("weaknesses bukan array:", weaknesses);
                    return;
                }
                weaknesses.forEach((weakness, index) => {
                    let weaknessCard = createAssessmentCard(weakness, "weakness", index);
                    weaknessContainer.appendChild(weaknessCard);
                });

                // Tampilkan modal
                new bootstrap.Modal(document.getElementById("updateAssessmentModal")).show();
            }

            // Fungsi untuk membuat card Strength/Weakness
            function createAssessmentCard(data, type, index) {
                let card = document.createElement("div");
                card.classList.add("card", "p-3", "mb-3");

                card.innerHTML = `
                <div class="mb-3">
                    <select class="form-control" name="${type}_ids[]" required>
                        <option value="">Pilih ALC</option>
                        ${data.alc_options.map(alc =>
                            `<option value="${alc.id}" ${alc.id == data.alc_id ? 'selected' : ''}>${alc.name}</option>`
                        ).join("")}
                    </select>
                </div>
                <div class="mb-3">
                    <label>${type.charAt(0).toUpperCase() + type.slice(1)}</label>
                    <textarea class="form-control" name="${type}[${index}]" rows="2">${data.description}</textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-danger btn-sm remove-card">Hapus</button>
                </div>
            `;

                card.querySelector(".remove-card").addEventListener("click", function() {
                    card.remove();
                });

                return card;
            }

            // Event listener untuk tombol Delete
            document.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", function() {
                    let assessmentId = this.getAttribute("data-id");

                    if (!assessmentId) {
                        console.error("ID Assessment tidak ditemukan!");
                        return;
                    }

                    // SweetAlert Konfirmasi
                    Swal.fire({
                        title: "Apakah Anda yakin?",
                        text: "Data assessment ini akan dihapus secara permanen!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ya, Hapus!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // AJAX request untuk menghapus data
                            fetch(`/assessment/${assessmentId}`, {
                                    method: "DELETE",
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector(
                                            'meta[name="csrf-token"]').getAttribute(
                                            "content"),
                                        "Content-Type": "application/json"
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire("Terhapus!", data.message, "success")
                                            .then(() => location
                                                .reload()); // Refresh halaman
                                    } else {
                                        Swal.fire("Error!", "Gagal menghapus data!",
                                            "error");
                                    }
                                })
                                .catch(error => {
                                    console.error("Error:", error);
                                    Swal.fire("Error!", "Terjadi kesalahan!", "error");
                                });
                        }
                    });
                });
            });

            // Submit form update
            document.getElementById("updateAssessmentForm").addEventListener("submit", function(event) {
                event.preventDefault();

                let formData = new FormData(this);
                let id = document.getElementById("update_assessment_id").value;

                fetch(`/assessment/${id}`, {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {

                        Swal.fire("Berhasil!", "Assessment berhasil diperbarui!", "success")
                            .then(() => location.reload()); // Refresh halaman
                    })
                    .catch(error => console.error("Error:", error));
            });
        });
    </script>
@endpush
