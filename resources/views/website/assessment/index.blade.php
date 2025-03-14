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
                        <input type="text" id="searchInput" class="form-control me-2" placeholder="Search Employee..." style="width: 200px;">
                        <button type="button" class="btn btn-primary me-3" id="searchButton">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-light-primary me-3 dropdown-toggle" type="button"
                                id="departmentFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="departmentFilterDropdown" id="filterMenu">
                                <li><a class="dropdown-item filter-department" href="#" data-department="">All Departments</a></li>
                                @foreach ($employees->unique('function') as $employee)
                                    <li>
                                        <a class="dropdown-item filter-department" href="#" data-department="{{ strtolower($employee->function) }}">
                                            {{ $employee->function }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssessmentModal">Add</a>
                    </div>
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
                            <th class="text-end">Actions</th>
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
                                <td class="text-end">
                                    <a href="{{ route('assessments.show', $assessment->employee->id) }}" class="btn btn-info btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-between">
                    <span>Showing {{ $assessments->firstItem() }} to {{ $assessments->lastItem() }} of {{ $assessments->total() }} entries</span>
                    {{ $assessments->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    @include('website.assessment.modal')
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
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

            rows.each(function () {
                var row = $(this);
                var cells = row.find("td");

                if (cells.length >= 5) {
                    var name = cells.eq(1).text().toLowerCase();
                    var department = cells.eq(2).text().toLowerCase();
                    var npk = cells.eq(3).text().toLowerCase();
                    var age = cells.eq(4).text().toLowerCase();

                    var searchMatch = name.includes(searchValue) || department.includes(searchValue) || 
                                      npk.includes(searchValue) || age.includes(searchValue);

                    var departmentMatch = selectedDepartment === "" || department === selectedDepartment;

                    if (searchMatch && departmentMatch) {
                        row.show();
                        isMatchFound = true;
                    } else {
                        row.hide();
                    }
                }
            });

            if (!isMatchFound) {
                tbody.append('<tr id="noDataRow"><td colspan="6" class="text-center text-muted">No Data Found</td></tr>');
            } else {
                $("#noDataRow").remove();
            }
        }

        searchInput.on("keyup", function () {
            filterTable();
        });

        filterItems.on("click", function (event) {
            event.preventDefault();
            var selectedDepartment = $(this).data("department").toLowerCase();
            console.log("🔍 Filter dipilih: ", selectedDepartment);
            filterTable(selectedDepartment);
        });

        $('#addAssessmentModal').on('show.bs.modal', function (event) {
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
                    $('input[name="' + field + '"][value="' + value + '"]').prop('checked', true);
                });

            } else {
                $('#addAssessmentModalLabel').text('Tambah Assessment');
                $('#btnSubmit').text('Simpan');
                $('#assessmentForm')[0].reset();
                $('#assessment_id').val('');
            }
        });

        $('#assessmentForm').submit(function (e) {
            e.preventDefault();
            let assessment_id = $('#assessment_id').val();
            let formData = new FormData(this);
            let url = assessment_id ? "{{ url('/assessments') }}/" + assessment_id : "{{ route('assessments.store') }}";
            let method = assessment_id ? "PUT" : "POST";

            $.ajax({
                url: url,
                type: method,
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    alert(assessment_id ? "Assessment berhasil diperbarui!" : "Assessment berhasil ditambahkan!");
                    $('#addAssessmentModal').modal('hide');
                    location.reload();
                },
                error: function () {
                    alert("Terjadi kesalahan!");
                }
            });
        });
    });
</script>
