@extends('layouts.root.main')

@section('main')
    <div class="d-flex flex-column flex-column-fluid">
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
                                <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="1" rowspan="1"
                                    colspan="1">
                                    <span class="dt-column-title" role="button">No</span><span
                                        class="dt-column-order"></span>
                                </th>
                                <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="2" rowspan="1"
                                    colspan="1">
                                    <span class="dt-column-title" role="button">Employee Name</span><span
                                        class="dt-column-order"></span>
                                </th>
                                <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" rowspan="1"
                                    colspan="1">
                                    <span class="dt-column-title" role="button">Department</span><span
                                        class="dt-column-order"></span>
                                </th>
                                <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="4" rowspan="1"
                                    colspan="1">
                                    <span class="dt-column-title" role="button">NPK</span><span
                                        class="dt-column-order"></span>
                                </th>
                                <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="5" rowspan="1"
                                    colspan="1">
                                    <span class="dt-column-title" role="button">Age</span><span
                                        class="dt-column-order"></span>
                                </th>
                                <th class="text-end min-w-100px dt-orderable-none" data-dt-column="6" rowspan="1"
                                    colspan="1">
                                    <span class="dt-column-title">Actions</span><span class="dt-column-order"></span>
                                </th>
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
                                    <td>
                                        {{-- <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('assessments.destroy', $assessment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form> --}}
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
        $('#assessmentForm').submit(function(e) {
            e.preventDefault();

            let formData = new FormData(this); // Ambil data form menggunakan this

            $.ajax({
                url: "{{ route('assessments.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response); // untuk debug response
                    alert("Assessment berhasil ditambahkan!");
                    $('#addAssessmentModal').modal('hide');
                    location.reload();
                },
                error: function(response) {
                    console.log(response); // debug error response
                    alert("Terjadi kesalahan!");
                }
            });


        });
    });
</script>
