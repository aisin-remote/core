@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Assessment' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Assessment' }}
@endsection

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="app-content container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Assessment List</h3>
                    <div class="d-flex align-items-center">
                        <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..."
                            style="width: 200px;">
                        <button type="button" class="btn btn-primary me-3" id="searchButton">
                            <i class="fas fa-search"></i> Search
                        </button>

                        <a href="#" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addAssessmentModal">Add</a>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>No</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>NPK</th>
                                <th>Age</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assessments as $index => $assessment)
                                <tr>
                                    <td>{{ $assessments->firstItem() + $index }}</td>
                                    <td>{{ $assessment->employee->name ?? '-' }}</td>
                                    <td>
                                        @if ($assessment->employee->departments->isNotEmpty())
                                            {{ $assessment->employee->departments->pluck('name')->join(', ') }}
                                        @else
                                            Tidak Ada Departemen
                                        @endif
                                    </td>
                                    <td>{{ $assessment->employee->npk ?? '-' }}</td>
                                    <td>
                                        @php
                                            $birthdate = $assessment->employee->birthday_date;
                                            $age = $birthdate ? \Carbon\Carbon::parse($birthdate)->age : null;
                                        @endphp
                                        {{ $age ?? '-' }}
                                    </td>
                                    <td class="text-center">
                                        {{-- <button class="btn btn-primary open-modal btn-sm"
                                            data-id="{{ $assessment->employee->id }}">
                                            Detail
                                        </button> --}}

                                        <a class="btn btn-info btn-sm history-btn"
                                            data-employee-id="{{ $assessment->employee->id }}" data-bs-toggle="modal"
                                            data-bs-target="#detailAssessmentModal">
                                            History
                                        </a>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between">
                        <span>Showing {{ $assessments->firstItem() }} to {{ $assessments->lastItem() }} of
                            {{ $assessments->total() }} entries</span>
                        {{ $assessments->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('website.assessment.modal')
    @include('website.assessment.modaldetail')
    @include('website.assessment.show')
@endsection

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
            /* Pastikan Select2 mengambil seluruh lebar */
        }

        .select2-selection {
            height: calc(2.25rem + 2px) !important;
            /* Samakan tinggi dengan form-select Bootstrap */
            padding: 0.375rem 0.75rem !important;
            border-radius: 0.375rem !important;
            border: 1px solid #ced4da !important;
            cursor: pointer !important;
        }

        .select2-selection__rendered {
            line-height: 1.5 !important;
            /* Sesuaikan dengan Bootstrap */
        }

        .select2-selection__arrow {
            height: 100% !important;
        }

        /* Agar dropdown menyesuaikan dengan parent */
        .select2-container--default .select2-selection--single {
            display: flex !important;
            align-items: center !important;
        }

        /* Menghindari overlap dengan elemen lain */
        .select2-container--open {
            z-index: 99999 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/plugins/custom/select2/js/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $(document).on("click", ".history-btn", function(event) {
                event.preventDefault();

                let employeeId = $(this).data("employee-id");
                console.log("Fetching history for Employee ID:", employeeId); // Debug

                // Reset data modal sebelum request baru dilakukan
                $("#npkText").text("-");
                $("#positionText").text("-");
                $("#kt_table_assessments tbody").empty();

                $.ajax({
                    url: `/assessment/history/${employeeId}`,
                    type: "GET",
                    success: function(response) {
                        console.log("Response received:", response); // Debug respons

                        if (!response.employee) {
                            console.error("Employee data not found in response!");
                            alert("Employee not found!");
                            return;
                        }

                        // Update informasi karyawan
                        $("#npkText").text(response.employee.npk);
                        $("#positionText").text(response.employee.position);

                        // Kosongkan tabel sebelum menambahkan data baru
                        $("#kt_table_assessments tbody").empty();

                        if (response.assessments.length > 0) {
                            response.assessments.forEach((assessment, index) => {
                                let row = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${assessment.date}</td>
                            <td class="text-center">
                                <a class="btn btn-info btn-sm" href="/assessment/${assessment.id}/${assessment.date}">
                                    Detail
                                </a>
                                ${assessment.upload ? `
                                                                                <a class="btn btn-primary btn-sm" target="_blank" href="/storage/${assessment.upload}">
                                                                                    View PDF
                                                                                </a>`
                                    : '<span class="text-muted">No PDF Available</span>'
                                }
                                <button type="button" class="btn btn-warning btn-sm updateAssessment"
                                data-bs-toggle="modal" data-bs-target="#updateAssessmentModal"
                                data-id="${assessment.id}"
                                data-employee-id="${assessment.employee_id}"
                                data-date="${assessment.date}"
                                data-upload="${assessment.upload}"
                                data-scores='${encodeURIComponent(JSON.stringify(assessment.details.map(d => d.score)))}'
                                data-alcs='${encodeURIComponent(JSON.stringify(assessment.details.map(d => d.alc_id)))}'
                                data-alc_name='${encodeURIComponent(JSON.stringify(assessment.details.map(d => d.alc?.name || "")))}'
                                data-strengths='${encodeURIComponent(JSON.stringify(assessment.details.map(d => d.strength || "")))}'
                                data-weaknesses='${encodeURIComponent(JSON.stringify(assessment.details.map(d => d.weakness || "")))}'>
                                Edit
                            </button>

                                <button type="button" class="btn btn-danger btn-sm delete-btn"
                                    data-id="${assessment.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                                $("#kt_table_assessments tbody").append(row);
                            });
                        } else {
                            $("#kt_table_assessments tbody").append(`
                    <tr>
                        <td colspan="3" class="text-center text-muted">No assessment found</td>
                    </tr>
                `);
                        }

                        // Tampilkan modal setelah data dimuat
                        $("#detailAssessmentModal").modal("show");
                    },
                    error: function(error) {
                        console.error("Error fetching data:", error);
                        alert("Failed to load assessment data!");
                    }
                });
            });
            $(document).on("click", ".updateAssessment", function() {
                let assessmentId = $(this).data("id");
                let employeeId = $(this).data("employee-id");
                let date = $(this).data("date");
                let upload = $(this).data("upload");

                // Decode data yang di-encode sebelumnya
                let scores = JSON.parse(decodeURIComponent($(this).attr("data-scores")));
                let alcs = JSON.parse(decodeURIComponent($(this).attr("data-alcs")));
                let alcNames = JSON.parse(decodeURIComponent($(this).attr("data-alc_name")));
                let strengths = JSON.parse(decodeURIComponent($(this).attr("data-strengths")));
                let weaknesses = JSON.parse(decodeURIComponent($(this).attr("data-weaknesses")));

                // Masukkan data ke dalam modal
                $("#update_assessment_id").val(assessmentId);
                $("#update_employee_id").val(employeeId);
                $("#update_date").val(date);
                $("#update_upload").attr("href", upload).text("Lihat File");

                /*** Mengisi Radio Button Scores ***/
                scores.forEach((score, index) => {
                    let alcId = alcs[index]; // ID dari ALC yang sesuai
                    $(`#update_score_${alcId}_${score}`).prop("checked", true);
                });

                /*** Mengisi Strengths ***/
                const strengthContainer = document.getElementById("update-strengths-wrapper");
                strengthContainer.innerHTML = "";
                strengths.forEach((strength, idx) => {
                    if (strength.trim() !== "") {
                        addAssessmentCard("strength", "update-strengths-wrapper", alcs[idx],
                            strength, alcNames[idx]);
                    }
                });

                if (strengths.length === 0) {
                    addAssessmentCard("strength", "update-strengths-wrapper");
                }

                /*** Mengisi Weaknesses ***/
                const weaknessContainer = document.getElementById("update-weaknesses-wrapper");
                weaknessContainer.innerHTML = "";
                weaknesses.forEach((weakness, idx) => {
                    if (weakness.trim() !== "") {
                        addAssessmentCard("weakness", "update-weaknesses-wrapper", alcs[idx],
                            weakness, alcNames[idx]);
                    }
                });

                if (weaknesses.length === 0) {
                    addAssessmentCard("weakness", "update-weaknesses-wrapper");
                }

                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById("updateAssessmentModal"));
                modal.show();

                $("#updateAssessmentModal").modal("show");

                // Tutup modal History sebelum membuka modal Update
                $("#detailAssessmentModal").modal("hide");

                setTimeout(() => {
                    $(".modal-backdrop").remove(); // Hapus overlay modal history
                    $("body").removeClass("modal-open"); // Pastikan body tidak terkunci

                    $("#updateAssessmentModal").modal("show");

                    // Buat overlay baru agar tetap ada
                    $("<div class='modal-backdrop fade show'></div>").appendTo(document.body);
                }, 300);
            });

            /**
             * Fungsi untuk menambahkan card Strength atau Weakness ke dalam modal
             */
            function addAssessmentCard(type, containerId, selectedAlc = "", description = "", alcName = "") {
                let container = document.getElementById(containerId);
                if (!container) {
                    console.error(`Container '${containerId}' tidak ditemukan.`);
                    return;
                }

                let templateCard = document.createElement("div");
                templateCard.classList.add("card", "p-3", "mb-3", "assessment-card", `${type}-card`);

                templateCard.innerHTML = `
        <div class="mb-3">
            <label>ALC</label>
            <select class="form-control alc-dropdown" name="${type}_alc_ids[]" required>
                <option value="">Pilih ALC</option>
                @foreach ($alcs as $alc)
                    <option value="{{ $alc->id }}" ${selectedAlc == "{{ $alc->id }}" ? "selected" : ""}>
                        {{ $alc->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea class="form-control ${type}-textarea" name="${type}[${selectedAlc}]" rows="2">${description}</textarea>
        </div>
        <div class="d-flex justify-content-end button-group">
            <button type="button" class="btn btn-success btn-sm add-assessment" data-type="${type}">Tambah ${type.charAt(0).toUpperCase() + type.slice(1)}</button>
        </div>
    `;

                let selectElement = templateCard.querySelector(".alc-dropdown");
                selectElement.addEventListener("change", function() {
                    updateDescriptionName(selectElement, type);
                });

                let buttonGroup = templateCard.querySelector(".button-group");

                templateCard.querySelector(".add-assessment").addEventListener("click", function() {
                    addAssessmentCard(type, containerId);
                    updateRemoveButtons(container);
                });

                container.appendChild(templateCard);
                updateRemoveButtons(container);
            }

            /**
             * Fungsi untuk mengupdate nama textarea berdasarkan ALC yang dipilih
             */
            function updateDescriptionName(selectElement, type) {
                let card = selectElement.closest(".assessment-card");
                let textarea = card.querySelector(`.${type}-textarea`);
                let alcId = selectElement.value;

                if (alcId) {
                    textarea.setAttribute("name", `${type}[${alcId}]`);
                } else {
                    textarea.removeAttribute("name");
                }
            }

            /**
             * Fungsi untuk menambahkan tombol hapus jika ada lebih dari 1 field
             */
            function updateRemoveButtons(container) {
                let cards = container.querySelectorAll(".assessment-card");
                let removeButtons = container.querySelectorAll(".remove-card");

                removeButtons.forEach(button => button.remove());

                if (cards.length > 1) {
                    cards.forEach((card, index) => {
                        if (index !== 0) {
                            let buttonGroup = card.querySelector(".button-group");
                            let removeButton = document.createElement("button");
                            removeButton.type = "button";
                            removeButton.classList.add("btn", "btn-danger", "btn-sm", "remove-card",
                                "me-2");
                            removeButton.textContent = "Hapus";

                            removeButton.addEventListener("click", function() {
                                card.remove();
                                updateRemoveButtons(container);
                            });

                            buttonGroup.insertBefore(removeButton, buttonGroup.firstChild);
                        }
                    });
                }
            }



            // Pastikan overlay baru dibuat saat modal update ditutup dan kembali ke modal history
            $("#updateAssessmentModal").on("hidden.bs.modal", function() {
                setTimeout(() => {
                    $(".modal-backdrop").remove(); // Hapus overlay modal update
                    $("body").removeClass("modal-open");

                    $("#detailAssessmentModal").modal("show");

                    // Tambahkan overlay kembali untuk modal history
                    $("<div class='modal-backdrop fade show'></div>").appendTo(document.body);
                }, 300);
            });


            // ===== HAPUS OVERLAY SAAT MODAL HISTORY DITUTUP =====
            $("#detailAssessmentModal").on("hidden.bs.modal", function() {
                setTimeout(() => {
                    if (!$("#updateAssessmentModal").hasClass("show")) {
                        $(".modal-backdrop").remove(); // Pastikan tidak ada overlay tertinggal
                        $("body").removeClass("modal-open");
                    }
                }, 300);
            });

            // ===== CEGAH OVERLAY BERLAPIS =====
            $(".modal").on("shown.bs.modal", function() {
                $(".modal-backdrop").last().css("z-index",
                    1050); // Atur overlay agar tidak bertumpuk terlalu tebal
            });






            // Pastikan overlay baru dibuat saat modal update ditutup dan kembali ke modal history


            var searchInput = $("#searchInput");
            var filterItems = $(".filter-department");
            var table = $("#kt_table_users");
            var tbody = table.find("tbody");
            var rows = tbody.find("tr");

            if (!searchInput.length || !table.length) {
                console.error("⚠️ Elemen pencarian atau tabel tidak ditemukan!");
                return;
            }

            function filterTable(selectedDepartment = "") {
                var searchValue = searchInput.val().toLowerCase();
                var isMatchFound = false;

                rows.each(function() {
                    var row = $(this);
                    var cells = row.find("td");

                    if (cells.length >= 5) {
                        var name = cells.eq(1).text().toLowerCase();
                        var department = cells.eq(2).text().toLowerCase();
                        var npk = cells.eq(3).text().toLowerCase();
                        var age = cells.eq(4).text().toLowerCase();

                        var searchMatch = name.includes(searchValue) || department.includes(searchValue) ||
                            npk.includes(searchValue) || age.includes(searchValue);

                        var departmentMatch = selectedDepartment === "" || department ===
                            selectedDepartment;

                        if (searchMatch && departmentMatch) {
                            row.show();
                            isMatchFound = true;
                        } else {
                            row.hide();
                        }
                    }
                });


            }

            searchInput.on("keyup", function() {
                filterTable();
            });


            // 🔹 Variabel Global untuk Chart
            let assessmentChartInstance = null;

            // 🔹 Fungsi untuk Membuat Chart
            function renderChart(details) {
                let canvas = document.getElementById('assessmentChart');
                let ctx = canvas.getContext('2d');

                // **Cek jika chart sudah ada, lalu hancurkan**
                if (assessmentChartInstance) {
                    assessmentChartInstance.destroy();
                }

                // **Bersihkan canvas sebelum menggambar ulang**
                canvas.width = canvas.width;

                let labels = details.map(d => d.alc.name);
                let scores = details.map(d => d.score);

                // **Buat chart baru dan simpan ke variabel global**
                assessmentChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Assessment Score',
                            data: scores,
                            backgroundColor: scores.map(score => score < 3 ?
                                'rgba(255, 99, 132, 0.6)' :
                                'rgba(75, 192, 192, 0.6)'),
                            borderColor: scores.map(score => score < 3 ? 'rgba(255, 99, 132, 1)' :
                                'rgba(75, 192, 192, 1)'),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            datalabels: {
                                anchor: 'center',
                                align: 'top',
                                color: 'black',
                                font: {
                                    weight: 'bold',
                                    size: 14
                                },
                                formatter: value => value
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: 5,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            filterItems.on("click", function(event) {
                event.preventDefault();
                var selectedDepartment = $(this).data("department").toLowerCase();
                console.log("🔍 Filter dipilih: ", selectedDepartment);
                filterTable(selectedDepartment);
            });

            $('#addAssessmentModal').on('show.bs.modal', function(event) {
                // $('#employee_id').select2({
                //     dropdownParent: $('#addAssessmentModal')
                // });
                // $('#employee_id').select2({
                //     dropdownParent: $('#addAssessmentModal')
                // });
                // $('.alc-dropdown').select2({
                //     dropdownParent: $('#addAssessmentModal')
                // });

                let button = $(event.relatedTarget);
                let assessment_id = button.data('id') || null;

                if (assessment_id) {
                    $('#addAssessmentModalLabel').text('Edit Assessment');
                    $('#btnSubmit').text('Update');
                    $('#assessment_id').val(assessment_id);

                    $('#employee_id').val(button.data('employee_id'));
                    $('#date').val(button.data('date'));

                    let fields = [
                        'vision_business_sense',
                        'customer_focus',
                        'interpersonal_skil',
                        'analysis_judgment',
                        'planning_driving_action',
                        'leading_motivating',
                        'teamwork',
                        'drive_courage'
                    ];

                    fields.forEach(field => {
                        let value = button.data(field);
                        $('input[name="' + field + '"][value="' + value + '"]').prop('checked',
                            true);
                    });

                } else {
                    $('#addAssessmentModalLabel').text('Tambah Assessment');
                    $('#btnSubmit').text('Simpan');
                    $('#assessmentForm')[0].reset();
                    $('#assessment_id').val('');
                }
            });

            $('#assessmentForm').submit(function(e) {
                e.preventDefault();
                let assessment_id = $('#assessment_id').val();
                let formData = new FormData(this);
                let url = assessment_id ? "{{ url('/assessment') }}/" + assessment_id :
                    "{{ route('assessments.store') }}";
                let method = assessment_id ? "PUT" : "POST";
                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            title: "Berhasil!",
                            text: assessment_id ? "Assessment berhasil diperbarui!" :
                                "Assessment berhasil ditambahkan!",
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            $('#addAssessmentModal').modal('hide'); // Tutup modal
                            location.reload(); // Refresh halaman setelah sukses
                        });
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = "Terjadi kesalahan!";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            title: "Gagal!",
                            text: errorMessage,
                            icon: "error",
                            confirmButtonText: "Coba Lagi"
                        });
                    }
                });

            });
        });
    </script>
@endpush
