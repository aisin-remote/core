@extends('layouts.root.main')

@section('title')
    Detail Assessment - {{ $employee->name }}
@endsection

@section('main')
    @php
        // Siapkan label & skor untuk chart dari $details
        $chartLabels = $details->map(fn($d) => $d->alc_name ?? optional($d->alc)->name)->filter()->values();
        $chartScores = $details->pluck('score')->values();
    @endphp

    <div class="container py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h1 class="h4 mb-1">Detail Assessment</h1>
                <div class="text-muted">Data ringkas untuk {{ $employee->name }}</div>
            </div>

            <div class="card-body pt-3">

                {{-- RINGKASAN KARYAWAN --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h2 class="h5 mb-0">Ringkasan Karyawan</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 g-md-4">
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Nama</div>
                                <div class="fs-5 fw-semibold">{{ $employee->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Departemen</div>
                                <div class="fs-5 fw-semibold">
                                    @if ($employee->department)
                                        {{ $employee->department->name }}
                                    @else
                                        <span class="text-muted">Tidak ada departemen</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Tanggal Assessment</div>
                                <div class="fs-5 fw-semibold">{{ $date }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Purpose</div>
                                <div>
                                    @if ($assessment->purpose)
                                        <span
                                            class="badge rounded-pill bg-primary px-3 py-2">{{ $assessment->purpose }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Lembaga</div>
                                <div>
                                    @if ($assessment->lembaga)
                                        <span
                                            class="badge rounded-pill bg-secondary px-3 py-2">{{ $assessment->lembaga }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CHART --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h2 class="h5 mb-0">Grafik Penilaian</h2>
                    </div>
                    <div class="card-body">
                        <div class="ratio ratio-21x9" style="max-height: 380px;">
                            <canvas id="assessmentChart" role="img" aria-label="Grafik skor assessment"></canvas>
                        </div>
                        @if ($chartLabels->isEmpty() || $chartScores->isEmpty())
                            <div class="text-center text-muted mt-3">Belum ada data untuk grafik.</div>
                        @endif
                    </div>
                </div>

                {{-- STRENGTHS --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h2 class="h5 mb-0">Strengths</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width:6%">#</th>
                                        <th style="width:22%">ALC</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $strengths = $details->filter(fn($item) => filled($item->strength))->values(); @endphp
                                    @forelse ($strengths as $index => $strength)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td><strong>{{ $strength->alc_name }}</strong></td>
                                            <td>{{ $strength->strength }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada data strengths.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- WEAKNESSES --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h2 class="h5 mb-0">Weaknesses</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width:6%">#</th>
                                        <th style="width:22%">ALC</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $weaknesses = $details->filter(fn($item) => filled($item->weakness))->values(); @endphp
                                    @forelse ($weaknesses as $index => $weakness)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td><strong>{{ $weakness->alc_name }}</strong></td>
                                            <td>{{ $weakness->weakness }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada data weaknesses.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- SUGGESTION DEVELOPMENT --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h2 class="h5 mb-0">Suggestion Development</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width:6%">#</th>
                                        <th style="width:22%">ALC</th>
                                        <th>Saran Pengembangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $suggests = $details->filter(fn($item) => filled($item->suggestion_development))->values(); @endphp
                                    @forelse ($suggests as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td><strong>{{ $item->alc_name }}</strong></td>
                                            <td>{{ $item->suggestion_development }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada saran pengembangan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        ← Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('custom-css')
    <style>
        /* Membaca nyaman untuk semua usia */
        .card-body,
        .table,
        .badge {
            font-size: 1.05rem;
        }

        .table thead th {
            letter-spacing: .3px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .ratio-21x9 {
            --bs-aspect-ratio: calc(100% * 9 / 21);
        }

        /* Jarak antar section */
        .card+.card {
            margin-top: 1rem;
        }

        @media (max-width: 576px) {

            .card-body,
            .table,
            .badge {
                font-size: 1rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const cvs = document.getElementById('assessmentChart');
            if (!cvs) return;
            const ctx = cvs.getContext('2d');

            // Data dari blade
            const rawLabels = @json($chartLabels);
            const scores = @json($chartScores);
            if (!rawLabels.length || !scores.length) return;

            // --- helper: bungkus label jadi multi-line (maks 16 char per baris)
            function wrapLabel(text, max = 16) {
                const words = String(text || '').split(' ');
                const lines = [];
                let line = '';
                for (const w of words) {
                    const tryLine = line ? line + ' ' + w : w;
                    if (tryLine.length > max) {
                        if (line) lines.push(line);
                        // kalau 1 kata kepanjangan, tetap pakai kata itu sendiri
                        line = w.length > max ? w : w;
                    } else {
                        line = tryLine;
                    }
                }
                if (line) lines.push(line);
                return lines;
            }

            // multi-line labels
            const labels = rawLabels.map(l => wrapLabel(l, 16));

            // pakai horizontal bar jika layar sempit / label multi-line lebih dari 2 baris
            const useHorizontal = window.innerWidth < 768 || labels.some(l => l.length > 2);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels, // <- sudah array of strings per label (multi-line)
                    datasets: [{
                        label: 'Skor ALC',
                        data: scores,
                        backgroundColor: scores.map(s => s < 3 ? 'rgba(220, 53, 69, .6)' :
                            'rgba(13, 110, 253, .6)'),
                        borderColor: scores.map(s => s < 3 ? 'rgb(220, 53, 69)' :
                            'rgb(13, 110, 253)'),
                        borderWidth: 1,
                        barPercentage: 0.8,
                        categoryPercentage: 0.7
                    }]
                },
                options: {
                    indexAxis: useHorizontal ? 'y' : 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 300
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: c => ` Skor: ${c.parsed.y}`
                            }
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        x: useHorizontal ? {
                            beginAtZero: true,
                            ticks: {
                                autoSkip: false, // << tampilkan semua
                                maxRotation: 0,
                                font: {
                                    size: 12,
                                    lineHeight: 1.1
                                },
                                padding: 6
                            },
                            grid: {
                                display: false
                            }
                        } : {
                            ticks: {
                                autoSkip: false, // << tampilkan semua
                                maxRotation: 0,
                                minRotation: 0,
                                font: {
                                    size: 12,
                                    lineHeight: 1.1
                                },
                                padding: 6
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: 5,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
