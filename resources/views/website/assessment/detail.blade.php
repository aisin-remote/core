@extends('layouts.root.main')

@section('title')
    Detail Assessment - {{ $employee->name }}
@endsection

@section('main')
    <div class="container mt-4">
        <div class="card shadow-lg">
            <div class="card-body">
                <div class="card mt-4 p-3">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p class="fs-4 fw-bold"><strong>Nama:</strong> {{ $employee->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="fs-4 fw-bold"><strong>Departement:</strong> {{ $employee-> function}}</p>
                        </div>
                        <div class="col-md-4">
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
                    <h4 class="text-center">Strengths & Weaknesses</h4>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 5%;">#</th>
                                <th class="text-center" style="width: 45%;">Strength</th>
                                <th class="text-center" style="width: 45%;">Weakness</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Ambil hanya yang memiliki strength atau weakness
                                $strengths = $details->filter(fn($item) => !empty($item->strength))->values();
                                $weaknesses = $details->filter(fn($item) => !empty($item->weakness))->values();
                                $maxRows = max($strengths->count(), $weaknesses->count()); // Menyesuaikan jumlah baris maksimum
                            @endphp

                            @for ($i = 0; $i < $maxRows; $i++)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>
                                        @if (isset($strengths[$i]))
                                            <strong>{{ $strengths[$i]->alc_name }}</strong> - {{ $strengths[$i]->strength }}
                                        @endif
                                    </td>
            
                                    <td>
                                        @if (isset($weaknesses[$i]))
                                            <strong>{{ $weaknesses[$i]->alc_name }}</strong> - {{ $weaknesses[$i]->weakness }}
                                        @endif
                                    </td>
                                </tr>
                            @endfor


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

        Chart.register(ChartDataLabels);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Scores ALC',
                    data: scores,
                    backgroundColor: scores.map(score => score < 3 ? 'rgba(255, 99, 132, 0.6)' : 'rgba(75, 192, 192, 0.6)'),
                    borderColor: scores.map(score => score < 3 ? 'rgba(255, 99, 132, 1)' : 'rgba(75, 192, 192, 1)'),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'center',
                        align: 'top',
                        color: 'black',
                        font: { weight: 'bold', size: 14 },
                        formatter: value => value
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 5,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    });
</script>