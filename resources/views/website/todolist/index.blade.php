@extends('layouts.root.main')

@section('title')
    {{ $title ?? 'Employee' }}
@endsection

@section('breadcrumbs')
    {{ $title ?? 'Employee' }}
@endsection

@push('custom-css')
    <style>
        :root {
            /* brand & surface */
            --brand: #0E54DE;
            --brand-ink: #0a3aa0;
            --ink: #0f172a;
            --muted: #64748b;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --border: #e2e8f0;
            --shadow: 0 6px 24px rgba(2, 6, 23, .08);

            /* status tokens (kontras tinggi) */
            --ok-bg: #ecfdf5;
            --ok-fg: #065f46;
            --ok-bd: #a7f3d0;
            --info-bg: #eff6ff;
            --info-fg: #1e40af;
            --info-bd: #bfdbfe;
            --warn-bg: #fffbeb;
            --warn-fg: #92400e;
            --warn-bd: #fde68a;
            --err-bg: #fef2f2;
            --err-fg: #7f1d1d;
            --err-bd: #fecaca;
            --muted-bg: #f4f4f5;
            --muted-fg: #27272a;
            --muted-bd: #e4e4e7;

            --radius: 16px;
        }

        /* === keep all cards in one row (horizontal strip) === */
        .row-nowrap {
            display: flex;
            flex-wrap: nowrap !important;
            overflow-x: auto;
            gap: 1rem;
            /* jarak antar card */
            padding-bottom: .25rem;
            /* ruang buat scrollbar */
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
        }

        /* scrollbar (optional, bisa dihapus kalau tidak perlu) */
        .row-nowrap::-webkit-scrollbar {
            height: 10px
        }

        .row-nowrap::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px
        }

        /* lebar tiap kolom-card saat di strip */
        /* === panels auto-shrink supaya muat dalam satu layar === */
        .row-nowrap>[class^="col-"],
        .row-nowrap>[class*=" col-"] {
            flex: 1 1 0 !important;
            /* biar boleh menyusut */
            max-width: none !important;
            min-width: 300px;
            /* aman utk 4 panel pada layar 1366–1440px */
            scroll-snap-align: start;
        }

        /* Boleh diperkecil lagi kalau perlu lebih padat */
        @media (max-width: 1366px) {

            .row-nowrap>[class^="col-"],
            .row-nowrap>[class*=" col-"] {
                min-width: 280px;
            }
        }

        /* Di layar lebar, sedikit lebih lega */
        @media (min-width: 1600px) {

            .row-nowrap>[class^="col-"],
            .row-nowrap>[class*=" col-"] {
                min-width: 340px;
            }
        }


        /* panel/card korporat */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            color: #fff;
            background: linear-gradient(120deg, var(--brand) 0%, var(--brand) 100%);
        }

        .panel-head h3 {
            margin: 0;
            font-weight: 700;
            letter-spacing: .2px;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 1.1rem;
        }

        .panel-body {
            padding: 1rem;
            background: var(--surface);
        }

        .panel-scroll {
            max-height: 600px;
            overflow: auto;
        }

        /* item tugas */
        .task-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .1rem;
            padding: .9rem 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            transition: box-shadow .15s, transform .15s, border-color .15s;
            position: relative;
        }

        .task-row+.task-row {
            margin-top: .75rem;
        }

        .task-row:hover {
            box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
            transform: translateY(-1px);
        }

        .task-title {
            margin: 0;
            font-weight: 600;
            color: var(--ink);
        }

        .task-sub {
            color: var(--muted);
            font-size: .9rem;
        }

        /* garis aksen kiri sesuai tone */
        .tone {
            position: absolute;
            inset: 0 0 0 0;
            border-radius: 12px;
            pointer-events: none;
        }

        .tone::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            border-radius: 12px 0 0 12px;
        }

        .tone-info::before {
            background: #3b82f6;
        }

        .tone-ok::before {
            background: #10b981;
        }

        .tone-warn::before {
            background: #f59e0b;
        }

        .tone-err::before {
            background: #ef4444;
        }

        .tone-muted::before {
            background: #94a3b8;
        }

        /* status chip */
        .status-chip {
            --bg: var(--muted-bg);
            --fg: var(--muted-fg);
            --bd: var(--muted-bd);
            --dot: #94a3b8;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .75rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: .85rem;
            background: var(--bg);
            color: var(--fg);
            border: 1px solid var(--bd);
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .status-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dot);
        }

        .status-chip i {
            font-size: .95rem;
        }

        .status-ok {
            --bg: var(--ok-bg);
            --fg: var(--ok-fg);
            --bd: var(--ok-bd);
            --dot: #10b981;
        }

        .status-info {
            --bg: var(--info-bg);
            --fg: var(--info-fg);
            --bd: var(--info-bd);
            --dot: #3b82f6;
        }

        .status-warn {
            --bg: var(--warn-bg);
            --fg: var(--warn-fg);
            --bd: var(--warn-bd);
            --dot: #f59e0b;
        }

        .status-err {
            --bg: var(--err-bg);
            --fg: var(--err-fg);
            --bd: var(--err-bd);
            --dot: #ef4444;
        }

        .status-muted {
            --bg: var(--muted-bg);
            --fg: var(--muted-fg);
            --bd: var(--muted-bd);
            --dot: #a1a1aa;
        }

        /* badge jumlah di header */
        .counter {
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .35);
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
            padding: .2rem .55rem;
            border-radius: 999px;
        }

        .link-plain {
            text-decoration: none;
            color: inherit;
        }

        /* grid spacing */
        .row.gx-4 {
            --bs-gutter-x: 1.25rem;
        }

        .row.gy-4 {
            --bs-gutter-y: 1.25rem;
        }

        /* Animasi halaman */
        .page-enter {
            opacity: 0;
            transform: translateY(8px)
        }

        .page-enter.page-in {
            animation: pageFadeIn .55s cubic-bezier(.21, 1, .21, 1) forwards
        }

        @keyframes pageFadeIn {
            to {
                opacity: 1;
                transform: none
            }
        }

        .stagger {
            opacity: 0;
            transform: translateY(10px)
        }

        .stagger.show {
            animation: cardIn .55s cubic-bezier(.21, 1, .21, 1) forwards;
            animation-delay: var(--d, 0ms)
        }

        @keyframes cardIn {
            to {
                opacity: 1;
                transform: none
            }
        }

        @media (prefers-reduced-motion:reduce) {

            .page-enter,
            .stagger {
                opacity: 1;
                transform: none;
                animation: none
            }
        }
    </style>
    <style>
        .disabled-link {
            pointer-events: none;
        }

        .task-row.readonly {
            cursor: default;
        }

        .task-row.readonly:hover {
            box-shadow: var(--shadow);
            transform: none;
        }
    </style>
