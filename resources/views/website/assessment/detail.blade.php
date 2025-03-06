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
                    <div class="col-md-4">
                    <p class="fs-4 fw-bold"><strong>Date:</strong> {{ $date }}</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle table-hover fs-6"
                        id="kt_table_assessments">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">ALC Name</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Description</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assessments as $key => $assessment)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td class="text-center">{{ $assessment->alc->name }}</td>
                                    <td class="text-center">{{ $assessment->score }}</td>
                                    <td class="text-center">{{ $assessment->description }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>


    </div>
@endsection
