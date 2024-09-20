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
            <th style="font-family: 'Arial Rounded MT Bold';text-transform: uppercase;color: {{ $tp->transaction_type == 'out' ? '#000000' : '#f44336'}};">{{ Str::upper($tp->transaction_code) }}</th>
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
