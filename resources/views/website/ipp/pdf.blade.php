@extends('layouts.root.pdf')

@section('title', 'IPP ' . $ipp->on_year . ' - ' . $owner->name)

@section('content')
    <style>
        body {
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        .no-border th,
        .no-border td {
            border: none;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .bg-lightgreen {
            background-color: #92CDDC;
        }

        .border-none td,
        .border-none th {
            border: none;
        }

        .signature-cell {
            height: 60px;
            text-align: center;
        }
    </style>

    <table class="no-border" style="margin-bottom: 5px;">
        <tr>
            <td rowspan="3" style="width:20%">
                <img src="{{ $logo }}" height="40">
            </td>
            <!-- Judul di tengah, span 2 kolom kanan -->
            <td class="text-center fw-bold" style="font-size:14px" colspan="1">
                INDIVIDUAL PERFORMANCE PLAN
            </td>
        </tr>
        <tr>
            <!-- No FORM tepat di bawah judul -->
            <td class="text-center" style="font-size:9px">
                <strong>No FORM:</strong> FRM-HRD-S3-012-00
            </td>
            <!-- Kolom kanan dikosongkan di baris ini -->
            <td style="width:25%"></td>
        </tr>
        <tr>
            <!-- ON YEAR di bawah No FORM -->
            <td class="text-center fw-bold">
                ON YEAR : {{ $ipp->on_year }}
            </td>
            <!-- Info tanggal & PIC di sisi kanan -->
            <td style="font-size: 9px">
                <div><strong>Date of review</strong>: {{ $ipp->review_date ?? '-' }}</div>
                <div><strong>PIC Review</strong>: {{ $ipp->pic_review ?? '-' }}</div>
            </td>
        </tr>
    </table>

    <table style="margin-bottom: 8px;">
        <tr>
            <td class="fw-bold" style="width:15%">NAME</td>
            <td style="width:35%">{{ $owner->name }}</td>
            <td class="fw-bold" style="width:15%">Date</td>
            <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">DEPARTMENT</td>
            <td>{{ $owner->bagian ?? '-' }}</td>
            <td class="fw-bold">PIC Review</td>
            <td>{{ $ipp->pic_review ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">SECTION / SUB</td>
            <td>{{ $owner->sub_bagian ?? '-' }}</td>
            <td class="fw-bold">DIVISION</td>
            <td>{{ $owner->divisi ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr class="text-center">
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
                $labels = [
                    'activity_management' => 'I. ACTIVITY MANAGEMENT',
                    'people_management' => 'II. PEOPLE MANAGEMENT',
                    'crp' => 'III. CRP',
                    'special_assignment' => 'IV. SPECIAL ASSIGNMENT',
                ];
                $no = 1;
            @endphp

            @foreach ($grouped as $cat => $items)
                @if (count($items))
                    <tr class="bg-lightgreen fw-bold">
                        <td colspan="6">{{ $labels[$cat] ?? strtoupper($cat) }} — {{ $summary[$cat] ?? '0%' }}</td>
                    </tr>

                    @foreach ($items as $row)
                        <tr>
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

            <tr class="fw-bold text-center">
                <td colspan="2">TOTAL</td>
                <td>{{ $summary['total'] ?? '100' }}%</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <br>

    <table class="text-center">
        <thead>
            <tr>
                <th>Superior of Superior</th>
                <th>Superior</th>
                <th>Employee</th>
            </tr>
        </thead>
        <tbody>
            <tr class="signature-cell">
                <td style="height: 80px"></td>
                <td style="height: 80px"></td>
                <td style="height: 80px"></td>
            </tr>
            <tr>
                <td>Date :</td>
                <td>Date :</td>
                <td>Date :</td>
            </tr>
        </tbody>
    </table>
@endsection
