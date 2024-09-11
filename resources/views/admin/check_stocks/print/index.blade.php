<table border="1" style="border-collapse: collapse">
    <thead>
    <tr>
        <td>Nama Barang</td>
        <td>Kode Barang</td>
        <td>Stok di Gudang</td>
        <td>Unit</td>
        <td>Stok</td>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $product)
        <tr>
            <td>{{$product->name}} {{$product->variant}}</td>
            <td>{{$product->code}}</td>
            <td></td>
            <td>{{$product->unit}}</td>
            <td>{{$product->stock}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
