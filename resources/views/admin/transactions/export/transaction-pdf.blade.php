@php
$cell_styles = 'border: 1px solid #111111; font-family: sans-serif; vertical-align: middle;';
@endphp

<table>
    <thead>
    <tr>
        <th colspan="2" style="border: 1px solid #FFFFFF; border-bottom: 1px solid #111111; vertical-align: top; font-family: sans-serif; height: 40px;">
            {{strtoupper($transaction->code)}}
        </th>
        <th colspan="2" style="border: 1px solid #FFFFFF; border-bottom: 1px solid #111111; vertical-align: top; font-family: sans-serif; height: 40px; text-align: right;">
            {{\Carbon\Carbon::parse($transaction->date)->format('d/m/Y')}}
        </th>
    </tr>
    <tr>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 120px; text-align: center;">
            QTY
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 260px;">
            NAMA BARANG
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 180px;">
            KETERANGAN
        </th>
        <th style="{{ $cell_styles }}; color: #111111; font-weight: bold; background-color: #d5d5d5; width: 120px;  text-align: center;">
            SISA
        </th>
    </tr>
    </thead>
    <tbody>
        @foreach($transaction->transaction_products as $tp)
        @php
        $row_styles = $transaction->type === 'out' ? 'color: #111111;' : 'color: #FF0000;';
        @endphp;
        <tr>
            <td style="{{ $cell_styles . $row_styles }} text-align: center;">
                {{$tp->quantity}}
            </td>
            <td style="{{ $cell_styles . $row_styles }}">
                {{strtoupper($tp->product->name)}}
            </td>
            <td style="{{ $cell_styles . $row_styles }}">
                {{strtoupper($tp->note)}}
            </td>
            <td style="{{ $cell_styles . $row_styles }} text-align: center;">
                {{$tp->to_stock}} {{strtoupper($tp->product->unit)}}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
