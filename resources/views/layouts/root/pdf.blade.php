<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Document')</title>
    <style>
        /* Dompdf-friendly: HANYA DejaVu Sans */
        html,
        body,
        * {
            font-family: "DejaVu Sans" !important;
        }

        body {
            font-size: 12px;
            line-height: 1.35;
            color: #111;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 6px 8px;
            vertical-align: top;
        }

        .no-border th,
        .no-border td {
            border: 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .mb-1 {
            margin-bottom: .25rem;
        }

        .mb-2 {
            margin-bottom: .5rem;
        }

        .mb-3 {
            margin-bottom: .75rem;
        }

        /* tambahkan utilitas yang dibutuhkan untuk layout PDF sederhanamu */
    </style>
</head>

<body>
    @yield('content')
</body>

</html>
