@extends('layouts.root.manage')

@section('title', 'Daftar IDP')

@section('toolbar')
    <div class="d-flex align-items-center gap-3">
        <h1 class="mb-0 fw-semibold">Daftar Individual Development Plan</h1>
    </div>
@endsection

@section('main')
    <div class="app-container container-fluid">

        <div class="card shadow-sm">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                {{-- HEADER + FILTER --}}
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-2 gap-lg-3 w-100 p-4">
                    {{-- Judul + badge filter aktif --}}
                    <div class="me-lg-auto">
                        <div class="fs-4 fw-semibold">Rekap IDP</div>
                        @if (!empty($company) || !empty($positions))
                            <div class="text-muted small mt-1">
                                Filter aktif:
                                @if (!empty($company))
                                    <span class="badge bg-secondary-subtle text-dark me-1">Company:
                                        {{ $company }}</span>
                                @endif
                                @if (!empty($positions))
                                    <span class="badge bg-secondary-subtle text-dark">Posisi:
                                        {{ implode(', ', $positions) }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- FILTER FORM --}}
                    <form method="GET" action="{{ route('idp.manage.all') }}"
                        class="filter-toolbar d-grid d-sm-flex align-items-end gap-2 gap-lg-3 ms-lg-auto">

                        {{-- Company --}}
                        <div class="min-w-200px">
                            <label for="company" class="form-label mb-1 small text-muted">Company</label>
                            <select id="company" name="company" class="form-select">
                                <option value="">Semua</option>
                                @foreach ($companies as $c)
                                    <option value="{{ $c }}" @selected(($company ?? '') === $c)>{{ $c }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Position (multi) --}}
                        <div class="min-w-220px">
                            <label class="form-label mb-1 small text-muted d-block">Position</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary w-100 filter-control" type="button"
                                    id="posDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    <span id="posDropdownLabel">Semua</span>
                                    <i class="ms-2 ki-duotone ki-down fs-6 align-middle"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="posDropdownBtn"
                                    style="min-width:260px; max-height:280px; overflow:auto;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="small text-muted">Pilih Posisi</strong>
                                        <a href="#" class="small" id="clearPositions">Bersihkan</a>
                                    </div>

                                    @php $sel = collect($positions ?? []); @endphp
                                    @foreach ($allPositions as $p)
                                        @php $id = 'pos_'.\Illuminate\Support\Str::slug($p, '_'); @endphp
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" name="positions[]"
                                                value="{{ $p }}" id="{{ $id }}"
                                                @checked($sel->contains($p))>
                                            <label class="form-check-label"
                                                for="{{ $id }}">{{ $p }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Terapkan</button>
                            <a href="{{ route('idp.manage.all') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>
                </div>

                {{-- TABEL --}}
                <div class="table-responsive mt-3">
                    <table id="idpTable" class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:72px;">No</th>
                                <th>Nama Karyawan</th>
                                <th>Perusahaan</th>
                                <th>Posisi</th>
                                <th>Kategori</th>
                                <th>Program</th>
                                <th>Tanggal</th>
                                <th style="width:160px;">Aksi</th>
                                <th class="text-center" style="width:100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($idps as $idp)
                                @php $hasBackup = (int)($idp->backups_count ?? 0) > 0; @endphp
                                <tr>
                                    {{-- kolom nomor (diisi DataTables) --}}
                                    <td data-row-index></td>

                                    <td class="fw-bold">{{ $idp->employee_name ?? '—' }}</td>
                                    <td>{{ $idp->employee_company_name ?? '—' }}</td>
                                    <td>{{ $idp->employee_position ?? '—' }}</td>
                                    <td>{{ $idp->category }}</td>
                                    <td>
                                        <div class="text-truncate" style="max-width:360px" data-bs-toggle="tooltip"
                                            title="{{ $idp->development_program }}">
                                            {{ $idp->development_program }}
                                        </div>
                                    </td>
                                    <td class="text-nowrap" style="font-size:.9rem;">
                                        @php
                                            $d = $idp->date
                                                ? \Illuminate\Support\Carbon::parse($idp->date)->translatedFormat(
                                                    'd M Y',
                                                )
                                                : '—';
                                        @endphp
                                        {{ $d }}
                                    </td>

                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('idp.edit', $idp->id) }}" class="btn btn-primary">Edit</a>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span data-bs-toggle="tooltip"
                                            title="{{ $hasBackup ? 'Ada backup (versi sebelumnya)' : 'Belum ada backup' }}">
                                            @if ($hasBackup)
                                                <i class="fa-solid fa-circle-check text-success"
                                                    style="font-size: 2rem;"></i>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
@endsection

@push('custom-css')
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dt = new DataTable('#idpTable', {
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [6, 'desc']
                ], // kolom Tanggal
                fixedHeader: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/id.json'
                },
                columnDefs: [{
                        targets: 0,
                        orderable: false,
                        searchable: false
                    }, // kolom No
                ]
            });

            // Isi ulang nomor baris saat draw
            function renumber() {
                const start = dt.page.info().start;
                document.querySelectorAll('#idpTable tbody tr').forEach((tr, idx) => {
                    const cell = tr.querySelector('td[data-row-index]');
                    if (cell) cell.textContent = start + idx + 1;
                });
            }
            dt.on('draw', renumber);
            renumber();

            // Bootstrap tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el, {
                    trigger: 'hover'
                });
            });

            // Label dropdown Position
            function updatePosLabel() {
                const checked = Array.from(document.querySelectorAll('input[name="positions[]"]:checked')).map(el =>
                    el.value);
                const label = document.getElementById('posDropdownLabel');
                if (!label) return;
                if (checked.length === 0) label.textContent = 'Semua';
                else if (checked.length <= 2) label.textContent = checked.join(', ');
                else label.textContent = `Dipilih: ${checked.length}`;
            }
            updatePosLabel();
            document.querySelectorAll('input[name="positions[]"]').forEach(el => el.addEventListener('change',
                updatePosLabel));
            const clearBtn = document.getElementById('clearPositions');
            if (clearBtn) clearBtn.addEventListener('click', e => {
                e.preventDefault();
                document.querySelectorAll('input[name="positions[]"]').forEach(el => el.checked = false);
                updatePosLabel();
            });
        });
    </script>

    <style>
        .filter-control {
            border-radius: .75rem;
        }

        .table> :not(caption)>*>* {
            padding: .6rem 1rem;
        }

        #idpTable th {
            white-space: nowrap;
        }

        .dataTables_length label,
        .dataTables_filter label {
            font-size: 1rem;
        }

        .dataTables_wrapper .form-select,
        .dataTables_wrapper .form-control {
            height: calc(2.5rem + 2px);
        }

        .table-striped>tbody>tr:nth-of-type(odd)>* {
            --bs-table-accent-bg: rgba(15, 23, 42, .04) !important;
        }

        .min-w-200px {
            min-width: 200px;
        }

        .min-w-220px {
            min-width: 220px;
        }

        .dropdown-menu {
            border-radius: 1rem;
            border: 1px solid rgba(0, 0, 0, .08);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .12);
        }
    </style>
@endpush
