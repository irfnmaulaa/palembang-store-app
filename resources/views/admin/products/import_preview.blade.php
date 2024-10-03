@extends('layouts.index', [
    'title' => 'Import Barang',
    'data' => $products,
    'order_options' => [],
    'withNoOrder' => true,
    'withNoFilter' => true,
    'withNoSummary' => true,
    'withNoPagination' => true,
    'withNoSearch' => true,
])

@section('cta')
    @if(auth()->user()->role === 'admin')
        <div class="d-flex gap-3">
            <form action="{{route('admin.products.import_preview')}}" method="POST" enctype="multipart/form-data" id="form-import-products">
                @csrf
                <label for="file-products"
                       class="btn btn-outline-success btn-lg"
                       type="button"
                >
                    <i class="fas fa-file-upload me-2"></i> Upload Ulang
                </label>
                <input style="position: absolute; pointer-events: none; opacity: 0;" type="file" name="file" id="file-products" onchange="event.preventDefault(); document.getElementById('form-import-products').submit()">
            </form>

            <button type="submit" form="form-import" class="btn btn-success btn-lg" {{ $products_valid_count == 0 ? 'disabled' : '' }}>
                Konfirmasi
            </button>
        </div>
    @endif
@endsection

@section('table')
    <div class="mb-3">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tbody>
                    <tr>
                        <td style="width: 200px;">Barang yang di upload</td>
                        <td style="width: 50px;" class="text-center">:</td>
                        <td>
                            {{ $products->count() }}
                        </td>
                    </tr>
                    <tr>
                        <td>Barang valid</td>
                        <td class="text-center">:</td>
                        <td>
                            {{ $products_valid_count }}
                        </td>
                    </tr>
                    <tr>
                        <td>Barang tidak valid</td>
                        <td class="text-center">:</td>
                        <td>
                            {{ $products->count() - $products_valid_count }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form action="{{route('admin.products.import')}}" method="POST" id="form-import">
        @csrf
        <table class="table mb-0 table-sm table-bordered">
            <thead>
            <tr>
                <th style="width: 200px">Kategori</th>
                <th>Nama Barang</th>
                <th>Variant</th>
                <th style="width: 150px">Kode Barang</th>
                <th class="text-center" style="width: 90px">Satuan</th>
                <th class="text-center" style="width: 120px">Status</th>
                <th>Keterangan</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $i => $product)

                @if($product['is_valid'])
                <input type="hidden" name="products[{{ $i }}][category_name]" value="{{$product['category_name']}}">
                <input type="hidden" name="products[{{ $i }}][name]" value="{{$product['name']}}">
                <input type="hidden" name="products[{{ $i }}][variant]" value="{{$product['variant']}}">
                <input type="hidden" name="products[{{ $i }}][code]" value="{{$product['code']}}">
                <input type="hidden" name="products[{{ $i }}][unit]" value="{{$product['unit']}}">
                @endif

                @php
                    $class_name = $product['is_valid'] ? 'text-success' : 'bg-danger text-white';
                @endphp
                <tr>
                    <td class="{{ $class_name }}">
                        {{$product['category_name']}}
                    </td>
                    <td class="{{ $class_name }}">
                        {{$product['name']}}
                    </td>
                    <td class="{{ $class_name }}">
                        {{$product['variant']}}
                    </td>
                    <td class="{{ $class_name }}">
                        {{$product['code']}}
                    </td>
                    <td class="text-center {{ $class_name }}">
                        {{$product['unit']}}
                    </td>
                    <td class="text-center {{ $class_name }}">
                        @if($product['is_valid'])
                            Valid
                        @else
                            Tidak Valid
                        @endif
                    </td>
                    <td class="{{ $class_name }}">
                        {{$product['note'] ? $product['note'] : '-'}}
                    </td>
                </tr>
            @endforeach
            @if(count($products) <= 0)
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data</td>
                </tr>
            @endif
            </tbody>
        </table>
    </form>
@endsection

@section('cta')

@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.products.index')}}">Data Barang</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Import Barang
    </div>
@endsection

