@extends('layouts.root.manage')

@section('title', 'IDP List')

@section('toolbar')
    <div class="idp-header">
        <h1 class="idp-title">Individual Development Plans</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-dark">Back</a>
    </div>
@endsection

@section('main')
    @php
        // Guards to avoid undefined notices on first load
        $company = $company ?? '';
        $positions = $positions ?? [];
        $backup = $backup ?? [];
        $alcsSel = $alcsSel ?? [];

        $selWith = collect($backup)->contains('with');
        $selWithout = collect($backup)->contains('without');

        $selectedAlcNames = collect($allAlcs ?? [])
            ->whereIn('id', $alcsSel)
            ->pluck('name')
            ->values()
            ->all();
    @endphp

    <div class="app-container container-fluid">
        <div class="card shadow-sm">

            {{-- HEADER: Title + chips + mobile Filter toggle --}}
            <div class="card-header border-0 pb-0">
                <div class="d-flex flex-wrap align-items-center gap-2 gap-sm-3">
                    <div class="me-auto">
                        <div class="fs-4 fw-semibold">IDP Overview</div>
                        @if (!empty($company) || !empty($positions) || ($selWith xor $selWithout) || !empty($selectedAlcNames))
                            <div class="mt-1 d-flex flex-wrap gap-2">
                                @if (!empty($company))
                                    <span class="badge bg-secondary-subtle text-dark fw-normal">Company:
                                        {{ $company }}</span>
                                @endif
                                @if (!empty($positions))
                                    <span class="badge bg-secondary-subtle text-dark fw-normal">Positions:
                                        {{ implode(', ', $positions) }}</span>
                                @endif
                                @if ($selWith xor $selWithout)
                                    <span class="badge bg-secondary-subtle text-dark fw-normal">Backup:
                                        {{ $selWith ? 'Available' : 'Not available' }}</span>
                                @endif
                                @if (!empty($selectedAlcNames))
                                    <span class="badge bg-secondary-subtle text-dark fw-normal">
                                        ALC: {{ implode(', ', $selectedAlcNames) }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Mobile toggle --}}
                    <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="fa-solid fa-filter me-2"></i>Filter
                    </button>
                </div>
            </div>

            {{-- FILTER BAR --}}
            <div class="collapse d-lg-block" id="filterCollapse">
                <div class="px-4 py-3">
                    <div class="filter-surface rounded-3 p-3 p-lg-4">
                        <form id="filterForm" method="GET" action="{{ route('idp.manage.all') }}">
                            <div class="row g-3 align-items-end">
                                {{-- Company --}}
                                <div class="col-12 col-md-6 col-lg-auto">
                                    <label for="company" class="form-label small text-muted mb-1">Company</label>
                                    <select id="company" name="company" class="form-select">
                                        <option value="">All</option>
                                        @foreach ($companies as $c)
                                            <option value="{{ $c }}" @selected(($company ?? '') === $c)>
                                                {{ $c }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Position (multi) --}}
                                <div class="col-12 col-md-6 col-lg-auto">
                                    <label class="form-label small text-muted mb-1 d-block">Position</label>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary w-100 filter-control" type="button"
                                            id="posDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                            aria-expanded="false" aria-haspopup="true">
                                            <span id="posDropdownLabel">All</span>
                                            <i class="ms-2 fa-solid fa-caret-down"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="posDropdownBtn"
                                            style="min-width:260px; max-height:280px; overflow:auto;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="small text-muted">Select positions</strong>
                                                <a href="#" class="small" id="clearPositions">Clear all</a>
                                            </div>

                                            @php $selPos = collect($positions ?? []); @endphp
                                            @foreach ($allPositions as $p)
                                                @php $id = 'pos_'.\Illuminate\Support\Str::slug($p, '_'); @endphp
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input form-check-lg" type="checkbox"
                                                        name="positions[]" value="{{ $p }}"
                                                        id="{{ $id }}" @checked($selPos->contains($p))>
                                                    <label class="form-check-label"
                                                        for="{{ $id }}">{{ $p }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{--  ALC (multi) --}}
                                <div class="col-12 col-md-6 col-lg-auto">
                                    <label class="form-label small text-muted mb-1 d-block">ALC</label>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary w-100 filter-control" type="button"
                                            id="alcDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                            aria-expanded="false" aria-haspopup="true">
                                            <span id="alcDropdownLabel">All</span>
                                            <i class="ms-2 fa-solid fa-caret-down"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="alcDropdownBtn"
                                            style="min-width:260px; max-height:280px; overflow:auto;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="small text-muted">Select ALC</strong>
                                                <a href="#" class="small" id="clearAlcs">Clear all</a>
                                            </div>

                                            @foreach ($allAlcs as $alc)
                                                @php $id = 'alc_'. $alc->id; @endphp
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input form-check-lg" type="checkbox"
                                                        name="alcs[]" value="{{ $alc->id }}"
                                                        id="{{ $id }}" @checked(in_array($alc->id, $alcsSel ?? []))>
                                                    <label class="form-check-label"
                                                        for="{{ $id }}">{{ $alc->name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Backup (checkbox) --}}
                                <div class="col-12 col-md-6 col-lg-auto">
                                    <label class="form-label small text-muted mb-1 d-block">Backup Status</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input form-check-lg" type="checkbox" name="backup[]"
                                                id="backup_with" value="with" @checked($selWith)>
                                            <label class="form-check-label" for="backup_with">
                                                <i class="fa-solid fa-circle-check text-success me-1"></i> Available
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input form-check-lg" type="checkbox" name="backup[]"
                                                id="backup_without" value="without" @checked($selWithout)>
                                            <label class="form-check-label" for="backup_without">
                                                <span class="text-muted me-1">—</span> Not available
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Actions (Apply hidden saat JS aktif; Reset tetap) --}}
                                <div class="col-12 col-lg-auto ms-lg-auto d-flex gap-2">
                                    <a href="{{ route('idp.manage.all') }}" class="btn btn-light">
                                        <i class="fa-solid fa-rotate-left me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table id="idpTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:72px;">No.</th>
                                <th class="w-30">Employee</th>
                                <th class="w-60">ALC</th>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Category</th>
                                <th>Program</th>
                                <th>IDP Date</th>
                                <th class="text-center" style="width:110px;">Sacho Revise</th>
                                <th>Revised Date</th>
                                <th style="width:160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($idps as $idp)
                                @php
                                    $hasBackup = (int) ($idp->backups_count ?? 0) > 0;
                                    $changedAtRaw = $idp->latest_backup_changed_at ?? null;
                                    $changedAt = $changedAtRaw
                                        ? \Illuminate\Support\Carbon::parse($changedAtRaw)
                                        : null;
                                @endphp
                                <tr>
                                    <td class="text-center" data-row-index></td>

                                    <td class="fw-semibold">{{ $idp->employee_name ?? '—' }}</td>
                                    <td>{{ $idp->alc->name ?? '—' }}</td>
                                    <td>{{ $idp->employee_company_name ?? '—' }}</td>
                                    <td>{{ $idp->employee_position ?? '—' }}</td>
                                    <td>{{ $idp->category }}</td>

                                    <td>
                                        <div class="line-clamp-2" data-bs-toggle="tooltip"
                                            title="{{ $idp->development_program }}">
                                            {{ $idp->development_program }}
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        @php
                                            $d = $idp->date
                                                ? \Illuminate\Support\Carbon::parse($idp->date)->translatedFormat(
                                                    'd M Y',
                                                )
                                                : '—';
                                        @endphp
                                        {{ $d }}
                                    </td>

                                    <td class="text-center">
                                        <span data-bs-toggle="tooltip"
                                            title="{{ $hasBackup ? 'Has backup (previous version)' : 'No backup yet' }}">
                                            @if ($hasBackup)
                                                <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        {{ $changedAt ? $changedAt->translatedFormat('d M Y') : '-' }}
                                    </td>

                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('idp.edit', $idp->id) }}" class="btn btn-primary">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                            </a>
                                        </div>
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

    <style>
        /* Subtle filter surface */
        .filter-surface {
            background: var(--bs-light-bg-subtle, #f8fafc);
            border: 1px solid rgba(15, 23, 42, .06);
        }

        /* Comfortable controls */
        .filter-control {
            border-radius: .75rem;
        }

        /* Slightly larger checkboxes */
        .form-check-lg {
            width: 1.2rem;
            height: 1.2rem;
        }

        /* Hide Apply button when JS enabled */
        .js-autosubmit-enabled .btn-apply {
            display: none !important;
        }

        /* Table spacing & gentle hover */
        .table>:not(caption)>*>* {
            padding: .7rem 1rem;
        }

        .table-hover tbody tr:hover>* {
            background: rgba(14, 84, 222, .06) !important;
        }

        .table thead th {
            white-space: nowrap;
        }

        /* Program column: 2-line clamp */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            th.w-35 {
                width: 35%;
            }
        }

        /* DataTables controls */
        .dataTables_length label,
        .dataTables_filter label {
            font-size: 1rem;
        }

        .dataTables_wrapper .form-select,
        .dataTables_wrapper .form-control {
            height: calc(2.5rem + 2px);
        }

        .idp-header {
            display: flex;
            flex: content;
            align-items: center;
            justify-content: space-between;
            gap: .75rem
        }

        .idp-title {
            margin: 0;
            font-weight: 600
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // mark JS enabled to hide Apply button
            document.body.classList.add('js-autosubmit-enabled');

            // DataTable
            const dt = new DataTable('#idpTable', {
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [6, 'desc']
                ], // Date column
                fixedHeader: true,
                autoWidth: false,
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false
                }],
            });

            function renumber() {
                const start = dt.page.info().start;
                document.querySelectorAll('#idpTable tbody tr').forEach((tr, idx) => {
                    const cell = tr.querySelector('td[data-row-index]');
                    if (cell) cell.textContent = start + idx + 1;
                });
            }
            dt.on('draw', renumber);
            renumber();

            // Tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el, {
                    trigger: 'hover'
                });
            });

            // Position dropdown label
            function updatePosLabel() {
                const checked = Array.from(document.querySelectorAll('input[name="positions[]"]:checked')).map(el =>
                    el.value);
                const label = document.getElementById('posDropdownLabel');
                if (!label) return;
                if (checked.length === 0) label.textContent = 'All';
                else if (checked.length <= 2) label.textContent = checked.join(', ');
                else label.textContent = `Selected: ${checked.length}`;
            }
            updatePosLabel();

            function updateAlcLabel() {
                const checked = Array.from(document.querySelectorAll('input[name="alcs[]"]:checked'))
                    .map(el => el.nextElementSibling?.textContent?.trim() || el.value);
                const label = document.getElementById('alcDropdownLabel');
                if (!label) return;
                if (checked.length === 0) label.textContent = 'All';
                else if (checked.length <= 2) label.textContent = checked.join(', ');
                else label.textContent = `Selected: ${checked.length}`;
            }
            updateAlcLabel();

            // === Auto-submit on filter change ===
            const form = document.getElementById('filterForm');
            const ddBtnPos = document.getElementById('posDropdownBtn');
            const ddInstPos = ddBtnPos ? bootstrap.Dropdown.getOrCreateInstance(ddBtnPos) : null;

            const ddBtnAlc = document.getElementById('alcDropdownBtn');
            const ddInstAlc = ddBtnAlc ? bootstrap.Dropdown.getOrCreateInstance(ddBtnAlc) : null;

            // Simple debounce to avoid multiple quick submits
            let submitTimer = null;

            function autoSubmit() {
                clearTimeout(submitTimer);
                submitTimer = setTimeout(() => {
                    // close positions dropdown (nicer UX)
                    ddInstPos?.hide();
                    ddInstAlc?.hide();

                    // submit
                    if (form?.requestSubmit) form.requestSubmit();
                    else form?.submit();
                }, 250);
            }

            // Company select: submit on change
            document.getElementById('company')?.addEventListener('change', autoSubmit);

            // Positions checkboxes
            document.querySelectorAll('input[name="positions[]"]').forEach(el => {
                el.addEventListener('change', () => {
                    updatePosLabel();
                    autoSubmit();
                });
            });

            // Clear positions
            document.getElementById('clearPositions')?.addEventListener('click', e => {
                e.preventDefault();
                document.querySelectorAll('input[name="positions[]"]').forEach(el => el.checked = false);
                updatePosLabel();
                autoSubmit();
            });

            // listen ALC changes
            document.querySelectorAll('input[name="alcs[]"]').forEach(el => {
                el.addEventListener('change', () => {
                    updateAlcLabel();
                    autoSubmit();
                });
            });

            // Clear alcs
            document.getElementById('clearAlcs')?.addEventListener('click', e => {
                e.preventDefault();
                document.querySelectorAll('input[name="alcs[]"]').forEach(el => (el.checked = false));
                updateAlcLabel();
                autoSubmit();
            });

            // Backup checkboxes
            document.querySelectorAll('input[name="backup[]"]').forEach(el => {
                el.addEventListener('change', autoSubmit);
            });
        });
    </script>
@endpush
