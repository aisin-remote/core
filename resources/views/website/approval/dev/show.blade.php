@extends('layouts.root.main')

@section('title', $title ?? 'Approval One Review Show')
@section('breadcrumbs', $title ?? 'Approval One Review Show')

@push('custom-css')
    <style>
        /* ===== General ===== */
        .section-title {
            font-weight: 800;
            font-size: 1.05rem;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .muted-small {
            font-size: 0.85rem;
            color: #6b7280;
        }

        /* ===== Table: show all text (NO truncate) ===== */
        table.custom-table {
            width: 100%;
            table-layout: auto;           /* penting: biar kolom ngikut konten */
            border-collapse: separate;
            border-spacing: 0;
        }

        table.custom-table th,
        table.custom-table td {
            padding: 0.75rem 1rem;
            vertical-align: top;
            font-size: 0.95rem;
            white-space: normal;          /* teks boleh turun baris */
            word-break: break-word;       /* pecah kata panjang */
            overflow-wrap: anywhere;      /* pecah kata super panjang (url/string) */
            line-height: 1.5rem;
        }

        table.custom-table thead {
            background-color: #f8f9fa;
            font-weight: 800;
        }

        table.custom-table tbody tr:hover {
            background-color: #f1faff;
        }

        /* OPTIONAL: kalau mau ada pemisah visual rapih */
        .table-responsive {
            border-radius: 12px;
            overflow: auto;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .4rem .7rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            font-size: .85rem;
            color: #334155;
            white-space: nowrap;
        }

        /* ===== Highlight One-Year Section ===== */
        .highlight-wrap {
            border: 1px solid rgba(13, 110, 253, .25);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(13, 110, 253, .08);
        }

        .highlight-header {
            background: linear-gradient(90deg, rgba(13, 110, 253, .12), rgba(13, 110, 253, .02));
            border-bottom: 1px solid rgba(13, 110, 253, .18);
        }

        .highlight-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            font-size: .85rem;
            font-weight: 700;
            color: #0d6efd;
            background: rgba(13, 110, 253, .10);
            border: 1px solid rgba(13, 110, 253, .18);
            white-space: nowrap;
        }

        .card-header-sticky {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #fff;
        }

        .one-actions-sticky {
            position: sticky;
            bottom: 0;
            z-index: 3;
            background: #fff;
            border-top: 1px solid #eef2f7;
        }

        /* kolom yang sebaiknya tidak wrap (opsional) */
        .nowrap {
            white-space: nowrap !important;
        }
    </style>
@endpush

