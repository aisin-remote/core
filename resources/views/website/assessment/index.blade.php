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
                        <div class="dropdown">
                            <button class="btn btn-light-primary me-3 dropdown-toggle" type="button"
                                id="departmentFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="departmentFilterDropdown" id="filterMenu">
                                <li><a class="dropdown-item filter-department" href="#" data-department="">All
                                        Departments</a></li>
                                @foreach ($employees->unique('function') as $employee)
                                    <li>
                                        <a class="dropdown-item filter-department" href="#"
                                            data-department="{{ strtolower($employee->function) }}">
                                            {{ $employee->function }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
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
                                    <td>{{ $assessment->employee->function ?? '-' }}</td>
                                    <td>{{ $assessment->employee->npk ?? '-' }}</td>
                                    <td>
                                        @php
                                            $birthdate = $assessment->employee->birthday_date;
                                            $age = $birthdate ? \Carbon\Carbon::parse($birthdate)->age : null;
                                        @endphp
                                        {{ $age ?? '-' }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-primary open-modal btn-sm"
                                            data-id="{{ $assessment->employee->id }}">
                                            Detail
                                        </button>

                                        <a href="{{ route('assessments.show', $assessment->employee->id) }}"
                                            class="btn btn-info btn-sm">
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

                if (!isMatchFound) {
                    tbody.append(
                        '<tr id="noDataRow"><td colspan="6" class="text-center text-muted">No Data Found</td></tr>'
                    );
                } else {
                    $("#noDataRow").remove();
                }
            }

            searchInput.on("keyup", function() {
                filterTable();
            });
            $(document).on('click', '.open-modal', function() {
                let employeeId = $(this).data('id');

                // Kosongkan modal sebelum request baru
                $('#assessmentModalLabel').text('Detail Assessment');
                $('#modal-department').text('');
                $('#modal-date').text('');
                $('#modal-strengths-body').empty().append(
                    `<tr><td colspan="3" class="text-center">Memuat data...</td></tr>`);
                $('#modal-weaknesses-body').empty().append(
                    `<tr><td colspan="3" class="text-center">Memuat data...</td></tr>`);

                // Hancurkan chart lama jika ada
                if (assessmentChartInstance) {
                    assessmentChartInstance.destroy();
                    assessmentChartInstance = null;
                }

                // 🔹 Ambil data berdasarkan employee_id
                $.ajax({
                    url: `/assessment/detail/${employeeId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            alert(response.error);
                            return;
                        }

                        // Masukkan data ke dalam modal
                        $('#assessmentModalLabel').text(
                            `Detail Assessment - ${response.employee.name}`);
                        $('#modal-department').text(response.employee.function);
                        $('#modal-date').text(response.date);

                        // Kosongkan tbody sebelum menambahkan data baru
                        $('#modal-strengths-body').empty();
                        $('#modal-weaknesses-body').empty();

                        // Filter hanya Strengths & Weaknesses yang valid
                        let filteredStrengths = response.strengths.filter(s => s.strength && s
                            .strength.trim() !== "");
                        let filteredWeaknesses = response.weaknesses.filter(w => w.weakness && w
                            .weakness.trim() !== "");

                        // Isi tabel Strengths
                        if (filteredStrengths.length > 0) {
                            filteredStrengths.forEach((s, index) => {
                                $('#modal-strengths-body').append(`
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td><strong>${s.alc.name}</strong></td>
                            <td>${s.strength}</td>
                        </tr>
                    `);
                            });
                        } else {
                            $('#modal-strengths-body').append(`
                    <tr><td colspan="3" class="text-center">Tidak ada Strengths</td></tr>
                `);
                        }

                        // Isi tabel Weaknesses
                        if (filteredWeaknesses.length > 0) {
                            filteredWeaknesses.forEach((w, index) => {
                                $('#modal-weaknesses-body').append(`
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td><strong>${w.alc.name}</strong></td>
                            <td>${w.weakness}</td>
                        </tr>
                    `);
                            });
                        } else {
                            $('#modal-weaknesses-body').append(`
                    <tr><td colspan="3" class="text-center">Tidak ada Weaknesses</td></tr>
                `);
                        }

                        // Render Chart
                        renderChart(response.details);

                        // Tampilkan modal
                        $('#assessmentModal').modal('show');
                    },
                    error: function() {
                        alert('Gagal mengambil data');
                    }
                });
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
                let url = assessment_id ? "{{ url('/assessments') }}/" + assessment_id :
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
