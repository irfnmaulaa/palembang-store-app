@php
$cell_styles = 'border: 1px solid #111111; font-family: sans-serif; vertical-align: middle; padding: 5px;';
@endphp

<table>
    <thead>
    <tr>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 100px;">
            TANGGAL
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 90px;">
            NO. DO
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 80px; text-align: center;">
            QTY
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 220px;">
            NAMA BARANG
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 140px;">
            KODE BARANG
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 150px;">
            KETERANGAN
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 100px;  text-align: center;">
            SISA
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 80px; text-align: center;">
            ID
        </th>
    </tr>
    </thead>
    <tbody>
        @foreach($transaction_products as $tp)
        @php
        $row_styles = $tp->transaction_type === 'out' ? 'color: #111111;' : 'color: #FF0000;';
        @endphp;
        <tr>
            <td style="{{ $cell_styles . $row_styles }}">
                {{\Carbon\Carbon::parse($tp->transaction_date)->format('d/m/Y')}}
            </td>
            <td style="{{ $cell_styles . $row_styles }}">
                {{strtoupper($tp->transaction_code)}}
            </td>
            <td style="{{ $cell_styles . $row_styles }} text-align: center;">
                {{$tp->quantity}}
            </td>
            <td style="{{ $cell_styles . $row_styles }}">
                {{strtoupper($tp->product_name)}}
            </td>
            <td style="{{ $cell_styles . $row_styles }}">
                {{strtoupper($tp->product_code)}}
            </td>
            <td style="{{ $cell_styles . $row_styles }}">
                {{strtoupper($tp->note)}}
            </td>
            <td style="{{ $cell_styles . $row_styles }} text-align: center;">
                {{$tp->to_stock}} {{strtoupper($tp->product_unit)}}
            </td>
            <td style="{{ $cell_styles . $row_styles }} text-align: center;">
                {{strtoupper($tp->creator_name)}}
            </td>
        </tr>
        @endforeach
        @if(count($transaction_products) <= 0)
        <tr>
            <td colspan="8" style="{{ $cell_styles }} text-align: center;">TIDAK ADA DATA</td>
        </tr>
        @endif
    </tbody>
</table>
