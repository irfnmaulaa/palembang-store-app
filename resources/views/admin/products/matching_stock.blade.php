@extends('layouts.index', [
    'title' => 'Data Barang',
    'data' => $products,
    'order_options' => [],
    'withNoOrder' => true,
    'withNoFilter' => true
])

@section('table')
    <x-alert></x-alert>
    <table class="table mb-0 table-sm table-hover">
        <thead>
        <tr>
            <th rowspan="2" style="width: 200px">Kategori</th>
            <th rowspan="2">Nama Barang</th>
            <th rowspan="2" style="width: 150px">Kode Barang</th>
            <th class="text-center" style="width: 360px" colspan="2">Stok Saat Ini</th>
        </tr>
        <tr>
            <th class="text-center" style="width: 180px">Aplikasi Lama</th>
            <th class="text-center" style="width: 180px">Aplikasi Baru</th>
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
                <td class="text-center">
                    {{$product->stock_at_old_app ?? '0'}}
                    {{$product->unit}}

                    @if($product->is_matched_stock)
                    <i class="fas fa-check text-success ms-2"></i>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a>  <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.products.index')}}">Data Barang</a>  <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Matching Stock
    </div>
@endsection

