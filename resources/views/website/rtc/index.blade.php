@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'RTC' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'RTC' }}
@endsection

@push('custom-css')
    <style>
        /* ===== Status Chip (sama seperti IDP) ===== */
        .status-chip {
            --bg: #eef2ff;
            --fg: #312e81;
            --bd: #c7d2fe;
            --dot: #6366f1;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .9rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: .9rem;
            line-height: 1;
            border: 1px solid var(--bd);
            background: var(--bg);
            color: var(--fg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .status-chip i {
            font-size: 1rem;
            opacity: .95
        }

        .status-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dot);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--dot) 20%, transparent)
        }

        .status-chip[data-status="approved"] {
            --bg: #ecfdf5;
            --fg: #065f46;
            --bd: #a7f3d0;
            --dot: #10b981
        }

        .status-chip[data-status="checked"] {
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b
        }

        .status-chip[data-status="waiting"] {
            --bg: #fffbeb;
            --fg: #92400e;
            --bd: #fde68a;
            --dot: #f59e0b
        }

        .status-chip[data-status="draft"] {
            --bg: #f8fafc;
            --fg: #334155;
            --bd: #e2e8f0;
            --dot: #94a3b8
        }

        .status-chip[data-status="not_created"],
        .status-chip[data-status="unknown"] {
            --bg: #f4f4f5;
            --fg: #27272a;
            --bd: #e4e4e7;
            --dot: #a1a1aa
        }

        @keyframes pulseDot {
            0% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 30%, transparent)
            }

            70% {
                box-shadow: 0 0 0 8px color-mix(in srgb, var(--dot) 0%, transparent)
            }

            100% {
                box-shadow: 0 0 0 0 color-mix(in srgb, var(--dot) 0%, transparent)
            }
        }

        .status-chip[data-status="waiting"]::before {
            animation: pulseDot 1.25s infinite
        }

        @media (max-width:768px) {
            .status-chip {
                max-width: 210px
            }
        }
    </style>
@endpush

