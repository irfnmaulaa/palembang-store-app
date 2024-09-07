@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h2 class="mb-0">Kategori</h2>
                <div class="d-flex align-items-center gap-2 fs-6 text-muted">
                    <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i> Kategori
                </div>
            </div>
            <a href="{{route('admin.categories.create')}}" class="btn btn-primary btn-lg">
                Tambah Kategori
            </a>
        </div>

        <div class="card border shadow-none">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <form action="" method="GET">
                        <input type="hidden" name="page" value="1">
                        @foreach(request()->except(['keyword', 'page']) as $key => $value)
                            <input type="hidden" name="{{$key}}" value="{{$value}}">
                        @endforeach
                        <div class="input-group input-group-lg" style="max-width: 300px;">
                            <input type="text" id="keyword" class="form-control form-control-lg" placeholder="Cari kategori.." name="keyword" value="{{request('keyword')}}"/>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    <form action="" method="GET">
                        @foreach(request()->except(['order', 'page']) as $key => $value)
                            <input type="hidden" name="{{$key}}" value="{{$value}}">
                        @endforeach
                        <div class="d-flex align-items-center gap-2">
                            <span><small class="text-muted">Urutkan</small></span>
                            <div class="input-group input-group-lg">
                                <select name="order" id="order" class="form-control form-control-lg">
                                    <option value="name-asc" {{request('order') === 'name-asc' ? 'selected' : ''}}>Nama A-Z</option>
                                    <option value="name-desc" {{request('order') === 'name-desc' ? 'selected' : ''}}>Nama Z-A</option>
                                    <option value="products_count-desc" {{request('order') === 'products_count-desc' ? 'selected' : ''}}>Jumlah barang paling banyak</option>
                                    <option value="products_count-asc" {{request('order') === 'products_count-asc' ? 'selected' : ''}}>Jumlah barang paling sedikit</option>
                                    <option value="created_at-desc" {{request('order') === 'created_at-desc' ? 'selected' : ''}}>Terkahir dibuat</option>
                                    <option value="created_at-asc" {{request('order') === 'created_at-asc' ? 'selected' : ''}}>Pertama dibuat</option>
                                </select>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-sort"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body py-0">
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
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="text-muted"><small>Ditampilkan {{$categories->firstItem()}} - {{$categories->count() - 1 + $categories->firstItem()}} dari {{$categories->total()}} kategori</small></div>
                    {{ $categories->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
