@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Assesment' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Assesment' }}
@endsection

@section('main')
    <div id="kt_app_content_container" class="app-container  container-fluid ">
        <div class="app-content  container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Assessment List</h3>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#addAssessmentModal">Add</a>
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
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $assessment->name ?? '-' }}</td>
                                    <td>{{ $assessment->position ?? '-' }}</td>
                                    <td>{{ $assessment->npk ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($assessment->birthday_date)->age ?? '-' }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-warning btn-sm editAssessment" data-bs-toggle="modal"
                                            data-bs-target="#addAssessmentModal" data-id="{{ $assessment->id }}"
                                            data-employee_npk="{{ $assessment->npk }}" data-date="{{ $assessment->date }}"
                                            data-vision_business_sense="{{ $assessment->vision_business_sense }}"
                                            data-customer_focus="{{ $assessment->customer_focus }}"
                                            data-interpersonal_skil="{{ $assessment->interpersonal_skil }}"
                                            data-analysis_judgment="{{ $assessment->analysis_judgment }}"
                                            data-planning_driving_action="{{ $assessment->planning_driving_action }}"
                                            data-leading_motivating="{{ $assessment->leading_motivating }}"
                                            data-teamwork="{{ $assessment->teamwork }}"
                                            data-drive_courage="{{ $assessment->drive_courage }}">
                                            Edit
                                        </button>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('website.assessment.modal')
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Saat tombol "Add" atau "Edit" diklik
        $('#addAssessmentModal').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget); // Tombol yang diklik
            let assessment_id = button.data('id') || null;

            if (assessment_id) {
                // Jika Edit, isi form dengan data dari button
                $('#addAssessmentModalLabel').text('Edit Assessment'); // Ubah judul modal
                $('#btnSubmit').text('Update'); // Ubah teks tombol
                $('#assessment_id').val(assessment_id); // Set ID di form

                $('#employee_npk').val(button.data('employee_npk'));
                $('#date').val(button.data('date'));

                // Isi nilai radio button sesuai data
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
                // Jika Add, kosongkan form
                $('#addAssessmentModalLabel').text('Tambah Assessment'); // Ubah judul modal
                $('#btnSubmit').text('Simpan'); // Ubah teks tombol
                $('#assessmentForm')[0].reset(); // Reset form
                $('#assessment_id').val(''); // Kosongkan hidden ID
            }
        });

        // Handle Submit Form
        $('#assessmentForm').submit(function(e) {
            e.preventDefault();
            let assessment_id = $('#assessment_id').val();
            let formData = new FormData(this);
            let url = assessment_id ?
                "{{ url('/assessments') }}/" + assessment_id :
                "{{ route('assessments.store') }}";
            let method = assessment_id ? "PUT" : "POST";

            $.ajax({
                url: url,
                type: method,
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    alert(assessment_id ? "Assessment berhasil diperbarui!" :
                        "Assessment berhasil ditambahkan!");
                    $('#addAssessmentModal').modal('hide');
                    location.reload();
                },
                error: function(response) {
                    alert("Terjadi kesalahan!");
                }
            });
        });
    });
</script>