@section('main')
    @if (session()->has('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Sukses!",
                    text: "{{ session('success') }}",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ $table }} List</h3>
                        <div class="d-flex align-items-center">
                            <input type="text" id="searchInput" class="form-control me-2" placeholder="Search ..."
                                style="width:200px;">
                            <button type="button" class="btn btn-primary me-3" id="searchButton">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                                data-kt-menu-placement="bottom-end">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_table_users">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th class="text-center">{{ $table }}</th>
                                    @if ($showPlanColumns)
                                        <th class="text-center">Short Term</th>
                                        <th class="text-center">Mid Term</th>
                                        <th class="text-center">Long Term</th>
                                    @endif
                                    @if ($showStatusColumn)
                                        <th class="text-center">Status</th>
                                    @endif
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($divisions as $division)
                                    @php
                                        // Nama kandidat hasil dekorasi controller (fallback ke relasi lama kalau ada)
                                        $shortName = $division->st_name ?? ($division->short->name ?? null);
                                        $midName = $division->mt_name ?? ($division->mid->name ?? null);
                                        $longName = $division->lt_name ?? ($division->long->name ?? null);

                                        // Pemetaan kode -> data-status chip + ikon
                                        $code = $division->overall_code ?? 'not_set';
                                        $label = $division->overall_label ?? 'Not Set';
                                        $chipStatus = match ($code) {
                                            'approved' => 'approved',
                                            'checked' => 'checked',
                                            'submitted' => 'waiting',
                                            'partial' => 'draft',
                                            'complete_no_submit' => 'draft',
                                            'not_set' => 'not_created',
                                            default => 'unknown',
                                        };
                                        $icon = match ($chipStatus) {
                                            'approved' => 'fas fa-circle-check',
                                            'checked' => 'fas fa-clipboard-check',
                                            'waiting' => 'fas fa-paper-plane',
                                            'draft' => 'far fa-pen-to-square',
                                            'not_created' => 'far fa-circle',
                                            default => 'fa-circle-info',
                                        };

                                        $canAdd = property_exists($division, 'can_add')
                                            ? (bool) $division->can_add
                                            : !($shortName && $midName && $longName);
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $division->name }}</td>

                                        @if ($showPlanColumns)
                                            <td class="text-center">
                                                @if ($shortName)
                                                    {{ $shortName }}
                                                @else
                                                    <span class="text-danger">not set</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($midName)
                                                    {{ $midName }}
                                                @else
                                                    <span class="text-danger">not set</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($longName)
                                                    {{ $longName }}
                                                @else
                                                    <span class="text-danger">not set</span>
                                                @endif
                                            </td>
                                        @endif

                                        @if ($showStatusColumn)
                                            <td class="text-center">
                                                <span class="status-chip" data-status="{{ $chipStatus }}">
                                                    <i class="fa-solid {{ $icon }}"></i>
                                                    <span>{{ $label }}</span>
                                                </span>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if ($table === 'Plant')
                                                {{-- Direktur: buka daftar Division di Plant --}}
                                                <a href="{{ route('rtc.list', ['id' => $division->id, 'level' => 'plant']) }}"
                                                    class="btn btn-sm btn-primary" title="Open Divisions">
                                                    <i class="fas fa-list-ul"></i>
                                                </a>
                                                <a href="{{ route('rtc.summary', ['id' => $division->id, 'filter' => strtolower($table)]) }}"
                                                    class="btn btn-sm btn-info" title="View" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @else
                                                {{-- Detail list (mode lama per Division/Department) --}}
                                                <a href="{{ route('rtc.list', ['id' => $division->id]) }}"
                                                    class="btn btn-sm btn-primary" title="Detail">
                                                    <i class="fas fa-info-circle"></i>
                                                </a>

                                                {{-- View summary (filter HARUS lowercase) --}}
                                                <a href="{{ route('rtc.summary', ['id' => $division->id, 'filter' => strtolower($table)]) }}"
                                                    class="btn btn-sm btn-info" title="View" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Add Plan (kalau memang ditampilkan) --}}
                                                @if (($metaById[$division->id]['can_add'] ?? true) && $showPlanColumns)
                                                    <a href="#" class="btn btn-sm btn-success open-add-plan-modal"
                                                        data-id="{{ $division->id }}">
                                                        <i class="fas fa-plus-circle"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    @php
                                        $colspan = 2 + ($showPlanColumns ? 3 : 0) + ($showStatusColumn ? 1 : 0) + 1; // actions
                                    @endphp
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="text-center text-muted">No data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add hanya saat boleh tambah plan (GM/Direktur tidak akan melihat karena showPlanColumns=false) --}}
    @if ($showPlanColumns)
        <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="addPlanForm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Plan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="filter" value="{{ strtolower($table) }}">
                            @foreach (['short_term' => 'Short Term', 'mid_term' => 'Mid Term', 'long_term' => 'Long Term'] as $key => $label)
                                <div class="mb-3">
                                    <label for="{{ $key }}" class="form-label">{{ $label }}</label>
                                    <select id="{{ $key }}" class="form-select" name="{{ $key }}">
                                        <option value="">-- Select --</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="submitPlanBtn" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if ($showPlanColumns)
        <script>
            let currentDivisionId = null;

            $(document).on('click', '.open-add-plan-modal', function() {
                currentDivisionId = $(this).data('id');
                const $modal = $('#addPlanModal');
                if ($modal.length) $modal.modal('show');
            });

            $('#submitPlanBtn').on('click', function() {
                const $modal = $('#addPlanModal');
                if (!$modal.length) return;

                const formData = {
                    filter: @json($table),
                    id: currentDivisionId,
                    short_term: $('#short_term').val(),
                    mid_term: $('#mid_term').val(),
                    long_term: $('#long_term').val(),
                };

                $.ajax({
                    url: '{{ route('rtc.update') }}',
                    type: 'GET', // idealnya POST/PUT
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        $modal.modal('hide');
                        window.location.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            });
        </script>
    @endif

    <script>
        // Simple search di client
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('searchInput');
            const tbody = document.querySelector('#kt_table_users tbody');
            input.addEventListener('keyup', function() {
                const q = this.value.toLowerCase();
                [...tbody.querySelectorAll('tr')].forEach(tr => {
                    const text = tr.innerText.toLowerCase();
                    tr.style.display = text.includes(q) ? '' : 'none';
                });
            });
        });
    </script>
@endpush
