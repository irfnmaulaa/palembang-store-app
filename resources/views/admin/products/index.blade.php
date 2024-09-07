@extends('layouts.index', [
    'title' => 'Data Barang',
    'data' => $products,
    'order_options' => $order_options
])

@section('table')
    <table class="table table-bordered mb-0 table-sm table-hover">
        <thead>
        <tr>
            <th style="width: 80px" class="text-center">No</th>
            <th style="width: 100px" class="text-center">Kode</th>
            <th>Nama Barang - Variant</th>
            <th>Kategori</th>
            <th style="width: 250px" class="text-center">Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $i => $product)
            <tr>
                <td class="text-center">{{number_format($products->firstItem() + $i, '0', ',', '.')}}</td>
                <td class="text-center">{{$product->code}}</td>
                <td>{{$product->name}} - {{$product->variant}}</td>
                <td>
                    @if($product->category)
                        {{$product->category->name}}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{route('admin.products.destroy', [$product])}}" data-mdb-tooltip-init data-mdb-html="true" title='Hapus Barang <br/> "{{$product->name}} - {{$product->variant}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2 btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                        <a href="{{route('admin.products.edit', [$product])}}" data-mdb-tooltip-init data-mdb-html="true" title='Edit Barang <br/> "{{$product->name}} - {{$product->variant}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{route('admin.products.show', [$product])}}" data-mdb-tooltip-init data-mdb-html="true" title='Lihat Detail Barang <br/> "{{$product->name}} - {{$product->variant}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('cta')
    <a href="{{route('admin.products.create')}}" class="btn btn-primary btn-lg">
        Tambah Barang
    </a>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i> Data Barang
    </div>
@endsection
