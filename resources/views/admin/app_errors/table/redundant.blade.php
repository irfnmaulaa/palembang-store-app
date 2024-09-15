<table class="table table-striped table-sm mb-0">
    <thead>
    <tr>
        <th>Tanggal</th>
        <th>No DO</th>
        <th class="text-center">Quantity</th>
        <th>Nama Barang</th>
        <th>Kode Barang</th>
        <th>Keterangan</th>
        <th class="text-center">Sisa</th>
        <th class="text-center">ID</th>
        <th class="text-center">Jumlah Redudansi</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $item)
    @php
    $className = get_table_row_classname($item->transaction->type)
    @endphp
    <tr>
        <td class="{{ $className }}">
            {{\Carbon\Carbon::parse($item->transaction->date)->format('d/m/Y')}}
        </td>
        <td>
            <a href="{{ route('admin.transactions.show', $item->transaction) }}" class="{{ $className }}">
                {{$item->transaction->code}}
            </a>
        </td>
        <td class="{{ $className }} text-center">
            {{$item->quantity}}
        </td>
        <td>
            <a href="{{ route('admin.products.show', $item->product) }}" class="{{ $className }}">
                {{$item->product->name}} {{$item->product->variant}}
            </a>
        </td>
        <td class="{{ $className }}">
            {{$item->product->code}}
        </td>
        <td class="{{ $className }}">
            {{$item->note}}
        </td>
        <td class="{{ $className }} text-center">
            {{$item->to_stock}}
        </td>
        <td class="{{ $className }} text-center">
            {{$item->creator->name}}
        </td>
        <td class="{{ $className }} text-center">
            {{$item->duplicate_count}}
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