@section('main')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div id="kt_app_content_container" class="app-container container-fluid">
        @php
            use Illuminate\Support\Str;

            /**
             * Sumber data:
             * - IDP Target dari $dev->idp
             * - Mid-Year latest dari $dev->idp->developments (ambil yang terbaru)
             * - One-Year dari $oneDevs itu sendiri
             */
            $idpTargets = [];
            $midLatest = [];

            foreach ($oneDevs as $dev) {
                $idp = $dev->idp;
                if (!$idp) continue;

                $alcName = optional(optional($idp)->alc)->name ?? '-';

                $idpTargets[] = [
                    'alc' => $alcName,
                    'category' => $idp->category ?? '-',
                    'program' => $idp->development_program ?? ($dev->development_program ?? '-'),
                    'target' => $idp->development_target ?? '-',
                    'due_date' => $idp->date ?? '-',
                ];

                $latestMid = null;
                if (optional($idp)->developments) {
                    $latestMid = $idp->developments->sortByDesc('created_at')->first();
                }

                $midLatest[] = [
                    'alc' => $alcName,
                    'program' => $idp->development_program ?? ($dev->development_program ?? '-'),
                    'achievement' => $latestMid->development_achievement ?? '-',
                    'next_action' => $latestMid->next_action ?? '-',
                    'status' => $latestMid->status ?? '-',
                    'date' => optional($latestMid?->created_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i') ?? '-',
                ];
            }
        @endphp

        {{-- ===== Header Page ===== --}}
        <div class="card mb-5">
            <div class="card-header align-items-center justify-content-between card-header-sticky">
                <div>
                    <h3 class="card-title mb-1" style="font-size: 1.75rem; font-weight: 800;">
                        Approval One Review Show
                    </h3>
                    <div>
                        {{ $employee->npk ?? '-' }} • {{ $employee->name ?? '-' }} •
                        {{ $employee->position ?? '-' }} • {{ $employee->department->name ?? '-' }}
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- ===== Quick Meta ===== --}}
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="meta-pill"><i class="bi bi-person-badge"></i> Employee: <b>{{ $employee->name ?? '-' }}</b></span>
                    <span class="meta-pill"><i class="bi bi-hash"></i> NPK: <b>{{ $employee->npk ?? '-' }}</b></span>
                    <span class="meta-pill"><i class="bi bi-briefcase"></i> Position: <b>{{ $employee->position ?? '-' }}</b></span>
                    <span class="meta-pill"><i class="bi bi-diagram-3"></i> Dept: <b>{{ $employee->department->name ?? '-' }}</b></span>
                    <span class="meta-pill"><i class="bi bi-list-check"></i> Items: <b>{{ $oneDevs->count() }}</b></span>
                </div>

                {{-- =========================================================
                    SECTION 1: IDP TARGET (ATAS)
                ========================================================== --}}
                <div class="card border mb-5">
                    <div class="card-header">
                        <div class="section-title mb-0">
                            <i class="bi bi-journal-text"></i> IDP Target
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 200px;">ALC</th>
                                        <th style="width: 180px;">Category</th>
                                        <th style="width: 320px;">Development Program</th>
                                        <th>Development Target</th>
                                        <th style="width: 140px;" class="nowrap">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($idpTargets as $row)
                                        <tr>
                                            <td class="fw-bold">{{ $row['alc'] }}</td>
                                            <td>{{ $row['category'] }}</td>
                                            <td class="fw-semibold">{{ $row['program'] ?: '-' }}</td>
                                            <td>{{ $row['target'] ?: '-' }}</td>
                                            <td class="nowrap">{{ $row['due_date'] ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                Tidak ada data IDP Target.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- =========================================================
                    SECTION 2: MID-YEAR (TENGAH)
                ========================================================== --}}
                <div class="card border mb-5">
                    <div class="card-header">
                        <div class="section-title mb-0">
                            <i class="bi bi-arrow-repeat"></i> Mid-Year Development
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 200px;">ALC</th>
                                        <th style="width: 320px;">Development Program</th>
                                        <th>Achievement</th>
                                        <th>Next Action</th>
                                        <th style="width: 140px;">Status</th>
                                        <th style="width: 170px;" class="nowrap">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($midLatest as $row)
                                        <tr>
                                            <td class="fw-bold">{{ $row['alc'] }}</td>
                                            <td class="fw-semibold">{{ $row['program'] ?: '-' }}</td>
                                            <td>{{ $row['achievement'] ?: '-' }}</td>
                                            <td>{{ $row['next_action'] ?: '-' }}</td>
                                            <td>
                                                <span class="badge
                                                    @if(($row['status'] ?? '') === 'draft') badge-light-warning
                                                    @elseif(($row['status'] ?? '') === 'submitted') badge-light-info
                                                    @elseif(($row['status'] ?? '') === 'approved') badge-light-success
                                                    @elseif(($row['status'] ?? '') === 'checked') badge-light-primary
                                                    @elseif(($row['status'] ?? '') === 'revised') badge-light-danger
                                                    @else badge-light-secondary @endif
                                                ">
                                                    {{ Str::ucfirst($row['status'] ?? '-') }}
                                                </span>
                                            </td>
                                            <td class="nowrap">{{ $row['date'] ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                Tidak ada data Mid-Year Development.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- =========================================================
                    SECTION 3: ONE-YEAR (BAWAH) - HIGHLIGHT + ACTION APPROVAL
                ========================================================== --}}
                <div class="highlight-wrap">
                    <div class="card border-0 mb-0">
                        <div class="card-header highlight-header">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <div class="section-title mb-0">
                                        <i class="bi bi-calendar-check-fill"></i> One-Year Development (Approval)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 200px;">ALC</th>
                                            <th style="width: 360px;">Development Program</th>
                                            <th>Evaluation Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($oneDevs as $dev)
                                            @php
                                                $idp = $dev->idp;
                                                $alcName = optional(optional($idp)->alc)->name ?? '-';
                                            @endphp

                                            <tr>
                                                <td class="fw-bold">{{ $alcName }}</td>
                                                <td class="fw-semibold">
                                                    {{ $dev->development_program ?? ($idp->development_program ?? '-') }}
                                                </td>
                                                <td>
                                                    {{ trim((string) $dev->evaluation_result) !== '' ? $dev->evaluation_result : '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-5">
                                                    Tidak ada One-Year Development yang bisa kamu approve saat ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-3 one-actions-sticky d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <span class="small">
                                    Total One-Year items: <b>{{ $oneDevs->count() }}</b>
                                </span>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-danger" id="btnReviseEmployee">
                                        <i class="fas fa-edit me-1"></i> Revise
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" id="btnApproveEmployee">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> {{-- card-body --}}
        </div> {{-- card --}}
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const approveUrl = @json(route('development.approveByEmployee', ['id' => $employee->id]));
            const reviseUrl = @json(route('development.reviseByEmployee', ['id' => $employee->id]));

            async function postJSON(url, payload = {}) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify(payload)
                });

                if (!res.ok) throw new Error(await res.text().catch(() => 'Request failed'));
                return res.json().catch(() => ({}));
            }

            // APPROVE (per employee)
            const btnApprove = document.getElementById('btnApproveEmployee');
            if (btnApprove) {
                btnApprove.addEventListener('click', async () => {
                    const result = await Swal.fire({
                        title: 'Approve Development?',
                        text: 'Yakin ingin APPROVE semua One-Year Development employee ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Approve',
                        cancelButtonText: 'Batal',
                    });
                    if (!result.isConfirmed) return;

                    btnApprove.disabled = true;
                    try {
                        const resp = await postJSON(approveUrl);

                        await Swal.fire({
                            icon: 'success',
                            title: 'Approved',
                            text: resp.message || 'Semua development berhasil di-approve.',
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        window.location.href = @json(route('development.approval')) + '?done=1';
                    } catch (e) {
                        console.error(e);
                        Swal.fire('Error', 'Gagal memproses APPROVE.', 'error');
                    } finally {
                        btnApprove.disabled = false;
                    }
                });
            }

            // REVISE (per employee)
            const btnRevise = document.getElementById('btnReviseEmployee');
            if (btnRevise) {
                btnRevise.addEventListener('click', async () => {
                    const result = await Swal.fire({
                        title: 'Reason for revision',
                        input: 'textarea',
                        inputPlaceholder: 'Tulis alasan revisi (wajib)...',
                        showCancelButton: true,
                        confirmButtonText: 'Kirim',
                        cancelButtonText: 'Batal',
                        inputValidator: value => !value ? 'Alasan revisi wajib diisi.' : null
                    });
                    if (!result.isConfirmed) return;

                    btnRevise.disabled = true;
                    try {
                        const resp = await postJSON(reviseUrl, { note: result.value });

                        await Swal.fire({
                            icon: 'success',
                            title: 'Revisi terkirim',
                            text: resp.message || 'Revisi berhasil dikirim.',
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        window.location.href = @json(route('development.approval')) + '?done=1';
                    } catch (e) {
                        console.error(e);
                        Swal.fire('Error', 'Gagal mengirim revisi.', 'error');
                    } finally {
                        btnRevise.disabled = false;
                    }
                });
            }
        });
    </script>
@endpush
