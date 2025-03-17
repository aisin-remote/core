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
                        id="kt_table_assessments" width="100%">
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
                                        <button type="button" class="btn btn-warning btn-sm updateAssessment"
                                            data-bs-toggle="modal" data-bs-target="#updateAssessmentModal"
                                            id="updateAssessment" data-id="{{ $assessment->id }}"
                                            data-employee-id="{{ $assessment->employee_id }}"
                                            data-date="{{ $assessment->date }}" data-upload="{{ $assessment->upload }}"
                                            data-scores='@json($assessment->details->pluck('score'))'
                                            data-alcs='@json($assessment->details->pluck('alc_id'))'
                                            data-alc_name='@json($assessment->details->pluck('alc.name'))'
                                            data-strengths='@json($assessment->details->pluck('strength'))'
                                            data-weaknesses='@json($assessment->details->pluck('weakness'))'>
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

    {{-- MODAL --}}
    <div class="modal fade" id="editAssessmentModal" tabindex="-1" aria-labelledby="editAssessmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssessmentModalLabel">Edit Assessment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assessmentForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="assessment_id" name="assessment_id">

                        <div class="mb-4">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-control" id="employee_id" name="employee_id" required>
                                <option value="">Pilih Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="date" class="form-label">Date Assessment <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>

                        <div class="mb-4">
                            <div class="section-title">Assessment Scores</div>
                            @foreach ($alcs as $alc)
                                <div class="card p-3 mb-3">
                                    <h6>{{ $alc->name }} <span class="text-danger">*</span></h6>
                                    <input type="hidden" name="alc_ids[]" value="{{ $alc->id }}">
                                    <div class="mb-2">
                                        <div class="d-flex gap-2">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="scores[{{ $alc->id }}]"
                                                        id="score_{{ $alc->id }}_{{ $i }}"
                                                        value="{{ $i }}" required>
                                                    <label class="form-check-label"
                                                        for="score_{{ $alc->id }}_{{ $i }}">{{ $i }}</label>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="section-title">Strength</div>
                        <div id="strength-container">
                            <div class="assessment-card strength-card card p-3 mb-3">
                                <div class="mb-3">
                                    <select class="form-control alc-dropdown" name="alc_ids[]" required>
                                        <option value="">Pilih ALC</option>
                                        @foreach ($alcs as $alc)
                                            <option value="{{ $alc->id }}">{{ $alc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Strength</label>
                                    <textarea class="form-control strength-textarea" name="strength[1]" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-sm add-assessment"
                                        data-type="strength">Tambah Strength</button>
                                </div>
                            </div>
                        </div>
                        <div class="section-title">Weakness</div>
                        <div id="weakness-container">
                            <div class="assessment-card weakness-card card p-3 mb-3">
                                <div class="mb-3">
                                    <select class="form-control alc-dropdown" name="alc_ids[]" required>
                                        <option value="">Pilih ALC</option>
                                        @foreach ($alcs as $alc)
                                            <option value="{{ $alc->id }}">{{ $alc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Weakness</label>
                                    <textarea class="form-control weakness-textarea" name="weakness[1]" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-sm add-assessment"
                                        data-type="weakness">Tambah Weakness</button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="upload" class="form-label">Upload File Assessment(PDF, JPG, PNG)</label>
                            <input type="file" class="form-control" id="upload" name="upload"
                                accept=".pdf,.jpg,.png">
                        </div>

                        <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('website.assessment.modalupdate')
@endsection

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/datatables/css/datatables.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/custom/datatables/js/datatables.min.js') }}"></script>
    {{-- <script>
        $(document).ready(function() {
            var table = $('#kt_table_assessments').DataTable({
                'lengthChange': true,
                'processing': true,
                'serverSide': false,
                'orderable': true,
                ajax: {
                    url: "{{ route('assessments.history_ajax') }}",
                },
                columns: [{
                        data: null,
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row, meta) {
                            var rowIndex = meta.row + meta.settings._iDisplayStart + 1;
                            return rowIndex;
                        },
                        className: "text-center" // Menetapkan kelas CSS 'text-center'
                    },
                    {
                        data: 'date',
                        name: 'date',
                    },
                    {
                        data: 'date',
                        name: 'date',
                    },
                ],
            });
        });
    </script> --}}
    {{-- <script>
        $(document).ready(function() {
            $('#kt_table_assessments').DataTable({
                paging: true,
                searching: false,
                lengthChange: false,
                ordering: true,
                responsive: true
            });
        });
    </script> --}}
    {{-- <script>
        $(document).ready(function() {
            $('#kt_table_assessments').DataTable({
                paging: true,
                searching: false,
                lengthChange: false,
                ordering: true,
                responsive: true
            });

            // Event delegation untuk tombol Edit karena elemen bisa ditambahkan secara dinamis
            $('#kt_table_assessments').on('click', '#editAssessment', function() {
                let button = $(this);
                console.log("test");
                // Ambil data dari button edit
                let id = button.data("id");
                let employeeId = button.data("employee-id");
                let date = button.data("date");
                let upload = button.data("upload");
                let scores = button.data("scores") || [];
                let alcs = button.data("alcs") || [];
                let strengths = button.data("strengths") || [];
                let weaknesses = button.data("weaknesses") || [];

                console.log("🔍 Debug Data Edit:", {
                    id,
                    employeeId,
                    date,
                    upload,
                    scores,
                    alcs,
                    strengths,
                    weaknesses
                });

                // Isi modal dengan data
                $('#assessment_id').val(id);
                $('#employee_id').val(employeeId).trigger(
                    "change"); // Pilih employee sesuai data
                $('#date').val(date);
                $('#upload').val(upload ? `File: ${upload}` : "Tidak ada file");

                // Isi radio button scores
                setTimeout(() => {
                    scores.forEach((score, index) => {
                        let alcId = alcs[
                            index]; // Dapatkan ALC ID terkait score
                        if (alcId) {
                            let radioSelector =
                                `input[name="scores[${alcId}]"][value="${score}"]`;
                            $(radioSelector).prop("checked", true);
                        }
                    });
                }, 500); // Beri jeda agar radio button terisi dengan benar

                // Bersihkan container sebelum diisi ulang
                $('#strength-container').empty();
                $('#weakness-container').empty();

                // Isi Strength Data
                if (Array.isArray(strengths)) {
                    strengths.forEach((strength, index) => {
                        let strengthCard = createAssessmentCard(strength, "strength",
                            index, alcs[
                                index]);
                        $('#strength-container').append(strengthCard);
                    });
                }

                // Isi Weakness Data
                if (Array.isArray(weaknesses)) {
                    weaknesses.forEach((weakness, index) => {
                        let weaknessCard = createAssessmentCard(weakness, "weakness",
                            index, alcs[
                                index]);
                        $('#weakness-container').append(weaknessCard);
                    });
                }

                // Tampilkan modal Edit
                $('#editAssessmentModal').modal('show');
            });

            // Fungsi untuk membuat card untuk Strength & Weakness
            function createAssessmentCard(value, type, index, alcId = "") {
                let card = $(`
            <div class="assessment-card card p-3 mb-3">
                <div class="mb-3">
                    <select class="form-control alc-dropdown" name="alc_ids[]" required>
                        <option value="">Pilih ALC</option>
                        ${alcsOptions(alcId)}
                    </select>
                </div>
                <div class="mb-3">
                    <label>${type.charAt(0).toUpperCase() + type.slice(1)}</label>
                    <textarea class="form-control ${type}-textarea" name="${type}[${index}]" rows="2">${value || ""}</textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-danger btn-sm remove-card">Hapus</button>
                </div>
            </div>
        `);

                // Event listener untuk tombol hapus
                card.find(".remove-card").on("click", function() {
                    card.remove();
                });

                return card;
            }

            // Fungsi untuk membuat opsi dropdown ALC
            function alcsOptions(selectedId) {
                let options = `<option value="">Pilih ALC</option>`;
                @foreach ($alcs as $alc)
                    options +=
                        `<option value="{{ $alc->id }}" ${selectedId == "{{ $alc->id }}" ? "selected" : ""}>{{ $alc->name }}</option>`;
                @endforeach
                return options;
            }

            // Event listener untuk submit update assessment
            $('#btnSubmit').on('click', function(e) {
                e.preventDefault();

                let assessmentId = $('#assessment_id').val();
                let employeeId = $('#employee_id').val();
                let date = $('#date').val();
                let formData = new FormData($('#assessmentForm')[0]); // Ambil data form

                if (!employeeId || !date) {
                    toastr['error']('Mohon lengkapi semua field yang diperlukan!');
                    return;
                }

                $.ajax({
                    url: `/assessments/${employeeId}`, // Gunakan employee_id sebagai parameter di URL
                    type: "POST",
                    data: formData,
                    processData: false, // Karena menggunakan FormData
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}" // Menambahkan CSRF Token ke request header
                    },
                    success: function(response) {
                        toastr['success']('Assessment berhasil diperbarui!');
                        $('#editAssessmentModal').modal('hide');

                        // Reload tabel atau halaman setelah update
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        toastr['error']('Terjadi kesalahan saat memperbarui data!');
                    }
                });
            });
        });
    </script> --}}
    {{-- <script>
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
            document.body.addEventListener("click", function(event) {
                if (event.target.classList.contains("editAssessment")) {
                    openUpdateModal(event.target);
                }
            });

            // Fungsi untuk membuka modal Edit
            function openUpdateModal(button) {
                try {
                    let id = button.getAttribute("data-id") || "";
                    let employeeId = button.getAttribute("data-employee-id") || "";
                    let date = button.getAttribute("data-date") || "";
                    let upload = button.getAttribute("data-upload") || "";

                    let scores = JSON.parse(button.getAttribute("data-scores") || "[]");
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

                    // Isi data dalam modal
                    document.getElementById("update_assessment_id").value = id;
                    document.getElementById("update_employee_id").value = employeeId;
                    document.getElementById("update_date").value = date;
                    document.getElementById("update-upload-info").textContent = upload ? `File: ${upload}` :
                        "Tidak ada file";

                    // Bersihkan container sebelum menambahkan elemen baru
                    let strengthContainer = document.getElementById("update-strength-container");
                    let weaknessContainer = document.getElementById("update-weakness-container");
                    strengthContainer.innerHTML = "";
                    weaknessContainer.innerHTML = "";

                    // Pastikan strengths dan weaknesses berupa array
                    if (Array.isArray(strengths)) {
                        strengths.forEach((strength, index) => {
                            let strengthCard = createAssessmentCard(strength, "strength", index);
                            strengthContainer.appendChild(strengthCard);
                        });
                    } else {
                        console.error("❌ Error: Strengths bukan array!", strengths);
                    }

                    if (Array.isArray(weaknesses)) {
                        weaknesses.forEach((weakness, index) => {
                            let weaknessCard = createAssessmentCard(weakness, "weakness", index);
                            weaknessContainer.appendChild(weaknessCard);
                        });
                    } else {
                        console.error("❌ Error: Weaknesses bukan array!", weaknesses);
                    }

                    // Tampilkan modal
                    new bootstrap.Modal(document.getElementById("updateAssessmentModal")).show();

                } catch (error) {
                    console.error("❌ Error saat memproses data Edit:", error);
                }
            }

            // Fungsi untuk membuat card Strength/Weakness
            function createAssessmentCard(value, type, index) {
                let card = document.createElement("div");
                card.classList.add("assessment-card", "card", "p-3", "mb-3");

                card.innerHTML = `
            <div class="mb-3">
                <label>${type.charAt(0).toUpperCase() + type.slice(1)}</label>
                <textarea class="form-control ${type}-textarea" name="${type}[${index}]" rows="2">${value || ""}</textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm remove-card">Hapus</button>
            </div>
        `;

                // Tambahkan event listener untuk tombol hapus
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
    </script> --}}
@endpush
