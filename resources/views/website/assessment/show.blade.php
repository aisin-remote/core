@extends('layouts.root.main')

@section('title')
    Detail Assessment - {{ $employee->name }}
@endsection

@section('main')
    <div class="container mt-4">
        <div class="card shadow-lg">


            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <p class="fs-4 fw-bold"><strong>NPK:</strong> {{ $employee->npk }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="fs-4 fw-bold"><strong>Position:</strong> {{ $employee->position_name }}</p>
                    </div>
                </div>


                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle table-hover fs-6"
                        id="kt_table_assessments">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="10%">No</th>
                                <th class="text-center">Date</th>
                                <th class="text-center" width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assessments as $index => $assessment)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">{{ $assessment->date }}</td>

                                    <td class="text-center">

                                        <a class="btn btn-info btn-sm"
                                            href="{{ route('assessments.showByDate', ['employee_id' => $assessment->employee_id, 'date' => $assessment->date]) }}">
                                            Detail
                                        </a>

                                        <a class="btn btn-primary btn-sm" target="_blank"
                                            href="{{ asset('storage/' . $assessment->upload) }}">
                                            View PDF
                                        </a>





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
@endsection

{{-- Tambahkan DataTables --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#kt_table_assessments').DataTable({
            paging: true,
            searching: false,
            lengthChange: false,
            ordering: true,
            responsive: true
        });
    });
</script>
