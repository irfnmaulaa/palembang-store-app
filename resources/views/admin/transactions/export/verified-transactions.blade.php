<style>
    @page {
        margin: 0;
        padding: 0;
    }

    html {
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, sans-serif;
    }

    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 1rem;
    }

    .table th,
    .table td {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        vertical-align: top;
        border-top: 1px solid #eceeef;
    }

    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #eceeef;
    }

    .table tbody+tbody {
        border-top: 2px solid #eceeef;
    }

    .table .table {
        background-color: #fff;
    }

    .table-sm th,
    .table-sm td {
        padding: 0.3rem;
    }

    .table-bordered {
        border: 1px solid #eceeef;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #eceeef;
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
        background-color: #eceeef;
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
</style>

<table style="margin-left: auto; margin-bottom: 1rem;">
    <tr>
        <td style="border: 2px solid #000000; width: 15px; height: 15px; background: #FF00FF;"></td>
        <td style="border: 2px solid #000000; width: 15px; height: 15px; background: #FFFF00;"></td>
        <td style="border: 2px solid #000000; width: 15px; height: 15px; background: #00FFFF;"></td>
    </tr>
</table>

<table
    id="table"
    class="table-hover"
>
    <thead>
    <tr>
        <th style="font-weight: 700;text-align: center;vertical-align: middle;font-size: 12pt;font-family: 'Arial Black';text-transform: uppercase;">TANGGAL</th>
        <th style="font-weight: 700;text-align: center;vertical-align: middle;font-size: 12pt;font-family: 'Arial Black';text-transform: uppercase;">NO DO</th>
        <th style="font-weight: 700;text-align: center;vertical-align: middle;font-size: 12pt;font-family: 'Arial Black';text-transform: uppercase;">QTY</th>
        <th style="font-weight: 700;text-align: center;vertical-align: middle;font-size: 12pt;font-family: 'Arial Black';text-transform: uppercase;">NAMA BARANG</th>
        <th style="font-weight: 700;text-align: center;vertical-align: middle;font-size: 12pt;font-family: 'Arial Black';text-transform: uppercase;">KETERANGAN</th>
        <th style="font-weight: 700;text-align: center;vertical-align: middle;font-size: 12pt;font-family: 'Arial Black';text-transform: uppercase;">KODE</th>
        <th style="font-weight: 700;text-align: center;vertical-align: middle;font-size: 12pt;font-family: 'Arial Black';text-transform: uppercase;">SISA</th>
        <th style="font-weight: 700;text-align: center;vertical-align: middle;font-size: 12pt;font-family: 'Arial Black';text-transform: uppercase;">ID</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($transaction_products as $tp)
        <tr>
            <th style="font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">
                {{ \Carbon\Carbon::parse($tp->transaction_date)->format('d/m/Y') }}
            </th>
            <th style="font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">
                {{ Str::upper($tp->transaction_code) }}
            </th>
            <th style="text-align: right;font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">
                {{ Str::upper("{$tp->quantity} {$tp->product_unit}") }}
            </th>
            <th style="font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">
                {{ Str::upper("{$tp->product_name} {$tp->product_variant}") }}
            </th>
            <th style="font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">
                {{ Str::upper($tp->note) }}
            </th>
            <th style="font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">
                {{ Str::upper($tp->product_code) }}
            </th>
            <th style="font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">
                {{ Str::upper($tp->to_stock) }}
            </th>
            <th style="text-align: center;font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">{{ $tp->creator_name }}</th>
        </tr>
    @endforeach
    </tbody>
</table>
