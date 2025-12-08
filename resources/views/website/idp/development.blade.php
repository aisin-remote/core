@extends('layouts.root.main')

@push('custom-css')
    <style>
        .legend-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .section-title {
            font-weight: 600;
            font-size: 1.1rem;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #0d6efd;
            font-size: 1.1rem;
        }

        .table-sm-custom th,
        .table-sm-custom td {
            padding: 0.5rem 0.75rem;
            vertical-align: top;
        }

        .badge-pill {
            border-radius: 999px;
            padding-inline: 0.7rem;
        }

        .text-muted-small {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .card-header-sticky {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #fff;
        }

        @media (max-width: 768px) {
            .flex-wrap-sm {
                flex-wrap: wrap;
            }
        }
    </style>
@endpush

@section('title')
    {{ $title ?? 'IDP Development' }}
@endsection

@section('breadcrumbs')
    IDP / {{ $assessment->employee->name ?? '-' }} / Development
@endsection

@section('main')
    @php
        use App\Models\Development;
        use App\Models\DevelopmentOne;

        /**
         * 1. Bangun rows IDP berdasarkan:
         *    - ALC yang punya weakness (sudah difilter di controller)
         *    - IDP yang dikirim dari controller, sudah di-groupBy('alc_id') => $idps
         */
        $idpRows = [];
        $alcByIdp = []; // map idp_id => alc_name (buat history table)

        foreach ($assessment->details as $detail) {
            $alcName = $detail->alc->name
                ?? $detail->alc->title
                ?? ('ALC ' . $detail->alc_id);

            $alcId = $detail->alc_id;

            // IDP untuk ALC ini (dari controller, grouped by alc_id)
            /** @var \Illuminate\Support\Collection $detailIdps */
            $detailIdps = $idps[$alcId] ?? collect();

            // Kalau belum ada IDP sama sekali untuk ALC ini, lewati (karena Mid/One-Year butuh idp_id)
            if ($detailIdps->isEmpty()) {
                continue;
            }

            // IDP terbaru sebagai "utama" untuk tabel utama
            $latestIdp = $detailIdps->sortByDesc('updated_at')->first();

            if ($latestIdp) {
                $idpRows[] = [
                    'alc_name' => $alcName,
                    'alc_id'   => $alcId,
                    'idp'      => $latestIdp,
                ];
            }

            // isi map idp_id => alc_name untuk history
            foreach ($detailIdps as $idp) {
                $alcByIdp[$idp->id] = $alcName;
            }
        }

        // 2. Ambil data development yang sudah pernah disimpan (optional, untuk history)
        $midDevs = Development::where('employee_id', $assessment->employee_id ?? null)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('idp_id');

        $oneDevs = DevelopmentOne::where('employee_id', $assessment->employee_id ?? null)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('idp_id');
    @endphp

    {{-- Notifikasi sukses --}}
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Sukses!",
                    text: @json(session('success')),
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    {{-- Notifikasi error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <div class="fw-bold mb-1">Terjadi kesalahan:</div>
            <ul class="mb-0 ps-4">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap-sm gap-3">
            <h3 class="mb-0">{{ $title ?? 'IDP Development' }}</h3>
            <a href="{{ route('idp.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Back to IDP List
            </a>
        </div>

        {{-- CARD: Employee & Assessment Info --}}
        <div class="card mb-5">
            <div class="card-body d-flex flex-wrap gap-4 align-items-center">
                {{-- Kiri: Employee Info --}}
                <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-50px symbol-circle bg-light-primary text-primary fw-bold">
                        {{ Str::substr($assessment->employee->name ?? '?', 0, 1) }}
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ $assessment->employee->name ?? '-' }}</div>
                        <div class="text-muted">
                            {{ $assessment->employee->position ?? '-' }}<br>
                            {{ $assessment->employee->department_name ?? $assessment->employee->department ?? '-' }}
                        </div>
                    </div>
                </div>

                {{-- Kanan: Assessment Info --}}
                <div class="border-start ps-4 ms-2">
                    <div><strong>Assessment Purpose:</strong> {{ $assessment->purpose ?? '-' }}</div>
                    <div><strong>Assessor:</strong> {{ $assessment->lembaga ?? '-' }}</div>
                    <div><strong>Target Position:</strong> {{ $assessment->target_position ?? '-' }}</div>
                    <div><strong>Assessment Date:</strong>
                        {{ optional($assessment->created_at)->timezone('Asia/Jakarta')->format('d M Y') ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD: IDP List (Read Only) --}}
        <div class="card mb-5">
            <div class="card-header card-header-sticky">
                <h4 class="card-title mb-0">Individual Development Program (IDP)</h4>
            </div>
            <div class="card-body">
                @if (count($idpRows))
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm-custom">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 20%;">ALC</th>
                                    <th style="width: 15%;">Category</th>
                                    <th>Development Program</th>
                                    <th style="width: 20%;">Development Target</th>
                                    <th style="width: 12%;">Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($idpRows as $idx => $row)
                                    @php
                                        /** @var \App\Models\Idp $idp */
                                        $idp = $row['idp'];
                                    @endphp
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $row['alc_name'] }}</td>
                                        <td>{{ $idp->category ?? '-' }}</td>
                                        <td>{{ $idp->development_program ?? '-' }}</td>
                                        <td>{{ $idp->development_target ?? '-' }}</td>
                                        <td>
                                            @if (!empty($idp->date))
                                                {{ \Illuminate\Support\Carbon::parse($idp->date)->timezone('Asia/Jakarta')->format('d-m-Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="text-muted-small mt-2">
                            *Data di atas adalah program IDP yang menjadi dasar pengisian Mid-Year & One-Year Development.
                        </p>
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada IDP yang dibuat untuk assessment ini.</p>
                @endif
            </div>
        </div>

        {{-- CARD: Development Progress (Mid-Year & One-Year) --}}
        <div class="card">
            <div class="card-header card-header-sticky">
                <ul class="nav nav-tabs card-header-tabs" role="tablist" style="cursor:pointer">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-mid" role="tab"
                           aria-selected="true">
                            Mid-Year Review
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-one" role="tab"
                           aria-selected="false">
                            One-Year Review
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">

                    {{-- TAB: Mid-Year Review --}}
                    <div class="tab-pane fade show active" id="tab-mid" role="tabpanel">
                        @if (!count($idpRows))
                            <p class="text-muted">Tidak ada IDP yang bisa di-review. Buat IDP terlebih dahulu.</p>
                        @else
                            <form method="POST"
                                  action="{{ route('idp.storeMidYear', ['employee_id' => $assessment->employee_id]) }}">
                                @csrf

                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-hover table-sm-custom align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 20%;">ALC</th>
                                                <th style="width: 25%;">Development Program</th>
                                                <th style="width: 20%;">Achievement (Mid-Year)</th>
                                                <th style="width: 20%;">Next Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($idpRows as $idx => $row)
                                                @php
                                                    /** @var \App\Models\Idp $idp */
                                                    $idp = $row['idp'];
                                                    $idpId = $idp->id;
                                                    $midList = $midDevs[$idpId] ?? collect();
                                                    $lastMid = $midList->first();
                                                @endphp
                                                <tr>
                                                    <td>{{ $idx + 1 }}</td>
                                                    <td>{{ $row['alc_name'] }}</td>
                                                    <td>
                                                        {{ $idp->development_program ?? '-' }}
                                                        <input type="hidden" name="idp_id[]" value="{{ $idpId }}">
                                                        <input type="hidden" name="development_program[]"
                                                               value="{{ $idp->development_program }}">
                                                    </td>
                                                    <td>
                                                        <textarea name="development_achievement[]"
                                                                  class="form-control form-control-sm"
                                                                  rows="2"
                                                                  placeholder="Tuliskan capaian pengembangan">{{ old("development_achievement.$idx") }}</textarea>
                                                        @if ($lastMid)
                                                            <div class="text-muted-small mt-1">
                                                                <i class="fas fa-clock me-1"></i>
                                                                Last: {{ $lastMid->development_achievement ?? '-' }}
                                                                ({{ optional($lastMid->created_at)->timezone('Asia/Jakarta')->format('d-m-Y') }})
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <textarea name="next_action[]"
                                                                  class="form-control form-control-sm"
                                                                  rows="2"
                                                                  placeholder="Next action setelah mid-year">{{ old("next_action.$idx") }}</textarea>
                                                        @if ($lastMid && $lastMid->next_action)
                                                            <div class="text-muted-small mt-1">
                                                                <i class="fas fa-list me-1"></i>
                                                                Last: {{ $lastMid->next_action }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Mid-Year Development
                                    </button>
                                </div>
                            </form>
                        @endif

                        @if ($midDevs->isNotEmpty())
                            <hr class="my-4">
                            <div class="section-title">
                                <i class="bi bi-bar-chart-line-fill"></i>
                                Mid-Year Development History
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover table-sm-custom">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%;">Development Program</th>
                                            <th style="width: 20%;">ALC</th>
                                            <th style="width: 25%;">Achievement</th>
                                            <th style="width: 25%;">Next Action</th>
                                            <th style="width: 10%;">Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($midDevs as $idpId => $devList)
                                            @foreach ($devList as $dev)
                                                @php
                                                    $alcName = $alcByIdp[$dev->idp_id] ?? '-';
                                                @endphp
                                                <tr>
                                                    <td>{{ $dev->development_program ?? '-' }}</td>
                                                    <td>{{ $alcName }}</td>
                                                    <td>{{ $dev->development_achievement ?? '-' }}</td>
                                                    <td>{{ $dev->next_action ?? '-' }}</td>
                                                    <td>{{ optional($dev->created_at)->timezone('Asia/Jakarta')->format('d-m-Y') }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- TAB: One-Year Review --}}
                    <div class="tab-pane fade" id="tab-one" role="tabpanel">
                        @if (!count($idpRows))
                            <p class="text-muted">Tidak ada IDP yang bisa di-review. Buat IDP terlebih dahulu.</p>
                        @else
                            <form method="POST"
                                  action="{{ route('idp.storeOneYear', ['employee_id' => $assessment->employee_id]) }}">
                                @csrf

                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-hover table-sm-custom align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 20%;">ALC</th>
                                                <th style="width: 25%;">Development Program</th>
                                                <th style="width: 20%;">Evaluation Result (One-Year)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($idpRows as $idx => $row)
                                                @php
                                                    /** @var \App\Models\Idp $idp */
                                                    $idp = $row['idp'];
                                                    $idpId = $idp->id;
                                                    $oneList = $oneDevs[$idpId] ?? collect();
                                                    $lastOne = $oneList->first();
                                                @endphp
                                                <tr>
                                                    <td>{{ $idx + 1 }}</td>
                                                    <td>{{ $row['alc_name'] }}</td>
                                                    <td>
                                                        {{ $idp->development_program ?? '-' }}
                                                        <input type="hidden" name="idp_id[]" value="{{ $idpId }}">
                                                        <input type="hidden" name="development_program[]"
                                                               value="{{ $idp->development_program }}">
                                                    </td>
                                                    <td>
                                                        <textarea name="evaluation_result[]"
                                                                  class="form-control form-control-sm"
                                                                  rows="2"
                                                                  placeholder="Tuliskan hasil evaluasi akhir tahun">{{ old("evaluation_result.$idx") }}</textarea>
                                                        @if ($lastOne)
                                                            <div class="text-muted-small mt-1">
                                                                <i class="fas fa-clock me-1"></i>
                                                                Last: {{ $lastOne->evaluation_result ?? '-' }}
                                                                ({{ optional($lastOne->created_at)->timezone('Asia/Jakarta')->format('d-m-Y') }})
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save One-Year Development
                                    </button>
                                </div>
                            </form>
                        @endif

                        @if ($oneDevs->isNotEmpty())
                            <hr class="my-4">
                            <div class="section-title">
                                <i class="bi bi-calendar-check-fill"></i>
                                One-Year Development History
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover table-sm-custom">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 25%;">Development Program</th>
                                            <th style="width: 20%;">ALC</th>
                                            <th style="width: 35%;">Evaluation Result</th>
                                            <th style="width: 10%;">Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($oneDevs as $idpId => $devList)
                                            @foreach ($devList as $dev)
                                                @php
                                                    $alcName = $alcByIdp[$dev->idp_id] ?? '-';
                                                @endphp
                                                <tr>
                                                    <td>{{ $dev->development_program ?? '-' }}</td>
                                                    <td>{{ $alcName }}</td>
                                                    <td>{{ $dev->evaluation_result ?? '-' }}</td>
                                                    <td>{{ optional($dev->created_at)->timezone('Asia/Jakarta')->format('d-m-Y') }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
