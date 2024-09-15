<table class="table table-bordered mb-0 table-sm">
    <thead>
    <tr>
        <th class="bg-body-tertiary" rowspan="2">Tanggal</th>
        <th class="bg-body-tertiary" rowspan="2">No DO</th>
        <th colspan="8" class="bg-body-tertiary text-center">Barang</th>
    </tr>
    <tr>
        <th class="bg-body-tertiary text-center" style="width: 120px">Quantity</th>
        <th class="bg-body-tertiary">Nama Barang</th>
        <th class="bg-body-tertiary">Kode Barang</th>
        <th class="bg-body-tertiary">Keterangan</th>
        <th class="bg-body-tertiary text-center" style="width: 120px">Sisa</th>
        <th class="bg-body-tertiary text-center">ID</th>
    </tr>
    </thead>
    <tbody>
    @foreach($transactions as $i => $transaction)
        @php
            $count = $transaction
                ->transaction_products()
                ->where('is_verified', 1)
                ->count() + 1;
            $products = $transaction
                ->products()
                ->wherePivot('is_verified', 1)
                ->get();
            $className = get_table_row_classname($transaction->type);
        @endphp
        <tr>
            <td rowspan="{{$count}}" class="{{$className}}">
                <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                    {{\Carbon\Carbon::parse($transaction->date)->format('d/m/Y')}}
                </label>
            </td>
            <td rowspan="{{$count}}" class="{{$className}}">
                <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                    <a href="{{route('admin.transactions.show', [$transaction])}}" class="{{$className}}">{{$transaction->code}}</a>
                </label>
            </td>
        </tr>
        @foreach($products as $product)
            <tr>
                <td class="text-center {{$className}}">
                    {{$product->pivot->quantity}}
                </td>
                <td class="{{$className}}">
                    <a href="{{route('admin.products.show', [$product])}}" style="color: inherit;">
                        {{$product->name}} {{$product->variant}}
                    </a>
                </td>
                <td class="{{$className}}">
                    {{$product->code}}
                </td>
                <td class="{{$className}}">
                    {{$product->pivot->note}}
                </td>
                <td class="text-center {{$className}}">
                    {{$product->pivot->to_stock}} {{$product->unit}}
                </td>
                <td class="text-center {{$className}}">
                    @if($transaction->creator)
                        {{$transaction->creator->name}}
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
    @endforeach
    @if(count($transactions) == 0)
        <tr>
            <td colspan="11" class="text-center">Tidak ada data</td>
        </tr>
    @endif
    </tbody>
</table>
