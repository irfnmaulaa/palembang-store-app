<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
            padding: 0;
        }

        html {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;

            font-size: 10pt;
        }

        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 0.2rem;
            vertical-align: top;
            border-top: 1px solid #000000;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000;
        }

        .table tbody+tbody {
            border-top: 2px solid #000000;
        }

        .table .table {
            background-color: #fff;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.3rem;
        }

        .table-bordered {
            border: 1px solid #000000;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000000;
        }

        .table-bordered thead th,
        .table-bordered thead td {
            border-bottom-width: 2px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-active,
        .table-active>th,
        .table-active>td {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-hover .table-active:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-hover .table-active:hover>td,
        .table-hover .table-active:hover>th {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-success,
        .table-success>th,
        .table-success>td {
            background-color: #dff0d8;
        }

        .table-hover .table-success:hover {
            background-color: #d0e9c6;
        }

        .table-hover .table-success:hover>td,
        .table-hover .table-success:hover>th {
            background-color: #d0e9c6;
        }

        .table-info,
        .table-info>th,
        .table-info>td {
            background-color: #d9edf7;
        }

        .table-hover .table-info:hover {
            background-color: #c4e3f3;
        }

        .table-hover .table-info:hover>td,
        .table-hover .table-info:hover>th {
            background-color: #c4e3f3;
        }

        .table-warning,
        .table-warning>th,
        .table-warning>td {
            background-color: #fcf8e3;
        }

        .table-hover .table-warning:hover {
            background-color: #faf2cc;
        }

        .table-hover .table-warning:hover>td,
        .table-hover .table-warning:hover>th {
            background-color: #faf2cc;
        }

        .table-danger,
        .table-danger>th,
        .table-danger>td {
            background-color: #f2dede;
        }

        .table-hover .table-danger:hover {
            background-color: #ebcccc;
        }

        .table-hover .table-danger:hover>td,
        .table-hover .table-danger:hover>th {
            background-color: #ebcccc;
        }

        .thead-inverse th {
            color: #fff;
            background-color: #292b2c;
        }

        .thead-default th {
            color: #464a4c;
            background-color: #000000;
        }

        .table-inverse {
            color: #fff;
            background-color: #292b2c;
        }

        .table-inverse th,
        .table-inverse td,
        .table-inverse thead th {
            border-color: #fff;
        }

        .table-inverse.table-bordered {
            border: 0;
        }

        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }

        .table-responsive.table-bordered {
            border: 0;
        }

        footer {
            position: fixed;
            bottom: 10px;
        }
        main {
            padding-bottom: 40px;
        }
        body::after {
            content: '';
            display: block;
            height: 70px; /* Adjust as needed */
        }
    </style>
    <title>Transaksi</title>
</head>

<body>

<main>
    <table class="table">
        <thead>
        <tr>
            <td style="width: 35%">
                <h3 style="font-weight: 400;margin: 0;">{{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}/{{$transaction->code}}</h3>
            </td>
            <td style="width: 30%;text-align: center;">
                <h3 style="margin: 0;">DO {{ $transaction->type == 'in' ? 'MASUK' : 'KELUAR' }}</h3>
            </td>
            <td style="text-align: right;width: 35%;">
                <h3 style="font-weight: 400;margin: 0;">{{ $transaction->creator->name }}</h3>
            </td>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
    <table class="table">
        <thead>
        <tr>
            <td>QTY</td>
            <td>NAMA BARANG</td>
            <td>KETERANGAN</td>
            @if ($transaction->type == 'in')
                <td>KODE</td>
            @endif
            <td>SISA</td>
        </tr>
        </thead>
        <tbody>
        @foreach ($transaction->transaction_products as $tp)
            @php
                $row_style  = 'text-transform: uppercase;';
                $row_style .= 'color:' . ($transaction->type === 'in' ? '#f44336' : '#000000') . ';';
            @endphp
            <tr>
                <td style="{{ $row_style }}">{{ $tp->quantity . ' ' .  $tp->product->unit }}</td>
                <td style="{{ $row_style }}">{{ $tp->product->name . ' ' . $tp->product->unit }}</td>
                <td style="{{ $row_style }}">{{ $tp->note }}</td>
                @if ($transaction->type == 'in')
                    <td style="{{ $row_style }}">{{ $tp->product->code }}</td>
                @endif
                <td style="{{ $row_style }}">{{ $tp->to_stock }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</main>

<table class="bottom-center" style="position:absolute;left: 50%;bottom: 30px;transform: translateX(-50%);">
    <tr>
        <td style="border: 2px solid #000000; width: 15px; height: 15px; background: #FF00FF;"></td>
        <td style="border: 2px solid #000000; width: 15px; height: 15px; background: #FFFF00;"></td>
        <td style="border: 2px solid #000000; width: 15px; height: 15px; background: #00FFFF;"></td>
    </tr>
</table>

</body>

</html>
