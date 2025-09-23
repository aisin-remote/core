@extends('layouts.root.pdf')

@section('title', 'IPP ' . $ipp->on_year . ' - ' . $owner->name)

@section('content')
    <style>
        /* Base */
        body {
            font-size: 10px
        }

        /* ===== HEADER (no borders) ===== */
        .head-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 2px solid #000;
            border-right: 2px solid #000;
            border-left: 2px solid #000;
        }

        .head-table td {
            padding: 2px 4px;
            vertical-align: middle;
            border: none
        }

        .title {
            font-weight: 700;
            font-size: 14px;
            text-align: center
        }

        .confidential {
            font-weight: 700;
            color: #c00;
            text-align: right;
            font-style: italic
        }

        /* ===== IDENT (NAME–DIVISION) : only outer border ===== */
        .ident-grid {
            width: 100%;
            border-collapse: separate;
            /* biar outer box rapi di dompdf */
            border-top: 2px solid #000;
            border-right: 2px solid #000;
            border-left: 2px solid #000;
        }

        .ident-grid td {
            border: none;
            padding: 2px 6px;
            vertical-align: top
        }

        .ident-label,
        .ident-label-right {
            width: 18%;
            font-weight: 700
        }

        .ident-mid,
        .ident-right {
            width: 32%
        }

        /* ===== MAIN GRID ===== */
        .table-grid {
            width: 100%;
            border-collapse: collapse
        }

        .table-grid th,
        .table-grid td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top
        }

        /* Perimeter tebal */
        .table-grid thead tr:first-child th {
            border-top-width: 2px
        }

        .table-grid tbody tr:last-child td {
            border-bottom-width: 2px
        }

        .table-grid th:first-child,
        .table-grid td:first-child {
            border-left-width: 2px
        }

        .table-grid th:last-child,
        .table-grid td:last-child {
            border-right-width: 2px
        }

        /* Header row */
        .grid-head th {
            background: #cfe6ef;
            font-weight: 700;
            text-align: center
        }

        /* Category bar */
        .cat-row td {
            background: #dfead8;
            font-weight: 700;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-top: none;
            border-bottom: none;
        }

        .cat-row td:first-child {
            border-left-width: 2px
        }

        .cat-row td:last-child {
            border-right-width: 2px
        }

        /* Data rows: kiri/kanan saja */
        .rowdata td {
            border-top: none !important;
            border-bottom: none !important;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
        }

        .rowdata td:first-child {
            border-left-width: 2px
        }

        .rowdata td:last-child {
            border-right-width: 2px
        }

        /* Total row */
        .total-row td {
            border-top: 1px solid #000;
            /* <-- koreksi dari 'none 1px' */
            border-bottom: 1px solid #000;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            font-weight: 700;
            text-align: center;
        }

        .total-row td:first-child {
            border-left-width: 2px
        }

        .total-row td:last-child {
            border-right-width: 2px
        }

        .total-row td:last-child {
            border-bottom-width: 2px
        }

        .total-row td:last-child {
            border-top-width: 2px
        }

        /* ===== SIGNATURE BOX ===== */
        .sig-box {
            width: 85%;
            margin: 8px auto 0;
            border-collapse: collapse;
        }

        .sig-box th,
        .sig-box td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top
        }

        .sig-box thead tr:first-child th {
            border-top-width: 2px
        }

        .sig-box th:first-child,
        .sig-box td:first-child {
            border-left-width: 2px
        }

        .sig-box th:last-child,
        .sig-box td:last-child {
            border-right-width: 2px
        }

        .sig-box tbody tr:last-child td {
            border-bottom-width: 2px
        }

        .sig-box thead th {
            background: #cfe6ef;
            text-align: center;
            font-weight: 700
        }

        .sig-space td {
            height: 70px;
            width: 50px;
            border-top: none !important;
            border-bottom: none !important;
        }

        .sig-date td {
            border-top: 1px solid #000;
        }
    </style>

    {{-- ======= HEADER ======= --}}
    <table class="head-table">
        <tr>
            <td rowspan="3" style="width:20%"><img src="{{ $logo }}" height="40"></td>
            <td class="title" colspan="1">INDIVIDUAL PERFORMANCE PLAN</td>
            <td class="confidential">PERSONAL &amp; CONFIDENTIAL</td>
        </tr>
        <tr class="fw-bold">
            <td class="text-center" style="font-size:10px">
                <strong style="font-size:10px">NO FORM:</strong> FRM-HRD-S3-012-00
            </td>
            <td style="width:25%"></td>
        </tr>
        <tr>
            <td class="text-center" style="font-weight:700; padding-bottom: 15px;">ON YEAR : {{ $ipp->on_year }}</td>
            <td></td>
        </tr>
    </table>

    {{-- ======= NAME – DIVISION (outer border only) ======= --}}
    <table class="ident-grid">
        <tr>
            <td class="ident-label">NAME</td>
            <td>:</td>
            <td class="ident-mid">{{ $identitas['nama'] }}</td>
            <td class="ident-label-right">Date</td>
            <td>:</td>
            <td class="ident-right">{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td class="ident-label">DEPARTMENT</td>
            <td>:</td>
            <td class="ident-mid">{{ $identitas['department'] ?? '-' }}</td>
            <td class="ident-label-right">PIC Review</td>
            <td>:</td>
            <td class="ident-right">{{ $ipp->pic_review ?? '-' }}</td>
        </tr>
        <tr>
            <td class="ident-label">SECTION / SUB</td>
            <td>:</td>
            <td class="ident-mid">{{ $identitas['section'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="ident-label-right">DIVISION</td>
            <td>:</td>
            <td class="ident-right">{{ $identitas['division'] ?? '-' }}</td>
        </tr>
    </table>

    {{-- ======= MAIN GRID ======= --}}
    <table class="table-grid">
        <thead class="grid-head">
            <tr>
                <th style="width:4%">#</th>
                <th style="width:36%">PROGRAM / ACTIVITY</th>
                <th style="width:8%">WEIGHT (%)</th>
                <th style="width:24%">MID YEAR (APR - SEPT)</th>
                <th style="width:24%">ONE YEAR (APR - MAR)</th>
                <th style="width:10%">DUE DATE</th>
            </tr>
        </thead>
        <tbody>
            @php
                $numbers = [
                    'activity_management' => 'I',
                    'people_development' => 'II',
                    'crp' => 'III',
                    'special_assignment' => 'IV',
                ];
                $labels = [
                    'activity_management' => 'ACTIVITY MANAGEMENT',
                    'people_development' => 'PEOPLE MANAGEMENT',
                    'crp' => 'CRP',
                    'special_assignment' => 'SPECIAL ASSIGNMENT',
                ];
                $no = 1;
            @endphp

            @foreach ($grouped as $cat => $items)
                @if (count($items))
                    {{-- Baris kategori: kolom pertama berisi angka romawi + titik, kolom kedua judul --}}
                    <tr class="cat-row">
                        <td class="text-center">
                            {{ ($numbers[$cat] ?? '') !== '' ? $numbers[$cat] . '.' : '' }}
                        </td>
                        <td>{{ $labels[$cat] ?? strtoupper($cat) }} — {{ $summary[$cat] ?? '0' }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    @foreach ($items as $row)
                        <tr class="rowdata">
                            <td class="text-center">{{ $no++ }}</td>
                            <td>{{ $row['activity'] }}</td>
                            <td class="text-center">{{ $row['weight'] }}%</td>
                            <td>{{ $row['target_mid'] }}</td>
                            <td>{{ $row['target_one'] }}</td>
                            <td class="text-center">{{ $row['due_date'] }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach

            <tr class="total-row">
                <td colspan="2">TOTAL</td>
                <td>{{ $summary['total'] ?? '100' }}%</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    {{-- ===== Signatures ===== --}}
    <table class="sig-box">
        <thead>
            <tr>
                <th>Superior of Superior</th>
                <th>Superior</th>
                <th>Employee</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sig-space">
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr class="sig-date">
                <td>Date :</td>
                <td>Date :</td>
                <td>Date :</td>
            </tr>
        </tbody>
    </table>
@endsection
