@extends('layouts.index', [
    'title' => 'Data Barang',
    'data' => $products,
    'order_options' => $order_options
])

@section('table')
    <table class="table table-striped mb-0 table-sm table-hover">
        <thead>
        <tr>
            <th style="width: 80px" class="text-center">No</th>
            <th>Kategori</th>
            <th>Nama Barang / Variant</th>
            <th style="width: 150px">Kode Barang</th>
            <th class="text-center" style="width: 150px">Stok Saat Ini</th>
            @if(auth()->user()->role === 'admin')
            <th style="width: 250px" class="text-center">Aksi</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($products as $i => $product)
            <tr>
                <td class="text-center">{{number_format($products->firstItem() + $i, '0', ',', '.')}}</td>
                <td>
                    @if($product->category)
                        {{$product->category->name}}
                    @else
                        -
                    @endif
                </td>
                <td>{{$product->name}} / {{$product->variant}}</td>
                <td>{{$product->code}}</td>
                <td class="text-center">{{$product->stock}}</td>
                @if(auth()->user()->role === 'admin')
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
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('cta')
    @if(auth()->user()->role === 'admin')
    <a href="{{route('admin.products.create')}}" class="btn btn-primary btn-lg">
        Tambah Barang
    </a>
    @endif
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i> Data Barang
    </div>
@endsection
