@extends('layouts.index', [
    'title' => 'Data Barang',
    'data' => $products,
    'order_options' => $order_options,
    'withNoOrder' => true,
    'withNoFilter' => true
])

@section('table')
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

@section('filter')
    <form action="" method="GET">
        @foreach(request()->except(['product_category_id', 'page']) as $key => $value)
            <input type="hidden" name="{{$key}}" value="{{$value}}">
        @endforeach

        <div class="d-flex align-items-center gap-2" style="min-width: 320px; white-space: nowrap;">
            <span><small class="text-muted">Kategori</small></span>
            <div class="input-group input-group-lg" style="position:relative;">
                <select name="product_category_id" id="product_category_id" class="form-control form-control-lg select-category">
                    @if(request('product_category_id') && $category = \App\Models\ProductCategory::find(request('product_category_id')))
                        <option value="{{ $category->id }}">
                            {{$category->name}}
                        </option>
                    @else
                        <option value="">Semua Kategori</option>
                    @endif
                </select>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
            @if(request()->has('product_category_id'))
            <button style="all: unset; font-size: 14px;" class="text-danger text-decoration-underline" form="form-reset-filter">Reset</button>
            @endif
        </div>
    </form>
    <form action="" id="form-reset-filter">
        @foreach(request()->except(['product_category_id', 'page']) as $key => $value)
            <input type="hidden" name="{{$key}}" value="{{$value}}">
        @endforeach
    </form>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $( '.select-category' ).select2( {
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                ajax: {
                    delay: 250,
                    url: '{{route('admin.categories.index')}}',
                    data: function (params) {
                        var query = {
                            keyword: params.term,
                            page: params.page || 1
                        }
                        // Query parameters will be ?search=[term]&page=[page]
                        return query;
                    }
                },
                dropdownParent: $('.select-category').parents('.input-group')
            } );
        })
    </script>
@endsection

