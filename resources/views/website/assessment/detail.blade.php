@extends('layouts.root.main')

@section('title')
    Detail Assessment - {{ $employee->name }}
@endsection

@section('main')
    <div class="container mt-4">
        <div class="card shadow-lg">
            <div class="card-body">
                <div class="card mt-4 p-3">
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="fs-4 fw-bold"><strong>Nama:</strong> {{ $employee->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="fs-4 fw-bold"><strong>Departemen:</strong>
                                @if ($employee->department)
                                    {{ $employee->department->name }}
                                @else
                                    Tidak Ada Departemen
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="fs-4 fw-bold"><strong>Date:</strong> {{ $date }}</p>
                        </div>
                    </div>
                </div>
                <div class="card mt-4 p-3">
                    <h4 class="text-center">Assessment Chart</h4>
                    <div style="width: 100%; max-width: auto; margin: 0 auto;">
                        <canvas id="assessmentChart"></canvas>
                    </div>
                </div>
                <div class="card mt-4 p-3">
                    <h4 class="text-center">Strengths</h4>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th class="text-center" style="width: 20%;">Strength</th>
                                <th class="text-center" style="width: 70%;">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $strengths = $details->filter(fn($item) => !empty($item->strength))->values();
                            @endphp

                            @foreach ($strengths as $index => $strength)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $strength->alc_name }}</strong>
                                    </td>
                                    <td>
                                        {{ $strength->strength }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card mt-4 p-3">
                    <h4 class="text-center">Weaknesses</h4>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th class="text-center" style="width: 20%;">Weakness</th>
                                <th class="text-center" style="width: 75%;">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $weaknesses = $details->filter(fn($item) => !empty($item->weakness))->values();
                            @endphp

                            @foreach ($weaknesses as $index => $weakness)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $weakness->alc_name }}</strong>
                                    </td>
                                    <td>
                                        {{ $weakness->weakness }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card mt-4 p-3">
                    <h4 class="text-center">Suggestion Development</h4>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th class="text-center" style="width: 20%;">ALC</th>
                                <th class="text-center" style="width: 75%;">Suggestion Development</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $suggests = $details->filter(fn($item) => !empty($item->suggestion_development))->values();
                            @endphp

                            @foreach ($suggests as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><strong>{{ $item->alc_name }}</strong></td>
                                    <td>{{ $item->suggestion_development }}</td>
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var canvas = document.getElementById('assessmentChart');
            if (!canvas) {
                console.error("Canvas 'assessmentChart' tidak ditemukan.");
                return;
            }
            var ctx = canvas.getContext('2d');

            // Ambil data dari backend
            var labels = @json($assessments->pluck('alc.name'));
            var scores = @json($assessments->pluck('score'));

            console.log("Labels:", labels);
            console.log("Scores:", scores);

            if (!labels.length || !scores.length) {
                console.warn("Data kosong, tidak menampilkan grafik.");
                return;
            }

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Scores ALC',
                        data: scores,
                        backgroundColor: scores.map(score => score < 3 ?
                            'rgba(255, 99, 132, 0.6)' :
                            'rgba(75, 192, 192, 0.6)'),
                        borderColor: scores.map(score => score < 3 ?
                            'rgba(255, 99, 132, 1)' :
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
        });
    </script>
@endpush
