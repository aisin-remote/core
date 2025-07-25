<!-- Modal -->
<div class="modal fade" id="detailAssessmentModal" tabindex="-1" aria-labelledby="detailAssessmentModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailAssessmentModalLabel">History Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h1 class="text-center mb-4 fw-bold">History Assessment</h1>

                <div class="row mb-3 d-flex justify-content-end align-items-center gap-4">
                    <div class="col-auto">
                        <p class="fs-5 fw-bold"><strong>NPK:</strong><span id="npkText"></span></p>
                    </div>
                    <div class="col-auto">
                        <p class="fs-5 fw-bold"><strong>Position:</strong> <span id="positionText"></span></p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle table-hover fs-6"
                        id="kt_table_assessments" width="100%">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="10%">No</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Target Position</th>
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
                                                View
                                            </a>
                                        @else
                                            <span class="text-muted">No PDF</span>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('website.assessment.modalupdate')
<script>
    $(document).ready(function() {
        // ===== DELETE FUNCTION =====
        $(document).on("click", ".delete-btn", function() {
            let assessmentId = $(this).data("id");
            console.log("ID yang akan dihapus:", assessmentId); // Debugging

            if (!assessmentId) {
                console.error("ID Assessment tidak ditemukan!");
                return;
            }

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
                    console.log("Mengirim request DELETE untuk ID:", assessmentId); // Debugging

                    fetch(`/assessment/${assessmentId}`, {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                    "content"),
                                "Content-Type": "application/json"
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Response dari server:", data); // Debugging

                            if (data.success) {
                                Swal.fire("Terhapus!", data.message, "success")
                                    .then(() => location.reload());
                            } else {
                                Swal.fire("Error!", "Gagal menghapus data!", "error");
                            }
                        })
                        .catch(error => {
                            console.error("Error saat menghapus:", error);
                            Swal.fire("Error!", "Terjadi kesalahan!", "error");
                        });
                }
            });
        });


        // ===== UPDATE FUNCTION =====
        // $(document).on("click", ".updateAssessment", function() {
        //     let assessmentId = $(this).data("id");
        //     let employeeId = $(this).data("employee-id");
        //     let date = $(this).data("date");
        //     let upload = $(this).data("upload");


        //     // Set nilai input dalam modal Update
        //     $("#updateAssessmentId").val(assessmentId);
        //     $("#updateEmployeeId").val(employeeId);
        //     $("#updateDate").val(date);
        //     $("#updateUpload").text(upload ? "View File" : "No File");

        //     // Tutup modal History sebelum membuka modal Update
        //     $("#detailAssessmentModal").modal("hide");

        //     setTimeout(() => {
        //         $(".modal-backdrop").remove(); // Hapus overlay modal history
        //         $("body").removeClass("modal-open"); // Pastikan body tidak terkunci

        //         $("#updateAssessmentModal").modal("show");

        //         // Buat overlay baru agar tetap ada
        //         $("<div class='modal-backdrop fade show'></div>").appendTo(document.body);
        //     }, 300);
        // });

        // // Pastikan overlay baru dibuat saat modal update ditutup dan kembali ke modal history
        // $("#updateAssessmentModal").on("hidden.bs.modal", function() {
        //     setTimeout(() => {
        //         $(".modal-backdrop").remove(); // Hapus overlay modal update
        //         $("body").removeClass("modal-open");

        //         $("#detailAssessmentModal").modal("show");

        //         // Tambahkan overlay kembali untuk modal history
        //         $("<div class='modal-backdrop fade show'></div>").appendTo(document.body);
        //     }, 300);
        // });


        // // ===== HAPUS OVERLAY SAAT MODAL HISTORY DITUTUP =====
        // $("#detailAssessmentModal").on("hidden.bs.modal", function() {
        //     setTimeout(() => {
        //         if (!$("#updateAssessmentModal").hasClass("show")) {
        //             $(".modal-backdrop").remove(); // Pastikan tidak ada overlay tertinggal
        //             $("body").removeClass("modal-open");
        //         }
        //     }, 300);
        // });

        // // ===== CEGAH OVERLAY BERLAPIS =====
        // $(".modal").on("shown.bs.modal", function() {
        //     $(".modal-backdrop").last().css("z-index",
        //         1050); // Atur overlay agar tidak bertumpuk terlalu tebal
        // });
    });
</script>
