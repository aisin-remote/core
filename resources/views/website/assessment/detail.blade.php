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
                            <p class="fs-4 fw-bold"><strong>Position:</strong> {{ $employee->position_name }}</p>
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
                                $strengths = $assessments->where('score', '>=', 3)->values(); // Reset indeks
                                $weaknesses = $assessments->where('score', '<', 3)->values(); // Reset indeks
                                $maxRows = max($strengths->count(), $weaknesses->count());
                            @endphp

                            @for ($i = 0; $i < $maxRows; $i++)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>
                                        @if (isset($strengths[$i]))
                                            <strong>{{ $strengths[$i]->alc->name }}</strong>
                                            {{ $strengths[$i]->description }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($weaknesses[$i]))
                                            <strong>{{ $weaknesses[$i]->alc->name }}</strong>
                                            {{ $weaknesses[$i]->description }}
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
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('assessmentChart').getContext('2d');

        if (!ctx) {
            console.error("Canvas dengan ID 'assessmentChart' tidak ditemukan.");
            return;
        }

        // Ambil nama ALC dan skor dari backend
        var labels = @json($assessments->pluck('alc.name'));
        var scores = @json($assessments->pluck('score'));

        console.log("Labels (ALC Name):", labels);
        console.log("Scores:", scores);

        if (labels.length === 0 || scores.length === 0) {
            console.warn("Data chart kosong, grafik tidak dapat ditampilkan.");
            return;
        }

        // Tentukan warna berdasarkan kondisi skor
        var backgroundColors = scores.map(score => score < 3 ? 'rgba(255, 99, 132, 0.6)' :
            'rgba(75, 192, 192, 0.6)');
        var borderColors = scores.map(score => score < 3 ? 'rgba(255, 99, 132, 1)' : 'rgba(75, 192, 192, 1)');

        // Registrasi plugin datalabels
        Chart.register(ChartDataLabels);

        var assessmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Scores ALC',
                    data: scores,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Menyembunyikan legend
                    },
                    datalabels: {
                        anchor: 'center', // Posisikan angka di atas batang
                        align: 'top', // Letakkan angka di bagian atas dalam batang
                        offset: 15, // Geser sedikit ke atas agar terlihat lebih baik
                        color: 'black',
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        formatter: function(value) {
                            return value; // Menampilkan angka sesuai score
                        }
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
