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
                                        $takeTerm = function ($term) use ($rtcs, $division) {
                                            $aliases = [
                                                'short' => ['short', 'short_term', 'st', 's/t'],
                                                'mid' => ['mid', 'mid_term', 'mt', 'm/t'],
                                                'long' => ['long', 'long_term', 'lt', 'l/t'],
                                            ][$term] ?? [$term];
                                            return $rtcs->first(function ($r) use ($division, $aliases) {
                                                return (int) $r->area_id === (int) $division->id &&
                                                    in_array(strtolower($r->term), $aliases, true);
                                            });
                                        };
                                        $rtcShort = $takeTerm('short');
                                        $rtcMid = $takeTerm('mid');
                                        $rtcLong = $takeTerm('long');

                                        // Nama kandidat (pakai relasi lama di model Division)
                                        $shortName = $division->short->name ?? null;
                                        $midName = $division->mid->name ?? null;
                                        $longName = $division->long->name ?? null;

                                        // lengkap 3 kandidat?
                                        $hasShort = !empty($shortName);
                                        $hasMid = !empty($midName);
                                        $hasLong = !empty($longName);
                                        $complete3 = $hasShort && $hasMid && $hasLong;

                                        // status per-term (0=submitted,1=checked,3=approved)
                                        $s = $rtcShort->status ?? null;
                                        $m = $rtcMid->status ?? null;
                                        $l = $rtcLong->status ?? null;

                                        // Map keseluruhan (sejalan dengan list RTC)
                                        $label = 'Not Set';
                                        $code = 'not_set';
                                        $icon = 'far fa-circle';
                                        if ($complete3) {
                                            $vals = collect([$s, $m, $l])->filter(
                                                fn($v) => in_array($v, [0, 1, 3], true),
                                            );
                                            if ($vals->isEmpty()) {
                                                $label = 'Complete';
                                                $code = 'draft';
                                                $icon = 'far fa-pen-to-square';
                                            } else {
                                                $allApproved = $vals->every(fn($v) => $v === 3);
                                                $allChecked = $vals->every(fn($v) => $v === 1);
                                                $allSubmitted = $vals->every(fn($v) => $v === 0);
                                                if ($allApproved) {
                                                    $label = 'Approved';
                                                    $code = 'approved';
                                                    $icon = 'fas fa-circle-check';
                                                } elseif ($allChecked) {
                                                    $label = 'Checked';
                                                    $code = 'checked';
                                                    $icon = 'fas fa-clipboard-check';
                                                } elseif ($allSubmitted) {
                                                    $label = 'Submitted';
                                                    $code = 'waiting';
                                                    $icon = 'fas fa-paper-plane';
                                                } else {
                                                    $label = 'Partial';
                                                    $code = 'draft';
                                                    $icon = 'far fa-pen-to-square';
                                                }
                                            }
                                        }

                                        $canAdd = !$complete3; // hide Add jika sudah lengkap 3
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

                                        {{-- Status keseluruhan (chip) --}}
                                        @php
                                            $meta = $metaById[$division->id] ?? null;
                                            $badge = $meta['overall_badge'] ?? [
                                                'text' => 'Not Set',
                                                'data_status' => 'not_created',
                                            ];
                                        @endphp

                                        @if ($showStatusColumn)
                                            <td class="text-center">
                                                <span class="status-chip" data-status="{{ $badge['data_status'] }}">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    <span>{{ $badge['text'] }}</span>
                                                </span>
                                            </td>
                                        @endif


                                        <td class="text-center">
                                            {{-- Detail list (ke /rtc/list/:id sesuai route name rtc.list) --}}
                                            <a href="{{ route('rtc.list', ['id' => $division->id]) }}"
                                                class="btn btn-sm btn-primary" title="Detail">
                                                <i class="fas fa-info-circle"></i>
                                            </a>

                                            {{-- View summary (buka orgchart summary) --}}
                                            <a href="{{ route('rtc.summary', ['id' => $division->id, 'filter' => $table]) }}"
                                                class="btn btn-sm btn-info" title="View" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- Add Plan -> hidden bila complete3 --}}
                                            @if (($metaById[$division->id]['can_add'] ?? true) && $showPlanColumns)
                                                <a href="#" class="btn btn-sm btn-success open-add-plan-modal"
                                                    data-id="{{ $division->id }}">
                                                    <i class="fas fa-plus-circle"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        @php $colspan = 3 + ($showPlanColumns ? 3 : 0); @endphp
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

    {{-- Modal Add hanya ditampilkan saat boleh tambah plan --}}
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
                    if (!$modal.length) return; // guard

                    const formData = {
                        filter: @json($table),
                        id: currentDivisionId,
                        short_term: $('#short_term').val(),
                        mid_term: $('#mid_term').val(),
                        long_term: $('#long_term').val(),
                    };

                    $.ajax({
                        url: '{{ route('rtc.update') }}',
                        type: 'GET', // (idealnya POST/PUT)
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
    @endpush

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
