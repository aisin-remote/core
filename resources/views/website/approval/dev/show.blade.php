@extends('layouts.root.main')

@section('title', $title ?? 'Approval Development Show')
@section('breadcrumbs', $title ?? 'Approval Development Show')

@section('main')
    <div id="kt_app_content_container" class="app-container container-fluid">

        <div class="card mb-5">
            <div class="card-header align-items-center justify-content-between">
                <div>
                    <h3 class="card-title mb-1" style="font-size: 1.75rem; font-weight: 800;">
                        Approval Development
                    </h3>
                    <div class="text-muted">
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
                @php use Illuminate\Support\Str; @endphp

                <style>
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

                    table.custom-table {
                        font-size: 0.93rem;
                    }

                    table.custom-table th,
                    table.custom-table td {
                        padding: 0.75rem 1rem;
                        vertical-align: top;
                    }

                    table.custom-table thead {
                        background-color: #f8f9fa;
                        font-weight: 800;
                    }

                    table.custom-table tbody tr:hover {
                        background-color: #f1faff;
                    }

                    .table-sticky thead th {
                        position: sticky;
                        top: 0;
                        z-index: 2;
                        background: #f8f9fa;
                    }

                    .sticky-ref {
                        position: sticky;
                        top: 90px;
                    }

                    .ref-card {
                        max-height: calc(100vh - 120px);
                        overflow: auto;
                    }

                    .ref-list {
                        display: grid;
                        gap: 14px;
                    }

                    .ref-item {
                        text-decoration: none;
                    }

                    .ref-item:hover {
                        text-decoration: none;
                    }

                    .ref-preview {
                        display: -webkit-box;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        line-height: 1.35rem;
                        max-height: calc(1.35rem * 2);
                    }

                    /* SWEETALERT: NO SCROLL */
                    .swal2-popup {
                        max-height: none !important;
                        height: auto !important;
                    }

                    .swal2-html-container {
                        max-height: none !important;
                        overflow: visible !important;
                    }

                    .swal2-content {
                        overflow: visible !important;
                    }
                </style>

                <div class="row g-6">

                    {{-- ================= LEFT: MAIN APPROVAL ================= --}}
                    <div class="col-lg-8">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="fw-bold" style="font-size: 1.1rem;">One-Year Development (Approval)</div>
                        </div>

                        <div class="card border">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover custom-table mb-0 table-sticky">
                                        <thead>
                                            <tr>
                                                <th style="width: 170px;">ALC</th>
                                                <th>Development Program</th>
                                                <th>Evaluation Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($oneDevs as $dev)
                                                @php
                                                    $idp = $dev->idp;
                                                    $alcName = optional(optional($idp)->alc)->name ?? '-';
                                                    $idpCat = $idp->category ?? '-';
                                                @endphp

                                                <tr>
                                                    <td class="fw-bold">{{ $alcName }}</td>

                                                    <td class="fw-semibold">
                                                        {{ $dev->development_program ?? '-' }}
                                                    </td>

                                                    <td>
                                                        <div class="text-muted">
                                                            {{ Str::limit((string) $dev->evaluation_result, 140) ?: '-' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-5">
                                                        Tidak ada One-Year Development yang bisa kamu approve saat ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">
                                        Total items: <b>{{ $oneDevs->count() }}</b>
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

                    {{-- ================= RIGHT: REFERENCE (STICKY) ================= --}}
                    <div class="col-lg-4">
                        <div class="sticky-ref">
                            <div class="card border ref-card">
                                <div class="card-body">

                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <div class="fw-bold" style="font-size: 1.05rem;">Reference</div>
                                            <div class="text-muted">{{ $employee->name ?? '-' }}</div>
                                        </div>
                                    </div>

                                    {{-- IDP Target --}}
                                    <div class="section-title" style="margin-top:0;">
                                        <i class="bi bi-journal-text"></i> IDP Target
                                    </div>

                                    <div class="ref-list mb-4">
                                        @forelse($oneDevs as $dev)
                                            @php
                                                $idp = $dev->idp;
                                                $alcName = optional(optional($idp)->alc)->name ?? '-';
                                                $fullTarget = trim((string) ($idp->development_target ?? ''));
                                                $previewTarget = Str::limit(strip_tags($fullTarget), 160);
                                            @endphp

                                            <button type="button" class="ref-item btn btn-link text-start p-0 w-100"
                                                data-title="{{ e('IDP Target • ' . $alcName) }}"
                                                data-content="{{ e($fullTarget ?: '-') }}">
                                                <div class="fw-bold">{{ $alcName }}</div>
                                                <div class="ref-preview text-muted">{{ $previewTarget ?: '-' }}</div>
                                            </button>
                                        @empty
                                            <div class="text-muted">-</div>
                                        @endforelse
                                    </div>

                                    <hr>

                                    <div class="section-title" style="margin-top:0;">
                                        <i class="bi bi-arrow-repeat"></i> Mid-Year Development
                                    </div>

                                    <div class="ref-list">
                                        @forelse($oneDevs as $dev)
                                            @php
                                                $idp = $dev->idp;
                                                $alcName = optional(optional($idp)->alc)->name ?? '-';

                                                // ambil mid development terbaru (kalau ada)
                                                $latestMid = optional($idp)->developments
                                                    ? $idp->developments->sortByDesc('created_at')->first()
                                                    : null;

                                                $fullMid = $latestMid
                                                    ? "Achievement:\n" .
                                                        trim((string) $latestMid->development_achievement) .
                                                        "\n\nNext Action:\n" .
                                                        trim((string) $latestMid->next_action)
                                                    : '-';

                                                $previewMid = $latestMid
                                                    ? Str::limit(preg_replace('/\s+/', ' ', strip_tags($fullMid)), 170)
                                                    : '-';
                                            @endphp

                                            <button type="button" class="ref-item btn btn-link text-start p-0 w-100"
                                                data-title="{{ e('Mid-Year • ' . $alcName) }}"
                                                data-content="{{ e($fullMid) }}">
                                                <div class="fw-bold">{{ $alcName }}</div>
                                                <div class="ref-preview text-muted">{{ $previewMid }}</div>
                                            </button>
                                        @empty
                                            <div class="text-muted">-</div>
                                        @endforelse
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- row --}}
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const approveUrl = @json(route('development.approveByEmployee', ['id' => $employee->id]));
            const reviseUrl = @json(route('development.reviseByEmployee', ['id' => $employee->id]));

            // Reference: full content (no scroll)
            document.querySelectorAll('.ref-item').forEach(btn => {
                btn.addEventListener('click', () => {
                    const title = btn.dataset.title || 'Detail';
                    const content = btn.dataset.content || '-';

                    Swal.fire({
                        title: title,
                        html: `
                            <div style="text-align:justify;line-height:1.7;font-size:15px;">
                                ${content}
                            </div>
                        `,
                        width: '900px',
                        padding: '2rem',
                        confirmButtonText: 'Tutup',
                        showCloseButton: true,
                        allowOutsideClick: true,
                        allowEscapeKey: true,
                    });
                });
            });

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
                        title: 'Enter reason for revision',
                        input: 'textarea',
                        inputPlaceholder: 'Write your reason here...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: value => !value ? 'You need to write something!' : null
                    });
                    if (!result.isConfirmed) return;

                    btnRevise.disabled = true;
                    try {
                        const resp = await postJSON(reviseUrl, {
                            note: result.value
                        });

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
