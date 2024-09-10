@extends('layouts.index', [
    'title' => 'Data Barang',
    'data' => $products,
    'order_options' => $order_options
])

@section('table')
    <x-alert></x-alert>
    <table class="table table-striped mb-0 table-sm table-hover">
        <thead>
        <tr>
            <th style="width: 80px" class="text-center">No</th>
            <th>Kategori</th>
            <th>Nama Barang / Variant</th>
            <th style="width: 150px">Kode Barang</th>
            <th class="text-center" style="width: 150px">Stok Saat Ini</th>
            <th style="width: 250px" class="text-center">Aksi</th>
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
                <td class="text-center">{{$product->latest_stock}}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-3">
                        @if(auth()->user()->role === 'admin')
                        <a href="{{route('admin.products.destroy', [$product])}}" data-mdb-tooltip-init data-mdb-html="true" title='Hapus Barang <br/> "{{$product->name}} - {{$product->variant}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2 btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                        <a href="{{route('admin.products.edit', [$product])}}" data-mdb-tooltip-init data-mdb-html="true" title='Edit Barang <br/> "{{$product->name}} - {{$product->variant}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        <a href="{{route('admin.products.show', [$product])}}" data-mdb-tooltip-init data-mdb-html="true" title='Lihat Riwayat <br/> Transaksi Barang <br/> "{{$product->name}} - {{$product->variant}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2">
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
    @if(auth()->user()->role === 'admin')
    <div class="d-flex gap-3">
        <div class="dropdown">
            <button
                class="btn btn-outline-success btn-lg dropdown-toggle"
                type="button"
                data-mdb-dropdown-init
                data-mdb-ripple-init
                aria-expanded="false"
                id=""
            >
                <i class="fas fa-file-import me-2"></i> Impor
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li>
                    <a class="dropdown-item" href="{{asset('/template/template-data-barang.xlsx')}}" download="template-data-barang.xlsx">
                        <i class="fas fa-table me-1"></i> Download Template Excel
                    </a>
                </li>
                <li>
                    <form action="{{route('admin.products.import')}}" method="POST" enctype="multipart/form-data" id="form-import-products">
                        @csrf
                        <label class="dropdown-item" for="file-products">
                            <i class="fas fa-upload me-1"></i> Impor dari File Excel
                        </label>
                        <input style="position: absolute; pointer-events: none; opacity: 0;" type="file" name="file" id="file-products" onchange="event.preventDefault(); document.getElementById('form-import-products').submit()">
                    </form>
                </li>
            </ul>
        </div>


        <x-export-button table="products"></x-export-button>
        <a href="{{route('admin.products.create')}}" class="btn btn-primary btn-lg">
            Tambah Barang
        </a>
    </div>
    @endif
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i> Data Barang
    </div>
@endsection
