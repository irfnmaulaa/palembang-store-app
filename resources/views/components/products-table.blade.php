<x-alert></x-alert>
<table class="table mb-0 table-sm table-hover">
    <thead>
    <tr>
        <th style="width: 200px">Kategori</th>
        <th>Nama Barang</th>
        <th style="width: 150px">Kode Barang</th>
        <th class="text-center" style="width: 180px">Stok Saat Ini</th>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $i => $product)
        <tr>
            <td>
                @if($product->category)
                    <a href="{{route('admin.categories.show', [$product->category])}}">
                        {{$product->category->name}}
                    </a>
                @else
                    -
                @endif
            </td>
            <td>
                <a href="{{route('admin.products.show', [$product])}}">
                    {{$product->name}} {{$product->variant}}
                </a>
            </td>
            <td>
                {{$product->code}}
            </td>
            <td class="text-center">
                {{$product->stock ?? '0'}}
                {{$product->unit}}
            </td>
        </tr>
    @endforeach
    @if(count($products) <= 0)
        <tr>
            <td colspan="4" class="text-center">Tidak ada data</td>
        </tr>
    @endif
    </tbody>
</table>
