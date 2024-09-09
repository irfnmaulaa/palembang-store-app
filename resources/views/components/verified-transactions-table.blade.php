<table class="table table-bordered mb-0 table-sm">
    <thead>
    <tr>
        <th class="bg-body-tertiary" rowspan="2">Tanggal</th>
        <th class="bg-body-tertiary" rowspan="2">No DO</th>
        <th rowspan="2" class="bg-body-tertiary">Dibuat oleh</th>
        <th colspan="8" class="bg-body-tertiary text-center">Barang</th>
    </tr>
    <tr>
        <th class="bg-body-tertiary">Nama Barang / Variant</th>
        <th class="bg-body-tertiary">Kode Barang</th>
        <th class="bg-body-tertiary text-center" style="width: 100px">Stok Awal</th>
        <th class="bg-body-tertiary text-center" style="width: 100px">Quantity</th>
        <th class="bg-body-tertiary text-center" style="width: 100px">Akhir</th>
        <th class="bg-body-tertiary">Satuan</th>
        <th class="bg-body-tertiary">Keterangan</th>
        <th class="bg-body-tertiary">Diverifikasi oleh</th>
    </tr>
    </thead>
    <tbody>
    @foreach($transactions as $i => $transaction)
        @php
            $count = $transaction->transaction_products()->where('is_verified', 1)->count() + 1;
            $products = $transaction->products()->wherePivot('is_verified', 1)->get();
            $className = $transaction->type == 'in' ? 'text-primary fw-bold' : 'text-danger fw-bold';
        @endphp
        <tr>
            <td rowspan="{{$count}}" class="{{$className}}">
                <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                    {{\Carbon\Carbon::parse($transaction->date)->format('d/m/Y H:i')}}
                </label>
            </td>
            <td rowspan="{{$count}}" class="{{$className}}">
                <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                    {{$transaction->code}}
                </label>
            </td>
            <td rowspan="{{$count}}" class="{{$className}}">
                <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                    @if($transaction->creator)
                        {{$transaction->creator->name}}
                    @else
                        -
                    @endif
                </label>
            </td>
        </tr>
        @foreach($products as $product)
            <tr>
                <td class="{{$className}}">
                    {{$product->name}} / {{$product->variant}}
                </td>
                <td class="{{$className}}">
                    {{$product->code}}
                </td>
                <td class="text-center {{$className}}">
                    {{$product->pivot->from_stock}}
                </td>
                <td class="text-center {{$className}}">
                    {{$product->pivot->quantity}}
                </td>
                <td class="text-center {{$className}}">
                    {{$product->pivot->to_stock}}
                </td>
                <td class="{{$className}}">
                    {{$product->unit}}
                </td>
                <td class="{{$className}}">
                    {{$product->pivot->note}}
                </td>
                <td class="{{$className}}">
                    @if($product->pivot->verified_by)
                        @php
                            $verificator = \App\Models\User::find($product->pivot->verified_by);
                        @endphp
                        {{$verificator->name}}
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
    @endforeach
    @if(count($transactions) == 0)
        <tr>
            <td colspan="7" class="text-center">Tidak ada data</td>
        </tr>
    @endif
    </tbody>
</table>
