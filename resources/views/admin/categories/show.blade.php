@extends('layouts.index', [
    'title' => $category->name,
    'data' => $products,
])

@section('header')
    <h3 class="mb-0 mt-2" style="margin-bottom: -0.3rem !important;">BARANG</h3>
@endsection

@section('table')
    <x-alert></x-alert>
    <form id="form-remove" action="{{route('admin.categories.remove_product', [$category])}}" method="POST">
        @csrf
        @method('DELETE')
        <table class="table mb-0 table-sm table-hover">
            <thead>
            <tr>
                <th class="text-center" style="width: 50px">
                    <div class="d-flex justify-content-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="check_all" />
                        </div>
                    </div>
                </th>
                <th>Nama Barang</th>
                <th style="width: 150px">Kode Barang</th>
                <th class="text-center" style="width: 150px">Stok Saat Ini</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $i => $product)
                <tr>
                    <td>
                        <div class="d-flex justify-content-center">
                            <label for="product-{{$product->id}}" class="form-check d-flex justify-content-center">
                                <input class="form-check-input transaction-checkbox" type="checkbox" name="product_ids[]" value="{{$product->id}}" id="product-{{$product->id}}" />
                            </label>
                        </div>
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
                        {{$product->latest_stock ? $product->latest_stock : '0'}}
                        {{$product->unit}}
                    </td>
                </tr>
            @endforeach
            @if(count($products) <= 0)
                <tr>
                    <td class="text-center" colspan="4">Tidak ada data</td>
                </tr>
            @endif
            </tbody>
        </table>
    </form>
@endsection

@section('cta')
    @if(auth()->user()->role === 'admin')
        <div class="d-flex gap-3 align-items-center">
            <a href="{{route('admin.categories.edit', [$category])}}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-edit me-1"></i> Edit Kategori</span>
            </a>
            <a href="{{route('admin.categories.destroy', [$category])}}" class="btn btn-delete btn-outline-danger btn-lg">
                <i class="fas fa-trash me-1"></i> Hapus Kategori
            </a>
        </div>
    @endif
@endsection

@section('top-right')
    @if(auth()->user()->role === 'admin')
    <div class="d-flex gap-3 align-items-center">
        <button type="submit" form="form-remove" class="btn btn-danger btn-lg disabled btn-remove">
            <i class="fas fa-minus me-1"></i> Keluarkan Barang <span class="ms-1" id="checked-count">(0)</span>
        </button>
        <a href="" class="btn btn-primary btn-lg" data-mdb-ripple-init data-mdb-modal-init data-mdb-target="#modal-add-product">
            <i class="fas fa-plus me-1"></i> Masukan Barang
        </a>

        <div class="modal fade modal-xl" id="modal-add-product" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Masukan Barang ke Kategori</h4>
                    </div>
                    <div class="modal-body">
                        <form action="{{route('admin.categories.add_product', [$category])}}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="products">Pilih Barang</label>
                                <select name="product_ids[]" id="products" class="form-control select-product" multiple="multiple">
                                </select>
                            </div>

                            <div class="d-flex gap-3 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Masukan</button>
                                <a href="" data-mdb-ripple-init data-mdb-dismiss="modal" class="btn btn-outline-primary btn-lg">Batalkan</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.categories.index')}}">Kategori</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        {{$category->name}}
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#modal-add-product').on('shown.bs.modal', function () {
                $('.select-product').select2('open')
            })
            $( '.select-product' ).select2( {
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                ajax: {
                    delay: 250,
                    url: '{{route('admin.products.index')}}',
                    data: function (params) {
                        var query = {
                            keyword: params.term,
                            page: params.page || 1
                        }
                        return query;
                    }
                },
                dropdownParent: $('.select-product').parents('.form-group')
            } );

            $('body').delegate('#check_all', 'change', function() {
                if($(this).is(':checked')) {
                    $(this).parents('table').find('tbody input[type="checkbox"]').prop('checked', true).trigger('change');
                } else {
                    $(this).parents('table').find('tbody input[type="checkbox"]').prop('checked', false).trigger('change');
                }
            })

            $('body').delegate('table tbody input[type="checkbox"]', 'change', function() {
                const checkedCount = $(this).parents('table').find('tbody input[type="checkbox"]:checked').length
                $('#checked-count').html(`(${ checkedCount })`)
                if(checkedCount >= $(this).parents('table').find('tbody input[type="checkbox"]').length) {
                    $(this).parents('table').find('#check_all').prop('checked', true)
                } else {
                    $(this).parents('table').find('#check_all').prop('checked', false)
                }

                if(checkedCount > 0) {
                    $('.btn-remove').removeClass('disabled')
                } else {
                    $('.btn-remove').addClass('disabled')
                }
            })
        })
    </script>
@endsection

