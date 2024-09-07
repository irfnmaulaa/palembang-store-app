@extends('layouts.index', [
    'title' => 'Data Kategori',
    'data' => $categories,
    'order_options' => $order_options
])

@section('table')
    <table class="table table-bordered mb-0 table-sm">
        <thead>
        <tr>
            <th style="width: 80px" class="text-center">No</th>
            <th>Nama</th>
            <th style="width: 200px" class="text-center">Jumlah Barang</th>
            <th style="width: 250px" class="text-center">Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $i => $category)
            <tr>
                <td class="text-center">{{$categories->firstItem() + $i}}</td>
                <td>{{$category->name}}</td>
                <td class="text-center">{{$category->products_count}}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{route('admin.categories.destroy', [$category])}}" data-mdb-tooltip-init data-mdb-html="true" title='Hapus Kategori <br/> "{{$category->name}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2 btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                        <a href="{{route('admin.categories.edit', [$category])}}" data-mdb-tooltip-init data-mdb-html="true" title='Edit Kategori <br/> "{{$category->name}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{route('admin.categories.show', [$category])}}" data-mdb-tooltip-init data-mdb-html="true" title='Lihat Barang - Barang <br/> "{{$category->name}}"' class="btn p-2 shadow-none border btn-lg d-flex align-items-center gap-2">
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
    <a href="{{route('admin.categories.create')}}" class="btn btn-primary btn-lg">
        Tambah Kategori
    </a>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i> Data Kategori
    </div>
@endsection