@endpush


@section('main')
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
    @if (session()->has('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: @json(session('error')),
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>
    @endif

    <div id="kt_app_content_container" class="app-container container-fluid page-enter">
        <div class="row gx-4 row-nowrap">
            {{-- ===================== IDP ===================== --}}
            <div class="col-12 col-xl-6 col-xxl-4">
                <div class="panel shadow-sm">
                    <div class="panel-head">
                        <h3 class="text-white"><i class="fas fa-user-check"></i><span class="fw-bold">IDP</span></h3>
                        <span class="counter">{{ $allIdpTasks->count() }} Items</span>
                    </div>

                    <div class="panel-body panel-scroll">
                        @php $isHRD = auth()->user()->role === 'HRD'; @endphp
                        @forelse ($allIdpTasks as $i => $item)
                            @php
                                $idpMap = [
                                    'unassigned' => [
                                        'tone' => 'err',
                                        'status' => 'status-err',
                                        'icon' => 'fa-exclamation-circle',
                                        'label' => 'To Be Assign',
                                    ],
                                    'need_check' => [
                                        'tone' => 'warn',
                                        'status' => 'status-warn',
                                        'icon' => 'fa-clipboard-check',
                                        'label' => 'Need Check',
                                    ],
                                    'draft' => [
                                        'tone' => 'warn',
                                        'status' => 'status-warn',
                                        'icon' => 'fa-pen',
                                        'label' => 'Need Submit',
                                    ],
                                    'need_approval' => [
                                        'tone' => 'info',
                                        'status' => 'status-info',
                                        'icon' => 'fa-hourglass-half',
                                        'label' => 'Need Approve',
                                    ],
                                    'revise' => [
                                        'tone' => 'err',
                                        'status' => 'status-err',
                                        'icon' => 'fa-rotate-left',
                                        'label' => 'Need Revise',
                                    ],
                                ];
                                $cfg = $idpMap[$item['type']] ?? [
                                    'tone' => 'muted',
                                    'status' => 'status-muted',
                                    'icon' => 'fa-circle-question',
                                    'label' => 'Unknown',
                                ];

                                $href = in_array($item['type'], ['need_check', 'need_approval'])
                                    ? route('idp.approval')
                                    : route('idp.index', [
                                        'company' => $item['employee_company'],
                                        'npk' => $item['employee_npk'],
                                    ]);
                            @endphp

                            @if ($isHRD)
                                <a class="link-plain disabled-link" href="{{ $href }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $item['employee_name'] }}</h5>
                                            <div class="task-sub">{{ $item['employee_company'] ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @else
                                <a class="link-plain" href="{{ $href }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $item['employee_name'] }}</h5>
                                            <div class="task-sub">{{ $item['employee_company'] ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @endif
                        @empty
                            <div class="task-row">
                                <div class="tone tone-ok"></div>
                                <h5 class="task-title mb-0 text-success">
                                    <i class="fas fa-check-circle me-2"></i> No Task.
                                </h5>
                                <span class="status-chip status-ok"><i class="fas fa-circle-check"></i>Clear</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===================== HAV ===================== --}}
            <div class="col-12 col-xl-6 col-xxl-4">
                <div class="panel shadow-sm">
                    <div class="panel-head">
                        <h3 class="text-white"><i class="fas fa-clipboard-list"></i><span class="fw-bold">HAV</span></h3>
                        <span class="counter">{{ $allHavTasks->count() }} Items</span>
                    </div>

                    <div class="panel-body panel-scroll">
                        @forelse ($allHavTasks as $i => $item)
                            @php
                                $raw = (int) $item->getRawOriginal('status');
                                $cfg =
                                    $raw === 1
                                        ? [
                                            'tone' => 'info',
                                            'status' => 'status-info',
                                            'icon' => 'fa-hourglass-half',
                                            'label' => 'To Be Assign',
                                        ]
                                        : ($raw === 0
                                            ? [
                                                'tone' => 'warn',
                                                'status' => 'status-warn',
                                                'icon' => 'fa-exclamation-circle',
                                                'label' => 'Need Approve',
                                            ]
                                            : [
                                                'tone' => 'muted',
                                                'status' => 'status-muted',
                                                'icon' => 'fa-circle-question',
                                                'label' => 'Unknown',
                                            ]);
                            @endphp

                            @if ($isHRD)
                                <a class="link-plain disabled-link" href="{{ route('hav.approval') }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $item->employee->name }}</h5>
                                            <div class="task-sub">{{ $item->employee->company_name ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @else
                                <a class="link-plain" href="{{ route('hav.approval') }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $item->employee->name }}</h5>
                                            <div class="task-sub">{{ $item->employee->company_name ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @endif
                        @empty
                            <div class="task-row">
                                <div class="tone tone-ok"></div>
                                <h5 class="task-title mb-0 text-success">
                                    <i class="fas fa-check-circle me-2"></i> No Task.
                                </h5>
                                <span class="status-chip status-ok"><i class="fas fa-circle-check"></i>Clear</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===================== ICP ===================== --}}
            <div class="col-12 col-xl-6 col-xxl-4">
                <div class="panel shadow-sm">
                    <div class="panel-head">
                        <h3 class="text-white">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="fw-bold">ICP</span>
                        </h3>
                        <span class="counter">{{ $allIcpTasks->count() }} Items</span>
                    </div>

                    <div class="panel-body panel-scroll">
                        @forelse ($allIcpTasks as $i => $item)
                            @php
                                // sekarang status diambil dari ICP (relasi di IcpApprovalStep)
                                $raw = (int) $item->icp->getRawOriginal('status');

                                $cfg =
                                    $raw === 1
                                        ? [
                                            'tone' => 'warn',
                                            'status' => 'status-warn',
                                            'icon' => 'fa-clipboard-check',
                                            'label' => 'Need Check',
                                        ]
                                        : ($raw === 2
                                            ? [
                                                'tone' => 'warn',
                                                'status' => 'status-warn',
                                                'icon' => 'fa-exclamation-circle',
                                                'label' => 'Need Approve',
                                            ]
                                            : [
                                                'tone' => 'muted',
                                                'status' => 'status-muted',
                                                'icon' => 'fa-circle-question',
                                                'label' => 'Unknown',
                                            ]);
                            @endphp

                            @php
                                $employee = $item->icp->employee;
                            @endphp

                            @if ($isHRD)
                                <a class="link-plain disabled-link" href="{{ route('icp.approval') }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $employee->name }}</h5>
                                            <div class="task-sub">{{ $employee->company_name ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @else
                                <a class="link-plain" href="{{ route('icp.approval') }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $employee->name }}</h5>
                                            <div class="task-sub">{{ $employee->company_name ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @endif
                        @empty
                            <div class="task-row">
                                <div class="tone tone-ok"></div>
                                <h5 class="task-title mb-0 text-success">
                                    <i class="fas fa-check-circle me-2"></i> No Task.
                                </h5>
                                <span class="status-chip status-ok">
                                    <i class="fas fa-circle-check"></i>Clear
                                </span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>


            {{-- ===================== RTC ===================== --}}
            <div class="col-12 col-xl-6 col-xxl-4">
                <div class="panel shadow-sm">
                    <div class="panel-head">
                        <h3 class="text-white"><i class="fas fa-business-time"></i><span class="fw-bold">RTC</span></h3>
                        <span class="counter">{{ $allRtcTasks->count() }} Items</span>
                    </div>

                    <div class="panel-body panel-scroll">
                        @forelse ($allRtcTasks as $i => $item)
                            @php
                                $raw = (int) $item->getRawOriginal('status');
                                // 1 → Need Approve, 0 → Need Check (sesuai kode lama)
                                $cfg =
                                    $raw === 1
                                        ? [
                                            'tone' => 'info',
                                            'status' => 'status-info',
                                            'icon' => 'fa-hourglass-half',
                                            'label' => 'Need Approve',
                                        ]
                                        : ($raw === 0
                                            ? [
                                                'tone' => 'warn',
                                                'status' => 'status-warn',
                                                'icon' => 'fa-clipboard-check',
                                                'label' => 'Need Check',
                                            ]
                                            : [
                                                'tone' => 'muted',
                                                'status' => 'status-muted',
                                                'icon' => 'fa-circle-question',
                                                'label' => 'Unknown',
                                            ]);
                            @endphp

                            @if ($isHRD)
                                <a class="link-plain disabled-link" href="{{ route('rtc.approval') }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $item->employee->name }}</h5>
                                            <div class="task-sub">{{ $item->employee->company_name ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @else
                                <a class="link-plain" href="{{ route('rtc.approval') }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $item->employee->name }}</h5>
                                            <div class="task-sub">{{ $item->employee->company_name ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @endif
                        @empty
                            <div class="task-row">
                                <div class="tone tone-ok"></div>
                                <h5 class="task-title mb-0 text-success">
                                    <i class="fas fa-check-circle me-2"></i> No Task.
                                </h5>
                                <span class="status-chip status-ok"><i class="fas fa-circle-check"></i>Clear</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===================== IPP (My IPP) ===================== --}}
            @php
                $role = auth()->user()->role;
                $isHRD = $role === 'HRD';
                $ippTasks = $allIppTasks['ippTasks'] ?? null;
                $message = $allIppTasks['message'] ?? null;
                $subordinateIpps = $allIppTasks['subordinateIpps'] ?? [];
            @endphp

            @if ($ippTasks)
                <div class="col-12 col-xl-6 col-xxl-4">
                    <div class="panel shadow-sm">
                        <div class="panel-head">
                            <h3 class="text-white">
                                <i class="fas fa-user-check"></i>
                                <span class="fw-bold">My IPP</span>
                            </h3>

                            {{-- kecil-kecilan: tampilkan total poin --}}
                            <span class="counter">
                                {{ $ippTasks['total'] ?? 0 }} pts
                            </span>
                        </div>

                        <div class="panel-body panel-scroll">
                            @php
                                $statusMap = [
                                    'Not Created' => [
                                        'tone' => 'err',
                                        'status' => 'status-err',
                                        'icon' => 'fa-exclamation-circle',
                                        'label' => 'To Be Assign',
                                    ],
                                    'submitted' => [
                                        'tone' => 'warn',
                                        'status' => 'status-warn',
                                        'icon' => 'fa-clipboard-check',
                                        'label' => 'Need Check',
                                    ],
                                    'draft' => [
                                        'tone' => 'warn',
                                        'status' => 'status-warn',
                                        'icon' => 'fa-pen',
                                        'label' => 'Need Submit',
                                    ],
                                    'checked' => [
                                        'tone' => 'info',
                                        'status' => 'status-info',
                                        'icon' => 'fa-hourglass-half',
                                        'label' => 'Need Approve',
                                    ],
                                    'revise' => [
                                        'tone' => 'err',
                                        'status' => 'status-err',
                                        'icon' => 'fa-rotate-left',
                                        'label' => 'Need Revise',
                                    ],
                                ];

                                $cfg = $statusMap[$ippTasks['status']] ?? [
                                    'tone' => 'muted',
                                    'status' => 'status-muted',
                                    'icon' => 'fa-circle-question',
                                    'label' => 'Unknown',
                                ];

                                // kalau status sudah masuk flow approval → ke halaman approval
                                $href = in_array($ippTasks['status'], ['checked', 'submitted'])
                                    ? route('ipp.approval')
                                    : route('ipp.index', [
                                        'company' => $ippTasks['employee_company'],
                                        'npk' => $ippTasks['employee_npk'],
                                    ]);

                                $ownerName = $ippTasks['employee_name'] ?: optional(auth()->user()->employee)->name;
                                $ownerCompany =
                                    $ippTasks['employee_company'] ?: optional(auth()->user()->employee)->company_name;
                            @endphp

                            @if ($message)
                                <div class="alert alert-warning py-1 px-2 mb-2">
                                    {{ $message }}
                                </div>
                            @endif

                            {{-- HRD biasanya tidak edit IPP, kalau mau boleh di-disable --}}
                            @php $linkClass = $isHRD ? 'link-plain disabled-link' : 'link-plain'; @endphp

                            <a class="{{ $linkClass }}" href="{{ $href }}">
                                <div class="task-row hover-shadow stagger">
                                    <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                    <div>
                                        <h5 class="task-title mb-1">
                                            {{ $ownerName }}
                                        </h5>
                                        <div class="task-sub">
                                            {{ $ownerCompany ?? '-' }}
                                        </div>
                                        {{-- breakdown poin kecil di bawah nama --}}
                                        <div class="task-sub mt-1">
                                            AM: {{ $ippTasks['activity_management'] ?? 0 }} •
                                            CRP: {{ $ippTasks['crp'] ?? 0 }} •
                                            PD: {{ $ippTasks['people_development'] ?? 0 }} •
                                            SA: {{ $ippTasks['special_assignment'] ?? 0 }}
                                        </div>
                                    </div>

                                    <span class="status-chip {{ $cfg['status'] }}">
                                        <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ===================== IPP Subordinates ===================== --}}
            @if (!empty($subordinateIpps))
                <div class="col-12 col-xl-6 col-xxl-4">
                    <div class="panel shadow-sm">
                        <div class="panel-head">
                            <h3 class="text-white">
                                <i class="fas fa-users-gear"></i>
                                <span class="fw-bold">IPP Subordinates</span>
                            </h3>
                            <span class="counter">{{ count($subordinateIpps) }} Items</span>
                        </div>

                        <div class="panel-body panel-scroll">
                            @foreach ($subordinateIpps as $i => $task)
                                @php
                                    // stage: 'check' atau 'approve'
                                    $cfg = match ($task['stage']) {
                                        'check' => [
                                            'tone' => 'warn',
                                            'status' => 'status-warn',
                                            'icon' => 'fa-clipboard-check',
                                            'label' => 'Need Check',
                                        ],
                                        'approve' => [
                                            'tone' => 'info',
                                            'status' => 'status-info',
                                            'icon' => 'fa-hourglass-half',
                                            'label' => 'Need Approve',
                                        ],
                                        default => [
                                            'tone' => 'muted',
                                            'status' => 'status-muted',
                                            'icon' => 'fa-circle-question',
                                            'label' => 'Unknown',
                                        ],
                                    };

                                    // satu route approval untuk semua
                                    $href = route('ipp.approval');

                                    $employee = $task['employee'] ?? [];
                                @endphp

                                {{-- HRD boleh lihat tapi tidak klik → pakai class disabled-link --}}
                                @php $linkClass = $isHRD ? 'link-plain disabled-link' : 'link-plain'; @endphp

                                <a class="{{ $linkClass }}" href="{{ $href }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">
                                                {{ $employee['name'] ?? '-' }}
                                            </h5>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif


            {{-- ================= Assessment (HRD only) ================= --}}
            @if (auth()->user()->role === 'HRD')
                <div class="col-12 col-xl-6 col-xxl-4">
                    <div class="panel shadow-sm">
                        <div class="panel-head">
                            <h3 class="text-white"><i class="fas fa-chart-line"></i><span
                                    class="fw-bold">Assessment</span>
                            </h3>
                            <span class="counter">{{ $allHavTasks->count() }} Items</span>
                        </div>

                        <div class="panel-body panel-scroll">
                            @forelse ($allHavTasks as $i => $item)
                                @php
                                    $raw = (int) $item->getRawOriginal('status');
                                    $cfg =
                                        $raw === 1
                                            ? [
                                                'tone' => 'err',
                                                'status' => 'status-err',
                                                'icon' => 'fa-exclamation-circle',
                                                'label' => 'To Be Assign',
                                            ]
                                            : ($raw === 0
                                                ? [
                                                    'tone' => 'warn',
                                                    'status' => 'status-warn',
                                                    'icon' => 'fa-exclamation-circle',
                                                    'label' => 'Need Approve',
                                                ]
                                                : [
                                                    'tone' => 'muted',
                                                    'status' => 'status-muted',
                                                    'icon' => 'fa-circle-question',
                                                    'label' => 'Unknown',
                                                ]);
                                    $href = in_array($cfg['label'], ['To Be Assign', 'Need Approve'])
                                        ? route('idp.approval')
                                        : route('idp.index', [
                                            'company' => $item->employee->company_name,
                                            'npk' => $item->employee->npk,
                                        ]);
                                @endphp

                                <a class="link-plain" href="{{ $href }}">
                                    <div class="task-row hover-shadow stagger" style="--d: {{ $i * 60 }}ms">
                                        <div class="tone tone-{{ $cfg['tone'] }}"></div>

                                        <div>
                                            <h5 class="task-title mb-1">{{ $item->employee->name }}</h5>
                                            <div class="task-sub">{{ $item->employee->company_name ?? '-' }}</div>
                                        </div>

                                        <span class="status-chip {{ $cfg['status'] }}">
                                            <i class="fas {{ $cfg['icon'] }}"></i>{{ $cfg['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="task-row">
                                    <div class="tone tone-ok"></div>
                                    <h5 class="task-title mb-0 text-success">
                                        <i class="fas fa-check-circle me-2"></i> No Task.
                                    </h5>
                                    <span class="status-chip status-ok"><i class="fas fa-circle-check"></i>Clear</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('kt_app_content_container');
            if (container) container.classList.add('page-in');

            // animasi panel (stagger)
            const allRows = document.querySelectorAll('.task-row.stagger');
            allRows.forEach((el, idx) => {
                el.style.setProperty('--d', (idx * 40) + 'ms');
                requestAnimationFrame(() => el.classList.add('show'));
            });
        });
    </script>
@endpush
